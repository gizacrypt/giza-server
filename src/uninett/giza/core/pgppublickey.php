<?php namespace uninett\giza\core;

use \LogicException;
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

	/**
	 * Get the public key that corresponds to the give key ID.
	 * This Key ID must be available in the populated keychain,
	 * which means that there must exist a user with this key ID.
	 *
	 * @param string $keyId the key ID
	 *
	 * @return PGPPublicKey the public key
	 */
	public static function fromKeyId($keyId) {
		$gpg = new PopulatedGPG();
		return $gpg->exportKey($keyId);
	}

	public function getKey() {
		return $this->pgpKey;
	}

	private function lazyLoad() {
		if (isset($this->keyList)) {
			return;
		}

		$gpg = new GPG();
		$gpg->importKey($this);
		$this->keyList = $gpg->listSigs();
		$gpg->finalize();
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
		throw new Exception('No public key in output!?');
	}

	/**
	 * Get a list of all key IDs that are signatures
	 *
	 * @param int $minimalTrust	The minimal trust level of the signature (max 3)
	 *
	 * @return GPGSignature
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
