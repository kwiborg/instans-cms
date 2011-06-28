<?php
function db_deleteproductgroup($groupid) {
	sletRow($groupid, "SHOP_PRODUCTGROUPS");
}

function db_updateproductgroup() {
	if ($_POST[groupid] == "") {
		// do insert
		$sql = "insert into SHOP_PRODUCTGROUPS 
					(PARENT_ID, NUMBER, NAME, DESCRIPTION, IMAGE_ID, PUBLISHED, LISTMODE, SITE_ID)
					values
					('$_POST[parentid]', '$_POST[group_number]', '$_POST[group_name]', '$_POST[group_desc]', '$_POST[imageid]', '$_POST[group_published]', '$_POST[group_listmode]', '$_SESSION[SELECTED_SITE]');";
		
	} else {
		// do update
 		$sql = "update SHOP_PRODUCTGROUPS set 
				NUMBER = '$_POST[group_number]',
				NAME = '$_POST[group_name]',
				DESCRIPTION = '$_POST[group_desc]',
				IMAGE_ID = '$_POST[imageid]',
				PUBLISHED = '$_POST[group_published]',
				LISTMODE = '$_POST[group_listmode]'
				where ID = '$_POST[groupid]'";

		// remove existing rows from SHOP_PRODUCTGROUPS_FORMFIELDS
		$delete_sql = "delete from SHOP_PRODUCTGROUPS_FORMFIELDS where PRODUCTGROUP_ID = $_POST[groupid]";
		mysql_query($delete_sql);
	} 

	$result = mysql_query($sql);
	if ($_POST[groupid] == "") {
		$groupid = mysql_insert_id();
	} else {
		$groupid = $_POST[groupid];
	}
	
	// (re-)insert rows in SHOP_PRODUCTGROUPS_FORMFIELDS
	foreach ($_POST as $key => $value) {
		if (strstr($key, "group_formfields_")) {
			$sql = "INSERT INTO 
						`SHOP_PRODUCTGROUPS_FORMFIELDS` 
						(  `ID` ,  `PRODUCTGROUP_ID` ,  `FIELDNAME` ) 
					VALUES (
						NULL ,  '$groupid',  '$value'
					)";
			mysql_query($sql);
		}
	}
	sitemap_generator();
}

function return_productformfields($groupid="") {
	// 1. Build array of allowed formfields
		// Forbidden formfields
		$arr_forbidden = array("ID", "GROUP_ID", "PRODUCT_NUMBER", "NAME", "IMAGE_ID", "PRICE", "DELETED");
		
		// Extra non-SHOP_PRODUCTS formfields
		$arr_extra = array("COLLI");
	
	
		$sql = "desc SHOP_PRODUCTS";
		$res = mysql_query($sql);
		$arr_formfields = array();
		while ($row = mysql_fetch_array($res)) {
			if (!in_array($row["Field"], $arr_forbidden)) {
				$arr_formfields[] = $row["Field"];
			}
		}
		foreach ($arr_extra as $key => $value) {
			$arr_formfields[] = $value;
		}
	// 2. Fetch checked formfields, if any
		$arr_formfields_checked = array();
		if (is_numeric($groupid)) {
			$sql = "select FIELDNAME from SHOP_PRODUCTGROUPS_FORMFIELDS where PRODUCTGROUP_ID = $groupid";
			$res = mysql_query($sql);
			while ($row = mysql_fetch_array($res)) {
				$arr_formfields_checked[] = $row[FIELDNAME];
			}
		}
	// Build checkboxes
	asort($arr_formfields);
	foreach ($arr_formfields as $key => $value) {
		$html .= "<input type='checkbox' id='group_formfields_$value' name='group_formfields_$value' value='$value' ";
		if (in_array($value, $arr_formfields_checked) || $groupid=="") {
			$html .= "checked ";
		}
		$html .= "/>&nbsp;$value<br/>";
	}
	return "<h2>Felter som kan udfyldes for varer i gruppen</h2>".$html;
}


