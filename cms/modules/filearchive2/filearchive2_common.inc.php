<?php
function allow_folder_delete($folder_id) {
	/*
	Function edited to determine if user is allowed to delete a filearchive folder.
	Returns true is there are NO FILES and NO SUBFOLDERS in the folder - even if the subfolders do not contain files/subfolders!
	*/
	// 1. Check for files in the folder
	$images_in_folder = 1;
	$sql = "select count(*) from FILEARCHIVE_FILES where FOLDER_ID = '$folder_id'";
	if ($res = mysql_query($sql)) {
		if (mysql_result($res,0) == 0) {
			$images_in_folder = 0;
		}
	}
	
	// 2. Check for subfolders
	$subfolders_in_folder = 1;
	$sql = "select count(*) from FILEARCHIVE_FOLDERS where PARENT_ID = '$folder_id'";
	if ($res = mysql_query($sql)) {
		if (mysql_result($res,0) == 0) {
			$subfolders_in_folder = 0;
		}
	}
	
	// 3. Allow delete?
	if ($subfolders_in_folder == 1 || $images_in_folder == 1) {
		return false;
	} else {
		return true;
	}
}

function reverse_tree($id){
	$sql = "select ID, PARENT_ID from FILEARCHIVE_FOLDERS where ID='$id'";
	$res = mysql_query($sql);
	while ($row = mysql_fetch_assoc($res)){
		$_SESSION["CURRENT_OPEN_FOLDERS"][] = $row[ID];
		reverse_tree($row[PARENT_ID]);
	}
}

function folder_has_childs($folder_id){
	$sql_childs = "select ID from FILEARCHIVE_FOLDERS where PARENT_ID='$folder_id'";
	$res = mysql_query($sql_childs);
	if (mysql_num_rows($res) > 0){
		return true;
	}
	return false;
}

function new_folder_view($parent_id){
	global $akkumulated_foldermenu,$filearchive_Uploaddir;
 	if ($parent_id == 0 || array_search($parent_id, $_SESSION["CURRENT_OPEN_FOLDERS"])) {
		$sql = "
			select 
				ID, PARENT_ID, TITLE, FOLDERNAME 
			from 
				FILEARCHIVE_FOLDERS
			where
				PARENT_ID='$parent_id' and
				SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
			order by
				TITLE asc
		";
		$result = mysql_query($sql) or die(mysql_error());
		$akkumulated_foldermenu .= "<ul>";
		while ($row = mysql_fetch_array($result)) {
			if (($row["PARENT_ID"] == 0 || array_search($row[ID], $_SESSION["CURRENT_OPEN_FOLDERS"]) || array_search($row[PARENT_ID], $_SESSION["CURRENT_OPEN_FOLDERS"]))){
				if (check_data_permission("DATA_FILEARCHIVE2_MANAGEFOLDER", "FILEARCHIVE_FOLDERS", $row[ID], "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_SETDATAPERMISSIONS_FILEARCHIVE_FOLDERS")) {
					$hasChilds = folder_has_childs($row[ID]); 
					$akkumulated_foldermenu .= "<li>";
					if ($row[ID] == $_GET[folder_id]) {
						$class = " class='selected'";
					} else {
						$class = "";
					}	
					$akkumulated_foldermenu .= "<a title='$row[TITLE]' href='index.php?content_identifier=filearchive2&amp;folder_id=$row[ID]' $class>".$row[TITLE]."</a>";
					// $akkumulated_foldermenu .= " ($folder_size)";
					if ($hasChilds) {
						new_folder_view($row[ID]);
						$akkumulated_foldermenu .= "</li>";
					} else {
						$akkumulated_foldermenu .= "</li>";
					}
				} // End permission check
			}
			$last_parent_id = $row[PARENT_ID];
		}

		if (checkpermission("CMS_SETDATAPERMISSIONS_FILEARCHIVE_FOLDERS") || $last_parent_id > 0) {
			$akkumulated_foldermenu .= "<li><a class='newfolder' href='index.php?content_identifier=filearchive2&amp;dothis=opretnymappe&amp;parent_id=$last_parent_id'>Opret ny mappe her</a></li>";
			$akkumulated_foldermenu .= "</ul>";
		}
	}
 }

