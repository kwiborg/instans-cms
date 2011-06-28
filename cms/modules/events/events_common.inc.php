<?php
function returnCalendarLanguage($calendar_id) {
  $sql = "select DEFAULT_LANGUAGE from CALENDARS where ID='$calendar_id'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  return $row[0];  
}
function returnCalendarName($calendar_id) {
  $sql = "select NAME from CALENDARS where ID='$calendar_id'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_row($result);
  return $row[0];  
}

function calendarSelector($filter, $for='') {
	$sql = "select ID, NAME from CALENDARS where SITE_ID in (0,'$_SESSION[SELECTED_SITE]') order by NAME asc";
	$result = mysql_query( $sql) or die(mysql_error());

	if ($for == "PageEditingAjax") {
		$html = "<select id='calendarselector-relevents' name='calendarselector-relevents' class='inputfelt' onchange='loadAvaliableEvents();'>";
		$html .= "<option value='null'>Vælg kalender</option>";
	} else {
		$html = "\n<select id='filter_calendar' name='filter_calendar' class='inputfelt' onchange='calendarSelected(this.value);'>";
		$html .= "<option value=''>Vælg kalender</option>\n";
	}
	$i=0;
	while ($row = mysql_fetch_array($result)) {
		if ($for == "PageEditingAjax" || ($for=="" && check_data_permission("DATA_CMS_CALENDAR_ACCESS", "CALENDARS", $row["ID"], "", $_SESSION["CMS_USER"]["USER_ID"]))) {
			$html .= "<option value='" . $row["ID"] . "'";
			if ($filter == $row["ID"]) {
				$html .= " selected";
			}
			$html .= ">" . $row["NAME"] . "</option>\n";
			$i++;
		}
	}
	if ($i==0) {
		return false; 
	}
	if ($for == "PageEditingAjax") {
		$html .= "<option value=''>Vis alle begivenheder i alle kalendere</option>";
	}

	$html .= "</select>\n\n";
	return $html;
}

