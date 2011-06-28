<?php
if ($dothis == "remove_attachment" && $id && $fileid && $tabel) {
	$sql = "delete from ATTACHMENTS where PAGE_ID=$id and FILE_ID=$fileid and TABEL='$tabel'";
	$result = mysql_query($sql);
	$dothis = "";
} 
?>