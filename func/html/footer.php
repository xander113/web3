<?php
	echo '</div>';
	if (strpos($_SERVER['SCRIPT_NAME'], "footer.php")) {
		header("Location: /");
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
	if (isset($hideLogo)) {
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		exit;
	}
	if (!isset($remcom)) {
			echo '<br><br>
<footer class="footer" style="background-color:rgba(245, 245, 245, 0);left:0px;margin-left: 0px;width:100%;">
	<div class="container Center" style="">
		<p style="margin:20px 0;color:grey;"><font color="grey"><h5>Graphictoria</h5><a href="https://xdiscuss.net/forum/post.php?id=62109">Terms of Service</a></font></p>
	</div>
</footer>';
	}
	if ($loggedIn == false) {
		include_once $_SERVER['DOCUMENT_ROOT'].'/func/db/close.php';
		die;
	}
?>
<div class="modal fade" id="logoutDialog" tabindex="-1" role="dialog" aria-labelledby="logoutd">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="logoutd"><font color="grey">Are you sure?</font></h4>
         </div>
         <div class="modal-body">
			<p>You are about to sign out. Are you sure you want to do that?</p>
         </div>
		<div class="modal-footer">
			<a type="button" href="/logout.php" class="btn btn-danger">Sign out</a>
		</div>
	 </div>
   </div>
</div>