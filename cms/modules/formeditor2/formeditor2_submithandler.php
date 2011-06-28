<?php
	if (!$_SESSION["CMS_USER"]){
		header("location: ../../login.php");
	}
	checkPermission("CMS_FORMS", true);
 
	if ($_POST[dothis] == "save_mappings"){
		save_mappings($_POST);
		header("location: index.php?content_identifier=formeditor2");
	}
	
	if ($dothis=="slet"){
		sletRow($formid, "DEFINED_FORMS");
		header("location: index.php?content_identifier=formeditor2&dothis=");
	}

	if ($dothis=="generalformsettings"){
		if ($mode=="rediger"){
			$datarow = hentRow($formid, "DEFINED_FORMS");
		}
 	}
 
	if ($dothis == "attachform" && $pageid && $formid){
  		$sql = "insert into PAGES_FORMS (PAGE_ID, FORM_ID) values ('$pageid', '$formid')";
  		$result = mysql_db_query($dbname, $sql) or die(mysql_error());  
  		header("location: index.php?content_identifier=pages&returntoid=$pageid&returnedfrom=formeditor&menuid=$menuid");
  		exit;
 	}
	
	if ($dothis == "save_general" && (($formid && $mode=="rediger") || $mode=="opret")){
		if ($form_save_email == "on"){
			$form_save_email = "1";
		}
  		if ($form_save_database == "on"){
			$form_save_database = "1";
		}
		$nu = time();
		$form_opendate = trim($form_opendate);
		$form_closedate = trim($form_closedate);
  		if ($form_opendate != ""){
  			$form_opendate = reverseDate($form_opendate);
  		}
  		if ($form_closedate != ""){
  			$form_closedate = reverseDate($form_closedate);
  		}
  		if ($mode=="opret"){
   			$sql = "
				insert into DEFINED_FORMS 
	  			(
					TITLE, EMAIL, CREATED_DATE, AUTHOR_ID, INTROTEXT, ENDTEXT, FORM_OPENDATE, 
					FORM_CLOSEDATE, LINKTEXT, SITE_ID, SEND_MAIL, SAVE_IN_DB, MAPPED_NEWSLETTER_ID, 
					MAPPED_USERGROUP_ID, SPAMPREVENT_CAPTCHA
				) values 
		 		(
					'$form_title', '$form_email', '$nu', '" . $_SESSION[CMS_USER][USER_ID] . "', '$form_introtext', 
					'$form_endtext', '$form_opendate', '$form_closedate', '$form_linktext', 
					'$_SESSION[SELECTED_SITE]', '$form_save_email', '$form_save_database', 
					'$_POST[mapped_newsletter_id]', '$_POST[mapped_usergroup_id]', '$_POST[form_spamprevent_captcha]'
				)
			";
   			$result = mysql_db_query($dbname, $sql) or die(mysql_error());  
   			$ny_form_id = mysql_insert_id();
   			if ($standardform == "on"){
				$sql = array();
    			$sql[] = "insert into DEFINED_FORMFIELDS (FORM_ID, FIELDTYPE, CAPTION, VERIFY_FILLED, POSITION) values ('$ny_form_id', '1', 'Fornavn:', '1', '1')";
				$sql[] = "insert into DEFINED_FORMFIELDS (FORM_ID, FIELDTYPE, CAPTION, VERIFY_FILLED, POSITION) values ('$ny_form_id', '1', 'Efternavn:', '1', '2')";
				$sql[] = "insert into DEFINED_FORMFIELDS (FORM_ID, FIELDTYPE, CAPTION, VERIFY_FILLED, POSITION) values ('$ny_form_id', '1', 'Adresse:', '1', '3')";
				$sql[] = "insert into DEFINED_FORMFIELDS (FORM_ID, FIELDTYPE, CAPTION, VERIFY_FILLED, VERIFY_NUMBER, TEXT_MAXLENGTH, TEXT_SIZE, POSITION) values ('$ny_form_id', '1', 'Postnummer', '1', '1', '4', '5', '4')";
				$sql[] = "insert into DEFINED_FORMFIELDS (FORM_ID, FIELDTYPE, CAPTION, VERIFY_FILLED, POSITION) values ('$ny_form_id', '1', 'By:', '1', '5')";
				$sql[] = "insert into DEFINED_FORMFIELDS (FORM_ID, FIELDTYPE, CAPTION, VERIFY_FILLED, POSITION) values ('$ny_form_id', '1', 'Tlf:', '1', '6')";
				$sql[] = "insert into DEFINED_FORMFIELDS (FORM_ID, FIELDTYPE, CAPTION, VERIFY_FILLED, VERIFY_EMAIL, POSITION) values ('$ny_form_id', '1', 'E-mail:', '1', '1', '7')";	 
				/// MAKE AUTO FIELDS (INTERESTE GROUPS + "JA TAK")
				if ($_POST[mapped_newsletter_id] > 0){
					$sql[] = "insert into DEFINED_FORMFIELDS (FORM_ID, FIELDTYPE, CAPTION, VERIFY_FILLED, POSITION, LOCKED) values ('$ny_form_id', '5', 'Vil du modtage vort nyhedsbrev?', '1', '8', '1')";				
					// $sql[] = "insert into DEFINED_FORMFIELDS (FORM_ID, FIELDTYPE, CAPTION, VERIFY_FILLED, POSITION, LOCKED) values ('$ny_form_id', '6', 'Interesser', '0', '9', '1')";				
				}
    			foreach ($sql as $str){
	 				$result = mysql_db_query($dbname, $str) or die(mysql_error());  
				}
   			}
  		}
  		if ($mode=="rediger"){
   			$sql = "
				update DEFINED_FORMS set 
   					LINKTEXT='$form_linktext', TITLE='$form_title', EMAIL='$form_email', 
					CREATED_DATE='$nu', AUTHOR_ID='" . $_SESSION[CMS_USER][USER_ID] . "', 
					INTROTEXT='$form_introtext', ENDTEXT='$form_endtext', 
					FORM_OPENDATE='$form_opendate', FORM_CLOSEDATE='$form_closedate', 
					SITE_ID='$_SESSION[SELECTED_SITE]', SEND_MAIL='$form_save_email', 
					SAVE_IN_DB='$form_save_database',
					MAPPED_NEWSLETTER_ID='$_POST[mapped_newsletter_id]', 
					MAPPED_USERGROUP_ID='$_POST[mapped_usergroup_id]',
					SPAMPREVENT_CAPTCHA = '$_POST[form_spamprevent_captcha]'
				where 
					ID='$formid'
			";
			$result = mysql_db_query($dbname, $sql) or die(mysql_error());
   			if(
   				($_POST[mapped_newsletter_id] == 0 && $_POST[mapped_usergroup_id] == 0) || 
				($_POST[mapped_newsletter_id] == 0 && $_POST[old_mapped_newsletter_id] != 0) ||
				($_POST[mapped_usergroup_id] == 0 && $_POST[old_mapped_usergroup_id] != 0)
			){
   				reset_mappings($formid);
   			}
			if ($_POST[old_mapped_newsletter_id] == 0 && $_POST[mapped_newsletter_id] != 0){
				$sql = "insert into DEFINED_FORMFIELDS (FORM_ID, FIELDTYPE, CAPTION, VERIFY_FILLED, POSITION, LOCKED) values ('$formid', '5', 'Vil du modtage vort nyhedsbrev?', '1', '8', '1')";				
				mysql_query($sql);
			}
  		}
  		header("location: index.php?content_identifier=formeditor2");
  		exit;
 	}

 	if ($dothis == "fields" && ($formid && $mode=="rediger")){
	}

	if ($dothis == "gemfelt" && $formid && $mode == "opret"){
		$radiocaptions = str_replace("'", "", $radiocaptions);
  		$checkcaptions = str_replace("'", "", $checkcaptions);
  		$sql = "select POSITION from DEFINED_FORMFIELDS where FORM_ID='$formid' order by POSITION desc limit 1";
  		$result = mysql_db_query($dbname, $sql);
  		$row = mysql_fetch_array($result);
  		$nypos = $row[0]+1;
  		if ($form__felttype_res == 1){
			if ($form_felt_text_verifyfilled == "on") $form_felt_text_verifyfilled = 1;
			if ($form_felt_text_verifyemail == "on") $form_felt_text_verifyemail = 1;
 			if ($form_felt_text_verifynumber == "on") $form_felt_text_verifynumber = 1;
			if ($form_felt_text_disabled == "on") $form_felt_text_disabled = 1;
			if ($form_felt_text_readonly == "on") $form_felt_text_readonly = 1;
			$sql = "
				insert into DEFINED_FORMFIELDS (
	 				FORM_ID, FIELDTYPE, CAPTION, TEXT_SIZE, TEXT_MAXLENGTH, TEXT_DEFAULTTEXT, 
					VERIFY_FILLED, VERIFY_EMAIL, VERIFY_NUMBER, 
	 				DISABLED, READONLY, POSITION, HELPTEXT)
	 			values (
	 				'$formid', '$form__felttype_res', '$form__felttitle', '$form_felt_text_size', 
					'$form_felt_text_maxlength', '$form_felt_text_defaultvalue', '$form_felt_text_verifyfilled', 
					'$form_felt_text_verifyemail', '$form_felt_text_verifynumber', '$form_felt_text_disabled', 
					'$form_felt_text_readonly', '$nypos', '$form_felt_helptext'
				)
   			";
   			$result = mysql_db_query($dbname, $sql);
  		}
		if ($form__felttype_res == 2) {
   if ($form_felt_area_verifyfilled == "on") $form_felt_area_verifyfilled = 1;
   if ($form_felt_area_verifyemail == "on") $form_felt_area_verifyemail = 1;
   if ($form_felt_area_verifynumber == "on") $form_felt_area_verifynumber = 1;
   if ($form_felt_area_disabled == "on") $form_felt_area_disabled = 1;
   if ($form_felt_area_readonly == "on") $form_felt_area_readonly = 1;
   $sql = "
    insert into DEFINED_FORMFIELDS (
	 FORM_ID, FIELDTYPE, CAPTION, TEXTAREA_COLS, TEXTAREA_ROWS, TEXTAREA_MAXLENGTH, TEXTAREA_DEFAULTTEXT, VERIFY_FILLED, VERIFY_EMAIL, VERIFY_NUMBER, 
	 DISABLED, READONLY, POSITION, HELPTEXT)
	 values (
	 '$formid', '$form__felttype_res', '$form__felttitle', '$form_felt_area_cols', '$form_felt_area_rows', '$form_felt_area_maxlength', 
	 '$form_felt_area_defaultvalue', '$form_felt_area_verifyfilled', '$form_felt_area_verifyemail', '$form_felt_area_verifynumber', '$form_felt_area_disabled', '$form_felt_area_readonly', '$nypos', '$form_felt_helptext')
   ";
   $result = mysql_db_query($dbname, $sql);
  }
  if ($form__felttype_res == 3) {
   if ($form_felt_radio_verifyfilled == "on") $form_felt_radio_verifyfilled = 1;
   $sql = "
    insert into DEFINED_FORMFIELDS (
	 FORM_ID, FIELDTYPE, CAPTION, RADIO_COUNT, RADIO_CAPTIONS, RADIO_DISABLEDSTATES, RADIO_SLETTETSTATES, VERIFY_FILLED, POSITION, HELPTEXT)
	 values (
	 '$formid', '$form__felttype_res', '$form__felttitle', '$radiocount', '$radiocaptions', '$radiodisabledstates', '$radioslettetstates', '1', '$nypos', '$form_felt_helptext')
   ";
   $result = mysql_db_query($dbname, $sql);
  }
  if ($form__felttype_res == 4) {
   $sql = "
    insert into DEFINED_FORMFIELDS (
	 FORM_ID, FIELDTYPE, CAPTION, CHECKBOX_COUNT, CHECKBOX_CAPTIONS, CHECKBOX_DISABLEDSTATES, CHECKBOX_SLETTETSTATES, CHECKBOX_MINFILLED, CHECKBOX_MAXFILLED, POSITION, HELPTEXT)
	 values (
	 '$formid', '$form__felttype_res', '$form__felttitle', '$checkboxcount', '$checkcaptions', '$checkdisabledstates', '$checkslettetstates', '$form_felt_check_minfilled', '$form_felt_check_maxfilled', '$nypos', '$form_felt_helptext')
   ";
   $result = mysql_db_query($dbname, $sql);
  }
  header("location: index.php?content_identifier=formeditor2&dothis=fields&mode=rediger&formid=$formid");
  exit;
 }

 if ($dothis == "gemfelt" && $formid && $fieldid && $mode == "rediger") {
  $radiocaptions = str_replace("'", "", $radiocaptions);
  $checkcaptions = str_replace("'", "", $checkcaptions);
  if ($form__felttype_res == 1) {
   if ($form_felt_text_verifyfilled == "on") $form_felt_text_verifyfilled = 1;
   if ($form_felt_text_verifyemail == "on") $form_felt_text_verifyemail = 1;
   if ($form_felt_text_verifynumber == "on") $form_felt_text_verifynumber = 1;
   if ($form_felt_text_disabled == "on") $form_felt_text_disabled = 1;
   if ($form_felt_text_readonly == "on") $form_felt_text_readonly = 1;
   if ($form_felt_text_modtager == "on") $form_felt_text_modtager = 1;
   $sql = "
    update DEFINED_FORMFIELDS set 
	 FIELDTYPE='$form__felttype_res', CAPTION='$form__felttitle', TEXT_SIZE='$form_felt_text_size', 
	 TEXT_MAXLENGTH='$form_felt_text_maxlength', TEXT_DEFAULTTEXT='$form_felt_text_defaultvalue', 
	 VERIFY_FILLED='$form_felt_text_verifyfilled', VERIFY_EMAIL='$form_felt_text_verifyemail', 
	 VERIFY_NUMBER='$form_felt_text_verifynumber', DISABLED='$form_felt_text_disabled', READONLY='$form_felt_text_readonly',
	 EMAIL_MODTAGER='$form_felt_text_modtager', HELPTEXT='$form_felt_helptext'
	where ID='$fieldid'
   ";
   $result = mysql_db_query($dbname, $sql);
  }
  if ($form__felttype_res == 2) {
   if ($form_felt_area_verifyfilled == "on") $form_felt_area_verifyfilled = 1;
   if ($form_felt_area_verifyemail == "on") $form_felt_area_verifyemail = 1;
   if ($form_felt_area_verifynumber == "on") $form_felt_area_verifynumber = 1;
   if ($form_felt_area_disabled == "on") $form_felt_area_disabled = 1;
   if ($form_felt_area_readonly == "on") $form_felt_area_readonly = 1;
   $sql = "
    update DEFINED_FORMFIELDS set
	 FIELDTYPE='$form__felttype_res', CAPTION='$form__felttitle', TEXTAREA_COLS='$form_felt_area_cols', 
	 TEXTAREA_ROWS='$form_felt_area_rows', TEXTAREA_MAXLENGTH='$form_felt_area_maxlength', 
	 TEXTAREA_DEFAULTTEXT='$form_felt_area_defaultvalue', VERIFY_FILLED='$form_felt_area_verifyfilled', 
	 VERIFY_EMAIL='$form_felt_area_verifyemail', VERIFY_NUMBER='$form_felt_area_verifynumber', 
	 DISABLED='$form_felt_area_disabled', READONLY='$form_felt_area_readonly', HELPTEXT='$form_felt_helptext'
	where ID='$fieldid'
   ";
   $result = mysql_db_query($dbname, $sql);
  }
  if ($form__felttype_res == 3) {
   if ($form_felt_radio_verifyfilled == "on") $form_felt_radio_verifyfilled = 1;
   $sql = "
    update DEFINED_FORMFIELDS set
	 FIELDTYPE='$form__felttype_res', CAPTION='$form__felttitle', RADIO_COUNT='$radiocount', 
	 RADIO_CAPTIONS='$radiocaptions', RADIO_DISABLEDSTATES='$radiodisabledstates', RADIO_SLETTETSTATES='$radioslettetstates', 
	 VERIFY_FILLED='1', HELPTEXT='$form_felt_helptext'
	where ID='$fieldid'	 
   ";
   $result = mysql_db_query($dbname, $sql);
  }
  if ($form__felttype_res == 4) {
   $sql = "
    update DEFINED_FORMFIELDS set
	 FIELDTYPE='$form__felttype_res', CAPTION='$form__felttitle', CHECKBOX_COUNT='$checkboxcount', 
	 CHECKBOX_CAPTIONS='$checkcaptions', CHECKBOX_DISABLEDSTATES='$checkdisabledstates', CHECKBOX_SLETTETSTATES='$checkslettetstates',
	 CHECKBOX_MINFILLED='$form_felt_check_minfilled', 
	 CHECKBOX_MAXFILLED='$form_felt_check_maxfilled', HELPTEXT='$form_felt_helptext'
	where ID='$fieldid'	 
   ";
   $result = mysql_db_query($dbname, $sql);
  }
  header("location: index.php?content_identifier=formeditor2&dothis=fields&mode=rediger&formid=$formid");
  exit;
 }
 
 if ($dothis=="sletfelt" && $feltid && $formid){
  $sql = "select POSITION from DEFINED_FORMFIELDS where ID='$feltid'";
  $result = mysql_db_query($dbname, $sql);
  $row = mysql_fetch_array($result);
  $sql = "update DEFINED_FORMFIELDS set DELETED='1', POSITION='-1' where ID='$feltid'";
  $result = mysql_db_query($dbname, $sql);
  $sql = "update DEFINED_FORMFIELDS set POSITION=POSITION-1 where POSITION>$row[POSITION] and FORM_ID='$formid'";
  $result = mysql_db_query($dbname, $sql);
  header("location: index.php?content_identifier=formeditor2&dothis=fields&mode=rediger&formid=$formid");
 }
 
 if ($dothis=="feltop" && $feltid && $formid){
  $sql = "select POSITION from DEFINED_FORMFIELDS where ID='$feltid'";
  $result = mysql_db_query($dbname, $sql);
  $row = mysql_fetch_array($result);
  if ($row[POSITION] > 1) {  
   $sql = "update DEFINED_FORMFIELDS set POSITION='999' where ID='$feltid' and FORM_ID='$formid'";
   $result = mysql_db_query($dbname, $sql);
   $sql = "update DEFINED_FORMFIELDS set POSITION='$row[POSITION]' where FORM_ID='$formid' and POSITION=($row[POSITION]-1)";
   $result = mysql_db_query($dbname, $sql);
   $sql = "update DEFINED_FORMFIELDS set POSITION=($row[POSITION]-1) where FORM_ID='$formid' and ID='$feltid'";
   $result = mysql_db_query($dbname, $sql);
  }
  header("location: index.php?content_identifier=formeditor2&dothis=fields&mode=rediger&formid=$formid");  
 }

 if ($dothis=="feltned" && $feltid && $formid){
  $sql = "select POSITION from DEFINED_FORMFIELDS where FORM_ID='$formid' and DELETED='0' order by POSITION desc limit 1";
  $result = mysql_db_query($dbname, $sql); 
  $row_max = mysql_fetch_array($result);
  $sql = "select POSITION from DEFINED_FORMFIELDS where ID='$feltid'";
  $result = mysql_db_query($dbname, $sql);
  $row = mysql_fetch_array($result);
  if ($row[POSITION] < $row_max[POSITION]) {
   $sql = "update DEFINED_FORMFIELDS set POSITION='999' where ID='$feltid' and FORM_ID='$formid'";
   $result = mysql_db_query($dbname, $sql);
   $sql = "update DEFINED_FORMFIELDS set POSITION='$row[POSITION]' where FORM_ID='$formid' and POSITION=($row[POSITION]+1)";
   $result = mysql_db_query($dbname, $sql);
   $sql = "update DEFINED_FORMFIELDS set POSITION=($row[POSITION]+1) where FORM_ID='$formid' and ID='$feltid'";
   $result = mysql_db_query($dbname, $sql);
  }
  header("location: index.php?content_identifier=formeditor2&dothis=fields&mode=rediger&formid=$formid");  
 }

 if ($dothis == "copyform" && $formid) {
  $sql = "select * from DEFINED_FORMS where ID='$formid' LIMIT 1";
  $result = mysql_db_query($dbname, $sql);
  $row = mysql_fetch_row($result);
  $row[3] = time();
  $row[1] = "Kopi af " . $row[1];
  $row[4] = $_SESSION[CMS_USER][USER_ID];
  unset($row[0]);
  foreach ($row as $key=>$val){
   $row[$key] = "'" . addslashes($val) . "'";
  }
  $insertsql = "'', " . implode(", ",$row);
  $sql = "insert into DEFINED_FORMS values ($insertsql)";
  $result = mysql_db_query($dbname, $sql);
  $nyt_id = mysql_insert_id();
  $sql = "select * from DEFINED_FORMFIELDS where FORM_ID='$formid'";
  $result = mysql_db_query($dbname, $sql);
  while ($row = mysql_fetch_row($result)) {
   unset($row[0]);
   $row[1] = $nyt_id;
   foreach ($row as $key=>$val){
    $row[$key] = "'" . addslashes($val) . "'";
   }
   $insertsql = "'', " . implode(", ",$row);
   $sql = "insert into DEFINED_FORMFIELDS values ($insertsql)";
   mysql_db_query($dbname, $sql);
  }
  header("location: index.php?content_identifier=formeditor2&dothis=");  
 }
 
  
?>
 