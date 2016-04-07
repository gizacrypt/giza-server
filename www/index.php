<?php require_once dirname(__DIR__) . '/src/_autoload.php';

/**
 *
 * @copyright Copyright (c) 2014, UNINETT
 */

function o($str) { echo htmlspecialchars($str); }
function qs($arr) { o(http_build_query($arr)); }

if ($uploaded = file_get_contents('php://input')) {
	try {
		\uninett\giza\secret\Secret::addSecret($uploaded);
		header('Content-Type: application/json', true, 200);
		die(json_encode([
			'result' => '200 OK',
		], JSON_PRETTY_PRINT) . "\n");
	} catch (Exception $e) {
		header('Content-Type: application/json', true, 500);
		die(json_encode([
			'result' => '500 Internal Server Error',
			'exception' => get_class($e),
			'message' => $e->getMessage(),
			'code' => $e->getCode(),
			'trace' => array_merge(
				[$e->getFile() . '(' . $e->getLine() . ')'],
				array_map(
					function($l){
						return 
							$l['file'] .
							'(' . $l['line'] . '): ' .
							$l['function'] . 
							'(' . implode(',', array_map(function($t){return is_object($t)?get_class($t):gettype($t);}, $l['args'])) . ')';
					},
					$e->getTrace())
			),
		], JSON_PRETTY_PRINT) . "\n");
	}
} elseif (isset($_GET['uuid'])) {
	\uninett\giza\secret\Secret::getSecret($_GET['uuid'])->generateOutput($_GET);
	exit;
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
