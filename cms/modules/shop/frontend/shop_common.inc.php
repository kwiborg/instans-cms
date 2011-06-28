<?php
include_once($_SERVER[DOCUMENT_ROOT]."/cms/frontend/frontend_common.inc.php");

function returnCachedImageUrl($image_url, $thumb) {
	global $shopimagesdirRel, $shopimagesFolderPath;
	if ($image_url == "") {
		return $image_url;
	}
	$iarr = explode("/" , $image_url);
	$iarr = array_reverse($iarr);
	$shopimage = $iarr[0];
	$shopimage_url = $shopimagesdirRel;
	if ($thumb == "1") {
		$shopimage_url .= "/thumbs";
	}
	$shopimage_url .= 	"/$shopimage";
	if (!file_exists($shopimage_url)) {
		generateShopImage($image_url, $thumb);
	}
	$newurl = "$shopimagesFolderPath";
	if ($thumb == "1") {
		$newurl .= 	"/thumbs";
	}

	$newurl .= 	"/$shopimage";
	return $newurl;
}

function generateShopImage($originalimage_url, $thumbnail) {
	global $shopimagesFolderPath, $shopimagesdirRel, $shopImageWidth, $shopThumbimageWidth; 
	if ($thumbnail == "1") {
		$newsize = $shopThumbimageWidth;
		$targetDir = $shopimagesdirRel."/thumbs";
	} else {
		$newsize = $shopImageWidth;
		$targetDir = $shopimagesdirRel;
	}

	$iarr = explode("/" , $originalimage_url);
	$iarr = array_reverse($iarr);
 	$filename = $iarr[0];
	$pic = $originalimage_url;
	$info = getimagesize($pic);
	$type = $info[2];
	$p = $info[0]/$info[1];
	if ($p >= 1) {
		$thumbwidth  = (($info[0]*0.1 < $newsize) ? $newsize : $newsize);
		$thumbheight = round($thumbwidth/$p);
	}
 	if ($p < 1) {

		$thumbheight  = (($info[1]*0.1 < $newsize) ? $newsize : $newsize);
		$thumbwidth = round($thumbheight*$p);
	}
 if ($type == 1) {
	if (array_search("imagegif", get_extension_funcs("gd"))) { // Checks if gdlib supports gif (above version 2.0.28)
		$new_image_placeholder = imagecreate($thumbwidth, $thumbheight);
		$new_image = imagecreatefromgif($pic);
		imagecopyresized($new_image_placeholder, $new_image, 0, 0, 0, 0, $thumbwidth, $thumbheight, $info[0], $info[1]);
		$thumbfile = "$targetDir/$filename";
		imagegif($new_image_placeholder, $thumbfile);
	} else {
		copy("$originalimage_url", "$targetDir/$filename");
	}
 }
 if ($type == 2) {
  $new_image_placeholder = imagecreatetruecolor($thumbwidth, $thumbheight);
  $new_image = imagecreatefromjpeg($pic);
  imagecopyresampled($new_image_placeholder, $new_image, 0, 0, 0, 0, $thumbwidth, $thumbheight, $info[0], $info[1]);
  $thumbfile = "$targetDir/$filename";
  imagejpeg($new_image_placeholder, $thumbfile, 100);
 } 
 if ($type == 3) {
  $new_image_placeholder = imagecreatetruecolor($thumbwidth, $thumbheight);
  $new_image = imagecreatefrompng($pic);
  imagecopyresampled($new_image_placeholder, $new_image, 0, 0, 0, 0, $thumbwidth, $thumbheight, $info[0], $info[1]);
  $thumbfile = "$targetDir/$filename";
  imagepng($new_image_placeholder, $thumbfile);
 } 
}

