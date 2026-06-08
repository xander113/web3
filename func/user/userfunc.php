<?php
	if (strpos($_SERVER['SCRIPT_NAME'], "userfunc.php")) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
	
	function getPostCount($userId, $dbcon) {
		$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
		$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		return $result['posts'];
	}
	
	function getImage($userSheet) {
		if ($userSheet['imgTime'] == 0) {
			return 'https://xdiscuss.net/func/user/getImage.php?id=def2&type=user';
		}else{
			return 'https://xdiscuss.net/func/user/getImage.php?id='.$userSheet['id'].'&type=user';
		}
	}
?>