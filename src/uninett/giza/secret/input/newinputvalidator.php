<?php namespace uninett\giza\secret\input;

/**
 * Validator for a "new" secret.
 *
 * A new secret will not have a history yet, so "previous" and "based-on"
 * must be null.  Additionally, the uploader must ensure to have full admin access.
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */

class NewInputValidator extends AbstractInputValidator {

	/**
	 * Construct a validator for the "new" action.
	 *
	 * @param Secret $secret the to-be-checked secret
	 */
	function __construct(Secret $secret) {
		parent::__construct($secret);
	}

	public function validate() {
		/*
		 * do not call parent method, it will check permissions and versioning,
		 * which we don't have yet.
		 */

		if (!is_null($this->secret->getMetadata()->getPrevious()) || !is_null($this->secret->getMetadata()->getBasedOn())) {
			throw new DomainException('A new secret cannot be based on another secret.');
		}

		$uploader = Profile::getActiveFromKey($this->secret->getSigningKey());
		if (($this->secret->getPermissions($uploader) & self::ACCESS_FULL) < self::ACCESS_FULL) {
			throw new DomainException('A new secret must have the uploader as admin.');
		}
	}

}