function new_file_view($folder_id){
	global $cmsDomain;
	if (check_data_permission("DATA_FILEARCHIVE2_USEINCMS", "FILEARCHIVE_FOLDERS", $folder_id, "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_SETDATAPERMISSIONS_FILEARCHIVE_FOLDERS")) {
		$sql = "
			select
				FF.*, FT.ICON_PATH as ICON
			from
				FILEARCHIVE_FILES FF, FILEARCHIVE_TYPE FT
			where
				FF.FOLDER_ID='$folder_id' and
				FF.FILETYPE_ID = FT.ID
			order by
				CREATED_DATE asc, ORIGINAL_FILENAME asc
		";
		$res = mysql_query($sql);
		$folderServerName = returnFieldValue("FILEARCHIVE_FOLDERS", "FOLDERNAME", "ID", $folder_id);
		$html .= "
			<div class='commandItem'>
				<a href='index.php?content_identifier=filearchive2&dothis=opretfil&folderid=$folder_id'><b>Upload ny fil i denne mappe</b></a>
				
				
				<span class='divider'>|</span>
				<a href='index.php?content_identifier=filearchive2&dothis=opretnymappe&amp;parent_id=$folder_id'>Opret undermappe her</a>
				".(allow_folder_delete($folder_id) ? "<span class='divider'>|</span>
				<a href='#' onclick='sletMappe($_GET[folder_id]); return false;'>Slet mappe</a>" : "<span class='divider'>|</span>
				<span style='color: #aaa'>Slet mappe</span>")."
				".(mysql_num_rows($res) > 0 ? "" : "")."
				<span class='divider'>| </span><a href='index.php?content_identifier=filearchive2&dothis=redigermappe&trin=1&folderid=$_GET[folder_id]'>Rediger mappe</a>
			</div>
		";
		while ($row = mysql_fetch_assoc($res)){
			$html .= "
				<div class='fileItem'>
					<img class='fileItemImg' src='$row[ICON]' alt='ikon' align='left' />
					Titel:
					$row[TITLE] <span class='filename'> <a class='filename' href='$cmsDomain/includes/uploaded_files/$row[FILENAME]'>$row[ORIGINAL_FILENAME]</a></span>
					<br/>
					$row[DESCRIPTION]
					<br/><a class='editDelete' href='index.php?content_identifier=filearchive2&dothis=editfil&folderid=$row[FOLDER_ID]&imageid=$row[ID]'>Rediger</a><span class='divider'>|</span><a class='editDelete' href='#' onclick='sletFil($row[ID], $row[FOLDER_ID]); return false;'>Slet</a><br/><hr />";
			$html .= "</div>
			";
		}
		return $html;
	} else {
		return "Du har ikke adgang til at se filer i denne mappe!";
	}
}

function select_folders($current_folder_id, $prefix_text, $parent_id=0, &$html, $level=0){
	$this_level = returnFieldValue("FILEARCHIVE_FOLDERS", "LEVEL", "ID", $current_folder_id);
	$this_thread_id = returnFieldValue("FILEARCHIVE_FOLDERS", "THREAD_ID", "ID", $current_folder_id);
	$sql = "
		select 
			ID, TITLE, THREAD_ID, LEVEL
		from 
			FILEARCHIVE_FOLDERS 
		where
			PARENT_ID='$parent_id' and
			SITE_ID in (0,'$_SESSION[SELECTED_SITE]') 
		order by 
			TITLE asc
	";
	$res = mysql_query($sql);
	while ($row = mysql_fetch_assoc($res)){
		if ($row[ID] != $current_folder_id && ($row[THREAD_ID] != $this_thread_id || ($row[THREAD_ID] == $this_thread_id && $row[LEVEL] < $this_level))){
			$html .= "<option value='$row[ID]'>";
			$html .= str_repeat("--", $level)."$prefix_text\"".$row[TITLE]."\"";
			$html .= "</option>";
		}
		if (folder_haschildren($row[ID])){
			select_folders($current_folder_id, $prefix_text,	 $row[ID], $html, $level+1);
		} else {
		}
	}
	return $html;
}

function folder_haschildren($folder_id){
	$sql = "select ID from FILEARCHIVE_FOLDERS where PARENT_ID='$folder_id'";
	$res = mysql_query($sql);
	if (mysql_num_rows($res) > 0){
		return true;
	}
	return false;
}  

