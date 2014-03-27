<?php namespace uninett\giza\identity\saml;

use \uninett\giza\identity\AttributeAssertion;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class SimpleSamlIdentityAssertion extends AttributeAssertion {

	private $uid;
	
	public function __construct($uid) {
		assert('is_string($uid);');

		$this->uid = $uid;
	}

	public function getUniqueId() {
		return $this->uid;
	}

}
