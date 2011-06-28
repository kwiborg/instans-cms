<?php
function update_newsletter_urls($str_html, $user_id, $newsletter_id, $language) {
	// Fix urls for newsletter

	// Get site_id from newsletter_template
	$sql = "select
				SITE_ID
			from
				NEWSLETTERS N,
				NEWSLETTER_TEMPLATES NT
			where
				N.ID = '$newsletter_id' and
				N.TEMPLATE_ID = NT.ID";
	$res = mysql_query($sql);
	$site_id = mysql_result($res,0);

	// Add base, if missing
	$base = returnBASE_URL($site_id).returnSITE_PATH($site_id)."/";

//	$base_4_regex = str_replace("/", "\/", $base)."index.php\?"; // escaped for regex matching
	$base_4_regex = str_replace("/", "\/", $base); // escaped for regex matching
	$str_html = preg_replace("/(href|src)=\"(?!http|ftp|https|mailto)([^\"]*)\"/", "$1=\"$base\$2\"", $str_html);
	// Add tracking device
	// First, the clicked url - encoded and add target attribute to links
	$str_html = preg_replace_callback("/(href)=\"($base_4_regex)([^\"]*)\"/", "update_newsletter_urls_callback", $str_html);

	// Second, extra track pars: openkey, user_id, newsletter_id (and add language attribute to links - not for tracking)
	// $extra_pars = "&amp;lang=$language"; // 2007-08-22 removed, no effect overridden two lines below!
	$openkey = md5($user_id."1nstansFlyvemaskine");

	$extra_pars = "&amp;openkey=$openkey&amp;nid=$newsletter_id&amp;uid=$user_id";
	
	$str_html = preg_replace("/(href)=\"($base_4_regex)([^\"]*)\"/", "$1=\"\$2\$3$extra_pars\"", $str_html);

	// Finally add tracking parameters to webbug_image newsletter_open.php
	$track_image = "includes/images/newsletter_open.php?opened=1$extra_pars";
	$str_html = str_replace("newsletter_open.php", $track_image, $str_html);

	return $str_html;
	
}

/*
2009-02-24: Replaced by new version (above) contributed by Kristian Wiborg / Intern1
function update_newsletter_urls($str_html, $user_id, $newsletter_id, $language) {
	// Fix urls for newsletter

	// Get site_id from newsletter_template
	$sql = "select 
				SITE_ID
			from 
				NEWSLETTERS N, 
				NEWSLETTER_TEMPLATES NT 
			where 
				N.ID = '$newsletter_id' and 
				N.TEMPLATE_ID = NT.ID";
	$res = mysql_query($sql);
	$site_id = mysql_result($res,0);		

	// Add base, if missing
	$base = returnBASE_URL($site_id).returnSITE_PATH($site_id)."/";

	$base_4_regex = str_replace("/", "\/", $base)."index.php\?"; // escaped for regex matching
	$str_html = preg_replace("/(href|src)=\"(?!http|ftp|https)([^\"]*)\"/", "$1=\"$base\$2\"", $str_html);
	// Add tracking device
	// First, the clicked url - encoded and add target attribute to links
	$str_html = preg_replace_callback("/(href)=\"($base_4_regex)([^\"]*)\"/", "update_newsletter_urls_callback", $str_html);

	// Second, extra track pars: openkey, user_id, newsletter_id (and add language attribute to links - not for tracking)
//	$extra_pars = "&amp;lang=$language"; // 2007-08-22 removed, no effect overridden two lines below!
	$openkey = md5($user_id."1nstansFlyvemaskine");
	$extra_pars = "&amp;openkey=$openkey&amp;nid=$newsletter_id&amp;uid=$user_id";

	$str_html = preg_replace("/(href)=\"($base_4_regex)([^\"]*)\"/", "$1=\"\$2\$3$extra_pars\"", $str_html);

	// Finally add tracking parameters to webbug_image newsletter_open.php
	$track_image = $base."includes/images/newsletter_open.php?opened=1$extra_pars";
	$str_html = str_replace("newsletter_open.php", $track_image, $str_html);

	return $str_html;
}
*/

function update_newsletter_urls_callback($matches) {
	return "$matches[1]=\"$matches[2]$matches[3]". (eregi('\?',$matches[3])?'&amp;':'?') ."clickedlink=".rawurlencode($matches[2]).rawurlencode($matches[3])."\" target=\"_blank\"";
}


/*
2009-02-24: Replaced by new version (above) contributed by Kristian Wiborg / Intern1
function update_newsletter_urls_callback($matches) {
//	print_r($matches);
  return "$matches[1]=\"$matches[2]$matches[3]&amp;clickedlink=".rawurlencode($matches[2]).rawurlencode($matches[3])."\" target=\"_blank\"";
}
*/

function newsletter_sendout_cleanup($history_id) {
	// Function to update NEWSLETTER_HISTORY with COMPLETETIME + MAIL CONTENT

	$newsletter_id = returnFieldValue("NEWSLETTER_HISTORY", "NEWSLETTER_ID", "ID", $history_id);

	// Get newsletter main data (headertext etc)
	$arr_maindata = returnNewsletterMaindataArray($newsletter_id);

	// Get newsletter items
	$arr_items = returnNewsletterItemArray($newsletter_id, $arr_maindata[IMAGES_DISPLAY]);
	
	// Get mail contents
	$arr_content = newsletter_build($newsletter_id, "raw", "da", $arr_maindata, $arr_items, array());
	
	$sql = "update
				NEWSLETTER_HISTORY
			set
				SENDOUT_COMPLETETIME = NOW(),
				SENDOUT_HTML = '".addslashes($arr_content[0])."',
				SENDOUT_PLAINTEXT = '".addslashes($arr_content[1])."'
			where
				ID = '$history_id'";
	if (mysql_query($sql)) {
		return true;
	} else {
		return false;
	}	
}

function newsletter_sendout_do($history_id, $batchsize) {
	// Creates an array with $batchsize recipients and mails the newsletter to them
	// First, get recipients
	$sql = "select 
				U.ID, 
				U.EMAIL,
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
				NEWSLETTER_HISTORY NH, NEWSLETTER_HISTORY_MAILLOG NHM, USERS U
			where
				NH.ID = '$history_id' and
				NH.ID = NHM.HISTORY_ID and
				NHM.USER_ID = U.ID and
				U.DELETED = '0' and
				U.UNFINISHED = '0' and
				NHM.SENDOUT_COMPLETED = '0'
			order by
				U.FIRSTNAME desc
			limit $batchsize";
	$arr_recipients = array();
	$res = mysql_query($sql);

	// On ZERO returned rows: return COMPLETE
	if (mysql_num_rows($res) == 0) {
		return array("COMPLETE", $arr_recipients);	
	}

	// Build recipients array
	$template_id = returnFieldValue("NEWSLETTER_HISTORY", "TEMPLATE_ID", "ID", $history_id);
	while ($recipient = mysql_fetch_assoc($res)) {
		$arr_recipients[] = $recipient;
		// Get subscribers opt-out
		// Append optouts to recipients array
		$arr_optoutcats = returnSubscriberOptouts($recipient[ID], $template_id);
		$arr_this = count($arr_recipients)-1;
		$arr_recipients[$arr_this][OPTOUTS] = $arr_optoutcats;
	}

	// Send mail to recipients in array
	$newsletter_id = returnFieldValue("NEWSLETTER_HISTORY", "NEWSLETTER_ID", "ID", $history_id);
	// NOTE LAST PARAMETER false = no emails
//	if (!sendNewsletter($newsletter_id, $arr_recipients, "mail", $history_id, false)) {
	if (!sendNewsletter($newsletter_id, $arr_recipients, "mail", $history_id, true)) {
		return false;
	}

	// Check array to determine what to do next
	// If fewer returned rows than $batchsize, we've got them all: return COMPLETE
	// If not return CONTINUE
	if (count($arr_recipients) < $batchsize) {
		$donext = "COMPLETE";
	} else {
		$donext = "CONTINUE";
	}			
	return array($donext,$arr_recipients);	
}

function build_newsletter_recipientlist($history_id) {
	// Create entry in NEWSLETTER_HISTORY for each recipient
	// Returns true/false on success/failure

	// First check if this history_id already has entries in NEWSLETTER_HISTORY_MAILLOG
	$sql = "select count(ID) from NEWSLETTER_HISTORY_MAILLOG where HISTORY_ID = '$history_id'";
	$res = mysql_query($sql);
	if (mysql_result($res,0) > 0) {
		return false;
	}
	
	// Ok, no record of previous records in NEWSLETTER_HISTORY_MAILLOG
	// Find recipients
	$sql = "select
				NEWSLETTER_ID, TEMPLATE_ID 
			from 
				NEWSLETTER_HISTORY
			where
				ID = '$history_id'";
	$res = mysql_query($sql);
	$h = mysql_fetch_array($res);
	$arr_recipients = getNewsletterRecipients("array", $h[NEWSLETTER_ID], $h[TEMPLATE_ID]);

	// Update NEWSLETTER_HISTORY with correct recipient count
	$no_recipients = count($arr_recipients);
	if ($no_recipients > 0) {
		$sql = "update NEWSLETTER_HISTORY set NO_RECIPIENTS = '$no_recipients' where ID = '$history_id'";
		mysql_query($sql);
	} else {
		return false;
	}

	// Create record in NEWSLETTER_HISTORY_MAILLOG for each recipient
	foreach ($arr_recipients as $arr_recipient) {
			
		$sql = "insert into
					NEWSLETTER_HISTORY_MAILLOG
				(
					HISTORY_ID,
					USER_ID
				)
				values
				(
					'$history_id',
					'$arr_recipient[ID]'
				)";
		$res = mysql_query($sql);
	}
	if ($res) {
		return true;
	} else {
		return false;
	}
}

function new_newsletter_history($newsletter_id) {
	// Creates entry in NEWSLETTER_HISTORY
	// Returns id of new entry on success
	// Returns false on failure
	$arr_maindata = returnNewsletterMaindataArray($newsletter_id);
	$template_id = returnNewsletterTemplateId($newsletter_id);
	$user_id = $_SESSION[CMS_USER][USER_ID];
	$sql = "insert into
				NEWSLETTER_HISTORY 
			(
				NEWSLETTER_ID,
				TEMPLATE_ID,
				USER_ID,
				SENDOUT_BEGINTIME,
				SENDOUT_SUBJECT
			)
			values
			(
				'$newsletter_id',
				'$template_id',
				'$user_id',
				NOW(),
				'$arr_maindata[TITLE]'
			)";
	$res = mysql_query($sql);
	return mysql_insert_id();
}

