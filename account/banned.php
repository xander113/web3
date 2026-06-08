<?php
	$banpage = true;
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
	if ($loggedIn == false) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}else{
		$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
		$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($result['banned'] == 0) {
			header("Location: /");
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
			exit;
		}
	}
	
	function humanTimingAuth ($time, $math) {
		$time = time()-$time;
		$time = $math-$time;
		$time = ($time<1)? 1 : $time;
		$tokens = array (
			31536000 => 'year',
			2592000 => 'month',
			604800 => 'week',
			86400 => 'day',
			3600 => 'hour',
			60 => 'minute',
			1 => 'second'
		);
		foreach ($tokens as $unit => $text) {
			if ($time < $unit) continue;
				$numberOfUnits = floor($time / $unit);
				return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo getName(); ?> | Account Suspended</title>
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
			$hAds = true;
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
		?>
		<div id="content" class="container">
			<?php
				if (isset($_POST['reactivate'])) {
					$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
					$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
					$stmt->execute();
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					
					if ($result['bantype'] != 0 and $result['bantype'] != 5) {
						if ($result['bantype'] == 1) {
							$stmt = $dbcon->prepare("UPDATE users SET bantype = 0 WHERE username = :user;");
							$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
							$stmt->execute();
							
							$stmt = $dbcon->prepare("UPDATE users SET banned = 0 WHERE username = :user;");
							$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
							$stmt->execute();
							
							header("Location: /");
						}
						if ($result['bantype'] == 2) {
							$currentTime = date('Y-m-d H:i:s');
							$to_time = strtotime($currentTime);
							$from_time = strtotime($result['bantime']);
							$timeSince =  round(abs($to_time - $from_time) / 60,2);
							if ($timeSince > 1440) {
								$stmt = $dbcon->prepare("UPDATE users SET bantype = 0 WHERE username = :user;");
								$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
								$stmt->execute();
								
								$stmt = $dbcon->prepare("UPDATE users SET banned = 0 WHERE username = :user;");
								$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
								$stmt->execute();
								
								header("Location: /");
							}else{
								echo '<div class="alert alert-dismissible alert-danger">Your ban has not been expired yet. Come back later.</div>';
							}
						}
						if ($result['bantype'] == 3) {
							$currentTime = date('Y-m-d H:i:s');
							$to_time = strtotime($currentTime);
							$from_time = strtotime($result['bantime']);
							$timeSince =  round(abs($to_time - $from_time) / 60,2);
							if ($timeSince > 10080) {
								$stmt = $dbcon->prepare("UPDATE users SET bantype = 0 WHERE username = :user;");
								$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
								$stmt->execute();
								
								$stmt = $dbcon->prepare("UPDATE users SET banned = 0 WHERE username = :user;");
								$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
								$stmt->execute();
								
								header("Location: /");
							}else{
								echo '<div class="alert alert-dismissible alert-danger">Your ban has not been expired yet. Come back later.</div>';
							}
						}
						if ($result['bantype'] == 4) {
							$currentTime = date('Y-m-d H:i:s');
							$to_time = strtotime($currentTime);
							$from_time = strtotime($result['bantime']);
							$timeSince =  round(abs($to_time - $from_time) / 60,2);
							if ($timeSince > 43200) {
								$stmt = $dbcon->prepare("UPDATE users SET bantype = 0 WHERE username = :user;");
								$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
								$stmt->execute();
								
								$stmt = $dbcon->prepare("UPDATE users SET banned = 0 WHERE username = :user;");
								$stmt->bindParam(':user', $result['username'], PDO::PARAM_STR);
								$stmt->execute();
								
								header("Location: /");
							}else{
								echo '<div class="alert alert-dismissible alert-danger">Your ban has not been expired yet. Come back later.</div>';
							}
						}
					}else{
						echo '<div class="alert alert-dismissible alert-danger">You can not reactivate your account while being deleted.</div>';
					}
				}
				if ($result['bantype'] == 0 or $result['bantype'] == 5) {
					$type = "Account Deleted";
				}elseif($result['bantype'] == 1) {
					$type = "Warning";
				}else{
					$type = "Banned";
				}
			?>
			<div class="well">
				<h4><?php echo $type;?></h4>
				<?php
					if ($type == "Account Deleted") {
						echo 'You will not be able to activate your account again.';
					}elseif($type == "Warning") {
						echo 'You will be able to re-activate your account now.';
					}else{
						echo 'Your account will be activatable once the ban period has expired.';
					}
				?>
				<br><br>
				<p>It has come to our attention that you have been violating the Graphictoria terms of services. Please know that we strongly enforce our rules.<br>
				<b>Reason for suspension:</b> <?php echo htmlentities($result['banreason'], ENT_QUOTES, "UTF-8");?><br>
				</p>
				<?php
					if ($type == "Banned") {
						$time = strtotime($result['bantime']);
						$timen = 0;
						if ($result['bantype'] == 2) {
							$timen = 86400;
						}elseif($result['bantype'] == 3) {
							$timen = 604800;
						}else{
							$timen = 2592000;
						}
						if (humanTimingAuth($time, $timen) == "1 second") {
							echo '<b>Your suspension has been expired. You may re-activate your account.</b>';
						}else{
							echo '<b>Your suspension will expire in:</b> '.humanTimingAuth($time, $timen);
						}
					}
				?>
				<form method="post">
					<?php
						if ($type != "Account Deleted") {
							echo '<button type="submit" class="btn btn-primary" name="reactivate">Press here to re-activate your account</button>';
						}
					?>
				</form>
			</div>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>