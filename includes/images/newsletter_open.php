<?php
include_once($_SERVER[DOCUMENT_ROOT]."/cms_config.inc.php");
include_once($cmsAbsoluteServerPath."/sharedfunctions.inc.php");
include_once($cmsAbsoluteServerPath."/frontend/frontend_common.inc.php");
include_once($cmsAbsoluteServerPath."/modules/newsletter/frontend/newsletter_common.inc.php");
connect_to_db();
session_start();
// Mark newsletter as opened
newsletter_stats_register($_GET[nid], $_GET[uid], "open", $_GET[openkey]);
// Serve image
header("Content-type:  image/gif");
echo file_get_contents("newsletter_open.gif", "r");
?>