<?php namespace uninett\giza\secret\output;

use \DomainException;

use \uninett\giza\identity\Profile;
use \uninett\giza\secret\Secret;

/**
 * Generator for Giza files
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */

abstract class AbstractOutputGenerator {

	/**
	 * @var Secret secret used as the basis (Based-On)
	 */
	protected $secret;

	/**
	 * @var identity running the generator
	 */
	protected $identity;

	/**
	 * @var string action attribute in output
	 */
	protected $action;

	/**
	 * Instantiate a validator for a given action
	 *
	 * @param string[] $parameters parameters to be used for the generator, must at least contain "action"
	 * @param Profile $identity the identity running the generator
	 * @param Secret $secret secret to be passed to the constructor of the action
	 *
	 * @throws RuntimeException if the requested action is unknown
	 */
	public static function getGenerator($parameters, Secret $secret, Profile $identity = null) {
		$class = __NAMESPACE__ . '\\' . ucfirst(strtolower($parameters['action'])) . 'OutputGenerator';
		return new $class($parameters, $secret, $identity);
	}

	/**
	 * Construct a validator.
	 *
	 * @param string[] $parameters for the generator
	 * @param Secret $secret the to-be-checked secret
	 */
	public function __construct($parameters, Secret $secret, Profile $identity = null) {
		$this->secret = $secret;
		$this->identity = is_null($identity) ? Profile::fromStore() : $identity;
		$this->action = strtolower($parameters['action']);
	}

	/**
	 * Get parameters to be included in the output generator.
	 *
	 * @return string[] parameters
	 */
	public function getParameters() {
		return ['Action' => strtolower($this->action)];
	}

	/**
	 * Check if the generator is valid, with respect to state of the secret and
	 * permissions of the identity requesting the output.
	 * If the generator is not valid, this method will throw an exception.
	 *
	 * @return void generator is valid
	 *
	 * @throws RuntimeException generator is not valid
	 */
	public function validate() {
		$access = Secret::getRequiredPermissionsForAction($this->action);
		if (($this->secret->getPermissions($this->identity) & $access) < $access) {
			throw new DomainException('You are not allowed to ' . $this->action . ' secret ' . $this->secret->getUUID());
		}
	}

	/**
	 * Generate the giza command as it must be appended to the Giza file.
	 *
	 * @return string giza command, multi-line
	 */
	public function __toString() {
		$parameters = $this->getParameters();
		array_walk($parameters, function(&$item, $attr){$item = "${attr}: ${item}";});
		return implode("\n", [
			'-----BEGIN GIZA COMMAND-----',
			implode("\n", $parameters),
			'-----END GIZA COMMAND-----'
		]);

	}

	/**
	 * Generate the content and HTTP headers, and send them to the PHP output.
	 * This will halt execution.
	 *
	 * @return void will not return
	 */
	final public function generateOutput() {
		$this->validate();
		$output = trim($this->secret) . "\n" . trim($this) . "\n";
		header('Content-Description: File Transfer');
		header('Content-Type: application/x-giza');
		header('Content-Disposition: attachment; filename=' . $this->secret->getUUID() . '.giza');
		header('Cache-Control: no-cache');
		header('Content-Length: ' . strlen($output));
		die($output);
	}

}
