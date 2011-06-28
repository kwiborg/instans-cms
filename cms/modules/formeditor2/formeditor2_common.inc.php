<?php
	function returnFieldType($fieldId){
		$sql = "select FIELDTYPE from DEFINED_FORMFIELDS where ID='$fieldId'";
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		return $row[FIELDTYPE];
	}
	
	function prepareCSV($data){
		$data = str_replace("__NOT_FILLED__", "", $data);
		$data = trim($data);
		$data = str_replace("\"", "\"\"", $data);
		$data = "\"$data\"";
		return $data;
	}

	function parseRadioToText($fieldId, $checkedList){
		$sql = "select RADIO_CAPTIONS from DEFINED_FORMFIELDS where ID='$fieldId'";		
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		$temp1 = explode("|",  $row[RADIO_CAPTIONS]);
		$temp2 = explode(",",  $checkedList);
		foreach($temp2 as $captionId){
			$text[] = $temp1[$captionId];
		}
		return implode(",", $text);
	}
	
	function parseCheckToText($fieldId, $checkedList){
		$sql = "select CHECKBOX_CAPTIONS from DEFINED_FORMFIELDS where ID='$fieldId'";		
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		$temp1 = explode("|",  $row[CHECKBOX_CAPTIONS]);
		$temp2 = explode(",",  $checkedList);
		foreach($temp2 as $captionId){
			$text[] = $temp1[$captionId];
		}
		return implode(",", $text);
	}

	function outputCSV($formId){		
		$sql = "
			select 
				ID, FIELDTYPE, CAPTION, RADIO_SLETTETSTATES, CHECKBOX_SLETTETSTATES
			from
				DEFINED_FORMFIELDS
			where
				FORM_ID='$formId'
			order by
				POSITION asc
		";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)){
			$allFieldIds[$row[ID]]		=	$row[ID];
			$allFieldTitles[$row[ID]] 	=	$row[CAPTION];				  
		}
		$sql = "
			select ID from TILMELDINGER where FORM_ID='$formId' 
		";
		$result = mysql_query($sql);
		$antalRows = mysql_num_rows($result);
		for($i=0; $i<$antalRows; $i++){
			$matrix[$i] = array();
			foreach ($allFieldIds as $key => $value){
				$matrix[$i][$key] = "__NOT_FILLED__"; 
			}
		}			

		$i = 0;
		$sql = " select FIELD_IDS, FIELD_VALUES, CREATED_DATE from TILMELDINGER where FORM_ID='$formId'";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_array($result)){
			$thisEntryIds 		= explode("|¤|", $row[FIELD_IDS]);
			$thisEntryValues 	= explode("|¤|", $row[FIELD_VALUES]);
			foreach ($thisEntryIds as $key => $fieldId){
				if (returnFieldType($fieldId) == 3) {
					$thisEntryValues[$key] = parseRadioToText($fieldId, $thisEntryValues[$key]);
				}
				if (returnFieldType($fieldId) == 4) {
					$thisEntryValues[$key] = parseCheckToText($fieldId, $thisEntryValues[$key]);
				}
				$matrix[$i][$fieldId]  = $thisEntryValues[$key];
			}
			$i++;
		}
		foreach ($allFieldTitles as $k => $v){	
			$allFieldTitles[$k] = prepareCSV($allFieldTitles[$k]);
		}
		$CSV_columnTitles = implode(",", $allFieldTitles);
		foreach ($matrix as $rowKey => $rowData){
			foreach ($rowData as $key => $value){
				$matrix[$rowKey][$key] = prepareCSV($value);
			}
			$CSV .= implode(",", $matrix[$rowKey]) . "\n";
		}
		$CSV = $CSV_columnTitles . "\n" . $CSV;
		
		header("HTTP/1.1 200 OK");
	    header("Status: 200 OK");
	    header("Content-type: text/csv; charset=utf-8");
	    header("Pragma: public");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Disposition: attachment; filename=\"" . ereg_replace("[^[:alnum:]+]","",returnFormTitle($formId)) . ".csv\"");
		// Alas, MS Excel can't read utf-8 encoded csv files :-(
		// The following probably does the trick along with using \t as a separation char instead of "," but it requires the 
		// mb_convert_encoding function to be installed on the server
		// $CSV = chr(255).chr(254).mb_convert_encoding( $CSV, 'UTF-16LE', 'UTF-8');
		echo utf8_decode($CSV); 
		exit;
	}	

	function formOversigt($selectmode=''){
		global $modules_browserPath;
		$sql = "select ID, TITLE, CREATED_DATE, AUTHOR_ID from DEFINED_FORMS where SITE_ID='$_SESSION[SELECTED_SITE]' and DELETED='0' order by CREATED_DATE desc";
		$result = mysql_query($sql);
		if (mysql_num_rows($result) != 0) {
			$html .= "<table class='oversigt'>";
			$html .= "<tr class='trtop'>";
			$html .= "<td class='kolonnetitel'>Titel</td>";
			$html .= "<td class='kolonnetitel'>Status</td>";
			$html .= "<td class='kolonnetitel'>Sidst ændret</td>";
			$html .= "<td class='kolonnetitel'>Af</td>";
			$html .= "<td class='kolonnetitel'>Funktioner</td>";
			$html .= "</tr>";
			while ($row = mysql_fetch_array($result)){
	  			$html .= "<tr id='formrow_$row[ID]' onmouseover='IEColorShift(this.id)' onmouseout='IEColorUnShift(this.id, 0)'>";
	    		$html .= "<td><a href='/index.php?mode=formware&formid=".$row[ID]."' target='_blank' title='Se formular...'>$row[TITLE]</a></td>";
	    		$html .= "<td>".(check_form_integrity($row[ID]) ? "OK" : "<span style='color:#f00'>BEMÆRK: Formularen er IKKE konfigureret korrekt i forhold til sit tilknyttede nyhedsbrev! Klik på 'Felter' for at rette fejlen.</span>")."</td>";
		 		$html .= "<td>".returnNiceDateTime($row[CREATED_DATE],2)."</td>";
		 		$html .= "<td>".returnAuthorName($row[AUTHOR_ID],1)."</td>";
				$html .= "<td>";
				///////////////////////////////////////////////////////////////////
				$sql2 = "select ID from TILMELDINGER where FORM_ID='$row[ID]'";
				$result2 = mysql_query($sql2);
				$antalDataRows = mysql_num_rows($result2);
				///////////////////////////////////////////////////////////////////
				if ($selectmode != "attachtopage"){
		  			$html .= "<input type='button' class='lilleknap' value='Indstillinger' onclick='location=\"index.php?content_identifier=formeditor2&dothis=generalformsettings&mode=rediger&formid=$row[ID]\"'> ";
					$html .= "<input type='button' class='lilleknap' value='Felter' onclick='location=\"index.php?content_identifier=formeditor2&dothis=fields&mode=rediger&formid=$row[ID]\"'> ";
					$html .= "<input type='button' class='lilleknap' value='Kopier' onclick='if (confirm(\"Vil du lave en kopi af denne formular?\")) location=\"index.php?content_identifier=formeditor2&dothis=copyform&formid=$row[ID]\"'> ";
					$html .= "<input type='button' class='lilleknap' value='Slet' onclick='if (confirm(\"Vil du slette denne formular?\\nDe data som evt. er opsamlet i en database vil også blive slettet!\")) location=\"index.php?content_identifier=formeditor2&dothis=slet&formid=$row[ID]\"'> ";
					$html .= "<input type='button' class='lilleknap' value='Vis data' onclick='window.open(\"$modules_browserPath/formeditor2/formeditor2_viewdata.php?formid=$row[ID]\")'> ";
					$html .= "<input type='button' class='lilleknap' value='Udtræk kommafil' ";
					if ($antalDataRows == 0){
						$html .= "disabled='disabled'";
					}
					$html .= " onclick='location=\"$modules_browserPath/formeditor2/formeditor2_csv.php?formid=$row[ID]\"'>";
				} else {
					$html .= "<input type='button' class='lilleknap' value='Vedhæft denne' onclick='addForm(".$row[ID].")' />&nbsp;<input type='checkbox' id='inlineform_$row[ID]' value='' />Inline";
				}
		 		$html .= "</td>";
				$html .= "</tr>";
			}
			$html .= "</table>";
		} else {
			$html .= "<table class='oversigt'><tr><td>Der er ikke oprettet nogen formularer.</td></tr></table>";
		}	
		return $html;
	}
	
	function form_newsletters_selector($selected=false, $other_mapping=false){
		$sql = "select ID, TITLE from NEWSLETTER_TEMPLATES where DELETED='0' and OPEN_FOR_SUBSCRIPTIONS='1' order by TITLE asc";
		$res = mysql_query($sql);
		$html .= "
			<select ".($other_mapping ? "disabled" : "")." name='mapped_newsletter_id' id='mapped_newsletter_id' class='inputfelt' onchange='disable_mapping_selectors()'>
				<option value='0'>Intet nyhedsbrev tilknyttet</option>
		";
		while ($row = mysql_fetch_assoc($res)){
			$html .= "<option value='$row[ID]' ".($selected == $row[ID] ? " selected " : "").">$row[TITLE]</option>";
		}
		$html .= "
			</select>
		";
		return $html;
	}

	function form_usergroups_selector($selected=false, $other_mapping=false){
		$sql = "select ID, GROUP_NAME from GROUPS where DELETED='0' and UNFINISHED='0' order by GROUP_NAME asc";
		$res = mysql_query($sql);
		$html .= "
			<select ".($other_mapping ? "disabled" : "")." name='mapped_usergroup_id' id='mapped_usergroup_id' class='inputfelt' onchange='disable_mapping_selectors()'>
				<option value='0'>Ingen brugergruppe tilknyttet</option>
		";
		while ($row = mysql_fetch_assoc($res)){
			$html .= "<option value='$row[ID]' ".($selected == $row[ID] ? " selected " : "").">$row[GROUP_NAME]</option>";
		}
		$html .= "
			</select>
		";
		return $html;
	}
	
	function form_mapped_field_selector($formfield_id, $newsletter_id=false, $usergroup_id=false){
		if ($newsletter_id){
			$sql = "
				select 
					NTF.FIELD_ID, NTF.MANDATORY, NF.CMS_LABEL, DF.MAPPED_FIELD_ID as SELECTED
				from 
					NEWSLETTER_FORMFIELDS NF, NEWSLETTER_TEMPLATES_FORMFIELDS NTF
				left join 
					DEFINED_FORMFIELDS DF on DF.MAPPED_FIELD_ID=NTF.FIELD_ID and DF.ID='$formfield_id'
				where
					NTF.TEMPLATE_ID='$newsletter_id' and 
					NF.ID=NTF.FIELD_ID 
				order by
					FIELD_ID asc
			";
			$res = mysql_query($sql);
			$html .= "<select name='fieldmapping_$formfield_id' id='fieldmapping_$formfield_id' class='inputfelt_kort'>";
			$html .= "<option value='0'>Intet tilknyttet felt</option>";
			while ($row = mysql_fetch_assoc($res)){
				$html .= "<option ".($row[SELECTED]==$row[FIELD_ID] ? "selected" : "")." value='$row[FIELD_ID]' ".($row[MANDATORY]==1 ? "style='background-color:#fffccc'" : "").">$row[CMS_LABEL]</option>";
				if ($row[MANDATORY]==1){
					$mandatory_ids[] 	= $row[FIELD_ID];
					$mandatory_titles[]	= "'$row[CMS_LABEL]'";
				}
			}
			$html .= "</select>";
		} else if ($usergroup_id){
		}
		return $html;
	}
	
	function form_mapped_field_script($newsletter_id=false, $usergroup_id=false){
		if ($newsletter_id){
			$sql = "
				select 
					NTF.FIELD_ID, NTF.MANDATORY, NF.CMS_LABEL
				from 
					NEWSLETTER_TEMPLATES_FORMFIELDS NTF, NEWSLETTER_FORMFIELDS NF
				where
					NTF.TEMPLATE_ID='$newsletter_id' and 
					NF.ID=NTF.FIELD_ID
				order by
					FIELD_ID asc
			";
			$res = mysql_query($sql);
			$mandatory_ids = array();
			$mandatory_titles = array();
			$other_ids = array();
			$other_titles = array();
			while ($row = mysql_fetch_assoc($res)){
				if ($row[MANDATORY]==1){
					$mandatory_ids[] 	= $row[FIELD_ID];
					$mandatory_titles[]	= "'$row[CMS_LABEL]'";
				} else {
					$other_ids[] = $row[FIELD_ID];
					$other_titles[]	= "'$row[CMS_LABEL]'";
				}
			}
			$html .= "
				<script>
					var mandatory_field_ids 	= [".implode(", ", $mandatory_ids)."];
					var mandatory_field_names 	= [".implode(", ", $mandatory_titles)."];
					var other_field_ids 		= [".implode(", ", $other_ids)."];
					var other_field_names 		= [".implode(", ", $other_titles)."];
				</script>
			";
		} else if ($usergroup_id){
		}
		return $html;
	}

	function save_mappings($POSTVARS){
		$sql = "
			update DEFINED_FORMFIELDS set
				MAPPED_FIELD_ID='0', LOCKED='0'
			where
				FORM_ID='$POSTVARS[formid]'
		";  
		mysql_query($sql);
		foreach ($POSTVARS as $k => $v){
			if (strstr($k, "fieldmapping_") && $v > 0){
				$temp = explode("_", $k);
				$formfield_id = $temp[1];
				$sql = "
					update DEFINED_FORMFIELDS set
						MAPPED_FIELD_ID='$v', LOCKED='1'
					where
						ID='$formfield_id'
					limit 1
				";  
				mysql_query($sql);
				if (force_verifyfilled($formfield_id)){
					$sql = "
						update DEFINED_FORMFIELDS set
							VERIFY_FILLED='1'
						where
							ID='$formfield_id'
						limit 1
					";  
					mysql_query($sql);
				}
			}
		}
	}
	
	function reset_mappings($form_id){
		$sql = "
			update DEFINED_FORMFIELDS set 
				MAPPED_FIELD_ID='0', LOCKED='0'
			where FORM_ID='$form_id'
		";
		mysql_query($sql);
		$sql = "
			delete from DEFINED_FORMFIELDS
			where FORM_ID='$form_id' and
			(FIELDTYPE='5' or FIELDTYPE='6')
		";
		mysql_query($sql);
	}
	
	function force_verifyfilled($field_id){
		$sql = "
			select
				NTF.MANDATORY, DFF.MAPPED_FIELD_ID
			from
				NEWSLETTER_TEMPLATES_FORMFIELDS NTF, DEFINED_FORMFIELDS DFF, DEFINED_FORMS DF
			where
				DF.MAPPED_NEWSLETTER_ID=NTF.TEMPLATE_ID and
				DFF.MAPPED_FIELD_ID=NTF.FIELD_ID and
				DFF.FORM_ID=DF.ID and 
				DFF.ID='$field_id'
		";
		$res = mysql_query($sql);
		$row = mysql_fetch_assoc($res);
		if ($row[MANDATORY] == 1){
			return true;
		}
		return false;
	}
	
	function check_form_integrity($form_id){
		$sql = "
			select
				DFF.MAPPED_FIELD_ID, NTF.MANDATORY
			from
				DEFINED_FORMFIELDS DFF, NEWSLETTER_TEMPLATES_FORMFIELDS NTF,
				DEFINED_FORMS DF
			where
				DFF.FORM_ID='$form_id' and
				DFF.MAPPED_FIELD_ID=NTF.FIELD_ID and
				DF.MAPPED_NEWSLETTER_ID=NTF.TEMPLATE_ID and
				DFF.FORM_ID=DF.ID
		";
		$res = mysql_query($sql);
		$used_mandatory_fields = array();
		while ($row = mysql_fetch_assoc($res)){
			if ($row[MANDATORY] == 1){
				$used_mandatory_fields[] = $row[MAPPED_FIELD_ID];
			}
		}
		$used_mandatory_fields = array_unique($used_mandatory_fields);
		sort($used_mandatory_fields);
		$newsletter_template_id = returnFieldValue("DEFINED_FORMS", "MAPPED_NEWSLETTER_ID", "ID", $form_id);
		$sql = "
			select 
				NTF.FIELD_ID, NTF.MANDATORY
			from
				NEWSLETTER_TEMPLATES_FORMFIELDS NTF, DEFINED_FORMS DF
			where
				DF.ID='$form_id' and
				DF.MAPPED_NEWSLETTER_ID=NTF.TEMPLATE_ID
		";
		$res = mysql_query($sql);
		$all_mandatory_fields = array();
		while ($row = mysql_fetch_assoc($res)){
			if ($row[MANDATORY] == 1){
				$all_mandatory_fields[] = $row[FIELD_ID];
			}
		}
		
		$all_mandatory_fields = array_unique($all_mandatory_fields);
		sort($all_mandatory_fields);
		if (count(array_diff($all_mandatory_fields, $used_mandatory_fields)) != 0){
			return false;
		} else {
			return true;
		}
	}
?>