<?php
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo getName(); ?> | Profile</title>
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
			if (isset($_GET['id'])) {
				$profile_id = $_GET['id'];
				if (is_array($profile_id)) {
					$hAds = true;
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
					echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Invalid parameter</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				if ($profile_id == 10370) {
					header("Location: /profile.php?id=0");
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				if ($profile_id == 0) {
					$profile_id = 10370;
				}
				if (strlen($profile_id) == 0) {
					$hAds = true;
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
					echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">No profile ID was specified.</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
			}else{
				$hAds = true;
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
				echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">No profile ID was specified.</div></div></div>';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
			$stmt->bindParam(':id', $profile_id, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$profile_id = $result['id'];
			if ($stmt->rowCount() == 0) {
				$hAds = true;
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
				echo '<br><div id="content" class=""><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">User not found.</div></div></div>';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			
			if ($loggedIn == true) {
				$unban_string = sha1($auth_formCode);
			}
			
			if (isset($_POST) and $loggedIn) {
				if (array_key_exists($unban_string, $_POST) and $user_rankId > 0 and $user_rankId != 3) {
					$userId = $_GET['id'];
					$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
					$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
					$stmt->execute();
					$resultunban = $stmt->fetch(PDO::FETCH_ASSOC);
					if ($resultunban['banned'] == 1) {
						if ($user_rankId != $resultunban['rank'] and $resultunban['rank'] != 1 and $resultunban['rank'] != 3) {
							$stmt = $dbcon->prepare("UPDATE users SET banned=0 WHERE id=:id");
							$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
							$stmt->execute();
						}else{
							if ($user_rankId == 1) {
								$stmt = $dbcon->prepare("UPDATE users SET banned=0 WHERE id=:id");
								$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
								$stmt->execute();
							}
						}
						
						header("Location: /profile.php?id=".$userId);
					}
				}
			}
			
			if ($result['banned'] == 1) {
				$hAds = true;
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
				echo '<br><div id="content" ><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">This user has been banned.</div>';
				if ($user_rankId > 0 and $user_rankId != 3) {
					if ($result['rank'] != $user_rankId and $result['rank'] != 3) {
						echo 'Username : '.htmlentities($result['username'], ENT_QUOTES, "UTF-8").'<br>';
						echo 'Reason : '.htmlentities($result['banreason'], ENT_QUOTES, "UTF-8");
						if ($result['rank'] != $user_rankId){
							echo '<form method="post"><button class="btn btn-danger" type="submit" name="'.$unban_string.'" value="'.$result['id'].'">Unban</button></form>';
						}else{
							if ($user_rankId == 1) {
								echo '<form method="post"><button class="btn btn-danger" type="submit" name="'.$unban_string.'" value="'.$result['id'].'">Unban</button></form>';
							}
						}
					}else{
						if ($user_rankId == 1) {
							echo 'Username : '.htmlentities($result['username'], ENT_QUOTES, "UTF-8");
							if ($result['rank'] != $user_rankId){
								echo '<form method="post"><button class="btn btn-danger" type="submit" name="unban" value="'.$result['id'].'">Unban</button></form>';
							}else{
								if ($user_rankId == 1) {
									echo '<form method="post"><button class="btn btn-danger" type="submit" name="unban" value="'.$result['id'].'">Unban</button></form>';
								}
							}
						}
					}
				}
				echo '</div></div>';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			
			if (isset($_POST['ban']) and $user_rankId > 0 and $user_rankId != 3) {
				$userId = $_POST['ban'];
				header("Location: /admin/ban.php?username=".$userId);
			}
			
			if (isset($_GET['page'])) {
				$page = $_GET['page'];
				$offset = $page*5;
				if ($page == 0){
					$page = 0;
					$offset = 0;
				}
			}else{
				$page = 0;
				$offset = 0;
			}
			if ($page < 0) {
				header("Location: /profile.php?id=".$profile_id."$page=0");
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
			
			// Check if user is already a friend
			$friend = false;
			$query = "SELECT * FROM `friends` WHERE `userId1` = :id AND `userId2` = :sid";
			$stmt = $dbcon->prepare($query);
			$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
			$stmt->bindParam(':sid', $profile_id, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$friend = true;
			}
			
			// Check if a friend request is already sent
			$requestSent = false;
			$query = "SELECT * FROM `friendRequests` WHERE `senduid` = :id AND `recvuid` = :sid";
			$stmt = $dbcon->prepare($query);
			$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
			$stmt->bindParam(':sid', $profile_id, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$requestSent = true;
			}
			
			$stmt->bindParam(':id', $profile_id, PDO::PARAM_INT);
			$stmt->bindParam(':sid', $auth_uid, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$requestSent = true;
			}
			
			if (isset($_POST['sendRequest']) and $loggedIn == true) {
				$userId = $_POST['sendRequest'];
				if ($userId == $profile_id and $userId != $auth_uid) {
					$error = false;
					
					// Check if already friends.
					$query = "SELECT * FROM `friends` WHERE `userId1` = :id AND `userId2` = :sid";
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
					$stmt->bindParam(':sid', $profile_id, PDO::PARAM_INT);
					$stmt->execute();
					if ($stmt->rowCount() > 0) {
						$error = true;
					}
					
					// Check if friend request already exists
					$query = "SELECT * FROM `friendRequests` WHERE `senduid` = :id AND `recvuid` = :sid";
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
					$stmt->bindParam(':sid', $profile_id, PDO::PARAM_INT);
					$stmt->execute();
					if ($stmt->rowCount() > 0) {
						$error = true;
					}
					
					// Check if a minute delay is occuring, friend request bot patch.
					$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
					$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
					$stmt->execute();
					$resultAuth = $stmt->fetch(PDO::FETCH_ASSOC);
					$currentTime = date('Y-m-d H:i:s');
					$from_time = strtotime($resultAuth['lastFR']);
					$to_time = strtotime($currentTime);
					$timeSince =  round(abs($to_time - $from_time) / 60,2);
					if ($timeSince < 1) {
						$error = true;
					}else{
						$query = "UPDATE users SET lastFR = NOW() WHERE id=:id";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
					}
					
					if ($error == false) {
						// Create friend request
						$query = "INSERT INTO friendRequests (`senduid`, `recvuid`) VALUES (:userId1, :userId2);";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':userId1', $auth_uid, PDO::PARAM_INT);
						$stmt->bindParam(':userId2', $profile_id, PDO::PARAM_INT);
						$stmt->execute();
						
						header("Location: /profile.php?id=".$profile_id);
					}else{
						echo '<div class="alert alert-dismissible alert-danger">Please wait a bit before sending another friend request.</div>';
					}
				}
			}
			if (isset($_POST['deleteFriend']) and $loggedIn == true) {
				$userId = $_POST['deleteFriend'];
				if ($profile_id == $userId and $userId != $auth_uid) {
					$query = "DELETE FROM `friends` WHERE `userId1` = :sid AND `userId2` = :id;";
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
					$stmt->bindParam(':sid', $userId, PDO::PARAM_INT);
					$stmt->execute();
					
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
					$stmt->bindParam(':sid', $auth_uid, PDO::PARAM_INT);
					$stmt->execute();
					
					header("Location: /profile.php?id=".$profile_id);
				}
			}
			if ($loggedIn == false) {
				echo '<br>';
			}
		?>
		<div id="content" >
			<div class="col-xs-12 col-sm-12 col-md-6">
				<ul class="nav nav-tabs" style="background-color:rgba(255,255,255,.84);">
					<li><a><font color="black">
					<?php
					$currentTime = date('Y-m-d H:i:s');
					$from_time = strtotime($result['lastSeen']);
					$to_time = strtotime($currentTime);
					$timeSince =  round(abs($to_time - $from_time) / 60,2);
					$online = false;
					if ($timeSince > 5){
						echo '<font color="grey">&#x25CF; </font>';
					}else{
						$online = true;
						echo '<font color="green">&#x25CF; </font>';
					}
					echo htmlentities($result['username'], ENT_QUOTES, "UTF-8");
					?>
					</font></a></li>
				</ul>
				<div class="well" style="padding:0px;box-shadow:none;">
					<?php
						if ($result['inGame'] == 1 and $online == true) {
							if ($loggedIn) {
								$stmt = $dbcon->prepare("SELECT * FROM gameJoins WHERE uid = :id");
								$stmt->bindParam(':id', $profile_id, PDO::PARAM_INT);
								$stmt->execute();
								$resultGame = $stmt->fetch(PDO::FETCH_ASSOC);
								echo '<a href="GraphictoriaClient://'.$auth_gameKey.';'.$resultGame['gameId'].';'.$auth_uid.'" class="btn btn-info"><span class="fa fa-play"></span> In Game || Follow</a><br>';
							}else{
								echo '<a class="btn btn-info disabled"><span class="fa fa-play"></span> In Game</a><br>';
							}
						}
					?>
					<div class="Center">
					<?php
						if ($result['imgTime'] != 0) {
							echo "<script>
						$(document).ready(function() {
							setTimeout(function() {
								var myImageElement = document.getElementById('character');
								myImageElement.src = 'https://xdiscuss.net/func/user/getImage.php?id=".$result['id']."&type=user&tick=".$result['imgTime']."'
							}, 1000);
						});
					</script>";
						}
					?>
					<?php
						echo '<img id="character" src="'.getImage($result).'"><br>';
						if ($result['about'] !== null and $result['about'] !== "") {
							echo '<div class="content" style="white-space:pre-wrap;">'.htmlentities(filter($result['about']), ENT_QUOTES, "UTF-8").'</div>';
						}else{
							echo 'This user has not set any text to display here.';
						}
						if ($result['id'] != $auth_uid and $loggedIn == true) {
							echo '<form method="post"><a class="btn btn-primary FullWidth" href="/messages/newmessage.php?uid='.$result['id'].'">Send Message</a>';
							if ($friend == false) {
								if ($requestSent == true) {
									echo '<button class="btn btn-primary disabled">Friend Request Pending</button>';
								}else{
									echo '<button class="btn btn-primary" type="submit" name="sendRequest" value="'.$profile_id.'">Send Friend Request</button>';
								}
							}else{
								if ($friend == true) {
									echo '<button class="btn btn-danger" type="submit" name="deleteFriend" value="'.$profile_id.'">Remove as Friend</button>';
								}
							}
							if ($result['rank'] != 1 and $user_rankId > 0 and $user_rankId != 3) {
								if ($result['rank'] != $user_rankId and $result['rank'] != 1 and $result['rank'] != 3){
									echo '<button class="btn btn-danger" type="submit" name="ban" value="'.$result['username'].'">Ban</button>';
								}else{
									if ($user_rankId == 1 and $result['rank'] != 1) {
										echo '<button class="btn btn-danger" type="submit" name="ban" value="'.$result['username'].'">Ban</button>';
									}
								}
							}
							echo '</form>';
						}
					?>
					</div>
				</div>
				<ul class="nav nav-tabs" style="background-color:rgba(255,255,255,.84);">
					<li><a><font color="black">Statistics</font></a></li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane fade active in" id="about">
						<div class="well Center" style="box-shadow:none;">
							<div class="row">
								<div class="col-xs-4">
									<b>Joined</b><br> <?php echo date('n/j/Y', strtotime($result['joinDate'])); ?>
								</div>
								<div class="col-xs-4">
									<b>Last Seen</b><br> <?php 
									if ($result['lastSeen'] !== null) {
										echo date('n/j/Y', strtotime($result['lastSeen'])); 
									}else{
										echo 'Never';
									}
								?>
								</div>
								<div class="col-xs-4">
									<b>Forum Posts</b><br>
									<?php
										$postCount = getPostCount($result['id'], $dbcon);
										echo $postCount;
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<ul class="nav nav-tabs" style="background-color:rgba(255,255,255,.84);">
					<li><a><font color="black">Badges</font></a></li>
				</ul>
				<div class="well Center" style="box-shadow:none;">
					<div class="row">
						<?php
							$stmt = $dbcon->prepare("SELECT * FROM badges WHERE uid = :id");
							$stmt->bindParam(':id', $profile_id, PDO::PARAM_INT);
							$stmt->execute();
							if ($stmt->rowCount() == 0) {
								echo 'This user has no badges.';
							}
							$name = "";
							foreach($stmt as $resultBadge) {
								if ($resultBadge['badgeId'] == 1) {
									$name = "Administrator";
								}
								if ($resultBadge['badgeId'] == 2) {
									$name = "Administrator";
								}
								if ($resultBadge['badgeId'] == 3) {
									$name = "Moderator";
								}
								if ($resultBadge['badgeId'] == 4) {
									$name = "Forumer";
								}
								if ($resultBadge['badgeId'] == 5) {
									$name = "Member";
								}
								if ($resultBadge['badgeId'] == 6) {
									$name = "ROBLOX Staff";
								}
								if ($resultBadge['badgeId'] == 7) {
									$name = "Before 100";
								}
								echo '<div class="col-xs-4"><img width="100" src="/html/img/badges/'.$resultBadge['badgeId'].'.png?v=4"><br><b>'.$name.'</b><br><br></div>';
							}
						?>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-12 col-md-6">
				<ul class="nav nav-tabs" style="background-color:rgba(255,255,255,.84);">
					<li><a><font color="black" id="friendCount">Friends (0)</font></a></li>
				</ul>
				<div class="well Center" style="box-shadow:none;padding:5px;">
					<div class="row">
						<?php
							$stmtc = $dbcon->prepare("SELECT * FROM friends WHERE userId1 = :id;");
							$stmtc->bindParam(':id', $profile_id, PDO::PARAM_INT);
							$stmtc->execute();
							
							$stmt = $dbcon->prepare("SELECT * FROM friends WHERE userId1 = :id ORDER BY id DESC LIMIT 6;");
							$stmt->bindParam(':id', $profile_id, PDO::PARAM_INT);
							$stmt->execute();
							if ($stmt->rowCount() == 0) {
								echo 'This user has no friends.';
							}
							echo '<script>$("#friendCount").html("Friends ('.$stmtc->rowCount().')");</script>';
							foreach($stmt as $result) {
								$userId = $result['userId2'];
								// Get username from userId2
								$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
								$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
								$stmt->execute();
								$resultuser = $stmt->fetch(PDO::FETCH_ASSOC);
								$username = $resultuser['username'];
								if (strlen($username) > 10) {
									$username = substr($username, 0, 7) . '...';
								}
								echo '<div class="col-xs-4"><br>';
								echo '<a href="/profile.php?id='.$resultuser['id'].'"><img width="120" src="'.getImage($resultuser).'"></a><br>';
								$from_time = strtotime($resultuser['lastSeen']);
								$to_time = strtotime($currentTime);
								$timeSince =  round(abs($to_time - $from_time) / 60,2);
								if ($timeSince > 5){
									echo '<font color="grey">&#x25CF; </font>';
								}else{
									echo '<font color="green">&#x25CF; </font>';
								}
								echo '<a href="/profile.php?id='.$resultuser['id'].'"><b>'.htmlentities($username, ENT_QUOTES, "UTF-8").'</b></a><br><br></div>';
							}
							if ($stmtc->rowCount() > 6) {
								echo '<a href="/friends/showfriends.php?id='.$profile_id.'">Show all friends</a>';
							}
						?>
					</div>
				</div>
				
				<ul class="nav nav-tabs" style="background-color:rgba(255,255,255,.84);">
					<li><a><font color="black">Groups</font></a></li>
				</ul>
				<div class="well" style="box-shadow:none;">
					<?php
						// Get group memberships
						$count = 0;
						$stmt = $dbcon->prepare("SELECT * FROM group_members WHERE uid = :id;");
						$stmt->bindParam(':id', $profile_id, PDO::PARAM_INT);
						$stmt->execute();
						foreach($stmt as $result) {
							$count++;
							// Get group info
							$stmt = $dbcon->prepare("SELECT * FROM groups WHERE id = :id");
							$gId = $result['gid'];
							$stmt->bindParam(':id', $gId, PDO::PARAM_INT);
							$stmt->execute();
							$resultGroupM = $stmt->fetch(PDO::FETCH_ASSOC);
							echo '<a href="/groups/view.php?id='.$gId.'">'.htmlentities($resultGroupM['name'], ENT_QUOTES, "UTF-8").'</a>, ';
						}
						
						// Get group ownerships
						$stmt = $dbcon->prepare("SELECT * FROM groups WHERE cuid = :id;");
						$stmt->bindParam(':id', $profile_id, PDO::PARAM_INT);
						$stmt->execute();
						foreach($stmt as $result) {
							$count++;
							// Get group info
							echo '<a href="/groups/view.php?id='.$result['id'].'">'.htmlentities($result['name'], ENT_QUOTES, "UTF-8").'</a> ';
						}
						
						if ($count == 0) {
							echo '<div class="Center">This user is not in any group.</div>';
						}
					?>
				</div>
				<ul class="nav nav-tabs" style="background-color:rgba(255,255,255,.84);">
					<li><a><font color="black">Games</font></a></li>
				</ul>
				<div class="well Center" style="box-shadow:none;">
					<div class="row">
						<?php
							if (isset($_GET['page'])) {
								$page = $_GET['page'];
								$offset = $page*3;
								if ($page == 0){
									$page = 0;
									$offset = 0;
								}
							}else{
								$page = 0;
								$offset = 0;
							}
							if ($page < 0) {
								header("Location: /profile.php?id=".$profile_id);
								include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
								exit;
							}
							$stmt = $dbcon->prepare("SELECT * FROM games WHERE `creator_uid` = :id  ORDER BY id DESC LIMIT 4 OFFSET :offset;");
							$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
							$stmt->bindParam(':id', $profile_id, PDO::PARAM_INT);
							$stmt->execute();
							$count = 0;
							if ($stmt->rowCount() == 0) {
								echo 'This user has no games.';
							}
							foreach($stmt as $result) {
								$count++;
								if ($count < 4) {
									echo '<div class="row"><div class="col-xs-12"><h4 style="margin:20px 0;">'.htmlentities(filter($result['name']), ENT_QUOTES, "UTF-8").'</h4></div><div class="col-xs-12"><a href="/games/view.php?id='.$result['id'].'" class="btn btn-primary FullWidth">Details</a></div></div>';
								}
							}
							if ($count == 0 and $page > 0) {
								header("Location: /profile.php?id=".$profile_id);
								include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
								exit;
							}
						?>
					</div>
				</div>
				<?php
					echo '<ul class="pager">';
					if ($page == 0) {
						echo '<li class="previous disabled"><a>&larr; Back</a></li>';
					}else{
						echo '<li class="previous"><a href="/profile.php?id='.$profile_id.'&page='.($page-1).'">&larr; Back</a></li>';
					}
					if ($count > 3) {
						echo '<li class="next"><a href="/profile.php?id='.$profile_id.'&page='.($page+1).'">Next &rarr;</a></li>';
					}else{
						echo '<li class="next disabled"><a>Next &rarr;</a></li>';
					}
					echo '</ul>';
				?>
			</div>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>