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
		<title><?php echo getName(); ?> | Edit</title>
	</head>
	<body>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
			if ($loggedIn == false) {
				echo '<br>';
			}
		?>
		<div id="content" class="col-xs-12 col-sm-12 col-md-10 col-sm-offset-0 col-md-offset-1">
			<?php
				if (isset($_GET['id'])) {
					$id = $_GET['id'];
					if (is_array($id)) {
						$hAds = true;
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
						echo '<title>Graphictoria | Error</title>';
						echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Invalid parameter.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
					if (strlen($id) == 0) {
						echo '<title>Graphictoria | Error</title>';
						echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">ID not specified.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
				}else{
					echo '<title>Graphictoria | Error</title>';
					echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">ID not specified.</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				if (isset($_GET['type'])) {
					$type = $_GET['type'];
					if (is_array($type)) {
						$hAds = true;
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
						echo '<title>Graphictoria | Error</title>';
						echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Invalid parameter.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
					if (strlen($type) == 0) {
						echo '<title>Graphictoria | Error</title>';
						echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Type not specified.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
					if ($type == "post") {
						$type = "post";
					}elseif($type == "reply") {
						$type = "reply";
					}else{
						echo '<title>Graphictoria | Error</title>';
						echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Unknown Type.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
				}else{
					echo '<title>Graphictoria | Error</title>';
					echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Type not specified.</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				if ($type == "post") {
					// Get post
					$stmt = $dbcon->prepare("SELECT * FROM topics WHERE id = :id AND developer = 0");
					$stmt->bindParam(':id', $id, PDO::PARAM_INT);
					$stmt->execute();
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					
					if ($stmt->rowCount() == 0) {
						echo '<title>Graphictoria | Error</title>';
						echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">The content you are trying to edit does not exist.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
					if ($result['author_uid'] != $auth_uid and $user_rankId != 1) {
						echo '<title>Graphictoria | Error</title>';
						echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">You can not edit this post.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
					
					if ($result['locked'] == 1 and $user_rankId !== 1) {
						echo '<title>Graphictoria | Error</title>';
						echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">You can not edit content in a post that is locked.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
				}else{
					// Get reply
					$stmt = $dbcon->prepare("SELECT * FROM replies WHERE id = :id AND developer = 0");
					$stmt->bindParam(':id', $id, PDO::PARAM_INT);
					$stmt->execute();
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					
					if ($stmt->rowCount() == 0) {
						echo '<title>Graphictoria | Error</title>';
						echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">The content you are trying to edit does not exist.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
					if ($result['author_uid'] !== $auth_uid and $user_rankId !== 1) {
						echo '<title>Graphictoria | Error</title>';
						echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">You can not edit this post.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
					
					// Get if locked
					$stmtLock = $dbcon->prepare("SELECT * FROM topics WHERE id = :id");
					$post_id = $result['postId'];
					$stmtLock->bindParam(':id', $post_id, PDO::PARAM_INT);
					$stmtLock->execute();
					$resultLock = $stmt->fetch(PDO::FETCH_ASSOC);
					if ($resultLock['locked'] == 1 and $user_rankId !== 1) {
						echo '<title>Graphictoria | Error</title>';
						echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">You can not edit content in a post that is locked.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
				}
			?>
			<div class="panel panel-primary">
				<div class="panel-heading">Edit</div>
				<div class="panel-body">
					<form method="post" name="postForm">
						<textarea style="display:100%;" maxlength="30000" placeholder="Enter your post here (30000 characters max)" class="form-control" rows="5" name="content"><?php echo htmlentities($result['content'], ENT_QUOTES, "UTF-8"); ?></textarea>
						<br>
						<button type="submit" name="<?php echo md5(strrev($auth_formCode)); ?>" class="btn btn-primary FullWidth BigBtn">Edit</button>
					</form>
				</div>
				<div class="panel-footer">
					<?php
						if (isset($_POST)) {
							$string = md5(strrev($auth_formCode));
							if (array_key_exists($string, $_POST)) {
								$content = $_POST['content'];
								$contentCheck = preg_replace('!\s+!', ' ', $content);
								$contentCheck = strip_tags($contentCheck);
								$contentCheck = preg_replace("/&#?[a-z0-9]+;/i","", $contentCheck);
								$contentCheck = preg_replace('!\s+!', ' ', $contentCheck);
								$error = false;
								if ($type == "post") {
									// Get post
									$stmt = $dbcon->prepare("SELECT * FROM topics WHERE id = :id");
									$stmt->bindParam(':id', $id, PDO::PARAM_INT);
									$stmt->execute();
									$result = $stmt->fetch(PDO::FETCH_ASSOC);
									$postId = $result['id'];
									$authorId = $result['author_uid'];
									if ($result['locked'] == 1 and $user_rankId !== 1) {
										$error = true;
										echo '<div class="Center">You can\'t edit a post that has been locked.</div>';
									}
								}else{
									// Get reply
									$stmt = $dbcon->prepare("SELECT * FROM replies WHERE id = :id");
									$stmt->bindParam(':id', $id, PDO::PARAM_INT);
									$stmt->execute();
									$result = $stmt->fetch(PDO::FETCH_ASSOC);
									$authorId = $result['author_uid'];
									
									// Get if locked
									$stmtLock = $dbcon->prepare("SELECT * FROM topics WHERE id = :id");
									$post_id = $result['postId'];
									$postId = $post_id;
									$stmtLock->bindParam(':id', $post_id, PDO::PARAM_INT);
									$stmtLock->execute();
									$resultLock = $stmt->fetch(PDO::FETCH_ASSOC);
									if ($resultLock['locked'] == 1 and $user_rankId !== 1) {
										$error = true;
										echo '<div class="Center">You can\'t edit a reply on a post that has been locked.</div>';
									}			
								}
								if ($content == "") {
									$error = true;
									echo '<div class="Center">Posts must contain text.</div>';
								}
								if (strlen($contentCheck) < 5) {
									$error = true;
									echo '<div class="Center">Your post must contain at least 5 characters.</div>';
								}
								if (strlen($content) > 30000) {
									$error = true;
									echo '<div class="Center">Post can not longer than 30000 characters long.</div>';
								}
								if ($authorId !== $auth_uid and $user_rankId !== 1) {
									$error = true;
									echo '<div class="Center">You can not edit this.</div>';
								}
								if ($error == false) {
									if ($type == "post") {
										$query = "UPDATE `topics` SET `content`=:content WHERE `id`=:id;";
										$stmt = $dbcon->prepare($query);
										$stmt->bindParam(':id', $id, PDO::PARAM_INT);
										$stmt->bindParam(':content', $content, PDO::PARAM_STR);
										$stmt->execute();
										
										$query = "UPDATE `topics` SET `updatedOn`=NOW() WHERE `id`=:id;";
										$stmt = $dbcon->prepare($query);
										$stmt->bindParam(':id', $id, PDO::PARAM_INT);
										$stmt->execute();
										
										$query = "UPDATE `topics` SET `updatedBy`=:uid WHERE `id`=:id;";
										$stmt = $dbcon->prepare($query);
										$stmt->bindParam(':id', $id, PDO::PARAM_INT);
										$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
										$stmt->execute();
									}else{
										$query = "UPDATE `replies` SET `content`=:content WHERE `id`=:id;";
										$stmt = $dbcon->prepare($query);
										$stmt->bindParam(':id', $id, PDO::PARAM_INT);
										$stmt->bindParam(':content', $content, PDO::PARAM_STR);
										$stmt->execute();
										
										$query = "UPDATE `replies` SET `updatedOn`=NOW() WHERE `id`=:id;";
										$stmt = $dbcon->prepare($query);
										$stmt->bindParam(':id', $id, PDO::PARAM_INT);
										$stmt->execute();
										
										$query = "UPDATE `replies` SET `updatedBy`=:uid WHERE `id`=:id;";
										$stmt = $dbcon->prepare($query);
										$stmt->bindParam(':id', $id, PDO::PARAM_INT);
										$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
										$stmt->execute();
									}
									header("Location: /forum/post.php?id=".$postId);
								}
							}else{
								echo '<div class="Center">Edit your post. Do remember that your post can not be longer than 30000 characters.</div>';
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