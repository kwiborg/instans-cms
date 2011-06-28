<?php
if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_NEWSLETTERADMIN", true);

switch ($dothis) {
case "insert":
	// Insert Template
	$sql = "insert into NEWSLETTER_TEMPLATES
		(
		OPEN_FOR_SUBSCRIPTIONS, 
		SHOW_IN_NEWSARCHIVE,
		REQ_EMAIL_VALIDATION,
		SENDER_NAME,
		SENDER_EMAIL,
		REPLYTO_EMAIL,
		BOUNCETO_EMAIL,
		SUBSCRIPTIONPAGE_TEXTTOP,
		SUBSCRIPTIONPAGE_TEXTBOTTOM,
		SUBSCRIPTIONPAGE_TEXTTHANKS,
		LANGUAGE_ID,
		SITE_ID,
		TEMPLATE_ID,
		TITLE,
		NEWSUBSCRIBER_NOTIFY_EMAIL
		)	
	values
		(
		'$_POST[nt_open]', 
		'$_POST[nt_newsarchive]', 
		'$_POST[nt_email_validation]',
		'$_POST[nt_sender_name]',
		'$_POST[nt_sender_email]',
		'$_POST[nt_replyto_email]',
		'$_POST[nt_bounce_email]',
		'$_POST[nt_subform_top]',
		'$_POST[nt_subform_bottom]',
		'$_POST[nt_subform_thanks]',
		'$_POST[nt_language]',
		'$_SESSION[SELECTED_SITE]',
		'$_POST[nt_template]',
		'$_POST[nt_title]',
		'$_POST[nt_notifymails]'
		)
	";
	$result = mysql_query($sql);
	// Insert Template-Interessegrupper intersections
	$groups = returnCheckedBoxes($_POST, "catgroup_");
	$insert_id = mysql_insert_id();
	if ($groups) {
		establishInterestgroupIntersections($insert_id, $groups);
	}
	establishDbFieldIntersections($insert_id);
	header("location: index.php?content_identifier=newslettertemplates");
	break;
case "update":
	// Update template
	$sql = "
	update NEWSLETTER_TEMPLATES
	set OPEN_FOR_SUBSCRIPTIONS = '$_POST[nt_open]',
	SHOW_IN_NEWSARCHIVE = '$_POST[nt_newsarchive]', 
	REQ_EMAIL_VALIDATION = '$_POST[nt_email_validation]',
	SENDER_NAME = '$_POST[nt_sender_name]',
	SENDER_EMAIL = '$_POST[nt_sender_email]',
	REPLYTO_EMAIL = '$_POST[nt_replyto_email]',
	BOUNCETO_EMAIL = '$_POST[nt_bounce_email]',
	SUBSCRIPTIONPAGE_TEXTTOP = '$_POST[nt_subform_top]',
	SUBSCRIPTIONPAGE_TEXTBOTTOM = '$_POST[nt_subform_bottom]',
	SUBSCRIPTIONPAGE_TEXTTHANKS = '$_POST[nt_subform_thanks]',
	LANGUAGE_ID = '$_POST[nt_language]',
	SITE_ID = '$_SESSION[SELECTED_SITE]',
	TEMPLATE_ID = '$_POST[nt_template]',
	TITLE = '$_POST[nt_title]',
	NEWSUBSCRIBER_NOTIFY_EMAIL = '$_POST[nt_notifymails]'
	where ID = '$_POST[id]'
	";
	$result = mysql_query($sql);
	// Insert Template-Interessegrupper intersections
	$groups = returnCheckedBoxes($_POST, "catgroup_");
	establishInterestgroupIntersections($_POST[id], $groups);
	establishDbFieldIntersections($_POST[id]);
	header("location: index.php?content_identifier=newslettertemplates");
	break;
case "delete":
	sletRow($_GET[ntid], "NEWSLETTER_TEMPLATES");
	header("location: index.php?content_identifier=newslettertemplates");
	break;
case "update_recipientgroups":
	$groups = returnCheckedBoxes($_POST, "usergroup_");
	establishUsergroupIntersections($_POST[id], $groups);
	header("location: index.php?content_identifier=newslettertemplates");
	break;
}

function returnCheckedBoxes($p, $keystring) {
	// Takes $_POST-array ($p) and parses it for ids containing $keystring and returns an array with values only.
	// Returns false on zero matches
	foreach ($_POST as $key => $value) {
		if (strstr($key, $keystring)) {
			$groups[] = $value;
		}
	}
	if (is_array($groups)) {
		return $groups;
	} else {
		return false;
	}
}

function establishUsergroupIntersections($id, $groups) {
	// Delete existing
	$sql = "delete from NEWSLETTER_TEMPLATES_USERGROUPS where TEMPLATE_ID = '$id'";
	mysql_query($sql);
	// Insert new intyersections

	if ($groups) {
		foreach ($groups as $key => $value) {
		$sql = "insert into NEWSLETTER_TEMPLATES_USERGROUPS (TEMPLATE_ID, GROUP_ID) values ($id, $value)";
		mysql_query($sql);
		}
	}
}

function establishInterestgroupIntersections($id, $groups) {
	// Delete existing
	$sql = "delete from NEWSLETTER_TEMPLATES_CATEGORYGROUPS where TEMPLATE_ID = '$id'";
	mysql_query($sql);
	// Insert new intyersections
	if ($groups) {
		foreach ($groups as $key => $value) {
		$sql = "insert into NEWSLETTER_TEMPLATES_CATEGORYGROUPS (TEMPLATE_ID, CATEGORYGROUP_ID) values ($id, $value)";
		mysql_query($sql);
		}
	}
}

function establishDbFieldIntersections($template_id){
	// DB-fields on subscribeform
	$sql = "delete from NEWSLETTER_TEMPLATES_FORMFIELDS where TEMPLATE_ID='$template_id'";
	mysql_query($sql);
	foreach ($_POST as $key => $val){
		if (strstr($key, "fieldid_")){
			$temp = explode("_", $key);
			$field_id = $temp[1];
			if ($val == 1){ 
				$sql = "
					insert into NEWSLETTER_TEMPLATES_FORMFIELDS (TEMPLATE_ID, FIELD_ID, MANDATORY) 
					values ('$template_id','$field_id', '0')
				";
				mysql_query($sql);
			}
			if ($val == 2){ 
				$sql = "
					insert into NEWSLETTER_TEMPLATES_FORMFIELDS (TEMPLATE_ID, FIELD_ID, MANDATORY) 
					values ('$template_id','$field_id', '1')
				";
				mysql_query($sql);
			}
		}
	}
	// Also insert row with e-mail field
	$fsql = "select NF.ID from NEWSLETTER_FORMFIELDS NF
				where 
					NF.TABLE_NAME = 'USERS' and
					NF.FIELD_NAME = 'EMAIL'
				limit 1";
	$res = mysql_query($fsql);
	$field_id = mysql_result($res,0);
	$sql = "insert into NEWSLETTER_TEMPLATES_FORMFIELDS (TEMPLATE_ID, FIELD_ID, MANDATORY)
				values ('$template_id','$field_id', '1')";
	mysql_query($sql);

}
?>