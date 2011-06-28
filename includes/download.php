<?php
  include($_SERVER[DOCUMENT_ROOT]."/cms_config.inc.php");
  include($cmsAbsoluteServerPath."/frontend/frontend_common.inc.php");
  $fildir = $fileUploaddir;
  connect_to_db();
  session_start();
	if (isset($_SESSION[CURRENT_SITE])) {
		$site_to_check = $_SESSION[CURRENT_SITE];
	} elseif (isset($_SESSION[SELECTED_SITE])) {
		$site_to_check = $_SESSION[SELECTED_SITE];
	}
  $querystr = "select 
  					F.FOLDER_ID, 
  					F.FILENAME, 
  					F.ORIGINAL_FILENAME, 
  					F.MIMETYPE,
  					FF.FOLDERNAME
  				from
  					FILEARCHIVE_FILES F,
  					FILEARCHIVE_FOLDERS FF
  				where 
  					F.FOLDER_ID = FF.ID and
  					FF.SITE_ID in (0,'$site_to_check') and 
  					F.ID='$_GET[id]'";  
  $result = mysql_query($querystr);
	if (mysql_num_rows($result) > 0) {
		$row1 = mysql_fetch_array($result);
		if ($row1[FOLDERNAME] != "") {
			$f = $fildir . "/" . $row1[FOLDERNAME] . "/" . $row1[FILENAME];
		} else {
			$f = $fildir . "/" . $row1[FILENAME];
		}
		if (file_exists($f)) {
			if(isset($row1[MIMETYPE])) {
				header("Content-Type: " . $row1[MIMETYPE]);
			}
			header("Cache-Control: private");
			header("Content-Disposition: attachment; filename=\"$row1[2]\"");
			readfile("$f");
		}
	} else {
		echo "Fejl: Kunne ikke hente filen! / Error: Unable to retrieve file!";
	}
?>