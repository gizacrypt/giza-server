<?php namespace uninett\giza\identity;

use \DomainException;

use \uninett\giza\core\Image;

class Profile extends AttributeAssertion {

	/**
	 * @var string
	 */
	protected $uid;
	/**
	 * @var string
	 */
	protected $displayName;
	/**
	 * @var string
	 */
	protected $mail;
	/**
	 * @var Image
	 */
	protected $image;
	/**
	 * @var AttributeAssertion[]
	 */
	protected $attributeAssertions;

	/**
	 * Create a profile from a give UID
	 *
	 * @param string $uid
	 *
	 * @return Profile
	 */
	public static function fromUid($uid = null) {
		$identities = AttributeAssertion::collect($uid);
		$profile = new Profile($identities);
		$profile->setDisplayName(reset($profile->getDisplayNames()));
		$profile->setMail(reset($profile->getMails()));
		$images = $profile->getImages();
		if ($images) {
			$profile->setImage(reset($images));
		}
		return $profile;
	}

	/**
	 * Construct a new profile
	 *
	 * @param AttributeAssertion[] $AttributeAssertions All attribute assertions contained in this profile.
	 *
	 * @return void
	 */
	public function __construct(array $attributeAssertions) {
		if (!$attributeAssertions) {
			throw new DomainException('At least one attribute assertion is required to construct a profile.');
		}
		foreach($attributeAssertions as $assertion) {
			$uid = $assertion->getUniqueId();
			if (!isset($uid)) {
				throw new DomainException('Cannot construct a profile from an attribute assertion without UID.');
			}
			if (!isset($this->uid)) {
				$this->uid = $uid;
			}
			if ($this->uid !== $uid) {
				throw new DomainException('All attribute assertions used to construct a profile must have the same UID.');
			}
		}
		$this->attributeAssertions = $attributeAssertions;
	}

	public function getUniqueId() {
		return $this->uid;
	}
	
	/**
	 * Get the list of attribute assertions this profile is based on.
	 *
	 * @return AttributeAssertion[] list of attribute assertions.
	 */
	protected function getAttributeAssertions() {
		return $this->attributeAssertions;
	}

	protected function collectFieldValues($field, $start = []) {
		$getter = 'get'.$field;
		$result = $start;
		foreach($this->getAttributeAssertions() as $assertion) {
			$result = array_merge($result, $assertion->$getter());
		}
		return array_unique($result);
	}

	/**
	 * Set the active display name.
	 * @param string $displayName the new display name.
	 *
	 * @return void
	 */
	public function setDisplayName($displayName) {
		$this->displayName = $displayName;
	}
	public function getDisplayName() {
		return $this->displayName;
	}
	public function getDisplayNames() {
		return $this->collectFieldValues(
			'DisplayNames',
			$this->displayName
				? [$this->displayName]
				: []
		);
	}
	/**
	 * Set the active e-mail address.
	 * @param string $mail the new e-mail address.
	 *
	 * @return void
	 */
	public function setMail($mail) {
		$this->mail = $mail;
	}
	public function getMail() {
		return $this->mail;
	}
	public function getMails() {
		return $this->collectFieldValues('Mails',
			$this->mail
				? [$this->mail]
				: []
		);
	}
	/**
	 * Set the active image.
	 * @param Image $image the new image.
	 *
	 * @return void
	 */
	public function setImage(Image $image) {
		$this->image = $image;
	}
	public function getImage() {
		return $this->image
			? $this->image
			: new NoImage()
			;
	}
	public function getImages() {
		return $this->collectFieldValues('Images',
			$this->image
				? [$this->image]
				: []
		);
	}

	public function getPGPPublicKeys() {
		return $this->collectFieldValues('PGPPublicKeys');
	}
	public function getSSHPublicKeys() {
		return $this->collectFieldValues('SSHPublicKeys');
	}

}
