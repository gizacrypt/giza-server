<?php namespace uninett\giza\secret;

use \Serializable;

/**
 * Giza API for secrets.  A Giza secret is a GPG encrypted file,
 * with additional signed metadata.
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
interface SecretStore {

	/**
	 * Get a secret with a give UUID
	 *
	 * @param string $uuid
	 *
	 * @return Secret
	 */
	function getSecret($uuid);

	/**
	 * Return the secret that replaces the given secret
	 *
	 * @param Secret|string $secret Secret object or UUID of secret
	 *
	 * @return Secret the secret that replaces the given secret, null if the given secret has not been supersceded
	 */
	function getNextSecret($secret);

	/**
	 * Return the name of a secret
	 *
	 * @param Secret|string $secret Secret object or UUID of secret
	 *
	 * @return string name of the secret, or null if secret does not exist
	 * 	if secret exists, but no name is found, the UUID is returned
	 */
	function getName($secret);

	/**
	 * Get all secrets which do not have a next version
	 *
	 * @param Profile $profile
	 *
	 * @return Secret[]
	 */
	function getNewestSecrets();

	/**
	 * Validates and updates the Giza file.
	 *
	 * @param string $newFile
	 *
	 * @return void
	 */
	function newSecret($contents);

	/**
	 * Get the URL that the shell script should use to send updated secrets.
	 *
	 * @return string the URL
	 */
	function getCallbackURL();

}
