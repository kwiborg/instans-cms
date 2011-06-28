<?php


 ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 include_once("common.inc.php");
 checkLoggedIn();

	function stripslashes_deep(&$value) {
		$value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
		return $value;
	}

	function mysql_real_escape_deep(&$value) {
		$value = is_array($value) ? array_map('mysql_real_escape_deep', $value) : mysql_real_escape_string($value);
		return $value;
	}

	$magic_quotes_gpc = get_magic_quotes_gpc();

	#$d->dump($magic_quotes_gpc);

	if ($magic_quotes_gpc == 1) {

		// Magic quotes er slået til på serveren
		stripslashes_deep($_GET);
		stripslashes_deep($_POST);
		stripslashes_deep($_COOKIE);
		stripslashes_deep($_REQUEST);

		mysql_real_escape_deep($_GET);
		mysql_real_escape_deep($_POST);
		mysql_real_escape_deep($_COOKIE);
		mysql_real_escape_deep($_REQUEST);

	} else {

		// Magic quotes er ikke slået til
		mysql_real_escape_deep($_GET);
		mysql_real_escape_deep($_POST);
		mysql_real_escape_deep($_COOKIE);
		mysql_real_escape_deep($_REQUEST);

	}

	foreach($_REQUEST as $key=>$value) {
		$$key=$value;
	}

// Check at der er valgt et site
//echo "SELECTED_SITE: ".$_SESSION["SELECTED_SITE"];
//print_r($_SESSION["CMS_USER"]["PERMISSIONS"]);
if (!is_numeric($_SESSION["SELECTED_SITE"]) || $_SESSION["SELECTED_SITE"] == 0) {
	header("location: site_selector.php");
	exit;
}

// Check access to current site!
if (!check_data_permission("DATA_CMS_ACCESSSITE", "SITES", $_SESSION["SELECTED_SITE"], "", $_SESSION[CMS_USER][USER_ID])) {
	echo "Du har ikke adgang til at redigere dette site.";
	exit;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if (!$_REQUEST[filter_display]) {
	$filter_display = "2";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ($_GET[content_identifier]) {
	$includes = $modules[$_GET[content_identifier]];
} else {
	$_GET[content_identifier] = "pages";
	$includes = $modules["pages"];
} 

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 $moduleCommonIncPHP 		= $includes[1] . "/" . $_GET[content_identifier] . "/" . $_GET[content_identifier] . "_common.inc.php";
 $moduleSubmitHandlerPHP 	= $includes[1] . "/" . $_GET[content_identifier] . "/" . $_GET[content_identifier] . "_submithandler.php";
 $moduleHeaderPHP 			= $includes[1] . "/" . $_GET[content_identifier] . "/" . $_GET[content_identifier] . "_header.php";
 $moduleCSSServer			= $includes[1] . "/" . $_GET[content_identifier] . "/" . $_GET[content_identifier] . "_style.css";
 $moduleCSSClient			= $includes[2] . "/" . $_GET[content_identifier] . "/" . $_GET[content_identifier] . "_style.css";
 $moduleJSServer			= $includes[1] . "/" . $_GET[content_identifier] . "/" . $_GET[content_identifier] . "_script.js";
 $moduleJSClient			= $includes[2] . "/" . $_GET[content_identifier] . "/" . $_GET[content_identifier] . "_script.js";
 $moduleIndexFilePHP		= $includes[1] . "/" . $_GET[content_identifier] . "/" . $_GET[content_identifier] . "_index.php";
 ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

 if (file_exists($moduleCommonIncPHP)) {
  	include($moduleCommonIncPHP);
 }

 if (file_exists($moduleSubmitHandlerPHP)) {
  	include($moduleSubmitHandlerPHP);
 }

 ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
 		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<title>Instans CMS<?php echo ": " . $modules[$_GET["content_identifier"]][0] ?></title>
 		<link rel="stylesheet" href="cms.css" type="text/css" />
 		<?php 
			if (file_exists($moduleCSSServer)) {
				echo "<link rel='stylesheet' href='$moduleCSSClient' type='text/css' />";
			}
 			if (file_exists($moduleHeaderPHP)) {
  				include($moduleHeaderPHP);
 			}
 		?>
		<script src="/cms/scripts/prototype.js" type="text/javascript"></script>
		<script src="/cms/scripts/scriptaculous/scriptaculous.js" type="text/javascript"></script>
 		<script type="text/javascript" src="commonscripts.js"></script>
		<?php
			if (file_exists($moduleJSServer)) {
				echo "<script type='text/javascript' src='$moduleJSClient'></script>";
			}
		?>
	</head>
	<body>
		<div id="wrapitall">
			<div id="topbar"><img src="../cms_configimages/logo.gif" id="logo" alt="" title="" /></div>
			<div id="main_wrapper"> 
				<div id="leftsidemenu">
  					<?php buildCmsMenu($_REQUEST[content_identifier]) ?>
 				</div>	
 				<div id="content">
  					<?php 
   						if (file_exists($moduleIndexFilePHP)) {
  							include($moduleIndexFilePHP);
 						}
  					?>
 				</div>
			</div>
		</div>
	</body>
</html>