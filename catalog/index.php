<?php
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo getName(); ?> | Catalog</title>
		<?php
			echo getHead();
		?>
		<style>
			.fullWidth {
				width: 100%;
				margin: 5px 0px 0px;
			}
		</style>
	</head>
	<body>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
			if (isset($_GET['type'])) {
				$type = $_GET['type'];
				if (is_array($type)) {
					header("Location: /catalog");
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				if ($type == "hats") {
					$type = "hats";
				}elseif($type == "shirts") {
					$type = "shirts";
				}elseif($type == "pants") {
					$type = "pants";
				}elseif($type == "gear") {
					$type = "gear";
				}elseif($type == "tshirts") {
					$type = "tshirts";
				}elseif($type == "faces") {
					$type = "faces";
				}elseif($type == "heads") {
					$type = "heads";
				}elseif($type == "decals") {
					$type = "decals";
				}else{
					$type = "hats";
				}
			}else{
				$type = "hats";
			}
			if (isset($_GET['term'])) {
				$searchTerm = $_GET['term'];
				if (is_array($searchTerm)) {
					header("Location: /catalog");
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				if (strlen($searchTerm) == 0) {
					$searchTerm = "";
				}
			}
			if (isset($_GET['page'])) {
				$page = $_GET['page'];
				if (is_array($page)) {
					header("Location: /catalog");
					include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
					exit;
				}
				$offset = $page*12;
				if ($page == 0){
					$page = 0;
					$offset = 0;
				}
			}else{
				$page = 0;
				$offset = 0;
			}
			if ($page < 0) {
				header("Location: /catalog/");
				include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
				exit;
			}
		?>
		<div id="content">
			<div class="col-md-2 col-sm-2 col-xs-12">
				<a href="/catalog/?type=hats" class="btn btn-default fullWidth" style="border-radius:0px;">Hats</a>
				<a href="/catalog/?type=heads" class="btn btn-default fullWidth" style="border-radius:0px;">Heads</a>
				<a href="/catalog/?type=faces" class="btn btn-default fullWidth" style="border-radius:0px;">Faces</a>
				<a href="/catalog/?type=tshirts" class="btn btn-default fullWidth" style="border-radius:0px;">T-Shirts</a>
				<a href="/catalog/?type=shirts" class="btn btn-default fullWidth" style="border-radius:0px;">Shirts</a>
				<a href="/catalog/?type=pants" class="btn btn-default fullWidth" style="border-radius:0px;">Pants</a>
				<a href="/catalog/?type=gear" class="btn btn-default fullWidth" style="border-radius:0px;">Gear</a>
				<a href="/catalog/?type=decals" class="btn btn-default fullWidth" style="border-radius:0px;">Decals</a>
				<a href="/catalog/upload.php" class="btn btn-success fullWidth" style="border-radius:0px;">Upload</a>
			</div>
			<div class="col-md-10 col-sm-10 col-xs-12">
				<form method="post">
					<div class="form-group">
						<div class="input-group">
							<input type="text" class="form-control" name="itemname" placeholder="Item name">
							<span class="input-group-btn">
								<button type="submit" class="btn btn-primary" name="search">Search</button>
							</span>
						</div>
					</div>
				</form>
				<?php
					if (isset($_POST['search'])) {
						$str = $_POST['itemname'];
						if (strlen($str) > 0) {
							$searchTerm = $_POST['itemname'];
							header("Location: /catalog/?type=".$type."&term=".$searchTerm);
						}
					}
				?>
				<div class="well">
					<div class="row">
						<?php
							if (isset($searchTerm)) {
								$searchTermSQL = '%'.$searchTerm.'%';
								$stmt = $dbcon->prepare("SELECT * FROM catalog WHERE name LIKE :term AND type = :type AND approved = 1 ORDER BY id DESC LIMIT 13 OFFSET :offset;");
								$stmt->bindParam(':term', $searchTermSQL, PDO::PARAM_STR);
							}else{
								$stmt = $dbcon->prepare("SELECT * FROM catalog WHERE type = :type AND buyable=1 AND approved = 1 ORDER BY id DESC LIMIT 13 OFFSET :offset;");
							}
							$stmt->bindParam(':type', $type, PDO::PARAM_STR);
							$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
							$stmt->execute();
							if ($stmt->rowCount() == 0) {
								echo 'No items found.';
							}
							$count = 0;
							foreach($stmt as $result) {
								$count++;
								if ($count < 13) {
									$itemName = htmlentities($result['name'], ENT_QUOTES, "UTF-8");
									if (strlen($itemName) > 16) {
										$itemName = substr($itemName, 0, 7) . '...';
									}
									echo '<div class="col-md-4 col-sm-4 col-xs-12 Center"><div class="well" style="height:280px;box-shadow:none;"><span class="content">'.filter($itemName).'</span><br>';
									if ($type == "shirts" or $type == "pants" or $type == "tshirts") {
										echo '<img width="150" src="https://xdiscuss.net/func/user/getImage.php?id='.$result['assetid'].'&type='.$result['type'].'&time='.time().'"><br><br><font color="green"><span class="fa fa-money"></span> '.$result['price'].'</font><br><a class="btn btn-primary" href="/catalog/viewitem.php?id='.$result['id'].'">Details</a></div></div>';
									}else{
										if ($type == "decals") {
											echo '<img width="150" height="150" src="/data/assets/'.$result['type'].'/'.$result['assetid'].'.png"><br><br><a class="btn btn-primary" href="/catalog/viewitem.php?id='.$result['id'].'">Details</a></div></div>';
										}else{
											echo '<img width="150" src="/data/assets/'.$result['type'].'/thumbnail/'.$result['datafile'].'.png"><br><br><font color="green"><span class="fa fa-money"></span> '.$result['price'].'</font><br><a class="btn btn-primary" href="/catalog/viewitem.php?id='.$result['id'].'">Details</a></div></div>';
										}
									}
								}
							}
							if ($count == 0 and $page > 0) {
								header("Location: /catalog/");
								include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
								exit;
							}
					echo '</div></div><ul class="pager">';
					if ($page == 0) {
						echo '<li class="previous disabled"><a>&larr; Back</a></li>';
					}else{
						if (isset($searchTerm)) {
							echo '<li class="previous"><a href="/catalog/?type='.$type.'&page='.($page-1).'&term='.$searchTerm.'">&larr; Back</a></li>';
						}else{
							echo '<li class="previous"><a href="/catalog/?type='.$type.'&page='.($page-1).'">&larr; Back</a></li>';
						}
					}
					if ($count > 12) {
						if (isset($searchTerm)) {
							echo '<li class="next"><a href="/catalog/?type='.$type.'&page='.($page+1).'&term='.$searchTerm.'">Next &rarr;</a></li>';
						}else{
							echo '<li class="next"><a href="/catalog/?type='.$type.'&page='.($page+1).'">Next &rarr;</a></li>';
						}
					}else{
						echo '<li class="next disabled"><a>Next &rarr;</a></li>';
					}
					echo '</ul>';
				?>
			</div>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>