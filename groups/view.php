<?php
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php
			echo getHead();
		?>
		<title><?php echo getName();?> | Group</title>
		<script src="/html/js/tinymce/tinymce.min.js"></script>
	</head>
	<body>
		<div id="content" >
		<div class="modal fade" id="leaveGroup" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><font color="grey">Leaving Group...</font></h4>
					</div>
					<div class="modal-body">
						<p>You are about to leave a group! Are you sure you want to do that?</p>
					</div>
					<div class="modal-footer">
						<button style="display:inline;" type="button" class="btn btn-default" data-dismiss="modal">No</button>
						<form method="post" style="display:inline;">
							<button style="display:inline;" type="submit" name="lGroup" class="btn btn-danger">Leave Group</button>
						</form>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal fade" id="leaveGroup2" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><font color="grey">Leaving Group...</font></h4>
					</div>
					<div class="modal-body">
						<p>You are leaving a group while owning it, <b><font color="red">this will delete the group entirely!</font></b><br>Are you sure you want to do this?</p>
					</div>
					<div class="modal-footer">
						<button style="display:inline;" type="button" class="btn btn-default" data-dismiss="modal">No</button>
						<form method="post" style="display:inline;">
							<button style="display:inline;" type="submit" name="leaveDeleteGroup" class="btn btn-danger">Leave and delete Group</button>
						</form>
					</div>
				</div>
			</div>
		</div>
		
			<?php
				if (isset($_GET['id'])) {
					$groupId = $_GET['id'];
					if (is_array($groupId)) {
						$hAds = true;
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
						echo '<title>Graphictoria | Error</title>';
						echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Invalid parameter.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
					if (strlen($groupId) == 0) {
						$hAds = true;
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
						echo '<div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3">';
						echo '<div class="well well-sm Center">Group not specified.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						echo '</body></html>';
						exit;
					}
				}else{
					$hAds = true;
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
					echo '<div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3">';
					echo '<div class="well well-sm Center">Group not specified.</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					echo '</body></html>';
					exit;
				}
				
				if (isset($_GET['page'])) {
					$page = $_GET['page'];
					if (is_array($page)) {
						$hAds = true;
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
						echo '<title>Graphictoria | Error</title>';
						echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Invalid parameter.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
					$offset = $page*6;
					if ($page == 0){
						$page = 0;
						$offset = 0;
					}
				}else{
					$page = 0;
					$offset = 0;
				}
				if ($page < 0) {
					header("Location: /groups/view.php?id=".$groupId);
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
				$stmt = $dbcon->prepare("SELECT * FROM groups WHERE id = :id");
				$stmt->bindParam(':id', $groupId, PDO::PARAM_INT);
				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				$groupId = $result['id'];
				
				if ($stmt->rowCount() == 0) {
					echo '<div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3">';
					echo '<div class="well well-sm Center">Group not found.</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					echo '</body></html>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				
				if (isset($_GET['error'])) {
					$error = $_GET['error'];
					if ($error == 1) {
						echo '<div class="alert alert-dismissible alert-danger">You can only be part of 10 groups at a time.</div>';
					}
				}
				
				if (isset($_POST['joinGroup']) and $loggedIn == true) {
					// Check if not already a member
					$stmt = $dbcon->prepare("SELECT * FROM group_members WHERE uid = :uid AND gid = :id");
					$stmt->bindParam(':id', $groupId, PDO::PARAM_INT);
					$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
					$stmt->execute();
					$error = false;
					
					
					if ($stmt->rowCount() > 0) {
						$error = true;
					}
					
					// Check if not owned
					if ($result['cuid'] == $auth_uid) {
						$error = true;
					}
					
					$count = 0;
					$stmt = $dbcon->prepare("SELECT * FROM group_members WHERE uid = :id;");
					$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
					$stmt->execute();
					
					foreach($stmt as $result) {
						$count++;
					}
					
					$stmt = $dbcon->prepare("SELECT * FROM groups WHERE cuid = :id;");
					$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
					$stmt->execute();
					
					foreach($stmt as $result) {
						$count++;
					}
					
					$toomany = false;
					if ($count > 9 and $error == false) {
						$error = true;
						$toomany = true;
					}
					
					if ($error == false) {
						$query = "INSERT INTO group_members (`uid`, `gid`) VALUES (:uid, :gid);";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
						$stmt->bindParam(':gid', $groupId, PDO::PARAM_STR);
						$stmt->execute();
						
						header("Location: /groups/view.php?id=".$groupId);
					}else{
						if ($toomany == true) {
							header("Location: /groups/view.php?id=".$groupId.'&error=1');
						}else{
							header("Location: /groups/view.php?id=".$groupId);
						}
					}
				}
				
				if (isset($_POST['lGroup']) and $loggedIn == true) {
					$error = false;
					// Check if not owner.
					if ($result['cuid'] == $auth_uid) {
						$error = true;
					}
					
					
					// Check if member
					$stmt = $dbcon->prepare("SELECT * FROM group_members WHERE uid = :uid AND gid = :id");
					$stmt->bindParam(':id', $groupId, PDO::PARAM_INT);
					$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
					$stmt->execute();
					if ($stmt->rowCount() == 0) {
						$error = true;
					}
					
					if ($error == false) {
						// Leave group
						$query = "DELETE FROM `group_members` WHERE `gid`=:groupId AND `uid`=:userId;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
						$stmt->bindParam(':userId', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
						
						header("Location: /groups/view.php?id=".$groupId);
					}else{
						header("Location: /groups/view.php?id=".$groupId);
					}
				}
				
				if (isset($_POST['leaveDeleteGroup']) and $loggedIn == true) {
					$error = false;
					// Check if owner.
					if ($result['cuid'] != $auth_uid) {
						$error = true;
					}
					
					
					// Check if not a member
					$stmt = $dbcon->prepare("SELECT * FROM group_members WHERE uid = :uid AND gid = :id");
					$stmt->bindParam(':id', $groupId, PDO::PARAM_INT);
					$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
					$stmt->execute();
					if ($stmt->rowCount() > 0) {
						if ($error == false) {
							$error = true;
						}
					}
					
					if ($error == false) {
						// Delete group and all its members.
						$query = "DELETE FROM `group_members` WHERE `gid`=:groupId;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
						$stmt->execute();
						
						$query = "DELETE FROM `groups` WHERE `id`=:groupId;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
						$stmt->execute();
						
						header("Location: /groups/");
					}else{
						header("Location: /groups/view.php?id=".$groupId);
					}
				}
			?>
			<div class="well">
				<div class="row">
					<div class="col-xs-4">
						<h4><font color="grey"><?php echo htmlentities($result['name'], ENT_QUOTES, "UTF-8"); ?></font></h4>
						<?php
							$creator_uid = $result['cuid'];
							$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
							$stmt->bindParam(':id', $creator_uid, PDO::PARAM_INT);
							$stmt->execute();
							$resultuser = $stmt->fetch(PDO::FETCH_ASSOC);

							echo '<a href="/profile.php?id='.$resultuser['id'].'"><img width="100" src="'.getImage($resultuser).'"></a><br>';
						?>
						<b>Creator</b>: <a href="/profile.php?id=<?php echo $resultuser['id']; ?>"><?php echo htmlentities($resultuser['username'], ENT_QUOTES, "UTF-8"); ?></a><br>
						<b>Date Created</b>: <?php echo date('M j Y g:i:s A', strtotime($result['creationDate'])); ?>
						<?php
							// Get membership
							// Check if the user owns the group
							
							if ($loggedIn == true) {
								if ($result['cuid'] == $auth_uid) {
									// Delete and leave button
									echo '<button type="button" class="btn btn-danger FullWidth" data-toggle="modal" data-target="#leaveGroup2">Leave and delete Group</button>';
								}else{
									// Check if member
									$stmt = $dbcon->prepare("SELECT * FROM group_members WHERE uid = :uid AND gid = :id");
									$stmt->bindParam(':id', $groupId, PDO::PARAM_INT);
									$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
									$stmt->execute();
									
									if ($stmt->rowCount() > 0) {
										// Leave button
										echo '<button type="button" class="FullWidth btn btn-danger" data-toggle="modal" data-target="#leaveGroup">Leave Group</button>';
									}else{
										// Join button
										echo '<form method="post"><button name="joinGroup" type="submit" class="FullWidth btn btn-success">Join Group</button></form>';
									}
								}
							}else{
								echo '<a class="btn btn-success disabled FullWidth">Join Group</a>';
							}
							$allowed = false;
							if ($loggedIn) {
								if ($result['cuid'] == $auth_uid) {
									$allowed = true;
								}
							}
							if ($user_rankId > 0 and $user_rankId != 3) {
								$allowed = true;
							}
							
							if ($allowed == true and $loggedIn == true) {
								echo '<a class="btn btn-success FullWidth" href="/groups/admin.php?id='.$groupId.'">Group Admin</a>';
							}
						?>
					</div>
					<div class="col-xs-8">
						<h4><font color="grey">Description</font></h4>
						<?php
							$description = $result['description'];
							if (strlen($description) == 0) {
								$description = '<font color="grey">No Description</font>';
							}else{
								$description = htmlentities($description, ENT_QUOTES, "UTF-8");
							}
						?>
						<div class="content">
							<p><?php echo $description; ?></p>
						</div>
					</div>
				</div>
			</div>
			<div class="well">
				<h4><font color="grey" id="memberCount">Members</font></h4>
				<div class="row">
					<?php
						$stmt = $dbcon->prepare("SELECT * FROM group_members WHERE gid = :id ORDER BY id DESC LIMIT 7 OFFSET :offset;");
						$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
						$stmt->bindParam(':id', $groupId, PDO::PARAM_INT);
						$stmt->execute();
						
						$stmtc = $dbcon->prepare("SELECT * FROM group_members WHERE gid = :id;");
						$stmtc->bindParam(':id', $groupId, PDO::PARAM_INT);
						$stmtc->execute();
						
						echo '<script>$("#memberCount").html("Members ('.$stmtc->rowCount().')");</script>';
						$count = 0;
						if ($stmt->rowCount() == 0) {
							echo 'No members found.';
						}
						
						foreach($stmt as $result) {
							$count++;
							if ($count < 7) {
								$userId = $result['uid'];
								$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
								$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
								$stmt->execute();
								$resultuser = $stmt->fetch(PDO::FETCH_ASSOC);
								$username = $resultuser['username'];
								if (strlen($username) > 10) {
									$username = substr($username, 0, 7) . '...';
								}
								echo '<div class="col-xs-2 Center"><a href="/profile.php?id='.$resultuser['id'].'"><img width="100" src="https://xdiscuss.net/func/user/getImage.php?id='.$userId.'&type=user&time='.time().'"></a><br>';
								$from_time = strtotime($resultuser['lastSeen']);
								$to_time = strtotime($currentTime);
								$timeSince =  round(abs($to_time - $from_time) / 60,2);
								if ($timeSince > 5){
									echo '<font color="grey">&#x25CF; </font>';
								}else{
									echo '<font color="green">&#x25CF; </font>';
								}
								echo '<a href="/profile.php?id='.$resultuser['id'].'"><b>'.htmlentities($username, ENT_QUOTES, "UTF-8").'</b></a></div>';
							}
						}
					?>
				</div>
			</div>
			<?php
				if ($count == 0 and $page > 0) {
					header("Location: /groups/view.php?id=".$groupId);
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				echo '<ul class="pager">';
				if ($page == 0) {
					echo '<li class="previous disabled"><a>&larr; Back</a></li>';
				}else{
					echo '<li class="previous"><a href="/groups/view.php?id='.$groupId.'&page='.($page-1).'">&larr; Back</a></li>';
				}
				if ($count > 6) {
					echo '<li class="next"><a href="/groups/view.php?id='.$groupId.'&page='.($page+1).'">Next &rarr;</a></li>';
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