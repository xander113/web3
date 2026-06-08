<?php
	if (strpos($_SERVER['SCRIPT_NAME'], "main.php")) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
	
	function getName() {
		return 'Graphictoria';
	}
	
	function getCodename() {
		return 'JanPhanter <span class="glyphicon glyphicon-heart"></span>';
	}
?>