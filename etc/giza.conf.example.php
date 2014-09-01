<?php

$saml = new \uninett\giza\identity\saml\SimpleSamlAttributeSource([
	'sspRoot' => '/opt/uninett/simplesamlphp',
	'authSource' => 'default-sp',
	'uidAttr' => 'eduPersonPrincipalName',
	'mailAttr' => 'mail',
	'displayNameAttr' => 'displayName',
	'jpegPhotoAttr' => 'jpegPhoto',
]);
return [

	'secretStore' => new \uninett\giza\secret\file\FileSecretStore(dirname(__DIR__) . '/var/secret'),

	'identitySource' => $saml,

	'auxiliaryIdentitySources' => [
		'SAML' => $saml,
		'Keyserver' => new \uninett\giza\identity\keyserver\KeyServerIdentitySource(
			['hkp://minsky.surfnet.nl']
		),
	],

	'identityStore' => new \uninett\giza\identity\file\FileProfileStore(
		dirname(__DIR__) . '/var/profile'
	),

	'gpgBinaryPath' => '/usr/bin/gpg',

	'gpgHomedirPath' => dirname(__DIR__) . '/var/gnupg',

	'standardIdentityImage' => dirname(__DIR__) . '/www/static/gfx/no-photo.svg',

];
