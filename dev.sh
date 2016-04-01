#!/bin/sh
set -e
tee <<EOF
               _______________________
     /\       /  ____/  /_____  /  _  |
   /____\    /  /_  /  /   ____/  __  |
 /________\ /______/__/_______/__/  |_|

GIZA development server

Author: Jørn Åne de Jong <jorn.dejong@uninett.no>
Copyright 2014-2015, UNINETT

EOF
if ! php -r 'exit (PHP_VERSION_ID < 50400 ? 1 : 0);'
then
	echo PHP version too old, 5.4.0 or newer required.
	php -v
	exit 1
fi >&2
cd "$(dirname "$0")"
mkdir -p var/profile var/secret var/gpg
[ -f etc/giza.conf.php ] || cp etc/giza.conf.dev.php etc/giza.conf.php
php -S [::1]:9124 -t www/ -d arg_separator.output=\; arg_separator.input=\;\&
