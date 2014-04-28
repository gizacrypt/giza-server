<?php require_once dirname(dirname(__DIR__)) . '/src/_autoload.php';

/**
 *
 * @author Jørn Åne de Jong <jorn.dejong@uninett.no>
 * @copyright Copyright (c) 2014, UNINETT
 */

$candidateProfile = uninett\giza\identity\Profile::fromAuthentication();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['storeProfile'])) {
		$candidateProfile->store();
	}
	header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
}

$storedProfile = uninett\giza\identity\Profile::fromStore();

$profile = isset($storedProfile) ? $storedProfile : $candidateProfile;

$title = 'Profile';

?><!DOCTYPE html>
<title><?php htmlentities($title); ?></title>
<?php
if (is_null($storedProfile)) {
	echo "<form action=\"./\" method=\"post\">\n";
}
echo '<p>Source: ' . (isset($storedProfile) ? 'store' : 'attribute source');
echo "\n";
if (is_null($storedProfile)) {
	echo "<input type=\"submit\" name=\"storeProfile\" value=\"Store profile\"/>\n";
}
echo "</p>\n";
if (is_null($storedProfile)) {
	echo "</form>\n";
}
?>
<table>
<tr>
	<th>source</th>
	<th>uid</th>
	<th>displayName</th>
	<th>mail</th>
	<th>image</th>
	<th>ssh</th>
	<th>gpg</th>
</tr>
<?php
foreach(array_merge([$profile], $profile->getAttributeAssertions()) as $source => $assertion) {
	if (is_int($source) && $source) {
		continue;
	}
	$displayNames = $assertion->getDisplayNames();
	$mails = $assertion->getMails();
	$images = $assertion->getImages();
	$pgpKeys = $assertion->getPGPPublicKeys();
	$sshKeys = $assertion->getSSHPublicKeys();
	$count = max(
		sizeof($displayNames),
		sizeof($mails),
		sizeof($images),
		sizeof($pgpKeys),
		sizeof($sshKeys)
	);
?>
<tr<?php echo is_int($source) ? ' style="background:#eee;color:red"' : '' ?>>
	<th rowspan="<?php echo $count ?>"><?php echo htmlentities($source); ?></th>
	<td rowspan="<?php echo $count ?>"><?php echo htmlentities($assertion->getUniqueId()); ?></td>
<?php
	for($i=0;$i<$count;$i++) {
		$displayName = isset($displayNames[$i]) ? htmlentities($displayNames[$i]) : '';
		$mail = isset($mails[$i]) ? htmlentities($mails[$i]) : '';
		$image = isset($images[$i]) ? '<img src="'.htmlentities($images[$i]->toBytes()->serialize()).'" alt="">' : '';
		$sshKey = isset($sshKeys[$i]) ? htmlspecialchars($sshKeys[$i]->getHexFingerprint()) : '';
		$pgpKey = isset($pgpKeys[$i]) ? htmlspecialchars($pgpKeys[$i]->getKeyID()) : '';
?>
	<td><?php echo $displayName; ?></td>
	<td><?php echo $mail; ?></td>
	<td><?php echo $image; ?></td>
	<td><?php echo $sshKey; ?></td>
	<td><?php echo $pgpKey; ?></td>
</tr>
<tr>
<?php
	}
?>
</tr>
<?php } ?>
</table>
