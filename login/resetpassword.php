<?php
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
	if ($loggedIn == true) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
	
	if (isset($_GET['userid']) and isset($_GET['key'])) {
		$userid = $_GET['userid'];
		$key = $_GET['key'];
	}else{
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
	
	$query = "SELECT * FROM passwordresets WHERE userid = :uid AND `key` = :key";
	$stmt = $dbcon->prepare($query);
	$stmt->bindParam(':uid', $userid, PDO::PARAM_INT);
	$stmt->bindParam(':key', $key, PDO::PARAM_STR); 
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($stmt->rowCount() == 0) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
	
	if ($result['used'] == 1) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo getName(); ?> | Reset Password</title>
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
			$hAds = true;
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
		?>
		<div id="content">
			<br><br>
			<?php
				$currentTime = date('Y-m-d H:i:s');
				$to_time = strtotime($currentTime);
				$from_time = strtotime($result['date']);
				$timeSince =  round(abs($to_time - $from_time) / 60,2);
				if ($timeSince > 5) {
					header("Location: /");
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				
					if (isset($_POST['changepassword'])) {
						$password = $_POST['password1'];
						$passwordc = $_POST['password2'];
						if (isset($_GET['userid']) and isset($_GET['key'])) {
							$userid = $_GET['userid'];
							$key = $_GET['key'];
						}else{
							header("Location: /");
							include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
							exit;
						}
						
						$query = "SELECT * FROM passwordresets WHERE userid = :uid AND `key` = :key";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':uid', $userid, PDO::PARAM_INT);
						$stmt->bindParam(':key', $key, PDO::PARAM_STR); 
						$stmt->execute();
						$result = $stmt->fetch(PDO::FETCH_ASSOC);
						if ($stmt->rowCount() == 0) {
							header("Location: /");
							include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
							exit;
						}
						
						if ($result['used'] == 1) {
							header("Location: /");
							include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
							exit;
						}
						
						$currentTime = date('Y-m-d H:i:s');
						$to_time = strtotime($currentTime);
						$from_time = strtotime($result['date']);
						$timeSince =  round(abs($to_time - $from_time) / 60,2);
						if ($timeSince > 5) {
							header("Location: /");
							include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
							exit;
						}
						
						$error = false;
						if ($password != $passwordc and $error == false) {
							echo '<div class="alert alert-dismissible alert-danger">Passwords do not match.</div>';
							$error = true;
						}
						
						if (strlen($password) < 6 and $error == false) {
							echo '<div class="alert alert-dismissible alert-danger">Password is too short.</div>';
							$error = true;
						}
						
						if (strlen($password) > 42 and $error == false) {
							echo '<div class="alert alert-dismissible alert-danger">Passwords is too long.</div>';
							$error = true;
						}
						
						if ($error == false) {
							$salt = '$2a$07$'.uniqid(mt_rand(), true).'$';
							$hash = crypt($password, $salt);
							
							$stmt = $dbcon->prepare("UPDATE users SET password_salt = :salt WHERE id = :user;");
							$stmt->bindParam(':user', $userid, PDO::PARAM_INT);
							$stmt->bindParam(':salt', $salt, PDO::PARAM_STR);
							$stmt->execute();
										
							$stmt = $dbcon->prepare("UPDATE users SET password_hash = :hash WHERE id = :user;");
							$stmt->bindParam(':user', $userid, PDO::PARAM_INT);
							$stmt->bindParam(':hash', $hash, PDO::PARAM_STR);
							$stmt->execute();
							
							$stmt = $dbcon->prepare("UPDATE passwordresets SET used = 1 WHERE `key` = :key AND userid = :uid;");
							$stmt->bindParam(':key', $key, PDO::PARAM_STR);
							$stmt->bindParam(':uid', $userid, PDO::PARAM_INT);
							$stmt->execute();
							
							$stmt = $dbcon->prepare("DELETE FROM sessions WHERE userId = :uid;");
							$stmt->bindParam(':uid', $userid, PDO::PARAM_INT);
							$stmt->execute();
							
							header("Location: /auth.php");
						}
					}
				?>
				<div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3 well" style="border-style: solid;border-color: #bbb;background-color:#ffffff;">
				<h5 style="color:#444444;margin-top:0px;margin-bottom:5px;">Password Reset</h5>
				<p style="color:#bbbbbb">Enter a new password and confirm it.</p>
				<form method="post">
					<p><input type="password" class="form-control" name="password1" required="required" placeholder="Enter new password"></input></p>
					<p><input type="password" class="form-control" name="password2" required="required" placeholder="Confirm new password"></input></p>
					<p><button type="submit" class="btn btn-primary btn-sm" style="-webkit-box-shadow:none;box-shadow:none;width:100%" name="changepassword">Reset Password</button></p>
				</form>
			</div>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>