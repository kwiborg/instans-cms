<?php
 include_once($_SERVER[DOCUMENT_ROOT]."/cms_config.inc.php");
 include_once($_SERVER[DOCUMENT_ROOT]."/cms_language.inc.php");

 session_start();
 connect_to_db();

 // To support old skool mysql_db_query
 $dbname = $db_name;
 include_once($cmsAbsoluteServerPath."/sharedfunctions.inc.php");
 // include_once("modules/modules_config.php");
 include_once($cmsAbsoluteServerPath."/fckeditor/fckeditor.php");

 require_once($_SERVER[DOCUMENT_ROOT]."/cms/scripts/smarty/libs/Smarty.class.php");	
 require_once($_SERVER[DOCUMENT_ROOT]."/cms/scripts/html_mime_mail/htmlMimeMail.php");

function connect_to_db() {
	global $db_user, $db_pass, $db_host, $db_name;
	// Connect to the database-server:
	if (!($db=mysql_connect($db_host,$db_user, $db_pass))) {
		echo "Error connecting to database!";
		exit();
	}
	if (!(mysql_select_db($db_name,$db))) {
		echo "Error selecting database!";
		exit();
	}
	mysql_query("SET NAMES utf8");
	mysql_query("SET CHARACTER_SET utf8");
}

function moduleInstalled($moduleName_str) {
	/**
	*	Function to check if module is installed
	*/
	global $modules;
	if (array_key_exists($moduleName_str, $modules)!== FALSE) {
		return true;
	} else {
		return false;
	}
}

function buildCmsMenu($content_identifier) {
	global $cmsVersion;
	/**
	*	Function to build cms menu
	*	May be improved to handle rights management of module access
	*/
	global $modules, $cms_Menu;
	foreach ($cms_Menu as $menuGroup){
		$showgroup = false; // Group not shown unless user has permission for at least one of the menuitems in the group
		$modulesInGroup = $menuGroup[0];
		if ($menuGroup[1]) {
			$groupName = $menuGroup[1];
			$moreThanOneModuleInGroup = true;
			// Check permission for each item in group
			foreach ($modulesInGroup as $moduleName){
				if (!$showgroup) {
					$showgroup = checkpermission($modules[$moduleName][3]);
				}
			}
		} else {
			$mg = $menuGroup[0][0];			
			$groupName = $modules[$mg][0];
			$moreThanOneModuleInGroup = false;
		}
		if ($moreThanOneModuleInGroup && $showgroup) {
			echo "<div class='menugroup'>";
			echo $groupName;
		}
		foreach ($modulesInGroup as $moduleName){
			// Only show menuitem if current user has relevant permission
			if (checkpermission($modules[$moduleName][3])) {
				echo "<div class='menuitem'><a href='index.php?content_identifier=$moduleName' class='";
				if ($_GET[content_identifier] == $moduleName) {
					echo "sidemenu_valgt";
				} else {
					echo "sidemenu";
				}
				echo "'>".$modules[$moduleName][0]."</a></div>";
			}
		}
		if ($moreThanOneModuleInGroup && $showgroup) {
			echo "</div>";
		}
	}
  echo "
  <div class='hvemerloggetind'>
   Du er logget ind som:<br /><strong>" . 
   $_SESSION["CMS_USER"]["USER_FIRSTNAME"] . " " . $_SESSION["CMS_USER"]["USER_LASTNAME"] . 
  "</strong><br /><a class='generel' href='#' onclick='if(confirm(\"Vil du logge af?\")) location=\"login.php\"'>Log af</a><br/>".(checkPermission("CMS_USERS") ? "<a class='generel' href='index.php?content_identifier=users&amp;dothis=rediger&amp;id=".$_SESSION["CMS_USER"]["USER_ID"]."'>Brugerprofil</a><br />" : "")."<br />Tidspunkt for login:<br /><strong>" . $_SESSION["CMS_USER"]["LOGIN_TIME"] . "</strong>
  <br /><br />
  Du redigerer sitet:<br />";
  $sitename = returnSiteName($_SESSION[SELECTED_SITE]);
  echo "<strong>$sitename</strong>";

//	$sql = "select count(*) from SITES";
//	$result = mysql_query($sql);
//	if (mysql_result($result,0) > 1) {
	if ($_SESSION["CMS_USER"]["MULTIPLE_SITES"] == 1) {
		echo "&nbsp;(<a class='generel' href='site_selector.php'>Skift</a>)";
	}

	echo "<br/><br/>Se publiceret website:<br/><a class='generel' target='_blank' href='";
	echo returnBASE_URL($_SESSION[SELECTED_SITE]).returnSITE_PATH($_SESSION[SELECTED_SITE]);
	echo "'>Åbn website</a><div id='cmsVersion'>Instans CMS v$cmsVersion</div></div>";

}

function includeCorrect($what) {
  global $modules;
  $pagename 	= 	$modules[$what][1];
  $scriptname 	=	$modules[$what][2]; 
  $submithandlername 	=	$modules[$what][3]; 
  return array($pagename, $scriptname, $submithandlername);
 }

function checkLoggedIn() {
	global $cmsURL;
	if (!$_SESSION["CMS_USER"]) {
		header("location: $cmsURL/login.php");
		exit;
	}
}

function returnDistinctUserPermissions($userid, $site_id=""){
// RETURN ALL DISTINCT PERMISSIONS FOR USER $userid. THESE PERMISSIONS ARE THE ACCUMULATED PERMISSIONS
// FROM _ALL_ GROUPS
// 2007-04-20	-	Added optional $site_id parameter. When set the function only returns permissions valid for this site.
	$sql = "
		select distinct
			PERMISSIONS.NAME AS NAME
		from 
			GROUPS, USERS_GROUPS, GROUPS_PERMISSIONS, PERMISSIONS
		where
			USERS_GROUPS.GROUP_ID = GROUPS.ID and 
			USERS_GROUPS.GROUP_ID = GROUPS_PERMISSIONS.GROUPS_ID and
			GROUPS_PERMISSIONS.PERMISSIONS_ID = PERMISSIONS.ID and 
			USERS_GROUPS.USER_ID='$userid' ";
	if ($site_id != "") {
		$sql .= "and GROUPS.SITE_ID in (0,'$site_id')";
	}		
			
	$sql .= "order by
			PERMISSIONS.NAME asc
	";
	$result = mysql_query($sql);
	while($row = mysql_fetch_array($result)){
 		$userPermissions[] = $row[NAME];
	}
/*
	echo "Permissions (user: $userid / site: $site_id):<pre>";
	print_r($userPermissions);
	echo "</pre>";
*/
	return $userPermissions;
}

 function checkPermission($permission_str, $terminate_bol=false) {
	// Takes permission NAME as string and checkes it against session variable.
	// Returns TRUE if user has permission. If user doesn't have permission the function returns 
	// FALSE except if $terminate_bol is TRUE in which case the function outputs an error message and
	// terminates php script.
	if (is_array($_SESSION[CMS_USER][PERMISSIONS])) {
		if (in_array($permission_str, $_SESSION[CMS_USER][PERMISSIONS])) {
			return true;
		} else {
			if ($terminate_bol) {
				$message = "Du har ikke lov til at benytte denne funktion. For at benytte funktionen skal du være i en brugergruppe der er tildelt rettigheden '".$permission_str."'. <a href='index.php'>Tilbage til forsiden</a>";
				usermessage("usermessage_error", $message);
				exit;
			} else {
				return false;
			}
		}
	} else {
		$message = "Du har ingen rettigheder til at redigere dette site. For at redigere dette site, skal du være i en brugergruppe der er tildelt rettigheder på dette site. <a href='index.php'>Tilbage til forsiden</a>";
		usermessage("usermessage_error", $message);
		unset($_SESSION["CMS_USER"]);
		exit;
	}
 }
 
 function setLoginValues($userid, $username, $firstname, $lastname, $permissions) {
  $_SESSION["CMS_USER"] = array();
  $_SESSION["CMS_USER"]["USER_ID"] 			= $userid;
  $_SESSION["CMS_USER"]["USERNAME"] 		= $username;
  $_SESSION["CMS_USER"]["USER_FIRSTNAME"] 	= $firstname;
  $_SESSION["CMS_USER"]["USER_LASTNAME"] 	= $lastname;
  $_SESSION["CMS_USER"]["LOGIN_TIME"] 		= date("d-m-y H:i");
  $_SESSION["CMS_USER"]["PERMISSIONS"] 		= $permissions;
 }
 
 function buildAuthorDropdown() {
  $html = "
  <select id=\"filter_author\" name=\"filter_author\" class=\"standard_select\">
   <option value=\"ALL_AUTHORS\">Alle forfattere</option>
   <option value=\"" . $_SESSION["CMS_USER"]["USER_ID"] ."\">".$_SESSION[CMS_USER][USER_FIRSTNAME]." ".$_SESSION[CMS_USER][USER_LASTNAME]."</option>";
  $sql = "select ID, FIRSTNAME, LASTNAME from USERS where ID != " . $_SESSION["CMS_USER"]["USER_ID"] . " and DELETED='0' order by FIRSTNAME asc, LASTNAME asc"; 
  $result = mysql_query( $sql);
  while ($row = mysql_fetch_array($result)) {
   $html .= "<option value=\"" . $row["ID"] . "\">" . $row["FIRSTNAME"] . " " . $row["LASTNAME"] . "</option>\n";
  }
  $html .= "</select>\n";
  return $html;
 }
 
function opretNyRow($tabel) {
//  global $dbname;
	$nu = time();
	if ($tabel != "MENUENTRIES" && $tabel != "GROUPS") {
		$sql = "
		   insert into 
			$tabel
			(
			 AUTHOR_ID, CREATED_DATE, CHANGED_DATE, UNFINISHED
			)
			values 
			(
			 '" . $_SESSION["CMS_USER"]["USER_ID"] . "', '$nu', '$nu', '1'
			)
		   ";
	}
	if ($tabel == "MENUENTRIES") {
	   $sql = "
	   insert into 
		$tabel
		(
		 AUTHOR_ID, CREATED_DATE, CHANGED_DATE, UNFINISHED
		)
		values 
		(
		 '" . $_SESSION["CMS_USER"]["USER_ID"] . "', '$nu', '$nu', '1'
		)
	   ";
	}
	if ($tabel == "GROUPS") {
	   $sql = "
		insert into 
		GROUPS (
		AUTHOR_ID, CREATED_DATE, CHANGED_DATE, UNFINISHED, SITE_ID
		) 
		values (
		 '".$_SESSION["CMS_USER"]["USER_ID"]."', '$nu', '$nu', '1', '$_SESSION[SELECTED_SITE]')
	   ";
	}  

	$result = mysql_query( $sql) or die(mysql_error());
	return mysql_insert_id();
}
   
function sletRow($id, $tabel) {
	$sql = "update $tabel set DELETED='1' where ID='$id'";
	$result = mysql_query( $sql) or die(mysql_error());  
	if ($tabel == "PAGES") {
		$sql = "select POSITION, PARENT_ID from $tabel where ID='$id'";
		$result = mysql_query( $sql) or die(mysql_error());  
		$row = mysql_fetch_row($result);
		$sql = "update $tabel set POSITION=POSITION-1 where POSITION > $row[0] and PARENT_ID='$row[1]'";
		mysql_query( $sql) or die(mysql_error());  
	}

	// Delete any rewrite-keys for this combination of $tabel and $id
	$sql = "delete from REWRITE_KEYWORDS where TABLENAME = '$tabel' and REQUEST_ID = '$id'";
	mysql_query( $sql) or die(mysql_error());  

	sitemap_generator();
}  
 
