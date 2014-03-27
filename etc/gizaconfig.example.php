<?php

$gizaConfig = [ 

	'secretStore' => new \uninett\giza\secret\FileSecretSource('/srv/giza'),

	'identitySource' => new \uninett\giza\identity\saml\SimpleSamlIdentitySource([
		'sspRoot' => '/usr/share/simplesamlphp',
		'authSource' => 'default-sp',
		'uidAttr' => 'eduPersonPrincipalName',
		'displayNameAttr' => 'displayName',
		'jpegPhotoAttr' => 'jpegPhoto',
	]),

	'identityAuxiliarySources' => [
		'Keyserver' => new \uninett\giza\identity\keyserver\KeyServerIdentitySource(
			['hkp://minsky.surfnet.nl']
		),
	],

	'identityStore' => new \uninett\giza\identity\database\DatabaseProfileStore(
		new PDO('pgsql:dbname=giza;host=127.0.0.1;sslmode=require', 'username', 'password')
	),

	'gpgBinary' => '/usr/bin/gpg',
	
	'standardImage' => './www/static/sfx/no-photo.svg',

];
