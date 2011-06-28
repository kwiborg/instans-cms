<?php
	echo "<h2>Siden indeholder en bog:</h2>";
	echo "Hvis siden skal indeholde en bog, så vælg den fra listen herunder. Alle andre indstillinger på siden vil blive ignoreret.";
	$sql = "select ID, BOOKTITLE, PUBLISHER, PUBLISHED, PUBMONTH, PUBYEAR, CHANGED_DATE from BOOKS where DELETED = 'N' order by BOOKTITLE";
	if ($result = mysql_query($sql)) {
			echo "<select name='bookSelector' id='bookSelector' class='inputfelt'>";
			if (db_hasrows()) {
				echo "<option value=''>Vælg en bog</option>";
				while ($row = mysql_fetch_array($result)) {
 	  				$selected_txt = " ";
					if ($pagedata["BOOK_ID"] == $row["ID"]) {
						$selected_txt = "selected ";
					}
					echo "<option ".$selected_txt."value='" . $row["ID"] . "'>" . $row["BOOKTITLE"] . "</option>";
				}
				echo "</select>\n\n";
			} else {
				echo "<option value=''>Der er ikke oprettet nogle bogprojekter endnu!</option>\n";
			}
			echo "</select>";
	}
?>