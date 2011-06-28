<?php

function check_data_permission($permission_name, $data_table_name, $data_id, $group_id="", $user_id="", $mode="loose") {
	// Function to check if $user_id / $group_id has access to $permission_name for this $data_table_name / $data_id
	// $mode = "loose" (default)
	// If NO rows found in DATAPERMISSIONS that matches $data_table_name AND $data_id, access is free for all
	// If ANY rows are found in DATAPERMISSIONS that matches $data_table_name AND $data_id, $group_id and/or $user_id MUST also match to allow access.
	// $mode = "strict"
	// If NO rows found in DATAPERMISSIONS that matches $data_table_name AND $data_id, NO ACCESS is granted

	// Function called WITHOUT or with numeric $permission_name is an illegal call and will return false
	if ($permission_name == "" || is_numeric($permission_name)) {
		return false;
	}

	// Function called with BOTH $user_id AND $group_id is an illegal call and will return false
	if ($user_id != "" && $group_id != "") {
		return false;
	}

	// Function called with ONLY $data_table_name OR $data_id combination is an illegal call and will return false
	if ($data_table_name == "" || !is_numeric($data_id)) {
		return false;
	}

	// Always allow access to people with permission to SET the relevant datapermission
    // Only backend, hence the function_exists call
    if (function_exists("checkpermission")){
	    $permission_to_check = "CMS_SETDATAPERMISSIONS_".$data_table_name;
        if (checkpermission($permission_to_check)) {
		    return true;
	    }
    }

	if ($data_table_name != "" && is_numeric($data_id)) {
		$sql = "select count(*) from DATA_PERMISSIONS D, PERMISSIONS P where D.PERMISSION_ID = P.ID and P.NAME = '$permission_name' and D.DATA_TABLE_NAME = '$data_table_name' and D.DATA_ID = '$data_id'";
		if (!$res = mysql_query($sql)) {
			return false;
		}
		if (mysql_result($res,0) == 0) {
			// No restrictions for this combination of $data_table_name / $data_id
			if ($mode == "strict") {
				return false;
			} else {
				return true;
			}
		} else {
			// Restrictions exist for this combination of $data_table_name / $data_id
			// Must validate against $user_id / $group_id
			if (!is_numeric($group_id) && !is_numeric($user_id)) {
				// Function called with only $data_table_name, $data_id
				// Can not validate against $user_id / $group_id
				return false;
			} else if (is_numeric($group_id) && !is_numeric($user_id)) {
				// $group_id given, validate against this
				$sql = "select count(*) from DATA_PERMISSIONS D, PERMISSIONS P where D.PERMISSION_ID = P.ID and P.NAME = '$permission_name' and D.DATA_TABLE_NAME = '$data_table_name' and D.DATA_ID = '$data_id' and D.GROUP_ID = '$group_id'";
				if (!$res = mysql_query($sql)) {
					return false;
				}
				if (mysql_result($res,0) > 0) {
					// Permission found
					return true;
				} else {
					// Permission not found
					return false;
				}
			} else if (!is_numeric($group_id) && is_numeric($user_id)) {
				// $user_id given, validate against this
				// 1. Check if user has individual permission
				$sql = "select count(*) from DATA_PERMISSIONS D, PERMISSIONS P where D.PERMISSION_ID = P.ID and P.NAME = '$permission_name' and D.DATA_TABLE_NAME = '$data_table_name' and D.DATA_ID = '$data_id' and D.USER_ID = '$user_id'";
				if (!$res = mysql_query($sql)) {
					return false;
				}
				if (mysql_result($res,0) > 0) {
					// Permission found";
					return true;
				} else {
					// Individual permission not found";
					// 2. Check if user is a member of a group with permission
					// 2.1 Find groups to which the user is associated
					$sql = "select GROUP_ID from USERS_GROUPS where USER_ID = '$user_id'";
					if (!$res = mysql_query($sql)) {
						return false;
					}
					while ($row = mysql_fetch_assoc($res)) {
						$arr_groups[] = $row[GROUP_ID];
					}
					if (!is_array($arr_groups)) {
						// Not a member of any groups
						return false;
					}
					$str_groups = implode($arr_groups, ",");
					// 2.2 Check if any of these groups has permission";
					$sql = "select count(*) from DATA_PERMISSIONS D, PERMISSIONS P where D.PERMISSION_ID = P.ID and P.NAME = '$permission_name' and D.DATA_TABLE_NAME = '$data_table_name' and D.DATA_ID = '$data_id' and D.GROUP_ID in ($str_groups)";
					if (!$res = mysql_query($sql)) {
						return false;
					}
					if (mysql_result($res,0) > 0) {
						// Permission found";
						return true;
					} else {
						// Permission not found";
						return false;
					}
				}
			} else {
				// Unexpected values in $group_id and/or $user_id";
				return false;
			}
		}
	} else {
		return false;
	}
}


function unhtmlentities ($string)  {
	$trans_tbl = get_html_translation_table (HTML_ENTITIES);
	$trans_tbl = array_flip ($trans_tbl);
	$ret = strtr ($string, $trans_tbl);
	return preg_replace('/&#(\d+);/me', "chr('\\1')",$ret);
} 

function hentRow($id, $tabel) {
	$sql = "select * from $tabel where ID='$id'";
	$result = mysql_query($sql) or die(mysql_error());
	if (mysql_num_rows($result) == 0) {
		die("Funktionen hentRow (id $id på tabel $tabel) returnerede ingen rækker");
	}
	$data = mysql_fetch_array($result);
	return $data;
}

  function returnFormTitle($formid) {
   global $dbname;
   $sql = "select TITLE from DEFINED_FORMS where ID='$formid'";
   $result = mysql_query( $sql);
   $row = mysql_fetch_row($result);
   return $row[0];
  }
  
  function outputTilmeldingsHtml($mode, $tilmeldings_id){
  global $dbname;
  $html .= "<ul class='mailReceipt'>";
  if ($mode==1) $sql = "select * from TILMELDINGER where ID='$tilmeldings_id'";
  if ($mode==2) $sql = "select * from TILMELDINGER where UNIK='$tilmeldings_id'";
  $result = mysql_query( $sql);
  $row = mysql_fetch_array($result);
  $ids = explode("|¤|", $row[FIELD_IDS]);
  $values = explode("|¤|", $row[FIELD_VALUES]);
  foreach ($ids as $key=>$id) {
   $fielddata = getFieldSettings($id);
   if ($fielddata[FIELDTYPE] == 1) $html .= "<li><strong>$fielddata[CAPTION]</strong>&nbsp;" . $values[$key] . "</li>"; 
   if ($fielddata[FIELDTYPE] == 2) $html .= "<li><strong>$fielddata[CAPTION]</strong>&nbsp;" . $values[$key] . "</li>"; 
   if ($fielddata[FIELDTYPE] == 3) {
    $html .= "<li><strong>$fielddata[CAPTION]</strong>&nbsp;<ul>";
    $radiovalues   = explode(",", $values[$key]);
	$radiocaptions = explode("|", $fielddata[RADIO_CAPTIONS]);
	foreach($radiovalues as $val){
	 $html .= "<li>$radiocaptions[$val]</li>";
	}
	//$html = substr($html, 0,-2);
	$html .= "</ul></li>";
   }
   if ($fielddata[FIELDTYPE] == 4) {
    $html .= "<li><strong>$fielddata[CAPTION]</strong>&nbsp;<ul>";
    $checkvalues   = explode(",", $values[$key]);
	$checkcaptions = explode("|", $fielddata[CHECKBOX_CAPTIONS]);
	foreach($checkvalues as $val){
	 $html .= "<li>$checkcaptions[$val]</li>";
	}
	//$html = substr($html, 0,-2);
	$html .= "</ul></li>";
   }
  }
  $html .= "</ul>";
  return $html;
 }

