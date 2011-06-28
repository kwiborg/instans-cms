<?php
function allow_folder_delete($folder_id) {
	/*
	2007-03-20: Function to determine if user is allowed to delete a picturearchive folder.
	Returns true is there are NO IMAGES and NO SUBFOLDERS in the folder - even if the subfolders do not contain images/subfolders!
	*/
	// 1. Check for images in the folder
	$images_in_folder = 1;
	$sql = "select count(*) from PICTUREARCHIVE_PICS where FOLDER_ID = '$folder_id'";
	if ($res = mysql_query($sql)) {
		if (mysql_result($res,0) == 0) {
			$images_in_folder = 0;
		}
	}
	
	// 2. Check for subfolders
	$subfolders_in_folder = 1;
	$sql = "select count(*) from PICTUREARCHIVE_FOLDERS where PARENT_ID = '$folder_id'";
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
	$sql = "select ID, PARENT_ID from PICTUREARCHIVE_FOLDERS where ID='$id'";
	$res = mysql_query($sql);
	while ($row = mysql_fetch_assoc($res)){
		$_SESSION["CURRENT_OPEN_FOLDERS"][] = $row[ID];
		reverse_tree($row[PARENT_ID]);
	}
}

function folder_has_childs($folder_id){
	$sql_childs = "select ID from PICTUREARCHIVE_FOLDERS where PARENT_ID='$folder_id'";
	$res = mysql_query($sql_childs);
	if (mysql_num_rows($res) > 0){
		return true;
	}
	return false;
}