function gemRow($changed_date, $POSTVARS, $author_id, $tabel, $unfinished) {
	foreach ($POSTVARS as $key=>$value) {
		$$key = $value;
	}
	// -----------> PAGES <-------------------------------------------------------------------------
	if ($tabel == "PAGES") {
		if ($no_display == "on") $no_display = "1";

		// Change frontpage setting
		if ($is_sitelang_frontpage == "on") {
			$is_sitelang_frontpage = "1";
			$sql = "update PAGES set IS_FRONTPAGE='0' where SITE_ID='$_SESSION[SELECTED_SITE]' and LANGUAGE='$languageselector'";
			mysql_query( $sql) or die("Unable to change frontpage setting: ".mysql_error());
		}

		$sql = "select POSITION from PAGES where PARENT_ID='$parentid' and MENU_ID='$menuid' and DELETED='0' order by POSITION desc limit 1";
		$result = mysql_query( $sql) or die("Unable to retrieve POSITION: ".mysql_error());
		$row = mysql_fetch_row($result);	
		if ($mode == "opret") {
			$ny_position = 1*$row[0]+1;
		} else {
			$ny_position = $current_position;
		}
		$sql = "select THREAD_ID from PAGES where ID='$parentid'";
		$result = mysql_query( $sql) or die("Unable to retrieve THREAD_ID: ".mysql_error());
		$row = mysql_fetch_row($result);	
		$threadid = $row[0];

		// Convert "is_menuplaceholder" checkbox
		if ($is_menuplaceholder == "on") {
			$is_menuplaceholder = 1;
		}

		// Only write TEMPLATE if different from SITE DEFAULT_TEMPLATE
		$defTemplate = returnDefaultTemplateId($_SESSION[SELECTED_SITE]);
		if ($templateselector == $deftemplate) {
			$templateselector = '';
		}

		// Only write META_SEOTITLE if different from site default
	 	$usetitle_default = returnGeneralSetting("META_TITLE_USEPAGESCOLUMN");

		if ($usetitle_res != $usetitle_default) {
			if ($usetitle_res != "BREADCRUMB" && $usetitle_res != "HEADING" ) {
				$seo_title = $usetitle_customtitle;
			} else {
				$seo_title = $usetitle_res;
			}
		} else {
			$seo_title = "";
		}
		// Use site_id from MENUS-table
		$site_id = returnFieldValue("MENUS", "SITE_ID", "MENU_ID", $menuid);

	
		$sql = "update $tabel set
				 PARENT_ID='$parentid', MENU_ID='$menuid', SITE_ID='$site_id', THREAD_ID='', 
				 BREADCRUMB='$breadcrumb', HEADING='$heading', SUBHEADING='$subheading', CONTENT='$Indhold', 
				 EDIT_AUTHOR_ID='$author_id', CHANGED_DATE='$changed_date', 
				 UNFINISHED='$unfinished', PUBLISHED='$published_res', NO_DISPLAY='$no_display',
				 LOCKED_BY_USER='$protection_selector_res', PROTECTED='$beskyttet_res', 
				 LANGUAGE='$languageselector', POSITION='$ny_position',
				 IS_FRONTPAGE='$is_sitelang_frontpage', POINTTOPAGE_ID='$pointToPageSelector', POINTTOPAGE_URL='$pointtopage_url',
				 PHP_HEADERINCLUDE_PATH='$php_headerinclude_path', PHP_INCLUDE_PATH='$php_include_path', PHP_INCLUDEAFTER_PATH='$php_includeafter_path', 
				 BOOK_ID='$bookSelector', IS_MENUPLACEHOLDER='$is_menuplaceholder', TEMPLATE='$templateselector',
				 META_DESCRIPTION='$meta_description', META_KEYWORDS='$meta_keywords', META_SEOTITLE='$seo_title',
				 POPUP='$POSTVARS[popup]',
				 REDIRECT_TO_URL='$POSTVARS[redirect_to_url]'
   				where
   				 ID='$det_nye_id'
   				";
		$result = mysql_query( $sql) or die("Unable to update PAGES: ".mysql_error());
		if ($parentid == 0) {
			$sql = "update $tabel set THREAD_ID='$det_nye_id' where ID='$det_nye_id'";
		} else {
			$sql = "update $tabel set THREAD_ID='$threadid' where ID='$det_nye_id'";
		}
		$result = mysql_query( $sql) or die("Unable to update THREAD_ID: ".mysql_error());
		$sql = "delete from GROUPS_PAGES where PAGE_ID='$det_nye_id'";
		$result = mysql_query( $sql) or die("Unable to delete group association: ".mysql_error());
		foreach ($POSTVARS as $key=>$value) {
			if (strstr($key, "B_")) { // indsæt grupperefs i GROUPS_PAGES
				$temp = explode("_", $key);
				$group_id =  $temp[1];
	 			$sql2 = "insert into GROUPS_PAGES (PAGE_ID, GROUP_ID) values ('$det_nye_id', '$group_id')";
				$sql_cache[] = $sql2;
	 			$result2 = mysql_query( $sql2) or die("Could not add group association: ".mysql_error());
	 			$sql3 = "update PAGES set PROTECTED='2' where ID='$det_nye_id'";
				$sql_cache[] = $sql3;
	 			$result3 = mysql_query( $sql3) or die("Could not update group association: ".mysql_error());
    		}
		}
		if ($POSTVARS["children_inherit_rights"] == "on"){
			set_recursive_rights($det_nye_id);
		}
		if ($mode=="opret") {
			$sql = "insert into BOX_SETTINGS (PAGE_ID, NEWS, EVENTS, SEARCH, STF, NEWSLETTER) values ('$det_nye_id', '1', '1', '1', '1', '1')";     
			mysql_query( $sql) or die("Could not add custom box settings: ". mysql_error());
		}
		// Save customfields
		save_customfields($POSTVARS);
	}
	// -----------> NEWS <-------------------------------------------------------------------------
	if ($tabel == "NEWS") {
	if ($languageselector == "0" || $languageselector == "") {
		$languageselector = returnFieldValue("NEWSFEEDS", "DEFAULT_LANGUAGE", "ID", $newsfeedid);
	}
   if ($frontpage_status == "on") $frontpage_status = "1";
   if ($global_status == "on") $global_status = "1";
	if ($news_date != "") {
		$news_date = reverseDate($news_date);
	}
	if ($limit_start != "") {
		$limit_start = reverseDate($limit_start);
	}
	if ($limit_end != "") {
		$limit_end = reverseDate($limit_end);
	}

	$site_id = returnFieldValue("NEWSFEEDS", "SITE_ID", "ID", $newsfeedid);
	
   $sql = "
   update $tabel set
	 HEADING='$heading', SUBHEADING='$subheading', CONTENT='$Indhold', AUTHOR_ID='$author_id', 
	 CHANGED_DATE='$changed_date', 
     UNFINISHED='$unfinished', BEING_EDITED='0', PUBLISHED='$published_res', LOCKED_BY_USER='$protection_selector_res',
	 NEWS_DATE='" . $news_date . "',
	 LIMITED='$limit_res', LIMIT_START='" . $limit_start . "', LIMIT_END='" . $limit_end . "', 
	 SITE_ID='$site_id', FRONTPAGE_STATUS='$frontpage_status', LANGUAGE='$languageselector',
	 NEWSFEED_ID='$newsfeedid', GLOBAL_STATUS='$global_status', IMAGE_ID='$imageid'
   where
     ID='$det_nye_id' 
   ";   
   $result = mysql_query( $sql) or die(mysql_error());  
  }
	// -----------> EVENTS <-------------------------------------------------------------------------
  if ($tabel == "EVENTS") {
	if ($global_status == "on") $global_status = "1";
	if ($focusevent == "on") $focusevent = "1";
	if ($startdate != "") {
		$startdate = reverseDate($startdate);
	}
	if ($enddate != "") {
		$enddate = reverseDate($enddate);
	}
	if ($languageselector == "0" || $languageselector == "") {
		$languageselector = returnFieldValue("CALENDARS", "DEFAULT_LANGUAGE", "ID", $calendar_id_res);
	}
	
	$site_id = returnFieldValue("CALENDARS", "SITE_ID", "ID", $calendar_id_res);
	
	$sql = "
		update $tabel set 
	 HEADING='$heading', SUBHEADING='$subheading', CONTENT='$Indhold', AUTHOR_ID='$author_id', 
	 CHANGED_DATE='$changed_date', 
     UNFINISHED='$unfinished', BEING_EDITED='0', PUBLISHED='$published_res', LOCKED_BY_USER='$protection_selector_res',
     DURATION='$duration_selector_res',
	 STARTDATE='" . $startdate . "', ENDDATE='" . $enddate . "',
	 TIMEOFDAY='$timeofday', LANGUAGE='$languageselector',
	 SITE_ID='$site_id', GLOBAL_STATUS='$global_status', FOCUSEVENT='$focusevent', IMAGE_ID='$imageid', CALENDAR_ID='$calendar_id_res'
   where
     ID='$det_nye_id' 
   ";
   $result = mysql_query( $sql) or die(mysql_error());
  }  
	// -----------> GROUPS <-------------------------------------------------------------------------
  if ($tabel == "GROUPS") {
   $sql = "
   update $tabel set
	 CHANGED_DATE='$changed_date', 
     UNFINISHED='$unfinished', PARENT_ID='$parent_id',
	 GROUP_NAME='$name', DESCRIPTION='$description',
	 REGISTRATION_OPEN='$POSTVARS[registration_open]', EDITING_OPEN='$POSTVARS[editing_open]',
	 NOTIFY_USER_ID='$POSTVARS[notify_user_id]', LANDING_GROUP_ID='$POSTVARS[landing_group_id]',
	 USERLIST_OPEN='$POSTVARS[userlist_open]', SORT_BY='$POSTVARS[sort_by]'
   where
     ID='$det_nye_id' 
   ";
   $result = mysql_query( $sql) or die(mysql_error());

	// Delete all permissions for current group
	$sql = "delete from GROUPS_PERMISSIONS where GROUPS_ID = '$det_nye_id'";
	if (mysql_query($sql)) {
		// Add checked permissions
		$sql = "insert into GROUPS_PERMISSIONS (GROUPS_ID, PERMISSIONS_ID) values ";
		foreach ($POSTVARS as $key=>$value) {
			if (strstr($key, "PERMISSION_")) { // indsæt grupperefs i GROUPS_PAGES
				$temp = explode("_", $key);
				$values[] = "($det_nye_id, $temp[1])";
			}
		}
		if (is_array($values)) {
			$values = implode(",", $values);
			$sql .= $values;
			if (!mysql_query($sql)) {
				echo "Error adding permissions!";
			}
		}
	}
  }    
	// -----------> GENERAL_SETTINGS <-------------------------------------------------------------------------
  if ($tabel == "GENERAL_SETTINGS") {
   $contact_emails = str_replace(" ", "", $contact_emails);
   if ($autoremove_deadlinks=="on") $autoremove_deadlinks=1;
   if ($take_site_offline=="on") $take_site_offline=1;
   $sql = "
   update $tabel set
	META_DESCRIPTION='$meta_description', META_KEYWORDS='$meta_keywords', CONTACT_EMAILS='$contact_emails'
   where
     ID='$_SESSION[SELECTED_SITE]' 
   ";
   $result = mysql_query( $sql) or die(mysql_error());
  }      
 sitemap_generator();
 }
 
function set_recursive_rights($inherit_page_id){
	$sql1 = "select ID from PAGES where PARENT_ID='$inherit_page_id' and UNFINISHED='0' and DELETED='0'";
  	$result1 = mysql_query($sql1) or die(mysql_error());
  	while ($row1 = mysql_fetch_assoc($result1)){
		$sql2 = "delete from GROUPS_PAGES where PAGE_ID='$row1[ID]'";
		mysql_query($sql2);
		$sql3 = "select * from GROUPS_PAGES where PAGE_ID='$inherit_page_id'";
		$result3 = mysql_query($sql3);
		$protected_state = returnFieldValue("PAGES", "PROTECTED", "ID", $inherit_page_id);
		$sql5 = "update PAGES set PROTECTED='".$protected_state."' where ID='".$row1[ID]."'";
		mysql_query($sql5);
		while ($row3 = mysql_fetch_assoc($result3)){
			$sql4 = "insert into GROUPS_PAGES (GROUP_ID, PAGE_ID) values ('".$row3[GROUP_ID]."', '".$row1[ID]."')";
			mysql_query($sql4);
		}
		set_recursive_rights($row1["ID"]);		
	}
}
  
 function rydOp($tabel) {
  $sql = "delete from $tabel where UNFINISHED='1' and AUTHOR_ID='" . $_SESSION["CMS_USER"]["USER_ID"] . "'";
  $result = mysql_query( $sql) or die(mysql_error());
 }

function billederRydOp() {
	global $picturearchive_Uploaddir;
	$sql = "select * from PICTUREARCHIVE_PICS where UNFINISHED='1' and AUTHOR_ID = '".$_SESSION[CMS_USER][USER_ID]."'";
	$result = mysql_query( $sql) or die(mysql_error());
	while ($row = mysql_fetch_array($result)) {
		$f1 = "$picturearchive_Uploaddir/" . returnFolderName($row["FOLDER_ID"], 1, "PICTUREARCHIVE_FOLDERS") . "/" . $row["FILENAME"];
		$f2 = "$picturearchive_Uploaddir/" . returnFolderName($row["FOLDER_ID"], 1, "PICTUREARCHIVE_FOLDERS") . "/thumbs/" . $row["FILENAME"];
		$f3 = "$picturearchive_Uploaddir/" . returnFolderName($row["FOLDER_ID"], 1, "PICTUREARCHIVE_FOLDERS") . "/originals/" . $row["FILENAME"];
		if (file_exists($f1)) unlink($f1);
		if (file_exists($f2)) unlink($f2);
		if (file_exists($f3)) unlink($f3);
	}
	$sql = "delete from PICTUREARCHIVE_PICS where UNFINISHED='1'";
	$result = mysql_query( $sql) or die(mysql_error());
}

function rydOp_all() {
	rydOp("CUSTOM_BOXES");
	rydOp("EVENTS");
	rydOp("USERS");
	rydOp("NEWS");
	rydOp("PAGES");
	rydOp("CUSTOM_BOXES");
	rydOp("BLOGS");
	rydOp("BLOGPOSTS");
	$sql = "delete from GRANTS where USER_ID='" . $_SESSION["CMS_USER"]["USER_ID"] . "'";
	$result = mysql_query( $sql) or die(mysql_error());
	billederRydOp();
}
 
function korrektVerbum($mode) {
	if ($mode == "opret") $verbum = "Opret";
	if ($mode == "rediger") $verbum = "Rediger";
	echo $verbum;
}

function buildTemplateDropdown($current_template, $id="templateselector", $type="PAGE") {
	$sql = "select ID, NAME from TEMPLATES where TYPE = '$type' and SITE_ID in (0,'$_SESSION[SELECTED_SITE]')";
	$result = mysql_query( $sql) or die(mysql_error());
	$numTemplates = mysql_num_rows($result);
	$html = "<select id='$id' name='$id' class='inputselect'>";
	if ($numTemplates == 0) {
		$html .= "<option value='0'>Der er ikke oprettet nogen templates</option>";
	} else {
		while ($row = mysql_fetch_array($result)) {			
			$html .= "<option value='". $row["ID"]."'";
			if ($current_template == $row["ID"]) {
				$html .= " selected";
			}
			$html .= ">". $row["NAME"] ."</option>\n";
		}
	}
	$html .= "</select>";
	return $html;
}

function buildLanguageDropdown($current_language, $disabled = true, $id="languageselector", $class = "inputselect") {
	$sql = "select ID, NAME from LANGUAGES";
	$result = mysql_query( $sql) or die(mysql_error());
	$numLanguages = mysql_num_rows($result);
	$html = "<select id='$id' name='$id' class='$class' ";
	if ($disabled) {
		$html .= "disabled";
	}
	$html .= ">";
	if ($numLanguages == 0) {
		$html .= "<option value='0'>Sitet er ikke sprogversioneret</option>";
	} else {
		while ($row = mysql_fetch_array($result)) {			
			$html .= "<option value='". $row["ID"]."'";
			if ($current_language == $row["ID"]) {
				$html .= " selected";
			}
			$html .= ">". $row["NAME"] ."</option>\n";
		}
	}
	$html .= "</select>";
	return $html;
}

function languageName($id) {
	$sql = "select NAME from LANGUAGES where ID='$id'";
	$result = mysql_query( $sql) or die(mysql_error());
	$row = mysql_fetch_array($result);
	return $row["NAME"];
}

function languageShortName($id) {
	$sql = "select SHORTNAME from LANGUAGES where ID='$id'";
	$result = mysql_query( $sql) or die(mysql_error());
	$row = mysql_fetch_array($result);
	return $row["SHORTNAME"];
}


function build_rewritekey_input($rw_title, $rw_tablename, $rw_requestid, $rw_suggestfrom) {
	/* 
		2007-05-23: Function to build the input elements needed to add rewrite functionality to a content item (MAP)
					Example call: build_rewritekey_input("Meningsfuld side-adresse", "BLOGS", $_GET[id], "this.form.blog_title.value");
	*/
	$html = "<h2>
				<div style='float:left;'>$rw_title</div>
				<div id='ajaxloader_rewrite' style='display:none;'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></div>
			</h2>
			<input disabled type='text' id='rewrite_keyword' name='rewrite_keyword' class='inputfelt' value='";
	$html .= return_rewrite_keyword('', $rw_requestid, $rw_tablename, $_SESSION[SELECTED_SITE]);
			$html .= "' onblur='keyword_onblur($rw_suggestfrom, this.value, $rw_requestid, \"$rw_tablename\", $_SESSION[SELECTED_SITE])' />&nbsp;
			<input type='button' value='Ret'  class='inputfelt_kort' onclick='edit_keyword()' />
			<input type='button' value='Foreslå'  class='inputfelt_kort' onclick='if (edit_keyword()) suggest_rewrite_keyword($rw_suggestfrom, $rw_requestid, \"$rw_tablename\", $_SESSION[SELECTED_SITE])' />";
	return $html;
}


 //
 /////////////////////// BRUGERE (USERS) /////////////////////////////////////////////////////////////////
 
