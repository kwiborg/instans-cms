<?php
function newsletterTemplateForm() {
	if (!$_GET[ntid]) {
		$html = "<h1>Opret ny skabelon</h1>";

		// Default variabler
		$nt_open = 1;
		$nt_email_validation = 1;
		
	} else {
		$sql = "select * from NEWSLETTER_TEMPLATES where ID = '$_GET[ntid]' and DELETED = '0' and SITE_ID = '$_SESSION[SELECTED_SITE]' limit 1";
		$result = mysql_query($sql);
		$t = mysql_fetch_array($result);

		// Variabler hentet fra DB
		$nt_title = $t[TITLE];
		$nt_open = $t[OPEN_FOR_SUBSCRIPTIONS];
		$nt_newsarchive = $t[SHOW_IN_NEWSARCHIVE];
		$nt_language = $t[LANGUAGE_ID];
		$nt_template = $t[TEMPLATE_ID];
		$nt_subform_top = $t[SUBSCRIPTIONPAGE_TEXTTOP];
		$nt_subform_bottom = $t[SUBSCRIPTIONPAGE_TEXTBOTTOM];
		$nt_subform_thanks = $t[SUBSCRIPTIONPAGE_TEXTTHANKS];
		$nt_email_validation = $t[REQ_EMAIL_VALIDATION];
		$nt_sender_name = $t[SENDER_NAME];
		$nt_sender_email = $t[SENDER_EMAIL];
		$nt_replyto_email = $t[REPLYTO_EMAIL];
		$nt_bounce_email = $t[BOUNCETO_EMAIL];
		$nt_notifymails = $t[NEWSUBSCRIBER_NOTIFY_EMAIL];
		$html = "<h1>Rediger skabelonen '$t[TITLE]'</h1>";
	}
	$html .= "<form action='' method='post' name='form_nt' id='form_nt'>";	
	$html .= "<ul id='tablist'>
					<li><a href='#' class='current' onClick='return expandcontent(\"sc1\", this)'>Generelt</a></li>
					<li><a href='#' onClick='return expandcontent(\"sc2\", this)'>Afsender</a></li>
					<li><a href='#' onClick='return expandcontent(\"sc3\", this)'>Tilmeldingsformular</a></li>
					<li><a href='#' onClick='return expandcontent(\"sc4\", this)'>Interessekategorier</a></li>
			</ul>";
	$html .= "<div id='tabcontentcontainer'>
					\n<div id='sc1' class='tabcontent'>";
	if (!$_GET[ntid]) {
		$dothis = "insert";
	} else {
		$dothis = "update";
	}
	$html .= "<input type='hidden' name='dothis' value='$dothis' />";	
	$html .= "<input type='hidden' name='id' value='$_GET[ntid]' />";	
	$html .= "<h2>Titel på nyhedsbrev</h2>
			<p class='feltkommentar'>Skriv den overordnede titel på nyhedsbrevet - f.eks. Produktnyheder. Den enkelte udgave af nyhedsbrevet kan du give et andet navn.</p>
			<input type='text' name='nt_title' id='nt_title' value='$nt_title' class='inputfelt' />
			<h2>Åben for nye abonnenter</h2>";
	$html .= createSelectYesNo("nt_open", $nt_open);
	$html .= "<h2>Vis i offentlig nyhedsarkiv</h2>";
	$html .= createSelectYesNo("nt_newsarchive", $nt_newsarchive);
	$html .= "<h2>Sprog</h2>";
	$html .= buildLanguageDropdown($nt_language, false, "nt_language");

			
	$html .= "<h2>Grafisk template</h2>\n";
	$html .= buildTemplateDropdown($nt_template, "nt_template", "NEWSLETTER");
	$html .= "<h2>Besked ved nye tilmeldinger</h2>
			<p class='feltkommentar'>Skriv den/de e-mail-adresser, som skal have en besked, når en ny person tilmelder sig nyhedsbrevet. Hvis du skriver flere adresser, skal de adskilles med komma.</p>
			<input type='text' name='nt_notifymails' id='nt_notifymails' value='$nt_notifymails' class='inputfelt' />
		";
	$html .= "</div>\n<div id='sc2' class='tabcontent'>";
	$html .= "<h2>Navn på afsender</h2>
				<input type='text' name='nt_sender_name' id='nt_sender_name' value='$nt_sender_name' class='inputfelt' />";
	$html .= "<h2>Afsender e-mail</h2>
				<p class='feltkommentar'>Vær så specifik som mulig. Mange modtagere bruger afsender adressen til at vurdere om de skal åbne e-mailen. F.eks. nyhedsbrev@mitdomæne.dk</p>
				<input type='text' name='nt_sender_email' id='nt_sender_email' value='$nt_sender_email' class='inputfelt' />";
	$html .= "<h2>Svar e-mail</h2>
				<p class='feltkommentar'>Det er en god idé at oprette en e-mail adresse specifikt til svar på nyhedsbreve. Du kan vælge at oprette en seperat adresse til hver template for at adskille svar fra forskellige nyhedsbreve. Eksempel på adresse: nyhedsbrev-svar@mitdomæne.dk</p>
				<input type='text' name='nt_replyto_email' id='nt_replyto_email' value='$nt_replyto_email' class='inputfelt' />";
	$html .= "<h2>Fejl e-mail</h2>
				<p class='feltkommentar'>Du har mulighed for at specificere hvilken adresse, der skal modtage evt. meddelelser om at nyhedsbrevet ikke kunne leveres. Det er vigtigt at følge op på fejlleverancer, så evt. nedlagte e-mail adresser kan blive fjernet. Eksempel på adresse: nyhedsbrev-fejl@mitdomæne.dk</p>
				<input type='text' name='nt_bounce_email' id='nt_bounce_email' value='$nt_bounce_email' class='inputfelt' />";
	$html .= "</div>\n<div id='sc3' class='tabcontent'>";
	$html .= "<h2>Tekst <em>over</em> tilmeldingsformular</h2>
				<textarea name='nt_subform_top' id='nt_subform_top' class='inputfelt'>$nt_subform_top</textarea>
				<h2>Tekst <em>under</em> tilmeldingsformular</h2>
				<textarea name='nt_subform_bottom' id='nt_subform_bottom' class='inputfelt'>$nt_subform_bottom</textarea>
				<h2>Tekst til 'Tak for din tilmelding'-siden</h2>
				<textarea name='nt_subform_thanks' id='nt_subform_thanks' class='inputfelt'>$nt_subform_thanks</textarea>
				<h2>Abonnent skal bekræfte e-mail adresse</h2>
				<p class='feltkommentar'>For at undgå at modtagerlisterne bliver fyldt med ugyldige e-mail adresser, skal nye abonnenter bekræfte deres e-mail adresse ved at klikke på et link i en fremsendt mail senest 72 timer efter de har tilmeldt sig.</p>";
	$html .= createSelectYesNo("nt_email_validation", $nt_email_validation);
	$html .= "<h2>Felter på tilmeldingsformular (i tillæg til e-mail)</h2>";
	$html .= newsletter_return_allowed_formfields($_GET[ntid]);
	$html .= "</div>\n<div id='sc4' class='tabcontent'>";
	$html .= "<h2>Interessekategorier</h2>";
	$html .= "<p class='feltkommentar'>Vælg de interessegrupper, som er relevant for dette nyhedsbrev. Det er disse kategorier, kunden kan vælge at afkrydse, når han/hun tilmelder sig.</p>";
	$html .= newsletter_returnGroupedCategories();
	$html .= "</div>"; // Last tab
	$html .= "</div>"; // Tab container
	$html .= "<div class='knapbar'>
					<input type='button' value='Afbryd' onclick='location=\"index.php?content_identifier=newslettertemplates\"' />
					<input type='button' value='Gem ændringer' onclick='saveTemplate()' />
			</div>";

	$html .= "</form>";
	return $html;
}

