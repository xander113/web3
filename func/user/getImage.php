<?php
	if (isset($_GET['id'])) {
		$id = $_GET['id'];
	}else{
		exit;
	}
	
	if (isset($_GET['type'])) {
		$type = $_GET['type'];
	}else{
		exit;
	}
	
	$remoteImage = "http://192.168.2.10/".$type."/".$id.".png";
	$imginfo = @getimagesize($remoteImage);
	if ($imginfo) {
		header("Content-type: {$imginfo['mime']}");
		ob_end_flush();
		@readfile($remoteImage);
	}else{
		if ($type == "user") {
			$remoteImage = "http://192.168.2.10/".$type."/def2.png";
		}else{
			$remoteImage = "http://192.168.2.10/".$type."/def.png";
		}
		$imginfo = @getimagesize($remoteImage);
		header("Content-type: {$imginfo['mime']}");
		ob_end_flush();
		@readfile($remoteImage);
	}
?>