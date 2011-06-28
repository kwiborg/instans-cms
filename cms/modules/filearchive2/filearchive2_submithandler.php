<?php 

if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_FILEARCHIVE", true);

unset($_SESSION["CURRENT_OPEN_FOLDERS"]);
$_SESSION["CURRENT_OPEN_FOLDERS"][] = "__START";
reverse_tree($_GET[folder_id]);

// require_once ("filearchive2.common.inc.php");

if ($dothis == "gem_mappe") {
	if ($_POST[mode] == "edit") {
		$sql = "update FILEARCHIVE_FOLDERS set TITLE = '$_POST[mappenavn]', PUBLIC_FOLDER='1', FOLDER_DESCRIPTION='$_POST[folder_description]' ".($_POST[new_parent_id] != "DO_NOT_MOVE" ? ", PARENT_ID='$_POST[new_parent_id]' " : "")." where ID = '$_POST[folderid]'";
		if (mysql_query($sql)) {
			update_thread_ids("FILEARCHIVE_FOLDERS", "ID", "PARENT_ID", "THREAD_ID", "LEVEL", "", "", 0, 0);
			header("location: index.php?content_identifier=filearchive2&dothis=oversigt&folder_id=$_POST[folderid]&mainmenuoff=$mmo");
			exit;
		}
	} else {
		$nyt_mappe_id = gemBilledMappe($mappenavn, $_SESSION["CMS_USER"]["USER_ID"]);
		update_thread_ids("FILEARCHIVE_FOLDERS", "ID", "PARENT_ID", "THREAD_ID", "LEVEL", "", "", 0, 0);
		header("location: index.php?content_identifier=filearchive2&dothis=oversigt&folder_id=$nyt_mappe_id&mainmenuoff=$mmo");
		exit;
	}
}

if ($dothis == "sletmappe" && $folderid) {
	if (check_data_permission("DATA_FILEARCHIVE2_MANAGEFOLDER", "FILEARCHIVE_FOLDERS", $folderid, "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_SETDATAPERMISSIONS_FILEARCHIVE_FOLDERS")) {
		// Get parent of current folder
		$parentfolder_id = returnFieldValue("FILEARCHIVE_FOLDERS", "PARENT_ID", "ID", $folderid);
	
		// Delete folder
		sletBilledMappe($folderid);
		header("location: index.php?content_identifier=filearchive2&folder_id=$parentfolder_id");
		exit;
	} else {
		echo "Du har ikke lov til at slette filmappen!";
		exit;
	}
}
   
?>
<?php 
 if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_FILEARCHIVE", true);
 if ($dothis == "gem_mappe")
 {
  $nyt_mappe_id = gemFilMappe($mappenavn, $_SESSION["CMS_USER"]["USER_ID"]);
  header("location: index.php?content_identifier=filearchive2&dothis=oversigt&newfolderid=$nyt_mappe_id");
  exit;
 }
 
 if ($dothis == "upload_fil")
 {
  $fileid = uploadFil("userfile", $folderid, $title, $description);
  if ($fileid == -1) {
   header("location: index.php?content_identifier=filearchive2&dothis=opretfil&folderid=$folderid&error=1");
   exit;
  }
  else {
  	header("location: index.php?content_identifier=filearchive2&folder_id=$folderid");
   exit;
  }
  
 }
 
 if ($dothis == "gem_editfil" && $_POST[editedfiles] && $folderid) {

	
 	// RET VAR HERUNDER!!!
	if (check_data_permission("DATA_FILEARCHIVE_MANAGEFOLDER", "FILEARCHIVE_FOLDERS", $folderid, "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_SETDATAPERMISSIONS_FILEARCHIVE_FOLDERS")) {
		
		$arr_postedfiles = explode("__", $_POST["editedfiles"]);
		
		foreach($arr_postedfiles as $key => $value){
			$fileid = $value;
			$description = $_POST['description_'.$value];
			$alttext = $_POST['alttext_'.$value];
			$type = $_POST['type_'.$value];	

			$sql = "update FILEARCHIVE_FILES set DESCRIPTION='$description', TITLE='$alttext', FILETYPE_ID='$type' where ID='$fileid'";
			$result = mysql_query($sql);
		
		}

		header("location: index.php?content_identifier=filearchive2&dothis=oversigt&folder_id=$folderid");
		exit;
	} else {
		echo "Du har ikke lov til gemme ændringer på filen!";
		exit;
	}
}
 
 if ($dothis == "sletfil" && $fileid && $folderid) {
  sletFil($fileid, $folderid);
	header("location: index.php?content_identifier=filearchive2&folder_id=$folderid");

 
	exit;
 }

 if ($dothis == "sletmappe" && $folderid) {
  sletFilMappe($folderid);
  header("location: index.php?content_identifier=filearchive2&dothis=oversigt&folderid=$folderid&mainmenuoff=$mainmenuoff");
  exit;
 }
 
 if ($dothis=="oldschool_addfile"){
 	$sql = "insert into ATTACHMENTS (PAGE_ID, FILE_ID, TABEL) values ('$_GET[content_id]', '$_GET[file_id]', '$_GET[tablename]')";
	mysql_query($sql);
	header("location: index.php?content_identifier=attachments&menuid=undefined&id=$_GET[content_id]&tabel=$_GET[tablename]");
	exit;
 }
 
?>






