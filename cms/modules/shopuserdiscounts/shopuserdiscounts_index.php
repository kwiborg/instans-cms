<?php
switch ($_REQUEST['dothis']) {
	default:
		echo return_userdiscountform();
}

function return_userdiscountform(){
	global $saved;
	$html .= "<h1>Webshop: Brugerrabatter</h1>";
	$html .= "
	<form method='post' action=''>
		<input type='hidden' name='dothis' value='' />
		<div class='feltblok_header'>
			Vælg varegruppe og/eller søg på brugere
		</div>
		<div class='feltblok_wrapper'>
			<h2>Vælg varegruppe</h2>	
			".productGroupSelector($_POST[productGroupSelector], "onchange='select_productgroup(this.value);'")."
			<h2>Vælg evt. en brugergruppe eller søg efter brugere</h2>
			<p class='feltkommentar'>Du kan indskrænke mængden af brugere yderligere ved <strong>enten</strong> at vælge en specifik brugergruppe <strong>eller</strong> søge på brugernavn nedenfor. Der søges både i brugernavn, fornavn, efternavn, firmanavn og e-mail.</p>
			Gruppevalg: ".return_usergroupselector($_POST[usergroupselector])."&nbsp;<input onclick='select_usergroup()' type='button' class='lilleknap' value='Opdater' />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			Brugersøgning: <input onchange='usersearchtoggle(this)' value='".$_POST[usersearch]."' type='text' id='usersearch' name='usersearch' class='inputfelt_kort' />&nbsp;<input type='button' value='Søg' onclick='user_search()' class='lilleknap' />
		</div>
	";
	if ($saved){
		$html .= "<br/><div class='usermessage_ok'>Priserne blev opdateret og gemt.</div>";
	}
	if ($_POST[productGroupSelector]){
		$html .= "
			<div class='feltblok_header'>Definer brugerpriser og evt. grundpriser</div>
			<div class='feltblok_wrapper'>
				".product_user_table($_POST[productGroupSelector], $_POST[usergroupselector], $_POST[usersearch])."
	";
	$html .= "
				<div class='knapbar'>
					<input type='button' value='Gem brugerpriser' onclick='verifyuserprices()' />
				</div>
			</div>
		";
	}
	$html .= "
	</form>
	";
	return $html;
}

function productGroupSelector($selected = "", $noonchange = "") {
	if ($noonchange == "") {
	 $onchange =  "onchange='groupSelected(this);'";
	} else {
		$onchange = " $noonchange";
	}
	$html .= "<select id='productGroupSelector' name='productGroupSelector' class='inputfelt'$onchange>";
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
	$html .= "</select>";
	return $html;
}

function return_usergroupselector($group_id) {
	$html .= "<select id='usergroupselector' name='usergroupselector' class='inputfelt_kort' onchange='select_productgroup(this.value);'>";
	$html .= usergroupSelector($group_id);
	$html .= "</select>";
	return $html;
}

