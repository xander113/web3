<?php
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
	if ($loggedIn == false) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
	
	if ($auth_enable2fa == 0 or $auth_factorFinish > 0) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo getName(); ?> | Two Factor Authentication</title>
		<?php
			echo getHead();
		?>
		<style>
			h1, h2, h3, h4, h5, h6 {
				color: grey;
			}
		</style>
	</head>
	<body>
		<?php
			$tfa = true;
			$hAds = true;
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
		?>
		<div id="content">
			<br><br>
			<?php
				if (isset($_POST['login'])) {
					$code = $_POST['code'];
					$code = str_replace(" ", "", $code);
					$query = "SELECT * FROM users WHERE id = :id";
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT); 
					$stmt->execute(); 
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					$gAuth = new GoogleAuthenticator();
					if (!$gAuth->checkCode($result['authKey'], $code)) {
						echo '<div class="alert alert-dismissible alert-danger">Code is incorrect.</div>';
					}else{
						$query = "UPDATE sessions SET `factorFinish`=1 WHERE `sessionId`=:sessionId AND `userId`=:userID;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':sessionId', $auth_sessionId, PDO::PARAM_STR);
						$stmt->bindParam(':userID', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
					}
					
					header("Location: /");
				}
			?>
			<div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3 well" style="border-style: solid;border-color: #bbb;background-color:#ffffff;">
				<h5 style="color:#444444;margin-top:0px;margin-bottom:5px;">Two Factor Authentication</h5>
				<p style="color:#bbbbbb">Use your authentication mobile app to obtain your code required to login.</p>
				<form method="post">
					<p><input type="text" class="form-control" name="code" required="required" placeholder="Enter code here"></input></p>
					<p><button type="submit" class="btn btn-primary btn-sm" style="-webkit-box-shadow:none;box-shadow:none;width:100%" name="login">Authenticate</button></p>
				</form>
			</div>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>