<?php
	if (strpos($_SERVER['SCRIPT_NAME'], "navigation.php")) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
	if (isset($_GET['searchVal'])) {
		$searchVal = $_GET['searchVal'];
		if (strlen($searchVal) > 0) {
			header("Location: /users.php?term=".$searchVal);
			include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
			exit;
		}
	}
?>
<script>
	$(function () {
		$("[data-toggle='tooltip']").tooltip();
	});
</script>
<style>
	@media screen and (max-width: 767px) {
		.mobilenav {
			background: #2196f3;
		}
		.navbar {
			
		}
	}
</style>
<nav class="navbar navbar-inverse navbar-fixed-top" style="webkit box-shadow:none;max-height:50px;min-height:0px;<?php if (isset($remcom)) { echo 'background-color:#292525;'; }?>">
	<div class="container">
		<div class="navbar-header">
			<button style="padding-top:1.0px;padding-bottom:1.0px;" type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<?php
				if (!isset($hideLogo)) {
					echo '<a class="navbar-brand" style="height:50px;padding:0px 5px;" href="/"><img src="/html/img/logo6.png"></a>';
				}
			?>
		</div>
		<div class="navbar-collapse collapse mobilenav" id="bs-example-navbar-collapse-1" aria-expanded="false" style="height: 1px;">
			  <ul class="nav navbar-nav">
				<?php
					if (!isset($tfa)) {
							echo '<li><a style="color:white;padding-top:13px;padding-bottom:14px;" href="/forum/">Forum</a></li>
							<li><a style="color:white;padding-top:13px;padding-bottom:14px;" href="/games/">Games</a></li>
							<li><a style="color:white;padding-top:13px;padding-bottom:14px;" href="/catalog/">Catalog</a></li>';
							if ($loggedIn) {
								if ($user_rankId > 0) {
									echo '<li><a style="color:white;padding-top:13px;padding-bottom:14px;" href="/admin/">Admin</a></li>';
								}
							}
						echo '<li class="dropdown"><a style="color:white;padding-top:13px;padding-bottom:14px;" class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false"><span class="caret"></span></a>';
						echo '<ul class="dropdown-menu"><li><a href="https://help.xdiscuss.net/blog/" class="WhiteText">Blog</a></li><li><a href="https://discord.gg/2Daa8uP" class="WhiteText">Discord</a></li><li><a href="https://developers.xdiscuss.net" class="WhiteText">Developer Forum</a></li></ul>';
						echo '</li>';
					}
				?>
			  </ul>
			  <?php 
				if (!isset($tfa)) {
					echo '<form class="navbar-form navbar-left" style="margin:bottom:0px;margin-top:4.5px" role="search">
				<div class="form-group">
					<input type="text" name="searchVal" class="form-control" placeholder="Find user">
				</div>
				<button type="submit" class="btn btn-warning">Search</button>
			 </form>';
				}
			  ?>
			 <ul class="nav navbar-nav navbar-right">
				<?php
					function humanTiming ($time)
					{
						$time = time()-$time;
						$time = 86400-$time;
						$time = ($time<1)? 1 : $time;
						$tokens = array (
							31536000 => 'year',
							2592000 => 'month',
							604800 => 'week',
							86400 => 'day',
							3600 => 'hour',
							60 => 'minute',
							1 => 'second'
						);
						foreach ($tokens as $unit => $text) {
							if ($time < $unit) continue;
							$numberOfUnits = floor($time / $unit);
							return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
						}
					}
					if ($loggedIn == true) {
						$time = strtotime($lastAward);
						if (!isset($tfa)) {
							echo '<li><a style="color:white;padding-top:13px;padding-bottom:14px;width:125px;" class="Center" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="'.humanTiming($time).' until next reward"><span class="fa fa-money"></span> '.$user_coins.'</a></li>';
						}
						
						$query = "SELECT id FROM `friendRequests` WHERE `recvuid` = :id";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
						$user_friendRequests = $stmt->rowCount();
						if ($user_friendRequests == 0 and !isset($tfa)) {
							echo '<li><a style="color:white;padding-top:13px;padding-bottom:14px;" href="/friends/"><span class="fa fa-users"></span></a></li>';
						}else{
							if (!isset($tfa)) {
								echo '<li><a style="color:white;padding-top:13px;padding-bottom:14px;" href="/friends/?mode=requests"><span class="fa fa-users"></span><span class="badge" style="background-color: #f44336;font-size:10px;padding: 6px 6spx;"> '.$user_friendRequests.'</span></a></li>';
							}
						}
						
						$query = "SELECT id FROM `messages` WHERE `recv_uid` = :id AND `read` = 0";
						$stmt = $dbcon->prepare($query);
						$stmt->bindParam(':id', $auth_uid, PDO::PARAM_INT);
						$stmt->execute();
						$user_numMessages = $stmt->rowCount();
						if ($user_numMessages == 0 and !isset($tfa)) {
							echo '<li><a style="color:white;padding-top:13px;padding-bottom:14px;" href="/messages/"><span class="fa fa-comments"></span></a></li>';
						}else{
							if (!isset($tfa)) {
								echo '<li><a style="color:white;padding-top:13px;padding-bottom:14px;" href="/messages/"><span class="fa fa-comments"></span><span class="badge" style="background-color: #f44336;font-size:10px;padding: 6px 6px;"> '.$user_numMessages.'</span></a></li>';
							}
						}
					}
				?>
				<li class="dropdown">
					<?php
						if ($loggedIn == true) {
							echo '<a href="#" style="color:white;padding-top:13px;padding-bottom:14px;" class="dropdown-toggle" data-toggle="dropdown"><span class="fa fa-user"></span> '.htmlentities($auth_uname, ENT_QUOTES, "UTF-8").' <span class="caret"></span></a>';
						}else{
							echo '<ul><a href="/auth.php" class="btn btn-success" style="box-shadow:none;-webkit-box-shadow:none;background-color:#0c6ab5;margin: 5px 15px;"><span class="fa fa-user"></span> Sign In / Register</a></ul>';
						}
					?>
					<ul class="dropdown-menu" role="menu">
						<?php
							if ($loggedIn == true) {
								if (!isset($tfa)) {
									echo '<li><a href="/user/settings.php" class="WhiteText"><span class="fa fa-cog"></span> Settings</a></li>';
									echo '<li><a href="/user/character.php" class="WhiteText"><span class="fa fa-male"></span> Character</a></li>';
									echo '<li><a href="/forum/myposts.php" class="WhiteText"><span class="glyphicon glyphicon-book"></span> My Forum Posts</a></li>';
								}
								if (isset($remcom)) {
									echo '<li><a href="/logout.php" class="WhiteText"><span class="fa fa-sign-out"></span> Sign Out</a></li>';
								}else{
									echo '<li><a href="#" data-toggle="modal" data-target="#logoutDialog" class="WhiteText"><span class="fa fa-sign-out"></span> Sign Out</a></li>';
								}
							}
						?>
					</ul>
				</li>
			</ul>
		</div>
	</div>
