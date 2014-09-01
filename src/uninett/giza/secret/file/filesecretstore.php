<?php namespace uninett\giza\secret\file;

use \uninett\giza\secret\Secret;
use \uninett\giza\secret\SecretStore;

use \DomainException;
use \InvalidArgumentException;
use \RuntimeException;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class FileSecretStore implements SecretStore {

	/** @var string */
	protected $path;

	public function __construct($path) {
		$this->path = $path;
	}

	public function getPath() {
		return $this->path;
	}

	public function getPathForSecret($secret) {
		if ($secret instanceof Secret) {
			return $this->getPathForSecret($secret->getUUID());
		} elseif (is_string($secret)) {
			return $this->getPath() . DIRECTORY_SEPARATOR . $secret;
		}
		throw new InvalidArgumentException('Secret must be object or UUID string.');
	}

	public function getSecret($uuid) {
		return new Secret(file_get_contents($this->getPathForSecret($uuid)), $this);
	}

	public function getNextSecretUUID($secret) {
		$next = $this->getNextSecret($secret);
		if ($next) {
			return $next->getUUID();
		}
	}

	public function getNextSecret($secret) {
		if ($secret instanceof Secret) {
			return $this->getNextSecret($secret->getUUID());
		} elseif (is_string($secret)) {
			foreach($this->getAllSecrets() as $candidate) {
				if ($candidate->getMetadata()->getPrevious() === $secret) {
					return $candidate;
				}
			}
		} else {
			throw new InvalidArgumentException('Secret must be object or UUID string.');
		}
		return null;
	}

	public function getName($secret) {
		if ($secret instanceof Secret) {
			$uuid = $secret->getUUID();
			while(!is_null($secret)) {
				$path = $this->getPathForSecret($path).'.name';
				if (is_file($path)) {
					return file_get_contents($path);
				}
				$secret = $secret->getBasedOn();
			}
			return $uuid;
		} elseif (is_string($secret)) {
			return $this->getName($this->getSecret($secret));
		}
		throw new InvalidArgumentException('Secret must be object or UUID string.');
	}

	public function getAllSecrets() {
		$paths = glob(
			$this->path . DIRECTORY_SEPARATOR . '????????-????-????-????-????????????',
			GLOB_NOSORT|GLOB_NOESCAPE|GLOB_MARK|GLOB_ERR
		);
		if (!is_array($paths)) {
			throw new DomainException('Cannot read directory '.$this->path);
		}
		return array_map([$this, 'getSecret'], array_map('basename', $paths));
	}

	public function getNewestSecrets() {
		$result = [];
		foreach($this->getAllSecrets() as $secret) {
			if (!$secret->hasNext()) {
				$result[] = $secret;
			}
		}
		return $result;
	}

	public function addValidSecret(Secret $secret) {
		$secret = new Secret($contents);
		if (file_exists($this->getPathForSecret($secret->getUUID()))) {
			throw new DomainException('A secret with UUID '.$secret->getUUID().' already exists.');
		}
		if (!file_put_contents($this->getPathForSecret($secret), $contents)) {
			throw new RuntimeException('Unable to write secret '.$secret->getUUID());
		}
	}

}
