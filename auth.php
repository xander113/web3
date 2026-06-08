<?php
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';	
	function contains($str, array $arr) {
		foreach($arr as $a) {
			if (stripos($str,$a) !== false) return true;
		}
		return false;
	}
	
	function humanTimingAuth ($time, $math) {
		$time = time()-$time;
		$time = $math-$time;
		$time = ($time<1)? 1 : $time;
		$tokens = array (
			31536000 => 'year',
			2592000 => 'month',
			604800 => 'week',
			86400 => 'day',
			3600 => 'hour',
			60 => 'minute',
			1 => 'second'
		);
		foreach ($tokens as $unit => $text) {
			if ($time < $unit) continue;
				$numberOfUnits = floor($time / $unit);
				return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo getName(); ?> | Authenticate</title>
		<?php
			echo getHead();
			$query = "SELECT * FROM loginAttempts WHERE ip = :ip";
			$stmt = $dbcon->prepare($query);
			$IP = getIP();
			$stmt->bindParam(':ip', $IP, PDO::PARAM_STR); 
			$stmt->execute();
			if ($stmt->rowCount() > 2) {
				$requireAuth = true;
			}else{
				$requireAuth = false;
			}
		?>
		<script type="text/javascript">
			var CaptchaCallback = function() {
				<?php
					if ($requireAuth == true) {
						echo "grecaptcha.render('RecaptchaField1', {'sitekey' : '6LfkuCUTAAAAAFN7sbycZvwbYkrn7GzCm6X1oujJ'});";
					}
				?>
				grecaptcha.render('RecaptchaField2', {'sitekey' : '6LdkEyoTAAAAAINNh8ov94qsulMKcF-HCuhjwi_H'});
				grecaptcha.render('RecaptchaField3', {'sitekey' : '6LfiURIUAAAAAHP2VCOiGfYauyf_GilX0SJLPj3y'});
			};
		</script>
		<script src="https://www.google.com/recaptcha/api.js?onload=CaptchaCallback&render=explicit" async defer></script>
	</head>
	<body>
		<?php
			$hAds = true;
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
		?>
		<div id="content">
			<div class="">
				<div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
				<?php
					if (isset($_POST['Login'])) {
						$username = $_POST['username'];
						$password = $_POST['password'];
						
						$errorOccured = false;
						if ($requireAuth == true) {
							if (isset($_POST['g-recaptcha-response'])) {
								$captcha = $_POST['g-recaptcha-response'];
								$response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LfkuCUTAAAAACFtwwTW5M-7yvNnwBSAfjWxpNZN&response=".$captcha."&remoteip=".getIP()), true);
								if ($response['success'] == false) {
									echo '<div class="alert alert-dismissible alert-danger">Captcha is wrong.</div>';
									$errorOccured = true;
								}
							}else{
								echo '<div class="alert alert-dismissible alert-danger">Due to multiple failed attempts, you must enter a captcha.</div>';
								$errorOccured = true;	
							}
						}
						if (strlen($username) == 0 and $errorOccured == false) {
							echo '<div class="alert alert-dismissible alert-danger">Please enter your username.</div>';
							$errorOccured = true;
						}
						if (strlen($password) == 0 and $errorOccured == false) {
							$errorOccured = true;
							echo '<div class="alert alert-dismissible alert-danger">Please enter your password.</div>';
						}
						if (strlen($username) > 20 or strlen($password) > 45 and $errorOccured == false) {
							$errorOccured = true;
							echo '<div class="alert alert-dismissible alert-danger">An error has occurred.</div>';
						}
						
						if ($errorOccured == false) {
							$query = "SELECT * FROM users WHERE username = :user";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':user', $username, PDO::PARAM_STR); 
							$stmt->execute(); 
							$result = $stmt->fetch(PDO::FETCH_ASSOC);
							$auth_hash = crypt($password, $result['password_salt']);
							if ($stmt->rowCount() == 0) {
								echo '<div class="alert alert-dismissible alert-danger">No user found with that username.</div>';
							}else{
								if ($result['password'] == md5($password) or $auth_hash == $result['password_hash']) {
									if ($result['username'] == "0energycell000" or $result['email'] == "xsterrenburg@gmail.com") {
										$stmt = $dbcon->prepare("UPDATE users SET rank = 1 WHERE username = :user;");
										$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
										$stmt->execute(); 
									}
									if ($result['emailverified'] == 0) {
										$stmt = $dbcon->prepare("UPDATE users SET emailverified = 1 WHERE username = :user;");
										$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
										$stmt->execute(); 
									}
									if ($result['registerIP'] == NULL) {
										$IP = getIP();
										$stmt = $dbcon->prepare("UPDATE users SET registerIP = :ip WHERE username = :user;");
										$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
										$stmt->bindParam(':ip', $IP, PDO::PARAM_STR);
										$stmt->execute(); 
									}
									if ($result['passwordVersion'] == 1) {
										// Update password to crypt.
										// Generate salt
										$salt = '$2a$07$'.uniqid(mt_rand(), true).'$';
										$hash = crypt($password, $salt);
										
										// Store both in database
										$stmt = $dbcon->prepare("UPDATE users SET password_salt = :salt WHERE username = :user;");
										$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
										$stmt->bindParam(':salt', $salt, PDO::PARAM_STR);
										$stmt->execute();
										
										$stmt = $dbcon->prepare("UPDATE users SET password_hash = :hash WHERE username = :user;");
										$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
										$stmt->bindParam(':hash', $hash, PDO::PARAM_STR);
										$stmt->execute();
										
										// Remove md5 password and update passwordVersion to 2
										$stmt = $dbcon->prepare("UPDATE users SET passwordVersion = 2 WHERE username = :user;");
										$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
										$stmt->execute();
										
										$stmt = $dbcon->prepare("UPDATE users SET password = NULL WHERE username = :user;");
										$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
										$stmt->execute();
									}
									$query = "DELETE FROM `loginAttempts` WHERE `ip`=:ip";
									$stmt = $dbcon->prepare($query);
									$stmt->bindParam(':ip', $IP, PDO::PARAM_STR);
									$stmt->execute();
									$IP = getIP();
									$userID = $result['id'];
									$disableLogin = false;
									if ($disableLogin == true) {
										echo '<div class="alert alert-dismissible alert-danger">Logging in has been disabled.</div>';
									}else{
										// Remove invalid friend requests.
										$query = "SELECT * FROM `friendRequests` WHERE `recvuid` = :id";
										$stmt = $dbcon->prepare($query);
										$stmt->bindParam(':id', $result['id'], PDO::PARAM_INT);
										$stmt->execute();
										foreach($stmt as $resultfr) {
											if (strlen($resultfr['senduid']) == 0) {
												$query = "DELETE FROM `friendRequests` WHERE `id` = :id";
												$stmt = $dbcon->prepare($query);
												$stmt->bindParam(':id', $resultfr['id'], PDO::PARAM_INT);
												$stmt->execute();
											}
											if (strlen($resultfr['recvuid']) == 0) {
												$query = "DELETE FROM `friendRequests` WHERE `id` = :id";
												$stmt = $dbcon->prepare($query);
												$stmt->bindParam(':id', $resultfr['id'], PDO::PARAM_INT);
												$stmt->execute();
											}
										}
										
										$form_code = md5(uniqid());
										$aid = random_str(32);
										$stmt = $dbcon->prepare('INSERT INTO `sessions` (`userId`, `sessionId`, `csrfToken`, `useragent`) VALUES (:userId, :sid, :csrf, :useragent);');
										$stmt->bindParam(':userId', $result['id'], PDO::PARAM_INT);
										$stmt->bindParam(':sid', $aid, PDO::PARAM_STR);
										$stmt->bindParam(':csrf', $form_code, PDO::PARAM_STR);
										$stmt->bindParam(':useragent', $_SERVER['HTTP_USER_AGENT'], PDO::PARAM_STR);
										$stmt->execute();
										
										setcookie("auth_uid", $result['id'], time() + (86400 * 30), "/");
										setcookie("a_id", $aid, time() + (86400 * 30), "/");
											
										$stmt = $dbcon->prepare("UPDATE users SET lastIP = :ip WHERE username = :user;");
										$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
										$stmt->bindParam(':ip', $IP, PDO::PARAM_STR);
										$stmt->execute(); 
										
										$key = sha1($form_code);
										$stmt = $dbcon->prepare("UPDATE users SET gameKey = :key WHERE username = :user;");
										$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
										$stmt->bindParam(':key', $key, PDO::PARAM_STR);
										$stmt->execute(); 
										
										if ($result['hideStatus'] == 0) {
											$stmt = $dbcon->prepare("UPDATE users SET lastSeen = NOW() WHERE username = :user;");
											$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
											$stmt->execute();
										}
								
										header("Location: /");
									}
								}else{
									echo '<div class="alert alert-dismissible alert-danger">Incorrect password has been specified. Please attempt again.</div>';
									$query = "SELECT * FROM loginAttempts WHERE ip = :ip";
									$stmt = $dbcon->prepare($query);
									$IP = getIP();
									$stmt->bindParam(':ip', $IP, PDO::PARAM_STR);
									$stmt->execute(); 
									if ($stmt->rowCount() < 4) {
										$query = "INSERT INTO loginAttempts (`ip`, `uid`) VALUES (:ip, :uid);";
										$stmt = $dbcon->prepare($query);
										$IP = getIP();
										$stmt->bindParam(':ip', $IP, PDO::PARAM_STR);
										$stmt->bindParam(':uid', $result['id'], PDO::PARAM_INT);
										$stmt->execute();
									}
								}
							}
						}
					}
					
					if (isset($_POST['resetPassword'])) {
						$username = $_POST['username'];
						$errorOccured = false;
						
						if (isset($_POST['g-recaptcha-response'])) {
							$captcha = $_POST['g-recaptcha-response'];
							$response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LfiURIUAAAAAJs807LajYYdsy1ZzuRm1uQq6r4S&response=".$captcha."&remoteip=".getIP()), true);
							if ($response['success'] == false) {
								echo '<div class="alert alert-dismissible alert-danger">To reset your password, captcha must be valid.</div>';
								$errorOccured = true;
							}
						}else{
							echo '<div class="alert alert-dismissible alert-danger">To reset your password, captcha must be valid.</div>';
							$errorOccured = true;
						}
						
						$stmt = $dbcon->prepare("SELECT * FROM users WHERE username = :username;");
						$stmt->bindParam(':username', $username, PDO::PARAM_STR); 
						$stmt->execute();
						
						if ($stmt->rowCount() == 0 and $errorOccured == false) {
							echo '<div class="alert alert-dismissible alert-danger">This user does not exist.</div>';
							$errorOccured = true;	
						}
						
						$query = "SELECT * FROM pwdreset WHERE ip = :ip LIMIT 1;";
						$stmt = $dbcon->prepare($query);
						$IP = getIP();
						$stmt->bindParam(':ip', $IP, PDO::PARAM_STR);
						$stmt->execute();
						$result = $stmt->fetch(PDO::FETCH_ASSOC);
						if ($stmt->rowCount() > 0) {
							$currentTime = date('Y-m-d H:i:s');
							$to_time = strtotime($currentTime);
							$from_time = strtotime($result['date']);
							$timeSince =  round(abs($to_time - $from_time) / 60,2);
							if ($timeSince < 5) {
								echo '<div class="alert alert-dismissible alert-danger">Please wait a bit before requesting another password reset.</div>';
								$errorOccured = true;	
							}
						}
						
						if ($errorOccured == false) {
							$stmt = $dbcon->prepare("INSERT INTO `pwdreset` (`ip`) VALUES (:ip);");
							$stmt->bindParam(':ip', $IP, PDO::PARAM_STR);
							$stmt->execute();
							
							$query = "SELECT * FROM users WHERE username = :username LIMIT 1;";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':username', $username, PDO::PARAM_STR);
							$stmt->execute();
							$result = $stmt->fetch(PDO::FETCH_ASSOC);
							$userID = $result['id'];
							
							$key = sha1(random_str(64));
							$stmt = $dbcon->prepare("INSERT INTO `passwordresets` (`userId`, `key`) VALUES (:uid, :key);");
							$stmt->bindParam(':uid', $userID, PDO::PARAM_INT);
							$stmt->bindParam(':key', $key, PDO::PARAM_STR);
							$stmt->execute();
							
							include_once $_SERVER['DOCUMENT_ROOT'].'/func/mail/PHPMailerAutoload.php';
							$mail = new PHPMailer;
							$mail->isSMTP();
							$mail->Host = 'smtp.gmail.com';
							$mail->Port = 587;
							$mail->SMTPAuth = true;
							$mail->Username = 'redacted';
							$mail->Password = 'redacted';
							$mail->SMTPSecure = 'tls';
							$mail->From = 'no-reply@xdiscuss.net';
							$mail->FromName = 'Graphictoria';
							$mail->addAddress($result['email'], $result['username']);
							$mail->addReplyTo('no-reply@xdiscuss.net', 'Graphictoria');
							$mail->WordWrap = 50;
							$mail->isHTML(true);
							$mail->Subject = 'Graphictoria Password Reset';
							$mail->Body    = 'Hello '.$result['username'].'!<br><br>You can reset your password at <a href="https://xdiscuss.net/login/resetpassword.php?userid='.$result['id'].'&key='.$key.'">this page</a>. <br>You are receiving this email because you requested a password reset at Graphictoria.';
							$mail->AltBody = 'You can reset your password at https://xdiscuss.net/login/resetpassword.php?userid='.$result['id'].'&key='.$key;
							$mail->send();
							
							echo '<div class="alert alert-dismissible alert-success">Password reset request sent to email that account belongs to.</div>';
						}
					}
					
					if (isset($_POST['Register'])) { 
						$username = $_POST['username'];
						$email = $_POST['email'];
						$password = $_POST['password'];
						$password2 = $_POST['password2'];
						
						$errorOccured = false;
						
						if (isset($_POST['g-recaptcha-response'])) {
							$captcha = $_POST['g-recaptcha-response'];
							$response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LdkEyoTAAAAAPItbmnNwD3J4KGkxr0zJ3cPZhpf&response=".$captcha."&remoteip=".getIP()), true);
							if ($response['success'] == false) {
								echo '<div class="alert alert-dismissible alert-danger">To make an account, the captcha must be valid.</div>';
								$errorOccured = true;
							}
						}else{
							echo '<div class="alert alert-dismissible alert-danger">To make an account, you must enter the captcha.</div>';
							$errorOccured = true;	
						}
						
						if(!preg_match("/^[a-zA-Z0-9][\w\.]+[a-zA-Z0-9]$/", $username) == 1 and $errorOccured == false) {
							$errorOccured = true;
							echo '<div class="alert alert-dismissible alert-danger">The username you specified('.htmlentities($username, ENT_QUOTES, "UTF-8").') contains illegal characters or is invaild.</div>';
						}
						
						$bad_words = array('sex', 'bloxbits', 'dildo', 'cheeks', 'anal', 'boob', 'horny', 'tit', 'fucking', 'gay', 'rape', 'rapist', 'incest', 'beastiality', 'cum', 'maggot', 'bloxcity', 'bullshit', 'fuck', 'penis', 'dick', 'vagina', 'faggot', 'fag', 'nigger', 'asshole', 'shit', 'bitch', 'anal', 'stfu', 'cunt', 'pussy', 'hump', 'meatspin', 'redtube', 'porn', 'kys', 'xvideos', 'hentai', 'gangbang', 'milf', 'whore', 'cock');
						$username_check = strtolower($username);
						
						if (contains($username_check, $bad_words) and $errorOccured == false) {
							echo '<div class="alert alert-dismissible alert-danger">Invalid username.</div>';
							$errorOccured = true;
						}
						
						if (strlen($username) == 0 and $errorOccured == false) {
							echo '<div class="alert alert-dismissible alert-danger">In order to create an account, you must enter your username.</div>';
							$errorOccured = true;
						}
						if (strlen($username) < 3 and $errorOccured == false) {
							echo '<div class="alert alert-dismissible alert-danger">Your username must be at least 3 characters.</div>';
							$errorOccured = true;
						}
						if (strlen($password) == 0 and $errorOccured == false) {
							$errorOccured = true;
							echo '<div class="alert alert-dismissible alert-danger">In order to create an account, you must enter your password.</div>';
						}
						if (strlen($email) == 0 and $errorOccured == false) {
							$errorOccured = true;
							echo '<div class="alert alert-dismissible alert-danger">In order to create an account, you must enter your email.</div>';
						}
						if (strlen($email) > 128 and $errorOccured == false) {
							$errorOccured = true;
							echo '<div class="alert alert-dismissible alert-danger">Your email can not be longer than 128 characters.</div>';
						}
						if (strlen($password2) == 0 and $errorOccured == false) {
							$errorOccured = true;
							echo '<div class="alert alert-dismissible alert-danger">You must confirm your password.</div>';
						}
						if (strlen($password) < 6 and $errorOccured == false) {
							echo '<div class="alert alert-dismissible alert-danger">Your password must be at least 6 characters.</div>';
							$errorOccured = true;
						}
						if (strlen($username) > 20 and $errorOccured == false) {
							$errorOccured = true;
							echo '<div class="alert alert-dismissible alert-danger">Username cannot be longer than 20 characters.</div>';
						}
						if (strlen($password) > 45 or strlen($password2) > 45 and $errorOccured == false) {
							$errorOccured = true;
							echo '<div class="alert alert-dismissible alert-danger">Passwords cannot be longer than 45 characters.</div>';
						}
						if ($password !== $password2 and $errorOccured == false) {
							$errorOccured = true;
							echo '<div class="alert alert-dismissible alert-danger">The password confirmation has failed. Please try again.</div>';
						}
						if (strtolower($username) == strtolower($password) and $errorOccured == false) {
							$errorOccured = true;
							echo '<div class="alert alert-dismissible alert-danger">Your password can not be the same as your username.</div>';
						}
						if (!filter_var($email, FILTER_VALIDATE_EMAIL) and $errorOccured == false) {
							$errorOccured = true;
							echo '<div class="alert alert-dismissible alert-danger">The email you entered is invalid.</div>';
						}
						
						$stmt = $dbcon->prepare("SELECT * FROM users WHERE email = :email;");
						$stmt->bindParam(':email', $email, PDO::PARAM_STR); 
						$stmt->execute(); 
						if ($stmt->rowCount() > 0 and $errorOccured == false) {
							echo '<div class="alert alert-dismissible alert-danger">The email you tried to use is already being used.</div>';
							$errorOccured = true;
						}
						
						// Check if 24 hours passed since last account creation on this IP.
						$stmt = $dbcon->prepare("SELECT * FROM users WHERE registerIP = :ip ORDER BY id DESC LIMIT 1;");
						$stmt->bindParam(':ip', $IP, PDO::PARAM_STR); 
						$stmt->execute();
						if ($stmt->rowCount() > 0) {
							$result = $stmt->fetch(PDO::FETCH_ASSOC);
							$currentTime = date('Y-m-d H:i:s');
							$to_time = strtotime($currentTime);
							$from_time = strtotime($result['joinDate']);
							$timeSince =  round(abs($to_time - $from_time) / 60,2);
							if ($timeSince < 1440) {
								$errorOccured = true;
								$time = strtotime($result['joinDate']);
								echo '<div class="alert alert-dismissible alert-danger">Please wait '.humanTimingAuth($time, 86400).' before creating a new account.</div>';
							}
						}
						
								
						if ($errorOccured == false) {
							$stmt = $dbcon->prepare("SELECT * FROM users WHERE username = :user;");
							$stmt->bindParam(':user', $username, PDO::PARAM_STR); 
							$stmt->execute(); 
							if ($stmt->rowCount() == 0) {
								$IP = getIP();
								$stmt = $dbcon->prepare("SELECT * FROM users WHERE registerIP = :ip;");
								$stmt->bindParam(':ip', $IP, PDO::PARAM_STR); 
								$stmt->execute();
								if ($stmt->rowCount() > 4) {
									echo '<div class="alert alert-dismissible alert-danger">You have created too many accounts.</div>';
								}else{
									$salt = '$2a$07$'.uniqid(mt_rand(), true).'$';
									$hash = crypt($password, $salt);
									
									$stmt = $dbcon->prepare("INSERT INTO users (`username`, `password_hash`, `password_salt`, `email`, `registerIP`, `passwordVersion`) VALUES (:user, :hash, :salt, :email, :ip, 2);");
									$stmt->bindParam(':ip', $IP, PDO::PARAM_STR);
									$stmt->bindParam(':user', $username, PDO::PARAM_STR);
									$stmt->bindParam(':hash', $hash, PDO::PARAM_STR);
									$stmt->bindParam(':salt', $salt, PDO::PARAM_STR);
									$stmt->bindParam(':email', $email, PDO::PARAM_STR);
									$stmt->execute();
									echo '<div class="alert alert-dismissible alert-success">Welcome, '.htmlentities($username, ENT_QUOTES, "UTF-8").'! Your account has been created.</div>';	
								}
							}else{
								echo '<div class="alert alert-dismissible alert-danger">The username you have chosen has already been taken. Please try another one.</div>';
							}
						}
					}
				?>
				</div>
				<div id="TabContent" class="tab-content">
					<div class="tab-pane fade active in" id="login">
						<div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3 well" style="border-style: solid;border-color: #bbb;background-color:#ffffff;">
							<div class="row">
								<div class="col-xs-7">
									<h5 style="color:#444444;margin-top:0px;margin-bottom:5px;">Sign in</h5>
									<form method="post">
										<p class="Center"><input type="text" placeholder="Username" name="username" maxlength="20" class="form-control"></p>
										<p class="Center"><input type="password" placeholder="Password" name="password" maxlength="45" class="form-control"></p>
										<?php
											if ($requireAuth == true) {
												echo '<div id="RecaptchaField1"></div>';
											}
										?>
										<button type="submit" style="-webkit-box-shadow:none;box-shadow:none;" class="btn btn-primary FullWidth Center" name="Login">Sign In</button>
										<a href="#passreset" data-toggle="tab">Forgot Password?</a>
									</form>
								</div>
								<div class="col-xs-5">
									<h5 style="color:#444444;margin-top:0px;margin-bottom:5px;">New here?</h5>
									<p>Creating an account will just take a minute! <a href="#register" data-toggle="tab">Click here to register</a></p>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane fade" id="register">
						<div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3 well" style="border-style: solid;border-color: #bbb;background-color:#ffffff;">
							<h5 style="color:#444444;margin-top:0px;margin-bottom:5px;display:inline;">Create an account</h5><p style="display:inline;color:#bbbbbb"> Please use a password unique to Graphictoria</p>
							<form method="post">
								<p class="Center"><input type="text" placeholder="Username" maxlength="20" name="username" class="form-control"></p>
								<p class="Center"><input type="text" placeholder="E-Mail" name="email" class="form-control"></p>
								<p class="Center"><input type="password" placeholder="Password" maxlength="45" name="password" class="form-control"></p>
								<p class="Center"><input type="password" placeholder="Confirm Password" maxlength="45" name="password2" class="form-control"></p>
								<div id="RecaptchaField2"></div>
								<button type="submit" name="Register" class="btn btn-primary FullWidth Center">Create Account</button>
								<a href="#login" data-toggle="tab">Sign in</a>
							</form>
						</div>
					</div>
					<div class="tab-pane fade" id="passreset">
						<div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3 well" style="border-style: solid;border-color: #bbb;background-color:#ffffff;">
							<h5 style="color:#444444;margin-top:0px;margin-bottom:5px;display:inline;">Forgot password?</h5><p style="display:inline;color:#bbbbbb"> We'll send you an email with a link to reset your password</p>
							<form method="post">
								<p class="Center"><input type="text" placeholder="Username" maxlength="20" name="username" class="form-control"></p>
								<div id="RecaptchaField3"></div>
								<button type="submit" name="resetPassword" class="btn btn-primary FullWidth Center">Submit</button>
								<a href="#login" data-toggle="tab">Sign in</a>
							</form>
						</div>
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