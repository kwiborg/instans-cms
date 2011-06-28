<?php
function showNewsletterArchive() {
	if ($_GET[newsletterid] != "") {
		$show_archive = returnFieldValue("NEWSLETTER_TEMPLATES", "SHOW_IN_NEWSARCHIVE", "ID", $_GET[newsletterid]);
		if ($show_archive == 0) {
			return;
		}
	}
	$html = "<h1>Nyhedsbrev arkiv</h1>";
			$sql = "select
						NH.TEMPLATE_ID,
						NH.NEWSLETTER_ID,
						(SELECT UNIX_TIMESTAMP(SENDOUT_COMPLETETIME)
							FROM 
								NEWSLETTER_HISTORY
							WHERE 
								TEMPLATE_ID = NH.TEMPLATE_ID
							AND 
								NEWSLETTER_ID = NH.NEWSLETTER_ID
							ORDER BY 
								Date(SENDOUT_COMPLETETIME) DESC,
								Time( SENDOUT_COMPLETETIME ) DESC 
							LIMIT 1
						) as SENDOUTTIME,
						NH.SENDOUT_SUBJECT as SENDOUT_TITLE,
						N.ARCHIVE_TITLE as ARCHIVE_TITLE,
						N.TITLE as NEWSLETTER_TITLE,
						NT.OPEN_FOR_SUBSCRIPTIONS,
						NT.TITLE as TEMPLATE_TITLE,
						NT.LANGUAGE_ID
					from
						NEWSLETTER_TEMPLATES NT,
						NEWSLETTER_HISTORY NH,
						NEWSLETTERS N						
					where
						NH.SENDOUT_COMPLETETIME > 0 and
						NT.SHOW_IN_NEWSARCHIVE = 1 and
						NT.DELETED = 0 and
						N.DELETED = 0 and
						NT.ID = NH.TEMPLATE_ID and
						NH.NEWSLETTER_ID = N.ID";
		if ($_GET[newsletterid] != "") {
			$sql .= "	and NH.TEMPLATE_ID = $_GET[newsletterid]";
		}
		$sql .= "	group by
						NH.NEWSLETTER_ID
					order by
						NT.TITLE asc,
						Date(NH.SENDOUT_COMPLETETIME) desc,
						Time(NH.SENDOUT_COMPLETETIME) desc
			";
			$res = mysql_query($sql);
			if (mysql_num_rows($res) > 0) {
				$prev_template = "";
				$html .= "<table class='newsletter_index_table' cellpadding='0' cellspacing='0'>";
				while ($row = mysql_fetch_assoc($res)) {
					if ($prev_template != $row[TEMPLATE_ID]) {
						$html .= "<tr><td colspan='2'><h2>$row[TEMPLATE_TITLE] ";
						if ($row[OPEN_FOR_SUBSCRIPTIONS] == 1) {
							$html .= "<span class='newsletter_subscribe'>(<a href='index.php?mode=newsletter&amp;action=subscribe&amp;newsletterid=$row[TEMPLATE_ID]' title='".cmsTranslate("NewsletterSubscribe")."'>".cmsTranslate("NewsletterSubscribe")."</a>)</span>";
						}
						$html .= "</h2></td></tr>";
						
						$prev_template = $row[TEMPLATE_ID];
					}
					$html .= "<tr><td class='newsletter_sendoutdate'>";
					$html .= date("j/n Y", $row[SENDOUTTIME]);
					$html .= "</td><td>";
					if ($row[ARCHIVE_TITLE] == "") {
						$show_title = $row[NEWSLETTER_TITLE];
					} else {
						$show_title = $row[ARCHIVE_TITLE]; 
					}
					global $cmsDomain;
					$html .= "<a href='".returnBASE_URL($_SESSION[SELECTED_SITE]).returnSITE_PATH($_SESSION[SELECTED_SITE])."/includes/shownewsletter.php?newsletterid=$row[NEWSLETTER_ID]' title='$show_title'>$show_title</a></td></tr>";
				}
				$html .= "</table>";					

			} else {
				$html .= "<p>".cmsTranslate("NewsletterNone")."</p>";
			}
	return $html;
}



