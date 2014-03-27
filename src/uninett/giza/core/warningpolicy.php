<?php namespace uninett\giza\core;

use \DateTime;
use \Serializable;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
abstract class WarningPolicy implements Serializable {

	/**
	 * @return DateTime
	 */
	public abstract function getWarningDateTime(DateTime $expireDate, DateTime $now = NULL);

	/**
	 * Get the name of the current instance.
	 * The name consists of the simple class name,
	 * with the "ExpirePolicy" part removed.
	 *
	 * @return string the name of the instance
	 */
	public function getName() {
		return substr(end(explode('\\', get_class($this))), 0-strlen(get_class()));
	}

}
