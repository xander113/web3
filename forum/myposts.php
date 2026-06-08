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
		<title><?php echo getName(); ?> | My Posts</title>
	</head>
	<body>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
			if (isset($_GET['page'])) {
				if (is_array($_GET['page'])) {
					$hAds = true;
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
					echo '<title>Graphictoria | Error</title>';
					echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Invalid parameter.</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				$page = preg_replace("/[^0-9]/","", $_GET['page']);
				$page = $_GET['page'];
				$offset = $page*20;
				if ($page == 0){
					$page = 0;
					$offset = 0;
				}
			}else{
				$page = 0;
				$offset = 0;
			}
			if ($page < 0) {
				header("Location: /forum/myposts.php");
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
		?>
		<div id="content" class="col-xs-12 col-sm-12 col-md-10 col-sm-offset-0 col-md-offset-1">
			<?php
				echo '<ul class="breadcrumb" style="background-color:#eeeeee;border:1px solid #dddddd;margin-bottom:10px;"><li><a href="/forum/">Forum</a></li><li><a>My Posts</a></li></ul>';
				echo '<div class="panel panel-primary"><div class="panel-heading" style="padding:2px 15px"><h5>My Posts</h5></div>';
				echo '<table class="table"><thead><tr><th>Name</th><th>Author</th><th>Replies</th><th>Locked</th><th>Last Post</th></tr></thead>';
				$stmt = $dbcon->prepare("SELECT * FROM topics WHERE author_uid = :id AND developer = 0;");
				$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
				$stmt->execute();
				$numpages = ($stmt->rowCount()/20);
				$stmt = $dbcon->prepare("SELECT * FROM topics WHERE author_uid = :id AND developer = 0 ORDER BY lastActivity DESC LIMIT 21 OFFSET :offset;");
				$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
				$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
				$stmt->execute();
				$count = 0;
				foreach($stmt as $result) {
					$count++;
					if ($count < 21) {
						$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
						$stmt->bindParam(':id', $result['author_uid'], PDO::PARAM_INT);
						$stmt->execute();
						$resultuser = $stmt->fetch(PDO::FETCH_ASSOC);
						
						$stmtr = $dbcon->prepare("SELECT * FROM `read` WHERE `userId` = :id AND `postId` = :pid");
						$stmtr->bindParam(':id', $auth_uid, PDO::PARAM_INT);
						$stmtr->bindParam(':pid', $result['id'], PDO::PARAM_INT);
						$stmtr->execute();
						$resultread = $stmt->fetch(PDO::FETCH_ASSOC);
						if ($stmtr->rowCount() == 0) {
							$read = false;
						}else{
							$read = true;
						}
						
						if ($resultuser['rank'] == 1) {
							if ($read == true) {
								echo '<tr><td><a style="text-decoration: none;" href="/forum/post.php?id='.$result['id'].'">'.htmlentities($result['title'], ENT_QUOTES, "UTF-8").'</a></td><td class="admin"><b><a style="color:inherit;text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></b></td><td>'.$result['replies'].'</td><td>'; if($result['locked'] == 0){ echo 'No'; }else{ echo 'Yes'; }echo '</td><td>'.date('M j Y g:i A', strtotime($result['lastActivity'])).'</td></tr>';
							}else{
								echo '<tr><td><b><a style="text-decoration: none;" href="/forum/post.php?id='.$result['id'].'">'.htmlentities($result['title'], ENT_QUOTES, "UTF-8").'</a></b></td><td class="admin"><b><a style="color:inherit;text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></b></td><td><b>'.$result['replies'].'</b></td><td><b>'; if($result['locked'] == 0){ echo 'No'; }else{ echo 'Yes'; }echo '</b></td><td><b>'.date('M j Y g:i A', strtotime($result['lastActivity'])).'</b></td></tr>';
							}
						}elseif($resultuser['rank'] == 2) {
							if ($read == true) {
								echo '<tr><td><a style="text-decoration: none;" href="/forum/post.php?id='.$result['id'].'">'.htmlentities($result['title'], ENT_QUOTES, "UTF-8").'</a></td><td class="moderator"><b><a style="color:inherit;text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></b></td><td>'.$result['replies'].'</td><td>'; if($result['locked'] == 0){ echo 'No'; }else{ echo 'Yes'; }echo '</td><td>'.date('M j Y g:i A', strtotime($result['lastActivity'])).'</td></tr>';
							}else{
								echo '<tr><td><b><a style="text-decoration: none;" href="/forum/post.php?id='.$result['id'].'">'.htmlentities($result['title'], ENT_QUOTES, "UTF-8").'</a></b></td><td class="moderator"><b><a style="color:inherit;text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></b></td><td><b>'.$result['replies'].'</b></td><td><b>'; if($result['locked'] == 0){ echo 'No'; }else{ echo 'Yes'; }echo '</b></td><td><b>'.date('M j Y g:i A', strtotime($result['lastActivity'])).'</b></td></tr>';
							}
						}elseif($resultuser['rank'] == 3) {
							if ($read == true) {
								echo '<tr><td><a style="text-decoration: none;" href="/forum/post.php?id='.$result['id'].'">'.htmlentities($result['title'], ENT_QUOTES, "UTF-8").'</a></td><td class="gm"><b><a style="color:inherit;text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></b></td><td>'.$result['replies'].'</td><td>'; if($result['locked'] == 0){ echo 'No'; }else{ echo 'Yes'; }echo '</td><td>'.date('M j Y g:i A', strtotime($result['lastActivity'])).'</td></tr>';
							}else{
								echo '<tr><td><b><a style="text-decoration: none;" href="/forum/post.php?id='.$result['id'].'">'.htmlentities($result['title'], ENT_QUOTES, "UTF-8").'</a></b></td><td class="gm"><b><a style="color:inherit;text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></b></td><td><b>'.$result['replies'].'</b></td><td><b>'; if($result['locked'] == 0){ echo 'No'; }else{ echo 'Yes'; }echo '</b></td><td><b>'.date('M j Y g:i A', strtotime($result['lastActivity'])).'</b></td></tr>';
							}
						}else{
							if ($read == true) {
								echo '<tr><td><a style="text-decoration: none;" href="/forum/post.php?id='.$result['id'].'">'.htmlentities($result['title'], ENT_QUOTES, "UTF-8").'</a></td><td><a style="text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></td><td>'.$result['replies'].'</td><td>'; if($result['locked'] == 0){ echo 'No'; }else{ echo 'Yes'; }echo '</td><td>'.date('M j Y g:i A', strtotime($result['lastActivity'])).'</td></tr>';
							}else{
								echo '<tr><td><b><a style="text-decoration: none;" href="/forum/post.php?id='.$result['id'].'">'.htmlentities($result['title'], ENT_QUOTES, "UTF-8").'</a></b></td><td><b><a style="text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></b></td><td><b>'.$result['replies'].'</b></td><td><b>'; if($result['locked'] == 0){ echo 'No'; }else{ echo 'Yes'; }echo '</b></td><td><b>'.date('M j Y g:i A', strtotime($result['lastActivity'])).'</b></td></tr>';
							}
						}
					}
				}
				if ($count == 0 and $page > 0) {
					header("Location: /forum/myposts.php");
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				echo '</tbody></table></div>';
				
				$remlater = false;
				$numpages = $numpages-1;
				if ($count > 20){
					$numpages = ($numpages+1);
				}else{
					$remlater = true;
				}
					
				if ($count == 0 && $page > ($numpages)){
					if ($page !== 0){
						header('Location: myposts.php?page='.round($numpages));
					}
				}
				if ($page < 0){
					header('Location: myposts.php?page=0');
				}
				echo '<ul class="pagination">';
				if ($page !== 0){
					echo '<li class="previous"><a href="myposts.php?page=0">&larr;</a></li>';
					echo '<li><a href="'.'myposts.php?page='.($page - 1).'">&laquo;</a></li>';
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
						echo '<li><a href="myposts.php?page='.$k.'">'.($k +1).'</a></li>';
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
				if ($count > 20){
					$numpages = ($numpages+1);
				}else{
					$numpages = ($numpages-1);
				}
				if (($page) < round($numpages)){
					echo '<li><a href="'.'myposts.php?page='.($page + 1).'">&raquo;</a></li>';
				}else{
					if ($count > 20) {
						echo '<li><a href="'.'myposts.php?page='.($page + 1).'">&raquo;</a></li>';
					}else{
						echo '<li class="disabled"><a>&raquo;</a></li>';
					}
				}
				if ($page < round($numpages)){
					echo '<li class="next"><a href="myposts.php?page='.$lastK.'">&rarr;</a></li>';
				}else{
					if ($count > 20) {
						echo '<li class="next"><a href="myposts.php?page='.$lastK.'">&rarr;</a></li>';
					}else{
						echo '<li class="next disabled"><a>&rarr;</a></li>';
					}
				}
			?>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>