function newsletter_return_allowed_formfields($template_id){
	global $newsletter_allowed_db_fields_on_form;
	$sql = "
		select 
		ID, FIELD_NAME, TABLE_NAME, TEMPLATE_TAG, POSITION, TEMPLATETAG_ONLY, CMS_LABEL
		from NEWSLETTER_FORMFIELDS 
		order by TABLE_NAME asc, POSITION asc
	";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)){
		$field_names[$row[TABLE_NAME]][$row[FIELD_NAME]] = array(
			"FIELD_NAME" => $row["FIELD_NAME"],
			"FIELD_LABEL" => $row["CMS_LABEL"],
			"FIELD_ID" => $row["ID"],
			"TEMPLATETAG_ONLY" => $row["TEMPLATETAG_ONLY"]
		);
	}
	foreach($field_names as $tablename => $fields){
		$html .= "<table cellpadding='2' cellspacing='2'>";
		foreach ($fields as $db_fieldname => $fielddata){
			$sql = "select ID, MANDATORY from NEWSLETTER_TEMPLATES_FORMFIELDS where FIELD_ID='".$fielddata[FIELD_ID]."' and TEMPLATE_ID='$template_id'";
			$result_dbfields = mysql_query($sql);
			$row_dbfields = mysql_fetch_assoc($result_dbfields);
			if ($fielddata[TEMPLATETAG_ONLY] == 0){
				$html .= "
					<tr>
						<td align='right'>".($fielddata[FIELD_LABEL] ? $fielddata[FIELD_LABEL] : $db_fieldname)."</td>
						<td align='center'>
							<select class='inputfelt_kort' name='fieldid_".$fielddata[FIELD_ID]."'>
								<option value='0'".(!mysql_num_rows($result_dbfields) ? " selected " : "").">Kan ikke udfyldes</option>
								<option value='1'".(($row_dbfields["ID"] && $row_dbfields["MANDATORY"]==0) ? " selected " : "").">Kan udfyldes</option>
								<option value='2'".(($row_dbfields["ID"] && $row_dbfields["MANDATORY"]==1) ? " selected " : "").">SKAL udfyldes</option>
							</select>
						</td>
					</tr>
				";
			}						
		}
		$html .= "</table>";
	}
	return $html;
}

