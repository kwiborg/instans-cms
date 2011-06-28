<?php
function db_updategroupdiscounts() {
	foreach ($_POST as $key => $value) {
		$changedstatus = "";
		$sql = "";
		$karr = explode("_" , $key);
		if ($karr[0] == "groupdiscount") {
			if ($value == "") {
				// No discount set, delete row from database
				$sql = "delete from SHOP_GROUPDISCOUNTS where GROUP_ID = '$karr[1]' and PRODUCTGROUP_ID = '$karr[2]'";
				mysql_query($sql);
			} else {
				// Discount set, update/insert row as appropriate
				$changedstatus = db_groupdiscountChanged($karr[1], $karr[2], $value);
				if ($changedstatus == 2) {
					// DB ENTRY EXISTS AND IS CHANGED -- UPDATE
					$sql = "update SHOP_GROUPDISCOUNTS 
							set DISCOUNT_PERCENTAGE = '$value' 
							where GROUP_ID = '$karr[1]' and
							PRODUCTGROUP_ID = '$karr[2]'";
					mysql_query($sql);
				} elseif ($changedstatus == 1 && $value > 0) {
					// DB ENTRY DOESN NOT EXIST -- INSERT
					$sql = "insert into SHOP_GROUPDISCOUNTS 
							(GROUP_ID, DISCOUNT_PERCENTAGE, CHANGED_DATE, PRODUCTGROUP_ID) 
							values
							('$karr[1]', '$value', NOW(), '$karr[2]')";
					mysql_query($sql);
				}
			}
		}
	}
}

function db_groupdiscountChanged($usergroupid, $productgroupid, $value) {
	// Returns:
	// 1 = Row does not exist
	// 2 = Row exists, and is changed
	// 3 = Row exists, but is not changed
	// 
	if ($value == "") {
		$value = 0;
	}
	$sql = "select DISCOUNT_PERCENTAGE from SHOP_GROUPDISCOUNTS where GROUP_ID = '$usergroupid' and PRODUCTGROUP_ID = '$productgroupid'";
	$res = mysql_query($sql);
	if (mysql_num_rows($res) == 0) {
		return 1;
	}
	if (mysql_result($res,0) != $value) {
		return 2;
	} else {
		return 3;
	}
}

function return_usergroupselector($group_id) {
	$html .= "<select id='usergroupselector' class='inputfelt' onchange='selectUsergroup()'>";
	if ($group_id == "" || $group_id == 0) {
		$html .= "<option value='' selected>Vælg brugergruppe</option>\n";
	}
	$html .= usergroupSelector($group_id);
	$html .= "</select>";
	return $html;
}

function return_productgroups() {
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
						Rabatprocent
					</td>
				  </tr>";
		$html .= return_shopproductRow(0, 0);
		$html .= "</table>";
	}
	$html .= "</div>";
	return $html;
}

function return_shopproductRow($parent_id, $level) {
	$sql = "select * from SHOP_PRODUCTGROUPS where DELETED = '0' and PARENT_ID = '$parent_id' and SITE_ID = '$_SESSION[SELECTED_SITE]' order by NUMBER asc";
	$result = mysql_query($sql);
	$margin_left = $level * 15;
	while ($row = mysql_fetch_array($result)){
		// Does the current group has subgroups?
		$csql = "select * from SHOP_PRODUCTGROUPS where DELETED = '0' and PARENT_ID = '$row[ID]'";
		$cresult = mysql_query($csql);
		$subgroups = mysql_num_rows($cresult);
		// Does the current group has products?
		$psql = "select count(id) from SHOP_PRODUCTS where GROUP_ID = '$row[ID]' and DELETED = 0";
		$pres = mysql_query($psql);
		$products = mysql_result($pres,0);
		// Only allow delete of empty groups with no subgroups		
		if ($subgroups > 0 || $products > 0) {
			$delete = " disabled";
		} else {
			$delete = "";
		}

		$html .= "<tr class='oversigt2' onmouseover='IEColorShift(this.id)' onmouseout='IEColorUnShift(this.id, 2)' id='productgroup_$row[ID]'>
				<td width='10%'><span style='margin-left: ".$margin_left."px;'>$row[NUMBER]</span></td>
				<td>$row[NAME]</td>
				<td>$row[DESCRIPTION]</td>
				<td>";
		$current_groupdiscount = "";
		$gd_sql = "select DISCOUNT_PERCENTAGE from SHOP_GROUPDISCOUNTS where GROUP_ID = '$_GET[gid]' and PRODUCTGROUP_ID = '$row[ID]' limit 1";
		if ($gd_res = mysql_query($gd_sql)) {
			if (mysql_num_rows($gd_res) > 0) {
				$current_groupdiscount = mysql_result($gd_res,0);
			} else {
				$current_groupdiscount = "";
			}
		}			
		$html .= "<input class='input_number' id='groupdiscount_".$_GET[gid]."_$row[ID]' name='groupdiscount_".$_GET[gid]."_$row[ID]' onblur='validate_groupdiscount(this);' value='$current_groupdiscount' /> %
				</td>
			</tr>";
		$html .= return_shopproductRow($row[ID], $level+1);
	}
	return $html;
}


