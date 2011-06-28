<?php
foreach ($_GET as $key => $value) {
	$_POST[$key] = $value;
}
header("Content-type: text/html; charset=UTF-8");
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/common.inc.php');
checkLoggedIn();
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/modules/filearchive2/filearchive2_common.inc.php');

switch ($_POST['do']) {
	case 'ajax_returnAvailablefiles':
		$output = filMappeListe();
		echo $output[0];
		echo "<script type='text/javascript'>";
			echo $output[1];
  		echo "</script>";
		break;
}

switch ($_REQUEST['do']) {
	case 'ajax_customfield_returnImageFolders':
		echo returnImageFolders($_POST["customfield_id"],$_POST["attribute_id"]);
		echo "|||".$_POST['customfield_id']."|||".$_POST['attribute_id'];
		break;
	case 'ajax_customfield_returnFolderImages':
		echo returnFolderImages($_POST["folder_id"],$_POST["customfield_id"],$_POST["attribute_id"]);
		echo "|||".$_POST['customfield_id']."|||".$_POST['attribute_id'];
		break;
	case 'picturearchive_returnImages':
		echo picturearchive_returnImages($_GET["fid"]);
		break;
	case 'ajax_returnAttachedGallery':
		echo returnAttachedGallery($_POST["page_id"]);
		break;
	case 'ajax_returnAvailablePicturefolders':
		echo returnAvailablePicturefolders();
		break;
	case 'ajax_addGalleryToPage':
		echo addGallery($_POST[page_id], $_POST[folder_id], "PAGES");
		break;
	case 'ajax_removeGalleryFromPage':
		echo removeGallery($_POST[page_id], "PAGES", $_POST[folder_id]);
		break;

	case 'ajax_dropboximport_init':
		include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/modules/filearchive2/dropbox_import.php');
		// 1. Copy dropbox folder to temporary folder
		copydirr($dropboxDropdir,$dropboxTempdir,0777,true);
		// 2. Empty dropbox folder
		recursive_remove_directory($dropboxDropdir,TRUE);
		// 3. Scan temporary folder, returns directory-tree-array
		$tmpfldr = scan_directory_recursively($dropboxTempdir);
		// 4. Create folders and build import que
		$batch_number = time();
		process_dropbox_array($tmpfldr, $batch_number, $_POST[imageKeepFolderstructure], $_POST[imageFolder]);
		if ($_POST[imageKeepFolderstructure] == "0") {
			if ($_POST[imageFolder] == 0) {
				// Hent nyeste billedmappe med PARENT_ID = 0
				$sql = "select ID from PICTUREARCHIVE_FOLDERS where PARENT_ID='0' and SITE_ID in (0,'$_SESSION[SELECTED_SITE]') order by ID desc limit 1";
				$res = mysql_query($sql);
				$row = mysql_fetch_assoc($res);
				$_POST[imageFolder] = $row[ID];
			}
			$sql = "update PICTUREARCHIVE_IMPORTQUE set TARGET_GROUP = '$_POST[imageFolder]' where BATCH_NUMBER = '$batch_number'";
			mysql_query($sql);
		}
		echo "success|||$batch_number";
		break;
	case 'ajax_dropboximport_upload':
		include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/modules/filearchive2/dropbox_import.php');
		// 5. Process que, 1 item at a time
		$status = process_dropbox_que($_POST[batchNumber]);
		// 6. Clean up on complete
		$arr_status = explode("|||", $status);
		if ($arr_status[1] == "complete") {
			// Empty import que
			$sql = "delete from 
						PICTUREARCHIVE_IMPORTQUE
					where
						BATCH_NUMBER = '$_POST[batchNumber]'";
			mysql_query($sql);
			// Empty temporary folder
			recursive_remove_directory($dropboxTempdir,TRUE);
		}
		echo $status;
		break;

}

?>
