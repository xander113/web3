<?php
	$hAds = true;
	$rmBr1 = true;
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo getName(); ?></title>
		<?php
			echo getHead();
		?>
	</head>
	<body>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
		?>
		<?php
			if ($loggedIn) {
				header("Location: /user/dashboard.php");
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
		?>
		<h3 style="color:grey">Graphictoria</h3>
		<div style="width:75%;padding-left:15px;padding-right:15px;">
			<h4 style="color:grey;">What to do?</h4>
			<ul>
				<li>Create an account and socialize with eachother using the forums and message system</li>
				<li>Customize your character with the items found in the Catalog</li>
				<li>Make friends through our friend system</li>
				<li>Play games with eachother, privately and publically</li>
			</ul>
			<a class="btn btn-primary" href="/auth.php">Create an account or Login</a>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>