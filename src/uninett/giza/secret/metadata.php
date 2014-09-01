<?php namespace uninett\giza\secret;

use \ArrayAccess;
use \RuntimeException;

/**
 * Metadata for a Giza secret, this is a attribute-value store.
 * There are three different kinds of metadata;
 * (1) versioning, (2) content-related and (3) access.
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
final class Metadata implements ArrayAccess {

	/**
	 * All values inside this metadata
	 */
	private $contents = [];

	/**
	 * Construct a new metadata object.
	 * This will parse the metadata to internal arrays.
	 */
	public function __construct($contents) {
		$contents = preg_replace([
			"/^(\s*\n)?-----BEGIN GIZA METADATA-----\n/m",
			"/\n-----END GIZA METADATA-----(\n\s*)?$/m"
		], ['', ''], $contents);
		foreach(preg_split('/\s*\n/', $contents) as $line) {
			if (trim($line) && strpos($line, ':')) {
				list($attribute, $value) = preg_split('/:\s*/', $line, 2);
				$this->contents[$attribute][] = $value;
			} else {
				throw new RuntimeException('Illegal metadata line: '.$line);
			}
		}
		foreach(['Giza-Version','Revision','Date','Action','Content-Type','Access'] as $required) {
			if (!isset($this->contents[$required])) {
				throw new RuntimeException('Missing required attribute '.$required);
			}
		}
	}

	public function offsetExists($offset) {
		return isset($this->contents[$offset]);
	}

	public function offsetGet($offset) {
		return isset($this->contents[$offset]) ? $this->contents[$offset] : NULL;
	}

	public function offsetSet($offset, $value) {
		throw new RuntimeException('Unsupported operation');
	}

	public function offsetUnset($offset) {
		throw new RuntimeException('Unsupported operation');
	}

	/**
	 * Compare all attributes with another metadata object.
	 * Since one of the attributes is the revision UUID,
	 * equal attributes should mean equal secrets.
	 *
	 * @param Metadata $other metadata to compare to
	 *
	 * @return boolean both metadata objects have same attributes
	 */
	public function compareAllAttirubtes(Metadata $other) {
		if (sizeof($other->contents) != sizeof($this->contents)) {
			return false;
		}
		foreach($this->contents as $attribute => $values) {
			if (!$this->compareAttribute($other, $attribute)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Compare one attribute with another metadata object.
	 *
	 * @param Metadata $other metadata to compare to
	 * @param string $attribute name of the attribute to compare
	 *
	 * @return boolean both metadata objects have the same attribute values for the compared attribute
	 */
	public function compareAttribute(Metadata $other, $attribute) {
		if (sizeof($other->contents[$attribute]) != sizeof($this->contents[$attribute])) {
			return false;
		}
		foreach($this->contents[$attribute] as $value) {
			if (!in_array($value, $other->contents[$attribute])) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Compare all attributes associated with the content.
	 * When the content of a secret changes, these attributes MAY change.
	 *
	 * @param Metadata $other metadata to compare to
	 *
	 * @return boolean both metadata objects have the same attribute values for content-related attributes
	 */
	public function compareContentAttributes(Metadata $other) {
		return $this->compareAttribute($other, 'Content-Type');
	}

	/**
	 * Compare all attributes associated with the content.
	 * When the access rules of a secret change, these attributes MUST change.
	 *
	 * @param Metadata $other metadata to compare to
	 *
	 * @return boolean both metadata objects have the same attribute values for access attributes
	 */
	public function compareAccessAttributes(Metadata $other) {
		return $this->compareAttribute($other, 'Access');
	}

	/**
	 * Get the Giza version this secret was created on
	 */
	public function getGizaVersion() {
		return $this['Giza-Version'][0];
	}

	/**
	 * Get the revision UUID of the secret
	 */
	public function getRevision() {
		return $this['Revision'][0];
	}

	/**
	 * Get the submission date of the secret
	 */
	public function getDate() {
		return $this['Date'][0];
	}

	/**
	 * Get the name of the action that created this revision of the secret
	 */
	public function getAction() {
		return $this['Action'][0];
	}

	/**
	 * Get the content-type of the secret payload
	 */
	public function getContentType() {
		return $this['Content-Type'][0];
	}

	/**
	 * Get the access rules for this secret
	 */
	public function getAccess() {
		return $this['Access'];
	}

	/**
	 * Get the revision UUID of the secret this secret is based on
	 */
	public function getBasedOn() {
		return isset($this['Based-On'][0]) ? $this['Based-On'][0] : NULL;
	}

	/**
	 * Get the revision UUID of the secret that was the newest before this secret was submitted
	 */
	public function getPrevious() {
		return isset($this['Previous'][0]) ? $this['Previous'][0] : NULL;
	}

	/**
	 * Generate metadata in plain-text
	 *
	 * @return string giza metadata in plain text, multi-line
	 */
	public function __toString() {
		$result = "-----BEGIN GIZA METADATA-----\n";
		foreach($contents as $attribute => $values) {
			foreach($values as $value) {
				$result .= "${attribute}: ${value}\n";
			}
		}
		$result .= '-----END GIZA METADATA-----';
		return $result;
	}

}
