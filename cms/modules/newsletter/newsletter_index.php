<?php
include_once('newsletter_common.inc.php');

switch ($dothis) {
case "rediger":
	echo newsletterForm();
	break;
case "sendnewsletter":
	echo sendForm($_GET[nid],$_GET[filter_template]);
	break;
case "proof":
	// Use this case as cleanup NEWSLETTER_ITEMS and NEWSLETTER_NEWSLETTER_CATEGORIES
	$sql = "delete from NEWSLETTER_ITEMS where TEMPORARY = '1' and CREATED_BY = '".$_SESSION[CMS_USER][USER_ID]."'";
	mysql_query($sql);
	$sql = "delete from NEWSLETTER_NEWSLETTER_CATEGORIES where TEMPORARY = '1' and USER_ID = '".$_SESSION[CMS_USER][USER_ID]."'";
	mysql_query($sql);
	
	// And perform intended action
	echo proofForm($_GET[nid]);
	break;
case "sendproof":
	// Get proof-user information
	$sql = "select 
				U.ID, 
				U.USERNAME, 
				U.FIRSTNAME, 
				U.LASTNAME,
				U.ADDRESS,
				U.ZIPCODE,
				U.CITY,
				U.PHONE,
				U.CELLPHONE,
				U.EMAIL,
				U.CV,
				U.COMPANY
			from
				USERS U
			where
				U.DELETED = '0' and
				U.UNFINISHED = '0' and
				U.ID = '".$_SESSION[CMS_USER][USER_ID]."'";
	$res = mysql_query($sql);
	$arr_recipients = array();
	$arr_recipients[] = mysql_fetch_array($res);

	// Get subscribers opt-out
	$template_id = $_GET[filter_template];
	$arr_optoutcats = returnSubscriberOptouts($_SESSION[CMS_USER][USER_ID], $template_id);
	// Append optouts to recipients array
	$arr_recipients[0][OPTOUTS] = $arr_optoutcats;
	
	sendNewsletter($_GET[nid], $arr_recipients, "mailproof");
	echo proofsentForm($_GET[nid]);
	break;
case "opret":
	// Insert category associations using a ramdom temporary newsletter id
	$_GET[nid_temp] = rand (10000, 19999);
	createTemporaryCategoryassociations($_GET[nid_temp],$_GET[ntid]);
	echo newsletterForm();
	break;
case "stats": 
	echo show_newsletter_stats($_GET[nid]);
	break;
case "stats_url":
	echo show_newsletter_stats_url();
	break;	
default:
	echo listNewsletters();
	break;
}


?>