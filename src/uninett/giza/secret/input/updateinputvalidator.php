<?php namespace uninett\giza\secret\input;

use \DomainException;
use \uninett\giza\secret\Secret;

/**
 * Action for updating a secret.
 *
 * Updating simply means to re-encrypt the content so that it can be read
 * by everybody with read access.
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */

class UpdateInputValidator extends WriteInputValidator {

	/**
	 * Construct a validator for the "update" action.
	 *
	 * @param Secret $secret the to-be-checked secret
	 */
	function __construct(Secret $secret) {
		parent::__construct($secret);
	}

	public function validate() {
		parent::validate();

		if ($this->secret->getBasedOn() != $this->secret->getPrevious()) {
			throw new DomainException('Update can only be done on the latest revision.');
		}

		/*
		 * If there is a way to check who the payload is encrypted for,
		 * do it here; it must match the Access attribute in the metadata.
		 */

		/*
		 * If there is a way to check that the payload didn't change between
		 * $this->secret and $this->secret->getBasedOn(), do it here.
		 */
	}

}