/*
// 2007-04-20	-	Obsolete?
 function brugerOversigt($group_id, $in_out)
 {
  global $dbname, $sortby, $sortdir;
  if ($group_id == false) {
	$sql = "
		SELECT DISTINCT U.USERNAME, U.ID, U.FIRSTNAME, U.LASTNAME, U.TRANSFER_TO_GROUP
		FROM USERS U
		LEFT OUTER JOIN USERS_GROUPS UG ON U.ID = UG.USER_ID
		WHERE (
		U.DELETED='0' and U.UNFINISHED='0' and
	";
	if (returnFieldValue("GENERAL_SETTINGS", "NEWSLETTER_GROUPID", "ID", $_SESSION[SELECTED_SITE]) != "" && returnFieldValue("GENERAL_SETTINGS", "NEWSLETTER_GROUPID", "ID", $_SESSION[SELECTED_SITE]) != NULL){
		$sql .= "
			(UG.GROUP_ID IS NULL OR UG.GROUP_ID NOT IN (".returnFieldValue("GENERAL_SETTINGS", "NEWSLETTER_GROUPID", "ID", $_SESSION[SELECTED_SITE])."))
		";
	} else {
		$sql .= "(UG.GROUP_ID IS NULL)";
	}
	$sql .= ")";
  }
  if ($group_id && $in_out == 0) {
   $sql = "
   select 
    USERS_GROUPS.USER_ID, USERS_GROUPS.GROUP_ID, USERS.ID, USERS.USERNAME, USERS.FIRSTNAME, USERS.LASTNAME
   from 
    USERS U, USERS_GROUPS UG
   where
    USERS.ID != USERS_GROUPS.USER_ID and USERS_GROUPS.GROUP_ID='$group_id' and
	DELETED='0' and UNFINISHED='0'
   ";
  }
  if ($group_id && $in_out == 1) {
   $sql = "
   select 
    USERS_GROUPS.USER_ID, USERS_GROUPS.GROUP_ID, USERS.ID, USERS.USERNAME, USERS.FIRSTNAME, USERS.LASTNAME
   from 
    USERS U, USERS_GROUPS UG
   where
    USERS.ID = USERS_GROUPS.USER_ID and USERS_GROUPS.GROUP_ID='$group_id' and
	DELETED='0' and UNFINISHED='0'
   ";
  }  
  if (!$sortdir) $sortdir = "DESC";
  if ($sortby && $sortdir) $sql .= " order by U.$sortby $sortdir";
  $result = mysql_query( $sql) or die(mysql_error());
  $tomt_ikon = "<img src='images/piltom.gif' border='0'>";
  if ($sortdir == "DESC") 
  {
   $sortdir = "ASC"; 
   $sortdir_changed=true;
   $ikon = "<img src='images/pilned.gif' border='0'>";
  }
  if ($sortdir == "ASC" && !$sortdir_changed) 
  {
   $sortdir = "DESC";
   $ikon = "<img src='images/pilop.gif' border='0'>";
  }
  if (!$group_id || ($group_id && $in_out == 1)) $html = "
   <table class='oversigt'>
   <tr class='trtop'>" . 
    ($group_id ? "<td class='kolonnetitel' width=100>Medlem?</td>" : "");
   	$html.="<td class='kolonnetitel'><a href='index.php?content_identifier=users&dothis=oversigt&sortby=FIRSTNAME&group_id=$group_id&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu' class='kolonnetitel'>Fornavn&nbsp;" . (($sortby=="FIRSTNAME") ?  $ikon : $tomt_ikon) . "</td>
    <td class='kolonnetitel'><a href='index.php?content_identifier=users&dothis=oversigt&sortby=LASTNAME&group_id=$group_id&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu' class='kolonnetitel'>Efternavn&nbsp;" . (($sortby=="LASTNAME") ?  $ikon : $tomt_ikon) . "</td>
    <td class='kolonnetitel'><a href='index.php?content_identifier=users&dothis=oversigt&sortby=USERNAME&group_id=$group_id&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu' class='kolonnetitel'>Brugernavn&nbsp;" . (($sortby=="USERNAME") ?  $ikon : $tomt_ikon) . "</td>
    <td class='kolonnetitel'>Funktioner</td>
   </tr>
  ";
  $i=0;
  while ($row = mysql_fetch_array($result))
  {
   $i++;
   $c=$i%2+1;
   $html .=  "
   <tr class='oversigt$c' onmouseover='IEColorShift(this.id)' onmouseout='IEColorUnShift(this.id, $c)' id='pagerow_$i'>" .
   ($group_id ? ($in_out == 1 ? "<td><input type='checkbox' checked /></td>" : "<td><input type='checkbox' /></td>") : "") ;
    $html .="
	<td>" . $row["FIRSTNAME"] . "</td>
	<td>" . $row["LASTNAME"] . "</td>
	<td>" . $row["USERNAME"] . "</td>
	<td>
	 <input type='button' class='lilleknap' value='Rediger' onclick='location=\"index.php?content_identifier=users&amp;dothis=rediger&amp;id=" . $row["ID"] . "\"'>
	 <input ".($_SESSION["CMS_USER"]["USER_ID"] == $row["ID"] ? "disabled" : "")." type='button' class='lilleknap' value='Slet' onclick='slet(" . $row["ID"] . ", \"users\")'>
	 <input type='button' class='lilleknap' value='Velkomstmail' onclick='if (confirm(\"Vil du sende en mail til denne bruger med vedkommendes brugernavn og password?\")) location=\"index.php?content_identifier=users&dothis=resendinfo&amp;userid=$row[ID]\"'>
	 ".($row[TRANSFER_TO_GROUP] ? "<input type='button' class='lilleknap' value='Flyt til \"".($gname=returnFieldValue("GROUPS", "GROUP_NAME", "ID", $row[TRANSFER_TO_GROUP]))."\"' onclick='if (confirm(\"Vil du flytte denne bruger til gruppen $gname og sende vedkommende en velkomstmail?\")) location=\"index.php?content_identifier=users&dothis=transfer&amp;userid=$row[ID]&amp;transfertoid=$row[TRANSFER_TO_GROUP]\"'>" : "")."
	</td>
   </tr>
   ";
  }
  if (!$group_id || ($group_id && $in_out == 0)) $html .= "</table>";
  return $html;
 }
*/
 
 function opretNyBruger()
 { 
  global $dbname;
  $nu = time();
  $sql = "
   insert into 
   USERS (
	AUTHOR_ID, CREATED_DATE, CHANGED_DATE, UNFINISHED
   ) 
   values (
    '".$_SESSION["CMS_USER"]["USER_ID"]."', '$nu', '$nu', '1')
  ";
  $result = mysql_query( $sql) or die(mysql_error());
  return mysql_insert_id();
 }
 
 function gemBruger($POSTVARS){
	foreach ($POSTVARS as $key=>$value){
   		$$key = $value;
		if (strstr($key, "B_") && $value == "on"){
			$temp = explode("_", $key);
			$arr_checked[] = $temp[1];
		}
	}
	$str_member_of = implode(",", $arr_checked);
	$sql = "delete from USERS_GROUPS where USER_ID='$POSTVARS[det_nye_id]' and GROUP_ID NOT IN ($str_member_of) and GROUP_ID NOT IN (select ID from GROUPS where HIDDEN = '1' or SITE_ID not in (0,'$_SESSION[SELECTED_SITE]'))"; // Delete groups that are not hidden and does not belong to current group (or global)
	mysql_query($sql) or die(mysql_error());
	$user_id = $POSTVARS[det_nye_id];
	foreach ($arr_checked as $group_id){
		$sql = "select GROUP_ID from USERS_GROUPS where USER_ID='$user_id' and GROUP_ID='$group_id'";
		$res = mysql_query($sql);
		if (mysql_num_rows($res) == 0){
			$sql = "select MAX(POSITION)+1 as NEWPOS from USERS_GROUPS where GROUP_ID='$temp[1]'";
			$res_max = mysql_query($sql) or die(mysql_error());
			$row_max = mysql_fetch_assoc($res_max);
			$sql = "insert into USERS_GROUPS (USER_ID, GROUP_ID, POSITION) values ('$user_id', '$group_id', '$row_max[NEWPOS]')";
			$result = mysql_query($sql) or die(mysql_error());
		}
	}

  /*
  foreach ($POSTVARS as $key=>$value)
  {
   $$key = $value;
   if (strstr($key, "B_")) { // indsæt grupperefs i USERS_GROUPS
    $temp = explode("_", $key);
	$sql = "select MAX(POSITION)+1 as NEWPOS from USERS_GROUPS where GROUP_ID='$temp[1]'";
	$res_max = mysql_query($sql) or die(mysql_error());
	$row_max = mysql_fetch_assoc($res_max);
	$sql = "insert into USERS_GROUPS (USER_ID, GROUP_ID, POSITION) values ('$det_nye_id', '$temp[1]', '$row_max[NEWPOS]')";
	$result = mysql_query( $sql) or die(mysql_error());
   }
  }
  */
  
  $username = strtolower($username);
  $password = strtolower($password);
  $sql = "
   update 
    USERS 
   set 
    USERNAME='$username', PASSWORD='$password1', UNFINISHED='0',
	FIRSTNAME='$firstname', LASTNAME='$lastname', COMPANY='$company', ADDRESS='$address', ZIPCODE='$zipcode', CITY='$city',
	PHONE='$phone', CELLPHONE='$cellphone', EMAIL='$email', CV='$cv',
	DATE_OF_BIRTH='".reverseDate($POSTVARS[date_of_birth])."', DATE_OF_HIRING='".reverseDate($POSTVARS[date_of_hiring])."', 
	INITIALS='$POSTVARS[initials]', DEPARTMENT='$POSTVARS[department]', JOB_TITLE='$POSTVARS[job_title]',
	EMAIL_VERIFIED='".(trim($email) != "" ? "1" : "0")."', NEVER_PUBLIC='$POSTVARS[never_public]', IMAGE_ID='$POSTVARS[imageid]', 
	PASSWORD_ENCRYPTED='".md5($password1)."', COUNTRY='$POSTVARS[country]'
   where 
    ID='$det_nye_id'
  ";
  $result = mysql_query( $sql) or die(mysql_error());
 }


 //
 /////////////////////// GRUPPER (GROUPS) /////////////////////////////////////////////////////////////////

function usergroupSelector($group_id) {
	$sql = "select 
				ID, 
				GROUP_NAME
			from
				GROUPS
			where
				UNFINISHED = 0 and
				HIDDEN = 0 and
				DELETED = 0 and
				SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
			order by GROUP_NAME asc";
	$result = mysql_query( $sql) or die(mysql_error());
	while ($row = mysql_fetch_array($result)) {
		if ($group_id == $row["ID"]) {
			$selected = "selected";
		} else {
			$selected = "";
		}
		$html .= "<option value='" . $row["ID"] . "' $selected>" . $row["GROUP_NAME"] . "</option>\n";
	}
	return $html;
}



 function gruppeOversigt($indent = 0, $parent_id = 0, $h, $level = 0)
 {
  global $dbname, $loops, $sortby, $sortdir;
  $sql = "
  select 
   ID, PARENT_ID, GROUP_NAME, DESCRIPTION
  from 
   GROUPS
  where 
   PARENT_ID='$parent_id' and UNFINISHED='0' and DELETED='0' and HIDDEN='0' and SITE_ID in (0,$_SESSION[SELECTED_SITE]) 
  order by $sortby $sortdir
  ";
  $result = mysql_query( $sql) or die(mysql_error());
  if (mysql_num_rows($result) == 0) return "<tr><td>Ingen grupper er oprettet.</td></tr>";
  while ($row = mysql_fetch_array($result)) {
   $i++;
   $c=$i%2+1;
   $indentor = "<img src='images/emptyspace.gif' width='$indent' height='1' alt='' title=''>";
   if ($h == $row["ID"]) $class = "highlighted";
   echo "
    <tr class='oversigt$c' onmouseover='IEColorShift(this.id)' onmouseout='IEColorUnShift(this.id, $c)' id='pagerow_$i'>";
/*	 <td>
	  $row[ID]
	 </td>
	 */
	 
	 echo "<td>" . 
	  $indentor . "<a class='$class' href=\"index.php?content_identifier=groups&dothis=rediger&id=" . $row["ID"] . "&parent_id=" . $row["PARENT_ID"] . "\">" . $row["GROUP_NAME"] . "</a>
     </td>
     <td>
      ". (strlen($row[DESCRIPTION]) > 50 ? substr($row[DESCRIPTION],0,50)." [...]" : $row[DESCRIPTION]) . "
     </td>
	 <td>
	  <input type='button' value='Rediger' class='lilleknap' onclick='location=\"index.php?content_identifier=groups&amp;dothis=rediger&amp;id=" . $row["ID"] . "&parent_id=" . $row["PARENT_ID"] . "\"'> 
	  <input type='button' value='Slet' class='lilleknap' " . (hasChildren($row["ID"], "GROUPS") ? "disabled" : "") . " onclick='sletGruppe(" . $row["ID"] . ")'> 
	  <!--<input type='button' value='Lav undergruppe' class='lilleknap' onclick='opretNyGruppe(" . $row["ID"] . ")'>-->
	  <input type='button' value='Medlemmer' class='lilleknap' onclick='medlemmer(" . $row["ID"] . ")'> 
	 </td>
	</tr>";
   $loops++;
   gruppeOversigt($indent+20, $row["ID"], $h, $level + 1);
  }
 }
 
function groupMembers($group_id, $showmembers){
	if ($showmembers){
		$sql = "
			SELECT DISTINCT U. * 
			FROM USERS U, USERS_GROUPS UG
			WHERE U.ID = UG.USER_ID
			AND U.UNFINISHED = '0'
			AND U.DELETED = '0'
			AND UG.GROUP_ID = '$group_id'
		";
		$users_result = mysql_query($sql);
		while ($user_row = mysql_fetch_array($users_result)){
			$html .= "
				<input type='checkbox' name='MEMBER_$user_row[ID]' id='NOTMEMBER_$user_row[ID]' / onclick='removeMember($user_row[ID], $group_id)' checked>
				<a href='index.php?content_identifier=users&dothis=rediger&id=$user_row[ID]'>$user_row[FIRSTNAME] $user_row[LASTNAME] ($user_row[USERNAME])</a><br/>
			";
		}
   	} else {
		$sql = "
			SELECT DISTINCT U.ID, U.FIRSTNAME, U.LASTNAME, U.USERNAME
			FROM USERS U, USERS_GROUPS UG, GROUPS G
			WHERE U.ID = UG.USER_ID
			AND UG.GROUP_ID = G.ID
			AND UG.GROUP_ID != $group_id
			AND G.HIDDEN = 0
			AND G.DELETED='0'
			AND G.UNFINISHED='0'
			AND U.ID NOT 
			IN (
			SELECT U.ID
			FROM USERS U, USERS_GROUPS UG, GROUPS G
			WHERE U.ID = UG.USER_ID
			AND UG.GROUP_ID = G.ID
			AND UG.GROUP_ID = $group_id
			AND G.HIDDEN = 0
			AND G.DELETED='0'
			AND G.UNFINISHED='0'
			)
			AND U.DELETED='0'
			AND U.UNFINISHED='0'
			ORDER BY U.ID, UG.GROUP_ID
		";
		$users_result = mysql_query($sql);
		while ($user_row = mysql_fetch_array($users_result)){
			$html .= "
				<input type='checkbox' name='NOTMEMBER_$user_row[ID]' id='NOTMEMBER_$user_row[ID]' onclick='makeMember($user_row[ID], $group_id)' />
				<a href='index.php?content_identifier=users&dothis=rediger&id=$user_row[ID]'>$user_row[FIRSTNAME] $user_row[LASTNAME] ($user_row[USERNAME])</a><br/>
			";
   		}
  	}
	return $html;
}
 

function addGroupMember($user_id, $group_id){
 	$sql = "select * from USERS_GROUPS where USER_ID='$user_id' and GROUP_id='$group_id'";
	$result = mysql_query($sql) or die(mysql_error());
	if (mysql_num_rows($result) != 0){
		return;
	} else {
		$sql = "select MAX(POSITION)+1 as NEWPOS from USERS_GROUPS where GROUP_ID='$group_id'";
		$res_max = mysql_query($sql) or die(mysql_error());
		$row_max = mysql_fetch_assoc($res_max);
		$sql = "insert into USERS_GROUPS (USER_ID, GROUP_ID, POSITION) values ('$user_id', '$group_id', '$row_max[NEWPOS]')";
		$result = mysql_query($sql) or die(mysql_error());
	}
}

 function removeGroupMember($user_id, $group_id)
 {
  global $dbname;
  $sql = "select * from USERS_GROUPS where USER_ID=$user_id and GROUP_id=$group_id";
  $result = mysql_query( $sql) or die(mysql_error());
  if (mysql_num_rows($result)==0) return;
  else {
   $sql = "delete from USERS_GROUPS where USER_ID=$user_id and GROUP_ID=$group_id";
   $result = mysql_query( $sql) or die(mysql_error());
  }
 }
 
 function updateUsersGroups($group_id)
 {
  global $dbname;
  $sql = "delete from USERS_GROUPS where GROUP_ID=$group_id";
  $result = mysql_query( $sql) or die(mysql_error());
 }
 
 function gruppeOversigtShort($indent = 0, $parent_id = 0, $h, $level = 0, $user_id)
 {
  global $dbname, $loops, $script;
  $sql = "
  select 
   ID, PARENT_ID, GROUP_NAME, DESCRIPTION
  from 
   GROUPS
  where 
   PARENT_ID='$parent_id' and 
   UNFINISHED='0' and 
   DELETED='0' and 
   SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
  order by GROUP_NAME asc
  ";
  $result = mysql_query( $sql) or die(mysql_error());
  if (mysql_num_rows($result) == 0) return "<tr><td>Ingen grupper er oprettet.</td></tr>";
  while ($row = mysql_fetch_array($result)) {
   $sql2 = "select * from USERS_GROUPS where USER_ID=$user_id and GROUP_ID=$row[ID]";
   $result2 = mysql_query( $sql2) or die(mysql_error());
   $member = "";
   if (mysql_num_rows($result2)>0) $member = "checked";
   echo "<div>$indentor <input $member on_click='if (this.checked) checkUnderDogs($row[ID], true); if (!this.checked) checkUnderDogs($row[ID], false)' type='checkbox' name='B_$row[ID]' id='B_$row[ID]' /> 	
   $row[GROUP_NAME]</div>";
   $script .= "boxes[$row[ID]] = $row[PARENT_ID];\n";
   $loops++;
   gruppeOversigtShort($indent+20, $row["ID"], $h, $level + 1, $user_id);
  }
 }
 
 function gruppeOversigtShortPages($indent = 0, $parent_id = 0, $h, $level = 0, $entry_id)
 {
  global $dbname, $loops, $script;
  $sql = "
  select 
   ID, PARENT_ID, GROUP_NAME, DESCRIPTION
  from 
   GROUPS
  where 
   PARENT_ID='$parent_id' and UNFINISHED='0' and DELETED='0' and HIDDEN='0' and SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
  order by GROUP_NAME asc
  ";
  $result = mysql_query( $sql) or die(mysql_error());
  if (mysql_num_rows($result) == 0) return "<tr><td>Ingen grupper er oprettet.</td></tr>";
  while ($row = mysql_fetch_array($result)) {
   $sql2 = "select * from GROUPS_PAGES where PAGE_ID=$entry_id and GROUP_ID=$row[ID]";
   $result2 = mysql_query( $sql2) or die(mysql_error());
   $member = "";
   if (mysql_num_rows($result2)>0) $member = "checked";
   $indentor = "<img src='images/emptyspace.gif' width='$indent' height='1' alt='' title='' />";
   echo "<div>$indentor <input $member onclick='if (this.checked) checkUnderDogs($row[ID], true); if (!this.checked) checkUnderDogs($row[ID], false)' type='checkbox' name='B_$row[ID]' id='B_$row[ID]' /> 	
   $row[GROUP_NAME]</div>";
   $script .= "boxes[$row[ID]] = $row[PARENT_ID];\n";
   $loops++;
   gruppeOversigtShortPages($indent+20, $row["ID"], $h, $level + 1, $entry_id);
  }
 }
  
 //
 /////////////////////// MENUER (MENUS) /////////////////////////////////////////////////////////////////

