<?php
	require_once($_SERVER["DOCUMENT_ROOT"]."/cms/scripts/html_mime_mail/htmlMimeMail.php");

	function newsletter_approve($newsletter_id, $user_id) {
		$sql = "update 
					NEWSLETTERS 
				set
					APPROVED = '1', 
					APPROVED_BY = '$user_id', 
					APPROVED_TIME = UNIX_TIMESTAMP()
				where 
					ID = '$newsletter_id'
				";
		mysql_query($sql);
	}

	function update_categories_from_email($user_id, $template_id, $POSTVARS){
		newsletter_remove_category_optouts($user_id, $template_id);
		foreach ($POSTVARS as $key => $val){
			if (strstr($key, "category_")){
				$temp = explode("_", $key);
				$selected_categories[] = $temp[2];
			}
		}
		newsletter_categories_optout($user_id, $template_id, $selected_categories);
	}
	
	function require_validation($template_id){
		if (returnFieldValue("NEWSLETTER_TEMPLATES", "REQ_EMAIL_VALIDATION", "ID", $template_id) == 1){
			return true;
		} else {
			return false;
		}
	}
	
	function newsletter_is_user($email){
		$sql = "
			select ID from USERS where EMAIL='$email' limit 1
		";
		$result = mysql_query($sql);
		if (mysql_num_rows($result)){
			$row = mysql_fetch_assoc($result);
			return $user_id = $row["ID"];
		} else {
			return false;
		}
	}
	
	function newsletter_is_registered_user($user_id){
		$group_id = returnFieldValue("GENERAL_SETTINGS", "NEWSLETTER_GROUPID", "ID", $_SESSION[CURRENT_SITE]);
		$sql = "
			select GROUP_ID 
			from USERS_GROUPS
			where USER_ID='$user_id' and GROUP_ID != '$group_id'
		";
		$result = mysql_query($sql);
		if (mysql_num_rows($result)){
			$group_ids = array();
			while ($row = mysql_fetch_assoc($result)){
				$group_ids[] = $row["GROUP_ID"];
			}
			return $group_ids;
		} else {
			return false;
		}
	}			
	
	function newsletter_insert_user($email, $POSTVARS){
		foreach ($POSTVARS as $key => $value){
			if (strstr($key, "cfield___")){
				$temp = explode("___", $key);
				$db_custom_fields_names[]  = "$temp[1]";
				$db_custom_fields_values[] = "'$value'";
			}
		}
		if ($db_custom_fields_names){
			$add_to_sql_fields = implode(", ", $db_custom_fields_names);
			$add_to_sql_values = implode(", ", $db_custom_fields_values);
		}
		if (valid_email($email)){
			$generated_password = str_makerand(8,8);
			/// Get group-id for newsletter-recipients-group from GENERAL_SETTINGS table
			$group_id = returnFieldValue("GENERAL_SETTINGS", "NEWSLETTER_GROUPID", "ID", $_SESSION[CURRENT_SITE]);
			/// Create user in USERS table
			$sql = "
				insert into USERS (USERNAME, PASSWORD, EMAIL, EMAIL_VERIFIED".($add_to_sql_fields?",".$add_to_sql_fields:"").")
				values ('".$email."', '$generated_password', '".$email."', '0'".($add_to_sql_values?",".$add_to_sql_values:"").")
			";
			mysql_query($sql);
			$user_id = mysql_insert_id();
			/// Create reference in USERS_GROUPS table
			$sql = "
				insert into USERS_GROUPS (USER_ID, GROUP_ID)
				values ('$user_id', '$group_id')
			";
			mysql_query($sql);
			return $user_id;
		} else {
			exit;
		}
	}
		
	function newsletter_subscription_engine($user_id, $template_id, $quiet=""){
		$statuscode = "";
		/// BØR DENNE SQL IKKE FRASORTERE SLETTEDE BRUGERE? CJS, 13/7/07
		$sql = "select ID, SUBSCRIBED, CONFIRMED from NEWSLETTER_SUBSCRIPTIONS where USER_ID='$user_id' and TEMPLATE_ID='$template_id'";
		$result = mysql_query($sql);		
		if (mysql_num_rows($result)){
			$row = mysql_fetch_assoc($result);
			if ($row["SUBSCRIBED"] == 0){
				$sub_status = "unsubscribed";
			}
			if ($row["SUBSCRIBED"] == 1){
				$sub_status = "already_subscribed";
			}
			if ($row["CONFIRMED"] == 0){
				$con_status = "not_confirmed";
			}
			if ($row["CONFIRMED"] == 1){
				$con_status = "confirmed";
			}
		} else {
			$sub_status = "not_subscribed";
		}
		if ($sub_status == "not_subscribed"){
			$sql = "
				insert into NEWSLETTER_SUBSCRIPTIONS (USER_ID, TEMPLATE_ID, SUBSCRIBED, CHANGED_DATE, CONFIRMED)
				values ('$user_id', '$template_id', '1', '".time()."', '0')
			";
			mysql_query($sql);
			$selected_categories = newsletter_subscribed_categories($_POST);
			if (count($selected_categories)>0) {
				newsletter_categories_optout($user_id, $template_id, $selected_categories);
			}
			$statuscode = "OKAY_subscribed";
		}
		if ($sub_status == "unsubscribed"){
			$sql = "
				update NEWSLETTER_SUBSCRIPTIONS 
				set SUBSCRIBED='1', CONFIRMED='0', CHANGED_DATE='".time()."' 
				where USER_ID='$user_id' and TEMPLATE_ID='$template_id' and SUBSCRIBED='0'
			";
			mysql_query($sql);
			$selected_categories = newsletter_subscribed_categories($_POST);
			if (count($selected_categories)>0) {
				newsletter_categories_optout($user_id, $template_id, $selected_categories);
			}
			$statuscode = "OKAY_resubscribed";
		}
		if ($sub_status == "already_subscribed" && $con_status == "confirmed"){
			$statuscode = "ERROR_alreadysubscribed";
		}
		if ($sub_status == "already_subscribed" && $con_status == "not_confirmed"){
			$statuscode = "ERROR_alreadysubscribed_unconfirmed";
			if ($quiet == ""){
				newsletter_send_subscribe_verification_mail($user_id, $template_id);
			}
		}
		return $statuscode;
	}
	
	function newsletter_unsubscription_engine($user_id, $template_id){
		$statuscode = "";
		$sql = "select ID, SUBSCRIBED from NEWSLETTER_SUBSCRIPTIONS where USER_ID='$user_id' and TEMPLATE_ID='$template_id'";
		$result = mysql_query($sql);		
		if (mysql_num_rows($result)){
			$row = mysql_fetch_assoc($result);
			if ($row["SUBSCRIBED"] == 0){
				$sub_status = "already_unsubscribed";
			}
			if ($row["SUBSCRIBED"] == 1){
				$sub_status = "subscribed";
			}
		} else {
			$sub_status = "not_subscribed";
		}
		if ($sub_status == "already_unsubscribed"){
			$statuscode = "ERROR_alreadyunsubscribed";
		}
		if ($sub_status == "subscribed"){
			$sql = "
				update NEWSLETTER_SUBSCRIPTIONS 
				set SUBSCRIBED='0', CONFIRMED='0', CHANGED_DATE='".time()."' 
				where USER_ID='$user_id' and TEMPLATE_ID='$template_id' and SUBSCRIBED='1'
			";			
			mysql_query($sql);
			newsletter_remove_category_optouts($user_id, $template_id);
			$statuscode = "OKAY_unsubscribed";
		}
		if ($sub_status == "not_subscribed"){
			if (newsletter_user_is_passive_recipient($user_id, $template_id)){
				$sql = "
					insert into NEWSLETTER_SUBSCRIPTIONS (USER_ID, TEMPLATE_ID, SUBSCRIBED, CHANGED_DATE, CONFIRMED)
					values ('$user_id', '$template_id', '0', '".time()."', '0')
				";
				mysql_query($sql);			
				newsletter_remove_category_optouts($user_id, $template_id);
				$statuscode = "OKAY_optout";
			} else {
				$statuscode = "ERROR_notsubscribed";
			}
		}
		return $statuscode;
	}

	/// FUNKTIONER TIL AT HÅNDTERE KATEGORIER ////////////////////////////////////////////////////////
	
	/// Denne funktion finder de kategorier, som man kan 
	/// vælge mellem, når man tilmelder sig en given template
	function newsletter_get_categories_for_template($template_id){
		$categories = array();
		$sql = "
			select 
				NC.ID
			from 
				NEWSLETTER_TEMPLATES_CATEGORYGROUPS NTC, NEWSLETTER_CATEGORIES NC
			where 
				NC.GROUP_ID=NTC.CATEGORYGROUP_ID and NTC.TEMPLATE_ID='$template_id' 
				and NC.DELETED='0'
			order by 
				NC.ID
		";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)){
			$categories[] = $row["ID"];
		}
		return $categories;
	}
	
	/// Denne funktion henter de kategorier ud af POST-variablen, som 
	/// brugeren har krydset af i checkboksene
	function newsletter_subscribed_categories($POSTVARS){
		$categories = array();
		foreach ($POSTVARS as $k => $v){
			if (strstr($k, "subscribeto_category_")){
				$temp = explode("_", $k);
				$cid = $temp[2];
				$categories[] = $cid; 
			}
		}
		return $categories;
	}

	/// Denne funktion tager de kategorier, som man har afkrydset,
	/// og "spejler" dem, så man i stedet får en opt-out array med dem,
	/// man IKKE har valgt
	function newsletter_categories_optout($user_id, $template_id, $selected_categories){
		$categories_optout = array();
		$all_cats = newsletter_get_categories_for_template($template_id);
		foreach ($all_cats as $cat_id){
			if (!in_array($cat_id, $selected_categories)){
				$categories_optout[] = $cat_id;
			}
		}
		foreach ($categories_optout as $optout_cat_id){
			if (!newsletter_category_outopted($user_id, $template_id, $optout_cat_id)){
				$sql = "
					insert into NEWSLETTER_CATEGORIES_OPTOUT (TEMPLATE_ID, USER_ID, CATEGORY_ID)
					values ('$template_id', '$user_id', '$optout_cat_id')
				";
				mysql_query($sql);
			}
		}
	}

	/// Denne funktion checker, om en bruger allerede har opted-out
	/// af en bestemt kategori på en bestemt template
	function newsletter_category_outopted($user_id, $template_id, $category_id){
		$sql = "
			select ID 
			from NEWSLETTER_CATEGORIES_OPTOUT 
			where USER_ID='$user_id' and TEMPLATE_ID='$template_id' and CATEGORY_ID='$category_id'
		";
		$result = mysql_query($sql);
		if (mysql_num_rows($result)){
			return true;
		} else {
			return false;
		}		
	}
	
	/// Denne funktion sletter kategori-opt-outs,
	/// hvilket skal gøres, når man unsubscriber
	function newsletter_remove_category_optouts($user_id, $template_id){
		$sql = "
			delete from NEWSLETTER_CATEGORIES_OPTOUT 
			where USER_ID='$user_id' and TEMPLATE_ID='$template_id'
		";
		mysql_query($sql);
	}
	
	/// FUNKTIONER TIL AT CHECKE FORSKELLIGE STATUS /////////////////////////////////////////////////////////////
	
	function newsletter_user_is_passive_recipient($user_id, $template_id){
		$sql = "
			select 
				UG.GROUP_ID
			from 
				NEWSLETTER_TEMPLATES_USERGROUPS NTU, USERS_GROUPS UG
			where 
				UG.USER_ID='$user_id' and UG.GROUP_ID=NTU.GROUP_ID
				and NTU.TEMPLATE_ID='$template_id'
		";
		$result = mysql_query($sql);
		if (mysql_num_rows($result)){
			return true;
		} else {
			return false;
		}
	}	
	
	/// FUNKTIONER TIL VERIFICERING AF EMAIL OG SUBSCRIPTION  /////////////////////////////////////////////////////////////
	
	function newsletter_send_subscribe_verification_mail($user_id, $template_id){
		/// Hent data fra db
		$site_domain	= returnFieldValue("SITES", "BASE_URL", "SITE_ID", $_SESSION["CURRENT_SITE"]);
		$site_path		= returnFieldValue("SITES", "SITE_PATH", "SITE_ID", $_SESSION["CURRENT_SITE"]);
		$template_name	= returnFieldValue("NEWSLETTER_TEMPLATES", "TITLE", "ID", $template_id);
		$from_name		= returnFieldValue("NEWSLETTER_TEMPLATES", "SENDER_NAME", "ID", $template_id); 
		$from_email		= returnFieldValue("NEWSLETTER_TEMPLATES", "SENDER_EMAIL", "ID", $template_id); 
		$replyto_email	= returnFieldValue("NEWSLETTER_TEMPLATES", "REPLYTO_EMAIL", "ID", $template_id); 
		$mail_recipient	= returnFieldValue("USERS", "EMAIL", "ID", $user_id);
		$user_name		= returnFieldValue("USERS", "FIRSTNAME", "ID", $user_id) . " " . returnFieldValue("USERS", "LASTNAME", "ID", $user_id);
		/// Lav key, link, subject, indhold
		$verify_md5		= md5($mail_recipient.$user_id.$template_id."1nstansNewsletter098");
		$link			= $site_domain."/".($site_path ? $site_path . "/" : "")."index.php?mode=newsletter&action=verify_subscription&uid=".$user_id."&tid=".$template_id."&verify=".$verify_md5;
		$mail_subject 	= cmsTranslate("NewsletterApprovemailSubject").": $template_name";
		$mail_text 		= cmsTranslate("NewsletterApprovemailSalutation")." " . $user_name . "\n\n".cmsTranslate("NewsletterApprovemailInstructionsClick")." \"" . $template_name . "\":\n\n".$link."\n\n".cmsTranslate("NewsletterApprovemailInstructionsIgnore")."\n" . $from_name;
		/// Send mail via htmlMimeMail
        $mail = new htmlMimeMail();

		// Change to UFT-8 encoding
		$mail->setTextCharset("UTF-8");
		$mail->setHTMLCharset("UTF-8");
		$mail->setHeadCharset("UTF-8"); 

        $mail->setText($mail_text);
		//$mail->setReturnPath('chris@newforms.dk');		
		$mail->setFrom('"'.$from_name.'" <'.$from_email.'>');
		$mail->setSubject($mail_subject);
		$mail->setHeader('Reply-To', $replyto_email);
		$result = $mail->send(array($mail_recipient), 'mail');
	}
	
	function newsletter_verify_subscription($user_id, $template_id, $key, $quiet=""){
		$email = returnFieldValue("USERS", "EMAIL", "ID", $user_id);
		$validate_key = md5($email.$user_id.$template_id."1nstansNewsletter098");
		if ($key == $validate_key){
			$sql_email = "
				update USERS set EMAIL_VERIFIED='1' where ID='$user_id'
			";
			mysql_query($sql_email);
			$sql_subscription = "
				update NEWSLETTER_SUBSCRIPTIONS set CONFIRMED='1' where USER_ID='$user_id' and TEMPLATE_ID='$template_id'
			";
			mysql_query($sql_subscription);
			$notify_email = returnFieldValue("NEWSLETTER_TEMPLATES", "NEWSUBSCRIBER_NOTIFY_EMAIL", "ID", $template_id);
			if (trim($notify_email) != "" && $quiet == ""){
				global $cmsDomain;
				$template_title = returnFieldValue("NEWSLETTER_TEMPLATES", "TITLE", "ID", $template_id);
				$from_name		= returnFieldValue("NEWSLETTER_TEMPLATES", "SENDER_NAME", "ID", $template_id); 
				$from_email		= returnFieldValue("NEWSLETTER_TEMPLATES", "SENDER_EMAIL", "ID", $template_id); 
				$link1 = $cmsDomain."/cms/";
				$link2 = $cmsDomain."/cms/index.php?content_identifier=users&dothis=rediger&id=".$user_id;
				$content_html = "
					En ny modtager har tilmeldt sig nyhedsbrevet $template_title. Du kan gøre følgende for at
					se mere info om brugeren:<br><br>
					1) Log ind på CMS'et på <a href='$link1'>$link1</a>, hvis du ikke allerede er logget ind.<br><br>
					2) Klik på dette link:<br><br>
					<a href='$link2'>$link2</a>
					<br><br>
					for at se brugerens detaljer.
				";
				// mail($notify_email, "Ny abonnent på nyhedsbrevet $template_title", "$content_html", "From: $from_name <$from_email>\nContent-Type: text/html; charset=UTF-8");
				$mail_subject = "Ny abonnent på nyhedsbrevet $template_title";
					/// Send mail via htmlMimeMail
					$mail = new htmlMimeMail();
			
					// Change to UFT-8 encoding
					$mail->setTextCharset("UTF-8");
					$mail->setHTMLCharset("UTF-8");
					$mail->setHeadCharset("UTF-8"); 

			        $mail->setHtml($content_html, strip_tags($content_html));
					$mail->setFrom('"'.$from_name.'" <'.$from_email.'>');
					$mail->setSubject($mail_subject);
					$result = $mail->send(array("'$notify_email' <$notify_email>"), 'mail');
			}
			if ($quiet == ""){
				newsletter_usermessage("subscribe", "OKAY_confirmed", $user_id, $template_id);
			}
		} else {
			if ($quiet == ""){
				newsletter_usermessage("subscribe", "ERROR_notconfirmed", $user_id, $template_id);
			}
		}
	}

	function newsletter_send_unsubscribe_verification_mail($user_id, $template_id){
		/// Hent data fra db
		$site_domain	= returnFieldValue("SITES", "BASE_URL", "SITE_ID", $_SESSION["CURRENT_SITE"]);
		$site_path		= returnFieldValue("SITES", "SITE_PATH", "SITE_ID", $_SESSION["CURRENT_SITE"]);
		$template_name	= returnFieldValue("NEWSLETTER_TEMPLATES", "TITLE", "ID", $template_id);
		$from_name		= returnFieldValue("NEWSLETTER_TEMPLATES", "SENDER_NAME", "ID", $template_id); 
		$from_email		= returnFieldValue("NEWSLETTER_TEMPLATES", "SENDER_EMAIL", "ID", $template_id); 
		$replyto_email	= returnFieldValue("NEWSLETTER_TEMPLATES", "REPLYTO_EMAIL", "ID", $template_id); 
		$mail_recipient	= returnFieldValue("USERS", "EMAIL", "ID", $user_id);
		$user_name		= returnFieldValue("USERS", "FIRSTNAME", "ID", $user_id) . " " . returnFieldValue("USERS", "LASTNAME", "ID", $user_id);
		/// Lav key, link, subject, indhold
		$verify_md5		= md5($mail_recipient.$user_id.$template_id."1nstansNewsletter098");
		$link			= $site_domain."/".($site_path ? $site_path . "/" : "")."index.php?mode=newsletter&action=cancel_unsubscription&uid=".$user_id."&tid=".$template_id."&verify=".$verify_md5;
		$mail_subject 	= cmsTranslate("NewsletterUnsubscribemailSubject").": $template_name";
		$mail_text 		= cmsTranslate("NewsletterUnsubscribemailSalutation")." " . $user_name . "\n\n".cmsTranslate("NewsletterUnsubscribemailSubject")." \"" . $template_name . "\". ".cmsTranslate("NewsletterUnsubscribemailInstructionsClick")."\n\n".$link."\n\n".cmsTranslate("NewsletterUnsubscribemailInstructionsIgnore")."\n" . $from_name;
		/// Send mail via htmlMimeMail
        $mail = new htmlMimeMail();

		// Change to UFT-8 encoding
		$mail->setTextCharset("UTF-8");
		$mail->setHTMLCharset("UTF-8");
		$mail->setHeadCharset("UTF-8"); 

        $mail->setText($mail_text);
		//$mail->setReturnPath('chris@newforms.dk');		
		$mail->setFrom('"'.$from_name.'" <'.$from_email.'>');
		$mail->setSubject($mail_subject);
		$mail->setHeader('Reply-To', $replyto_email);
		$result = $mail->send(array($mail_recipient), 'mail');
	}

	function newsletter_cancel_unsubscription($user_id, $template_id, $key){
		$email = returnFieldValue("USERS", "EMAIL", "ID", $user_id);
		$validate_key = md5($email.$user_id.$template_id."1nstansNewsletter098");
		if ($key == $validate_key){
			$sql_subscription = "
				update NEWSLETTER_SUBSCRIPTIONS set SUBSCRIBED='1', CONFIRMED='1' where USER_ID='$user_id' and TEMPLATE_ID='$template_id' and SUBSCRIBED='0' and CONFIRMED='0'
			";
			mysql_query($sql_subscription);
			newsletter_usermessage("subscribe", "OKAY_unsubscription_canceled", $user_id, $template_id); 
		} else {
			newsletter_usermessage("subscribe", "ERROR_could_not_verify", $user_id, $template_id);
		}
	}

	/// DIVERSE //////////////////////////////////////////////////////////////////////////
	
	function newsletter_usermessage($action, $errorcode, $user_id="", $template_id=""){
		$action = "usermessage";
		$url = "index.php?mode=newsletter&action=".$action."&newsletterid=".$template_id."&statuscode=".$errorcode;
		header("location: ".$url);
		exit;
	}
		
	function newsletter_update_name($user_id, $fn, $ln){
		$sql = "update USERS set FIRSTNAME='$fn', LASTNAME='$ln' where ID='$user_id'";
		mysql_query($sql);
	}

	function valid_email($email){
		if (eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)){
			return true;
		} else {
			return false;
		}
	}

	if (!function_exists("str_makerand")){
	function str_makerand($minlength, $maxlength, $useupper=false, $usespecial=false, $usenumbers=false){ 
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
	}


function newsletter_stats_register($newsletter_id, $user_id, $action, $openkey="", $clicked_url="", $tablename="", $request_id=""){
	if ($newsletter_id == "" || $newsletter_id == "0" || $user_id == "" || $user_id == "0" ) {
		return;
	}
	// Check openkey
	$compare_key = md5($user_id."1nstansFlyvemaskine");

	if ($compare_key != $openkey) {
		return;
	}

	// Only register stats for newsletters that have been sent out!
	$sql = "select count(*) from NEWSLETTERS N, NEWSLETTER_HISTORY NH where N.DELETED = 0 and NH.SENDOUT_COMPLETETIME > 0 and N.ID = '$newsletter_id' and N.ID = NH.NEWSLETTER_ID";
	$res = mysql_query($sql);
	if (mysql_result($res,0)==0) {
		return;
	}

	$clicked_url = html_entity_decode($clicked_url);
	switch ($action) {
		case "click":
				// Click already registered for this newsletter/user/clickedurl combination?
				$sql = "select ID, TIMES_REPEATED from NEWSLETTER_STATS where NEWSLETTER_ID = '$newsletter_id' and USER_ID = '$user_id' and CLICKED_URL = '$clicked_url' and USER_ACTION = '$action'";
				$res = mysql_query($sql);
				if (mysql_num_rows($res)>0) {
					$row = mysql_fetch_assoc($res);
					$repeat_count = $row["TIMES_REPEATED"] + 1;
					$isql = "update NEWSLETTER_STATS set TIMES_REPEATED = '$repeat_count', CHANGED_DATE = NOW() where ID = '$row[ID]'";
				} else {
					$isql = "insert into NEWSLETTER_STATS (
									NEWSLETTER_ID, USER_ID, USER_ACTION, TABLENAME, REQUEST_ID, CLICKED_URL, TIMES_REPEATED, CREATED_DATE
								) values (
									'$newsletter_id', '$user_id', '$action', '$tablename', '$request_id', '$clicked_url', '1', NOW()
								)";
				}
				if (mysql_query($isql)) {
					// Make sure opening of newsletter has been registered
						$asql = "select ID, TIMES_REPEATED from NEWSLETTER_STATS where NEWSLETTER_ID = '$newsletter_id' and USER_ID = '$user_id' and USER_ACTION = 'open'";
					$ares = mysql_query($asql);
					if (mysql_num_rows($ares)==0) {
							$aisql = "insert into NEWSLETTER_STATS (
										NEWSLETTER_ID, USER_ID, USER_ACTION, TABLENAME, REQUEST_ID, CLICKED_URL, TIMES_REPEATED, CREATED_DATE
									) values (
										'$newsletter_id', '$user_id', 'open', '$tablename', '$request_id', '$clicked_url', '1', NOW()
									)";
						if (mysql_query($aisql)) {
							return true;
						} else {
							return false;
						}
					} else {
						return true;
					}
				} else {
					return false;
				}
				break;
		case "open":
				// Already registered for this newsletter/user combination?
				$sql = "select ID, TIMES_REPEATED from NEWSLETTER_STATS where NEWSLETTER_ID = '$newsletter_id' and USER_ID = '$user_id' and USER_ACTION = '$action'";
				$res = mysql_query($sql);
				if (mysql_num_rows($res)>0) {
					$row = mysql_fetch_assoc($res);
					$repeat_count = $row["TIMES_REPEATED"] + 1;
					$isql = "update NEWSLETTER_STATS set TIMES_REPEATED = '$repeat_count', CHANGED_DATE = NOW() where ID = '$row[ID]'";
				} else {
					$isql = "insert into NEWSLETTER_STATS (
									NEWSLETTER_ID, USER_ID, USER_ACTION, TIMES_REPEATED, CREATED_DATE
								) values (
									'$newsletter_id', '$user_id', '$action', '1', NOW()
								)";
				}
				if (mysql_query($isql)) {
					return true;
				} else {
					return false;
				}
				break;

		
	}
}
?>