function billedMappeListe($for='') {
	global $id, $tabel;
	if ($tabel == "") {
		$tabel = "PAGES";
	}
	$sql = "select 
				* 
			from 
				FILEARCHIVE_FOLDERS 
			where
				SITE_ID in (0,'$_SESSION[SELECTED_SITE]') 
			order by 
				TITLE asc";
	$result = mysql_query( $sql) or die(mysql_error());
	if (mysql_num_rows($result) == 0) return "<table class='oversigt'><tr><td>Der er ikke oprettet nogen mapper.</td></tr></table>";
	$html = "<table class='oversigt'>";
	while ($row = mysql_fetch_array($result)) {
		$sql_n = "select * from FILEARCHIVE_FILES where FOLDER_ID='" . $row["ID"] . "' and UNFINISHED='0' order by POSITION asc";
		$resultN = mysql_query( $sql_n);
		$numberOfPics = mysql_num_rows($resultN);

		if (check_data_permission("DATA_FILEARCHIVE2_USEINCMS", "FILEARCHIVE_FOLDERS", $row["ID"], "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_SETDATAPERMISSIONS_FILEARCHIVE_FOLDERS")) {
			$html .= "
			<tr>
				<td>";
					if ($for != "galleri") {
						if ($numberOfPics!=0) {
							$html .= 	"<input type='hidden' name='foldeknap_state_" . $row["ID"] . "' id='foldeknap_state_" . $row["ID"] . "' value='1' />
										<input type='button' name='foldeknap_" . $row["ID"] . "' id='foldeknap_" . $row["ID"] . "' class='plusminus' value='+' onclick='hideShowFolder(" . $row["ID"] . ")' />";
						} else {
							$html .= 	"<input type='button' class='plusminus' value=' ' disabled />";
						}
						$html .= "&nbsp;";
					}
					$html .= $row["TITLE"] . "</td>
				<td width='40%' align='right'>";
					if ($for == "galleri") {
						$html .= "<input type='button' value='Brug som galleri' class='lilletekst_knap' onclick='addGallery(\"$tabel\", $row[ID])'>";
					} else {
						$html .= 	"<input type='button' " . (allow_folder_delete($folder_id) ? "" : "disabled") . " value='Slet mappe' class='lilletekst_knap' onclick='sletMappe(" . $row["ID"] . ")'>
						<input type='button' value='Tilføj billede' class='lilletekst_knap' onclick='opretBillede(" . $row["ID"] . ")'>
						<input type='button' value='Omdøb' class='lilletekst_knap' onclick='redigerMappe(" . $row["ID"] . ")'>
						<input type='button' value='Rækkefølge' class='lilletekst_knap' onclick='location=\"index.php?content_identifier=filearchive2&folderid=".$row[ID]."&dothis=reorganize\"'>";
					}				
					$html .= "
				</td>
			</tr>";
		} // End permission check   
	if ($numberOfPics != 0) {
		$html .= "<tr id='picturecontainerRow_".$row["ID"]."' style='display:none;' class='picturecontainerRow'>
						<td colspan='2' id='picturecontainerCell_".$row["ID"]."'>HENTER BILLEDER...</td>
				</tr>";
	}
  }
  $html .= "</table>";
  return $html;
 }
 
 function gemBilledMappe($mappenavn, $userid="", $parentid="")
 {
  global $filearchive_Uploaddir;
	if ($parentid === "") {
		$parentid = $_POST[parent_id];
	}
	
	if ($userid == "") {
		$userid = $_SESSION[CMS_USER][USER_ID];
	}

  if ($_POST[imagepublicFolder]=="1") {
  	$is_public = 1;
  } else {
  	$is_public = 0;
  }

	if ($parentid == "0") {
		$site_id = $_SESSION[SELECTED_SITE];
	} else {
		// Inherit thumbmode and site-id from parent
		$site_id = returnFieldValue("FILEARCHIVE_FOLDERS", "SITE_ID", "ID", $parentid);
	}

  $nu = time();
  $foldername = $nu.str_makerand(4,4);
  $sql = "insert into FILEARCHIVE_FOLDERS 
   (TITLE, FOLDERNAME, CREATED_DATE, AUTHOR_ID, PRIVATE, PARENT_ID, PUBLIC_FOLDER, SITE_ID)
  values
   ('$mappenavn', '', '$nu', '" . $userid . "', '0', '$parentid', '$is_public', '$site_id');";
  $result = mysql_query( $sql) or die(mysql_error());
  $nyt_mappe_id = mysql_insert_id();

  return $nyt_mappe_id;
 }
 

