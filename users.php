<?php
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo getName(); ?> | Users</title>
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
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
			if (isset($_GET['term'])) {
				$searchTerm = $_GET['term'];
				if (is_array($searchTerm)) {
					header("Location: /users.php");
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				if (strlen($searchTerm) == 0) {
					$searchTerm = "";
				}
			}
			if (isset($_GET['page'])) {
				$page = $_GET['page'];
				if (is_array($page)) {
					header("Location: /users.php");
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				$offset = $page*12;
				if ($page == 0){
					$page = 0;
					$offset = 0;
				}
			}else{
				$page = 0;
				$offset = 0;
			}
			
			if ($loggedIn == false) {
				echo '<br>';
			}
		?>
		<div id="content">
			<form method="post">
				<div class="form-group">
					<div class="input-group">
						<input type="text" class="form-control" name="username" placeholder="Username">
						<span class="input-group-btn">
							<button type="submit" class="btn btn-primary" name="search">Search</button>
						</span>
					</div>
				</div>
			</form>
			<?php
				if (isset($_POST['search'])) {
					$str = $_POST['username'];
					$searchTerm = $_POST['username'];
					header("Location: /users.php?page=0&term=".$searchTerm);
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				?>
			<?php
				$currentTime = date('Y-m-d H:i:s');
				if (isset($searchTerm)) {
					$searchTermSQL = '%'.$searchTerm.'%';
					$stmt = $dbcon->prepare("SELECT * FROM users WHERE username LIKE :term AND banned = 0 AND hideStatus = 0 ORDER BY id ASC LIMIT 11 OFFSET :offset;");
					$stmt->bindParam(':term', $searchTermSQL, PDO::PARAM_STR);
				}else{
					$stmt = $dbcon->prepare("SELECT * FROM users WHERE banned = 0 AND hideStatus = 0 ORDER BY lastSeen DESC LIMIT 11 OFFSET :offset;");
				}
				$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
				$stmt->execute();
				if ($stmt->rowCount() == 0) {
					echo 'Nothing found.';
				}
				$count = 0;
				foreach($stmt as $result) {
					$count++;
					if ($count < 11) {
						echo '<div class="well" style="box-shadow: none;">';
						echo '<div class="row">';
						echo '<div class="col-md-4 col-sm-12 col-xs-12">';
						echo '<a href="/profile.php?id='.$result['id'].'"><img width="150" src="'.getImage($result).'"></a>';
						echo '</div>';
						echo '<div class="col-md-8 col-sm-12 col-xs-12">';
						$from_time = strtotime($result['lastSeen']);
						$to_time = strtotime($currentTime);
						$timeSince =  round(abs($to_time - $from_time) / 60,2);
						if ($timeSince > 5){
							echo '<h4><font color="grey">&#x25CF; </font>';
						}else{
							echo '<h4><font color="green">&#x25CF; </font>';
						}
						echo htmlentities($result['username'], ENT_QUOTES, "UTF-8").'</h4>';
						if ($result['lastSeen'] !== NULL) {
							echo '<font color="grey">Last seen:</font> '.date('M j Y g:i A', strtotime($result['lastSeen'])).'<br>';
						}else{
							echo '<font color="grey">Last seen:</font> Never<br>';
						}
						echo '<div class="content">'.htmlentities(filter($result['about']), ENT_QUOTES, "UTF-8").'</div>';
						echo '</div></div>';
						echo '<a class="btn btn-primary FullWidth" href="/profile.php?id='.$result['id'].'">Visit Profile</a></div>';
					}
				}
				if ($count == 0 and $page > 0) {
					header("Location: /users.php");
					exit;
				}
			?>
			<?php
				echo '<ul class="pager">';
				if ($page == 0) {
					echo '<li class="previous disabled"><a>&larr; Back</a></li>';
				}else{
					if (isset($searchTerm)) {
						echo '<li class="previous"><a href="/users.php?page='.($page-1).'&term='.$searchTerm.'">&larr; Back</a></li>';
					}else{
						echo '<li class="previous"><a href="/users.php?page='.($page-1).'">&larr; Back</a></li>';
					}
				}
				if ($count > 10) {
					if (isset($searchTerm)) {
						echo '<li class="next"><a href="/users.php?page='.($page+1).'&term='.$searchTerm.'">Next &rarr;</a></li>';
					}else{
						echo '<li class="next"><a href="/users.php?page='.($page+1).'">Next &rarr;</a></li>';
					}
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