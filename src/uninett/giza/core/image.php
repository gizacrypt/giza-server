<?php namespace uninett\giza\core;

use \Serializable;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
abstract class Image implements Serializable {

	/**
	 * Create Image object from bytes.
	 * 
	 * @param string $imageData raw image data.
	 * @param string $contentType override content-type detection.
	 *
	 * @return ImageBytes	the image object.
	 */
	public static function fromBytes($bytes, $contentType = null) {
		return new ImageBytes($bytes, $contentType);
	}

	/**
	 * Create Image object from a base64 encoded string.
	 * 
	 * @param string $imageData raw image data encoded in base64.
	 * @param string $contentType override content-type detection.
	 *
	 * @return ImageBytes	the image object.
	 */
	public static function fromBase64($base64, $contentType = null) {
		return new ImageBytes(base64_decode($base64), $contentType);
	}

	/**
	 * Create Image object from file.
	 * 
	 * @param string $imageData path to image file.
	 *
	 * @return ImageFile	the image object.
	 */
	public static function fromFile($file) {
		return new ImageFile($file);
	}

	/**
	 * Map an array with raw images to Image objects.
	 *
	 * @param string[] $imageDatas	array with raw image data for each entry.
	 * @param string $contentType override content-type detection.
	 *
	 * @return ImageBytes[]	the image objects.
	 */
	public static function fromBytesArray(array $imageDatas, $contentType = null) {
		$result = [];
		foreach($imageDatas as $imageData) {
			$result[] = Image::fromBytes($imageData, $contentType);
		}
		return $result;
	}

	/**
	 * Map an array with base64 encoded images to Image objects.
	 *
	 * @param string[] $imageDatas	array with base64 encoded image data for each entry.
	 * @param string $contentType override content-type detection.
	 *
	 * @return ImageBytes[]	the image objects.
	 */
	public static function fromBase64Array(array $base64s, $contentType = null) {
		$result = [];
		foreach($base64s as $base64) {
			$result[] = Image::fromBase64($base64, $contentType);
		}
		return $result;
	}

	/**
	 * Map an array with files to Image objects.
	 *
	 * @param string[] $imageFiles	array with file paths.
	 *
	 * @return ImageBytes[]	the image objects.
	 */
	public static function fromFileArray(array $imageFiles) {
		$result = [];
		foreach($imageFiles as $imageFile) {
			$result[] = Image::fromFile($imageFile);
		}
		return $result;
	}

	/**
	 * Extract the image and return it as ImageBytes object.
	 * This is useful for serialising, as the source image could be moved after serialisation,
	 * making the unserialize method in this class unreliable.
	 */
	public abstract function toBytes();

	/**
	 * Output image to the webbrowser, with correct Content-type header
	 * If not image is available, a 404 is sent, along with a preconfigured image.
	 * This function will not return.
	 */
	public abstract function viewImage();
	/**
	 * Get the raw bytes of the image file.
	 * @return string 
	 */
	public abstract function getImageBytes();
	/**
	 * Get the image file.
	 * @return string path to the image file.
	 */
	public abstract function getImageFile();
	/**
	 * Get the content type of the image.
	 * @return string content type, for example image/jpeg.
	 */
	public abstract function getImageContentType();

	/**
	 * Convert the image to a string. This is only used for comparing objects,
	 * so it returns $this->serialze().
	 *
	 * @return string serialised string.
	 */
	public function __toString() {
		return $this->serialize();
	}

}
