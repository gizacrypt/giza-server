<?php namespace uninett\giza;

use \DomainException;

/**
 * Giza application class.
 * It will read the configfile on construction
 *  and make the values available via its methods.
 */
class Giza {

	private static $instance;
	
	public static function setInstance($config = null) {
		static::$instance = new Giza($config);
	}

	public static function getInstance() {
		if ( is_null( static::$instance ) ) {
			static::setInstance();
		}
		return static::$instance;
	}

	protected $config;

	/**
	 * Construct a new Giza application class
	 */
	public function __construct($config = null) {
		if (is_object($config) || is_array($config)) {
			$this->config = (object)$config;
		} elseif (is_string($config)) {
			$this->config = $config;
		} elseif (is_null($config)) {
			$this->config = require dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR
				. 'etc' . DIRECTORY_SEPARATOR . 'giza.conf.php';
		} else {
			throw new DomainException('Config must be object, array, string or null but it is a '.gettype($config));
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