/* 
	Disse funktioner (ovenfor og nedenfor) skal laves om,
	så de bruger HtmlMimeMail og kan outputte en mail med tekst- og html-part.
	Problemet er, at Outlook + Exchange-servere laver html-mails om til
	attachments, så derfor er en html-mail i sig selv ikke nok.
	CJS, 6/7-06
		
  function outputTilmeldingsPlaintext($mode, $tilmeldings_id){
  global $dbname;
  $html .= "<ul>";
  if ($mode==1) $sql = "select * from TILMELDINGER where ID='$tilmeldings_id'";
  if ($mode==2) $sql = "select * from TILMELDINGER where UNIK='$tilmeldings_id'";
  $result = mysql_query( $sql);
  $row = mysql_fetch_array($result);
  $ids = explode("|¤|", $row[FIELD_IDS]);
  $values = explode("|¤|", $row[FIELD_VALUES]);
  foreach ($ids as $key=>$id) {
   $fielddata = getFieldSettings($id);
   if ($fielddata[FIELDTYPE] == 1) $html .= "<li><strong>$fielddata[CAPTION]</strong> " . $values[$key] . "</li>"; 
   if ($fielddata[FIELDTYPE] == 2) $html .= "<li><strong>$fielddata[CAPTION]</strong> " . $values[$key] . "</li>"; 
   if ($fielddata[FIELDTYPE] == 3) {
    $html .= "<li><strong>$fielddata[CAPTION]</strong><ul>";
    $radiovalues   = explode(",", $values[$key]);
	$radiocaptions = explode("|", $fielddata[RADIO_CAPTIONS]);
	foreach($radiovalues as $val){
	 $html .= "<li>$radiocaptions[$val],</li>";
	}
	$html = substr($html, 0,-2);
	$html .= "</ul></li>";
   }
   if ($fielddata[FIELDTYPE] == 4) {
    $html .= "<li><strong>$fielddata[CAPTION]</strong><ul>";
    $checkvalues   = explode(",", $values[$key]);
	$checkcaptions = explode("|", $fielddata[CHECKBOX_CAPTIONS]);
	foreach($checkvalues as $val){
	 $html .= "<li>$checkcaptions[$val],</li>";
	}
	$html = substr($html, 0,-2);
	$html .= "</ul></li>";
   }
  }
  $html .= "</ul>";
  return $html;
 } 
*/

  function outputTilmeldingsPlaintext($mode, $tilmeldings_id){
  global $dbname;
  if ($mode==1) $sql = "select * from TILMELDINGER where ID='$tilmeldings_id'";
  if ($mode==2) $sql = "select * from TILMELDINGER where UNIK='$tilmeldings_id'";
  $result = mysql_query( $sql);
  $row = mysql_fetch_array($result);
  $ids = explode("|¤|", $row[FIELD_IDS]);
  $values = explode("|¤|", $row[FIELD_VALUES]);
  foreach ($ids as $key=>$id) {
   $fielddata = getFieldSettings($id);
   if ($fielddata[FIELDTYPE] == 1) $html .= "$fielddata[CAPTION] " . $values[$key] . "\n\n"; 
   if ($fielddata[FIELDTYPE] == 2) $html .= "$fielddata[CAPTION] " . $values[$key] . "\n\n"; 
   if ($fielddata[FIELDTYPE] == 3) {
    $html .= "$fielddata[CAPTION] ";
    $radiovalues   = explode(",", $values[$key]);
	$radiocaptions = explode("|", $fielddata[RADIO_CAPTIONS]);
	foreach($radiovalues as $val){
	 $html .= "$radiocaptions[$val], ";
	}
	$html = substr($html, 0,-2);
	$html .= "\n\n";
   }
   if ($fielddata[FIELDTYPE] == 4) {
    $html .= "$fielddata[CAPTION] ";
    $checkvalues   = explode(",", $values[$key]);
	$checkcaptions = explode("|", $fielddata[CHECKBOX_CAPTIONS]);
	foreach($checkvalues as $val){
	 $html .= "$checkcaptions[$val], ";
	}
	$html = substr($html, 0,-2);
	$html .= "\n\n";
   }
  }
  return $html;
 } 
 
 function returnSiteName($site_id) {
  global $dbname;
  $sql = "select SITE_NAME from SITES where SITE_ID='$site_id'";
  $result = mysql_query( $sql);
  $row = mysql_fetch_array($result);   
  return $row[0];
 }
 
 function returnFileTitle($id) {
  $sql = "select TITLE, DESCRIPTION, ORIGINAL_FILENAME from FILEARCHIVE_FILES where ID='$id'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row1 = mysql_fetch_array($result);
  $sql = "select FOLDERNAME from FILEARCHIVE_FOLDERS where ID='$row[3]'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row2 = mysql_fetch_array($result);
  return $row1;
} 	 

 function returnFileName($file_id, $mode, $table) {
  $sql = "select FILENAME, ORIGINAL_FILENAME from $table where ID='$file_id'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_array($result);
  if ($mode == 1) return $row["FILENAME"];
  if ($mode == 2) return $row["ORIGINAL_FILENAME"];
 }

 function returnHeading($id, $tabel)
 {
  global $dbname;  
  $sql = "select HEADING from $tabel where ID='$id'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_array($result);
  return $row["HEADING"];
 } 
 
 function returnBreadcrumb($id, $tabel)
 {
  global $dbname;  
  $sql = "select BREADCRUMB from $tabel where ID='$id'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_array($result);
  return $row["BREADCRUMB"];
 } 
 
 
 function returnSnippet($id, $tabel, $chars, $word)
 {
  global $dbname;  
  $sql = "select CONTENT from $tabel where ID='$id'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_array($result);
  $row["CONTENT"] = unhtmlentities(strip_tags($row["CONTENT"]));
  $start = stripos($row["CONTENT"], $word);
  $snippet = substr($row["CONTENT"] ,$start, $chars);  
  $snippet = eregi_replace($word, "<span style='background-color:#00ff00'>$word</span>", $snippet);
  return "... " . $snippet;
 } 

 function returnHighestPos($parent_id, $menu_id)
 {
  $sql = "select POSITION from PAGES where PARENT_ID='$parent_id' and MENU_ID='$menu_id' and DELETED='0' order by POSITION desc limit 1";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  return 1*$row[0];
 }

