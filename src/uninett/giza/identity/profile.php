<?php namespace uninett\giza\identity;

use \LogicException;

use \uninett\giza\core\Image;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
final class Profile extends AttributeAssertion {

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

	protected $singleValueAttributes = ['uid', 'displayName', 'mail', 'photo'];

	/**
	 * Create a profile from the authenticated identity
	 *
	 * @return Profile
	 */
	public static function fromAuthentication() {
		$identities = AttributeAssertion::collect(null);
		return new Profile($identities);
	}
	/**
	 * Create a profile from a given UID
	 *
	 * @param string $uid
	 *
	 * @return Profile
	 */
	public static function fromUid($uid) {
		$identities = AttributeAssertion::collect($uid);
		return new Profile($identities);
	}
	/**
	 * Load profile from the profile store.
	 * This method will return NULL if the profile was not stored before
	 *
	 * This is a convenience function that calls the ProfileStore#getProfile(string) method.
	 *
	 * @todo Allow retrieval of older version
	 *
	 * @param string $uid uid or null for authenticated user
	 * @param string $revision TODO revision string or null for latest
	 *
	 * @return Profile Profile or NULL if not previously stored
	 */
	public static function fromStore($uid = null, $revision = null) {
		if (isset($revision)) {
			throw new LogicException('Retrieval of older profile not implemented.');
		}
		if (is_null($uid)) {
			$profile = Profile::fromAuthentication();
			if (is_null($profile)) {
				return null;
			}
			$uid = $profile->getUniqueId();
		}
		return $GLOBALS['gizaConfig']['profileStore']->getProfile($uid);
	}

	public static function getNewestProfiles() {
		return $GLOBALS['gizaConfig']['profileStore']->getNewestProfiles();
	}

	/**
	 * Store the profile in the profile store.
	 *
	 * This is a convenience function that calls the ProfileStore#store() method.
	 */
	public function store() {
		$GLOBALS['gizaConfig']['profileStore']->store($this);
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
			throw new LogicException('At least one attribute assertion is required to construct a profile.');
		}
		foreach($attributeAssertions as $assertion) {
			$uid = $assertion->getUniqueId();
			if (!isset($uid)) {
				throw new LogicException('Cannot construct a profile from an attribute assertion without UID.');
			}
			if (!isset($this->uid)) {
				$this->uid = $uid;
			}
			if ($this->uid !== $uid) {
				throw new LogicException('All attribute assertions used to construct a profile must have the same UID.');
			}
		}
		$this->attributeAssertions = $attributeAssertions;
		$this->setDisplayName(reset($this->getDisplayNames()));
		$this->setMail(reset($this->getMails()));
		$images = $this->getImages();
		if ($images) {
			$this->setImage(reset($images));
		}

	}

	public function getUniqueId() {
		return $this->uid;
	}
	
	/**
	 * Get the list of attribute assertions this profile is based on.
	 *
	 * @return AttributeAssertion[] list of attribute assertions.
	 */
	public function getAttributeAssertions() {
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

	public function getAttributes() {
		$elements = [
			'uid' => [$this->uid],
			'displayName' => [$this->displayName],
			'mail' => [$this->mail],
			'photo' => isset($this->image) ? [$this->image->getImageBytes()] : [],
			'assertion' => [],
		];
		foreach($this->attributeAssertions as $assertion) {
			$elements['assertion'][] = $assertion->serialize();
		}
		return $elements;
	}

	public function setAttributes($attributes) {
		$this->uid = reset($attributes['uid']);
		$this->displayNames = $attributes['displayName'];
		$this->mails = $attributes['mail'];
		if (isset($attributes['photo'])) foreach($attributes['photo'] as $image) {
			$this->images = Image::fromBytes($image);
		} else {
			$this->images = [];
		}
		foreach($attributes['assertion'] as $assertionSerialized) {
			$this->attributeAssertions[] = new AttributeAssertion($assertionSerialized);
		}
	}

}
