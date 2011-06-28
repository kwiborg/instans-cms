<?php
function newsfeedSelector($filter, $for='', $bool_checkpermission=true) {
	$sql = "select ID, NAME from NEWSFEEDS where SITE_ID in (0,'$_SESSION[SELECTED_SITE]') order by NAME asc";
	$result = mysql_query( $sql) or die(mysql_error());

	if ($for == "PageEditingAjax") {
		$html = "<select id='newsarchiveselector-relnews' name='newsarchiveselector-relnews' class='inputfelt' onchange='loadAvaliableNews();'>";
		$html .= "<option value='null'>Vælg nyhedsarkiv</option>";
	} else {
		$html = "<select id='newsfeedselector' name='newsfeedselector' class='inputfelt' onchange='newsfeedSelected(this.value);'>";
	}
	$i = 0;
	while ($row = mysql_fetch_array($result)) {
		if (check_data_permission("DATA_CMS_NEWSARCHIVE_ACCESS", "NEWSFEEDS", $row["ID"], "", $_SESSION["CMS_USER"]["USER_ID"]) || $bool_checkpermission==false) {
			$html .= "<option value='" . $row["ID"] . "'";
			if ($filter == $row["ID"]) {
				$html .= " selected";
			}
			$html .= ">" . $row["NAME"] . "</option>\n";
			$i++;
		}
	}
	if ($for == "PageEditingAjax") {
		$html .= "<option value=''>Vis alle nyheder i alle arkiver</option>";
	}
	if ($i == 0) {
		$html .= "<option value=''>Du har ikke adgang til nogen nyhedsarkiver på dette site!</option>";
	}
	$html .= "</select>";
	return $html;
}

