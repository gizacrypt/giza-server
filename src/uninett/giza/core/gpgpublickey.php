<?php namespace uninett\giza\core;

use \Serializable;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class GPGPublicKey implements Serializable {

	private $pgpKey;

	private $keychain;

	const TRUST_REVOKED = -1;
	const TRUST_UNKNOWN = 0;
	const TRUST_TRUSTED = 1;

	public function __construct($pgpKey) {
		$this->pgpKey = trim($pgpKey);
	}

	public function getKey() {
		return $this->pgpKey;
	}

	private function lazyLoad() {
		if (isset($this->keychain)) {
			return;
		}

		$file = tempnam();
		$descriptorspec = [0 => ['pipe', 'r']];

		try {
			$process = proc_open(
				shellescapecmd(GizaConfig::$gpgBinary)
				. ' --no-default-keyring --import --keyring '
				. escapeshellarg($file), 
				$descriptorspec, 
				$pipes, 
				sys_get_temp_dir(), 
				[]
			);
			if (is_resource($process)) {
				fwrite($pipes[0], $this->getKey());
				fclose($pipes[0]);
				proc_close($process);
				$this->keychain = file_get_contents($file);
			}
		} catch (Exception $e) {
		} /* finally { */
			unlink($file);
		/* } */
	}
	
	public function getFullKey() {

	}

	public function getSignatures() {

	}

	public function isSignedByTrustedParty() {

	}

	public function isRevoked() {
		
	}

	public function serialize() {
		return $this->pgpKey;
	}

	public function unserialize($serialized) {
		$this->pgpKey = $serialized;
	}

}
