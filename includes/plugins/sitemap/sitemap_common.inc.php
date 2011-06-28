<?php
	/// SITEMAP PLUGIN
	/// CJS, 14/6/2007
	
	function sitemap($siteid, $arr_menuids, $arr_content, $arr_modes=false){
		$sitemap .= "<div id='sitemap'>";
		foreach ($arr_menuids as $k => $menuid){
			$html = "";
			$sitemap .= "<h2 class='sitemap_menutitle'>".$k."</h2>";
			$sitemap .= newBuildFrontendMenuSitemap($html, $siteid, $menuid, 0, 0, $arr_content, "", "");
		}
		if ($arr_modes){
			foreach ($arr_modes as $mode){
				if ($mode == "NEWS"){
					$sql = "select ID, NAME from NEWSFEEDS where SITE_ID='$siteid' order by ID asc";
					$res = mysql_query($sql);
					if (mysql_num_rows($res)){
						$sitemap .= "<h2>"."Nyhedsarkiver"."</h2>";
						$sitemap .= "<ul>";
						while ($row = mysql_fetch_assoc($res)){
							$sitemap .= "<li><a href='$arr_content[baseurl]/index.php?mode=news&feedid=$row[ID]'>".$row[NAME]."</a></li>";
						}
						$sitemap .= "</ul>";
					}
				}
				if ($mode == "EVENTS"){
					$sql = "select ID, NAME from CALENDARS where SITE_ID='$siteid' order by ID asc";
					$res = mysql_query($sql);
					if (mysql_num_rows($res)){
						$sitemap .= "<h2>"."Kalendere"."</h2>";
						$sitemap .= "<ul>";
						while ($row = mysql_fetch_assoc($res)){
							$sitemap .= "<li><a href='$arr_content[baseurl]/index.php?mode=events&calendarid=$row[ID]'>".$row[NAME]."</a></li>";
						}
						$sitemap .= "</ul>";
					}
				}
			}
		}
		$sitemap .= "</div>";
		return $sitemap;
	}

	function newBuildFrontendMenuSitemap(&$html, $siteid, $menuid, $parentid, $level, $arr_content, $prefixIfHasChilds="", $css_id=""){
		global $menuPageIdsWithOtherMenus;
	
		//	If $siteid id not set (""), the site will come from $arr_content. This is the INTENDED DEFAULT BEHAVIOR of this function!
		if (!is_numeric($siteid)) {
			$siteid = $arr_content[site];
		}
		//	If $menuid id not set (""), the menu will be auto-selected from parameters set in $arr_content. This is the INTENDED DEFAULT BEHAVIOR of this function!
		if (!is_numeric($menuid)) {
			$sql = "select MENU_ID from MENUS where SITE_ID in (0, $arr_content[site]) and DEFAULT_LANGUAGE in (0,".returnLanguageId($arr_content[lang]).") LIMIT 1";
			$res = mysql_query($sql);
			if (mysql_num_rows($res)>0) {
				$menuid = mysql_result($res,0);
			}
		}
		$pageid = $arr_content[pageid];
	 	if ($parentid == 0 || array_search($parentid, $_SESSION["CURRENT_OPEN_MENUS"]) || true == true) {
	   		$sql = "
	    		select 
	     			ID, PARENT_ID, BOOK_ID, SITE_ID, MENU_ID, PROTECTED, BREADCRUMB, 
		 			IS_MENUPLACEHOLDER, POINTTOPAGE_URL 
				from 
		 			PAGES 
	    		where 
	     			SITE_ID='$siteid' and MENU_ID='$menuid' and PARENT_ID='$parentid' and 
					DELETED='0' and UNFINISHED='0' and PUBLISHED='1' and NO_DISPLAY='0' 
	    		order by
	     			POSITION asc
	   		";
			$result = mysql_query($sql) or die(mysql_error());
			$html .= "\n<ul".($css_id != "" && $parentid == 0 ? " id='$css_id'" : "").">";
			while ($row = mysql_fetch_array($result)) {
				if ($menuFunctionName = $menuPageIdsWithOtherMenus[$row["ID"]]){
					$html .= call_user_func($menuFunctionName, $row["ID"]);
				} else {
					if ($row[ID]){
						if ($row["PROTECTED"]==1 || $row["PROTECTED"]==2 && checkPageRights($row[ID])){
							$hasChilds = hasChildren($row[ID]); 
							$html .= "\n\t<li>";
								if ($row[ID] == $pageid) {
								$class = " class='selected'";
							} else {
								$class = "";
							}	
							if ($row[POINTTOPAGE_URL]) {
								if ($prefixIfHasChilds){
									$html .= "
										<span class='".($hasChilds ? "menuPrefix_hasSubpoints" : "menuPrefix_noSubpoints")."'>".
											$prefixIfHasChilds."
										</span>
									";
								}
								$html .= "<a title='$row[BREADCRUMB]' href='".$row[POINTTOPAGE_URL]."'$class>".$row[BREADCRUMB]."</a>";
							} else if ($row[IS_MENUPLACEHOLDER]==1) {
								$actualPageId = returnFirstPageInMenuPlaceholder($row[ID]);
								if ($prefixIfHasChilds){
									$html .= "
										<span class='".($hasChilds ? "menuPrefix_hasSubpoints" : "menuPrefix_noSubpoints")."'>".
											$prefixIfHasChilds."
										</span>
									";
								}
								$html .= "<a title='$row[BREADCRUMB]' href='$arr_content[baseurl]/index.php?pageid=".$actualPageId.($row[BOOK_ID] ? "&amp;bookid=".$row[BOOK_ID] : "")."'$class>".$row[BREADCRUMB]."</a>";
							} else {
								if ($prefixIfHasChilds){
									$html .= "
										<span class='".($hasChilds ? "menuPrefix_hasSubpoints" : "menuPrefix_noSubpoints")."'>".
											$prefixIfHasChilds."
										</span>
									";
								}
								$html .= "<a title='$row[BREADCRUMB]' href='$arr_content[baseurl]/index.php?pageid=".$row[ID].($row[BOOK_ID] ? "&amp;bookid=".$row[BOOK_ID] : "")."'$class>".$row[BREADCRUMB]."</a>";
							}				
							if ($hasChilds) {
								newBuildFrontendMenuSitemap($html, $row[SITE_ID], $row[MENU_ID], $row[ID], $level+1, $arr_content, $prefixIfHasChilds);
								$html .= "</li>";
							} else {
								$html .= "</li>";
							}
						}
					}
				}
			}
			$html .= "</ul>";
		}
		return $html;
	 }
?>
