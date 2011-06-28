<?php 
if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_TAGMANAGER", true);
include_once("tags_common.inc.php");

if ($_GET[dothis] == "delete") {
	sletRow($_GET[id], "TAGS");
	header("location: index.php?content_identifier=$_GET[content_identifier]");
} elseif ($_GET[dothis] == "merge") {
	// First move all references
	$sql = "update TAG_REFERENCES set TAG_ID = '$_GET[mergeinto_id]' where TAG_ID = '$_GET[tag_id]'";
	if (mysql_query($sql)) {
		sletRow($_GET[tag_id], "TAGS");
		header("location: index.php?content_identifier=$_GET[content_identifier]");
	} else {
		echo "Fejl: Kunne ikke lægge de to tags sammen.";
		exit;
	}
}

?>