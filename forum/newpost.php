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
		<?php
			echo getHead();
		?>
	</head>
	<body>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
			if (isset($_GET['id'])) {
				$forum_id = $_GET['id'];
				if (is_array($forum_id)) {
					$hAds = true;
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
					echo '<title>Graphictoria | Error</title>';
					echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Invalid parameter.</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				if (strlen($forum_id == 0)) {
					echo '<title>Graphictoria | Error</title>';
					echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Forum not specified.</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
			}else{
				echo '<title>Graphictoria | Error</title>';
				echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Forum not specified.</div></div></div>';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			$stmt = $dbcon->prepare("SELECT * FROM forums WHERE id = :id AND developer = 0");
			$stmt->bindParam(':id', $forum_id, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() == 0) {
				echo '<title>Graphictoria | Error</title>';
				echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">The forum you tried to post in does not exist.</div></div></div>';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($result['locked'] == 1 and $user_rankId !== 1) {
				echo '<title>Graphictoria | Error</title>';
				echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">The forum you tried to post in is locked.</div></div></div>';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			echo '<title>'.getName().' | Posting in '.htmlentities($result['name'], ENT_QUOTES, "UTF-8").'</title>';
		?>
		<div id="content" class="col-xs-12 col-sm-12 col-md-10 col-sm-offset-0 col-md-offset-1">
			<div class="panel panel-primary">
				<div class="panel-heading">New Post</div>
				<div class="panel-body">
					<form method="post" name="postForm">
						<input type="text" name="title" id="title" class="form-control empty" maxlength="64" placeholder="Post Title (64 characters maximum)"><br>
						<textarea style="display:100%;" maxlength="30000" placeholder="Enter your post here (30000 characters max)" class="form-control" rows="5" name="content"></textarea>
						<br>
						<button type="submit" name="<?php echo md5($auth_formCode); ?>" class="btn btn-primary FullWidth BigBtn">Post</button>
					</form>
				</div>
				<div class="panel-footer">
					<?php
						if (isset($_POST)) {
							$string = md5($auth_formCode);
							if (array_key_exists($string, $_POST)) {
								$title = $_POST['title'];
								$content = $_POST['content'];
								$error = false;
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
									echo '<div class="Center">Posts must contain text.</div>';
								}
								if (strlen($contentCheck) < 5) {
									$error = true;
									echo '<div class="Center">Your post must contain at least 5 characters.</div>';
								}
								if (strpos(strtolower($content), 'rbxpri') !== false) {
									$error = true;
									echo '<div class="Center">Post blocked because it contains illegal words.</div>';
								}
								if (strpos(strtolower($title), 'rbxpri') !== false) {
									$error = true;
									echo '<div class="Center">Post blocked because it contains illegal words.</div>';
								}
								if (strpos(strtolower($content), 'nobelium') !== false and $error == false) {
									$error = true;
									echo '<div class="Center">Post blocked because it contains illegal words.</div>';
								}
								if (strpos(strtolower($title), 'nobelium') !== false and $error == false) {
									$error = true;
									echo '<div class="Center">Post blocked because it contains illegal words.</div>';
								}
								if (strlen($content) > 30000) {
									$error = true;
									echo '<div class="Center">Post can not longer than 30000 characters long.</div>';
								}
								if (strlen($title) > 64) {
									$error = true;
									echo '<div class="Center">Titles can not longer than 64 characters long.</div>';
								}
								if (strlen($titleCheck) < 5) {
									$error = true;
									echo '<div class="Center">Titles must be at least 5 characters long.</div>';
								}
								$currentTime = date('Y-m-d H:i:s');
								$from_time = strtotime($user_lastPost);
								$to_time = strtotime($currentTime);
								$timeSince =  round(abs($to_time - $from_time) / 60,2);
								if ($timeSince < 0.2 and $user_rankId == 0) {
									$error = true;
									echo '<div class="Center">You are posting too fast. Please wait 12 seconds.</div>';
								}
								
								$from_time = strtotime($user_joined);
								$to_time = strtotime($currentTime);
								$timeSince =  round(abs($to_time - $from_time) / 60,2);
								if ($timeSince < 1440 and $user_rankId == 0) {
									$error = true;
									echo '<div class="Center">Your account has to be at least one day old to be able to post on the forums.</div>';
								}
								
								$stmt = $dbcon->prepare("SELECT * FROM forums WHERE id = :id");
								$stmt->bindParam(':id', $forum_id, PDO::PARAM_INT);
								$stmt->execute();
								$result = $stmt->fetch(PDO::FETCH_ASSOC);
								
								if ($stmt->rowCount() == 0) {
									$error = true;
									echo '<div class="Center">The forum you are posting in does not exist.</div>';
								}
								
								if ($result['locked'] == 1 and $user_rankId !== 1) {
									$error = true;
									echo '<div class="Center">The forum you are posting in has been locked.</div>';
								}
								if ($error == false) {
									$query = "INSERT INTO topics (`forumId`, `title`, `author_uid`, `content`, `lastActivity`) VALUES (:forumid, :topicname, :poster, :content, NOW());";
									$stmt = $dbcon->prepare($query);
									$stmt->bindParam(':forumid', $forum_id, PDO::PARAM_INT);
									$stmt->bindParam(':topicname', $title, PDO::PARAM_STR);
									$stmt->bindParam(':poster', $_COOKIE['auth_uid'], PDO::PARAM_INT);
									$stmt->bindParam(':content', $content, PDO::PARAM_STR);
									$stmt->execute();
									
									$query = "UPDATE `users` SET `lastPost`=NOW() WHERE `id`=:id;";
									$stmt = $dbcon->prepare($query);
									$stmt->bindParam(':id', $_COOKIE['auth_uid'], PDO::PARAM_INT);
									$stmt->execute();
									
									// Update forum post count
									$stmt = $dbcon->prepare("SELECT * FROM forums WHERE id = :id ORDER BY id DESC LIMIT 1;");
									$stmt->bindParam(':id', $forum_id, PDO::PARAM_INT);
									$stmt->execute();
									$result = $stmt->fetch(PDO::FETCH_ASSOC);
									$posts = $result['posts']+1;
									$query = "UPDATE `forums` SET `posts`=:posts WHERE `id`=:id;";
									$stmt = $dbcon->prepare($query);
									$stmt->bindParam(':id', $forum_id, PDO::PARAM_INT);
									$stmt->bindParam(':posts', $posts, PDO::PARAM_INT);
									$stmt->execute();
									
									
									// Update user forum post count
									$stmt = $dbcon->prepare("SELECT posts FROM users WHERE id = :id ORDER BY id DESC LIMIT 1;");
									$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
									$stmt->execute();
									$result = $stmt->fetch(PDO::FETCH_ASSOC);
									$posts = $result['posts']+1;
									$query = "UPDATE `users` SET `posts`=:posts WHERE `id`=:id;";
									$stmt = $dbcon->prepare($query);
									$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
									$stmt->bindParam(':posts', $posts, PDO::PARAM_INT);
									$stmt->execute();
									
									$stmt = $dbcon->prepare("SELECT * FROM topics WHERE author_uid = :id ORDER BY id DESC LIMIT 1;");
									$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
									$stmt->execute();
									$result = $stmt->fetch(PDO::FETCH_ASSOC);
									header("Location: /forum/post.php?id=".$result['id']);
									
									
								}
							}else{
								echo '<div class="Center">Write a post. Do remember that your title can not be longer than 64 characters and your post can not be longer than 30000.</div>';
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