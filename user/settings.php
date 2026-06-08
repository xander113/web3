<?php
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
	if ($loggedIn == false) {
		header("Location: /auth.php");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo getName(); ?> | Settings</title>
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
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
		?>
		<div id="content">
			<?php
				if (isset($_POST['savetext'])) {
					$text = $_POST['text'];
					if (strlen($text) > 256) {
						echo '<div class="alert alert-dismissible alert-danger">Your text can not be longer than 256 characters.</div>';
					}else{
						$query = "UPDATE users SET `about`=:text WHERE `username`=:uname;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':text', $text, PDO::PARAM_STR);
						$stmt->bindParam(':uname', $auth_uname, PDO::PARAM_STR);
						$stmt->execute();
						echo '<div class="alert alert-dismissible alert-success">Text saved!</div>';
					}
				}
				
				if (isset($_POST['hideStatus'])) {
					if ($user_rankId == 1) {
						$query = "SELECT * FROM users WHERE id = :id";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT); 
						$stmt->execute(); 
						$result = $stmt->fetch(PDO::FETCH_ASSOC);
						
						if ($result['hideStatus'] == 1) {
							$query = "UPDATE users SET `hideStatus`=0 WHERE `id`=:uid;";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
							$stmt->execute();
							echo '<div class="alert alert-dismissible alert-success">You are now no longer invisible to users.</div>';
						}else{
							$query = "UPDATE users SET `hideStatus`=1 WHERE `id`=:uid;";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
							$stmt->execute();
							echo '<div class="alert alert-dismissible alert-success">You are now invisible to users.</div>';
						}
					}
				}
				
				if (isset($_POST)) {
					$string3 = sha1(md5(sha1(md5(sha1($auth_formCode)))));
					$string4 = md5(sha1(md5(sha1($auth_formCode))));
					
					if (array_key_exists($string3, $_POST)) {
						$gAuth = new GoogleAuthenticator();
						$code = $gAuth->generateSecret();
						if ($auth_enable2fa == 0) {
							$query = "UPDATE users SET `2faEnabled`=1 WHERE `id`=:uid;";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
							$stmt->execute();
							
							$query = "UPDATE users SET `authKey`=:code WHERE `id`=:uid;";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':code', $code, PDO::PARAM_STR);
							$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
							$stmt->execute();
							
							$query = "UPDATE sessions SET `factorFinish`=1 WHERE `sessionId`=:sessionId AND `userId`=:userID;";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':sessionId', $auth_sessionId, PDO::PARAM_STR);
							$stmt->bindParam(':userID', $auth_uid, PDO::PARAM_INT);
							$stmt->execute();
						}
						
						header("Location: /user/settings.php?authOn");
					}
					
					if (isset($_GET['authOn'])) {
						echo '<script>
							$(document).ready(function() {
								$("#securityToggle").click();
							})
						</script>';
						echo '<div class="alert alert-dismissible alert-warning">Enabled two factor authentication. Please set it up now.</div>';
					}
					
					if (array_key_exists($string4, $_POST)) {
						if ($user_rankId == 0) {
							$query = "UPDATE users SET `2faEnabled`=0 WHERE `id`=:uid;";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
							$stmt->execute();
							$auth_enable2fa = 0;
							echo '<script>
							$(document).ready(function() {
								$("#securityToggle").click();
							})
						</script>';
							echo '<div class="alert alert-dismissible alert-warning">Disabled two factor authentication.</div>';
						}else{
							echo '<script>
							$(document).ready(function() {
								$("#securityToggle").click();
							})
						</script>';
							echo '<div class="alert alert-dismissible alert-warning">Staff can not disable this.</div>';
						}
					}
				}
				
				if (isset($_POST['changeEmail'])) {
					$new_email = $_POST['nEmail'];
					$current_passwd = $_POST['cPasswd'];
					$error = false;
					
					if (strlen($new_email) == 0 and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">Please enter your new e-mail.</div>';
					}
					
					if (strlen($new_email) > 128 and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">E-Mail can not be longer than 128 characters.</div>';
					}
					
					if (!filter_var($new_email, FILTER_VALIDATE_EMAIL) and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">Invalid E-Mail specified.</div>';
					}
					
					// Verify password
					$query = "SELECT * FROM users WHERE id = :id";
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT); 
					$stmt->execute(); 
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					$hash_currentPassword = $result['password_hash'];
					$auth_hashCurrent = crypt($current_passwd, $result['password_salt']);
					
					if ($hash_currentPassword != $auth_hashCurrent and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">The password you specified is incorrect.</div>';
					}
					
					if ($error == false) {
						$query = "UPDATE users SET `email`=:email WHERE `id`=:uid;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':email', $new_email, PDO::PARAM_STR);
						$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
						
						echo '<div class="alert alert-dismissible alert-success">E-Mail changed!</div>';
					}
				}
				
				if (isset($_POST['changePassword'])) {
					$currentPassword = $_POST['curPassword'];
					$newPassword1 = $_POST['nPassword1'];
					$newPassword2 = $_POST['nPassword2'];
					
					// Verify if password is correct.
					$query = "SELECT * FROM users WHERE id = :id";
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT); 
					$stmt->execute(); 
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					$error = false;
					$hash_currentPassword = $result['password_hash'];
					$auth_hash = crypt($newPassword1, $result['password_salt']);
					$auth_hashCurrent = crypt($currentPassword, $result['password_salt']);
					
					if (md5($currentPassword) !== $result['password'] and $auth_hashCurrent !== $hash_currentPassword) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">Your current password is incorrect.</div>';
					}
					if (md5($newPassword1) == $result['password'] or $auth_hash == $hash_currentPassword) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">You can not change your password to your current password.</div>';
					} 
					if (strlen($newPassword1) < 6) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">Your password must be at least 6 characters long.</div>';
					}
					if (strlen($newPassword2) < 6) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">Your password must be at least 6 characters long.</div>';
					}
					if (strlen($newPassword1) > 45) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">Your password can not be longer than 45 characters.</div>';
					}
					if (strlen($newPassword2) > 45) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">Your password can not be longer than 45 characters.</div>';
					}
					if ($newPassword1 !== $newPassword2) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">Your password and password confirmation do not match.</div>';
					}
					
					if ($error == false) {
						// Change password
						$salt = '$2a$07$'.uniqid(mt_rand(), true).'$';
						$hash = crypt($newPassword1, $salt);
						
						$query = "UPDATE users SET `password_salt`=:salt WHERE `id`=:uid;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':salt', $salt, PDO::PARAM_STR);
						$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
						
						$query = "UPDATE users SET `password_hash`=:hash WHERE `id`=:uid;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':hash', $hash, PDO::PARAM_STR);
						$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
						
						$query = "UPDATE users SET `passwordVersion`=2 WHERE `id`=:uid;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
						
						$IP = getIP();
						$query = "UPDATE users SET `passwordChangeIP`=:ip WHERE `id`=:uid;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':ip', $IP, PDO::PARAM_STR);
						$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
						
						$query = "UPDATE users SET `passwordChangeDate`=NOW() WHERE `id`=:uid;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
						
						$query = "UPDATE users SET `lastIP`=NULL WHERE `id`=:uid;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
						
						// Kill all sessions.
						$query = "DELETE FROM sessions WHERE userId = :uid;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
						
						header("Location: /auth.php");
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
				}
			?>
			<ul class="nav nav-tabs" style="background-color:rgba(255,255,255,.84);">
				<li class="active"><a href="#profile" data-toggle="tab">Profile</a></li>
				<li><a href="#account" data-toggle="tab">Account</a></li>
				<li><a id="securityToggle" href="#security" data-toggle="tab">Security</a></li>
			</ul>
			<div id="contentTab" class="tab-content">
				<div class="tab-pane fade active in" id="profile">
					<div class="well">
						<h4>About Me</h4><p>You can set your text here which will appear on your <a href="/profile.php?id=<?php echo $auth_uid; ?>">profile</a>.</p>
						<form method="post">
							<textarea type="text" rows="5" maxlength="256" class="form-control" name="text" placeholder="Insert your text here"><?php echo $user_about; ?></textarea><br>
							<button type="submit" class="btn btn-primary FullWidth" name="savetext">Save</button>
						</form>
					</div>
				</div>
				<div class="tab-pane fade in" id="account">
					<div class="well">
						<h4>Change Password</h4><p>You can change your password here, passwords changed through this can be up to 45 characters.</p>
						<form method="post">
							<input type="password" rows="5" maxlength="45" class="form-control" name="curPassword" placeholder="Current Password"><br>
							<input type="password" rows="5" maxlength="45" class="form-control" name="nPassword1" placeholder="New Password"><br>
							<input type="password" rows="5" maxlength="45" class="form-control" name="nPassword2" placeholder="Repeat new Password"><br>
							<button type="submit" class="btn btn-primary FullWidth" name="changePassword">Change password</button>
						</form>
					</div>
					<?php
						if ($user_rankId == 1) {
							echo '<div class="well"><h4>Hide Status</h4><p>As an admin, you can hide your online status.</p><form method="post"><button name="hideStatus" class="btn btn-success" type="submit">Unhide or hide online status</button></div>';
						}
					?>
				</div>
				<div class="tab-pane fade in" id="security">
					<div class="well">
						<h4>Change E-Mail</h4>
						<p>Forgot your email or just need to change it? You're at the right place! Do this before enabling two factor authentication if you are unsure.</p>
						<form method="post">
							<p><input type="email" placeholder="New E-Mail" name="nEmail" maxlength="128" class="form-control"></input></p>
							<p><input type="password" placeholder="Current Password" name="cPasswd" maxlength="45" class="form-control"></input></p>
							<button type="submit" name="changeEmail" class="btn btn-primary">Change E-Mail</button>
						</form>
					</div>
					<div class="well">
						<h4>Two Factor Authentication</h4><p>This feature ensures that <b>you</b> are accessing <b>your</b> account by requiring you a code needed to login.</p>
						<?php
							if ($auth_enable2fa == 0) {
								echo '<button style="margin:5px 0px;display:inline;" href="#" data-toggle="modal" data-target="#enable2fa" class="btn btn-success">Enable Two Factor Authentication</button>';
								echo '<div class="modal fade" id="enable2fa" tabindex="-1" role="dialog" aria-labelledby="e2">
								   <div class="modal-dialog">
									  <div class="modal-content">
										 <div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
											<h4 class="modal-title" id="e2"><font color="grey">Warning!</font></h4>
										 </div>
										 <div class="modal-body">
											<p><b>You will need Google Authenticator to use two factor authentication. Please ensure you scan the QR code or manually enter the code into it before logging out.</p>
										 </div>
										<div class="modal-footer">
											<form method="post"><button style="margin:5px 0px;" type="submit" name="'.sha1(md5(sha1(md5(sha1($auth_formCode))))).'" class="btn btn-success">Enable Two Factor Authentication</button></form>
										</div>
									 </div>
								   </div>
								</div>';
							}else{
								$gAuth = new GoogleAuthenticator();
								$query = "SELECT * FROM users WHERE id = :id";
								$stmt = $dbcon->prepare($query);
								$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT); 
								$stmt->execute(); 
								$result = $stmt->fetch(PDO::FETCH_ASSOC);
								echo 'Your secret key is <b>'.$result['authKey'].'</b><br>You can also scan the QR code to add your secret key automatically.<br>';
								echo '<img src="'.$gAuth->getURL($auth_uname, 'xdiscuss.net', $result['authKey']).'"><br>';
								echo '<b>You will be asked for the code generated by this key the next time you login.</b><br>';
								echo '<button style="margin:5px 0px;display:inline;" href="#" data-toggle="modal" data-target="#disable2fa" class="btn btn-danger"><span class="glyphicon glyphicon-remove"></span> Disable Two Factor Authentication</button>';
								echo '<div class="modal fade" id="disable2fa" tabindex="-1" role="dialog" aria-labelledby="d2">
								   <div class="modal-dialog">
									  <div class="modal-content">
										 <div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
											<h4 class="modal-title" id="d2"><font color="grey">Warning!</font></h4>
										 </div>
										 <div class="modal-body">
											<p>Disabling Two Factor Authentication will open a hole in your account\'s security!<br><b>Only do this if you know what you are doing!</b></p>
										 </div>
										<div class="modal-footer">
											<form method="post"><button style="margin:5px 0px;" type="submit" name="'.md5(sha1(md5(sha1($auth_formCode)))).'" class="btn btn-danger"><span class="glyphicon glyphicon-remove"></span> Disable Two Factor Authentication</button></form>
										</div>
									 </div>
								   </div>
								</div>';
							}
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>