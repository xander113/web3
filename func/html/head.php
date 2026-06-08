<?php
	if (strpos($_SERVER['SCRIPT_NAME'], "head.php")) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
	
	function getHead() {
		return '
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<link rel="icon" href="/favicon2.ico">
		<meta name="theme-color" content="#0c7cd5">
		<link rel="stylesheet" href="/html/css/bootstrap.min.css?v=50"/>
		<link href="/html/css/ripples.min.css?v=2" rel="stylesheet">
		<link rel="stylesheet" href="/html/css/style.css?v=51"/>
		<link rel="stylesheet" href="/html/css/style3.css?v=40"/>
		<link rel="stylesheet" href="/html/css/effects.css?v=5"/>
		<link rel="stylesheet" href="/html/css/font-awesome.min.css">
		<link rel="stylesheet" href="/html/css/post.css"/>
		<script src="/html/js/jquery-3.1.0.min.js"></script>
		<script async src="/html/js/bootstrap.min.js"></script>
		<script async src="/html/js/material.min.js"></script>
		<script async src="/html/js/ripples.min.js"></script>
		<script>
			$(document).ready(function (){
				$("#content").fadeIn("fast");
			});
		</script>
		<style>
			.well {
				background-color: #eeeeee;
			}
		</style>
		<style>
			.container {
				padding-left: 15px;
				padding-right: 15px;
			}
			
			.col-xs-1, .col-sm-1, .col-md-1, .col-lg-1, .col-xs-2, .col-sm-2, .col-md-2, .col-lg-2, .col-xs-3, .col-sm-3, .col-md-3, .col-lg-3, .col-xs-4, .col-sm-4, .col-md-4, .col-lg-4, .col-xs-5, .col-sm-5, .col-md-5, .col-lg-5, .col-xs-6, .col-sm-6, .col-md-6, .col-lg-6, .col-xs-7, .col-sm-7, .col-md-7, .col-lg-7, .col-xs-8, .col-sm-8, .col-md-8, .col-lg-8, .col-xs-9, .col-sm-9, .col-md-9, .col-lg-9, .col-xs-10, .col-sm-10, .col-md-10, .col-lg-10, .col-xs-11, .col-sm-11, .col-md-11, .col-lg-11, .col-xs-12, .col-sm-12, .col-md-12, .col-lg-12 {
				padding-left: 15px;
				padding-right: 15px;
			}
			
			.panel {
				border-radius: 2px;
			}
			
			
			@media screen and (max-width: 767px) {
				.container {
					padding-left: 0px;
					padding-right: 0px;
				}
				.panel {
					border-radius: 0px;
				}
				
				.col-xs-1, .col-sm-1, .col-md-1, .col-lg-1, .col-xs-2, .col-sm-2, .col-md-2, .col-lg-2, .col-xs-3, .col-sm-3, .col-md-3, .col-lg-3, .col-xs-4, .col-sm-4, .col-md-4, .col-lg-4, .col-xs-5, .col-sm-5, .col-md-5, .col-lg-5, .col-xs-6, .col-sm-6, .col-md-6, .col-lg-6, .col-xs-7, .col-sm-7, .col-md-7, .col-lg-7, .col-xs-8, .col-sm-8, .col-md-8, .col-lg-8, .col-xs-9, .col-sm-9, .col-md-9, .col-lg-9, .col-xs-10, .col-sm-10, .col-md-10, .col-lg-10, .col-xs-11, .col-sm-11, .col-md-11, .col-lg-11, .col-xs-12, .col-sm-12, .col-md-12, .col-lg-12 {
					padding-left: 0px;
					padding-right: 0px;
				}
			}
		</style>';
	}
?>