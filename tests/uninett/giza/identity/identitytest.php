<?php namespace uninett\giza\identity;

use \uninett\giza\GizaTestCase;
use \uninett\giza\identity\AttributeAssertion;
use \uninett\giza\identity\Image;

class IdentityTest extends GizaTestCase {

const IDENTITY = 'uid: jorn.dejong@uninett.no
displayName:: WcO4cm4gZGUgSm9uZw==
mail: jorn.dejong@uninett.no
photo::
 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
';

const IMAGE = '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';

	private function assertIdentity(AttributeAssertion $identity) {
		$this->assertEquals('jorn.dejong@uninett.no', $identity->getUniqueID());
		$this->assertEquals('jorn.dejong@uninett.no', $identity->getMail());
		$this->assertEquals('Jørn Åne de Jong', $identity->getDisplayName());
		$this->assertEquals(base64_decode(static::IMAGE), $identity->getImage()->getImageBytes());
	}

	public function testReadIdentity() {
		$identity = new AttributeAssertion(static::IDENTITY);
		$this->assertIdentity($identity);
	}

	/**
	 * @depends testReadIdentity
	 */
	public function testWriteIdentity() {
		$identity = new AttributeAssertion([
			'uid' => ['jorn.dejong@uninett.no'],
			'mail' => ['jorn.dejong@uninett.no'],
			'displayName' => ['Jørn Åne de Jong'],
			'photo' => [base64_decode(static::IMAGE)],
		]);
		$this->assertIdentity(new AttributeAssertion($identity->serialize()));
	}

	/**
	 * @depends testReadIdentity
	 */
	public function testWriteObjectIdentity() {
		$identity = new AttributeAssertion([
			'uid' => ['jorn.dejong@uninett.no'],
			'mail' => ['jorn.dejong@uninett.no'],
			'displayName' => ['Jørn Åne de Jong'],
			'photo' => [Image::fromBytes(base64_decode(static::IMAGE))],
		]);
		$this->assertIdentity(new AttributeAssertion($identity->serialize()));
	}

}
