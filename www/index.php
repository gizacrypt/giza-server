<?php require_once dirname(__DIR__) . '/src/_autoload.php';

/**
 *
 * @copyright Copyright (c) 2014, UNINETT
 */

function o($str) { return htmlspecialchars($str); }
function qs($arr) { return o(http_build_query($arr)); }

if ($uploaded = file_get_contents('php://input')) {
	try {
		$params = [];
		if (isset($_SERVER['HTTP_X_GIZA_NAME'])) {
			$params['name'] = $_SERVER['HTTP_X_GIZA_NAME'];
		}
		\uninett\giza\secret\Secret::addSecret($uploaded, $params);
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

$title = 'Giza' ;
include './_header.php' ;

echo '<div id="giza-view">'."\n";

foreach(\uninett\giza\secret\Secret::getSecretsForProfile() as $secret) {
	$accesslevel = (($secret->getMetadata()->hasPermission(6))?(($secret->getMetadata()->hasPermission(7))?3:2):1) ;
	echo '    <div>
        <div><div>
            <img src="static/gfx/icon-menu-' . $accesslevel . '.svg" alt="">
            <div>';
#	TODO: gray out meaningless choices
	echo '<a href="?' . qs(['uuid' => $secret->getUUID(), 'action' => 'read', 'method' => 'view']) . '"><span><img src="static/gfx/icon-view.svg" alt=""></span><span>Decrypt &amp; view</span></a>';
	echo '<a href="?' . qs(['uuid' => $secret->getUUID(), 'action' => 'read', 'method' => 'save']) . '"><span><img src="static/gfx/icon-download.svg" alt=""></span><span>Decrypt &amp; save plaintext</span></a>';
#	echo '<a href=""><span><img src="static/gfx/icon-update.svg" alt=""></span><span>Update with new users</span></a>';
	if ($accesslevel >= 2) {
		echo '<a href="?' . qs(['uuid' => $secret->getUUID(), 'action' => 'write', 'method' => 'edit']) . '"><span><img src="static/gfx/icon-edit.svg" alt=""></span><span>Edit</span></a>';
		echo '<a href="?' . qs(['uuid' => $secret->getUUID(), 'action' => 'write', 'method' => 'upload']) . '"><span><img src="static/gfx/icon-upload.svg" alt=""></span><span>Upload new version</span></a>';
#		echo '<a href=""><span><img src="static/gfx/icon-revert.svg" alt=""></span><span>Revert to previous version</span></a>';
#		echo '<a href=""><span><img src="static/gfx/icon-history.svg" alt=""></span><span>Full history</span><span><img src="gfx/icon-more.svg" alt=""></span></a>';
		if ($accesslevel == 3) { 
#		echo '<a href=""><span><img src="static/gfx/icon-gears.svg" alt=""></span><span>Users &amp; properties</span><span><img src="gfx/icon-more.svg" alt=""></span></a>';
#		echo '<a href=""><span><img src="static/gfx/icon-delete.svg" alt=""></span><span>Delete</span></a>';
			}
		}
	echo '</div>
        </div></div>
        <div>
            <a href="?' . qs(['uuid' => $secret->getUUID(), 'action' => 'read', 'method' => 'view']) . '">' . o($secret->getName()) . '</a>
        </div>
        <div><div>
            <!-- user list icon -->
            <div><!-- user list --></div>
        </div></div>
        <div><div>
            <!-- status box icon -->
            <div><!-- status box --></div>
        </div></div>
    </div>' . "\n" ;
	}

echo '</div>'."\n";

include './_footer.php' ;

?>

