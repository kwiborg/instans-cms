<?php
	if (!$_SESSION["CMS_USER"]) {
		header("location: ../../login.php");
	}
	function ____oversigt() {}
	if ($dothis == "oversigt" || !$dothis) {
?>
<h1>Tilmeldingsformularer: Oversigt</h1>
<div class="feltblok_header">Oprettede formularer</div>
<div class="feltblok_wrapper">
	<?php echo formOversigt(); ?>
</div>	
<div class="knapbar">
	<input type="button" value="Opret ny formular" onclick="location='index.php?content_identifier=formeditor2&dothis=generalformsettings&mode=opret'">
</div>
<?php } ?>

<?php
function ____generalformsettings() {}
	if ($dothis == "generalformsettings") {
?>
<script type="text/javascript" src="/cms/scripts/date-picker/js/datepicker.js"></script>
<link href="/cms/scripts/date-picker/css/datepicker.css" rel="stylesheet" type="text/css" />
<h1>Tilmeldingsformular: <?php if ($mode=="opret") echo "Opret"; else echo "Rediger"; ?></h1>
<form id="defaultForm" method="post" action="">
	<input type="hidden" name="dothis" value="" />
 	<input type="hidden" name="mode" value="<?php echo $mode ?>" />
 	<div class="feltblok_header">Generelle indstillinger for formular <span class="yellow"><?php echo returnFormTitle($formid)?></span></div>
 	<div class="feltblok_wrapper">
	 	<?php if ($mode!="rediger") { ?>
	 		<h2>Start med "basis-formular" med felterne <em>fornavn</em>, <em>efternavn</em>, <em>adresse</em>, <em>postnummer</em>, <em>by</em>, <em>telefon</em> og <em>e-mail</em>?</h2>
	 		<input type="checkbox" name="standardform">&nbsp;Ja, start med disse felter defineret
	 	<?php } ?>
		<h2>Linktekst i "Gå til tilmelding"-boks:</h2>
	 	<input type="text" name="form_linktext" class="inputfelt" value="<?php echo ($datarow[LINKTEXT] ? $datarow[LINKTEXT] : "Klik her for at gå til tilmelding!") ?>">
	 	<h2>Formularens overskrift:</h2>
	 	<input type="text" name="form_title" class="inputfelt" value="<?php echo $datarow[TITLE] ?>">
	 	<h2>Indledende tekst:</h2>
	 	<textarea name="form_introtext" class="inputfelt" rows="5"><?php echo $datarow[INTROTEXT] ?></textarea>
	 	<p class="feltkommentar">
	  		TIP: Dette kan være en kort beskrivende tekst om, hvad formularen er beregnet til. Den vises på siden oven for selve formularen.
	 	</p>
	 	<h2>Afsluttende tekst:</h2>
	 	<textarea name="form_endtext" class="inputfelt" rows="5"><?php echo $datarow[ENDTEXT] ?></textarea>
	 	<p class="feltkommentar">
	  		TIP: Dette er den tekst, som brugeren præsenteres for, når han trykker "Send" for at sende de indtastede data.
	 	</p>
	 	<h2>Formularen er kun aktiv i denne periode:</h2>
	 	<input type="text" name="form_opendate" class="inputfelt_kort format-d-m-y divider-dash highlight-days-67" value="<?php if ($datarow["FORM_OPENDATE"] != "0000-00-00" && $datarow["FORM_OPENDATE"] != ""){
			echo reverseDate($datarow["FORM_OPENDATE"]);
	 	} ?>"> &nbsp;til&nbsp;<input type="text" name="form_closedate" class="inputfelt_kort format-d-m-y divider-dash highlight-days-67" value="<?php if ($datarow["FORM_CLOSEDATE"] != "0000-00-00" && $datarow["FORM_CLOSEDATE"] != ""){
	 		echo reverseDate($datarow["FORM_CLOSEDATE"]);
	 	} ?>">
	 	<p class="feltkommentar">
	  		TIP: Lad felterne være tomme, hvis du selv vil lukke formularen, når det passer dig. Datoer skal være på formatet dd-mm-åååå.
	 	</p>
	 	<h2>Gem indtastningerne på denne måde (vælg mindst én):</h2>
	 	<input type="checkbox" name="form_save_email" <?php if ($datarow[SEND_MAIL]) { echo "checked"; } ?> />&nbsp;Send til denne e-mail-adresse:&nbsp;<input type="text" name="form_email" class="inputfelt_kort" value="<?php echo $datarow[EMAIL] ?>"><br/>
	 	<input type="checkbox" name="form_save_database" <?php echo ($datarow[SAVE_IN_DB] ? "checked" : "") ?> />&nbsp;Gem i en database-tabel, som løbende kan udtrækkes
		<h2>Avanceret spambekæmpelse: Brugere skal indtaste kode for at indsende formular</h2>
		<?php
		echo createSelectYesNo("form_spamprevent_captcha", $datarow[SPAMPREVENT_CAPTCHA]);
		?>
	 	<h2>Tilknyt formularen til et nyhedsbrev:</h2>
		<input type='hidden' name='old_mapped_newsletter_id' value='<?=$datarow[MAPPED_NEWSLETTER_ID]?>' />
			<?=form_newsletters_selector($datarow[MAPPED_NEWSLETTER_ID], $datarow[MAPPED_USERGROUP_ID])?>
	 	<p class="feltkommentar">
	  		TIP: Brug denne indstilling, hvis du automatisk vil oprette folk som nyhedsbrevs-modtagere via fomularen.
	 	</p>
		<?php /*
		<h2>Tilknyt formularen til en brugergruppe:</h2>
		<input type='hidden' name='old_mapped_usergroup_id' value='<?=$datarow[MAPPED_USERGROUP_ID]?>' />
			<?=form_usergroups_selector($datarow[MAPPED_USERGROUP_ID], $datarow[MAPPED_NEWSLETTER_ID])?>
	 	<p class="feltkommentar">
	  		TIP: Brug denne indstilling, hvis du automatisk vil placere folk i en brugergruppe som nye brugere via formularen.
	 	</p> */
		?>
	 	<h2>Linktekst til direkte link til formularen:</h2>
	 	<input type="text" readonly="readonly" name="form_directurl" class="inputfelt" value="<?php echo $cmsDomain.returnSitepath($_SESSION[SELECTED_SITE]); ?>/index.php?mode=formware&formid=<?php echo $formid ?>"> 
	</div>
	<div class="knapbar">
		<input type="button" value="Afbryd" onclick="location='index.php?content_identifier=formeditor2'">
		<input type="button" value="Gem" onclick="verify_form_general()">
	</div>
