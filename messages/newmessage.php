<?php
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
	if ($loggedIn == false) {
		header("Location: /auth.php");
		exit;
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php
			echo getHead();
		?>
		<title>New Message</title>
	</head>
	<body>
		<div id="cont" class="col-xs-12 col-sm-12 col-md-10 col-sm-offset-0 col-md-offset-1">
			<?php
				$messageString = md5(sha1(md5($auth_formCode)));
				if (isset($_GET['uid'])) {
					if (is_array($_GET['uid'])) {
						$hAds = true;
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
						echo '<title>Graphictoria | Error</title>';
						echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Invalid parameter.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
					
					if (strlen($_GET['uid']) == 0) {
						$hAds = true;
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
						echo '<title>Graphictoria | Error</title>';
						echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">No userID has been specified.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
					if (filter_var($_GET['uid'], FILTER_VALIDATE_INT) == false) {
						$hAds = true;
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
						echo '<title>Graphictoria | Error</title>';
						echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">No userID has been specified.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
					$recv_uid = $_GET['uid'];
				}else{
					$hAds = true;
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
					echo '<title>Graphictoria | Error</title>';
					echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">No userID has been specified.</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
				$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id;");
				$stmt->bindParam(':id', $recv_uid, PDO::PARAM_INT);
				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($stmt->rowCount() == 0) {
					echo 'The user you are trying to send a message to doesn\'t exist.';
					echo '</div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				if ($result['banned'] == 1) {
					echo 'You can not send messages to a banned user.';
					echo '</div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				if ($result['id'] == $auth_uid) {
					echo 'You can not send messages to yourself.';
					echo '</div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
				$stmt->bindParam(':id', $recv_uid, PDO::PARAM_INT);
				$stmt->execute();
				$resultuser = $stmt->fetch(PDO::FETCH_ASSOC);
			?>
			<div class="panel panel-primary">
				<div class="panel-heading">New Message to <?php echo $resultuser['username']; ?></div>
				<div class="panel-body">
					<form method="post" name="postForm">
						<?php
							if (isset($_GET['reply'])) {
								echo '<input type="text" name="title" id="title" class="form-control empty" maxlength="32" value="Reply" placeholder="Message Title (32 characters maximum)"><br>';
							}else{
								echo '<input type="text" name="title" id="title" class="form-control empty" maxlength="32" placeholder="Message Title (32 characters maximum)"><br>';
							}
						?>
						<textarea maxlength="30000" placeholder="Enter your message here (30000 characters max)" class="form-control" rows="5" name="content" id="content"></textarea>
						<br>
						<p><font color="red">* Do <b>not</b> ever send your password to anyone. Graphictoria staff will <b>never</b> ask for your password.</font></p>
						<button type="submit" name="<?php echo $messageString; ?>" class="btn btn-primary FullWidth BigBtn">Send</button>
					</form>
				</div>
				<div class="panel-footer">
					<?php
						if (isset($_POST)) {
							if (array_key_exists($messageString, $_POST)) {
								$title = $_POST['title'];
								$content = $_POST['content'];
								if ($title == "") {
									$error = true;
									echo '<div class="Center">Title can not be empty.</div>';
								}
								$titleCheck = preg_replace("/[^ \w]+/", "", $title);
								$titleCheck = preg_replace('!\s+!', ' ', $titleCheck);
								$contentCheck = preg_replace('!\s+!', ' ', $content);
								$contentCheck = strip_tags($contentCheck);
								$contentCheck = preg_replace("/&#?[a-z0-9]+;/i","", $contentCheck);
								$contentCheck = preg_replace('!\s+!', ' ', $contentCheck);
								if ($content == "") {
									$error = true;
									echo '<div class="Center">Messages must contain text.</div>';
								}
								if (strlen($contentCheck) < 5) {
									$error = true;
									echo '<div class="Center">Your message must contain at least 5 characters.</div>';
								}
								if (strlen($content) > 30000) {
									$error = true;
									echo '<div class="Center">Message can not longer than 30000 characters long.</div>';
								}
								if (strlen($title) > 32) {
									$error = true;
									echo '<div class="Center">Titles can not longer than 32 characters long.</div>';
								}
								if (strlen($titleCheck) < 5) {
									$error = true;
									echo '<div class="Center">Titles must be at least 5 characters long.</div>';
								}
								$currentTime = date('Y-m-d H:i:s');
								$from_time = strtotime($user_lastPost);
								$to_time = strtotime($currentTime);
								$timeSince =  round(abs($to_time - $from_time) / 60,2);
								if ($timeSince < 1 and $user_rankId == 0) {
									$error = true;
									echo '<div class="Center">You are sending messages or posting too fast. Please wait a minute.</div>';
								}
								if ($error == false) {
									$query = "INSERT INTO messages (`recv_uid`, `sender_uid`, `title`, `content`) VALUES (:recv_uid, :sender_uid, :title, :content);";
									$stmt = $dbcon->prepare($query);
									$stmt->bindParam(':sender_uid', $auth_uid, PDO::PARAM_INT);
									$stmt->bindParam(':recv_uid', $recv_uid, PDO::PARAM_INT);
									$stmt->bindParam(':title', $title, PDO::PARAM_STR);
									$stmt->bindParam(':content', $content, PDO::PARAM_STR);
									$stmt->execute();
									
									$query = "UPDATE `users` SET `lastPost`=NOW() WHERE `id`=:id;";
									$stmt = $dbcon->prepare($query);
									$stmt->bindParam(':id', $_COOKIE['auth_uid'], PDO::PARAM_INT);
									$stmt->execute();
									
									header("Location: /messages/?messageSent");
								}
							}else{
								echo '<div class="Center">Write a message. Do remember that your title can not be longer than 32 characters and your message can not be longer than 30000.</div>';
							}
						}
					?>
				</div>
			</div>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>