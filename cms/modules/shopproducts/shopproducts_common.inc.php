<?php
function db_deleteproduct($id) {
	sletRow($id, "SHOP_PRODUCTS");
}

function db_updateproduct() {
	if (!isset($_POST[product_show_related])) {
		$_POST[product_show_related] = 0;
	}
	if ($_POST[productid] == "") {
		// do insert
		$sql = "insert into SHOP_PRODUCTS 
					(ID, 
					GROUP_ID, 
					PRODUCT_NUMBER, 
					ALT_PRODUCT_NUMBER, 
					NAME, 
					DESCRIPTION, 
					DESCRIPTION_COMPLETE, 
					URL_EXT_INFO,
					URL_EXT_PRODUCTSHEET,
					IMAGE_ID, 
					QUALITY_ID, 
					DIAMETER, 
					LENGTH, 
					PRICE,
					SHOW_RELATED_PRODUCTS)
					values
					('$_POST[productid]', 
					'$_POST[groupid]', 
					'$_POST[product_number]', 
					'$_POST[alt_product_number]', 
					'$_POST[product_name]', 
					'$_POST[product_desc]', 
					'$_POST[product_desc_complete]', 
					'$_POST[product_url_ext_info]', 
					'$_POST[product_url_ext_productsheet]', 
					'$_POST[imageid]', 
					'$_POST[productQualitySelector]', 
					'$_POST[product_diameter]', 
					'$_POST[product_length]', 
					'$_POST[product_price]',
					'$_POST[product_show_related]');";
	} else {
		// do update
 		$sql = "update SHOP_PRODUCTS set 
				GROUP_ID = '$_POST[groupid]',
				PRODUCT_NUMBER = '$_POST[product_number]',
				ALT_PRODUCT_NUMBER = '$_POST[alt_product_number]',
				NAME = '$_POST[product_name]',
				DESCRIPTION = '$_POST[product_desc]',
				DESCRIPTION_COMPLETE = '$_POST[product_desc_complete]',
				URL_EXT_INFO = '$_POST[product_url_ext_info]',
				URL_EXT_PRODUCTSHEET = '$_POST[product_url_ext_productsheet]',
				IMAGE_ID = '$_POST[imageid]',
				QUALITY_ID = '$_POST[productQualitySelector]',
				DIAMETER = '$_POST[product_diameter]',
				LENGTH = '$_POST[product_length]',
				PRICE = '$_POST[product_price]',
				SHOW_RELATED_PRODUCTS = '$_POST[product_show_related]' 
				where ID = '$_POST[productid]'";
	} 
	$result = mysql_query($sql);
	// Make sure product_id is known on new product
	if ($_POST[productid] == "") {
		$_POST[productid] = mysql_insert_id();
	}
	// parse colli form
	foreach ($_POST as $key => $value) {
		if (strstr($key, "colli")) {
			$a = explode("_", $key);
			if ($value > 0) {
				if ($a[0] == "colliQuantity") {
					$colliRows[] = $a[1];
					$colliID[$a[1]] = $a[2];
					$colliQ[$a[1]] = $value;
				} 
				if ($a[0] == "colliDiscPerc") {
					$colliDP[$a[1]] = $value;
				} 
				if ($a[0] == "colliDiscAbs") {
					$colliDA[$a[1]] = $value;
				}
			}
		}
	}

	// Delete unused colli
	if (is_array($colliRows)) {
		$sql = "update SHOP_PRODUCTS_COLLI set DELETED = '1' where PRODUCT_ID = '$_POST[productid]' and QUANTITY not in (";
		foreach ($colliRows as $key => $value) {
			$sql .= $colliQ[$value];
			if (count($colliRows)-1 > $key) {
				$sql .= ",";
			}
		}
		$sql .= ")";
	} else {
		$sql = "update SHOP_PRODUCTS_COLLI set DELETED = '1' where PRODUCT_ID = '$_POST[productid]'";
	}
	$result = mysql_query($sql);

	// Insert / update colli
	if (is_array($colliRows)) {
		foreach ($colliRows as $key => $value) {
			if (colli_exists($_POST[productid], $colliQ[$value])) {
				// update
				$sql = "update SHOP_PRODUCTS_COLLI set
				QUANTITY = '$colliQ[$value]',
				DISCOUNT_PERCENTAGE = '$colliDP[$value]',
				DISCOUNT_AMOUNTPERCOLLI = '$colliDA[$value]',
				DELETED = '0'
				where
				PRODUCT_ID = '$_POST[productid]' 
				and QUANTITY = '$colliQ[$value]'";
			} else {
				// insert
				$sql = "insert into SHOP_PRODUCTS_COLLI 
					(PRODUCT_ID, QUANTITY, DISCOUNT_PERCENTAGE, DISCOUNT_AMOUNTPERCOLLI)
					values
					('$_POST[productid]', '$colliQ[$value]', '$colliDP[$value]', '$colliDA[$value]');";
			}
			$result = mysql_query($sql);
		}
	}




	// Parse selected productgroups
	foreach ($_POST as $key => $value) {
		if (strstr($key, "productgroup_")) {
			$a = explode("_", $key);
			if ($value == 1) { // productgroup selected
				$a_groups[] = $a[1];
			}
		}
	}
	if (count($a_groups) > 0) {
		// selection of groups made, so safe to delete old selecion
		$sql = "delete from SHOP_PRODUCTS_GROUPS where PRODUCT_ID = '$_POST[productid]'";
		mysql_query($sql);
		// Now insert selected groups
		foreach ($a_groups as $key => $value) {
			$sql = "insert into SHOP_PRODUCTS_GROUPS (PRODUCT_ID, GROUP_ID) values ('$_POST[productid]', '$value')";
			mysql_query($sql);
		}
		
	}
	sitemap_generator();
}

