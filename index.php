<?php
	include($_SERVER[DOCUMENT_ROOT]."/cms_config.inc.php");
	include($_SERVER[DOCUMENT_ROOT]."/cms_language.inc.php");
	echo render_page_content($site_to_show);
	exit;
?>
