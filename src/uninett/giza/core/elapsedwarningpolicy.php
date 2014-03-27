<?php namespace uninett\giza\core;

use \DateInterval;
use \DateTime;
use \InvalidArgumentException;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class ElapsedWarningPolicy extends WarningPolicy {

	/**
	 * @var int
	 */
	private $percent;

	public function __construct($percent) {
		$this->unserialize($percent);
	}

	/**
	 * @return DateTime
	 */
	public function getWarningDateTime(DateTime $expireDate, DateTime $now = NULL) {
		if (is_null($now)) {
			$now = new DateTime('now');
		}
		$expiryDuration = $expireDate->diff($now);
		$days = floor($expiryDuration->days * $this->percent/100);
		$warningDuration = new DateInterval('P'.$days.'D');
		return $now->add($warningDuration);
	}

	public function serialize() {
		return $this->percent;
	}

	public function unserialize($serialized) {
		if (!is_numeric($serialized)) {
			throw new InvalidArgumentException('Percent must be a number.');
		}
		$this->percent = (double)$serialized;
	}

}
