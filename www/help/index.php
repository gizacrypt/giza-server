<?php require_once dirname(__DIR__) . '/../src/_autoload.php';

/**
 *
 * @copyright Copyright (c) 2014, UNINETT
 */

$title = 'Giza – Help page' ;
$path = '../' ;
include $path . '_header.php' ;

echo '<h1><img src="' . $path . 'static/gfx/icon-rank-3.svg" alt="">Giza</h1>
<div id="giza-tabs">
<a href="' . $path . '">Secrets</a> <a href="' . $path . 'new/">New secret</a> <a href="' . $path . 'profile/">Profile</a> <span id="giza-tabs-selected">Help</span>
</div>
<div id="giza-sheet">

<p>Giza files can be read using standard PGP tools, such as the GPG suite. However, as some metadata are added after encryption but before signing, two-pass parsing is required: 
<code>gpg -d <i>file</i> | gpg -d</code> will display the contents of a file with plaintext payload. To avoid output from the first command (the signature check) interfering with the 
second command’s request for a passphrase, one may have to resort to odd tricks such as <code>gpg -d <i>file</i> | tac | tac | gpg -d</code> (if installed, <code>tac</code> reverses 
the order of lines in the output; invoked twice it effecively does nothing, but ensures that the second <code>gpg</code> command does not start until the entire file is parsed by the 
first one).<p>

<p>In order to simplify the usage, it is preferable to install a client program which parses the files received from the server and invokes the appropriate PGP commands. A 
<code>bash</code> implementation can be found here: <code><a href="' . $path . 'static/app/bash-app/giza.sh">giza.sh</a></code></p>

<p>This program needs to be made executable, and chosen as the handler for the content-type <code>application/x-giza</code>. It requires that the GPG suite (<code>gpg</code> and 
<code>gpg-agent</code>) is installed on the system.</p>

</div>' ;

include $path . '_footer.php' ;

?>

