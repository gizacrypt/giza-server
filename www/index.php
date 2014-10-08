<?php require_once dirname(__DIR__) . '/src/_autoload.php';

/**
 *
 * @copyright Copyright (c) 2014, UNINETT
 */

use \uninett\giza\secret\Secret ;

function o($str) { return htmlspecialchars($str); }
function qs($arr) { return o(http_build_query($arr)); }

if ($uploaded = file_get_contents('php://input')) {
	try {
		$params = [];
		if (isset($_SERVER['HTTP_X_GIZA_NAME'])) {
			$params['name'] = $_SERVER['HTTP_X_GIZA_NAME'];
		}
		Secret::addSecret($uploaded, $params);
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
	Secret::getSecret($_GET['uuid'])->generateOutput($_GET);
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

foreach(Secret::getSecretsForProfile() as $secret) {
	$accesslevel = (($secret->getMetadata()->hasPermission(Secret::ACCESS_WRITE))?
		(($secret->getMetadata()->hasPermission(Secret::ACCESS_ADMIN))?3:2):1) ;
	$readable = $secret->isReadableByProfile() ;
	$userlist = [];
	$expired = FALSE ;
	$missing = FALSE ;
	foreach ($secret->getMetadata()->getIdentities() as $profile) {
		$bitmask = $secret->getMetadata()->getPermissions($profile) ;
		$userlist[$profile->getUniqueId()] = array (
			'expired' => FALSE ,
			'missing' => TRUE ,
			'name' => $profile->getDisplayName() ,
			'accesslevel' => 1 + ($bitmask&2 != 0) + ($bitmask&1 != 0)
			) ;
		}
	foreach ($secret->getIdentities() as $profile) {
		$id = $profile->getUniqueId() ;
		if (isset($userlist[$id])) $userlist[$id]['missing'] = FALSE ;
		else {
			$expired = TRUE ;
			$userlist[$id] = array (
				'expired' => TRUE ,
				'missing' => FALSE ,
				'name' => $profile->getDisplayName() ,
				'accesslevel' => 0
				) ;
			}
		}
	foreach ($userlist as $id) if ($id['missing']) $missing = TRUE ;

	echo '    <div' . (($readable)?'':' class="unavailable"') . '>
        <div><div>
            <img src="static/gfx/icon-menu-' . $accesslevel . '.svg" alt="">
            <div>';
	$link = (($readable)?' href="?' . qs(['uuid' => $secret->getUUID(), 'action' => 'read', 'method' => 'view']) . '"':'');
	echo '<a' . $link . '><span><img src="static/gfx/icon-view.svg" alt=""></span><span>Decrypt &amp; view</span></a>';

	$link = (($readable)?' href="?' . qs(['uuid' => $secret->getUUID(), 'action' => 'read', 'method' => 'save']) . '"':'');
	echo '<a' . $link . '><span><img src="static/gfx/icon-download.svg" alt=""></span><span>Decrypt &amp; save plaintext</span></a>';

#	echo '<a href=""><span><img src="static/gfx/icon-update.svg" alt=""></span><span>Update with new users</span></a>';

	if ($accesslevel >= 2) {
		$link = (($readable)?' href="?' . qs(['uuid' => $secret->getUUID(), 'action' => 'write', 'method' => 'edit']) . '"':'');
		echo '<a' . $link . '><span><img src="static/gfx/icon-edit.svg" alt=""></span><span>Edit</span></a>';

		$link = ' href="?' . qs(['uuid' => $secret->getUUID(), 'action' => 'write', 'method' => 'upload']) . '"';
		echo '<a' . $link . '><span><img src="static/gfx/icon-upload.svg" alt=""></span><span>Upload new version</span></a>';

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
            <img src="static/gfx/icon-users' . (($missing)?'-missing':'') . (($expired)?'-expired':'') . '.svg" alt="">
            <div>';
	foreach ($userlist as $user) {
		echo '<a href=""' . (($user['missing'])?' class="missing"':'') . (($user['expired'])?' class="expired"':'') . '>' .
		'<span><img src="static/gfx/icon-no-photo.svg" alt=""></span>' .
		'<span>' . $user['name'] . '</span>' .
		'<span><img src="static/gfx/icon-rank-' . $user['accesslevel'] . '.svg" alt=""></span>' ;
		}
	echo '</div>
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

