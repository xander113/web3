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
		<title><?php echo getName(); ?> | New Server</title>
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
				if (isset($_POST['addGame'])) {
					$name = $_POST['name'];
					$description = $_POST['description'];
					$ip = $_POST['ip'];
					$port = $_POST['port'];
					$public = $_POST['public'];
					
					$error = false;
					$nameCheck = preg_replace("/[^ \w]+/", "", $name);
					$nameCheck = preg_replace('!\s+!', ' ', $nameCheck);
					$descriptionCheck = preg_replace("/[^ \w]+/", "", $description);
					$descriptionCheck = preg_replace('/\s+/', '', $descriptionCheck);
					if (strlen($name) > 32 and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">Server name is too long.</div>';
					}
					
					if (strlen($nameCheck) < 4 and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">Server name is too short.</div>';
					}
					
					if (strlen($description) > 128 and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">Server description is too long.</div>';
					}
					
					if (strlen($ip) == 0 and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">You must enter an IP address.</div>';
					}
					
					if (strlen($ip) > 64 and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">IP address is too long.</div>';
					}
					
					if (strlen($port) == 0 and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">Server port is too short.</div>';
					}
					
					if (strlen($port) > 5 and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">Server port is too long.</div>';
					}
					
					if (ctype_digit($port) == false and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">Server port can only be numbers.</div>';
					}
					
					if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) == false and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">You have entered an invalid IP address. IPV6 is not supported.</div>';
					}
					
					if ($public != 1 and $public != 2 and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">Invalid public option.</div>';
					}
					
					if ($error == false) {
						$key = md5(microtime().rand());
						$serverkey = md5(microtime().rand());
						if ($_POST['public'] == 1) {
							$addP = 0;
						}else{
							$addP = 1;
						}
						$stmt = $dbcon->prepare("INSERT INTO games (`public`, `creator_uid`, `name`, `description`, `ip`, `port`, `key`, `privatekey`) VALUES (:public, :user, :name, :description, :ip, :port, :key, :serverkey);");
						$stmt->bindParam(':public', $addP, PDO::PARAM_INT);
						$stmt->bindParam(':serverkey', $serverkey, PDO::PARAM_STR);
						$stmt->bindParam(':user', $auth_uid, PDO::PARAM_INT);
						$stmt->bindParam(':name', $name, PDO::PARAM_STR);
						$stmt->bindParam(':description', $description, PDO::PARAM_STR);
						$stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
						$stmt->bindParam(':port', $port, PDO::PARAM_INT);
						$stmt->bindParam(':key', $key, PDO::PARAM_STR);
						$stmt->execute();
						
						$stmt = $dbcon->prepare("SELECT * FROM games WHERE `creator_uid`=:uid ORDER BY id DESC LIMIT 1;");
						$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
						$result = $stmt->fetch(PDO::FETCH_ASSOC);
						$id = $result['id'];
						
						header("Location: /games/view.php?id=".$id);
					}
				}
			?>
			<div class="panel panel-primary">
				<div class="panel-heading">New Server</div>
				<div class="panel-body">
					<form method="post">
						<input type="text" placeholder="Name" name="name" maxlength="32" class="form-control">
						<textarea name="description" placeholder="Description" maxlength="128" class="form-control" rows="10"></textarea>
						<input type="text" placeholder="IP" maxlength="64" class="form-control" name="ip" value="<?php echo getIP(); ?>">
						<input type="number" placeholder="Port" maxlength="5" name="port" class="form-control">
						<p>Public :
							<select name="public">
								<option value="1">No (You will get a key to invite people)</option>
								<option value="2">Yes (Be aware of exploiters)</option>
							</select>
						</p>
						<button type="submit" class="btn btn-success FullWidth" name="addGame">Create Server</button>
					</form>
				</div>
			</div>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>