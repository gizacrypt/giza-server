<?php namespace uninett\giza\secret;

use \DomainException;
use \Serializable;

use \uninett\giza\core\GPG;
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

	protected $store;
	protected $uuid;
	protected $gpg;
	protected $nextUUID;

	protected $rawContents;
	protected $signedContents;
	protected $rawMetadata;
	protected $signedMetadata;

	/**
	 * @var string $contents contents of the secret
	 * @var SecretStore $store store of the secret
	 */
	public function __construct($contents, SecretStore $store = null) {
		$this->store = $store;
		$this->rawContents = $contents;
		$this->gpg = new PopulatedGPG();
		$this->signedContents = $this->gpg->verifyClear($this->rawContents, $key1);
		preg_match('_\n-----BEGIN PGP SIGNED MESSAGE-----\n.+_s', $this->signedContents, $matches);
		$this->rawMetadata = $matches[0];
		if (!$this->rawMetadata) {
			throw new DomainException('No signed metadata found');
		}
		$this->signedMetadata = $this->gpg->verifyClear($this->rawMetadata, $key2);
		if ($key1 != $key2) {
			throw new DomainException('Inner and outer signatures must be made with the same key.');
		}
	}

	public static function getSecretsForProfile(Profile $profile = null) {
		if (is_null($profile)) {
			$profile = Profile::fromStore();
		}
		if (is_null($profile)) {
			throw new DomainException('No profile provided and none available from session.');
		}
		$result = [];
		foreach($GLOBALS['gizaConfig']['secretStore']->getNewestSecrets() as $secret) {
			if ($secret->getPermissions($profile)) {
				$result[] = $secret;
			}
		}
		return $result;
	}

	public static function getSecret($uuid) {
		return $GLOBALS['gizaConfig']['secretStore']->getSecret($uuid);
	}

	protected function getValues($key) {
		preg_match_all('_^'.$key.':\\s+(.+)$_m', $this->signedMetadata, $matches);
		$result = [];
		foreach(reset($matches) as $match) {
			$result[] = trim(substr($match, strlen($key)+1));
		}
		return $result;
	}

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
	 *
	 * @return Secret
	 */
	public function getNext() {
		$this->store->getNextSecret($this);
	}

	/**
	 * Get the secret that was the most recent one before this secret was created
	 *
	 * @return Secret
	 */
	public function getPreviousUUID() {
		$uuid = reset($this->getValues('Previous'));
		if ($uuid === FALSE) {
			return null;
		}
		return $uuid;
	}

	public function getPrevious() {
		return $this->store->getSecret($this->getPreviousUUID());
	}

	/**
	 * Get the secret this secret was based on.
	 *
	 * @return Secret
	 */
	public function getBasedOnUUID() {
		$uuid = reset($this->getValues('Basis'));
		if ($uuid === FALSE) {
			return null;
		}
		return $uuid;
	}

	public function getBasedOn() {
		return $this->store->getSecret($this->getBasedOnUUID);
	}

	/**
	 * @return Secret the newest update of this secret.
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
	 * Generate a Giza file with this action.
	 *
	 * @param string $action
	 *
	 * @return void
	 */
	public function action($action) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/x-giza');
		header('Content-Disposition: attachment; filename='.$this->getUUID().'.giza');
		header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		$output = trim($this->rawContents)
		        . "\n" . '-----BEGIN GIZA COMMAND-----'
		        . "\n" . 'Action: ' . $action
		        . "\n" . 'Latest: ' . $this->getLatest()->getUUID()
		        . "\n" . 'Callback: ' . $this->store->getCallbackURL()
		        . "\n" . '-----END GIZA COMMAND-----'
		        ;
		header('Content-Length: ' . strlen($output));
		ob_clean();
		flush();
		echo $output;
		exit(0);
	}

	/**
	 * Get the name of this version of the secret.
	 */
	public function getName() {
		return $this->store->getName($this);
	}

	/**
	 * @return DateTime
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
	 * Get the content type of this version of the secret.
	 *
	 * @return string
	 */
	public function getContentType() {
		return reset($this->getValues('Content-Type'));
	}

	/**
	 * Return the permissions for this secret.
	 *
	 * @return int bitmap 
	 */
	function getPermissions(Profile $user) {
		$result = 0;
		$keys = [];
		foreach($user->getPGPPublicKeys() as $key) {
			$keys[] = $key->getKeyID();
		}
		foreach($this->getValues('Access') as $line) {
			$segments = preg_split('/\\s+/', $line, 3);
			if (in_array($segments[1], $keys)) {
				$accessLevels = explode('|', $segments[0]);
				if (in_array('ADMIN', $accessLevels)) $result |= self::ACCESS_ADMIN;
				if (in_array('WRITE', $accessLevels)) $result |= self::ACCESS_WRITE;
				if (in_array('READ',  $accessLevels)) $result |= self::ACCESS_READ;
			}
		}
		return $result;
	}

	/**
	 * Returns expire object
	 *
	 * @return Expire
	 */
	//function getExpire();

}