function colli_exists($productid, $quantity) {
	$sql = "select ID 
				from SHOP_PRODUCTS_COLLI 
				where QUANTITY = '$quantity' 
				and PRODUCT_ID = '$productid' 
				limit 1";
	$res = mysql_query($sql);
	if (mysql_num_rows($res) > 0) {
		return true;
	} else {
		return false;
	}
}
function check_formelement_display($field_name, $field_id, $str_dbname, $mode="begin") {
	// Check if $str_dbname is registered as a formfield for any of the groups to which this $product_id belongs
	// $field_name is "PRODUCT_ID" or "GROUP_ID" - added 2007-04-24
	$sql = "select 
				count(*) 
			from 
				SHOP_PRODUCTGROUPS_FORMFIELDS
			where ";
	if ($field_name == "PRODUCT_ID") {
		$sql .= "PRODUCTGROUP_ID in (select GROUP_ID from SHOP_PRODUCTS_GROUPS where $field_name = '$field_id') and FIELDNAME = '$str_dbname'";
	} else {
		$sql .= "PRODUCTGROUP_ID = '$field_id' and FIELDNAME = '$str_dbname'";
	}
	if ($res = mysql_query($sql)) {
		if (!mysql_result($res,0) > 0) {
			// No, the formfield should not be shown
			$display = false;
		} else {
			$display = true;
		}
	}
	if ($display) {
		return;
	} else {
		if ($mode == "begin") {
			return "<div style='display: none;'>";	
		} else {
			return "</div>";
		}
		
	}
}

function shop_productgroup_checkboxes($parentId, $product_id){
	$sql = "
		select 
			ID, NUMBER, NAME, PARENT_ID
		from 
			SHOP_PRODUCTGROUPS
		where 
			SITE_ID = '$_SESSION[SELECTED_SITE]' and 
			DELETED='0' and PARENT_ID='$parentId'
		order by
			NUMBER asc
	";
	$result = mysql_query($sql);
	$html .= "<ul>";
	while ($row = mysql_fetch_array($result)){
		$html .= "<li>";
		if ($_GET[dothis] == "opret" && $_GET[groupid]==$row[ID]) {
			$checked = "checked ";
		} elseif ($_GET[dothis] == "rediger") {
			$sql = "select count(*) from SHOP_PRODUCTS_GROUPS where GROUP_ID = '$row[ID]' and PRODUCT_ID = '$product_id'";
			$res = mysql_query($sql);
			if (mysql_result($res,0)>0) {
				$checked = "checked ";
			} else {
				$checked = "";
			}
		} else {
			$checked = "";
		}
		$html .= "<input type='checkbox' id='productgroup_$row[ID]' name='productgroup_$row[ID]' class='pg_checkbox' value='1'$checked />$row[NAME] ($row[NUMBER])<br/>";
		if (shop_productgroup_haschildren($row[ID])){
			$html .= shop_productgroup_checkboxes($row[ID], $product_id);
			$html .= "</li>";
		} else {
			$html .= "</li>";
		}
	}
	$html .= "</ul>";
	return $html;
 } 

 function shop_productgroup_haschildren($id){
	$sql = "select count(ID) from SHOP_PRODUCTGROUPS where PARENT_ID='$id' and DELETED='0'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	if ($row[0] > 0){
		return true;
	} else {
		return false;
	}
 }
 

