<?php
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
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
					echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Forum not specified.</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
			}else{
				$hAds = true;
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
				echo '<title>Graphictoria | Error</title>';
				echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Forum not specified.</div></div></div>';
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
				header("Location: /forum/forum.php?id=".$id);
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			$stmt = $dbcon->prepare("SELECT * FROM forums WHERE id = :id AND developer = 0");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if ($stmt->rowCount() == 0) {
				$hAds = true;
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
				echo '<title>Graphictoria | Error</title>';
				echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Forum not found.</div></div></div>';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
			
			$catagory_id = $result['catid'];
			
			$stmt = $dbcon->prepare("SELECT name FROM catagories WHERE id = :id");
			$stmt->bindParam(':id', $catagory_id, PDO::PARAM_INT);
			$stmt->execute();
			$resultcat = $stmt->fetch(PDO::FETCH_ASSOC);
			
			$catagory_name = $resultcat['name'];
			
			$forum_name = $result['name'];
			echo '<title>'.getName().' | '.htmlentities($forum_name, ENT_QUOTES, "UTF-8").'</title>';
			$forum_id = $result['id'];
			$forum_description = $result['description'];
			if ($loggedIn == false) {
				echo '<br>';
			}
		?>
		<div id="content">
			<div class="col-xs-12 col-sm-12 col-md-10 col-sm-offset-0 col-md-offset-1">
			<?php
				if ($result['locked'] == 1) {
					if ($user_rankId == 1) {
						echo '<a style="margin:5px 0px;" href="/forum/newpost.php?id='.$forum_id.'" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> New Post</a>';
					}else{
						echo '<div style="margin:5px 0px;" class="btn btn-primary disabled"><span class="glyphicon glyphicon-remove"></span> Forum Locked</div>';
					}
				}else{
					echo '<a style="margin:5px 0px;" href="/forum/newpost.php?id='.$forum_id.'" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> New Post</a>';
				}
				echo '<ul class="breadcrumb" style="background-color:#eeeeee;border:1px solid #dddddd;margin-bottom:10px;"><li><a href="/forum/">Forum</a></li><li><a>'.htmlentities($catagory_name, ENT_QUOTES, "UTF-8").'</a></li><li><a>'.htmlentities($forum_name, ENT_QUOTES, "UTF-8").'</a></li></ul>';
				echo '<div class="panel panel-primary"><div class="panel-heading" style="padding:2px 15px"><h5>'.htmlentities($forum_name, ENT_QUOTES, "UTF-8").'</h5></div>';
				echo '<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Name</th><th>Author</th><th>Replies</th><th>Views</th><th>Locked</th><th>Last Post</th></tr></thead>';
				$stmt = $dbcon->prepare("SELECT * FROM topics WHERE forumId = :id AND pinned = 0");
				$stmt->bindParam(':id', $id, PDO::PARAM_INT);
				$stmt->execute();
				$numpages = ($stmt->rowCount()/20);
				
				$stmt = $dbcon->prepare("SELECT id, author_uid, title, locked, lastActivity, replies, views FROM topics WHERE forumId = :id AND pinned = 1 ORDER BY id");
				$stmt->bindParam(':id', $id, PDO::PARAM_INT);
				$stmt->execute();
				foreach($stmt as $result) {
					$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
					$stmt->bindParam(':id', $result['author_uid'], PDO::PARAM_INT);
					$stmt->execute();
					$resultuser = $stmt->fetch(PDO::FETCH_ASSOC);	
					if ($resultuser['rank'] == 1) {
						echo '<tr style="background-color: #fbf9f9;"><td><a style="text-decoration: none;color:#19588a;" href="/forum/post.php?id='.$result['id'].'">'.htmlentities(filter($result['title']), ENT_QUOTES, "UTF-8").'</a></td><td class="admin"><a href="/profile.php?id='.$resultuser['id'].'"><b><a style="color:inherit;text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></b></td><td style="color:#19588a;">'.$result['replies'].'</td><td style="color:#19588a;">'.$result['views'].'</td><td style="color:#19588a;">'; if($result['locked'] == 0){ echo 'No'; }else{ echo 'Yes'; }echo '</td><td style="color:#19588a;">'.date('n/j/Y g:i A', strtotime($result['lastActivity'])).'</td></tr>';
					}elseif($resultuser['rank'] == 2) {
						echo '<tr style="background-color: #fbf9f9;"><td><a style="text-decoration: none;color:#19588a;" href="/forum/post.php?id='.$result['id'].'">'.htmlentities(filter($result['title']), ENT_QUOTES, "UTF-8").'</a></td><td class="moderator"><a href="/profile.php?id='.$resultuser['id'].'"><b><a style="color:inherit;text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></b></td><td style="color:#19588a;">'.$result['replies'].'</td><td style="color:#19588a;">'.$result['views'].'</td><td style="color:#19588a;">'; if($result['locked'] == 0){ echo 'No'; }else{ echo 'Yes'; }echo '</td><td style="color:#19588a;">'.date('n/j/Y g:i A', strtotime($result['lastActivity'])).'</td></tr>';
					}elseif($resultuser['rank'] == 3) {
						echo '<tr style="background-color: #fbf9f9;"><td><a style="text-decoration: none;color:#19588a;" href="/forum/post.php?id='.$result['id'].'">'.htmlentities(filter($result['title']), ENT_QUOTES, "UTF-8").'</a></td><td class="gm"><a href="/profile.php?id='.$resultuser['id'].'"><b><a style="color:inherit;text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></b></td><td style="color:#19588a;">'.$result['replies'].'</td><td style="color:#19588a;">'.$result['views'].'</td><td style="color:#19588a;">'; if($result['locked'] == 0){ echo 'No'; }else{ echo 'Yes'; }echo '</td><td style="color:#19588a;">'.date('n/j/Y g:i A', strtotime($result['lastActivity'])).'</td></tr>';
					}else{
						echo '<tr style="background-color: #fbf9f9;"><td><a style="text-decoration: none;color:#19588a;" href="/forum/post.php?id='.$result['id'].'">'.htmlentities(filter($result['title']), ENT_QUOTES, "UTF-8").'</a></td><td style="color:#19588a;"><a href="/profile.php?id='.$resultuser['id'].'"><a style="text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></td><td>'.$result['replies'].'</td><td style="color:#19588a;">'.$result['views'].'</td><td style="color:#19588a;">'; if($result['locked'] == 0){ echo 'No'; }else{ echo 'Yes'; }echo '</td><td style="color:#19588a;">'.date('n/j/Y g:i A', strtotime($result['lastActivity'])).'</td></tr>';
					}
				}
				
				
				$stmt = $dbcon->prepare("SELECT id, author_uid, title, locked, lastActivity, replies, views FROM topics WHERE forumId = :id AND pinned = 0 ORDER BY lastActivity DESC LIMIT 21 OFFSET :offset;");
				$stmt->bindParam(':id', $id, PDO::PARAM_INT);
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
						if ($resultuser['rank'] == 1) {
							echo '<tr><td><a style="text-decoration: none;" href="/forum/post.php?id='.$result['id'].'">'.htmlentities(filter(filter($result['title'])), ENT_QUOTES, "UTF-8").'</a></td><td class="admin"><a href="/profile.php?id='.$resultuser['id'].'"><b><a class="Center" style="color:inherit;text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></b></td><td>'.$result['replies'].'</td><td>'.$result['views'].'</td><td>'; if($result['locked'] == 0){ echo 'No'; }else{ echo 'Yes'; }echo '</td><td>'.date('n/j/Y g:i A', strtotime($result['lastActivity'])).'</td></tr>';
						}elseif($resultuser['rank'] == 2) {
							echo '<tr><td><a style="text-decoration: none;" href="/forum/post.php?id='.$result['id'].'">'.htmlentities(filter($result['title']), ENT_QUOTES, "UTF-8").'</a></td><td class="moderator"><a href="/profile.php?id='.$resultuser['id'].'"><b><a class="Center" style="color:inherit;text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></b></td><td>'.$result['replies'].'</td><td>'.$result['views'].'</td><td>'; if($result['locked'] == 0){ echo 'No'; }else{ echo 'Yes'; }echo '</td><td>'.date('n/j/Y g:i A', strtotime($result['lastActivity'])).'</td></tr>';
						}elseif($resultuser['rank'] == 3) {
							echo '<tr><td><a style="text-decoration: none;" href="/forum/post.php?id='.$result['id'].'">'.htmlentities(filter($result['title']), ENT_QUOTES, "UTF-8").'</a></td><td class="gm"><a href="/profile.php?id='.$resultuser['id'].'"><b><a class="Center" style="color:inherit;text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></b></td><td>'.$result['replies'].'</td><td>'.$result['views'].'</td><td>'; if($result['locked'] == 0){ echo 'No'; }else{ echo 'Yes'; }echo '</td><td>'.date('n/j/Y g:i A', strtotime($result['lastActivity'])).'</td></tr>';
						}else{
							echo '<tr><td><a style="text-decoration: none;" href="/forum/post.php?id='.$result['id'].'">'.htmlentities(filter($result['title']), ENT_QUOTES, "UTF-8").'</a></td><td><a href="/profile.php?id='.$resultuser['id'].'"><a class="Center" style="text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></td><td>'.$result['replies'].'</td><td>'.$result['views'].'</td><td>'; if($result['locked'] == 0){ echo 'No'; }else{ echo 'Yes'; }echo '</td><td>'.date('n/j/Y g:i A', strtotime($result['lastActivity'])).'</td></tr>';
						}
					}
				}
				if ($count == 0 and $page > 0) {
					if ($page == round($numpages)) {
						header("Location: /forum/forum.php?id=".$id."&page=".($page-1));	
					}else{
						header("Location: /forum/forum.php?id=".$id);
					}
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				echo '</tbody></table></div></div>';
				
				$remlater = false;
				$numpages = $numpages-1;
				if ($count > 20){
					$numpages = ($numpages+1);
				}else{
					$remlater = true;
				}
					
				if ($count == 0 && $page > ($numpages)){
					if ($page !== 0){
						header('Location: forum.php?id='.$forum_id.'&page='.round($numpages));
					}
				}
				if ($page < 0){
					header('Location: forum.php?id='.$forum_id.'&page=0');
				}
				echo '<ul class="pagination">';
				if ($page !== 0){
					echo '<li class="previous"><a href="forum.php?id='.$forum_id.'&page=0">&larr;</a></li>';
					echo '<li><a href="'.'forum.php?id='.$forum_id.'&page='.($page - 1).'">&laquo;</a></li>';
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
						echo '<li><a href="forum.php?id='.$forum_id.'&page='.$k.'">'.($k +1).'</a></li>';
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
					echo '<li><a href="'.'forum.php?id='.$forum_id.'&page='.($page + 1).'">&raquo;</a></li>';
				}else{
					if ($count > 20) {
						echo '<li><a href="'.'forum.php?id='.$forum_id.'&page='.($page + 1).'">&raquo;</a></li>';
					}else{
						echo '<li class="disabled"><a>&raquo;</a></li>';
					}
				}
				if ($page < round($numpages)){
					echo '<li class="next"><a href="forum.php?id='.$forum_id.'&page='.$lastK.'">&rarr;</a></li>';
				}else{
					if ($count > 20) {
						echo '<li class="next"><a href="forum.php?id='.$forum_id.'&page='.$lastK.'">&rarr;</a></li>';
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