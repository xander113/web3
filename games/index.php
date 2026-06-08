<?php
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo getName(); ?> | Games</title>
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
		?>
		<div id="content">
			<script src="/func/games/page.js?tick=<?php echo time();?>"></script>
			<div class="col-xs-12 col-sm-12 col-md-10 col-sm-offset-0 col-md-offset-1">
				<div class="alert" style="background-color:#0c7cd5;border-radius:0px;margin-bottom:0px;">Looking to host a server? You can add a server <a href="/games/newserver.php">here</a>!</div>
				<div id="addKeyResult"></div>
				<div class="alert" style="background-color:#212121;margin-bottom:0px;border-radius:0px;padding:5px;">
					<span class="fa fa-key"></span> Enter key directly : <input id="serverKey" type="text" class="form-control" placeholder="Server key"></input>
					<button style="width:100%;" id="addServer" class="btn btn-success"><span class="fa fa-plus"></span> Add Private Server</button>
				</div>
				<div class="btn-group btn-group-justified" style="margin-bottom:23px;">
					<a href="#" class="btn btn-default" id="showPublic" style="-webkit-box-shadow:none;box-shadow:none;">View Public Servers</a>
					<a href="#" class="btn btn-default" id="showMy" style="-webkit-box-shadow:none;box-shadow:none;">Private Servers</a>
					<a href="#" class="btn btn-default" id="showMyS" style="-webkit-box-shadow:none;box-shadow:none;">My Servers</a>
					<a href="/core/views/games/download.php" class="btn btn-default" style="-webkit-box-shadow:none;box-shadow:none;">Download Client</a>
				</div>
				<div id="result">
					<div class="Center"><i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i></div>
				</div>
			</div>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>