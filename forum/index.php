<?php
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo getName(); ?> | Forum</title>
		<?php
			echo getHead();
		?>
	</head>
	<body>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
			if ($loggedIn == false) {
				echo '<br>';
			}
		?>
		<div id="content" >
			<div class="col-xs-12 col-sm-12 col-md-10 col-sm-offset-0 col-md-offset-1">
			<?php
				$stmt = $dbcon->prepare("SELECT name, id FROM catagories WHERE developer = 0");
				$stmt->execute();
				foreach($stmt as $result) {
					echo '<div class="panel panel-primary"><div class="panel-heading" style="padding:2px 15px"><h5>'.$result['name'].'</h5></div>';
					echo '<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Name</th><th>Description</th><th>Posts</th><th>Replies</th></tr></thead>';
					$stmt = $dbcon->prepare("SELECT * FROM forums WHERE catid = :id AND developer = 0");
					$stmt->bindParam(':id', $result['id'], PDO::PARAM_INT);
					$stmt->execute();
					echo '<tbody>';
					$totalReplies = 0;
					$totalPosts = 0;
					foreach($stmt as $result) {
						echo '<tr><td><a style="text-decoration: none;" href="forum.php?id='.$result['id'].'">'.htmlentities($result['name'], ENT_QUOTES, "UTF-8").'</a></td><td>'.htmlentities($result['description'], ENT_QUOTES, "UTF-8").'</td><td>'.$result['posts'].'</td><td>'.$result['replies'].'</td></tr>';
					}
					echo '</tbody></table></div></div>';
				}
				$currentTime = date('Y-m-d H:i:s');
				$to_time = strtotime($currentTime);
				$stmt = $dbcon->prepare("SELECT lastSeen FROM users WHERE banned = 0 AND hideStatus = 0 ORDER BY lastSeen DESC;");
				$stmt->execute();
				$hcount = 0;
				
				foreach($stmt as $result) {
					$from_time = strtotime($result['lastSeen']);
					$timeSince =  round(abs($to_time - $from_time) / 60,2);
					if ($timeSince < 1440){
						$hcount++;
					}
				}
				?>
				<script>
					$(document).ready(function() {
						console.log("Got online players");
						$("#onlineContainer").load("/func/user/forum/getOnline.php");
						setInterval(function() {
							$("#onlineContainer").load("/func/user/forum/getOnline.php");
						}, 5000);
					});
				</script>
				<div id="onlineContainer">
					<div class="panel panel-primary">
						<div class="panel-heading" id="count"><span class="fa fa-user"></span> Users currently online</div>
						<div class="panel-body">
						</div>
					</div>
				</div>
				<?php
					$stmt = $dbcon->prepare("SELECT id FROM users;");
					$stmt->execute();
				?>
				<div class="panel panel-primary">
					<div class="panel-heading">Statistics</div>
					<div class="panel-body">
						We currently have <?php echo $stmt->rowCount(); ?> registered users.<br>
						In the past 24 hours, there have been <?php echo $hcount; ?> users online.
					</div>
				</div>
		</div></div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>