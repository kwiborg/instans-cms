<?php
/////////////////////// SIDER (PAGES) /////////////////////////////////////////////////////////////////
 function nySideOversigt($menuid, $parent_id, $level){
  if ($parent_id !=0 && !array_search($parent_id, $_SESSION["OPEN_MENUS"])) return;
  global $dbname, $sti, $husk;
  $sti=""; $husk="";
  $i++;
  $whereclause = " SITE_ID in (0,'$_SESSION[SELECTED_SITE]') and MENU_ID='$menuid' and DELETED='0' and UNFINISHED='0' and PARENT_ID='$parent_id' ";
  $sql = "select ID, PARENT_ID, THREAD_ID, MENU_ID, SITE_ID, BREADCRUMB, CHANGED_DATE, EDIT_AUTHOR_ID, PUBLISHED, NO_DISPLAY, IS_FRONTPAGE, LOCKED_BY_USER, LANGUAGE, PROTECTED, POSITION, POINTTOPAGE_ID from PAGES where $whereclause order by POSITION asc";
  $result = mysql_query( $sql) or die(mysql_error());
  if (!$menuid) return "Vælg venligst en menu.";
  if (mysql_num_rows($result) == 0 && (!$filter_time && !$filter_menu)) return "Ingen sider er endnu oprettet i dette hierarki. Tryk på [Opret ny side] for at oprette en side.";
  if (mysql_num_rows($result) == 0 && ($filter_time || $filter_menu)) return "Ingen sider opfylder de kriterier, du har indstillet i filteret.";
  $level++;
  while ($row = mysql_fetch_array($result)) {
   $sql_galleries = "select * from GALLERIES where PAGE_ID='$row[ID]'";
   $result_galleries = mysql_query( $sql_galleries) or die(mysql_error());
   if (mysql_num_rows($result_galleries)>0) $nomoregalleries = true;   
   $sql_forms = "select * from PAGES_FORMS where PAGE_ID='$row[ID]'";
   $result_forms = mysql_query( $sql_forms) or die(mysql_error());
   if (mysql_num_rows($result_forms)>0) $nomoreforms = true;   
   $laast = ""; if ($row[LOCKED_BY_USER] == 1 && $_SESSION["CMS_USER"]["USER_ID"] != $row[EDIT_AUTHOR_ID]) $laast = " disabled ";
   echo "
    <div class='sideOversigt' onmouseover='pagesIEColorShift(this.id)' onmouseout='pagesIEColorUnShift(this.id)' id='pagerow__ID_$row[ID]_PARID_$row[PARENT_ID]_TID_$row[THREAD_ID]_AID_$row[EDIT_AUTHOR_ID]'>
	 <table border='0' cellpadding='0' cellspacing='0' width='100%'>
	  <tr>
	   <td style='padding-left:" . ($level*20 - 20) . "px'>
	  <span class='" . ($row[NO_DISPLAY] == 1 ? "breadcrumb_nodisplay" : "") . "'>" .
       (hasChildren($row[ID], "PAGES") ? "<input type='hidden' name='foldStatus_$row[THREAD_ID]' value=''><a class='linkplus' href='index.php?content_identifier=pages&amp;dothis=oversigt&amp;menuid=$row[MENU_ID]&amp;addtoopen=$row[ID]' title='Klik for at folde sidens undersider ind/ud'>+</a>" : "<span class='pageInfo'>+</span>") . "
       <a name='sideanker_$row[ID]'>&raquo;</a>&nbsp;$row[BREADCRUMB]&nbsp;" . ($row[IS_FRONTPAGE]==1 ? "<span class='pageInfo'>(<em>Startside</em>) </span>" : "") . "</span>
	  " . 
  	  // <span class='pageInfo' style='color:#666666'>ID=$row[ID];POS=$row[POSITION]; </span>
	  // <span class='pageInfo' style='color:#7E7145'>" . (returnAuthorName($row[EDIT_AUTHOR_ID],1) . ", ") . "</span>
	  // <span class='pageInfo' style='color:#7E7145'>" . (returnNiceDateTime($row[CHANGED_DATE],1) . "; ") . "</span><br/>
	   "<span class='pageInfo'>" . ($row[PUBLISHED] == 0 ? "<em>(Kladde)</em> " : "") .  "</span>".
	  // <span class='pageInfo' style='color:#000088'>" . ($row[NO_DISPLAY] == 1 ? "Skjult; " : " ") .  "</span>
	  "<span class='pageInfo' style='color:#ff0000'>" . ($row[LOCKED_BY_USER] == 1 ? "(Låst af ".returnAuthorName($row[EDIT_AUTHOR_ID],1).")" : " ") .  "</span>" . 
	  // <span class='pageInfo' style='color:#770077'>" . ($row[PROTECTED] == 2 ? "Kræver login;" : " ") .  "</span>
	  // <span class='pageInfo' style='color:#666666'>" . ($row[POINTTOPAGE_ID] != 0 ? "Peger på <em>" . menuPath($row[POINTTOPAGE_ID],false) . "</em>" : "") . "</span> 	  
      "</td>
	   <td align='right'>";

	if (!check_data_permission("DATA_CMS_PAGE_ACCESS", "PAGES", $row["ID"], "", $_SESSION["CMS_USER"]["USER_ID"])) {
		echo  "<input type='button' class='lilleknap' id='nopermissions__DATA_CMS_PAGE_ACCESS__PAGES__".$row["ID"]."' onclick='datapermission_loadgrants_plaintext(this.id)' value='Klik her for at se rettigheder' />";
	} else {
		echo "<a href='index.php?content_identifier=pages&amp;dothis=flytop&amp;id=$row[ID]&amp;menuid=$row[MENU_ID]'" . ($row[LOCKED_BY_USER] == 1 && $_SESSION["CMS_USER"]["USER_ID"] != $row[EDIT_AUTHOR_ID] ? "style='display:none'" : "" ) . "><img src='images/sideop.gif' border='0' /></a>&nbsp;
	  <a href='index.php?content_identifier=pages&amp;dothis=flytned&amp;id=$row[ID]&amp;menuid=$row[MENU_ID]'". ($row[LOCKED_BY_USER] == 1 && $_SESSION["CMS_USER"]["USER_ID"] != $row[EDIT_AUTHOR_ID] ? "style='display:none'" : "" ) . "><img src='images/sidened.gif' border='0' /></a>&nbsp;
	  <input type='button' $laast class='lilleknap' value='Rediger' onclick='location=\"index.php?content_identifier=pages&amp;dothis=rediger&amp;menuid=$row[MENU_ID]&amp;parentid=$row[PARENT_ID]&amp;id=$row[ID]\"' />&nbsp;
	  <input type='button' $laast class='lilleknap' value='Slet' onclick='if(confirm(\"Er du sikker på, at du vil slette?\")) location=\"index.php?content_identifier=pages&amp;dothis=slet&amp;id=$row[ID]&amp;menuid=$row[MENU_ID]\"'" . (hasChildren($row["ID"], "PAGES") ? "disabled" : "") . " />
	  &nbsp;
	  <input type='button' id='flytknap_$row[ID]' $laast class='lilleknap' value='Flyt'  onclick='pageFunction($row[ID], 7, $row[MENU_ID], $row[THREAD_ID], $level)' />&nbsp;
	  <input type='button' $laast class='lilleknap' value='Ny underside'  onclick='location=\"index.php?content_identifier=pages&amp;dothis=opret&amp;menuid=$row[MENU_ID]&amp;parentid=$row[ID]\"'>";
	}
	  
/*
	  &nbsp;&nbsp;eller&nbsp;&nbsp;&nbsp;
	  <select $laast class='lilleknap' name='extraFunctions_$row[ID]'>
	   <option value='0'>Vælg på listen...</option>
	   <option value='4'>Rediger relateret indhold</option>	   
       <option value='7'>Flyt siden i hierarkiet</option>
       <option value='8'>Højre margin</option>
	  </select>&nbsp;
	  <input type='button' $laast class='lilleknap' value='&gt;' onclick='pageFunction($row[ID], this.form.extraFunctions_$row[ID].value, $row[MENU_ID], $row[THREAD_ID], $level)'>
*/
echo "</td>
	  </tr>
	 </table>
	</div>";
   $lastParId = ($row[PARENT_ID] ? $row[PARENT_ID] : 0);
   $lastId = ($row[ID] ? $row[ID] : 0);
   $lastMenuId = $row[MENU_ID];
   $lastThreadId = $row[THREAD_ID];
   echo "    
	<div class='movepage_outer' id='insertAsSubPointTo_newparent$lastId"."_newthread$lastThreadId"."_newlevel$level'>
    <div class='movepage_inner' style='padding-left:" . (($level)*20 - 20) . "px''>&raquo;&nbsp;<a href='#' onclick='if (confirm(\"Vil du flytte denne side?\")) movePageToNewParent($lastId, $lastMenuId); return false'>Indsæt siden som underpunkt til \"".returnPageTitle($lastId)."\"</a></div>
   </div>"; //}
   nySideOversigt($menuid, $row["ID"], $level);
   $nomoreforms=false;
   $nomoregalleries=false;
  }
  if ($level==1) echo "    
   <div class='movepage_outer' style='margin-top:5px' id='insertAsSubPointTo_newparent0"."_newthread999999"."_newlevel0'>
    <div class='movepage_inner'>&raquo;&nbsp;<a href='#' onclick='if (confirm(\"Vil du flytte denne side?\")) movePageToNewParent(0, $lastMenuId); return false'>Indsæt siden her som nyt hovedpunkt</a></div>
   </div>";	
 }
 