function product_user_table($productgroup_id, $usergroup_id="", $usersearch_str=""){
	if (trim($usersearch_str) == ""){
		if (!$usergroup_id){
			$user_sql = "
				select 
					ID, FIRSTNAME, LASTNAME, COMPANY
				from 
					USERS
				where
					DELETED='0' and UNFINISHED='0'
				order by
					FIRSTNAME asc
			";
		} else if ($usergroup_id){
			$user_sql = "
				select 
					U.ID, U.FIRSTNAME, U.LASTNAME, COMPANY
				from 
					USERS U, USERS_GROUPS UG
				where
					U.DELETED='0' and U.UNFINISHED='0' and U.ID=UG.USER_ID and UG.GROUP_ID='$usergroup_id'
				order by
					U.FIRSTNAME asc
			";
		}
	} else {
			$user_sql = "
				select 
					ID, FIRSTNAME, LASTNAME, COMPANY
				from 
					USERS
				where
					DELETED='0' and UNFINISHED='0' and
					(
						FIRSTNAME like '".$usersearch_str."%' or
						LASTNAME like '".$usersearch_str."%' or
						EMAIL like '".$usersearch_str."%' or
						COMPANY like '".$usersearch_str."%' or
						USERNAME like '".$usersearch_str."%'
					)
				order by
					FIRSTNAME asc
			";
	}
	$user_res = mysql_query($user_sql);
	$num_users = mysql_num_rows($user_res);
	$userheader_html .= "
		<tr>
			<td>
				<div class='corner'>
					<table cellpadding='0' cellspacing='0' border='1'>
						<tr>
							<th><div>&nbsp;</div></th>
						</tr>
					</table>
				</div>
			</td>
			<td>
				<div class='headerRow'>
					<table cellpadding='0' cellspacing='0' border='1'>
						<tr><th id='grundpris'><div><em>Grundpris</em></div></th>";	
	$user_ids[] = array();
	while ($user_row = mysql_fetch_assoc($user_res)){
		$user_ids[] = $user_row[ID];
		$c++;
		$userheader_html .= "<th id='header_$c' align='center'><div>".$user_row[FIRSTNAME]." ".$user_row[LASTNAME].($user_row[COMPANY] ? "<br/>$user_row[COMPANY]" : "")."</div></th>";
	}
	$userheader_html .= "</tr>
					</table>
				</div>
			</td>
		</tr>
	";
	$product_sql = "
		select
			SP.ID, SP.NAME
		from
			SHOP_PRODUCTS SP, SHOP_PRODUCTS_GROUPS SPG
		where 
			SPG.GROUP_ID='$productgroup_id' and SPG.PRODUCT_ID=SP.ID and
			SP.DELETED='0'
	";
	$product_res = mysql_query($product_sql);
	$num_products = mysql_num_rows($product_res);
	$productheader_html .= "
		<tr>
			<td valign='top'>
				<div class='headerColumn'>
					<table cellpadding='0' cellspacing='0' border='1'>
	";
	$product_ids = array();
	while ($product_row = mysql_fetch_assoc($product_res)){
		$product_ids[] = $product_row[ID];
		$productheader_html .= "<tr><th><div>".$product_row[NAME]."</div></th></tr>\n";
	}
	$productheader_html .= "					
					</table>
				</div>
			</td>
	";
	$productheader_html .="
			<td>
				<div class='body'>
					<table cellpadding='0' cellspacing='0' border='1'>\n";
	for($k=0; $k<$num_products; $k++){
			$odd = ($k%2 ? " class='odd'" : "");
			$productheader_html .= "<tr $odd>";
			for($i=0; $i<$num_users+1; $i++){
			$fieldname = "userprice_".$product_ids[$k]."_".($i==0 ? "grundpris" : $user_ids[$i]);
			if ($i > 0){
				$grundpris = false;
				$sql = "select ID from SHOP_PRODUCTS_COLLI where PRODUCT_ID='".$product_ids[$k]."' and DELETED='0'";
				$colli_res = mysql_query($sql);
				if (mysql_num_rows($colli_res) > 0){
					$colliprice = true;
				}
				$sql = "
					select USERPRICE from SHOP_USERPRICES where USER_ID='".$user_ids[$i]."' and PRODUCT_ID='".$product_ids[$k]."' limit 1
				";
				$thisprice_res = mysql_query($sql);
				if (mysql_num_rows($thisprice_res) == 1){
					$thisprice_row = mysql_fetch_assoc($thisprice_res);
					$thisprice = $thisprice_row[USERPRICE];
				} else {
					$thisprice = "";
				}
			} else {
				$colliprice = false;
				$grundpris = true;
				/// HENT GRUNDPRIS + GRUNDPRIS ORIGINAL
				$sql = "select PRICE from SHOP_PRODUCTS where ID='$product_ids[$k]' limit 1";
				$thisprice_res = mysql_query($sql);
				if (mysql_num_rows($thisprice_res) == 1){
					$thisprice_row = mysql_fetch_assoc($thisprice_res);
					$thisprice = $thisprice_row[PRICE];
				} else {
					$thisprice = "";
				}
			}
			$productheader_html .= "<td align='center'>
				<div>
					
					".($grundpris ? "<input type='hidden' value='$thisprice' id='userprice_".$product_ids[$k]."_grundprisoriginal' name='userprice_".$product_ids[$k]."_grundprisoriginal' />" : "")."
					".($colliprice ? "<em>Kollipris</em>" : "<input onblur='userprice_format(this)' size='8' class='userpricefield' type='text' id='$fieldname' value='$thisprice' name='$fieldname' />")."
					<a name='scrollto_".$fieldname."'></a>
				</div>
			</td>";
		}
		$productheader_html .= "</tr>";
	}
	$productheader_html .= "</table></div></td></tr>";
	$html .= "
		<table border='1' id='scrollTable' class='scrollTable'>
		".$userheader_html."
		".$productheader_html."
		</table>";
	$html = str_replace("\n", "", $html);
	$html = str_replace("\r", "", $html);
	$html = str_replace("\r\n", "", $html);
	$html = str_replace("\t", "", $html);
	return $html;
}
?>

<style>
.scrollTable td, .scrollTable th
{
	font-family: Verdana;
	font-size: 9px;
}

.scrollTable table
{
	border-collapse: collapse;
}
.scrollTable table td, .scrollTable table th
{
	vertical-align: top;
	text-align: center;
	margin: 0px;
	padding-left: 10px;
	padding-right: 10px;
	padding-top: 1px;
	padding-bottom: 1px;
	white-space: nowrap;
	border-top: solid 1px #aaaaaa;
	border-bottom: solid 1px #aaaaaa;
}
.scrollTable table tr.odd{
	background-color:#ccc;
}
th
{
	background-color:#666;
	color: #ffffff;
	font-weight: bold;
}
.userpricefield{
	font:normal 9px verdana;
}
</style>

<script language="javascript">
	paddingLeft = 10;
	paddingRight = 10;
	paddingTop = 1;
	paddingBottom = 1;
	
	if ($("scrollTable")){
		ScrollTableAbsoluteSize(
			document.getElementById("scrollTable"), 
			600, 
			600);
		}
</script>