function menuSelector($bool_checkpermission=true) {
	$sql = "select MENU_ID, MENU_TITLE from MENUS where SITE_ID='$_SESSION[SELECTED_SITE]' order by MENU_ID asc";
	$result = mysql_query( $sql) or die(mysql_error());
	while ($row = mysql_fetch_array($result)) {
		if (check_data_permission("DATA_CMS_MENU_ACCESS", "MENUS", $row["MENU_ID"], "", $_SESSION["CMS_USER"]["USER_ID"]) || $bool_checkpermission==false) {
			$html .= "<option value='" . $row["MENU_ID"] . "'>" . $row["MENU_TITLE"] . "</option>\n";
		}
	}
	return $html;
}
 
function returnMenuTitle($menuid) {
	$sql = "select MENU_TITLE from MENUS where MENU_ID='$menuid'";
	$result = mysql_query( $sql) or die(mysql_error());
	$row = mysql_fetch_row($result);
	return $row[0];
}
 
 function returnParentId($id)
 {
  global $dbname; 
  $sql = "select PARENT_ID from PAGES where ID='$id'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  return $row[0];  
 }
 
 function returnMenuId($id)
 {
  $sql = "select MENU_ID from PAGES where ID='$id'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  return $row[0];  
 }

 function returnDefaultTemplateId($id)
 {
  $sql = "select DEFAULT_TEMPLATE from SITES where SITE_ID='$id'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  return $row[0];  
 }

 function returnMenuLanguage($menu_id)
 {
  $sql = "select DEFAULT_LANGUAGE from MENUS where MENU_ID='$menu_id'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  return $row[0];  
 }

function returnNewsfeedLanguage($newsfeed_id) {
  $sql = "select DEFAULT_LANGUAGE from NEWSFEEDS where ID='$newsfeed_id'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  return $row[0];  
}
function returnNewsfeedName($newsfeed_id) {
  $sql = "select NAME from NEWSFEEDS where ID='$newsfeed_id'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  return $row[0];  
}

 function returnLanguageCount() {
  $sql = "select count(*) from LANGUAGES";
  $result = mysql_query( $sql) or die(mysql_error());
  return mysql_result($result,0);
 }

 
 function returnPosition($id)
 {
  $sql = "select POSITION from PAGES where ID='$id'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  return $row[0];  
 }

 function returnSitepath($site_id)
 {
  $sql = "select SITE_PATH from SITES where SITE_ID='$site_id'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  return $row[0];  
 }


 function returnFolderName($folder_id, $mode, $table)
 {
  global $dbname;
  $sql = "select FOLDERNAME, TITLE from $table where ID='$folder_id'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_array($result);
  if ($mode == 1) return $row["FOLDERNAME"];
  if ($mode == 2) return $row["TITLE"];
 }

 function returnFolderName_recursive($folder_id, $mode, $table, $str_foldername="") {
 	$sql = "select FOLDERNAME, TITLE, PARENT_ID from $table where ID='$folder_id'";
	if ($result = mysql_query($sql)) {
		if ($row = mysql_fetch_array($result)) {
			if ($str_foldername != "") {
				$str_foldername = " -> ".$str_foldername;
			}
			if ($mode == 1) $str_foldername=$row["FOLDERNAME"].$str_foldername;
			if ($mode == 2) $str_foldername=$row["TITLE"].$str_foldername;
		}
		if ($row["PARENT_ID"] == 0) {
			return $str_foldername;
		} else {
			return returnFolderName_recursive($row["PARENT_ID"], $mode, $table, $str_foldername);
		}
	}		
 }



 function hasChildren($id, $tabel)
 {
  global $dbname;
  $sql = "select * from $tabel where PARENT_ID='$id' and DELETED='0'";
  $result = mysql_query( $sql) or die(mysql_error());
  if (mysql_num_rows($result) == 0) return false;
  if (mysql_num_rows($result) > 0)  return true;
 }
 
 function flytop($id)
 {
  global $dbname; 
  $parentid = returnParentId($id);
  $menuid = returnMenuId($id);
  $position = returnPosition($id);
  if ($position > 1)
  {
   $sql1  = "update PAGES set POSITION='999' where ID='$id';";
   $sql2  = "update PAGES set POSITION='$position' where PARENT_ID='$parentid' and MENU_ID='$menuid' and POSITION=($position-1);";
   $sql3  = "update PAGES set POSITION=($position-1) where ID='$id';";
   $result = mysql_query( $sql1) or die(mysql_error());
   $result = mysql_query( $sql2) or die(mysql_error());
   $result = mysql_query( $sql3) or die(mysql_error());
  }
 }
 
 function flytned($id)
 {
  global $dbname; 
  $parentid = returnParentId($id);
  $menuid = returnMenuId($id);
  $position = returnPosition($id);
  if ($position < returnHighestPos($parentid, $menuid))
  {
   $sql1 = "update PAGES set POSITION='999' where ID='$id';";
   $sql2 = "update PAGES set POSITION='$position' where PARENT_ID='$parentid' and MENU_ID='$menuid' and POSITION=($position+1);";
   $sql3 = "update PAGES set POSITION=($position+1) where ID='$id';"; 
   $result = mysql_query( $sql1) or die(mysql_error());  
   $result = mysql_query( $sql2) or die(mysql_error());  
   $result = mysql_query( $sql3) or die(mysql_error());  
  }
 }
 
 function sletMenuPunkt($id)
 {
  global $dbname; 
  $sql = "select PARENT_ID, MENU_ID, POSITION from MENUENTRIES where ID='$id'";
  $result = mysql_query( $sql) or die(mysql_error());  
  $row = mysql_fetch_array($result);
  $sql = "update MENUENTRIES set DELETED='1' where ID='$id'";
  $result = mysql_query( $sql) or die(mysql_error());  
  $sql = "update MENUENTRIES set POSITION=(POSITION-1) where PARENT_ID=$row[PARENT_ID] and MENU_ID=$row[MENU_ID] and POSITION>$row[POSITION]";
  $result = mysql_query( $sql) or die(mysql_error());  
 }

 
  function menuPath($to_id, $addmenuname)
  {
   global $dbname, $sti, $husk;
   $sql 	= "select BREADCRUMB, PARENT_ID, MENU_ID from PAGES where ID='$to_id'";
   $result 	= mysql_query( $sql) or die(mysql_error());      
   while ($row = mysql_fetch_array($result))
   {
	$husk = $row[2];
    $sti .= "|" . $row[0]; 
    menuPath($row[1], 0);
   }
   $sti = implode(" > ", array_reverse(explode("|", $sti)));
   $sti =  substr($sti,0, strlen($sti)-1);
   if ($addmenuname == true) {
    $sti = returnPageTitle($husk) . " > " . $sti;
    $link = "<a href='#' onclick='if(confirm(\"Vil du gå til menuoversigten? Dine rettelser går tabt, hvis du ikke har gemt dem.\")) location=\"index.php?content_identifier=menus&menuid=$husk&amp;highlight=$to_id\"; else return false;'>$sti</a>";
    return $link;
   }
   else return $sti;
  }	 
 
 function relatedList($pageid, $type, $tabel, $menuid) {
  global $dbname;
  $sql = "select ID, REL_ID from RELATED_CONTENT where SRC_ID='$pageid' and REL_TABEL='$type'";
  $result = mysql_query( $sql) or die(mysql_error());
  while ($row = mysql_fetch_array($result)) {
   if ($type=="PAGES") $html.= "&raquo;&nbsp;" . returnPageTitle($row[REL_ID]) . "&nbsp;<a href='#' onclick='removeRel($row[ID], $pageid, \"$tabel\", $menuid); return false;'>[Fjern]</a><br/>";
   if ($type=="NEWS") $html.= "&raquo;&nbsp;" . returnNewsTitle($row[REL_ID]) . "&nbsp;<a href='#' onclick='removeRel($row[ID], $pageid, \"$tabel\", $menuid); return false;'>[Fjern]</a><br/>";
   if ($type=="EVENTS") $html.= "&raquo;&nbsp;" . returnEventTitle($row[REL_ID]) . "&nbsp;<a href='#' onclick='removeRel($row[ID], $pageid, \"$tabel\", $menuid); return false;'>[Fjern]</a><br/>";
  }
  if (mysql_num_rows($result)==0) $html = "Endnu ingen relationer af denne type.";
  return $html;
 }

 function relatedListForBoxes($boxid) {
  global $dbname;
  $sql = "select ID, REL_ID from RELATED_CONTENT where CUSTOMBOX_ID='$boxid'";
  $result = mysql_query( $sql) or die(mysql_error());
  while ($row = mysql_fetch_array($result)) {
   $html.= "&raquo;&nbsp;" . returnPageTitle($row[REL_ID]) . "&nbsp;<a href='#' onclick='removeRelForBoxes($row[ID]); return false;'>[Fjern]</a><br/>";
  }
  if (mysql_num_rows($result)==0) $html = "Endnu ingen relationer af denne type.";
  return $html;
 }
 
 function pageSelector($selected)
 {
  global $dbname;
  $sql = "select P.ID, P.TITLE, M.SITE_ID, M.MENU_TITLE from PAGES P, MENUS M where P.UNFINISHED='0' and P.DELETED='0' and M.SITE_ID in (0,'$_SESSION[SELECTED_SITE]') order by M.SITE_ID asc, M.MENU_TITLE asc, P.TITLE asc, P.ID asc";
  $result = mysql_query( $sql) or die(mysql_error());
  while ($row = mysql_fetch_array($result))
  { 
   if ($row[MENU_TITLE] != $site_nu) {
    $html .= "<option value='-99'>===".$row[MENU_TITLE]."===</option>";
   }
   $selected_txt = "";
   if ($selected == $row["ID"]) $selected_txt = "selected";
   $html .= "<option $selected_txt value='" . $row["ID"] . "'>" . $row["TITLE"] . " (id: " . $row["ID"] . ")</option>";
   $site_nu = $row[MENU_TITLE];
  }
  return $html;
 }
 
function buildPagesDropdown($indent, $parent_id, $count) {
	global $loop, $ddhtml, $s1, $m1;
	$sql = "select 
				P.ID, 
				P.PARENT_ID, 
				P.BREADCRUMB, 
				P.LANGUAGE, 
				P.MENU_ID,
				M.MENU_TITLE
			from 
				PAGES P,
				MENUS M
			where 
				P.MENU_ID = M.MENU_ID and
				P.PARENT_ID='$parent_id' and 
				P.UNFINISHED='0' and 
				P.DELETED='0' and 
				M.SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
			order by 
				M.MENU_TITLE asc, 
				P.POSITION asc";
	$result = mysql_query( $sql) or die(mysql_error());
	if ($loop=="" || $loop==0) {
		$ddhtml .= "<option value='-99' selected style=\"background-color:#ddd; color:#f00\">Vælg side...</option>";
	}
	while ($row = mysql_fetch_array($result)) {
		if ($row[MENU_TITLE]!=$m1) {
			$ddhtml .= "<option value=\"-1\" style=\"background-color:#ddd; color:#f00\">";
			$ddhtml .= "$row[MENU_TITLE]";
			$ddhtml .= returnSiteName($row[SITE_ID]) . " (" . languageName($row[LANGUAGE]) .  ")</option>";
		}
		$loop += 1;
		$ddhtml .= "<option " . ($row[PARENT_ID]==0 ? "style=\"background-color:#666; color:#fff\"" : "") . " value=\"$row[ID]\">";
		if ($indent=="") {
			$show_indent = $indent;
		} else {
			$show_indent = "$indent>";
		}
		$ddhtml .= "$show_indent ". $row[BREADCRUMB]."</option>"; //  (Side-ID: $row[ID])
		$m1 = $row[MENU_TITLE];
		buildPagesDropdown($indent . "-", $row["ID"], $count);
	}
	return $ddhtml;
}  
  
 
function newsSelector($selected){
	$sql = "select 
	  			N.ID, 
  				N.HEADING, 
  				N.NEWS_DATE,
  				NF.NAME
	  		from 
	  			NEWS N,
	  			NEWSFEEDS NF
	  		where 
				N.NEWSFEED_ID = NF.ID and
		  		N.UNFINISHED='0' and 
		  		N.DELETED='0' and 
		  		NF.SITE_ID in (0,'$_SESSION[SELECTED_SITE]') 
		  	order by 
				NF.NAME asc, 
				N.NEWS_DATE desc,
		  		N.TITLE asc, 
		  		N.ID asc";
	$result = mysql_query( $sql) or die(mysql_error());
	$html .= "<option value='-99' selected style=\"background-color:#ddd; color:#f00\">Vælg nyhed...</option>";
	while ($row = mysql_fetch_array($result)) { 
		if ($row[NAME] != $feed_nu) {
			$html .= "<option value='-99' style=\"background-color:#ddd; color:#f00\">".$row[NAME]."</option>";
		}
		$selected_txt = "";
		if ($selected == $row["ID"]) $selected_txt = "selected";
		$html .= "<option $selected_txt value='" . $row["ID"] . "'>";
		$html .= "$row[NEWS_DATE]: ";
		$html .= $row["HEADING"] . "</option>";
		$feed_nu = $row[NAME];
	}
	return $html;
}

function eventsSelector($selected) {
	$sql = "select 
  				E.ID, 
  				E.HEADING, 
  				E.STARTDATE,
  				C.NAME 
  			from 
  				EVENTS E,
  				CALENDARS C
  			where 
  				E.CALENDAR_ID = C.ID and
  				E.UNFINISHED='0' and 
  				E.DELETED='0' and 
  				C.SITE_ID in (0,'$_SESSION[SELECTED_SITE]') 
  			order by 
				C.NAME asc,
				E.STARTDATE desc, 
  				E.TITLE asc, 
  				E.ID asc";
	$result = mysql_query( $sql) or die(mysql_error());
	$html .= "<option value='-99' selected style=\"background-color:#ddd; color:#f00\">Vælg arrangement...</option>";
	while ($row = mysql_fetch_array($result)) { 
		if ($row[NAME] != $calendar_nu) {
			$html .= "<option value='-99' style=\"background-color:#ddd; color:#f00\">".$row[NAME]."</option>";
		}
		$selected_txt = "";
		if ($selected == $row["ID"]) $selected_txt = "selected";
		$html .= "<option $selected_txt value='" . $row["ID"] . "'>";
		$html .= "$row[STARTDATE]: ";
  		$html .= $row["HEADING"] . "</option>";
		$calendar_nu = $row[NAME];
	}
	return $html;
}   

function fileSelector($selected) {
	// Used in FCK-editor customLink plug-in
	$sql = "select 
				FILEARCHIVE_FOLDERS.ID as FOLDER_ID, 
				FILEARCHIVE_FOLDERS.TITLE as FOLDER_TITLE, 
				FILEARCHIVE_FOLDERS.FOLDERNAME, 
				FILEARCHIVE_FILES.FILENAME, 
				FILEARCHIVE_FILES.ORIGINAL_FILENAME, 
				FILEARCHIVE_FILES.TITLE, 
				FILEARCHIVE_FILES.EXTENSION, 
				FILEARCHIVE_FILES.ID as FILE_ID
			from 
				FILEARCHIVE_FILES, 
				FILEARCHIVE_FOLDERS 
			where 
				FILEARCHIVE_FILES.FOLDER_ID = FILEARCHIVE_FOLDERS.ID and
				FILEARCHIVE_FOLDERS.SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
			order by 
				FILEARCHIVE_FOLDERS.THREAD_ID asc, 
				FILEARCHIVE_FOLDERS.TITLE asc, 
				FILEARCHIVE_FILES.TITLE asc";	
	$result = mysql_query( $sql) or die(mysql_error());
	$html .= "<option value='-99' selected style=\"background-color:#ddd; color:#f00\">Vælg fil...</option>";
	while ($row = mysql_fetch_array($result)) { 
		if ($row[FOLDER_TITLE] != $nu_titel) {
			$html .= "<option value='-99' style=\"background-color:#ddd; color:#f00\">";
			$html .= returnFolderName_recursive($row[FOLDER_ID], 2, "FILEARCHIVE_FOLDERS");
			$html .= "</option>";

		}
		if ($row[TITLE] == "") {
			$displayname = $row[ORIGINAL_FILENAME];
		} else {	
			$displayname = $row[TITLE];
		}
		$displayname = "$displayname (".$row[EXTENSION].")";	
		$filepath = "includes/download.php?id=".$row["FILE_ID"];
		$html .= "<option value='" . $filepath . "'>" . $displayname . "</option>";
		$nu_titel = $row[FOLDER_TITLE];
	}
	return $html;
}   
 