function returnPageTitle($pageid, $for="") {
	$sql = "select BREADCRUMB, HEADING, META_SEOTITLE from PAGES where ID='$pageid'";
	$result = mysql_query( $sql) or die(mysql_error());
	$row = mysql_fetch_array($result);

	if ($for == "titletag") {
		if ($row[META_SEOTITLE] == "BREADCRUMB") {
			return $row[BREADCRUMB];
		} elseif ($row[META_SEOTITLE] == "HEADING") {
			return $row[HEADING];
		} elseif ($row[META_SEOTITLE] != "") {	
			return $row[META_SEOTITLE];
		} else {
			$meta_title_default = returnGeneralSetting("META_TITLE_USEPAGESCOLUMN");
			return $row[$meta_title_default];  
		}
	} else {
		return $row[BREADCRUMB];  
	}
}
 
 function returnNewsTitle($pageid)
 {
  global $dbname;  
  $sql = "select HEADING from NEWS where ID='$pageid' and DELETED='0'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_array($result);
  if ($row["HEADING"]) return $row["HEADING"];  
 }
 
 function returnEventTitle($pageid)
 {
  global $dbname;  
  $sql = "select HEADING from EVENTS where ID='$pageid' and DELETED='0'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_array($result);
  if ($row["HEADING"]) return $row["HEADING"];  
 }

 function returnGroupName($group_id)
 {
  global $dbname;
  $sql = "select GROUP_NAME from GROUPS where ID=$group_id";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_array($result);
  return $row[0];
 }
 
 function returnUserName($user_id)
 {
  global $dbname;
  $sql = "select FIRSTNAME, LASTNAME, USERNAME from USERS where ID=$user_id";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_array($result);
  if ($row[2] == "") {
  	return "";
  } else {
	  return "$row[0] $row[1] (\"$row[2]\")";
  }
 }

 function UKtimeToUNIXtime($uktime) { // yyyy-mm-dd
  $exp = explode("-", $uktime);
  return $unixdate = mktime(0,0,0,$exp[1],$exp[2],$exp[0]);
 }
 
 function returnAuthorName($author_id, $shorthand) {
  global $dbname;
  $sql = "select FIRSTNAME, LASTNAME from USERS where ID='$author_id'";
  $result = mysql_query( $sql);
  $row = mysql_fetch_array($result);
  if ($shorthand == 0) return $row["FIRSTNAME"] . " " . $row["LASTNAME"];
  if ($shorthand == 1) return substr($row["FIRSTNAME"],0,1) . ". " . $row["LASTNAME"];
 }

 function microtime_float() {
  list($usec, $sec) = explode(" ", microtime());
  return ((float)$usec + (float)$sec);
 }
 
 function returnNiceDateTime($unixtime, $medklokkeslet, $medsekunder = 0) {
  $ind = date("Y-m-d H:i:s", $unixtime);
  $dato_ind = substr($ind, 0, 10);
  if ($medsekunder == 0) {
	$tid = substr($ind, -8, 5);
  } else {
	$tid = substr($ind, -8, 8);
  }
  if ($dato_ind == "0000-00-00") return "Ikke angivet";
  $temp   = explode("-", $dato_ind);
  $danish_months = array(1 => "januar", "februar", "marts", "april", "maj", "juni", "juli", "august", "september", "oktober", "november", "december");
  $dato_ud = 1*$temp[2] . ". " . $danish_months[1*$temp[1]] . " " . $temp[0];
  if ($medklokkeslet == 1) return $dato_ud . " " . $tid;
  else return $dato_ud;
 }
 
 function reverseDate($dato = "") {
  if ($dato == "0000-00-00") return "Irrelevant";
  if ($dato == "") {
	$dato = date("Y-m-d");
  }
  $temp = explode("-", $dato);
  return $temp[2] . "-" . $temp[1] . "-" . $temp[0];
 }
 
 function returnMonth($m) {
  $danish_months = array(1 => "Januar", "Februar", "Marts", "April", "Maj", "Juni", "Juli", "August", "September", "Oktober", "November", "December");
  return $danish_months[abs($m)];
 }
       

// Add support for PHP < 5 by implementing this function manually
if (!function_exists("stripos")) {
  function stripos($str,$needle,$offset=0)
  {
     return strpos(strtolower($str),strtolower($needle),$offset);
  }
}
/*
	// The above version replaces this one on 2006-06-23
 if(!function_exists('stripos')) {
   function stripos($haystack, $needle) {
       $parts = explode(strtolower($needle), strtolower($haystack), 2);
       if (count($parts) == 1) {
           return false;
       }
       return strlen($parts[0]);
    }
 } 
*/
 function getFieldSettings($id){ 
  global $dbname;
  $sql = "select FIELDTYPE, CAPTION, RADIO_CAPTIONS, CHECKBOX_CAPTIONS from DEFINED_FORMFIELDS where ID='$id'";
  $result = mysql_query( $sql);
  $row = mysql_fetch_array($result);
  return $row; 
 } 
 