</nav>
<?php
	if ($loggedIn) {
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/navigation2.php';
	}
	$news_on = false;
	include_once $_SERVER['DOCUMENT_ROOT'].'/func/html/newsbar.php';
	if (!isset($doContain)) {
		echo '<div class="container">';
	}
	if (!isset($hAds)) {
	$rand = rand(0, 1);
	if ($rand == 0) {
				echo '<div style="margin:10px 0px 0px;text-align: center;height: 100px;">
					<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
					<ins class="adsbygoogle"
						 style="display:block"
						 data-ad-client="ca-pub-2653985119940841"
						 data-ad-slot="8627309816"
						 data-ad-format="auto"></ins>
					<script>
						setTimeout(function(){(adsbygoogle = window.adsbygoogle || []).push({})}, 500);
					</script>
				</div>';
			}else{
				echo '<div style="margin:10px 0px 0px;text-align: center;height: 100px;">
					<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
					<ins class="adsbygoogle"
						 style="display:block"
						 data-ad-client="ca-pub-2653985119940841"
						 data-ad-slot="1169591812"
						 data-ad-format="auto"></ins>
					<script>
						setTimeout(function(){(adsbygoogle = window.adsbygoogle || []).push({})}, 500);
					</script>
				</div>';
			}
	}
?>