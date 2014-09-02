<?php namespace uninett\giza;

use \PHPUnit_Framework_TestCase;

use \uninett\giza\Giza;

abstract class GizaTestCase extends PHPUnit_Framework_TestCase {

	protected function setUp() {
		Giza::setInstance([
			'secretStore' => null, // TODO mock
			'identitySource' => $null, // TODO mock,
			'auxiliaryIdentitySources' => [
				'SAML' => null, // TODO mock,
				'Keyserver' => null, // TODO mock,
			],
			'identityStore' => null, // TODO mock,
			'gpgBinaryPath' => '/usr/bin/gpg',
			'gpgHomedirPath' => dirname(__DIR__) . '/var/gnupg',
			'standardIdentityImage' => dirname(__DIR__) . '/www/static/gfx/no-photo.svg',
		]);
	}

}
