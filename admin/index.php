<?php
	$hAds = true;
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
	if ($loggedIn == false) {
		header("Location: /auth.php");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
	if ($user_rankId != 1 and $user_rankId != 2) {
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		header("Location: /");
		exit;
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo getName(); ?> | Admin Panel</title>
		<?php
			echo getHead();
		?>
	</head>
	<body>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
		?>
		<div id="content" >
			<h3 style="color:grey;">Admin Panel</h3>
			<p><a href="/admin/ban.php">Ban User</a></p>
			<p><a href="/admin/reports.php">Reports</a></p>
			<?php
				$stmt = $dbcon->prepare("SELECT * FROM catalog WHERE approved = 0 AND declined = 0;");
				$stmt->execute();
			?>
			<p><a href="/admin/statistics.php">Statistics</a></p>
			<p><a href="/admin/assets.php">Asset Approval (<?php echo $stmt->rowCount();?>)</a></p>
			<p><a href="/admin/pruneposts.php">Prune Posts (Admin only)</a></p>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>