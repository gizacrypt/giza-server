<?php namespace uninett\giza\core;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class NoImage extends ImageFile {

	public function __construct() {
		parent::__construct($GLOBALS['gizaConfig']['standardImage']);
	}

	public final function viewImage() {
		header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found', 404);
		parent::viewImage();
	}

}
