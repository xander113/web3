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
		<title>Friends</title>
		<script src="/html/js/tinymce/tinymce.min.js"></script>
		<script>tinymce.init({ selector:'textarea' });</script>
	</head>
	<body>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
			if (isset($_GET['mode'])) {
				$mode = $_GET['mode'];
				if ($mode == "requests") {
					$mode = "requests";
				}else{
					$mode = "showfriends";
				}
			}else{
				$mode = "showfriends";
			}
			if (isset($_GET['page'])) {
				$page = $_GET['page'];
				$offset = $page*10;
				if ($page == 0){
					$page = 0;
					$offset = 0;
				}
			}else{
				$page = 0;
				$offset = 0;
			}
			if ($page < 0) {
				header("Location: /friends/?mode=".$mode);
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
		?>
		<div class="btn-group btn-group-justified" style="margin:9px 0px;">
			<a href="/friends/?mode=showfriends" class="btn btn-default">Friends</a>
			<a href="/friends/?mode=requests" class="btn btn-default">Requests</a>
		</div>
		<div id="content" >
			<?php
				if (isset($_POST['acceptFriend'])) {
					$userId = $_POST['acceptFriend'];
					$error = false;
					
					// Check if userId is actually in requests
					$query = "SELECT * FROM `friendRequests` WHERE `recvuid` = :id AND `senduid` = :sid";
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
					$stmt->bindParam(':sid', $userId, PDO::PARAM_INT);
					$stmt->execute();
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					if ($stmt->rowCount() == 0) {
						$error = true;
					}
					
					// Check if request comes from the user itself
					if ($result['senduid'] == $auth_uid and $stmt->rowCount() > 0) {
						$query = "DELETE FROM `friendRequests` WHERE `senduid` = :sid AND `recvuid` = :id;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
						$stmt->bindParam(':sid', $userId, PDO::PARAM_INT);
						$stmt->execute();
						$error = true;
					}
					
					// Check if the friend is already a friend, if yes; delete request.
					$query = "SELECT * FROM `friends` WHERE `userId1` = :id AND `userId2` = :sid";
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
					$stmt->bindParam(':sid', $userId, PDO::PARAM_INT);
					$stmt->execute();
					if ($stmt->rowCount() > 0) {
						// Delete request.
						$query = "DELETE FROM `friendRequests` WHERE `senduid` = :sid AND `recvuid` = :id;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
						$stmt->bindParam(':sid', $userId, PDO::PARAM_INT);
						$stmt->execute();
						$error = true;
					}
					
					// Check if the friend is already a friend, if yes; delete request, again.
					$query = "SELECT * FROM `friends` WHERE `userId1` = :sid AND `userId2` = :id";
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
					$stmt->bindParam(':sid', $userId, PDO::PARAM_INT);
					$stmt->execute();
					if ($stmt->rowCount() > 0) {
						// Delete request.
						$query = "DELETE FROM `friendRequests` WHERE `senduid` = :id AND `recvuid` = :sid;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
						$stmt->bindParam(':sid', $userId, PDO::PARAM_INT);
						$stmt->execute();
						$error = true;
					}
					
					if ($error == false) {
						// Add to friends.
						$query = "INSERT INTO friends (`userId1`, `userId2`) VALUES (:userId1, :userId2);";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':userId1', $auth_uid, PDO::PARAM_INT);
						$stmt->bindParam(':userId2', $userId, PDO::PARAM_INT);
						$stmt->execute();
						
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':userId1', $userId, PDO::PARAM_INT);
						$stmt->bindParam(':userId2', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
						
						// Delete request.
						$query = "DELETE FROM `friendRequests` WHERE `senduid` = :sid AND `recvuid` = :id;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
						$stmt->bindParam(':sid', $userId, PDO::PARAM_INT);
						$stmt->execute();
						
						// Send a message to user
						// Get user info
						$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
						$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
						$resultuinfo = $stmt->fetch(PDO::FETCH_ASSOC);
						$message = '<b><a href="/profile.php?id='.$resultuinfo['id'].'">'.htmlentities($resultuinfo['username'], ENT_QUOTES, "UTF-8").'</a></b> has accepted your friend request. Start a conversation by replying!';
						$query = "INSERT INTO messages (`recv_uid`, `sender_uid`, `title`, `content`) VALUES (:userId1, :userId2, 'Friend Request Accepted', :msg);";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':userId1', $userId, PDO::PARAM_INT);
						$stmt->bindParam(':userId2', $auth_uid, PDO::PARAM_INT);
						$stmt->bindParam(':msg', $message, PDO::PARAM_STR);
						$stmt->execute();
						
						header("Location: /friends/?mode=requests");
					}else{
						echo '<div class="alert alert-dismissible alert-danger">Could not add friend.</div>';
					}
				}
				
				if (isset($_POST['ignoreFriend'])) {
					$userId = $_POST['ignoreFriend'];
					$query = "DELETE FROM `friendRequests` WHERE `senduid` = :sid AND `recvuid` = :id;";
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
					$stmt->bindParam(':sid', $userId, PDO::PARAM_INT);
					$stmt->execute();
					
					header("Location: /friends/?mode=requests");
				}
				
				if (isset($_POST['deleteFriend'])) {
					$userId = $_POST['deleteFriend'];
					$query = "DELETE FROM `friends` WHERE `userId1` = :sid AND `userId2` = :id;";
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
					$stmt->bindParam(':sid', $userId, PDO::PARAM_INT);
					$stmt->execute();
					
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
					$stmt->bindParam(':sid', $auth_uid, PDO::PARAM_INT);
					$stmt->execute();
					
					header("Location: /friends/?mode=showfriends");
				}
			?>
			<div class="well Center" style="box-shadow:none;">
				<div class="row">
				<?php
					$currentTime = date('Y-m-d H:i:s');
					if ($mode == "requests") {
						$stmt = $dbcon->prepare("SELECT * FROM `friendRequests` WHERE `recvuid` = :id ORDER BY id DESC LIMIT 10 OFFSET :offset;");
						$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
						$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
						$stmt->execute();
						$count = 0;
						if ($stmt->rowCount() == 0) {
							echo 'You do not have any friend request inbound.';
						}
						foreach($stmt as $result) {
							$count++;
							if ($count < 10) {
								$userId = $result['senduid'];
								// Get username
								$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id;");
								$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
								$stmt->execute();
								$resultuser = $stmt->fetch(PDO::FETCH_ASSOC);
								$username = $resultuser['username'];
								if (strlen($username) > 10) {
									$username = substr($username, 0, 7) . '...';
								}
								echo '<div class="col-xs-4"><a href="/profile.php?id='.$resultuser['id'].'"><img width="100" src="'.getImage($resultuser).'"></a><br>';
								$from_time = strtotime($resultuser['lastSeen']);
								$to_time = strtotime($currentTime);
								$timeSince =  round(abs($to_time - $from_time) / 60,2);
								if ($timeSince > 5){
									echo '<font color="grey">&#x25CF; </font>';
								}else{
									echo '<font color="green">&#x25CF; </font>';
								}
								echo '<a href="/profile.php?id='.$resultuser['id'].'"><b>'.htmlentities($username, ENT_QUOTES, "UTF-8").'</b></a><br><br>';
								echo '<form method="post"><button type="submit" name="acceptFriend" class="btn btn-success" value="'.$resultuser['id'].'">Accept</button><button type="submit" name="ignoreFriend" class="btn btn-danger" value="'.$resultuser['id'].'">Ignore</button></form>';
								echo '</div>';
							}
						}
					}else{
						// Get all friends.
						$stmt = $dbcon->prepare("SELECT * FROM `friends` WHERE `userId1` = :id ORDER BY id DESC LIMIT 10 OFFSET :offset;");
						$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
						$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
						$stmt->execute();
						$count = 0;
						if ($stmt->rowCount() == 0) {
							echo 'You do not have any friend on Graphictoria. Why not make some by sending anyone a request?';
						}
						foreach($stmt as $result) {
							$count++;
							if ($count < 10) {
								$userId = $result['userId2'];
								// Get username
								$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
								$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
								$stmt->execute();
								$resultuser = $stmt->fetch(PDO::FETCH_ASSOC);
								$username = $resultuser['username'];
								if (strlen($username) > 10) {
									$username = substr($username, 0, 7) . '...';
								}
								echo '<div class="col-xs-4"><a href="/profile.php?id='.$resultuser['id'].'"><img width="100" src="'.getImage($resultuser).'"></a><br>';
								$from_time = strtotime($resultuser['lastSeen']);
								$to_time = strtotime($currentTime);
								$timeSince =  round(abs($to_time - $from_time) / 60,2);
								if ($timeSince > 5){
									echo '<font color="grey">&#x25CF; </font>';
								}else{
									echo '<font color="green">&#x25CF; </font>';
								}
								echo '<a href="/profile.php?id='.$resultuser['id'].'"><b>'.htmlentities($username, ENT_QUOTES, "UTF-8").'</b></a><br><br>';
								echo '<form method="post"><button type="submit" name="deleteFriend" class="btn btn-danger" value="'.$resultuser['id'].'">Delete</button></form>';
								echo '</div>';
							}
						}
					}
				?>
				</div>
			</div>
			<?php
				if ($count == 0 and $page > 0) {
					header("Location: /friends/?mode=".$mode);
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				echo '<ul class="pager">';
				if ($page == 0) {
					echo '<li class="previous disabled"><a>&larr; Back</a></li>';
				}else{
					echo '<li class="previous"><a href="/friends/?mode='.$mode.'&page='.($page-1).'">&larr; Back</a></li>';
				}
				if ($count > 9) {
					echo '<li class="next"><a href="/friends/?mode='.$mode.'&page='.($page+1).'">Next &rarr;</a></li>';
				}else{
					echo '<li class="next disabled"><a>Next &rarr;</a></li>';
				}
				echo '</ul>';
			?>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>