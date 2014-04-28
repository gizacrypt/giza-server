<?php namespace uninett\giza\identity\saml;

use \uninett\giza\core\Image;

use \uninett\giza\identity\AttributeAssertion;
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
		$attr = $this->as->getAttributes();
		foreach ($attributeAssertions as $assertion) {
			$uid = $assertion->getUniqueId();
			if ($uid === reset($attr[$this->uidAttr])) {
				return $this->getAuthenticationAssertion();
			}
		}
		return null;
	}

	public function getAuthenticationAssertion() {
		$this->as->requireAuth();
		$attr = $this->as->getAttributes();
		$images = [];
		if (isset($attr[$this->jpegPhotoAttr])) {
			$images = Image::fromBase64Array($attr[$this->jpegPhotoAttr], 'image/jpeg');
		}
		return new AttributeAssertion([
			'uid' => $attr[$this->uidAttr],
			'displayName' => $attr[$this->displayNameAttr],
			'mail' => $attr[$this->mailAttr],
			'photo' => $images
		]);
	}

}