function returnFolderImages($folder_id, $customfield_id="", $attribute_id="") {
	// 					Used in FCK editor plugin: customImage
	// 2007-04-18	-	Site-separation: Only return images/subfolders in allowed folders
	// 2007-05-09	-	Now also used for cms_pages module customfields when variables $customfield_id and $attribute_id are passed
	// 2007-05-29	-	Now checks for relevant datapermission and/or manager role (MAP)
	if (check_data_permission("DATA_FILEARCHIVE2_USEINCMS", "FILEARCHIVE_FOLDERS", $folder_id, "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_SETDATAPERMISSIONS_FILEARCHIVE_FOLDERS")) {
		global $filearchive_UploaddirRel;
		
		// Items per row
		$numberPerRow = 3;
	
		// First get subfolders
		$sql = "select 
						* 
					from 
						FILEARCHIVE_FOLDERS 
					where 
						PARENT_ID = '$folder_id' and
						SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
					order by 
						TITLE asc";
		$folder_result = mysql_query($sql) or die(mysql_error());
		$numberOfFolders = mysql_num_rows($folder_result);
	
		// Get parent of current folder
		$parentfolder_id = returnFieldValue("FILEARCHIVE_FOLDERS", "PARENT_ID", "ID", $folder_id);
	
		// Get pics
		$sql = "select 
						PP.* 
					from 
						FILEARCHIVE_FILES PP,
						FILEARCHIVE_FOLDERS PF
					where 
						PF.ID = PP.FOLDER_ID and
						PP.FOLDER_ID='$folder_id' and 
						PP.UNFINISHED='0' and
						PF.SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
					order by 
						PP.POSITION asc";
		$result = mysql_query( $sql);
		$numberOfPics = mysql_num_rows($result);
	
		$html = "<table class='oversigt'>";
	
		$i = 1;
		$html .= "\n\t<tr class='file_row' id='parent".$folder_id."_billede$i'>";
	
		$baseurl = returnBASE_URL($_SESSION[SELECTED_SITE]).returnSITE_PATH($_SESSION[SELECTED_SITE]);
		
		// Output up-folder
		if ($parentfolder_id > 0) {
			if ($i == 1) {
				$html .= "\n\t<tr class='file_row' id='parent_$folder_id'>";
			}
			if ($customfield_id == "") {
				$html .= "\n\t\t<td id='imageFolder_".$parentfolder_id."' align='center' onclick='folderClicked(this);' >";
			} else {
				$html .= "\n\t\t<td id='imageFolder_".$parentfolder_id."' align='center' onclick='customfield_folderClicked($customfield_id, $attribute_id, $parentfolder_id);' >";
			}
			$html .= "\n\t\t\t<img src='$baseurl/cms/images/image_folder.png' alt='Niveau op' width='50' height='49 border='1' /><br />";
			$html .= "\n\t\t\t<strong>";
			$html .= "Niveau op";
			$html .= "</strong>";
			$html .= "\n\t\t</td>";
			$modula = $i%$numberPerRow;
			if ($modula == 0) {
				$html .= "\n\t</tr><tr><td colspan='$numberPerRow'><hr></td>\n\t</tr>\n\t<tr class='file_row' id='parent".$folder_id."_billede$i'>";
			}
			$i++;
		}
	
		while ($folder_row =  mysql_fetch_array($folder_result)) {
	
			// Output subfolders
			if ($i == 1) {
				$html .= "\n\t<tr class='file_row' id='parent_$folder_id'>";
			}
			if ($customfield_id == "") {
				$html .= "\n\t\t<td id='imageFolder_".$folder_row[ID]."' align='center' onclick='folderClicked(this);' >";
			} else {
				$html .= "\n\t\t<td id='imageFolder_".$folder_row[ID]."' align='center' onclick='customfield_folderClicked($customfield_id, $attribute_id, $folder_row[ID]);' >";
			}
			$html .= "\n\t\t\t<img src='$baseurl/cms/images/image_folder.png' alt='".$folder_row[TITLE]."' width='50' height='49 border='1' /><br />";
			$html .= "\n\t\t\t<strong>";
			if ($folder_row["TITLE"] != "") {
				$html .= $folder_row["TITLE"];
			} else {
				$html .= $folder_row["FOLDERNAME"];
			}
			$html .= "</strong>";
			$html .= "\n\t\t</td>";
			$modula = $i%$numberPerRow;
			if ($modula == 0) {
				$html .= "\n\t</tr><tr><td colspan='$numberPerRow'><hr></td>\n\t</tr>\n\t<tr class='file_row' id='parent".$folder_id."_billede$i'>";
			}
			$i++;
		}
		// Output images
		while ($row =  mysql_fetch_array($result)) {
			if ($i == 1) {
				$html .= "\n\t<tr class='file_row' id='parent_$folder_id'>";
			}
			if ($customfield_id == "") {
				$html .= "\n\t\t<td id='billede_".$row[ID]."' align='center' onclick='imageClicked(this);' >";
			} else {
				$html .= "\n\t\t<td id='billede_".$row[ID]."' align='center' onclick='customfield_imageClicked(this, $customfield_id, $attribute_id, $row[ID]);' >";
			}
			$html .= "\n\t\t\t<img src='" .$filearchive_UploaddirRel."/".returnFolderName($folder_id,1, "FILEARCHIVE_FOLDERS")."/thumbs/" . $row["FILENAME"] . "' alt='".$row[ALTTEXT]."' owidth='".$row[SIZE_X]."' oheight='".$row[SIZE_Y]."' border='1' /><br />";
			$html .= "\n\t\t\t<strong>";
			if ($row["ALTTEXT"] != "") {
				$html .= $row["ALTTEXT"];
			} else {
	//			$html .= $row["ORIGINAL_FILENAME"];
				$html .= "&nbsp;";
			}
			$html .= "</strong>";
			$html .= "<br />";
	//		$html .= "\n\t\t\t".returnNiceDateTime($row["CREATED_DATE"], 1) . "<br />";
	//		$html .= "\n\t\t\t".returnAuthorName($row["AUTHOR_ID"], 1) . "<br />";
			$html .= "\n\t\t\t<input type='radio' class='imageRadio' name='imageGroup' id='image".$row[ID]."' />\n\t\t</td>";
			$modula = $i%$numberPerRow;
			if ($modula == 0) {
				$html .= "\n\t</tr><tr><td colspan='$numberPerRow'><hr></td>\n\t</tr>\n\t<tr class='file_row' id='parent".$folder_id."_billede$i'>";
			}
			$i++;
		}
		// Fyld op med td'er hvis antallet af billeder ikke går op!
		if ($modula != 0) {
			for ($index=0 ; $index <= $modula; ++$index) {
				$html .= "\n\t\t<td>\n\t\t</td>";
			}
		}
	
		$html .= "\n\t</tr>\n</table>\n";
	//	$html .= "</form>";
		return $html;
	} else {
		return "Du har ikke lov til at se billeder i denne mappe";
	} // End permission check
}

