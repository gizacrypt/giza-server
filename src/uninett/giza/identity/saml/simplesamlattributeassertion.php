<?php namespace uninett\giza\identity\saml;

use \uninett\giza\identity\AttributeAssertion;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class SimpleSamlAttributeAssertion extends AttributeAssertion {

	private $uid;
	private $displayNames;
	private $mailAddresses;
	private $jpegPhotos;

	public function __construct($uid, array $displayNames, array $mailAddresses, array $jpegPhotos) {
		assert('is_string($uid);');
		$this->uid = $uid;

		$this->displayNames = $displayNames;
		$this->mailAddresses = $mailAddresses;
		$this->jpegPhotos = $jpegPhotos;
	}

	public function getUniqueId() {
		return $this->uid;
	}
	
	public function getDisplayNames() {
		return $this->displayNames;
	}

	public function getMailAddresses() {
		return $this->mailAddresses;
	}

	public function getImages() {
		return $this->jpegPhotos;
	}


}
