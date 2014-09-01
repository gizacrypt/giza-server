<?php require_once dirname(__DIR__) . '/src/_autoload.php';

/**
 *
 * @copyright Copyright (c) 2014, UNINETT
 */

function o($str) { echo htmlspecialchars($str); }
function qs($arr) { o(http_build_query($arr)); }

if (isset($_GET['uuid'])) {
	\uninett\giza\secret\Secret::getSecret($_GET['uuid'])->generateOutput($_GET);
	exit;
} elseif (isset($_POST['secret'])) {
	\uninett\giza\secret\Secret::addSecret($_POST['secret']);
} elseif ($_SERVER['QUERY_STRING']) {
	header('Location: ' . (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? 'http' : 'https')
			. '://'
			. $_SERVER['HTTP_HOST']
			. dirname($_SERVER['SCRIPT_NAME'])
		, true, 301);
	exit;
}

foreach(\uninett\giza\secret\Secret::getSecretsForProfile() as $secret) {
?>

<div>
	<p><?php o($secret->getName()); ?></p>
	<p>
		<a href="./?<?php qs(['uuid' => $secret->getUUID(), 'action' => 'read', 'method' => 'view']); ?>">view</a>
		<a href="./?<?php qs(['uuid' => $secret->getUUID(), 'action' => 'read', 'method' => 'save']); ?>">save</a>
		<a href="./?<?php qs(['uuid' => $secret->getUUID(), 'action' => 'write', 'method' => 'edit']); ?>">edit</a>
		<a href="./?<?php qs(['uuid' => $secret->getUUID(), 'action' => 'write', 'method' => 'upload']); ?>">upload</a>
	</p>
</div>

<?php } ?>
