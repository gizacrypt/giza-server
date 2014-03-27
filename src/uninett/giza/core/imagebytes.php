<?php namespace uninett\giza\core;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class ImageBytes extends Image {

	private $bytes;

	/**
	 * Create Image object from bytes.
	 * 
	 * @param string $imageData raw image data.
	 */
	public function __construct($bytes) {
		$this->bytes = $bytes;
	}

	public function viewImage() {
		header('Content-type: '.$this->getImageContentType());
		echo $this->getImageBytes();
	}
	public function getImageBytes() {
		return $this->bytes;
	}
	public function getImageFile() {
		$file = tempnam(sys_get_temp_dir(), 'img');
		$handle = fopen($file, 'w');
		fwrite($handle, $this->getImageBytes());
		fclose($handle);
		return $file;
	}
	public function getImageContentType() {
		$finfo = new finfo(FILEINFO_MIME);
		return $finfo->buffer($this->getImageBytes());
	}

}
