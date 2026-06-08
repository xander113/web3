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
		<title>Messages</title>
	</head>
	<body>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
			if (isset($_GET['page'])) {
				$page = $_GET['page'];
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
				header("Location: /messages/");
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
		?>
		<div id="content" class="col-xs-12 col-sm-12 col-md-10 col-sm-offset-0 col-md-offset-1">
			<?php
				if (isset($_GET['messageSent'])) {
					echo '<div class="alert alert-dismissible alert-success">Message sent</div>';
				}
				echo '<div class="panel panel-primary"><div class="panel-heading">Messages</div>';
				$stmt = $dbcon->prepare("SELECT * FROM messages WHERE recv_uid = :id");
				$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
				$stmt->execute();
				$numpages = ($stmt->rowCount()/10);
				if ($stmt->rowCount() == 0) {
					echo '<div class="panel-body">You don\'t have any message. You can send messages to users by visiting their profiles.</div>';
				}else{
					echo '<div class="table-responsive"><table class="table"><thead><tr><th>Name</th><th>Sender</th><th>Date</th></tr></thead>';
				}
				
				$stmt = $dbcon->prepare("SELECT * FROM messages WHERE recv_uid = :id ORDER BY date DESC LIMIT 11 OFFSET :offset;");
				$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
				$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
				$stmt->execute();
				$count = 0;
				foreach($stmt as $result) {
					$count++;
					if ($count < 11) {
						$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
						$stmt->bindParam(':id', $result['sender_uid'], PDO::PARAM_INT);
						$stmt->execute();
						$resultuser = $stmt->fetch(PDO::FETCH_ASSOC);
						if ($resultuser['rank'] == 1) {
							if ($result['read'] == 1) {
								echo '<tr><td><a style="text-decoration: none;" href="/messages/view.php?id='.$result['id'].'">'.htmlentities($result['title'], ENT_QUOTES, "UTF-8").'</a></td><td class="admin"><b><a style="color:inherit;text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></b></td><td>'.date('M j Y g:i A', strtotime($result['date'])).'</td></tr>';
							}else{
								echo '<tr><td><b><a style="text-decoration: none;" href="/messages/view.php?id='.$result['id'].'">'.htmlentities($result['title'], ENT_QUOTES, "UTF-8").'</a></b></td><td class="admin"><b><a style="color:inherit;text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></b></td><td><b>'.date('M j Y g:i A', strtotime($result['date'])).'</b></td></tr>';
							}
						}elseif($resultuser['rank'] == 2) {
							if ($result['read'] == 1) {
								echo '<tr><td><a style="text-decoration: none;" href="/messages/view.php?id='.$result['id'].'">'.htmlentities($result['title'], ENT_QUOTES, "UTF-8").'</a></td><td class="moderator"><b><a style="color:inherit;text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></b></td><td>'.date('M j Y g:i A', strtotime($result['date'])).'</td></tr>';
							}else{
								echo '<tr><td><b><a style="text-decoration: none;" href="/messages/view.php?id='.$result['id'].'">'.htmlentities($result['title'], ENT_QUOTES, "UTF-8").'</a></b></td><td class="moderator"><b><a style="color:inherit;text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></b></td><td><b>'.date('M j Y g:i A', strtotime($result['date'])).'</b></td></tr>';
							}
						}else{
							if ($result['read'] == 1) {
								echo '<tr><td><a style="text-decoration: none;" href="/messages/view.php?id='.$result['id'].'">'.htmlentities($result['title'], ENT_QUOTES, "UTF-8").'</a></td><td><a style="text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></td><td>'.date('M j Y g:i A', strtotime($result['date'])).'</td></tr>';
							}else{
								echo '<tr><td><b><a style="text-decoration: none;" href="/messages/view.php?id='.$result['id'].'">'.htmlentities($result['title'], ENT_QUOTES, "UTF-8").'</a></b></td><td><b><a style="text-decoration: none;" href="/profile.php?id='.$resultuser['id'].'">'.$resultuser['username'].'</a></b></td><td><b>'.date('M j Y g:i A', strtotime($result['date'])).'</b></td></tr>';
							}
						}
					}
				}
				if ($count == 0 and $page > 0) {
					if ($page == round($numpages)) {
						header("Location: /messages/?page=".($page-1));	
					}else{
						header("Location: /messages/");
					}
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				echo '</tbody></table></div></div>';
				
				$remlater = false;
				$numpages = $numpages-1;
				if ($count > 10){
					$numpages = ($numpages+1);
				}else{
					$remlater = true;
				}
					
				if ($count == 0 && $page > ($numpages)){
					if ($page !== 0){
						header('Location: /messages/?page='.round($numpages));
					}
				}
				if ($page < 0){
					header('Location: /messages/?page=0');
				}
				echo '<ul class="pagination">';
				if ($page !== 0){
					echo '<li class="previous"><a href="/messages/?page=0">&larr;</a></li>';
					echo '<li><a href="'.'/messages/?page='.($page - 1).'">&laquo;</a></li>';
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
						echo '<li><a href="/messages/?page='.$k.'">'.($k +1).'</a></li>';
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
				if ($count > 10){
					$numpages = ($numpages+1);
				}else{
					$numpages = ($numpages-1);
				}
				if (($page) < round($numpages)){
					echo '<li><a href="'.'/messages/?page='.($page + 1).'">&raquo;</a></li>';
				}else{
					if ($count > 10) {
						echo '<li><a href="'.'/messages/?page='.($page + 1).'">&raquo;</a></li>';
					}else{
						echo '<li class="disabled"><a>&raquo;</a></li>';
					}
				}
				if ($page < round($numpages)){
					echo '<li class="next"><a href="/messages/?page='.$lastK.'">&rarr;</a></li>';
				}else{
					if ($count > 10) {
						echo '<li class="next"><a href="/messages/?page='.$lastK.'">&rarr;</a></li>';
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