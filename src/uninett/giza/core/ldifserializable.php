<?php namespace uninett\giza\core;

use \LogicException;
use \Serializable;

/**
 * 
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */
abstract class LDIFSerializable implements Serializable {

	abstract protected function getAttributes();

	abstract protected function setAttributes($attributes);

	protected $singleValueAttributes = ['uid'];

	public function serialize() {
		$result = '';
		$elements = $this->getAttributes();
		foreach($elements as $attributeName => $values) {
			if (!is_array($values)) {
				throw new LogicException('The value for '.$attributeName.' is not an array.');
			}
			foreach($values as $valueData) {
				if (is_object($valueData)) {
					if ($valueData instanceof Serializable) {
						$valueData = $valueData->serialize();
					} else {
						throw new LogicException('The object for value '.$attributeName.' is not Serializable.');
					}
				}
				if (preg_match('/[^\x20-\x7f]/', $valueData)) {
					$line = $attributeName . ':: ' . base64_encode($valueData) . "\n";
				} else {
					$line = $attributeName . ': ' . $valueData . "\n";
				}
				if (strlen($line) > 80) {
					if (preg_match('/^[^\\x20-\\x7f\\s][^\\x20-\\x7f][^\\x20-\\x7f\\s]+$/', $valueData)) {
						$line = $attributeName . ": \n " . implode("\n ", str_split($valueData, 80)) . "\n";
					} else {
						$line = $attributeName . ":: \n " . implode("\n ", str_split(base64_encode($valueData), 80)) . "\n";
					}
				}
				$result .= $line;
			}
		}
		return $result;
	}

	public function unserialize($unserialized) {
		$lines = explode("\n", $unserialized."\n");
		$attributeName = NULL;
		$attributeValue = '';
		$b64Encoded = false;
		$result = [];
		$rejectAttributes = [];
		foreach($this->singleValueAttributes as $attributeName) {
			$rejectAttributes[$attributeName] = false;
		}
		foreach($lines as $lineNr => $line) {
			if (strlen($line) && trim($line) && $line{0} === ' ') {
				$attributeValue .= substr($line, 1);
			} else {
				if($attributeName) {
					$result[$attributeName][] = $b64Encoded
						? base64_decode($attributeValue)
						: $attributeValue
						;
				}
				$match = preg_match('/^([^\s:]*?)(:+) *(.*)$/', $line, $matches);
				if ($match) {
					$attributeName = $matches[1];
					$attributeValue = $matches[3];
					$b64Encoded = strlen($matches[2]) > 1;
					if (isset($rejectAttributes[$attributeName])) {
						if ($rejectAttributes[$attributeName]) {
							throw new LogicException('LDIF contains duplicate attribute '.$attributeName.' on line '.$lineNr);
						}
						$rejectAttributes[$attributeName] = true;
					}
				} elseif (!trim($line)) {
					$attributeName = NULL;
					$attributeValue = NULL;
					$b64Encoded = false;
				} else {
					throw new LogicException('Invalid LDIF on line '.($lineNr+1));
				}
			}
		}
		$this->setAttributes($result);
	}

}
