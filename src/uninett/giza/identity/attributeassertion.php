<?php namespace uninett\giza\identity;

use \DomainException;
use \Serializable;

use \uninett\giza\core\Image;
use \uninett\giza\core\NoImage;

abstract class AttributeAssertion {

	public function __construct() {}

	/**
	 * Collect different assertion objects for a uid.
	 * 
	 * @param $uid string|null
	 *
	 * @return AttributeAssertion[]
	 */
	public static function collect($uid = null) {
		$assertions = [];
		if (is_null($uid)) {
			$identityAssertion = $GLOBALS['gizaConfig']['authenticationSource']->getAuthenticationAssertion();
			$uid = $identityAssertion->getUniqueId();
			if (!$uid || !$identityAssertion) {
				return [];
			}
		}
		foreach($GLOBALS['gizaConfig']['auxiliaryAttributeSources'] as $name => $source) {
			$assertion = $source->getAssertionFor([$identityAssertion]);
			if (isset($assertion)) {
				$testUid = $assertion->getUniqueId();
				if ($testUid !== $uid) {
					throw new DomainException(
						'Unique ID must be consistent over all assertions. '
						.htmlentities($name)
						.' fails to keep this promise and returned '
						.htmlentities($testUid).'.'
					);
				}
				if ($assertion) {
					$assertions[$name] = $assertion;
				}
			}
		}
		return $assertions;
	}

	/**
	 * Get unique ID in this assertion
	 * @return string uid
	 */
	abstract public function getUniqueId();
	/**
	 * Get display name for this assertion
	 * @return string
	 */
	public function getDisplayName() {
		if ($this->getDisplayNames()) {
			return reset($this->getDisplayNames());
		} else {
			return strstr($this->getUniqueId(), '@', true);
		}
	}
	/**
	 * Get display names for this assertion
	 * @return string[] display names
	 */
	public function getDisplayNames() {
		return [];
	}
	/**
	 * Get e-mail address from this assertion
	 * @return string[] e-mail address
	 */
	public function getMail() {
		if ($this->getMails()) {
			return reset($this->getMails());
		} else {
			return null;
		}
	}
	/**
	 * Get e-mail addresses from this assertion
	 * @return string[] e-mail addresses
	 */
	public function getMails() {
		return [];
	}

	/**
	 * Get image from this assertion
	 * @return Image image
	 */
	public function getImage() {
		$images = $this->getImages();
		if ($images) {
			return reset($images);
		} else {
			return new NoImage();
		}
	}
	/**
	 * Get images from this assertion
	 * @return Image[] images
	 */
	public function getImages() {
		return [];
	}
	/**
	 * Get the PGP public keys from this assertion
	 * @return GPGKey[] the PGP public keys
	 */
	public function getPGPPublicKeys() {
		return [];
	}
	/**
	 * Get the SSH public keys from this assertion
	 * @return SSHKey[] SSH public keys
	 */
	public function getSSHPublicKeys() {
		return [];
	}

}
