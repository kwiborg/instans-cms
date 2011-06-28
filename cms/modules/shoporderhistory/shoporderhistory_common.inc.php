<?php
	function show_order_history($user_id=0, $mode="COMPACT", $searchfield="", $searchword=""){
		global $shop_ordersPerHistoryPage;
		$offset = ($_POST[offset] > 0 ? $_POST[offset] : 0);
		$sql = "
			select 
				SO.*, 
				COO.ORDERSTATUS_ID, COO.CREATED_DATE
			from 
				SHOP_ORDERS SO, CART_ORDERS_ORDERSTATUS COO
			where
				SO.ORDERNUMBER_SEQ=COO.ORDER_ID and
				SO.SITE_ID = '$_SESSION[SELECTED_SITE]'
			order by 
				SO.ID desc
			limit
				$offset, $shop_ordersPerHistoryPage;
		";
		if (trim($searchfield) != "" && trim($searchword) != ""){
			$sql = "
				select 
					SO.*, 
					COO.ORDERSTATUS_ID, COO.CREATED_DATE
				from 
					SHOP_ORDERS SO, CART_ORDERS_ORDERSTATUS COO
				where
					SO.ORDERNUMBER_SEQ=COO.ORDER_ID and 
					(SO.".$searchfield." like '%".$searchword."%' or
					SO.".$searchfield."='".$searchword."') and
					SO.SITE_ID = '$_SESSION[SELECTED_SITE]'
				order by 
					SO.ID desc
			";
		}
		if($_POST[orderid] || $_GET[orderid]){
			$mode = "EXPANDED";
			$sql = "
				select 
					SO.*, 
					COO.ORDERSTATUS_ID, COO.CREATED_DATE
				from 
					SHOP_ORDERS SO, CART_ORDERS_ORDERSTATUS COO
				where
					SO.ORDERNUMBER_SEQ=COO.ORDER_ID and
					SO.ORDERNUMBER_SEQ='".($_POST[orderid] ? $_POST[orderid] : $_GET[orderid])."' and
					SO.SITE_ID = '$_SESSION[SELECTED_SITE]'
				order by 
					SO.ID desc
				limit 1
			";
		}
		if($_POST[userid]){
			$sql = "
				select 
					SO.*, 
					COO.ORDERSTATUS_ID, COO.CREATED_DATE
				from 
					SHOP_ORDERS SO, CART_ORDERS_ORDERSTATUS COO
				where
					SO.ORDERNUMBER_SEQ=COO.ORDER_ID and
					SO.USER_ID='$_POST[userid]' and
					SO.SITE_ID = '$_SESSION[SELECTED_SITE]'
				order by 
					SO.ID desc
			";
		}
		$res = mysql_query($sql);
		if ($mode == "COMPACT"){
			$html .= "
				<table class='ordercompact' cellspacing='0' cellpadding='0'>
					<tr>
						<th>Ordrenr.</th>
						<th>Dato</th>
						<th>Total</th>
						<th>Firma</th>
						<th>Navn</th>
						<th>Adresse</th>
						<th>Postnr.</th>
						<th>By</th>
						<th>Tlf.</th>
						<th></th>
					</tr>
			";
			while ($row = mysql_fetch_assoc($res)){
				$i++;
				$html .= "
					<tr class='".($i%2==0?"even":"")."'>
						<td valign='top'>$row[ORDERNUMBER_SEQ]</td>
						<td valign='top'>".returnNiceDateTime($row[CREATED_DATE], 1)."</td>
						<td valign='top'>".number_format(return_orderlines($row[ORDERNUMBER_SEQ], true), 2, ",", ".")."</td>
						<td valign='top'>".return_if_defined($row[COMPANY])."</td>
						<td valign='top'>".($row[USER_ID] ? "<a href='#' onclick='document.forms[0].userid.value=\"$row[USER_ID]\"; document.forms[0].submit();'>$row[NAME]</a>" : "$row[NAME]")."</td>
						<td valign='top'>$row[ADDRESS]</td>
						<td valign='top'>$row[ZIPCODE]</td> 
						<td valign='top'>$row[CITY]</td>
						<td valign='top'>".return_if_defined($row[PHONE])."</td>
						<!--<td valign='top'><a href='index.php?content_identifier=shoporderhistory&orderid=$row[ORDERNUMBER_SEQ]'>Vis</a></td>-->
						<td valign='top'><a href='#' onclick='document.forms[0].orderid.value=\"$row[ORDERNUMBER_SEQ]\"; document.forms[0].submit();'>Vis</a></td>
					</tr>
				";
			}
			$html .= "</table>";
		} else if ($mode == "EXPANDED"){
			while ($row = mysql_fetch_assoc($res)){
				$i++;
				$html .= "
					<div class='order".($i%2==0?" even":"")."'>
						<table cellpadding='0' cellspacing='0' width='100%'>
							<tr>
								<td colspan='3' align='left' bgcolor='#ddeedd'>Ordrenummer: <strong>".$row[ORDERNUMBER_SEQ]."</strong>&nbsp;&nbsp;/&nbsp;&nbsp;".returnNiceDateTime($row[CREATED_DATE], 1).($row[USER_ID] ? "&nbsp;&nbsp;/&nbsp;&nbsp;<a href='#' onclick='document.forms[0].userid.value=\"$row[USER_ID]\"; document.forms[0].submit();'>Flere fra denne bruger</a>" : "")."</td>
							</tr>
							<tr>
								<td height='10'></td>
							</tr>
							<tr>
								<td>
									<em>Fakturering til</em>:<br/>
									".($row[COMPANY] ? $row[COMPANY]."<br/>" : "")."
									<strong>$row[NAME]<br/>
									$row[ADDRESS]<br/>
									$row[ZIPCODE] $row[CITY]</strong><br/>
									".return_if_defined($row[ATTENTION], "Att.: ")."
									".return_if_defined($row[PHONE], "Tlf.: ")."
									".return_if_defined($row[CELLPHONE], "Mobil: ")."
									".return_if_defined($row[FAX], "Fax: ")."
									".return_if_defined($row[EMAIL], "E-mail: ")."
									".return_if_defined($row[VAT_NUMBER], "CVR-nr.: ")."
									".return_if_defined($row[FROZEN_PAYMENTTERM], "Betalingsbetingelse: ")."
									".return_if_defined($row[NOTES], "Bemærk: ")."
								</td>
								<td width='50'></td>
								<td valign='top'>
									<em>Levering til</em>:<br/>
									".($row[DELIVERYNAME] ? "
									<strong>$row[DELIVERYCOMPANY]<br/>
									$row[DELIVERYNAME]<br/>
									$row[DELIVERYADDRESS]<br/>
									$row[DELIVERYZIPCODE] $row[DELIVERYCITY]</strong><br/>" : "Samme som fakturering")."
								</td>
							</tr>
						</table>
						".return_orderlines($row[ORDERNUMBER_SEQ])."
						<div class='printorder'><a target='_blank' href='/cms/modules/shoporderhistory/shoporderhistory_print.php?orderid=$row[ORDERNUMBER_SEQ]'>Print</a></div>
					</div>
				";
			}
		}
		return $html;
	}
	
	function return_if_defined($v, $before="", $newline=true){
		if ($v){
			if (strstr($v, "@") && strstr($v, ".")){
				$v = "<a href='mailto:$v'>$v</a>";
			}
			if ($newline){
				return "$before"."$v<br/>";
			} else {
				return "$before"."$v";
			}
		} else {
			return "";
		}
	}
	
	function return_orderlines($orderid_seq, $total_only=false){
		global $dbProductsMomsState, $cartMomsPct;
		$sql = "
			select 
				SOD.* 
			from 
				SHOP_ORDERDETAILS SOD
			where
				SOD.ORDERNUMBER_SEQ='$orderid_seq'
			order by
				SOD.ID asc
		";
		$res = mysql_query($sql);
		$html .= "<table cellspacing='0' cellpadding='0' class='orderlines' width='100%'>";
		$html .= "
			<tr>
				<th>Varenummer</th>
				<th>Varenavn</th>
				<th>Pris/stk.*</th>
				<th>Kolli</th>
				<th>Antal</th>
				<th>Kollirabat (%)</th>
				<th>Kollirabat (kr.)</th>
				<th>Grupperabat (%)</th>
				<th style='text-align:right'>Subtotal</th>
			</tr>
		";
		while ($row = mysql_fetch_assoc($res)){
			$i++;
			$price=false; // reset
			if ($row[FROZEN_CUSTOMPRICE] != NULL) {
				$price = $row[FROZEN_CUSTOMPRICE];
				$showcustomprice = true;
			} else {
				$price = false;
				$showcustomprice = false;
			}
			if (!$price && $row[FROZEN_USERPRICE] != NULL) {
				$price = $row[FROZEN_USERPRICE];
				$showuserprice = true;
			} else {
				$price = false;
				$showuserprice = false;
			}
			if (!$price) {
				if ($row[FROZEN_COLLIQUANTITY] == 0){
					$price = $row[AMOUNT] * $row[FROZEN_PRODUCTPRICE];
				} else if ($row[FROZEN_COLLIQUANTITY] > 0){
					$price = $row[AMOUNT] * $row[FROZEN_PRODUCTPRICE] * $row[FROZEN_COLLIQUANTITY];
					if ($row[FROZEN_COLLIDISCOUNT_AMOUNT] > 0){
						$price = $price - ($row[AMOUNT] * $row[FROZEN_COLLIDISCOUNT_AMOUNT]);
					}
					if ($row[FROZEN_COLLIDISCOUNT_PCT] > 0){
						$price = $price * ((100-$row[FROZEN_COLLIDISCOUNT_PCT])/100);
					}
				}
				if ($row[FROZEN_GROUPDISCOUNT] > 0){
					$price = $price * ((100-$row[FROZEN_GROUPDISCOUNT])/100);
				}

			}
			$total += $price;
			$html .= "
				<tr class='".($i%2==0?"even":"")."'>
					<td>$row[FROZEN_PRODUCTNUMBER]</td>
					<td>$row[FROZEN_PRODUCTNAME]</td>
					<td>";
			if ($showcustomprice) {
				$html .= number_format($row[FROZEN_CUSTOMPRICE], 2, ",", ".")." (c)";
			} elseif ($showuserprice) {
				$html .= number_format($row[FROZEN_USERPRICE], 2, ",", ".")." (b)";
			} else {
				$html .= number_format($row[FROZEN_PRODUCTPRICE], 2, ",", ".");
			}
			$html .= "</td>
					<td>".($row[FROZEN_COLLIQUANTITY] ? $row[FROZEN_COLLIQUANTITY] : "-")."</td>
					<td>$row[AMOUNT]</td>
					<td>".($row[FROZEN_COLLIDISCOUNT_PCT] ? $row[FROZEN_COLLIDISCOUNT_PCT] : "-")."</td>
					<td>".($row[FROZEN_COLLIDISCOUNT_AMOUNT] ? $row[FROZEN_COLLIDISCOUNT_AMOUNT] : "-")."</td>
					<td>".($row[FROZEN_GROUPDISCOUNT] ? $row[FROZEN_GROUPDISCOUNT] : "-")."</td>
					<td align='right'>".number_format($price, 2, ",", ".")."</td>
				</tr>
			";
		}
		$html .= "
			<tr class='ordertotal'>
				<td colspan='8'>Total ".($dbProductsMomsState == 1 ? "inkl. moms" : "ekskl. moms").":</td>
				<td class='total'>".number_format($total, 2, ",", ".")."</td>
			</tr>
		";
		$html .= "
			<tr class='ordertotal'>
				<td colspan='8'>Moms:</td>
				<td class='total'>".($dbProductsMomsState == 0 ? number_format($total*($cartMomsPct/100), 2, ",", ".") : number_format($total*(1-(1/(1+$cartMomsPct/100))), 2, ",", "."))."</td>
			</tr>
		";
		$html .= "</table>";
		if ($total_only) return $total;
		$html .= "<p>(*) Priser markeret med (u) er brugerspecifikke priser, priser markeret med (c) er custom/beregnede priser.</p>";
		return $html;
	}
?>