<?php 
 if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_FILEARCHIVE", true);
 if ($dothis == "gem_mappe")
 {
  $nyt_mappe_id = gemFilMappe($mappenavn, $_SESSION["CMS_USER"]["USER_ID"]);
  header("location: ?content_identifier=filearchive&dothis=oversigt&newfolderid=$nyt_mappe_id");
  exit;
 }
 
 if ($dothis == "upload_fil")
 {
  $fileid = uploadFil("userfile", $folderid, $title, $description);
  if ($fileid == -1) {
   header("location: ?content_identifier=filearchive&dothis=opretfil&folderid=$folderid&error=1");
   exit;
  }
  else {
   header("location: ?content_identifier=filearchive&dothis=oversigt&folderid=$folderid");
   exit;
  }
 }
 
 if ($dothis == "sletfil" && $fileid && $folderid) {
  sletFil($fileid, $folderid);
  header("location: ?content_identifier=filearchive&dothis=oversigt&folderid=$folderid");
  exit;
 }

 if ($dothis == "sletmappe" && $folderid) {
  sletFilMappe($folderid);
  header("location: ?content_identifier=filearchive&dothis=oversigt&folderid=$folderid&mainmenuoff=$mainmenuoff");
  exit;
 }
 
 if ($dothis=="oldschool_addfile"){
 	$sql = "insert into ATTACHMENTS (PAGE_ID, FILE_ID, TABEL) values ('$_GET[content_id]', '$_GET[file_id]', '$_GET[tablename]')";
	mysql_query($sql);
	header("location: index.php?content_identifier=attachments&menuid=undefined&id=$_GET[content_id]&tabel=$_GET[tablename]");
	exit;
 }
 
?>