</form>
<?php
 }
?>

<?php
 if ($dothis == "fields" && $mode=="rediger" && $formid){
 	$mapped_newsletter_id 	= returnFieldValue("DEFINED_FORMS", "MAPPED_NEWSLETTER_ID", "ID", $formid);
 	$mapped_usergroup_id 	= returnFieldValue("DEFINED_FORMS", "MAPPED_USERGROUP_ID", "ID", $formid);
?>
<h1>
 Tilmeldingsformular: Definer felter
</h1>
<div class="broedtekst">
 Opret felter, som din formular skal bestå af. Når du har oprettet feltet kan du give det en titel. Består din formular af flere felter, kan du selv bestemme rækkefølgen af felterne ved at klikke [op] og [ned].
</div>
<form id="defaultForm" method="post" action="">
 <input type="hidden" name="dothis" value="" />
 <input type="hidden" name="formid" value="<?php echo $_GET[formid] ?>" />
 <input type="hidden" name="mode" value="<?php echo $mode ?>" />
 <div class="feltblok_header">
 Felter i formularen '<span class="yellow"><?php echo returnFormTitle($formid)?></span>'
 </div>
 <div class="feltblok_wrapper">
  <?php 
   $types = array(1=>"Tekstfelt", "Tekstområde", "Radioknap-gruppe", "Kryds-af-boks gruppe", "Specialfelt til nyhedsbrev", "Specialfelt til nyhedsbrev");
   $sql = "select * from DEFINED_FORMFIELDS where FORM_ID='$formid' and DELETED='0' order by POSITION asc";
   $result = mysql_db_query($dbname, $sql);
   if (mysql_num_rows($result) != 0) {
    echo "
    <table class='oversigt'>
    <tr class='trtop'>
     <td class='kolonnetitel'>Titel</td>
     <td class='kolonnetitel'>Felttype</td>
     <td class='kolonnetitel'><em>Skal</em> udfyldes</td>
     <td class='kolonnetitel'>Funktioner</td>".
	 ($mapped_newsletter_id || $mapped_usergroup_id ? "
	 <td class='kolonnetitel'>Tilknyttet felt</td>
	 " : "")."
    </tr>";
    while ($row = mysql_fetch_array($result)) {
     echo "
     <tr>
      <td>$row[CAPTION]</td>
      <td>" . $types[$row[FIELDTYPE]] . " " . ($row[FIELDTYPE]==3 ? "[".$row[RADIO_COUNT]." stk.]" : ($row[FIELDTYPE]==4 ? "[".$row[CHECKBOX_COUNT]." stk.]" : "")) . "</td>
      <td>";
      if ($row[VERIFY_FILLED] == "1") {
      	echo "Ja";
      } else {
      	echo "Nej";
      }
      echo "</td>
	  <td> 
	   <input type='button' class='lilleknap' value='Op' onclick='location=\"index.php?content_identifier=formeditor2&dothis=feltop&formid=$formid&feltid=$row[ID]\"'>&nbsp;
	   <input type='button' class='lilleknap' value='Ned' onclick='location=\"index.php?content_identifier=formeditor2&dothis=feltned&formid=$formid&feltid=$row[ID]\"'>&nbsp;&nbsp;&nbsp;&nbsp;
	   <input type='button' class='lilleknap' value='Rediger' onclick='location=\"index.php?content_identifier=formeditor2&dothis=addfield&mode=rediger&formid=$row[FORM_ID]&fieldid=$row[ID]\"'>&nbsp;
	   <input ".($row[MAPPED_FIELD_ID] > 0 || $row[LOCKED] > 0 ? "disabled" : "")." type='button' class='lilleknap' value='Slet' onclick='sletFelt($row[ID], $formid)'>&nbsp;
	  </td>".($mapped_newsletter_id || $mapped_usergroup_id ? "
	  <td>".($row[FIELDTYPE] != 5 ? form_mapped_field_selector($row[ID], $mapped_newsletter_id, $mapped_usergroup_id) : "")."</td>
	  " : "")."
     </tr>
     ";
    }
    echo "</table>";
	echo form_mapped_field_script($mapped_newsletter_id, $mapped_usergroup_id);
   }
   else {
    echo "<table class='oversigt'><tr><td>Der er ikke oprettet nogen felter i denne formular endnu.</td></tr></table>";
   }   
  ?>
 </div>
  <div class="knapbar">
   <input type="button" value="Færdig" onclick="location='index.php?content_identifier=formeditor2'">
   <input type="button" value="Tilføj felt" onclick="location='index.php?content_identifier=formeditor2&dothis=addfield&formid=<?php echo $formid ?>&mode=opret'">
   <?php if ($mapped_newsletter_id || $mapped_usergroup_id) { ?>
   	<input type="button" value="Gem indstillinger" onclick="verify_mandatory_fields()">
  <?php } ?>
  </div>
</form>
<?php
 }
?>

<?php
 if ($dothis == "addfield" && $formid) {
  if ($mode=="rediger") {
   $datarow = hentRow($fieldid, "DEFINED_FORMFIELDS");
  }
?>
<h1>
 Tilmeldingsformular: <?php echo ($mode=="opret" ? "Tilføj felt" : "Rediger felt") ?>
</h1>
<div class="broedtekst">
</div>
<form id="defaultForm" method="post" action="">
 <input type="hidden" name="dothis" value="" />
 <input type="hidden" name="mode" value="<?php echo $mode ?>" />
 <input type="hidden" name="formid" value="<?php echo $formid ?>" />
 <input type="hidden" name="radiocount" value="<?php echo $datarow[RADIO_COUNT] ?>" />
 <input type="hidden" name="checkboxcount" value="<?php echo $datarow[CHECKBOX_COUNT] ?>" />
 <input type="hidden" name="radiocaptions" value="<?php echo $datarow[RADIO_CAPTIONS] ?>" />
 <input type="hidden" name="checkcaptions" value="<?php echo $datarow[CHECKBOX_CAPTIONS] ?>" />
 <input type="hidden" name="radiodisabledstates" value="<?php echo $datarow[RADIO_DISABLEDSTATES] ?>" />
 <input type="hidden" name="checkdisabledstates" value="<?php echo $datarow[CHECKBOX_DISABLEDSTATES] ?>" />
 <input type="hidden" name="radioslettetstates" value="<?php echo $datarow[RADIO_SLETTETSTATES] ?>" />
 <input type="hidden" name="checkslettetstates" value="<?php echo $datarow[CHECKBOX_SLETTETSTATES] ?>" />
 <div class="feltblok_header">
  Indstillinger for felt
 </div>
 <div class="feltblok_wrapper">
  <h2>Vælg felttype:</h2>
  <input type="hidden" name="form__felttype_res" value="">
  <input type="radio" name="form__felttype" onclick="showFeltSettings('text',1)">&nbsp;Tekstfelt<br/>
  <input type="radio" name="form__felttype" onclick="showFeltSettings('area',2)">&nbsp;Tekstområde (flere linjer)<br/>
  <input type="radio" name="form__felttype" onclick="showFeltSettings('radio',3)">&nbsp;Radioknap gruppe<br/>
  <input type="radio" name="form__felttype" onclick="showFeltSettings('check',4)">&nbsp;Kryds-af-boks gruppe  
  <h2>Feltets overskrift:</h2>
  <input type="text" name="form__felttitle" class="inputfelt" value="<?php echo $datarow[CAPTION] ?>">
  <!-- *** -->
  <h2>Hjælpetekst til felt:</h2>
  <textarea rows="10" cols="50" name="form_felt_helptext"><?php echo $datarow[HELPTEXT] ?></textarea>
  <div id="form_felt_text" style="display:none">
  <h2>Feltets størrelse:</h2>
  <input type="text" name="form_felt_text_size" class="inputfelt_kort" value="<?php echo ($datarow[TEXT_SIZE]!=0?$datarow[TEXT_SIZE]:"") ?>">&nbsp;tegn
  <p class="feltkommentar">
   TIP: Du kan lade dette felt være tomt for at bruge en standard-værdi
  </p>
  <h2>Max. længde af indhold:</h2>
  <input type="text" name="form_felt_text_maxlength" class="inputfelt_kort" value="<?php echo ($datarow[TEXT_MAXLENGTH]!=0?$datarow[TEXT_MAXLENGTH]:"") ?>">&nbsp;tegn
  <p class="feltkommentar">
   TIP: Du kan lade dette felt være tomt for at bruge en standard-værdi
  </p>
  <h2>Default-indhold:</h2>
  <input type="text" name="form_felt_text_defaultvalue" class="inputfelt" value="<?php echo $datarow[TEXT_DEFAULTTEXT] ?>">
  <h2>Verifikation:</h2>
  <input <?php if ($datarow[VERIFY_FILLED]==1) echo "checked" ?> type="checkbox" name="form_felt_text_verifyfilled" onclick="">&nbsp;Brugeren skal udfylde feltet<br/>
  <input <?php if ($datarow[VERIFY_EMAIL]==1)  echo "checked" ?> type="checkbox" name="form_felt_text_verifyemail" onclick="relation(this.checked, ['form_felt_text_verifynumber'], [0], 1)">&nbsp;Check, om feltets indhold er en gyldig e-mail-adresse<br/>
  <input <?php if ($datarow[VERIFY_NUMBER]==1) echo "checked" ?> type="checkbox" name="form_felt_text_verifynumber" onclick="relation(this.checked, ['form_felt_text_verifyemail'], [0], 1)">&nbsp;Check, om feltets indhold er et rent tal<br/>
  <h2>Andre felt-indstillinger:</h2>
  <input <?php if ($datarow[DISABLED]==1) echo "checked" ?> type="checkbox" name="form_felt_text_disabled" onclick="relation(this.checked, ['form_felt_text_verifyfilled', 'form_felt_text_verifynumber', 'form_felt_text_verifyemail'], [0,0,0], 1)">&nbsp;"Disabled"<br/>
  <input <?php if ($datarow[READONLY]==1) echo "checked" ?> type="checkbox" name="form_felt_text_readonly" onclick="relation(this.checked, ['form_felt_text_verifyfilled', 'form_felt_text_verifynumber', 'form_felt_text_verifyemail'], [0,0,0], 1)">&nbsp;"Read-only"<br/>
  <!--
  <h2>Specielle indstillinger:</h2>
  <input <?php if ($datarow[EMAIL_MODTAGER]==1) echo "checked" ?> type="checkbox" name="form_felt_text_modtager" onclick="relation(this.checked, ['form_felt_text_verifyfilled', 'form_felt_text_verifyemail', 'form_felt_text_disabled', 'form_felt_text_readonly'], [1,1,0,0], 0)">&nbsp;E-mail-kvittering sendes til den adresse, som er indtastet i dette felt<br/> 
  -->
  </div>
  <!-- *** -->
  <div id="form_felt_area" style="display:none">
  <h2>Feltets størrelse:</h2>
  <input type="text" name="form_felt_area_rows" class="inputfelt_kort" value="<?php echo ($datarow[TEXTAREA_ROWS]!=0?$datarow[TEXTAREA_ROWS]:"") ?>">&nbsp;rækker,
  &nbsp;&nbsp;<input type="text" name="form_felt_area_cols" class="inputfelt_kort" value="<?php echo ($datarow[TEXTAREA_COLS]!=0?$datarow[TEXTAREA_COLS]:"") ?>">&nbsp;kolonner 
  <p class="feltkommentar">
   TIP: Du kan lade disse felter være tomme for at bruge en standard-værdi
  </p>
  <h2>Max. længde af indhold:</h2>
  <input type="text" name="form_felt_area_maxlength" class="inputfelt_kort" value="<?php echo ($datarow[TEXTAREA_MAXLENGTH]!=0?$datarow[TEXTAREA_MAXLENGTH]:"") ?>">&nbsp;tegn
  <p class="feltkommentar">
   TIP: Du kan lade dette felt være tomt for at bruge en standard-værdi
  </p>
  <h2>Default-indhold:</h2>
  <input type="text" name="form_felt_area_defaultvalue" class="inputfelt" value="<?php echo $datarow[TEXTAREA_DEFAULTTEXT] ?>">
  <h2>Verifikation:</h2>
  <input <?php if ($datarow[VERIFY_FILLED]==1) echo "checked" ?> <?=($datarow[MAPPED_FIELD_ID]>0 ? "disabled" : "")?>  type="checkbox" name="form_felt_area_verifyfilled" onclick="">&nbsp;Brugeren skal udfylde feltet<br/>
  <input <?php if ($datarow[VERIFY_EMAIL]==1)  echo "checked" ?> type="checkbox" name="form_felt_area_verifyemail" onclick="relation(this.checked, ['form_felt_area_verifynumber'], [0])">&nbsp;Check, om feltets indhold er en gyldig e-mail-adresse<br/>
  <input <?php if ($datarow[VERIFY_NUMBER]==1) echo "checked" ?> type="checkbox" name="form_felt_area_verifynumber" onclick="relation(this.checked, ['form_felt_area_verifyemail'], [0])">&nbsp;Check, om feltets indhold er et rent tal<br/>
  <h2>Andre felt-indstillinger:</h2>
  <input <?php if ($datarow[DISABLED]==1) echo "checked" ?> type="checkbox" name="form_felt_area_disabled" onclick="relation(this.checked, ['form_felt_area_verifyfilled', 'form_felt_area_verifynumber', 'form_felt_area_verifyemail'], [0,0,0], 1)">&nbsp;"Disabled"<br/>
  <input <?php if ($datarow[READONLY]==1) echo "checked" ?> type="checkbox" name="form_felt_area_readonly" onclick="relation(this.checked, ['form_felt_area_verifyfilled', 'form_felt_area_verifynumber', 'form_felt_area_verifyemail'], [0,0,0], 1)">&nbsp;"Read-only"<br/>
  </div>
  <!-- *** -->
  <div id="form_felt_radio" style="display:none">
  <h2>Radioknapper:</h2>
  <div id="radioknapper">   
  </div>
  <h2>Verifikation:</h2>
  <!--<input <?php if ($datarow[VERIFY_FILLED]==1) echo "checked" ?> type="checkbox" name="form_felt_radio_verifyfilled" onclick="">&nbsp;Brugeren skal udfylde feltet, dvs. vælge én knap<br/>-->
  </div>
  <!-- *** -->
  <div id="form_felt_check" style="display:none">
  <h2>Kryds-af-knapper:</h2>
  <div id="checkbokse">   
  </div>
  <h2>Verifikation:</h2>
  Brugeren skal afkrydse mindst <input type="text" name="form_felt_check_minfilled" class="inputfelt_kort" size="2" value="<?php echo ($datarow[CHECKBOX_MINFILLED]!=0?$datarow[CHECKBOX_MINFILLED]:"") ?>"> og højst <input type="text" name="form_felt_check_maxfilled" class="inputfelt_kort" size="2" value="<?php echo ($datarow[CHECKBOX_MAXFILLED]!=0?$datarow[CHECKBOX_MAXFILLED]:"") ?>"> bokse
  <p class="feltkommentar">
   TIP: Lad det ene eller begge felter være tomme, hvis du enten ikke vil checke for udfyldt antal, eller kun angive et minimum- eller maximumantal.
  </p>
  </div>
  <!-- *** -->
 </div>
  <div class="knapbar">
   <input type="button" value="Afbryd" onclick="location='index.php?content_identifier=formeditor2&dothis=fields&mode=rediger&formid=<?php echo $formid ?>'">
   <input type="button" value="Gem felt" onclick="verify_felt()">
  </div>
</form>
<script type="text/javascript">
 <?php 
  if ($mode == "rediger") {
   $types = array(1=>"text", "area", "radio", "check");
   echo "setRadioCheckedMark('form__felttype', $datarow[FIELDTYPE], 1);\n"; 
   echo "showFeltSettings('" . $types[$datarow[FIELDTYPE]] . "', $datarow[FIELDTYPE]);\n";
   echo "disableFelttypeSelector();";
   if ($datarow[FIELDTYPE] == 3) {
    $radiocaptions = "['" . substr(implode("', '", explode("|", $datarow[RADIO_CAPTIONS])),0,-4) . "']";
    $radiocaptions = unhtmlentities($radiocaptions);
	$radiodisabledstates = "['" . substr(implode("', '", explode("|", $datarow[RADIO_DISABLEDSTATES])),0,-4) . "']";
	$radioslettetstates = "['" . substr(implode("', '", explode("|", $datarow[RADIO_SLETTETSTATES])),0,-4) . "']";
	echo "radioknap_captions = $radiocaptions;\n";
	echo "radioknap_disabledstates = $radiodisabledstates;\n";
	echo "radioknap_slettetstates = $radioslettetstates;\n";
	echo "for(i=0; i < " . ($datarow[RADIO_COUNT]) . "; i++){
	 radioknapper[i] = [radioknap_captions[i], radioknap_disabledstates[i], radioknap_slettetstates[i]];
	}";
   }
   if ($datarow[FIELDTYPE] == 4) {
    $checkcaptions = "['" . substr(implode("', '", explode("|", $datarow[CHECKBOX_CAPTIONS])),0,-4) . "']";
    $checkcaptions = unhtmlentities($checkcaptions);
    $checkdisabledstates = "['" . substr(implode("', '", explode("|", $datarow[CHECKBOX_DISABLEDSTATES])),0,-4) . "']";
    $checkslettetstates = "['" . substr(implode("', '", explode("|", $datarow[CHECKBOX_SLETTETSTATES])),0,-4) . "']";
	echo "checkbox_captions = $checkcaptions;\n";
	echo "checkbox_disabledstates = $checkdisabledstates;\n";
	echo "checkbox_slettetstates = $checkslettetstates;\n";
	echo "for(i=0; i < " . ($datarow[CHECKBOX_COUNT]) . "; i++){
	 checkbokse[i] = [checkbox_captions[i], checkbox_disabledstates[i], checkbox_slettetstates[i]];
	}";
   }
  }
  if (force_verifyfilled($datarow[ID])){
  	echo "document.forms[0].form_felt_text_verifyfilled.disabled = true;";
  }
 ?>
 generateRadioGroup();
 generateCheckboxGroup();
</script>
<?php
 }
?>









