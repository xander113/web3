<?php
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
	if ($loggedIn == false) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}else{
		if ($auth_emailVerified > 0) {
			header("Location: /");
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
			exit;
		}
	}
	
	$query = "SELECT * FROM users WHERE id = :id";
	$stmt = $dbcon->prepare($query);
	$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT); 
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$from_time = strtotime($result['emailcodeTime']);
	$to_time = strtotime($currentTime);
	$timeSince =  round(abs($to_time - $from_time) / 60,2);
	if ($result['emailcodeTime'] == NULL or $timeSince > 5) {
		$stmt = $dbcon->prepare("UPDATE users SET emailcodeTime = NOW() WHERE id = :id;");
		$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
		$stmt->execute();
		
		$emailCode = md5(random_str(64));
		$stmt = $dbcon->prepare("UPDATE users SET emailVerifyCode = :code WHERE id = :id;");
		$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
		$stmt->bindParam(':code', $emailCode, PDO::PARAM_STR);
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
		$mail->Subject = 'Graphictoria Email Verification';
		$mail->Body    = 'Hello '.$result['username'].'!<br><br>Your code is : <b>'.$emailCode.'</b><br><br>You are receiving this message because someone (hopefully you!) tried to register an account using your email<br>If it was not you, you may ignore this e-mail.';
		$mail->AltBody = 'Your code is : '.$emailCode;
		$mail->send();
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo getName(); ?> | E-mail verification</title>
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
				if (isset($_POST['verify'])) {
					$code = $_POST['code'];
					
					$query = "SELECT * FROM users WHERE id = :id";
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT); 
					$stmt->execute();
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					
					if ($result['emailverifyCode'] == $code) {
						$stmt = $dbcon->prepare("UPDATE users SET emailVerified = 1 WHERE id = :id;");
						$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
						
						header("Location: /");
					}else{
						echo '<div class="alert alert-dismissible alert-danger">Incorrect code.</div>';
					}
				}
			?>
			<div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3 well" style="border-style: solid;border-color: #bbb;background-color:#ffffff;">
				<h5 style="color:#444444;margin-top:0px;margin-bottom:5px;">Verify your E-Mail</h5>
				<p style="color:#bbbbbb">An e-mail has been sent to <b><?php echo $auth_email;?></b>, the e-mail contains a verification code. Enter that code to activate your account.</p>
				<form method="post">
					<p><input type="text" class="form-control" name="code" required="required" placeholder="Enter code here"></input></p>
					<p><button type="submit" class="btn btn-primary btn-sm" style="-webkit-box-shadow:none;box-shadow:none;width:100%" name="verify">Verify</button></p>
				</form>
			</div>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>