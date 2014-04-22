<?php namespace uninett\giza\core;

use \FInfo;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class ImageBytes extends Image {

	private $bytes;

	private $contentType;
	
	/**
	 * Create Image object from bytes.
	 * 
	 * @param string $imageData raw image data.
	 */
	public function __construct($bytes, $contentType = null) {
		$this->bytes = $bytes;
		$this->contentType = $contentType;
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
		if (isset($this->contentType)) {
			return $this->contentType;
		}
		$finfo = new FInfo(FILEINFO_MIME);
		return $this->contentType = $finfo->buffer($this->getImageBytes());
	}

	public function toBytes() {
		return $this;
	}

	public function serialize() {
		if (!preg_match(
			'_(?<contentType>[a-z0-9][a-z0-9!#$&\\-\\^\\_\\.\\+]*/[a-z0-9][a-z0-9!#$&\\-\\^\\_\\.\\+]*)_',
			$this->getImageContentType(), $matches)) {
			throw new DomainException('Illegal characters in content-type.');
		}
		$contentType = $matches['contentType'];
		return 'data:'.$contentType.';base64,'.base64_encode($this->getImageBytes());
	}
	public function unserialize($serialized) {
		if (preg_match('_data:(?<contentType>[a-z]+);base64,(?<base64>[A-Za-z0-9/\+]+)_i', $serialized, $matches)) {
			$this->contentType = $matches['contentType'];
			$this->bytes = base64_decode($matches['base64']);
		} else {
			throw new DomainException('Illegal serialized image bytes.');
		}
	}

}
