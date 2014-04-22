<?php namespace uninett\giza\core;

use \DateTime;
use \LogicException;
use \ReflectionClass;
use \Serializable;

/**
 * 
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
final class Expire implements Serializable {

	public function __construct($expirePolicy) {
		$this->expirePolicy = $expirePolicy;
		$this->warningDateTime = $expirePolicy->getWarningDateTime();
		$this->expireDateTime = $expirePolicy->getExpireDateTime();
	}

	/**
	 * @var ExpirePolicy
	 */
	public $expirePolicy;

	/**
	 * @var DateTime
	 */
	public $warningDateTime;

	/**
	 * @var DateTime
	 */
	public $expireDateTime;

	/**
	 * @return Expire
	 */
	public function getExpire(DateTime $now) {
		return new Expire($this);
	}

	public function serialize() {
		$decoded = [
			'expirePolicy' => [
				$this->expirePolicy->getName(), 
				$this->expirePolicy->serialize()
			],
			'warningPolicy' => [
				$this->expirePolicy->warningPolicy->getName(), 
				$this->expirePolicy->warningPolicy->serialize()
			],
			'warningDateTime' => $this->warningDateTime->format(DateTime::DATE_W3C), 
			'expireDateTime' => $this->expireDateTime->format(DateTime::DATE_W3C),
		];
		foreach($decoded as $key => $value) {
			if (is_null($value)) {
				throw new LogicException(htmlspecialchars($key).' has a NULL value.');
			}
		}
		return json_encode($decoded);
	}

	public function unserialize($serialized) {
		$decoded = json_decode($serialized);
		if (is_null($decoded)) {
			throw new InvalidArgumentException('Cannot decode JSON for expire.');
		}
		if (count($decoded->expirePolicy) != 2) {
			throw new InvalidArgumentException('Expire policy is supposed to be a name and value tuple.');
		}
		if (count($decoded->warningPolicy) != 2) {
			throw new InvalidArgumentException('Warning policy is supposed to be a name and value tuple.');
		}
		$expirePolicyClass = new ReflectionClass(__NAMESPACE__ . '\\' . reset($decoded->expirePolicy) . 'ExpirePolicy');
		$warningPolicyClass = new ReflectionClass(__NAMESPACE__ . '\\' . reset($decoded->warningPolicy) . 'WarningPolicy');
		$this->expirePolicy = $expirePolicyClass->newInstanceWithoutConstructor()->unserialize(end($decoded->expirePolicy));
		$this->warningPolicy = $warningPolicyClass->newInstanceWithoutConstructor()->unserialize(end($decoded->warningPolicy));
		$this->warningDateTime = new DateTime($decoded->warningDateTime);
		$this->expireDateTime = new DateTime($decoded->expireDateTime);
	}

}
