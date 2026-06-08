<?php
	$hAds = true;
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo getName(); ?> | 403</title>
		<?php
			echo getHead();
		?>
	</head>
	<body>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
			if ($loggedIn == false) {
				echo '<br><br>';
			}
		?>
		<div id="content" class="Center">
			<br>
			<div class="panel panel-primary">
				<div class="panel-heading">Access Denied</div>
				<div class="panel-body">
					<p>You tried to access something you may not access.</p>
					<a href="/" class="btn btn-primary FullWidth">Go Home</a>
				</div>
			</div>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>