<?php namespace uninett\giza\identity\saml;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class SimpleSamlAttributeAssertion extends SimpleSamlIdentityAssertion {

	private $displayNames;
	private $mailAddresses;
	private $jpegPhotos;

	public function __construct($uid, array $displayNames, array $mailAddresses, array $jpegPhotos) {
		parent::__construct($uid);

		$this->displayNames = $displayNames;
		$this->mailAddresses = $mailAddresses;
		$this->jpegPhotos = $jpegPhotos;
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
