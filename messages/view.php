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
		<title><?php echo getName(); ?> | Message</title>
	</head>
	<body>
		<?php
			$currentTime = date('Y-m-d H:i:s');
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
						$hAds = true;
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
						echo '<title>Graphictoria | Error</title>';
						echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">MessageID not specified.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
				}else{
					$hAds = true;
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
					echo '<title>Graphictoria | Error</title>';
					echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">MessageID not specified.</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
				$stmt = $dbcon->prepare("SELECT * FROM messages WHERE id = :id");
				$stmt->bindParam(':id', $id, PDO::PARAM_INT);
				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				
				if ($stmt->rowCount() == 0) {
					echo 'Message not found.';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				
				if ($result['recv_uid'] != $auth_uid) {
					echo 'Message not found.';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				if ($result['read'] == 0) {
					$query = "UPDATE `messages` SET `read`=1 WHERE `id`=:id;";
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':id', $id, PDO::PARAM_INT);
					$stmt->execute();
				}
			?>
			<div class="panel panel-primary">
				<div class="panel-heading"><b><?php echo htmlentities($result['title'], ENT_QUOTES, "UTF-8"); ?></b> - Sent on <?php echo date('M j Y g:i A', strtotime($result['date']));?></div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-2">
							<div class="Center">
								<?php
									$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
									$stmt->bindParam(':id', $result['sender_uid'], PDO::PARAM_INT);
									$stmt->execute();
									$resultuser = $stmt->fetch(PDO::FETCH_ASSOC);
									$from_time = strtotime($resultuser['lastSeen']);
									$to_time = strtotime($currentTime);
									$timeSince =  round(abs($to_time - $from_time) / 60,2);
									if ($timeSince > 5){
										echo '<font color="grey">&#x25CF; </font>';
									}else{
										echo '<font color="green">&#x25CF; </font>';
									}
									$postCount = getPostCount($resultuser['id'], $dbcon);
									if ($resultuser['rank'] == 1) {
										echo '<b><a class="admin" href="/profile.php?id='.$resultuser['id'].'">'.htmlentities($resultuser['username'], ENT_QUOTES, "UTF-8").'</a></b><br>';
										echo 'Administrator<br>';
										echo '<a href="/profile.php?id='.$resultuser['id'].'"><img width="150" src="'.getImage($resultuser).'"></a><br>';
										if ($postCount == 1) {
											echo $postCount.' post';
										}else{
											echo $postCount.' posts';
										}
									}elseif ($resultuser['rank'] == 2) {
										echo '<b><a class="moderator" href="/profile.php?id='.$resultuser['id'].'">'.htmlentities($resultuser['username'], ENT_QUOTES, "UTF-8").'</a></b><br>';
										echo 'Moderator<br>';
										echo '<a href="/profile.php?id='.$resultuser['id'].'"><img width="150" src="'.getImage($resultuser).'"></a><br>';
										if ($postCount == 1) {
											echo $postCount.' post';
										}else{
											echo $postCount.' posts';
										}
									}else{
										echo '<a href="/profile.php?id='.$resultuser['id'].'">'.htmlentities($resultuser['username'], ENT_QUOTES, "UTF-8").'</a><br>';
										echo '<a href="/profile.php?id='.$resultuser['id'].'"><img width="150" src="'.getImage($resultuser).'"></a><br>';
										if ($postCount == 1) {
											echo $postCount.' post';
										}else{
											echo $postCount.' posts';
										}
									}
								?>
							</div>
						</div>
						<div class="col-md-10">
							<?php
								$content = $result['content'];
								$content = strip_tags($content);
								$content = htmlentities($content, ENT_QUOTES, "UTF-8");
							?>
							<div class="content" style="white-space:pre-wrap;"><?php echo $content; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<a href="/messages/newmessage.php?uid=<?php echo $result['sender_uid']; ?>&reply" class="btn btn-success FullWidth">Reply</a>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>