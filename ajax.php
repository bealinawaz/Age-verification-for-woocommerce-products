<?php

require "../../../wp-load.php"; global $current_user;

if(!empty($_POST["action"])){
	$ajax = $_POST["action"];
	
	if($ajax == "verifyIDCard") {
		
		$err = ""; $err = array();
		$sus = ""; $sus = array();
		
		if(empty($_POST['ida'])) { $err[] = 'ID Card A Empty. Please put data in it'; }
		if(empty($_POST['idb'])) { $err[] = 'ID Card B Empty. Please put data in it'; }
		if(empty($_POST['idc'])) { $err[] = 'ID Card C Empty. Please put data in it'; }
		if(empty($_POST['idd'])) { $err[] = 'ID Card D Empty. Please put data in it'; }

		$perso_id = $_POST['ida']." ".$_POST['idb']." ".$_POST['idc']." ".$_POST['idd'];

		$perso_checksum = perso_checksum($perso_id);
	 
		if($perso_checksum) { $sus[] = 'Your age verify Successfully.'; }
		else { $err[] = 'Your Card is not valid to verify your age.'; }
		
		$chk = ""; foreach($err as $e) { $chk .= $e; }
		
		if(!empty($chk)) {
			?><ul class="woocommerce-error"><?php foreach($err as $e) { ?><li><?php echo $e; ?></li><?php } ?></ul><?php
		
			update_user_meta( $current_user->ID, "verify_age", 0);
		} else {
			?><ul class="woocommerce-error"><?php foreach($sus as $s) { ?><li><?php echo $s; ?></li><?php } ?></ul><?php
		
			update_user_meta( $current_user->ID, "ida", $_POST["ida"]);
			update_user_meta( $current_user->ID, "idb", $_POST["idb"]);
			update_user_meta( $current_user->ID, "idc", $_POST["idc"]);
			update_user_meta( $current_user->ID, "idd", $_POST["idd"]);
			update_user_meta( $current_user->ID, "verify_age", 1);
		
		}

	}
}