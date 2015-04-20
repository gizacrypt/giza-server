<?php require_once dirname(dirname(__DIR__)) . '/src/_autoload.php';

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014-2015, UNINETT
 */

function o($str) { return htmlspecialchars($str); }
function getBaseURL() {
	return (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? 'http' : 'https')
			. '://'
			. $_SERVER['HTTP_HOST']
			. dirname($_SERVER['SCRIPT_NAME']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!in_array($_POST['content-type'], ['password', 'select', 'input', 'auto'])) {
		header('Content-Type: text/plain', true, 403);
		die('Invalid content-type');
	}
	$_POST['content-type-password'] = 'application/x-giza-password';
	$_POST['content-type-auto'] = 'application/x-giza-auto';
	$access = [];
	foreach($_POST as $key => $value) {
		if (!$value) {
			continue;
		}
		if (substr($key, 0, 7) === 'access_') {
			$access[substr($key, 7)] = $value;
		}
	}
	\uninett\giza\secret\output\NewOutputGenerator::getInstance([
		'action' => 'new',
		'name' => $_POST['name'],
		'method' => $_POST['content-type'] == 'password' ? 'edit' : 'upload',
		'content-type' => $_POST['content-type-' . $_POST['content-type']],
		'callback-url' => dirname(getBaseURL()) . '/',
		'access' => $access,
	])->generateOutput();
	exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_SERVER['QUERY_STRING'])) {
	header('Location: ' . getBaseURL(), true, 301);
	exit;
}

$title = 'Giza – Help page' ;
$path = '../' ;
include $path . '_header.php' ;

echo '<h1><img src="' . $path . 'static/gfx/icon-rank-3.svg" alt="">Giza</h1>
<div id="giza-tabs">
<a href="' . $path . '">Secrets</a> <span id="giza-tabs-selected">New secret</span> <a href="' . $path . 'profile/">Profile</a> <a href="' . $path . 'help/">Help</a>
</div>
<div id="giza-sheet">' ;

?>

<form action="./" method="post">
<h2>Create new secret</h2>

<div style="vertical-align: top">
<div style="display: inline-block ; width: 50%">
	<h3>Name</h3> <input type="text" name="name">
</div>

<div style="display: inline-block ; width: 50%">
	<h3>Content-Type</h3>
	<div>
		<input type="radio" name="content-type" value="password" id="content-type-password" checked>
		<label for="content-type-password">password</label>
	</div>
	<div>
		<input type="radio" name="content-type" value="auto" id="content-type-auto">
		<label for="content-type-auto">auto</label>
	</div>
	<div>
		<input type="radio" name="content-type" value="select" id="content-type-select">
		<label for="content-type-select">
			<select name="content-type-select">
				<option>text/plain</option>
				<option>image/jpeg</option>
				<option>application/pdf</option>
			</select>
		</label>
	</div>
	<div>
		<input type="radio" name="content-type" value="input" id="content-type-input">
		<label for="content-type-input"><input type="text" name="content-type-input"></label>
	</div>
</div>
</div>

<div>
<h3>Access list</h3>
<?php
$uid = \uninett\giza\identity\Profile::fromStore()->getUniqueID();
foreach(\uninett\giza\identity\Profile::getActiveProfiles() as $profile) {
	$me = $uid == $profile->getUniqueID();
	$keys = $profile->getPGPPublicKeys();
	foreach($keys as $key) {
?>


<div>
	<input type="radio" name="access_<?= o($key->getKeyID()) ?>" <?= $me?'disabled':'checked' ?> value="">
	<input type="radio" name="access_<?= o($key->getKeyID()) ?>" <?= $me?'disabled ':'' ?>value="READ">
	<input type="radio" name="access_<?= o($key->getKeyID()) ?>" <?= $me?'disabled ':'' ?>value="READ|WRITE">
	<input type="radio" name="access_<?= o($key->getKeyID()) ?>" <?= $me?'checked ':'' ?>value="READ|WRITE|ADMIN">
	<?= o($profile->getDisplayName()) ?>
	&lt;<?= o($profile->getMail()) ?>&gt;
<?php if (sizeof($keys) > 1) { ?>
	(<?= o($key->getKeyID()) ?>)
<?php } ?>
</div>

<?php
	}
}
?>
</div>

<p><input type="submit" value="Stage new secret"></p>

</form>
</div>

<?php

include $path . '_footer.php' ;

?>