function returnform_shopproducts($productid="") {
	global $productDiameterUnit, $productLengthUnit, $exchangeRates, $fckEditorPath;
	if ($productid == "") {
		$check_formelement_display_fieldname = "GROUP_ID";
		$check_formelement_display_fieldid = $_GET[groupid];
		$product_show_related = 1;
		$mode = "Opret";
	} else {
		$mode = "Rediger";
		$row = hentRow($productid, "SHOP_PRODUCTS");
		$check_formelement_display_fieldname = "PRODUCT_ID";
		$check_formelement_display_fieldid = $row[ID];
		$product_number = $row[PRODUCT_NUMBER];
		$alt_product_number = $row[ALT_PRODUCT_NUMBER];
		$product_name = $row[NAME];
		$product_desc = $row[DESCRIPTION];
  		$product_desc_complete = unhtmlentities($row[DESCRIPTION_COMPLETE]);
  		$product_url_ext_info = $row[URL_EXT_INFO];
  		$product_url_ext_productsheet = $row[URL_EXT_PRODUCTSHEET];
  		$imageid = $row[IMAGE_ID];
		if ($imageid == 0) {
			$imageid = "";	  	
	  	} else {
	  		$image_url = returnImageUrl($imageid);
	  	}
  		$product_qualityid = $row[QUALITY_ID];
		if ($product_qualityid == 0) {
			$product_qualityid = "";	  	
		}
		if ($row[DIAMETER] == 0) {
			$row[DIAMETER] = "";
		}
		if ($row[LENGTH] == 0) {
			$row[LENGTH] = "";
		}
  		$product_diameter = $row[DIAMETER];
  		$product_length = $row[LENGTH];
  		$product_price = $row[PRICE];
  		$product_show_related = $row[SHOW_RELATED_PRODUCTS];
	}
	if ($_GET[groupid] == "") {
		$groupid = "";
	} else {
		$groupid = $_GET[groupid];
	}	

	$html .= "<h1>$mode vare</h1>";

	$html .= "<form action='' method='post' id='form_product'>
				<input type='hidden' name='dothis' value='updateproduct' />
				<input type='hidden' name='productid' value='$productid' />
				<input type='hidden' name='groupid' value='$groupid' />
				<input type='hidden' name='imageid' id='imageid' value='$imageid' />
				<input type='hidden' name='image_url' id='image_url' value='$image_url' />
				<div class='feltblok_header'></div>
				<div class='feltblok_wrapper'>
					<h2>Varegruppe</h2>";
//	$html .= productGroupSelector($groupid, "noonchange");
	$html .= "<p class='feltkommentar'>Felter som kan udfyldes for denne vare afhænger af hvilke grupper den placeres i. Derfor kan du få mulighed for at udfylde flere felter, ved at afkrydse flere varegrupper. Du kan sikre at du ser alle relevante felter ved at trykke Gem og dernæst Rediger på varen, når du har afkrydset de korrekte varegrupper.</p>";

	$html .= "<p>Placér varen i følgende varegrupper:</p>\n<div id='productgroups'>";
	$html .= shop_productgroup_checkboxes(0,$productid);
	$html .= "</div><h2>Varenummer</h2>
					<input type='text' name='product_number' id='product_number' value='$product_number' class='inputfelt' />";

	$html .= check_formelement_display($check_formelement_display_fieldname, $check_formelement_display_fieldid, "ALT_PRODUCT_NUMBER");
	$html .= "<h2>Alternativt varenummer</h2>
					<input type='text' name='alt_product_number' id='alt_product_number' value='$alt_product_number' class='inputfelt' />";
	$html .= check_formelement_display($check_formelement_display_fieldname, $check_formelement_display_fieldid, "ALT_PRODUCT_NUMBER", "end");


	$html .= "<h2>Vare navn</h2>
					<input type='text' name='product_name' id='product_name' value='$product_name' class='inputfelt' />";

					
	$html .= check_formelement_display($check_formelement_display_fieldname, $check_formelement_display_fieldid, "DESCRIPTION");
	$html .= "<h2>Vare beskrivelse, resumé (anvendes til udvidet listevisning af varegrupper)</h2>
					<textarea name='product_desc' id='product_desc' class='inputfelt'>$product_desc</textarea>";
	$html .= check_formelement_display($check_formelement_display_fieldname, $check_formelement_display_fieldid, "DESCRIPTION", "end");

					
	$html .= check_formelement_display($check_formelement_display_fieldname, $check_formelement_display_fieldid, "DESCRIPTION_COMPLETE");
	$html .= "<h2>Vare beskrivelse, komplet (anvendes ved enkeltvisning af produkter)</h2>";
	$oFCKeditor = new FCKeditor('product_desc_complete') ;
	$oFCKeditor->BasePath = $fckEditorPath . "/";
	$oFCKeditor->ToolbarSet	= "CMS_Default";
	$oFCKeditor->Height	= "400";
	$oFCKeditor->Value	= $product_desc_complete;
	$oFCKeditor->Config['CustomConfigurationsPath']	= $fckEditorCustomConfigPath . "/cms_fckconfig.js";
	$html .= $oFCKeditor->CreateHtml() ;
	$html .= check_formelement_display($check_formelement_display_fieldname, $check_formelement_display_fieldid, "DESCRIPTION_COMPLETE", "end");


	$html .= check_formelement_display($check_formelement_display_fieldname, $check_formelement_display_fieldid, "URL_EXT_INFO");
	$html .= "<h2>Link til producentens hjemmeside</h2>
					<input type='text' name='product_url_ext_info' id='product_url_ext_info' value='$product_url_ext_info' class='inputfelt' />";
	$html .= check_formelement_display($check_formelement_display_fieldname, $check_formelement_display_fieldid, "URL_EXT_INFO", "end");


	$html .= check_formelement_display($check_formelement_display_fieldname, $check_formelement_display_fieldid, "URL_EXT_PRODUCTSHEET");
	$html .= "<h2>Link til produktark</h2>
					<input type='text' name='product_url_ext_productsheet' id='product_url_ext_productsheet' value='$product_url_ext_productsheet' class='inputfelt' />";
	$html .= check_formelement_display($check_formelement_display_fieldname, $check_formelement_display_fieldid, "URL_EXT_PRODUCTSHEET", "end");


	$html .= check_formelement_display($check_formelement_display_fieldname, $check_formelement_display_fieldid, "QUALITY_ID");
	$html .= "<h2>Kvalitet</h2>".productQualitySelector($product_qualityid);
	$html .= check_formelement_display($check_formelement_display_fieldname, $check_formelement_display_fieldid, "QUALITY_ID", "end");


	$html .= check_formelement_display($check_formelement_display_fieldname, $check_formelement_display_fieldid, "DIAMETER");
	$html .= "<h2>Diameter ($productDiameterUnit)</h2>
					<input type='text' name='product_diameter' id='product_diameter' value='$product_diameter' class='inputfelt' onblur='validateNumber(this, \"Diameter\")' />";
	$html .= check_formelement_display($check_formelement_display_fieldname, $check_formelement_display_fieldid, "DIAMETER", "end");

	$html .= check_formelement_display($check_formelement_display_fieldname, $check_formelement_display_fieldid, "LENGTH");
	$html .= "<h2>Længde ($productLengthUnit)</h2>
					<input type='text' name='product_length' id='product_length' value='$product_length' class='inputfelt' onblur='validateNumber(this, \"Længde\")' />";
	$html .= check_formelement_display($check_formelement_display_fieldname, $check_formelement_display_fieldid, "LENGTH", "end");

	$html .= "<h2>Pris (".$exchangeRates[da][FORKORTELSE].")</h2>
					<input type='text' name='product_price' id='product_price' value='$product_price' class='inputfelt' onblur='validateNumber(this, \"Pris\")' />";

	if ($product_show_related == "1") {
		$product_show_related_checked = "checked ";
	} else {
		$product_show_related_checked = "";
	}
	$html .= "<h2>Vis relaterede varer</h2>
				<p class='feltkommentar'>Du angiver relaterede varer vha. knappen 'Relaterede varer' i vareoversigten. Her kan du til/fravælge visning af de relaterede varer for den aktuelle vare i shoppen.</p>
				<input type='checkbox' name='product_show_related' id='product_show_related' value='1' $product_show_related_checked />&nbsp;Vis relaterede varer";
	$html .= "<h2>
				<div style='float:left;'>Meningsfuld side-adresse for varen:</div>
				<div id='ajaxloader_rewrite'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></div>
			</h2>";
	if (!is_numeric($productid)) {
		$html .= "<p class='feltkommentar'>Meningsfuld side-adresse kan først indstilles, når produktet er gemt!</p>";
	} else {
		$html .= "<input disabled type='text' id='rewrite_keyword' name='rewrite_keyword' class='inputfelt' value='";
		$html .= (is_numeric($productid) ? return_rewrite_keyword('', $productid, 'SHOP_PRODUCTS', $_SESSION[SELECTED_SITE]) : '');
		$html .= "' onblur='keyword_onblur(this.form.product_name.value, this.value, this.form.productid.value, \"SHOP_PRODUCTS\", $_SESSION[SELECTED_SITE])' />
			&nbsp;
			<input type='button' value='Ret'  class='inputfelt_kort' onclick='edit_keyword()' />
			<input type='button' value='Foreslå'  class='inputfelt_kort' onclick='if (edit_keyword()) suggest_rewrite_keyword(this.form.product_name.value, this.form.productid.value, \"SHOP_PRODUCTS\", $_SESSION[SELECTED_SITE])' />";
			
	}
	$html .= "<h2>Benyt varebillede?</h2>
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

	$html .= check_formelement_display($check_formelement_display_fieldname, $check_formelement_display_fieldid, "COLLI");
	// Check for user-specific price
	$userprice = false;
	$sql = "select count(*) from SHOP_USERPRICES where PRODUCT_ID = '$productid' limit 1";
	if ($res = mysql_query($sql)) {
		if (mysql_result($res,0)>0) {
			$userprice = true;
		} else {
			$userprice = false;
		}
	} else {
			$userprice = false;
	}
	if ($mode == "Rediger" && $userprice) {
		$html .= "<h2>Colli</h2>
				<p class='feltkommentar'>Der er angivet en eller flere brugerspecifikke priser på dette produkt. Derfor er det ikke muligt at angive colli. For at angive colli, slet først alle brugerspecifikke priser for varen.</p>";
	} else {
		$html .= "<h2>Colli</h2>
				<p class='feltkommentar'>Hvis dette produkt kun sælges i faste colli, kan du definere dem herunder. Definerer du ingen colli herunder, kan varen købes enkeltvis.</p>";
		$html .= productColliForm($productid);
	}
	$html .= check_formelement_display($check_formelement_display_fieldname, $check_formelement_display_fieldid, "COLLI", "end");


	$html .= "</div>"; // Feltblock wrapper
	$html .= "<div class='knapbar'>
					<input type='button' value='Afbryd' onclick='location=\"index.php?content_identifier=shopproducts&amp;groupid=$groupid\"' />
					<input type='button' value='Gem ændringer' onclick='saveProduct()' />
			</div>";

	$html .= "</form>";
	return $html;
}


