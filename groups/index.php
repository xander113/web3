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
		<title><?php echo getName();?> | Groups</title>
		<script src="/html/js/tinymce/tinymce.min.js"></script>
	</head>
	<body>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
		?>
		<div id="content">
			<div class="well">
				<div class="Center">
					<h3 style="margin-bottom:-69.5px;margin-top:-33px;"><font color="grey" style="line-height:200px;">Groups</font></h3>
					<p>Groups will make team-work a lot easier, interact with friends and make new ones!</p>
					<div class="Center">
						<a href="/groups/new.php" style="padding:0px 16px;" class="btn btn-success">Create a Group</a>
					</div>
				</div>
			</div>
			<br>
			<div>
				<div class="well">
					<p class="Center">With groups you can manage clans easier than ever before, make new friends and host meetings.</p>
				</div>
			</div>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>