<?php namespace uninett\giza\secret\input;

use \uninett\giza\secret\Secret;

/**
 * Action for writing new content to a secret.
 *
 * The secret will have the same metadata as the previous version,
 * but it will have a different payload.
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */

class WriteInputValidator extends AbstractInputValidator {

	/**
	 * Construct a validator for the "write" action.
	 *
	 * @param Secret $secret the to-be-checked secret
	 */
	function __construct(Secret $secret) {
		parent::__construct($secret);
	}

	public function validate() {
		parent::validate();

		/*
		 * Metadata must be the same between $this->secret and $this->secret->getPrevious(),
		 * except for the Content-Type, which must be the same between $this->secret and $this->secret->getBasedOn(),
		 */
		if (!$this->secret->getMetadata()->compareAccessAttributes($this->secret->getPrevious()->getMetadata())) {
			throw new DomainException('Access rights cannot be modified through action' . $this->getAction() . '.');
		}
	}

}
