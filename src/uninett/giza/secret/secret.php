<?php namespace uninett\giza\secret;

use \DomainException;
use \Serializable;

use \uninett\giza\Giza;
use \uninett\giza\core\GPG;
use \uninett\giza\core\PGPPublicKey;
use \uninett\giza\core\PopulatedGPG;
use \uninett\giza\identity\Profile;
use \uninett\giza\secret\input\AbstractInputValidator;
use \uninett\giza\secret\output\AbstractOutputGenerator;

/**
 * Giza API for secrets.  A Giza secret is a GPG encrypted file,
 * with additional signed metadata.
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
final class Secret {

	const ACCESS_READ = 4;
	const ACCESS_WRITE = 2;
	const ACCESS_ADMIN = 1;
	const ACCESS_FULL = 7; // ACCESS_READ|ACCESS_WRITE|ACCESS_ADMIN

	const ACTION_NEW = 'new';
	const ACTION_READ = 'read';
	const ACTION_UPDATE = 'update';
	const ACTION_WRITE = 'write';
	const ACTION_DELETE = 'delete';
	const ACTION_META = 'meta';

	protected static $actionPermissions = [
		'new' => 0, // like newupload
		'read' => 4, // self::ACCESS_READ
		'update' => -1, // disallow
		'write' => 2, // self::ACCESS_WRITE
		'delete' => 1, // self::ACCESS_ADMIN
		'meta' => 3, // self::ACCESS_WRITE|self::ACCESS_ADMIN
	];

	/**
	 * Get all secrets that are accessible from either the current or a given profile.
	 *
	 * @param Profile $profile The profile to check
	 * @param int $mask Bit map of all qualifying access bits
	 */
	public static function getSecretsForProfile(Profile $profile = null, $mask = -1) {
		if (is_null($profile)) {
			$profile = Profile::fromStore();
		}
		if (is_null($profile)) {
			throw new DomainException('No profile provided and none available from session.');
		}
		$result = [];
		foreach(Giza::getInstance()->getSecretStore()->getNewestSecrets() as $secret) {
			if (($secret->getPermissions($profile) & $mask) > 0) {
				$result[] = $secret;
			}
		}
		return $result;
	}

	/**
	 * Shorthand for <code>Giza::getInstance()->getSecretStore()->getSecret($uuid)</code>
	 *
	 * @return Secret secret with the requested UUID
	 * @throws RuntimeException if a secret with the requested UUID does not exist
	 */
	public static function getSecret($uuid) {
		return Giza::getInstance()->getSecretStore()->getSecret($uuid);
	}

	/**
	 * Get required permissions to execute an action
	 *
	 * @param string $action The action
	 *
	 * @return int required permissions
	 * @throws RuntimeException if the requested action is unknown
	 */
	public static function getRequiredPermissionsForAction($action) {
		if (!isset(static::$actionPermissions[strtolower($action)])) {
			throw new DomainException('Unknown action: '.$action);
		}
		return static::$actionPermissions[strtolower($action)];
	}

	/**
	 * @var SecretStore the secret store where this secret is stored
	 */
	protected $store;

	/**
	 * @var PGPPublicKey the PGP public key used to sign this secret
	 */
	protected $key;

	/**
	 * @var string the raw contents of this secret
	 */
	protected $rawContents;

	/**
	 * @var string the raw contents of this secret, as stored in the file storage
	 */
	protected $signedContents;

	/**
	 * @var string the metadata of this secret, in PGP clearsigned container
	 */
	protected $rawMetadata;

	/**
	 * @var Metadata parsed metadata object
	 */
	protected $metadata;

	/**
	 * @param string $contents contents of the secret
	 * @param SecretStore $store store of the secret
	 *
	 * @throws RuntimeException if the secret is wrongly formatted or has an invalid signature
	 */
	public function __construct($contents, SecretStore $store = null) {
		$this->store = $store;
		$this->rawContents = $contents;
		$gpg = new PopulatedGPG();
		$this->signedContents = $gpg->verifyClear($this->rawContents, $key1);
		preg_match('_\n-----BEGIN PGP SIGNED MESSAGE-----\n.+_s', $this->signedContents, $matches);
		$this->rawMetadata = $matches[0];
		if (!$this->rawMetadata) {
			throw new DomainException('No signed metadata found');
		}
		$this->metadata = new Metadata($gpg->verifyClear($this->rawMetadata, $key2));
		if ($key1 != $key2) {
			throw new DomainException('Inner and outer signatures must be made with the same key.');
		}
		$this->key = PGPPublicKey::fromKeyId(reset($key1));
	}

	/**
	 * Determine whether this secret and another secret have a common ancestor.
	 * Two secrets have a common ancestor if it is possible to eventually get to the same secret
	 *  by calling getPrevious() on both secrets.
	 *
	 * @param Secret $secret the other secret to test whether it has a common ancestor with this secret
	 *
	 * @return boolean the secrets have a common ancestor
	 */
	protected function hasCommonAncestor($secret) {
		if ($secret == $this) {
			return true;
		}
		$ancestors = [];
		while(!is_null($secret)) {
			$ancestors[] = $secret;
			$secret = $secret->getPrevious();
		}
		$secret = $this;
		while(!is_null($secret)) {
			if (in_array($secret, $ancestors)) {
				return true;
			}
			$secret = $secret->getPrevious();
		}
		return false;
	}

	/**
	 * Get the UUID of this secret.
	 *
	 * The UUID is used as a unique persistent identifier of a secret.
	 * The UUID is public information and used as the filename of a secret.
	 *
	 * @return string the UUID of this secret
	 */
	public function getUUID() {
		return $this->metadata->getRevision();
	}

	/**
	 * Get the metadata associated with this secret
	 *
	 * @return Metadata metadata associated with this secret
	 */
	public function getMetadata() {
		return $this->metadata;
	}

	/**
	 * Get the secret that replaced this secret.
	 * Return <code>null</code> if this secret has no newer version.
	 *
	 * @return Secret secret that replaces this secret
	 */
	public function getNext() {
		$this->store->getNextSecret($this);
	}

	/**
	 * Returns whether this secret has a newer version.
	 *
	 * @return boolean this secret has a newer version
	 */
	public function hasNext() {
		return !is_null($this->getNext());
	}

	/**
	 * Get the secret that was the most recent one before this secret was created
	 *
	 * @return Secret previous secret
	 */
	public function getPrevious() {
		$previous = $this->metadata->getPrevious();
		return is_null($previous) ? NULL : static::getSecret($previous);
	}

	/**
	 * Get the secret that this secret was based on.
	 *
	 * @return Secret secret this secret was based on
	 */
	public function getBasedOn() {
		$basedOn = $this->metadata->getBasedOn();
		return is_null($basedOn) ? NULL : static::getSecret($basedOn);
	}

	/**
	 * Get the latest version of this secret
	 *
	 * @return Secret the latest version of this secret.
	 */
	public function getLatest() {
		$current = $this;
		while(true) {
			$next = $current->getNext();
			if (is_null($next)) {
				return $current;
			}
			$current = $next;
		}
	}

	/**
	 * Get the encrypted GPG payload.
	 * This is a multi-line string that contains an ASCII-armoured GPG encrypted payload.
	 *
	 * @return string encrypted payload
	 */
	public function getEncryptedPayload() {
		preg_match(
			'_\n-----BEGIN PGP MESSAGE-----\n.+\n-----END PGP MESSAGE-----\n_s', 
			$this->signedContents, 
			$matches
		);
		return $matches[0];
	}

	/**
	 * Generate a Giza file with an action
	 *
	 * @param string[string] $parameters parameters for how the file must be generated
	 * @param Profile $identity the identity that requested the action, null to use current
	 *
	 * @return void, headers and a Content-Length header are sent, so no more output is accepted
	 *
	 * @throws RuntimeException if generation failed
	 */
	public function generateOutput($parameters, Profile $identity = null) {
		$generator = AbstractOutputGenerator::getGenerator($parameters, $this, $identity);
		$generator->generateOutput();
	}

	/**
	 * Get the name of this secret.
	 *
	 * @return string name of this secret
	 */
	public function getName() {
		return $this->store->getName($this->getUUID());
	}

	/**
	 * Return the permissions that a profile has for this secret.
	 *
	 * @param Profile $profile The profile
	 *
	 * @return int Permissions bitmask
	 */
	public function getPermissions(Profile $user) {
		$result = 0;
		$keyIDs = [];
		foreach($user->getPGPPublicKeys() as $key) {
			$keyIDs[] = $key->getKeyID();
		}
		foreach($this->metadata->getAccess() as $line) {
			$segments = preg_split('/\\s+/', $line, 3);
			if (in_array($segments[1], $keyIDs)) {
				$accessLevels = explode('|', $segments[0]);
				if (in_array('ADMIN', $accessLevels)) $result |= self::ACCESS_ADMIN;
				if (in_array('WRITE', $accessLevels)) $result |= self::ACCESS_WRITE;
				if (in_array('READ',  $accessLevels)) $result |= self::ACCESS_READ;
			}
		}
		return $result;
	}

	/**
	 * Get the key this secret was signed with.
	 *
	 * @return PGPPublicKey the public key
	 */
	public function getSigningKey() {
		return $this->key();
	}

	/**
	 * Add a new secret, this can either be an update or a new chain.
	 *
	 * @return void secret was added
	 *
	 * @throws RuntimeException secret was not added
	 */
	public static function addSecret($contents) {
		$secret = new Secret($contents);
		$action = AbstractInputValidator::getValidator($secret->metadata->getAction(), $secret);
		$action->validate();
		static::getStore()->addValidSecret($secret);
	}

	/**
	 * Comparison of objects in PHP happens through the __toString() method.
	 * It will return the raw contents of the secret, since it contains both payload and UUID,
	 * and is therefore guaranteed to be unique.
	 */
	public function __toString() {
		return $this->rawContents;
	}

	/**
	 * Returns expire object
	 *
	 * @return Expire
	 */
	//function getExpire();

}
