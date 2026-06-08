<?php
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo getName(); ?> | Item</title>
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
			$deleteString = md5($auth_formCode);
			$buyString = sha1($deleteString);
			if (isset($_GET['id'])) {
				$id = $_GET['id'];
				if (is_array($id)) {
					$hAds = true;
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
					echo '<br>Invalid parameter';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				if (strlen($id) == 0) {
					$hAds = true;
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
					echo '<br>No ID was specified.';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
			}else{
				$hAds = true;
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
				echo '<br>No ID was specified.';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			
			$stmt = $dbcon->prepare("SELECT * FROM catalog WHERE id=:id");
			$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($stmt->rowCount() == 0) {
				$hAds = true;
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
				if ($news_on == true){
					echo '<br>';
				}
				echo '<br>This item was not found.';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			$deleted = $result['deleted'];
			if ($deleted == 1) {
				$hAds = true;
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
				if ($news_on == true){
					echo '<br>';
				}
				echo '<br>This item has been deleted by a moderator because it broke our rules.';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			$denied = $result['declined'];
			if ($denied == 1) {
				$hAds = true;
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
				if ($news_on == true){
					echo '<br>';
				}
				echo '<br>This item has been denied.';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			$approved = $result['approved'];
			if ($approved == 0) {
				$hAds = true;
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
				if ($news_on == true){
					echo '<br>';
				}
				echo '<br>This item has not been approved.';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
			
			$buyable = $result['buyable'];
			$price = $result['price'];
			$type = $result['type'];
			$stmt = $dbcon->prepare("SELECT * FROM users WHERE id=:id");
			$stmt->bindParam(':id', $result['creator_uid'], PDO::PARAM_INT);
			$stmt->execute();
			$resultcreator = $stmt->fetch(PDO::FETCH_ASSOC);
			
			$stmt = $dbcon->prepare("SELECT * FROM ownedItems WHERE uid=:id AND catalogid = :catid");
			$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
			$stmt->bindParam(':catid', $id, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$owned = true;
			}else{
				$owned = false;
			}
			$stmt = $dbcon->prepare("SELECT * FROM ownedItems WHERE catalogid = :catid");
			$stmt->bindParam(':catid', $id, PDO::PARAM_INT);
			$stmt->execute();
			$boughtTimes = $stmt->rowCount();
		?>
		<div id="content" >
			<br><br>
			<div class="col-xs-12 col-sm-4 col-md-8 col-sm-offset-2 col-md-offset-2">
				<?php
					if (isset($_POST)) {
						if (array_key_exists($deleteString, $_POST)) {
							$itemId = $_GET['id'];
							// Get item info again.
							$stmt = $dbcon->prepare("SELECT * FROM catalog WHERE id=:id");
							$stmt->bindParam(':id', $itemId, PDO::PARAM_INT);
							$stmt->execute();
							$result = $stmt->fetch(PDO::FETCH_ASSOC);
							
							// Make deleted true
							$stmt = $dbcon->prepare("UPDATE catalog SET deleted = 1 WHERE id = :id;");
							$stmt->bindParam(':id', $itemId, PDO::PARAM_INT);
							$stmt->execute();
							
							// Make item unbuyable
							$stmt = $dbcon->prepare("UPDATE catalog SET buyable = 0 WHERE id = :id;");
							$stmt->bindParam(':id', $itemId, PDO::PARAM_INT);
							$stmt->execute();
							
							// Make item deleted in ownedItems
							$stmt = $dbcon->prepare("UPDATE ownedItems SET deleted = 1 WHERE catalogid = :id;");
							$stmt->bindParam(':id', $itemId, PDO::PARAM_INT);
							$stmt->execute();
							
							// Delete the actual file
							if ($result['type'] == "tshirts") {
								unlink("/var/www/graphictoria/data/assets/tshirts/models/".$result['assetid'].'.png');
							}
							if ($result['type'] == "shirts") {
								unlink("/var/www/graphictoria/data/assets/shirts/textures/".$result['assetid'].'.png');
							}
							if ($result['type'] == "pants") {
								unlink("/var/www/graphictoria/data/assets/pants/textures/".$result['assetid'].'.png');
							}
							if ($result['type'] == "decals") {
								unlink("/var/www/graphictoria/data/assets/decals/".$result['assetid'].'.png');
							}
							
							if ($result['type'] !== "decals") {
								// Remove from wearing
								$stmt = $dbcon->prepare("SELECT * FROM wearing WHERE catalogId = :id");
								$stmt->bindParam(':id', $itemId, PDO::PARAM_INT);
								$stmt->execute();
								foreach($stmt as $result) {
									// Delete and put a request up in the thumbnailserver
									$query = "DELETE FROM `wearing` WHERE `id`=:id";
									$stmt = $dbcon->prepare($query);
									$id = $result['id'];
									$stmt->bindParam(':id', $id, PDO::PARAM_INT);
									$stmt->execute();
									
									$uid = $result['uid'];
									// Add request to thumbnailserver
									$query = "INSERT INTO renders (`render_id`, `type`) VALUES (:id, 'character');";
									$stmt = $dbcon->prepare($query);
									$stmt->bindParam(':id', $uid, PDO::PARAM_INT);
									$stmt->execute();
								}
							}
							header("Location: /catalog/?type=".$result['type']);
							include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
							exit;
						}
						
						if (array_key_exists($buyString, $_POST) and $loggedIn == true) {
							$itemId = $_GET['id'];
							// Check if owned again.
							$stmt = $dbcon->prepare("SELECT * FROM ownedItems WHERE uid=:id AND catalogid = :catid");
							$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
							$stmt->bindParam(':catid', $itemId, PDO::PARAM_INT);
							$stmt->execute();
							if ($stmt->rowCount() > 0) {
								$owned2 = true;
							}else{
								$owned2 = false;
							}
							// Get item info again.
							$stmt = $dbcon->prepare("SELECT * FROM catalog WHERE id=:id");
							$stmt->bindParam(':id', $itemId, PDO::PARAM_INT);
							$stmt->execute();
							$result = $stmt->fetch(PDO::FETCH_ASSOC);
							
							if ($owned2 == false and $result['buyable'] == 1 and $result['type'] !== "decals" and $result['approved'] == 1) {
								if ($itemId != $_GET['id']) {
									header("Location: /catalog/viewitem.php?id=".$_GET['id']);
									include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
									exit;
								}
								if ($result['price'] < $user_coins or $result['price'] == $user_coins){
									$newCoins = $user_coins-$result['price'];
									$stmt = $dbcon->prepare("UPDATE users SET coins = :coins WHERE id = :user;");
									$stmt->bindParam(':coins', $newCoins, PDO::PARAM_INT);
									$stmt->bindParam(':user', $auth_uid, PDO::PARAM_INT);
									$stmt->execute();
									
									$stmt = $dbcon->prepare("INSERT INTO ownedItems (`uid`, `catalogid`, `type`) VALUES (:user, :itemid, :type);");
									$stmt->bindParam(':user', $auth_uid, PDO::PARAM_INT);
									$stmt->bindParam(':itemid', $result['id'], PDO::PARAM_INT);
									$stmt->bindParam(':type', $result['type'], PDO::PARAM_STR);
									$stmt->execute();
									
									// Give creator coins
									$creatorCoins = round($result['price']/2);
									$creator_uid = $result['creator_uid'];
									$stmt = $dbcon->prepare("SELECT * FROM users WHERE id=:id");
									$stmt->bindParam(':id', $creator_uid, PDO::PARAM_INT);
									$stmt->execute();
									$result = $stmt->fetch(PDO::FETCH_ASSOC);
									$currentCreatorCoins = $result['coins'];
									$newCreatorCoins = $currentCreatorCoins+$creatorCoins;
									$creatorIP = $result['lastIP'];
									$creatorRegIP = $result['registerIP'];
									$creatormail = $result['email'];
									
									$enableAward = false;
									if ($creatorIP != getIP() and $enableAward == true and $creatorRegIP != getIP() and $creatormail != $auth_email) {
										$stmt = $dbcon->prepare("UPDATE users SET coins = :coins WHERE id = :user;");
										$stmt->bindParam(':coins', $newCreatorCoins, PDO::PARAM_INT);
										$stmt->bindParam(':user', $creator_uid, PDO::PARAM_INT);
										$stmt->execute();
									}
									
									header("Location: /catalog/viewitem.php?id=".$_GET['id']);
									include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
									exit;
								}
							}else{
								header("Location: /catalog/viewitem.php?id=".$_GET['id']);
								include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
								exit;
							}
						}
					}
				?>
				<div class="well">
					<div class="row">
						<div class="col-md-5 col-sm-12 col-xs-12">
							<h4 class="content"><?php echo htmlentities(filter($result['name']), ENT_QUOTES, "UTF-8"); ?> </h4>
							<?php
								if ($type == "shirts" or $type == "pants" or $type == "tshirts") {
									echo '<img width="150" src="https://xdiscuss.net/func/user/getImage.php?id='.$result['assetid'].'&type='.$result['type'].'&time='.time().'">';
								}else{
								if($type == "torso" or $type == "leftarm" or $type == "leftleg" or $type == "rightarm" or $type == "rightleg") {
									echo '<img width="150" src="/data/assets/package/thumbnail/'.$result['datafile'].'.png">';
								}elseif($type == "decals") {
									echo '<img width="150" src="/data/assets/decals/'.$result['assetid'].'.png">';
								}else{
									echo '<img width="150" src="/data/assets/'.$result['type'].'/thumbnail/'.$result['datafile'].'.png">';
								}
								}
							?>
						</div>
						<div class="col-md-7 col-sm-12 col-xs-12">
							<h4>Details</h4>
							<?php
								echo '<a href="/profile.php?id='.$resultcreator['id'].'"><img width="100" src="'.getImage($resultcreator).'"></a><br>';
								echo '<b>Creator</b>: <a href="/profile.php?id='.$result['creator_uid'].'">'.htmlentities($resultcreator['username'], ENT_QUOTES, "UTF-8").'</a><br>';
								if ($result['type'] !== "decals") {
									if ($result['buyable'] == 1) {
										echo '<b>Price</b>: <font color="green">'.$result['price'].' coins</font><br>';
									}else{
										echo '<b>Price</b>: <font color="red">Unbuyable</font><br>';
									}
									if ($boughtTimes == 1) {
										echo '<b>Owned</b>: 1 time<br>';
									}else{
										echo '<b>Owned</b>: '.$boughtTimes.' times<br>';
									}
								}
							?>
							<b>Date Created</b>: <?php echo date('M j Y g:i:s A', strtotime($result['createDate'])); ?>
							<?php
								if ($result['type'] !== "decals" and $loggedIn == true) {
									if ($owned == true) {
										echo '<a class="btn btn-success disabled FullWidth">Already Bought</a>';
									}else{
										if ($result['buyable'] == 1) {
											if ($user_coins < $result['price']) {
												echo '<a class="btn btn-success disabled FullWidth">Insuffient Coins</a>';
											}else{
												echo '<form method="post" style="display:inline;"><button type="submit" class="btn btn-success FullWidth" name="'.$buyString.'" value="'.$result['id'].'">Buy</button></form>';
											}
										}else{
											echo '<a class="btn btn-success disabled FullWidth">Buy</a>';
										}
									}
								}
								if ($type == "shirts" or $type == "tshirts" or $type == "pants" or $type == "decals") {
									if ($user_rankId > 0 and $user_rankId != 3) {
										echo '<form method="post" style="display:inline;"><button type="submit" class="btn btn-danger FullWidth" name="'.$deleteString.'" value="'.$result['id'].'">Delete</button></form>';
									}
								}
							?>
						</div>
						<div class="col-xs-12 content">
							<hr>
							<b>Description</b>
							<?php
								if (strlen($result['description']) !== 0) {
									echo '<p>'.htmlentities(filter($result['description']), ENT_QUOTES, "UTF-8").'</p>';
								}else{
									echo '<font color="grey"><p>None</p></font>';
								}
								if ($result['type'] == "decals") {
									echo '<b>Decal texture ID</b><br><code>http://xdiscuss.net/data/assets/decals/'.$result['assetid'].'.png</code>';
								}
							?>
						</div>
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