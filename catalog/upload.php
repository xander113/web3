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
		<title><?php echo getName(); ?> | Upload</title>
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
				if (isset($_GET['error'])) {
					$id = $_GET['error'];
					if ($id == 1) {
						echo '<div class="alert alert-dismissible alert-danger">Your description is too long!</div>';
					}elseif($id == 2) {
						echo '<div class="alert alert-dismissible alert-danger">Your asset name is too long!</div>';
					}elseif($id == 3) {
						echo '<div class="alert alert-dismissible alert-danger">Price can not be higher than 99999 coins.</div>';
					}elseif($id == 4) {
						echo '<div class="alert alert-dismissible alert-danger">Price can not be lower than 1 coin.</div>';
					}elseif($id == 5) {
						echo '<div class="alert alert-dismissible alert-danger">Asset name must be at least 5 letters.</div>';
					}elseif($id == 6) {
						echo '<div class="alert alert-dismissible alert-danger">Illegal character has been detected.</div>';
					}
				}
			?>
			<div class="row">
				<div class="col-xs-8">
					<div class="panel panel-primary">
						<div class="panel-heading">Upload</div>
						<div class="panel-body">
							<p>In here you can upload an asset. This will cost you 5 coins. If your item gets deleted, you will <b>not</b> be granted a refund. In addition, you also need to wait for your asset to be approved.</p>
							<form method="post" enctype="multipart/form-data">
								<input type="text" name="title" placeholder="Name" maxlength="32" class="form-control">
								<textarea name="description" placeholder="Description" maxlength="128" class="form-control" rows="10"></textarea>
								<input type="number" name="price" placeholder="Price (Not needed when uploading a decal)" maxlength="5" class="form-control"><br>
								<p>Type :
									<select name="type">
										<option value="1">Shirt</option>
										<option value="2">Pants</option>
										<option value="3">T-Shirt</option>
										<option value="4">Decal</option>
									</select>
								</p>
								<p style="display:inline;">
								File : 
								<input style="display:inline;" type="file" name="file">
								</p>
								<button type="submit" name="upload" name="upload" class="btn btn-primary FullWidth">Upload</button>
							</form>
						</div>
						<div class="panel-footer">
							<?php
								if (isset($_POST['upload'])) {
									$uploadOK = false;
									$type = $_POST['type'];
									if ($type !== 4) {
										$price = $_POST['price'];
									}
									$description = $_POST['description'];
									$title = $_POST['title'];
									if ($type == 1) {
										$dbtype = "shirts";
									}elseif($type == 2) {
										$dbtype = "pants";
									}elseif($type == 3) {
										$dbtype = "tshirts";
									}elseif($type == 4) {
										$dbtype = "decals";
									}else{
										header("Location: /catalog/upload.php");
										include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
										exit;
									}
									$title = preg_replace("/[^ \w]+/", "", $title);
									$title = preg_replace('!\s+!', ' ', $title);
									$description = preg_replace('!\s+!', ' ', $description);
									if (strlen($title) > 32) {
										header("Location: /catalog/upload.php?error=2");
										include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
										exit;
									}
									if (strlen($title) < 5) {
										header("Location: /catalog/upload.php?error=5");
										include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
										exit;
									}
									if (strlen($description) > 128) {
										header("Location: /catalog/upload.php?error=1");
										include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
										exit;
									}
									if ($price < 1 and $dbtype !== "decals") {
										header("Location: /catalog/upload.php?error=4");
										include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
										exit;
									}
									if (strlen($price) > 5 and $dbtype !== "decals") {
										header("Location: /catalog/upload.php?error=3");
										include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
										exit;
									}
									if ($dbtype == "decals") {
										$price = 0;
									}
									$stmt = $dbcon->prepare("SELECT * FROM catalog WHERE type=:dbtype ORDER BY id DESC LIMIT 1;");
									$stmt->bindParam(':dbtype', $dbtype, PDO::PARAM_STR);
									$stmt->execute();
									$result = $stmt->fetch(PDO::FETCH_ASSOC);
									if ($stmt->rowCount() == 0) {
										$assetId = 1;
									}else{
										$assetId = $result['assetid']+1;
									}
									if ($dbtype == "shirts") {
										$target_dir = "/var/www/graphictoria/data/assets/shirts/textures/";
									}
									if ($dbtype == "pants") {
										$target_dir = "/var/www/graphictoria/data/assets/pants/textures/";
									}
									if ($dbtype == "tshirts") {
										$target_dir = "/var/www/graphictoria/data/assets/tshirts/models/";
									}
									if ($dbtype == "decals") {
										$target_dir = "/var/www/graphictoria/data/assets/decals/";
									}
									$target_file = $target_dir.$assetId.'.png';
									$check = @getimagesize($_FILES["file"]["tmp_name"]);
									list($width, $height) = @getimagesize($_FILES["file"]["tmp_name"]);
									if ($auth_uid == 11) {
										echo '<script>alert("'.$width.'");</script>';
										echo '<script>alert("'.$height.'");</script>';
									}
									if ($width != 580 and $height != 556) {
										if ($dbtype == "shirts" or $dbtype == "pants") {
											$uploadOK = false;
										}
									}
									if ($check == false) {
										echo 'The file you tried to upload is not an image.';
										$uploadOK = false;
									}else{
										$uploadOK = true;
										$imageFileType = pathinfo($_FILES['file']["name"], PATHINFO_EXTENSION);
										if ($_FILES["file"]["size"] > 1000000) {
											echo 'The file you tried to upload is too large.';
											$uploadOK = false;
										}else{
											$finfo = finfo_open(FILEINFO_MIME_TYPE);
											$mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
											if ($imageFileType != "jpg" && $imageFileType != "JPG" && $imageFileType != "png" && $imageFileType != "PNG" && $imageFileType != "jpeg" && $imageFileType != "JPEG" && $mime != "image/png" && $mime != "image/jpeg") {
												echo 'The file you tried to upload is not allowed. Only JPG, JPEG and PNG are allowed.';
												$uploadOK = false;
											}else{
												if ($user_coins < 5) {
													echo 'You do not have enough coins.';
												}else{
													if ($uploadOK == true) {
														if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
															$user_coins = $user_coins-5;
															$stmt = $dbcon->prepare("UPDATE users SET coins = :coins WHERE id = :user;");
															$stmt->bindParam(':coins', $user_coins, PDO::PARAM_INT);
															$stmt->bindParam(':user', $auth_uid, PDO::PARAM_INT);
															$stmt->execute();
															
															$stmt = $dbcon->prepare("INSERT INTO catalog (`price`, `creator_uid`, `assetid`, `name`, `description`, `type`) VALUES (:price, :user, :assetid, :name, :description, :type);");
															$stmt->bindParam(':user', $auth_uid, PDO::PARAM_INT);
															$stmt->bindParam(':price', $price, PDO::PARAM_INT);
															$stmt->bindParam(':type', $dbtype, PDO::PARAM_STR);
															$stmt->bindParam(':assetid', $assetId, PDO::PARAM_INT);
															$stmt->bindParam(':name', $title, PDO::PARAM_STR);
															$stmt->bindParam(':description', $description, PDO::PARAM_STR);
															$stmt->execute();
															
															if ($dbtype != "decals") {
																$message = 'Your asset named <b>'.$title.'</b> is pending approval. You will receive another message after approval. Once approved, you will receive the item.';
															}else{
																$message = 'Your asset named <b>'.$title.'</b> is pending approval. You will receive another message after approval. Once approved, the decal will be visible in the catalog.';
															}
															$titleM = 'Asset Approval for '.$title;
															$query = "INSERT INTO messages (`recv_uid`, `sender_uid`, `title`, `content`) VALUES (:userId2, 10370, :title, :msg);";
															$stmt = $dbcon->prepare($query);
															$stmt->bindParam(':userId2', $auth_uid, PDO::PARAM_INT);
															$stmt->bindParam(':msg', $message, PDO::PARAM_STR);
															$stmt->bindParam(':title', $titleM, PDO::PARAM_STR);
															$stmt->execute();
															
															header("Location: /catalog/?type=".$dbtype);
														}else{
															echo 'An error occurred while uploading. Please attempt again. '.$_FILES['file']['error'];
														}
													}else{
														echo 'Could not upload your file. Are you using the correct template?';
													}
												}
											}
										}
									}
								}else{
									echo 'Fill in the required fields to upload an asset. This will appear for others including yourself once it has been approved.';
								}
							?>
						</div>
					</div>
				</div>
				<div class="col-xs-4">
					<div class="well Center">
						<h4 style="color:grey;">Shirts and Pants Template</h4>
						<img src="/data/templates/clothing.png?v=2" class="img-responsive">
						<p>Use this template to create your shirt or pants. All other templates will be denied.</p>
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