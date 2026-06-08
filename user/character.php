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
		<title><?php echo getName(); ?> | Character</title>
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
			if (isset($_GET['type'])) {
				$type = $_GET['type'];
				if ($type == "hats") {
					$type = "hats";
				}elseif($type == "shirts") {
					$type = "shirts";
				}elseif($type == "pants") {
					$type = "pants";
				}elseif($type == "gear") {
					$type = "gear";
				}elseif($type == "colors") {
					$type = "colors";
				}elseif($type == "tshirts") {
					$type = "tshirts";
				}elseif($type == "faces") {
					$type = "faces";
				}elseif($type == "heads") {
					$type = "heads";
				}else{
					$type = "colors";
				}
			}else{
				$type = "colors";
			}
		?>
		<div id="content" class="">
			<div class="btn-group btn-group-justified" style="margin:10px 0px;">
				<?php
					if ($type == "colors") {
						echo '<a href="/user/character.php?type=colors" class="btn btn-primary" style="background-color : #158cba;">Colors</a>';
					}else{
						echo '<a href="/user/character.php?type=colors" class="btn btn-primary">Colors</a>';
					}
					if ($type == "hats") {
						echo '<a href="/user/character.php?type=hats" class="btn btn-primary" style="background-color : #158cba;">Hats</a>';
					}else{
						echo '<a href="/user/character.php?type=hats" class="btn btn-primary">Hats</a>';
					}
					if ($type == "heads") {
						echo '<a href="/user/character.php?type=heads" class="btn btn-primary" style="background-color : #158cba;">Heads</a>';
					}else{
						echo '<a href="/user/character.php?type=heads" class="btn btn-primary">Heads</a>';
					}
					if ($type == "faces") {
						echo '<a href="/user/character.php?type=faces" class="btn btn-primary" style="background-color : #158cba;">Faces</a>';
					}else{
						echo '<a href="/user/character.php?type=faces" class="btn btn-primary">Faces</a>';
					}
					if ($type == "tshirts") {
						echo '<a href="/user/character.php?type=tshirts" class="btn btn-primary" style="background-color : #158cba;">T-Shirts</a>';
					}else{
						echo '<a href="/user/character.php?type=tshirts" class="btn btn-primary">T-Shirts</a>';
					}
					if ($type == "shirts") {
						echo '<a href="/user/character.php?type=shirts" class="btn btn-primary" style="background-color : #158cba;">Shirts</a>';
					}else{
						echo '<a href="/user/character.php?type=shirts" class="btn btn-primary">Shirts</a>';
					}
					if ($type == "pants") {
						echo '<a href="/user/character.php?type=pants" class="btn btn-primary" style="background-color : #158cba;">Pants</a>';
					}else{
						echo '<a href="/user/character.php?type=pants" class="btn btn-primary">Pants</a>';
					}
					if ($type == "gear") {
						echo '<a href="/user/character.php?type=gear" class="btn btn-primary" style="background-color : #158cba;">Gear</a>';
					}else{
						echo '<a href="/user/character.php?type=gear" class="btn btn-primary">Gear</a>';
					}
				?>
			</div>
			<div class="col-md-4 col-sm-12 col-xs-12">
				<div class="panel panel-primary">
					<div class="panel-heading">Character</div>
					<div class="panel-body Center">
						<script>
							setInterval(function() {
								var myImageElement = document.getElementById('character');
								myImageElement.src = 'https://xdiscuss.net/func/user/getImage.php?id=<?php echo $auth_uid; ?>&type=user&tick=' + Math.random();
							}, 5000);
						</script>
						<?php
							$creator_uid = $auth_uid;
							$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
							$stmt->bindParam(':id', $creator_uid, PDO::PARAM_INT);
							$stmt->execute();
							$resultuser = $stmt->fetch(PDO::FETCH_ASSOC);
						?>
						<img width="320" id="character" src="<?php echo getImage($resultuser);?>">
					</div>
					<div class="panel-footer" id="regenfooter">
						<form method="post">
							<button id="regenbtn" class="btn btn-primary FullWidth" name="regen" type="submit">Regen</button>
							<div class="btn-group btn-group-justified">
								<button id="regularChara" class="btn btn-primary" style="width:33%;" name="charaRegular" type="submit">Normal</button>
								<button id="walkingChara" class="btn btn-primary" style="width:33%;" name="charaWalking" type="submit">Walking</button>
								<button id="sittingChara" class="btn btn-primary" style="width:33%;" name="charaSitting" type="submit">Sitting</button>
							</div>
						</form>
					</div>
					<?php
						if (isset($_POST['regen'])) {
							$query = "INSERT INTO renders (`render_id`, `type`) VALUES (:uid, 'character');";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':uid', $_COOKIE['auth_uid'], PDO::PARAM_INT);
							$stmt->execute();
							echo '<script>document.getElementById("regenbtn").remove();</script>';
						}
						
						if (isset($_POST['charaRegular'])) {
							$query = "UPDATE users SET charap = 0 WHERE id = :uid";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
							$stmt->execute();
							
							$query = "INSERT INTO renders (`render_id`, `type`) VALUES (:uid, 'character');";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':uid', $_COOKIE['auth_uid'], PDO::PARAM_INT);
							$stmt->execute();
							echo '<script>document.getElementById("regenbtn").remove();</script>';
						}
						
						if (isset($_POST['charaWalking'])) {
							$query = "UPDATE users SET charap = 1 WHERE id = :uid";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
							$stmt->execute();
							
							$query = "INSERT INTO renders (`render_id`, `type`) VALUES (:uid, 'character');";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':uid', $_COOKIE['auth_uid'], PDO::PARAM_INT);
							$stmt->execute();
							echo '<script>document.getElementById("regenbtn").remove();</script>';
						}
						
						if (isset($_POST['charaSitting'])) {
							$query = "UPDATE users SET charap = 2 WHERE id = :uid";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
							$stmt->execute();
							
							$query = "INSERT INTO renders (`render_id`, `type`) VALUES (:uid, 'character');";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':uid', $_COOKIE['auth_uid'], PDO::PARAM_INT);
							$stmt->execute();
							echo '<script>document.getElementById("regenbtn").remove();</script>';
						}
					?>
				</div>
			</div>
			<div class="col-md-8 col-sm-12 col-xs-12">
				<?php
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/character/posts.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/character/main.php';
				?>
			</div>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>