function listNewsletterTemplates() {
	$html = "<h1>Skabeloner for nyhedsbrev</h1>
			<div class='feltblok_header'></div>
			<div class='feltblok_wrapper'>";
	$sql = "select * from NEWSLETTER_TEMPLATES where DELETED = 0 and SITE_ID = '$_SESSION[SELECTED_SITE]'";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$html .= "<table class='oversigt'>
				<tr class='trtop'>
					<td class='kolonnetitel'>Titel</td>
					<td class='kolonnetitel'>Sidst udsendt</td>
					<td class='kolonnetitel'>Funktioner</td>
				</tr>";
		while ($row = mysql_fetch_array($result)) {
			$sql = "select UNIX_TIMESTAMP(SENDOUT_COMPLETETIME) as SENT_TIME, NO_RECIPIENTS from NEWSLETTER_HISTORY where TEMPLATE_ID = '$row[ID]' and (UNIX_TIMESTAMP(SENDOUT_COMPLETETIME) > 0) order by Date(SENDOUT_COMPLETETIME) desc, Time(SENDOUT_COMPLETETIME) desc LIMIT 1";
			$result2 = mysql_query($sql);
			if (mysql_num_rows($result2) > 0) {
				$th = mysql_fetch_array($result2);
				$lastsent = returnNiceDateTime($th[SENT_TIME], 1)."<br />til $th[NO_RECIPIENTS] modtagere";
			} else {
				$lastsent = "Endnu ikke udsendt";
			}
			

			$html .= "<tr>
					<td>$row[TITLE]</td>
					<td>$lastsent</td>
					<td>
						<input type='button' class='lilleknap' value='Slet'  onclick='if (confirm(\"Vil du slette skabelonen?\")) location=\"index.php?content_identifier=newslettertemplates&amp;dothis=delete&amp;ntid=$row[ID]\"' />
						<input type='button' class='lilleknap' value='Historik' onclick='location=\"index.php?content_identifier=newslettertemplates&amp;dothis=history&amp;ntid=$row[ID]\"' ";
			if (mysql_num_rows($result2) == 0) {
				$html .= "disabled ";
			}
			$html .= "/>
						<input type='button' class='lilleknap' value='Abonnenter' onclick='location=\"index.php?content_identifier=newslettersubscribers&template_id=$row[ID]\"' />
						<input type='button' class='lilleknap' value='Andre modtagere' onclick='location=\"index.php?content_identifier=newslettertemplates&amp;dothis=recipients_group&amp;ntid=$row[ID]\"' />
						<input type='button' class='lilleknap' value='Rediger' onclick='location=\"index.php?content_identifier=newslettertemplates&amp;dothis=rediger&amp;ntid=$row[ID]\"' />
					</td>
				</tr>";
		}
		$html .= "</table>";
	} else {
		$html .= "Der er ikke oprettet nogen skabeloner.";
	}
	$html .= "</div>";
	$html .= "<div class='knapbar'>
					<input type='button' value='Opret ny skabelon' onclick='location=\"index.php?content_identifier=newslettertemplates&amp;dothis=opret\"' />
				</div>";
	return $html;
}