function sendForm($newsletter_id, $template_id) {
	// Function to output send form
	$html .= "<form action=''>
				<input id='sendout_status' type='hidden' value='CONTINUE' />";
	$html .= "<h1>Udsend nyhedsbrev</h1>";
	

	// First, check previous sendouts
	$sql = "select ID, USER_ID, NO_RECIPIENTS, UNIX_TIMESTAMP(SENDOUT_BEGINTIME) as SENDOUT_BEGINTIME, UNIX_TIMESTAMP(SENDOUT_COMPLETETIME) as SENDOUT_COMPLETETIME, SENDOUT_SUBJECT from NEWSLETTER_HISTORY where NEWSLETTER_ID = '$newsletter_id'";
	$res = mysql_query($sql);
	if (mysql_num_rows($res) > 0) {
		// Newsletter has been sent out before
		$html .= "<div class='feltblok_header'>Nyhedsbrev udsendt tidligere</div>
				<div class='feltblok_wrapper'>";
		$html .= "<p>Du er i færd med at udsende et nyhedsbrev, som tidligere er udsendt. Derfor bør du overveje om du ønsker at udsende nyhedsbrevet igen. Herunder kan du se en liste med tidligere udsendelser.</p>";
		
		$html .= "<table class='oversigt'>
					<tr class='trtop'>
						<td class='kolonnetitel'>Titel</td>
						<td class='kolonnetitel'>Udsendelse påbegyndt</td>
						<td class='kolonnetitel'>Udsendelse afsluttet</td>
						<td class='kolonnetitel'>Modtagere</td>
						<td class='kolonnetitel'>Funktioner</td>												
					</tr>";
		while ($sendout = mysql_fetch_assoc($res)) {
			$disable_reassume = "";
			$html .= "<tr>
						<td>$sendout[SENDOUT_SUBJECT]</td>
						<td>".returnNiceDateTime($sendout[SENDOUT_BEGINTIME], 1, 1)."</td>";
			if ($sendout[SENDOUT_COMPLETETIME] > 0) {
				// Sendout completed
				$html .= "<td>".returnNiceDateTime($sendout[SENDOUT_COMPLETETIME], 1, 1)."</td>";
				$html .= "<td align='center'>$sendout[NO_RECIPIENTS]</td>";
				$disable_reassume = " disabled";
			} else {
				$html .= "<td><strong>Udsendelse afbrudt</strong></td>";
				// Hvor mange er udsendelsen sendt til?
				$sql_total = "select count(ID) from NEWSLETTER_HISTORY_MAILLOG where HISTORY_ID = '$sendout[ID]'";
				$sql_sent = "select count(ID) from NEWSLETTER_HISTORY_MAILLOG where HISTORY_ID = '$sendout[ID]' and SENDOUT_COMPLETED = '1'";
				$res_total = mysql_query($sql_total);
				$res_sent = mysql_query($sql_sent);
				$count_total = mysql_result($res_total, 0);
				$count_sent = mysql_result($res_sent, 0);
				$html .= "<td align='center'>$count_sent af $count_total</td>";
				// Reassume only possible if at least one mail has been sent out. Otherwise user must start new mailout.
				if ($sql_sent > 0) {
					$disable_reassume = "";
				}
			}
			$html .= 	"<td><input type='button' class='lilleknap' onclick='this.disabled = true; sendNewsletter_reassume($sendout[ID], $count_sent, $count_total)' value='Genoptag udsendelse'$disable_reassume /></td>";
			$html .= "</tr>";			
		}
		$html .= "</table></div>";
		$sent_before = true;
	} else {
		// First time sendout
		$sent_before = false;
	}
	$no_recipients = getNewsletterRecipients("count", $newsletter_id, $template_id);
	// Exit on no recipients
	if ($no_recipients == 0) {
		$html .= "<div class='feltblok_wrapper'><h2>Udsendelse ikke mulig</h2>
					<p>Du kan ikke udsende nyhedsbrevet, da der ikke er nogen modtagere.</p></div>";
		$html .= "<div class='knapbar'>
					<input type='button' value='Til oversigten' onclick='location=\"index.php?content_identifier=newsletter&amp;filter_template=$template_id\"' />
				</div>";
		return $html;
	}
	// Add plural "e"
	if ($no_recipients > 1) {
		$plural_e = "e";
	}

	if ($sent_before) {
			$html .= "<div class='feltblok_wrapper'><h2>Genudsend nyhedsbrev</h2>
					<p>Du har mulighed for at genudsende nyhedsbrevet. Nyhedsbrevet bliver sendt på e-mail til <strong>$no_recipients modtager$plural_e</strong>. Uanset at de tidligere kan have modtaget samme nyhedsbrev.</p>";
	} else {
			$html .= "<div class='feltblok_wrapper'><h2>Udsend nyhedsbrev</h2>
					<p>Du skal til at udsende nyhedsbrevet for første gang. Nyhedsbrevet bliver sendt på e-mail til <strong>$no_recipients modtager$plural_e</strong>.</p>
					";
	}				
	$html .= "					<h2>
						<span style='float:left;'>Status på udsendelse</span>
						<span id='ajaxloader_sendout'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></span>
					</h2>";
	$html .= "<table class='oversigt'>
					<tr class='trtop'>
						<td class='kolonnetitel'>Status</td>
						<td class='kolonnetitel'>Sendt til</td>
						<td class='kolonnetitel'>Funktioner</td>
					</tr>
					<tr>
						<td id='newsletter_sendstatus'>Udsendelse ikke påbegyndt</td>
						<td><span id='recipient_count'>0</span> af <span id='recipient_count_total'>$no_recipients</span></td>
						<td><input id='newsletter_sendstop' type='button' value='Stop udsendelse' class='lilleknap' onclick='sendNewsletter_stop()' disabled />&nbsp;<input id='newsletter_send' type='button' value='Udsend mail' class='lilleknap' onclick='sendNewsletter($newsletter_id, $no_recipients)' /></td>
					</tr>
				</table>";
	$html .= "<div class='knapbar'>
							<input type='button' value='Til oversigten' onclick='location=\"index.php?content_identifier=newsletter&amp;filter_template=$template_id\"' />
			</div>";				
	$html .= "<div id='recipients_list_container'>
						<h2>Nyhedsbrevet er udsendt til</h2>
						<div id='recipients_list' style='height: 100px; overflow: auto;'></div>
					</div>
				</div></form>";
	return $html;
}


function renderTemplateTags($str_text, $arr_formfields, $arr_recipient, $language) {
	// Function to replace template tags with user-content
	// BEMÆRK at hvis de nødvendige data er fra USERS tabellen findes de som $arr_recipient[FIELD_NAME] 
	// hvor FIELD_NAME svarer til kolonnen af tilsvarende navn i tabellen NEWSLETTER_FORMFIELDS. 
	// Ellers foretages ekstra database kald.
	foreach ($arr_formfields as $key => $value) {
		if ($value[TABLE_NAME] = "USERS") {
			// Replace value is in $arr_recipients
			if (strstr($str_text, $value[TEMPLATE_TAG])){
				$replace_field = $value[FIELD_NAME];
				if ($arr_recipient[$replace_field] == "") {
					$replace_value = cmsTranslateBackend($language, $value[DEFAULT_VALUE]);
				} else {
					$replace_value = $arr_recipient[$replace_field];
				}
				$str_text = str_replace($value[TEMPLATE_TAG], $replace_value, $str_text);
			}
		} else {
			// Replace value must be fetched from database - key must be the USER_ID as found in $arr_recipient["ID"]
			if (strstr($str_text, $value[TEMPLATE_TAG])){
				$replace_value = returnFieldValue("$value[TABLE_NAME]", "$value[FIELD_NAME]", "$value[ID_COLUMN_NAME]",$arr_recipient["ID"]);
				if ($replace_value == "") {
					$replace_value = cmsTranslateBackend($language, $value[DEFAULT_VALUE]);
				} else {
					$replace_value = $arr_recipient[$replace_field];
				}
				$str_text = str_replace($value[TEMPLATE_TAG], $replace_value, $newsletter_complete_html);
			}					
		}
	}
	return $str_text;
}


function html2text($text) {
	$text = trim($text);
	$text = ahref2text($text);
	$text = removeTabs($text);
	$text = br2nl($text);	
	$text = removeTagsKeepWhitespace($text);
	return $text;
}

function removeTagsKeepWhitespace($text) {
	$text = preg_replace('/</',' <',$text);
    $text = preg_replace('/>/','> ',$text);
    $desc = html_entity_decode(strip_tags($text));
    $desc = preg_replace('/[\n\r\t]/',' ',$desc);
	// Modification to ony allow two spaces in a row
	$desc = preg_replace('/\s\s\s+/', '  ', $desc);
	$desc = str_replace("  ", "\n\n", $desc); 

//	$desc = preg_replace('/\s\s+/', '\\n\\n', $desc);
    return preg_replace('/  /',' ',$desc);
}

function removeTabs($text) {
	return str_replace("\t", "", $text); 
}

function br2nl($text) {
	// Converts occurences of BR tags to \n (newline)
	return preg_replace('/<br\\\\s*?\\/??>/i', "\\n", $text);
}

function ahref2text($string) {
	// Replaces html links with text-equivalent
	return eregi_replace('(<a [^<]*href=["|\']?([^ "\']*)["|\']?[^>]*>([^<]*)</a>)','[\\3] (link: \\2)', $string);
}

function returnTemplateData($template_id) {
	$sql = "select
				NT.*, L.SHORTNAME as LANGUAGE
			from
				NEWSLETTER_TEMPLATES NT, LANGUAGES L
			where
				NT.ID = '$template_id' and
				NT.LANGUAGE_ID = L.ID
			";
	$res = mysql_query($sql);
	return mysql_fetch_assoc($res);
}

function sendNewsletter($int_newsletter_id, $arr_recipients, $str_mode, $history_id=0, $sendmail=true) {
	// $int_newsletter_id = id of newsletter to send
	// $arr_recipients = array of arrays containing recipient information
	// $str_mode = "mailproof" or "mail"
	// $sendmail = used for debugging. Send mail only if true(default) 

	// Get newsletter main data (headertext etc)
	$arr_maindata = returnNewsletterMaindataArray($int_newsletter_id);

	// Get newsletter items
	$arr_items = returnNewsletterItemArray($int_newsletter_id, $arr_maindata[IMAGES_DISPLAY]);

	// Get formfields (for dynamic replacement of template tags)
	$arr_formfields = returnFormfields();

	// Get template_id
	$template_id = $arr_maindata[NEWSLETTERTEMPLATE_ID];
	
	// Get template data & setup e-mail vars
	$arr_templatedata = returnTemplateData($template_id);

	$subject = $arr_maindata[TITLE];
	$sender_name = $arr_templatedata[SENDER_NAME];
	$sender_email = $arr_templatedata[SENDER_EMAIL];
	$replyto_email = $arr_templatedata[REPLYTO_EMAIL];
	$bounceto_email = $arr_templatedata[BOUNCETO_EMAIL];
	$language = $arr_templatedata[LANGUAGE];

	// Get possible interestgroups for current newsletter/template
	$arr_interestgroups = returnNewsletterInterestgrouplist($template_id);
	
	foreach ($arr_recipients as $arr_recipient) {
		$subject_rendered = renderTemplateTags($subject, $arr_formfields, $arr_recipient, $language);
		$arr_content = newsletter_build($int_newsletter_id, $str_mode, $language, $arr_maindata, $arr_items, $arr_formfields, $arr_recipient, $arr_interestgroups);

		$rec_name = $arr_recipient[FIRSTNAME]." ".$arr_recipient[LASTNAME];
		$rec_email = $arr_recipient[EMAIL];

		/// Send mail via htmlMimeMail
        $mail = NULL; // Kill mail object to make sure nothing lives to next mail!!!
        $mail = new htmlMimeMail();

		// Change to UFT-8 encoding
		$mail->setTextCharset("UTF-8");
		$mail->setHTMLCharset("UTF-8");
		$mail->setHeadCharset("UTF-8"); 

		$mail->setTextWrap(60);
        $mail->setHtml($arr_content[0], $arr_content[1]);
		$mail->setReturnPath($bounceto_email);		
		$mail->setFrom('"'.$sender_name.'" <'.$sender_email.'>');
		$mail->setSubject($subject_rendered);
		$mail->setHeader('Reply-To', $replyto_email);
		if ($sendmail) {
			$result = $mail->send(array("'$rec_name' <$rec_email>"), 'mail');
		} else {
			$result = 1;
		}
		// $result == 1 on success
		if ($str_mode == "mail" && $result == "1") {
			// Register that the mail has been sent!
			if ($history_id != "0") {
				$usql = "update
							NEWSLETTER_HISTORY_MAILLOG
						set
							SENDOUT_COMPLETED = '1',
							SENDOUT_COMPLETETIME = NOW()
						where
							HISTORY_ID = '$history_id' and
							USER_ID = '$arr_recipient[ID]'
							";
				if (!mysql_query($usql)) {
					return false;
				}
			}
		} elseif ($str_mode == "mail" && $result == "0") {
			return false;
		}
	}
	return true;
}

