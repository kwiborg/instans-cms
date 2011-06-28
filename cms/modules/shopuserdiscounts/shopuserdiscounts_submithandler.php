<?php
	switch ($_POST[dothis]){
		case "saveuserprices":
			foreach ($_POST as $k => $v){
				$temp = explode("_", $k);
				if ($temp[0] == "userprice"){
					$product_id = $temp[1];
					$user_id = $temp[2];
					$userprice = $v;
					if ($v || !$v){
						if ($user_id == "grundpris"){
							$grundpris_origial = $_POST["userprice_".$product_id."_grundprisoriginal"];
							if ($grundpris_origial != $v){
								/// Grundpris ændret, opdater
								$sql = "
									update SHOP_PRODUCTS set PRICE='$v' where ID='$product_id' limit 1
								";
								 mysql_query($sql);
							}
						} else {
							$sql = "
								select 
									ID 
								from 
									SHOP_USERPRICES 
								where 
									USER_ID='$user_id' and PRODUCT_ID='$product_id' 
								limit 1
							";
							$res = mysql_query($sql);
							if (mysql_num_rows($res) == 1){
								$row = mysql_fetch_assoc($res);
								if ($userprice == ""){
									$sql = "delete from SHOP_USERPRICES where ID='$row[ID]'";
								} else {
									$sql = "
										update 
											SHOP_USERPRICES 
										set 
											USERPRICE='$userprice', CHANGED_DATE=NOW()
										where
											ID='$row[ID]'
									";
								}
								mysql_query($sql);
							} else if (mysql_num_rows($res) == 0 && $user_id && is_numeric($user_id) && $product_id && $userprice != ""){
								$sql = "
									insert into 
										SHOP_USERPRICES(
											USER_ID, PRODUCT_ID, USERPRICE, CHANGED_DATE
										) values (
											'$user_id', '$product_id', '$userprice', NOW()
										)
								";
								mysql_query($sql);
							}
						}
					}
				}
			}
		break;
	}
?>