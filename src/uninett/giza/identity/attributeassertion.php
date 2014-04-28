<?php namespace uninett\giza\identity;

use \DomainException;
use \LogicException;
use \Serializable;

use \uninett\giza\core\Image;
use \uninett\giza\core\LDIFSerializable;
use \uninett\giza\core\NoImage;
use \uninett\giza\core\PGPPublicKey;
use \uninett\giza\core\SSHPublicKey;

class AttributeAssertion extends LDIFSerializable {

	private $uid;
	private $displayNames;
	private $mails;
	private $images;
	private $pgpPublicKeys;
	private $sshPublicKeys;

	public function __construct($attributes = null) {
		if (is_null($attributes)) {
			return;
		} elseif (is_string($attributes)) {
			$this->unserialize($attributes);
		} elseif (is_array($attributes)) {
			$this->setAttributes($attributes);
		} else throw new LogicException('Illegal type for attributes; not string or array');
	}

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
						. htmlentities($name)
						. ' fails to keep this promise and returned '
						. htmlentities($testUid).'.'
					);
				}
				if ($assertion) {
					$assertions[$name] = $assertion;
				}
			}
		}
		return $assertions;
	}

	protected function getAttributes() {
		$images = [];
		foreach($this->images as $image) {
			if ($image) {
				$images[] = $image->getImageBytes();
			}
		}
		return [
			'uid' => [$this->uid],
			'displayName' => $this->displayNames,
			'mail' => $this->mails,
			'photo' => $images,
			'pGPKeys' => $this->pgpPublicKeys,
			'sSHKeys' => $this->sshPublicKeys,
		];
	}

	protected function setAttributes($attributes) {
		$this->uid = reset($attributes['uid']);
		if (isset($attributes['displayName'])) {
			$this->displayNames = $attributes['displayName'];
		} else {
			$this->displayNames = [];
		}
		if (isset($attributes['mail'])) {
			$this->mails = $attributes['mail'];
		} else {
			$this->mails = [];
		}
		$this->images = [];
		if (isset($attributes['photo'])) foreach($attributes['photo'] as $image) {
			if ($image instanceof Image) {
				$this->images[] = $image;
			} else {
				$this->images[] = Image::fromBytes($image);
			}
		}
		$this->pgpPublicKeys = [];
		if (isset($attributes['pGPKeys'])) foreach($attributes['pGPKeys'] as $pgpPublicKey) {
			if ($pgpPublicKey instanceof PGPPublicKey) {
				$this->pgpPublicKeys[] = $pgpPublicKey;
			} else {
				$this->pgpPublicKeys[] = new PGPPublicKey($pgpPublicKey);
			}
		}
		$this->sshPublicKeys = [];
		if (isset($attributes['sSHKeys'])) foreach($attributes['sSHKeys'] as $sshPublicKey) {
			if ($sshPublicKey instanceof SSHPublicKey) {
				$this->sshPublicKeys[] = $sshPublicKey;
			} else {
				$this->sshPublicKeys[] = new SSHPublicKey($sshPublicKey);
			}
		}
	}

	/**
	 * Get unique ID in this assertion
	 * @return string uid
	 */
	public function getUniqueId() {
		return $this->uid;
	}
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
		return $this->displayNames;
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
		return $this->mails;
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
		return $this->images;
	}
	/**
	 * Get the PGP public keys from this assertion
	 * @return GPGKey[] the PGP public keys
	 */
	public function getPGPPublicKeys() {
		return $this->pgpPublicKeys;
	}
	/**
	 * Get the SSH public keys from this assertion
	 * @return SSHKey[] SSH public keys
	 */
	public function getSSHPublicKeys() {
		return $this->sshPublicKeys;
	}

}
