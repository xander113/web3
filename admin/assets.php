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
		<title><?php echo getName(); ?> | Asset Approval</title>
		<?php
			echo getHead();
		?>
	</head>
	<body>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
		?>
		<div id="content">
			<?php
				if (isset($_POST['acceptAsset'])) {
					$assetID = $_POST['acceptAsset'];
					$stmt = $dbcon->prepare("SELECT * FROM catalog WHERE id=:id;");
					$stmt->bindParam(':id', $assetID, PDO::PARAM_INT);
					$stmt->execute();
					$resultApprove = $stmt->fetch(PDO::FETCH_ASSOC);
					$aID = $resultApprove['assetid'];
					$dbtype = $resultApprove['type'];
					$creatorID = $resultApprove['creator_uid'];
					$assetName = $resultApprove['name'];
					
					if ($resultApprove['approved'] == 0 and $resultApprove['declined'] == 0) {
						$stmt = $dbcon->prepare("UPDATE catalog SET approved = 1 WHERE id=:id");
						$stmt->bindParam(':id', $assetID, PDO::PARAM_INT);
						$stmt->execute();
						
						if ($dbtype == "shirts" or $dbtype == "pants" or $dbtype == "tshirts") {
							$query = "INSERT INTO renders (`render_id`, `type`) VALUES (:id, :dbtype);";
							$stmt = $dbcon->prepare($query);
							$stmt->bindParam(':id', $aID, PDO::PARAM_INT);
							$stmt->bindParam(':dbtype', $dbtype, PDO::PARAM_STR);
							$stmt->execute();
						}
					}
					
					if ($dbtype != "decals") {
						$query = "INSERT INTO ownedItems (`uid`, `catalogid`, `type`) VALUES (:uid, :catid, :type);";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':uid', $creatorID, PDO::PARAM_INT);
						$stmt->bindParam(':catid', $assetID, PDO::PARAM_INT);
						$stmt->bindParam(':type', $dbtype, PDO::PARAM_STR);
						$stmt->execute();
					}
					
					if ($dbtype != "decals") {
						$message = 'Your asset named <b>'.$assetName.'</b> has been approved and can be seen in the catalog. You also have received the item in your inventory. Your item can be found at https://xdiscuss.net/catalog/viewitem.php?id='.$assetID;
					}else{
						$message = 'Your asset named <b>'.$assetName.'</b> has been approved and can be seen in the catalog. Your item can be found at https://xdiscuss.net/catalog/viewitem.php?id='.$assetID;
					}
					$title = 'Asset Approval result for '.$assetName;
					$query = "INSERT INTO messages (`recv_uid`, `sender_uid`, `title`, `content`) VALUES (:userId2, 10370, :title, :msg);";
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':userId2', $creatorID, PDO::PARAM_INT);
					$stmt->bindParam(':msg', $message, PDO::PARAM_STR);
					$stmt->bindParam(':title', $title, PDO::PARAM_STR);
					$stmt->execute();
				}
				
				if (isset($_POST['denyAsset'])) {
					$assetID = $_POST['denyAsset'];
					$stmt = $dbcon->prepare("SELECT * FROM catalog WHERE id=:id;");
					$stmt->bindParam(':id', $assetID, PDO::PARAM_INT);
					$stmt->execute();
					$resultApprove = $stmt->fetch(PDO::FETCH_ASSOC);
					$aID = $resultApprove['assetid'];
					$dbtype = $resultApprove['type'];
					$creatorID = $resultApprove['creator_uid'];
					$assetName = $resultApprove['name'];
					
					if ($resultApprove['approved'] == 0 and $resultApprove['declined'] == 0) {
						$stmt = $dbcon->prepare("UPDATE catalog SET declined = 1 WHERE id=:id");
						$stmt->bindParam(':id', $assetID, PDO::PARAM_INT);
						$stmt->execute();
					}
					
					if ($dbtype == "tshirts") {
						@unlink("/var/www/graphictoria/data/assets/tshirts/models/".$aID.'.png');
					}
					if ($dbtype == "shirts") {
						@unlink("/var/www/graphictoria/data/assets/shirts/textures/".$aID.'.png');
					}
					if ($dbtype == "pants") {
						@unlink("/var/www/graphictoria/data/assets/pants/textures/".$aID.'.png');
					}
					if ($dbtype == "decals") {
						@unlink("/var/www/graphictoria/data/assets/decals/".$aID.'.png');
					}
					
					$message = 'Your asset named <b>'.$assetName.'</b> has been denied because it violated our rules. You have not been refunded.';
					$title = 'Asset Approval result for '.$assetName;
					$query = "INSERT INTO messages (`recv_uid`, `sender_uid`, `title`, `content`) VALUES (:userId2, 10370, :title, :msg);";
					$stmt = $dbcon->prepare($query);
					$stmt->bindParam(':userId2', $creatorID, PDO::PARAM_INT);
					$stmt->bindParam(':msg', $message, PDO::PARAM_STR);
					$stmt->bindParam(':title', $title, PDO::PARAM_STR);
					$stmt->execute();
				}
			?>
			<h4 style="color:grey;">Asset Approval</h4>
			<div class="alert alert-danger">
				<b>Things to not approve</b>
				<ul>
					<li>Pornographic content such as naked human beings.</li>
					<li>Photos of staff members or members. Only allow known people such as Bill Gates.</li>
					<li>Deny all copyrighted content.</li>
				</li>
			</div>
			<?php
				$stmt = $dbcon->prepare("SELECT * FROM catalog WHERE approved = 0 AND declined = 0;");
				$stmt->execute();
				if ($stmt->rowCount() == 0) {
					echo '<p>There are no pending assets to approve at this moment.</p>';
				}
				$count = 0;
				foreach($stmt as $result) {
					$itemName = htmlentities($result['name'], ENT_QUOTES, "UTF-8");
					if (strlen($itemName) > 16) {
						$itemName = substr($itemName, 0, 7) . '...';
					}
					echo '<div class="col-md-4 col-sm-4 col-xs-12 Center"><div class="well" style="box-shadow:none;"><span class="content">'.$itemName.'</span><br>';
					$creator = $result['creator_uid'];
					$stmt = $dbcon->prepare("SELECT * FROM users WHERE id=:id;");
					$stmt->bindParam(':id', $creator, PDO::PARAM_INT);
					$stmt->execute();
					$result2 = $stmt->fetch(PDO::FETCH_ASSOC);
					$username = $result2['username'];
					
					if ($result['type'] == "decals") {
						echo '<img width="150" src="/data/assets/'.$result['type'].'/'.$result['assetid'].'.png?time='.time().'"><br><b>Type : '.$result['type'].'</b><br><b>Uploaded by <a href="/profile.php?id='.$creator.'">'.$username.'</a></b><br><form method="post"><button type="submit" name="acceptAsset" class="btn btn-success" value="'.$result['id'].'">Accept</button><button type="submit" name="denyAsset" class="btn btn-danger" value="'.$result['id'].'">Decline</button></form></div></div>';
					}elseif($result['type'] == "tshirts") {
						echo '<img width="150" src="/data/assets/'.$result['type'].'/models/'.$result['assetid'].'.png?time='.time().'"><br><b>Type : '.$result['type'].'</b><br><b>Uploaded by <a href="/profile.php?id='.$creator.'">'.$username.'</a></b><br><form method="post"><button type="submit" name="acceptAsset" class="btn btn-success" value="'.$result['id'].'">Accept</button><button type="submit" name="denyAsset" class="btn btn-danger" value="'.$result['id'].'">Decline</button></form></div></div>';
					}else{
						echo '<img width="150" src="/data/assets/'.$result['type'].'/textures/'.$result['assetid'].'.png?time='.time().'"><br><b>Type : '.$result['type'].'</b><br><b>Uploaded by <a href="/profile.php?id='.$creator.'">'.$username.'</a></b><br><form method="post"><button type="submit" name="acceptAsset" class="btn btn-success" value="'.$result['id'].'">Accept</button><button type="submit" name="denyAsset" class="btn btn-danger" value="'.$result['id'].'">Decline</button></form></div></div>';
					}
				}
			?>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>