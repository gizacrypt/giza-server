<?php namespace uninett\giza\secret\input;

use \uninett\giza\secret\Secret;

/**
 * Validator for reverting a secret.
 *
 * Reverting secret to a previous version.
 * This will set the payload of the secret to the same payload as an earlier
 * version.  The payload may need to be re-encrypted to compensate for
 * changes in the access attributes.
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */

class RevertInputValidator extends WriteInputValidator {

	public function validate() {
		parent::validate();

		/*
		 * If there is a way to check that the payload didn't change between
		 * $this->secret and $this->secret->getBasedOn(), do it here.
		 */
	}

}