function returnform_shopproductgroups($groupid="") {
	if ($groupid == "") {
		$mode = "Opret";
	} else {
		$mode = "Rediger";
		$row = hentRow($groupid, "SHOP_PRODUCTGROUPS");
		if ($row[SITE_ID] != $_SESSION[SELECTED_SITE]) {
			return "Varegruppen findes ikke for dette site.";
		}
		$group_number = $row[NUMBER];
		$group_name = $row[NAME];
		$group_desc = $row[DESCRIPTION];
  		$imageid = $row[IMAGE_ID];
		$group_published = $row[PUBLISHED];
		$group_listmode = $row[LISTMODE];
		if ($imageid == 0) {
			$imageid = "";	  	
	  	} else {
	  		$image_url = returnImageUrl($imageid);
	  	}
	}
	if ($_GET[parentid] == "") {
		$parentid = 0;
	} else {
		$parentid = $_GET[parentid];
	}	
	$html .= "<h1>$mode varegruppe</h1>
				<div class='knapbar'>
					<input type='button' value='Afbryd' onclick='location=\"index.php?content_identifier=shopproductgroups\"' />
					<input type='button' value='Gem ændringer' onclick='saveProductGroup()' />
				</div>";

	$html .= "<form action='' method='post' id='form_productgroup'>
				<input type='hidden' name='dothis' value='updateproductgroup' />
				<input type='hidden' name='groupid' value='$groupid' />
				<input type='hidden' name='parentid' value='$parentid' />
				<input type='hidden' name='imageid' id='imageid' value='$imageid' />
				<input type='hidden' name='image_url' id='image_url' value='$image_url' />
				<div class='feltblok_header'></div>
				<div class='feltblok_wrapper'>
					<h2>Publiceret</h2>";
	$html .= createSelectYesNo("group_published", $group_published);
	$html .= "		<p class='feltkommentar'>Hvis gruppen er sat til kladde er gruppen og varer i gruppen skjult på sitet. Det samme gælder evt. undergrupper til denne gruppe.</p>
					<h2>Varegruppe nummer</h2>
					<input type='text' name='group_number' id='group_number' value='$group_number' class='inputfelt' />
					<h2>Varegruppe navn</h2>
					<input type='text' name='group_name' id='group_name' value='$group_name' class='inputfelt' />
					<h2>Varegruppe beskrivelse</h2>
					<textarea name='group_desc' id='group_desc' class='inputfelt'>$group_desc</textarea>";
					$html .= return_productformfields($groupid);
	$html .= "<h2>Listevisning</h2>
					<input type='radio' name='group_listmode' value='1' ";
/*
	$listmode = 1; // Show products on a list with just number, name and price
	$listmode = 2; // Show products on a list with image, short description, number, name and price (default)
	$listmode = 3; // Show products on a list with all available information
*/
					if ($group_listmode == "1") { $html .= "checked "; }
					$html .= " />&nbsp;Vis komprimeret liste med varenummer, varenavn og pris<br/>
					<input type='radio' name='group_listmode' value='2' ";
					if ($group_listmode == "2") { $html .= "checked "; }
					$html .= " />&nbsp;Vis udvidet liste med al information og varebeskrivelse i resumé";
	$html .= "<h2>
				<div style='float:left;'>Meningsfuld side-adresse for varegruppen:</div>
				<div id='ajaxloader_rewrite'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></div>
			</h2>";
	if (!is_numeric($groupid)) {
		$html .= "<p class='feltkommentar'>Meningsfuld side-adresse kan først indstilles, når varegruppen er gemt!</p>";
	} else {
		$html .= "<input disabled type='text' id='rewrite_keyword' name='rewrite_keyword' class='inputfelt' value='";
		$html .= (is_numeric($groupid) ? return_rewrite_keyword('', $groupid, 'SHOP_PRODUCTGROUPS', $_SESSION[SELECTED_SITE]) : '');
		$html .= "' onblur='keyword_onblur(this.form.group_name.value, this.value, this.form.groupid.value, \"SHOP_PRODUCTGROUPS\", $_SESSION[SELECTED_SITE])' />
			&nbsp;
			<input type='button' value='Ret'  class='inputfelt_kort' onclick='edit_keyword()' />
			<input type='button' value='Foreslå'  class='inputfelt_kort' onclick='if (edit_keyword()) suggest_rewrite_keyword(this.form.group_name.value, this.form.groupid.value, \"SHOP_PRODUCTGROUPS\", $_SESSION[SELECTED_SITE])' />";
			
	}

	$html .= "<h2>Benyt varegruppebillede?</h2>
					<p class='feltkommentar'>Hvis du vælger et billede for varegruppen, vil det blive vist ved gruppens varer, når der <em>ikke</em> er valgt et billede på den enkelte vare.</p>";
	if ($imageid == "") {
		$use_image_checked="N";
		$disabled_2 = "disabled";
	} else {
		$use_image_checked="Y";
		$disabled_2 = "";
	}		
	$html .= "<table><tr><td valign='top'>";
	$html .= createCheckbox("Ja tak", "use_image", "Y", "$use_image_checked", "useImage(this);", $image_disabled);
	$html .= "&nbsp;&nbsp;<input type='button' class='lilleknap' name='selectImageButton' id='selectImageButton' value='Vælg' $disabled_2 onclick='selectImage($folder_id);' />";
	$html .= "</td><td>";

	$thumburl = explode("/",$image_url);
	$lastpart = array_pop($thumburl);
	$thumburl[] = "thumbs";
	$thumburl[] = $lastpart;
	$thumburl = implode("/", $thumburl); 
	 
	$html .= "&nbsp;&nbsp;\n<img id='imgthumb' src='$thumburl' border='1'";
	if ($image_url == "") {
		$html .= " style='display:none;'";
	}
	$html .= "/>";
	$html .= "</td></tr></table>";
	// Div for selecting images
	$html .= "<div id='selectImageDiv' style='
		display: none;
		width: 725px; 
		height: auto; 
		border: 1px solid #999; 
		background-color: #FFF;'></div>";
	$html .= "</div>"; // Feltblock wrapper
	$html .= "<div class='knapbar'>
					<input type='button' value='Afbryd' onclick='location=\"index.php?content_identifier=shopproductgroups\"' />
					<input type='button' value='Gem ændringer' onclick='saveProductGroup()' />
			</div>";
	$html .= "</form>";
	return $html;
}


