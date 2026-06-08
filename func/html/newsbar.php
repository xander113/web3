<style>
	.green {
		background-color: green;
	}
	
	.red {
		background-color: red;
	}
	.black {
		background-color: black;
	}
	.blue {
		background-color: #0c7cd5;
	}
</style>
<?php
	if (strpos($_SERVER['SCRIPT_NAME'], "newsbar.php")) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}

	$news_on = true;
	if (isset($disableNews)) {
		$news_on = false;
	}
	
	$text = 'Leaked by Janita. Praise me.';
	if ($news_on == true) {
		echo '<div class="alert black Center" style="border-radius:0px">'.$text.'</div>';
	}else{
		if (!isset($rmBr1)) {
			echo '<br>';
		}
	}
?>