<?php
	unset($_COOKIE['auth_uid']);
	unset($_COOKIE['session_id']);
	setcookie('auth_uid', '', time() - 3600, '/');
	setcookie('session_id', '', time() - 3600, '/');
	
	if (isset($_SERVER['HTTP_REFERER'])) {
		header("Location: ".$_SERVER['HTTP_REFERER']);
	}else{
		header("Location: /");
	}
?>