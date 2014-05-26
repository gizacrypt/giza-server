<?php namespace uninett\giza\core;

use \uninett\giza\identity\Profile;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class PopulatedGPG extends GPG {

	public function __construct() {
		parent::__construct();
		foreach(Profile::getActiveProfiles() as $profile) {
			foreach($profile->getPGPPublicKeys() as $key) {
				$this->importKey($key);
			}
		}
	}

}
