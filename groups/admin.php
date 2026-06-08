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
		<title><?php echo getName();?> | Group Admin</title>
		<script src="/html/js/tinymce/tinymce.min.js"></script>
	</head>
	<body>
		<br>
		<div id="content" >
		<div class="modal fade" id="leaveGroup" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><font color="grey">Leaving Group...</font></h4>
					</div>
					<div class="modal-body">
						<p>You are about to leave a group! Are you sure you want to do that?</p>
					</div>
					<div class="modal-footer">
						<button style="display:inline;" type="button" class="btn btn-default" data-dismiss="modal">No</button>
						<form method="post" style="display:inline;">
							<button style="display:inline;" type="submit" name="lGroup" class="btn btn-danger">Leave Group</button>
						</form>
					</div>
				</div>
			</div>
		</div>
		
		<div class="modal fade" id="leaveGroup2" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><font color="grey">Leaving Group...</font></h4>
					</div>
					<div class="modal-body">
						<p>You are leaving a group while owning it, <b><font color="red">this will delete the group entirely!</font></b><br>Are you sure you want to do this?</p>
					</div>
					<div class="modal-footer">
						<button style="display:inline;" type="button" class="btn btn-default" data-dismiss="modal">No</button>
						<form method="post" style="display:inline;">
							<button style="display:inline;" type="submit" name="leaveDeleteGroup" class="btn btn-danger">Leave and delete Group</button>
						</form>
					</div>
				</div>
			</div>
		</div>
		
			<?php
				if (isset($_GET['id'])) {
					$groupId = $_GET['id'];
					if (is_array($groupId)) {
						$hAds = true;
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
						echo '<title>Graphictoria | Error</title>';
						echo '<br><div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3"><div class="well" style="box-shadow: none;">Invalid parameter.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
					if (strlen($groupId) == 0) {
						$hAds = true;
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
						echo '<div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3">';
						echo '<div class="well well-sm Center">Group not specified.</div></div></div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						echo '</body></html>';
						exit;
					}
				}else{
					$hAds = true;
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
					echo '<div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3">';
					echo '<div class="well well-sm Center">Group not specified.</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					echo '</body></html>';
					exit;
				}
				
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
				$stmt = $dbcon->prepare("SELECT * FROM groups WHERE id = :id");
				$stmt->bindParam(':id', $groupId, PDO::PARAM_INT);
				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				$groupId = $result['id'];
				
				if ($stmt->rowCount() == 0) {
					echo '<div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3">';
					echo '<div class="well well-sm Center">Group not found.</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					echo '</body></html>';
					exit;
				}
				
				$allow = false;
				if ($result['cuid'] == $auth_uid) {
					$allow = true;
				}
				
				if ($user_rankId > 0 and $user_rankId != 3) {
					$allow = true;
				}
				
				if ($allow == false) {
					echo '<div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3 col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3">';
					echo '<div class="well well-sm Center">You are not allowed to make changes to this group.</div></div></div>';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					echo '</body></html>';
					exit;
				}
				
				if (isset($_POST['saveDescription'])) {
					$content = $_POST['descriptionContent'];
					
					$descriptionCheck = preg_replace("/[^ \w]+/", "", $content);
					$descriptionCheck = preg_replace('/\s+/', '', $descriptionCheck);
					
					
					$error = false;
					if (strlen($descriptionCheck) > 256 or strlen($content) > 256) {
						$error = true;
						echo '<div class="alert alert-dismissible alert-danger">Your description can not be longer than 256 characters.</div>';
					}
					
					if ($error == false) {
						$query = "UPDATE `groups` SET `description`=:content WHERE `id`=:id;";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':id', $groupId, PDO::PARAM_INT);
						$stmt->bindParam(':content', $content, PDO::PARAM_STR);
						$stmt->execute();
						
						header("Location: /groups/admin.php?id=".$groupId);
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
				}
			?>
			<div class="well">
				<h4><font color="grey">Group Admin</font></h4>
				<p>Change Description</p>
				<form method="post">
					<textarea placeholder="Description goes here" name="descriptionContent" class="FullWidth form-control" rows="8"><?php echo htmlentities($result['description'], ENT_QUOTES, "UTF-8"); ?></textarea>
					<button type="submit" name="saveDescription" class="btn btn-success FullWidth">Save</button>
				</form>
			</div>
			<a href="/groups/view.php?id=<?php echo $groupId; ?>" class="btn btn-primary FullWidth">Return to Group</a>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>