function returnImageFolders($customfield_id="", $attribute_id="") {
	# Used from bookmaker select coverimage
	# and from cms_pages module if customfield_id and attribute_id are passed
	$sql = "select 
					* 
				from 
					FILEARCHIVE_FOLDERS 
				where 
					PARENT_ID = 0 and
					SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
				order by 
					TITLE asc";
	$result = mysql_query( $sql) or die(mysql_error());
	if (mysql_num_rows($result) == 0) {
		return "Der er ikke oprettet nogen billedmapper.";
	}
	$shown_folders = 0;
	while ($row = mysql_fetch_array($result)) {
		if (check_data_permission("DATA_FILEARCHIVE2_USEINCMS", "FILEARCHIVE_FOLDERS", $row["ID"], "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_SETDATAPERMISSIONS_FILEARCHIVE_FOLDERS")) {
			$shown_folders++;
			if ($attribute_id != "") {
				$html .= "<p class='imageFolder' id='customfield_".$customfield_id."_attribute_".$attribute_id."_imageFolder_$row[ID]' onmouseover='highlight(this);' onmouseout='highlight_off(this);' onclick='customfield_folderClicked($customfield_id, $attribute_id, $row[ID]);'>$row[TITLE]</p>";
			} else {
				$html .= "<p class='imageFolder' id='imageFolder_$row[ID]' onmouseover='highlight(this);' onmouseout='highlight_off(this);' onclick='folderClicked(this);'>$row[TITLE]</p>";
			}
		} // End permission check
	}
	if ($shown_folders == 0) {
		return "Du har ikke adgang til nogen billedmapper!";
	} else {
		return $html;
	}
}

