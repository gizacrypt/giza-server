<?php namespace uninett\giza\identity\saml;

use \uninett\giza\core\Image;

use \uninett\giza\identity\AttributeSource;

use \SimpleSAML_Auth_Simple;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class SimpleSamlAttributeSource implements AttributeSource {

	private $as;

	private $uidAttr;
	private $displayNameAttr;
	private $mailAttr;
	private $jpegPhotoAttr;

	public function __construct($settings) {
		require_once $settings['sspRoot'] . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . '_autoload.php';
		$this->as = new SimpleSAML_Auth_Simple($settings['authSource']);
		$this->uidAttr = $settings['uidAttr'];
		$this->displayNameAttr = $settings['displayNameAttr'];
		$this->mailAttr = $settings['mailAttr'];
		$this->jpegPhotoAttr = $settings['jpegPhotoAttr'];
	}

	public function getAssertionFor(array $attributeAssertions) {
		if ($attributeAssertions) {
			$uid = reset($attributeAssertions)->getUniqueId();
			$attr = $this->as->getAttributes();
			if ($uid === reset($attr[$this->uidAttr])) {
				$images = [];
				if (isset($attr[$this->jpegPhotoAttr])) {
					$images = Image::fromBytesArray($attr[$this->jpegPhotoAttr]);
				}
				return new SimpleSamlAttributeAssertion(
					reset($attr[$this->uidAttr]),
					$attr[$this->displayNameAttr],
					$attr[$this->mailAttr],
					$images
				);
			}
		}
		return null;
	}

	public function getAuthenticationAssertion() {
		$this->as->requireAuth();
		$attr = $this->as->getAttributes();
		return new SimpleSamlIdentityAssertion(
			reset($attr[$this->uidAttr])
		);
	}

}
