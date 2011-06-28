<?php
header("Content-type: text/html; charset=UTF-8");
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/common.inc.php');
checkLoggedIn();
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/modules/news/news_common.inc.php');

switch ($_POST['do']) {
	// Related news
	case 'ajax_returnNewsarchiveslist':
		echo newsfeedSelector("", "PageEditingAjax");
		break;
	case 'ajax_returnAttachedNews':
		echo returnAttachedNews($_POST[page_id],"PAGES");
		break;
	case 'ajax_returnAvailableNews':
		echo returnAvailableNews($_POST[newsarchive_id]);
		break;
}

function returnAttachedNews($id, $tabel) {
	$sql = "select NEWS.HEADING, NEWS.ID 
    			from RELATED_CONTENT, NEWS 
    			where NEWS.ID = RELATED_CONTENT.REL_ID
    			and RELATED_CONTENT.SRC_ID=$id 
    			and RELATED_CONTENT.REL_TABEL= 'NEWS' 
    			and RELATED_CONTENT.SRC_TABEL = '$tabel'";
	$result = mysql_query($sql);
	$f_count = mysql_num_rows($result);
	if ($f_count > 0) {
		$html ="<table class='oversigt'>
				<tr>
					<td class='kolonnetitel'>Titel</td>
					<td class='kolonnetitel'>Funktioner</td>
				</tr>";
		while ($row = mysql_fetch_array($result)) {
			$i++;
			$c = $i % 2 + 1;
			$html .= "<tr class='oversigt$id' id='pagerow_$i'>
						<td>".$row["HEADING"]."</td>
						<td width='15%'><input type='button' class='lilleknap' value='Fjern' onclick='removeRelatedNews(".$row[ID].")'>
						</td>
					</tr>";
		}
	} else {
		return "<table class='oversigt'>
				<tr>
					<td>Der er ikke valgt relaterede nyheder.</td>
				</tr>
				</table>";
	}
	$html .= "</table>";
	return $html;
}

function returnAvailableNews($newsarchive_id) {
	$sql = "select 
				N.ID, 
				N.HEADING, 
				N.NEWSFEED_ID,
				N.NEWS_DATE
			from 
				NEWS N,
				NEWSFEEDS NF
			where 
				NF.ID = N.NEWSFEED_ID and
				N.UNFINISHED='0' 
			and 
				N.DELETED='0' ";
	if ($newsarchive_id == '') {
		$sql .= "and N.NEWSFEED_ID > 0 ";
	} else {
		$sql .= "and N.NEWSFEED_ID = $newsarchive_id ";
	}	
	$sql .= "and NF.SITE_ID in (0,'$_SESSION[SELECTED_SITE]') 
			order by N.NEWS_DATE desc, N.HEADING asc";
	$result = mysql_query( $sql) or die(mysql_error());

	if (mysql_num_rows($result) == 0 && $parent_id == 0) {
		return "<tr><td>Der er ingen nyheder i arkivet.</td></tr>";
	}

	$listhtml = "<table class='oversigt'>";
	$listhtml .= 	"<tr class='oversigt$parent_id'><td class='kolonnetitel' colspan='2'>Vælg den nyhed du vil tilføje</td></tr>";

	while ($row = mysql_fetch_array($result)) {
		$listhtml .= 	"<tr class='oversigt$parent_id'>
							<td>";
		if ($indent != "") {
			$listhtml .= $indent."> ";
		}

		$listhtml .= reverseDate($row[NEWS_DATE]).": ".$row[HEADING]."</td>
							<td width='15%'>
								<input type='button' class='lilleknap' value='Tilføj' onclick='addRelatedNews(".$row[ID].")'>
							</td>
						</tr>";
	}
	$listhtml .= "</table>";
	return $listhtml;
}  
?>