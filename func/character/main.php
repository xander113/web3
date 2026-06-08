<?php
	if (strpos($_SERVER['SCRIPT_NAME'], "main.php")) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
	
	if ($type == "colors") {
		$title = "Colors";
	}
	
	if ($type == "hats") {
		$title = "Hats";
	}
	
	if ($type == "shirts") {
		$title = "Shirts";
	}
	
	if ($type == "pants") {
		$title = "Pants";
	}
	
	if ($type == "gear") {
		$title = "Gear";
	}
	if ($type == "tshirts"){
		$title = "T-Shirts";
	}
	if ($type == "faces") {
		$title = "Faces";
	}
	if ($type == "torso") {
		$title = "Torso";
	}
	if ($type == "leftleg") {
		$title = "Left Leg";
	}
	if ($type == "leftarm") {
		$title = "Left Arm";
	}
	if ($type == "rightleg") {
		$title = "Right Leg";
	}
	if ($type == "rightarm") {
		$title = "Right Arm";
	}
	if ($type == "heads") {
		$title = "Heads";
	}
	if (isset($_GET['page'])) {
		$page = $_GET['page'];
		$offset = $page*6;
		if ($page == 0){
			$page = 0;
			$offset = 0;
		}
	}else{
		$page = 0;
		$offset = 0;
	}
	if ($page < 0) {
		header("Location: /user/character.php?type=".$type);
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
?>

<?php
	if ($type !== "colors") {
		echo '<div class="panel panel-primary"><div class="panel-heading">'.$title.' (wearing)</div>';
		echo '<div class="panel-body">';
		$stmt = $dbcon->prepare("SELECT * FROM wearing WHERE uid = :uid AND type = :type");
		$stmt->bindParam(':type', $type, PDO::PARAM_STR);
		$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt->rowCount() == 0) {
			echo 'You are not wearing any item.';
		}
		foreach($stmt as $resultWearing) {
			$stmt = $dbcon->prepare("SELECT * FROM catalog WHERE id = :id;");
			$stmt->bindParam(':id', $resultWearing['catalogId'], PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($result['deleted'] == 0) {
				$itemName = $result['name'];
				if (strlen($itemName) > 16) {
					$itemName = substr($itemName, 0, 13) . '...';
				}
				echo '<div class="col-md-4 col-sm-4 col-xs-12 Center"><div class="well" style="box-shadow:none;height:250px;">'.htmlentities($itemName, ENT_QUOTES, "UTF-8").'<br>';
				if ($type == "shirts" or $type == "pants" or $type == "tshirts") {
					echo '<img width="150" src="https://xdiscuss.net/func/user/getImage.php?id='.$result['assetid'].'&type='.$result['type'].'&time='.time().'">';
				}else{
					if($type == "torso" or $type == "leftarm" or $type == "leftleg" or $type == "rightarm" or $type == "rightleg") {
						echo '<img width="150" src="/data/assets/package/thumbnail/'.$result['datafile'].'.png">';
					}else{
						echo '<img width="150" src="/data/assets/'.$result['type'].'/thumbnail/'.$result['datafile'].'.png">';
					}
				}
				echo '<br><form method="post"><button type="submit" class="btn btn-primary" name="unwear" value="'.$result['id'].'">Unwear</button>';
				echo '</div></div>';
			}
		}
		echo '</div></div>';
	}
?>

<div class="panel panel-primary">
	<div class="panel-heading"><?php echo $title; ?></div>
	<div class="panel-body">
		<?php
			if ($type != "colors") {
				$stmt = $dbcon->prepare("SELECT * FROM ownedItems WHERE type = :type AND uid = :uid AND deleted=0 ORDER BY id DESC LIMIT 7 OFFSET :offset;");
				$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
				$stmt->bindParam(':type', $type, PDO::PARAM_STR);
				$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
				$stmt->execute();
				if ($stmt->rowCount() == 0) {
					echo 'No items found.';
				}
				$count = 0;
				foreach($stmt as $resultOwned) {
					$count++;
					if ($count < 7) {
						$wearing = false;
						$disableWear = false;
						$stmt = $dbcon->prepare("SELECT * FROM wearing WHERE uid = :uid AND catalogid = :id");
						$stmt->bindParam(':id', $resultOwned['catalogid'], PDO::PARAM_INT);
						$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
						if ($stmt->rowCount() > 0) {
							$wearing = true;
						}
						
						$stmt = $dbcon->prepare("SELECT * FROM wearing WHERE uid = :uid AND type = :type");
						$stmt->bindParam(':type', $type, PDO::PARAM_STR);
						$stmt->bindParam(':uid', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
						if ($stmt->rowCount() > 4 and $type == "hats") {
							$disableWear = true;
						}
						
						$stmt = $dbcon->prepare("SELECT * FROM catalog WHERE id = :id");
						$stmt->bindParam(':id', $resultOwned['catalogid'], PDO::PARAM_INT);
						$stmt->execute();
						$result = $stmt->fetch(PDO::FETCH_ASSOC);
						if ($result['deleted'] == 0) {
							$itemName = $result['name'];
							if (strlen($itemName) > 16) {
								$itemName = substr($itemName, 0, 13) . '...';
							}
							echo '<div class="col-md-4 col-sm-4 col-xs-12 Center"><div class="well" style="box-shadow:none;height:250px;">'.htmlentities($itemName, ENT_QUOTES, "UTF-8").'<br>';
							if ($type == "shirts" or $type == "pants" or $type == "tshirts") {
								echo '<img width="150" src="https://xdiscuss.net/func/user/getImage.php?id='.$result['assetid'].'&type='.$result['type'].'&time='.time().'">';
							}else{
								if($type == "torso" or $type == "leftarm" or $type == "leftleg" or $type == "rightarm" or $type == "rightleg") {
									echo '<img width="150" src="/data/assets/package/thumbnail/'.$result['datafile'].'.png">';
								}else{
									echo '<img width="150" src="/data/assets/'.$result['type'].'/thumbnail/'.$result['datafile'].'.png">';
								}
							}
							if ($wearing == true) {
								echo '<br><form method="post"><button type="submit" class="btn btn-primary" name="unwear" value="'.$result['id'].'">Unwear</button>';
							}else{
								if ($disableWear == false) {
									echo '<br><form method="post"><button type="submit" class="btn btn-primary" name="wear" value="'.$result['id'].'">Wear</button>';
								}else{
									echo '<br><a class="btn btn-primary disabled">Wear</a>';
								}
							}
							echo '</div></div>';
						}
					}
				}
				if ($count == 0 and $page > 0) {
					header("Location: /user/character.php?type=".$type);
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
			}else{
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/character/colors.php';
			}
		?>
	</div>
</div>

<?php
	if ($type !== "colors") {
		echo '<ul class="pager">';
		if ($page == 0) {
			echo '<li class="previous disabled"><a>&larr; Back</a></li>';
		}else{
			echo '<li class="previous"><a href="/user/character.php?type='.$type.'&page='.($page-1).'">&larr; Back</a></li>';
		}
		if ($count > 6) {
			echo '<li class="next"><a href="/user/character.php?type='.$type.'&page='.($page+1).'">Next &rarr;</a></li>';
		}else{
			echo '<li class="next disabled"><a>Next &rarr;</a></li>';
		}
		echo '</ul>';
	}
?>