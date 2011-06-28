<?php
	function group_selector_items($selected_id){
		$sql = "
			select 
				G.ID, G.GROUP_NAME
			from 
				GROUPS G
			where 
				G.DELETED='0' and 
				G.UNFINISHED='0' and
				G.SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
			order by 
				G.GROUP_NAME asc 
		";
		$res = mysql_query($sql);
		while ($row = mysql_fetch_assoc($res)){
			$html .= "<option value='$row[ID]' ".($selected_id == $row[ID] ? "selected" : "").">$row[GROUP_NAME]</option>";
		}
		return $html;
	}

	function new_user_overview($group_id, $searchwords, $context="userlist"){
		global $sortby, $sortdir;
		if ($group_id){
			$sql = "
				select 
					U.USERNAME, U.ID, U.FIRSTNAME, U.LASTNAME, U.TRANSFER_TO_GROUP
				from 
					USERS U, USERS_GROUPS UG, GROUPS G
				where 
					U.DELETED='0' and 
					U.UNFINISHED='0' and
					UG.GROUP_ID=G.ID and 
					G.ID='$_GET[group_id]' and
					U.ID=UG.USER_ID and
					G.SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
			";
			if ($context == "groupmembers"){
				$sql .= " and U.ID not in (SELECT USER_ID from USERS_GROUPS where GROUP_ID='$_GET[id]')";
			}
		} else {
			$sql = "
				select DISTINCT  
					U.USERNAME, U.ID, U.FIRSTNAME, U.LASTNAME, U.TRANSFER_TO_GROUP
				from 
					USERS U, USERS_GROUPS UG, GROUPS G
				where 
					U.ID = UG.USER_ID and
					UG.GROUP_ID = G.ID and
					G.SITE_ID in (0,'$_SESSION[SELECTED_SITE]') and
					U.DELETED='0' and 
					U.UNFINISHED='0'
			";
			if ($context == "groupmembers"){
				$sql .= " and U.ID not in (SELECT USER_ID from USERS_GROUPS where GROUP_ID='$_GET[id]')";
			}
		}
		if ($searchwords){
			$sql = "
				select DISTINCT
					U.USERNAME, U.ID, U.FIRSTNAME, U.LASTNAME, U.TRANSFER_TO_GROUP
				from 
					USERS U, USERS_GROUPS UG, GROUPS G
				where 
					U.ID = UG.USER_ID and
					UG.GROUP_ID = G.ID and
					G.SITE_ID in (0,'$_SESSION[SELECTED_SITE]') and
					U.DELETED='0' and U.UNFINISHED='0' and
					(U.FIRSTNAME like '$searchwords%' or U.LASTNAME like '$searchwords%' or 
					U.EMAIL like '$searchwords%' or U.USERNAME like '$searchwords%' or 
					CONCAT(U.FIRSTNAME, CONCAT(' ', U.LASTNAME))='$searchwords')
			";
			if ($context == "groupmembers"){
				$sql .= " and U.ID not in (SELECT USER_ID from USERS_GROUPS where GROUP_ID='$_GET[id]')";
			}
		}
		if (!$sortdir){
	  		$sortdir = "DESC";
		}
		if ($sortby && $sortdir){
			$sql .= " order by U.$sortby $sortdir";
		}
		$result = mysql_query($sql);
		$tomt_ikon = "<img src='images/piltom.gif' border='0'>";
		if ($sortdir == "DESC"){
			$sortdir = "ASC"; 
			$sortdir_changed=true;
			$ikon = "<img src='images/pilned.gif' border='0'>";
		}
		if ($sortdir == "ASC" && !$sortdir_changed){
			$sortdir = "DESC";
			$ikon = "<img src='images/pilop.gif' border='0'>";
		}
		if ($context == "userlist"){
			$html .= "
				<table class='oversigt'>
					<tr class='trtop'>
		    			<!--
						<td class='kolonnetitel'>
							<a href='index.php?content_identifier=users&dothis=oversigt&sortby=ID&group_id=$group_id&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu' class='kolonnetitel'>ID&nbsp;".(($sortby=="ID") ?  $ikon : $tomt_ikon)."
						</td>
						-->
						<td class='kolonnetitel'><a href='index.php?content_identifier=users&dothis=oversigt&sortby=FIRSTNAME&group_id=$group_id&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&usersearch=$searchwords' class='kolonnetitel'>Fornavn&nbsp;" . (($sortby=="FIRSTNAME") ?  $ikon : $tomt_ikon) . "</td>
						<td class='kolonnetitel'><a href='index.php?content_identifier=users&dothis=oversigt&sortby=LASTNAME&group_id=$group_id&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&usersearch=$searchwords' class='kolonnetitel'>Efternavn&nbsp;" . (($sortby=="LASTNAME") ?  $ikon : $tomt_ikon) . "</td>
						<td class='kolonnetitel'><a href='index.php?content_identifier=users&dothis=oversigt&sortby=USERNAME&group_id=$group_id&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&usersearch=$searchwords' class='kolonnetitel'>Brugernavn&nbsp;" . (($sortby=="USERNAME") ?  $ikon : $tomt_ikon) . "</td>
						<td class='kolonnetitel'>Funktioner</td>
					</tr>
		  	";
			$i = 0;
			if (mysql_num_rows($result)){
				while ($row = mysql_fetch_array($result)){
					$i++;
					$c = $i % 2 + 1;
					$html .= "
						<tr class='oversigt$c' onmouseover='IEColorShift(this.id)' onmouseout='IEColorUnShift(this.id, $c)' id='pagerow_$i'>
							<td>".$row["FIRSTNAME"]."</td>
							<td>".$row["LASTNAME"]."</td>
							<td>".$row["USERNAME"]."</td>
							<td>
				 				<input type='button' class='lilleknap' value='Rediger' onclick='location=\"index.php?content_identifier=users&amp;dothis=rediger&amp;backtogroup=$group_id&amp;backtosearch=$searchwords&amp;id=" . $row["ID"] . "\"'>
	 							<input ".($_SESSION["CMS_USER"]["USER_ID"] == $row["ID"] ? "disabled" : "")." type='button' class='lilleknap' value='Slet' onclick='slet(" . $row["ID"] . ", \"users\")'>
				 				<input type='button' class='lilleknap' value='Velkomstmail' onclick='if (confirm(\"Vil du sende en mail til denne bruger med vedkommendes brugernavn og password?\")) location=\"index.php?content_identifier=users&dothis=resendinfo&amp;userid=$row[ID]\"'>
				 				".($row[TRANSFER_TO_GROUP] ? "<input type='button' class='lilleknap' value='Flyt til \"".($gname=returnFieldValue("GROUPS", "GROUP_NAME", "ID", $row[TRANSFER_TO_GROUP]))."\"' onclick='if (confirm(\"Vil du flytte denne bruger til gruppen $gname og sende vedkommende en velkomstmail?\")) location=\"index.php?content_identifier=users&dothis=transfer&amp;userid=$row[ID]&amp;transfertoid=$row[TRANSFER_TO_GROUP]\"'>" : "")."
							</td>
						</tr>
					";
				}
			} else {
				$html .= "<tr><td colspan='99'>Ingen brugere fundet, som opfylder kriterierne.</td></tr>";
			}
		  	$html .= "</table>";
		} else if ($context == "groupmembers"){
			$html .= "
				<table class='oversigt'>
					<tr class='trtop'>
		    			<!--
						<td class='kolonnetitel'>
							<a href='index.php?content_identifier=users&dothis=oversigt&sortby=ID&group_id=$group_id&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu' class='kolonnetitel'>ID&nbsp;".(($sortby=="ID") ?  $ikon : $tomt_ikon)."
						</td>
						-->
						<td class='kolonnetitel'><a href='index.php?content_identifier=groups&dothis=medlemmer&id=$_GET[id]&sortby=FIRSTNAME&group_id=$group_id&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&usersearch=$searchwords' class='kolonnetitel'>Fornavn&nbsp;" . (($sortby=="FIRSTNAME") ?  $ikon : $tomt_ikon) . "</td>
						<td class='kolonnetitel'><a href='index.php?content_identifier=groups&dothis=medlemmer&id=$_GET[id]&sortby=LASTNAME&group_id=$group_id&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&usersearch=$searchwords' class='kolonnetitel'>Efternavn&nbsp;" . (($sortby=="LASTNAME") ?  $ikon : $tomt_ikon) . "</td>
						<td class='kolonnetitel'><a href='index.php?content_identifier=groups&dothis=medlemmer&id=$_GET[id]&sortby=USERNAME&group_id=$group_id&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&usersearch=$searchwords' class='kolonnetitel'>Brugernavn&nbsp;" . (($sortby=="USERNAME") ?  $ikon : $tomt_ikon) . "</td>
						<td class='kolonnetitel'>Funktioner</td>
					</tr>
		  	";
			$i = 0;
			if (mysql_num_rows($result)){
				while ($row = mysql_fetch_array($result)){
					$i++;
					$c = $i % 2 + 1;
					$html .= "
						<tr class='oversigt$c' onmouseover='IEColorShift(this.id)' onmouseout='IEColorUnShift(this.id, $c)' id='pagerow_$i'>
							<td>".$row["FIRSTNAME"]."</td>
							<td>".$row["LASTNAME"]."</td>
							<td>".$row["USERNAME"]."</td>
							<td>
				 				<input type='button' class='lilleknap' value='Tilføj som medlem' onclick='makeMember($row[ID], $_GET[id])'>
							</td>
						</tr>
					";
				}
			} else {
				$html .= "<tr><td colspan='99'>Ingen brugere fundet, som opfylder kriterierne.</td></tr>";
			}
		  	$html .= "</table>";
		}
		return $html;
	}
	
	function varied_fields_per_group($user_id, $field_name, $table_name, $extra_field_id=false){
		$field_translate_title = cmsTranslateBackend("da", "subscribeDbFieldNames", $field_name);
	 	$html .= "
			<table class='oversigt'>
				<tr>
					<td class='kolonnetitel'>Gruppe</td>
					<td class='kolonnetitel'>".($field_translate_title != "" ? $field_translate_title : $field_name)." i denne gruppe (hvis forskellige fra ovenstående)</td>
				</tr>
		";
	 	$sql = "
			select 
				G.GROUP_NAME, G.ID as GROUP_ID
			from
				USERS_GROUPS UG, GROUPS G
			where
				UG.USER_ID='$user_id' and G.ID=UG.GROUP_ID and
				G.DELETED='0' and G.SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
		";
		$res = mysql_query($sql);
		while ($row = mysql_fetch_assoc($res)){
			$sql = "
				select 
					FIELD_VALUE, ID
				from 
					USERS_VARIED_FIELDS 
				where 
					FIELD_NAME='$field_name' and TABLE_NAME='$table_name' and
					USER_ID='$user_id' and GROUP_ID='$row[GROUP_ID]'
			";
			$res2 = mysql_query($sql);
			$row2 = mysql_fetch_assoc($res2);
			if (mysql_num_rows($res2)){
				$html .= "
					<tr>
						<td>$row[GROUP_NAME]</td>
						<td><input type='text' value='$row2[FIELD_VALUE]' class='inputfelt_kort' name='vary__".$field_name."__".$table_name."__".$user_id."__".$row[GROUP_ID]."__".$row2[ID]."'/></td>
					</tr>
				";
			} else {
				$html .= "
					<tr>
						<td>$row[GROUP_NAME]</td>
						<td><input type='text' value='' class='inputfelt_kort' name='vary__".$field_name."__".$table_name."__".$user_id."__".$row[GROUP_ID]."__"."NEW"."'/></td>
					</tr>
				";
			}
		}
		$html .= "</table>";
		return $html;
	}
	
	function save_varied_fields($POSTVARS){
		foreach ($POSTVARS as $k => $v){
			if (strstr($k, "vary__")){
				$temp = explode("__", $k);
				$field_name = $temp[1];
				$table_name = $temp[2];
				$user_id = $temp[3];
				$group_id = $temp[4];
				$vary_field_id = $temp[5];
				if ($vary_field_id == "NEW" && trim($v) != ""){
					$sql = "
						insert into USERS_VARIED_FIELDS (
							USER_ID, GROUP_ID, FIELD_NAME, TABLE_NAME, FIELD_VALUE
						) values (
							'$user_id', '$group_id', '$field_name', '$table_name', '$v'
						)
					";
					mysql_query($sql);
				} else if (is_numeric($vary_field_id) && $vary_field_id > 0 && trim($v) != ""){
					$sql = "
						update USERS_VARIED_FIELDS set
							FIELD_VALUE='$v'
						where
							USER_ID='$user_id' and GROUP_ID='$group_id' and FIELD_NAME='$field_name' and TABLE_NAME='$table_name'
						limit 1
					";
					mysql_query($sql);
				} else if (is_numeric($vary_field_id) && $vary_field_id > 0 && trim($v) == ""){
					$sql = "delete from USERS_VARIED_FIELDS where ID='$vary_field_id' limit 1";
					mysql_query($sql);
				}
			}
		}	
	}
	
?>