function update_thread_ids($table_name, $id_column, $parent_id_column, $thread_id_column, $level_column, $deleted_column, $unfinished_column, $parent_id=0, $level=0){
	$sql = "
		select 
			$id_column, $parent_id_column".($thread_id_column ? " ,$thread_id_column " : "")."
		from 
			$table_name
		where 
			$parent_id_column='$parent_id'".($deleted_column ? " and $deleted_column='0'" : "")."
			".($unfinished_column ? " and $unfinished_column='0'" : "")."
		order by
			$id_column ASC
	";
	$res = mysql_query($sql);
	while ($row = mysql_fetch_assoc($res)){
		if ($row[$parent_id_column] == 0){
			$thread_id = $row[$id_column];
		} else {
			$thread_id = returnFieldValue($table_name, $thread_id_column, $id_column, $row[$parent_id_column]);
		}
		if ($thread_id == 0 || !$thread_id){
			$thread_id = get_correct_thread_id($row[$id_column], $table_name, $id_column, $parent_id_column);
		}
		$sql = "
			update 
				$table_name 
			set 
				$thread_id_column='$thread_id'".($level_column ? ", $level_column='".($level + 1)."' " : "")."
			where 
				$id_column='$row[$id_column]'
			limit 1;
		";
		mysql_query($sql);
		update_thread_ids($table_name, $id_column, $parent_id_column, $thread_id_column, $level_column, $deleted_column, $unfinished_column, $row[$id_column], $level+1);
	}
}

function get_correct_thread_id($id, $table_name, $id_column, $parent_id_column){
	$sql = "select $id_column, $parent_id_column from $table_name where $id_column='$id'";
	$res = mysql_query($sql);
	$row = mysql_fetch_assoc($res);
	if ($row[$parent_id_column] != 0){
		return get_correct_thread_id($row[$parent_id_column], $table_name, $id_column, $parent_id_column);
	} else {
		return $row[$id_column];
	}
}

function sletBilledMappe($id)
{
/*
2008-10-06	-	Files no longer stored in folders, so need to physically delete 
 global $filearchive_Uploaddir;
 $mappe = returnFolderName($id, 1, "FILEARCHIVE_FOLDERS");
 if (file_exists("$filearchive_Uploaddir/$mappe/thumbs")) recursive_remove_directory('$filearchive_Uploaddir/$mappe/thumbs');
 if (file_exists("$filearchive_Uploaddir/$mappe")) recursive_remove_directory('$filearchive_Uploaddir/$mappe/thumbs');
*/
 $sql = "delete from FILEARCHIVE_FOLDERS where ID='$id'";
 $result = mysql_query( $sql) or die(mysql_error()); 
}