function returnSubscriberOptouts($subscriber_id, $template_id) {
	$arr_optoutcats = array();
	$sql_subscriberoptouts = "select NCO.CATEGORY_ID	
			from
				NEWSLETTER_CATEGORIES_OPTOUT NCO
			where
				NCO.USER_ID = '$subscriber_id' and
				NCO.TEMPLATE_ID = '$template_id'
			";
	$optoutcategories = mysql_query($sql_subscriberoptouts);
	if (mysql_num_rows($optoutcategories) > 0) {
		while ($optoutcat = mysql_fetch_array($optoutcategories)) {
			$arr_optoutcats[] = $optoutcat[CATEGORY_ID];
		}
	}
	return $arr_optoutcats;
}

	function buildUnsubscribeForm($newsletterId){
		$sql = "
			select TITLE, SUBSCRIPTIONPAGE_TEXTTOP, SUBSCRIPTIONPAGE_TEXTBOTTOM  
			from NEWSLETTER_TEMPLATES 
			where ID='$newsletterId' and DELETED='0'
		";
		$result = mysql_query($sql);
		if (!mysql_num_rows($result)){
			$html .= newsletter_show_usermessage(cmsTranslate("NewsletterErrorNotFound")." (id=$newsletterId).", "message_error");
			return $html;
		}
		$row = mysql_fetch_array($result);
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
				function verify_subscriber(){
					F = document.unsubscribeForm;
					if (!checkMail(F.subscriber_email.value)){
						alert('".cmsTranslate("NewsletterErrorEmailNotValid")."');
						return false;
					}
					return true;
				}
			</script>
		";
		$predefined_email = (
			($_GET["uid"] && $_GET["c"] == md5($_GET["uid"]."1nstansFlyvemaskine")) ?
			returnFieldValue("USERS", "EMAIL", "ID", $_GET["uid"]) : 
			""
		);
		$html .= "<form method='post' action='' name='unsubscribeForm'>";
		$html .= "<input type='hidden' name='t_id' value='$newsletterId' />"; 
		$html .= "<input type='hidden' name='action' value='unsubscribe' />"; 
		$html .= "<h1>".cmsTranslate("NewsletterUnsubscribe").": ".$row[TITLE]."</h1>";
		$html .= "<p>".cmsTranslate("NewsletterInstructionsUnsubscribe")." <strong>".$row[TITLE]."</strong>.</p>";
		$html .= "<div class='generatedFormFieldHeader'>".cmsTranslate("NewsletterYourEmail").":</div>";
		$html .= "
			<div class='generatedFormFieldContainer'>
	  			<input type='text' value='".$predefined_email."' class='generatedFormField' name='subscriber_email' size='50'>
	 		</div>
		";
		$html .= "
			<div class='generatedFormButtonBar'>
				<input type='button' name='newsletter_break_button' onclick='location=\"index.php\"' value='".cmsTranslate("NewsletterButtonCancel")."' />
				<input type='submit' name='newsletter_subscribe_button' class='important_button' onclick='return verify_subscriber()' value='".cmsTranslate("NewsletterButtonUnsubscribe")."' />
			</div>
		";
		$html .= "</form>";
		return $html;
	}
	
	function buildSubscribeForm($newsletterId){
		$sql = "
			select TITLE, SUBSCRIPTIONPAGE_TEXTTOP, SUBSCRIPTIONPAGE_TEXTBOTTOM  
			from NEWSLETTER_TEMPLATES 
			where ID='$newsletterId' and DELETED='0' and OPEN_FOR_SUBSCRIPTIONS='1'
		";
		$result = mysql_query($sql);
		if (!mysql_num_rows($result)){
			$html .= newsletter_show_usermessage(cmsTranslate("NewsletterErrorNotFound")." (id=$newsletterId).", "message_error");
			return $html;
		}
		$row = mysql_fetch_array($result);
		$db_field_data = return_db_fields($newsletterId);
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
				function verify_subscriber(){
					F = document.subscribeForm;
					if (!checkMail(F.subscriber_email.value)){
						alert('".cmsTranslate("NewsletterErrorEmailNotValid")."');
						return false;
					}
					".$db_field_data["VERIFY_JAVASCRIPT"]."
					return true;
				}
			</script>
		";
		$html .= "<form method='post' action='' name='subscribeForm'>";
		$html .= "<input type='hidden' name='t_id' value='$newsletterId' />"; 
		$html .= "<input type='hidden' name='action' value='subscribe' />"; 
		$html .= "<h1>".cmsTranslate("NewsletterSubscribe").": ".$row[TITLE]."</h1>";
		$html .= "<p>".$row[SUBSCRIPTIONPAGE_TEXTTOP]."</p>";
		$html .= "<div class='generatedFormFieldHeader'>".cmsTranslate("NewsletterYourEmail")." (*)</div>";
		$html .= "
			<div class='generatedFormFieldContainer'>
	  			<input type='text' value='$_REQUEST[email]' class='generatedFormField' name='subscriber_email' size='50'>
	 		</div>
		";
		$html .= $db_field_data["HTML"];
		$html .= returnCategoryGroups($newsletterId);
		$html .= "<p>".$row[SUBSCRIPTIONPAGE_TEXTBOTTOM]."</p>";
		$html .= "
			<div class='generatedFormButtonBar'>
				<input type='button' name='newsletter_break_button' onclick='location=\"index.php\"' value='".cmsTranslate("NewsletterButtonCancel")."' />
				<input type='submit' name='newsletter_subscribe_button' class='important_button' onclick='return verify_subscriber()' value='".cmsTranslate("NewsletterButtonSubscribe")."' />
			</div>
		";
		$html .= "</form>";
		return $html;
	}
	
	function return_db_fields($template_id){
		$sql = "
			select NTF.MANDATORY, NF.FIELD_NAME, NF.ID
			from NEWSLETTER_TEMPLATES_FORMFIELDS NTF, NEWSLETTER_FORMFIELDS NF 
			where NTF.TEMPLATE_ID='$template_id' and NTF.FIELD_ID=NF.ID and TEMPLATETAG_ONLY='0'
			order by NF.POSITION asc
		";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)){
			$html .= "
				<div class='generatedFormFieldHeader'>".cmsTranslate("subscribeDbFieldNames", $row[FIELD_NAME])." ".($row["MANDATORY"] == 1 ? "(*)" : "")."</div>
				<div class='generatedFormFieldContainer'>
					<input type='text' value='' class='generatedFormField' name='cfield___".$row[FIELD_NAME]."' size='50'>
				</div>
			";
			if ($row["MANDATORY"] == 1){
				$js .= "
					if (F.cfield___".$row[FIELD_NAME].".value == ''){
						alert('".cmsTranslate("NewsletterErrorFillout")." \"".cmsTranslate("subscribeDbFieldNames", $row[FIELD_NAME])."\".');
						return false;
					}
				";
			}
		}
		return array("HTML" => $html, "VERIFY_JAVASCRIPT" => $js);
	}
	
	function returnCategoryGroups($newsletterId, $user_id=""){
		$sql = "select ID, TEMPLATE_ID, CATEGORYGROUP_ID from NEWSLETTER_TEMPLATES_CATEGORYGROUPS where TEMPLATE_ID='$newsletterId'";
		$result = mysql_query($sql);
		if (mysql_num_rows($result)){
			if ($user_id != "") {
				$arr_optoutcats = returnSubscriberOptouts($user_id, $newsletterId);
			}
			$html .= "<p>".cmsTranslate("NewsletterInstructionsInterests")."</p>";
			while ($row = mysql_fetch_array($result)){
				$html .= "<div class='generatedFormFieldHeader'>".returnFieldValue("NEWSLETTER_CATEGORYGROUPS", "NAME", "ID", $row[CATEGORYGROUP_ID])."</div>";
				$html .= returnCategoriesFromGroup($row[CATEGORYGROUP_ID], $arr_optoutcats);
			}
		}
		return $html;	
	}
	
	function returnCategoriesFromGroup($groupId, $arr_optoutcats=""){
		$sql = "select ID, NAME from NEWSLETTER_CATEGORIES where GROUP_ID='$groupId' and DELETED='0' order by NAME asc";
		$result = mysql_query($sql);
		$html .= "<div id='newsletter_categories_container'>";
		while ($row = mysql_fetch_array($result)){
			$checked = "checked ";
			if (is_array($arr_optoutcats)) {
				if (in_array($row[ID], $arr_optoutcats)) {
					// User has opted out
					$checked = "";
				} else {
					$checked = "checked ";
				}
			}
			$html .= "<input type='checkbox' $checked name='subscribeto_category_$row[ID]' />&nbsp;".$row[NAME]."<br/>";
		}
		$html .= "</div>";
		return $html;
	}
	
