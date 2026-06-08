<?php
	if (strpos($_SERVER['SCRIPT_NAME'], "navigation2.php")) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
	
	if (!isset($tfa)) {
		echo '<nav class="alert" style="padding:0px;webkit box-shadow:none;max-height:25px;min-height:0px;background-color:#212121;border-radius:0px;margin-bottom:0px;">
	<div class="container">
		<a style="font-weight:normal;color:white;padding-top:3px;padding-bottom:0px;" href="/profile.php?id='.$auth_uid.'">Profile</a> |
		<a style="font-weight:normal;color:white;padding-top:3px;padding-bottom:0px;" href="/groups">Groups</a> |
		<a style="font-weight:normal;color:white;padding-top:3px;padding-bottom:0px;" href="/games/newserver.php">Create new Server</a> |
		<a style="font-weight:normal;color:white;padding-top:3px;padding-bottom:0px;" href="/catalog/upload.php">Upload new Item</a>
	</div>
</nav>';
	}
?>