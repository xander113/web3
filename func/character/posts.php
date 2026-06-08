<?php
	if (isset($_GET['page'])) {
		$page = $_GET['page'];
	}else{
		$page = 0;
	}
	if (isset($_POST['wear'])) {
		$catalogId = $_POST['wear'];
		
		if ($type == "colors") {
			header("Location: /user/character.php?type=".$type."&page=".$page);
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
			exit;
		}
		
		$stmt = $dbcon->prepare("SELECT * FROM wearing WHERE uid = :uid AND catalogid = :id");
		$stmt->bindParam(':id', $catalogId, PDO::PARAM_INT);
		$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			header("Location: /user/character.php?type=".$type."&page=".$page);
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
			exit;
		}
		
		
		$stmt = $dbcon->prepare("SELECT * FROM ownedItems WHERE catalogId = :id AND uid = :uid");
		$stmt->bindParam(':id', $catalogId, PDO::PARAM_INT);
		$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt->rowCount() == 0) {
			header("Location: /user/character.php?type=".$type."&page=".$page);
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
			exit;
		}
		
		$stmt = $dbcon->prepare("SELECT * FROM catalog WHERE id = :id");
		$stmt->bindParam(':id', $catalogId, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($result['deleted'] == 1) {
			header("Location: /user/character.php?type=".$type."&page=".$page);
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
			exit;
		}
		$stmt = $dbcon->prepare("SELECT * FROM wearing WHERE uid = :uid AND type = :type");
		$stmt->bindParam(':type', $type, PDO::PARAM_STR);
		$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
		$stmt->execute();
		$resultcheck = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($type == "hats") {
			if ($stmt->rowCount() == 5) {
				header("Location: /user/character.php?type=".$type."&page=".$page);
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
		}else{
			if ($stmt->rowCount() > 0) {
				$stmt = $dbcon->prepare("DELETE FROM wearing WHERE catalogId=:id AND uid=:user");
				$stmt->bindParam(':user', $auth_uid, PDO::PARAM_INT);
				$stmt->bindParam(':id', $resultcheck['catalogId'], PDO::PARAM_INT);
				$stmt->execute();
			}
		}
		if ($type == "hats" or $type == "gear" or $type == "faces" or $type == "heads") {
			$aprString = "http://xdiscuss.net/data/assets/".$type."/models/".$result['datafile'];
		}
		if ($type == "shirts" or $type == "pants" or $type == "tshirts") {
			$aprString = "http://xdiscuss.net/data/assets/".$type."/models/get.php?id=".$result['assetid'];
		}
		if ($type == "torso" or $type == "leftarm" or $type == "leftleg" or $type == "rightarm" or $type == "rightleg") {
			$aprString = "http://xdiscuss.net/data/assets/package/models/".$result['datafile'];
		}
		$stmt = $dbcon->prepare("INSERT INTO wearing (`uid`, `catalogid`, `type`, `aprString`) VALUES (:user, :itemid, :type, :aprString);");
		$stmt->bindParam(':user', $auth_uid, PDO::PARAM_INT);
		$stmt->bindParam(':itemid', $catalogId, PDO::PARAM_INT);
		$stmt->bindParam(':type', $type, PDO::PARAM_STR);
		$stmt->bindParam(':aprString', $aprString, PDO::PARAM_STR);
		$stmt->execute();
		
		$query = "INSERT INTO renders (`render_id`, `type`) VALUES (:uid, 'character');";
		$stmt = $dbcon->prepare($query);
		$stmt->bindParam(':uid', $_COOKIE['auth_uid'], PDO::PARAM_INT);
		$stmt->execute();
		
		$query = "INSERT INTO renders (`render_id`, `type`, `version`) VALUES (:uid, 'character', 2);";
		$stmt = $dbcon->prepare($query);
		$stmt->bindParam(':uid', $_COOKIE['auth_uid'], PDO::PARAM_INT);
		$stmt->execute();
		
		header("Location: /user/character.php?type=".$type."&page=".$page);
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
	
	if (isset($_POST['unwear'])) {
		$catalogId = $_POST['unwear'];
		$stmt = $dbcon->prepare("DELETE FROM wearing WHERE catalogId=:id AND uid=:user");
		$stmt->bindParam(':user', $auth_uid, PDO::PARAM_INT);
		$stmt->bindParam(':id', $catalogId, PDO::PARAM_INT);
		$stmt->execute();
		
		$query = "INSERT INTO renders (`render_id`, `type`) VALUES (:uid, 'character');";
		$stmt = $dbcon->prepare($query);
		$stmt->bindParam(':uid', $_COOKIE['auth_uid'], PDO::PARAM_INT);
		$stmt->execute();
		
		$query = "INSERT INTO renders (`render_id`, `type`, `version`) VALUES (:uid, 'character', 2);";
		$stmt = $dbcon->prepare($query);
		$stmt->bindParam(':uid', $_COOKIE['auth_uid'], PDO::PARAM_INT);
		$stmt->execute();
		
		header("Location: /user/character.php?type=".$type."&page=".$page);
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
?>