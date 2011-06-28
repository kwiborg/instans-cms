<?php
	/*	
		* PLUGIN: Adresseliste til brug i sidens primære indholdsfelt.
		* CJS
		* 22-01-2007
	*/
	
	function list_users($arr_content, $column_settings, $temp_image_id, $collapse_name=false, $displaymode="LIST"){
		// $displaymode er LIST for klassisk visning eller BOXES for thumbnail-agtig visning
		// 2007-04-30 - cjs-Switch vendt om (MAP)
		if (!$_GET[cjs]) $displaymode="BOXES";
		foreach ($column_settings as $key => $value){
			$arr_sql_user_cols[] = "U.$key";
		}
		$sql_user_cols = implode(", ", $arr_sql_user_cols);
		if ($arr_content[groupid]){
			$html .= "<h1>Kontakt: ".returnFieldValue("GROUPS", "GROUP_NAME", "ID", $arr_content[groupid])."</h1>";
			$sql = "
				select 
					U.ID, $sql_user_cols, G.ID as GROUP_ID, G.GROUP_NAME
				from
					USERS U, USERS_GROUPS UG, GROUPS G
				where
					U.ID=UG.USER_ID and G.ID=UG.GROUP_ID and
					".($arr_content[groupid] ? "G.ID='$arr_content[groupid]'" : "G.ID>0")." and
					USERLIST_OPEN='1' and
					U.NEVER_PUBLIC='0' and 
					U.DELETED='0'
				order by
					".($arr_content[sortaddresses] ? "$arr_content[sortaddresses] asc" : "POSITION asc")."
			";
			$res = mysql_query($sql);
			if ($displaymode == "LIST"){
				if (mysql_num_rows($res) > 0){
					$html .= "<table cellpadding='0' cellspacing='0' class='userlist_table'>";
					$header .= "
						<tr>
					";
					foreach ($column_settings as $key => $value){
						if ($key == "FIRSTNAME" && $collapse_name){
							$header .= "
								<th>
									<a href='$arr_content[baseurl]/index.php?pageid=$arr_content[pageid]&amp;groupid=$arr_content[groupid]&amp;sortaddresses=$key'>Navn</a>
								</th>
							";
						} else if ($key == "LASTNAME" && $collapse_name){
							$header .= "";
						} else if ($key == "CELLPHONE" && $column_settings["PHONE"]){
							$header .= "";
						} else {
							$header .= "
								<th>
									<a title='Sortér' href='$arr_content[baseurl]/index.php?pageid=$arr_content[pageid]&amp;groupid=$arr_content[groupid]&amp;sortaddresses=$key'>".cmsTranslate(subscribeDbFieldNames, $key)."</a>
								</th>
							";
						}
	
					}
					$header .= "
						</tr>
					";
					if ($arr_content[groupid]){
						$html .= $header;
					}
					while ($row = mysql_fetch_assoc($res)){
						if (!$arr_content[groupid] && $row[GROUP_ID] != $old_gid){
							$html .= "<tr><td colspan='10'><h1>Adresseliste for \"".returnFieldValue("GROUPS", "GROUP_NAME", "ID", $row[GROUP_ID])."\"</h1></td></tr>".$header;
						}
						$i++;
						$html .= "
							<tr class='".($i%2==0?"even":"odd")."'>";
						foreach ($row as $key => $val){
							if ($column_settings[$key] == true){
								if ($collapse_name){
									if ($key == "FIRSTNAME"){
										$html .= "<td>x</td><td>$row[FIRSTNAME] $row[LASTNAME]</td>";
									}
									if ($key == "LASTNAME"){
										$html .= "";
									}
								}
								if ($key == "EMAIL"){
									$html .= "<td>".($row[EMAIL] ? safeAddress($row[EMAIL], "Send mail", "Send mail", 0, 0) : "")."</td>";
								} else if ($key == "PHONE"){
									$html .= "<td>".phone($row)."</td>";
								} else if ($key == "CELLPHONE" && $column_settings["PHONE"]){
									$html .= "";	
								} else if ($key == "FIRSTNAME" || $key == "LASTNAME"){
									$html .= "<td><a href='$arr_content[baseurl]/index.php?pageid=$arr_content[pageid]&amp;groupid=$row[GROUP_ID]&amp;uid=$row[ID]' title='Klik for mere info om personen'>".$val."</a></td>";
								} else if ($key == "IMAGE_ID"){
									if ($val != 0){
										$html .= "<td><img src='".image_url($val)."' width='50' /></td>";
									} else {
										$html .= "<td><img src='".image_url($temp_image_id)."' width='50' /></td>";
									}
								} else if ($key == "JOB_TITLE"){
									$html .= "<td>".return_varied_field_value($row[ID], $row[GROUP_ID], "USERS", "JOB_TITLE", $row[JOB_TITLE])."</td>";
								} else {
									$html .= "<td>".$val."</td>";
								}
							}
						}
						$html .= "
							</tr>
						";
						$old_gid = $row[GROUP_ID];
					}
					$html .= "</table>";
					return $html;
				} else {
					return false;
				}
			} else {
				while ($row = mysql_fetch_assoc($res)){
					$image_url = ($row[IMAGE_ID] ? image_url($row[IMAGE_ID]) : image_url($temp_image_id));
					$phone = phone($row);
					$html .= "
						<div class='tiled_user_item'>
							<img src='".$image_url."' alt='$row[FIRSTNAME] $row[LASTNAME]' />
							<p class='user_name'>$row[FIRSTNAME] $row[LASTNAME]</p>
							<p class='user_job_title'>".return_varied_field_value($row[ID], $row[GROUP_ID], "USERS", "JOB_TITLE", $row[JOB_TITLE])."</p>
							".($phone != false ? "<p>Tlf. ".$phone."</p>" : "")."
							".($row[EMAIL] ? "<p class='user_contact_link'><a href='$arr_content[baseurl]/index.php?mode=formware&amp;formid=1&amp;setfields[9]=".$row[FIRSTNAME]." ".$row[LASTNAME]."&amp;uid=$row[ID]&amp;ticket=".md5($row[ID].$row[FIRSTNAME]."1nstansFlyvema5kine")."'>Send e-mail</a></p>" : "")."
						</div>
					";
				}
				$html .= "<div style='clear:both'></div>";
				return $html;
			}
		}
	}
	
	function return_varied_field_value($user_id, $group_id, $table_name, $field_name, $fallback_value){
		$sql = "
			select
				FIELD_VALUE 
			from 
				USERS_VARIED_FIELDS 
			where 
				USER_ID='$user_id' and GROUP_ID='$group_id' and
				TABLE_NAME='$table_name' and FIELD_NAME='$field_name' 
			limit 1
		";
		$res = mysql_query($sql);
		$row = mysql_fetch_assoc($res);
		if (trim($row[FIELD_VALUE]) != ""){
			return $row[FIELD_VALUE];
		} else {
			return $fallback_value;
		}
	}
	
	function list_public_groups($arr_content){
		$sql = "
				select 
					G.ID as GROUP_ID, G.GROUP_NAME
				from
					GROUPS G
				where
					USERLIST_OPEN='1'
				order by
					GROUP_NAME asc
		";
		$res = mysql_query($sql);
		$ahtml .= "<h1>Kontaktpersoner</h1>";
		$ahtml .= "<ul class='userlist_grouplist'>";
		while ($row = mysql_fetch_assoc($res)){
			$row[ANTAL] = group_count($row[GROUP_ID]);
			if ($row[ANTAL] > 0){
				$ahtml .= "
					<li>
						<a href='$arr_content[baseurl]/index.php?pageid=$arr_content[pageid]&amp;groupid=$row[GROUP_ID]'>$row[GROUP_NAME]</a>&nbsp;($row[ANTAL] ".($row[ANTAL] == 1 ? "person" : "personer").")
					</li>
				";
			}
		}
		$ahtml .= "</ul>";
		return $ahtml;
	}
	
	function group_count($group_id){
		$sql = "
			select
				U.ID
			from
				USERS U, USERS_GROUPS UG
			where 
				U.NEVER_PUBLIC='0' and
				U.DELETED='0'
				and UG.GROUP_ID='$group_id'
				and UG.USER_ID=U.ID
		";
		$res = mysql_query($sql);
		return mysql_num_rows($res);
	}
	
	function phone($row){
		if ($row[PHONE] && $row[CELLPHONE]){
			return "$row[PHONE] / $row[CELLPHONE]";
		} else if ($row[PHONE] && !$row[CELLPHONE]){
			return "$row[PHONE]";
		} else if (!$row[PHONE] && $row[CELLPHONE]){
			return "$row[CELLPHONE]";
		} else {
			return false;
		}
		
	}
	
	function build_contact_page($arr_content, $column_settings, $user_details_to_show, $temp_image_id, $always_show_groups=true){
		if ($arr_content[groupid] && !$arr_content[uid]){
			$list = list_users($arr_content, $column_settings, $temp_image_id, false);
			if ($list){
				$ahtml .= $list;
			}
			if ($always_show_groups){
				$ahtml .= list_public_groups($arr_content);
			} else {
				$ahtml .= "<p><a href='$arr_content[baseurl]/index.php?pageid=$arr_content[pageid]'>&laquo;&nbsp;Tilbage til oversigten</a></p>";
			}
		} else if ($arr_content[groupid] && $arr_content[uid]){
			$ahtml .= show_user($arr_content, $temp_image_id, $user_details_to_show);
		} else {
			$ahtml .= "<h1>Kontakt</h1>";
			$ahtml .= "<p>Vælg en gruppe nedenfor for at se en liste over kontaktpersoner.</p>";
			$ahtml .= list_public_groups($arr_content);
		}
		return $ahtml;
	}
	
	function show_user($arr_content, $temp_image_id, $user_details_to_show=array()){
		$sql = "select * from USERS where ID='$arr_content[uid]' and NEVER_PUBLIC='0'";
		$res = mysql_query($sql);
		$row = mysql_fetch_assoc($res);
		$ahtml .= "<h1>".$row[FIRSTNAME]." ".$row[LASTNAME]."</h1>";
		if ($row[JOB_TITLE]){
			$ahtml .= "<p id='job_title'>$row[JOB_TITLE]</p>";
		}
		$ahtml .= "
			<div id='user_details_wrapper'>
		";
		if ($row[IMAGE_ID]){
			$ahtml .= "<div id='user_image'><img src='".image_url($row[IMAGE_ID])."' alt='$row[FIRSTNAME] $row[LASTNAME]'/></div>";
		} else {
			$ahtml .= "<div id='user_image'><img src='".image_url($temp_image_id)."' alt='$row[FIRSTNAME] $row[LASTNAME]'/></div>";
		}
		$ahtml .= "
			<div id='user_details'>
			".details_table($row, $user_details_to_show)."
			</div>
		";
		$ahtml .= "</div>";
		$ahtml .= "<div id='user_details_back'><a href='$arr_content[baseurl]/index.php?pageid=$arr_content[pageid]&amp;groupid=$arr_content[groupid]'>&laquo;&nbsp;Tilbage til oversigten</a></div>";
		return $ahtml;
	}
	
	function details_table($row_userdata, $user_details_to_show){
		$ahtml .= "<table id='user_details_table'>";
		foreach($user_details_to_show as $key => $val){
			if ($val){
				if ($row_userdata[$key]){
					$ahtml .= "<tr><td class='column_name'>".cmsTranslate(subscribeDbFieldNames, $key).":&nbsp;</td><td>".$row_userdata[$key]."</td></tr>";
				}
				if ($key == "FORMMAIL" && $row_userdata["EMAIL"]){
					$ahtml .= "<tr><td class='column_name'>"."Kontaktformular".":&nbsp;</td><td>"."<a href='$arr_content[baseurl]/index.php?mode=formware&amp;formid=".$val."&amp;setfields[9]=".$row_userdata[FIRSTNAME]." ".$row_userdata[LASTNAME]."&amp;uid=$row_userdata[ID]&amp;ticket=".md5($row_userdata[ID].$row_userdata[FIRSTNAME]."1nstansFlyvema5kine")."'>Klik her for at kontakte $row_userdata[FIRSTNAME] $row_userdata[LASTNAME]</a>"."</td></tr>";
				}
			}
		}
		$ahtml .= "</table>";
		return $ahtml;
	}

	function image_url($image_id){
		global $picturearchive_UploaddirAbs;
		$sql = "
			select 
				PP.FILENAME, PF.FOLDERNAME,
				PP.ID as IMAGE_ID, PF.ID as FOLDER_ID
			from 
				PICTUREARCHIVE_PICS PP, PICTUREARCHIVE_FOLDERS PF
			where
				PF.ID=PP.FOLDER_ID and PP.ID='$image_id'
		";
		$res = mysql_query($sql);
		$row = mysql_fetch_assoc($res);
		$image_url = $picturearchive_UploaddirAbs."/".$row[FOLDERNAME]."/".$row[FILENAME];
		if ($row[IMAGE_ID] && $row[FOLDER_ID]){
			return $image_url;
		} else {
			return false;
		}
	}
?>

<?php
	$column_settings = array(
		"IMAGE_ID" => true,
		"FIRSTNAME" => true,
		"LASTNAME" => true,
		"JOB_TITLE" => true,
		"PHONE" => true,
		"CELLPHONE" => true,
		"EMAIL" => true
	);
	
	$user_details_to_show = array(
		"PHONE" => true,
		"CELLPHONE" => true,
		"CV" => true,
		"FORMMAIL" => 1
	);
	
	$temp_image_id = 117;

	echo build_contact_page($arr_content, $column_settings, $user_details_to_show, $temp_image_id, true)
?>