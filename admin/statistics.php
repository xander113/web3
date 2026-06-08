<?php
	$hAds = true;
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
	if ($loggedIn == false) {
		header("Location: /auth.php");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
	if ($user_rankId != 1 and $user_rankId != 2) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo getName(); ?> | Statistics</title>
		<?php
			echo getHead();
		?>
	</head>
	<body>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
		?>
		<div id="content" >
			<?php
				$stmt = $dbcon->prepare("SELECT * FROM users;");
				$stmt->execute();
				$amusers = $stmt->rowCount();
				
				$stmt = $dbcon->prepare("SELECT * FROM users WHERE banned = 1;");
				$stmt->execute();
				$busers = $stmt->rowCount();
				
				$stmt = $dbcon->prepare("SELECT * FROM users WHERE banned = 0;");
				$stmt->execute();
				$ubusers = $stmt->rowCount();
				
				$stmt = $dbcon->prepare("SELECT * FROM topics");
				$stmt->execute();
				$topcount = $stmt->rowCount();
				
				$stmt = $dbcon->prepare("SELECT * FROM replies");
				$stmt->execute();
				$repcount = $stmt->rowCount();
				
				$totalp = ($topcount + $repcount);
				
				$stmt = $dbcon->prepare("SELECT * FROM users WHERE rank > 0;");
				$stmt->execute();
				$staffc = $stmt->rowCount();
				
				$stmt = $dbcon->prepare("SELECT * FROM catalog");
				$stmt->execute();
				$catcount = $stmt->rowCount();
				
				$stmt = $dbcon->prepare("SELECT * FROM `read`");
				$stmt->execute();
				$readc = $stmt->rowCount();
				
				$stmt = $dbcon->prepare("SELECT * FROM `wearing`");
				$stmt->execute();
				$wearc = $stmt->rowCount();
				
				$stmt = $dbcon->prepare("SELECT * FROM `messages`");
				$stmt->execute();
				$messagecount = $stmt->rowCount();
				
				$total = ($amusers + $busers + $ubusers + $topcount + $repcount + $totalp + $staffc + $catcount + $readc + $wearc + $messagecount);
			?>
			<h4 style="color:grey;">Statistics</h4>
			There are currently <b><?php echo $amusers;?></b> registered accounts.<br>
			There are <b><?php echo $totalp;?></b> forum posts.<br>
			There are <b><?php echo $readc;?></b> read forum topics.<br>
			There are <b><?php echo $messagecount;?></b> sent private messages.<br>
			There are <b><?php echo $wearc;?></b> items being worn.<br>
			There are <b><?php echo $topcount;?></b> forum topics.<br>
			There are <b><?php echo $repcount;?></b> forum replies.<br>
			There are <b><?php echo $busers;?></b> banned users.<br>
			There are <b><?php echo $ubusers;?></b> unbanned users.</br>
			There are <b><?php echo $catcount;?></b> catalog items.</br>
			There are <b><?php echo $staffc;?></b> staff members.</br>
			
			If we count this all up, we get the number <b><?php echo $total;?></b>.
			<br><a href="/admin/">Go back</a>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>