function proofsentForm($newsletter_id) {
	$html .= "<h1>Korrektur afsendt</h1>";
	$html .= "<div class='feltblok_header'>Check din mail!</div>
			<div class='feltblok_wrapper'>";
	$html .= "<p>Før du kan udsende nyhedsbrevet, skal du godkende en korrektur. Når du klikker på knappen herunder får du tilsendt en korrektur-mail. Har du brug for at ændre i nyhedsbrevet, kan du foretage dine ændringer og udsende en ny korrektur. Er alt som det skal være, skal du klikke på 'Godkend korrektur'-linket nederst i mailen. Når du har godkendt korrekturen, kan du udsende nyhedsbrevet med funktionen 'Udsend nyhedsbrev', som du finder på oversigten.</p>";





	$html .= "<div class='knapbar'>
							<input type='button' value='Til oversigten' onclick='location=\"index.php?content_identifier=newsletter&amp;filter_template=$_GET[filter_template]\"' />
							<input type='button' value='Send ny korrektur' onclick='location=\"index.php?content_identifier=newsletter&amp;dothis=proof&amp;nid=$newsletter_id&amp;filter_template=$_GET[filter_template]\"' />
						</div>
			</div>";
	return $html;
}

function proofForm($newsletter_id) {
	$html .= "<h1>Udsend korrektur</h1>";
	$html .= "<div class='feltblok_header'>Vigtigt om korrektur</div>
			<div class='feltblok_wrapper'>";
	$html .= "<p>Før du kan udsende nyhedsbrevet, skal du godkende en korrektur. Når du klikker på knappen herunder får du tilsendt en korrektur-mail. Har du brug for at ændre i nyhedsbrevet, kan du foretage dine ændringer og udsende en ny korrektur. Er alt som det skal være, skal du klikke på 'Godkend korrektur'-linket nederst i mailen. Når du har godkendt korrekturen, kan du udsende nyhedsbrevet med funktionen 'Udsend nyhedsbrev', som du finder på oversigten.</p>";
	$html .= "<p>Ved tilmelding til dette nyhedsbrev har abonnenter mulighed for at oplyse en række informationer. Hvis du har benyttet et {{TEMPLATE-TAG}} i dette nyhedsbrev, vil disse informationer automatisk blive indsat. I korrekturen vil følgende informationer blive udfyldt:</p>";
	
	$html .= "<table class='oversigt'>
				<tr class='trtop'>
					<td class='kolonnetitel'>&nbsp;</td>
					<td class='kolonnetitel'>TEMPLATE-TAG</td>
					<td class='kolonnetitel'>Udfyldes med</td>
				</tr>";
				
	$formfields = returnFormfields();
	foreach ($formfields as $key => $value) {
		$html .= "<tr>
					<td class='kolonnetitel'>$value[CMS_LABEL]</td>
					<td>$value[TEMPLATE_TAG]</td>
					<td>";
		$ff_value = returnFieldValue("$value[TABLE_NAME]", "$value[FIELD_NAME]", "$value[ID_COLUMN_NAME]", $_SESSION[CMS_USER][USER_ID]);
		if ($ff_value == "") {
			$ff_value = cmsTranslateBackend("da", $value[DEFAULT_VALUE]);
		}
		$html .= "$ff_value</td>
				</tr>";
	}
	$html .= "</table>";

	$str_email = returnFieldValue("USERS", "EMAIL", "ID", $_SESSION[CMS_USER][USER_ID]);
	$html .= "<div class='knapbar'>
							<input type='button' value='Afbryd' onclick='location=\"index.php?content_identifier=newsletter&amp;filter_template=$_GET[filter_template]\"' />
							<input type='button' value='Send korrektur til $str_email' onclick='location=\"index.php?content_identifier=newsletter&amp;dothis=sendproof&amp;nid=$newsletter_id&amp;filter_template=$_GET[filter_template]\"' />
						</div>
			</div>";
	return $html;
}

function init_smarty($template_id){
	$template_folder_name = returnFieldValue("TEMPLATES", "FOLDER_NAME", "ID", $template_id);
	$smarty = new Smarty;
	$smarty->template_dir 	= 	$_SERVER[DOCUMENT_ROOT].'/includes/templates/'.$template_folder_name.'/';
	$smarty->compile_dir 	= 	$_SERVER[DOCUMENT_ROOT].'/includes/templates/smarty_templates_c/';
	$smarty->config_dir 	= 	$_SERVER[DOCUMENT_ROOT].'/includes/templates/smarty_configs/';
	$smarty->cache_dir 		= 	$_SERVER[DOCUMENT_ROOT].'/includes/templates/smarty_cache/';
	return $smarty;
}	

function returnNewsletterMaindataArray($newsletter_id) {
	$sql_main = "
		select 
			N.ARCHIVE_TITLE, N.TITLE, N.CONTENT_TOP, N.CONTENT_BOTTOM, N.IMAGES_DISPLAY, 
			N.TEMPLATE_ID as NEWSLETTERTEMPLATE_ID, N.SHOW_INDEX,
			NT.TEMPLATE_ID as TEMPLATE_ID,
			T.PATH, T.FOLDER_NAME, T.ID as TEMPLATES_TEMPLATE_ID
		from 
			NEWSLETTERS N,
			NEWSLETTER_TEMPLATES NT,
			TEMPLATES T
		where 
			N.DELETED='0' 
			and N.ID='$newsletter_id'
			and N.TEMPLATE_ID=NT.ID
			and NT.TEMPLATE_ID=T.ID
	";
	$result_main = mysql_query($sql_main);

	/// Gem disse data i $main_data
	$main_data = mysql_fetch_assoc($result_main);
	return $main_data;
}

function returnNewsletterItemArray($newsletter_id, $image_display) {
	/// Hent data om alle news-items i den pågældende newsletter 
	/// fra NEWSLETTER_ITEMS
	/// $image_display kan være "LEFT", "RIGHT", "ALTERNATING", eller "NONE"
	/// Join tables NEWSLETTERS and NEWSLETTER_TEMPLATES to get site_id

	$sql_items = "
		select
			NI.ID, NI.HEADING, NI.CONTENT, NI.IMAGEURL, NI.LINKURL, NI.IMAGEMODE, NI.LINKMODE, NI.ORIGINAL_TYPE, NI.ORIGINAL_ID, NT.SITE_ID
		from
			NEWSLETTER_ITEMS NI, NEWSLETTERS N, NEWSLETTER_TEMPLATES NT
		where 
			NI.NEWSLETTER_ID='$newsletter_id' and
			NI.NEWSLETTER_ID = N.ID and
			N.TEMPLATE_ID = NT.ID
			and NI.DELETED='0'
		order by
			NI.POSITION asc
	";
	$result_items = mysql_query($sql_items);

	/// Loop hen over items, og byg en array op, der for hvert item indeholder
	/// * HEADING: nyhedens overskrift
	/// * CONTENT: nyhedens indhold
	/// * IMAGEURL: url til nyhedens billede (kan være tom)
	/// * LINKURL: url, som overskrift og "læs mere" skal linke til
	/// * IMAGEPOS: billedposition for dette item - baseres på IMAGES_DISPLAY samt 
	///   det aktuelle billedes plads i listen (via $loops hvis ALTERNATING)
	while ($items_row = mysql_fetch_assoc($result_items)){
		switch ($image_display){
			case "NONE":
				$this_imagepos = "NONE";
			break;
			case "LEFT":
				if ($items_row["IMAGEMODE"] == "noimage"){
					$this_imagepos = "NONE";
				} else {
					$this_imagepos = "LEFT";
				}
			break;
			case "RIGHT":
				if ($items_row["IMAGEMODE"] == "noimage"){
					$this_imagepos = "NONE";
				} else {
					$this_imagepos = "RIGHT";
				}
			break;
			case "ALTERNATING":
				if ($items_row["IMAGEMODE"] == "noimage"){
					$this_imagepos = "NONE";
				} else {
					if ($loops % 2 == 0) {
						$this_imagepos = "RIGHT";
					} else {
						$this_imagepos = "LEFT";
					}
					$loops++;
				}
			break;
		}	
		/// Generer links ved LINKMODE == item
		if ($items_row[LINKMODE] == "item") {
			$site_url = returnBASE_URL($items_row[SITE_ID]).returnSITE_PATH($items_row[SITE_ID]);
			$link = "$site_url/index.php?";
			switch ($items_row[ORIGINAL_TYPE]) {
				case "newsitem":
					$link .= "mode=news&amp;newsid=";
					break;
				case "calendarevent":
					$link .= "mode=events&amp;eventid=";
					break;
				case "page":
					$link .= "pageid=";
					break;
			}
			$link .= $items_row[ORIGINAL_ID];
		} elseif ($items_row[LINKMODE] == "url") {
			$link = $items_row[LINKURL];
		} else {
			$link = "";
		}

		$items_array[] = array(
			"ID" 		=> $items_row["ID"], 
			"HEADING" 	=> $items_row["HEADING"], 
			"CONTENT" 	=> $items_row["CONTENT"],
			"IMAGEURL" 	=> $items_row["IMAGEURL"],
			"LINKMODE" 	=> $items_row["LINKMODE"],
			"LINKURL" 	=> $link,
			"IMAGEPOS" 	=> $this_imagepos
		);
	}
	return $items_array;
}

function returnFormfields() {
	$sql = "select 
				NF.* 
			from
				NEWSLETTER_FORMFIELDS NF
			order by
				NF.POSITION
			";
	$res = mysql_query($sql);
	$formfields = array();
	while ($formfield = mysql_fetch_assoc($res)) {
		$formfields[] = $formfield;
	}
	return $formfields;
}

function returnApproveProofUrl($newsletter_id, $user_id, $language) {
	$site_url = returnBASE_URL($_SESSION[SELECTED_SITE]).returnSITE_PATH($_SESSION[SELECTED_SITE]);
	$approve_url = $site_url.
		"/index.php?mode=newsletter&action=approveproof&newsletterid=".
		$newsletter_id.
		"&uid=".$user_id.
		"&c=".md5($user_id."1nstansFlyvemaskine").
		"&lang=$language";
	return $approve_url;
	
}