function kalenderOversigt($sortby, $sortdir, $filter_author, $filter_time, $filter_calendar, $filter_startdate_from="", $filter_startdate_to="", $offset="") {
	global $calendar_eventsPerPage_cms;
	if ($offset==""){
		$offset=0;
	}
	if ($count==""){
		$count=$calendar_eventsPerPage_cms;
	}

  $filtertid = mktime(0,0,0, date("m"), date("d")-(1*$filter_time), date("y"));
  $whereclause = " where E.CALENDAR_ID = C.ID and C.SITE_ID in (0,'$_SESSION[SELECTED_SITE]') and E.DELETED='0' ";
  if ($filter_author && $filter_author != "ALL_AUTHORS") {
  	$whereclause .= " and E.AUTHOR_ID='$filter_author'";
  }
  if ($filter_time && $filter_time != "ALL_TIMES") {
  	$whereclause .= " and E.CHANGED_DATE >= '$filtertid'";
  }
  
  if ($filter_startdate_from != "") {
  	$filter_startdate_from_rev = reverseDate($filter_startdate_from);
  	$whereclause .= " and STARTDATE >= '$filter_startdate_from_rev'";
  }
  if ($filter_startdate_to != "") {
  	$filter_startdate_to_rev = reverseDate($filter_startdate_to);
  	$whereclause .= " and STARTDATE <= '$filter_startdate_to_rev'";
  }

  if ($filter_calendar && $filter_calendar != "ALL_CALENDARS") $whereclause .= " and E.CALENDAR_ID='$filter_calendar'"; 
  else $whereclause .= " and E.CALENDAR_ID";
  $sql = "
  select *, E.ID as EVENTID from
   EVENTS E, CALENDARS C $whereclause";
  if (!$sortdir) $sortdir = "DESC";
  if ($sortby && $sortdir) $sql .= " order by E.$sortby $sortdir";
	$sql .= " limit $offset, $count";
  $result = mysql_query( $sql) or die(mysql_error());
  $tomt_ikon = "<img src='images/piltom.gif' border='0'>";
  $old_sortdir = $sortdir;  
  if ($sortdir == "DESC") {
   $sortdir = "ASC"; 
   $sortdir_changed=true;
   $ikon = "<img src='images/pilned.gif' border='0'>";
  }
  if ($sortdir == "ASC" && !$sortdir_changed) {
   $sortdir = "DESC";
   $ikon = "<img src='images/pilop.gif' border='0'>";
  }
  $html = "
  <table class='oversigt'>
   <tr class='trtop'>
    <td class='kolonnetitel'><a href='index.php?content_identifier=events&dothis=oversigt&sortby=HEADING&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&filter_calendar=$filter_calendar' class='kolonnetitel'>Titel&nbsp;" . (($sortby=="HEADING") ?  $ikon : $tomt_ikon) . "</td>
    <td class='kolonnetitel'><a href='index.php?content_identifier=events&dothis=oversigt&sortby=AUTHOR_ID&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&filter_calendar=$filter_calendar' class='kolonnetitel'>Forfatter&nbsp;" . (($sortby=="AUTHOR_ID") ?  $ikon : $tomt_ikon) . "</td>
    <td class='kolonnetitel'><a href='index.php?content_identifier=events&dothis=oversigt&sortby=PUBLISHED&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&filter_calendar=$filter_calendar' class='kolonnetitel'>Pub.&nbsp;" . (($sortby=="PUBLISHED") ?  $ikon : $tomt_ikon) . "</td>
    <td class='kolonnetitel'><a href='index.php?content_identifier=events&dothis=oversigt&sortby=STARTDATE&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&filter_calendar=$filter_calendar' class='kolonnetitel'>Startdato&nbsp;" . (($sortby=="STARTDATE") ?  $ikon : $tomt_ikon) . "</td>
    <td class='kolonnetitel'><a href='index.php?content_identifier=events&dothis=oversigt&sortby=ENDDATE&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&filter_calendar=$filter_calendar' class='kolonnetitel'>Slutdato&nbsp;" . (($sortby=="ENDDATE") ?  $ikon : $tomt_ikon) . "</td>
    <td class='kolonnetitel'><a href='index.php?content_identifier=events&dothis=oversigt&sortby=CALENDAR_ID&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&filter_calendar=$filter_calendar' class='kolonnetitel'>Kalender&nbsp;" . (($sortby=="CALENDAR_ID") ?  $ikon : $tomt_ikon) . "</td>
    <td class='kolonnetitel'>Funktioner</td>
   </tr>
  ";
  $i=0;
  $feedscript = "<script type=\"text/javascript\">";  
  while ($row = mysql_fetch_array($result)) {  
   // Farverne skal skifte for hver <TR>
   $i++;
   $c=$i%2+1;   
   // Vælg billede til Låst-status
   if ($row["LOCKED_BY_USER"] == 1 && $row["AUTHOR_ID"] != $_SESSION[CMS_USER][USER_ID]) {
	$lockedimg = "Ja";
	$lockstr = "disabled=\"disabled\"";
   } else {
	$lockedimg = "Nej";
    $lockstr = "";
   }
   $publishedimg = "";
   // Vælg billede til Færdigredigeret-status
   if ($row["PUBLISHED"] == 0) {
	$publishedimg = "Nej";
   } else {
	$publishedimg = "Ja";	
   }   
   // Byg selve table-rows'ene
   $html .=  "
   <tr class='oversigt$c' onmouseover='IEColorShift(this.id)' onmouseout='IEColorUnShift(this.id, $c)' id='pagerow_$i'>
	<td>" . $row["HEADING"] . "</td>
	<td>" . returnAuthorName($row["AUTHOR_ID"], 1) . "</td>
    <td>";
    if ($row["PUBLISHED"] == 1) {
    	$html .= "Ja";
    } else {
    	$html .= "Nej";
	}    
    $html .= "</td>
	<td>" . reverseDate($row["STARTDATE"]) . "</td>
	<td>" . reverseDate($row["ENDDATE"]) . "</td>
	<td>" . returnFieldValue("CALENDARS", "NAME", "ID", $row[CALENDAR_ID]) . "</td>
	<td>";
	
	if (!check_data_permission("DATA_CMS_EVENT_ACCESS", "EVENTS", $row["EVENTID"], "", $_SESSION["CMS_USER"]["USER_ID"])) {
		$disable = "disabled";
	} else {
		$disable = "";
	}
	
	if ($disable != "") {
		$html .= "<input type='button' class='lilleknap' id='nopermissions__DATA_CMS_EVENT_ACCESS__EVENTS__".$row["EVENTID"]."' onclick='datapermission_loadgrants_plaintext(this.id)' value='Klik her for at se rettigheder' />";
	} else {
		$html .= "<input type='button' class='lilleknap' value='Rediger' $lockstr onclick='location=\"index.php?content_identifier=events&dothis=rediger&filter_calendar=$filter_calendar&id=" . $row["EVENTID"] . "\"' />
		 <input type='button' class='lilleknap' value='Slet' $lockstr onclick='sletCalendar(" . $row["EVENTID"] . ", \"events\", \"$filter_calendar\")' />
		 <input type='button' class='lilleknap' value='Vedhæft filer' $lockstr onclick='attachment(" . $row["EVENTID"] . ", \"EVENTS\")' />
		 <input type='button' class='lilleknap' value='Relateret indhold' $lockstr onclick='related(" . $row["EVENTID"] . ", \"EVENTS\")' />";
	}

	$html .= "</td>
   </tr>
   ";
  }
  $html .= "</table>";
  $feedscript .= "</script>";
  return array($html, $feedscript);
 }

function get_default_calendar($arr_noaccess="") {

	if (is_array($arr_noaccess)) {
		$str_noaccess = implode($arr_noaccess, ",");
		if (count($arr_noaccess)>0) {
			$sql_filter = "and ID not in ($str_noaccess)";
		} else {
			$sql_filter = "";
		}
	}

	// Get first menu
	$sql = "select ID from CALENDARS where SITE_ID in (0,'$_SESSION[SELECTED_SITE]') $sql_filter order by NAME asc limit 1"; 
	$result = mysql_query($sql) or die(mysql_error());
	if (mysql_num_rows($result)==0) {
		return false;
	}
	$row = mysql_fetch_row($result);
	$calid = $row[0];

	// Check if user has permission to see it
	if (!check_data_permission("DATA_CMS_CALENDAR_ACCESS", "CALENDARS", $calid, "", $_SESSION["CMS_USER"]["USER_ID"])) {
		$arr_noaccess[] = $calid;
		$calid = get_default_calendar($arr_noaccess);
	}
	if (is_numeric($calid)) {
		return $calid;
	} else {
		echo "<p>Du har ikke adgang til nogen kalendre på dette site!</p>";
		exit;

	}
}

?>