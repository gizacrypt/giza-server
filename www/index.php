<?php require_once dirname(__DIR__) . '/src/_autoload.php';

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014-2015, UNINETT
 */

use \uninett\giza\secret\Secret ;
use \uninett\giza\secret\Metadata ;
use \uninett\giza\identity\Profile ;

function o($str) { return htmlspecialchars($str); }
function qs($arr) { return o(http_build_query($arr)); }
function accesslevel(Metadata $metadata, Profile $profile = NULL) { 
	return (($metadata->hasPermission(Secret::ACCESS_WRITE, $profile))?
                (($metadata->hasPermission(Secret::ACCESS_ADMIN, $profile))?3:2):1) ;
}

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
} elseif (isset($_SERVER['QUERY_STRING'])) {
	header('Location: ' . (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? 'http' : 'https')
			. '://'
			. $_SERVER['HTTP_HOST']
			. dirname($_SERVER['SCRIPT_NAME'])
		, true, 301);
	exit;
}

$path = './' ;

try	{
	$profile = uninett\giza\identity\Profile::fromStore() ;
} catch	(RuntimeException $e) {
	header('Location: ' . $path . 'profile/', true, 303);
	exit ;
}

$title = 'Giza' ;
include $path . '_header.php' ;

echo '<h1><img src="' . $path . 'static/gfx/icon-rank-3.svg" alt="">Giza</h1>
<div id="giza-tabs">
<span id="giza-tabs-selected">Secrets</span> <a href="new/">New secret</a> <a href="profile/">Profile</a> <a href="help/">Help</a>
</div>
<div id="giza-sheet">
<div id="giza-view">'."\n";

foreach(Secret::getSecretsForProfile() as $secret) {
	$accesslevel = accesslevel($secret->getMetadata()) ;
	$readable = $secret->isReadableByProfile() ;
	$userlist = [];
	$expired = FALSE ;
	$missing = FALSE ;
	foreach ($secret->getMetadata()->getIdentities() as $profile) {
		$userlist[$profile->getUniqueId()] = array (
			'expired' => FALSE ,
			'missing' => TRUE ,
			'name' => $profile->getDisplayName() ,
			'accesslevel' => accesslevel($secret->getMetadata(), $profile)
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

	$author = o(Profile::getActiveFromKey($secret->getSigningKey())->getDisplayName()) ;
	$date = o($secret->getMetadata()->getDate()->format('Y-m-d')) ;

	echo '    <div' . (($readable)?'':' class="unavailable"') . '>
        <div><div>
            <img src="' . $path . 'static/gfx/icon-menu-' . $accesslevel . '.svg" alt="">
            <div>';
	$link = (($readable)?' href="?' . qs(['uuid' => $secret->getUUID(), 'action' => 'read', 'method' => 'view']) . '"':'');
	echo '<a' . $link . '><span><img src="' . $path . 'static/gfx/icon-view.svg" alt=""></span><span>Decrypt &amp; view</span></a>';

	$link = (($readable)?' href="?' . qs(['uuid' => $secret->getUUID(), 'action' => 'read', 'method' => 'save']) . '"':'');
	echo '<a' . $link . '><span><img src="' . $path . 'static/gfx/icon-download.svg" alt=""></span><span>Decrypt &amp; save plaintext</span></a>';

#	echo '<a href=""><span><img src="' . $path . 'static/gfx/icon-update.svg" alt=""></span><span>Update with new users</span></a>';

	if ($accesslevel >= 2) {
		$link = (($readable)?' href="?' . qs(['uuid' => $secret->getUUID(), 'action' => 'write', 'method' => 'edit']) . '"':'');
		echo '<a' . $link . '><span><img src="' . $path . 'static/gfx/icon-edit.svg" alt=""></span><span>Edit</span></a>';

		$link = ' href="?' . qs(['uuid' => $secret->getUUID(), 'action' => 'write', 'method' => 'upload']) . '"';
		echo '<a' . $link . '><span><img src="' . $path . 'static/gfx/icon-upload.svg" alt=""></span><span>Upload new version</span></a>';

#		echo '<a href=""><span><img src="' . $path . 'static/gfx/icon-revert.svg" alt=""></span><span>Revert to previous version</span></a>';

#		echo '<a href=""><span><img src="' . $path . 'static/gfx/icon-history.svg" alt=""></span><span>Full history</span><span><img src="gfx/icon-more.svg" alt=""></span></a>';

		if ($accesslevel == 3) { 
#			echo '<a href=""><span><img src="' . $path . 'static/gfx/icon-gears.svg" alt=""></span><span>Users &amp; properties</span><span><img src="gfx/icon-more.svg" alt=""></span></a>';

#			echo '<a href=""><span><img src="' . $path . 'static/gfx/icon-delete.svg" alt=""></span><span>Delete</span></a>';
			}
		}
	echo '</div>
        </div></div>
        <div>
            <a href="?' . qs(['uuid' => $secret->getUUID(), 'action' => 'read', 'method' => 'view']) . '">' . o($secret->getName()) . '</a>
        </div>
        <div><div>
            <img src="' . $path . 'static/gfx/icon-users' . (($missing)?'-missing':'') . (($expired)?'-expired':'') . '.svg" alt="">
            <div>';
	foreach ($userlist as $user) {
		echo '<a href="#"' . (($user['missing'])?' class="missing"':'') . (($user['expired'])?' class="expired"':'') . '>' .
		'<span><img src="' . $path . 'static/gfx/no-photo.svg" alt=""></span>' .
		'<span>' . $user['name'] . '</span>' .
		'<span><img src="' . $path . 'static/gfx/icon-rank-' . $user['accesslevel'] . '.svg" alt=""></span>' .
		'</a>';
		}
	echo '</div>
        </div></div>
        <div><div>
            <img src="' . $path . 'static/gfx/icon-cal-0.svg" alt="">
            <div><div>Last changed by</div><a><img src="' . $path . 'static/gfx/no-photo.svg" alt="">' . $author . '</a><div>' . $date . '</div></div>
        </div></div>
    </div>' . "\n" ;
	}

echo '</div>'."\n" . '</div>'."\n" ;

include $path . '_footer.php' ;

?>

