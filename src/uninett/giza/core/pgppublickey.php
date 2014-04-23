<?php namespace uninett\giza\core;

use \FilesystemIterator;
use \LogicException;
use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;
use \RuntimeException;
use \Serializable;

use \uninett\giza\identity\AttributeAssertion;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class PGPPublicKey implements Serializable {

	private $pgpKey;

	private $signatures;

	public function __construct($pgpKey) {
		// TODO also accept binary keys
		// TODO also accept key IDs
		$this->pgpKey = trim($pgpKey);
	}

	public function getKey() {
		return $this->pgpKey;
	}

	private function lazyLoad() {
		if (isset($this->keyList)) {
			return;
		}

		$gpgDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gpg' . microtime(true);
		mkdir($gpgDir);
		chmod($gpgDir, 0700);
		$readSpec = [0 => ['pipe', 'r']];
		$writeSpec = [1 => ['pipe', 'w']];

		try {
			$process = proc_open(
				escapeshellcmd($GLOBALS['gizaConfig']['gpgBinary'])
				. ' --import --homedir '
				. escapeshellarg($gpgDir),
				$readSpec, 
				$pipes, 
				sys_get_temp_dir(), 
				[]
			);
			if (is_resource($process)) {
				fwrite($pipes[0], $this->getFullKey());
				fclose($pipes[0]);
				proc_close($process);
			}
			$process = proc_open(
				escapeshellcmd($GLOBALS['gizaConfig']['gpgBinary'])
				. ' --list-sigs --with-colons --homedir '
				. escapeshellarg($gpgDir),
				$writeSpec, 
				$pipes, 
				sys_get_temp_dir(), 
				[]
			);
			if (is_resource($process)) {
				$this->keyList = explode("\n", stream_get_contents($pipes[1]));
				fclose($pipes[1]);
				proc_close($process);
			}
		} catch (Exception $e) {
			die($e->getMessage());
		} //finally {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($gpgDir, FilesystemIterator::SKIP_DOTS),
				RecursiveIteratorIterator::CHILD_FIRST
			);
			foreach ($iterator as $filename => $fileInfo) {
				if ($fileInfo->isDir()) {
					rmdir($filename);
				} else {
					unlink($filename);
				}
			}
			rmdir($gpgDir);
		//}
	}
	
	/**
	 * Return the ASCII armoured PGP key
	 */
	public function getFullKey() {
		return $this->pgpKey;
	}

	public function getKeyID() {
		$this->lazyLoad();
		foreach($this->keyList as $line) {
			$segments = explode(':', $line);
			if ($segments[0] === 'pub') {
				return $segments[4];
			}
		}
		throw new Exception('No public key in output?');
	}

	/**
	 * Get a list of all key IDs that are signatures
	 *
	 * @param int $minimalTrust	The minimal trust level of the signature (max 3)
	 */
	public function getSignatures($minimalTrust = 0) {
		assert('$minimalTrust >= 0 && $minimalTrust <= 3');
		$this->lazyLoad();
		if (!isset($this->signatures)) {
			$uid = NULL;
			foreach($this->keyList as $line) {
				$segments = explode(':', $line);
				if ($segments[0] === 'uid') {
					$uid = $segments[7];
					$this->identities[$segments[7]] = $segments[9];
					continue;
				} elseif ($segments[0] === 'sig') {
					if (!isset($uid)) {
						throw new Exception('Signature before identity');
					}
					$this->signatures[$uid][] = new GPGSignature($segments);
				}
			}
		}
		if (!$minimalTrust) {
			return $this->signatures;
		}
		$result = [];
		foreach($this->signatures as $id => $signatures) foreach($signatures as $signature) {
			if ($signature->getTrustLevel() >= $minimalTrust) {
				$result[$id][] = $signature;
			}
		}
		return $result;
	}

	/**
	 * Checks whether the key is signed by a trusted party.
	 * This will effectively run getSignatures and check if
	 * at least one of the results is a trusted party from the config.
	 */
	public function isSignedByTrustedParty($identity) {
		if ($identity instanceof AttributeAssertion) {
			foreach($identity->getMails() as $mail) {
				if ($this->isSignedByTrustedParty($mail)) {
					return true;
				}
			}
			return false;
		}
		$exact = substr(trim($identity), -1) === '>' && strpos($identity, '<') !== false;
		foreach($this->getSignatures(3) as $id => $signatures) foreach($signatures as $signature) {
			if (!$exact && substr(trim($this->identities[$id]), -1-strlen($identity), -1) === $identity
				|| $identity === $this->identities[$id]
			) {
				if ($signature->getPGPKey()->isTrustedParty()) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Checks whether this key is revoked
	 */
	public function isRevoked() {
		$this->lazyLoad();
		// TODO: query on external server for revocation

		foreach($this->keyList as $line) {
			if (substr($line, 0, 4) !== 'rev:') {
				return true;
			}
		}
		return false;
	}

	public function isTrustedParty() {
		return false;
	}

	public function serialize() {
		return $this->pgpKey;
	}

	public function unserialize($serialized) {
		$this->pgpKey = $serialized;
	}

}
