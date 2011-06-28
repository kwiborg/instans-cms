<?php
	include("common.inc.php");
	checkLoggedIn();
	rydOp_all();
  // Fetch default content identifier
	$current_user = $_SESSION[CMS_USER][USER_ID];
	$sql = "select distinct
				G.DEFAULT_CONTENT_IDENTIFIER
			from
				USERS_GROUPS UG, GROUPS G
			where
				UG.USER_ID = $current_user and
				UG.GROUP_ID = G.ID and
				G.HIDDEN = 0 and
				G.DELETED = 0 and
				G.UNFINISHED = 0 and
				G.DEFAULT_CONTENT_IDENTIFIER not like ''
			order by G.ID asc
			limit 1";
	 $res = mysql_query($sql);
	if (mysql_num_rows($res)>0) {
		$content_identifier = mysql_result($res,0);
		if ($content_identifier == "") {
			$content_identifier = "pages";
		}
	} else {
		$content_identifier = "pages";
	}

if ($_POST[dothis] == "videre") {
	require_once("auto_upgrade.php");
	$_SESSION["SELECTED_SITE"] = $_POST[site_id];
	$permissions = returnDistinctUserPermissions($current_user, $_POST[site_id]);
	$_SESSION["CMS_USER"]["PERMISSIONS"] = $permissions;
	header("location: index.php?content_identifier=$content_identifier");
	exit;
}

$html = "
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN'
   'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html>
<head>
<meta http-equiv='content-type' content='text/html; charset=UTF-8' />
<title>Vælg website at redigere</title>
<link rel='stylesheet' href='cms.css'/>
<script src='/cms/scripts/prototype.js' type='text/javascript'></script>
<script src='/cms/scripts/scriptaculous/scriptaculous.js' type='text/javascript'></script>
<script type='text/javascript' src='commonscripts.js'></script>
</head>
<body id='content'>
<form method='post' action=''>
 <input type='hidden' name='dothis' value=''/>
 <input type='hidden' name='site_id' value=''/>
 <div id='wrapitall'>
  <div style='width:600px; margin:0px auto'>
   <div class='feltblok_header'>
    Vælg website at redigere
   </div>
   <div class='feltblok_wrapper'>
    <h2>Følgende sites er tilgængelige:</h2>";
$sql = "select SITE_ID, SITE_NAME from SITES where 1";
$result = mysql_query($sql);
while ($row = mysql_fetch_array($result)) {
	if (!check_data_permission("DATA_CMS_ACCESSSITE", "SITES", $row[SITE_ID], "", $current_user)) {
		$disabled = "disabled";
    } else {
		$cur_permissions = returnDistinctUserPermissions($current_user, $row[SITE_ID]);
		if (!is_array($cur_permissions)) {
			$disabled = "disabled";
		} else {
			if (!in_array("CMS_LOGIN", $cur_permissions)) {
				$disabled = "disabled";
			} else {
				$disabled = "";
				$sitecount++;
				$gotosite=$row[SITE_ID];
			}
		}		
    }
    
// echo "Evaluated site $row[SITE_ID]: disabled=$disabled, sitecount=$sitecount, gotosite=$gotosite<br/>";
    
	$html .= "<input type='radio' name='sites' onclick='this.form.site_id.value=\"$row[SITE_ID]\"' $disabled />&nbsp;$row[SITE_NAME]<br/>";
}
$html .= "<br/><br/>
    <input type='button' value='Videre' onclick='verifySiteSel()' class='lilletekst_knap'/>	
   </div>
  </div>
 </div>
</form>
</body>
</html>";
if ($sitecount == 1) {
	require_once("auto_upgrade.php");
	$_SESSION["CMS_USER"]["MULTIPLE_SITES"] = 0; // Used to determine if "Skift"-button is showed in CMS menu
	$_SESSION["SELECTED_SITE"] = $gotosite;
	$permissions = returnDistinctUserPermissions($current_user, $gotosite);
	$_SESSION["CMS_USER"]["PERMISSIONS"] = $permissions;
	header("location: index.php?content_identifier=$content_identifier");
	exit;
} else {
	$_SESSION["CMS_USER"]["MULTIPLE_SITES"] = 1;
	echo $html;
}
?>
