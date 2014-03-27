<?php namespace uninett\giza\core;

use \DateTime;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class AtExpirePolicy extends ExpirePolicy {

	/**
	 * @var DateInterval
	 */
	private $date;

	/**
	 * @param DateTime $date
	 */
	public function __construct(WarningPolicy $warningPolicy, DateTime $date) {
		parent::__construct($warningPolicy);
		$this->unserialize($date);
	}

	/**
	 * @return DateTime
	 */
	public function getExpireDateTime(DateTime $now = NULL) {
		return $this->date;
	}

	public function serialize() {
		return $this->date->format(DateTime::DATE_W3C);
	}

	public function unserialize($date) {
		if (is_string($date)) {
			$date = new DateTime($date);
		}
		$this->date = $date;
	}

}
