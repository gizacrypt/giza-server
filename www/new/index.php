<?php require_once dirname(dirname(__DIR__)) . '/src/_autoload.php';

/**
 *
 * @copyright Copyright (c) 2014, UNINETT
 */

function o($str) { return htmlspecialchars($str); }

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
		'access' => $access,
	])->generateOutput();
	exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['QUERY_STRING']) {
	header('Location: ' . (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? 'http' : 'https')
			. '://'
			. $_SERVER['HTTP_HOST']
			. dirname($_SERVER['SCRIPT_NAME'])
		, true, 301);
	exit;
}

?>

<form action="./" method="post">
<h1>Name</h1>
<p>
	<input type="text" name="name">
</p>

<h1>Content-Type</h1>
<p>
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
</p>

<h1>Access</h1>
<p>
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
</p>

<p><input type="submit" value="Stage new secret"></p>
