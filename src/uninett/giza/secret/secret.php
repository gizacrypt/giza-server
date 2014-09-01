<?php namespace uninett\giza\secret;

use \DomainException;
use \Serializable;

use \uninett\giza\Giza;
use \uninett\giza\core\GPG;
use \uninett\giza\core\PGPPublicKey;
use \uninett\giza\core\PopulatedGPG;
use \uninett\giza\identity\Profile;

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

	/**
	 * Get all secrets that are accessible from either the current or a given profile.
	 *
	 * @param Profile $profile The profile to check
	 * @param int $mask Bit map of all qualifying access bits
	 */
	public static function getSecretsForProfile(Profile $profile = null) {
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
	 * Get the URL that the shell script should use to send updated secrets.
	 *
	 * @return string the URL
	 */
	public static function getCallbackURL() {
		return (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? 'http' : 'https')
			. '://'
			. $_SERVER['HTTP_HOST']
			. dirname($_SERVER['PHP_SELF'])
			;
	}

	/**
	 * @var SecretStore the secret store where this secret is stored
	 */
	protected $store;

	/**
	 * @var string UUID of this secret
	 */
	protected $uuid;

	/**
	 * @var string UUID of the secret that replaces this secret, or null
	 */
	protected $nextUUID;

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
	 * @var string the metadata of this secret, without the PGP clearsigned container
	 */
	protected $signedMetadata;

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
		$this->signedMetadata = $gpg->verifyClear($this->rawMetadata, $key2);
		if ($key1 != $key2) {
			throw new DomainException('Inner and outer signatures must be made with the same key.');
		}
		$this->key = PGPPublicKey::fromKeyId($key1);
	}

	/**
	 * Get all values for a given key from the secret's metadata
	 *
	 * @param string $key key whose values are to be returned
	 *
	 * @return string[] all values found, empty array if none found
	 */
	protected function getValues($key) {
		preg_match_all('_^'.$key.':\\s+(.+)$_m', $this->signedMetadata, $matches);
		$result = [];
		foreach(reset($matches) as $match) {
			$result[] = trim(substr($match, strlen($key)+1));
		}
		return $result;
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
		if (is_null($this->uuid)) {
			$this->uuid = reset($this->getValues('Revision'));
		}
		if (!$this->uuid) {
			throw new DomainException('Secret has no UUID');
		}
		return $this->uuid;
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
	 * Get the UUID of the secret that replaced this secret.
	 * Return <code>null</code> if this secret has no newer version.
	 *
	 * @return string UUID of the secret
	 */
	public function getNextUUID() {
		$next = $this->getNext();
		if ($next) {
			return $next->getUUID();
		}
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
	 * Get the UUID of secret that was the most recent one before this secret was created.
	 *
	 * @return string the UUID of the previous secret
	 */
	public function getPreviousUUID() {
		$uuid = reset($this->getValues('Previous'));
		if ($uuid === FALSE) {
			return null;
		}
		return $uuid;
	}

	/**
	 * Get the secret that was the most recent one before this secret was created
	 *
	 * @return Secret previous secret
	 */
	public function getPrevious() {
		return static::getSecret($this->getPreviousUUID());
	}

	/**
	 * Get the UUID of the secret that this secret was based on.
	 *
	 * @return string UUID of the secret this secret was based on
	 */
	public function getBasedOnUUID() {
		$uuid = reset($this->getValues('Basis'));
		if ($uuid === FALSE) {
			return null;
		}
		return $uuid;
	}

	/**
	 * Get the secret that this secret was based on.
	 *
	 * @return Secret secret this secret was based on
	 */
	public function getBasedOn() {
		return static::getSecret($this->getBasedOnUUID);
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
	 * Generate a Giza file with an action
	 *
	 * @param string[string] $parameters parameters for how the file must be generated
	 * @param Profile $profile the identity that requested the action, null to use current
	 *
	 * @return void, headers and a Content-Length header are sent, so no more output is accepted
	 */
	public function action($action, Profile $profile) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/x-giza');
		header('Content-Disposition: attachment; filename='.$this->getUUID().'.giza');
		header('Cache-Control: no-cache');
		$output = trim($this->rawContents)
		        . "\n" . '-----BEGIN GIZA COMMAND-----'
		        . "\n" . 'Action: ' . $action
		        . (is_null($this->getNext())
		        	? ''
		        	: "\n" . 'Latest: ' . $this->getLatest()->getUUID()
		        	)
		        . "\n" . 'Callback: ' . static::getCallbackURL()
		        . "\n" . '-----END GIZA COMMAND-----'
		        . "\n"
		        ;
		header('Content-Length: ' . strlen($output));
		ob_clean();
		flush();
		echo $output;
		exit(0);
	}

	/**
	 * Get the name of this secret.
	 *
	 * @return string name of this secret
	 */
	public function getName() {
		return $this->store->getName($this);
	}

	/**
	 * Get the creation time of this secret
	 *
	 * @return DateTime creation time of this secret
	 */
	public function getTimestamp() {
		$timestamp = DateTime::createFromFormat(DATE_W3C, reset($this->getValues('Date')));
		if (!$timestamp instanceof DateTime) {
			throw new DomainException('Secret has invalid timestamp.');
		} 
	}

	/**
	 * @return DateTime
	 *
	 * @todo Check change type
	 */
	public function getContentChangedTimestamp() {
		return $this->getTimestamp();
	}

	/**
	 * Get the content type of this secret.
	 *
	 * @return string content type of this secret
	 */
	public function getContentType() {
		return reset($this->getValues('Content-Type'));
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
		foreach($this->getMetadataValues('Access') as $line) {
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
	 * Returns expire object
	 *
	 * @return Expire
	 */
	//function getExpire();

}