function productColliForm($productid = "") {
	global $exchangeRates;
	$html .= "<table id='productColliTable' class='oversigt'>";
	$html .= "<tr class='trtop'>
				<td class='kolonnetitel'>
					Antal
				</td>
				<td class='kolonnetitel'>
					Rabat %
				</td>
				<td class='kolonnetitel'>
					Rabat pr. colli (".$exchangeRates[da][FORKORTELSE].")
				</td>
			  </tr>";
	$sql = "select * from SHOP_PRODUCTS_COLLI where PRODUCT_ID = '$productid' and DELETED = '0' order by QUANTITY asc";
	$res = mysql_query($sql);
	for ($i = 1; $i <= 5; $i++) {
		$row = mysql_fetch_array($res);
		if ($row[ID] == "") {
			$row[ID] = 0;
		}
		if ($row[QUANTITY] == 0) {
			$row[QUANTITY] = "";
		}
		if ($row[DISCOUNT_PERCENTAGE] == 0) {
			$row[DISCOUNT_PERCENTAGE] = "";
		}
		if ($row[DISCOUNT_AMOUNTPERCOLLI] == 0) {
			$row[DISCOUNT_AMOUNTPERCOLLI] = "";
		}
	
		// disable illegal input fields
		$disableDA = "";
		$disableDP = "";
		if ($row[DISCOUNT_PERCENTAGE] > 0 && $row[DISCOUNT_AMOUNTPERCOLLI] == "") {
			$disableDA = " disabled";
		}
		if ($row[DISCOUNT_AMOUNTPERCOLLI] > 0 && $row[DISCOUNT_PERCENTAGE] == "") {
			$disableDP = " disabled";
		}

		$html .= "<tr>
					<td>
						<input type='text' name='colliQuantity_".$i."_".$row[ID]."' id='colliQuantity_".$i."_".$row[ID]."' value='$row[QUANTITY]' class='inputfelt number' onblur='validateColli(\"colliQuantity\", $i, $row[ID], this.value)' />
					</td>
					<td>
						<input type='text' name='colliDiscPerc_".$i."_".$row[ID]."' id='colliDiscPerc_".$i."_".$row[ID]."' value='$row[DISCOUNT_PERCENTAGE]' class='inputfelt number' onblur='validateColli(\"colliDiscPerc\", $i, $row[ID], this.value)' $disableDP />
					</td>
					<td>
						<input type='text' name='colliDiscAbs_".$i."_".$row[ID]."' id='colliDiscAbs_".$i."_".$row[ID]."' value='$row[DISCOUNT_AMOUNTPERCOLLI]' class='inputfelt number' onblur='validateColli(\"colliDiscAbs\", $i, $row[ID], this.value)' $disableDA />
					</td>
				  </tr>";
	}
	$html .= "</table>";
	return $html;
}

