<?php namespace uninett\giza\secret\input;

use \uninett\giza\secret\Secret;

/**
 * Validator for changing metadata.
 *
 * Change of metadata implies that the payload is not changed,
 * however for access changes the payload has to be encrypted again.
 * Since the server will never be able to decrypt the content,
 * we can only trust that the newly encrypted payload is equal to the old payload.
 *
 * Somebody changing the permissions MUST ensure that he has access rights in
 * in the resulting secret.
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */

class MetaInputValidator extends AbstractInputValidator {

	/**
	 * Construct a validator for the "meta" action.
	 *
	 * @param Secret $secret the to-be-checked secret
	 */
	function __construct(Secret $secret) {
		parent::__construct($secret);
	}

	public function validate() {
		parent::validate();

		if ($this->secret->getMetadata()->compareContentAttributes($this->secret->getPrevious()->getMetadata())) {
			throw new DomainException('It is not allowed to change the payload when changing the metadata.');
		}

		$uploader = Profile::getActiveFromKey($this->secret->getSigningKey());
		if (($this->secret->getPermissions($uploader) & self::ACCESS_ADMIN) < self::ACCESS_ADMIN) {
			throw new DomainException('On access change, the person making the change must be admin in the resulting secret.');
		}

		/*
		 * If there is a way to check that the payload didn't change between
		 * $this->secret and $this->secret->getBasedOn(), do it here.
		 */
	}

}
