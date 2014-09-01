<?php namespace uninett\giza\secret\input;

/**
 * Validator for deleting a secret.
 *
 * Deletion will never actually delete secrets from the server,
 *  but will set the newest secret to a "deleted" secret.
 *
 * Deleted secrets will have no payload and metadata which is equal to the previous version.
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */

class DeleteInputValidator extends AbstractInputValidator {

	/**
	 * Construct a validator for the "delete" action.
	 *
	 * @param Secret $secret the to-be-checked secret
	 */
	function __construct(Secret $secret) {
		parent::__construct($secret);
	}

	public function validate() {
		parent::validate();

		if ($this->secret->getEncryptedPayload()) {
			throw new DomainException('Deleted secrets cannot contain payload.');
		}

		if (!$this->secret->getMetadata()->compareAccessAttributes($this->secret->getPrevious()->getMetadata())) {
			throw new DomainException('Metadata rights cannot be modified through this action type.');
		}
	}

}