function nyhedsOversigt($sortby, $sortdir, $filter_newsfeedid, $filter_author = "ALL_AUTHORS", $filter_time = "ALL_TIMES", $offset="", $count="") {
	global $newsarchive_newsPerPage_cms;
	if ($offset==""){
		$offset=0;
	}
	if ($count==""){
		$count=$newsarchive_newsPerPage_cms;
	}
	$filtertid = mktime(0,0,0, date("m"), date("d")-(1*$filter_time), date("y"));
	$whereclause = " where N.NEWSFEED_ID = NF.ID and NF.SITE_ID in (0,'$_SESSION[SELECTED_SITE]') and N.DELETED='0' ";
	if ($filter_author != "ALL_AUTHORS" && $filter_author != "") {
		$whereclause .= " and N.AUTHOR_ID='$filter_author'";
	}
	
	if ($filter_time != "ALL_TIMES" && $filter_time != "") {
		$whereclause .= " and N.CHANGED_DATE >= '$filtertid'";
	}

	$whereclause .= " and N.NEWSFEED_ID = '$filter_newsfeedid'";


	$sql = "
  select N.* from
   NEWS N, NEWSFEEDS NF $whereclause";
  if (!$sortdir) $sortdir = "DESC";
  if ($sortby && $sortdir) $sql .= " order by N.$sortby $sortdir";
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
    <td class='kolonnetitel'><a href='index.php?content_identifier=news&dothis=oversigt&sortby=HEADING&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&newsfeedid=$filter_newsfeedid' class='kolonnetitel'>Titel&nbsp;" . (($sortby=="HEADING") ?  $ikon : $tomt_ikon) . "</td>
    <td class='kolonnetitel'><a href='index.php?content_identifier=news&dothis=oversigt&sortby=AUTHOR_ID&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&newsfeedid=$filter_newsfeedid' class='kolonnetitel'>Forfatter&nbsp;" . (($sortby=="AUTHOR_ID") ?  $ikon : $tomt_ikon) . "</td>
    <td class='kolonnetitel'><a href='index.php?content_identifier=news&dothis=oversigt&sortby=NEWS_DATE&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&newsfeedid=$filter_newsfeedid' class='kolonnetitel'>Nyhedsdato&nbsp;" . (($sortby=="NEWS_DATE") ?  $ikon : $tomt_ikon) . "</td>
    <!--<td class='kolonnetitel'><a href='index.php?content_identifier=news&dothis=oversigt&sortby=NEWSFEED_ID&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&newsfeedid=$filter_newsfeedid' class='kolonnetitel'>Nyhedsarkiv&nbsp;" . (($sortby=="NEWSFEED_ID") ?  $ikon : $tomt_ikon) . "</td>-->
    <td class='kolonnetitel'><a href='index.php?content_identifier=news&dothis=oversigt&sortby=FRONTPAGE_STATUS&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&newsfeedid=$filter_newsfeedid' class='kolonnetitel'>Forside?&nbsp;" . (($sortby=="FRONTPAGE_STATUS") ?  $ikon : $tomt_ikon) . "</td>
    <!--<td class='kolonnetitel'><a href='index.php?content_identifier=news&dothis=oversigt&sortby=NEWSLETTER_ID&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&newsfeedid=$filter_newsfeedid' class='kolonnetitel'>Nyhedsbrev&nbsp;" . (($sortby=="NEWSLETTER_ID") ?  $ikon : $tomt_ikon) . "</td>-->
    <td class='kolonnetitel'><a href='index.php?content_identifier=news&dothis=oversigt&sortby=LOCKED_BY_USER&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&newsfeedid=$filter_newsfeedid' class='kolonnetitel'>Låst?&nbsp;" . (($sortby=="LOCKED_BY_USER") ?  $ikon : $tomt_ikon) . "</td>
    <td class='kolonnetitel'><a href='index.php?content_identifier=news&dothis=oversigt&sortby=PUBLISHED&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&newsfeedid=$filter_newsfeedid' class='kolonnetitel'>Færdig?&nbsp;" . (($sortby=="PUBLISHED") ?  $ikon : $tomt_ikon) . "</td>
    <td class='kolonnetitel'>Funktioner</td>
   </tr>
  ";
  $i=0;
  while ($row = mysql_fetch_array($result)) {  
   // Farverne skal skifte for hver <TR>
   $i++;
   $c=$i%2+1;
   // Vælg billede til Låst-status
   if ($row["LOCKED_BY_USER"] == 1) {
	$lockedimg = "Ja";
	if ($_SESSION["CMS_USER"]["USER_ID"] != $row[AUTHOR_ID]) $lockstr = "disabled=\"disabled\"";
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
   // Checkbox for forside on/off
   $frontpage_checkbox = "<input type='checkbox' name='frontpage_checkbox_" .$row["ID"] . "' onclick='if (this.checked) setFrontpage(" . $row["ID"] . ",1,\"$sortby\",\"$old_sortdir\", $filter_newsfeedid, \"$_GET[offset]\"); else setFrontpage(" . $row["ID"] . ",0,\"$sortby\",\"$old_sortdir\", $filter_newsfeedid, \"$_GET[offset]\");'>";
   // Script til checkbox for forside on/off
   if ($row["FRONTPAGE_STATUS"] == 1) $feedscript .= "checkCheckbox(\"frontpage_checkbox_" . $row["ID"] . "\", 1);";
   else $feedscript .= "checkCheckbox(\"frontpage_checkbox_" . $row["ID"] . "\", 0);";   
   // Byg selve table-rows'ene
   $html .=  "
   <tr class='oversigt$c' onmouseover='IEColorShift(this.id)' onmouseout='IEColorUnShift(this.id, $c)' id='pagerow_$i'>
	<td>" . $row["HEADING"] . "</td>
	<td>" . returnAuthorName($row["AUTHOR_ID"], 1) . "</td>
	<td>" . reverseDate($row["NEWS_DATE"]) . "</td>
	<!--<td>" . $newsfeed_knap . "</td>-->
	<td>" . $frontpage_checkbox . "</td>
	<!--<td>" . $newsletter_knap . "</td>-->
	<td>" .  $lockedimg . "</td>
	<td>" .  $publishedimg . "</td>
	<td>";
	if (!check_data_permission("DATA_CMS_NEWSITEM_ACCESS", "NEWS", $row["ID"], "", $_SESSION["CMS_USER"]["USER_ID"])) {
		$html .= "<input type='button' class='lilleknap' id='nopermissions__DATA_CMS_NEWSITEM_ACCESS__NEWS__".$row["ID"]."' onclick='datapermission_loadgrants_plaintext(this.id)' value='Klik her for at se rettigheder' />";
	} else {
		$html .= "<input type='button' class='lilleknap' value='Rediger' $lockstr onclick='location=\"index.php?content_identifier=news&dothis=rediger&id=" . $row["ID"] . "\"'>
	 <input type='button' class='lilleknap' value='Slet' $lockstr onclick='slet_nyhed(" . $row["ID"] . ", \"news\", ".$filter_newsfeedid.")'>
	 <input type='button' class='lilleknap' value='Vedhæft filer' $lockstr onclick='attachment(" . $row["ID"] . ", \"NEWS\")'>
	 <input type='button' class='lilleknap' value='Relateret indhold' $lockstr onclick='related(" . $row["ID"] . ", \"NEWS\")'>";
	}
	 $html .= "</td>
   </tr>
   ";
  }
  $html .= "</table>";
  $feedscript .= "</script>";
  return array($html, $feedscript);
 }
  
 function newsFrontpageState($id, $state) {
  $sql = "update NEWS set FRONTPAGE_STATUS='$state' where id='$id'";
  $result = mysql_query( $sql) or die(mysql_error());
 } 

function get_default_newsfeed($arr_noaccess="") {

	if (is_array($arr_noaccess)) {
		$str_noaccess = implode($arr_noaccess, ",");
		if (count($arr_noaccess)>0) {
			$sql_filter = "and ID not in ($str_noaccess)";
		} else {
			$sql_filter = "";
		}
	}

	// Get first menu
	$sql = "select ID from NEWSFEEDS where SITE_ID in (0,'$_SESSION[SELECTED_SITE]') $sql_filter order by NAME asc limit 1"; 
	$result = mysql_query($sql) or die(mysql_error());
	if (mysql_num_rows($result)==0) {
		return false;
	}
	$row = mysql_fetch_row($result);
	$feedid = $row[0];

	// Check if user has permission to see it
	if (!check_data_permission("DATA_CMS_NEWSARCHIVE_ACCESS", "NEWSFEEDS", $feedid, "", $_SESSION["CMS_USER"]["USER_ID"])) {
		$arr_noaccess[] = $feedid;
		$feedid = get_default_newsfeed($arr_noaccess);
	}
	if (is_numeric($feedid)) {
		return $feedid;
	} else {
		echo "<p>Du har ikke adgang til nogen nyhedsarkiver på dette site!</p>";
		exit;

	}
}


?>