<?php namespace uninett\giza\core;

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
		readfile($file);
	}
	public function getImageBytes() {
		return file_get_contents($this->getImageFile());
	}
	public function getImageFile() {
		return $this->file;
	}
	public function getImageContentType() {
		$finfo = new finfo(FILEINFO_MIME);
		return $finfo->file($this->getImageFile());
	}

	/**
	 * Extract the image and return it as ImageBytes object.
	 * This is useful for serialising, as the source image could be moved after serialisation,
	 * making the unserialize method in this class unreliable.
	 */
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