?>
<?php
function filMappeListe() {
	global $fileUploaddirAbs;
	$sql = "select 
				* 
			from 
				FILEARCHIVE_FOLDERS 
			where
				SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
			order by THREAD_ID asc, TITLE asc";
	$result = mysql_query( $sql) or die(mysql_error());
	if (mysql_num_rows($result) == 0) return array($html = "<table class='oversigt'><tr><td>Der er ikke oprettet nogen mapper.</td></tr></table>", "");
	$html = "<table class='oversigt'>";
	if ($_GET[selectfile] == 1) {
		$html .= "<tr><td class='kolonnetitel' colspan='5'>Vælg den fil, du ønsker at vedhæfte</td></tr>";
	}
	while ($row = mysql_fetch_array($result)) {
		$sql_n = "select * from FILEARCHIVE_FILES F, FILEARCHIVE_FOLDERS FF where FF.ID = F.FOLDER_ID and F.FOLDER_ID='" . $row["ID"] . "' and FF.SITE_ID in (0,'$_SESSION[SELECTED_SITE]')";
		$resultN = mysql_query( $sql_n);
		$numberOfFiles = mysql_num_rows($resultN);
		$html .= "
		   <tr>";
		   	if ($_GET[selectfile]) {
		   		$html .= "<td colspan='5'>";
			} else {
		   		$html .= "<td>";
			}
			$html .= ($numberOfFiles!=0 ? " 
			 <input type='hidden' id='foldeknap_state_" . $row["ID"] . "' name='foldeknap_state_" . $row["ID"] . "' value='1' />
			 <input type='button' id='foldeknap_" . $row["ID"] . "' class='plusminus' value='-' onclick='hideShowFolder(" . $row["ID"] . ", -1)' />" : "<input type='button' class='plusminus' value=' ' disabled />") . "
			 &nbsp;
			" . returnFolderName_recursive($row["ID"], 2, "FILEARCHIVE_FOLDERS")  . "
			</td>";

		   	if (!$_GET[selectfile]) {
				$html .= "<td colspan='4' align='right'><input type='button' value='Tilføj fil' class='lilletekst_knap' onclick='opretFil($row[ID])'>
			 <input type='button' " . ($numberOfFiles!=0 ? "disabled" : "") . " value='Slet mappe' class='lilletekst_knap' onclick='sletMappe(" . $row["ID"] . ")'></td>";
			 }
		   $html .="</tr>";   
		if ($numberOfFiles != 0) $script .= "hideShowFolder(" . $row["ID"] . ", -1);\n";
		$sql = "select * from FILEARCHIVE_FILES where FOLDER_ID='" . $row["ID"] . "' order by ORIGINAL_FILENAME asc";
		$result2 = mysql_query( $sql) or die(mysql_error());
		while ($prow =  mysql_fetch_array($result2)) {
			$i++;
			if ($_GET[content_identifier] == "filearchive2"){
				$onclick_handler = "attachFile(".$prow[ID].", $_GET[returntoid], \"$_GET[returntotabel]\")";
			} else {
				$onclick_handler = "attachFile(".$prow[ID].")";
			}
			$html .= "
				<tr class='file_row' id='parent" . $row["ID"] . "_fil$i'>
				 <td align='left'>" . $prow["TITLE"] . "</td>	
				 <td align='left'><a href='/includes/download.php?id=$prow[ID]'>".$prow["ORIGINAL_FILENAME"]."</a>
				 <!--<a href='$fileUploaddirAbs/";
			$html .= returnFolderName($prow["FOLDER_ID"], 1, "FILEARCHIVE_FOLDERS");
			$html .= "/".$prow["FILENAME"]."' target='_blank' alt='Download filen'>" . $prow["ORIGINAL_FILENAME"] . "</a>--></td>
				 <td align='left'>" . returnNiceDateTime($prow["CREATED_DATE"],1) . "</td>
				 <td align='left'>" . returnAuthorName($prow["AUTHOR_ID"], 1) . "</td>
				 <td align='left'>
				 <!--<input type='button' class='lilleknap' value='Rediger' onclick=''>-->
				  " . ($_GET[selectfile] == 1 ? "<input type='button' class='lilleknap' value='Vedhæft denne' onclick='$onclick_handler'>" : "") . "
				  " . (!$_GET[selectfile]     ? "<input type='button' class='lilleknap' value='Slet' onclick='sletFil(" . $prow["ID"] . "," . $row["ID"] . ")'>" : "") . "
				 </td>
				</tr>";
		}
	$i=0;
	}
	$html .= "</table>";
	return array("$html", "$script");
}

function gemFilMappe($mappenavn, $userid)
 {
  global $fileUploaddir;
  $nu = $foldername = time();
  $sql = "
  insert into FILEARCHIVE_FOLDERS 
   (TITLE, FOLDERNAME, CREATED_DATE, AUTHOR_ID, PRIVATE, SITE_ID)
  values
   ('$mappenavn', '$foldername', '$nu', '" . $_SESSION["CMS_USER"]["USER_ID"] . "', '0', '$_SESSION[SELECTED_SITE]');";
  $result = mysql_query( $sql) or die(mysql_error());
  $nyt_mappe_id = mysql_insert_id();
  if (!mkdir("$fileUploaddir/$foldername", 0777)) {
   echo "Der skete en fejl under mappeoprettelsen";
   exit;
  }
  else
  {
   chmod("$fileUploaddir/$foldername", 0777);
  }
  return $nyt_mappe_id;
 }
 
 function return_file_type($str_filename){
	$filetype = extension($str_filename);
	switch($filetype) {
		case "docx":
			$filetype = ".doc";
			break;
		case "xlsx":
			$filetype = ".xls";
			break;
		case "xls":
			$filetype = ".xls";
			break;
		case "pdf":
			$filetype = ".pdf";
			break;
		case "pptx":
			$filetype = ".ppt";
			break;
		case "ppt":
			$filetype = ".ppt";
			break;
		case "pps":
			$filetype = ".ppt";
			break;
		case "fla":
			$filetype = ".fla";
			break;
		case "swf":
			$filetype = ".fla";
			break;
		case "asf":
			$filetype = "generic_video";
			break;
		case "rm":
			$filetype = "generic_video";
			break;
		case "mpg":
			$filetype = "generic_video";
			break;
		case "mpeg":
			$filetype = "generic_video";
			break;
		case "mpeg2":
			$filetype = "generic_video";
			break;
		case "avi":
			$filetype = "generic_video";
			break;
		case "mp4":
			$filetype = "generic_video";
			break;
		case "mov":
			$filetype = "generic_quicktime";
			break;
		case "qt":
			$filetype = "generic_quicktime";
			break;
		case "mp3":
			$filetype = "generic_sound";
			break;
		case "wav":
			$filetype = "generic_sound";
			break;
		case "aif":
			$filetype = "generic_sound";
			break;
		case "mid":
			$filetype = "generic_sound";
			break;
		case "midi":
			$filetype = "generic_sound";
			break;
		case "mpa":
			$filetype = "generic_sound";
			break;
		case "wma":
			$filetype = "generic_sound";
			break;
		case "gif":
			$filetype = "gif_bmp";
			break;
		case "bmp":
			$filetype = "gif_bmp";
			break;
		case "jpg":
			$filetype = "jpg";
			break;
		case "jpeg":
			$filetype = "jpg";
			break;
		case "mng":
			$filetype = "generic_image";
			break;
		case "tif":
			$filetype = "tif";
			break;
		case "tiff":
			$filetype = "tif";
			break;
		case "zip":
			$filetype = ".zip";
			break;
		case "gz":
			$filetype = ".zip";
			break;
		case "gzip":
			$filetype = ".zip";
			break;
	}
	
	$sql = "select
				ID
			from
				FILEARCHIVE_TYPE
			where 
				INTERNAL_NAME = '$filetype'";
				
	$res = mysql_query($sql);
	while($row = mysql_fetch_assoc($res)){
		$fileid = $row[ID];
	}
	
	if($fileid == ""){
		$fileid = 21;
	}
	
	return $fileid;
	
 }
 
 function uploadFil($felt, $folder_id, $title, $description)
 {
 
  global $fileUploaddir; 
  $temp_filename = $_FILES["userfile"]["tmp_name"];
  $original_filename = $_FILES["userfile"]["name"];
  $mimetype = $_FILES["userfile"]["type"];
  $new_filename = time();
  $dest_filename = "$fileUploaddir/$new_filename";
  if (!move_uploaded_file($temp_filename, $dest_filename)) {
   echo "Fejl i overførsel";
   exit;
  }
  $extension = extension($original_filename);
//  $filetype = return_file_type($_FILES);
  $filetype = return_file_type($original_filename);
  $filesize = filesize("$fileUploaddir/$new_filename");
  if ($filesize <= 0) {
   return -1;
  }
  $new_filename_2 = $new_filename . "." . $extension;
  $nu = time();
  rename("$fileUploaddir/$dest_folder/$new_filename", "$fileUploaddir/$new_filename_2");
  $sql = "insert into FILEARCHIVE_FILES 
   (FOLDER_ID, FILENAME, ORIGINAL_FILENAME, TITLE, DESCRIPTION, AUTHOR_ID, CREATED_DATE, EXTENSION, MIMETYPE, FILETYPE_ID)
  values
   ('$folder_id', '$new_filename_2', '$original_filename', '$title', '$description', '" . $_SESSION["CMS_USER"]["USER_ID"] . "', '$nu', '$extension', '$mimetype', '$filetype')"; 
  $result = mysql_query( $sql) or die(mysql_error());
  return mysql_insert_id();
 }
 
 function extension($filename)
 {
  $r = strrev($filename);
  $p = strpos($r, ".");
  return substr($filename, -1*$p, $p);
 }
 
 function sletFil($id)
 {
  global $fileUploaddir;
  $sql = "select * from FILEARCHIVE_FILES F, FILEARCHIVE_FOLDERS FF where F.FOLDER_ID = FF.ID and F.ID='$id' and FF.SITE_ID in (0,'$_SESSION[SELECTED_SITE]')";
  $result = mysql_query( $sql) or die(mysql_error());
  if (mysql_num_rows($result) > 0) {
	  $row = mysql_fetch_array($result);
	  $f1 = "$fileUploaddir/" . $row["FILENAME"];
	  if (file_exists($f1)) unlink($f1);
	  $sql = "delete from FILEARCHIVE_FILES where ID='$id'";
	  $result = mysql_query( $sql) or die(mysql_error()); 
	  $sql = "delete from ATTACHMENTS where FILE_ID='$id'";
	  $result = mysql_query( $sql) or die(mysql_error()); 
  } 
 }

function sletFilMappe($id)
{
 global $fileUploaddir;
 $mappe = returnFolderName($id, 1, "FILEARCHIVE_FOLDERS");
 if (file_exists("$fileUploaddir/$mappe")) rmdir("$fileUploaddir/$mappe");
 $sql = "delete from FILEARCHIVE_FOLDERS2 where ID='$id' and SITE_ID in (0,'$_SESSION[SELECTED_SITE]')";
 $result = mysql_query( $sql) or die(mysql_error()); 

}
?>
