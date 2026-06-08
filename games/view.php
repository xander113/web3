<?php
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/includes.php';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo getName(); ?> | Server</title>
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
		<div id="content" >
				<?php
					if (isset($_GET['id'])) {
						$id = $_GET['id'];
						if (is_array($id)) {
							$hAds = true;
							include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
							echo '<div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3">';
							echo '<div class="well Center" style="box-shadow: none;">Invalid parameter</div>';
							include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
							include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
							exit;
						}
						if (strlen($id) == 0) {
							$hAds = true;
							include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
							echo '<div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3">';
							echo '<div class="well Center" style="box-shadow: none;">No ServerID specified</div>';
							include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
							include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
							exit;
						}
					}else{
						$hAds = true;
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
						echo '<div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3">';
						echo '<div class="well Center" style="box-shadow: none;">No ServerID specified</div>';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
						exit;
					}
					$stmt = $dbcon->prepare("SELECT * FROM games WHERE id = :id;");
					$stmt->bindParam(':id', $id, PDO::PARAM_INT);
					$stmt->execute();
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					if ($result['public'] == 0) {
						$gameKey = $result['key'];
						$stmtU = $dbcon->prepare("SELECT * FROM gameKeys WHERE userid=:id AND `key` = :key;");
						$stmtU->bindParam(':id', $auth_uid, PDO::PARAM_INT);
						$stmtU->bindParam(':key', $gameKey, PDO::PARAM_STR);
						$stmtU->execute();
						
						if ($stmtU->rowCount() == 0 and $result['creator_uid'] != $auth_uid and $user_rankId == 0) {
							$hAds = true;
							include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
							echo '<div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3">';
							echo '<div class="well Center" style="box-shadow: none;">This server was not found.</div>';
							include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
							include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
							exit;
						}
					}
					
					$version = $result['version'];
					if ($stmt->rowCount() == 0) {
						$hAds = true;
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
						echo '<div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3">';
						echo '<div class="well Center" style="box-shadow: none;">This server was not found.</div>';
					}else{
						include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation.php';
						echo '<div class="col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3col-xs-12 col-sm-4 col-md-6 col-sm-offset-2 col-md-offset-3">';
						$stmt = $dbcon->prepare("SELECT * FROM users WHERE id = :id");
						$stmt->bindParam(':id', $result['creator_uid'], PDO::PARAM_INT);
						$stmt->execute();
						$resultuser = $stmt->fetch(PDO::FETCH_ASSOC);
						echo '<div class="well"><div class="row">';
						echo '<div class="col-md-4 col-sm-12 col-xs-12">';
						echo '<a href="/profile.php?id='.$result['creator_uid'].'"><img width="150" src="'.getImage($resultuser).'"></a></div>';
						echo '<div class="col-md-8 col-sm-12 col-xs-12">';
						echo '<h3>'.htmlentities(filter($result['name']), ENT_QUOTES, "UTF-8").'</h3><font color="grey">Creator:</font><a href="/profile.php?id='.$resultuser['id'].'"> '.$resultuser['username'].'</a><br>';
						echo '<font color="grey">Date Created: </font>'.date('M j Y g:i A', strtotime($result['date'])).'<br>';
						if (strlen($result['description']) > 0) {
							echo '<div class="content"><font color="grey">Description: </font>'.htmlentities(filter($result['description']), ENT_QUOTES, "UTF-8").'</div>';
						}else{
							echo '<font color="grey">Description: None</font>';
						}
						echo '</div></div>';
						if ($loggedIn == true) {
							echo '<a class="btn btn-success FullWidth" href="GraphictoriaClient://'.$auth_gameKey.';'.$result['id'].';'.$auth_uid.'">Play</a>';
						}else{
							echo '<a class="btn btn-success disabled FullWidth">Play</a>';
						}
						$allowedToDelete = false;
						if ($loggedIn) {
							if ($user_rankId == 1 or $user_rankId == 3 or $result['creator_uid'] == $auth_uid) {
								$allowedToDelete = true;
								echo '<form method="post"><button type="submit" name="delete" class="btn btn-danger FullWidth">Delete Game</button></form>';
							}
						}
						echo '</div>';
						if (isset($_POST['delete']) and $loggedIn == true) {
							if ($allowedToDelete == true) {
								$query = "DELETE FROM `games` WHERE `id`=:id";
								$stmt = $dbcon->prepare($query);
								$stmt->bindParam(':id', $id, PDO::PARAM_INT);
								$stmt->execute();
								
								header("Location: /games/?v=".$version);
							}
						}
					}
					?>
					<ul class="nav nav-tabs" style="background-color:rgba(255,255,255,.84);">
					<li><a><font color="black" id="playerCount">Online Players (0)</font></a></li>
					</ul>
					<div class="well Center" style="box-shadow:none;padding:5px;">
						<div class="row">
							<?php
								$stmtc = $dbcon->prepare("SELECT * FROM users WHERE inGameId = :id;");
								$stmtc->bindParam(':id', $id, PDO::PARAM_INT);
								$stmtc->execute();
								
								$stmt = $dbcon->prepare("SELECT * FROM users WHERE inGameId = :id ORDER BY id");
								$stmt->bindParam(':id', $id, PDO::PARAM_INT);
								$stmt->execute();
								$count = 0;
								foreach($stmt as $resultk) {
									$from_time = strtotime($resultk['lastSeen']);
									$to_time = strtotime($currentTime);
									$timeSince =  round(abs($to_time - $from_time) / 60,2);
									if ($timeSince < 1 and $resultk['inGame'] == 1) {
										$count++;
										$username = $resultk['username'];
										if (strlen($username) > 10) {
											$username = substr($username, 0, 7) . '...';
										}
										echo '<div class="col-xs-4"><br>';
										echo '<a href="/profile.php?id='.$resultk['id'].'"><img width="120" src="'.getImage($resultk).'"></a><br>';
										echo '<a href="/profile.php?id='.$resultk['id'].'"><b>'.htmlentities($username, ENT_QUOTES, "UTF-8").'</b></a><br><br></div>';
									}
								}
								echo '<script>$("#playerCount").html("Online Players ('.$count.')");</script>';
								if ($count == 0) {
									echo 'There is nobody online.';
								}
							?>
						</div>
					</div>
					<?php
					if ($loggedIn) {
						if ($result['creator_uid'] == $auth_uid) {
							echo '<div class="well"><h4>Command</h4><p>Use this command to start your server</p><code>dofile("http://api.xdiscuss.net/serverscripts/server.php?key='.$result['privatekey'].'")</code></div>';
							if ($result['public'] == 0) {
								echo '<div class="well"><h4>Invites</h4><p>Use this key to invite people to your server</p><code>'.$result['key'].'</code></div>';
							}
						}
					}
				?>
			</div>
		</div>
		<?php
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/footer.php';
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		?>
	</body>
</html>