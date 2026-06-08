<?php
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<?php
			echo getHead();
		?>
		<style>
			h1, h2, h3, h4, h5, h6 {
				color: black;
			}
		</style>
	</head>
	<body>
		<?php
			$currentTime = date('Y-m-d H:i:s');
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
					echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Post not specified.</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
			}else{
				$hAds = true;
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
				echo '<title>Graphictoria | Error</title>';
				echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Post not specified.</div></div></div>';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
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
				$page = preg_replace("/[^0-9]/","", $page);
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
				header("Location: /forum/post.php?id=".$id);
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			$stmt = $dbcon->prepare("SELECT * FROM topics WHERE id = :id AND developer = 0");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if ($stmt->rowCount() == 0) {
				$hAds = true;
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
				echo '<title>Graphictoria | Error</title>';
				echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Post not found.</div></div></div>';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
			
			$post_name = filter($result['title']);
			$post_id = $result['id'];
			$locked_bystaff = $result['lockedByStaff'];
			$locked = $result['locked'];
			$forumId = $result['forumId'];
			$author = $result['author_uid'];
			$pinned = $result['pinned'];
			
			$stmt = $dbcon->prepare("SELECT * FROM forums WHERE id = :id");
			$stmt->bindParam(':id', $forumId, PDO::PARAM_INT);
			$stmt->execute();
			$resultforumInfo = $stmt->fetch(PDO::FETCH_ASSOC);
			$forum_name = $resultforumInfo['name'];
			$catagory_id = $resultforumInfo['catid'];
			
			$stmt = $dbcon->prepare("SELECT * FROM catagories WHERE id = :id");
			$stmt->bindParam(':id', $catagory_id, PDO::PARAM_INT);
			$stmt->execute();
			$resultcat = $stmt->fetch(PDO::FETCH_ASSOC);
			
			$catagory_name = $resultcat['name'];
			
			echo '<title>'.getName().' | '.htmlentities($post_name, ENT_QUOTES, "UTF-8").'</title>';
			// Make post read if unread
			$stmtr = $dbcon->prepare("SELECT * FROM `read` WHERE `userId` = :id AND `postId` = :pid;");
			$stmtr->bindParam(':id', $auth_uid, PDO::PARAM_INT);
			$stmtr->bindParam(':pid', $post_id, PDO::PARAM_INT);
			$stmtr->execute();
			$resultread = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($stmtr->rowCount() == 0) {
				$read = false;
			}else{
				$read = true;
			}
			if ($read == false and $loggedIn == true) {
				$query = "INSERT INTO `read` (`userId`, `postId`) VALUES (:userId, :postId);";
				$stmt = $dbcon->prepare($query);
				$stmt->bindParam(':postId', $post_id, PDO::PARAM_INT);
				$stmt->bindParam(':userId', $_COOKIE['auth_uid'], PDO::PARAM_INT);
				$stmt->execute();
				
				// Also update views by one
				$stmt = $dbcon->prepare("SELECT * FROM topics WHERE id = :id");
				$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
				$stmt->execute();
				$resultTopicInfoCount = $stmt->fetch(PDO::FETCH_ASSOC);
				$viewCount = $resultTopicInfoCount['views']+1;
				
				$stmt = $dbcon->prepare("UPDATE topics SET views = :views WHERE id = :id");
				$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
				$stmt->bindParam(':views', $viewCount, PDO::PARAM_INT);
				$stmt->execute();
			}
			
			$lock_string = sha1($auth_formCode);
			$unlock_string = md5(sha1($auth_formCode));
			$delete_string = md5(strrev($unlock_string));
			$pin_string = md5($unlock_string);
			$unpin_string = sha1($pin_string);
			if ($loggedIn == false) {
				echo '<br>';
			}
		?>
		<div id="content">
			<div class="col-xs-12 col-sm-12 col-md-10 col-sm-offset-0 col-md-offset-1">
			<form style="display:inline;" method="post">
				<?php
					$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
					$stmt->bindParam(':id', $result['author_uid'], PDO::PARAM_INT);
					$stmt->execute();
					$resultuser = $stmt->fetch(PDO::FETCH_ASSOC);
					$allowedToLock = false;
					$allowedToUnlock = false;
					if ($result['author_uid'] == $auth_uid or $user_rankId > 0 and $user_rankId !== 3) {
						if ($locked == 0) {
							echo '</form><button style="margin:5px 0px;display:inline;" href="#" data-toggle="modal" data-target="#lockPost" class="btn btn-primary"><span class="glyphicon glyphicon-lock"></span> Lock Post</button><form style="display:inline;" method="post">';
							echo '<div class="modal fade" id="lockPost" tabindex="-1" role="dialog" aria-labelledby="lockPostdia">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="lockPostdia"><font color="grey">Locking Post</font></h4>
         </div>
         <div class="modal-body">
			<p>Nobody except staff members will be able reply to this post until you unlock it. Are you sure you want to do this?</p>
         </div>
		<div class="modal-footer">
			<form method="post"><button style="margin:5px 0px;" type="submit" name="'.$lock_string.'" class="btn btn-primary"><span class="glyphicon glyphicon-lock"></span> Lock Post</button></form>
		</div>
	 </div>
   </div>
</div>';
							echo '<span style="margin:0px 1px;"></span>';
							$allowedToLock = true;
						}else{
							if ($locked_bystaff == 0) {
								echo '</form><button style="margin:5px 0px;display:inline;" href="#" data-toggle="modal" data-target="#unlockPost" class="btn btn-primary"><span class="glyphicon glyphicon-lock"></span> Unlock Post</button><form style="display:inline;" method="post">';
								echo '<span style="margin:0px 1px;"></span>';
								$allowedToUnlock = true;
							}else{
								if ($user_rankId > 0 and $user_rankId !== 3) {
									echo '</form><button style="margin:5px 0px;display:inline;" href="#" data-toggle="modal" data-target="#unlockPost" class="btn btn-primary"><span class="glyphicon glyphicon-lock"></span> Unlock Post</button><form style="display:inline;" method="post">';
									echo '<span style="margin:0px 1px;"></span>';
									$allowedToUnlock = true;
								}else{
									echo '<a style="margin:5px 0px;" class="btn btn-primary disabled"><span class="glyphicon glyphicon-alert"></span> ( Locked by Staff )</a> ';
									echo '<span style="margin:0px 1px;"></span>';
									$allowedToUnlock = false;
								}
							}
							
							if ($allowedToUnlock == true) {
								echo '<div class="modal fade" id="unlockPost" tabindex="-1" role="dialog" aria-labelledby="unlockPostdia">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="unlockPostdia"><font color="grey">Unlocking Post</font></h4>
         </div>
         <div class="modal-body">
			<p>Everyone will be able to reply on this post if you unlock this post. Are you sure you want to do that?</p>
         </div>
		<div class="modal-footer">
			<form method="post"><button style="margin:5px 0px;" type="submit" name="'.$unlock_string.'" class="btn btn-primary"><span class="glyphicon glyphicon-lock"></span> Unlock Post</button></form>
		</div>
	 </div>
   </div>
</div>';
							}
						}
					}
					if ($locked == 0) {
						if ($loggedIn == false) {
							echo '<a style="margin:5px 0px;" class="btn btn-primary" href="/forum/newreply.php?id='.$post_id.'"><span class="glyphicon glyphicon-comment"></span> New Reply</a>';
						}else{
							echo '<a style="margin:5px 5px;" class="btn btn-primary" href="/forum/newreply.php?id='.$post_id.'"><span class="glyphicon glyphicon-comment"></span> New Reply</a>';
						}
					}else{
						if ($user_rankId > 0) {
							echo '<a style="margin:5px 0px;" class="btn btn-primary" href="/forum/newreply.php?id='.$post_id.'"><span class="glyphicon glyphicon-comment"></span> New Reply</a>';
						}else{
							echo '<a style="margin:5px 0px;" class="btn btn-primary disabled" href=""><span class="glyphicon glyphicon-alert"></span> Post is Locked</a>';
						}
					}
					
					if ($user_rankId == 1) {
						if ($pinned == 0) {
							echo '</form><button style="margin:5px 0px;display:inline;" href="#" data-toggle="modal" data-target="#pinPost" class="btn btn-primary"><span class="glyphicon glyphicon-pushpin"></span> Pin Post</button><form style="display:inline;" method="post">';
							echo '<div class="modal fade" id="pinPost" tabindex="-1" role="dialog" aria-labelledby="pinPostdia">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="pinPostdia"><font color="grey">Pinning Post</font></h4>
         </div>
         <div class="modal-body">
			<p>Everyone will be able to see this post at the top of the forum. Are you sure you want to do this?</p>
         </div>
		<div class="modal-footer">
			<form method="post"><button style="margin:5px 0px;" type="submit" name="'.$pin_string.'" class="btn btn-primary"><span class="glyphicon glyphicon-pushpin"></span> Pin Post</button></form>
		</div>
	 </div>
   </div>
</div>';
						}else{
							echo '</form><button style="margin:5px 0px;display:inline;" href="#" data-toggle="modal" data-target="#unpinPost" class="btn btn-primary"><span class="glyphicon glyphicon-pushpin"></span> Unpin Post</button><form style="display:inline;" method="post">';
							echo '<div class="modal fade" id="unpinPost" tabindex="-1" role="dialog" aria-labelledby="pinPostdia">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="pinPostdia"><font color="grey">Unpinning Post</font></h4>
         </div>
         <div class="modal-body">
			<p>This post will no longer appear at the top of this forum. Are you sure you want to do this?</p>
         </div>
		<div class="modal-footer">
			<form method="post"><button style="margin:5px 0px;" type="submit" name="'.$unpin_string.'" class="btn btn-primary"><span class="glyphicon glyphicon-pushpin"></span> Unpin Post</button></form>
		</div>
	 </div>
   </div>
</div>';
						}
					}
					
					$allowedToDelete = false;
					if ($user_rankId > 0 and $resultuser['rank'] == 0 and $user_rankId !== 3) {
						$allowedToDelete = true;
						echo '</form> <button style="margin:5px 0px;display:inline;" href="#" data-toggle="modal" data-target="#deletePost" class="btn btn-danger"><span class="glyphicon glyphicon-remove"></span> Delete Post</button> <form style="display:inline;" method="post">';
					}
					if ($user_rankId == 1 and $allowedToDelete == false) {
						$allowedToDelete = true;
						echo '</form> <button style="margin:5px 0px;display:inline;" href="#" data-toggle="modal" data-target="#deletePost" class="btn btn-danger"><span class="glyphicon glyphicon-remove"></span> Delete Post</button> <form style="display:inline;" method="post">';
					}
					if ($user_rankId > 0 and $auth_uid == $author and $allowedToDelete == false and $user_rankId !== 3) {
						$allowedToDelete = true;
						echo '</form> <button style="margin:5px 0px;display:inline;" href="#" data-toggle="modal" data-target="#deletePost" class="btn btn-danger"><span class="glyphicon glyphicon-remove"></span> Delete Post</button> <form style="display:inline;" method="post">';
					}
					
					if ($allowedToDelete == true) {
						echo '<div class="modal fade" id="deletePost" tabindex="-1" role="dialog" aria-labelledby="delPostdia">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="delPostdia"><font color="grey">Deleting Post</font></h4>
         </div>
         <div class="modal-body">
			<p>This post will be removed forever, it can not be recovered after deletion. Are you sure you want to do that?</p>
         </div>
		<div class="modal-footer">
			<form method="post"><button style="margin:5px 0px;" type="submit" name="'.$delete_string.'" class="btn btn-danger"><span class="glyphicon glyphicon-remove"></span> Delete Post</button> </form>
		</div>
	 </div>
   </div>
</div>';
					}
				?>
			</form>
			<?php
				if (isset($_POST)) {
					if (array_key_exists($lock_string, $_POST) and $allowedToLock == true) {
						if ($user_rankId > 0) {
							$query = "UPDATE `topics` SET `locked`=1 WHERE `id`=:id;";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
							$stmt->execute();
							$query = "UPDATE `topics` SET `lockedByStaff`=1 WHERE `id`=:id;";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
							$stmt->execute();
						}else{
							$query = "UPDATE `topics` SET `locked`=1 WHERE `id`=:id;";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
							$stmt->execute();
						}
						header("Location: /forum/post.php?id=".$post_id.'&page='.$page);
					}
					
					if (array_key_exists($pin_string, $_POST) and $user_rankId == 1) {
						$query = "UPDATE `topics` SET `pinned`=1 WHERE `id`=:id;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
						$stmt->execute();
						
						header("Location: /forum/post.php?id=".$post_id.'&page='.$page);
					}
					
					if (array_key_exists($unpin_string, $_POST) and $user_rankId == 1) {
						$query = "UPDATE `topics` SET `pinned`=0 WHERE `id`=:id;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
						$stmt->execute();
						
						header("Location: /forum/post.php?id=".$post_id.'&page='.$page);
					}
					
					if (array_key_exists($unlock_string, $_POST) and $allowedToUnlock == true) {
						if ($user_rankId > 0) {
							$query = "UPDATE `topics` SET `locked`=0 WHERE `id`=:id;";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
							$stmt->execute();
							$query = "UPDATE `topics` SET `lockedByStaff`=0 WHERE `id`=:id;";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
							$stmt->execute();
						}else{
							$query = "UPDATE `topics` SET `locked`=0 WHERE `id`=:id;";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
							$stmt->execute();
						}
						header("Location: /forum/post.php?id=".$post_id.'&page='.$page);
					}
					
					if (array_key_exists($delete_string, $_POST) and $allowedToDelete == true) {
						$stmt = $dbcon->prepare("SELECT * FROM topics WHERE id = :id ORDER BY id DESC LIMIT 1;");
						$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
						$stmt->execute();
						$result = $stmt->fetch(PDO::FETCH_ASSOC);
						$userId = $result['author_uid'];
						
						$stmt = $dbcon->prepare("SELECT posts FROM users WHERE id = :id ORDER BY id DESC LIMIT 1;");
						$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
						$stmt->execute();
						$result = $stmt->fetch(PDO::FETCH_ASSOC);
						$posts = $result['posts']-1;
						$query = "UPDATE `users` SET `posts`=:posts WHERE `id`=:id;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
						$stmt->bindParam(':posts', $posts, PDO::PARAM_INT);
						$stmt->execute();
						
						$query = "DELETE FROM `topics` WHERE `id`=:id";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
						$stmt->execute();
						
						$stmt = $dbcon->prepare("SELECT * FROM replies WHERE postId = :id");
						$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
						$stmt->execute();
						foreach($stmt as $result) {
							$userId = $result['author_uid'];
							$stmt = $dbcon->prepare("SELECT posts FROM users WHERE id = :id ORDER BY id DESC LIMIT 1;");
							$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
							$stmt->execute();
							$result = $stmt->fetch(PDO::FETCH_ASSOC);
							$posts = $result['posts']-1;
							$query = "UPDATE `users` SET `posts`=:posts WHERE `id`=:id;";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
							$stmt->bindParam(':posts', $posts, PDO::PARAM_INT);
							$stmt->execute();
						}
						
						$query = "DELETE FROM `replies` WHERE `postId`=:id";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
						$stmt->execute();
						
						$query = "DELETE FROM `read` WHERE `postId`=:id";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
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
						
						ob_start();
						header("Location: /forum/forum.php?id=".$forumId);
					}
				}
				$OPPostTime = $result['postTime'];
				
				echo '<ul class="breadcrumb" style="background-color:#eeeeee;border:1px solid #dddddd;margin-bottom:10px;"><li><a href="/forum/">Forum</a></li><li><a href="/forum/">'.htmlentities($catagory_name, ENT_QUOTES, "UTF-8").'</a></li><li><a href="/forum/forum.php?id='.$forumId.'">'.htmlentities($forum_name, ENT_QUOTES, "UTF-8").'</a></li><li><a>'.htmlentities($post_name, ENT_QUOTES, "UTF-8").'</a></li></ul>';
				if ($resultuser['rank'] == 1) {
					$style = "info";
				}else{
					$style = "primary";
				}
			?>
			<div class="panel panel-<?php echo $style;?>">
				<div class="panel-heading"><b>Original Post</b> - Posted on <?php echo date('M j Y g:i A', strtotime($result['postTime']));?>
				<?php
					if ($result['author_uid'] == $auth_uid or $user_rankId == 1) {
						if ($result['locked'] == 0) {
							echo '<a href="/forum/edit.php?id='.$result['id'].'&type=post" style="padding:0px 0px;margin:0px 0px;float:right;box-shadow:none;" class="btn btn-'.$style.' btn-xs">Edit</a>';
						}else{
							if ($user_rankId == 1) {
								echo '<a href="/forum/edit.php?id='.$result['id'].'&type=post" style="padding:0px 0px;margin:0px 0px;float:right;box-shadow:none;" class="btn btn-'.$style.' btn-xs">Edit</a>';
							}
						}
					}
					if ($user_rankId > 0 and $user_rankId != 3) {
						$ip = $resultuser['lastIP'];
						if ($resultuser['rank'] == 1) {
							$ip = strtolower($resultuser['username']).'.energy ';
						}
						echo '| <b>IP : </b>'.$ip;
					}
				?>
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-2">
							<div class="Center">
								<?php
									$from_time = strtotime($resultuser['lastSeen']);
									$to_time = strtotime($currentTime);
									$timeSince =  round(abs($to_time - $from_time) / 60,2);
									if ($timeSince > 5){
										echo '<font color="grey">&#x25CF; </font>';
									}else{
										echo '<font color="green">&#x25CF; </font>';
									}
									if (strlen($resultuser['username']) > 16) {
										$resultuser['username'] = substr($resultuser['username'], 0, 13) . '...';
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
									}elseif ($resultuser['rank'] == 3) {
										echo '<b><a class="gm" href="/profile.php?id='.$resultuser['id'].'">'.htmlentities($resultuser['username'], ENT_QUOTES, "UTF-8").'</a></b><br>';
										echo 'Security Engineer<br>';
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
									echo '<br>Joined '.date('M j Y', strtotime($resultuser['joinDate']));
								?>
							</div>
						</div>
						<div class="col-md-10">
							<?php
								$content = filter($result['content']);
								$content = strip_tags($content);
								$content = htmlentities($content, ENT_QUOTES, "UTF-8");
								if ($resultuser['rank'] > 0) {
									$content = preg_replace("/\s*[a-zA-Z\/\/:\.]*youtube.com\/watch\?v=([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i","<iframe width=\"420\" height=\"315\" src=\"//www.youtube.com/embed/$1\" frameborder=\"0\" allowfullscreen></iframe>", $content);
									$content = preg_replace("/https?:\/\/[^ ]+?(?:\.jpg|\.png|\.gif)/",'<img src="$0">', $content);
								}
							?>
							<div class="content"><?php echo nl2br($content); ?>
							</div>
						</div>
					</div>
				</div>
				<?php
					$locked = $result['locked'];
					if ($user_rankId > 0 and $user_rankId !== 3) {
						echo '<div class="panel-footer"><form method="post">';
						if ($resultuser['banned'] == 0) {
							if ($resultuser['rank'] == 0) {
								echo '<a href="/admin/ban.php?username='.$resultuser['username'].'" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Ban User</a>';
							}else{
								if ($user_rankId == 1 and $resultuser['rank'] !== 1) {
									echo '<a href="/admin/ban.php?username='.$resultuser['username'].'" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Ban User</a>';
								}else{
									echo '<a class="btn btn-danger disabled btn-xs"><span class="glyphicon glyphicon-remove"></span> Ban User</a>';
								}
							}
						}else{
							if ($resultuser['rank'] == 0) {
								echo '<a href="/profile.php?id='.$resultuser['id'].'" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Unban User</a>';
							}else{
								if ($user_rankId == 1 and $resultuser['rank'] !== 1) {
									echo '<a href="/profile.php?id='.$resultuser['id'].'" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Unban User</a>';
								}else{
									echo '<a class="btn btn-danger disabled btn-xs"><span class="glyphicon glyphicon-remove"></span> Unban User</a>';
								}
							}
						}
						echo '</form>';
						if ($result['updatedBy'] !== 0) {
							$edited_by = $result['updatedBy'];
							$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
							$stmt->bindParam(':id', $edited_by, PDO::PARAM_INT);
							$stmt->execute();
							$resultEdit = $stmt->fetch(PDO::FETCH_ASSOC);
							
							echo '<br>Edited by '.$resultEdit['username'].' on '.date('M j Y g:i A', strtotime($result['updatedOn']));
						}
						echo '</div>';
					}else{
						if ($result['updatedBy'] !== 0) {
							$edited_by = $result['updatedBy'];
							$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
							$stmt->bindParam(':id', $edited_by, PDO::PARAM_INT);
							$stmt->execute();
							$resultEdit = $stmt->fetch(PDO::FETCH_ASSOC);
							
							echo '<div class="panel-footer">Edited by '.$resultEdit['username'].' on '.date('M j Y g:i A', strtotime($result['updatedOn'])).'</div>';
						}
					}
				?>
			</div>
			<?php
				$stmt = $dbcon->prepare("SELECT * FROM replies WHERE postid = :id");
				$stmt->bindParam(':id', $id, PDO::PARAM_INT);
				$stmt->execute();
				$numpages = ($stmt->rowCount()/10);
				
				$stmt = $dbcon->prepare("SELECT * FROM replies WHERE postId = :id ORDER BY id ASC LIMIT 11 OFFSET :offset");
				$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
				$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
				$stmt->execute();
				$replycount = $stmt->rowCount();
				$count = 0;
				foreach($stmt as $result) {
					$count++;
					if ($count < 11) {
						$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
						$stmt->bindParam(':id', $result['author_uid'], PDO::PARAM_INT);
						$stmt->execute();
						$resultuser = $stmt->fetch(PDO::FETCH_ASSOC);
						if ($resultuser['rank'] == 1) {
							$style = "info";
						}else{
							$style = "primary";
						}
						echo '<div class="panel panel-'.$style.'"><div class="panel-heading"><b>Reply</b> - Posted on '.date('M j Y g:i A', strtotime($result['post_time']));
						if ($result['author_uid'] == $auth_uid or $user_rankId == 1) {
							if ($locked == 0) {
								echo '<a href="/forum/edit.php?id='.$result['id'].'&type=reply" style="padding:0px 0px;margin:0px 0px;float:right;box-shadow:none;" class="btn btn-'.$style.' btn-xs">Edit</a>';
							}else{
								if ($user_rankId == 1) {
									echo '<a href="/forum/edit.php?id='.$result['id'].'&type=reply" style="padding:0px 0px;margin:0px 0px;float:right;box-shadow:none;" class="btn btn-'.$style.' btn-xs">Edit</a>';
								}
							}
						}
						if ($user_rankId > 0 and $user_rankId != 3) {
							$ip = $resultuser['lastIP'];
							if ($resultuser['rank'] == 1) {
								$ip = strtolower($resultuser['username']).'.energy ';
							}
							echo ' | <b>IP : </b>'.$ip;
						}
						echo '</div>';
						echo '<div class="panel-body"><div class="row"><div class="col-md-2"><div class="Center">';
						$from_time = strtotime($resultuser['lastSeen']);
						$timeSince =  round(abs($to_time - $from_time) / 60,2);
						if ($timeSince > 5){
							echo '<font color="grey">&#x25CF; </font>';
						}else{
							echo '<font color="green">&#x25CF; </font>';
						}
						if (strlen($resultuser['username']) > 16) {
							$resultuser['username'] = substr($resultuser['username'], 0, 13) . '...';
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
						}elseif ($resultuser['rank'] == 3) {
							echo '<b><a class="gm" href="/profile.php?id='.$resultuser['id'].'">'.htmlentities($resultuser['username'], ENT_QUOTES, "UTF-8").'</a></b><br>';
							echo 'Security Engineer<br>';
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
						echo '<br>Joined '.date('M j Y', strtotime($resultuser['joinDate']));
						$content = filter($result['content']);
						$content = strip_tags($content);
						$content = htmlentities($content, ENT_QUOTES, "UTF-8");
						if ($resultuser['rank'] > 0) {
							$content = preg_replace("/\s*[a-zA-Z\/\/:\.]*youtube.com\/watch\?v=([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i","<iframe width=\"420\" height=\"315\" src=\"//www.youtube.com/embed/$1\" frameborder=\"0\" allowfullscreen></iframe>", $content);
							$content = preg_replace("/https?:\/\/[^ ]+?(?:\.jpg|\.png|\.gif)/",'<img src="$0">', $content);
						}
						echo '</div></div><div class="col-md-10"><div class="content">'.nl2br($content).'</div></div></div></div>';
						$allowedToDeleteReply = false;
						if ($user_rankId > 0 and $user_rankId !== 3) {
							echo '<div class="panel-footer"><form method="post">';
							if ($resultuser['banned'] == 0) {
								if ($resultuser['rank'] == 0) {
									echo '<a href="/admin/ban.php?username='.$resultuser['username'].'" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Ban User</a>';
								}else{
									if ($user_rankId == 1 and $resultuser['rank'] !== 1) {
										echo '<a href="/admin/ban.php?username='.$resultuser['username'].'" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Ban User</a>';
									}else{
										echo '<a class="btn btn-danger disabled btn-xs"><span class="glyphicon glyphicon-remove"></span> Ban User</a>';
									}
								}
							}else{
								if ($resultuser['rank'] == 0) {
									echo '<a href="/profile.php?id='.$resultuser['id'].'" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Unban User</a>';
								}else{
									if ($user_rankId == 1 and $resultuser['rank'] !== 1) {
										echo '<a href="/profile.php?id='.$resultuser['id'].'" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-remove"></span> Unban User</a>';
									}else{
										echo '<a class="btn btn-danger disabled btn-xs"><span class="glyphicon glyphicon-remove"></span> Unban User</a>';
									}
								}
							}
							if ($resultuser['rank'] == 0) {
								echo ' <button type="submit" name="deletereply" value="'.$result['id'].'" class="btn btn-danger btn-xs">Delete Reply</button>';
								$allowedToDeleteReply = true;
							}else{
								if ($user_rankId == 1) {
									echo ' <button type="submit" name="deletereply" value="'.$result['id'].'" class="btn btn-danger btn-xs">Delete Reply</button>';
									$allowedToDeleteReply = true;
								}else{
									if ($auth_uid == $result['author_uid']) {
										echo ' <button type="submit" name="deletereply" value="'.$result['id'].'" class="btn btn-danger btn-xs">Delete Reply</button>';
										$allowedToDeleteReply = true;
									}else{
										echo ' <a class="btn btn-danger disabled btn-xs">Delete Reply</a>';
										$allowedToDeleteReply = false;
									}
								}
							}
							echo '</form>';
							if ($result['updatedBy'] !== 0) {
								$edited_by = $result['updatedBy'];
								$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
								$stmt->bindParam(':id', $edited_by, PDO::PARAM_INT);
								$stmt->execute();
								$resultEdit = $stmt->fetch(PDO::FETCH_ASSOC);
								
								echo '<br>Edited by '.$resultEdit['username'].' on '.date('M j Y g:i A', strtotime($result['updatedOn']));
							}
							echo '</div>';
						}else{
							if ($result['updatedBy'] !== 0) {
								$edited_by = $result['updatedBy'];
								$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
								$stmt->bindParam(':id', $edited_by, PDO::PARAM_INT);
								$stmt->execute();
								$resultEdit = $stmt->fetch(PDO::FETCH_ASSOC);
								
								echo '<div class="panel-footer">Edited by '.$resultEdit['username'].' on '.date('M j Y g:i A', strtotime($result['updatedOn'])).'</div>';
							}
						}
						echo '</div>';
					}
				}
				
				if ($locked == 0) {
					echo '<a style="margin:-30px 0px;" class="btn btn-primary" href="/forum/newreply.php?id='.$post_id.'"><span class="glyphicon glyphicon-comment"></span> New Reply</a>';
				}else{
					if ($user_rankId > 0) {
						echo '<a style="margin:-30px 0px;" class="btn btn-primary" href="/forum/newreply.php?id='.$post_id.'"><span class="glyphicon glyphicon-comment"></span> New Reply</a>';
					}else{
						echo '<a style="margin:-30px 0px;" class="btn btn-primary disabled" href=""><span class="glyphicon glyphicon-alert"></span> Post is Locked</a>';
					}
				}
				
				echo '<br>';
				
				if ($count == 0 and $page > 0) {
					if ($page == round($numpages)) {
						header("Location: /forum/post.php?id=".$post_id."&page=".($page-1));
					}else{
						header("Location: /forum/post.php?id=".$post_id);
					}
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				
				$remlater = false;
				$numpages = $numpages-1;
				if ($stmt->rowCount() > 10){
					$numpages = ($numpages+1);
				}else{
					$remlater = true;
				}
					
				if ($count == 0 && $page > ($numpages)){
					if ($page !== 0){
						header('Location: post.php?id='.$post_id.'&page='.round($numpages));
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
				}
				if ($page < 0){
					header('Location: post.php?id='.$post_id.'&page=0');
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				echo '<ul class="pagination">';
				if ($page !== 0){
					echo '<li class="previous"><a href="post.php?id='.$post_id.'&page=0">&larr;</a></li>';
					echo '<li><a href="'.'post.php?id='.$post_id.'&page='.($page - 1).'">&laquo;</a></li>';
				}else{
					echo '<li class="previous disabled"><a>&larr;</a></li>';
					echo '<li class="disabled"><a>&laquo;</a></li>';
				}
				if ($remlater == true){
					$numpages = ($numpages +1);
				}
				$pagesint = ($page+5);
				$pagesint2 = $numpages;
				if ($pagesint > $numpages){
					$pagesint = $numpages;
				}
				if ($pagesint < 1){
					$pagesint = 1;
				}
				$lastK = 0;
				for ($k = $page ; $k < ($pagesint); $k++){
					if ($k == $page){
						echo '<li class="active"><a>'.($k +1).'</a></li>';
					}else{
						echo '<li><a href="post.php?id='.$post_id.'&page='.$k.'">'.($k +1).'</a></li>';
					}
				}
				for ($k = $page ; $k < ($pagesint2); $k++){
					$lastK = $k;
				}
				if ($remlater == true){
					$numpages = ($numpages);
				}else{
					$numpages = ($numpages-1);
				}
				if ($stmt->rowCount() > 10){
					$numpages = ($numpages+1);
				}else{
					$numpages = ($numpages-1);
				}
				if (($page) < round($numpages)){
					echo '<li><a href="'.'post.php?id='.$post_id.'&page='.($page + 1).'">&raquo;</a></li>';
				}else{
					if ($count > 10) {
						echo '<li><a href="'.'post.php?id='.$post_id.'&page='.($page + 1).'">&raquo;</a></li>';
					}else{
						echo '<li class="disabled"><a>&raquo;</a></li>';
					}
				}
				if ($page < round($numpages)){
					echo '<li class="next"><a href="post.php?id='.$post_id.'&page='.$lastK.'">&rarr;</a></li>';
				}else{
					if ($count > 10) {
						echo '<li class="next"><a href="post.php?id='.$post_id.'&page='.$lastK.'">&rarr;</a></li>';
					}else{
						echo '<li class="next disabled"><a>&rarr;</a></li>';
					}
				}
				if (isset($_GET['gotoLastPage'])) {
					header("Location: /forum/post.php?id=".$post_id.'&page='.$lastK);
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				
				if ($replycount == 0 and $page > 0) {
					header("Location: /forum/post.php?id=".$post_id);
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				
				if (isset($_POST['deletereply']) and $user_rankId > 0 and $allowedToDeleteReply == true) {
					$replyId = $_POST['deletereply'];
					
					$stmt = $dbcon->prepare("SELECT * FROM replies WHERE id = :id ORDER BY id DESC LIMIT 1;");
					$stmt->bindParam(':id', $replyId, PDO::PARAM_INT);
					$stmt->execute();
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					$userId = $result['author_uid'];
					$postId = $result['postId'];
					
					$stmt = $dbcon->prepare("SELECT posts FROM users WHERE id = :id ORDER BY id DESC LIMIT 1;");
					$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
					$stmt->execute();
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					
					$posts = $result['posts']-1;
					$query = "UPDATE `users` SET `posts`=:posts WHERE `id`=:id;";
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
					$stmt->bindParam(':posts', $posts, PDO::PARAM_INT);
					$stmt->execute();
					
					$query = "DELETE FROM `replies` WHERE `id`=:id";
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':id', $replyId, PDO::PARAM_INT);
					$stmt->execute();
					
					$stmt = $dbcon->prepare("SELECT * FROM replies WHERE postId = :id ORDER BY id DESC LIMIT 1;");
					$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
					$stmt->execute();
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					$postTime = $result['post_time'];
					
					if ($stmt->rowCount() > 0) {
						$query = "UPDATE `topics` SET `lastActivity`=:date WHERE `id`=:id;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
						$stmt->bindParam(':date', $postTime, PDO::PARAM_STR);
						$stmt->execute();
					}else{
						$query = "UPDATE `topics` SET `lastActivity`=:date WHERE `id`=:id;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
						$stmt->bindParam(':date', $OPPostTime , PDO::PARAM_STR);
						$stmt->execute();
					}
					
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
					
					$query = "SELECT * FROM replies WHERE postId=:id;";
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':id', $postId, PDO::PARAM_INT);
					$stmt->execute();
					$total = $stmt->rowCount();
						
					$stmt = $dbcon->prepare("UPDATE topics SET replies = :posts WHERE id=:id;");
					$stmt->bindParam(':id', $postId, PDO::PARAM_INT);
					$stmt->bindParam(':posts', $total, PDO::PARAM_INT);
					$stmt->execute();
					
					header("Location: /forum/post.php?id=".$post_id."&page=".$page);
				}
			?>
		</div>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>