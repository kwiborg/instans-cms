<?php
function returnAttachedfiles($id, $tabel) {
	$html ="<table class='oversigt'>
				<tr>
					<td class='kolonnetitel'>Filnavn</td>
					<td class='kolonnetitel'>Funktioner</td>
				</tr>";
    $sql = "select FILE_ID, PAGE_ID from ATTACHMENTS where PAGE_ID=$id and TABEL='$tabel'";
    $result = mysql_query($sql);
	$f_count = mysql_num_rows($result);
	if ($f_count > 0) {
		while ($row = mysql_fetch_array($result)) {
			$i++;
			$c = $i % 2 + 1;
			$fileinfo = returnFileTitle($row[FILE_ID]);
			if ($_GET[content_identifier] == "attachments"){
				$onclick_handler = "remove_attachment(".$row[PAGE_ID].", $row[FILE_ID], \"$tabel\", 0, 0)";
			} else {
				$onclick_handler = "removeAttachment(".$row[FILE_ID].")";
			}
			$html .= "<tr class='oversigt$id' id='pagerow_$i'>
						<td>".$fileinfo["TITLE"]." - ".$fileinfo["DESCRIPTION"]." (".returnFileName($row[FILE_ID], 2, "FILEARCHIVE_FILES").")</td>
						<td width='15%'><input type='button' class='lilleknap' value='Fjern' onclick='$onclick_handler'>
						</td>
					</tr>";
		}
	} else {
		return "<table class='oversigt'>
				<tr>
					<td>Der er ikke vedhæftet filer til siden.</td>
				</tr>
				</table>";
	}
	$html .= "</table>";
	return $html;
}

function attachFile($file_id, $id, $tabel) {
	$sql = "select count(*) from ATTACHMENTS where PAGE_ID=$id and FILE_ID=$file_id and TABEL='$tabel'";
	$result = mysql_query($sql);
	mysql_result($result,0);
	if (mysql_result($result,0) == 0) {
		$sql = "insert into ATTACHMENTS (PAGE_ID, FILE_ID, TABEL) values ($id, $file_id, '$tabel')";
		$result = mysql_query($sql);
		return "ok";
	} else {
		return "Filen er allerede valgt som relateret indhold.";
	}
}

function removeAttachment($file_id, $id, $tabel) {
	$sql = "delete from ATTACHMENTS where PAGE_ID=$id and FILE_ID=$file_id and TABEL='$tabel'";
	$result = mysql_query($sql);
	return "ok";
}

?>
