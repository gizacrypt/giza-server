<?php namespace uninett\giza\core;

use \Serializable;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
abstract class Image {

	/**
	 * Create Image object from bytes.
	 * 
	 * @param string $imageData raw image data.
	 *
	 * @return ImageBytes	the image object.
	 */
	public static function fromBytes($bytes) {
		return new ImageBytes($bytes);
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
	 *
	 * @return ImageBytes[]	the image objects.
	 */
	public static function fromBytesArray(array $imageDatas) {
		$result = [];
		foreach($imageDatas as $imageData) {
			$result[] = Image::fromBytes($imageData);
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

}
