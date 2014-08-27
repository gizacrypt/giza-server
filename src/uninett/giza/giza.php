<?php namespace uninett\giza;

/**
 * Giza application class.
 * It will read the configfile on construction
 *  and make the values available via its methods.
 */
class Giza {

	private static $instance;
	
	public static function getInstance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new Giza;
		}
		return static::$instance;
	}

	protected $config;

	public function __construct($configfile = NULL) {
		if (isset($configfile)) {
			$this->config = $configfile;
		} else {
			$this->config = require dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR
				. 'etc' . DIRECTORY_SEPARATOR . 'giza.conf.php';
		}
	}

	/**
	 * Get the storage API for secrets
	 */
	public function getSecretStore() {
		return $this->config['secretStore'];
	}
	/**
	 * Get the identity source
	 */
	public function getIdentitySource() {
		return $this->config['identitySource'];
	}

	/**
	 * Get auxiliary identity sources
	 */
	public function getAuxiliaryIdentitySources() {
		return $this->config['auxiliaryIdentitySources'];
	}

	/**
	 * Get the storage API for identities
	 */
	public function getIdentityStore() {
		return $this->config['identityStore'];
	}

	/**
	 * Get path to the GPG binary
	 */
	public function getGpgBinaryPath() {
		return $this->config['gpgBinaryPath'];
	}
	
	/**
	 * Get path to the GPG home directory
	 */
	public function getGpgHomedirPath() {
		return $this->config['gpgHomedirPath'];
	}
	
	/**
	 * Get path to the standard identity image
	 */
	public function getStandardIdentityImage() {
		return $this->config['standardIdentityImage'];
	}

}
