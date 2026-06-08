<?php
	include_once $_SERVER['DOCUMENT_ROOT'].'/core/func/includes.php';
	if ($GLOBALS['loggedIn'] == true) {
		header("Location: https://drive.google.com/uc?export=download&id=0B3SkXcIonqWiYUdRSzg4TzIydHc");
	}else{
		header("Location: /games");
	}
?>