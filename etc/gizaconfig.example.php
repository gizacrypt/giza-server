<?php

return [ 

	'secretStore' => new \uninett\giza\secret\FileSecretStore('/srv/giza/secret'),

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

	'gpgBinaryPath' => '/usr/bin/gpg',
	
	'standardIdentityImage' => './www/static/sfx/no-photo.svg',

];
