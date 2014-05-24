<?php require_once dirname(__DIR__) . '/src/_autoload.php';

/**
 *
 * @copyright Copyright (c) 2014, UNINETT
 */

foreach(\uninett\giza\secret\Secret::getSecretsForProfile() as $secret) {
?>

<div>
	<p><?php echo htmlspecialchars($secret->getName()); ?></p>
	<p>
		<a href="./secret/?uuid=<?php echo $secret->getUUID(); ?>&amp;action=view">view</a>
		<a href="./secret/?uuid=<?php echo $secret->getUUID(); ?>&amp;action=edit">edit</a>
	</p>
</div>

<?php } ?>