function new_folder_view($parent_id){
	global $akkumulated_foldermenu,$picturearchive_Uploaddir;
 	if ($parent_id == 0 || array_search($parent_id, $_SESSION["CURRENT_OPEN_FOLDERS"])) {
		$sql = "
			select 
				ID, PARENT_ID, TITLE, FOLDERNAME 
			from 
				PICTUREARCHIVE_FOLDERS
			where
				PARENT_ID='$parent_id' and
				SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
			order by
				TITLE asc
		";
		$result = mysql_query($sql) or die(mysql_error());
		$akkumulated_foldermenu .= "<ul>";
		while ($row = mysql_fetch_array($result)) {
//			$folder_size = dirsize("$picturearchive_Uploaddir/$row[FOLDERNAME]");
			if (($row["PARENT_ID"] == 0 || array_search($row[ID], $_SESSION["CURRENT_OPEN_FOLDERS"]) || array_search($row[PARENT_ID], $_SESSION["CURRENT_OPEN_FOLDERS"]))){
				if (check_data_permission("DATA_PICTUREARCHIVE_MANAGEFOLDER", "PICTUREARCHIVE_FOLDERS", $row[ID], "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_SETDATAPERMISSIONS_PICTUREARCHIVE_FOLDERS")) {
					$hasChilds = folder_has_childs($row[ID]); 
					$akkumulated_foldermenu .= "<li>";
					if ($row[ID] == $_GET[folder_id]) {
						$class = " class='selected'";
					} else {
						$class = "";
					}	
					$akkumulated_foldermenu .= "<a title='$row[TITLE]' href='index.php?content_identifier=picturearchive&amp;folder_id=$row[ID]' $class>".$row[TITLE]."</a>";
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

		if (checkpermission("CMS_SETDATAPERMISSIONS_PICTUREARCHIVE_FOLDERS") || $last_parent_id > 0) {
			$akkumulated_foldermenu .= "<li><a class='newfolder' href='index.php?content_identifier=picturearchive&amp;dothis=opretnymappe&amp;parent_id=$last_parent_id'>Opret ny mappe her</a></li>";
			$akkumulated_foldermenu .= "</ul>";
		}
	}
 }

function new_file_view($folder_id){
	if (check_data_permission("DATA_PICTUREARCHIVE_USEINCMS", "PICTUREARCHIVE_FOLDERS", $folder_id, "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_SETDATAPERMISSIONS_PICTUREARCHIVE_FOLDERS")) {
		$sql = "
			select
				*
			from
				PICTUREARCHIVE_PICS
			where
				FOLDER_ID='$folder_id' and 
				UNFINISHED = 0
			order by
				POSITION asc, ORIGINAL_FILENAME asc
		";
		$res = mysql_query($sql);
		$folderServerName = returnFieldValue("PICTUREARCHIVE_FOLDERS", "FOLDERNAME", "ID", $folder_id);
		$html .= "
			<div class='commandItem'>
				<a href='index.php?content_identifier=picturearchive&dothis=opretbillede&trin=1&folderid=$folder_id'>Upload nyt billede i denne mappe</a>
				<span class='divider'>|</span>
				<a href='index.php?content_identifier=picturearchive&dothis=opretnymappe&amp;parent_id=$folder_id'>Opret undermappe her</a>
				".(allow_folder_delete($folder_id) ? "<span class='divider'>|</span>
				<a href='#' onclick='sletMappe($_GET[folder_id]); return false;'>Slet denne mappe</a>" : "")."
				".(mysql_num_rows($res) > 0 ? "<span class='divider'>|</span>
				<a href='index.php?content_identifier=picturearchive&folderid=$_GET[folder_id]&dothis=reorganize'>Re-organiser</a>" : "")."
				<span class='divider'>|</span><a href='index.php?content_identifier=picturearchive&dothis=redigermappe&trin=1&folderid=$_GET[folder_id]'>Indstillinger</a>
			</div>
		";
		while ($row = mysql_fetch_assoc($res)){
			$html .= "
				<div class='imageItem'>
					<a class='editDelete' href='index.php?content_identifier=picturearchive&dothis=editbillede&folderid=$row[FOLDER_ID]&imageid=$row[ID]'><img src='/includes/uploaded_pictures/$folderServerName/thumbs/$row[FILENAME]' class='imageThumb' alt='$row[DESCRIPTION]' border='0' /></a><br/>
					".(trim($row[ALTTEXT]) ? "$row[ALTTEXT]<br/>" : "")."
					<span class='filename'>$row[ORIGINAL_FILENAME]</span>
					<br/><a class='editDelete' href='index.php?content_identifier=picturearchive&dothis=editbillede&folderid=$row[FOLDER_ID]&imageid=$row[ID]'>Rediger</a><br/><a class='editDelete' href='#' onclick='sletBillede($row[ID], $row[FOLDER_ID]); return false;'>Slet</a><br/>
	";
			if ($row[ORIGINAL_ARCHIVED] == 1) {
				$html .= "<a class='editDelete' target='_blank' href='/includes/uploaded_pictures/$folderServerName/originals/$row[FILENAME]'>Vis original i nyt vindue</a><br/>";
			}		
			$html .= "</div>
			";
		}
		return $html;
	} else {
		return "Du har ikke adgang til at se billeder i denne mappe!";
	}
}

function select_folders($current_folder_id, $prefix_text, $parent_id=0, &$html, $level=0){
	$this_level = returnFieldValue("PICTUREARCHIVE_FOLDERS", "LEVEL", "ID", $current_folder_id);
	$this_thread_id = returnFieldValue("PICTUREARCHIVE_FOLDERS", "THREAD_ID", "ID", $current_folder_id);
	$sql = "
		select 
			ID, TITLE, THREAD_ID, LEVEL
		from 
			PICTUREARCHIVE_FOLDERS 
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
	$sql = "select ID from PICTUREARCHIVE_FOLDERS where PARENT_ID='$folder_id'";
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
				PICTUREARCHIVE_FOLDERS 
			where
				SITE_ID in (0,'$_SESSION[SELECTED_SITE]') 
			order by 
				TITLE asc";
	$result = mysql_query( $sql) or die(mysql_error());
	if (mysql_num_rows($result) == 0) return "<table class='oversigt'><tr><td>Der er ikke oprettet nogen mapper.</td></tr></table>";
	$html = "<table class='oversigt'>";
	while ($row = mysql_fetch_array($result)) {
		$sql_n = "select * from PICTUREARCHIVE_PICS where FOLDER_ID='" . $row["ID"] . "' and UNFINISHED='0' order by POSITION asc";
		$resultN = mysql_query( $sql_n);
		$numberOfPics = mysql_num_rows($resultN);

		if (check_data_permission("DATA_PICTUREARCHIVE_USEINCMS", "PICTUREARCHIVE_FOLDERS", $row["ID"], "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_SETDATAPERMISSIONS_PICTUREARCHIVE_FOLDERS")) {
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
						<input type='button' value='Rækkefølge' class='lilletekst_knap' onclick='location=\"index.php?content_identifier=picturearchive&folderid=".$row[ID]."&dothis=reorganize\"'>";
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
  global $picturearchive_Uploaddir;
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
		$thumbmode = "NEWEST";
		$site_id = $_SESSION[SELECTED_SITE];
	} else {
		// Inherit thumbmode and site-id from parent
		$thumbmode = returnFieldValue("PICTUREARCHIVE_FOLDERS", "THUMBMODE", "ID", $parentid);
		$site_id = returnFieldValue("PICTUREARCHIVE_FOLDERS", "SITE_ID", "ID", $parentid);
	}

  $nu = time();
  $foldername = $nu.str_makerand(4,4);
  $sql = "insert into PICTUREARCHIVE_FOLDERS 
   (TITLE, FOLDERNAME, CREATED_DATE, AUTHOR_ID, PRIVATE, PARENT_ID, PUBLIC_FOLDER, THUMBMODE, SITE_ID)
  values
   ('$mappenavn', '$foldername', '$nu', '" . $userid . "', '0', '$parentid', '$is_public', '$thumbmode', '$site_id');";
  $result = mysql_query( $sql) or die(mysql_error());
  $nyt_mappe_id = mysql_insert_id();
  if (!mkdir("$picturearchive_Uploaddir/$foldername", 0777) || !mkdir("$picturearchive_Uploaddir/$foldername/thumbs", 0777) || !mkdir("$picturearchive_Uploaddir/$foldername/originals", 0777)) {
	$usermessage_error = "Der skete en fejl under mappeoprettelsen";
	header("Location: index.php?content_identifier=picturearchive&&usermessage_error=$usermessage_error");
	exit;
  }
  else
  {
   chmod("$picturearchive_Uploaddir/$foldername", 0777);
   chmod("$picturearchive_Uploaddir/$foldername/thumbs", 0777);
  }
  return $nyt_mappe_id;
 }
 
 function uploadBillede($felt, $folder_id)
 {
  global $picturearchive_Uploaddir; 
  $temp_filename = $_FILES["billede"]["tmp_name"];
  $original_filename = $_FILES["billede"]["name"];
  $new_filename = time().str_makerand(4,4);
  $dest_folder = returnFolderName($folder_id, 1, "PICTUREARCHIVE_FOLDERS");
  $dest_filename = "$picturearchive_Uploaddir/$dest_folder/$new_filename";
  if (!move_uploaded_file($temp_filename, $dest_filename)) {
	$usermessage_error = "Fejl i overførsel af billede!";
	header("Location: index.php?content_identifier=picturearchive&&usermessage_error=$usermessage_error");
	exit;
  }
  chmod("$picturearchive_Uploaddir/$dest_folder/$new_filename", 0755);
  $info = getimagesize("$picturearchive_Uploaddir/$dest_folder/$new_filename");
 
  switch ($imagetype = $info[2]) {
   case 1: $extension = "gif"; break;
   case 2: $extension = "jpg"; break;	
   case 3: $extension = "png"; break;
   default: $extension = "not_allowed"; break;
  }
  if ($extension == "not_allowed") {
   return $errorcode = "-1";
  }
  $new_filename_2 = $new_filename . "." . $extension;
  $nu = time();
  rename("$picturearchive_Uploaddir/$dest_folder/$new_filename", "$picturearchive_Uploaddir/$dest_folder/$new_filename_2");
  $sql = "insert into PICTUREARCHIVE_PICS 
   (FOLDER_ID, FILENAME, ORIGINAL_FILENAME, AUTHOR_ID, UNFINISHED, CREATED_DATE, IMAGETYPE, SIZE_X, SIZE_Y)
  values
   ('$folder_id', '$new_filename_2', '$original_filename', '" . $_SESSION["CMS_USER"]["USER_ID"] . "', '1', '$nu', '$imagetype', '$info[0]', '$info[1]')"; 
  mysql_query( $sql) or die(mysql_error());
  $new_id = mysql_insert_id();
  $sql = "select max(POSITION) as MAXPOS from PICTUREARCHIVE_PICS where FOLDER_ID='$folder_id'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_array($result);
  $sql = "update PICTUREARCHIVE_PICS set POSITION='" . ($row[0]+1) . "' where ID='$new_id'";
  mysql_query( $sql) or die(mysql_error());
  return $new_id;
 }

function generateThumb($folder_id, $image_id) {
	global $picturearchive_Uploaddir, $imageThumbSize;

	$thumbSize = $imageThumbSize;
	
	$foldername = returnFolderName($folder_id, 1, "PICTUREARCHIVE_FOLDERS");
	$filename   = returnFileName($image_id, 1, "PICTUREARCHIVE_PICS");
	$pic = "$picturearchive_Uploaddir/$foldername/$filename";
	if (@!$info = getimagesize($pic)) {
		return false;
	}
	$type = $info[2];
	$p = $info[0]/$info[1];
	if ($p >= 1) {
		$thumbwidth  = (($info[0]*0.1 < $thumbSize) ? $thumbSize : $thumbSize);
 		$thumbheight = round($thumbwidth/$p);
	}
	if ($p < 1) {
 		$thumbheight  = (($info[1]*0.1 < $thumbSize) ? $thumbSize : $thumbSize);
		$thumbwidth = round($thumbheight*$p);
	}
	if ($type == 1) {
		if (array_search("imagegif", get_extension_funcs("gd"))) { // Checks if gdlib supports gif (above version 2.0.28)
			if (@!$new_image_placeholder = imagecreate($thumbwidth, $thumbheight)) {
				return false;
			}
			if (@!$new_image = imagecreatefromgif($pic)) {
				return false;
			}
			if (@!imagecopyresized($new_image_placeholder, $new_image, 0, 0, 0, 0, $thumbwidth, $thumbheight, $info[0], $info[1])) {
				return false;
			}
			$thumbfile = "$picturearchive_Uploaddir/$foldername/thumbs/$filename";
			if (@!imagegif($new_image_placeholder, $thumbfile)) {
				return false;
			}
		} else {
			if (@!copy("$picturearchive_Uploaddir/$foldername/$filename", "$picturearchive_Uploaddir/$foldername/thumbs/$filename")) {
				return false;
			}
		}
	}
	if ($type == 2) {
		if (@!$new_image_placeholder = imagecreatetruecolor($thumbwidth, $thumbheight)) {
			return false;
		}
		if (@!$new_image = imagecreatefromjpeg($pic)) {
			return false;
		}
		if (@!imagecopyresampled($new_image_placeholder, $new_image, 0, 0, 0, 0, $thumbwidth, $thumbheight, $info[0], $info[1])) {
			return false;
		}
		$thumbfile = "$picturearchive_Uploaddir/$foldername/thumbs/$filename";
		if (@!imagejpeg($new_image_placeholder, $thumbfile, 100)) {
			return false;
		}
	} 
	if ($type == 3) {
		$new_image_placeholder = imagecreatetruecolor($thumbwidth, $thumbheight);
		if (!$new_image_placeholder) {
			return false;
		}
		$new_image = imagecreatefrompng($pic);
		if (!$new_image) {
			return false;
		}
		if (!imagecopyresampled($new_image_placeholder, $new_image, 0, 0, 0, 0, $thumbwidth, $thumbheight, $info[0], $info[1])) {
			return false;
		}
		$thumbfile = "$picturearchive_Uploaddir/$foldername/thumbs/$filename";
		if (!imagepng($new_image_placeholder, $thumbfile)) {
			return false;
		}
	} 
	return true;
}


 
function resizeBillede($folder_id, $image_id, $imagewidth, $imageheight, $type, $quality, $description, $alttext) {
 global $picturearchive_Uploaddir;
 $foldername = returnFolderName($folder_id, 1, "PICTUREARCHIVE_FOLDERS");
 $filename   = returnFileName($image_id, 1, "PICTUREARCHIVE_PICS");
 $pic = "$picturearchive_Uploaddir/$foldername/$filename";
 if (!$info = getimagesize($pic)) {
 	return false;
 }
 // Resizet original
 if ($type == 1) {
 }
 if ($type == 2) {
  if ($imagewidth != $info[0] || $imageheight != $info[1]) {
   if (@!$new_image_placeholder = imagecreatetruecolor($imagewidth, $imageheight)) {
   	return false;
   }
   if (@!$new_image = imagecreatefromjpeg($pic)) {
   	return false;
   }
   if (@!imagecopyresampled($new_image_placeholder, $new_image, 0, 0, 0, 0, $imagewidth, $imageheight, $info[0], $info[1])) {
   	return false;
   }
   if (@!imagejpeg($new_image_placeholder, $pic, $quality)) {
   	return false;
   }
  }
 }
 if ($type == 3) {
  if ($imagewidth != $info[0] || $imageheight != $info[1]) {
   if (@!$new_image_placeholder = imagecreatetruecolor($imagewidth, $imageheight)) {
   	return false;
   }
   if (@!$new_image = imagecreatefrompng($pic)) {
   	return false;
   }
   if (@!imagecopyresampled($new_image_placeholder, $new_image, 0, 0, 0, 0, $imagewidth, $imageheight, $info[0], $info[1])) {
   	return false;
   }
   if (@!imagepng($new_image_placeholder, $pic)) {
   	return false;
   }
  }
 }

 // Thumbnail
 // generateThumb($foldername, $filename);
 $sql = "SELECT max(POSITION) from PICTUREARCHIVE_PICS where FOLDER_ID='$folder_id'";
 $result = mysql_query( $sql) or die(mysql_error());
 $row = mysql_fetch_array($result);
 $newmaxpos = $row[0] + 1;
 $sql = "update PICTUREARCHIVE_PICS set UNFINISHED='0', QUALITY='$quality', DESCRIPTION='$description', ALTTEXT='$alttext'";
 if ($type > 1) {
	$sql .= ", SIZE_X='$imagewidth', SIZE_Y='$imageheight'";
 }
 $sql .= ", POSITION='$newmaxpos' where ID='$image_id'";
 $result = mysql_query( $sql) or die(mysql_error());	
 return true;
}

/*
2007-05-14	-	Flyttet til common.inc.php
function billederRydOp()
{
 global $picturearchive_Uploaddir;
 $sql = "select * from PICTUREARCHIVE_PICS where UNFINISHED='1' and AUTHOR_ID = '".$_SESSION[CMS_USER][USER_ID]."'";
 $result = mysql_query( $sql) or die(mysql_error());
 while ($row = mysql_fetch_array($result)) {
  $f1 = "$picturearchive_Uploaddir/" . returnFolderName($row["FOLDER_ID"], 1, "PICTUREARCHIVE_FOLDERS") . "/" . $row["FILENAME"];
  $f2 = "$picturearchive_Uploaddir/" . returnFolderName($row["FOLDER_ID"], 1, "PICTUREARCHIVE_FOLDERS") . "/thumbs/" . $row["FILENAME"];
  $f3 = "$picturearchive_Uploaddir/" . returnFolderName($row["FOLDER_ID"], 1, "PICTUREARCHIVE_FOLDERS") . "/originals/" . $row["FILENAME"];
  if (file_exists($f1)) unlink($f1);
  if (file_exists($f2)) unlink($f2);
  if (file_exists($f3)) unlink($f3);
 }
 $sql = "delete from PICTUREARCHIVE_PICS where UNFINISHED='1'";
 $result = mysql_query( $sql) or die(mysql_error());
}
*/

function sletBillede($id)
{
 global $picturearchive_Uploaddir;
 $sql = "select * from PICTUREARCHIVE_PICS where ID='$id'";
 $result = mysql_query( $sql) or die(mysql_error());
 $row = mysql_fetch_array($result);
 $f1 = "$picturearchive_Uploaddir/" . returnFolderName($row["FOLDER_ID"], 1, "PICTUREARCHIVE_FOLDERS") . "/" . $row["FILENAME"];
 $f2 = "$picturearchive_Uploaddir/" . returnFolderName($row["FOLDER_ID"], 1, "PICTUREARCHIVE_FOLDERS") . "/thumbs/" . $row["FILENAME"];
 $f3 = "$picturearchive_Uploaddir/" . returnFolderName($row["FOLDER_ID"], 1, "PICTUREARCHIVE_FOLDERS") . "/originals/" . $row["FILENAME"];
 if (file_exists($f1)) unlink($f1);
 if (file_exists($f2)) unlink($f2);
 if (file_exists($f3)) unlink($f3);
 $sql = "delete from PICTUREARCHIVE_PICS where ID='$id'";
 $result = mysql_query( $sql) or die(mysql_error()); 
 $sql = "update PICTUREARCHIVE_PICS set POSITION = (POSITION - 1) where POSITION > $row[POSITION] and FOLDER_ID=$row[FOLDER_ID]"; 
 $result = mysql_query( $sql) or die(mysql_error()); 
}

function sletBilledMappe($id)
{
 global $picturearchive_Uploaddir;
 $mappe = returnFolderName($id, 1, "PICTUREARCHIVE_FOLDERS");
 if (file_exists("$picturearchive_Uploaddir/$mappe/thumbs")) recursive_remove_directory('$picturearchive_Uploaddir/$mappe/thumbs');
 if (file_exists("$picturearchive_Uploaddir/$mappe")) recursive_remove_directory('$picturearchive_Uploaddir/$mappe/thumbs');
 $sql = "delete from PICTUREARCHIVE_FOLDERS where ID='$id'";
 $result = mysql_query( $sql) or die(mysql_error()); 
}

function checkIfImageIsUsed($filename) {
 global $dbname;
 $sql = "select PAGES.ID, NEWS.ID, EVENTS.ID from PAGES, NEWS, EVENTS where CONTENT like '%$filename%'";
 $result = mysql_query( $sql);
 while ($row = mysql_fetch_row($result)) {
  print_r($row);
 } 
}

/*
AJAX RESPONDER FUNCTIONS
*/
function removeGallery($page_id, $tabel, $folder_id='') {
	$sql = "delete from GALLERIES where PAGE_ID='$page_id' and TABEL='$tabel'";
	if ($folder_id != "") {
		$sql .= " and FOLDER_ID='$folder_id' ";
	}
	$result = mysql_query($sql);
	if (!$result = mysql_query($sql)) {
		echo "Der opstod en fejl og galleriet blev ikke fjernet fra siden.";
	}
}

function addGallery($id, $folderid, $tabel) {
	// Delete existing gallery attachments
	removeGallery($id, $tabel);

	// Add new gallery attachment
	$sql = "insert into GALLERIES (PAGE_ID, FOLDER_ID, TABEL) values ($id, $folderid, '$tabel')";
	if ($result = mysql_query($sql)) {
		echo "ok";
	} else {
		echo "Der opstod en fejl og galleriet blev ikke tilføjet siden.";
	}
} 

function returnAvailablePicturefolders() {
	# Used from Page editing
	$html = billedMappeListe("galleri");
	return $html;
}

function returnAttachedGallery($page_id) {
	# Used from Page editing
	$sql = "select PICTUREARCHIVE_FOLDERS.ID, PICTUREARCHIVE_FOLDERS.TITLE 
				from PICTUREARCHIVE_FOLDERS, GALLERIES 
				where PICTUREARCHIVE_FOLDERS.ID = GALLERIES.FOLDER_ID 
				and GALLERIES.PAGE_ID = '$page_id'";
	$result = mysql_query($sql);
	$n = mysql_num_rows($result);
	if ($n > 0) {
		$has_gallery = "yes";
	} else {
		$has_gallery = "no";
	}		
	$html = "<input type='hidden' id='page_has_gallery' value='$has_gallery' />";

	if ($n > 0) {
		$html .="<table class='oversigt'>
					<tr>
					<td class='kolonnetitel'>Mappe der bruges som galleri</td>
					<td class='kolonnetitel'>Funktioner</td>
				</tr>";
		while ($row = mysql_fetch_array($result)) {
			$i++;
			$c = $i % 2 + 1;
			$html .= "<tr>
						<td>".$row[TITLE]."</td>
						<td width='15%'><input type='button' class='lilleknap' value='Fjern' onclick='removeGallery(".$row[ID].")'>
						</td>
					</tr>";
		}
	} else {
		$html .= "<table class='oversigt'>
				<tr>
					<td>Der er ikke vedhæftet noget galleri til siden.</td>
				</tr>";
	}
	$html .= "</table>";
	return $html;
}

function picturearchive_returnImages($folder_id) {
	// Used from picturearchive.ajaxresponders.php
	// NOTE: VINTAGE FUNCTION - PREVIOUSLY USED IN PICTUREARCHIVE WHEN THIS WAS AJAX BASED!!!
	// 2007-04-18	-	Implement check to ensure that only pictures in allowed folders can be showed
	// 2007-05-29	-	Implemented check for datapermission DATA_PICTUREARCHIVE_USEINCMS
	if (check_data_permission("DATA_PICTUREARCHIVE_USEINCMS", "PICTUREARCHIVE_FOLDERS", $folder_id, "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_SETDATAPERMISSIONS_PICTUREARCHIVE_FOLDERS")) {
		global $picturearchive_UploaddirAbs;
		$sql = "select 
						PP.* 
					from 
						PICTUREARCHIVE_PICS PP,
						PICTUREARCHIVE_FOLDERS PF
					where 
						PF.ID = PP.FOLDER_ID and
						PP.FOLDER_ID='" . $folder_id . "' and 
						PP.UNFINISHED='0' and
						PF.SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
					order by 
						PP.POSITION asc, PP.ORIGINAL_FILENAME asc";
		$result2 = mysql_query($sql) or die(mysql_error());
		$html = "<table class='oversigt' style='margin:0;'>";
		$html .= "<tr>
		<td class='kolonnetitel' width='60'>Billede</td>
		<td class='kolonnetitel'>Titel / ALT-tekst</td>
		<td class='kolonnetitel'>Org. filnavn</td>
		<td class='kolonnetitel'>Arkivdato</td>
		<td class='kolonnetitel'>Tilføjet af</td>
		<td class='kolonnetitel'>Funktioner</td>
		</tr>
		";
		while ($prow =  mysql_fetch_array($result2)) {
			$html .= "
			\n\t<tr id='parent" . $folder_id . "_billede$i'>
				\n\t\t<td><a style='border:0;' href='index.php?content_identifier=picturearchive&dothis=editbillede&folderid=$folder_id&imageid=$prow[ID]'><img border='0' src='" . $picturearchive_UploaddirAbs."/" . returnFolderName($folder_id,1, "PICTUREARCHIVE_FOLDERS") . "/thumbs/" . $prow["FILENAME"] . "'></a></td>	
				\n\t\t<td>" . $prow["ALTTEXT"] . "</td>
				\n\t\t<td>" . $prow["ORIGINAL_FILENAME"] . "</td>
				\n\t\t<td>" . returnNiceDateTime($prow["CREATED_DATE"], 1) . "</td>
				\n\t\t<td>" . returnAuthorName($prow["AUTHOR_ID"], 1) . "</td>
				\n\t\t<td align='right' width='15%'>
					<input type='button' class='lilleknap' value='Rediger' onclick='location=\"index.php?content_identifier=picturearchive&dothis=editbillede&folderid=$folder_id&imageid=$prow[ID]\"'>
					<input type='button' class='lilleknap' value='Slet' onclick='sletBillede(" . $prow["ID"] . "," . $folder_id . ")'>
				\n\t\t</td>
			\n\t</tr>";
	   }
		$html .= "<tr><td colspan='5'></td><td align='right'><input type='button' class='lilleknap' value='Tilføj billede' onclick='opretBillede($folder_id);'></td></tr>";
		$html .= "\n</table>";
		return $html;
	} else {
		return "Du har ikke lov til at se billeder i denne mappe";
	} // End permission check
}

function returnFolderImages($folder_id, $customfield_id="", $attribute_id="") {
	// 					Used in FCK editor plugin: customImage
	// 2007-04-18	-	Site-separation: Only return images/subfolders in allowed folders
	// 2007-05-09	-	Now also used for cms_pages module customfields when variables $customfield_id and $attribute_id are passed
	// 2007-05-29	-	Now checks for relevant datapermission and/or manager role (MAP)
	if (check_data_permission("DATA_PICTUREARCHIVE_USEINCMS", "PICTUREARCHIVE_FOLDERS", $folder_id, "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_SETDATAPERMISSIONS_PICTUREARCHIVE_FOLDERS")) {
		global $picturearchive_UploaddirRel;
		
		// Items per row
		$numberPerRow = 3;
	
		// First get subfolders
		$sql = "select 
						* 
					from 
						PICTUREARCHIVE_FOLDERS 
					where 
						PARENT_ID = '$folder_id' and
						SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
					order by 
						TITLE asc";
		$folder_result = mysql_query($sql) or die(mysql_error());
		$numberOfFolders = mysql_num_rows($folder_result);
	
		// Get parent of current folder
		$parentfolder_id = returnFieldValue("PICTUREARCHIVE_FOLDERS", "PARENT_ID", "ID", $folder_id);
	
		// Get pics
		$sql = "select 
						PP.* 
					from 
						PICTUREARCHIVE_PICS PP,
						PICTUREARCHIVE_FOLDERS PF
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
		$html .= "\n\t<tr class='picture_row' id='parent".$folder_id."_billede$i'>";
	
		$baseurl = returnBASE_URL($_SESSION[SELECTED_SITE]).returnSITE_PATH($_SESSION[SELECTED_SITE]);
		
		// Output up-folder
		if ($parentfolder_id > 0) {
			if ($i == 1) {
				$html .= "\n\t<tr class='picture_row' id='parent_$folder_id'>";
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
				$html .= "\n\t</tr><tr><td colspan='$numberPerRow'><hr></td>\n\t</tr>\n\t<tr class='picture_row' id='parent".$folder_id."_billede$i'>";
			}
			$i++;
		}
	
		while ($folder_row =  mysql_fetch_array($folder_result)) {
	
			// Output subfolders
			if ($i == 1) {
				$html .= "\n\t<tr class='picture_row' id='parent_$folder_id'>";
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
				$html .= "\n\t</tr><tr><td colspan='$numberPerRow'><hr></td>\n\t</tr>\n\t<tr class='picture_row' id='parent".$folder_id."_billede$i'>";
			}
			$i++;
		}
		// Output images
		while ($row =  mysql_fetch_array($result)) {
			if ($i == 1) {
				$html .= "\n\t<tr class='picture_row' id='parent_$folder_id'>";
			}
			if ($customfield_id == "") {
				$html .= "\n\t\t<td id='billede_".$row[ID]."' align='center' onclick='imageClicked(this);' >";
			} else {
				$html .= "\n\t\t<td id='billede_".$row[ID]."' align='center' onclick='customfield_imageClicked(this, $customfield_id, $attribute_id, $row[ID]);' >";
			}
			$html .= "\n\t\t\t<img src='" .$picturearchive_UploaddirRel."/".returnFolderName($folder_id,1, "PICTUREARCHIVE_FOLDERS")."/thumbs/" . $row["FILENAME"] . "' alt='".$row[ALTTEXT]."' owidth='".$row[SIZE_X]."' oheight='".$row[SIZE_Y]."' border='1' /><br />";
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
				$html .= "\n\t</tr><tr><td colspan='$numberPerRow'><hr></td>\n\t</tr>\n\t<tr class='picture_row' id='parent".$folder_id."_billede$i'>";
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
					PICTUREARCHIVE_FOLDERS 
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
		if (check_data_permission("DATA_PICTUREARCHIVE_USEINCMS", "PICTUREARCHIVE_FOLDERS", $row["ID"], "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_SETDATAPERMISSIONS_PICTUREARCHIVE_FOLDERS")) {
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

?>