function return_productgroups() {
	$html .= "<h1>Varegrupper</h1>";
	$html .= "<div class='feltblok_header'></div>";
	$html .= "<div class='feltblok_wrapper'>";

	$sql = "select * from SHOP_PRODUCTGROUPS where DELETED = '0' and PARENT_ID = '0' and SITE_ID = '$_SESSION[SELECTED_SITE]' order by NUMBER asc";
	$result = mysql_query($sql);

	if (!db_hasrows($result)) {
		$html .= "<p>Der er ikke oprettet varegrupper!</p>";
	} else {
		$html .= "<table class='oversigt'>";
		$html .= "<tr class='trtop'>
					<td class='kolonnetitel'>
						Nummer
					</td>
					<td class='kolonnetitel'>
						Navn
					</td>
					<td class='kolonnetitel'>
						Beskrivelse
					</td>
					<td class='kolonnetitel'>
						Funktioner
					</td>
				  </tr>";
		$html .= return_shopproductRow(0, 0);
		$html .= "</table>";
	}
	$html .= "</div>";
	$html .= "<div class='knapbar'>
					<input type='button' value='Opret ny varegruppe' onclick='location=\"index.php?content_identifier=shopproductgroups&amp;dothis=opret&amp;parentid=0\"' />
				</div>";
	
	return $html;
}

function return_shopproductRow($parent_id, $level) {
// 2007-04-24	-	Site separation 0 now counts product groups correctly in "Does the current group has products?"
	$sql = "select * from SHOP_PRODUCTGROUPS where DELETED = '0' and PARENT_ID = '$parent_id' and SITE_ID = '$_SESSION[SELECTED_SITE]' order by NUMBER asc";
	$result = mysql_query($sql);
	$margin_left = $level * 15;
	while ($row = mysql_fetch_array($result)){
		// Does the current group has subgroups?
		$csql = "select * from SHOP_PRODUCTGROUPS where DELETED = '0' and PARENT_ID = '$row[ID]'";
		$cresult = mysql_query($csql);
		$subgroups = mysql_num_rows($cresult);
		// Does the current group has products?
		$psql = "select count(SP.ID) from SHOP_PRODUCTS SP, SHOP_PRODUCTS_GROUPS SPG where SP.ID = SPG.PRODUCT_ID and SPG.GROUP_ID = '$row[ID]' and SP.DELETED = 0";
		$pres = mysql_query($psql);
		$products = mysql_result($pres,0);
		// Only allow delete of empty groups with no subgroups		
		if ($subgroups > 0 || $products > 0) {
			$delete = " disabled";
		} else {
			$delete = "";
		}

		$html .= "<tr class='oversigt2' onmouseover='IEColorShift(this.id)' onmouseout='IEColorUnShift(this.id, 2)' id='productgroup_$row[ID]'>
				<td><span style='margin-left: ".$margin_left."px;'>$row[NUMBER]</span></td>
				<td>$row[NAME]</td>
				<td>$row[DESCRIPTION]</td>
				<td>
					<input type='button' class='lilleknap' value='Rediger' onclick='location=\"index.php?content_identifier=shopproductgroups&amp;dothis=rediger&amp;id=$row[ID]\"' />
					<input type='button' class='lilleknap' value='Slet' onclick='groupDelete($row[ID])'$delete />
					<input type='button' class='lilleknap' value='Ny undergruppe' onclick='location=\"index.php?content_identifier=shopproductgroups&amp;dothis=opret&amp;parentid=$row[ID]\"' />
					<input type='button' class='lilleknap' value='Relaterede varer' onclick='location=\"index.php?content_identifier=shopproducts&dothis=relatedproducts&groupid=$row[ID]\"' />
				</td>
										
			</tr>";
		$html .= return_shopproductRow($row[ID], $level+1);
	}
	return $html;
}

?>