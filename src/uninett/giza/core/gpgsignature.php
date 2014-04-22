<?php namespace uninett\giza\core;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
final class GPGSignature {

	private $segments;

	public function __construct($segments) {
		$this->segments = $segments;
	}

	public function getPGPKey() {
		return new PGPPublicKey($this->segments[4]);
	}

	public function getTrustLevel() {
		// remove the "x" at the end
		// drop all but the two most significant bits
		return substr($this->segments[10], 0, -1) & 0x03;
	}

	public function getDate() {
		if ($this->date) {
			return $this->date;
		}
		$this->date = new DateTime('@'.$this->segments[5]);
		if (!$this->date) {
			throw new Exception('Illegal signature format');
		}
		return $this->getDate();
	}

}