function returnNewsarchiveUrl($template_id, $language) {
	$site_url = returnBASE_URL($_SESSION[SELECTED_SITE]);
	if ($site_url == "") {
		$site_url = returnBASE_URL($_SESSION[CURRENT_SITE]);
	}
	$url = $site_url."/includes/shownewsletter.php?newsletterid=".$template_id."&lang=$language&ticket=".md5($template_id."tid");
	return $url;
}

function returnUnsubscribeUrl($template_id, $user_id, $language) {
	$site_url = returnBASE_URL($_SESSION[SELECTED_SITE]).returnSITE_PATH($_SESSION[SELECTED_SITE]);
	$unsubscribe_url = 
		$site_url.
		"/index.php?mode=newsletter&action=unsubscribe&newsletterid=".
		$template_id.
		"&uid=".$user_id.
		"&c=".md5($user_id."1nstansFlyvemaskine").
		"&lang=$language";

	return $unsubscribe_url;
}

function returnUpdateCategoriesUrl($template_id, $user_id, $language) {
	$site_url = returnBASE_URL($_SESSION[SELECTED_SITE]).returnSITE_PATH($_SESSION[SELECTED_SITE]);
	$update_categories_url = 
		$site_url.
		"/index.php?mode=newsletter&action=updateinterestgroups&newsletterid=".
		$template_id.
		"&uid=".$user_id.
		"&c=".md5($user_id."1nstansFlyvemaskine").
		"&lang=$language";
	return $update_categories_url;
}

function returnUpdateCategoriesFormAction($template_id, $user_id, $language) {
	$site_url = returnBASE_URL($_SESSION[SELECTED_SITE]).returnSITE_PATH($_SESSION[SELECTED_SITE]);
	$update_categories_url = 
		$site_url.
		"/index.php?mode=newsletter&action=update_categories_from_email&newsletterid=".
		$template_id.
		"&uid=".$user_id.
		"&c=".md5($user_id."1nstansFlyvemaskine").
		"&lang=$language";
	return $update_categories_url;
}


	function newsletter_build($newsletter_id, $mode, $language, $arr_maindata, $arr_items, $arr_formfields, $arr_recipient=array(), $arr_interestgroups=array()) {	
	/// ----------------------------------------------------------------------
	/// MODES: $mode kan være "mail" eller "site" eller "mailproof" eller "raw" (raw does not render tags)
	/// $arr_recipients = array der indeholder information om modtager
	/// Returns array containing [0] html version and [1] plaintext version of newsletter
	/// ----------------------------------------------------------------------
	
	/// Hent kategorier, der er valgbare i den pågældende
	/// template og check dem af om nødvendigt
	if ($mode == "mail" || $mode == "mailproof"){
		foreach ($arr_interestgroups as $value) {
			if (in_array($value[ID], $arr_recipient[OPTOUTS])) {
				$optout = 1;
			} else {
				$optout = 0;
			}		
	
			$categories_for_template[] = array(
				"NAME" => $value[NAME],
				"ID" => $value[ID],
				"OPTED_OUT"	=> $optout);
		}
	}

	/// Byg sti til template-filer for det pågældende newsletter
	$templates_path = $_SERVER[DOCUMENT_ROOT]."/includes/templates/".$arr_maindata["FOLDER_NAME"];

	/// Initialiser alle smarty templates og sub-templates
	$smarty_main = init_smarty($arr_maindata["TEMPLATES_TEMPLATE_ID"]);
	$smarty_main_plaintext = init_smarty($arr_maindata["TEMPLATES_TEMPLATE_ID"]);
	$smarty_items = init_smarty($arr_maindata["TEMPLATES_TEMPLATE_ID"]);
	$smarty_items_plaintext = init_smarty($arr_maindata["TEMPLATES_TEMPLATE_ID"]);
	$smarty_userform = init_smarty($arr_maindata["TEMPLATES_TEMPLATE_ID"]);
	$smarty_userform_plaintext = init_smarty($arr_maindata["TEMPLATES_TEMPLATE_ID"]);
	
	// Byg + render userform
	$smarty_userform -> assign("categories", $categories_for_template);

	$unsubscribe_url = returnUnsubscribeUrl($arr_maindata["NEWSLETTERTEMPLATE_ID"], $arr_recipient[ID], $language);
	$smarty_userform -> assign("unsubscribe_url", $unsubscribe_url);
	$smarty_userform_plaintext -> assign("unsubscribe_url", $unsubscribe_url);

	$newsarchive_url = returnNewsarchiveUrl($newsletter_id, $language);
	$smarty_main -> assign("newsarchive_url", $newsarchive_url);
	$smarty_main_plaintext -> assign("newsarchive_url", $newsarchive_url);

	$update_categories_url = returnUpdateCategoriesUrl($arr_maindata["NEWSLETTERTEMPLATE_ID"], $arr_recipient[ID], $language);
	$smarty_userform_plaintext -> assign("update_categories_url", $update_categories_url);

	$update_categories_action = returnUpdateCategoriesFormAction($arr_maindata["NEWSLETTERTEMPLATE_ID"], $arr_recipient[ID], $language);
	$smarty_userform -> assign("update_categories_action", $update_categories_action);

	$newsletter_userform_html = $smarty_userform -> fetch($templates_path."/".$arr_maindata["FOLDER_NAME"]."_form.tpl");
	$newsletter_userform_plaintext = $smarty_userform -> fetch($templates_path."/".$arr_maindata["FOLDER_NAME"]."_form_plaintext.tpl");
	
	/// Byg + render items liste
	$arr_items_plaintext = $arr_items;
	if (is_array($arr_items_plaintext)) {
		foreach ($arr_items_plaintext as $key => $value) {
			$arr_items_plaintext[$key][CONTENT] = trim(removeTagsKeepWhitespace($arr_items_plaintext[$key][CONTENT]));
		}
	}
	$smarty_items -> assign("newsletter_items", $arr_items);
	$smarty_items_plaintext -> assign("newsletter_items", $arr_items_plaintext);

	$newsletter_items_html = $smarty_items -> fetch($templates_path."/".$arr_maindata["FOLDER_NAME"]."_item.tpl");
	$newsletter_items_plaintext = $smarty_items_plaintext -> fetch($templates_path."/".$arr_maindata["FOLDER_NAME"]."_item_plaintext.tpl");

	/// Byg main template
	
	/// Assign titel, top-text og bund-text til main-templaten,
	$smarty_main -> assign("newsletter_title", $arr_maindata["TITLE"]);
	$smarty_main_plaintext -> assign("newsletter_title", $arr_maindata["TITLE"]);
	$smarty_main -> assign("newsletter_textabove", trim($arr_maindata["CONTENT_TOP"]));
	$smarty_main_plaintext -> assign("newsletter_textabove", trim(removeTagsKeepWhitespace($arr_maindata["CONTENT_TOP"])));
	$smarty_main -> assign("newsletter_textbelow", trim($arr_maindata["CONTENT_BOTTOM"]));
	$smarty_main_plaintext -> assign("newsletter_textbelow", trim(removeTagsKeepWhitespace($arr_maindata["CONTENT_BOTTOM"])));
	if ($arr_maindata["SHOW_INDEX"]){
		$smarty_main -> assign("newsletter_content_index", createContentIndex($newsletter_id));
		$smarty_main_plaintext -> assign("newsletter_content_index", createContentIndex($newsletter_id));
	}

	/// Assign items til main-templaten
	$smarty_main -> assign("content_rows", $newsletter_items_html);				
	$smarty_main_plaintext -> assign("content_rows", $newsletter_items_plaintext);				

	// Assign userform til main-templaten
	if ($mode == "mail" || $mode == "mailproof"){
		$smarty_main -> assign("user_form", $newsletter_userform_html);
		$smarty_main_plaintext -> assign("user_form", $newsletter_userform_plaintext);
	}
	
	// Render main templates
	$main_template_path = $templates_path."/".$arr_maindata["FOLDER_NAME"]."_main.tpl";
	$newsletter_complete_html = $smarty_main -> fetch($main_template_path);

	$main_template_path_plaintext = $templates_path."/".$arr_maindata["FOLDER_NAME"]."_main_plaintext.tpl";
	$newsletter_complete_plain = $smarty_main_plaintext -> fetch($main_template_path_plaintext); 

	if ($mode == "mail" || $mode == "mailproof" || $mode == "site") {
		/// Erstat vores "nyhedsbrevs-tags" med de rette tekster
		$newsletter_complete_html = renderTemplateTags($newsletter_complete_html, $arr_formfields, $arr_recipient, $language);
		$newsletter_complete_plain = renderTemplateTags($newsletter_complete_plain, $arr_formfields, $arr_recipient, $language);
	}

	// Fix urls for newsletter
	$newsletter_complete_html = update_newsletter_urls($newsletter_complete_html, $arr_recipient[ID], $newsletter_id, $language);

	/// Finish html version
	if ($mode != "site") {
		$html_begin = "<html><head></head><body>";
		$html_end = "</body></html>";
		if ($mode == "mailproof") {
			$approve_action = returnApproveProofUrl($newsletter_id, $arr_recipient[ID], $language);
			// $html_begin .= "<table width='100%' style='border: 1px solid; background-color: #33FF33; text-align: center; height: 24px; line-height: 24px;'><tr><td><form action='$approve_action' method='post'><input style='font-size: 18px;' type='submit' value='Godkend korrektur' /></form></td></tr></table>";			
			$html_begin .= "<table width='100%' style='border: 1px solid; background-color: #33FF33; text-align: center; height: 24px; line-height: 24px;'><tr><td><a style='text-decoration:underline !important' href='$approve_action'><u><strong>Klik her for at godkende korrekturen</strong></u></a></td></tr></table>";			
		}
		$newsletter_complete_html = $html_begin.$newsletter_complete_html.$html_end;
	}

	/// Returner hele nyhedsbrevets HTML + plain
	return array($newsletter_complete_html,$newsletter_complete_plain);
}

function updateInterestgroup($int_newsletter_id, $int_interestgroup_id, $str_mode, $template_id, $int_istemp=0) {
	// Function to create / destroy interest group association
	// $str_mode values: "create" or "destroy"
	// int_istemp values: 0/1 to indicate whether or not the current newsletter is temporary
	
	if ($str_mode == "destroy") {
		$sql = "delete from 
					NEWSLETTER_NEWSLETTER_CATEGORIES 
				where
					CATEGORY_ID = '$int_interestgroup_id' and
					NEWSLETTER_ID = '$int_newsletter_id'
					";
	} else {
		$sql = "insert into NEWSLETTER_NEWSLETTER_CATEGORIES (
					NEWSLETTER_ID,
					CATEGORY_ID,
					USER_ID,
					TEMPORARY)
				values (
					'$int_newsletter_id',
					'$int_interestgroup_id', '";
		$sql .= 	$_SESSION[CMS_USER][USER_ID]."',";
		$sql .= 	"'$int_istemp')";
	}
	mysql_query($sql);
	return getNewsletterRecipients("count", $int_newsletter_id, $template_id);						
}

