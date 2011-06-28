<?php	
	function folders_sqlresult($parent_id, $arr_content){
		$sql = "
			select 
				ID, TITLE, FOLDER_DESCRIPTION, PUBLIC_FOLDER
			from 
				PICTUREARCHIVE_FOLDERS 
			where
				PARENT_ID='$parent_id' and
				SITE_ID in (0,'$arr_content[site]')
			order by 
				TITLE asc
		";
		return $res = mysql_query($sql);
	}

	function image_sqlresult($image_id){
		$sql = "
			select 
				PP.*, PF.FOLDERNAME
			from 
				PICTUREARCHIVE_PICS PP, PICTUREARCHIVE_FOLDERS PF
			where
				PP.ID='$image_id' and PF.ID=PP.FOLDER_ID
				and PP.UNFINISHED = 0
			order by
				PP.CREATED_DATE desc
			limit 1
		";
		$res = mysql_query($sql);
		return $row = mysql_fetch_assoc($res);
	}

	function gallery_imagebrowser_folderlist($arr_content, $parent_id=0, &$html=""){
		$res = folders_sqlresult($parent_id, $arr_content);
		$html .= "<ul>";
		while ($row = mysql_fetch_assoc($res)){
			$html .= "<li>";
			$html .= "<a href='".$arr_content[baseurl]."/index.php?mode=picturearchive&amp;folderid=".$row[ID]."'>".$row[TITLE]."</a>";
			if (gallery_folder_haschildren($row[ID])){
				gallery_imagebrowser_folderlist($arr_content, $row[ID], $html);
				$html .= "</li>";
			} else {
				$html .= "</li>";
			}
		}
		$html .= "</ul>";
		return $html;
	}

	function gallery_imagebrowser_folderdivs($arr_content, $parent_id=0, &$html="", $level=0){
		global $gallery_showMaxLevels;
		$res = folders_sqlresult($parent_id, $arr_content);
		while ($row = mysql_fetch_assoc($res)){
			if ($row[PUBLIC_FOLDER] == 1 && $level <= $gallery_showMaxLevels){
				$nf = gallery_folder_subfolders($row[ID]);
				$n=gallery_folder_numpics($row[ID]);				
				$html .= "
					<div class='imagefolder level_$level'>
						<div class='imagefolder_thumb'>
							".(($thumb = gallery_returnFolderFirstImage($row[ID], "NEWEST")) != false ? "<a href='".$arr_content[baseurl]."/index.php?mode=picturearchive&amp;folderid=".$row[ID]."'><img src='".$thumb."' /></a>" : "")."
						</div>
						<div class='imagefolder_desc'>
							<a href='".$arr_content[baseurl]."/index.php?mode=picturearchive&amp;folderid=".$row[ID]."'>".$row[TITLE]."</a>&nbsp;(".($n > 0 ? ($n == 1 ? "$n ".cmsTranslate("gallery_image") : "$n ".cmsTranslate("gallery_images")) : "").($nf > 0 && $n > 0 ? " / " : "").($nf > 0 ? ($nf == 1 ? "$nf ".cmsTranslate("gallery_folder") : "$nf ".cmsTranslate("gallery_folders")) : "").")	
							<br/>
							$row[FOLDER_DESCRIPTION]
						</div>
					</div>
				";
			}
			if (gallery_folder_haschildren($row[ID])){
				gallery_imagebrowser_folderdivs($arr_content, $row[ID], $html, $level+1);
			} else {
			}
		}
		return $html;
	}
	
	function gallery_imagebrowser_folderselectoptions($arr_content, $parent_id=0, &$html="", $level=0){
		$res = folders_sqlresult($parent_id, $arr_content);
		while ($row = mysql_fetch_assoc($res)){
			if ($row[PUBLIC_FOLDER] == 1){
				$html .= "<option ".($row[ID]==$arr_content[folderid] ? "selected" : "")." value='".$arr_content[baseurl]."/index.php?mode=picturearchive&amp;folderid=".$row[ID]."'>";
				$html .= str_repeat("&nbsp;&nbsp;&nbsp;", $level)."&raquo;&nbsp;".$row[TITLE];
				$html .= "</option>";
			}
			if (gallery_folder_haschildren($row[ID])){
				gallery_imagebrowser_folderselectoptions($arr_content, $row[ID], $html, $level+1);
			} else {
			}
		}
		return $html;
	}
	
	function gallery_imagebrowser_folderselect($arr_content){
		return "<div id='gallery_folderselect'>".cmsTranslate("gallery_jump").":&nbsp;<select id='select_gallery_folder'>".gallery_imagebrowser_folderselectoptions($arr_content)."</select></div>";
	}
	
	function gallery_folder_haschildren($folder_id){
		$sql = "select ID from PICTUREARCHIVE_FOLDERS where PARENT_ID='$folder_id'";
		$res = mysql_query($sql);
		if (mysql_num_rows($res) > 0){
			return true;
		}
		return false;
	}  
	
	function gallery_thumbnail_index($folder_id, $arr_content){
		global $picturearchive_UploaddirAbs;
		$sql = "
			select 
				PP.FILENAME, PF.FOLDERNAME, 
				PP.ALTTEXT, PP.DESCRIPTION,
				PP.ID as IMAGE_ID,
				PP.VIEW_COUNT,
				PP.CREATED_DATE, PP.SIZE_X, PP.SIZE_Y, PP.AUTHOR_ID, PP.ORIGINAL_FILENAME,
				PP.ORIGINAL_ARCHIVED
			from 
				PICTUREARCHIVE_PICS PP, PICTUREARCHIVE_FOLDERS PF
			where
				PP.FOLDER_ID='$folder_id' and PF.ID=PP.FOLDER_ID and PF.PUBLIC_FOLDER='1' and PP.UNFINISHED='0'
			order by
				PP.POSITION,PP.CREATED_DATE desc
		";
		$res = mysql_query($sql);
		while ($row = mysql_fetch_assoc($res)){
			$alt_title_text = "" .
				($row[ALTTEXT] ? cmsTranslate("gallery_title").": ".$row[ALTTEXT]."\n" : "").
				($row[DESCRIPTION] ? "Beskrivelse: ".$row[DESCRIPTION]."\n" : "").
				cmsTranslate("gallery_uploaded").": ".returnNiceDateTime($row[CREATED_DATE], 0)."\n".
			    cmsTranslate("gallery_uploadedby").": ".returnFieldValue("USERS", "FIRSTNAME", "ID", $row[AUTHOR_ID])."&nbsp;".
			    returnFieldValue("USERS", "LASTNAME", "ID", $row[AUTHOR_ID])."\n".
				cmsTranslate("gallery_size").": $row[SIZE_X] x $row[SIZE_Y] pixels"."";
			$html .= "
				<div class='thumbnail_index_item'>
					<a title='".$alt_title_text."' href='".$arr_content[baseurl]."/index.php?mode=picturearchive&folderid=".$folder_id."&imageid=$row[IMAGE_ID]'>
						<img border='0' title='".$alt_title_text."' alt='".$alt_title_text."' src='".$picturearchive_UploaddirAbs."/".$row[FOLDERNAME]."/thumbs/".$row[FILENAME]."' 
					/></a>
					<br/>
					$row[VIEW_COUNT] visn.<br/>
					<a href='".$arr_content[baseurl]."/index.php?mode=picturearchive&folderid=".$folder_id."&imageid=$row[IMAGE_ID]'>".cmsTranslate("gallery_show")."</a>
					".($row[ORIGINAL_ARCHIVED]==1 ? "<br/><a target='_blank' href='".$picturearchive_UploaddirAbs."/".$row[FOLDERNAME]."/originals/".$row[FILENAME]."'>".cmsTranslate("gallery_getoriginal")."</a>" : "")."
				</div>
			";
		}
		$subfolders = gallery_imagebrowser_folderdivs($arr_content, $folder_id);
		if ($subfolders){
			$html .= "<div class='before_subfolders'><h2>Mappen indeholder også disse mapper:</h2></div>";
			$html .= $subfolders;
		}
		return $html;
	}
	
	function gallery_returnFolderFirstImage($folderid, $mode="NEWEST"){
		// mode kan være NEWEST eller FIRSTPOS
		global $picturearchive_UploaddirAbs;
		$sql = "
			select 
				PP.FILENAME, PF.FOLDERNAME
			from 
				PICTUREARCHIVE_PICS PP, PICTUREARCHIVE_FOLDERS PF
			where
				PP.FOLDER_ID='$folderid' and PF.ID=PP.FOLDER_ID and PP.UNFINISHED = '0'
			order by ".($mode=="NEWEST" ? "PP.CREATED_DATE desc" : "PP.POSITION asc")." limit 1
		";
		$res = mysql_query($sql);
		$row = mysql_fetch_assoc($res);
		if ($row[FILENAME]){
			return $picturearchive_UploaddirAbs."/".$row[FOLDERNAME]."/thumbs/".$row[FILENAME];
		} else {
			return false;
		}
	}
	
	function gallery_folder_numpics($folder_id){
		$sql = "select COUNT(ID) from PICTUREARCHIVE_PICS where FOLDER_ID='$folder_id' and UNFINISHED = '0'";
		$res = mysql_query($sql);
		$row = mysql_fetch_row($res);
		return $row[0];
	}

	function gallery_folder_subfolders($folder_id){
		$sql = "select COUNT(ID) from PICTUREARCHIVE_FOLDERS where PARENT_ID='$folder_id' and PUBLIC_FOLDER='1'";
		$res = mysql_query($sql);
		$row = mysql_fetch_row($res);
		return $row[0];
	}
		
	function gallery_folderpath($folder_id, &$akku, $arr_content){
		$sql = "select TITLE, ID, PARENT_ID from PICTUREARCHIVE_FOLDERS where ID='$folder_id' and PUBLIC_FOLDER='1'";
		$res = mysql_query($sql);
		while ($row = mysql_fetch_assoc($res)){
			$akku[] = "<a href='".$arr_content[baseurl]."/index.php?mode=picturearchive&folderid=$row[ID]'>$row[TITLE]</a>";
			gallery_folderpath($row[PARENT_ID], $akku, $arr_content);
		}
		return implode("&nbsp;&raquo;&nbsp;", array_reverse($akku));
	}
	
	function gallery_image_url($folder_id, $image_id){
		global $picturearchive_UploaddirAbs;
		$sql = "
			select 
				PP.FILENAME, PF.FOLDERNAME, PF.PUBLIC_FOLDER,
				PP.ID as IMAGE_ID, PF.ID as FOLDER_ID
			from 
				PICTUREARCHIVE_PICS PP, PICTUREARCHIVE_FOLDERS PF
			where
				PP.FOLDER_ID='$folder_id' and PF.ID=PP.FOLDER_ID and
				PP.ID='$image_id' and PF.PUBLIC_FOLDER='1' and PP.UNFINISHED = '0'
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

	function gallery_prev_next_nav($folder_id, $image_id){
		$imagepos = returnFieldValue("PICTUREARCHIVE_PICS", "POSITION", "ID", $image_id);
		$sql = "
			select 
				PP.ID as IMAGE_ID, PF.ID as FOLDER_ID
			from 
				PICTUREARCHIVE_PICS PP, PICTUREARCHIVE_FOLDERS PF
			where
				PP.FOLDER_ID='$folder_id' and PF.ID=PP.FOLDER_ID and
				PP.POSITION > '$imagepos' and PF.PUBLIC_FOLDER='1' and PP.UNFINISHED = '0'
			order by 
				PP.POSITION asc limit 1
		";
		$res = mysql_query($sql);
		$next = mysql_fetch_assoc($res);
		$sql = "
			select 
				PP.ID as IMAGE_ID, PF.ID as FOLDER_ID
			from 
				PICTUREARCHIVE_PICS PP, PICTUREARCHIVE_FOLDERS PF
			where
				PP.FOLDER_ID='$folder_id' and PF.ID=PP.FOLDER_ID and
				PP.POSITION < '$imagepos' and PF.PUBLIC_FOLDER='1' and PP.UNFINISHED = '0'
			order by 
				PP.POSITION desc limit 1
		";
		$res = mysql_query($sql);
		$prev = mysql_fetch_assoc($res);
		return array("PREV" => $prev, "NEXT" => $next);
	}
	
	function gallery_nav($arr_nav, $arr_content){
		$html .= "
			<div class='gallery_nav'>
				<table width='100%'><tr><td align='left'>
				".($arr_nav["PREV"]["IMAGE_ID"] ? "<a title='".cmsTranslate("gallery_prev")."' href='".$arr_content[baseurl]."/index.php?mode=picturearchive&folderid=".$arr_nav["PREV"]["FOLDER_ID"]."&amp;imageid=".$arr_nav["PREV"]["IMAGE_ID"]."'>&laquo;&nbsp;".cmsTranslate("gallery_prev")."</a>" : "")."
				".($arr_nav["PREV"]["IMAGE_ID"] && $arr_nav["NEXT"]["IMAGE_ID"] ? "<span class='gallery_nav_divider'>|</span>" : "")."
				".($arr_nav["NEXT"]["IMAGE_ID"] ? "<a title='".cmsTranslate("gallery_next")."' href='".$arr_content[baseurl]."/index.php?mode=picturearchive&folderid=".$arr_nav["NEXT"]["FOLDER_ID"]."&amp;imageid=".$arr_nav["NEXT"]["IMAGE_ID"]."'>".cmsTranslate("gallery_next")."&nbsp;&raquo;</a>" : "")."
				</td><td align='right'>&uarr;&nbsp;<a title='".cmsTranslate("gallery_index")."' href='".$arr_content[baseurl]."/index.php?mode=picturearchive&folderid=".$arr_content[folderid]."'>".cmsTranslate("gallery_index")."</a></td></tr></table>
			</div>
		";
		return $html;	
	}
	
	function update_viewcount($image_id){
		$sql = "update PICTUREARCHIVE_PICS set VIEW_COUNT = VIEW_COUNT + 1 where ID='$image_id'";
		mysql_query($sql);
	}
	
	function gallery_display_image($arr_content){
		global $picturearchive_UploaddirAbs, $picturearchive_Uploaddir;
		$arr_nav = gallery_prev_next_nav($arr_content[folderid], $arr_content[imageid]);
		$navbar .= gallery_nav($arr_nav, $arr_content);
		$image_url = gallery_image_url($arr_content[folderid], $arr_content[imageid]);
		$image_data = image_sqlresult($arr_content[imageid]);
		if ($image_url){
			$orig_url 		 	= $picturearchive_UploaddirAbs."/".$image_data[FOLDERNAME]."/originals/".$image_data[FILENAME];
			$orig_server_url	= $picturearchive_Uploaddir."/".$image_data[FOLDERNAME]."/originals/".$image_data[FILENAME];
			if ($image_data[ORIGINAL_ARCHIVED] == 1){
				$kilobytes = number_format((filesize($orig_server_url) / pow(2,10)), 0);
			}
			$alt_title_text = "" .
				($image_data[ALTTEXT] ? cmsTranslate("gallery_title").": ".$image_data[ALTTEXT]."\n" : "").
				($image_data[DESCRIPTION] ? "Beskrivelse: ".$image_data[DESCRIPTION]."\n" : "").
				cmsTranslate("gallery_uploaded").": ".returnNiceDateTime($image_data[CREATED_DATE], 0)."\n".
			    cmsTranslate("gallery_uploadedby").": ".returnFieldValue("USERS", "FIRSTNAME", "ID", $image_data[AUTHOR_ID])."&nbsp;".
			    returnFieldValue("USERS", "LASTNAME", "ID", $image_data[AUTHOR_ID])."\n".
				cmsTranslate("gallery_size").": $image_data[SIZE_X] x $image_data[SIZE_Y] pixels"."";
			$html .= "
				$navbar
				<div id='gallery_image'>
					<img src='".$image_url."' border='0' alt='$alt_title_text' title='$alt_title_text' width='$image_data[SIZE_X]' height='$image_data[SIZE_Y]' />
					<div id='gallery_image_desc'>
					".($image_data[DESCRIPTION] ? "$row[DESCRIPTION]" : "")."
					</div>
					<table id='gallery_image_info'>
						<tr>
							<td class='fieldname'>".cmsTranslate("gallery_uploaded").":</td>
							<td class='fieldvalue'>".returnNiceDateTime($image_data[CREATED_DATE], 1)."</td>
						</tr>
                        <!--
						<tr>
							<td class='fieldname'>".cmsTranslate("gallery_uploadedby").":</td>
							<td class='fieldvalue'>".returnFieldValue("USERS", "FIRSTNAME", "ID", $row[AUTHOR_ID])."&nbsp;".returnFieldValue("USERS", "LASTNAME", "ID", $row[AUTHOR_ID])."</td>
						</tr>-->".($image_data[ORIGINAL_ARCHIVED]==1 ? "
						<tr>
							<td class='fieldname'>".cmsTranslate("gallery_original").":</td>
							<td class='fieldvalue'><a href='".$orig_url."'>".cmsTranslate("gallery_download")."</a>&nbsp;(".$kilobytes." kb)</td>
						</tr>" : "")
						."
					</table>
				</div>
				$navbar
			";
		} else {
			$html .= cmsTranslate("gallery_noimage");
		}
		return $html;
	}

?>