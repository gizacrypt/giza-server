<?php namespace uninett\giza\core;

use \DateTime;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class NoneWarningPolicy extends WarningPolicy {

	/**
	 * @return DateTime
	 */
	public function getWarningDateTime(DateTime $expireDate, DateTime $now = NULL) {
		return NULL;
	}

	public function serialize() {}

	public function unserialize($serialized) {}

}
