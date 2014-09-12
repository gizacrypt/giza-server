<?php namespace uninett\giza\secret\output;

use \uninett\giza\secret\output\NewOutputGenerator;
use \uninett\giza\GizaTestCase;

class OutputGeneratorTest extends GizaTestCase {

const EXPECTED_NEW_SECRET = '-----BEGIN GIZA COMMAND-----
Action: new
Name: test
Content-Type: password
Access: READ|WRITE|ACCESS 0000000000000000
Access: READ 1111111111111111
Callback-URL: http://giza.labs.uninett.no/new/
Method: edit
-----END GIZA COMMAND-----';

	public function testGenerateNewSecret() {
		$this->assertEquals(self::EXPECTED_NEW_SECRET, (string)NewOutputGenerator::getInstance([
			'action' => 'new',
			'name' => 'test',
			'method' => 'edit',
			'content-type' => 'password',
			'callback-url' => 'http://giza.labs.uninett.no/new/',
			'access' => [
				'0000000000000000' => 'READ|WRITE|ACCESS',
				'1111111111111111' => 'READ',
			]
		]));
	}

}