// Used as ajax responders

function modifyRelatedBoxesNormal($page_id, $name, $perform) {
	// $name = name of the box
	// $perform = attach or remove
	
	if ($perform == "attach") {
		$value = 1;
	} else {
		$value = 0;
	}
	
	$dbcolname = strtoupper($name);
	$sql = "update BOX_SETTINGS set $dbcolname = '$value' where PAGE_ID='$page_id'";  
	if (mysql_query($sql)) {
		return "ok";
	} else {
		return "Der opstod en fejl. Den valgte boks ikke tilføjet som relateret indhold.";
	}	
}

function addRelatedContent($src_tabel, $src_id, $rel_tabel, $rel_id) {
	$sql = "select count(*) 
			from RELATED_CONTENT
			where SRC_TABEL = '$src_tabel'
			and SRC_ID = '$src_id'
			and REL_TABEL = '$rel_tabel'
			and REL_ID = '$rel_id'";
	$result = mysql_query($sql);
	if (mysql_result($result,0) == 0) {
		$sql = "insert into RELATED_CONTENT (SRC_TABEL, SRC_ID, REL_TABEL, REL_ID) values ('$src_tabel','$src_id','$rel_tabel','$rel_id')";
		if ($result = mysql_query($sql)) {
			return "ok";
		} else {
			return "Der opstod en fejl. Det valgte blev ikke tilføjet som relateret indhold.";
		}
	} else {
			return "Det valgte er allerede tilføjet som relateret indhold.";
	}	
}

