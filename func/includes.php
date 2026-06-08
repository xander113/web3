<?php
	error_reporting(0);
	ob_start();
	// Cookie security
	ini_set("session.cookie_httponly", 1);
	
	// Debugging
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	if (strpos($_SERVER['SCRIPT_NAME'], "includes.php")) {
		header("Location: /");
		exit;
	}
	if ($_SERVER['SERVER_PORT'] == 80) {
		header("Location: https://xdiscuss.net".$_SERVER['REQUEST_URI']);
		exit;
	}
	
	if ($_SERVER['HTTP_HOST'] != "xdiscuss.net") {
		header("Location: https://xdiscuss.net".$_SERVER['REQUEST_URI']);
		exit;
	}
	date_default_timezone_set("Europe/Brussels");
	ini_set('session.gc_maxlifetime', 604800);
	ini_set('session.cookie_lifetime', 604800);
	session_set_cookie_params(604800);
	session_start();
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/conf/db.php';
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/create.php';
	if (!isset($ignoreSession)) {
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/auth/sessions.php';
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/auth/crypt.php';
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/conf/main.php';
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/head.php';
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/user/userfunc.php';
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/user/filter.php';
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/user/badges.php';
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/google/GoogleAuthenticator.php';
	}
?>