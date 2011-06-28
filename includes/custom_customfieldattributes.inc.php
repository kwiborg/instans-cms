<?php
/*
	This optional file can hold installation-specific customfield attributes
	for use in custom-field specifications

	Custom-Customfield attributes must be:
		1. Created in the table CUSTOMFIELDATTRIBUTES
		2. Handled in the switch below
		
	To use a new customfield attribute in an implementation you must also reference it 
	for use in a custom field type in the table CUSTOMFIELDTYPES which in turn must be 
	referenced for use in a specific entry in the CUSTOMFIELDS table (linked to a specific template). 
	
	The format of this file is a switch on the attributetypename similar 
	to that in common.inc-function "return_customfields_input"
	Please refer to this functions for more further examples.
	
	Variables:
		$a_row // Array corresponding to a row in the CUSTOMFIELDATTRIBUTES table
		$attribute_id = "CUSTOM___$row[CUSTOMFIELD_ID]___$a_row[ID]";
		$attribute_value = return_customfieldattribute_value($row[CUSTOMFIELD_ID], $a_row[ID], $_GET[$request_id_getvar]);
		
	Example:
		function return_custom_customfields_input($a_row, $attribute_id, $attribute_value){
			switch ($a_row[ATTRIBUTETYPE]) {
				case "CUSTOM_TEXT":
					$html .= "<input type='text' id='$attribute_id' name='$attribute_id' class='inputfelt' value='$attribute_value' />";
					break;
			}
		}	
	Don't use these reserved typenames as they are already implemented:
		TEXT, TEXTEDITOR, IMAGESELECTOR, FILESELECTOR, DROPDOWN
*/
function return_custom_customfields_input($a_row, $attribute_id, $attribute_value){
	switch ($a_row[ATTRIBUTETYPE]) {
		case "IMAGEARCHIVE":
			$arr_ca = array("folderid" => $attribute_value, "attribute_id" => $attribute_id);
			$html .= ca_imagebrowser_folderselect($arr_ca);
			break;
		case "FILEARCHIVE":
			$arr_ca = array("folderid" => $attribute_value, "attribute_id" => $attribute_id);
			$html .= ca_filebrowser_folderselect($arr_ca);
			break;
	}
	return $html;
}

/* HELPER FUNCTIONS - TYPE: IMAGEARCHIVE */

function ca_imagebrowser_folderselectoptions($arr_ca, $parent_id=0, &$html="", $level=0){
	$res = ca_imagefolders_sqlresult($parent_id, $arr_ca);
	while ($row = mysql_fetch_assoc($res)){
		$html .= "<option ".($row[ID]==$arr_ca[folderid] ? "selected" : "")." value='".$row[ID]."'>";
		$html .= str_repeat("&nbsp;&nbsp;&nbsp;", $level);
		if ($level > 0) {
			$html .= "&raquo;&nbsp;";
		}
		$html .= $row[TITLE];
		$html .= "</option>";
		if (ca_galleryfolder_haschildren($row[ID])){
			ca_imagebrowser_folderselectoptions($arr_ca, $row[ID], $html, $level+1);
		}
	}
	return $html;
}

function ca_imagebrowser_folderselect($arr_ca){
	return "<select id='".$arr_ca[attribute_id]."' name='".$arr_ca[attribute_id]."'><option>Vælg billedmappe...</option>".ca_imagebrowser_folderselectoptions($arr_ca)."</select>";
}

function ca_galleryfolder_haschildren($folder_id){
	$sql = "select ID from PICTUREARCHIVE_FOLDERS where PARENT_ID='$folder_id'";
	$res = mysql_query($sql);
	if (mysql_num_rows($res) > 0){
		return true;
	}
	return false;
}  

function ca_imagefolders_sqlresult($parent_id, $arr_ca){
	$sql = "
		select 
			ID, TITLE, FOLDER_DESCRIPTION, PUBLIC_FOLDER
		from 
			PICTUREARCHIVE_FOLDERS 
		where
			PARENT_ID='$parent_id' and
			SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
		order by 
			TITLE asc
	";
	return $res = mysql_query($sql);
}

/* HELPER FUNCTIONS - TYPE: FILEARCHIVE */
function ca_filebrowser_folderselectoptions($arr_ca, $parent_id=0, &$html="", $level=0){
	$res = ca_filefolders_sqlresult($parent_id, $arr_ca);
	while ($row = mysql_fetch_assoc($res)){
		$html .= "<option ".($row[ID]==$arr_ca[folderid] ? "selected" : "")." value='".$row[ID]."'>";
		$html .= str_repeat("&nbsp;&nbsp;&nbsp;", $level);
		if ($level > 0) {
			$html .= "&raquo;&nbsp;";
		}
		$html .= $row[TITLE];
		$html .= "</option>";
		if (ca_filefolder_haschildren($row[ID])){
			ca_filebrowser_folderselectoptions($arr_ca, $row[ID], $html, $level+1);
		}
	}
	return $html;
}

function ca_filebrowser_folderselect($arr_ca){
	return "<select id='".$arr_ca[attribute_id]."' name='".$arr_ca[attribute_id]."'><option>Vælg filmappe...</option>".ca_filebrowser_folderselectoptions($arr_ca)."</select>";
}

function ca_filefolder_haschildren($folder_id){
	$sql = "select ID from FILEARCHIVE_FOLDERS where PARENT_ID='$folder_id'";
	$res = mysql_query($sql);
	if (mysql_num_rows($res) > 0){
		return true;
	}
	return false;
}  

function ca_filefolders_sqlresult($parent_id, $arr_ca){
	$sql = "
		select 
			ID, TITLE, FOLDERNAME as FOLDER_DESCRIPTION
		from 
			FILEARCHIVE_FOLDERS 
		where
			PARENT_ID='$parent_id' and
			SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
		order by 
			TITLE asc
	";
	return $res = mysql_query($sql);
}



?>