function removeRelatedContent($src_tabel, $src_id, $rel_tabel, $rel_id) {
  $sql = "delete from RELATED_CONTENT
  			where SRC_TABEL = '$src_tabel'
			and SRC_ID = '$src_id'
			and REL_TABEL = '$rel_tabel'
			and REL_ID = '$rel_id'";
		if ($result = mysql_query($sql)) {
			return "ok";
		} else {
			return "Der opstod en fejl. Det valgte blev ikke fjernet fra relateret indhold.";
		}
}


function returnMenuslist() {
	$html .= "<select id='menuselector-relpages' name='menuselector-relpages' class='inputfelt' onchange='loadAvaliablePages()'>";
	$html .= "<option value='null'>Vælg menu...</option>";
	$html .= menuSelector(false);
	$html .= "<option value=''>Vis alle sider i alle menuer</option>";
	$html .= "</select>";
	return $html;
}

function returnAttachedPages($id, $tabel) {
	$html ="<table class='oversigt'>
				<tr>
					<td class='kolonnetitel'>Titel</td>
					<td class='kolonnetitel'>Funktioner</td>
				</tr>";
    $sql = "select PAGES.BREADCRUMB, PAGES.ID 
    			from RELATED_CONTENT, PAGES 
    			where PAGES.ID = RELATED_CONTENT.REL_ID
    			and RELATED_CONTENT.SRC_ID=$id 
				and RELATED_CONTENT.REL_TABEL = 'PAGES' 
				and RELATED_CONTENT.SRC_TABEL='$tabel'";
    $result = mysql_query($sql);
	$f_count = mysql_num_rows($result);
	if ($f_count > 0) {
		while ($row = mysql_fetch_array($result)) {
			$i++;
			$c = $i % 2 + 1;
			$html .= "<tr class='oversigt$id' id='pagerow_$i'>
						<td>".$row["BREADCRUMB"]."</td>
						<td width='15%'><input type='button' class='lilleknap' value='Fjern' onclick='removeRelatedPage(".$row[ID].")'>
						</td>
					</tr>";
		}
	} else {
		return "<table class='oversigt'>
				<tr>
					<td>Der er ikke defineret relaterede sider.</td>
				</tr>
				</table>";
	}
	$html .= "</table>";
	return $html;
}

function returnAvailablePages($indent, $parent_id, $count, $menu_id='') {
	global $listhtml, $count;
	if ($count == 0) {
		$listhtml .= 	"<tr class='oversigt$parent_id'><td class='kolonnetitel' colspan='2'>Vælg den side du vil tilføje</td></tr>";
		$count = 1;
	}
	$sql = "select ID, PARENT_ID, BREADCRUMB, SITE_ID, LANGUAGE, MENU_ID
			from PAGES 
			where PARENT_ID='$parent_id' 
			and UNFINISHED='0' 
			and DELETED='0' ";
	if ($menu_id == '') {
		$sql .= "and MENU_ID>0 ";
	} else {
		$sql .= "and MENU_ID=$menu_id ";
	}	
	$sql .= "and SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
			order by SITE_ID asc, MENU_ID asc, LANGUAGE asc, POSITION asc";
	$result = mysql_query( $sql) or die(mysql_error());
	if (mysql_num_rows($result) == 0 && $parent_id == 0) {
		return "<tr><td>Der er ingen sider i menuen.</td></tr>";
	}
	while ($row = mysql_fetch_array($result)) {
		$listhtml .= 	"<tr class='oversigt$parent_id'>
							<td>";
		if ($indent != "") {
			$listhtml .= $indent."> ";
		}

		$listhtml .= $row[BREADCRUMB]."</td>
							<td width='15%'>
								<input type='button' class='lilleknap' value='Tilføj' onclick='addRelatedPage(".$row[ID].")'>
							</td>
						</tr>";
		returnAvailablePages($indent . "-", $row["ID"], $count, $menu_id);
	}
	return $listhtml;
}


?>