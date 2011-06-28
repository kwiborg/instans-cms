<?php
function listTags() {
	$html = "<h1>Tags</h1>
				<div class='feltblok_header'></div>
				<div class='feltblok_wrapper'>";
	
	$sql = "select
				T.ID, count(TR.ID) as TAGGED_COUNT, T.TAGNAME
			from 
				TAGS T
			left join 
				TAG_REFERENCES TR 
				on
				(T.ID = TR.TAG_ID)
			where 
				DELETED = 0 and 
				SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
				
			group by T.ID
			order by T.TAGNAME asc
			";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$html .= 	"<table class='oversigt'>
						<tr class='trtop'>
							<td class='kolonnetitel'>Tag</td>
							<td class='kolonnetitel'>Antal forekomster</td>
							<td class='kolonnetitel'>Funktioner</td>
						</tr>";
		// Store tags in array for use in next loop
		while ($row = mysql_fetch_array($result)) {
			$arr_tags[$row[ID]] = $row[TAGNAME];
		}
		mysql_data_seek($result,0);
		while ($row = mysql_fetch_array($result)) {
			$html .= "<tr>
					<td><span id='tagname_$row[ID]'>$row[TAGNAME]</span></td>
					<td>$row[TAGGED_COUNT]</td>
					<td>
						<input type='button' class='lilleknap' value='Slet' onclick='if (confirm(\"Vil du slette tagget >$row[TAGNAME]<?\")) location=\"index.php?content_identifier=tags&amp;dothis=delete&amp;id=$row[ID]\"' />
						<input type='button' class='lilleknap' value='Rediger' onclick='tag_makeeditable($row[ID]);' />
						&nbsp;Læg sammen med \n<select id='tag_mergewith' name='tag_mergewith' onclick='merge_tags($row[ID], \"$row[TAGNAME]\", this.value);'>
						\n\t<option value=''>Vælg tag...</option>";
			$current_tags = $arr_tags;
			unset($current_tags[$row[ID]]);
			foreach ($current_tags as $key => $value) {
				$html .= "\n\t<option value='".$key."__".$value."'>$value</option>\n\t";
			}
			$html .= "\n</select>
					</td>
				</tr>";
		}
		$html .= "</table>";
	} else {
		$html .= "Der er ikke oprettet nogen tags.";
	}
	$html .= "</div>";
	return $html;
}
?>