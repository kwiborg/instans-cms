<?php
	if ($arr_content[usegraphicalheadings] == 1) {
		echo "\n<script type='text/javascript' src='$arr_content[baseurl]/includes/javascript/gfxheadings.js'></script>";
	}
	// Javascript variables, translated
	echo "\n<script type='text/javascript'>";
	echo "\n\tvar SearchMinchars = '".cmsTranslate("SearchMinchars")."';";
	echo "\n</script>";
?>