function showGroups($arr_content) {
	// Determine which group to show (0 means show list of groups)
	if (isset($arr_content[group])) {
		$groupid = $arr_content[group];
	} else {
		$groupid = 0;
	}
	$groupid = db_safedata($groupid);

	// Check if $groupid is public / deleted
	if (is_numeric($groupid) && $groupid > 0) {
		$published = returnFieldValue("SHOP_PRODUCTGROUPS", "PUBLISHED", "ID", $groupid);
		$deleted = returnFieldValue("SHOP_PRODUCTGROUPS", "DELETED", "ID", $groupid);
		if ($published == "0" || $deleted == "1") {
			return "";
		}
	}

	// Get subgroups, if any
	$has_subgroups = hasSubgroups($groupid, $arr_content);
	
	// Make headlinebar
	if (is_numeric($groupid) && $groupid > 0 && $has_subgroups) {
		$parent_name = returnFieldValue("SHOP_PRODUCTGROUPS", "NAME", "ID", "$groupid");
		$html .= "<div class='headlinebar'>
						".cmsTranslate("shopGroupSpecific")." '$parent_name' ".cmsTranslate("shopGroupHasSubgroups")."
					</div>";
	} elseif (($groupid == "0" || $groupid == "") && $has_subgroups) {
		$html .= "<div class='headlinebar'>
						".cmsTranslate("shopGroupSelect")."
					</div>";
	} elseif (($groupid == "0" || $groupid == "") && !$has_subgroups) {
		$html .= "<div class='headlinebar'>
						".cmsTranslate("shopGroupNone")."
					</div>";
	}

	// Show subgroups, if any
	if ($has_subgroups) {
		$HideNoProductsBar = true; // Don't show no-products message when there are subgroups!
		$sql = "select ID, NUMBER, NAME, DESCRIPTION, IMAGE_ID
				from SHOP_PRODUCTGROUPS 
				where PARENT_ID = '$groupid'
				and PUBLISHED = '1'
				and DELETED = '0'
				and SITE_ID in ('0','$arr_content[site]')
				order by NUMBER";
		$subgroups = mysql_query($sql);
		while($row = mysql_fetch_array($subgroups)) {
			$csql = "select count(*) from SHOP_PRODUCTS_GROUPS SPG, SHOP_PRODUCTS SP where SPG.GROUP_ID = '$row[ID]' and SPG.PRODUCT_ID = SP.ID and SP.DELETED = '0'";
			$cres = mysql_query($csql);
			$number_of_products = mysql_result($cres,0);
			$link_showgroup = "$arr_content[baseurl]/index.php?mode=shop&amp;action=showgroup&amp;group=$row[ID]";

			$html .= "<div class='group'>
						<table cellpadding='0' cellspacing='0'>
							<tr>";
			if ($row[IMAGE_ID] > 0) {
				$image_url = returnImageUrl($row[IMAGE_ID]);
				$thumbimage_url = returnCachedImageUrl($image_url, "1");

				$html .=	"<td>
								<a href='$link_showgroup'><img src='$thumbimage_url' class='groupimage' alt='$row[NAME]' /></a>
							</td>";
			}
			$html .= 		"<td>
									<h1 class='groupname'><a href='$link_showgroup'>$row[NAME]</a></h1>
									<p class='groupdetailline'>".cmsTranslate("shopGroupNumber")." $row[NUMBER], $number_of_products";
			if ($number_of_products == 1) {
				$html .= " ".cmsTranslate("shopProduct");
			} else {
				$html .= " ".cmsTranslate("shopProducts");
			}
			$html .= " ".cmsTranslate("shopInGroup")."</p>";
			if ($row[DESCRIPTION] != "") {
				$html .=			"<p class='groupdesc'><a href='$link_showgroup'>$row[DESCRIPTION]</a></p>";
			}
			$html .=			"</td>
							</tr>
						</table>
						<div class='groupviewproducts'>
							<a href='$link_showgroup'>".cmsTranslate("shopGroupViewAll")." '$row[NAME]' ($number_of_products ".cmsTranslate("CartUnits").")</a>
						</div>
					</div>";
		}
	}
	// Finally show products in group
	$html .= showProducts($arr_content, $groupid, $HideNoProductsBar);
	return $html;
}


