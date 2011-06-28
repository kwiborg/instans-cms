<h1>Nyhedsbrev: Abonnenter</h1>
	<?php 
		if (!$_GET[dothis]){
			if (!$_GET[offset] || $_GET[offset]<0 ){
				$offset = 0;
			} else {
				$offset = $_GET[offset];
			}
			$html .= '<div class="feltblok_header">Abonnenter på '.($_GET[template_id] ? returnFieldValue("NEWSLETTER_TEMPLATES", "TITLE", "ID", $_GET[template_id]) : 'alle nyhedsbreve').'</div>';
			$html .= '<div class="feltblok_wrapper">';
			$sql = "
				select 
					distinct NS.USER_ID, U.EMAIL,  U.EMAIL_VERIFIED, U.FIRSTNAME, U.LASTNAME
				from 
					NEWSLETTER_SUBSCRIPTIONS NS, 
					USERS U
				where 
					U.ID=NS.USER_ID ".($_GET[template_id]?"and NS.TEMPLATE_ID='$_GET[template_id]'":"")."
					and NS.SUBSCRIBED='1'
					and U.DELETED = '0'
				order by 
					".($_GET[orderby]?$_GET[orderby]:"EMAIL")." ".$_GET["dir"]."
				limit $offset, 100
			";		
			$temp = niceTableListing2(
					$sql, 
					array("FIRSTNAME"=>"", "LASTNAME"=>"", "EMAIL"=>"", "EMAIL_VERIFIED"=>"", "USER_ID"=>""), 
					array("EMAIL"=>"E-mail", "EMAIL_VERIFIED"=>"Verificeret", "FIRSTNAME"=>"Fornavn", "LASTNAME"=>"Efternavn"), 
					array("EMAIL"=>"return email_verified('__EMAIL__', __EMAIL_VERIFIED__);", "EMAIL_VERIFIED"=>"return (__EMAIL_VERIFIED__==1?'Ja':'Nej');"),
					array(
						"Funktioner" => 
							"
								<input type='button' value='Abonnementer' class='lilleknap' onclick='location=\"index.php?content_identifier=newslettersubscribers&amp;dothis=show_subscriptions&amp;user_id=__USER_ID__\"' />
								<input type='button' value='Brugerprofil' class='lilleknap' onclick='location=\"index.php?content_identifier=users&amp;dothis=rediger&amp;id=__USER_ID__\"' />
							"
					),
					"Ikke flere abonnenter på dette nyhedsbrev."
			);
			$numrows = $temp[0];
			$html .= "
				<div class='knapbar'>
					<input ".($offset==0?"disabled":"")." type='button' value='&laquo; Forrige 100' onclick='location=\"index.php?content_identifier=newslettersubscribers&amp;template_id=".$_GET[template_id]."&amp;offset=".($offset-100)."\"' />
					<input ".($numrows<100?"disabled":"")." type='button' value='Næste 100 &raquo;' onclick='location=\"index.php?content_identifier=newslettersubscribers&amp;template_id=".$_GET[template_id]."&amp;offset=".(100+$offset)."\"' />
				</div>
			";
			$html .= $temp[1];
			$html .= "
				<div class='knapbar'>
					<input ".($offset==0?"disabled":"")." type='button' value='&laquo; Forrige 100' onclick='location=\"index.php?content_identifier=newslettersubscribers&amp;template_id=".$_GET[template_id]."&amp;offset=".($offset-100)."\"' />
					<input ".($numrows<100?"disabled":"")." type='button' value='Næste 100 &raquo;' onclick='location=\"index.php?content_identifier=newslettersubscribers&amp;template_id=".$_GET[template_id]."&amp;offset=".(100+$offset)."\"' />
				</div>
			";
//			$html .= "</div>";			
			echo $html;
		} else if ($_GET[dothis] == "show_subscriptions" && $_GET[user_id]){
			$sql = "
				select 
					NT.TITLE,
					NS.TEMPLATE_ID, NS.SUBSCRIBED, NS.CHANGED_DATE, NS.CONFIRMED,
					U.EMAIL, U.FIRSTNAME, U.LASTNAME, U.EMAIL_VERIFIED
				from 
					NEWSLETTER_SUBSCRIPTIONS NS, 
					USERS U,
					NEWSLETTER_TEMPLATES NT
				where 
					U.ID=NS.USER_ID 
					and NS.USER_ID='".$_GET[user_id]."'
					and NT.ID=NS.TEMPLATE_ID
					and NS.SUBSCRIBED='1'
				order by 
					".($_GET[orderby]?$_GET[orderby]:"TITLE")." ".$_GET["dir"]."
			";		
			$result = mysql_query($sql);
			if (mysql_num_rows($result)){
				$row = mysql_fetch_assoc($result);
				mysql_data_seek($result, 0);
			}
			$html .= '<div class="feltblok_header">Abonnementer for <span class="yellow">'.returnFieldValue("USERS", "EMAIL", "ID", $_GET[user_id])." / ".returnFieldValue("USERS", "FIRSTNAME", "ID", $_GET[user_id])." ".returnFieldValue("USERS", "LASTNAME", "ID", $_GET[user_id]).'</span></div>';
			$html .= '<div class="feltblok_wrapper">';
			$temp = niceTableListing2(			
				$sql,
				array("TITLE"=>"", "CHANGED_DATE"=>"", "TEMPLATE_ID"=>"", "EMAIL"=>"", "CONFIRMED"=>""),
				array("TITLE"=>"Nyhedsbrev", "CHANGED_DATE"=>"Tidspunkt for tilmelding", "CONFIRMED"=>"Verificeret"),
				array("CHANGED_DATE"=>"return returnNiceDateTime((int)__CHANGED_DATE__, 1);", "CONFIRMED"=>"return (__CONFIRMED__==1?'Ja':'Nej');"),
				array("Funktioner"=>"<input type='button' class='lilleknap' value='Frameld abonnement' onclick='newsletter_unsubscribe($_GET[user_id], __TEMPLATE_ID__, \"__EMAIL__\", \"__TITLE__\")' />"),
				"Ingen abonnementer for denne bruger."
			);
			$html .= $temp[1];
			$html .= "<div class='knapbar'><input type='button' class='lilleknap' value='Tilbage til oversigten' onclick='location=\"index.php?content_identifier=newslettersubscribers\"'/></div>";
//			$html .= "</div>";
			
			echo $html;
		}
	?>
</div>

