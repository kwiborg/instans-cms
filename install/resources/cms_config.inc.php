<?php
// ====================================================================
// SESSION HANDLING + VARIOUS PHP.INI SETTINGS
// --------------------------------------------------------------------
	if (file_exists("$_SERVER[DOCUMENT_ROOT]/../sessions/")) {
		ini_set("session.save_path","$_SERVER[DOCUMENT_ROOT]/../sessions/");
	}
	ini_set("session.gc_maxlifetime","28800");
	ini_set("arg_separator.output", "&amp;"); 

// ====================================================================
// INCLUDE BASECONFIG
// --------------------------------------------------------------------
	$baseconfigFile = $_SERVER['DOCUMENT_ROOT'] . "/cms/cms_baseconfig.inc.php";
	require_once($baseconfigFile);

// ====================================================================
// DATABASE SETTINGS
// --------------------------------------------------------------------
	$db_host 					=	"___DB_HOST___";
	$db_user					=	"___DB_USER___";
	$db_pass					=	"___DB_PASS___";
	$db_name					=	"___DB_NAME___";

// ====================================================================
// INCLUDE COMMON FILES AND CONNECT TO DATABASE
// --------------------------------------------------------------------
// Only include frontend_common + sharedfunctions on frontend
	$arr_request = explode("/",$_SERVER[REQUEST_URI]);
	if (strtolower($arr_request[1]) != "cms" && strtolower($arr_request[1]) != "includes") {
		include_once($cmsAbsoluteServerPath."/sharedfunctions.inc.php");	
		include_once($cmsAbsoluteServerPath."/frontend/frontend_common.inc.php");
		include_once($_SERVER[DOCUMENT_ROOT]."/includes/custom_functions.php");
		session_start();	
		connect_to_db(); 

		// ====================================================================
		// DETERMINE WHICH SITE TO SHOW
		// --------------------------------------------------------------------
		$site_to_show = return_site_to_show();
		$_SESSION[CURRENT_SITE] = $site_to_show;
		$site_to_switch_on = $site_to_show;
	} else {
		session_start();	
		$site_to_switch_on = $_SESSION[SELECTED_SITE];
	}

// --------------------------------------------------------------------
// AFTER THIS LINE, ONLY DEFINE CONFIG SETTINGS IF YOU WISH TO OVERRIDE
// THE BASECONFIG SETTINGS
// REMEMBER TO ADD SWITCH-ENTRIES FOR EACH SITE_ID.
// ALSO REMEMBER THAT THERE MUST ALWAYS BE A DEFAULT CASE IN THE SWITCH
// THIS IS USED TO LOAD CMS PREFERENCES
// --------------------------------------------------------------------

switch ($site_to_switch_on) {
	default:
		$useModRewrite		 = true;
		$useModRewrite_enforce_url_rewrite = true;
	
		// ====================================================================
		// CUSTOM SITE PATHS
		// --------------------------------------------------------------------
		$cmsDomain					= 	"___CMS_DOMAIN___"; 
		// Need to redeclare these variables using $cmsDomain:
		$cmsURL 					= 	$cmsDomain."/cms"; 
		$imagesFolderPath 			=	$cmsDomain . "/includes/images";
		$shopimagesFolderPath 		=	$imagesFolderPath . "/shopimages_cache";
		$picturearchive_UploaddirAbs=	$cmsDomain.$picturearchive_UploaddirRel;
		$fckEditorCustomConfigPath  = 	"";
		$pluginsPath = $_SERVER[DOCUMENT_ROOT] . "/includes/plugins";
		$cookieDomain				= "___COOKIE_DOMAIN___";
		$cmsSiteId 					= ___CMS_SITE_ID___;
		$stat_url 					= "___STAT_URL___";
		
		$dropbox_ftp_server 	= "___DROPBOX_FTP_SERVER___";
		$dropbox_ftp_user 		= "___DROPBOX_FTP_USER___";
		$dropbox_ftp_pass	 	= "___DROPBOX_FTP_PASS___";
}

?>