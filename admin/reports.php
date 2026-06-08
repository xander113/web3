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
		<title><?php echo getName(); ?> | Reports</title>
		<?php
			echo getHead();
		?>
	</head>
	<body>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
		?>
		<div id="content" >
			<div class="panel panel-default">
				<div class="panel-heading"><h4 style="color:grey;">Reports</h4></div>
				<div class="panel-body">
					<table class="table table-hover">
						<thead>
							<th>Reported User</th>
							<th>Reason</th>
							<th>Date Reported</th>
						</thead>
						<tbody>
							<?php
								$stmt = $dbcon->prepare("SELECT * FROM reports ORDER BY id DESC;");
								$stmt->bindParam(':id', $id, PDO::PARAM_INT);
								$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
								$stmt->execute();
								$count = 0;
								foreach($stmt as $result) {
									$count++;
									echo '<tr><td>'.$result['target'].'</td><td>'.htmlentities($result['reason'], ENT_QUOTES, "UTF-8").'</td><td>'.date('M j Y g:i A', strtotime($result['date'])).'</td></tr>';
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
			<a href="/admin/">Go back</a>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>