function return_groupdiscountform() {
	if ($_GET[usermessage] == "ok") {
		$html .= "<div class='usermessage_ok'>Dine ændringer er gemt</div>";
	}
	$html .= "<h1>Grupperabatter</h1>";
	$html .= "<form action='' method='post' id='form_groupdiscounts'>
				<input type='hidden' name='dothis' value='updategroupdiscounts' />
				<div class='feltblok_wrapper'>
			<h2>Vælg brugergruppe</h2>";
	$html .= return_usergroupselector($_GET["gid"]);	
	if (is_numeric($_GET["gid"]) && $_GET["gid"] > 0) {
		$html .= "<input type='hidden' id='gid' name='gid' value='".$_GET["gid"]."' />";
		$group_name = returnFieldValue("GROUPS", "GROUP_NAME", "ID", $_GET["gid"]);
		$general_discount = returnFieldValue("SHOP_GROUPDISCOUNTS", "DISCOUNT_PERCENTAGE", "GROUP_ID", $_GET["gid"]);
		if ($general_discount=="") {
			$general_discount = 0;
		}
		$html .= "<h2 id='discount-heading'>Tildel rabatter til brugergruppen \"$group_name\"</h2>
					<p>Rabatten gælder for alle brugere i gruppen \"$group_name\". Hvis en kunde tilhører mere end én kundegruppe gælder den højeste rabatsats. Hvis en vare tilhører mere end én varegruppe gælder den højeste rabatsats.</p>
					<h3>Generel rabatprocent</h3>
					<p>Herunder kan du indtaste en generel rabatprocent for brugergruppen \"$group_name\". Den generelle rabatprocent gælder alle varer i alle varegrupper, som ikke har en nærmere defineret rabatprocent herunder. Der beregnes ikke rabat på fragt. </p>
					<input class='input_number' id='groupdiscount_".$_GET[gid]."_0' name='groupdiscount_".$_GET[gid]."_0' onblur='validate_generaldiscount(this);' value='$general_discount' /> %";
		$html .= "<div class='knapbar'>
				<input type='button' value='Afbryd' onclick='location=\"index.php?content_identifier=shopgroupdiscounts\"' />
				<input type='button' value='Gem ændringer' onclick='saveGroupDiscounts()' />
			</div>";
		$html .= "<h3>Rabatprocent udspecificeret på varegrupper</h3>
					<p>Her kan du indtaste en rabatprocent for hver varegruppe i systemet. Hvis der er indtastet en rabatprocent ud for varegruppen benyttes denne til prisudregning, ellers benyttes den generelle rabatprocent. Rabatten gælder for alle varer i de nævnte varegrupper.</p>";

		$html .= return_productgroups();
	} // End if no-usergroup selected
	if (is_numeric($_GET["gid"]) && $_GET["gid"] > 0) {
		$html .= "<div class='knapbar'>
					<input type='button' value='Afbryd' onclick='location=\"index.php?content_identifier=shopgroupdiscounts\"' />
				<input type='button' value='Gem ændringer' onclick='saveGroupDiscounts()' />
			</div>";
	}
	$html .= "</form>";
	return $html;
}
?>