function productQualitySelector($selected = "") {
	$html .= "<select name='productQualitySelector' id='productQualitySelector' class='inputfelt'>";
	$sql = 'select ID, NAME from SHOP_PRODUCTS_QUALITIES where DELETED = 0 order by NAME asc';	
	$result = mysql_query($sql) or die(mysql_error());
	$html .= "<option value='' ";
	if ($selected == "") {
		$html .= "selected";
	}
	$html .= " style=\"background-color:#ddd; color:#f00\">Vælg kvalitet...</option>";
	while ($row = mysql_fetch_array($result)) { 
		$displayname = $row[NAME];
		$html .= "<option value='$row[ID]'";
		if ($selected == $row[ID]) {
			$html .= " selected";
		}
		
		$html .= ">".$displayname.
				"</option>";
	}
	$html .= "</select>";
	return $html;
}


function productGroupSelector($selected = "", $noonchange = "") {
	if ($noonchange == "") {
	 $onchange =  "onchange='groupSelected(this);'";
	} else {
	 $onchange = $noonchange;
	}
	$html .= "<select id='productGroupSelector' class='inputfelt'$onchange>";
	$sql = "select ID, NUMBER, NAME from SHOP_PRODUCTGROUPS where SITE_ID = '$_SESSION[SELECTED_SITE]' and DELETED = 0 order by NUMBER asc";	
	$result = mysql_query($sql) or die(mysql_error());
	$html .= "<option value='' ";
	if ($selected == "") {
		$html .= "selected";
	}
	$html .= " style=\"background-color:#ddd; color:#f00\">Vælg varegruppe...</option>";

	while ($row = mysql_fetch_array($result)) { 
		$csql = "select count(SP.ID) from SHOP_PRODUCTS_GROUPS SPG, SHOP_PRODUCTS SP where SPG.PRODUCT_ID = SP.ID and SP.DELETED = 0 and SPG.GROUP_ID = '$row[ID]'";
		$cres = mysql_query($csql);
		$c = mysql_result($cres,0);



		$displayname = "$row[NUMBER] - $row[NAME] ($c)";	
		$html .= "<option value='$row[ID]'";
		if ($selected == $row[ID]) {
			$html .= " selected";
		}
		
		$html .= ">".$displayname.
				"</option>";
	}

/*
	$html .= "<option value='0'";
	if ($selected == "0") {
		$html .= "selected";
	}
	$html .= ">Vis alle varer</option>";
*/
	$html .= "</select>";

	return $html;
}

