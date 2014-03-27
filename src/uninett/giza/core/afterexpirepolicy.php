<?php namespace uninett\giza\core;

use \DateTime;
use \DateInterval;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class AfterExpirePolicy extends ExpirePolicy {

	/**
	 * @var DateInterval
	 */
	private $duration;

	/**
	 * @param DateInterval $duration
	 */
	public function __construct(WarningPolicy $warningPolicy, DateInterval $duration) {
		parent::__construct($warningPolicy);
		$this->unserialize($duration);
	}

	/**
	 * @return DateTime
	 */
	public function getExpireDateTime(DateTime $now = NULL) {
		if (is_null($now)) {
			$now = new DateTime('now');
		}
		return $now->add($this->duration);
	}

	public function serialize() {
		return sprintf("P%04d-%02d-%02d", $this->duration->y, $this->duration->m, $this->duration->d);
	}

	public function unserialize($serialized) {
		$this->duration = new DateInterval($serialized);
	}

}