function getNewsletterRecipients($str_mode, $newsletter_id, $template_id = "") {
	// Parameters:
	// str_mode = "array" - returns array with information about recipients
	// str_mode = "count" - returns recipient count (integer)
	// newsletter_id = which newsletter?
	// template_id (optional) because a "temporary" newsletter (NEWSLETTERS table) does not have a template_id in the database
	//
	// Example use:
	// $arr_recipients = getNewsletterRecipients("array", 22, 2);
	// $int_number_of_recipients = getNewsletterRecipients("count", 22, 2);

	// If no template_id is given, retrieve it from the newsletter_id
	if (($template_id == "") || ($template_id == "0")) {
		$template_id = returnNewsletterTemplateId($newsletter_id);
	}
	// Select the users subscribed through usergroups WHO ARE NOT unsubscribed
	// UNION DISTINCT users actively subscribed to this template
	$sql = "(select distinct 
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
				NEWSLETTER_TEMPLATES_USERGROUPS NTU,
				GROUPS G,
				USERS_GROUPS UG,
				USERS U				
			where 
				NTU.GROUP_ID = G.ID and
				G.ID = UG.GROUP_ID and
				UG.USER_ID = U.ID and
				U.DELETED = '0' and
				U.UNFINISHED = '0' and
				U.EMAIL_VERIFIED = '1' and
				G.DELETED = '0' and				
				NTU.TEMPLATE_ID = '$template_id' and 
				U.ID not in (select distinct U.ID
								from
									NEWSLETTER_SUBSCRIPTIONS NS,
									USERS U
								where 
									NS.USER_ID = U.ID and
									U.DELETED = '0' and
									U.UNFINISHED = '0' and
									NS.TEMPLATE_ID = '$template_id' and
									NS.SUBSCRIBED = '0')
			)
			UNION DISTINCT
			(select distinct 
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
				NEWSLETTER_SUBSCRIPTIONS NS,
				USERS U
			where 
				NS.USER_ID = U.ID and
				U.DELETED = '0' and
				U.UNFINISHED = '0' and
				U.EMAIL_VERIFIED = '1' and
				NS.TEMPLATE_ID = '$template_id' and
				NS.SUBSCRIBED = '1' and
				NS.CONFIRMED = '1'
			)";

	// Set up + reset array to store recipients
	$arr_recipients = array();
	
	// Run above sql to get list of all subscribers
	$all_subscribers = mysql_query($sql);
	if (mysql_num_rows($all_subscribers) > 0) {
		// Newsletter will be sent to subscribers who are interested in these categories (checked off in the CMS Newsletter creation function):
		$arr_sendtocats = returnNewsletterInterestgroups($newsletter_id, $template_id);



		// For each possible subscriber, check if their options opt them opt
		while ($subscriber = mysql_fetch_array($all_subscribers)) {
			// For each subscriber, get list of their opt-out categories for the current template:
			$arr_optoutcats = returnSubscriberOptouts($subscriber[ID], $template_id);
			if (count($arr_optoutcats) == 0) {
				// Recipient has no opt-outs for this template.
				// Register as recipient
				$arr_recipients[] = $subscriber;
			} else {
				// Recipient has opt-outs for this template
				// Subtract out-out categories from send-to categories
				$arr_remaining_cats = subtractArray($arr_sendtocats,$arr_optoutcats);
/*
				echo "SEND TO THESE GROUPS: <br>";
				print_r($arr_sendtocats);
				echo "<br>USER $subscriber[ID] HAS OPTED OUT OF THESE GROUPS: <br>";
				print_r($arr_optoutcats);
				echo "<br>So arr_remaining_cats are: <br>";
				print_r($arr_remaining_cats);
*/
				// Send only if any send-to categories remain
				if (count($arr_remaining_cats) > 0) {
					// Register as recipient
					$subscriber[OPTOUTS] = $arr_optoutcats;
					$arr_recipients[] = $subscriber;
				}
			}
		}
	}
	if ($str_mode == "count") {
		return count($arr_recipients);
	} else {
		return $arr_recipients;
	}
}