function return_products($groupid = "") {
	$html .= "<h1>Varekatalog</h1>";
	$html .= "<div class='feltblok_header'></div>";
	$html .= "<div class='feltblok_wrapper'>";
	$html .= "<h2>Vis varer i gruppen</h2>";
	$html .= productGroupSelector($groupid);

	if ($groupid == "") {
		return $html;
	}
	$selectedGroupName = returnFieldValue("SHOP_PRODUCTGROUPS", "NAME", "ID", $groupid);

	$sql = "select 
				SHOP_PRODUCTS.ID, SHOP_PRODUCTS.IMAGE_ID, SHOP_PRODUCTS.PRODUCT_NUMBER, SHOP_PRODUCTS.NAME, SHOP_PRODUCTS.DESCRIPTION, SHOP_PRODUCTGROUPS.IMAGE_ID as GROUPIMAGE_ID 
			from SHOP_PRODUCTS, SHOP_PRODUCTGROUPS, SHOP_PRODUCTS_GROUPS 
			where 
				SHOP_PRODUCTS.ID = SHOP_PRODUCTS_GROUPS.PRODUCT_ID and
				SHOP_PRODUCTS_GROUPS.GROUP_ID = SHOP_PRODUCTGROUPS.ID and
				SHOP_PRODUCTGROUPS.ID = '$groupid' and 
				SHOP_PRODUCTGROUPS.DELETED = '0' and
				SHOP_PRODUCTS.DELETED = '0' and
				SHOP_PRODUCTGROUPS.SITE_ID = '$_SESSION[SELECTED_SITE]'
			order by PRODUCT_NUMBER asc";
	$result = mysql_query($sql);

	if (!db_hasrows($result)) {
		$html .= "<p>Der er ingen varer i varegruppen!</p>";
	} else {
	$html .= "<div class='knapbar'>
					<input type='button' value='Opret ny vare' onclick='location=\"index.php?content_identifier=shopproducts&amp;dothis=opret&amp;groupid=$groupid\"' />
				</div>";
	$html .= "<h2>$selectedGroupName</h2>";
		$html .= "<table class='oversigt'>";
		$html .= "<tr class='trtop'>
					<td class='kolonnetitel' width='10%'>
						Billede
					</td>
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
		while ($row = mysql_fetch_array($result)) {
			if ($row[IMAGE_ID] > 0) {
				$image = returnImageThumbUrl($row[IMAGE_ID]);
				$image = "<img src='$image' alt='$row[NAME]' />";
			} elseif ($row[GROUPIMAGE_ID] > 0) {
				$image = returnImageThumbUrl($row[GROUPIMAGE_ID]);
				$image = "<img src='$image' alt='$row[NAME]' /><div class='noimage'>(gruppebillede)</div>";
			} else {
				$image = "<div class='noimage'>Rediger for at tilføje billede</div>";
			}
			$html .= "<tr class='oversigt2' onmouseover='IEColorShift(this.id)' onmouseout='IEColorUnShift(this.id, 2)' id='product_$row[ID]'>
					<td class='imagetd'>$image</td>
					<td>$row[PRODUCT_NUMBER]</td>
					<td>$row[NAME]</td>
					<td>$row[DESCRIPTION]</td>
					<td>
						<input type='button' class='lilleknap' value='Rediger' onclick='location=\"index.php?content_identifier=shopproducts&amp;dothis=rediger&amp;id=$row[ID]&amp;groupid=$groupid\"' />
						<input type='button' class='lilleknap' value='Slet' onclick='productDelete($row[ID],$groupid)'$delete />
						<input type='button' class='lilleknap' value='Relaterede varer' onclick='relatedProducts($row[ID], 0)' />
					</td>
				</tr>";
		}
		$html .= "</table>";
	}
	$html .= "</div>";
	$html .= "<div class='knapbar'>
					<input type='button' value='Opret ny vare' onclick='location=\"index.php?content_identifier=shopproducts&amp;dothis=opret&amp;groupid=$groupid\"' />
				</div>";
	
	return $html;
}

