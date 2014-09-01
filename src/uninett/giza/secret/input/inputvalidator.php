<?php namespace uninett\giza\secret\input;

/**
 * InputValidators will validate incoming secrets before they are written to
 * permanent storage.  The validator can throw an exception, which will prevent
 * the secret from being stored.
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */

interface InputValidator {

	/**
	 * Check if the action for this secret is valid, with respect to allowed
	 * fields, and fields changed compared to the previous version and the
	 * based-on version of this secret.  If the action is not valid,
	 * this method will throw an exception and the new secret should not be
	 * stored.
	 *
	 * @return void action is valid
	 *
	 * @throws RuntimeException action is not valid
	 */
	function validate();

	/**
	 * Get the name of the action this validator will validate.
	 *
	 * @return string the name of the action
	 */
	function getAction();

}
