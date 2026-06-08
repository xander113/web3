<?php
	$hAds = true;
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
	if ($loggedIn == false) {
		header("Location: /auth.php");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
	if ($user_rankId != 1) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo getName(); ?> | Prune Posts</title>
		<?php
			echo getHead();
		?>
	</head>
	<body>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
		?>
		<div id="content">
			<?php
				if (isset($_POST)) {
					if (array_key_exists(sha1($auth_formCode), $_POST) and $user_rankId > 0 and $user_rankId != 3) {
						$username = $_POST['username'];
						$stmt = $dbcon->prepare("SELECT * FROM users WHERE username=:uname;");
						$stmt->bindParam(':uname', $username, PDO::PARAM_STR);
						$stmt->execute();
						$result = $stmt->fetch(PDO::FETCH_ASSOC);
						if ($result['rank'] == 0 and $stmt->rowCount() > 0) {
							$userID = $result['id'];
							$stmt = $dbcon->prepare("UPDATE users SET posts = 0 WHERE id=:id;");
							$stmt->bindParam(':id', $userID, PDO::PARAM_INT);
							$stmt->execute();
							
							$stmt = $dbcon->prepare("SELECT * FROM topics WHERE author_uid = :uid");
							$stmt->bindParam(':uid', $userID, PDO::PARAM_STR);
							$stmt->execute();
							foreach($stmt as $result) {
								$postID = $result['id'];
								$forumId = $result['forumId'];
								$stmt = $dbcon->prepare("DELETE FROM topics WHERE id = :id");
								$stmt->bindParam(':id', $postID, PDO::PARAM_STR);
								$stmt->execute();
								
								$stmt = $dbcon->prepare("DELETE FROM replies WHERE postId = :id");
								$stmt->bindParam(':id', $postID, PDO::PARAM_STR);
								$stmt->execute();
								
								$query = "SELECT * FROM topics WHERE forumId=:id;";
								$stmt = $dbcon->prepare($query);
								$stmt->bindParam(':id', $forumId, PDO::PARAM_INT);
								$stmt->execute();
								$total = $stmt->rowCount();
								
								$stmt = $dbcon->prepare("UPDATE forums SET posts = :posts WHERE id=:id;");
								$stmt->bindParam(':id', $forumId, PDO::PARAM_INT);
								$stmt->bindParam(':posts', $total, PDO::PARAM_INT);
								$stmt->execute();
								
								$query = "SELECT * FROM replies WHERE forumId=:id;";
								$stmt = $dbcon->prepare($query);
								$stmt->bindParam(':id', $forumId, PDO::PARAM_INT);
								$stmt->execute();
								$total = $stmt->rowCount();
								
								$stmt = $dbcon->prepare("UPDATE forums SET replies = :posts WHERE id=:id;");
								$stmt->bindParam(':id', $forumId, PDO::PARAM_INT);
								$stmt->bindParam(':posts', $total, PDO::PARAM_INT);
								$stmt->execute();
							}
							
							$stmt = $dbcon->prepare("SELECT * FROM replies WHERE author_uid = :uid");
							$stmt->bindParam(':uid', $userID, PDO::PARAM_INT);
							$stmt->execute();
							foreach($stmt as $result) {
								$replyID = $result['id'];
								$postID = $result['postId'];
								$forumId = $result['forumId'];
								$stmt = $dbcon->prepare("DELETE FROM replies WHERE id = :id;");
								$stmt->bindParam(':id', $replyID, PDO::PARAM_STR);
								$stmt->execute();
								
								$stmt = $dbcon->prepare("SELECT * FROM replies WHERE postId = :id ORDER BY id DESC LIMIT 1;");
								$stmt->bindParam(':id', $postID, PDO::PARAM_INT);
								$stmt->execute();
								$result = $stmt->fetch(PDO::FETCH_ASSOC);
								$postTime = $result['post_time'];
								
								if ($stmt->rowCount() > 0) {
									$query = "UPDATE `topics` SET `lastActivity`=:date WHERE `id`=:id;";
									$stmt = $dbcon->prepare($query);
									$stmt->bindParam(':id', $postID, PDO::PARAM_INT);
									$stmt->bindParam(':date', $postTime, PDO::PARAM_STR);
									$stmt->execute();
								}else{
									$stmt = $dbcon->prepare("SELECT * FROM topics WHERE id = :id;");
									$stmt->bindParam(':id', $postID, PDO::PARAM_INT);
									$stmt->execute();
									$result = $stmt->fetch(PDO::FETCH_ASSOC);
									$OPPostTime = $result['postTime'];
									
									$query = "UPDATE `topics` SET `lastActivity`=:date WHERE `id`=:id;";
									$stmt = $dbcon->prepare($query);
									$stmt->bindParam(':id', $postID, PDO::PARAM_INT);
									$stmt->bindParam(':date', $OPPostTime , PDO::PARAM_STR);
									$stmt->execute();
								}
								
								// Also set the replies count
								$stmt = $dbcon->prepare("SELECT * FROM replies WHERE postId = :id;");
								$stmt->bindParam(':id', $postID, PDO::PARAM_INT);
								$stmt->execute();
								$replyCount = $stmt->rowCount();
								
								$query = "UPDATE `topics` SET `replies`=:rCount WHERE `id`=:id;";
								$stmt = $dbcon->prepare($query);
								$stmt->bindParam(':id', $postID, PDO::PARAM_INT);
								$stmt->bindParam(':rCount', $replyCount , PDO::PARAM_STR);
								$stmt->execute();
								
								$query = "SELECT * FROM topics WHERE forumId=:id;";
								$stmt = $dbcon->prepare($query);
								$stmt->bindParam(':id', $forumId, PDO::PARAM_INT);
								$stmt->execute();
								$total = $stmt->rowCount();
								
								$stmt = $dbcon->prepare("UPDATE forums SET posts = :posts WHERE id=:id;");
								$stmt->bindParam(':id', $forumId, PDO::PARAM_INT);
								$stmt->bindParam(':posts', $total, PDO::PARAM_INT);
								$stmt->execute();
								
								$query = "SELECT * FROM replies WHERE forumId=:id;";
								$stmt = $dbcon->prepare($query);
								$stmt->bindParam(':id', $forumId, PDO::PARAM_INT);
								$stmt->execute();
								$total = $stmt->rowCount();
								
								$stmt = $dbcon->prepare("UPDATE forums SET replies = :posts WHERE id=:id;");
								$stmt->bindParam(':id', $forumId, PDO::PARAM_INT);
								$stmt->bindParam(':posts', $total, PDO::PARAM_INT);
								$stmt->execute();
							}
							
							echo '<div class="alert alert-dismissible alert-success">Deleted all posts from this user.</div>';
						}else{
							if ($stmt->rowCount() == 0) {
								echo '<div class="alert alert-dismissible alert-danger">This user does not exist.</div>';
							}else{
								echo '<div class="alert alert-dismissible alert-danger">This action can not be done with a staff member.</div>';
							}
						}
					}
				}
			?>
			<h4 style="color:grey;">Prune Posts</h4>
			<p>This utility will remove all posts and replies of a certain user. Quite useful for if the user is a spammer and is not stopping.</p>
			<p>Please know that this can not be undone and that this will only have to be done if no other option is possible.</p>
			<form method="post">
				<input type="text" name="username" placeholder="User to prune" class="form-control"></input>
				<button type="submit" name="<?php echo sha1($auth_formCode);?>" class="btn btn-primary" style="width:100%">Prune posts</button>
			</form>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>