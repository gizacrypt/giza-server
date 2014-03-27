<?php namespace uninett\giza\core;

use \DateTime;
use \Serializable;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
abstract class ExpirePolicy implements Serializable {

	/**
	 * @var WarningPolicy
	 */
	public $warningPolicy;

	public function __construct(WarningPolicy $warningPolicy) {
		$this->warningPolicy = $warningPolicy;
	}

	/**
	 * @return DateTime
	 */
	public abstract function getExpireDateTime(DateTime $now = NULL);

	/**
	 * @return DateTime
	 */
	public function getWarningDateTime(DateTime $now = NULL) {
		return $this->warningPolicy->getWarningDateTime($this, $now);
	}

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
