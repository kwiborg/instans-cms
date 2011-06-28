<?php 
if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_PICTUREARCHIVE", true);

unset($_SESSION["CURRENT_OPEN_FOLDERS"]);
$_SESSION["CURRENT_OPEN_FOLDERS"][] = "__START";
reverse_tree($_GET[folder_id]);

// require_once ("picturearchive.common.inc.php");
if ($dothis == "dropbox_import") {
	include($cmsAbsoluteServerPath."/modules/picturearchive/dropbox_import.php");
	header("location: index.php?content_identifier=picturearchive");
}

if ($dothis == "gem_mappe") {
	if ($_POST[mode] == "edit") {
		$sql = "update PICTUREARCHIVE_FOLDERS set TITLE = '$_POST[mappenavn]', PUBLIC_FOLDER='$_POST[public_folder]', FOLDER_DESCRIPTION='$_POST[folder_description]', THUMBMODE='$_POST[thumbmode]' ".($_POST[new_parent_id] != "DO_NOT_MOVE" ? ", PARENT_ID='$_POST[new_parent_id]' " : "")." where ID = '$_POST[folderid]'";
		if (mysql_query($sql)) {
			update_thread_ids("PICTUREARCHIVE_FOLDERS", "ID", "PARENT_ID", "THREAD_ID", "LEVEL", "", "", 0, 0);
			header("location: index.php?content_identifier=picturearchive&dothis=oversigt&folder_id=$_POST[folderid]&mainmenuoff=$mmo");
		}
	} else {
		$nyt_mappe_id = gemBilledMappe($mappenavn, $_SESSION["CMS_USER"]["USER_ID"]);
		header("location: index.php?content_identifier=picturearchive&dothis=oversigt&folder_id=$nyt_mappe_id&mainmenuoff=$mmo");
		update_thread_ids("PICTUREARCHIVE_FOLDERS", "ID", "PARENT_ID", "THREAD_ID", "LEVEL", "", "", 0, 0);
		exit;
	}
}
 
if ($dothis == "upload_billede") {
	if (check_data_permission("DATA_PICTUREARCHIVE_MANAGEFOLDER", "PICTUREARCHIVE_FOLDERS", $folderid, "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_SETDATAPERMISSIONS_PICTUREARCHIVE_FOLDERS")) {
		if ($trin == 1) {  
			$imageid = uploadBillede("billede", $folderid);
			if ($imageid == -1) {
				header("location: index.php?content_identifier=picturearchive&dothis=opretbillede&trin=1&folderid=$folderid&error=1&mainmenuoff=$mmo");
				exit;
			} else {
				header("location: index.php?content_identifier=picturearchive&dothis=opretbillede&trin=2&folderid=$folderid&imageid=$imageid&mainmenuoff=$mmo");
				exit;
			}
		}
		if ($trin == 2) {
			// Archive original
			if ($_POST[imageKeepOriginal] == 1) {
				$foldername = returnFolderName($folderid, 1, "PICTUREARCHIVE_FOLDERS");
				$filename = returnFileName($imageid, 1, "PICTUREARCHIVE_PICS");
				$image = "$picturearchive_Uploaddir/$foldername/$filename";
				$dest_file_original = "$picturearchive_Uploaddir/$foldername/originals/$filename";
				if (!copy($image,$dest_file_original)) {
					return false;
				}
				$sql = "update PICTUREARCHIVE_PICS set ORIGINAL_ARCHIVED = 1 where ID = '$imageid'";
				mysql_query($sql);
				chmod($dest_file_original, 0755);
			}
	
			resizeBillede($folderid, $imageid, $imagewidth, $imageheight, $billedtype, $quality, $description, $alttext);
			generateThumb($folderid, $imageid);
	
		   header("location: ?content_identifier=picturearchive&dothis=oversigt&folder_id=$folderid&imageid=$imageid");
		   exit;
		}
	} else {
		echo "Du har ikke lov til at tilføje billeder til denne mappe";
		exit;
	} // End check permission
}

if ($dothis == "sletbillede" && $_GET[imageid] && $_GET[folder_id]) {
	if (check_data_permission("DATA_PICTUREARCHIVE_MANAGEFOLDER", "PICTUREARCHIVE_FOLDERS", $_GET[folder_id], "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_SETDATAPERMISSIONS_PICTUREARCHIVE_FOLDERS")) {
		sletBillede($_GET[imageid]);
		header("location: index.php?content_identifier=picturearchive&folder_id=$_GET[folder_id]");
		exit;
	} else {
		echo "Ikke adgang til at slette billeder i denne mappe!";
		exit;
	}
// 2007-03-21 - Advarsel ved anvendt billede udkoblet indtil funktionen bliver udbygget til at håndtere alle mulige brugssituationer (MAP)
/*
	unset($_SESSION[IMAGEUSING_IDS]);
	if (!$_GET[ignorealert]) {
		$p = returnFieldValue("PICTUREARCHIVE_PICS", "FILENAME", "ID", $imageid);
		$sql = "select NEWS.ID, EVENTS.ID, PAGES.ID from NEWS, EVENTS, PAGES where (NEWS.CONTENT like '%$p%') or (EVENTS.CONTENT like '%$p%') or (PAGES.CONTENT like '%$p%')";
		$result = mysql_db_query($dbname, $sql);
		if (mysql_num_rows($result) == 0) {
			sletBillede($imageid);
		} else {
			while ($row = mysql_fetch_array($result)) {
				if ($row[ID])  $_SESSION[IMAGEUSING_IDS][] = $row[ID];
			}
			header("location: index.php?content_identifier=picturearchive&dothis=advarsel&folder_id=".$_GET[folder_id]."&imageid=".$_GET[imageid]);
			exit;
		}
	} else {
		sletBillede($_GET[imageid]);
	}
	header("location: index.php?content_identifier=picturearchive&folder_id=$_GET[folder_id]");
	exit;
*/
}