function related_products($product_id, $group_id=false){
	$html .= "<h1>Relaterede varer for ".($product_id ? "varen \"".returnFieldValue("SHOP_PRODUCTS", "NAME", "ID", $product_id) : "varegruppen \"".returnFieldValue("SHOP_PRODUCTGROUPS", "NAME", "ID", $group_id))."\"</h1>";
	$current_rels = already_related($product_id, $group_id);
	$html .= "<form method='post'>
		<input type='hidden' name='this_product_id' value='$product_id' />
		<input type='hidden' name='this_group_id' value='$group_id' />
		<input type='hidden' name='dothis' value='' />
		<div class='feltblok_header'>Tilføj og fjern relaterede varer her</div>
		<div class='feltblok_wrapper'>
			<h2>Allerede relaterede varer</h2><br/>
			".$current_rels["HTML"]."
			<div class='knapbar'>
				<input type='button' value='Fjern afkrydsede varer' onclick='removeRelations($product_id)' />
			</div>
			<h2>Tilføj nye relaterede varer</h2>
			".productGroupSelector($_GET[fromgroupid], "onchange='".($product_id ? "showPossibleRelations($product_id, 0, this.value)" : "showPossibleRelations(0, $group_id, this.value)")."'")."
			";
	if ($_GET[fromgroupid]){
		$html .= "
			<p class='feltkommentar'>Sæt kryds i de varer, du ønsker at relatere, og tryk derefter på knappen \"Tilføj relaterede varer\".</p>
			<p>
		";
		$sql = "
			select 
				SP.NAME, SP.PRODUCT_NUMBER, SP.ID as PRODUCT_ID
			from 
				SHOP_PRODUCTS SP, SHOP_PRODUCTS_GROUPS SPG
			where
				SP.ID=SPG.PRODUCT_ID and 
				SP.DELETED = '0' and 
				SPG.GROUP_ID='$_GET[fromgroupid]' and 
				SP.ID != '$product_id' 
				".(count($current_rels["ARR_RELATED"]) > 0 ? " and 
				SP.ID not in (".implode(", ", $current_rels["ARR_RELATED"]).")" : "")."
			order by
				SP.PRODUCT_NUMBER asc
		";
		$res = mysql_query($sql);
		$html .= "<table border='1' width='50%'>
			<tr>
				<th bgcolor='#cccccc'>&nbsp;</th>
				<th>Varenr.</th>
				<th>Varenavn</th>
			</tr>
		";
		while ($row = mysql_fetch_assoc($res)){
			$html .= "<tr><td><input type='checkbox' name='addrelation_".$row[PRODUCT_ID]."' /></td><td>$row[PRODUCT_NUMBER]</td><td>$row[NAME]</td></tr>";
		}
		$html .= "</table>";
	}
	$html .= "</p>";
	$html .= "<div class='knapbar'>
					<input type='button' value='Tilføj relaterede varer' onclick='addRelations()' />
				</div>
		</div>
		</form>
	";
	return $html;
}

