<script type="text/javascript">
</script>
<?php 
	global $shopPermissions;
	include_once($_SERVER[DOCUMENT_ROOT]."/cms/modules/shop/frontend/shop_common.inc.php");
	// Make sure that $arr_content is available
	if (!is_array($arr_content)) {
		$arr_content = return_content_build_array();
	}

	if ($arr_content[action] == "showproduct"){
		if ($shopPermissions[browse] != "") {
			if ($_SESSION[LOGGED_IN]) {
				if (checkPermissionFE($shopPermissions[browse], $_SESSION[USERDETAILS][2], false)) {
					// Krav om login - logget ind && HAR rettighed
					echo showProducts($arr_content, $arr_content[group], true, $arr_content[product]);
				} else {
					// Krav om login - logget ind && HAR rettighed
					echo "<div class='usermessage_error'>Du er logget ind, men du har ikke adgang til at se varer i shoppen. Kontakt os venligst, hvis det er en fejl!</div>";				
					}
			} else {
				// Krav om login - ikke logget ind
				echo requestLogin("Du skal være logget ind for at se varer i shoppen.", $arr_content);		
			}
		} else {
			// Ikke krav om login
			echo showProducts($arr_content, $arr_content[group], true, $arr_content[product]);
		}
	}



	if ($arr_content[action] == "showgroup" || !$arr_content[action]){
		if ($shopPermissions[browse] != "") {
			if ($_SESSION[LOGGED_IN]) {
				if (checkPermissionFE($shopPermissions[browse], $_SESSION[USERDETAILS][2], false)) {
					// Krav om login - logget ind && HAR rettighed
					echo showGroups($arr_content);
				} else {
					// Krav om login - logget ind && HAR rettighed
					echo "<div class='usermessage_error'>Du er logget ind, men du har ikke adgang til at se varer i shoppen. Kontakt os venligst, hvis det er en fejl!</div>";				
					}
			} else {
				// Krav om login - ikke logget ind
				echo requestLogin("Du skal være logget ind for at se varer i shoppen.", $arr_content);		
			}
		} else {
			// Ikke krav om login
			echo showGroups($arr_content);
		}
	}
?>