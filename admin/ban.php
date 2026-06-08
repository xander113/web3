<?php
	$hAds = true;
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
	if ($loggedIn == false) {
		header("Location: /auth.php");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
	if ($user_rankId != 1 and $user_rankId != 2) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo getName(); ?> | Ban</title>
		<?php
			echo getHead();
		?>
	</head>
	<body>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
		?>
		<div id="content" >
			<?php
				if (isset($_POST)) {
					if (array_key_exists(sha1($auth_formCode), $_POST) and $user_rankId > 0 and $user_rankId != 3) {
						$username = $_POST['username'];
						$reason = $_POST['reason'];
						$duration = $_POST['duration'];
						
						echo '<div class="alert alert-dismissible alert-danger">';
						$error = false;
						if ($duration > 5) {
							$error = true;
							echo '<p>Invalid Duration</p>';
						}
						
						if ($username == $auth_uname) {
							$error = true;
							echo '<p>You can not ban yourself.</p>';
						}
						
						if (filter_var($duration, FILTER_VALIDATE_INT) == false) {
							$error = true;
							echo '<p>Invalid Duration</p>';
						}
						
						if (strlen($username) == 0) {
							$error = true;
							echo '<p>An username must be given.</p>';
						}
						
						if (strlen($username) > 20) {
							$error = true;
							echo '<p>The username specified is too long.</p>';
						}
						
						if (strlen($reason) == 0) {
							$error = true;
							echo '<p>A reason must be given.</p>';
						}
						if (strlen($reason) > 256) {
							$error = true;
							echo '<p>Your reason is too long.</p>';
						}
						if ($error == false) {
							// Check if the user exists.
							$stmt = $dbcon->prepare("SELECT * FROM users WHERE username=:uname;");
							$stmt->bindParam(':uname', $username, PDO::PARAM_STR);
							$stmt->execute();
							if ($stmt->rowCount() == 0) {
								$error = true;
								echo '<p>The user you are trying to ban does not exist.</p>';
							}
							$result = $stmt->fetch(PDO::FETCH_ASSOC);
							if ($result['banned'] == 1) {
								$error = true;
								echo '<p>The user you are trying to ban has already been banned. You can unban this user <a href="/profile.php?id='.$result['id'].'">here</a>.</p>';
							}
							if ($result['rank'] > 0) {
								$error = true;
								echo '<p>This user can not be banned.</p>';
							}
						}
						if ($error == false) {
							// Ban user
							// Set banned to 1.
							$query = "UPDATE `users` SET `banned`=1 WHERE `username`=:uname;";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':uname', $username, PDO::PARAM_STR);
							$stmt->execute();
							
							// Set ban type
							$query = "UPDATE `users` SET `bantype`=:type WHERE `username`=:uname;";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':uname', $username, PDO::PARAM_STR);
							$stmt->bindParam(':type', $duration, PDO::PARAM_STR);
							$stmt->execute();
							
							// Set ban reason
							$query = "UPDATE `users` SET `banreason`=:reason WHERE `username`=:uname;";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':uname', $username, PDO::PARAM_STR);
							$stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
							$stmt->execute();
							
							// Set ban time
							$query = "UPDATE `users` SET `bantime`=NOW() WHERE `username`=:uname;";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':uname', $username, PDO::PARAM_STR);
							$stmt->execute();
							
							echo 'User has been banned.';
						}
						echo '</div>';
					}
				}
			?>
			<div class="alert alert-danger">All bans are logged. Do not abuse this. Abusing this will lead into demotion and account deletion.</div>
			<h4 style="color:grey;">Ban User</h4>
					<form method="post">
						<?php
							if (isset($_GET['username'])) {
								$username = $_GET['username'];
								if (!is_array($username)) {
									echo '<input type="text" name="username" maxlength="20" placeholder="Username" value="'.htmlentities($_GET['username'], ENT_QUOTES, "UTF-8").'" class="form-control"></input>';
								}
							}else{
								echo '<input type="text" name="username" maxlength="20" placeholder="Username" class="form-control"></input>';
							}
						?>
						<textarea type="text" rows="5" maxlength="256" name="reason" placeholder="Reason (Be clear and actually explain why)" class="form-control"></textarea>
						<p>Duration :
							<select name="duration">
								<option value="1">Warning</option>
								<option value="2">1 Day</option>
								<option value="3">1 Week</option>
								<option value="4">1 Month</option>
								<option value="5">Forever</option>
							</select>
						</p>
						<button class="btn btn-default" name="<?php echo sha1($auth_formCode); ?>" id="<?php echo md5(sha1($auth_formCode)); ?>" type="submit">Ban User</button>
					</form>
			<?php
				if (!isset($_GET['username'])) {
					echo '<p><a href="/admin/">Go back</a></p>';
				}
			?>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>