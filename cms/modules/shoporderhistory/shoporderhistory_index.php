<h1>Ordrehistorik</h1>
<form method="post">
	<input type="hidden" name="dothis" value="" />
	<input type="hidden" name="offset" value="<?=$_POST[offset]?>" />
	<input type="hidden" name="orderid" value="<?=$_POST[orderid]?>" />
	<input type="hidden" name="userid" value="<?=$_POST[userid]?>" />
<div id="sort_orders">
	Visning:
	<select name="view" class="inputfelt_kort">
		<option value="COMPACT" <?=($_POST[view] == "COMPACT" ? "selected" : "")?>>Kompakt</option>
		<option value="EXPANDED" <?=($_POST[view] == "EXPANDED" ? "selected" : "")?>>Udvidet</option>
	</select>
	<input type="submit" value="OK" class="lilletekst_knap" name="switch_view" />
	&nbsp;&nbsp;|&nbsp;&nbsp;
	Søg i 
	<select name="search_in" class="inputfelt_kort">
		<option value="COMPANY" 	<?=($_POST[search_in] == "COMPANY" ? "selected" : "")?>>Firma</option>
		<option value="NAME" 		<?=($_POST[search_in] == "NAME" ? "selected" : "")?>>Navn</option>
		<option value="ADDRESS" 	<?=($_POST[search_in] == "ADDRESS" ? "selected" : "")?>>Adresse</option>
		<option value="ZIPCODE"		<?=($_POST[search_in] == "ZIPCODE" ? "selected" : "")?>>Postnr.</option>
		<option value="CITY"		<?=($_POST[search_in] == "CITY" ? "selected" : "")?>>By</option>
		<option value="PHONE"		<?=($_POST[search_in] == "PHONE" ? "selected" : "")?>>Tlf.</option>
		<option value="CELLPHONE"	<?=($_POST[search_in] == "CELLPHONE" ? "selected" : "")?>>Mobil</option>
		<option value="EMAIL"		<?=($_POST[search_in] == "EMAIL" ? "selected" : "")?>>E-mail</option>
		<option value="ORDERNUMBER_SEQ"<?=($_POST[search_in] == "ORDERNUMBER_SEQ" ? "selected" : "")?>>Ordrenr.</option>
	</select>
	efter
	<input type="text" name="order_searchword" class="inputfelt_kort" size="20" value="<?=trim($_POST[order_searchword])?>" />
	<input type="submit" value="Søg" onclick="document.forms[0].orderid.value=''; document.forms[0].userid.value=''" class="lilletekst_knap" name="search_orders" />
	&nbsp;&nbsp;|&nbsp;&nbsp;
	<input type="button" value="Reset" class="lilletekst_knap" name="reset" onclick="location='index.php?content_identifier=shoporderhistory'"/>
</div>
<div class="offset">
	<?php if ($_POST[orderid]) { ?>
		<a href="#" onclick="document.forms[0].orderid.value=''; document.forms[0].submit();">&laquo;&nbsp;Oversigt</a>
	<?php } else { ?>
		<a href="#" onclick="offsetfunc(<?=$shop_ordersPerHistoryPage?>)">&laquo;&nbsp;Ældre</a>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<a href="#" onclick="offsetfunc(-<?=$shop_ordersPerHistoryPage?>)">Nyere&nbsp;&raquo;</a>
	<?php } ?>
</div>
<?php
	if ($_POST){
		echo show_order_history($_GET[USER_ID], $_POST[view], $_POST[search_in], $_POST[order_searchword]);
	} else {
		echo show_order_history();
	}
?>
<div class="offset">
	<?php if ($_POST[orderid]) { ?>
		<a href="#" onclick="document.forms[0].orderid.value=''; document.forms[0].submit();">&laquo;&nbsp;Oversigt</a>
	<?php } else { ?>
		<a href="#" onclick="offsetfunc(<?=$shop_ordersPerHistoryPage?>)">&laquo;&nbsp;Ældre</a>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<a href="#" onclick="offsetfunc(-<?=$shop_ordersPerHistoryPage?>)">Nyere&nbsp;&raquo;</a>
	<?php } ?>
</div>
</form>
