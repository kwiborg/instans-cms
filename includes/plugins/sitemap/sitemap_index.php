<?php
	/// SITEMAP PLUGIN
	/// CJS, 14/6/2007	
	include($_SERVER[DOCUMENT_ROOT]."/includes/plugins/sitemap/sitemap_common.inc.php");
	echo sitemap(1, array("Hovedmenu" => 1), $arr_content, array("NEWS"));
?>