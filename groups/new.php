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
		<title><?php echo getName();?> | New Group</title>
		<script src="/html/js/tinymce/tinymce.min.js"></script>
	</head>
	<body>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
		?>
		<div id="content" >
			<?php
				if (isset($_POST['createGroup'])) {
					$name = $_POST['groupName'];
					$description = $_POST['groupDescription'];
					$error = false;
					
					$nameCheck = preg_replace("/[^ \w]+/", "", $name);
					$nameCheck = preg_replace('!\s+!', ' ', $nameCheck);
					
					if (strlen($nameCheck) == 0 and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">You must enter a Group Name.</div>';
					}
					
					if (strlen($nameCheck) < 5 and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">Group Names must be at least 5 characters.</div>';
					}
					
					if (strlen($nameCheck) > 32 or strlen($name) > 32 and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">Group names can not be longer than 32 characters.</div>';
					}
					
					$descriptionCheck = preg_replace("/[^ \w]+/", "", $description);
					$descriptionCheck = preg_replace('/\s+/', '', $descriptionCheck);
					
					if (strlen($descriptionCheck) > 256 or strlen($description) > 256 and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">Group descriptions can not be longer than 256 characters.</div>';
					}
					
					if ($user_coins < 49 and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">50 coins are required to make a group.</div>';
					}
					
					// Get in how many groups this user is.
					// Get group memberships
					$count = 0;
					$stmt = $dbcon->prepare("SELECT * FROM group_members WHERE uid = :id;");
					$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
					$stmt->execute();
					foreach($stmt as $result) {
						$count++;
					}
					
					$stmt = $dbcon->prepare("SELECT * FROM groups WHERE cuid = :id;");
					$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
					$stmt->execute();
					foreach($stmt as $result) {
						$count++;
					}
					
					if ($count > 9 and $error == false) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">You can only be in 10 groups at a time.</div>';
					}
					
					
					if ($error == false) {
						// Update user coins.
						$user_coins = $user_coins-50;
						$stmt = $dbcon->prepare("UPDATE users SET coins = :coins WHERE id = :user;");
						$stmt->bindParam(':coins', $user_coins, PDO::PARAM_INT);
						$stmt->bindParam(':user', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
						
						// Create group
						$query = "INSERT INTO groups (`cuid`, `name`, `description`) VALUES (:cuid, :name, :description);";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':cuid', $auth_uid, PDO::PARAM_INT);
						$stmt->bindParam(':name', $name, PDO::PARAM_STR);
						$stmt->bindParam(':description', $description, PDO::PARAM_STR);
						$stmt->execute();
						
						// Redirect to group (!)
						// Get groupId
						$stmt = $dbcon->prepare("SELECT * FROM groups WHERE cuid = :id ORDER BY id DESC LIMIT 1;");
						$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
						$result = $stmt->fetch(PDO::FETCH_ASSOC);
						$groupId = $result['id'];
						
						header("Location: /groups/view.php?id=".$groupId);
					}
				}
			?>
			<div class="col-xs-8">
				<div class="panel panel-primary">
					<div class="panel-heading">New Group</div>
					<div class="panel-body">
						<form method="post">
							<input type="text" maxlength="32" name="groupName" placeholder="Name of Group" class="form-control FullWidth"></input>
							<br>
							<textarea rows="8" maxlength="256" name="groupDescription" class="form-control FullWidth" placeholder="Description"></textarea>
							<br>
							<button type="submit" name="createGroup" class="btn btn-success FullWidth">Create Group</button>
						</form>
					</div>
				</div>
			</div>
			<div class="col-xs-4">
				<div class="panel panel-primary">
					<div class="panel-heading">Tips</div>
					<div class="panel-body">
						<ul>
							<li>Group Names can not be longer than 32 characters.</li>
							<li>Group Names must be at least 5 characters.</li>
							<li>Descriptions can not be longer than 256 characters.</li>
							<li>Descriptions are optional.</li>
							<li>Creating a Group will cost you 50 coins.</li>
							<li>You can only be in 10 groups at a time.</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>