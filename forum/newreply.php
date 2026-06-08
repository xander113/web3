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
	</head>
	<body>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
			if (isset($_GET['id'])) {
				$post_id = $_GET['id'];
				if (is_array($post_id)) {
					$hAds = true;
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
					echo '<title>Graphictoria | Error</title>';
					echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Invalid parameter.</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				if (strlen($post_id == 0)) {
					echo '<title>Graphictoria | Error</title>';
					echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Post ID not specified.</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
			}else{
				echo '<title>Graphictoria | Error</title>';
				echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Post ID not specified.</div></div></div>';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			$stmt = $dbcon->prepare("SELECT * FROM topics WHERE id = :id AND developer = 0");
			$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() == 0) {
				echo '<title>Graphictoria | Error</title>';
				echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">The post you tried to reply to does not exist.</div></div></div>';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($result['locked'] == 1 and $user_rankId == 0) {
				echo '<title>Graphictoria | Error</title>';
				echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">The post you tried to reply to is locked.</div></div></div>';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			
			$forumId = $result['forumId'];
			echo '<title>'.getName().' | Replying to '.htmlentities($result['title'], ENT_QUOTES, "UTF-8").'</title>';
		?>
		<div id="content" class="col-xs-12 col-sm-12 col-md-10 col-sm-offset-0 col-md-offset-1">
			<div class="panel panel-primary">
				<div class="panel-heading">Reply to <?php echo htmlentities($result['title'], ENT_QUOTES, "UTF-8"); ?></div>
				<div class="panel-body">
					<form method="post" name="postForm">
						<textarea maxlength="30000" placeholder="Enter your reply here (30000 characters max)" class="form-control" rows="5" name="content"></textarea>
						<br>
						<button style="display:100%;" type="submit" name="<?php echo $auth_formCode; ?>" class="btn btn-primary FullWidth BigBtn">Reply</button>
					</form>
				</div>
				<div class="panel-footer">
					<?php
						if (isset($_POST)) {
							if (array_key_exists($auth_formCode, $_POST)) {
								$content = $_POST['content'];
								$contentCheck = preg_replace('!\s+!', ' ', $content);
								$contentCheck = strip_tags($contentCheck);
								$contentCheck = preg_replace("/&#?[a-z0-9]+;/i","", $contentCheck);
								$contentCheck = preg_replace('!\s+!', ' ', $contentCheck);
								$error = false;
								if ($content == "") {
									$error = true;
									echo '<div class="Center">Replies must contain text.</div>';
								}
								if (strlen($contentCheck) < 5) {
									$error = true;
									echo '<div class="Center">Your reply must contain at least 5 characters.</div>';
								}
								if (strlen($content) > 30000) {
									$error = true;
									echo '<div class="Center">Replies can not longer than 30000 characters long.</div>';
								}
								if (strpos(strtolower($content), 'rbxpri') !== false) {
									$error = true;
									echo '<div class="Center">Your post must contain at least 5 characters.</div>';
								}
								if (strpos(strtolower($content), 'nobelium') !== false and $error == false) {
									$error = true;
									echo '<div class="Center">Post blocked because it contains illegal words.</div>';
								}
								$stmt = $dbcon->prepare("SELECT * FROM topics WHERE id = :id");
								$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
								$stmt->execute();
								$result = $stmt->fetch(PDO::FETCH_ASSOC);
								if ($result['locked'] == 1 and $user_rankId == 0) {
									$error = true;
									echo '<div class="Center">The post you are replying to is locked.</div>';
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
								
								$stmt = $dbcon->prepare("SELECT * FROM topics WHERE id = :id");
								$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
								$stmt->execute();
								
								if ($stmt->rowCount() == 0) {
									$error = true;
									echo '<div class="Center">The post you are posting in does not exist.</div>';
								}
								
								$result = $stmt->fetch(PDO::FETCH_ASSOC);
								if ($result['locked'] == 1 and $user_rankId !== 1) {
									$error = true;
									echo '<div class="Center">The post you are posting in has been locked.</div>';
								}
								if ($error == false) {
									$query = "INSERT INTO replies (`postId`, `content`, `author_uid`, `forumId`) VALUES (:id, :content, :poster, :forumId);";
									$stmt = $dbcon->prepare($query);
									$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
									$stmt->bindParam(':poster', $auth_uid, PDO::PARAM_INT);
									$stmt->bindParam(':content', $content, PDO::PARAM_STR);
									$stmt->bindParam(':forumId', $forumId, PDO::PARAM_INT);
									$stmt->execute();
									
									$query = "UPDATE `topics` SET `lastActivity`=NOW() WHERE `id`=:id;";
									$stmt = $dbcon->prepare($query);
									$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
									$stmt->execute();
									
									$query = "UPDATE `users` SET `lastPost`=NOW() WHERE `id`=:id;";
									$stmt = $dbcon->prepare($query);
									$stmt->bindParam(':id', $_COOKIE['auth_uid'], PDO::PARAM_INT);
									$stmt->execute();
									
									// Delete all reads
									$query = "DELETE FROM `read` WHERE `postId`=:id";
									$stmt = $dbcon->prepare($query);
									$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
									$stmt->execute();
									
									// Update forum reply count
									$stmt = $dbcon->prepare("SELECT * FROM forums WHERE id = :id ORDER BY id DESC LIMIT 1;");
									$stmt->bindParam(':id', $forumId, PDO::PARAM_INT);
									$stmt->execute();
									$result = $stmt->fetch(PDO::FETCH_ASSOC);
									$posts = $result['replies']+1;
									$query = "UPDATE `forums` SET `replies`=:posts WHERE `id`=:id;";
									$stmt = $dbcon->prepare($query);
									$stmt->bindParam(':id', $forumId, PDO::PARAM_INT);
									$stmt->bindParam(':posts', $posts, PDO::PARAM_INT);
									$stmt->execute();
									
									// Update topic reply count
									$stmt = $dbcon->prepare("SELECT * FROM topics WHERE id = :id ORDER BY id DESC LIMIT 1;");
									$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
									$stmt->execute();
									$result = $stmt->fetch(PDO::FETCH_ASSOC);
									$posts = $result['replies']+1;
									$query = "UPDATE `topics` SET `replies`=:posts WHERE `id`=:id;";
									$stmt = $dbcon->prepare($query);
									$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
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
									
									header("Location: /forum/post.php?id=".$post_id.'&gotoLastPage');
								}
							}else{
								echo '<div class="Center">Write a reply. Remember that your reply can not be longer than 30000 characters.</div>';
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