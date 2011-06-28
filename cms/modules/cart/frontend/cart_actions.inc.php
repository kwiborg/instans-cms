<?php
global $shopPermissions;

if ($arr_content["ajax"] == 1 || $_POST["ajax"] == 1){
	header("Content-type: text/html; charset=UTF-8");
	include_once($_SERVER[DOCUMENT_ROOT]."/cms_config.inc.php");
	include_once($_SERVER[DOCUMENT_ROOT]."/cms_language.inc.php");
	include_once($_SERVER[DOCUMENT_ROOT]."/cms/sharedfunctions.inc.php");
	include_once($_SERVER[DOCUMENT_ROOT]."/cms/frontend/frontend_common.inc.php");
	include_once($customFunctionsPath);
	connect_to_db();
}

if (checkPermissionFE($shopPermissions["buy"], $_SESSION["USERDETAILS"][2])){
	// Make sure that $arr_content is available
	if (!is_array($arr_content)) {
		$arr_content = return_content_build_array(return_site_to_show());
	}
	
	if ($arr_content[action] == "addtocart"){
		/// VÆRDIERNE DER GÅR IND I FUNKTIONEN SKAL VÆRE POST-VARIABLER
		/// COOKIENAVNET "instans_cart_id" KAN VÆRE ET HIDDEN FELT I DEN FORM, DER POSTER DATA NED I KURVEN
		$colliId			= $arr_content[colliId];
		$productId			= $arr_content[productId];
		$productTableName 	= $arr_content[PTN];
		$productAmount		= $arr_content[productAmount];
		$productCustom		= $arr_content[productCustomPrice];
		$productCustomDescription = $arr_content[productCustomDescription];
		$productFragt		= $arr_content[productFragt];
		$productDelDays 	= $arr_content[productDeldays];
		$customerZipCode 	= $arr_content[customerZipcode];
		$fromPage			= $arr_content[fromPage];
		$cartId = initCart("instans_cart_id");
		$orderId = createOrder($cartId);
		if ($customerZipCode) {
			updateOrderZipcode($orderId, $customerZipCode);
		}
		addToCart($orderId, $productId, $productTableName, $productAmount, $productFragt, $productDelDays, $productCustomPrice, $productCustomDescription, $colliId);
		// Loop through $_POST to add any related products to the cart
		// Example: id='shop_relatedproduct_5'
		foreach ($_POST as $key => $value) {
			if (strpos($key, "shop_relatedproduct_") !== false) {
				if (($value*1)>0) {
					$arr_rp = explode("_",$key);
					if (is_numeric($arr_rp[2])) {
					// This is a related product, add it to the cart!
						$productId = $arr_rp[2];
						$productAmount = $value;
						// Reset values set for main product
						$productFragt = "";
						$productDelDays = "";
						$productCustomPrice = "0";
						$productCustomDescription = "";
						$colliId = "";
						addToCart($orderId, $productId, $productTableName, $productAmount, $productFragt, $productDelDays, $productCustomPrice, $productCustomDescription, $colliId);
					}
				}
			}
		}

		if (!$arr_content["ajax"]){
			setcookie("instans_cart_id", $cartId, time()+24*60*60, "/", $cookieDomain);		
			header("location: $arr_content[baseurl]/index.php?mode=cart&action=showcart");
		} else if ($arr_content["ajax"] == 1) {
			setcookie("instans_cart_id", $cartId, time()+24*60*60, "/", $cookieDomain);
			echo showMicroCart($arr_content, $cartId, $arr_content[action]);
			exit;
		}
	}
		
	if ($_SESSION[LOGGED_IN] && isset($_COOKIE[instans_cart_id])){
		$cartId = initCart("instans_cart_id");
		$sql = "
			select 
				* from CART_ORDERS, CART_CONTENTS 
			where 
				CART_ORDERS.ID = CART_CONTENTS.CART_ORDERS_ID and CART_ORDERS.CART_ID = '$cartId' and CART_ORDERS.DELETED='0'
		";
		$resultFrom = mysql_query($sql);
		if (mysql_num_rows($resultFrom) > 0){
			// NOGET I COOKIE-KURV
			$sql = "select ID from CART_ORDERS where USER_ID = '".$_SESSION[USERDETAILS][0][0]."' and DELETED = '0'";
			$resultTo = mysql_query($sql);
			if (mysql_num_rows($resultTo) > 0){
				// DER FINDES USER_ID ORDRE - FLYT COOKIE-ORDRE
				$rowTo = mysql_fetch_array($resultTo);
				$toOrderId = $rowTo["ID"];
				while ($rowFrom = mysql_fetch_array($resultFrom)){
					addToCart($toOrderId, $rowFrom[PRODUCT_ID], $rowFrom[PRODUCT_TABLENAME], $rowFrom[AMOUNT], $rowFrom[FRAGT], $rowFrom[DELIVERY_DAYS], $rowFrom[CUSTOM_PRICE], $rowFrom[CUSTOM_DESCRIPTION], $rowFrom[COLLI_ID]);
				}
			} else {
				// DER FINDES IKKE USER_ID ORDRE - OMDØB COOKIE-ORDRE
				$sql = "update CART_ORDERS set USER_ID='".$_SESSION[USERDETAILS][0][0]."', CART_ID='' where CART_ID='$cartId'";
				mysql_query($sql);
			}
		} 
		killCookie($cartId);
		$redirect = "yes";
	}
		
	if ($arr_content[action] == "showcart" || !$arr_content[action]){
		$cartId = initCart("instans_cart_id");	
		$_COOKIE["instans_cart_id"] = $cartId;
	}
	
	if ($arr_content[action] == "showmicrocart"){
		//if ($arr_content["ajax"] == 1){
			$cartId = initCart("instans_cart_id");
			echo showMicroCart($arr_content, $cartId, $arr_content[action]);
			exit;
		//}
	}

	if ($arr_content[action] == "returncartid"){
		//if ($arr_content["ajax"] == 1){
			echo initCart("instans_cart_id");
			exit;
		//}
	}	
	
	if ($arr_content[action] == "updatecart" || $arr_content["UPDATECART_CLICKED"]){		
		$ref = $arr_content[referer];
		$cartId = initCart("instans_cart_id");
		updateCart();
		header("location: $arr_content[baseurl]/index.php?mode=cart&action=showcart&r=".urlencode($ref));
		exit;
	}		
	
	if ($arr_content[action] == "emptycart" || $arr_content["EMPTYCART_CLICKED"] || $arr_content["safarisubmit"] == "EMPTYCART_CLICKED"){
		$cartId = initCart("instans_cart_id");
		emptyCart($cartId);
		header("location: $arr_content[baseurl]/index.php?mode=cart&action=showcart");		
		exit;
	}

	if ($arr_content[action] == "checkout" || $arr_content["CHECKOUT_CLICKED"]){
		$cartId = initCart("instans_cart_id");
		updateCart();
		header("location: $arr_content[baseurl]/index.php?mode=cart&action=checkoutform");	
		exit;
	}

	if ($arr_content["PAYORDER_CLICKED"]){
		$cartId = initCart("instans_cart_id");
		$orderId = createOrder($cartId); 
		$key=md5($orderId."r4nDom");

		header("location: $arr_content[baseurl]/index.php?mode=cart&action=checkoutpay&order=$orderId&key=$key");
		exit;
	}

	if ($arr_content["CONTINUE_CLICKED"]){
		header("location: $arr_content[referer]");		
		exit;
	}
	
	if ($arr_content["BACKTOCART_CLICKED"]){
		header("location: $arr_content[baseurl]/index.php?mode=cart&action=showcart");		
		exit;
	}

	if ($arr_content["APPROVEORDER_CLICKED"]){
		$cartId = initCart("instans_cart_id");
		updateOrderDetails($cartId, $arr_content);
		$checkoutErrors = serversideCheckoutValidate($arr_content);
		if (count($checkoutErrors) == 0){
			header("location: $arr_content[baseurl]/index.php?mode=cart&action=checkoutfinalize");
			exit;
		}		
	}
	
	if ($arr_content[action] == "checkoutfinalize" || $arr_content[action] == "checkoutform"){
		$cartId = initCart("instans_cart_id");
		if (isEmptyOrder($cartId)){
			exit;
		}
	}

	


	if ($arr_content["COMPLETEORDER_CLICKED"]){
		$cartId = initCart("instans_cart_id");
		completeOrder($arr_content, $cartId);
		header("location: $arr_content[baseurl]/index.php?mode=cart&action=checkoutcomplete");
		exit;
	}

	if ($arr_content["SENDQUOTE_CLICKED"]){
		header("location: $arr_content[baseurl]/index.php?mode=cart&action=sendcart");
		exit;
	}

	if ($arr_content["SENDCART_CLICKED"]){
		$cartId = initCart("instans_cart_id");
		updateOrderDetails($cartId, $arr_content);
		sendCart($arr_content, $cartId);
		$goto_pageid = cmsTranslate("CartSendQuoteThanksPageId");	
		header("location: $arr_content[baseurl]/index.php?pageid=".$goto_pageid."&cartId=".$cartId);
		exit;
	}
	if ($arr_content["BACKTOFRONTPAGE_CLICKED"]){
		header("location: $arr_content[baseurl]/index.php");
		exit;
	}

	if ($redirect == "yes" && !isset($arr_content[redirected])) {
		// preserve action
		if (isset($arr_content[action])) {
			$str_action = "&action=$arr_content[action]";
		}
		header("location: $arr_content[baseurl]/index.php?mode=cart$str_action&redirected=1");
		exit;
	}

// END checkPermissionFE 
}
?>