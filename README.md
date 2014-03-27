Giza secure password sharing architecture
=========================================
Giza stores passwords as PGP encrypted files and makes these available to authenticated users through a web interface.
The server will never have access to the plain-text passwords as all cryptographic operations are to be done on the client-side.
The server will only validate uploaded files.

Requirements
------------
Giza should run be able to run on any POSIX compatible operating system with a web server and PHP 5.4+.
