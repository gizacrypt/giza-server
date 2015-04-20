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

	'secretStore' => new \uninett\giza\secret\storage\file\FileSecretStore(
		implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'var', 'secret'])
	),

	'identitySource' => $saml,

	'identityStore' => new \uninett\giza\identity\file\FileProfileStore(
		implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'var', 'profile'])
	),

	'auxiliaryIdentitySources' => [
		'SAML' => $saml,
		'Keyserver' => new \uninett\giza\identity\keyserver\KeyServerIdentitySource(
			['hkp://minsky.surfnet.nl']
		),
	],

	'gpgBinaryPath' => '/usr/bin/gpg',

	'gpgHomedirPath' => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'var', 'gnupg']),

	'standardIdentityImage' => implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'www', 'static', 'gfx', 'no-photo.svg']),

];