function showProducts($arr_content, $groupid, $HideNoProductsBar = true, $productnumber = "") {
	// Global vars from cms_config
	global $shopPermissions, $productLengthUnit, $productDiameterUnit, $exchangeRates, $shopProductsBuySetQuantity, $shopProductsOrderBy;
	
	// Check if $groupid is public / deleted
	if (is_numeric($groupid)) {
		$published = returnFieldValue("SHOP_PRODUCTGROUPS", "PUBLISHED", "ID", $groupid);
		$deleted = returnFieldValue("SHOP_PRODUCTGROUPS", "DELETED", "ID", $groupid);
		if ($published == "0" || $deleted == "1") {
			return "";
		}
	}
	
	// Which kind of product list to make
//	$listmode = 1; // Show products on a list with just number, name and price
//	$listmode = 2; // Show products on a list with image, short description, number, name and price (default)
//	$listmode = 3; // Show products on a list with all available information
	$listmode = returnFieldValue("SHOP_PRODUCTGROUPS", "LISTMODE", "ID", $groupid);

	// Display only single product?
//	if ($productnumber != "" and $groupid== "") {
//	if (is_numeric($arr_content[product])) { NO! product is not the ID, but PRODUCT_NUMBER (remember?) so non numeric values allowed...
	if ($arr_content[product] != "") {
		$internal_mode = "singleproduct";

		// If no $groupid given, default to $arr_content[group] // $_GET
		if (!is_numeric($groupid)) {
			$groupid = $arr_content[group];
		}
			
		// Override listmode, always display full information on singleproduct
		$listmode = 3;
	}

	// Build sql
	$sql = "select SHOP_PRODUCTS.ID, 
			SHOP_PRODUCTS.IMAGE_ID, 
			SHOP_PRODUCTS.PRODUCT_NUMBER, 
			SHOP_PRODUCTS.ALT_PRODUCT_NUMBER,
			SHOP_PRODUCTS.NAME, 
			SHOP_PRODUCTS.DESCRIPTION, 
			SHOP_PRODUCTS.DIAMETER, 
			SHOP_PRODUCTS.LENGTH, 
			SHOP_PRODUCTS.PRICE, 
			SHOP_PRODUCTS.DESCRIPTION_COMPLETE, 
			SHOP_PRODUCTS.URL_EXT_INFO, 
			SHOP_PRODUCTS.URL_EXT_PRODUCTSHEET, 
			SHOP_PRODUCTGROUPS.IMAGE_ID as GROUPIMAGE_ID, 
			SHOP_PRODUCTGROUPS.NAME as GROUPNAME, 
			SHOP_PRODUCTGROUPS.ID as GROUP_ID, 
			SHOP_PRODUCTS_QUALITIES.NAME as QUALITY
			from 
			SHOP_PRODUCTS, SHOP_PRODUCTGROUPS, SHOP_PRODUCTS_GROUPS SPG
			left join SHOP_PRODUCTS_QUALITIES
			on SHOP_PRODUCTS.QUALITY_ID = SHOP_PRODUCTS_QUALITIES.ID ";
		if ($internal_mode == "singleproduct") {
			$sql .= "where SHOP_PRODUCTS.PRODUCT_NUMBER = '$productnumber' and SPG.GROUP_ID = '$groupid' ";
		} else {
			$sql .= "where SPG.GROUP_ID = '$groupid' ";
		}					
	$sql .= "
			and SHOP_PRODUCTS.ID = SPG.PRODUCT_ID
			and SPG.GROUP_ID = SHOP_PRODUCTGROUPS.ID	
			and SHOP_PRODUCTS.DELETED = '0' 
			and SHOP_PRODUCTGROUPS.PUBLISHED = '1' 
			order by $shopProductsOrderBy";
	$result = mysql_query($sql);

	// Output product header bar
	if (mysql_num_rows($result) > 0) {
		$row = mysql_fetch_array($result);

		if (!$internal_mode == "singleproduct") {
			$html .= "<div class='headlinebar'>
					".cmsTranslate("shopProductsInGroup")." '$row[GROUPNAME]'
				</div>";
		} else {
			$groupname = $row[GROUPNAME];
		}

		// Which product formfields to show?
		// Product belongs to these groups
		$gsql = "select GROUP_ID from SHOP_PRODUCTS_GROUPS where PRODUCT_ID = '$row[ID]'";
		$gres = mysql_query($gsql);
		while ($grow = mysql_fetch_assoc($gres)) {
			$arr_groupids[] = $grow[GROUP_ID];
		}
		$str_groupids = implode($arr_groupids, ",");

		$arr_formfields_checked = array();
//		$sql = "select FIELDNAME from SHOP_PRODUCTGROUPS_FORMFIELDS where PRODUCTGROUP_ID = $row[GROUP_ID]";
		$sql = "select distinct FIELDNAME from SHOP_PRODUCTGROUPS_FORMFIELDS where PRODUCTGROUP_ID in ($str_groupids)";
		$res = mysql_query($sql);
		while ($row = mysql_fetch_array($res)) {
			$arr_formfields_checked[] = $row[FIELDNAME];
		}

		mysql_data_seek($result, 0);
/*
	} else {
		if (!$HideNoProductsBar) {
			$parent_name = returnFieldValue("SHOP_PRODUCTGROUPS", "NAME", "ID", "$groupid");
			$html .= "<div class='headlinebar'>
					".cmsTranslate("shopProductNone")." '$parent_name'
				</div>";
			return $html;
		}
	}
*/

	// List products
		switch ($listmode) {
			case 1:
				$html .= "<div class='product' id='product_$row[ID]'>
									<table class='listtable' cellpadding='0' cellspacing='0'>
										<tr>
											<th>".cmsTranslate("shopProductNumber")."</th>
											<th>".cmsTranslate("shopProductName")."</th>
											<th class='number'>".cmsTranslate("shopProductPrice").", ".$exchangeRates[da][FORKORTELSE].".</th>
											<th>&nbsp;</th>";
				$i = 1;
				while ($row = mysql_fetch_array($result)) {
					$i++;
					if (($i%2)>0) {
						$tr_altrow = " class='altrow'";
					} else {
						$tr_altrow = "";
					}
					$html .= "<tr$tr_altrow>
											<td>$row[PRODUCT_NUMBER]</td>
											<td><a href='$arr_content[baseurl]/index.php?mode=shop&amp;action=showproduct&amp;group=$groupid&amp;product=".urlencode($row[PRODUCT_NUMBER])."' title='".cmsTranslate("shopProductMoreInfo").": $row[NAME]'>$row[NAME]</a></td>";
					// Show colli?
					$colsql = "select ID, QUANTITY, DISCOUNT_PERCENTAGE, DISCOUNT_AMOUNTPERCOLLI
								from SHOP_PRODUCTS_COLLI
								where PRODUCT_ID = $row[ID]
								and DELETED = '0'
								order by QUANTITY ASC";
					$colres = mysql_query($colsql);
					$colnumber = mysql_num_rows($colres);
					if ($colnumber == 0) {
						$showprice = true;
						$showcolli = false;
					} else {	
						$showprice = false;
						$showcolli = true;
					}
					$showrestriction = false; // defaults
					$showbuybutton   = true;
					// Can user see price/order etc - determine restrictions and appropriate message
					if ($shopPermissions[prices] != "") {
						if (!checkPermissionFE($shopPermissions[prices], $_SESSION[USERDETAILS][2], false)) {
							// Bruger MANGLER rettighed
							if (!$_SESSION[LOGGED_IN]) {
								// For han er ikke logget ind 
								$restrictmessage = "<a href='$arr_content[baseurl]/index.php?mode=login&amp;redirect=referer'>".cmsTranslate("Login")."</a>"; //.cmsTranslate("shopToSeePrices");
							} else {
								$restrictmessage = cmsTranslate("shopRestrictPricesShort");
							}
							$showprice = false;
							$showcolli = false;
							$showrestriction = true;
						}
						if ($shopPermissions[buy] != "") {
							if (!checkPermissionFE($shopPermissions[buy], $_SESSION[USERDETAILS][2], false)) {
								// Bruger MANGLER rettighed
								if (!$showrestriction) {
									// Det er den første restriction, som passer - derfor start 
									if (!$_SESSION[LOGGED_IN]) {
										// For han er ikke logget ind 
										$restrictmessage = "<a href='$arr_content[baseurl]/index.php?mode=login&amp;redirect=referer'>".cmsTranslate("Login")."</a> ".cmsTranslate("shopToBuy")."";
									} else {
										$restrictmessage = cmsTranslate("shopRestrictBuy");
									}
								} else {
//									$restrictmessage .= " ".cmsTranslate("shopAndOrder");
								}
								$showrestriction = true;
								$showbuybutton = false;
							}
						}
						if ($restrictmessage != "") {
							$restrictmessage .= "";
						}
					}			
		
					// Get group discount
					if ($_SESSION[LOGGED_IN]) {
						// Check for user specific price
						if ($userprice = returnUserPrice($_SESSION[USERDETAILS][0][ID], $row["ID"])) {
							$row[PRICE] = $userprice;
							unset($discount_percentage);
							unset($discount_factor);
						} else {
							// No userprice, check for group discount
//							$discount_percentage = returnUserDiscount($_SESSION[USERDETAILS][0][ID]);
							$discount_percentage = returnDiscountPercentage($_SESSION[USERDETAILS][0][ID], $row["ID"], $arr_content);
							$discount_factor = (100-$discount_percentage)/100;
						}
					}							
					// Start price-td		
					$html .=				"<td class='number'>";	
					if ($showprice) {
						// Apply group discount if applicable
						if ($discount_factor > 0) {
							$row[PRICE] = $row[PRICE] * $discount_factor;
						}
						
						$html .= number_format($row[PRICE],2,cmsTranslate("CartDecimalPoint"),cmsTranslate("CartThousandsSeperator"));
					}	
					if ($showrestriction) {
						$html .= $restrictmessage;
					}
					if ($showcolli) {
						$html .= "<form class='listform' id='productform_info_$row[ID]' action='$arr_content[baseurl]/index.php?mode=shop&amp;action=showproduct&amp;group=$groupid&amp;product=".urlencode($row[PRODUCT_NUMBER])."' method='post'><input type='submit' value='".cmsTranslate("shopChooseColli")."' /></form>";
					}
					
					// End price-td		
					$html .= "</td><td class='number'>";
					
					$html .= "<form class='listform' id='productform_info_$row[ID]' action='$arr_content[baseurl]/index.php?mode=shop&amp;action=showproduct&amp;group=$groupid&amp;product=".urlencode($row[PRODUCT_NUMBER])."' method='post'><input type='submit' value='".cmsTranslate("shopProductMoreInfo")."' /></form>";

					$fromPage = htmlentities("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
					if ($showbuybutton) {
						$html .= "<form class='listform' id='productform_$row[ID]' action='$arr_content[baseurl]/index.php?mode=cart&amp;action=addtocart' method='post'>";
						if ($showcolli) {
							$buybuttondisabled = "disabled ";
						} else {
							$buybuttondisabled = "";
						}
						$html .= "		<input type='hidden' name='productId' value='$row[ID]' />
										<input type='hidden' name='productAmount' value='1' />
										<input type='hidden' name='PTN' value='SHOP_PRODUCTS' />
										<input type='hidden' name='fromPage' value='$fromPage' />
										<input type='submit' $buybuttondisabled value='".cmsTranslate("shopAddToBasket")."' />
									</form>";
					}

					
					$html ."</td></tr>";
				}
				$html .= "</table></div>";				
				break;
/*
			case 2:
				while ($row = mysql_fetch_array($result)) {
					$html .= "listmode 2";
				}			
				break;
			case 3:
*/
			default: // Covers listmode 2 and 3 since the difference is very small
				while ($row = mysql_fetch_array($result)) {
					$html .= "<div class='product' id='product_$row[ID]'>
								<form id='productform_$row[ID]' action='$arr_content[baseurl]/index.php?mode=cart&amp;action=addtocart' method='post'>
									<table cellpadding='0' cellspacing='0' class='shop_product_maintable'>
										<tr>";
				// Show image
				$image_url = "";
				$image_comment = "";
				if ($row[IMAGE_ID] > 0) {
					$image_url = returnImageUrl($row[IMAGE_ID]);
					$usegroupImage = false;
				} elseif ($row[GROUPIMAGE_ID] > 0) {
					$image_url = returnImageUrl($row[GROUPIMAGE_ID]);
					$usegroupImage = true;
				}
		
				if ($image_url != "") {
					$thumbimage_url = returnCachedImageUrl($image_url, "1");
					$largeimage_url = returnCachedImageUrl($image_url, "0");
		
					// Get size of large image
					$info = getimagesize($largeimage_url);
		
					$html .= 		"<td>";
					if (!$usegroupImage) {
						$html .= 			"<a href='$largeimage_url' class='shopproductlink $info[0]x$info[1]' title='$row[NAME]' >"; 
					}
					$html .= 			"<img src='$thumbimage_url' class='productimage' alt='$row[NAME]' />";
					if (!$usegroupImage) {
						$html .= 			"</a>";
					} else {	
						$html .=			"<div class='productimagecomment'>(".cmsTranslate("shopGroupImage").")</div>";
					}
					$html .= 		"</td>";
				}
					$html .= 		"<td>
										<h1 class='productname'>$row[NAME]</h1>
										<p class='productdetailline'>".cmsTranslate("shopProductNumber")." $row[PRODUCT_NUMBER] - ".cmsTranslate("shopGroupShort")." ";
					if ($internal_mode == "singleproduct") {
						$html .=			"<a href='$arr_content[baseurl]/index.php?mode=shop&action=showgroup&group=$row[GROUP_ID]' title='".cmsTranslate("shopGroupViewAll")." \"$row[GROUPNAME]\"'>$row[GROUPNAME]</a>";
					} else {
						$html .=			"'$row[GROUPNAME]'";
					}
					$html .=			"</p>";
					
					// Display description
					if ($listmode == 2) {
						$html .= 		"<p class='productdesc'>".nl2br($row[DESCRIPTION]);
						$html .= 		"</p>";
						if ($row[DESCRIPTION_COMPLETE] != "") {
							$html .=		"<p class=''><a href='$arr_content[baseurl]/index.php?mode=shop&action=showproduct&amp;group=$groupid&product=".urlencode($row[PRODUCT_NUMBER])."' title='$row[NAME]'>".cmsTranslate("shopLabelMoreInfo")."</a></p>";
						}
					} elseif ($listmode == 3) {
						if ($row[DESCRIPTION_COMPLETE] != "") {
							$html .= 		"<p class='productdesc'>$row[DESCRIPTION_COMPLETE]</p>";
						} else {
							$html .= 		"<p class='productdesc'>".nl2br($row[DESCRIPTION])."</p>";
						}
					}

					// Display URL_EXT_PRODUCTSHEET
					if (in_array("URL_EXT_PRODUCTSHEET", $arr_formfields_checked)) {
						if ($row[URL_EXT_PRODUCTSHEET] != "" && $row[URL_EXT_PRODUCTSHEET] != "0") {
							if (!strstr($row[URL_EXT_PRODUCTSHEET], "http://") && !strstr($row[URL_EXT_PRODUCTSHEET], "https://")){
								$row[URL_EXT_PRODUCTSHEET] = "http://".$row[URL_EXT_PRODUCTSHEET];
							}
						$html .= 				"<p class='productlink'>
														<a href='$row[URL_EXT_PRODUCTSHEET]' title='".cmsTranslate("shopLabelUrlExtSheet")."'>".cmsTranslate("shopLabelUrlExtSheet")."</a>
												</p>";
						}
					}
					
					// Display URL_EXT_INFO
					if (in_array("URL_EXT_INFO", $arr_formfields_checked)) {
						if ($row[URL_EXT_INFO] != "" && $row[URL_EXT_INFO] != "0") {
							if (!strstr($row[URL_EXT_INFO], "http://") && !strstr($row[URL_EXT_INFO], "https://")){
								$row[URL_EXT_INFO] = "http://".$row[URL_EXT_INFO];
							}
						$html .= 				"<p class='productlink'>
														<a href='$row[URL_EXT_INFO]' title='".cmsTranslate("shopLabelUrlExt")."' target='_blank'>".cmsTranslate("shopLabelUrlExt")."</a>
												</p>";
						}
					}

					// Begin extravalues table
					$html .= 			"<table cellpadding='0' cellspacing='0' class='shop_extravalues_table'>";
					

					// Display QUALITY
					if (in_array("QUALITY_ID", $arr_formfields_checked)) {
						if ($row[QUALITY] != "" && $row[QUALITY] != "0") {
							$html .=			"<tr>
													<td class='productextralabel'>
														".cmsTranslate("shopLabelQuality")."
													</td>
													<td class='productextravalue'>
														$row[QUALITY]
													</td>
												</tr>";
						}
					}					
					
					// Display DIAMETER
					if (in_array("DIAMETER", $arr_formfields_checked)) {
						if ($row[DIAMETER] != "" && $row[DIAMETER] != "0") {
						$html .= 				"<tr>
													<td class='productextralabel'>
														".cmsTranslate("shopLabelDiameter")."
													</td>
													<td class='productextravalue number'>
														$row[DIAMETER] $productDiameterUnit
													</td>
												</tr>";
						}
					}
					
					// Display LENTGH
					if (in_array("LENGTH", $arr_formfields_checked)) {
						if ($row[LENGTH] != "" && $row[LENGTH] != "0") {
						$html .= 				"<tr>
													<td class='productextralabel'>
														".cmsTranslate("shopLabelLength")."
													</td>
													<td class='productextravalue number'>
														$row[LENGTH] $productLengthUnit
													</td>
												</tr>";
						}
					}

					// Display ALT_PRODUCT_NUMBER
					if (in_array("ALT_PRODUCT_NUMBER", $arr_formfields_checked)) {
						if ($row[ALT_PRODUCT_NUMBER] != "" && $row[ALT_PRODUCT_NUMBER] != "0") {
						$html .= 				"<tr>
													<td class='productextralabel'>
														".cmsTranslate("shopLabelAltNumber")."
													</td>
													<td class='productextravalue number'>
														$row[ALT_PRODUCT_NUMBER]
													</td>
												</tr>";
						}
					}

					// Display COLLI (always displayed if values defined - disregards $arr_formfields_checked)
					$colsql = "select ID, QUANTITY, DISCOUNT_PERCENTAGE, DISCOUNT_AMOUNTPERCOLLI
								from SHOP_PRODUCTS_COLLI
								where PRODUCT_ID = $row[ID]
								and DELETED = '0'
								order by QUANTITY ASC";
					$colres = mysql_query($colsql);
					$colnumber = mysql_num_rows($colres);
					if ($colnumber == 0) {
						$showprice = true;
						$showcolli = false;
					} else {	
						$showprice = false;
						$showcolli = true;
					}
					$showrestriction = false; // defaults
					$showbuybutton   = true;
					if ($shopPermissions[prices] != "") {
						if (!checkPermissionFE($shopPermissions[prices], $_SESSION[USERDETAILS][2], false)) {
							// Bruger MANGLER rettighed
							if (!$_SESSION[LOGGED_IN]) {
								// For han er ikke logget ind 
								$restrictmessage = "<a href='$arr_content[baseurl]/index.php?mode=login&amp;redirect=referer'>".cmsTranslate("Login")."</a> ".cmsTranslate("shopToSeePrices");
							} else {
								$restrictmessage = cmsTranslate("shopRestrictPrices");
							}
							$showprice = false;
							$showcolli = false;
							$showrestriction = true;
						}
						if ($shopPermissions[buy] != "") {
							if (!checkPermissionFE($shopPermissions[buy], $_SESSION[USERDETAILS][2], false)) {
								// Bruger MANGLER rettighed
								if (!$showrestriction) {
									// Det er den første restriction, som passer - derfor start 
									if (!$_SESSION[LOGGED_IN]) {
										// For han er ikke logget ind 
										$restrictmessage = "<a href='$arr_content[baseurl]/index.php?mode=login&amp;redirect=referer'>".cmsTranslate("Login")."</a> ".cmsTranslate("shopToBuy")."";
									} else {
										$restrictmessage = cmsTranslate("shopRestrictBuy");
									}
								} else {
									$restrictmessage .= " ".cmsTranslate("shopAndOrder");
								}
								$showrestriction = true;
								$showbuybutton = false;
							}
						}
						if ($restrictmessage != "") {
							$restrictmessage .= "";
						}
					}			
		
					// Get group discount
					if ($_SESSION[LOGGED_IN]) {
						// Check for user specific price
						if ($userprice = returnUserPrice($_SESSION[USERDETAILS][0][ID], $row["ID"])) {
							$row[PRICE] = $userprice;
						} else {
							// No userprice, check for group discount
//							$discount_percentage = returnUserDiscount($_SESSION[USERDETAILS][0][ID]);
							$discount_percentage = returnDiscountPercentage($_SESSION[USERDETAILS][0][ID], $row["ID"], $arr_content);
							$discount_factor = (100-$discount_percentage)/100;
						}							
					}
					if ($showprice) {
						if ($discount_factor > 0) {
							$row[PRICE] = $row[PRICE] * $discount_factor;
						}
						$html .= 			"<tr>
												<td class='productextralabel'>
													Pris
												</td>
												<td class='productextravalue number'>
													".$exchangeRates[da][FORKORTELSE].". ".number_format($row[PRICE],2,cmsTranslate("CartDecimalPoint"),cmsTranslate("CartThousandsSeperator"))."
												</td>
											</tr>";
					}
					if ($showcolli) {
						$html .= 			"<tr>
												<td class='productextralabel' colspan='2'>
													".cmsTranslate("shopDeliveredColli")."
												</td>
											</tr>";
						$i =0;
						while ($colrow = mysql_fetch_array($colres)) {		
							$i++;
							// Calculate colli price and discounts
							$colprice = "";
							$colunitprice = "";
							$colprice = $row[PRICE] * $colrow[QUANTITY];
							if ($colrow[DISCOUNT_PERCENTAGE] > 0) {
								$colprice = $colprice*((100-$colrow[DISCOUNT_PERCENTAGE])/100);
							}
							if ($colrow[DISCOUNT_AMOUNTPERCOLLI] > 0) {
								$colprice = $colprice - $colrow[DISCOUNT_AMOUNTPERCOLLI];
							}
							if ($discount_factor > 0){
								$colprice = $colprice * $discount_factor;
							}
							$colunitprice = $colprice / $colrow[QUANTITY];
							$html .= 		"<tr>
												<td class='productextralabel alignright'>
													<span class='colliInput'><input type='radio' name='colliId' value='$colrow[ID]' ";
													if ($i == 1) {
														$html .= "checked ";
													}
													$html .= "/></span>".number_format($colrow[QUANTITY],0,"",cmsTranslate("CartThousandsSeperator"))." ".cmsTranslate("CartUnits")."
												</td>
												<td class='productextravalue'>
													".number_format($colprice,2,cmsTranslate("CartDecimalPoint"),cmsTranslate("CartThousandsSeperator"))." ".$exchangeRates[da][FORKORTELSE].".
													<div class='unitprice'>(".number_format($colunitprice,2,cmsTranslate("CartDecimalPoint"),cmsTranslate("CartThousandsSeperator"))." / ".cmsTranslate("CartUnits").")</div>
												</td>
											</tr>";
						}
					}
					if ($showrestriction) {
						$html .= 			"<tr>
												<td class='shoprestriction' colspan='2'>
													$restrictmessage
												</td>
											</tr>";
					}
					if ($showbuybutton) {
						$html .= "<tr><td colspan='2'><div class='productAddtoCart'>";
						if ($shopProductsBuySetQuantity) {
							$html .= "<input type='text' style='text-align: right; width: 23px;' name='productAmount' value='1' class='productAmount' /> stk. ";
						} else {
							$html .= "<input type='hidden' name='productAmount' value='1' />";
						}
						$fromPage = htmlentities("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
						$html .= "<input type='hidden' name='productId' value='$row[ID]' />
										<input type='hidden' name='PTN' value='SHOP_PRODUCTS' />
										<input type='hidden' name='fromPage' value='$fromPage' />
										<input type='submit' value='".cmsTranslate("shopAddToBasket")."' />
									</div></td></tr>";
					}

					// End extravalues table
					$html .= 			"</table>";

					// Tilbehør
					$arr_relproducts = return_related_products($row[ID]);
					if ($arr_relproducts) {
						$html .= "<table cellpadding='0' cellspacing='0' class='related_products_table'>";
						$arr_relproducts = group_related_products($arr_relproducts);
						$html .= "<tr>
												<td class='' colspan='2'><h2 class='shop_related_heading'>
													".cmsTranslate("shopBuyRelatedProducts")."
												</h2></td>
											</tr>";
						$groupname_rel = "";
						foreach ($arr_relproducts as $p) {
							if ($p[GROUP_NAME] != $groupname_rel) {
								$groupname_rel = $p[GROUP_NAME];
								$html .= "<tr>
												<td class='' colspan='2'><h3 class='shop_related_group'>
													$groupname_rel
												</h3></td>
											</tr>";
							
							}	
							$html .= 		"<tr>
												<td class='shop_related_product'>";
							$html .= "<a href='$arr_content[baseurl]/index.php?mode=shop&amp;action=showproduct&amp;group=$p[GROUP_ID]&amp;product=".urlencode($p[PRODUCT_NUMBER])."' title='".cmsTranslate("shopProductMoreInfo").": $p[PRODUCT_NAME]'>$p[PRODUCT_NAME]</a>";
							if ($showprice) {
								$html .= ", kr. ".number_format($p[PRICE],2,cmsTranslate("CartDecimalPoint"),cmsTranslate("CartThousandsSeperator"));
							}
							$html .= "</td>
												<td class='shop_related_productprice alignright'>
													<input size='3' type='text' id='shop_relatedproduct_$p[PRODUCT_ID]' name='shop_relatedproduct_$p[PRODUCT_ID]' value='0' />&nbsp;stk.
												</td>
											</tr>";
						}					
						// End tilbehør table
						$html .=		"</table>";
					}
					
					$html .= 		"</td>
								</tr>"; 
					$html .=		"</table>";
		
					if ($showbuybutton && $arr_relproducts) {
						$html .= "<div class='productAddtoCart'>
										<input type='submit' value='".cmsTranslate("shopAddToBasket")."' />
								</div>";
					}
					$html .= "</form>
						</div>";				
					}
					break;
		}
		
		

	if ($internal_mode == "singleproduct") {
		if ($groupid == "") {
			$groupid = returnFieldValue("SHOP_PRODUCTGROUPS", "ID", "NAME", $groupname);
		}
		$html .= "<div class='headlinebar'>";
		$html .= cmsTranslate("shopGroupViewAll")." <a href='$arr_content[baseurl]/index.php?mode=shop&action=showgroup&group=$groupid' title='".cmsTranslate("shopGroupViewAll")." \"$groupname\"'>$groupname</a></div>";	
	}


	} else { // No products found in main sql
		if (!$HideNoProductsBar) {
			$parent_name = returnFieldValue("SHOP_PRODUCTGROUPS", "NAME", "ID", "$groupid");
			$html .= "<div class='headlinebar'>
					".cmsTranslate("shopProductNone")." '$parent_name'
				</div>";
			return $html;
		}
	}

	return $html;
}

function hasSubgroups($group_id, $arr_content) {
	$sql = "select count(*) from SHOP_PRODUCTGROUPS where
			PARENT_ID = '$group_id' 
			and PUBLISHED = '1'
			and DELETED = '0'
			and SITE_ID in ('0','$arr_content[site]')";
	$result = mysql_query($sql);
	if (mysql_result($result,0) > 0) {
		return true;
	} else {
		return false;
	}
}


?>