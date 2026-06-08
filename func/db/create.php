<?php
	if (strpos($_SERVER['SCRIPT_NAME'], "create.php")) {
		header("Location: /");
		exit;
	}
	
	try{
		$dbcon = new PDO('mysql:host='.$db_host.';port='.$db_port.';dbname='.$db_name.'', $db_user, $db_passwd);
		$dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
		$dbcon->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}catch (exception $e){
		echo 'We could not connect to the database. Please try again shortly as we sent a team of potatoes to fix this issue.';
		exit;
	}
?>