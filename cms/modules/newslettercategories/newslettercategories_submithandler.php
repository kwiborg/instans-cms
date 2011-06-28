<?php
	switch ($_POST["dothis"]){
	
		case "newcat":
			$nameFieldName = "nykat_".$_POST[groupid];
			$sql = "insert into NEWSLETTER_CATEGORIES (NAME, GROUP_ID) values ('".$_POST[$nameFieldName]."', '$_POST[groupid]')";
			mysql_query($sql);
			header("index.php?content_identifier=newslettercategories");
		break;

		case "newcatgroup":
			newsletter_addGroup($_POST[newgroup_title]);
			header("index.php?content_identifier=newslettercategories");
		break;
		
		case "deletecats":
			foreach ($_POST as $k=>$v){
				if (strstr($k, "category_")){
					$temp = explode("_", $k);
					$catId = $temp[1];
					newsletter_deleteCat($catId);
				}
				if (strstr($k, "catgroup_")){
					$temp = explode("_", $k);
					$groupId = $temp[1];
					newsletter_deleteGroup($groupId);				
				}
			}
			header("index.php?content_identifier=newslettercategories");
		break;

	}
?>