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
		} elseif (!is_string($secret)) {
			throw new InvalidArgumentException('Secret must be object or UUID string.');
		}
		return $this->getPath() . DIRECTORY_SEPARATOR . $secret;
	}

	public function getSecret($uuid) {
		return new Secret(file_get_contents($this->getPathForSecret($uuid)), $this);
	}

	public function getNextSecret($secret) {
		if ($secret instanceof Secret) {
			return $this->getNextSecret($secret->getUUID());
		} elseif (is_string($secret)) {
			if (is_link($this->getPathForSecret($secret).'.next')) {
				$this->getSecret(readlink($this->getPathForSecret($secret).'.next'));
			}
		} else {
			throw new InvalidArgumentException('Secret must be object or UUID string.');
		}
		return null;
	}

	public function getName($secret) {
		if ($secret instanceof Secret) {
			return $secret->getUUID();
		} elseif (is_string($secret)) {
			return $secret;
		} else {
			throw new InvalidArgumentException('Secret must be object or UUID string.');
		}
	}

	public function getNewestSecrets() {
		$result = [];
		$paths = glob(
			$this->path . DIRECTORY_SEPARATOR . '????????-????-????-????-????????????',
			GLOB_NOSORT|GLOB_NOESCAPE|GLOB_MARK|GLOB_ERR
		);
		if (!is_array($paths)) {
			throw new DomainException('Cannot read directory '.$this->path);
		}
		foreach($paths as $path) {
			if (!file_exists($path . '.next')) {
				$result[] = $this->getSecret(basename($path));
			}
		}
		return $result;
	}

	public function newSecret($contents) {
		$secret = new Secret($contents);
		if (file_exists($this->getPathForSecret($secret->getUUID()))) {
			throw new DomainException('A secret with UUID '.$secret->getUUID().' already exists.');
		}
		if (is_null($secret->getPreviousUUID()) && is_null($secret->getBasisUUID())) {
			if (!file_put_contents(getPathForSecret($secret), $contents)) {
				throw new RuntimeException('Unable to write secret '.$secret->getUUID());
			}
		} else {
			$previous = $this->getSecret($secret->getPreviousUUID());
			if (!is_null($previous->getNext())) {
				throw new DomainException(
						  'The new secret '
						. $secret->getUUID()
						. ' lists '
						. $previous->getUUID()
						. ' as it predecessor, but '
						. $previos->getLatest()->getUUID()
						. ' is the current revision.'
					);
			}
			// TODO Check authority
			// TODO Check basis and previous have common ancestor
			if (file_put_contents(getPathForSecret($secret), $contents)) {
				symlink($secret->getUUID, getPathForSecret($previous).'.next');
			} else {
				throw new RuntimeException('Unable to write secret '.$secret->getUUID());
			}
		}
	}

	public function getCallbackURL() {
		return (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? 'http' : 'https')
			. '://'
			. $_SERVER['HTTP_HOST']
			. $_SERVER['PHP_SELF']
			;
	}

}
