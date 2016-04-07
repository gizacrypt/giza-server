<?php namespace uninett\giza\identity\file;

use \ReflectionClass;
use \RuntimeException;

use \uninett\giza\core\Image;

use \uninett\giza\identity\Profile;
use \uninett\giza\identity\ProfileStore;

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
class FileProfileStore implements ProfileStore {

	private $path;

	public function __construct($path) {
		$this->path = $path;
	}

	public function getAssertionFor(array $attributeAssertions) {
		foreach($attributeAssertions as $assertion) {
			$newUid = $assertion->getUniqueId();
			if (isset($uid) && $uid != $newUid || !isset($newUid)) {
				throw new LogicException('Inconsistent UID');
			}
			$uid = $assertion->getUniqueId();
		}
		if (!isset($uid)) {
			throw new LogicException('No UID found');
		}
		$profile = $this->getProfile($uid);
		foreach($profile->getAttributeAssertions() as $assertion) {
			if (!in_array($assertion, $attributeAssertions)) {
				return null;
			}
		}
		return $profile;
	}

	public function getAuthenticationAssertion() {
		return null;
	}

	public function store(Profile $profile) {
		$index = date('YmdHis');
		$filename = $this->path.DIRECTORY_SEPARATOR.$profile->getUniqueId().'.';
		while(file_exists($filename.$index.'.ldif')) {
			$index++;
		}
		$filename .= $index . '.ldif';
		if (!file_put_contents($filename, $profile->serialize(), LOCK_EX)) {
			throw new RuntimeException('Unable to write file.');
		}
		chmod($filename, 0400);
	}

	public function getProfile($uid) {
		$filename = $uid.'.';
		$files = glob(
			rtrim($this->path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename . '*',
			GLOB_NOSORT|GLOB_NOESCAPE|GLOB_MARK|GLOB_ERR
		);
		if (!is_array($files)) {
			throw new RuntimeException('Unable to access profile directory');
		} elseif (empty($files)) {
			return null;
		}
		rsort($files);
		$reflect = new ReflectionClass('\\uninett\\giza\\identity\\Profile');
		$profile = $reflect->newInstanceWithoutConstructor();
		$profile->unserialize(file_get_contents(reset($files)));
		return $profile;
	}

	public function getActiveProfiles() {
		$files = glob(
			rtrim($this->path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*',
			GLOB_NOSORT|GLOB_NOESCAPE|GLOB_MARK|GLOB_ERR
		);
		$pop = function($a){array_pop($a);return $a;};
		$files = array_map('basename', $files);
		$files = array_map(function($f){return explode('.',$f);}, $files);
		$files = array_map($pop, $files);
		$files = array_map($pop, $files);
		$files = array_map(function($f){return implode('.',$f);}, $files);
		return array_map([$this, 'getProfile'], $files);
	}

}
