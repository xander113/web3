<?php
	if (strpos($_SERVER['SCRIPT_NAME'], "db.php")) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
?>