function returnNewsletterTemplateId($newsletter_id) {
		$sql = "select TEMPLATE_ID from NEWSLETTERS where ID = '$newsletter_id'";
		$res = mysql_query($sql);
		if (mysql_num_rows($res) > 0) {
			return mysql_result($res,0);
		} else {
			return $_GET[ntid];
		}
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
function returnNewsletterInterestgrouplist($template_id) {
	// Function used to create an array containing the possible interestgroups 		
	// a user can choose for a given template
	$arr_interestgroups = array();
	$sql = "select NC.ID, NC.NAME
			from
				NEWSLETTER_TEMPLATES_CATEGORYGROUPS NTC, NEWSLETTER_CATEGORIES NC
			where
				NTC.TEMPLATE_ID = '$template_id' and
				NTC.CATEGORYGROUP_ID = NC.GROUP_ID and
				NC.DELETED = '0'
			order by
				NC.GROUP_ID, NC.NAME
			";
	$interestgroups = mysql_query($sql);
	if (mysql_num_rows($interestgroups) > 0) {
		// Some categories selected
		// Build array containing categories to send to
		while ($interestgroup = mysql_fetch_array($interestgroups)) {
			$arr_interestgroups[] = array("ID" => $interestgroup[ID], "NAME" => $interestgroup[NAME]);
		}
	}
	return $arr_interestgroups;
}

function returnNewsletterInterestgroups($newsletter_id, $template_id) {
	// Function used (by getNewsletterRecipients) to create a list of interestgroups to send to
	// For a complete list of interest groups use function returnNewsletterInterestgrouplist

	$arr_sendtocats = array();
	$sql_sendtocategories = "select NNC.CATEGORY_ID
			from
				NEWSLETTER_NEWSLETTER_CATEGORIES NNC, NEWSLETTERS N
			where
				N.ID = '$newsletter_id' and
				N.TEMPLATE_ID = '$template_id' and
				N.ID = NNC.NEWSLETTER_ID
			";
	$sendtocategories = mysql_query($sql_sendtocategories);
	if (mysql_num_rows($sendtocategories) > 0) {
		// Some categories selected
		// Build array containing categories to send to
		while ($sendtocategory = mysql_fetch_assoc($sendtocategories)) {
			$arr_sendtocats[] = $sendtocategory[CATEGORY_ID];
		}
	}
	return $arr_sendtocats;
}

function listNewsletterSubscribedUsergroups($newsletter_id, $template_id) {
	if ($template_id == "") {
		// Editing existing newsletter - get template id from db
		$template_id = returnNewsletterTemplateId($newsletter_id);
	}
	// Set relevant usergroups for this template id
	$sql = "select G.ID, G.GROUP_NAME
			from 
				NEWSLETTER_TEMPLATES_USERGROUPS NTU,
				GROUPS G
			where 
				G.ID = NTU.GROUP_ID and
				NTU. TEMPLATE_ID = '$template_id'
			";
	$res = mysql_query($sql);
	if (mysql_num_rows($res) > 0) {
		while ($i = mysql_fetch_assoc($res)) {
			$html .= "<input type='checkbox' id='usergroup_$i[ID]' name='usergroup_$i[ID]' disabled checked />&nbsp;$i[GROUP_NAME]<br />";
		}
	} else {
		$html .= "<p>Nyhedsbreve baseret på denne template sender ikke ud til nogen brugergrupper.</p>";
	}
	return $html;
}				
			
function listNewsletterInterestGroups($newsletter_id, $template_id) {
	if ($template_id == "") {
		// Editing existing newsletter - get template id from db
		$template_id = returnNewsletterTemplateId($newsletter_id);
	}
	// Get relevant categories for this template id
	$sql = "select NC.ID as CAT_ID, NC.NAME as CAT_NAME, NG.NAME AS GROUP_NAME
		from
			NEWSLETTER_TEMPLATES_CATEGORYGROUPS NTC, 
			NEWSLETTER_CATEGORYGROUPS NG,
			NEWSLETTER_CATEGORIES NC
		where
			NTC.TEMPLATE_ID = '$template_id' and
			NTC.CATEGORYGROUP_ID = NC.GROUP_ID and 
			NC.GROUP_ID = NG.ID and
			NG.DELETED = '0' and
			NC.DELETED = '0'
		";
	$res = mysql_query($sql);
	if (mysql_num_rows($res) > 0) {
		$current_groupname = "";
		while ($i = mysql_fetch_assoc($res)) {
			// Output category groupname
			if ($current_groupname != $i[GROUP_NAME]) {
				$current_groupname = $i[GROUP_NAME];
				$html .= "<h3>$current_groupname</h3>";
			}
			// Check category?
			$innersql = "select count(*) from NEWSLETTER_NEWSLETTER_CATEGORIES NNC where NNC.CATEGORY_ID = $i[CAT_ID] and NNC.NEWSLETTER_ID = '$newsletter_id'";
			$innerres = mysql_query($innersql); 
			if (mysql_result($innerres,0) > 0) {
				$i[CHECKED] = " checked";
			}
			$html .= "<input type='checkbox' id='catcheck_$i[CAT_ID]' name='catcheck_$i[CAT_ID]'$i[CHECKED] onclick='toggle_newsletterinterestgroup(this.id, this.checked)' />&nbsp;$i[CAT_NAME]<br />";
		}
	} else {
		$html .= "<p>Der er ikke defineret interessegrupper for denne template</p>";
	}
	return $html;
}

function createTemporaryCategoryassociations($newsletter_id, $template_id) {
	// Get relevant categories for this template id
	$sql = "select NC.ID
		from
			NEWSLETTER_TEMPLATES_CATEGORYGROUPS NTC, 
			NEWSLETTER_CATEGORIES NC
		where
			NTC.TEMPLATE_ID = '$template_id' and
			NTC.CATEGORYGROUP_ID = NC.GROUP_ID and 
			NC.DELETED = '0'
		";
	$res = mysql_query($sql);
	if (mysql_num_rows($res) > 0) {
		while ($cat = mysql_fetch_assoc($res)) {
			$sql = "insert into
						NEWSLETTER_NEWSLETTER_CATEGORIES
					(ID, NEWSLETTER_ID, CATEGORY_ID, USER_ID, TEMPORARY)
					values
					('', '$newsletter_id', '$cat[ID]', '".$_SESSION[CMS_USER][USER_ID]."', '1')";
				mysql_query($sql);
		}
	}
}



function newsletterForm() {
	global $fckEditorPath;
	// Create template-tag comment
	$comment_tag = "<p class='feltkommentar feltkode'>Tip: Hvis du skriver én eller flere af følgende feltkoder vil de blive udskiftet med modtagerens oplysninger. Bemærk at hvis du benytter en feltkode som ikke er udfyldt for alle brugere vil der komme til at stå en standard-værdi i mailen. Felter som brugeren <em>skal</em> oplyse ved tilmelding til nyhedsbrevet er markeret med <strong>fed</strong>:<br />";
	$sql = "
			select 
				NF.TEMPLATE_TAG,
				NTF.MANDATORY
			from
				NEWSLETTER_TEMPLATES_FORMFIELDS NTF,
				NEWSLETTER_FORMFIELDS NF 
			where
				NF.ID = NTF.FIELD_ID and
				NTF.TEMPLATE_ID = '$_GET[ntid]' 
			union distinct
			select 
				NF.TEMPLATE_TAG,
				'1'
			from
				NEWSLETTER_FORMFIELDS NF 
			where
				TEMPLATETAG_ONLY = '1'
			";
	$result = mysql_query($sql);
	while ($field = mysql_fetch_assoc($result)) {
		if ($field[MANDATORY] == 1) {
			$comment_tag .= "<strong>$field[TEMPLATE_TAG]</strong>  ";
		} else {
			$comment_tag .= "$field[TEMPLATE_TAG]  ";
		}
	}
	$comment_tag .= "</p>";	

	if (!$_GET[nid]) {
		// GET DEFAULTS FROM NEWSLETTER_TEMPLATE
		$sql = "select *
					from NEWSLETTER_TEMPLATES
					where ID = '$_GET[ntid]'";
		$result = mysql_query($sql);
		$trow = mysql_fetch_array($result);
		// SET DEFAULT VALUES
		$row[TITLE] = $trow[TITLE];
		$row[IMAGES_DISPLAY] = "RIGHT";
		// Set $row[ID] = temporary id
		$row[ID] = $_GET[nid_temp];
		$html = "<h1>Opret nyhedsbrev</h1>";
	} else {
		// GET NEWSLETTER DATA
		$sql = "select * from NEWSLETTERS where ID = '$_GET[nid]' and DELETED = '0'";
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		$html = "<h1>Rediger nyhedsbrev</h1>";
	}
	$html .= "<div class='broedtekst'>Definér top og bund, indhold og modtagere for dit nyhedsbrev</div>
				<form id='form_newsletter' method='post' action=''>
					<input type='hidden' name='dothis' id='dothis' value='";
						if ($_GET[nid] == "") {
							$html .= "insert";
						} else {
							$html .= "update";
						}
					$html .= "' />
					<input type='hidden' name='newsletter_id' id='newsletter_id' value='$_GET[nid]' />
					<input type='hidden' name='newsletter_id_temporary' id='newsletter_id_temporary' value='$row[ID]' />
					<input type='hidden' name='newsletter_template_id' id='newsletter_template_id' value='$_GET[ntid]' />
				<ul id='tablist'>
					<li><a href='#' class='current' onClick='return expandcontent(\"sc1\", this)'>Indhold</a></li>
					<li><a href='#' onClick='return expandcontent(\"sc2\", this)'>Nyhedsliste</a></li>
					<li><a href='#' onClick='return expandcontent(\"sc3\", this)'>Modtagere (<span id='no_recipients_tab'>";
					$html .= getNewsletterRecipients("count", $row[ID]);
					$html .= "</span>)</a></li>
				</ul>
				<div id='tabcontentcontainer'>
					<div id='sc1' class='tabcontent'>
						<h2>Titel i nyhedsbrev-arkiv</h2>
							<input type='text' id='archive_title' name='archive_title' class='inputfelt' value='$row[ARCHIVE_TITLE]' />
						<h2>Titel (emne) i e-mail</h2>
							<input type='text' id='title' name='title' class='inputfelt' value='$row[TITLE]' />
							$comment_tag";
	$html .= "<h2>Vis indholdsfortegnelse i nyhedsbrev?</h2>";
	$html .= "<input type='checkbox' name='show_index' ".($row[SHOW_INDEX] == "1" ? " checked " : "")." />&nbsp;Ja tak";
	
	$html .= 			"<h2>Placering af billeder i nyhedsliste</h2>
							<input type='radio' name='images_display' value='RIGHT'";
							if ($row[IMAGES_DISPLAY] == "RIGHT") { $html .= " checked"; }
	$html .=				" />&nbsp;Til højre for nyhederne<br/>
							<input type='radio' name='images_display' value='LEFT'";
							if ($row[IMAGES_DISPLAY] == "LEFT") { $html .= " checked"; }
	$html .=				"/>&nbsp;Til venstre for nyhederne<br/>
							<input type='radio' name='images_display' value='ALTERNATING'";
							if ($row[IMAGES_DISPLAY] == "ALTERNATING") { $html .= " checked"; }
	$html .=				"/>&nbsp;Skiftevis til højre og venstre for nyhederne<br/>
							<input type='radio' name='images_display' value='NONE'";
							if ($row[IMAGES_DISPLAY] == "NONE") { $html .= " checked"; }
	$html .=				"/>&nbsp;Vis ingen billeder";
	$html .=			"<div class='knapbar'>
							<input type='button' value='Afbryd' onclick='location=\"index.php?content_identifier=newsletter&amp;filter_template=$_GET[ntid]\"' />
							<input type='button' value='Gem' onclick='verify()' />
						</div>
						<h2>Tekst i toppen af nyhedsbrevet</h2>";
							$oFCKeditor = new FCKeditor('content_top') ;
							$oFCKeditor->BasePath = $fckEditorPath . "/";
							$oFCKeditor->ToolbarSet	= "CMS_NewsletterContent";
							$oFCKeditor->Height	= "200";
							$oFCKeditor->Value	= $row["CONTENT_TOP"];
							$oFCKeditor->Config['CustomConfigurationsPath']	= $fckEditorCustomConfigPath . "/cms_fckconfig.js";
	$html .= 				$oFCKeditor->CreateHtml() ;
	$html .= 			"$comment_tag<h2>Tekst under nyhedsliste</h2>";
							$oFCKeditor = new FCKeditor('content_bottom') ;
							$oFCKeditor->BasePath = $fckEditorPath . "/";
							$oFCKeditor->ToolbarSet	= "CMS_NewsletterContent";
							$oFCKeditor->Height	= "200";
							$oFCKeditor->Value	= $row["CONTENT_BOTTOM"];
							$oFCKeditor->Config['CustomConfigurationsPath']	= $fckEditorCustomConfigPath . "/cms_fckconfig.js";
	$html .= 				$oFCKeditor->CreateHtml() ;
	$html .= 			"$comment_tag
					</div>
					<div id='sc2' class='tabcontent'>
					<h2>
						<span style='float:left;'>Nyhedsliste</span>
						<span id='ajaxloader_newsitemlist'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></span>
					</h2>
					<p class='feltkommentar'>Herunder ser du en liste over alle elementer i nyhedsbrevet. Træk og slip elementerne for at ændre rækkefølgen.</p>
					<div id='newsletter_itemlist'>";
						$html .= list_newsletter_items($row[ID]);
	$html .= 		"</div>
					<input type='button' id='newsletter_additem_init_button' value='Tilføj element til nyhedslisten' class='lilleknap' onclick='newsletter_additem_init()' />
					<div id='newsletter_selectitemtype'>
						<h2>Vælg indholdstype</h2>
							<input type='hidden' name='newsletter_itemtype' id='newsletter_itemtype' value='newsitem' />
							<select id='itemtype_select' onchange='itemtype_selected(this.value)' class='inputselect'>
								<option value='' selected>Vælg indholdstype...</option>
								<option value='newsitem'>Nyhed fra et nyhedsarkiv</option>
								<option value='calendarevent'>Begivenhed fra en kalenderen</option>
								<option value='page'>Side fra en menu</option>
								<option value='custom'>Fritekst</option>
							</select>
					</div>
					<div id='newsletter_additem'>
						<input type='hidden' id='newsletter_itemoriginalid' name='newsletter_itemoriginalid' value='' />
						<h2 id='newsletter_additem_h2'>
							<span style='float:left;'>Vælg <span id='newsletter_additem_typetext'>nyhed</span> fra listen</span>
							<span id='ajaxloader_additem'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></span>
						</h2>
						<div id='newsletter_additem_newsitem'>
							<div id='newsletter_additem_newsarchives'>Henter nyhedsarkiv-liste...</div>
							<div id='newsletter_additem_newsitems'></div>
						</div>
						<div id='newsletter_additem_calendarevent'>
							<div id='newsletter_additem_calendars'>Henter kalenderliste...</div>
							<div id='newsletter_additem_calendarevents'></div>
						</div>
						<div id='newsletter_additem_menupage'>
							<div id='newsletter_additem_menus'>Henter menuliste...</div>
							<div id='newsletter_additem_menupages'></div>
						</div>
					</div>
					<div id='newsletter_edititem'>
						<input type='hidden' name='newsletter_newsletteritem_originalid' id='newsletter_newsletteritem_originalid' value='' />
						<h2>Overskrift</h2>
							<input type='text' id='newsletter_itemtitle' name='newsletter_itemtitle' class='inputfelt' value='' />
						<div id='newsletter_additem_importoptions'>
							<h2>Hent indhold til indholdseditor</h2>
							<p class='feltkommentar'>Her kan du vælge om din nyhed skal baseres på resumé eller indhold i det originale materiale. Bemærk at nyt valg overskriver eventuelle ændringer i indholdsfeltet herunder.</p>
							<input type='radio' id='newsletter_imported_content' name='newsletter_imported' value='content' onclick='set_fck_content(this.value)' checked />&nbsp;Hele <span id='newsletter_additem_importoptiontext'>nyheden</span><br />
							<input type='radio' id='newsletter_imported_summary' name='newsletter_imported' value='summary' onclick='set_fck_content(this.value)' />&nbsp;Resumé
							<div id='newsletter_imported_contenthidden'></div>
						</div>						
						<h2>Indhold til nyhedsliste</h2>";
							$oFCKeditor = new FCKeditor('content_item') ;
							$oFCKeditor->BasePath = $fckEditorPath . "/";
							$oFCKeditor->ToolbarSet	= "CMS_NewsletterItemContent";
							$oFCKeditor->Height	= "150";
							$oFCKeditor->Value	= '';
							$oFCKeditor->Config['CustomConfigurationsPath']	= $fckEditorCustomConfigPath . "/cms_fckconfig.js";
	$html .= 				$oFCKeditor->CreateHtml() ;
	$html .=			"<h2>Billede</h2>
						<table>
							<tr>
								<td valign='top'>
									<span id='newsletter_edititem_image_item_container'>
									<input type='radio' id='newsletter_edititem_image_item' name='newsletter_edititem_image' value='item' onclick='newsletter_edititem_imagemode(this.value)' />&nbsp;Billede fra <span id='newsletter_edititem_imagefromtext'>nyheden</span>&nbsp;<span id='newsletter_edititem_selectimage'></span><br /></span>
									<input type='radio' id='newsletter_edititem_image_archive' name='newsletter_edititem_image' value='archive' onclick='newsletter_edititem_imagemode(this.value)' />&nbsp;Billede fra billedarkivet <input type='button' id='selectImageButton' name='newsletter_itemimage_selectbutton' class='lilleknap' value='Vælg billede' onclick='selectImage($folder_id);' /><br />
									<input type='radio' id='newsletter_edititem_image_noimage' name='newsletter_edititem_image' value='noimage' checked onclick='newsletter_edititem_imagemode(this.value)' />&nbsp;Intet billede<br />
								</td>
								<td>						
									<div id='newsletter_edititem_showimage'>
									</div>
								</td>
							</tr>
						</table>
						<div id='selectImageDiv'></div>						
						<h2>Link på overskriften</h2>
						<span id='newsletter_edititem_link_item_container'><input type='radio' id='newsletter_edititem_link_item' name='newsletter_edititem_link' value='item' onclick='newsletter_edititem_linkmode(this.value)' checked />&nbsp;Link til <span id='newsletter_edititem_linktotext'>nyheden</span><br /></span>
						<input type='radio' id='newsletter_edititem_link_url' name='newsletter_edititem_link' value='url'  onclick='newsletter_edititem_linkmode(this.value)'/>&nbsp;Link til <input type='text' id='newsletter_itemlinkurl' name='newsletter_itemlinkurl' class='inputfelt' value='' /><br />
						<input type='radio' id='newsletter_edititem_link_nolink' name='newsletter_edititem_link' value='nolink'  onclick='newsletter_edititem_linkmode(this.value)'/>&nbsp;Intet link<br />
					</div>
					<div id='newsletter_listitem_knapbar'>
						<input type='button' id='newsletter_edititem_buttonadd' class='lilleknap' value='Tilføj til nyhedslisten' onclick='save_item()' />
						<input type='button' id='newsletter_edititem_buttoncancel' class='lilleknap' value='Fortryd nyt element' onclick='abort_add_item()' />
					</div>
					</div>
					<div id='sc3' class='tabcontent'>
					<h2>
						<span style='float:left;'>Send nyhedsbrevet til abonnenter, der har afkrydset følgende interessegrupper</span>
						<span id='ajaxloader_interestgroups'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></span>
					</h2>";
		$html .=	listNewsletterInterestGroups($row[ID], $_GET[ntid]);
		$html .= 	"<h2>Nyhedsbrevet sendes også til medlemmer af følgende brugergrupper</h2>";
		$html .=	listNewsletterSubscribedUsergroups($row[ID], $_GET[ntid]);
		$html .= 	"</div>
				</div>
				<div class='knapbar'>
					<input type='button' value='Afbryd' onclick='location=\"index.php?content_identifier=newsletter&amp;filter_template=$_GET[ntid]\"' />
					<input type='button' value='Gem' onclick='verify()' />
				</div>
			</form>";
	return $html;
}

function listNewsletters() {
	$html .= "<h1>Nyhedsbreve</h1>";
	$sql = "select * from NEWSLETTER_TEMPLATES where DELETED = 0 and SITE_ID = '$_SESSION[SELECTED_SITE]' order by TITLE asc";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$html .= "<form action='' method='get' id='form_newsletterList' name='form_newsletterList'>
					<input type='hidden' id='content_identifier' name='content_identifier' value='newsletter' />
					<div class='feltblok_header'>Vis nyhedsbreve baseret på skabelonen&nbsp;
							<select id='filter_template' name='filter_template' class='standard_select' onchange='submit()'>";
		while ($t = mysql_fetch_assoc($result)) {
			// Default to filter by first found template
			if (!$_GET[filter_template]) {
				$_GET[filter_template] = $t[ID];
			}
			$html .=			"<option value='$t[ID]'";
								if ($_GET[filter_template] == $t[ID]) {
									$html .= " selected";
								}
			$html .=			">$t[TITLE]</option>";
		}
		$html .= 			"</select></div>
						<div class='feltblok_wrapper'>";
		$sql = "SELECT 
					N.ID,
					N.ARCHIVE_TITLE,
					N.TITLE,
					N.APPROVED,
					N.APPROVED_TIME,
					NT.TITLE AS TEMPLATE_TITLE,
					U.FIRSTNAME,
					U.LASTNAME
				FROM
					NEWSLETTER_TEMPLATES NT, NEWSLETTERS N
				LEFT JOIN
					USERS U on
					N.APPROVED_BY = U.ID
				WHERE
					N.TEMPLATE_ID = NT.ID
					AND N.TEMPLATE_ID = '$_GET[filter_template]'
					AND N.DELETED = '0'
				GROUP BY
					N.ID
				ORDER BY
					N.ID DESC";
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			$html .= "<table class='oversigt'>
				<tr class='trtop'>
					<td class='kolonnetitel'>Titel</td>
					<td class='kolonnetitel'>Korrektur</td>
					<td class='kolonnetitel'>Udsendt</td>
					<td class='kolonnetitel'>Funktioner</td>
				</tr>";
			while ($row = mysql_fetch_array($result)) {
				$html .= "<tr>
							<td>";
							if ($row[ARCHIVE_TITLE] != "") {
							     $html .= $row[ARCHIVE_TITLE];
				            } else {
							     $html .= $row[TITLE];
				            }							
				$html .= "</td>
							<td>";
							if ($row[APPROVED] == 0) {
								$html .= "Ikke godkendt";
							} else {
								$name = substr($row[FIRSTNAME], 0, 1).". ".$row[LASTNAME];
                                $time = date("j/n Y H:i", $row["APPROVED_TIME"]);
//								$time = returnNiceDateTime($row["APPROVED_TIME"], 1);
								$html .= "Godkendt<br />af $name<br />$time";
							}
				$html .= 	"</td>
							<td>";
                $usql = "select ID 
                        from 
                            NEWSLETTER_HISTORY 
                        where 
                            NEWSLETTER_ID = '$row[ID]' and
                            TEMPLATE_ID = '$_GET[filter_template]'
                        order by
                            Date(SENDOUT_COMPLETETIME) desc,
                            Time(SENDOUT_COMPLETETIME) desc,
                            Date(SENDOUT_BEGINTIME) desc,
                            Time(SENDOUT_BEGINTIME) desc
                        limit 1";
                $ures = mysql_query($usql);
            if (mysql_num_rows($ures)>0) {
                $nh_id = mysql_result($ures,0);
                $usql = "select 
                            UNIX_TIMESTAMP(NH.SENDOUT_BEGINTIME) as SENDOUT_BEGINTIME,
                            UNIX_TIMESTAMP(NH.SENDOUT_COMPLETETIME) as SENDOUT_COMPLETETIME,
                            NH.NO_RECIPIENTS,
                            U.FIRSTNAME,
                            U.LASTNAME
                        from
                            NEWSLETTER_HISTORY NH, USERS U
                        where
                            NH.ID = '$nh_id' and
                            NH.USER_ID = U.ID";
                $ures = mysql_query($usql);
                $urow = mysql_fetch_assoc($ures);
            } else {
				$urow = array();
			}
							if ($urow[SENDOUT_COMPLETETIME] == "" && $urow[SENDOUT_BEGINTIME] == "") {
								$html .= "Ikke udsendt";
								$stat_disabled = "disabled";
							} elseif ($urow[SENDOUT_COMPLETETIME] == "" && $urow[SENDOUT_BEGINTIME] != "") {
								$html .= "<strong>Delvist udsendt</strong>";
								$stat_disabled = "disabled";
							} else {
								$name = substr($urow[FIRSTNAME], 0, 1).". ".$urow[LASTNAME];
								$time = date("j/n Y H:i", $urow["SENDOUT_COMPLETETIME"]);
								$html .= "Til $urow[NO_RECIPIENTS] modtagere<br /> af $name<br />$time";
								$stat_disabled = "";
							}

				$html .= 	"</td>";
                if ($row[APPROVED] != 1) {
					// Må kun udsendes hvis korrektur er godkendt
					$allowsend = " disabled";
				} else {
					$allowsend = "";
				}
                $html .=    "<td>
								<input type='button' class='lilleknap' value='Slet' onclick='location=\"index.php?content_identifier=newsletter&amp;dothis=delete&amp;nid=$row[ID]&amp;ntid=$_GET[filter_template]\"' />
								<input type='button' class='lilleknap' value='Korrektur' onclick='location=\"index.php?content_identifier=newsletter&amp;dothis=proof&amp;nid=$row[ID]&amp;filter_template=$_GET[filter_template]\"' />
								<input type='button' class='lilleknap' value='Udsend nyhedsbrev' onclick='location=\"index.php?content_identifier=newsletter&amp;dothis=sendnewsletter&amp;nid=$row[ID]&amp;filter_template=$_GET[filter_template]\"'$allowsend />
								<input type='button' class='lilleknap' value='Rediger' onclick='location=\"index.php?content_identifier=newsletter&amp;dothis=rediger&amp;nid=$row[ID]&amp;ntid=$_GET[filter_template]\"' />
								<input type='button' class='lilleknap' value='Statistik' $stat_disabled onclick='location=\"index.php?content_identifier=newsletter&amp;dothis=stats&amp;nid=$row[ID]\"' />
							</td>
						</tr>";
			}
			$html .= "</table>";
			
		} else {
			/* Ingen nyhedsbreve for valgte skabelon */
			$html .= "<h2>Ingen nyhedsbreve oprettet.</h2>";
		}


		$html .=	"</div>";
		$html .=	"<div class='knapbar'>
						<input type='button' value='Opret nyhedsbrev' onclick='location=\"index.php?content_identifier=newsletter&amp;dothis=opret&amp;ntid=$_GET[filter_template]\"' />
					</div>";

	} else {
		/* Ingen skabeloner */
		$html .= "<div class='feltblok_header'>Der er ikke oprettet nogen skabeloner.</div>
					<div class='knapbar'>
						<input type='button' value='Opret ny skabelon' onclick='location=\"index.php?content_identifier=newslettertemplates&amp;dothis=opret\"' />
					</div>";
	}
	$html .= "</form>";
	return $html;
}

