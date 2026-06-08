<?php
	echo 'Graphictoria is under maintenance. Check back later.';
	$dbcon = null;
	exit;
	$currentTime = date('Y-m-d H:i:s');
	if (strpos($_SERVER['SCRIPT_NAME'], "sessions.php")) {
		header("Location: /");
		exit;
	}
	
	function getIP() {
		if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
			$_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
			return $_SERVER['REMOTE_ADDR'];
		}
	}
	
	$loggedIn = false;
	if (isset($_COOKIE['auth_uid']) && isset($_COOKIE['a_id'])) {
		$stmt = $dbcon->prepare('SELECT lastUsed, id, csrfToken, factorFinish, useragent, sessionId FROM sessions WHERE userId = :userId AND sessionId = :sessionId LIMIT 1;');
		$stmt->bindParam(':userId', $_COOKIE['auth_uid'], PDO::PARAM_INT);
		$stmt->bindParam(':sessionId', $_COOKIE['a_id'], PDO::PARAM_STR);
		$stmt->execute();
		$resultSession = $stmt->fetch(PDO::FETCH_ASSOC);
		$from_time = strtotime($resultSession['lastUsed']);
		$sessionId = $resultSession['id'];
		$to_time = strtotime($currentTime);
		$timeSince =  round(abs($to_time - $from_time) / 60,2);
		$sesexpired = false;
		if ($timeSince > 1440) {
			$sesexpired = true;
			$stmt = $dbcon->prepare('DELETE FROM sessions WHERE id=:id;');
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
		}
		
		
		if ($stmt->rowCount() == 0 or $sesexpired == true) {
			$loggedIn = false;
		}else{
			$loggedIn = true;
			$query = "SELECT * FROM users WHERE id = :id LIMIT 1";
			$stmt = $dbcon->prepare($query);
			$stmt->bindParam(':id', $_COOKIE['auth_uid'], PDO::PARAM_STR); 
			$stmt->execute(); 
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$auth_uname = $result['username'];
			$user_rankId = $result['rank'];
			$auth_uid = $result['id'];
			$user_joined = $result['joinDate'];
			$user_lastPost = $result['lastPost'];
			$user_lastSeen = $result['lastSeen'];
			$user_about = htmlentities($result['about'], ENT_QUOTES, "UTF-8");
			$user_coins = $result['coins'];
			$lastAward = $result['lastAward'];
			$auth_email = $result['email'];
			$auth_formCode = $resultSession['csrfToken'];
			$auth_userAgent = $resultSession['useragent'];
			$auth_lastidGen = $result['lastIDGen'];
			$auth_id = $result['authId'];
			$option_showImg = $result['imgp'];
			$auth_enable2fa = $result['2faEnabled'];
			$auth_secret = $result['authKey'];
			$auth_emailVerified = $result['emailverified'];
			$auth_factorFinish = $resultSession['factorFinish'];
			$auth_sessionId = $resultSession['sessionId'];
			$auth_gameKey = $result['gameKey'];
			$user_banned = $result['banned'];
			if ($result['hideStatus'] == 1 and $result['rank'] == 1) {
				$user_hideStatus = true;
			}else{
				$user_hideStatus = false;
			}
			if ($user_hideStatus == false) {
				$from_time = strtotime($user_lastSeen);
				$to_time = strtotime($currentTime);
				$timeSince =  round(abs($to_time - $from_time) / 60,2);
				if ($timeSince > 3) {
					$stmt = $dbcon->prepare("UPDATE users SET lastSeen = NOW() WHERE username = :user;");
					$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
					$stmt->execute();
				}
			}
			
			if ($result['inGame'] == 1) {
				$stmt = $dbcon->prepare("UPDATE users SET inGame = 0 WHERE username = :user;");
				$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
				$stmt->execute();
			}
			
			if (strlen($auth_formCode) == 0) {
				$loggedIn = false;
			}
		}
	}else{
		$loggedIn = false;
	}
	
	if ($loggedIn == true && strpos($_SERVER['SCRIPT_NAME'], "auth.php") == true) {
		header("Location: /");
		exit;
	}
	
	if ($loggedIn == true) {
		if ($auth_userAgent != $_SERVER['HTTP_USER_AGENT']) {
			$loggedIn = false;
		}
	}
	
	$allowed = true;
	if ($loggedIn == true) {
		if ($auth_enable2fa == 1) {
			if ($auth_factorFinish == 0) {
				if ($loggedIn == true and strpos($_SERVER['SCRIPT_NAME'], "twofactor.php") == false) {
					$allowed = false;
					ob_start();
					header("Location: /login/twofactor.php");
					exit;
				}
			}
		}
		
		if ($auth_emailVerified == 0 and $user_banned == 0) {
			if ($loggedIn == true and strpos($_SERVER['SCRIPT_NAME'], "verifyemail.php") == false) {
				$allowed = false;
				ob_start();
				header("Location: /login/verifyemail.php");
				exit;
			}
		}
		
		if ($user_banned == 1) {
			if ($loggedIn == true and strpos($_SERVER['SCRIPT_NAME'], "banned.php") == false) {
				$allowed = false;
				ob_start();
				header("Location: /account/banned.php");
				exit;
			}
		}
	}
	
	if ($loggedIn == true) {
		$IP = getIP();
		if ($result['lastIP'] != $IP) {
			$stmt = $dbcon->prepare("UPDATE users SET lastIP = :ip WHERE username = :user;");
			$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
			$stmt->bindParam(':ip', $IP, PDO::PARAM_STR);
			$stmt->execute();
		}
		
		$from_time = strtotime($lastAward);
		$to_time = strtotime($currentTime);
		$timeSince =  round(abs($to_time - $from_time) / 60,2);
		if ($timeSince > 1440) {
			$newCoins = $user_coins+10;
			$stmt = $dbcon->prepare("UPDATE users SET lastAward = NOW() WHERE username = :user;");
			$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
			$stmt->execute();
			
			$stmt = $dbcon->prepare("UPDATE users SET coins = :newCoins WHERE username = :user;");
			$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
			$stmt->bindParam(':newCoins', $newCoins, PDO::PARAM_STR);
			$stmt->execute();
		}
	}
	
	if ($loggedIn == false) {
		unset($_COOKIE['auth_uid']);
		unset($_COOKIE['session_id']);
		setcookie('auth_uid', '', time() - 3600, '/');
		setcookie('session_id', '', time() - 3600, '/');
		$option_showImg = 1;
		$user_ads = 0;
		$user_rankId = 0;
		$auth_formCode = sha1(getIP());
	}else{
		$from_time = strtotime($resultSession['lastUsed']);
		$to_time = strtotime($currentTime);
		$timeSince =  round(abs($to_time - $from_time) / 60,2);
		if ($timeSince > 60) {
			$stmt = $dbcon->prepare("UPDATE sessions SET lastUsed = NOW() WHERE sessionId = :sid;");
			$stmt->bindParam(':sid', $auth_sessionId, PDO::PARAM_STR);
			$stmt->execute();
		}
	}
	
	if ($allowed == false) {
		exit;
	}
?>