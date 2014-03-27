<?php namespace uninett\giza\core;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
interface Permission {

	/**
	 * Determine whether an action is allowed.
	 *
	 * @param string $action the action to check
	 * @return boolean whether the action is allowed
	 */
	function allows($action);

}