function listGroupRights($group_id) {
	// Get rights for current group
	$thisgroup_permissions = array();
	$sql = "select
				PERMISSIONS_ID
			from
				GROUPS_PERMISSIONS
			where
				GROUPS_ID = '$group_id'
			";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result)){
		$thisgroup_permissions[] = $row[PERMISSIONS_ID];
	}
	// Output checkboxes for all permissiongroups and their permissions
	// First: Get all permission groups
	$sql = "select ID, TITLE from PERMISSIONGROUPS order by TITLE ASC";
	$result = mysql_query( $sql);
	while ($row = mysql_fetch_array($result)){
		$html .= "<h2>".$row[TITLE]."</h2>";
		// Get all permissions in group
		$psql = "select ID, NAME, DESCRIPTION from PERMISSIONS where PERMISSIONGROUPS_ID = '$row[ID]' and IS_DATAPERMISSION = 0";
		$presult = mysql_query($psql);
		while ($prow = mysql_fetch_array($presult)){
			// Check if current group has this right, and check box accordingly
			if (in_array($prow[ID], $thisgroup_permissions)) {
				$c = "checked";
			} else {
				$c = "";
			}
			$html.= "<input type='checkbox' name='PERMISSION_$prow[ID]' $c />&nbsp;$prow[DESCRIPTION]<br/>";
		}
	}
	return $html;
}
 

 function newparent($pageid, $newparentid){
  global $dbname;
  $sql = "select POSITION, PARENT_ID from PAGES where ID='$pageid'";  	// Find sidens oprindelige ID og PARENT
  $result = mysql_query( $sql) or die($sql.mysql_error());
  $row = mysql_fetch_row($result);
  $sql = "update PAGES set POSITION=(POSITION-1) where PARENT_ID='$row[1]' and POSITION>$row[0]";	// Opdater sidens "gamle familie"'s positioner
  mysql_query( $sql) or die($sql.mysql_error());
  $sql = "update PAGES set PARENT_ID='$newparentid', POSITION='0' where ID='$pageid'";	// Sæt ny PARENT
  mysql_query( $sql) or die($sql.mysql_error());
  $sql = "select POSITION from PAGES where PARENT_ID='$newparentid' order by POSITION desc limit 1";  // Sæt ny POSITION i ny familie
  $result = mysql_query( $sql) or die($sql.mysql_error());
  $row = mysql_fetch_row($result);
  $sql = "select THREAD_ID from PAGES where ID='$newparentid'";  // Sæt ny POSITION i ny familie
  $result_thread = mysql_query( $sql) or die($sql.mysql_error());
  $row_thread = mysql_fetch_row($result_thread);
  $sql = "update PAGES set POSITION='" . ($row[0] + 1) . "', THREAD_ID='$row_thread[0]' where ID='$pageid'";
  mysql_query( $sql) or die($sql.mysql_error()); 
 }
   
function db_numrows() {
		// Call this function immedietely following a SELECT query to 
		// return number of found rows.
		$result = mysql_query("SELECT FOUND_ROWS()");
		$total = mysql_fetch_row($result);
		return $total[0];
}

function db_hasrows() {
		// Call this function immedietely following a SELECT query to 
		// determine if the SELECT returned any rows.
		// Returns true if number of rows > 0, otherwise returns false.
		$result = mysql_query("SELECT FOUND_ROWS()");
		$total = mysql_fetch_row($result);
		if ($total[0] > 0) {
			return true;
		} else {
			return false;
		}
}

function usermessage($class, $message) {
	# Outputs a <div> containing a message to the user
	# The <div> is styled with class="$class"
	# Current classes in use are:
	# "usermessage_ok" = all systems go
	# "usermessage_error" = alert to the user
	$message = urldecode($message);
	$message = stripslashes($message);
	$message = stripslashes($message);
	echo "<div class='$class'>".$message."</div>";
}


function str_makerand ($minlength, $maxlength, $useupper=false, $usespecial=false, $usenumbers=false)
{ 
/*  
Author: Peter Mugane Kionga-Kamau 
http://www.pmkmedia.com 

Description: string str_makerand(int $minlength, int $maxlength, bool $useupper, bool $usespecial, bool $usenumbers) 
returns a randomly generated string of length between $minlength and $maxlength inclusively.

Notes:  
- If $useupper is true uppercase characters will be used; if false they will be excluded.
- If $usespecial is true special characters will be used; if false they will be excluded.
- If $usenumbers is true numerical characters will be used; if false they will be excluded.
- If $minlength is equal to $maxlength a string of length $maxlength will be returned.
- Not all special characters are included since they could cause parse errors with queries. 

Modify at will. 
*/ 
    $charset = "abcdefghijklmnopqrstuvwxyz"; 
    if ($useupper)   $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ"; 
    if ($usenumbers) $charset .= "0123456789"; 
    if ($usespecial) $charset .= "~@#$%^*()_+-={}|][";   // Note: using all special characters this reads: "~!@#$%^&*()_+`-={}|\\]?[\":;'><,./"; 
    if ($minlength > $maxlength) {
    	$length = mt_rand ($maxlength, $minlength);
    }
    if ($minlength < $maxlength) {
    	$length = mt_rand ($minlength, $maxlength);
    }
    if ($minlength == $maxlength) {
    	$length = $maxlength;
    }
    for ($i=0; $i<$length; $i++) $key .= $charset[(mt_rand(0,(strlen($charset)-1)))]; 
    return $key; 
}


function recursive_remove_directory($directory, $empty=FALSE) {
// ------------ lixlpixel recursive PHP functions -------------
// recursive_remove_directory( directory to delete, empty )
// expects path to directory and optional TRUE / FALSE to empty
// of course PHP has to have the rights to delete the directory
// you specify and all files and folders inside the directory
// ------------------------------------------------------------
 
// to use this function to totally remove a directory, write:
// recursive_remove_directory('path/to/directory/to/delete');
 
// to use this function to empty a directory, write:
// recursive_remove_directory('path/to/full_directory',TRUE);

     // if the path has a slash at the end we remove it here
     if(substr($directory,-1) == '/')
     {
         $directory = substr($directory,0,-1);
     }
  
     // if the path is not valid or is not a directory ...
     if(!file_exists($directory) || !is_dir($directory))
     {
         // ... we return false and exit the function
        return FALSE;
  
     // ... if the path is not readable
     }elseif(!is_readable($directory))
     {
         // ... we return false and exit the function
         return FALSE;
  
     // ... else if the path is readable
     }else{
  
         // we open the directory
         $handle = opendir($directory);
  
         // and scan through the items inside
         while (FALSE !== ($item = readdir($handle)))
         {
             // if the filepointer is not the current directory
             // or the parent directory
             if($item != '.' && $item != '..')
             {
                 // we build the new path to delete
                 $path = $directory.'/'.$item;
  
                 // if the new path is a directory
                 if(is_dir($path)) 
                 {
                     // we call this function with the new path
                     recursive_remove_directory($path);
 
                // if the new path is a file
                 }else{
                     // we remove the file
                    unlink($path);
                }
            }
        }
        // close the directory
        closedir($handle);
 
        // if the option to empty is not set to true
        if($empty == FALSE)
        {
            // try to delete the now empty directory
            if(!rmdir($directory))
            {
                // return false if not possible
                return FALSE;
            }
        }
        // return success
        return TRUE;
    }
}
// ------------------------------------------------------------

function sendNewUserMail($email, $firstname, $lastname, $sitename, $username, $password, $fromname, $frommail) {
	global $defaultLanguage;
	$translateLang = $defaultLanguage;
	if (cmsTranslateBackend($translateLang, "newusermail_signatureName") == ""){
		$fromname = str_replace("http://","",$fromname);
		$signaturename = $fromname;
	} else {
		$fromname = cmsTranslateBackend($translateLang, "newusermail_fromname");
		$signaturename = cmsTranslateBackend($translateLang, "newusermail_signatureName");
	}
    mail($email, 
	   		"Login",
	   		cmsTranslateBackend($translateLang, "newusermail_dear")." $firstname $lastname,<br /><br />
	   			".cmsTranslateBackend($translateLang, "newusermail_youAreRegistered")." $sitename.
	   			<br /><br /><strong>".cmsTranslateBackend($translateLang, "newusermail_username").":</strong> $username<br /><strong>".cmsTranslateBackend($translateLang, "newusermail_password").":</strong> $password<br /><br />
	   			".cmsTranslateBackend($translateLang, "newusermail_bestRegards")."<br />$signaturename", 
	   		"From: ".$fromname." <$frommail>\nContent-Type: text/html; charset=UTF-8"
   	);
}

function createCheckbox($label, $selectname, $value = "Y", $checked = "N", $onClickFunction = "", $disabled="") {
	# Will return checkbox form element
	# Label is the text appearing after the checkbox
	# Use $selectname to set name/id of form element
	# Call with $value to set the value posted if the box is checked
	# Call with $checked = "Y" to preselect checkbox
	# Call with $disabled = "Y" (optional) to disable checkbox
	$cb = '<input type="checkbox" id="'.$selectname.'" name="'.$selectname.'" value="'.$value.'"';
	if ($onClickFunction != "") {
		$cb .= ' onclick="'.$onClickFunction.'"';
	}
	if ($checked == "Y") {
		$cb .= ' checked="checked"';
	}
	if ($disabled != "") {
		$cb .= ' disabled="disabled"';
	}
	$cb .= "/>&nbsp;".$label;
	return $cb;
}

function createSelectYesNo($selectname, $value = 0, $class="inputfelt_kort") {
	# Will return select form element used for choosing yes / no 
	# Use $selectname to set name/id of form element
	# Call with $value = 0-1 preselect state. Use blank argument for 0 (no)
	return '<select id="'.$selectname.'" name="'.$selectname.'" class="'.$class.'" size="1">
	<option value="1"'.(($value == 1) ? ' selected="selected"' : '').'>Ja</option>
	<option value="0"'.(($value == 0) ? ' selected="selected"' : '').'>Nej</option>
	</select>';
}

function niceTableListing($sql_result, $db_columns, $display_columns, $functions_per_column, $additional_columns = array(), $no_results_html = ""){
	/* Example use:
	$html .= niceTableListing(
		mysql_query($sql), 
		array(
			"HEY" => "", 
			"ID" => "", 
			"NO_RECIPIENTS" => "", 
			"SENT_TIME" => ""
			), 
		array(
			"HEY" => "Overskrift", 
			"NO_RECIPIENTS" => "Antal modtagere", 
			"SENT_TIME" => "Afsendt"
			), 
		array(
			"NO_RECIPIENTS" => "return 'Hej __ID__';",
			"HEY" => "return '<input type=\'checkbox\' value=\'__HEY____ID__\' />';"
		), 
		array(
			"Funktioner" => 
			"
				<a href='index.php?content_identifier=easyfood_concepts&dothis=rediger&concept_id=__NEWSLETTER_ID__'>Rediger</a>&nbsp;
				<a href='index.php?content_identifier=easyfood_concepts&dothis=rediger&concept_id=__NEWSLETTER_ID__'>Slet</a>
			"
		),
		"Ingen koncepter er blevet oprettet endnu."
	);
	*/
	if (mysql_num_rows($sql_result)){
		$row = mysql_fetch_array($sql_result);
		$html .= "<table border='1' class='oversigt'>";
		$html .= "<tr>";
		foreach ($display_columns as $keyname=>$title){
			$html .= "<th>".$title."</th>";
		}
		foreach ($additional_columns as $title=>$content){
			$html .= "<th>".$title."</th>";
		}
		mysql_data_seek($sql_result, 0);
		while($row = mysql_fetch_array($sql_result)){
			$html .= "<tr>";
			foreach ($row as $key=>$value){
				if (is_string($key) && in_array($key, array_keys($display_columns))){
					if ($function = $functions_per_column[$key]){
						foreach ($db_columns as $colkey => $colname){
							if (strstr($function, $needle = "__".$colkey."__")){
								$function = str_replace($needle, $row[$colkey], $function);
							}
							$output_value = eval($function);
						}
					} else {
						$output_value = $value;
					}
					$html .= "<td>".$output_value."</td>";
				}
			}
			foreach ($additional_columns as $add_html){
				foreach ($db_columns as $colkey=>$colname){
					if (strstr($add_html, $needle = "__".$colkey."__")){
						$add_html = str_replace($needle, $row[$colkey], $add_html);
					}
				}
				$html .= "<td>".$add_html."</td>";
			}
			$html .= "</tr>";
		}	
		$html .= "</table>";
	} else {
		$html .= $no_results_html;
	}
	return $html;
}

function cmsTranslateBackend($language, $key, $optionalKey=false) {	
	// Function to translate backend CMS values
	// $language is a reference to the LANGUAGES table
	// If $language is an integer it refers to the ID column
	// If $language is a string it refers to the SHORTNAME column
	// $key is the cmsLang term to translate
	// $optionalKey (optional) is for use in special cases where cmsLang has two dimensions
	global $cmsLang;	

	if (is_int($language)) {
		$language = returnFieldValue("LANGUAGES", "SHORTNAME", "ID", $language);
	}

	if (!$optionalKey) {
		return $cmsLang[$language][$key];
	} else {
		return $cmsLang[$language][$key][$optionalKey];
	}
}

/*
function datapermission_set($str_permissionname, $str_datatablename, $int_dataid, $str_label="") {
	// Function to build php/html part of function to set datapermissions for permission named $str_permissionname
	// $str_label is optional parameter to describe the permission, default is permission description from database
	$sql = "select NAME, DESCRIPTION from PERMISSIONS where NAME = '$str_permissionname' and IS_DATAPERMISSION = 1";
	if (!$res = mysql_query($sql)) {
		return false;
	}
	if (mysql_num_rows($res) != 1) {
		return false;
	}
	if (!$p_row = mysql_fetch_assoc($res)) {
		return false;
	}
	// Permission data successfully retrieved, now build interface
	$html .= "<h2>
				<span style='float:left;'>";
	if ($str_label=="") {
		$html .= $p_row[DESCRIPTION];
	} else {
		$html .= $str_label;
	}
	$html .= "</span>
				<span id='ajaxloader_datapermission_$str_permissionname' style='display:none'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></span>
			  </h2>
			  <p class='feltkommentar'>Tryk på knappen \"Vis rettigheder\" for at se hvem der er tildelt denne rettighed. Tryk på knappen \"Tildel rettighed\" for at vælge, hvilke brugergrupper eller brugere der skal have denne rettighed.</p>";
	$html .= "<div id='datapermission_usergroups_$str_permissionname' style='display:none'></div>";
	$html .= "<div id='datapermission_users_$str_permissionname' style='display:none'></div>";
	$html .= "<div class='knapbar'>
					<input type='button' id='datapermission__showgrantsbutton_$str_permissionname' value='Vis rettigheder' onclick='datapermission_showgrants(\"$str_permissionname\", \"$str_datatablename\", \"$int_dataid\")' />
					<input type='button' id='datapermission__showusergroupsbutton_$str_permissionname' value='Tildel rettigheder' onclick='datapermission_showusergroups(\"$str_permissionname\", \"$str_datatablename\", \"$int_dataid\")' />
				</div>";
	$html .= "<div id='datapermission_grantcandidates_$str_permissionname' style='display:none'></div>";
	return $html;
}
*/

function datapermission_set($str_permissionname, $str_datatablename, $int_dataid, $str_label="") {
	// Function to build php/html part of function to set datapermissions for permission named $str_permissionname
	// $str_label is optional parameter to describe the permission, default is permission description from database
	$sql = "select NAME, DESCRIPTION from PERMISSIONS where NAME = '$str_permissionname' and IS_DATAPERMISSION = 1";
	if (!$res = mysql_query($sql)) {
		return false;
	}
	if (mysql_num_rows($res) != 1) {
		return false;
	}
	if (!$p_row = mysql_fetch_assoc($res)) {
		return false;
	}
	// Permission data successfully retrieved, now build interface
	$html .= "<h2>
				<span style='float:left;'>";
	if ($str_label=="") {
		$html .= $p_row[DESCRIPTION];
	} else {
		$html .= $str_label;
	}
	$html .= "</span>
				<span id='ajaxloader_datapermission_$str_permissionname"."_$int_dataid' style='display:none'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></span>
			  </h2>
			  <p class='feltkommentar'>Tryk på knappen \"Vis rettigheder\" for at se hvem der er tildelt denne rettighed. Tryk på knappen \"Tildel rettighed\" for at vælge, hvilke brugergrupper eller brugere der skal have denne rettighed.</p>";
	$html .= "<div id='datapermission_usergroups_".$str_permissionname."_".$int_dataid."' style='display:none'></div>";
	$html .= "<div id='datapermission_users_".$str_permissionname."_".$int_dataid."' style='display:none'></div>";
	$html .= "<div class='knapbar'>
					<input type='button' id='datapermission__showgrantsbutton_$str_permissionname"."_".$int_dataid."' value='Vis rettigheder' onclick='datapermission_showgrants(\"$str_permissionname\", \"$str_datatablename\", \"$int_dataid\")' />
					<input type='button' id='datapermission__showusergroupsbutton_$str_permissionname"."_$int_dataid' value='Tildel rettigheder' onclick='datapermission_showusergroups(\"$str_permissionname\", \"$str_datatablename\", \"$int_dataid\")' />
				</div>";
	$html .= "<div id='datapermission_grantcandidates_$str_permissionname"."_$int_dataid' style='display:none'></div>";
	return $html;
}

