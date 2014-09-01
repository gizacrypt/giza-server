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

	'secretStore' => new \uninett\giza\secret\file\FileSecretStore('/srv/giza/secret'),

	'identitySource' => $saml,

	'auxiliaryIdentitySources' => [
		'SAML' => $saml,
		'KIND' => new \uninett\giza\identity\kind\KindAttributeSource(
			new PDO('pgsql:dbname=kind;host=postgresql.uninett.no;sslmode=require', 'kind_ro', NULL)
		),
	],

	'identityStore' => new \uninett\giza\identity\file\FileProfileStore(
		'/srv/giza/profile'
	),

	'gpgBinaryPath' => '/usr/bin/gpg',

	'gpgHomedirPath' => '/srv/giza/gnupg',

	'standardIdentityImage' => dirname(__DIR__) . '/www/static/gfx/no-photo.svg',

];
