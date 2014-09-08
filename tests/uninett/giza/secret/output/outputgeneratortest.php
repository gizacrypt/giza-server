<?php namespace uninett\giza\secret\output;

use \uninett\giza\secret\output\NewOutputGenerator;
use \uninett\giza\GizaTestCase;

class OutputGeneratorTest extends GizaTestCase {

const EXPECTED_NEW_SECRET = '-----BEGIN GIZA COMMAND-----
Action: new
Callback-URL: http://.
Method: edit
Access: READ|WRITE|ACCESS 0000000000000000
Access: READ 1111111111111111
Content-Type: password
-----END GIZA COMMAND-----';

	public function testGenerateNewSecret() {
		$this->assertEquals(self::EXPECTED_NEW_SECRET, (string)NewOutputGenerator::getInstance([
			'action' => 'new',
			'name' => 'test',
			'method' => 'edit',
			'content-type' => 'password',
			'access' => [
				'0000000000000000' => 'READ|WRITE|ACCESS',
				'1111111111111111' => 'READ',
			]
		]));
	}

}
