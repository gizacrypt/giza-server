<?php namespace uninett\giza\core;

use \uninett\giza\Giza;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class NoImage extends ImageFile {

	public function __construct() {
		parent::__construct(Giza::getInstance()->getStandardIdentityImage());
	}

	public final function viewImage() {
		header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found', 404);
		parent::viewImage();
	}

	public function serialize() {
		return '';
	}

	public function unserialize($serialized) {
		$this->__construct();
	}

}
