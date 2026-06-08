<?php
	if (strpos($_SERVER['SCRIPT_NAME'], "badges.php")) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
	if ($loggedIn == true) {
		// This awards badges or removes them.
		
		if ($user_rankId == 1) {
			// Check if the admin badge is owned
			$stmt = $dbcon->prepare("SELECT id FROM badges WHERE uid = :uid AND badgeId = 2");
			$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() == 0) {
				// Award badge
				$query = "INSERT INTO badges (`uid`, `badgeId`) VALUES (:uid, 2);";
				$stmt = $dbcon->prepare($query);
				$stmt->bindParam(':uid', $_COOKIE['auth_uid'], PDO::PARAM_INT);
				$stmt->execute();
			}
			
			// Check if the moderator badge is owned
			$stmt = $dbcon->prepare("SELECT id FROM badges WHERE uid = :uid AND badgeId = 3");
			$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() == 0) {
				// Award badge
				$query = "INSERT INTO badges (`uid`, `badgeId`) VALUES (:uid, 3);";
				$stmt = $dbcon->prepare($query);
				$stmt->bindParam(':uid', $_COOKIE['auth_uid'], PDO::PARAM_INT);
				$stmt->execute();
			}
		}
		
		if ($user_rankId == 2) {
			// Check if the moderator badge is owned
			$stmt = $dbcon->prepare("SELECT id FROM badges WHERE uid = :uid AND badgeId = 3");
			$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() == 0) {
				// Award badge
				$query = "INSERT INTO badges (`uid`, `badgeId`) VALUES (:uid, 3);";
				$stmt = $dbcon->prepare($query);
				$stmt->bindParam(':uid', $_COOKIE['auth_uid'], PDO::PARAM_INT);
				$stmt->execute();
			}
			
			// Remove admin badge if any
			$query = "DELETE FROM badges WHERE badgeId = 2 AND uid = :uid";
			$stmt = $dbcon->prepare($query);
			$stmt->bindParam(':uid', $_COOKIE['auth_uid'], PDO::PARAM_INT);
			$stmt->execute();
		}
		
		if ($user_rankId == 0) {
			// Remove staff badges if any
			$query = "DELETE FROM badges WHERE badgeId = 2 AND uid = :uid";
			$stmt = $dbcon->prepare($query);
			$stmt->bindParam(':uid', $_COOKIE['auth_uid'], PDO::PARAM_INT);
			$stmt->execute();
			
			$query = "DELETE FROM badges WHERE badgeId = 3 AND uid = :uid";
			$stmt = $dbcon->prepare($query);
			$stmt->bindParam(':uid', $_COOKIE['auth_uid'], PDO::PARAM_INT);
			$stmt->execute();
		}
		
		// Check if the member badge is owned
		$stmt = $dbcon->prepare("SELECT id FROM badges WHERE uid = :uid AND badgeId = 5");
		$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt->rowCount() == 0) {
			// Award badge
			$query = "INSERT INTO badges (`uid`, `badgeId`) VALUES (:uid, 5);";
			$stmt = $dbcon->prepare($query);
			$stmt->bindParam(':uid', $_COOKIE['auth_uid'], PDO::PARAM_INT);
			$stmt->execute();
		}
		
		// Get forum post count
		$postCount = getPostCount($auth_uid, $dbcon);
		$stmt = $dbcon->prepare("SELECT id FROM badges WHERE uid = :uid AND badgeId = 4");
		$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt->rowCount() == 0 and $postCount > 999) {
			// Award badge
			$query = "INSERT INTO badges (`uid`, `badgeId`) VALUES (:uid, 4);";
			$stmt = $dbcon->prepare($query);
			$stmt->bindParam(':uid', $_COOKIE['auth_uid'], PDO::PARAM_INT);
			$stmt->execute();
		}else{
			if ($postCount < 1000) {
				$query = "DELETE FROM badges WHERE badgeId = 4 AND uid = :uid";
				$stmt = $dbcon->prepare($query);
				$stmt->bindParam(':uid', $_COOKIE['auth_uid'], PDO::PARAM_INT);
				$stmt->execute();
			}
		}
		
		$stmt = $dbcon->prepare("SELECT id FROM badges WHERE uid = :uid AND badgeId = 7");
		$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt->rowCount() == 0 and $auth_uid < 101) {
			// Award badge
			$query = "INSERT INTO badges (`uid`, `badgeId`) VALUES (:uid, 7);";
			$stmt = $dbcon->prepare($query);
			$stmt->bindParam(':uid', $_COOKIE['auth_uid'], PDO::PARAM_INT);
			$stmt->execute();
		}else{
			if ($auth_uid > 100) {
				$query = "DELETE FROM badges WHERE badgeId = 7 AND uid = :uid";
				$stmt = $dbcon->prepare($query);
				$stmt->bindParam(':uid', $_COOKIE['auth_uid'], PDO::PARAM_INT);
				$stmt->execute();
			}
		}
	}
?>