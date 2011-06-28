<?php
header("Content-type: text/html; charset=UTF-8");
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/common.inc.php');
checkLoggedIn();
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/modules/events/events_common.inc.php');

switch ($_POST['do']) {
	// Related events
	case 'ajax_returnCalendarslist':
		echo calendarSelector("", "PageEditingAjax");
		break;
	case 'ajax_returnAttachedEvents':
		echo returnAttachedEvents($_POST[page_id],"PAGES");
		break;
	case 'ajax_returnAvailableEvents':
		echo returnAvailableEvents($_POST[calendar_id]);
		break;
}

function returnAttachedEvents($id, $tabel) {
	$sql = "select EVENTS.HEADING, EVENTS.ID 
    			from RELATED_CONTENT, EVENTS 
    			where EVENTS.ID = RELATED_CONTENT.REL_ID
    			and RELATED_CONTENT.SRC_ID=$id 
    			and RELATED_CONTENT.REL_TABEL= 'EVENTS' 
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
						<td width='15%'><input type='button' class='lilleknap' value='Fjern' onclick='removeRelatedEvent(".$row[ID].")'>
						</td>
					</tr>";
		}
	} else {
		return "<table class='oversigt'>
				<tr>
					<td>Der er ikke valgt relaterede begivenheder.</td>
				</tr>
				</table>";
	}
	$html .= "</table>";
	return $html;
}

function returnAvailableEvents($calendar_id) {
	$sql = "select 
				E.ID, 
				E.HEADING, 
				E.CALENDAR_ID,
				E.STARTDATE,
				E.ENDDATE
			from 
				EVENTS E,
				CALENDARS C
			where 
				E.CALENDAR_ID = C.ID and
				E.UNFINISHED='0' and 
				E.DELETED='0' ";
	if ($calendar_id == '') {
		$sql .= "and E.CALENDAR_ID > 0 ";
	} else {
		$sql .= "and E.CALENDAR_ID = $calendar_id ";
	}	
	$sql .= "and C.SITE_ID in (0,'$_SESSION[SELECTED_SITE]') 
			order by 
				E.STARTDATE desc, E.ENDDATE asc, E.HEADING asc";
	$result = mysql_query( $sql) or die(mysql_error());

	if (mysql_num_rows($result) == 0 && $parent_id == 0) {
		return "<tr><td>Der er ingen begivenheder i kalenderen.</td></tr>";
	}

	$listhtml = "<table class='oversigt'>";
	$listhtml .= 	"<tr class='oversigt$parent_id'><td class='kolonnetitel' colspan='2'>Vælg den begivenhed du vil tilføje</td></tr>";

	while ($row = mysql_fetch_array($result)) {
		$listhtml .= 	"<tr class='oversigt$parent_id'>
							<td>";
		if ($indent != "") {
			$listhtml .= $indent."> ";
		}

		$listhtml .= reverseDate($row[STARTDATE]).": ".$row[HEADING];
		if ($row[ENDDATE] != "0000-00-00") {
			$listhtml .= " (".reverseDate($row[STARTDATE])." til ".reverseDate($row[ENDDATE]).")";
		}
		$listhtml .= "</td>
							<td width='15%'>
								<input type='button' class='lilleknap' value='Tilføj' onclick='addRelatedEvent(".$row[ID].")'>
							</td>
						</tr>";
	}
	$listhtml .= "</table>";
	return $listhtml;
}  
?>