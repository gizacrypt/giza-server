<?php namespace uninett\giza\secret\output;

use \uninett\giza\identity\Profile;
use \uninett\giza\secret\Secret;

/**
 * Write output generator, generates Giza files meant for writing.
 * Written Giza files are returned to the script and handled by an InputValidator.
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */

class WriteOutputGenerator extends AbstractOutputGenerator {

	/**
	 * Get the URL that the shell script should use to send updated secrets.
	 *
	 * @return string the URL
	 */
	public static function getCallbackURL() {
		return (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? 'http' : 'https')
			. '://'
			. $_SERVER['HTTP_HOST']
			. dirname($_SERVER['SCRIPT_NAME'])
			;
	}

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
		$parameters = parent::getParameters();
		$next = $this->secret->getNext();
		if (!is_null($next)) {
			$parameters['Latest'] = $next->getUUID();
		}
		return array_merge($parameters, [
			'Method' => strtolower($this->method),
			'Callback-URL' => static::getCallbackURL(),
		]);
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