function subtractArray($vectorA,$vectorB) 
	// Subtract vectorA from VectorB and return results array
{ 
$cantA=count($vectorA); 
$cantB=count($vectorB); 
$No_saca=0; 
for($i=0;$i<$cantA;$i++) 
{ 
for($j=0;$j<$cantB;$j++) 
{ 
if($vectorA[$i]==$vectorB[$j]) 
$No_saca=1; 
} 

if($No_saca==0) 
$nuevo_array[]=$vectorA[$i]; 
else 
$No_saca=0; 
} 

return $nuevo_array; 
} 

function returnSingleNewsletterItem($id) {
	$sql = "select * from NEWSLETTER_ITEMS where ID = '$id' and DELETED = '0'";
	$res = mysql_query($sql);
	$i = mysql_fetch_assoc($res);
	$str_response = "";
	foreach ($i as $key => $value) {
		if ($str_response != "") {
			$str_response .= "|||||";
		}
		$str_response .= $value;
	}
	return $str_response;
}


function list_newsletter_items($newsletter_id = "") {
	if ($newsletter_id != "") {
		$sql = "select ID, HEADING from NEWSLETTER_ITEMS where NEWSLETTER_ID = $newsletter_id and DELETED = '0' order by POSITION ASC";
		$result = mysql_query($sql);
		if (mysql_num_rows($result) == 0) {
			return "<p>Der er endnu ingen elementer i nyhedsbrevet.</p>";
		} 
		while($i = mysql_fetch_assoc($result)) {
			$html .= "<table class='newsletter_itemlist_table' id='newsletteritem_$i[ID]'>
						<tr>
							<td>$i[HEADING]</td>
							<td class='newsletter_itemlist_buttoncontainer' align='right'>
								<input type='button' id='newsletter_item_delete_button$i[ID]' value='Slet' class='lilleknap' onclick='newsletter_item_delete($i[ID])' />&nbsp;
								<input type='button' id='newsletter_item_edit_button$i[ID]' value='Rediger' class='lilleknap' onclick='newsletter_item_edit($i[ID])' />
							</td>
							</tr>
						</table>\n";
		}
	} else {
		$html = "<p>Der er endnu ingen elementer i nyhedsbrevet.</p>";
	}
	return $html;
}

