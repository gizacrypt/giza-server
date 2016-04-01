<?php
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__DIR__) . DIRECTORY_SEPARATOR . 'dev' . DIRECTORY_SEPARATOR . 'src');

$saml = new \uninett\giza\identity\development\StaticAttributeSource([
	'uid' => ['sysop@gizacrypt.io'],
	'mail' => ['sysop@gizacrypt.io'],
	'displayName' => ['Dist Sysop'],
	'jpegPhoto' => [],
	'gpgKey' => [new \uninett\giza\pki\PGPPublicKey('')]
]);
return [

	'secretStore' => new \uninett\giza\secret\storage\file\FileSecretStore(dirname(__DIR__) . '/var/secret'),

	'identitySource' => $saml,

	'auxiliaryIdentitySources' => [
		'SAML' => $saml,
		/*
		'Keyserver' => new \uninett\giza\identity\keyserver\KeyServerIdentitySource(
			['hkp://minsky.surfnet.nl']
		),
		*/
	],

	'identityStore' => new \uninett\giza\identity\file\FileProfileStore(
		dirname(__DIR__) . '/var/profile'
	),

	'gpgBinaryPath' => '/usr/bin/gpg',

	'gpgHomedirPath' => dirname(__DIR__) . '/var/gnupg',

	'standardIdentityImage' => dirname(__DIR__) . '/www/static/gfx/no-photo.svg',

];
