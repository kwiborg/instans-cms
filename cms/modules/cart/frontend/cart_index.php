<script type="text/javascript">
function cart_verifyCheckout(){
	if ($("cart_checkoutForm_NAME") && $("cart_checkoutForm_NAME").value == ""){
 		alert("<?php echo cmsTranslate("CartJSAlertName"); ?>");
	 	return false;
 	}
 	if ($("cart_checkoutForm_ADDRESS") && $("cart_checkoutForm_ADDRESS").value == ""){
 		alert("<?php echo cmsTranslate("CartJSAlertAddress"); ?>");
	 	return false;
 	}
	if ($("cart_checkoutForm_ZIPCODE") && $("cart_checkoutForm_ZIPCODE").value == ""){
 		alert("<?php echo cmsTranslate("CartJSAlertZip"); ?>");
 		return false;
 	}
 	if ($("cart_checkoutForm_CITY") && $("cart_checkoutForm_CITY").value == ""){
 		alert("<?php echo cmsTranslate("CartJSAlertCity"); ?>");
 		return false;
 	}
 	if (($("cart_checkoutForm_PHONE") && $("cart_checkoutForm_CELLPHONE")) && ($("cart_checkoutForm_PHONE").value == "" && $("cart_checkoutForm_CELLPHONE").value == "")){
 		alert("<?php echo cmsTranslate("CartJSAlertPhone"); ?>");
 		return false;
 	}
 	if ($("cart_checkoutForm_EMAIL") && !isMail($("cart_checkoutForm_EMAIL").value)){
 		alert("<?php echo cmsTranslate("CartJSAlertMail"); ?>");
 		return false;
 	}
	delAdd = 0;
 	if ($("cart_checkoutForm_DELIVERYNAME") && $("cart_checkoutForm_DELIVERYNAME").value != ""){
		delAdd++;
 	}	
 	if ($("cart_checkoutForm_DELIVERYADDRESS") && $("cart_checkoutForm_DELIVERYADDRESS").value != ""){
		delAdd++;
 	}	
 	if ($("cart_checkoutForm_DELIVERYZIPCODE") && $("cart_checkoutForm_DELIVERYZIPCODE").value != ""){
		delAdd++;
 	}	
 	if ($("cart_checkoutForm_DELIVERYCITY") && $("cart_checkoutForm_DELIVERYCITY").value != ""){
		delAdd++;
 	}	
 	if (delAdd > 0 && delAdd != 4){
 		alert("<?php echo cmsTranslate("CartJSAlertDelAddress"); ?>");
 		return false;
 	}
	return true;
}
function cart_verifySendQuote(){
	if ($("cart_sendCart_NAME") && $("cart_sendCart_NAME").value == ""){
 		alert("<?php echo cmsTranslate("CartJSAlertName"); ?>");
	 	return false;
 	}
 	if ($("cart_sendCart_EMAIL") && !isMail($("cart_sendCart_EMAIL").value)){
 		alert("<?php echo cmsTranslate("CartJSAlertMail"); ?>");
 		return false;
 	}
	return true;
}
</script>
<?php 
global $shopPermissions;
// Check permissions
if ($shopPermissions[buy] != "") {
	if ($_SESSION[LOGGED_IN]) {
		if (checkPermissionFE($shopPermissions[buy], $_SESSION[USERDETAILS][2], false)) {
			// Krav om login - logget ind && HAR rettighed
			$permissions_ok = 1;
		} else {
			// Krav om login - logget ind && HAR rettighed
			echo "<div class='usermessage_error'>Du er logget ind, men du har ikke adgang til at se benytte webshoppen. Kontakt os venligst, hvis det er en fejl!</div>";				
			}
	} else {
		// Krav om login - ikke logget ind
		echo requestLogin("Du skal være logget ind for at benytte webshoppen.", $arr_content);
	}
} else {
	// Ikke krav om login
	$permissions_ok = 1;
}

if ($permissions_ok == 1) {
	// Only perform action if permissions ok!
	if ($arr_content[action] == "showcart" || !$arr_content[action]){
		$cartId = initCart("instans_cart_id");	
		echo showCart($arr_content, $cartId);
	}

	if ($arr_content[action] == "checkoutform"){
		$cartId = initCart("instans_cart_id");	
		echo checkoutForm($arr_content, $cartId);
	}

	if ($arr_content[action] == "checkoutpay"){
		echo "BETAL ELLER:...";

	}

	if ($arr_content[action] == "checkoutfinalize"){
		$cartId = initCart("instans_cart_id");	
		echo returnOrderSummary($arr_content, $cartId);
	}

	if ($arr_content[action] == "checkoutcomplete"){
		echo receiptPage($arr_content);
	}

	if ($arr_content[action] == "sendcart"){
		echo sendCartPage();
	}

	if ($arr_content[action] == "sendcartcomplete"){
		echo sendCartCompletePage();
	}
	
	if ($arr_content[action] == "printcart"){
		$cartId = initCart("instans_cart_id");	
		echo printCart(arr_content, $cartId);
	}
}		
?>