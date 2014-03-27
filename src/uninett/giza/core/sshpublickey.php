<?php namespace uninett\giza\core;

use \InvalidArgumentException;
use \Serializable;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class SSHPublicKey implements Serializable {

	const AUTHKEYS_FORMAT_REGEX = '_((?<options>(\S+|"[^"]*")+)\s+)?(?<encType>ssh-\w+)\s+(?<pubKey>AAAA[A-Za-z0-9/\+]+={0,2})(\s+(?<comment>.*))?_';

	/** Comment of the key */
	private $comment;
	/** Base64 encoded partition of the key; the actual key value */
	private $pubKey;
	/** Encryption type */
	private $encType;
	/** Key options */
	private $options;
	/** Fingerprint (lazy loaded) */
	private $fingerprint;
	/** Bubble-babble formatted fingerprint (lazy loaded) */
	private $bbFingerprint;
	/** Size of the key */
	private $keySize;

	public function __construct($sshKey) {
		return $this->unserialize($sshKey);
	}

	private function lazyLoad($bubbleBabble = false) {
		if ($bubbleBabble) {
			$this->lazyLoad(false);
		}
		if ((!$bubbleBabble && isset($this->fingerprint))
			|| ($bubbleBabble && isset($this->bbFingerprint))
		) {
			return;
		}

		$file = tempnam(sys_get_temp_dir(), 'ssh');
		if (!$file) {
			return;
		}
		try {
			$handle = fopen($file, 'w');
			fwrite($handle, $this->getAuthorizedKeysEntry() . "\n");
			fclose($handle);

			$descriptorspec = [1 => ['pipe', 'w']];

			$process = proc_open(
				escapeshellcmd('ssh-keygen')
				. ($bubbleBabble ? ' -B' : ' -v') . ' -l -f '
				. escapeshellarg($file), 
				$descriptorspec, 
				$pipes, 
				sys_get_temp_dir(), 
				[]
			);
			if (is_resource($process)) {
				$output = preg_split('/\s/', stream_get_contents($pipes[1]));
				$this->keySize = (int)reset($output);
				if ($bubbleBabble) {
					$this->bbFingerprint = next($output);
				} else {
					$this->fingerprint = hex2bin(str_replace(':', '', next($output)));
				}
				fclose($pipes[1]);
				proc_close($process);
			}
		} catch (Exception $e) {
		} /* finally { */
			unlink($file);
		/* } */
	}

	public function getComment() {
		return $this->comment;
	}

	public function getEncryptionType() {
		return $this->encType;
	}

	public function getPublicKey() {
		return $this->pubKey;
	}

	public function getFingerprint() {
		$this->lazyLoad();
		return $this->fingerprint;
	}

	public function getHexFingerprint() {
		return implode(':', str_split(bin2hex($this->getFingerprint()), 2));
	}

	public function getBubbleBabbleFingerprint() {
		$this->lazyLoad(true);
		return $this->bbFingerprint;
	}

	public function getKeySize() {
		$this->lazyLoad();
		return $this->keySize();
	}

	public function getOptions() {
		return $this->options;
	}

	public function getAuthorizedKeysEntry() {
		return rtrim(ltrim(implode(',', $this->getOptions()) . ' ')
				. $this->getEncryptionType() . ' '
				. base64_encode($this->getPublicKey()) . ' '
				. $this->getComment()
			);
	}

	public function serialize() {
		return $this->getAuthorizedKeysEntry();
	}

	public function unserialize($sshKey) {
		if (preg_match(self::AUTHKEYS_FORMAT_REGEX, $sshKey, $segments)) {
			if (isset($segments['options']) && $segments['options']) {
				$this->options = explode(',',$segments['options']);
			} else {
				$this->options = [];
			}
			$this->encType = $segments['encType'];
			$this->pubKey = base64_decode($segments['pubKey']);
			if (isset($segments['comment'])) {
				$this->comment = $segments['comment'];
			}
		} else {
			throw new InvalidArgumentException('SSH key is not in a recognised format.');
		}
	}

}