function newsletter_returnGroupedCategories(){			
		$sql = "
			select 
				NCG.NAME as GROUP_NAME, NCG.ID as GROUP_ID
			from 
				NEWSLETTER_CATEGORYGROUPS NCG 
			where
				NCG.SITE_ID in (0,'$_SESSION[SELECTED_SITE]') and
				NCG.DELETED='0' 
			order by
				NCG.NAME asc
		";
		$result = mysql_query($sql);
		if (mysql_num_rows($result)){
			while ($row = mysql_fetch_array($result)){
				// Is checked?
				if ($_GET[ntid]) {
	
					$sql = "select count(*) 
					from 
						NEWSLETTER_TEMPLATES_CATEGORYGROUPS NTC
					where 
						NTC.TEMPLATE_ID = $_GET[ntid]
					and
						NTC.CATEGORYGROUP_ID = $row[GROUP_ID]";
					$checked_result = mysql_query($sql);
					if (mysql_result($checked_result,0) > 0) {
						$row[CHECKED] = "checked";
					}
				}				


				$html .= "
					<p>
						<input type='checkbox' name='catgroup_$row[GROUP_ID]' value='$row[GROUP_ID]' $row[CHECKED] />&nbsp;<strong>$row[GROUP_NAME]</strong>
				";
				$sql = "
					select NC.NAME as CAT_NAME, NC.ID as CAT_ID
					from NEWSLETTER_CATEGORIES NC
					where NC.DELETED='0' and NC.GROUP_ID='$row[GROUP_ID]'
					order by NC.NAME asc
				";
				
				$result2 = mysql_query($sql);
				if (mysql_num_rows($result2) == 0){
					$html .= "(Der er ikke oprettet nogen interessekategorier i denne gruppe endnu.)";
				} else {
					while ($row2 = mysql_fetch_array($result2)){
						$html .= "&nbsp;($row2[CAT_NAME])";
					}
				}
				$html .= "</p>";

			}
		} else {
			$html .= "<p>Der er ikke oprettet nogen interessekategorier.</p>";
		}
		return $html;
}
function recipientUsergroupsForm($ntid) {
	$html .= "<form action='' method='post' name='form_nt' id='form_nt'>";	
	$html .= "<input type='hidden' name='dothis' value='update_recipientgroups' />";	
	$html .= "<input type='hidden' name='id' value='$_GET[ntid]' />";	

	$html .= "<h1>Andre modtagere</h1><br/>
			<div class='feltblok_wrapper'>
			<h2>Send også nyhedsbrev til medlemmer af følgende brugergrupper</h2>";
	$sql = "select GROUPS.*, COUNT(USERS_GROUPS.USER_ID) as NO_USERS
			from GROUPS, USERS_GROUPS, USERS 
			where GROUPS.ID = USERS_GROUPS.GROUP_ID
				and GROUPS.SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
				and GROUPS.HIDDEN = '0' 
				and GROUPS.DELETED = '0' 
				and GROUPS.UNFINISHED = '0'
				and USERS.ID = USERS_GROUPS.USER_ID
				and USERS.DELETED = '0'
				and USERS.UNFINISHED = '0'
			group by GROUPS.ID
			order by GROUPS.GROUP_NAME asc";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result)) {
		// Is checked?
		$sql = "select count(*) 
		from 
			NEWSLETTER_TEMPLATES_USERGROUPS NTU
		where 
			NTU.TEMPLATE_ID = $_GET[ntid]
		and
			NTU.GROUP_ID = $row[ID]";
		$checked_result = mysql_query($sql);
		if (mysql_result($checked_result,0) > 0) {
			$row[CHECKED] = "checked";
		}
		$html .= "<p><input type='checkbox' name='usergroup_$row[ID]' value='$row[ID]' $row[CHECKED] />&nbsp;<strong>$row[GROUP_NAME]</strong> ($row[NO_USERS] modtager";
		if($row[NO_USERS] != 1) { $html .= "e"; }
		$html .= ")</p>";		
	}
	$html .= "<div class='knapbar'>
					<input type='button' value='Afbryd' onclick='location=\"index.php?content_identifier=newslettertemplates\"' />
					<input type='button' value='Gem ændringer' onclick='saveTemplate()' />
			</div>";
	$html .= "</div></form>";
	return $html;
}