function return_customfields_input($str_tablename, $int_templateid) {
	global $fckEditorPath;
	switch ($_GET[content_identifier]) {
		case "pages":
			$request_id_getvar = "id";
			break;
		default:
			return "<strong>Warning: </strong>Function return_customfields_input not implemented for this content_identifier.";
	}
	$html .= "<input type='hidden' name='REQUEST_ID' id='REQUEST_ID' value='$_GET[$request_id_getvar]' />";

	$sql = "select
				C.ID as CUSTOMFIELD_ID, C.DESCRIPTION, C.TYPE_ID, CT.TYPENAME
			from
				CUSTOMFIELDS C, CUSTOMFIELDTYPES CT
			where
				C.TYPE_ID = CT.ID and
				C.TEMPLATE_ID in (0,'$int_templateid') and
				C.TABLENAME = '$str_tablename' and
				C.DELETED = '0'
			order by
				C.POSITION asc";
				
	$res = mysql_query($sql);
	if (mysql_num_rows($res) == 0) {
		return "";
	} else {
		while ($row = mysql_fetch_assoc($res)) {
			$html .= "<h2>$row[DESCRIPTION]</h2>";
			// Get attributes for each custom field
			$a_sql = "select 
						CA.*
					from
						CUSTOMFIELDTYPES CT, CUSTOMFIELDATTRIBUTES CA
					where
						CT.ID = CA.CUSTOMFIELDTYPE_ID and
						CT.ID = '$row[TYPE_ID]'
					order by
						CA.POSITION asc";
			$a_res = mysql_query($a_sql);
			if (mysql_num_rows($a_res) > 1) {
				$multiple_attributes = true;
			} else {
				$multiple_attributes = false;
			}
			if ($multiple_attributes) {
				$html .= "<table class='oversigt'>";
			}	
			while ($a_row = mysql_fetch_assoc($a_res)) {
				if ($multiple_attributes) {
					$html .= "<tr><td>$a_row[ATTRIBUTENAME]</td><td>";
				}	
				$attribute_id = "CUSTOM___$row[CUSTOMFIELD_ID]___$a_row[ID]";
				$attribute_value = return_customfieldattribute_value($row[CUSTOMFIELD_ID], $a_row[ID], $_GET[$request_id_getvar]);
				if ($attribute_value == "") {
					$attribute_value = $a_row[DEFAULTVALUE];
				}
				// Implement all standard attribute-types here (see below switch for CUSTOM attribute-types)
				switch ($a_row[ATTRIBUTETYPE]) {
					case "TEXT":
						$html .= "<input type='text' id='$attribute_id' name='$attribute_id' class='inputfelt' value='$attribute_value' />";
						break;
					case "TEXTEDITOR":
						// Preferences: 			[ToolbarSet]|||[Height]
						// Preferences, example:	CMS_Default|||400
						if ($a_row[PREFERENCES] == "") {
							$texteditor_toolbarset 	= "CMS_Default";
							$texteditor_height 		= 200;
						} else {
							$arr_prefs = explode("|||", $a_row[PREFERENCES]);
							$texteditor_toolbarset 	= $arr_prefs[0];
							$texteditor_height 		= $arr_prefs[1];
						}
						$oFCKeditor = new FCKeditor($attribute_id) ;
						$oFCKeditor->BasePath = $fckEditorPath . "/";
						$oFCKeditor->ToolbarSet	= $texteditor_toolbarset;
						$oFCKeditor->Height	= $texteditor_height;
						$oFCKeditor->Value	= $attribute_value;
						$oFCKeditor->Config['CustomConfigurationsPath']	= $fckEditorCustomConfigPath . "/cms_fckconfig.js";
						$html .= $oFCKeditor->CreateHtml() ;
						break;
					case "IMAGESELECTOR":
						// Get selected image
						if ($attribute_value == 0) {
							$attribute_value = "";
							$image_url = "";
							$thumburl = "";
						} else {
							$image_url = returnImageUrl($attribute_value);
							$thumburl = explode("/",$image_url);
							$lastpart = array_pop($thumburl);
							$thumburl[] = "thumbs";
							$thumburl[] = $lastpart;
							$thumburl = implode("/", $thumburl); 
						}

						$html .= "<input type='button' class='lilleknap' name='customfield_$row[CUSTOMFIELD_ID]_attribute_$a_row[ID]_selectImageButton' id='customfield_$row[CUSTOMFIELD_ID]_attribute_$a_row[ID]_selectImageButton' value='Vælg billede' onclick='customfield_selectImage($row[CUSTOMFIELD_ID],$a_row[ID]);' />";
						$html .= "<input type='button' class='lilleknap' name='customfield_$row[CUSTOMFIELD_ID]_attribute_$a_row[ID]_noImageButton' id='customfield_$row[CUSTOMFIELD_ID]_attribute_$a_row[ID]_noImageButton' value='Fjern billede' onclick='customfield_noImage($row[CUSTOMFIELD_ID],$a_row[ID]);' ";
						if ($image_url == "") {
							$html .= " disabled";
						}
						$html .= "/>";

						$html .= "<input type='hidden' name='$attribute_id' id='$attribute_id' value='$attribute_value' />";
						 
						$html .= "&nbsp;&nbsp;\n<img id='customfield_$row[CUSTOMFIELD_ID]_attribute_$a_row[ID]_imgthumb' src='$thumburl' border='1'";
						if ($image_url == "") {
							$html .= " style='display:none;'";
						}
						$html .= "/>";
						// Div for selecting images
						$html .= "<div id='customfield_$row[CUSTOMFIELD_ID]_attribute_$a_row[ID]_selectImageDiv' style='
							display: none;
							width: 725px; 
							height: auto; 
							border: 1px solid #999; 
							background-color: #FFF;'></div>";
						break;
					case "FILESELECTOR":
						$arr_prefs = explode("|||", $a_row[PREFERENCES]);
						$folderid = $arr_prefs[0];
						$sql = "
							select 
								FF.ID as FILE_ID, FF.FILENAME, FF.ORIGINAL_FILENAME, FF.TITLE 
							from
								FILEARCHIVE_FILES FF, FILEARCHIVE_FOLDERS FO
							where
								FF.FOLDER_ID=FO.ID and FO.ID='$folderid' 
						";
						$f_res = mysql_query($sql);
						while ($f_row = mysql_fetch_assoc($f_res)){
							$options[] = $f_row[TITLE]." (".$f_row[ORIGINAL_FILENAME].")"."___".$f_row[FILE_ID];
						}
						$arr_options = $options;
						$html .= "<select id='$attribute_id' name='$attribute_id'>";
						foreach ($arr_options as $option) {
							$arr_optionvalues = explode("___", $option);
							$html .= "<option value='$arr_optionvalues[1]'";
							if ($arr_optionvalues[1] == $attribute_value) {
								$html .= " selected";
							}
							$html .= ">$arr_optionvalues[0]</option>";
						}
						$html .= "</select>";

					break;
					case "DROPDOWN":
						// Options: 			[option1]___[value1]|||[option2]___[value2]
						// Options, example:	Vælg___0|||Fisk___1|||Midtimellem___2|||Fugl___3
						$arr_options = explode("|||", $a_row[OPTIONS]);
						$html .= "<select id='$attribute_id' name='$attribute_id'>";
						foreach ($arr_options as $option) {
							$arr_optionvalues = explode("___", $option);
							$html .= "<option value='$arr_optionvalues[1]'";
							if ($arr_optionvalues[1] == $attribute_value) {
								$html .= " selected";
							}
							$html .= ">$arr_optionvalues[0]</option>";
						}
						$html .= "</select>";
						break;
				}
				// Include custom customfield attributes, if file exists
				// must contain a switch similar to the one above
				$ca_file = $_SERVER[DOCUMENT_ROOT] . "/includes/custom_customfieldattributes.inc.php";
				if(file_exists($ca_file)){
					include_once($ca_file);
					$html .= return_custom_customfields_input($a_row, $attribute_id, $attribute_value);
				}

				if ($multiple_attributes) {
					$html .= "</td></tr>";
				}	
			}
			if ($multiple_attributes) {
				$html .= "</table>";
			}	
		}
		return $html;
	}
}

function return_customfieldattribute_value($customfield_id, $attribute_id, $request_id) {
	$sql = "select
				CD.VALUE
			from
				CUSTOMFIELDDATA CD
			where
				CD.CUSTOMFIELD_ID = '$customfield_id' and
				CD.ATTRIBUTE_ID = '$attribute_id' and
				CD.REQUEST_ID = '$request_id'
			limit 1";
	$res = mysql_query($sql);
	if (mysql_num_rows($res) > 0) {
		return mysql_result($res,0);
	} else {
		return "";
	}
}

function save_customfields($arr_post) {
	/*
		FUNCTION TO SAVE CUSTOM FIELD DATA VALUES
		Must parse $arr_post that looks like this:
			[CUSTOM___3___2] => 1
			[CUSTOM___1___1] => Indtast custom tekst her!
			[CUSTOM___2___3] => http://www.instans.dk/
			[CUSTOM___2___4] => linktekst
			[CUSTOM___2___5] => blank
			[CUSTOM___4___2] => 109
			that is CUSTOM___[CUSTOMFIELD_ID]___[ATTRIBUTE_ID] => VALUE
*/
	foreach ($arr_post as $key => $value) {
		if (strstr($key, "CUSTOM___")) {
			$arr_key = explode("___", $key);
			$sql = "select 
						ID 
					from 
						CUSTOMFIELDDATA CD 
					where 
						CD.CUSTOMFIELD_ID = '$arr_key[1]' and 
						CD.ATTRIBUTE_ID = '$arr_key[2]' and 
						CD.REQUEST_ID = '$arr_post[REQUEST_ID]'";
			$res = mysql_query($sql);
			if (mysql_num_rows($res) > 0) {
				$data_id = mysql_result($res,0);
				// Exists, update
				$sql = "update
							CUSTOMFIELDDATA
						set
							VALUE = '$value'
						where
							ID = '$data_id'";
				mysql_query($sql);
			} else {
				// Not exists, insert
				$sql = "insert into
							CUSTOMFIELDDATA (
								`ID`, 
								`CUSTOMFIELD_ID`, 
								`ATTRIBUTE_ID`, 
								`REQUEST_ID`, 
								`VALUE`)
							values (
								'', 
								'$arr_key[1]', 
								'$arr_key[2]', 
								'$arr_post[REQUEST_ID]', 
								'$value')";
				mysql_query($sql);
			}
		}
	}
}

function hasCustomfields($str_tablename, $int_templateid) {
	$sql = "select
				count(*)
			from
				CUSTOMFIELDS C
			where
				C.TEMPLATE_ID in (0,'$int_templateid') and
				C.TABLENAME = '$str_tablename' and
				C.DELETED = '0'";
	$res = mysql_query($sql);
	if (mysql_result($res,0)>0) {
		return true;
	} else {
		return false;
	}
}

/// TAGS ////////////////////////////////////////////////

	function build_tag_form($request_id, $tablename, $site_id){
		$sql = "
			select 
				T.TAGNAME
			from 
				TAGS T, TAG_REFERENCES TR
			where 
				TR.REQUEST_ID='$request_id' and TR.TAG_ID=T.ID and TR.TABLENAME='$tablename' and
				T.SITE_ID='$site_id'
			order by 
				T.ID asc
		";
		$res = mysql_query($sql);
		$tags = array();
		while ($row = mysql_fetch_assoc($res)){
			$tags[] = $row[TAGNAME];
		}
		$tags_str = implode(", ", $tags);
		$html .= "
			<input type='text' class='inputfelt' name='taglist' id='taglist' value='$tags_str' onkeyup='tag_handler(".$_SESSION[SELECTED_SITE].")'/>
			<div id='tags_autosuggest' style='margin-top:5px; font-size:10px; height:20px; line-height:20px; background-color:#dfd; padding:3px; border:1px solid #ccc;'></div>
		";
		return $html;
	}
	
	function AJAX_return_suggested_tags($letters, $usedtags, $site_id){
		$relevant_tags = array();
		if (trim($letters) != ""){
			$sql = "
				select * from TAGS 
				where TAGNAME like '".trim($letters)."%' and
				DELETED='0' and SITE_ID='$site_id'
				order by TAGNAME asc
			";
			$result = mysql_query($sql);
			if (mysql_num_rows($result)){
				while ($row = mysql_fetch_assoc($result)){
					if (!strstr(strtolower($usedtags), strtolower($row["TAGNAME"]).",")){
						$relevant_tags[] = $row["TAGNAME"];
					}
				}
				foreach($relevant_tags as $tag){
					$html .= "<a href='#' onclick='add_suggested_tag(\"$tag\"); return false;'>$tag</a> <span class='tag_divider'></span>";
				}
			} else {
				$html .= "Ingen, der starter med \"".$letters."\"";
			}
			return $html;
		}
	}
	
	function save_tags($taglist, $tablename, $request_id, $site_id){
		$usable_tags = array();
		temp_remove_tags($tablename, $request_id);
		$taglist = trim($taglist);
		$tags = explode(",", $taglist);
		foreach ($tags as $tag){
			$tag = strtolower(trim($tag));
			if ($tag != ""){
				$usable_tags[] = $tag;
			} 
		}
		foreach ($usable_tags as $tag){
			$sql = "select ID, DELETED from TAGS where TAGNAME='$tag' and SITE_ID='$site_id'";
			$res = mysql_query($sql);
			if (mysql_num_rows($res) == 0){
				$sql = "
					insert into TAGS (TAGNAME, SITE_ID) values ('$tag', '$site_id')
				";
				mysql_query($sql);
				$tag_id = mysql_insert_id();
			} else {
				$row = mysql_fetch_assoc($res);
				$tag_id = $row["ID"];
			} 
			$sql = "insert into TAG_REFERENCES (TAG_ID, TABLENAME, REQUEST_ID) VALUES ('$tag_id', '$tablename', '$request_id')";
			mysql_query($sql);
		}
	}
	
	function temp_remove_tags($tablename, $request_id){
		$sql = "delete from TAG_REFERENCES where TABLENAME='$tablename' and REQUEST_ID='$request_id'";
		mysql_query($sql);
	}

function return_feed_permalink($tablename, $requestid, $itemid) {
	switch ($tablename) {
		case "BLOGS":
			$temp_url = "/index.php?mode=blogs&blogid=$requestid&postid=$itemid";
			break;
		case "NEWSFEEDS";
			$temp_url = "/index.php?mode=news&feedid=$requestid&newsid=$itemid";
			break;
		default:
			return;
	}
	$url = returnBASE_URL($_SESSION[SELECTED_SITE]).returnSITE_PATH($_SESSION[SELECTED_SITE]).$temp_url;
	return $url;
}

function return_blog_url($blog_id) {
	$blog_url = returnBASE_URL($_SESSION[SELECTED_SITE]).returnSITE_PATH($_SESSION[SELECTED_SITE])."/index.php?mode=blogs&blogid=$blog_id";
	return $blog_url;
}

function return_newsfeed_url($newsfeed_id) {
	$url = returnBASE_URL($_SESSION[SELECTED_SITE]).returnSITE_PATH($_SESSION[SELECTED_SITE])."/index.php?mode=news&feedid=$newsfeed_id";
	return $url;
}

