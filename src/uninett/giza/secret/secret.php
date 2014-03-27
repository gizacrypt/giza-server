<?php namespace uninett\giza\secret;

use \Serializable;

/**
 * Giza API for secrets.  A Giza secret is a GPG encrypted file,
 * with additional signed metadata.
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
interface Secret extends Serializable {

	/**
	 * @var string $secret UUID of secret
	 * @var boolean $allowOld Allow operations on this object,
	 *      even if it is an older version of the secret.
	 */
	function __construct($secret, $allowOld=false);

	/**
	 * @return string[] List of UUIDs of secrets
	 */
	function getRevisionList();

	/**
	 * Get the secret that replaced this secret.
	 *
	 * @return Secret
	 */
	function getNext();

	/**
	 * Get the secret that was the most recent one before this secret was created
	 *
	 * @return Secret
	 */
	function getPrevious();

	/**
	 * Get the secret this secret was based on.
	 *
	 * @return Secret
	 */
	function getBasedOn();

	/**
	 * @return Secret the newest update of this secret.
	 */
	function getLatest();

	/**
	 * Validates and updates the Giza file.
	 *
	 * @param string $newFile
	 *
	 * @return void
	 */
	function newRevision($newFile);

	/**
	 * Generate a Giza file with this action.
	 *
	 * @param string $action
	 *
	 * @return void
	 */
	function getFileForAction($action);

	/**
	 * Get the name of this version of the secret.
	 */
	function getName();

	/**
	 * Set a boolean value with a numeric index. The number must be a power of 2.
	 */
	/* protected function setFlag($flag, $value); */

	/**
	 * Get a boolean value with a numeric index. The number must be a power of 2.
	 */
	/* protected function getFlag($flag); */

	/**
	 * @return DateTime
	 */
	function getTimestamp();

	/**
	 * @return DateTime
	 */
	function getContentChangedTimestamp();

	/**
	 * Get the content type of this version of the secret.
	 *
	 * @return string
	 */
	function getContentType();

	/**
	 * Return the permissions for this secret.
	 *
	 * @return Permission[]
	 */
	function getPermissions();

	/**
	 * Returns expire object
	 *
	 * @return Expire
	 */
	function getExpire();

}