/*
function returnRecipientCount($ntid) {
	$sql = "(select distinct NTU.USER_ID
			from NEWSLETTER_TEMPLATES_USERS NTU
			where NTU.TEMPLATE_ID = $ntid)
			UNION DISTINCT
			(select distinct UG.USER_ID
			from NEWSLETTER_TEMPLATES_USERGROUPS NTUG, USERS_GROUPS UG, USERS U
			where NTUG.TEMPLATE_ID = $ntid
			and U.ID not in (select USER_ID from NEWSLETTER_OPTOUT where TEMPLATE_ID = $ntid)
			and U.DELETED = '0'
			and U.UNFINISHED = '0'
			and U.ID = UG.USER_ID
			and NTUG.GROUP_ID = UG.GROUP_ID)";
	return mysql_num_rows(mysql_query($sql));
}
*/

function templateHistory($ntid) {
	$html .= "<h1>Historik: '";
	$html .= returnFieldValue("NEWSLETTER_TEMPLATES", "TITLE", "ID", "$ntid");
	$html .= "'</h1>
			<div class='feltblok_header'></div>
			<div class='feltblok_wrapper'>";
	$sql = "select 
	           SENDOUT_SUBJECT,
	           NEWSLETTER_ID, 
	           NO_RECIPIENTS, 
	           UNIX_TIMESTAMP(SENDOUT_COMPLETETIME) as SENDOUT_COMPLETETIME
	       from NEWSLETTER_HISTORY
	       where 
	           TEMPLATE_ID = '$ntid' and 
	           (UNIX_TIMESTAMP(SENDOUT_COMPLETETIME) > 0) 
	       order by 
	           Date(SENDOUT_COMPLETETIME) desc, 
	           Time(SENDOUT_COMPLETETIME) desc";

	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$html .= "<table class='oversigt'>
				<tr class='trtop'>
					<td class='kolonnetitel'>Titel</td>
					<td class='kolonnetitel'>Modtagere</td>
					<td class='kolonnetitel'>Udsendt</td>
				</tr>";
		while ($row = mysql_fetch_array($result)) {
            $html .= "<tr>
                        <td>$row[SENDOUT_SUBJECT]</td>
                        <td>$row[NO_RECIPIENTS] 
                        <input type='button' class='lilleknap' value='Statistik'  onclick='location=\"index.php?content_identifier=newsletter&amp;dothis=stats&amp;nid=$row[NEWSLETTER_ID]\"' />
                        </td>
                        <td>";
            $html .= returnNiceDateTime($row[SENDOUT_COMPLETETIME], 1);            
            $html .= "</td></tr>";
        }
        $html .= "</table>";
    } else {
        $html .= "<p>Der er endnu ikke udsendt et nyhedsbrev baseret p&aring; denne skabelon.</p>";
    }
/*	
	niceTableListing(
	mysql_query($sql), 
	array(
		"NEWSLETTER_ID" => "", 
		"NO_RECIPIENTS" => "", 
		"SENDOUT_COMPLETETIME" => ""
		), 
	array(
		"NEWSLETTER_ID" => "Titel", 
		"NO_RECIPIENTS" => "Antal modtagere", 
		"SENDOUT_COMPLETETIME" => "Udsendt"
		), 
	array(
		"NEWSLETTER_ID" => "return '__NEWSLETTER_ID__';",
		"NO_RECIPIENTS" => "return '__NO_RECIPIENTS__';",
		"SENDOUT_COMPLETETIME" => "return returnNiceDateTime((int)__SENDOUT_COMPLETETIME__, 1);"
	));
*/
	$html .= "<div class='knapbar'>
					<input type='button' value='Tilbage til oversigten' onclick='location=\"index.php?content_identifier=newslettertemplates\"' />
				</div>";
return $html;
}
?>