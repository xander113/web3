<?php
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
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
			if (isset($_GET['id'])) {
				$profile_id = $_GET['id'];
				if (is_array($profile_id)) {
					$hAds = true;
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
					echo '<br><br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Invalid parameter</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				if (strlen($profile_id) == 0) {
					$hAds = true;
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
					echo '<br><br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">No profile ID was specified.</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
			}else{
				$hAds = true;
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
				echo '<br><br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">No profile ID was specified.</div></div></div>';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			
			$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
			$stmt->bindParam(':id', $profile_id, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$profile_id = $result['id'];
			$username = $result['username'];
			if ($stmt->rowCount() == 0) {
				$hAds = true;
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
				echo '<br><br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">User not found.</div></div></div>';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
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
				header("Location: /friends/showfriends.php?id=".$profile_id);
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
		?>
		<div id="content" class="">
			<ul class="nav nav-tabs" style="background-color:rgba(255,255,255,.84);">
				<li><a><font color="black"><?php echo htmlentities($username, ENT_QUOTES, "UTF-8"); ?> 's friends</font></a></li>
			</ul>
			<div class="well Center" style="box-shadow:none;">
				<div class="row">
				<?php
					$currentTime = date('Y-m-d H:i:s');
					// Get all friends.
					$stmt = $dbcon->prepare("SELECT * FROM `friends` WHERE `userId1` = :id ORDER BY id DESC LIMIT 10 OFFSET :offset;");
					$stmt->bindParam(':id', $profile_id, PDO::PARAM_INT);
					$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
					$stmt->execute();
					$count = 0;
					if ($stmt->rowCount() == 0) {
						echo 'This user has no friends.';
					}
					foreach($stmt as $result) {
						$count++;
						if ($count < 10) {
							$userId = $result['userId2'];
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
							echo '</div>';
						}
					}
				?>
				</div>
			</div>
			<?php
				if ($count == 0 and $page > 0) {
					header("Location: /friends/showfriends.php?id=".$profile_id);
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				echo '<ul class="pager">';
				if ($page == 0) {
					echo '<li class="previous disabled"><a>&larr; Back</a></li>';
				}else{
					echo '<li class="previous"><a href="/friends/showfriends.php?id='.$profile_id.'&page='.($page-1).'">&larr; Back</a></li>';
				}
				if ($count > 9) {
					echo '<li class="next"><a href="/friends/showfriends.php?id='.$profile_id.'&page='.($page+1).'">Next &rarr;</a></li>';
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