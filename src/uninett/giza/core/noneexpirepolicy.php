<?php namespace uninett\giza\core;

use \DateTime;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class NoneExpirePolicy extends ExpirePolicy {

	public function __construct() {
		parent::__construct(NULL);
	}

	/**
	 * @return DateTime
	 */
	public function getExpireDateTime(DateTime $now = NULL) {
		return NULL;
	}

	/**
	 * @return DateTime
	 */
	public function getWarningDateTime(DateTime $now = NULL) {
		return NULL;
	}

	public function serialize() {}

	public function unserialize($serialized) {}

}