function saveNewsletterItem() {
	if ($_POST[mode] == "insert") {
		// Get next position
		$sql = "select max(POSITION)+1 from NEWSLETTER_ITEMS where NEWSLETTER_ID and DELETED = '0' = $_POST[newsletter_id]";
		$res = mysql_query($sql);
		$pos = mysql_result($res,0);
		if (!$pos) {
			$pos = 1;
		}		
		// Insert item
 		$sql = "insert into NEWSLETTER_ITEMS values (
			'',
			'".$_SESSION[CMS_USER][USER_ID]."',
			'$_POST[item_istemp]',
			'$_POST[newsletter_id]',
			'$_POST[item_original_id]',
			'$_POST[item_original_type]',
			'$_POST[item_heading]',
			'$_POST[item_content]',
			'$_POST[item_imagemode]',
			'$_POST[item_imageurl]',
			'$_POST[item_linkmode]',
			'$_POST[item_linkurl]',
			'$pos',
			'0'
			)";
		$res = mysql_query($sql);
	} else {
		// Update item
		$sql = "update NEWSLETTER_ITEMS 
		set
			HEADING = '$_POST[item_heading]',
			CONTENT = '$_POST[item_content]',
			IMAGEMODE = '$_POST[item_imagemode]',
			IMAGEURL = '$_POST[item_imageurl]',
			LINKMODE = '$_POST[item_linkmode]',
			LINKURL = '$_POST[item_linkurl]'
		where
			ID = $_POST[item_updateid]";
		$res = mysql_query($sql);
	}
}

function saveReordered() {
	$temp = explode("&", $_POST["order"]);
	foreach ($temp as $line){
		eval("$".$line.";");
	}
	foreach ($newsletter_itemlist as $itemId){
		$pos++;
		$sql = "update NEWSLETTER_ITEMS set POSITION='$pos' where ID='$itemId' and DELETED = '0'";
		mysql_query($sql);
	}
}

function returnSingleitem($type, $id) {
	// $type = newsitem, calendarevent or page
	// $id = news id
	// returns HEADING|||||SUBHEADING|||||CONTENT_STRIPPED|||||CONTENT

	switch ($type) {
		case "newsitem":
			$db_tablename = "NEWS";
			break;
		case "calendarevent":
			$db_tablename = "EVENTS";
			break;
		case "page":
			$db_tablename = "PAGES";
			break;
	}			
	$sql = "select HEADING, SUBHEADING, CONTENT 
			from 
				$db_tablename
			where
				DELETED = '0'
				and UNFINISHED = '0'
				and ID = '$id'";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$i = mysql_fetch_assoc($result);
		$i[CONTENT_STRIPPED] = strip_tags($i[CONTENT], "<p><a><table><td><tr>");
		return "$i[HEADING]|||||$i[SUBHEADING]|||||$i[CONTENT_STRIPPED]|||||$i[CONTENT]";
	}
}

function saveNewsletter() {
	$template_id = $_GET[ntid];
	if ($_POST[show_index] == "on"){
		$_POST[show_index] = 1;
	}
	if ($_POST[newsletter_id] == "") {
		// insert
		$sql = "insert into NEWSLETTERS 
				(ARCHIVE_TITLE, TITLE, CONTENT_TOP, CONTENT_BOTTOM, IMAGES_DISPLAY, TEMPLATE_ID, SHOW_INDEX)
				values 
				('$_POST[archive_title]','$_POST[title]','$_POST[content_top]','$_POST[content_bottom]','$_POST[images_display]','$template_id', '$_POST[show_index]')";
			mysql_query($sql);
		// Check for temporary files 
		if ($_POST[newsletter_id_temporary] != "") {
			$insert_id = mysql_insert_id();		
			// Make temp newsletter_items permanent
			$sql = "update NEWSLETTER_ITEMS 
						set NEWSLETTER_ID = '$insert_id', TEMPORARY = '0' 
						where NEWSLETTER_ID = $_POST[newsletter_id_temporary]";
			mysql_query($sql);
			// Make temp category associations permanent
			$sql = "update NEWSLETTER_NEWSLETTER_CATEGORIES 
						set NEWSLETTER_ID = '$insert_id', TEMPORARY = '0' 
						where NEWSLETTER_ID = $_POST[newsletter_id_temporary]";
			mysql_query($sql);
		}	
	} else {
		// update
		$sql = "update NEWSLETTERS
				set TITLE = '$_POST[title]',
				ARCHIVE_TITLE = '$_POST[archive_title]',
				CONTENT_TOP = '$_POST[content_top]',
				CONTENT_BOTTOM = '$_POST[content_bottom]',
				IMAGES_DISPLAY = '$_POST[images_display]',
				SHOW_INDEX = '$_POST[show_index]'
				where ID = $_POST[newsletter_id]";
				mysql_query($sql);
	}
}

function createContentIndex($newsletter_id){
	$sql = "select ID, HEADING from NEWSLETTER_ITEMS where NEWSLETTER_ID = '$newsletter_id' and DELETED='0' order by POSITION asc";
	$res = mysql_query($sql);
	while ($row = mysql_fetch_assoc($res)){
		$html .= "<li class='content_index_item'><a href='#item_$row[ID]'>$row[HEADING]</a></li>";
	}
	return $html;
}

function show_newsletter_stats($newsletter_id){
	// Get reader count
	$sql = "select count(ID) as READER_COUNT, SUM(TIMES_REPEATED) as TOTAL_READS from NEWSLETTER_STATS where NEWSLETTER_ID = '$newsletter_id' and USER_ACTION = 'open'";
	$res = mysql_query($sql);
	$row = mysql_fetch_assoc($res);
	$html .= "<h1>Statistik for nyhedsbrevet '";
	$html .= returnFieldValue("NEWSLETTERS", "ARCHIVE_TITLE", "ID", $newsletter_id);
	$template_id = returnFieldValue("NEWSLETTERS", "TEMPLATE_ID", "ID", $newsletter_id);
	$html .= "'</h1>";
	$recipient_count .= returnFieldValue("NEWSLETTER_HISTORY", "NO_RECIPIENTS", "NEWSLETTER_ID", $newsletter_id);
	if ($row[READER_COUNT]>0) {
		$read_percentage = round(($row[READER_COUNT]/$recipient_count)*100);
	} else {
		$read_percentage = "0";
	}


	$sql = "select COUNT(ID) from NEWSLETTER_STATS where NEWSLETTER_ID = '$newsletter_id' and USER_ACTION = 'click' and CLICKED_URL like '%action=unsubscribe%'";
	$res = mysql_query($sql);
	$unsubscribe_count = mysql_result($res,0);

	$html .= "<div class='feltblok_wrapper'><h2>Læsere</h2>";
	$html .= "<table class='oversigt' style='width: 400px;'>";
	$html .= "<tr><td class='kolonnetitel'>Antal modtagere</td><td style='text-align: right;'>$recipient_count</td></tr>";
	$html .= "<tr><td class='kolonnetitel'>Antal læsere (*)</td><td style='text-align: right;'>$row[READER_COUNT] ($read_percentage%)</td></tr>";
	$html .= "<tr><td class='kolonnetitel'>Antal læsere der har afmeldt nyhedsbrevet (**)</td><td style='text-align: right;'>$unsubscribe_count</td></tr>";
	$html .= "</table>";
	$html .= "<p>(*) En læser er en abonnent, som har åbnet nyhedsbrevet i sin mail klient. Det reelle tal kan være højere, da det ikke er muligt at modtage data fra alle abonnenter.</p><p>(**) Dette tal viser alene hvor mange, læsere der har klikket på nyhedsbrevets 'Afmeld'-link. Afmelding skal efterfølgende bekræftes på hjemmesiden. Det reelle tal kan derfor være lavere. </p>";
	$html .= "<h2>Det har læserne klikket på (kun klik på interne links registreres)</h2>";
	$html .= "<table class='oversigt'><tr><td class='kolonnetitel'>Link</td><td class='kolonnetitel'>Antal klik</td><td class='kolonnetitel'>Funktioner</td></tr>";

	$sql = "select CLICKED_URL, sum(TIMES_REPEATED) as CLICKCOUNT, COUNT(ID) as CLICKCOUNT_UNIQUE from NEWSLETTER_STATS where NEWSLETTER_ID = '$newsletter_id' and USER_ACTION = 'click' and CLICKED_URL not like '%action=unsubscribe%' group by CLICKED_URL order by CLICKCOUNT desc";
	$res = mysql_query($sql);
	while ($row = mysql_fetch_assoc($res)) {
		$urlurl = rawurlencode($row[CLICKED_URL]);
		$html .= "<tr><td><a href='$row[CLICKED_URL]' target='_blank'>$row[CLICKED_URL]</a></td><td>$row[CLICKCOUNT] klik fra $row[CLICKCOUNT_UNIQUE] læsere</td><td><input type='button' class='lilleknap' value='Hvem har klikket?' onclick='location=\"index.php?content_identifier=newsletter&amp;dothis=stats_url&amp;nid=$newsletter_id&amp;clickedurl=$urlurl\"' /></td></tr>";
	}
	$html .= "</table>";
	$html .= "<div class='knapbar'>
					<input type='button' value='Til oversigten' onclick='location=\"index.php?content_identifier=newsletter&amp;filter_template=$template_id\"' />
				</div>";
	$html .= "</div>";
	return $html;
}

function show_newsletter_stats_url(){
	$html .= "<h1>Statistik for nyhedsbrevet '";
	$html .= returnFieldValue("NEWSLETTERS", "ARCHIVE_TITLE", "ID", $_GET[nid]);
	$html .= "'</h1>";
	$html .= "<div class='feltblok_wrapper'><h2>Hvem har klikket på '$_GET[clickedurl]'?</h2>";
	$html .= "<table class='oversigt'>";
	$html .= "<tr><td class='kolonnetitel'>Abonnent</td><td class='kolonnetitel'>Antal klik</td><td class='kolonnetitel'>Seneste klik</td></tr>";
	$sql = "select U.FIRSTNAME, U.LASTNAME, U.EMAIL, NS.TIMES_REPEATED, UNIX_TIMESTAMP(NS.CREATED_DATE) as CREATED, UNIX_TIMESTAMP(NS.CHANGED_DATE) as CHANGED from NEWSLETTER_STATS NS, USERS U where NS.NEWSLETTER_ID = '$_GET[nid]' and NS.CLICKED_URL = '$_GET[clickedurl]' and NS.USER_ID = U.ID order by UNIX_TIMESTAMP(NS.CHANGED_DATE) desc, UNIX_TIMESTAMP(NS.CREATED_DATE) desc";
	$res = mysql_query($sql);
	while ($row = mysql_fetch_assoc($res)) {
		if ($row[CHANGED]>0) {
			$latest = returnNiceDateTime($row[CHANGED], 1, 1);
		} else {
			$latest = returnNiceDateTime($row[CREATED], 1, 1);
		}
		$html .= "<tr><td>$row[FIRSTNAME] $row[LASTNAME] ($row[EMAIL])</td><td>$row[TIMES_REPEATED]</td><td>$latest</td></tr>";
	}
	$html .= "</table>";
		$html .= "<div class='knapbar'>
					<input type='button' value='Tilbage til statistik-oversigten' onclick='location=\"index.php?content_identifier=newsletter&amp;dothis=stats&amp;nid=$_GET[nid]\"' />
				</div>";
	$html .= "</div>";

	return $html;
}

?>