<?php
	function newsletter_returnGroupedCategories(){			
		$sql = "
			select 
				NCG.NAME as GROUP_NAME, NCG.ID as GROUP_ID 
			from 
				NEWSLETTER_CATEGORYGROUPS NCG
			where
				NCG.SITE_ID in (0,'$_SESSION[SELECTED_SITE]') and
				NCG.DELETED='0' 
			order by
				NCG.NAME asc
		";
		$result = mysql_query($sql);
		if (mysql_num_rows($result)){
			$html .= "<div class='knapbar'><input type='button' value='Slet afkrydsede kategorier' onclick='deleteCheckedCats()' /></div>";
			while ($row = mysql_fetch_array($result)){
				$html .= "
					<h2 style='padding-left:0'>
						<input type='checkbox' name='catgroup_$row[GROUP_ID]' value='$row2[CAT_ID]' onclick='newslettercat_checkSub(this, this.checked)' />&nbsp;$row[GROUP_NAME]
					</h2>
				";
				$sql = "
					select NC.NAME as CAT_NAME, NC.ID as CAT_ID
					from NEWSLETTER_CATEGORIES NC
					where NC.DELETED='0' and NC.GROUP_ID='$row[GROUP_ID]'
					order by NC.NAME asc
				";
				$result2 = mysql_query($sql);
				if (mysql_num_rows($result2) == 0){
					$html .= "Der er ikke oprettet nogen interessekategorier i denne gruppe endnu.";
				} else {
					while ($row2 = mysql_fetch_array($result2)){
						$html .= "<input type='checkbox' name='category_$row2[CAT_ID]"."_catgroup_$row[GROUP_ID]"."_' value='$row2[CAT_ID]' />&nbsp;$row2[CAT_NAME]<br/>";
					}
				}
				$html .= "
					<p style='background-color:#ddd; padding:5px'>
						Opret ny kategori i <strong>$row[GROUP_NAME]</strong> med denne titel: 
						<input type='text' class='inputfelt' name='nykat_$row[GROUP_ID]' id='nykat_$row[GROUP_ID]' />&nbsp;
						<input type='button' value='Opret' class='lilleknap' onclick='newCategory($row[GROUP_ID])' />
					</p>
				";
			}
			$html .= "<div class='knapbar'><input type='button' value='Slet afkrydsede kategorier' onclick='deleteCheckedCats()' /></div>";
		} else {
			$html .= "<p>Der er ikke oprettet nogen interessekategorier.</p>";
		}
		return $html;
	}
	
	function newsletter_addCatToGroup($catName, $groupId){
		$sql = "
			insert into NEWSLETTER_CATEGORIES (NAME, GROUP_ID) 
			values ('$catName', '$groupId')
		";
		if (mysql_query($sql)){
			return true;
		}
	}

	function newsletter_addGroup($groupName){
		$sql = "
			insert into NEWSLETTER_CATEGORYGROUPS (NAME, SITE_ID) 
			values ('$groupName' ,'$_SESSION[SELECTED_SITE]')
		";
		if (mysql_query($sql)){
			return true;
		}
	}

	function newsletter_deleteCat($catId){
		$sql = "
			update NEWSLETTER_CATEGORIES set DELETED='1' where ID='$catId'
		";
		if (mysql_query($sql)){
			return true;
		}
	}

	function newsletter_deleteGroup($groupId){
		$sql = "
			update NEWSLETTER_CATEGORYGROUPS set DELETED='1' where ID='$groupId'
		";
		mysql_query($sql);
		$sql = "
			update NEWSLETTER_CATEGORIES set DELETED='1' where GROUP_ID='$groupId'
		";
		mysql_query($sql);
	}
?>