function update_feed($tablename, $requestid) {
	/*
		Function to handle all aspects of rss-feed updating
		Checks if feed should still be published, then publishes new edition
		Deletes feed xml if feed should no longer be published
	*/

	// 1. Get feed channel information
	$channel_info = hentRow($requestid, $tablename);
//	print_r($channel_info);
	
	// 2. Retrieve channel information, build arr_channel
	switch ($tablename) {
		case "BLOGS":
			if ($channel_info[DELETED]==0&&$channel_info[PUBLISHED]==1&&$channel_info[SYNDICATION_ALLOWED]==1) {
				$arr_channel["title"] 		= $channel_info[TITLE];
				if ($channel_info[SUBTITLE] != "") {
					$arr_channel["title"] 	.= $channel_info[SUBTITLE];
				}
				$arr_channel["link"] 		= return_blog_url($channel_info[ID]);
				$arr_channel["description"] = $channel_info[DESCRIPTION];
				$arr_channel["language"] 	= $channel_info[LANGUAGE_ID];
			} else {
				$arr_channel = array();
			}
			break;
		case "NEWSFEEDS":
			if ($channel_info[SYNDICATION_ALLOWED]==1) {
				$arr_channel["title"] 		= $channel_info[NAME];
				$arr_channel["link"] 		= return_newsfeed_url($channel_info[ID]);
				$arr_channel["description"] = "";
				$arr_channel["language"] 	= $channel_info[DEFAULT_LANGUAGE];
			} else {
				$arr_channel = array();
			}
			break;
		default:
			// Feed not implemented for this tablename
			return;
	}
//	print_r($arr_channel);

	// 3. Delete existing feed(s) regardless of syndication_key
	$path = return_feed_savepath();
	$filename = return_feed_filename($tablename, $requestid);
	// Convert filename to wildcard pattern to delete all feeds regardless of syndication_key
	$arr_filename = explode("_", $filename);
	array_pop($arr_filename);
	$filename_pattern = implode("_",$arr_filename)."*";
	remove_files_recursive_wildcard($path,$filename_pattern);

	// 4. Publish?
	if (count($arr_channel)==0) {
		// No channel information given, don't publish new feed
		return;
	}
	
	// 5. Generate new feed edition
	switch ($tablename) {
		case "BLOGS":
			$sql = "select 
						ID, HEADING, CONTENT, CONTENTSNIPPET, AUTHOR_ID, UNIX_TIMESTAMP(PUBLISHED_DATE) as TIMESTAMP
					from 
						BLOGPOSTS
					where 
						BLOG_ID = '$channel_info[ID]' and
						PUBLISHED = '1' and
						DELETED = '0'
					order by
						Date(CREATED_DATE) desc, Time(CREATED_DATE) desc
					limit
						10";
			$res = mysql_query($sql);
			while ($row = mysql_fetch_assoc($res)) {
				$arr_items[$row[ID]]["title"]			= $row[HEADING];
				$arr_items[$row[ID]]["link"]			= return_feed_permalink($tablename, $requestid, $row[ID]);
				if ($channel_info[SYNDICATION_SHOWCOMPLETEPOST] == 1) {
					$arr_items[$row[ID]]["content"]		= $row[CONTENT];
				} else {
					if ($row[CONTENTSNIPPET] == "") {
						$arr_items[$row[ID]]["content"]	= blog_snippet($row[CONTENT], $channel_info[SYNDICATION_SNIPPETLENGTH]);
					} else {
						$arr_items[$row[ID]]["content"]	= $row[CONTENTSNIPPET];
					}
				}
				$rfcdate = date("r", $row[TIMESTAMP]);

				$arr_items[$row[ID]]["created_date"]	= $rfcdate; 
				$arr_items[$row[ID]]["author"]			= $row[AUTHOR_ID]; 
				$arr_items[$row[ID]]["tags"]			= blogpost_tags_array($row[ID]);
			}
			break;
		case "NEWSFEEDS":
			$sql = "select 
						ID, HEADING, CONTENT, SUBHEADING, AUTHOR_ID, UNIX_TIMESTAMP(NEWS_DATE) as TIMESTAMP
					from 
						NEWS
					where 
						NEWSFEED_ID = '$channel_info[ID]' and
						PUBLISHED = '1' and
						DELETED = '0'
					order by
						NEWS_DATE desc, ID desc
					limit 
						10";
			$res = mysql_query($sql);
			while ($row = mysql_fetch_assoc($res)) {
				$arr_items[$row[ID]]["title"]			= $row[HEADING];
				$arr_items[$row[ID]]["link"]			= return_feed_permalink($tablename, $requestid, $row[ID]);
				if ($channel_info[SYNDICATION_SHOWCOMPLETEPOST] == 1) {
					$arr_items[$row[ID]]["content"]		= $row[CONTENT];
				} else {
					if ($row[SUBHEADING] == "") {
						$arr_items[$row[ID]]["content"]	= blog_snippet($row[CONTENT], $channel_info[SYNDICATION_SNIPPETLENGTH]);
					} else {
						$arr_items[$row[ID]]["content"]	= $row[SUBHEADING];
					}
				}
				$rfcdate = date("r", $row[TIMESTAMP]);

				$arr_items[$row[ID]]["created_date"]	= $rfcdate; 
				$arr_items[$row[ID]]["author"]			= $row[AUTHOR_ID]; 
			}
			break;
	}
	$feed_xml = generate_feed($arr_channel, $arr_items);
	$str_filename = return_feed_filename($tablename, $requestid);
	save_feed($str_filename, $feed_xml);
	$feed_url = return_feed_url_basepath($_SESSION[SELECTED_SITE]).$str_filename;
	blog_pinger($arr_channel["title"], $feed_url);
}

function generate_feed($arr_channel, $arr_items) {
//	global $cp1252_map;
	include_once($_SERVER[DOCUMENT_ROOT]."/cms/scripts/rss_writer_class.php");

	// Create feed
	$feed = new RSS();
	$sitename = returnSiteName($_SESSION[SELECTED_SITE]);
//	$feed->title       = $sitename.": ".strtr(utf8_encode(htmlspecialchars($arr_channel["title"])), $cp1252_map);
	$feed->title       = $sitename.": ".$arr_channel['title'];
	$feed->link        = htmlspecialchars(rewrite_urls_callback($arr_channel["link"]));
//	$feed->description = strtr(utf8_encode(htmlspecialchars(strip_tags($arr_channel["description"]))), $cp1252_map);
	$feed->description = strip_tags($arr_channel["description"]);
	if ($arr_channel["language"] != "") {
		// Default er "en-us";
		$feed->language	   = languageShortName($arr_channel["language"]);
	}
	
	// Populate feed with items
	if (is_array($arr_items)) {
		foreach ($arr_items as $item) {
			$new_item = new RSSItem();
//			$new_item->title = strtr(utf8_encode(htmlspecialchars($item["title"])), $cp1252_map);
			$new_item->title = $item["title"];
			$new_item->link  = htmlspecialchars(rewrite_urls_callback($item["link"]));
			$new_item->setPubDate($item["created_date"]); 
//			$new_item->author = strtr(utf8_encode(htmlspecialchars(returnAuthorName($item["author"], 0))), $cp1252_map);
			$new_item->author = returnAuthorName($item["author"], 0);
			if (is_array($item["tags"])) {
				if (count($item["tags"]) > 0) {
					$item["content"] .= "<p>Tags: ";
					foreach ($item["tags"] as $tag) {
						$item["content"] .= $tag[TAGNAME]."&nbsp;";
					}
					$item["content"] .= "</p>";
				}
			}
//			$new_item->description = "<![CDATA[ ".strtr(utf8_encode(str_replace($find, $replace,$item['content'])), $cp1252_map)." ]]>";
			$new_item->description = "<![CDATA[".$item['content']."]]>";
			$feed->addItem($new_item);
		}
	}
	return $feed->out();
//	return $feed->serve();
}

function save_feed($str_filename, $feed_xml) {
	$str_feedpath = return_feed_savepath();
	if (!file_put_contents("$str_feedpath$str_filename", $feed_xml)) {
		echo "Kunne ikke gemme RSS FEED i $str_feedpath$str_filename";
		return false;
	} else {
		return true;
	}
}

function remove_files_recursive_wildcard($path,$match){
	/*
		Here is simple function that will find and remove all files (except "." ones) that match the expression ($match, "*" as wildcard) under starting directory ($path) and all other directories under it.
	*/
   static $deld = 0, $dsize = 0;
    $dirs = glob($path."*");
    if(!is_array($dirs)) {
        $dirs = array();
    }
    $files = glob($path.$match);
    if(!is_array($files)) {
        $files = array();
    }
   foreach($files as $file){
      if(is_file($file)){
         $dsize += filesize($file);
//			echo "deleting $file";
         unlink($file);
         $deld++;
      }
   }
   foreach($dirs as $dir){
      if(is_dir($dir)){
         $dir = basename($dir) . "/";
         remove_files_recursive_wildcard($path.$dir,$match);
      }
   }
   return "$deld files deleted with a total size of $dsize bytes";
}