function newsletter_show_usermessage($errorcode, $type=""){
		// Allowed types: message_ok, message_neutral, message_error
		if ($type == "") {
			if (strstr($errorcode, "OKAY_")) {
				$type = "message_ok";
			} elseif (strstr($errorcode, "ERROR_")) {
				$type = "message_error";
			} else {
				$type = "message_neutral";
			}
		}

		switch ($errorcode) {
			case "OKAY_subscribed":
				$message = cmsTranslate("Newsletter".$errorcode);
			break;
			case "OKAY_subscribedconfirm":
				$message = cmsTranslate("Newsletter".$errorcode);
			break;
			case "OKAY_resubscribed":
				$message = cmsTranslate("Newsletter".$errorcode);
			break;
			case "OKAY_resubscribedconfirm":
				$message = cmsTranslate("Newsletter".$errorcode);
			break;
			case "OKAY_unsubscribed":
				$message = cmsTranslate("Newsletter".$errorcode);
			break;
			case "OKAY_unsubscription_canceled":
				$message = cmsTranslate("Newsletter".$errorcode);
			break;
			case "OKAY_optout":
				$message = cmsTranslate("Newsletter".$errorcode);
			break;
			case "OKAY_categoriesupdated":
				$message = cmsTranslate("Newsletter".$errorcode);
			break;
			case "OKAY_proofapproved":
				$message = cmsTranslate("Newsletter".$errorcode);
			break;
			case "OKAY_confirmed":
				$message = cmsTranslate("Newsletter".$errorcode);
			break;
			case "ERROR_subscriptionnotconfirmed":
				$message = cmsTranslate("Newsletter".$errorcode);
			break;
			case "ERROR_unsubscribenotcancelled":
				$message = cmsTranslate("Newsletter".$errorcode);
			break;
			case "ERROR_alreadysubscribed":
				$message = cmsTranslate("Newsletter".$errorcode);
			break;
			case "ERROR_alreadysubscribed_unconfirmed":
				$message = cmsTranslate("Newsletter".$errorcode);
			break;
			case "ERROR_alreadyunsubscribed":
				$message = cmsTranslate("Newsletter".$errorcode);
			break;
			case "ERROR_notsubscribed":
				$message = cmsTranslate("Newsletter".$errorcode);
			break;
			case "ERROR_notconfirmed":
				$message = cmsTranslate("Newsletter".$errorcode);
			break;
			case "ERROR_could_not_verify":
				$message = cmsTranslate("Newsletter".$errorcode);
			break;
			default:
				$message = $errorcode;
			break;
		}			
		$message = "<div class='newsletter_usermessage $type'>$message</div>";
		return $message;
	}
	
	switch ($_GET["action"]){
/*
		case "makeusersq37290301787208931789310237192":
			$template_id = 29;
			$count = 999;
			$startat = 1001;
	
			for ($counter = $startat; $counter < $startat+$count; $counter++) {			
				$email = $counter."@instans.dk";
				// build array with userdata
				$arr_userdata = array(
					"cfield___FIRSTNAME" => "First$counter",
					"cfield___LASTNAME" => "Last$counter",
					"cfield___COMPANY" => "$counter A/S"
				);
				$user_id = newsletter_insert_user($email, $arr_userdata);
				$status = newsletter_subscription_engine($user_id, $template_id);
				echo $status;
				$sql = "update NEWSLETTER_SUBSCRIPTIONS set CONFIRMED = '1' where USER_ID = '$user_id'";
				$sql = "update USERS set EMAIL_VERIFIED = '1' where ID = '$user_id'";
				if (mysql_query($sql)) {
					echo " user $user_id creatd <br/>";
				}
			}
			break;
*/
		case "subscribe":
			if ($_GET["newsletterid"]){
				$html .= buildSubscribeForm($_GET["newsletterid"]);
			} else {
				$html .= newsletter_show_usermessage("Fejl: Der skal angives et ID på et nyhedsbrev.", "message_error");
			}
		break;
		case "unsubscribe":
			if ($_GET["newsletterid"]){
				$html .= buildUnsubscribeForm($_GET["newsletterid"]);
			} else {
				$html .= newsletter_show_usermessage("Fejl: Der skal angives et ID på et nyhedsbrev.", "message_error");
			}
		break;
		case "usermessage":
			$html .= newsletter_show_usermessage($_GET["statuscode"]);
			$html .= showNewsletterArchive();
		break;
		case "updateinterestgroups":
			// Check key
			$key = md5($_GET[uid]."1nstansFlyvemaskine");
			if ($_GET[c] == $key) {
				global $warning;
				if ($warning != "") {
					$html .= newsletter_show_usermessage($warning, "message_neutral");
				}
				$html .= "<h1>".cmsTranslate("NewsletterInstructionsInterestsShort")."</h1>";
				$html .= "<form action='index.php?mode=newsletter&action=update_categories_from_email&newsletterid=$_GET[newsletterid]&uid=$_GET[uid]&c=$_GET[c]' method='post'>";
				$html .= returnCategoryGroups($_GET[newsletterid],$_GET[uid]);
				$html .= "<div class='newsletter_knapbar'><input type='submit' value='".cmsTranslate("NewsletterButtonUpdate")."' name='insite_update_categories' /></div>
							</form>";
								
			} else {
				echo newsletter_show_usermessage("Fejl: Du har ikke adgang til at opdatere interessegrupper (ugyldig nøgle).", "message_error");
			}
		break;
		/// DEFAULT: SHOW NEWSLETTER ARCHIVE
		default:
			$html = showNewsletterArchive();
			break;

	}
	echo $html;
?>