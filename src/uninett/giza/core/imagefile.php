<?php namespace uninett\giza\core;

use \FInfo;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class ImageFile extends Image {

	private $file;

	/**
	 * Create Image object from file.
	 * 
	 * @param string $imageData path to image file.
	 */
	public function __construct($file) {
		$this->file = $file;
	}

	public function viewImage() {
		header('Content-type: '.$this->getImageContentType());
		readfile($this->file);
	}
	public function getImageBytes() {
		return file_get_contents($this->getImageFile());
	}
	public function getImageFile() {
		return $this->file;
	}
	public function getImageContentType() {
		$finfo = new FInfo(FILEINFO_MIME);
		return $finfo->file($this->getImageFile());
	}

	public function toBytes() {
		return new ImageBytes($this->getImageBytes(), $this->getImageContentType());
	}

	public function serialize() {
		return http_build_url([
			'scheme' => 'file',
			'path' => $this->file,
		]);
	}
	public function unserialize($serialized) {
		$parsed = parse_url($serialized);
		$this->file = $parsed['path'];
	}

}