function already_related($product_id, $group_id=false){
	$sql = "
		select 
			SPR.RELATED_ITEM_ID, SP.NAME, SP.PRODUCT_NUMBER, SPR.ID as RELATION_ID, SP.ID as PRODUCT_ID
		from 
			SHOP_RELATED_PRODUCTS SPR, SHOP_PRODUCTS SP
		where 
			".($product_id ? "SPR.ITEM_ID='$product_id'" : "SPR.GROUP_ID='$group_id'")." and SP.ID=SPR.RELATED_ITEM_ID and SP.DELETED = '0'
	";
	$res = mysql_query($sql);
	if (mysql_num_rows($res) == 0){
		if ($group_id) return array("HTML" => "Endnu ingen relaterede varer for denne varegruppe. Der kan dog indirekte være relateret varer via varegrupper på et højere niveau.", "ARR_RELATED" => array());
		if ($product_id) return array("HTML" => "Endnu ingen relaterede varer for denne vare. Der kan dog indirekte være relateret varer via den/de varegrupper, som varen tilhører.", "ARR_RELATED" => array());
	}
	$html .= "<table border='1' width='50%'>
		<tr>
			<th bgcolor='#cccccc'>&nbsp;</th>
			<th>Varenr.</th>
			<th>Varenavn</th>
		</tr>
	";
	$arr_related = array();
	while ($row = mysql_fetch_assoc($res)){
		$arr_related[] = $row[PRODUCT_ID]; 
		$html .= "<tr><td><input type='checkbox' name='removerelation_".$row[RELATION_ID]."' /></td><td>$row[PRODUCT_NUMBER]</td><td>$row[NAME]</td></tr>";
	}
	$html .= "</table>";
	return array("HTML" => $html, "ARR_RELATED" => $arr_related);
}

function db_removerelations($POSTVARS){
	foreach ($POSTVARS as $k => $v){
		$temp = explode("_", $k);
		if ($temp[0] == "removerelation"){
			$sql = "delete from SHOP_RELATED_PRODUCTS where ID='$temp[1]' limit 1";
			mysql_query($sql);
		}
	}
}

function db_addrelations($POSTVARS){
	foreach ($POSTVARS as $k => $v){
		$temp = explode("_", $k);
		if ($temp[0] == "addrelation"){
			$sql = "
				insert into SHOP_RELATED_PRODUCTS(
					GROUP_ID, ITEM_ID, RELATED_ITEM_ID
				) values (
					'".($_POST[this_group_id] ? $_POST[this_group_id] : "0")."', '".($_POST[this_product_id] ? $_POST[this_product_id] : "0")."', '".$temp[1]."'
				)
			";
			mysql_query($sql);
		}
	}
}


?>