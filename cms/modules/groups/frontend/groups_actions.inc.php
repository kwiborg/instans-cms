<?php	
	require_once($_SERVER["DOCUMENT_ROOT"]."/cms/scripts/html_mime_mail/htmlMimeMail.php");

	switch($_POST[action]){
		case "register":
			$verify_ticket = md5("1nstansFlyveMa5kine".$_POST[group_id]);
			if ($verify_ticket == $_POST["ticket"]){
				$new_user_id = groups_save_user($_POST);
				if ($new_user_id){
					group_send_verification_mail($new_user_id, $_POST[group_id]);
					header("location:$arr_content[baseurl]/index.php?mode=groups&action=pending&groupid=$_POST[group_id]");
					exit;
				} else {
					$_POST[username_exists] = true;
				}
			} else {
				echo "Not allowed.";
				exit;
			}
		break;
		case "edit":
			if (update_user($_POST)){
				$_POST[updated_okay] = true;
			} else {
				$_POST[username_exists] = true;
			}
		break;
	}
	
	switch($_GET[action]){
		case "verify_membership":
			if ($_GET[uid] && $_GET[gid] && $_GET[verify]){
				if (group_verify_newuser($_GET[uid], $_GET[gid], $_GET[verify])){
					$userdata = hentRow($_GET[uid], "USERS");
					groups_send_admin_notification($_GET[gid], $userdata);
					groups_send_user_notification($_GET[uid], $_GET[gid], $userdata);
					header("location:$arr_content[baseurl]/index.php?mode=groups&action=registered&groupid=$_GET[gid]");
					exit;
				} else {
					echo "Something went wrong: Key from mail could not be verified.";
				}
			}
		break;
	}

	function group_send_verification_mail($user_id, $group_id){
		/// Hent data fra db
		$site_domain	= returnFieldValue("SITES", "BASE_URL", "SITE_ID", $_SESSION["CURRENT_SITE"]);
		$site_path		= returnFieldValue("SITES", "SITE_PATH", "SITE_ID", $_SESSION["CURRENT_SITE"]);
		$group_name		= returnFieldValue("GROUPS", "GROUP_NAME", "ID", $group_id);
		$from_name		= returnFieldValue("SITES", "SITE_NAME", "SITE_ID", $_SESSION["CURRENT_SITE"]); 
		$from_email		= "no-reply@".returnFieldValue("SITES", "EMAIL_DOMAIN", "SITE_ID", $_SESSION["CURRENT_SITE"]);
		$replyto_email	= $from_email;
		$mail_recipient	= returnFieldValue("USERS", "EMAIL", "ID", $user_id);
		$user_name		= returnFieldValue("USERS", "FIRSTNAME", "ID", $user_id) . " " . returnFieldValue("USERS", "LASTNAME", "ID", $user_id);
		/// Lav key, link, subject, indhold
		$verify_md5		= md5($mail_recipient.$user_id.$group_id."1nstansNewsletter098");
		$link			= $site_domain."/".($site_path ? $site_path . "/" : "")."index.php?mode=groups&action=verify_membership&uid=".$user_id."&gid=".$group_id."&verify=".$verify_md5;
		$mail_subject 	= cmsTranslate("groups_ApprovemailSubject").": $group_name";
		$mail_text 		= cmsTranslate("groups_ApprovemailSalutation")." " . $user_name . "\n\n".cmsTranslate("groups_ApprovemailInstructionsClick")." \"" . $group_name . "\":\n\n".$link."\n\n".cmsTranslate("groups_ApprovemailInstructionsIgnore")."\n" . $from_name;
		/// Send mail via htmlMimeMail
        $mail = new htmlMimeMail();

		// Change to UFT-8 encoding
		$mail->setTextCharset("UTF-8");
		$mail->setHTMLCharset("UTF-8");
		$mail->setHeadCharset("UTF-8"); 

        $mail->setText($mail_text);
		$mail->setFrom('"'.$from_name.'" <'.$from_email.'>');
		$mail->setSubject($mail_subject);
		$mail->setHeader('Reply-To', $replyto_email);
		$result = $mail->send(array($mail_recipient), 'mail');
	}
	
	function group_verify_newuser($user_id, $group_id, $key){
		$email = returnFieldValue("USERS", "EMAIL", "ID", $user_id);
		$validate_key = md5($email.$user_id.$group_id."1nstansNewsletter098");
		if ($key == $validate_key){
			$sql_email = "
				update USERS set EMAIL_VERIFIED='1', UNFINISHED='0' where ID='$user_id'
			";
			mysql_query($sql_email);
			// groups_usermessage("confirm_user", "OKAY_confirmed", $user_id, $template_id); 
			return true;
		} else {
			// groups_usermessage("confirm_user", "ERROR_notconfirmed", $user_id, $template_id);
			return false;
		}
	}

	function groups_usermessage($action, $errorcode, $user_id="", $group_id=""){
		$action = "usermessage";
		$url = "index.php?mode=groups&action=".$action."&groupid=".$group_id."&statuscode=".$errorcode;
		header("location: ".$url);
		exit;
	}

	function groups_send_user_notification($user_id, $group_id, $POSTVARS){
		$landing_group_id = returnFieldValue("GROUPS", "LANDING_GROUP_ID", "ID", $group_id);
		if ($landing_group_id > 0){
			return false;
		} else {
			$userdata = hentRow($user_id, "USERS");
			$mail_domain = returnFieldValue("SITES", "EMAIL_DOMAIN", "SITE_ID", $_SESSION["CURRENT_SITE"]);
			$sitename 	 = returnFieldValue("SITES", "SITE_NAME", "SITE_ID", $_SESSION["CURRENT_SITE"]);
			sendNewUserMail($userdata[EMAIL], $userdata[FIRSTNAME], $userdata[LASTNAME], $sitename, $userdata[USERNAME], $userdata[PASSWORD], $sitename, "no-reply@".$mail_domain);
		}
	}
	
	function sendNewUserMail($email, $firstname, $lastname, $sitename, $username, $password, $fromname, $frommail) {
		if (cmsTranslate("newusermail_signatureName") == ""){
			$fromname = str_replace("http://","",$fromname);
		} else {
			$fromname = cmsTranslate("newusermail_signatureName");
		}
	    mail($email, 
	   		"Login",
	   		cmsTranslate("newusermail_dear")." $firstname $lastname,<br /><br />
	   			".cmsTranslate("newusermail_youAreRegistered").".
	   			<br /><br /><strong>".cmsTranslate("newusermail_username").":</strong> $username<br /><strong>".cmsTranslate("newusermail_password").":</strong> $password<br /><br />
	   			".cmsTranslate("newusermail_bestRegards")."<br />$fromname", 
	   		"From: ".$fromname." <$frommail>\nContent-Type: text/html; charset=UTF-8"
	   	);
	}
	
	function groups_send_admin_notification($group_id, $POSTVARS){
		$notify_user_id = returnFieldValue("GROUPS", "NOTIFY_USER_ID", "ID", $group_id);
		$group_name 	= returnFieldValue("GROUPS", "GROUP_NAME", "ID", $group_id);
		$mail_recipient	= returnFieldValue("USERS", "EMAIL", "ID", $notify_user_id);
		if ($notify_user_id == 0 || !$notify_user_id){
			return false;
		} else {
			$mail_subject .= cmsTranslate("groups_newUserSubject").": $group_name";
			$mail_content .= cmsTranslate("groups_newUserContent").": $group_name.\r\n\n";
			$mail_content .= cmsTranslate("groups_newUserData").":\r\n\n";
			foreach ($POSTVARS as $k => $v){
				// if (strstr($k, "cfield___")){
					// $k =  substr($k, 7);
					if ($k != "PASSWORD" && $k != "PASSWORD_2"){
						if (cmsTranslate("subscribeDbFieldNames", $k) != ""){
							$mail_content .= cmsTranslate("subscribeDbFieldNames", $k)." = $v\r\n\n";
						}
					}
				// }
			}
		}
		$from_name = returnFieldValue("SITES", "SITE_NAME", "SITE_ID", $_SESSION["CURRENT_SITE"]);
		$from_maildomain = returnFieldValue("SITES", "EMAIL_DOMAIN", "SITE_ID", $_SESSION["CURRENT_SITE"]);
        $mail = new htmlMimeMail();

		// Change to UFT-8 encoding
		$mail->setTextCharset("UTF-8");
		$mail->setHTMLCharset("UTF-8");
		$mail->setHeadCharset("UTF-8"); 

        $mail->setText($mail_content);
		$mail->setFrom('"'.$from_name.'" <no-reply@'.$from_maildomain.'>');
		$mail->setSubject($mail_subject);
		$mail->setHeader('Reply-To', 'no-reply@'.$from_maildomain);
		$result = $mail->send(array($mail_recipient), 'mail');
	}
	
	function update_user($POSTVARS){
		$user_id = $_SESSION[USERDETAILS][0][ID];
		if (!$_SESSION[LOGGED_IN]){
			return false;
		} else if ($POSTVARS["cfield___USERNAME"] != "" && username_exists($POSTVARS["cfield___USERNAME"], $user_id)){
			return false;
		} else {
			foreach ($POSTVARS as $k => $v){
				if (strstr($k, "cfield___")){
					if ($k != "cfield___PASSWORD_2"){
						$v = strip_tags($v);
						$temp = explode("___", $k);
						$dbfield_name = $temp[1];
						if ($dbfield_name == "DATE_OF_BIRTH"){
							$v = reverseDate($v);
						}
						$updates[] = "$dbfield_name="."'".$v."'";
					}
				}
			}
			$sql = "update USERS set ".implode(", ", $updates)." where ID='$user_id' limit 1";
			mysql_query($sql);
			return true;
		}
	}
	
	function groups_save_user($POSTVARS){
		$sql = "select * from GROUPS where ID='$POSTVARS[group_id]' limit 1";
		$res = mysql_query($sql);
		$groupdata = mysql_fetch_assoc($res);
		$inserts = array();
		foreach ($POSTVARS as $k => $v){
			if (strstr($k, "cfield___")){
				if ($k != "cfield___PASSWORD_2"){
					$v = strip_tags($v);
					$temp = explode("___", $k);
					$dbfield_name = $temp[1];
					if ($dbfield_name == "DATE_OF_BIRTH"){
						$v = reverseDate($v);
					}
					$fields["$dbfield_name"] = "$dbfield_name";
					$values["$dbfield_name"] 	= "'".$v."'";
				}
			}
		}
		if ($POSTVARS["cfield___USERNAME"] != "" && username_exists($POSTVARS["cfield___USERNAME"])){
			return false;
		}
		if (!$fields[USERNAME]){
			$fields["USERNAME"] = "USERNAME";
			if ($fields[EMAIL]){
				$values["USERNAME"] = $values["EMAIL"];
			} else {
				$values["USERNAME"] = "'".randomString(7)."'";
			}
		}
		if (!$fields[PASSWORD]){
			$fields[] = "PASSWORD";
			$values[] = "'".randomString(7)."'";
		}
		if ($groupdata[LANDING_GROUP_ID]){
			$user_sql .= "insert into USERS (".implode(", ", $fields).", TRANSFER_TO_GROUP, CREATED_DATE, UNFINISHED) values (".implode(", ", $values).", '$POSTVARS[group_id]', '".time()."', '1')";
			mysql_query($user_sql);
			$new_user_id = mysql_insert_id();
			$user_group_sql .= "insert into USERS_GROUPS (USER_ID, GROUP_ID) values ('$new_user_id', '$groupdata[LANDING_GROUP_ID]')";
			mysql_query($user_group_sql);
			return $new_user_id;
		} else {
			$user_sql .= "insert into USERS (".implode(", ", $fields).", UNFINISHED) values (".implode(", ", $values).", '1')";
			mysql_query($user_sql);
			$new_user_id = mysql_insert_id();
			$user_group_sql .= "insert into USERS_GROUPS (USER_ID, GROUP_ID) values ('$new_user_id', '$POSTVARS[group_id]')";
			mysql_query($user_group_sql);
			return $new_user_id;
		}
	}
	
	function username_exists($username, $userid=false){
		$sql = "select ID from USERS where USERNAME='$username' ".($userid ? "and ID != '$userid'" : "")." and DELETED='0' and UNFINISHED='0'";
		$res = mysql_query($sql);
		if (mysql_num_rows($res) > 0){
			return true;
		} else {
			return false;
		}
	}
	
	function groups_registration_receipt($group_id){
		$sql = "
			select 
				ID, GROUP_NAME, LANDING_GROUP_ID
			from 
				GROUPS 
			where 
				ID='$group_id' and DELETED='0' and UNFINISHED='0' and REGISTRATION_OPEN='1'
		";
		$res = mysql_query($sql);
		$row = mysql_fetch_assoc($res);
		$html .= "<h1>".cmsTranslate("groups_createHeading")."</h1>";
		$html .= "<p>".cmsTranslate("groups_createContent")."</p>";
		if ($row[LANDING_GROUP_ID] == 0){
			$html .= "<p>".cmsTranslate("groups_userPass").". <a href='$arr_content[baseurl]/index.php?mode=login'>".cmsTranslate("groups_loginNow")."</a>.</p>";
		} else if ($row[LANDING_GROUP_ID] > 0){
			$html .= "<p>".cmsTranslate("groups_mustBeValidated")."</p>";
		}
		$html .= "<p><a href='$arr_content[baseurl]'>".cmsTranslate("groups_gotoFrontpage")."</a>.</p>";
		return $html;
	}

	function groups_pending_receipt($group_id){
		$sql = "
			select 
				ID, GROUP_NAME, LANDING_GROUP_ID
			from 
				GROUPS 
			where 
				ID='$group_id' and DELETED='0' and UNFINISHED='0' and REGISTRATION_OPEN='1'
		";
		$res = mysql_query($sql);
		$row = mysql_fetch_assoc($res);
		$html .= "<h1>".cmsTranslate("groups_createHeading").": ".$row[GROUP_NAME]."</h1>";
		$html .= "<p>Systemet har sendt en mail til dig. I denne mail er der et link, som du skal klikke på for at bekræfte din e-mail-adresse. Først herefter vil du blive oprettet som bruger.</p>";
		$html .= "<p><a href='$arr_content[baseurl]'>".cmsTranslate("groups_gotoFrontpage")."</a>.</p>";
		return $html;
	}
	
	function groups_registration_form($group_id, $mode="REGISTER"){
		$sql = "
			select 
				ID, GROUP_NAME 
			from 
				GROUPS 
			where 
				ID='$group_id' and DELETED='0' and UNFINISHED='0' and ".($mode=="REGISTER" ? "REGISTRATION_OPEN='1'" : "EDITING_OPEN='1'")."
		";
		$res = mysql_query($sql);
		$row = mysql_fetch_assoc($res);
		if ($mode=="REGISTER" && !$row[ID]){
			$html .= "ERROR_NOT_AVAILABLE";
		} else if ($mode=="REGISTER" && $row[ID] || $mode=="EDIT"){
			if ($mode == "REGISTER"){
				$html .= "<h1>".cmsTranslate("groups_createHeading")."</h1>";
                $html .= "<p>".cmsTranslate("groups_createSubheading")."</p>";
			} else if ($mode == "EDIT"){
				$html .= "<h1>".cmsTranslate("groups_editDetails")."</h1>";
			}
			$db_field_data = groups_formfields($group_id);
			$html .= "
				<script type='text/javascript'>
					function checkMail(x){
						var filter  = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
						if (filter.test(x)) {
							return true;
						} else {					
							return false;
						}
					}
					function validateDate(v) {
					    var RegExPattern = /^((((0?[1-9]|[12]\d|3[01])[\.\-\/](0?[13578]|1[02])[\.\-\/]((1[6-9]|[2-9]\d)?\d{2}))|((0?[1-9]|[12]\d|30)[\.\-\/](0?[13456789]|1[012])[\.\-\/]((1[6-9]|[2-9]\d)?\d{2}))|((0?[1-9]|1\d|2[0-8])[\.\-\/]0?2[\.\-\/]((1[6-9]|[2-9]\d)?\d{2}))|(29[\.\-\/]0?2[\.\-\/]((1[6-9]|[2-9]\d)?(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)|00)))|(((0[1-9]|[12]\d|3[01])(0[13578]|1[02])((1[6-9]|[2-9]\d)?\d{2}))|((0[1-9]|[12]\d|30)(0[13456789]|1[012])((1[6-9]|[2-9]\d)?\d{2}))|((0[1-9]|1\d|2[0-8])02((1[6-9]|[2-9]\d)?\d{2}))|(2902((1[6-9]|[2-9]\d)?(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)|00))))$/;
					    var errorMessage = '';
					    if ((v.match(RegExPattern)) && (v!='')) {
							return true;
					    } else {
							return false;
					    } 
					}
					function verify_user(){
						F = document.register_form;
						".$db_field_data["VERIFY_JAVASCRIPT"]."
						if (F.cfield___EMAIL){
							if (!checkMail(F.cfield___EMAIL.value)){
								alert('".cmsTranslate("NewsletterErrorEmailNotValid")."');
								return false;
							}
						}
						if (F.cfield___DATE_OF_BIRTH){
							if (!validateDate(F.cfield___DATE_OF_BIRTH.value)){
								alert('".cmsTranslate("NewsletterErrorDateNotValid")."');
								return false;
							}
						}
						return true;
					}
				</script>
			";
			$html .= "<form method='post' action='' name='register_form' id='register_form'>";
			$html .= "<input type='hidden' name='group_id' value='$group_id' />"; 
			$html .= "<input type='hidden' name='action' value='".strtolower($mode)."' />"; 
			$html .= "<input type='hidden' name='ticket' value='".md5("1nstansFlyveMa5kine".$group_id)."' />"; 
			$html .= $db_field_data["HTML"];
			$html .= "
				<div class='generatedFormButtonBar'>
					<input type='button' name='newsletter_break_button' onclick='location=\"index.php\"' value='".cmsTranslate("NewsletterButtonCancel")."' />
					<input type='submit' name='newsletter_subscribe_button' class='important_button' onclick='return verify_user()' value='".cmsTranslate("groups_createuser")."' />
				</div>
			";
			$html .= "</form>";
		} 
		return $html;
	}
	
	function groups_formfields($group_id){
		if ($_SESSION[LOGGED_IN]){
			$sql = "select * from USERS where ID='".$_SESSION[USERDETAILS][0][ID]."'";
			$result = mysql_query($sql);
			$userdata = mysql_fetch_assoc($result);
			$all_fields = return_user_all_fields($_SESSION[USERDETAILS][0][ID]);
			$sql = "
				select
					NF.FIELD_NAME, NF.ID, MAX(GF.MANDATORY) as MANDATORY
				from 
					NEWSLETTER_FORMFIELDS NF, GROUPS_FORMFIELDS GF
				where
					NF.ID in (".implode(", ", $all_fields).") and
					GF.FIELD_ID=NF.ID
				GROUP BY
					NF.FIELD_NAME
				order by
					NF.POSITION asc
			";
		} else {
			$sql = "
				select 
					GF.MANDATORY, NF.FIELD_NAME, NF.ID, G.GROUP_NAME
				from 
					GROUPS_FORMFIELDS GF, NEWSLETTER_FORMFIELDS NF, GROUPS G
				where 
					GF.GROUP_ID='$group_id' and GF.FIELD_ID=NF.ID and
					G.ID='$group_id'
				order 
					by NF.TABLE_NAME asc, NF.POSITION asc
			";
		}
		$result = mysql_query($sql);
		if ($_POST[username_exists]){
			$html .= "<div class='usermessage_error'>".cmsTranslate("groups_usernameExists")." \"$_POST[cfield___USERNAME]\". ".cmsTranslate("groups_usernameSelect").".</div>";
		}
		if ($_POST[updated_okay]){
			$html .= "<div class='usermessage_ok'>".cmsTranslate("groups_detailsUpdated").".</div>";
		}
		while ($row = mysql_fetch_assoc($result)){
			$html .= "
				<div class='generatedFormFieldHeader'>".cmsTranslate("subscribeDbFieldNames", $row[FIELD_NAME])." ".($row["MANDATORY"] == 1 ? "(*)" : "")."</div>
				<div class='generatedFormFieldContainer'>
			";
			if ($row[FIELD_NAME] == "PASSWORD"){
				$html .= "<input type='password' value='".($_POST ? $_POST["cfield___$row[FIELD_NAME]"] : $userdata[$row[FIELD_NAME]])."' class='generatedFormField' name='cfield___".$row[FIELD_NAME]."' size='50'>";
				$html .= "<input type='password' value='".($_POST ? $_POST["cfield___$row[FIELD_NAME]"] : $userdata[$row[FIELD_NAME]])."' class='generatedFormField' name='cfield___".$row[FIELD_NAME]."_2' size='50'>";
			} else if ($row[FIELD_NAME] == "CV") {
				$html .= "<textarea class='generatedFormField' name='cfield___".$row[FIELD_NAME]."' cols='50' rows='10'>".($_POST ? $_POST["cfield___$row[FIELD_NAME]"] : $userdata[$row[FIELD_NAME]])."</textarea>";
			} else {
				$html .= "<input type='text' value='".($_POST ? $_POST["cfield___$row[FIELD_NAME]"] : $userdata[$row[FIELD_NAME]])."' class='generatedFormField' name='cfield___".$row[FIELD_NAME]."' size='50'>";			
			}	
			$html .= "</div>
			";
			if ($row["MANDATORY"] == 1){
				$js .= "
					if (F.cfield___".$row[FIELD_NAME].".value == ''){
						alert('".cmsTranslate("NewsletterErrorFillout")." \"".cmsTranslate("subscribeDbFieldNames", $row[FIELD_NAME])."\".');
						return false;
					}
				";
			}
			if ($row[FIELD_NAME] == "PASSWORD"){
				$js .= "
					if (F.cfield___".$row[FIELD_NAME].".value != F.cfield___".$row[FIELD_NAME]."_2.value){
						alert('".cmsTranslate("groups_pwTwice").".');
						return false;
					}
				";
			}
		}
		return array("HTML" => $html, "VERIFY_JAVASCRIPT" => $js);
	}
	
	function return_user_all_fields($user_id){
		$sql = "
			select 
				distinct GF.FIELD_ID
			from 
				USERS_GROUPS UG, GROUPS_FORMFIELDS GF, NEWSLETTER_FORMFIELDS NF
			where 
				UG.USER_ID='$user_id' and
				GF.GROUP_ID=UG.GROUP_ID and
				NF.ID=GF.FIELD_ID
		";
		$res = mysql_query($sql);
		$field_ids = array();
		while ($row = mysql_fetch_assoc($res)){
			$field_ids[] = $row[FIELD_ID];
		}
		return $field_ids;
	}

?>
