<?php namespace uninett\giza\secret\input;

/**
 * Default implementation for validator.
 * It will run common checks (permissions, common ancestor) and can be extended
 * for more precise checks.
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */

abstract class AbstractInputValidator implements InputValidator {

	/**
	 * @var Secret newly uploaded, not yet stored, to-be-checked, secret
	 */
	protected $secret;

	/**
	 * Instantiate a validator for a given action
	 *
	 * @param string $actionName name of the action
	 * @param Secret $secret secret to be passed to the constructor of the action
	 *
	 * @throws RuntimeException if the requested action is unknown
	 */
	public static function getValidator($actionName, Secret $secret) {
		$class = __NAMESPACE__ . '\\' . ucfirst(strtolower($actionName)) . 'InputValidator';
		return new $class($secret);
	}

	/**
	 * Construct a validator.
	 *
	 * @param Secret $secret the to-be-checked secret
	 */
	public function __construct(Secret $secret) {
		$this->secret = $secret;
	}

	public function validate() {
		$uploader = Profile::getActiveFromKey($this->secret->getSigningKey());
		$required = Secret::getRequiredPermissionsForAction($this->getAction());
		if (($previous->getPermissions($uploader) & $required) < $required) {
			throw new DomainException('Insufficient permissions.');
		}

		$previous = $this->secret->getPrevious();
		$basedOn = $this->secret->getBasedOn();
		if (!$previous->hasCommonAncestor($basedOn)) {
			throw new DomainException('Previous and based-on secret must have common ancestor.');
		}
	}

	/**
	 * Get the name of the action this validator will validate.
	 *
	 * @return string the name of the action
	 */
	function getAction() {
		/*                 strlen('InputValidator') == 14 */
		return strtolower(substr(get_class($this), 0, -14));
	}

}
