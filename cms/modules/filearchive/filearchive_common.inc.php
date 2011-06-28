<?php
function filMappeListe() {
	global $fileUploaddirAbs;
	$sql = "select 
				* 
			from 
				FILEARCHIVE_FOLDERS 
			where
				SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
			order by TITLE asc";
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
			" . $row["TITLE"] . "
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
			if ($_GET[content_identifier] == "filearchive"){
				$onclick_handler = "attachFile(".$prow[ID].", $_GET[returntoid], \"$_GET[returntotabel]\")";
			} else {
				$onclick_handler = "attachFile(".$prow[ID].")";
			}
			$html .= "
				<tr class='file_row' id='parent" . $row["ID"] . "_fil$i'>
				 <td align='left'>" . $prow["TITLE"] . "</td>	
				 <td align='left'>";
			$html .= $prow["ORIGINAL_FILENAME"]."   [<a href='$fileUploaddirAbs/";
			$html .= returnFolderName($prow["FOLDER_ID"], 1, "FILEARCHIVE_FOLDERS");
			$html .= "/".$prow["FILENAME"]."' target='_blank' alt='Link til filen'>" . "link" . "</a>]   ";
			$html .= " [<a href='/includes/download.php?id=$prow[ID]'>"."download"."</a>]";
			$html .= "
				 </td>
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
 
 function uploadFil($felt, $folder_id, $title, $description)
 {
  global $fileUploaddir; 
  $temp_filename = $_FILES["userfile"]["tmp_name"];
  $original_filename = $_FILES["userfile"]["name"];
  $mimetype = $_FILES["userfile"]["type"];
  $new_filename = time();
  $dest_folder = returnFolderName($folder_id, 1, "FILEARCHIVE_FOLDERS");
  $dest_filename = "$fileUploaddir/$dest_folder/$new_filename";
  if (!move_uploaded_file($temp_filename, $dest_filename)) {
   echo "Fejl i overførsel";
   exit;
  }
  $extension = extension($original_filename);
  $filesize = filesize("$fileUploaddir/$dest_folder/$new_filename");
  if ($filesize <= 0) {
   return -1;
  }
  $new_filename_2 = $new_filename . "." . $extension;
  $nu = time();
  rename("$fileUploaddir/$dest_folder/$new_filename", "$fileUploaddir/$dest_folder/$new_filename_2");
  $sql = "insert into FILEARCHIVE_FILES 
   (FOLDER_ID, FILENAME, ORIGINAL_FILENAME, TITLE, DESCRIPTION, AUTHOR_ID, CREATED_DATE, EXTENSION, MIMETYPE)
  values
   ('$folder_id', '$new_filename_2', '$original_filename', '$title', '$description', '" . $_SESSION["CMS_USER"]["USER_ID"] . "', '$nu', '$extension', '$mimetype')"; 
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
  $row = mysql_fetch_array($result);
  $f1 = "$fileUploaddir/" . returnFolderName($row["FOLDER_ID"], 1, "FILEARCHIVE_FOLDERS") . "/" . $row["FILENAME"];
  if (file_exists($f1)) unlink($f1);
  $sql = "delete from FILEARCHIVE_FILES where ID='$id'";
  $result = mysql_query( $sql) or die(mysql_error()); 
  $sql = "delete from ATTACHMENTS where FILE_ID='$id'";
  $result = mysql_query( $sql) or die(mysql_error()); 
 }

function sletFilMappe($id)
{
 global $fileUploaddir;
 $mappe = returnFolderName($id, 1, "FILEARCHIVE_FOLDERS");
 if (file_exists("$fileUploaddir/$mappe")) rmdir("$fileUploaddir/$mappe");
 $sql = "delete from FILEARCHIVE_FOLDERS where ID='$id' and SITE_ID in (0,'$_SESSION[SELECTED_SITE]')";
 $result = mysql_query( $sql) or die(mysql_error()); 
}
?>