function sitemap_generator() {
	// Sitemap generator, bliver kørt hver gang man Gemmer/Sletter en ny side, nyhed, blog, kalender eller shop enhed
	// Funktionen returnerer true hvis sitemap blive genereret, ellers false
	global $googlesitemap_generator;
	if($googlesitemap_generator == true){
		$siteid = $_SESSION[SELECTED_SITE];
		$arr_sitemap_content[siteid] = $siteid;
		$arr_units = return_sitemap_array($arr_sitemap_content);
		
		if(is_array($arr_units)){
		//Hvis der blev oprettet et array med sider i bliver det skrevet til sitemap filen
		//Sørger for at sitemap filen kommer til at lægge yderst i roden på serveren
			$counter = count($arr_units);
			$filename = "sitemap_".$siteid.".xml";
			$filepath = $_SERVER["DOCUMENT_ROOT"]."/".$filename;

			$handle = fopen($filepath, "w+");
			fwrite($handle,'<?xml version="1.0" encoding="UTF-8"?>' . "\n");
			fwrite($handle,'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
			xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
			xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
			http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n");

			foreach ($arr_units as $key => $value) {
				fwrite($handle,"\t<url>\n");
				fwrite($handle,"\t\t<loc>".htmlentities($value[url])."</loc>\n");
				fwrite($handle,"\t\t<lastmod>$value[date]</lastmod>\n");
				fwrite($handle,"\t\t<changefreq>daily</changefreq>\n");
				fwrite($handle,"\t\t<priority>1.0</priority>\n");
				fwrite($handle,"\t</url>\n");
			}

			fwrite($handle,"</urlset>\n");
			fclose($handle);
		} else{
			return false;
		}
		if(file_exists($filepath)){
			//Hvis der findes et Sitemap bliver google pinget
			$baseurl = return_cmsdomains_baseurl($siteid);
			$url_xml = $baseurl."/".$filename;
			pingGoogleSitemaps($url_xml);
		}
		return true;
	} else {
		return false;
	}
}

function pingGoogleSitemaps($url_xml){ 
	global $googlesitemap_pingintervalmin; 
	global $googlesitemap_ping;
	if($googlesitemap_ping == true){
		$sql = "select 
					ID, NAME, LASTRUN, UNIX_TIMESTAMP(LASTRUN_COMPLETE) as LASTRUN_COMPLETE_UT 
				from 
					ACTIVITY_LOG 
				where 
					NAME = 'GOOGLE_PING'";
	
		if($res = mysql_query($sql)){
			if(mysql_num_rows($res) == 0){
				$sql = "INSERT INTO ACTIVITY_LOG (NAME, LASTRUN, DESCRIPTION) ";
				$sql .= "VALUES('GOOGLE_PING',NOW(),'Pinger googlesitemaps') ";			
				mysql_query($sql);
				$row = array("NAME" => "GOOGLE_PING", "LASTRUN_COMPLETE" => 0);
			} else {
				$row = mysql_fetch_assoc($res);					
			}
		
			if((time() - $row[LASTRUN_COMPLETE_UT]) > $googlesitemap_pingintervalmin){
				//Hvis google ikke er blevet pinget inden for de sidste X sekunder bliver de pinget igen
				//Tiden der skal være gået siden sidste ping kan ændres i cms_config.inc.php
				$sql = "UPDATE ACTIVITY_LOG set LASTRUN = NOW()";
				mysql_query($sql) or die("FEJL" . mysql_error());
	
				$status = 0;
				$google = 'www.google.com';
		   
				if($fp=@fsockopen($google, 80)){
					$req =  'GET /webmasters/sitemaps/ping?sitemap=' .
					   urlencode( $url_xml ) . " HTTP/1.1\r\n" .
					  "Host: $google\r\n" .
					  "User-Agent: Mozilla/5.0 (compatible; " .
					   PHP_OS . ") PHP/" . PHP_VERSION . "\r\n" .
					  "Connection: Close\r\n\r\n";
					fwrite( $fp, $req );
					while(!feof($fp)){
						if( @preg_match('~^HTTP/\d\.\d (\d+)~i', fgets($fp, 128), $m) ){
							$status = intval( $m[1] );
							break;
						}
					}
					fclose( $fp );
				}
				$msg = "if status isn't 200, google sitemaps wasn't pinged";
				$msg = mysql_real_escape_string($msg);
				$sql = "UPDATE ACTIVITY_LOG set LASTRUN_COMPLETE = NOW(), STATUS = '$status, $msg'";		   
				if (!mysql_query($sql)) {
					return false;
				}
			}
		} 
	}
}



function return_sitemap_array($arr_sitemap_content) {
	// Følgende er med: Sider, nyheder, nyhedsarkiver, kalenderbegivenheder, kalenderoversigt, 
	// blogoversigt, enkeltblogoversigt, blogindlæg, shop-hovedside, varegrupper, produkter
	
	// Finder alle de sider der oprettet og gemmer dem i $arr_units
	$sql = "select 
				P.ID, P.CHANGED_DATE, P.LANGUAGE, M.MENU_ID 
			from 
				PAGES P, MENUS M 
			where
				P.MENU_ID = M.MENU_ID and
				M.SITE_ID in (0, $arr_sitemap_content[siteid]) and
				P.SITE_ID in (0, $arr_sitemap_content[siteid]) and
				P.DELETED = '0' and
				P.UNFINISHED ='0' and
				P.PUBLISHED = '1' and
				P.POINTTOPAGE_URL = ''";

	if ($res = mysql_query($sql)) {
		if (mysql_num_rows($res) > 0) {
			while ($row = mysql_fetch_assoc($res)) {
				$date = date("Y-m-d", $row[CHANGED_DATE]);
				$baseurl = return_cmsdomains_baseurl($arr_sitemap_content[siteid], $row['LANGUAGE']);
				$loc = $baseurl."index.php?pageid=$row[ID]";
				$loc = rewrite_urls_callback($loc);				
				$arr_units[] = array("url" => $loc, "date" => $date);				
			}
		}
	} else {
		return false;
	}	

	// Finder alle nyheder der er oprettet og gemmer dem i $arr_units
	$sql = "select 
				N.ID NEWS_ID, NF.ID as NEWSFEED_ID, N.LANGUAGE, N.CHANGED_DATE 
			from 
				NEWS N, NEWSFEEDS NF
			where
				N.NEWSFEED_ID = NF.ID and
				N.SITE_ID in (0, $arr_sitemap_content[siteid]) and
				NF.SITE_ID in (0, $arr_sitemap_content[siteid]) and
				N.DELETED = '0' and
				N.UNFINISHED = '0' and
				N.PUBLISHED = '1'";
	
	if($res = mysql_query($sql)) {
		if (mysql_num_rows($res) > 0) {
			while ($row = mysql_fetch_assoc($res)) {
				$date = date("Y-m-d", $row[CHANGED_DATE]);
				$baseurl = return_cmsdomains_baseurl($arr_sitemap_content[siteid], $row['LANGUAGE']);
				$loc = $baseurl."index.php?mode=news&feedid=$row[NEWSFEED_ID]&newsid=$row[NEWS_ID]";
				$loc = rewrite_urls_callback($loc);
				$arr_units[] = array("url" => $loc, "date" => $date);
			}
		}
	} else {
		return false;
	}
	
	// Finder nyhedsarkiver og gemmer dem i $arr_units
	$sql = "select 
				distinct NF.ID as NEWSFEED_ID, NF.DEFAULT_LANGUAGE as LANGUAGE 
			from 
				NEWSFEEDS NF, NEWS N
			where 
				N.DELETED = '0' and
				N.PUBLISHED = '1' and
				N.NEWSFEED_ID = NF.ID and
				N.SITE_ID in (0, $arr_sitemap_content[siteid]) and
				NF.SITE_ID in (0, $arr_sitemap_content[siteid])";
	
	$res = mysql_query($sql);
		while ($row = mysql_fetch_assoc($res)) {
			$date = date("Y-m-d");
			$baseurl = return_cmsdomains_baseurl($arr_sitemap_content[siteid], $row['LANGUAGE']);
			$loc = $baseurl."index.php?mode=news&feedid=$row[NEWSFEED_ID]";

			$loc = rewrite_urls_callback($loc);
		
			$arr_units[] = array("url" => $loc, "date" => $date);
		}

	//Finder alle kalender begivenheder(events) og gemmer dem i $arr_units
	$sql = "select 
				C.ID as CALID, E.ID as EVENTID, E.LANGUAGE 
			from 
				CALENDARS C, EVENTS E 
			where
				E.CALENDAR_ID = C.ID and
				E.DELETED = '0' and
				E.UNFINISHED = '0' and
				E.PUBLISHED = '1' and
				C.SITE_ID in (0, $arr_sitemap_content[siteid]) and
				E.SITE_ID in (0, $arr_sitemap_content[siteid])";
	
	if($res = mysql_query($sql)){
		if(mysql_num_rows($res) > 0){
			while($row = mysql_fetch_assoc($res)){
				$date = date("Y-m-d");
				$baseurl = return_cmsdomains_baseurl($arr_sitemap_content[siteid], $row['LANGUAGE']);
				$loc = $baseurl."index.php?mode=events&calendarid=$row[CALID]&eventid=$row[EVENTID]";
				$loc = rewrite_urls_callback($loc);
				
				$arr_units[] = array("url" => $loc, "date" => $date);
			}
		}
	} else{
		return false;
	}
	//Finder kalender(der er som regel kun en) og gemmer den i $arr_units, men skriver den kun hvis der er nogen events i den
	$sql = "select 
				distinct C.ID as CAL_ID, C.DEFAULT_LANGUAGE as LANGUAGE
			from 
				CALENDARS C, EVENTS E
			where 
				E.DELETED = '0' and
				C.ID = E.CALENDAR_ID and
				E.SITE_ID in (0, $arr_sitemap_content[siteid]) and
				C.SITE_ID in (0, $arr_sitemap_content[siteid])";
	
	if($res = mysql_query($sql)){
		if (mysql_num_rows($res) > 0){
			while($row = mysql_fetch_assoc($res)){
				$date = date("Y-m-d");
				$baseurl = return_cmsdomains_baseurl($arr_sitemap_content[siteid], $row['LANGUAGE']);
				$loc = $baseurl."index.php?mode=events&calendarid=$row[CAL_ID]";
				$loc = rewrite_urls_callback($loc);
				$arr_units[] = array("url" => $loc, "date" => $date);
			}
		}
	} else{
		return false;
	}
			
	//Finder de blogs hvor der er oprettet indlæg i og gemmer dem i $arr_units
	 $sql = "select 
				distinct B.ID as BLOG_ID, B.LANGUAGE_ID as LANGUAGE 
			from 
				BLOGS B, BLOGPOSTS BP
			where 
				B.ID = BP.BLOG_ID and
				B.DELETED = '0' and
				B.UNFINISHED = '0' and
				BP.DELETED = '0' and
				BP.PUBLISHED = '1' and
				B.SITE_ID in (0, $arr_sitemap_content[siteid])
				group by BLOG_ID
				";
	$hasblogs = false;
	if($res = mysql_query($sql)){
		if(mysql_num_rows($res) > 0){
			while($row = mysql_fetch_assoc($res)){
				$hasblogs = true;
				$date = date("Y-m-d");
				$baseurl = return_cmsdomains_baseurl($arr_sitemap_content[siteid], $row['LANGUAGE']);
				$loc = $baseurl."index.php?mode=blogs&blogid=$row[BLOG_ID]";
				$loc = rewrite_urls_callback($loc);
				
				$arr_units[] = array("url" => $loc, "date" => $date);
			}
		}
	} else{
		return false;
	}
	
	if ($hasblogs){
		//Finder oversigt over alle blogs og gemmer dem i $arr_units
		$date = date("Y-m-d");
		$baseurl = return_cmsdomains_baseurl($arr_sitemap_content[siteid]);
		$loc = $baseurl."index.php?mode=blogs";
		$loc = rewrite_urls_callback($loc);
		$arr_units[] = array("url" => $loc, "date" => $date);
	}
		
	//Finder alle blogindlæg og gemmer dem i $arr_units
	$sql = "select 
				B.ID as BLOGS_ID, P.ID as POSTS_ID, P.BLOG_ID, B.LANGUAGE_ID as LANGUAGE 
			from 
				BLOGS B, BLOGPOSTS P 
			where 
				B.ID = P.BLOG_ID and
				B.DELETED = '0' and
				B.UNFINISHED = '0' and
				P.DELETED = '0' and
				P.UNFINISHED = '0' and
				P.PUBLISHED = '1' and
				B.SITE_ID in (0, $arr_sitemap_content[siteid])";
	
	if($res = mysql_query($sql)){
		if(mysql_num_rows($res) > 0){
			while($row = mysql_fetch_assoc($res)){
				$date = date("Y-m-d");
				$baseurl = return_cmsdomains_baseurl($arr_sitemap_content[siteid], $row['LANGUAGE']);
				$loc = $baseurl."index.php?mode=blogs&blogid=$row[BLOGS_ID]&postid=$row[POSTS_ID]";
				$loc = rewrite_urls_callback($loc);
				$arr_units[] = array("url" => $loc, "date" => $date);
			}
		}
	} else{
		return false;
	}
	
	//Finder alle produktgrupper og gemmer dem i $arr_units
	$sql = "select 
				distinct PG.ID 
			from 
				SHOP_PRODUCTGROUPS PG, SHOP_PRODUCTS P, SHOP_PRODUCTS_GROUPS PPG
			where 
				P.ID = PPG.PRODUCT_ID and
				PG.ID = PPG.GROUP_ID and
				PG.DELETED = '0' and
				PG.PUBLISHED = '1' and
				P.DELETED = '0' and
				PG.SITE_ID in (0, $arr_sitemap_content[siteid])";
	
	if($res = mysql_query($sql)){
		if(mysql_num_rows($res) > 0){
			while($row = mysql_fetch_assoc($res)){
				$date = date("Y-m-d");
				$baseurl = return_cmsdomains_baseurl($arr_sitemap_content[siteid]);
				$loc = $baseurl."index.php?mode=shop&action=showgroup&group=$row[ID]";
				$loc = rewrite_urls_callback($loc);
				$arr_units[] = array("url" => $loc, "date" => $date);
			}
		}
	} else{
		return false;
	}
	
	// Mode "shop"
	$sql = "select 
				distinct NAME 
			from 
				REWRITE_MODES
			where
				INTERNALNAME = 'shop'";
	
	if($res = mysql_query($sql)){
		if(mysql_num_rows($res) > 0){
			while($row = mysql_fetch_assoc($res)){
				$date = date("Y-m-d");
				$baseurl = return_cmsdomains_baseurl($arr_sitemap_content[siteid]);
				$loc = $baseurl."index.php?mode=$row[NAME]";
				$loc = rewrite_urls_callback($loc);
			
				$arr_units[] = array("url" => $loc, "date" => $date);
			}
		}
	}else{
		return false;
	}	
	
	//Finder alle produkter der er oprettet og gemmer dem i arr_units
	$sql = "select
					P.ID, P.GROUP_ID, P.PRODUCT_NUMBER as PNUMBER,
					PG.ID as PGROUP_ID, 
					PPG.ID, PPG.PRODUCT_ID, PPG.GROUP_ID
				from 
					SHOP_PRODUCTGROUPS PG, SHOP_PRODUCTS P, SHOP_PRODUCTS_GROUPS PPG
				where
				P.ID = PPG.PRODUCT_ID and
				PG.ID = PPG.GROUP_ID and
				PG.PUBLISHED = '1' and 
				PG.DELETED = '0' and
				P.DELETED = '0' and
				PG.SITE_ID in (0, $arr_sitemap_content[siteid])";
	
	if($res = mysql_query($sql)){
		if(mysql_num_rows($res) > 0){
			while($row = mysql_fetch_assoc($res)){
				$date = date("Y-m-d");
				$baseurl = return_cmsdomains_baseurl($arr_sitemap_content[siteid]);
				$loc = $baseurl."index.php?mode=shop&action=showproduct&group=$row[PGROUP_ID]&product=$row[PNUMBER]";
				$loc = rewrite_urls_callback($loc);
				
				$arr_units[] = array("url" => $loc, "date" => $date);
			}
		}
	} else{
		return false;
	}
	
	//Finder alle nyhedsbreve            
	$sql = "select
				distinct N.ID
			from
				NEWSLETTERS N, NEWSLETTER_HISTORY NH, NEWSLETTER_TEMPLATES NT
			where 
				N.TEMPLATE_ID = NT.ID and
				NH.NEWSLETTER_ID = N.ID and
				NH.TEMPLATE_ID = NT.ID and
				N.DELETED = '0' and
				N.APPROVED = '1' and
				NT.DELETED = '0' and
				NT.SHOW_IN_NEWSARCHIVE = '1' and
				N.SHOW_INDEX = '1' and
				NT.SITE_ID in (0, $arr_sitemap_content[siteid]) and
				NH.SENDOUT_COMPLETETIME > 0";
				
	if($res = mysql_query($sql)){
		if(mysql_num_rows($res) > 0){
			while($row = mysql_fetch_assoc($res)){
				$date = date("Y-m-d");
				$baseurl = return_cmsdomains_baseurl($arr_sitemap_content[siteid]);
				$loc = $baseurl."includes/shownewsletter.php?newsletterid=$row[ID]";
				$loc = rewrite_urls_callback($loc);
				
				$arr_units[] = array("url" => $loc, "date" => $date);
				
				$hasNewsletter = 1;
				
			}
		}
	} else{
		return false;
	}
				
	//Skriver mode=newsletter
	if($hasNewsletter == 1){
		$date = date("Y-m-d");
		$baseurl = return_cmsdomains_baseurl($arr_sitemap_content[siteid]);
		$loc = $baseurl."index.php?mode=newsletter";
		
		$arr_units[] = array("url" => $loc, "date" => $date);
	}
	
	return $arr_units;
}

function return_feed_url_basepath($siteid, $languageid=""){
	$baseurl = return_cmsdomains_baseurl($siteid, $languageid);
	$baseurl .= "feeds/";
	return $baseurl;
}

function return_cmsdomains_baseurl($siteid, $languageid="") {
	// Function to return default baseurl from CMS_SITEDOMAINS

	if ($languageid=="") {
		// No language passed to function, get default language for site
		$sql = 'select LANGUAGE from CMS_SITEDOMAINS where SITE_ID = "'.$siteid.'" and `DEFAULT` = "1" limit 1';
		if ($res = mysql_query($sql)) {
			if (mysql_num_rows($res)>0) {
				$languageid = mysql_result($res,0);
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	$sql = "select
				* 
			from
				CMS_SITEDOMAINS
			where
				SITE_ID = '$siteid' and
				LANGUAGE = '$languageid' and
				PREFERRED_FOR_LANGUAGE = '$languageid'
			limit 1";
	if ($res = mysql_query($sql)) {
		if (mysql_num_rows($res)>0) {
			$row = mysql_fetch_assoc($res);
		} else {
			// preferred for language not found, check for default sitedomain
			$sql = "select
						* 
					from
						CMS_SITEDOMAINS
					where
						SITE_ID = '$siteid' and
						LANGUAGE = '$languageid' and
						DEFAULT = '1'
					limit 1";
			if ($res = mysql_query($sql)) {
				if (mysql_num_rows($res)>0) {
					$row = mysql_fetch_assoc($res);
				} else {
					// default sitedomain not found, is there ANY row with this language?
					$sql = "select
								* 
							from
								CMS_SITEDOMAINS
							where
								SITE_ID = '$siteid' and
								LANGUAGE = '$languageid'
							order by 
								ID asc
							limit 1";
					if ($res = mysql_query($sql)) {
						if (mysql_num_rows($res)>0) {
							$row = mysql_fetch_assoc($res);
						} else {
							// Ok, lets grab the default sitedomain
							$sql = "select
										* 
									from
										CMS_SITEDOMAINS
									where
										SITE_ID = '$siteid' and
										DEFAULT = '1'
									limit 1";
							if ($res = mysql_query($sql)) {
								if (mysql_num_rows($res)>0) {
									$row = mysql_fetch_assoc($res);
								} else {
									return false;
								}
							}
						}
					}
				}
			} else {
				return false;
			}
		}
	} else {
		return false;
	}
	
	// Build baseurl
	$baseurl = "http://";
	if ($row['SUBDOMAIN'] != "" && $row['SUBDOMAIN'] != "*") {
		$baseurl .= $row['SUBDOMAIN'].".";
	}
	$baseurl .= $row['DOMAIN']."/";

	$sql = "select SITE_PATH from SITES where SITE_ID = ".$row['SITE_ID']." and SITE_PATH != '' limit 1";
	
	if ($res = mysql_query($sql)) {
		if (mysql_num_rows($res)>0) {
			$sitepath = mysql_result($res,0);
			$baseurl .= $sitepath."/";
		}
	} else {
		return false;
	}
	return $baseurl;
}

function blog_pinger($emne, $url){
	global $ping_blogs;
	if ($ping_blogs) {
		require_once($_SERVER[DOCUMENT_ROOT]."/cms/scripts/weblog_pinger.php");
		$pinger = new Weblog_Pinger();
		$pinger->ping_all($emne, $url);
	}
}

class Config {
	private $props = array();
	private static $instance;

	private function __construct() { }
	
	public function getInstance() {
		if (empty(self::$instance)) {
			self::$instance = new Config();
		}
		return self::$instance;
	}
	
	public function setProperty($key, $val) {
		$this->props[$key] = $val;
	}
	
	public function getProperty($key) {
		return $this->props[$key];
	}	
}
$prefs = Config::getInstance();
$prefs->setProperty("db_host", $db_host);
$prefs->setProperty("db_name", $db_name);
$prefs->setProperty("db_user", $db_user);
$prefs->setProperty("db_pass", $db_pass);
$prefs->setProperty("document_root", $_SERVER["DOCUMENT_ROOT"]);
unset($prefs);

/* 
	This structure encodes the difference between ISO-8859-1 and Windows-1252,
	as a map from the UTF-8 encoding of some ISO-8859-1 control characters to
	the UTF-8 encoding of the non-control characters that Windows-1252 places
	at the equivalent code points. Example: strtr(utf8_encode($str), $cp1252_map);
*/
$cp1252_map = array(
		"\xc2\x80" => "\xe2\x82\xac", /* EURO SIGN */
		"\xc2\x82" => "\xe2\x80\x9a", /* SINGLE LOW-9 QUOTATION MARK */
		"\xc2\x83" => "\xc6\x92",     /* LATIN SMALL LETTER F WITH HOOK */
		"\xc2\x84" => "\xe2\x80\x9e", /* DOUBLE LOW-9 QUOTATION MARK */
		"\xc2\x85" => "\xe2\x80\xa6", /* HORIZONTAL ELLIPSIS */
		"\xc2\x86" => "\xe2\x80\xa0", /* DAGGER */
		"\xc2\x87" => "\xe2\x80\xa1", /* DOUBLE DAGGER */
		"\xc2\x88" => "\xcb\x86",     /* MODIFIER LETTER CIRCUMFLEX ACCENT */
		"\xc2\x89" => "\xe2\x80\xb0", /* PER MILLE SIGN */
		"\xc2\x8a" => "\xc5\xa0",     /* LATIN CAPITAL LETTER S WITH CARON */
		"\xc2\x8b" => "\xe2\x80\xb9", /* SINGLE LEFT-POINTING ANGLE QUOTATION */
		"\xc2\x8c" => "\xc5\x92",     /* LATIN CAPITAL LIGATURE OE */
		"\xc2\x8e" => "\xc5\xbd",     /* LATIN CAPITAL LETTER Z WITH CARON */
		"\xc2\x91" => "\xe2\x80\x98", /* LEFT SINGLE QUOTATION MARK */
		"\xc2\x92" => "\xe2\x80\x99", /* RIGHT SINGLE QUOTATION MARK */
		"\xc2\x93" => "\xe2\x80\x9c", /* LEFT DOUBLE QUOTATION MARK */
		"\xc2\x94" => "\xe2\x80\x9d", /* RIGHT DOUBLE QUOTATION MARK */
		"\xc2\x95" => "\xe2\x80\xa2", /* BULLET */
		"\xc2\x96" => "\xe2\x80\x93", /* EN DASH */
		"\xc2\x97" => "\xe2\x80\x94", /* EM DASH */
	
		"\xc2\x98" => "\xcb\x9c",     /* SMALL TILDE */
		"\xc2\x99" => "\xe2\x84\xa2", /* TRADE MARK SIGN */
		"\xc2\x9a" => "\xc5\xa1",     /* LATIN SMALL LETTER S WITH CARON */
		"\xc2\x9b" => "\xe2\x80\xba", /* SINGLE RIGHT-POINTING ANGLE QUOTATION*/
		"\xc2\x9c" => "\xc5\x93",     /* LATIN SMALL LIGATURE OE */
		"\xc2\x9e" => "\xc5\xbe",     /* LATIN SMALL LETTER Z WITH CARON */
		"\xc2\x9f" => "\xc5\xb8"      /* LATIN CAPITAL LETTER Y WITH DIAERESIS*/
	);
?>