if ($dothis == "sletmappe" && $folderid) {
	if (check_data_permission("DATA_PICTUREARCHIVE_MANAGEFOLDER", "PICTUREARCHIVE_FOLDERS", $folder_id, "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_SETDATAPERMISSIONS_PICTUREARCHIVE_FOLDERS")) {
		// Get parent of current folder
		$parentfolder_id = returnFieldValue("PICTUREARCHIVE_FOLDERS", "PARENT_ID", "ID", $folder_id);
	
		// Delete folder
		sletBilledMappe($folderid);
		header("location: index.php?content_identifier=picturearchive&folder_id=$parentfolder_id");
		exit;
	} else {
		echo "Du har ikke lov til at slette billedmappen!";
		exit;
	}
}
 
if ($dothis == "gem_editbillede" && $imageid && $folderid) {
	if (check_data_permission("DATA_PICTUREARCHIVE_MANAGEFOLDER", "PICTUREARCHIVE_FOLDERS", $folder_id, "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_SETDATAPERMISSIONS_PICTUREARCHIVE_FOLDERS")) {
		$sql = "update PICTUREARCHIVE_PICS set DESCRIPTION='$description', ALTTEXT='$alttext' where ID='$imageid'";
		$result = mysql_db_query($dbname, $sql);
		header("location: ?content_identifier=picturearchive&dothis=oversigt&folder_id=$folderid");
		exit;
	} else {
		echo "Du har ikke lov til gemme ændringer på billedet!";
		exit;
	}
}
 
 if ($dothis == "billedop" && $imageid && $folderid) {
  $sql = "select POSITION from PICTUREARCHIVE_PICS where ID=$imageid and FOLDER_ID=$folderid";
  $result = mysql_db_query($dbname, $sql);
  $row = mysql_fetch_row($result);
  $oldpos = $row[0];
  if ($oldpos > 1) {
   $sql = "update PICTUREARCHIVE_PICS set POSITION=POSITION+1 where POSITION=$oldpos-1 and FOLDER_ID=$folderid";
   $result = mysql_db_query($dbname, $sql);  
   $sql = "update PICTUREARCHIVE_PICS set POSITION=POSITION-1 where ID=$imageid and FOLDER_ID=$folderid";
   $result = mysql_db_query($dbname, $sql);  
  }
  header("location: index.php?content_identifier=picturearchive&dothis=oversigt&folderid=$folderid");
  exit;
 } 

 if ($dothis == "billedned" && $imageid && $folderid) {
  $sql = "select POSITION from PICTUREARCHIVE_PICS where FOLDER_ID=$folderid order by POSITION desc limit 1";
  $result = mysql_db_query($dbname, $sql);
  $row = mysql_fetch_row($result);
  $maxpos = $row[0];
  $sql = "select POSITION from PICTUREARCHIVE_PICS where ID=$imageid and FOLDER_ID=$folderid";
  $result = mysql_db_query($dbname, $sql);
  $row = mysql_fetch_row($result);
  $oldpos = $row[0];
  if ($oldpos < $maxpos) {
   $sql = "update PICTUREARCHIVE_PICS set POSITION=POSITION-1 where POSITION=$oldpos+1 and FOLDER_ID=$folderid";
   $result = mysql_db_query($dbname, $sql);  
   $sql = "update PICTUREARCHIVE_PICS set POSITION=POSITION+1 where ID=$imageid and FOLDER_ID=$folderid";
   $result = mysql_db_query($dbname, $sql);  
  }
  header("location: index.php?content_identifier=picturearchive&dothis=oversigt&folderid=$folderid");
  exit;
 }  

/*
 if ($dothis == "addgallery" && $pageid && $folderid && $tabel)
 {
  $sql = "insert into GALLERIES (PAGE_ID, FOLDER_ID, TABEL) values ($pageid, $folderid, '$tabel')";
  $result = mysql_db_query($dbname, $sql);
  header("location: index.php?content_identifier=pages&dothis=oversigt&menuid=$menuid");
  exit;
 } 
 
*/

 if ($dothis == "saveorder"){
	$temp = explode("&", $_POST["order"]);
	foreach ($temp as $line){
		eval("$".$line.";");
	}
	foreach ($sortThis as $imgId){
		$pos++;
		$sql = "update PICTUREARCHIVE_PICS set POSITION='$pos' where ID='$imgId'";
		mysql_query($sql);
	}
	$folderid = returnFieldValue("PICTUREARCHIVE_PICS", "FOLDER_ID", "ID", $imgId);
	header("location: index.php?content_identifier=picturearchive&folder_id=$folderid");
 }
   
?>