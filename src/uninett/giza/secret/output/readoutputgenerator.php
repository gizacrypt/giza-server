<?php namespace uninett\giza\secret\output;

use \uninett\giza\identity\Profile;
use \uninett\giza\secret\Secret;

/**
 * Read output generator, generates Giza files meant for reading
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */

class ReadOutputGenerator extends AbstractOutputGenerator {

	/**
	 * @var string method how the secret is represented by the client application
	 */
	protected $method;

	/**
	 * Construct a validator.
	 *
	 * @param string[] $parameters for the generator
	 * @param Secret $secret the to-be-checked secret
	 */
	public function __construct($parameters, Secret $secret, Profile $identity = null) {
		parent::__construct($parameters, $secret, $identity);
		$this->method = $parameters['method'];
	}

	/**
	 * Get parameters to be included in the output generator.
	 *
	 * @return string[] parameters
	 */
	public function getParameters() {
		return array_merge(parent::getParameters(), ['Method' => strtolower($this->method)]);
	}

	/**
	 * Get the method of the output, this influences how the secret is
	 * presented to the end user by the client application.
	 *
	 * @return string the method
	 */
	public function getMethod() {
		return $this->method;
	}

}