function returnFieldValue($tabel, $feltnavn, $a="", $b="") {
	$sql = "select $feltnavn from $tabel";
	if ($a != "" && $b != "") {
		$sql .= " where $a = '$b'";
	}
	if ($result = mysql_query($sql)) {
		if ($row = mysql_fetch_row($result)) {
			return $row[0];
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function returnGeneralSetting($settingname, $siteid=""){
	if ($siteid == "") {
		$siteid = $_SESSION[SELECTED_SITE];
	}
	if ($siteid == "") {
		$siteid = $_SESSION[CURRENT_SITE];
	}
	$sql = "select ".$settingname." from GENERAL_SETTINGS where ID='$siteid'";
	$result = mysql_query($sql);
	return mysql_result($result, 0);
}

function returnImageUrl($image_id) {
	global $picturearchive_UploaddirAbs;
	$sql = "select PICTUREARCHIVE_FOLDERS.FOLDERNAME, PICTUREARCHIVE_PICS.FILENAME
	from PICTUREARCHIVE_PICS, PICTUREARCHIVE_FOLDERS
	where PICTUREARCHIVE_PICS.FOLDER_ID = PICTUREARCHIVE_FOLDERS.ID
	and PICTUREARCHIVE_PICS.ID = '$image_id'
	limit 1";
	if ($result = mysql_query($sql)) {
		if ($row = mysql_fetch_array($result)) {
			$url = $picturearchive_UploaddirAbs."/".$row[FOLDERNAME]."/".$row[FILENAME];
		}
	}
	return $url;
}


function returnImageThumbUrl($image_id) {
	$image_url = returnImageUrl($image_id);
	$thumburl = explode("/",$image_url);
	$lastpart = array_pop($thumburl);
	$thumburl[] = "thumbs";
	$thumburl[] = $lastpart;
	$thumburl = implode("/", $thumburl); 
	return $thumburl;
}

 function returnSITE_PATH($site_id) {
  $sql = "select SITE_PATH from SITES where SITE_ID='$site_id' limit 1;";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_array($result);
  return $row["SITE_PATH"];
 }

 function returnBASE_URL($site_id) {
  $sql = "select BASE_URL from SITES where SITE_ID='$site_id' limit 1;";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_array($result);
  return $row["BASE_URL"];
 }

function return_rewrite_keyword($input_string, $content_id, $tablename, $site_id){
// 2007-04-16	-	Added $site_id input parameter to function + database calls 
//					to make same key in several sites possible. 
	if ($input_string != ""){
		/// FJERN DANSKE TEGN
		$input_string = str_replace("\"", "", $input_string);
		$input_string = str_replace("æ", "ae", $input_string);
		$input_string = str_replace("ø", "oe", $input_string);
		$input_string = str_replace("å", "aa", $input_string);
		$input_string = str_replace("Æ", "Ae", $input_string);
		$input_string = str_replace("Ø", "Oe", $input_string);
		$input_string = str_replace("Å", "Aa", $input_string);
		// ERSTAT ANDRE UDVALGTE TEGN
		$input_string = str_replace("á", "a", $input_string);
		$input_string = str_replace("č", "c", $input_string);
		$input_string = str_replace("ť", "t", $input_string);
		$input_string = str_replace("ľ", "l", $input_string);
		$input_string = str_replace("ý", "y", $input_string);
		$input_string = str_replace("í", "i", $input_string);
		$input_string = str_replace("ú", "u", $input_string);
		$input_string = str_replace("ž", "z", $input_string);
		/// ERSTAT UDENLANDSKE TEGN (é ETC.) MED DET, DER "LIGNER"
		$input_string = htmlentities($input_string);
		$input_string = preg_replace("/&(\w{1})(\w*);/", "\\1", $input_string);
		/// TILLAD KUN ALFANUMERISKE TEGN + SPACES
		$input_string = preg_replace("/[^\w-\s]/", "", $input_string);
		$input_string = ereg_replace("[[:space:]]+", " ", $input_string);
		/// LAV LOWECASE
		$input_string = trim(strtolower($input_string));
		/// ERSTAT SPACES MED "-"
		$input_string = str_replace(" - ", " ", $input_string);
		$input_string = str_replace(" ", "-", $input_string);
		/// CHECK, OM KEY'EN FINDES I FORVEJEN PÅ EN ANDEN SIDE
		$sql = "
			select 
				COUNT(ID) as ANTAL
			from 
				REWRITE_KEYWORDS 
			where 
				KEYWORD='$input_string' and
				REQUEST_ID != '$content_id' and
				SITE_ID in (0,'$site_id')
		";
		$res = mysql_query($sql);
		$row = mysql_fetch_assoc($res);
		if ($row[ANTAL] > 0){
			$sql = "select KEYWORD from REWRITE_KEYWORDS where REQUEST_ID != '$content_id' and (KEYWORD like '$input_string-_' or  KEYWORD like '$input_string-__' or  KEYWORD like '$input_string-___' or  KEYWORD like '$input_string-____') and SITE_ID in (0,'$site_id')"; 
			$res_morekeys = mysql_query($sql);
			if (mysql_num_rows($res_morekeys) == 0){
				$input_string .= "-1";
			} else {
				$key_numbers = array();
				while ($row_morekeys  = mysql_fetch_assoc($res_morekeys)){
					$temp = explode("-", $row_morekeys[KEYWORD]);
					if (is_numeric($temp[count($temp)-1])){
						$key_numbers[] = $temp[count($temp)-1];
					}
				}
				rsort($key_numbers);
				$new_number = (1*$key_numbers[0])+1;
				$input_string .= "-".$new_number;
			}
		}
	} else {
		/// RETURNER DEN EKSISTERENDE KEY, HVIS 
		/// DER IKKE KOMMER NOGEN STRENG IND FUNKTIONEN
		$sql = "
			select 
				KEYWORD
			from 
				REWRITE_KEYWORDS 
			where 
				REQUEST_ID='$content_id' and
				TABLENAME='$tablename' and
				SITE_ID='$site_id'
		";
		$res = mysql_query($sql);
		$row = mysql_fetch_assoc($res);
		$input_string = $row["KEYWORD"];
	}
	return $input_string;
}

function save_rewrite_keyword($keyword, $content_id, $tablename, $site_id){
// 2007-04-16	-	Added $site_id input parameter to function + database calls 
//					to make same key in several sites possible. 
	$sql = "select ID from REWRITE_KEYWORDS where TABLENAME='$tablename' and REQUEST_ID='$content_id' and SITE_ID='$site_id'";
	$res = mysql_query($sql);
	if (mysql_num_rows($res)){
		$row = mysql_fetch_assoc($res);
		$keyword_id = $row[ID];
		if ($keyword == "") {
			// Previously defined keyword has been deleted, delete from databasse
			$sql = "delete from REWRITE_KEYWORDS where TABLENAME='$tablename' and REQUEST_ID='$content_id' and SITE_ID='$site_id'";
		} else {
			// Previously defined keyword has been changed, update databasse
			$sql = "
				update
					REWRITE_KEYWORDS set
					KEYWORD='$keyword' 
				where
					ID='$keyword_id' and 
					SITE_ID='$site_id'
				limit 1
			";
		}
	} else {
		if ($keyword != "") {
			/// New non-empty keyword has been defined, insert into database
			$sql = "
				insert into
					REWRITE_KEYWORDS (KEYWORD, TABLENAME, REQUEST_ID, SITE_ID)
				values
					('$keyword', '$tablename', '$content_id', '$site_id')
			";
		}
	}
	if ($sql != "") {
		mysql_query($sql);
	}
}

 function db_safedata($value) {
 //	echo "SAFECHECK: $value";
	# Returns safely quoted values to prevent evil database injection
	# Requires connection to the database established via mysql_connect()
	if (get_magic_quotes_gpc()) {
		$value = stripslashes($value);
	}
	if (is_numeric($value)) {
		return $value;
	} else {
		// Old php versions don't have this security feature
		if (!function_exists("mysql_real_escape_string")) {
			return $value;
		}
		if ($value = mysql_real_escape_string($value)) {
			return $value;
		} else {
			if ($value == '') {
				return $value;
			}
			die(db_errorhandler("Funktionen '".__FUNCTION__."' fejlede med værdien '$value'. Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
		}
	}
 }
 
 function db_errorhandler($errormessage) {
	# Temp function to handle sql errors.
	# To be extended with logging
	echo $errormessage;
	echo mysql_error();
 }
 
function blogpost_tags_array($blogpost_id){
	$sql = "
		select 
			TAGS.TAGNAME, TAGS.ID 
		from
			TAGS, TAG_REFERENCES
		where
			TAG_REFERENCES.REQUEST_ID='$blogpost_id' and TAG_REFERENCES.TAG_ID=TAGS.ID
				
	";
	$res = mysql_query($sql);
	$tags = array();
	while ($row = mysql_fetch_assoc($res)){
		$tags[] = array("ID" => $row[ID], "TAGNAME" => $row[TAGNAME]);
	}
	if ($tags){
		return $tags;
	} else {
		return array();
	}
}

function blog_snippet($content, $length_sentences=10){
	$content = strip_tags($content);
	$sentences = explode(".", $content);
	foreach ($sentences as $k => $v){
		$sentences[$k] = trim($v);
	}
	$use = array_slice($sentences, 0, $length_sentences);
	$content = implode(". ", $use).".";
	return $content;
}

function check_whitelist($email, $tablename, $request_id){
	$sql = "
		select ID, EMAIL from EMAIL_WHITELIST where EMAIL='$email' and TABLENAME='$tablename' and REQUEST_ID='$request_id'
	";
	$res = mysql_query($sql);
	if (mysql_num_rows($res)){
		$row = mysql_fetch_assoc($res);
		return $row["ID"];
	} else {
		return false;
	}
}

function add_whitelist($email, $tablename, $request_id, $commment_id, $arr_content=array()){
	if ($commment_id){
		$sql = "select COMMENTER_EMAIL from COMMENTS where ID='".$commment_id."' limit 1";
		$res_wl = mysql_query($sql);
		$row_wl = mysql_fetch_assoc($res_wl);
		$sql = "select ID from EMAIL_WHITELIST where EMAIL='".$row_wl[COMMENTER_EMAIL]."' and TABLENAME='".$tablename."' and REQUEST_ID='".$request_id."'";
		$res_exists = mysql_query($sql);
		if (mysql_num_rows($res_exists) == 0){
			$sql = "insert into EMAIL_WHITELIST (EMAIL, TABLENAME, REQUEST_ID) values ('".$row_wl[COMMENTER_EMAIL]."', '$tablename', '".$request_id."')";
			mysql_query($sql);
			return array($row_wl[COMMENTER_EMAIL], true);
		} else {
			return array($row_wl[COMMENTER_EMAIL], false);
		}
	} else if ($email){
		$sql = "select ID from EMAIL_WHITELIST where EMAIL='".$email."' and TABLENAME='".$tablename."' and REQUEST_ID='".$request_id."'";
		$res_exists = mysql_query($sql);
		if (mysql_num_rows($res_exists) == 0){
			$sql = "insert into EMAIL_WHITELIST (EMAIL, TABLENAME, REQUEST_ID) values ('".$email."', '$tablename', '".$request_id."')";
			mysql_query($sql);
			return array($email, true);
		} else {
			return array($email, false);
		} 
	}
}

function remove_whitelist($email, $tablename, $request_id, $whitelist_id, $arr_content=array()){
	if ($whitelist_id){
		$sql = "select EMAIL from EMAIL_WHITELIST where ID='".$whitelist_id."'";
		$res = mysql_query($sql);
		if (mysql_num_rows($res)){
			$row = mysql_fetch_assoc($res);
			$sql = "delete from EMAIL_WHITELIST where ID='$whitelist_id' limit 1";
			mysql_query($sql);
			return array($row[EMAIL], true);
		} else {
			return array($row[EMAIL], false);			
		}
	} else if ($email){
		$sql = "select EMAIL from EMAIL_WHITELIST where EMAIL='".$email."'";
		$res = mysql_query($sql);
		if (mysql_num_rows($res)){
			$row = mysql_fetch_assoc($res);
			$sql = "delete from EMAIL_WHITELIST where EMAIL='$email' limit 1";
			mysql_query($sql);
			return array($email, true);
		} else {
			return array($email, false);			
		}
	}
}

function comment_count($postid, $tablename="BLOGPOSTS", $parentid="", $approved=1){
	
	if ($postid) {
		$sql = "
			select 
				count(C.ID)
			from
				COMMENTS C
			where
				C.TABLENAME='$tablename' and C.REQUEST_ID='".$postid."' and
				C.IS_SPAM='0' and C.DELETED='0' and C.APPROVED='$approved'
		";
		$res = mysql_query($sql);
		return mysql_result($res,0);
	} elseif ($tablename == "BLOGPOSTS" && $parentid) {
		$sql = "
			select
				count(C.ID)
			from
				COMMENTS C, BLOGPOSTS B
			where
				C.REQUEST_ID = B.ID and 
				B.BLOG_ID = '$parentid' and 
				C.TABLENAME='$tablename' and
				C.IS_SPAM='0' and C.DELETED='0' and C.APPROVED='1'";
		$res = mysql_query($sql);
		return mysql_result($res,0);
	
	}
}

function return_feed_url($tablename, $requestid) {
	$feed_url = returnBASE_URL($_SESSION[SELECTED_SITE]).returnSITE_PATH($_SESSION[SELECTED_SITE])."/feeds/".return_feed_filename($tablename, $requestid);
	return $feed_url;
}

function return_feed_filename($tablename, $requestid) {
	$sql = "select SYNDICATION_KEY from $tablename where ID = '$requestid'";
	$res = mysql_query($sql);
	$syndicationkey = mysql_result($res,0);
	switch($tablename) {
		case "BLOGS":
			$prefix = "blog";
			break;
		case "NEWSFEEDS":
			$prefix = "newsfeed";
			break;
		default:
			$prefix = strtolower($tablename);
	}
	$filename = $prefix."_".$requestid."_".$syndicationkey.".xml";
	return $filename;
}

function return_feed_savepath() {
	return $_SERVER["DOCUMENT_ROOT"]."/feeds/";
}

function rewrite_urls_callback($matches) {
// 2007-04-16	-	Function changed to ensure that correct site-specific urls are returned
// 2007-10-01	-	Function changed to include rewrite of shop urls
// 2008-02-04	-	Function changed to accept url as string (stand alone)
//	echo "<hr/><br/>Rewriting $matches[2]$matches[3] ...";

	// Emulate array behavior when called with url-string
	// This function is ONLY called with an url-string from backend
	if (is_array($matches)) {
		$in_url = $matches[2].$matches[3];
		$backend = false;
	} else {
		// Backend implied
		$backend = true;
		$in_url = $matches;
		$matches = array("", "", $in_url, "", "");
	}

//	echo "<br/>IN: $in_url";

	if (!strstr($in_url, "index.php")) {
		return $matches[1].$in_url.$matches[4];
	}
	$arr_url = parse_url($in_url);

//	echo "<hr/>ORIGINAL ARR_URL (in: $in_url):<pre>";
//	print_r($arr_url);
//	echo "</pre>";
	
	// Set current language and current site session variables
	$arr_url[site] = set_current_site();
	if ($backend) {
		$arr_url[lang] = get_language_from_url($in_url);
	} else {	
		$arr_url[lang] = set_current_language();
	}

	// Begin building rw url
	$rw_url = $arr_url[scheme]."://".$arr_url[host];

	// Strip "index.php" from path and append it to the url;
		//	2007-06-19	-	 Generic site path moved to bottom of function (fallback)
		//	$arr_url[path] = str_replace("index.php", "", $arr_url[path]);
		//	$rw_url .= $arr_url[path];
	$rw_url .= "/";
	$arr_url[file] = "index.php";

	// Parse query
	if (isset($arr_url[query])) {
		$arr_url[query] = unhtmlentities(convert_url_parameterstring($arr_url[query]));
		$arr_get = explode("&",$arr_url[query]);
		foreach ($arr_get as $key => $value) {
			$this_get = explode("=", $value);
			if (count($this_get) == 2) {
				$arr_url[query_parsed][$this_get[0]] = urldecode($this_get[1]);
			}
		}
	}


	// Check for forced site change through url parameter (&site=1)
	if (isset($arr_url[query_parsed][siteid])) { 
		$rw_sitekeyword = return_rewrite_keyword("", $arr_url[query_parsed][siteid], "SITES", 0); // 0 added as site-id because the site keywords are global
		if ($rw_sitekeyword != "") {
			$rw_url .= $rw_sitekeyword."/";
		} else {
			// Attempting to set site that does not have a rewrite keyword, return original url
			return $matches[1].$in_url.$matches[4];
		}		
	}

	// Check for mode and append if valid rewrite mode	
	if (isset($arr_url[query_parsed][mode])) {
		if ($rw_mode = return_rewrite_mode($arr_url[query_parsed][mode], $arr_url[lang])) {
			$rw_url_mode = $rw_mode."/";
		} else {
			return $matches[1].$in_url.$matches[4];
		}
	}

/*
	echo "<br/>RWURL_ARR (rw: $rw_url):<pre>";
	print_r($arr_url);
	echo "</pre>";
*/

	// Find keyword for this combination of mode and id
	if (isset($arr_url[query_parsed][pageid])) {
		// Add site name (currently only implemented for PAGES, similar features may be implemented for other modes!)
		// NOTE: Now also implemented for other modes (CJS, 19/6/2007)
		$siteid = returnFieldValue("PAGES", "SITE_ID", "ID", $arr_url[query_parsed][pageid]);
		$rw_sitekeyword = return_rewrite_keyword("", $siteid, "SITES", 0); // 0 added as rewrite site_id because the site keywords are global
		if ($rw_sitekeyword != "") {
			$rw_url .= $rw_sitekeyword."/".$rw_url_mode;
		} else {
			$rw_url .= $rw_url_mode;
		}		

		// Add page keyword
		global $useModRewrite_preserve_page_hierarchy;
		if ($useModRewrite_preserve_page_hierarchy) {
			// Preserve hierarchy
			$rw_page_url .= rewrite_urls_callback_pagekey_recursive($arr_url[query_parsed][pageid], $arr_url[site]);
			if (strpos($rw_page_url, "||nokey|||")) { // Note that match is performed against ||nokey||| NOT |||nokey||| so strpos will not return zero (=false) 
				return $matches[1].$in_url.$matches[4];
			} elseif ($arr_url[query_parsed][pageid] == getFrontpageId($arr_url[lang], $arr_url[site])) {
				// Showing home page for current site/lang combination
				$rw_url .= ""; 
			} else {
				$rw_url .= $rw_page_url."/";
			}
		} else {
			// Flat - only current page
			$rw_keyword = generate_rewrite_keyword("PAGES", $arr_url[query_parsed][pageid], "HEADING", $arr_url[site]);
			if ($rw_keyword == "") {
				return $matches[1].$in_url.$matches[4];
			} elseif ($arr_url[query_parsed][pageid] == getFrontpageId($arr_url[lang], $arr_url[site])) {
				// Showing home page for current site/lang combination
				$rw_url .= ""; 
			} else {
				$rw_url .= $rw_keyword."/";
			}
		}
	} elseif (isset($arr_url[query_parsed][newsid])) {
		$siteid = returnFieldValue("NEWS", "SITE_ID", "ID", $arr_url[query_parsed][newsid]);
		$rw_sitekeyword = return_rewrite_keyword("", $siteid, "SITES", 0); // 0 added as rewrite site_id because the site keywords are global
		if ($rw_sitekeyword != "") {
			$rw_url .= $rw_sitekeyword."/".$rw_url_mode;
		} else {
			$rw_url .= $rw_url_mode;
		}		
		// $rw_url .= $rw_url_mode; // Add mode
		$rw_keyword = generate_rewrite_keyword("NEWS", $arr_url[query_parsed][newsid], "HEADING", $arr_url[site]);
		if ($rw_keyword == "") {
			return $matches[1].$in_url.$matches[4];
		} else {
			$rw_url .= $rw_keyword."/";
		}
	} elseif (isset($arr_url[query_parsed][eventid])) {
		$siteid = returnFieldValue("EVENTS", "SITE_ID", "ID", $arr_url[query_parsed][eventid]);
		$rw_sitekeyword = return_rewrite_keyword("", $siteid, "SITES", 0); // 0 added as rewrite site_id because the site keywords are global
		if ($rw_sitekeyword != "") {
			$rw_url .= $rw_sitekeyword."/".$rw_url_mode;
		} else {
			$rw_url .= $rw_url_mode;
		}		
		// $rw_url .= $rw_url_mode; // Add mode
		$rw_keyword = generate_rewrite_keyword("EVENTS", $arr_url[query_parsed][eventid], "HEADING", $arr_url[site]);
		if ($rw_keyword == "") {
			return $matches[1].$in_url.$matches[4];
		} else {
			$rw_url .= $rw_keyword."/";
		}
	} elseif (isset($arr_url[query_parsed][feedid])) {
		$siteid = returnFieldValue("NEWSFEEDS", "SITE_ID", "ID", $arr_url[query_parsed][feedid]);
		$rw_sitekeyword = return_rewrite_keyword("", $siteid, "SITES", 0); // 0 added as rewrite site_id because the site keywords are global
		if ($rw_sitekeyword != "") {
			$rw_url .= $rw_sitekeyword."/".$rw_url_mode;
		} else {
			$rw_url .= $rw_url_mode;
		}		
		// $rw_url .= $rw_url_mode; // Add mode
		$rw_keyword = generate_rewrite_keyword("NEWSFEEDS", $arr_url[query_parsed][feedid], "NAME", $arr_url[site]);
		if ($rw_keyword == "") {
			return $matches[1].$in_url.$matches[4];
		} else {
			$rw_url .= $rw_keyword."/";
		}
	} elseif (isset($arr_url[query_parsed][calendarid])) {
		$siteid = returnFieldValue("CALENDARS", "SITE_ID", "ID", $arr_url[query_parsed][calendarid]);
		$rw_sitekeyword = return_rewrite_keyword("", $siteid, "SITES", 0); // 0 added as rewrite site_id because the site keywords are global
		if ($rw_sitekeyword != "") {
			$rw_url .= $rw_sitekeyword."/".$rw_url_mode;
		} else {
			$rw_url .= $rw_url_mode;
		}		
		// $rw_url .= $rw_url_mode; // Add mode
		$rw_keyword = generate_rewrite_keyword("CALENDARS", $arr_url[query_parsed][calendarid], "NAME", $arr_url[site]);
		if ($rw_keyword == "") {
			return $matches[1].$in_url.$matches[4];
		} else {
			$rw_url .= $rw_keyword."/";
		}
	} elseif (isset($arr_url[query_parsed][folderid])) { // Picturearchive/gallery folders
		$siteid = returnFieldValue("PICTUREARCHIVE_FOLDERS ", "SITE_ID", "ID", $arr_url[query_parsed][folderid]);
		$rw_sitekeyword = return_rewrite_keyword("", $siteid, "SITES", 0); // 0 added as rewrite site_id because the site keywords are global
		if ($rw_sitekeyword != "") {
			$rw_url .= $rw_sitekeyword."/".$rw_url_mode;
		} else {
			$rw_url .= $rw_url_mode;
		}		
		// $rw_url .= $rw_url_mode; // Add mode
		$rw_keyword = generate_rewrite_keyword("PICTUREARCHIVE_FOLDERS", $arr_url[query_parsed][folderid], "TITLE", $arr_url[site]);
		if ($rw_keyword == "") {
			return $matches[1].$in_url.$matches[4];
		} else {
			$rw_url .= $rw_keyword."/";
		}
	} elseif (isset($arr_url[query_parsed][blogid])) { // Blog
		$siteid = returnFieldValue("BLOGS ", "SITE_ID", "ID", $arr_url[query_parsed][blogid]);
		$rw_sitekeyword = return_rewrite_keyword("", $siteid, "SITES", 0); // 0 added as rewrite site_id because the site keywords are global
		if ($rw_sitekeyword != "") {
			$rw_url .= $rw_sitekeyword."/".$rw_url_mode;
		} else {
			$rw_url .= $rw_url_mode;
		}		
		// $rw_url .= $rw_url_mode; // Add mode
		$rw_keyword = generate_rewrite_keyword("BLOGS", $arr_url[query_parsed][blogid], "TITLE", $arr_url[site]);
		if ($rw_keyword == "") {
			return $matches[1].$in_url.$matches[4];
		} else {
			$rw_url .= $rw_keyword."/";
		}
		unset($arr_url[query_parsed][blogid]);
		if (isset($arr_url[query_parsed][postid])) { // Blogpost
			$rw_keyword = generate_rewrite_keyword("BLOGPOSTS", $arr_url[query_parsed][postid], "HEADING", $arr_url[site]);
			if ($rw_keyword == "") {
				return $matches[1].$in_url.$matches[4];
			} else {
				$rw_url .= $rw_keyword."/";
			}
		}
		unset($arr_url[query_parsed][postid]);
	} elseif (isset($arr_url[query_parsed][group])) { // Productgroup
		$siteid = returnFieldValue("SHOP_PRODUCTGROUPS ", "SITE_ID", "ID", $arr_url[query_parsed][group]);
		$rw_sitekeyword = return_rewrite_keyword("", $siteid, "SITES", 0); // 0 added as rewrite site_id because the site keywords are global
		if ($rw_sitekeyword != "") {
			$rw_url .= $rw_sitekeyword."/".$rw_url_mode;
		} else {
			$rw_url .= $rw_url_mode;
		}		
		// $rw_url .= $rw_url_mode; // Add mode
		$rw_keyword = generate_rewrite_keyword("SHOP_PRODUCTGROUPS", $arr_url[query_parsed][group], "NAME", $arr_url[site]);
		if ($rw_keyword == "") {
			return $matches[1].$in_url.$matches[4];
		} else {
			$rw_url .= $rw_keyword."/";
		}
		unset($arr_url[query_parsed][group]);
		if (isset($arr_url[query_parsed][product])) { // Product
			// Obs! Products are called with product=[PRODUCT_NUMBER] not with ID. Thus the need for this:
			$product_id = returnFieldValue("SHOP_PRODUCTS ", "ID", "PRODUCT_NUMBER", $arr_url[query_parsed][product]);
			$rw_keyword = generate_rewrite_keyword("SHOP_PRODUCTS", $product_id, "NAME", $arr_url[site]);
			if ($rw_keyword == "") {
				return $matches[1].$in_url.$matches[4];
			} else {
				$rw_url .= $rw_keyword."/";
			}
		}
		unset($arr_url[query_parsed][product]);
		unset($arr_url[query_parsed][action]);
	} else {
		// Append rw_sitekeyword for current site if applicable
		$rw_sitekeyword = return_rewrite_keyword("", $arr_url[site], "SITES", 0); // 0 added as site-id because the site keywords are global
		if ($rw_sitekeyword != "") {
			$rw_url .= $rw_sitekeyword."/";
		}
		$rw_url .= $rw_url_mode; // Add mode
	}
	
	// Unset used parsed-query-parameters
	unset($arr_url[query_parsed][mode]);
	unset($arr_url[query_parsed][pageid]);
	unset($arr_url[query_parsed][newsid]);
	unset($arr_url[query_parsed][feedid]);
	unset($arr_url[query_parsed][eventid]);
	unset($arr_url[query_parsed][calendarid]);
	unset($arr_url[query_parsed][folderid]);
	unset($arr_url[query_parsed][siteid]);

	// Finally convert existing parsed-query-parameters to underscore/rewrite-method notation
	// If _any_ of the remaining parameters are NOT registered, return the original url
	if 	(count($arr_url[query_parsed]) > 0) {
		foreach ($arr_url[query_parsed] as $key => $value) {
			if ($str_method = return_rewrite_method($key, $arr_content[lang])) {
				if (!($str_method == "offset" && $value == "0")) {
					$rw_url .= $str_method."__".$value."/";
				}
			} else {
				// Return original url is method notregistered
//				echo "<br/>unregistered method: $key=$value";
				return $matches[1].$in_url.$matches[4];
			}
		}
	}
	if (isset($arr_url[fragment])) {
		$rw_url .= "#".$arr_url[fragment];
	}

//	echo "<br/>Returns: $rw_url";

// 	return $matches[1].$in_url.$matches[4]; // Original
	return $matches[1].$rw_url.$matches[4]; // Rewritten
}

function rewrite_urls_callback_pagekey_recursive($pageid, $siteid, $rw_url="") {
	$rw_keyword = generate_rewrite_keyword("PAGES", $pageid, "HEADING", $siteid);
	if ($rw_keyword == "") {
		$rw_url .= "/|||nokey|||";
	} else {
		$rw_url = "/".$rw_keyword.$rw_url;
	}
	// Get parent id
	$parent_id = returnFieldValue("PAGES", "PARENT_ID", "ID", $pageid);
	if ($parent_id == 0) {
		// Remove leading slash before returning
		$rw_url = substr($rw_url,1);
		return $rw_url;
	} else {
		return rewrite_urls_callback_pagekey_recursive($parent_id, $siteid, $rw_url);
	}
}


function get_language_from_url($str_url) {
	global $defaultLanguage;
	if ($arr_url = parse_url($str_url)) {
		$arr_http_host = explode(".", $arr_url[host]);
		if (count($arr_http_host) == 3) {
			$arr_url[subdomain] = $arr_http_host[0];
			$arr_url[domain] 	= $arr_http_host[1].".".$arr_http_host[2];
		} else {
			$arr_url[subdomain] = "";
			$arr_url[domain] 	= $arr_http_host[0].".".$arr_http_host[1];
		}
		return get_language_from_domain($arr_url[subdomain], $arr_url[domain]);
	} else {
		return $defaultLanguage;
	}
}

function get_language_from_domain($subdomain, $domain) {
	global $defaultLanguage;
	$sql = "select L.SHORTNAME from CMS_SITEDOMAINS CS, LANGUAGES L where L.ID = CS.LANGUAGE and CS.SUBDOMAIN = '$subdomain' and CS.DOMAIN = '$domain'";
	$res = mysql_query($sql);
	if (mysql_num_rows($res)>0) {
		return mysql_result($res,0);
	} else {
		// No domain-language found, use defaultLanguage!
		return $defaultLanguage;
	}
}

function set_current_language($language = ""){
	if ($language != "") {
		$_SESSION["CURRENT_LANGUAGE"] = $language;
	} elseif ($_GET[lang]) {
		$_SESSION["CURRENT_LANGUAGE"] = $_GET[lang];
	}
	if (!$_SESSION["CURRENT_LANGUAGE"]) {
		// Get language from CMS_SITEDOMAINS
		$arr_url = return_url_array();
		$_SESSION["CURRENT_LANGUAGE"] = get_language_from_domain($arr_url[subdomain], $arr_url[domain]);
	}
	return $_SESSION["CURRENT_LANGUAGE"];
}

function set_current_site(){
// 2007-04-13: Ændret fra global variabel til at trække på funktionen getdefaultsite(), som benytter CMS_DOMAINS tabellen
//	global $defaultSite;
	if (!$_SESSION["CURRENT_SITE"]) {
		// 2008-02-20: 	Der er forskel på hvordan current site bestemmes på frontend og backend. 
		//				Derfor checker vi om funktionen getdefaultsite eksisterer (så er vi frontend) eller ej (så er vi backend).
		if (function_exists("getdefaultsite")){
			$_SESSION["CURRENT_SITE"] = getdefaultsite();
		} else {
			return $_SESSION["SELECTED_SITE"];
		}
	}
	return $_SESSION["CURRENT_SITE"];
}

function convert_url_parameterstring($str) {
	// This function will convert a url parameter string to a 
	// lowercase, non-utf-8, sql-injection-safe version
	$str = db_safedata($str);
	if ($str == "safedata_error") {
		return $str;
	} else {
//		return html_entity_decode(strtolower(htmlentities(utf8_decode($str))));
		return html_entity_decode(strtolower(htmlentities($str)));
	}
}

function generate_rewrite_keyword($table_name, $content_id, $table_default_dbfield, $site_id) {
// 2007-04-16	-	Added site parameter to function to allow same keyword in multiple sites
		if (!$rw_keyword = return_rewrite_keyword("", $content_id, $table_name, $site_id)) {
			$page_heading = returnFieldValue($table_name, $table_default_dbfield, "ID", $content_id);
			$rw_keyword = return_rewrite_keyword($page_heading, $content_id, $table_name, $site_id);
			save_rewrite_keyword($rw_keyword, $content_id, $table_name, $site_id);
		}
		return $rw_keyword;
}

function return_rewrite_mode($value, $language) {
	$sql = "SELECT
				RM.NAME
			from 
				REWRITE_MODES RM, LANGUAGES L 
			where 
				RM.LANGUAGE_ID = L.ID and
				L.SHORTNAME = '$language' and
				RM.INTERNALNAME = '$value' 
			order by
				RM.ID asc
			LIMIT 1";
	$res = mysql_query($sql);
	if ($r = mysql_fetch_assoc($res)) {
		return $r[NAME];
	} else {
		return false;
	}
}

?>