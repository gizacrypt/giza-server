<?php namespace uninett\giza\core;

use \DateInterval;
use \DateTime;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class RemainingWarningPolicy extends WarningPolicy {

	/**
	 * @var DateInterval
	 */
	protected $interval;

	/**
	 * @param DateInterval $interval
	 */
	public function __construct(DateInterval $interval) {
		$this->interval = $interval;
	}

	/**
	 * @return DateTime
	 */
	public function getWarningDateTime(DateTime $expireDate, DateTime $now = NULL) {
		return $expireDate->sub($this->interval);
	}

	public function serialize() {
		return sprintf("P%04d-%02d-%02d", $this->interval->y, $this->interval->m, $this->interval->d);
	}

	public function unserialize($serialized) {
		$this->interval = new DateInterval($serialized);
	}

}
