<?php
require_once($_SERVER[DOCUMENT_ROOT]."/cms/scripts/html_mime_mail/htmlMimeMail.php");

function general_menupath($pageid, $mode, $clean_sti=array(), $menuPageIdsWithOtherMenus=array()) {
    // global $clean_sti, $menuPageIdsWithOtherMenus;
    if (!$mode){
       $sql = "select SITE_ID, MENU_ID, PARENT_ID, ID, BREADCRUMB, POINTTOPAGE_URL, BOOK_ID from PAGES where ID='$pageid' and PUBLISHED='1' and DELETED='0'";
    
        $result = mysql_query($sql) or die(__FUNCTION__." ".mysql_error());
        while ($row = mysql_fetch_array($result)){
            if (!$menuPageIdsWithOtherMenus[$row["ID"]] && checkPageRights($row[ID])){
                if ($row[POINTTOPAGE_URL]){
                    $clean_sti[] = $row[ID];
                } else {
                    $clean_sti[] = $row[ID];
                }
             }
            $clean_sti = general_menupath($row[PARENT_ID], $mode, $clean_sti, $menuPageIdsWithOtherMenus);
        }
    }
    return $clean_sti;
}

function perform_actions($arr_content) {
// 2007-04-18	-	Changed input from $dothis to $arr_content for site separation
	$dothis = $arr_content[dothis];
	if ($dothis != "") {
		if ($dothis == "login" && $_POST[username] && $_POST[password]) {
			$userdetails = loginUser($_POST[username], $_POST[password], $arr_content); 
			if ($userdetails == false) {
				header("location: index.php?mode=loginfailure"); 
				exit;
			} else {
				if (checkPermissionFE("FE_LOGIN", $userdetails[2])){
					$_SESSION["LOGGED_IN"] = 1;
					$_SESSION["USERDETAILS"] = $userdetails;
					// Where to go after login?
					if ($_POST[ref] == $arr_content[baseurl].$arr_content[sitepath]."login" || 
						$_POST[ref] == $arr_content[baseurl].$arr_content[sitepath]."login/" || 
						strstr($_POST[ref], "?mode=login") || 
						$_POST[ref] == "") {
						// Loginsiden kaldt direkte (eller uden "ref"), gå til defineret side - alternativt forsiden 
							if ($userdetails[3] == "") {
								global $fallback_login_gotourl;
								if ($fallback_login_gotourl==""){
									$goto = $arr_content[baseurl].$arr_content[sitepath];
								} else {
									$goto = $fallback_login_gotourl;
								}
							} else {
								$goto = $userdetails[3];
							}
					} else {
						// Loginsiden kaldt ved forespørgsel på beskyttet side - eller med redirect method, gå til beskyttet side
						$goto = $_POST[ref];
					}
					header("location: " . $goto);
					exit;	
				} else {
					header("location: index.php?mode=loginfailure"); 
					exit;
				}
			}
		}
		if ($dothis == "logout") {
			if ($_SESSION["LOGGED_IN"]) {
				unset($_SESSION["LOGGED_IN"]);
				unset($_SESSION["USERDETAILS"]);
			}
			header("location: $arr_content[baseurl]");   
		}
		if ($dothis == "updateProfile") {
			updateProfile($_POST);
			$updated = "ja";
		}
		if ($dothis == "gemgallerimappe") {
			createGalleryFolder($_POST);
			header("location:index.php?pageid=$pageid&show=gallery&gid=$gid");
		}
		if ($dothis == "gembillede") {	
			createGalleryImage($_POST);
			header("location:index.php?pageid=$pageid&show=gallery&gid=$gid");
		}  
		if ($dothis == "sendtilven") {
			sendLinkToFriend($_POST);
			header("location: index.php?mode=stf&sent=1");
			exit;
		}
		if ($dothis == "subscribe") {
			subscribe($letter_id, $email, "subscribe");
			header("location: index.php?mode=sub&adr=$email");
			exit;
		}
		if ($dothis == "unsubscribe") {
			subscribe($letter_id, $email, "unsubscribe");
			header("location: index.php?mode=unsub&adr=$email");
			exit;   
		}  
	}
} 

/////////////////////////////////////////////////////////////////////////////////////////////////////
// * FUNCTIONS: PAGE RENDERING + REWRITING
/////////////////////////////////////////////////////////////////////////////////////////////////////

function validate_content_array($arr_content) {
	// Function to ensure that the requested content is allowed for the requested site.
	// That is the content has a matching or global (0) site_id
	// Function returns true/false
	// Note that this function performs a "loose" validation which means that it will return true except on 
	// explicit mismatch problems

//	echo "Array to validate:<pre>";
//	print_r($arr_content);
//	echo "</pre>";


	if (isset($arr_content[pageid])) {
		// Showing a page
		$sql = "select count(*) from PAGES where ID = '$arr_content[pageid]' and SITE_ID = '$arr_content[site]'";
		$res = mysql_query($sql);
		if (mysql_result($res,0) == 0) {
			return false;
		}
	}	
	
	if ($arr_content[mode] == "picturearchive") {
		// Gallery-mode
		if (isset($arr_content[folderid]) && !isset($arr_content[imageid])) {
			// Showing a folder
			$sql = "select count(*) from PICTUREARCHIVE_FOLDERS where ID = '$arr_content[folderid]' and SITE_ID in (0,'$arr_content[site]')";
			$res = mysql_query($sql);
			if (mysql_result($res,0) == 0) {
				return false;
			}
		} elseif (isset($arr_content[imageid])) {
			// Showing an image		
			$sql = "select count(*) from PICTUREARCHIVE_FOLDERS PF, PICTUREARCHIVE_PICS PP where PP.FOLDER_ID = PF.ID and PP.ID = '$arr_content[imageid]' and PF.SITE_ID in (0,'$arr_content[site]')";
			$res = mysql_query($sql);
			if (mysql_result($res,0) == 0) {
				return false;
			}
		}
	}

	if ($arr_content[mode] == "news") {
		// News mode
		if (isset($arr_content[feedid]) && !isset($arr_content[newsid])) {
			// Showing a news feed / archive
			$sql = "select count(*) from NEWSFEEDS where ID = '$arr_content[feedid]' and SITE_ID in (0,'$arr_content[site]')";
			$res = mysql_query($sql);
			if (mysql_result($res,0) == 0) {
				return false;
			}
		} elseif (isset($arr_content[newsid])) {
			// Showing a news item		
			$sql = "select count(*) from NEWS where ID = '$arr_content[newsid]' and (SITE_ID in (0,'$arr_content[site]') or GLOBAL_STATUS = 1)";
			$res = mysql_query($sql);
			if (mysql_result($res,0) == 0) {
				return false;
			}
		}
	}

	if ($arr_content[mode] == "events") {
		// Calendar mode
		if (isset($arr_content[calendarid]) && !isset($arr_content[eventid])) {
			// Showing a calendar
			$sql = "select count(*) from CALENDARS where ID = '$arr_content[calendarid]' and SITE_ID in (0,'$arr_content[site]')";
			$res = mysql_query($sql);
			if (mysql_result($res,0) == 0) {
				return false;
			}
		} elseif (isset($arr_content[eventid])) {
			// Showing a calendar event		
			$sql = "select count(*) from EVENTS where ID = '$arr_content[eventid]' and (SITE_ID in (0,'$arr_content[site]') or GLOBAL_STATUS = 1)";
			$res = mysql_query($sql);
			if (mysql_result($res,0) == 0) {
				return false;
			}
		}
	}

	if ($arr_content[mode] == "shop") {
		// Shop mode
		if (isset($arr_content[group]) && !isset($arr_content[product])) {
			// Showing a productgroup
			$sql = "select count(*) from SHOP_PRODUCTGROUPS where ID = '$arr_content[group]' and SITE_ID ='$arr_content[site]'";
			$res = mysql_query($sql);
			if (mysql_result($res,0) == 0) {
				return false;
			}
		} elseif (isset($arr_content[product])) {
			// Showing a product
			// Validate that the product belongs to the right group
			// AND that the productgroup belongs to the right site		
			$sql = "select count(*) 
						from 
							SHOP_PRODUCTS P, 
							SHOP_PRODUCTS_GROUPS SPG, 
							SHOP_PRODUCTGROUPS G 
						where 
							P.ID = SPG.PRODUCT_ID and 
							SPG.GROUP_ID = G.ID and 
							P.PRODUCT_NUMBER = '$arr_content[product]' and 
							SPG.GROUP_ID = '$arr_content[group]' and 
							G.SITE_ID = '$arr_content[site]'";
			$res = mysql_query($sql);
			if (mysql_result($res,0) == 0) {
				return false;
			}
		}
	}

	if ($arr_content[mode] == "search") {
		// Searchresults are filtered by site, so no need to validate this mode
		return true;
	}
	if ($arr_content[mode] == "stf") {
		// Send to friend can only send referring page, so no need to validate this mode
		return true;
	}

	if ($arr_content[mode] == "newsletter") {
		// Newsletter mode
		if (isset($arr_content[newsletterid]) && $arr_content[action] != "approveproof") {
			// "newsletterid" refers to newsletter_template id. Check site against that!
			$sql = "select count(*) from NEWSLETTER_TEMPLATES where ID = '$arr_content[newsletterid]' and SITE_ID in (0,'$arr_content[site]')";
			$res = mysql_query($sql);
			if (mysql_result($res,0) == 0) {
				return false;
			}
		}
	}

	if ($arr_content[mode] == "formware") {
		// Form mode
		if (isset($arr_content[formid])) {
			// Showing a form
			$sql = "select count(*) from DEFINED_FORMS where ID = '$arr_content[formid]' and SITE_ID in (0,'$arr_content[site]')";
			$res = mysql_query($sql);
			if (mysql_result($res,0) == 0) {
				return false;
			}
		}
	}

	if ($arr_content[mode] == "groups") {
		// GROUPS mode
		if (isset($arr_content[groupid])) {
			// F.instance used when registering users for this group
			$sql = "select count(*) from GROUPS where ID = '$arr_content[groupid]' and SITE_ID in (0,'$arr_content[site]')";
			$res = mysql_query($sql);
			if (mysql_result($res,0) == 0) {
				return false;
			}
		}
	}

	if ($arr_content[mode] == "blogs") {
		// Blog mode
		if (isset($arr_content[blogid]) && !isset($arr_content[postid])) {
			// Showing a blog
			$sql = "select count(*) from BLOGS where ID = '$arr_content[blogid]' and SITE_ID in (0,'$arr_content[site]')";
			$res = mysql_query($sql);
			if (mysql_result($res,0) == 0) {
				return false;
			}
		} elseif (isset($arr_content[postid])) {
			// Showing a blogpost
			$sql = "select count(B.ID) from BLOGS B, BLOGPOSTS BP where B.ID = BP.BLOG_ID and BP.ID = '$arr_content[postid]' and B.SITE_ID in (0,'$arr_content[site]')";
			$res = mysql_query($sql);
			if (mysql_result($res,0) == 0) {
				return false;
			}
		}
	}

	return true;
}

function return_preferred_sitedomain_from_language($content_language, $site_id) {
	$sql = "select * from CMS_SITEDOMAINS where SITE_ID = '$site_id' and PREFERRED_FOR_LANGUAGE = '$content_language' limit 1";
	if ($res = mysql_query($sql)) {
		if (mysql_num_rows($res) > 0) {
			$row = mysql_fetch_assoc($res);
			return $row[SUBDOMAIN].".".$row[DOMAIN];
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function return_content_language($arr_content) {
	if (isset($arr_content[pageid])) {
		// Showing a page
		$sql = "select LANGUAGE from PAGES where ID = '$arr_content[pageid]'";
	}	
	
//	if ($arr_content[mode] == "picturearchive") {
			// NO LANGUAGE DEFINITION AVAILABLE
//	}

	if ($arr_content[mode] == "news") {
		// News mode
		if (isset($arr_content[feedid]) && !isset($arr_content[newsid])) {
			// Showing a news feed / archive
			$sql = "select DEFAULT_LANGUAGE from NEWSFEEDS where ID = '$arr_content[feedid]'";
		} elseif (isset($arr_content[newsid])) {
			// Showing a news item		
			$sql = "select LANGUAGE from NEWS where ID = '$arr_content[newsid]'";
		}
	}

	if ($arr_content[mode] == "events") {
		// Calendar mode
		if (isset($arr_content[calendarid]) && !isset($arr_content[eventid])) {
			// Showing a calendar
			$sql = "select DEFAULT_LANGUAGE from CALENDARS where ID = '$arr_content[calendarid]'";
		} elseif (isset($arr_content[eventid])) {
			// Showing a calendar event		
			$sql = "select LANGUAGE from EVENTS where ID = '$arr_content[eventid]'";
		}
	}

//	if ($arr_content[mode] == "shop") {
			// NO LANGUAGE DEFINITION AVAILABLE
//	}

//	if ($arr_content[mode] == "search") {
			// NO LANGUAGE DEFINITION AVAILABLE
//	}

//	if ($arr_content[mode] == "stf") {
			// NO LANGUAGE DEFINITION AVAILABLE
//	}

	if ($arr_content[mode] == "newsletter") {
		// Newsletter mode
		if (isset($arr_content[newsletterid]) && $arr_content[action] != "approveproof") {
			// "newsletterid" refers to newsletter_template id. Check language against that!
			$sql = "select LANGUAGE_ID from NEWSLETTER_TEMPLATES where ID = '$arr_content[newsletterid]'";
		}
	}

//	if ($arr_content[mode] == "formware") {
			// NO LANGUAGE DEFINITION AVAILABLE
//	}

//	if ($arr_content[mode] == "groups") {
			// NO LANGUAGE DEFINITION AVAILABLE
//	}

	if ($arr_content[mode] == "blogs") {
		// Blog mode
		if (isset($arr_content[blogid]) && !isset($arr_content[postid])) {
			// Showing a blog
			$sql = "select LANGUAGE_ID from BLOGS where ID = '$arr_content[blogid]'";
		} elseif (isset($arr_content[postid])) {
			// Showing a blogpost
			$sql = "select B.LANGUAGE_ID from BLOGS B, BLOGPOSTS BP where B.ID = BP.BLOG_ID and BP.ID = '$arr_content[postid]'";
		}
	}

	if ($sql != "") {
		if ($res = mysql_query($sql)) {
			if (mysql_num_rows($res) > 0) {
				$content_language_id = mysql_result($res,0);
				if (is_numeric($content_language_id)) {
					return returnFieldValue("LANGUAGES", "SHORTNAME", "ID", $content_language_id);
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	} else {
		return $arr_content[lang];
	}
}

function render_page_content($site_to_show){	
	// Get content for page
	$arr_content = return_content_build_array($site_to_show);
/*
	echo "render_page_content:<pre>";
	print_r($arr_content);
	echo "</pre>";
*/

	// Check that the content language matches the arr_content language
	// Only relevant when a single-site implementation runs multiple languages on multiple domains
	// The system will redirect to the preferred sitedomain for the language with the same REQUEST_URI.
	$content_language = return_content_language($arr_content);
	if ($content_language != $arr_content[lang]) {
		$content_language_id = returnFieldValue("LANGUAGES", "ID", "SHORTNAME", $content_language);
		$new_domain = return_preferred_sitedomain_from_language($content_language_id, $arr_content[site]);

		// Must be different from current domain to redirect!
		if ($_SERVER["HTTP_HOST"] != $new_domain) {
			$new_url .= "http://$new_domain".$_SERVER[REQUEST_URI];
			header( "HTTP/1.1 301 Moved Permanently" );
			header( "Status: 301 Moved Permanently" );
			header( "Location: $new_url" );
			exit(0); // This is Optional but suggested, to avoid any accidental output
		}
	}

	// Check for 301 redirect on this content item
	if ($arr_content[redirect_to_url] != "") {
		if (checkMD5key($arr_content[redirect_key], $arr_content[redirect_to_url])) {
			header( "HTTP/1.1 301 Moved Permanently" );
			header( "Status: 301 Moved Permanently" );
			header( "Location: ".$arr_content[redirect_to_url] );
			exit(0); // This is Optional but suggested, to avoid any accidental output
		}
	}


	// Check $arr_content integrity
	if (!validate_content_array($arr_content)) {
		echo "Kan ikke vise siden - ulovlige parametre i URL / Cannot show page - illegal parameters in url";
		exit;
	}

	// Register "clickedlink" from newsletter
	if (isset($arr_content[clickedlink])) {
		global $cmsAbsoluteServerPath;
		include_once($cmsAbsoluteServerPath."/modules/newsletter/frontend/newsletter_common.inc.php");
		newsletter_stats_register($arr_content[nid], $arr_content[uid], "click", $arr_content[openkey], $arr_content[clickedlink]);
	}
	
	// If form submitted, do this
	if ($arr_content[saveformdata]==1){
		/// SPAM CAPTCHA CHECK
		if ($_POST["formware_captcha"]){
			$captcha_okay = spam_captcha($_SESSION["security_code"], $_POST["formware_captcha"]);
		} else {
			$captcha_okay = true;
		}
		if ($captcha_okay && ($_POST["formware_captcha"] !="" || !isset($_POST["formware_captcha"]))) {
			parseSubmittedForm();
		} else {
			$arr_content[mode] = "formware";
			$arr_content[formid] = $_POST[formid];
			$arr_content[captcha] = "failed";
			unset($arr_content[saveformdata]);
		}
	}

	if (isset($arr_content[dothis])) {
		perform_actions($arr_content);
	}

/*
	echo "ARR_CONTENT:<pre>";
	print_r($arr_content);
	echo "</pre>";
*/
	// Check for and optionally include header-plugin (output before browser-output)
	if (isset($arr_content[pageid])) {
		echo includeHeaderPlugin($arr_content);
	}

	// Check for and optionally include mode-header-plugin (output before browser-output)
	if (isset($arr_content[mode])) {
		include_mode_plugin($arr_content, "pagetop");
	}

	// Begin output buffering
	ob_start();

    // Reset menu foldin/out
    initFrontendMenu();
    
    $path = returnHTMLMenuPath($arr_content[pageid], $arr_content[mode], "Du er her: ", "", "&nbsp;&nbsp;&raquo;&nbsp;&nbsp;", $arr_content); 
    if ($arr_content[pageid]){
        $arr_temp = general_menupath($arr_content[pageid], $arr_content[mode]);
        if (count($arr_temp)>0){
            $arrPath = array_reverse($arr_temp);
            $toplevel_id = $arrPath[0];
            $arr_content[thread_id] = $toplevel_id;
        } 
    }
    if (!$arrPath) $arrPath=array();
    $arr_content["arrPath"] = $arrPath;
    $arr_content["toplevel_id"] = $toplevel_id;

	// Include template
	load_template($arr_content);

	// End output buffering and return page-html
	$html = ob_get_clean();
	
	if ($arr_content[replacepngimagetags] == 1) {
		$html = replacePngTags($html, "$arr_content[baseurl]/includes/images");
	}

	// Make sure that any internal links point to the correct current domain
	$html = str_replace(returnBASE_URL($arr_content[site]), $arr_content[baseurl], $html);
	
	if ($arr_content[usemodrewrite] == 1) {
		$html = rewrite_links($html, $arr_content);
	}
	return $html;
}

function rewrite_links($str_html, $arr_content) {
	// 2007-10-01	-	RegEx now also matches form actions in addition to anchor hrefs (MAP)
	
	$base_4_regex = str_replace("/", "\/", $arr_content[baseurl]);
	$str_html = preg_replace_callback("/(<[a||form][^>]*[href||action]=[\"|\'])($base_4_regex)([^\"\']*)([\"|\'][^>]*>)/", "rewrite_urls_callback", $str_html);

	return $str_html;
}



function load_template($arr_content) {
	//	print_r($arr_content);
	// Function to determine and subsequently include correct template
	
	// 1. Check if we are showing a page to find the PAGES->TEMPLATE(_ID)
	if (isset($arr_content[pageid])) {
		// Get template id
//		$sql = "select TEMPLATE, SITE_ID from PAGES where ID = '$arr_content[pageid]'";
// 2007-04-13	-	Updated to make sure that template is allowed for this site! 
//					Template is valid if the SITE_ID matches or is 0 (common template)
		$sql = "select 
					P.TEMPLATE, 
					P.SITE_ID 
				from
					PAGES P,
					TEMPLATES T
				where 
					T.ID = P.TEMPLATE and
					T.SITE_ID in (0,".$arr_content[site].") and
					P.ID = '$arr_content[pageid]'";
		$result = mysql_query($sql);
		if (mysql_num_rows($result)>0) {
			$row = mysql_fetch_array($result);
			if (($row[TEMPLATE] != "") && ($row[TEMPLATE] != "0")) {
				$template_id = $row[TEMPLATE];
			}
		}
	}

	// 1.5. Check if we're trying to show a picturearchive image
	if (isset($arr_content[folderid]) && isset($arr_content[imageid])) {
		// Get template id
		$sql = "select ID as TEMPLATE from TEMPLATES where TYPE = 'IMAGE' && SITE_ID = '".$arr_content[site]."'";
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		if (($row[TEMPLATE] != "") && ($row[TEMPLATE] != "0")) {
			$template_id = $row[TEMPLATE];
		}	
	}

	// 1.6. Check if we're showing a blog, if yes, use blog's template (CJS)
	if ($arr_content[mode] == "blogs"){
		// Get template id
		$sql = "select TEMPLATE_ID from BLOGS where SITE_ID = '".$arr_content[site]."' and ID='".$arr_content[blogid]."'";
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		if (($row[TEMPLATE_ID] != "") && ($row[TEMPLATE_ID] != "0")) {
			$template_id = $row[TEMPLATE_ID];
		}	
	}

	// 2. Fall back on default template 
	if (!isset($template_id)) {
		$template_id = return_default_template($arr_content[site]);
		if (!$template_id) {
			return "Kunne ikke hente template! / Could not load template!";
		}
	}

	// 3. Determine what kind of template to use
//	if (!isset($arr_content["printversion"])) {
	if (!check_method($arr_content[methods], "print")) {
		if (!isset($arr_content["mode"])) {
			// No mode defined
			$use_template_type = "PATH";
		} elseif ($arr_content["mode"] == "cart") {
			if ($arr_content["action"] == "showcart" || $arr_content["action"] == "sendcart"  || $arr_content["action"] == "sendcartcomplete" || $arr_content["action"] == ""){
				$use_template_type = "CARTTEMPLATE_PATH";
			}
			if ($arr_content["action"] == "checkoutform" || $arr_content["action"] == "checkoutfinalize" || $arr_content["action"] == "checkoutcomplete" || $arr_content["action"] == "checkoutpay"){
				$use_template_type = "CHECKOUTTEMPLATE_PATH";
			}
		} else {
			// No special case detected, use default
			$use_template_type = "PATH";
		}
	} else {
		// Printversion ordered
		$use_template_type = "PRINTTEMPLATE_PATH";
	}

	// 4. Get template path
	$sql = "select $use_template_type from TEMPLATES where ID = '$template_id'";
	if ($result = mysql_query($sql)) {
		$template_path = mysql_result($result, 0);
	} else {
		die("Kunne ikke hente template! / Could not load template! ($use_template_type)(db)");	
	}	

	// 5. Include template
	if (file_exists($template_path)) {
		include($template_path);
	} else {
		die("Kunne ikke hente template! / Could not load template! ($use_template_type)(file)");	
	}
}

function return_rewrite_parameters_array($str_path) {
	$arr_path = explode("/",$str_path);
	$arr_path = array_diff($arr_path, array("")); // Remove extra array-positions caused by trailing slash(es)
	$arr_path = array_merge($arr_path, array());
	return $arr_path;
}

function return_url_elements_array() {
	$arr_url_elements = parse_url($_SERVER[REQUEST_URI]);
	$arr_url_elements[query] = convert_url_parameterstring($arr_url_elements[query]);
	return $arr_url_elements;
}

function return_content_build_array($site_to_show) {
//	echo "Building \$arr_content for site: $site_to_show";

	// Set current language and current site session variables
	$arr_content[site] = $site_to_show;
	$arr_content[lang] = set_current_language();
	$arr_content[show_printversion_link] = 1;

	// Translate $_POST vars
	foreach ($_POST as $key => $value) {
		$arr_content[$key] = $value;
	}
	$arr_url_elements = return_url_elements_array();

	// Populate $arr_content with necessary variables from config.inc and database
	$arr_content[baseurl] = "http://".$_SERVER["HTTP_HOST"];
	$arr_content[sitepath] = "/".return_rewrite_keyword("", $arr_content[site], "SITES", 0);

	
	global $useGrahicalHeadings;
	if ($useGrahicalHeadings) {
		$arr_content[usegraphicalheadings] = 1;
	}
	global $useModRewrite;
	if ($useModRewrite) {
		$arr_content[usemodrewrite] = 1;
	}
	global $replacePNGimages;
	if ($replacePNGimages) {
		$arr_content[replacepngimagetags] = 1;
	}

	if (!isset($_GET[rw])) {
		// 1. URL has not been rewritten
		$arr_content[rw] = "false";
		// 1.1. Check if home page is requested (empty query string)
		if ($arr_url_elements["query"] == "") {
			// Build and return content array for home page
			$arr_content[pageid] = getFrontpageId($arr_content[lang], $arr_content[site]);
		} else {
			// 1.2. Translate query string to array elements - only if NOT rewritten!
			$arr_get = explode("&",$arr_url_elements[query]);
			foreach ($arr_get as $key => $value) {
				$this_get = explode("=", $value);
				if (count($this_get) == 2) {
					// Site parameter NOT allowed to override value given to function
					if ($this_get[0] != "site") {
						$arr_content[$this_get[0]] = urldecode($this_get[1]);
					}
					// Check for language change
					if ($this_get[0] == "lang"  && !isset($_GET["grant"]) && !isset($_GET["clickedlink"])) {
						$arr_content[pageid] = getFrontpageId($this_get[1], $arr_content[site]);
						unset($arr_content[http_status]); 
					}
				}
			}
			// 1.3 Translate old-skool array elements into new common structure for rw/non-rw
			if (isset($arr_content[printversion])) {
				$arr_content[methods][] = array("print");
				unset($arr_content[printversion]);
			}
			if (isset($arr_content[offset])) {
				$arr_content[methods][] = array("offset",$arr_content[offset]);
				unset($arr_content[offset]);
			}

		}
		// 1.4 Add support for mode search through POST form
		if (strlen($_POST[searchwords_x])>0) {
			$arr_content[mode] = "search";
			$arr_content[searchwords] = $_POST[searchwords_x];
		}
		// 1.5 Fallback to frontpage if no mode is set
		if ($arr_content[mode]=="") {
				if (!isset($arr_content[pageid])) {
					$arr_content[pageid] = getFrontpageId($arr_content[lang], $arr_content[site]);
				}
		}
		$arr_content = add_content_redirect($arr_content);

		// 1.6 Check if url could have been rewritten, and redirect if possible
		global $useModRewrite_enforce_url_rewrite;
		if ($useModRewrite_enforce_url_rewrite) {
			$current_url = "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
			$rewritten_url = rewrite_urls_callback($current_url);
			if ($current_url != $rewritten_url) {
				$arr_content[redirect_to_url] = $rewritten_url;
				$arr_content[redirect_key] = returnMD5key($rewritten_url);
			}
/*
	Unfinished functionality:

			// 1.6.1 - When showing home page, make sure there is no superflous elements in url
			if ($arr_content[pageid] == getFrontpageId($arr_content[lang], $arr_content[site]) && strstr($_SERVER["REQUEST_URI"], "index.php")) {
				// only works on frontpage for current language
				$arr_content[redirect_to_url] = $arr_content[baseurl]."/";
				$arr_content[redirect_key] = returnMD5key($arr_content[baseurl]."/");
			}
*/

		}
		return $arr_content;
	} else {
		// 2. URL has been rewritten by Apache mode_rewrite
		$arr_content[rw] = "true";
		
		// 2.1 Remove site-path from string
		$str_path = convert_url_parameterstring($arr_url_elements[path]); // Make sure all is safe (and lowercase)
		$site_folder = $arr_content[sitepath];

		// and from $str_path
		if ($arr_content[sitepath] != "/"){
			$str_path = str_replace($arr_content[sitepath], "", $str_path);
		}
		
		while (strstr($str_path, "//")) {
			$str_path = str_replace("//", "/", $str_path); // Strip double slashes
		}
		// 2.2 Convert rewrite parameters to array		
		$arr_path = return_rewrite_parameters_array($str_path);

/*
		echo "<br/>ARR_PATH:<pre>";
		print_r($arr_path);
		echo "</pre>";
*/

		// 2.3 Check for rewrite methods - rewrite methods must be last in $arr_path to register
		$arr_path = register_rewrite_methods($arr_path, $arr_content);

		// 2.4 Check for single rewrite keyword - a rewrite keyword must be last in $arr_path to register
		if (count($arr_path) > 0) {
			$arr_keyword = check_rewrite_keyword($arr_path[count($arr_path)-1], $site_to_show);
			if (is_array($arr_keyword)) {
				if ($arr_keyword[0] != "SITES") {
					$arr_content[keyword_table]	= $arr_keyword[0];
					$arr_content[keyword_id] 	= $arr_keyword[1];
					$arr_content[keyword]		= $arr_path[count($arr_path)-1];
					if ($arr_content[keyword_table] != "RESERVED") {
						array_pop($arr_path); // keyword registered, now pop it!
					}
				}
			}
		}
		
		// 2.5	Check for single rewrite mode - a siterewrite mode must be last in $arr_path to register
		// 		2007-06-18	-	Changed to allow mode keyword anywhere in $arr_path instead of just as last parameter
		unset($arr_content[mode]);
		foreach ($arr_path as $key => $value) {
			if ($arr_content[mode] = check_rewrite_mode($value)) {
				// Yes, the path element is a mode keyword
				array_shift($arr_path); // mode registered, now pop it!
				break; // Stop looping path elements, most likely after first iteration since mode keywords are ofteh first in array
			} else {
				unset($arr_content[mode]);
			}
		}
/*
	// 2007-10-01 - NOW 2.7.5 MOVED DOWN TO BE EXECUTED AFTER ADDING EXTRA VALUES TO ACCOMODATE POSSIBLE FILTERS F.INSTANCE FOR PRODUCTGROUP
		// 2.6 Check for remaining parameters
		if (count($arr_path) > 0) {
			$arr_content[errors][] = array("error" => "not all rewrite parameters parsed", "remaining parameters" => $arr_path);
		}
*/
		// 2.7 Add extra arr_content values derived from the values already set
		if ((!isset($arr_content[mode]) || $arr_content[mode]=="") && !isset($arr_content[keyword_id])) {
			// Nothing useful in $arr_content, default to home page
			// Build and return content array for home page
			header("Status: 404 Not Found");
			$arr_content[http_status] = 404;
			$arr_content[pageid]	= getFrontpageId($arr_content[lang], $arr_content[site]);
		}
		if (!isset($arr_content[mode]) && isset($arr_content[keyword_id])) {
			// No mode given - defaults to page!
			$arr_content[pageid] = $arr_content[keyword_id];
			// Now, remove left-over url-pagekeys
			if (count($arr_path) > 0) {
				$str_keysleft = implode($arr_path, "','");
				// Test keys against database
				$pagekey_sql = "select distinct RK.KEYWORD from 
								REWRITE_KEYWORDS RK
							where 
								RK.KEYWORD in ('$str_keysleft') and 
								RK.TABLENAME = 'PAGES' and 
								RK.SITE_ID = $arr_content[site]";
				if ($pagekey_result = mysql_query($pagekey_sql)) {
					if (mysql_num_rows($pagekey_result) > 0) {
						while ($pagekey = mysql_fetch_assoc($pagekey_result)) {
							$key_pos = array_search($pagekey[KEYWORD], $arr_path);
							if (is_numeric($key_pos)) {
								unset($arr_path[$key_pos]);
							}
						}
					}
				}
			}

		} elseif ($arr_content[mode] == "news" && $arr_content[keyword_table] == "NEWSFEEDS") {
			// Newsfeed
			$arr_content[feedid] = $arr_content[keyword_id];
		} elseif ($arr_content[mode] == "news" && $arr_content[keyword_table] == "NEWS") {
			// Newsitem
			$arr_content[newsid] = $arr_content[keyword_id];
			if (!isset($arr_content[feedid])) {
				$arr_content[feedid] = returnFieldValue("NEWS", "NEWSFEED_ID", "ID", $arr_content[newsid]);
			}
		} elseif ($arr_content[mode] == "events" && $arr_content[keyword_table] == "CALENDARS") {
			// Calendar
			$arr_content[calendarid] = $arr_content[keyword_id];
		} elseif ($arr_content[mode] == "events" && $arr_content[keyword_table] == "EVENTS") {
			// Calendar item (event)
			$arr_content[eventid] = $arr_content[keyword_id];
			if (!isset($arr_content[calendarid])) {
				$arr_content[calendarid] = returnFieldValue("EVENTS", "CALENDAR_ID", "ID", $arr_content[eventid]);
			}
		} elseif ($arr_content[mode] == "picturearchive" && $arr_content[keyword_table] == "PICTUREARCHIVE_FOLDERS") {
			// Picturearchive folder (gallery)
			$arr_content[folderid] = $arr_content[keyword_id];
		} elseif ($arr_content[mode] == "blogs" && $arr_content[keyword_table] == "BLOGS") {
			// Blog
			$arr_content[blogid] = $arr_content[keyword_id];
		} elseif ($arr_content[mode] == "blogs" && $arr_content[keyword_table] == "BLOGPOSTS") {
			// Single blogpost
			$arr_content[postid] = $arr_content[keyword_id];
			if (!isset($arr_content[blogid])) {
				$arr_content[blogid] = returnFieldValue("BLOGPOSTS", "BLOG_ID", "ID", $arr_content[postid]);
			}
		} elseif ($arr_content[mode] == "shop" && $arr_content[keyword_table] == "SHOP_PRODUCTGROUPS") {
			// Productgroup
			$arr_content[group] = $arr_content[keyword_id];
			$arr_content[action] = "showgroup";
		} elseif ($arr_content[mode] == "shop" && $arr_content[keyword_table] == "SHOP_PRODUCTS") {
			// Single product
			// $arr_content[product] = $arr_content[keyword_id]; NO!!! product=[PRODUCT_NUMBER], REMEMBER?!? so...
			$arr_content[product] = returnFieldValue("SHOP_PRODUCTS", "PRODUCT_NUMBER", "ID", $arr_content[keyword_id]);
			
			if (!isset($arr_content[group])) {
				// Use leftleft-over group url-array parameter for group identification
				if (count($arr_path) > 0) {
					// First get possible keys
					$key_sql = "select distinct RK.KEYWORD, RK.REQUEST_ID from 
									SHOP_PRODUCTS_GROUPS SPG,
									REWRITE_KEYWORDS RK
								where 
									SPG.GROUP_ID = RK.REQUEST_ID and
									SPG.PRODUCT_ID = '$arr_content[keyword_id]' and
									RK.TABLENAME = 'SHOP_PRODUCTGROUPS'";
					$key_res = mysql_query($key_sql);
					while ($key_text = mysql_fetch_assoc($key_res)) {
						if (in_array($key_text[KEYWORD], $arr_path)) {
							$arraykey = array_search($key_text[KEYWORD], $arr_path);
							if (is_numeric($arraykey)) {
								$arr_content[group] = $key_text[REQUEST_ID];
								unset($arr_path[$arraykey]);
							}
						}
					}
				} else {
					// No rewrite url parameter, so fall back to SHOP_PRODUCTS_GROUPS
					// Note that this may not be accurate, since a product can exist in more than one category. 
					// Only when url parameter is passed, will the correct context be registered.
					$arr_content[group] = returnFieldValue("SHOP_PRODUCTS_GROUPS", "GROUP_ID", "PRODUCT_ID", $arr_content[keyword_id]);
				}
			}
			$arr_content[action] = "showproduct";
		}

		// 2.7.5 Check for remaining parameters
		if (count($arr_path) > 0) {
			$arr_content[errors][] = array("error" => "not all rewrite parameters parsed", "remaining parameters" => $arr_path);
		}

		// 2.8 Convert arr_content method-values to arr_content values if they exist
		foreach ($arr_content[methods] as $key => $value) {
			if (count($value) == 2) {
				// Only match methods with a single value
				if (isset($arr_content[$value[0]])) {
					// This method name already has a value in arr_content, so overwrite it!
					$arr_content[$value[0]] = $value[1];
					// Check for language change
					if ($value[0] == "lang") {					
						$arr_content[lang] = set_current_language($value[1]);
						$arr_content[pageid]= getFrontpageId($arr_content[lang], $arr_content[site]);
						unset($arr_content[http_status]); 
					}
					
				}
			}
		}

/*		
// 2007-04-13	-	Page site_id no longer allowed to override the set site id. 
		// 2.9 Check if current site session and page site_id matches
		if (isset($arr_content[pageid])) {
			// Get site_id from page table
			$page_site_id = returnFieldValue("PAGES", "SITE_ID", "ID", $arr_content[pageid]);
			if ($arr_content[site] != $page_site_id) {
				$arr_content[site] = $page_site_id;
				$_SESSION[CURRENT_SITE] = $page_site_id;
			}
		}
*/

		// 2.10 Implements newsletter click-tracking on rewritten links, contributed by Kristian Wiborg / Intern1
		$arr_get = explode("&",$arr_url_elements[query]);
        foreach ($arr_get as $key => $value) {
            $this_get = explode("=", $value);
            if (count($this_get) == 2) {
                // Site parameter NOT allowed to override value given to function
                if ($this_get[0] != "site") {
                    $arr_content[$this_get[0]] = urldecode($this_get[1]);
                }
                // Check for language change
                if ($this_get[0] == "lang"  && !isset($_GET["grant"]) && !isset($_GET["clickedlink"])) {
                    $arr_content[pageid] = getFrontpageId($this_get[1], $arr_content[site]);
                    unset($arr_content[http_status]);
                }
            }
        }
		
		
	}
	// 3. Check for page actions (before browser output) based on the dothis parameter
	if (!isset($arr_content[dothis]) && isset($_POST[dothis])) {
		$arr_content[dothis] = $_POST[dothis];
	}

/*
	echo "<br/>\$arr_content:<pre>";
	print_r($arr_content);
	echo "</pre>";
*/
	$arr_content = add_content_redirect($arr_content);

/*
	Unfinished functionality:
	
	// 4. When showing home page, make sure there is no superflous elements in url
	global $useModRewrite_enforce_url_rewrite;
	if ($useModRewrite_enforce_url_rewrite) {
		$hommepage_id = getFrontpageId($arr_content[lang], $arr_content[site]);
		if ($arr_content[pageid] == $hommepage_id) {
			// yes, is home page, only works home page for current language
			$homepage_keyword = return_rewrite_keyword("", $arr_content[pageid], "PAGES", $arr_content[site]);
			if ($arr_content[keyword] == $homepage_keyword && $arr_content[keyword_table] == "PAGES" && $arr_content[keyword_id] == $hommepage_id) {
				$arr_content[redirect_to_url] = $arr_content[baseurl]."/";
				$arr_content[redirect_key] = returnMD5key($arr_content[baseurl]."/");
			}
		}
	}
*/

	return $arr_content;
}

function register_rewrite_methods($arr_path, &$arr_content) {
	// Is last array-position a rewrite-method?
	if ($arr_content[methods][] = check_rewrite_method($arr_path[count($arr_path)-1])) {
		array_pop($arr_path); // method registered, now pop it!
		$arr_path = register_rewrite_methods($arr_path, $arr_content);
	} else {
		array_pop($arr_content[methods]);
	}
	return $arr_path;
}

function check_method($arr_methods, $str_methodname) {
	if (!is_array($arr_methods)) {
		return false;
	}
	// Checks for existence of $str_methodname in given $arr_content[methods]-array
	foreach ($arr_methods as $key => $value) {
		if ($value[0] == $str_methodname) {
			array_shift($value); // Shift off method name to only return value(s)
			$arr_values = $value;
			$found = true;
			break;
		}
	}
	if ($found) {
		if (is_array($arr_values) && count($arr_values) > 1) {
			return $arr_values;
		} else if (is_array($arr_values) && count($arr_values) == 1){
			return $arr_values[0];
		} else {
			return true;
		}
	} else {
		return false;
	}
}

function check_rewrite_method($value) {
	// Function to check and return valid rewrite methods
	// A valid rewrite method must be registered in the database REWRITE_METHODS
	// Methods parameters (optional) may be appended using the __ (double-underscore) notation
	// F.instance "method__value1__value2"
	// Function returns an array where the first position is the internal name of valid rewrite methods, optional values are returned as subsequent positions
	// If no valid rewrite method is found in the database, the function returns false.
	
	$arr_value = explode("__",$value);
	$str_method = array_shift($arr_value);
	$sql = "SELECT INTERNALNAME from REWRITE_METHODS where NAME = '$str_method' LIMIT 1";
	$res = mysql_query($sql);
	if ($r = mysql_fetch_assoc($res)) {
		// Method is valid
		$arr_method[] = $r[INTERNALNAME];
		// Add values if present
		foreach ($arr_value as $key => $value) {
			$arr_method[] = $value;
		}
		return $arr_method;
	} else {
		return false;
	}
}

function return_rewrite_method($str_internalname, $language) {
	// Function to rewrite valid methods from parsed-query-parameters to rewrite-underscore-notation
	// A valid rewrite method must be registered in the database REWRITE_METHODS
	// Function returns an array where the first position is the public name of valid rewrite methods in the given or global (0) language
	// If no valid rewrite method is found in the database, the function returns false.
	if (is_numeric($language)) {
		$language_id = $language;
	} else {
		if (!$language_id = returnFieldValue("LANGUAGES", "ID", "SHORTNAME", $language)) {
			$language_id = 0;
		}	
	}
	$sql = "SELECT NAME from REWRITE_METHODS where INTERNALNAME = '$str_internalname' and LANGUAGE_ID in (0, $language_id) LIMIT 1";
	$res = mysql_query($sql);
	if (mysql_num_rows($res) > 0) {
		return mysql_result($res,0);
	} else {
		return false;
	}
}


function check_rewrite_keyword($value, $site_id) {
// 2007-04-13	-	Added site parameter to function to allow same keyword in multiple sites
	$sql = "SELECT TABLENAME, REQUEST_ID from REWRITE_KEYWORDS where KEYWORD = '$value' and SITE_ID = '$site_id' LIMIT 1";
	$res = mysql_query($sql);
	if ($r = mysql_fetch_assoc($res)) {
		return array($r[TABLENAME],$r[REQUEST_ID]);
	} else {
		return false;
	}
}


function check_rewrite_mode($value) {
	$sql = "SELECT INTERNALNAME from REWRITE_MODES where NAME = '$value' LIMIT 1";
	$res = mysql_query($sql);
	if ($r = mysql_fetch_assoc($res)) {
		return $r[INTERNALNAME];
	} else {
		return false;
	}
}


 


 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: SHOP
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function shopCategoryPathArray($arr_content, $categoryId, $huskMig){
 	global $huskMig;
// 2007-04-16	-	Site separation tilføjet (arr_content tilføjet som ekstra input parameter)
// 2007-04-18	-	Site afgrænsning strammet op, så fælles produktgrupper (site-id: 0) ikke længere er muligt
	$sql = "select PARENT_ID, ID, NAME from SHOP_PRODUCTGROUPS where ID='$categoryId' and SITE_ID='$arr_content[site]'";
	$result = mysql_query($sql);  
	while ($row = mysql_fetch_array($result)){
		$huskMig[] 	= array($categoryId, $row["NAME"]);
		$_SESSION["CURRENT_OPEN_PRODUCTMENUS"][] = $row["ID"];
		shopCategoryPathArray($arr_content, $row["PARENT_ID"], $huskMig);
  	} 
	if (is_array($huskMig)) return array_reverse($huskMig);
 }  
 
 function shopProductMenuPath($arr_content, $categoryArray, $divider){
	if (!is_array($categoryArray)) return;
	foreach ($categoryArray as $k => $groupData){
			$categoryArray[$k] = "<a href='$arr_content[baseurl]/index.php?mode=shop&amp;action=showgroup&amp;group=".$groupData[0]."'>".$groupData[1]."</a>";
	}
	$htmlPath = implode($divider, $categoryArray);
	return $htmlPath;
 }

 function shopGroupHasChildren($id){
	$sql = "select count(ID) from SHOP_PRODUCTGROUPS where PARENT_ID='$id' and DELETED='0' and PUBLISHED='1'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	if ($row[0] > 0){
		return true;
	} else {
		return false;
	}
 }
 
function shopProductMenu($parentId, $arr_content){
// 2007-04-18	-	Site-seperation: Afgrænser nu på site-id

	if ($parentId == 0 || array_search($parentId, $_SESSION["CURRENT_OPEN_PRODUCTMENUS"])){
		$sql = "
			select 
				ID, NAME, PARENT_ID
			from 
				SHOP_PRODUCTGROUPS
			where 
				DELETED='0' and 
				PARENT_ID='$parentId' and 
				PUBLISHED='1' and
				SITE_ID = '$arr_content[site]'
			order by
				NUMBER asc
		";
		$result = mysql_query($sql);
		echo "<ul>";
		while ($row = mysql_fetch_array($result)){
			$class = "";
			if ($row[ID] == $arr_content[group]){
				$class = "class='selected'";
			}
			echo "<li>";
			echo "<a href='$arr_content[baseurl]/index.php?mode=shop&amp;action=showgroup&amp;group=$row[ID]' $class>$row[NAME]</a>";
			if (shopGroupHasChildren($row[ID])){
				shopProductMenu($row[ID], $arr_content);
				echo "</li>";
			} else {
				echo "</li>";
			}
		}
		echo "</ul>";
	}
 } 



 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: SHOPPING CART
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////


 
 function initCart($cookieName){
 	global $cookieDomain;
 	/// FUNKTIONEN OPRETTER COOKIEN, HVIS DEN IKKE EKSISTERER. ELLERS HENTER DEN
	/// DENS VÆRDI I $cart_id. 
	/// DEN BURDE OGSÅ KUNNE SKRIVES OM TIL AT HÅNDTERE SESSION-VARS, HVIS COOKIES=OFF
	session_start();
 	if (!isset($_COOKIE[$cookieName])){
		$cart_id = md5(uniqid(rand()));
		if (!$_SESSION[LOGGED_IN]){
			setcookie($cookieName, $cart_id, time()+24*60*60, "/", $cookieDomain);
		} else {
			return false;
		}
		// $_SESSION["cart_id"] = $cart_id;
	} else {
		$cart_id = $_COOKIE[$cookieName];
	}
	// if (!$cart_id){
	//	$cart_id = $_SESSION["cart_id"];
	// }
	return $cart_id;
 } 
 
 function createOrder($cartId){
 	if ($cartId){
		// HVIS DER ER ET CART_ID, DVS. IKKE LOGGET IND
		$sql = "select ID from CART_ORDERS where CART_ID='$cartId' and DELETED='0'";
		$result = mysql_query($sql);
		if (mysql_num_rows($result) == 0){
			$uniq = randomString();
			$sql = "insert into CART_ORDERS (CART_ID, UNIQUE_ORDERID) values ('$cartId', '$uniq')";
			$result = mysql_query($sql);
			$orderId = mysql_insert_id();
		} else {
			$row = mysql_fetch_array($result);
			$orderId = $row["ID"];
		}
	} else {
		// HVIS DER IKKE ER ET CART_ID, DVS MAN ER LOGGET IND
		$sql = "select ID from CART_ORDERS where USER_ID='".$_SESSION[USERDETAILS][0][0]."' and DELETED='0'";
		$result = mysql_query($sql);
		if (mysql_num_rows($result) == 0){
			$uniq = randomString();
			$sql = "insert into CART_ORDERS (USER_ID, UNIQUE_ORDERID) values ('".$_SESSION[USERDETAILS][0][0]."', '$uniq')";
			$result = mysql_query($sql);
			$orderId = mysql_insert_id();
		} else {
			$row = mysql_fetch_array($result);
			$orderId = $row["ID"];
		}
	}
	return $orderId;		
 }
 
 function updateOrderZipcode($orderId, $zip){
 	$sql = "update CART_ORDERS set ZIPCODE='$zip' where ID='$orderId'";
	$result = mysql_query($sql);
 }
 
 function emptyCart($cartId){
 	$orderId = createOrder($cartId);
	$sql = "delete from CART_CONTENTS where CART_ORDERS_ID='$orderId'";
	mysql_query($sql);
 }
 
 function checkoutForm($arr_content, $cartId){
 	global $customCheckoutFunctionName;
	if ($customCheckoutFunctionName){
		$html .= call_user_func($customCheckoutFunctionName, $arr_content, $cartId);
	} else {
		$html .= checkoutDefaultForm($arr_content, $cartId);
	}
	return $html;
 }
 
 function completeOrder($arr_content, $cartId){
 	global $customMakeOrderPermanentFunctionName, $customReturnOrderStamdataFunctionName;
	//////////
	$orderId = createOrder($cartId);
	$cssFileName = $_SERVER["DOCUMENT_ROOT"]."/cms/modules/cart/frontend/cart.css";
	$cssHandle = fopen($cssFileName, "r");
	$css = fread($cssHandle, filesize($cssFileName));
	fclose($cssHandle);

	$css .= "
				p { font:normal 11px verdana; }
			";

	//////////
	$realOrderNumber = drawOrderNumber();
	$sendReceiptTo 	= returnFieldValue("CART_ORDERS", "EMAIL", "ID", $orderId);
	$customerName 	= returnFieldValue("CART_ORDERS", "NAME", "ID",  $orderId);
	$mailSubject	= cmsTranslate("CartOrderMailSubject") . $realOrderNumber;
	$html .= "<style type='text/css'>$css</style>";

	$html .= "<p>".cmsTranslate("CartOrderMailAboutNumber").$realOrderNumber."</p>";

	$html .= "<p><strong>".cmsTranslate("CartOrderMailHeading").$customerName."</strong></p>";
	$html .= "<p>".cmsTranslate("CartOrderMailTopText")."</p>";
	if ($customReturnOrderStamdataFunctionName){
		$html .= call_user_func($customReturnOrderStamdataFunctionName, $cartId);
	} else {
		$html .= returnOrderStamdata($cartId);
	}
	$html .= "<p><strong>".cmsTranslate("CartYourOrder").":</strong></p>";
	$html .= showCart($arr_content, $cartId, "mail");
	$html .= "<p>".cmsTranslate("CartOrderMailBottomText")."</p>";
	mail($sendReceiptTo, $mailSubject, $html, "From: ".cmsTranslate("CartOrderMailFromName")." <".cmsTranslate("CartOrderMailReplyTo").">\nContent-Type: text/html; charset=UTF-8");
	mail(cmsTranslate("CartOrderMailCCto"), $mailSubject, $html, "From: ".cmsTranslate("CartOrderMailFromName")." <".cmsTranslate("CartOrderMailReplyTo").">\nContent-Type: text/html; charset=UTF-8");
	if ($customMakeOrderPermanentFunctionName){
		call_user_func($customMakeOrderPermanentFunctionName, $orderId, $realOrderNumber, $arr_content);
	} else {
		makeOrderPermanent($orderId, $realOrderNumber, $arr_content);
	}
	setOrderStatus($realOrderNumber, 30);
	killCookie($cartId);
 }

 function makeOrderPermanent($orderId, $realOrderNumber, $arr_content){
 	$sql = "select * from CART_ORDERS where ID='$orderId'";
	$result = mysql_query($sql);
	$cartOrderRow = mysql_fetch_array($result);
	foreach ($cartOrderRow as $key => $val){
		$cartOrderRow[$key] == db_safedata($val);
	}
	$frozenPaymentTerm = db_safedata(returnFieldValue("CART_PAYMENTTERMS", "TITLE", "ID", $cartOrderRow[PAYMENTTERM]));
	$sql = "
		insert into SHOP_ORDERS 
			(
				UNIQUE_ORDERID, ORDERNUMBER_SEQ, ORIGINAL_COOKIE_ID, USER_ID, 
				NAME, ADDRESS, CITY, ZIPCODE, PHONE, CELLPHONE, FAX, EMAIL, COMPANY, VAT_NUMBER, 
				ATTENTION, NOTES, 
				DELIVERYNAME, DELIVERYADDRESS, DELIVERYZIPCODE, DELIVERYCITY, DELIVERYCOMPANY,
				FROZEN_PAYMENTTERM, 
				SITE_ID
			)
		values
			(
				'$cartOrderRow[UNIQUE_ORDERID]', '".$realOrderNumber."',
				'$cartOrderRow[CART_ID]', '$cartOrderRow[USER_ID]',
				'$cartOrderRow[NAME]', '$cartOrderRow[ADDRESS]', '$cartOrderRow[CITY]', '$cartOrderRow[ZIPCODE]',
				'$cartOrderRow[PHONE]', '$cartOrderRow[CELLPHONE]', '$cartOrderRow[FAX]', '$cartOrderRow[EMAIL]',
				'$cartOrderRow[COMPANY]', '$cartOrderRow[VAT_NUMBER]', 
				'$cartOrderRow[ATTENTION]', '$cartOrderRow[NOTES]',
				'$cartOrderRow[DELIVERYNAME]', '$cartOrderRow[DELIVERYADDRESS]', 
				'$cartOrderRow[DELIVERYZIPCODE]', '$cartOrderRow[DELIVERYCITY]', '$cartOrderRow[DELIVERYCOMPANY]', '$frozenPaymentTerm',
				'$_SESSION[CURRENT_SITE]'
			)
	";
	$result = mysql_query($sql);
 	$sql = "select * from CART_CONTENTS where CART_ORDERS_ID='$orderId'";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result)){
		$cartOrderLines[] = $row;
	}
	foreach ($cartOrderLines as $key => $orderLine){
		$cartOrderLines[$key][FROZEN_PRODUCTNUMBER] = db_safedata(returnProductProductnumber($orderLine[PRODUCT_ID], $orderLine[PRODUCT_TABLENAME]));
		$cartOrderLines[$key][FROZEN_PRODUCTNAME] 	= db_safedata(returnProductName($orderLine[PRODUCT_ID], $orderLine[PRODUCT_TABLENAME]));
		$cartOrderLines[$key][FROZEN_PRODUCTDESCRIPTION] = db_safedata(returnProductSomeColumn($orderLine[PRODUCT_ID], $orderLine[PRODUCT_TABLENAME], "PRODUCT_DESCRIPTION_COLUMN"));
		$cartOrderLines[$key][FROZEN_PRODUCTPRICE] 	= db_safedata(returnProductPrice($orderLine[PRODUCT_ID], $orderLine[PRODUCT_TABLENAME]));

		$colliDetails = returnColliDetails($orderLine[COLLI_ID]);
		$cartOrderLines[$key][FROZEN_COLLIQUANTITY] = $colliDetails[QUANTITY];
		$cartOrderLines[$key][FROZEN_COLLIDISCOUNT_PCT] = $colliDetails[DISCOUNT_PERCENTAGE];
		$cartOrderLines[$key][FROZEN_COLLIDISCOUNT_AMOUNT] = $colliDetails[DISCOUNT_AMOUNTPERCOLLI];
		$cartOrderLines[$key][FROZEN_TIME_ADDED] = $orderLine[TIME_ADDED];
		if ($userprice = returnUserPrice($cartOrderRow[USER_ID], $orderLine[PRODUCT_ID])) {
			$cartOrderLines[$key][FROZEN_USERPRICE] = $userprice;
		} else {
			$cartOrderLines[$key][FROZEN_USERPRICE] = "NULL";
		}
		if ($orderLine[CUSTOM_PRICE] > 0) {
			$cartOrderLines[$key][FROZEN_CUSTOMPRICE] = $orderLine[CUSTOM_PRICE];
		} else {
			$cartOrderLines[$key][FROZEN_CUSTOMPRICE] = "NULL";
		}
		$cartOrderLines[$key][FROZEN_CUSTOMDESCRIPTION] = $orderLine[CUSTOM_DESCRIPTION];

		// Don't register group discount for lines with user and/or customprice
		if (is_numeric($cartOrderLines[$key][FROZEN_USERPRICE]) || is_numeric($cartOrderLines[$key][FROZEN_CUSTOMPRICE])) {
			$cartOrderLines[$key][FROZEN_GROUPDISCOUNT] = 0;
		} else {
			$cartOrderLines[$key][FROZEN_GROUPDISCOUNT] = returnDiscountPercentage($cartOrderRow[USER_ID], $orderLine[PRODUCT_ID], $arr_content);
		}

		$sql = "
			insert into SHOP_ORDERDETAILS
				(
					ORDERNUMBER_SEQ, PRODUCT_ID, PRODUCT_TABLENAME, 
					AMOUNT, FRAGT, 
					FROZEN_PRODUCTNUMBER, FROZEN_PRODUCTNAME, FROZEN_PRODUCTDESCRIPTION, FROZEN_PRODUCTPRICE,
					FROZEN_GROUPDISCOUNT, FROZEN_COLLIQUANTITY, FROZEN_COLLIDISCOUNT_PCT, FROZEN_COLLIDISCOUNT_AMOUNT,
					FROZEN_TIME_ADDED, FROZEN_USERPRICE, FROZEN_CUSTOMPRICE, FROZEN_CUSTOMDESCRIPTION
				)
			values
				(
					'".$realOrderNumber."', '".$cartOrderLines[$key][PRODUCT_ID]."', 
					'".$cartOrderLines[$key][PRODUCT_TABLENAME]."', '".$cartOrderLines[$key][AMOUNT]."',
					'".$cartOrderLines[$key][FRAGT]."',
					'".$cartOrderLines[$key][FROZEN_PRODUCTNUMBER]."', '".$cartOrderLines[$key][FROZEN_PRODUCTNAME]."',
					'".$cartOrderLines[$key][FROZEN_PRODUCTDESCRIPTION]."', '".$cartOrderLines[$key][FROZEN_PRODUCTPRICE]."',
					'".$cartOrderLines[$key][FROZEN_GROUPDISCOUNT]."', '".$cartOrderLines[$key][FROZEN_COLLIQUANTITY]."',
					'".$cartOrderLines[$key][FROZEN_COLLIDISCOUNT_PCT]."', '".$cartOrderLines[$key][FROZEN_COLLIDISCOUNT_AMOUNT]."',
					'".$cartOrderLines[$key][FROZEN_TIME_ADDED]."',
					".$cartOrderLines[$key][FROZEN_USERPRICE].",
					".$cartOrderLines[$key][FROZEN_CUSTOMPRICE].",
					'".$cartOrderLines[$key][FROZEN_CUSTOMDESCRIPTION]."'
				)
		";
		$result = mysql_query($sql);
	}
	$sql = "update CART_ORDERS set DELETED='1' where ID='$orderId'";
	mysql_query($sql);
 }
 
 function returnColliDetails($colliId){
 	$sql = "
		select 
			QUANTITY, DISCOUNT_PERCENTAGE, DISCOUNT_AMOUNTPERCOLLI 
		from 
			SHOP_PRODUCTS_COLLI 
		where
			ID='$colliId'
	";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	return $row;
 }
 
 function drawOrderNumber(){
 	$sql = "insert into SHOP_ORDERSEQ (RESERVED_TIME) values (NOW())";
	$result = mysql_query($sql);
	return mysql_insert_id();
 }
 
 function returnGroupDiscount(){
	// 2007-03-20: Never used, discount is always given in relation to a usergroup AND productgroup (MAP)
	return 0;
}

 /*
// 2007-03-20: REPLACED by fn returnDiscountPercentage (MAP)
  function returnUserDiscount($userid){
	// Function takes a userid and returns the appropriate groupdiscount
	// If a user belongs to more than one group the largest discount will be returned
 	$sql = "select MAX(SHOP_GROUPDISCOUNTS.DISCOUNT_PERCENTAGE) from SHOP_GROUPDISCOUNTS, USERS, USERS_GROUPS 
 			where USERS.ID = USERS_GROUPS.USER_ID
 			and USERS_GROUPS.GROUP_ID = SHOP_GROUPDISCOUNTS.GROUP_ID
 			and USERS.ID = $userid";
	$res = mysql_query($sql);
	return (mysql_result($res, 0));		
 }
 */
 
 function returnDiscountPercentage($userid, $productid, $arr_content) {
 	// Function takes a userid and productid and returns the appropriate discount percentage
	// If a user belongs to more than one usergroup, the largest discount will be returned
	// If a product belongs to more than one productgroup, the largest discount will be returned
	// If the productgroup discount percentage and usergroup discount percentage are not the same, the largest discount will be returned
	// 2007-04-18	-	Site-seperation: Added $arr_content input-parameter and implemented siteid in where clauses
	// 2008-10-31	-	Fixed issues with users not identified (logged in)

	// Instantiate arrays to avoid trouble later if either array is empty
	$arr_usergroups = array();
	$arr_productgroups = array();
	$arr_combusergroups = array();
 
 	// 1. Get productgroups to which the product belongs (only published productgroups)
	// No need to site-separate this because product can only belong to productgroups available in the same site
 	$pg_sql = "select 
 					SP.ID 
 				from 
 					SHOP_PRODUCTS_GROUPS SPG, 
 					SHOP_PRODUCTGROUPS SP 
 				where 
 					SPG.GROUP_ID = SP.ID and 
 					SPG.PRODUCT_ID = '$productid' and 
 					SP.DELETED = 0 and 
 					SP.PUBLISHED = 1";
	$pg_res = mysql_query($pg_sql);
	while ($pg_row = mysql_fetch_assoc($pg_res)) {
		$arr_productgroups[] = $pg_row[ID];
	}

 	// 2. Get usergroups to which the user belongs
	// Site seperation implemented - only get groups belonging to current site and global groups (siteid = 0)
	if (is_numeric($userid)&&$userid>0) {
		// Only if user is identified (logged in)
		$ug_sql = "select 
						G.ID 
					from 
						USERS_GROUPS UG, 
						GROUPS G 
					where 
						UG.GROUP_ID = G.ID and 
						UG.USER_ID = '$userid' and 
						G.UNFINISHED = 0 and 
						G.DELETED = 0 and
						G.SITE_ID in (0,'$arr_content[site]')";
		$ug_res = mysql_query($ug_sql);
		while ($ug_row = mysql_fetch_assoc($ug_res)) {
			$arr_usergroups[] = $ug_row[ID];
		}
	}

 	// 3. Get highest discount percentage from a combination of usergroup and productgroup
	// No need to site-separate, the separation has been made in choosing only relevant usergroups (see "2" above)
	$comb_sql = "select
 						DISCOUNT_PERCENTAGE, GROUP_ID, PRODUCTGROUP_ID
 					from
 						SHOP_GROUPDISCOUNTS
	 				where
	 					GROUP_ID in (".implode($arr_usergroups, ",").") and
	 					PRODUCTGROUP_ID in (".implode($arr_productgroups, ",").") and
	 					PRODUCTGROUP_ID > 0
		 			 order by DISCOUNT_PERCENTAGE asc";
	if ($comb_res = mysql_query($comb_sql)) {
		if (mysql_num_rows($comb_res) == 0) {
			$comb_disc = NULL;
		} else {
			while ($row=mysql_fetch_assoc($comb_res)) {
				// Get highest percentage + array of groups to ignore in usergroup discount search
				$comb_disc = $row[DISCOUNT_PERCENTAGE];
				$arr_combusergroups[] = $row[GROUP_ID];
			}
			$arr_combusergroups = array_unique($arr_combusergroups);
		}
	} else {
			$comb_disc = NULL;
	}

	// 4. Get highest discount percentage for usergroup only (general usergroup discount)
	// Exclude usergroups found in "3" ($arr_combusergroups)
	// No need to site-separate, the separation has been made in choosing only relevant usergroups (see "2" above)
	$arr_usergroups_to_search = array_diff($arr_usergroups, $arr_combusergroups);

	$ugdisc_sql = "select
							MAX(SHOP_GROUPDISCOUNTS.DISCOUNT_PERCENTAGE)
						from
							SHOP_GROUPDISCOUNTS
						where
							GROUP_ID in (".implode($arr_usergroups_to_search, ",").") and
							PRODUCTGROUP_ID = 0";
	if ($ugdisc_res = mysql_query($ugdisc_sql)) {
		if (mysql_num_rows($ugdisc_res) == 0) {
			$ug_disc = 0;
		} else {
			$ug_disc = mysql_result($ugdisc_res, 0);
			if ($ug_disc == "" || $ug_disc == NULL) {
				$ug_disc = 0;			
			}
		}
	} else {
		$ug_disc = 0;				
	}
//echo "<br/>CD: $comb_disc";
//echo "<br/>UG: $ug_disc";

// 5. Decide which discount percentage to return - $ug_disc og $comb_disc?
	if ($comb_disc == NULL) {
		// Productgroup/usergroup (combined) discount NOT FOUND, return usergroup (general) discount
		return $ug_disc;
	} else {
		// Productgroup/usergroup (combined) discount FOUND, return highest discount percentage 
		if ($comb_disc > $ug_disc) {
			return $comb_disc;
		} else {
			return $ug_disc;
		}
	}
}

function sendCart($arr_content, $cartId){
 	if ($cartId){
		//// ERROR LOGGING /////////////////////////////////////////////////////
		$postcheck = "";
		foreach ($_POST as $k => $v){
			$postcheck .= "POST $k => $v<br/>";
		}
		foreach ($_GET as $k => $v){
			$postcheck .= "GET $k => $v<br/>";
		}
		foreach ($_SERVER as $k => $v){
			$postcheck .= "SERVER $k => $v<br/>";
		}
		foreach ($_SESSION as $k => $v){
			$postcheck .= "SESSION $k => $v<br/>";
		}
		foreach ($_COOKIE as $k => $v){
			$postcheck .= "COOKIE $k => $v<br/>";
		}
		//// END OF ERROR LOGGING ///////////////////////////////////////////////
		$cssFileName = $_SERVER["DOCUMENT_ROOT"]."/includes/css/cart.css";
		$cssHandle = fopen($cssFileName, "r");
		$css = fread($cssHandle, filesize($cssFileName));
		fclose($cssHandle);
//		$orderNumber 	= returnFieldValue("CART_ORDERS", "ID", "CART_ID", $cartId);
		$orderNumber = drawOrderNumber();

		// Update CART_ORDERS with ordernumber from sequence
		$sql = "UPDATE CART_ORDERS set ORDER_NUMBER = '$orderNumber' where CART_ID = '$cartId'";
		mysql_query($sql);

		$sendReceiptTo 	= returnFieldValue("CART_ORDERS", "EMAIL", "CART_ID", $cartId);
		$mailSubject	= cmsTranslate("CartQuoteMailSubject") . "w" . $orderNumber;
		$html .= "<style type='text/css'>$css</style>";
		$html .= "<p><strong>".cmsTranslate("CartOrderMailHeading").returnFieldValue("CART_ORDERS", "NAME", "CART_ID", $cartId)."</strong></p>";	
		$html .= "<p>".cmsTranslate("CartQuoteMailTopText")."</p>";
		$html .= returnOrderStamdata($cartId, true);
		$html .= "<p><strong>".cmsTranslate("CartYourQuote").":</strong></p>";
		$html .= showCart($arr_content, $cartId, "mail");
		$html .= "<p>".cmsTranslate("CartQuoteMailBottomText")."</p>";
		$textpart = "Du skal bruge en mailklient, der understøtter HTML-mails, for at se denne mail."; // $textpart = strip_tags($html);
        $mail = new htmlMimeMail();

		// Change to UFT-8 encoding
		$mail->setTextCharset("UTF-8");
		$mail->setHTMLCharset("UTF-8");
		$mail->setHeadCharset("UTF-8"); 

        $mail->setHtml($html, $textpart);
		$mail->setReturnPath(cmsTranslate("CartQuoteMailCCto"));		
		$mail->setFrom('"'.cmsTranslate("CartQuoteMailFromName").'" <'.cmsTranslate("CartQuoteMailReplyTo").'>');
		$mail->setSubject($mailSubject);
		$mail->setHeader('Reply-To', cmsTranslate("CartQuoteMailCCto"));
		$mail->send(array("'".$sendReceiptTo."' <".$sendReceiptTo.">"), 'mail');
		$mail->send(array("'".cmsTranslate("CartQuoteMailCCto")."' <".cmsTranslate("CartQuoteMailCCto").">"), 'mail');
		setOrderStatus($orderNumber, 20);
	}
 }
 
 function printCart($arr_content, $cartId){
	$html .= showCart($arr_content, $cartId, "print");
	return $html;
 }
 
 function setOrderStatus($orderId, $statusId){
	$sql = "insert into CART_ORDERS_ORDERSTATUS (ORDER_ID, ORDERSTATUS_ID, CREATED_DATE) values ('$orderId', '$statusId', '".time()."')";
	mysql_query($sql);
 }

 function killCookie($cartId){
 	global $cookieDomain;
 	setcookie("instans_cart_id", $cartId, time()-3600, "/", $cookieDomain);
 }
  
 function updateOrderDetails($cartId, $POSTVARS){
 	$orderId = createOrder($cartId);
 	foreach ($POSTVARS as $key => $val){
		$temp = array_reverse(explode("_", $key));
		$fieldName = $temp[0];
		$val = db_safedata($val);
		$sql = "update CART_ORDERS set $fieldName='$val' where ID='$orderId'";
		mysql_query($sql);
	}
 }
 
 function returnOrderSummary($arr_content, $cartId){	
	$orderId = createOrder($cartId);
 	$html .= returnOrderflow(2, $arr_content, $orderId);
	$html .= "<form id='cart_summaryForm' name='cart_summaryForm' method='post' action='$arr_content[baseurl]/index.php?mode=cart&action=checkoutfinalize'>";
	$html .= "<p>".cmsTranslate("CartSummaryTopText")."</p>";
	$html .= "<p class='formGroupingHeader'>".cmsTranslate("CartYourOrder").":</p>";
	$html .= showCart($arr_content, $cartId, "summary");
 	$html .= returnOrderStamdata($cartId);
	$html .= "
		<table class='table_orderbuttons' width='100%'>
			<tr id='cartBottomButtons2'>
				<td><input type='submit' name='BACKTOCART_CLICKED' value='&laquo;&laquo;&nbsp;".cmsTranslate("CartEditCart")."' /></td> 
				<td><input type='submit' name='CHECKOUT_CLICKED' value='&laquo;&nbsp;".cmsTranslate("CartEditAddress")."' /></td>";
	
	if (return_include_payment_step($arr_content, $orderId)){
		$html .= "	<td><input type='submit' name='PAYORDER_CLICKED' value='".cmsTranslate("CartCheckoutFlowPay")."&nbsp;&raquo;' onclick='return cart_verifyCheckout();' class='forward' /></td> ";
	} else {
		$html .= "	<td><input type='submit' name='COMPLETEORDER_CLICKED' value='".cmsTranslate("CartCompleteOrder")."&nbsp;&raquo;' onclick='return cart_verifyCheckout();' class='forward' /></td> ";
	}

	$html .= "</tr>
		</table>
	";
	$html .= "</form>";	
	return $html;
 }

 function isEmptyOrder($cartId){
 	$orderId = createOrder($cartId); 
 	$sql = "select ID from CART_CONTENTS where CART_ORDERS_ID='$orderId'";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) == 0){
		header("location: index.php");
	}
	return false;
 }

 function returnOrderStamdata($cartId, $quotemode=false){
 	$orderId = createOrder($cartId); 
	$sql = "select * from CART_ORDERS where ID='$orderId'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	$html .= "<p class='formGroupingHeader'>".cmsTranslate("CartBillTo").":</p>";
	$html .= "<p>";
	if ($row[COMPANY]){
		$html .= $row[COMPANY] . "<br/>";
	}
	$html .= $row[NAME] . "<br/>";
	$html .= $row[ADDRESS] . "<br/>";
	$html .= $row[ZIPCODE] . "&nbsp;" . $row[CITY] . "<br/><br/>";
	if ($row[PHONE]){
		$html .= cmsTranslate("CartPlaceOrderPhone") . ": " . $row[PHONE] . "<br/>";
	}
	if ($row[CELLPHONE]){
		$html .= cmsTranslate("CartPlaceOrderCellphone") . ": " . $row[CELLPHONE] . "<br/>";
	}
	$html .= cmsTranslate("CartPlaceOrderEmail"). ": " . $row[EMAIL] . "<br/><br/>";
	if ($row[PAYMENTTERM]){
		$html .= cmsTranslate("CartPlaceOrderPaymentterm") . ": " . returnFieldValue("CART_PAYMENTTERMS", "TITLE", "ID", $row[PAYMENTTERM]) . "<br/><br/>";
	}
	if ($row[NOTES]){
		if ($quotemode){
			$html .= cmsTranslate("CartQuoteNotes") . ": " . $row[NOTES];
		} else {
			$html .= cmsTranslate("CartPlaceOrderNotes") . ": " . $row[NOTES];
		}
	}
	$html .= "</p>";
	$html .= "<p class='formGroupingHeader'>".cmsTranslate("CartDeliverTo").":</p>";
	$html .= "<p>";
	if ($row[DELIVERYNAME] && $row[DELIVERYADDRESS] && $row[DELIVERYZIPCODE] && $row[DELIVERYCITY]){
		$html .= $row[DELIVERYCOMPANY] . "<br/>";
		$html .= $row[DELIVERYNAME] . "<br/>";
		$html .= $row[DELIVERYADDRESS] . "<br/>";
		$html .= $row[DELIVERYZIPCODE] . "&nbsp;" . $row[DELIVERYCITY] . "<br/><br/>";
	} else {
		if ($row[COMPANY]){
			$html .= $row[COMPANY] . "<br/>";
		}
		$html .= $row[NAME] . "<br/>";
		$html .= $row[ADDRESS] . "<br/>";
		$html .= $row[ZIPCODE] . "&nbsp;" . $row[CITY] . "<br/><br/>";
	}
	$html .= "</p>";
	return $html;
 }
 
 function checkoutDefaultForm($arr_content, $cartId=false){
	global $checkoutErrors;
 	$orderId = createOrder($cartId); 
	if ($_SESSION["LOGGED_IN"]){
		$sql = "select NAME from CART_ORDERS where ID='$orderId'";
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		if ($row[NAME] == ""){
			$sql = "select * from USERS where ID='".$_SESSION[USERDETAILS][0][0]."'";
			$result = mysql_query($sql);	
			$ORDERDATA = mysql_fetch_array($result);
			$ORDERDATA[NAME] = $ORDERDATA[FIRSTNAME]." ".$ORDERDATA[LASTNAME];
		} else {
			$sql = "select * from CART_ORDERS where ID='$orderId'";
			$result = mysql_query($sql);	
			$ORDERDATA = mysql_fetch_array($result);
		}
	} else {
		$sql = "select * from CART_ORDERS where ID='$orderId'";
		$result = mysql_query($sql);	
		$ORDERDATA = mysql_fetch_array($result);
	}
 	$html .= returnOrderflow(1, $arr_content, $orderId);
	$html .= "<p>".cmsTranslate("CartEnterAddressTextTop")."</p>";
	if ($checkoutErrors){
		$html .= "
			<p class='missingInfo'>" .
				parseCheckoutErrors($checkoutErrors) . "
			</p>
		";
	}
	$sql = "select ID, TITLE, PERMISSION from CART_PAYMENTTERMS order by POSITION asc";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result)){
		$loop++;
		if ($ORDERDATA["PAYMENTTERM"] == $row[ID]) {
			$pt_checked = " checked ";
		} elseif ($ORDERDATA["PAYMENTTERM"] == 0 && $loop == 1) {
			$pt_checked = " checked ";
		} else {
			$pt_checked = "";
		}
		
		if ($row["PERMISSION"] == ""){
			$paymentTermHtml .= "<input ".$pt_checked." type='radio' name='cart_checkoutForm_PAYMENTTERM' value='$row[ID]' />&nbsp;$row[TITLE]<br/>";
		} else {
			if (checkPermissionFE($row["PERMISSION"], $_SESSION["USERDETAILS"][2])){
				$paymentTermHtml .= "<input ".$pt_checked."type='radio' name='cart_checkoutForm_PAYMENTTERM' value='$row[ID]' />&nbsp;$row[TITLE]<br/>";
			}
		}
	}

	// print_r($ORDERDATA);

	$html .= "
		<form id='cart_checkoutForm' name='cart_checkoutForm' method='post' action='$arr_content[baseurl]/index.php?mode=cart'>
			<table width='100%' id='cartAddressTable'>
			<tr><td valign='top'>
			<div id='cart_checkoutForm_BILLINGDIV'>
				<p class='formGroupingHeader'>".cmsTranslate("CartBillingAddress")."</p>
	
				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderCompany")."</div>
				<div class='generatedFormFieldContainer'>
					<input value='$ORDERDATA[COMPANY]' class='generatedFormField' type='text' name='cart_checkoutForm_COMPANY' id='cart_checkoutForm_COMPANY'/>
				</div>

				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderName")."</div>
				<div class='generatedFormFieldContainer'>
					<input value='$ORDERDATA[NAME]' class='generatedFormField' type='text' name='cart_checkoutForm_NAME' id='cart_checkoutForm_NAME'/>
				</div>
				
				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderAddress")."</div>
				<div class='generatedFormFieldContainer'>
					<input value='$ORDERDATA[ADDRESS]' class='generatedFormField' type='text' name='cart_checkoutForm_ADDRESS' id='cart_checkoutForm_ADDRESS'/>
				</div>
	
				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderZipcode")."</div>
				<div class='generatedFormFieldContainer'>
					<input value='".($ORDERDATA[ZIPCODE]!=0?$ORDERDATA[ZIPCODE]:"")."' class='generatedFormField' type='text' name='cart_checkoutForm_ZIPCODE' id='cart_checkoutForm_ZIPCODE'/>
				</div>
	
				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderCity")."</div>
				<div class='generatedFormFieldContainer'>
					<input value='$ORDERDATA[CITY]' class='generatedFormField' type='text' name='cart_checkoutForm_CITY' id='cart_checkoutForm_CITY'/>
				</div>
	
				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderPhone")."</div>
				<div class='generatedFormFieldContainer'>
					<input value='$ORDERDATA[PHONE]' class='generatedFormField' type='text' name='cart_checkoutForm_PHONE' id='cart_checkoutForm_PHONE'/>
				</div>
	
				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderCellphone")."</div>
				<div class='generatedFormFieldContainer'>
					<input value='$ORDERDATA[CELLPHONE]' class='generatedFormField' type='text' name='cart_checkoutForm_CELLPHONE' id='cart_checkoutForm_CELLPHONE'/>
				</div>
	
				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderEmail")."</div>
				<div class='generatedFormFieldContainer'>
					<input value='$ORDERDATA[EMAIL]' class='generatedFormField' type='text' name='cart_checkoutForm_EMAIL' id='cart_checkoutForm_EMAIL'/>
				</div>
			</div>
			</td><td>&nbsp;</td><td valign='top'>
			<div id='cart_checkoutForm_DELIVERYDIV'>
	
				<p class='formGroupingHeader'>".cmsTranslate("CartPlaceOrderPaymentterm")."</p>
				<div class='generatedFormFieldContainer'>
					$paymentTermHtml
				</div>
	
				<p class='formGroupingHeader'>".cmsTranslate("CartPlaceOrderNotes")."</p>
				<div class='generatedFormFieldContainer'>
					<textarea class='generatedFormField' name='cart_checkoutForm_NOTES' id='cart_checkoutForm_NOTES'>$ORDERDATA[NOTES]</textarea>
				</div>

				<p class='formGroupingHeader'>".cmsTranslate("CartDeliveryAddress")."</p>
	
				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderCompany")."</div>
				<div class='generatedFormFieldContainer'>
					<input value='$ORDERDATA[DELIVERYCOMPANY]' class='generatedFormField' type='text' name='cart_checkoutForm_DELIVERYCOMPANY' id='cart_checkoutForm_DELIVERYCOMPANY'/>
				</div>

				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderName")."</div>
				<div class='generatedFormFieldContainer'>
					<input value='$ORDERDATA[DELIVERYNAME]' class='generatedFormField' type='text' name='cart_checkoutForm_DELIVERYNAME' id='cart_checkoutForm_DELIVERYNAME'/>
				</div>
				
				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderAddress")."</div>
				<div class='generatedFormFieldContainer'>
					<input value='$ORDERDATA[DELIVERYADDRESS]' class='generatedFormField' type='text' name='cart_checkoutForm_DELIVERYADDRESS' id='cart_checkoutForm_DELIVERYADDRESS'/>
				</div>
	
				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderZipcode")."</div>
				<div class='generatedFormFieldContainer'>
					<input value='$ORDERDATA[DELIVERYZIPCODE]' class='generatedFormField' type='text' name='cart_checkoutForm_DELIVERYZIPCODE' id='cart_checkoutForm_DELIVERYZIPCODE'/>
				</div>
	
				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderCity")."</div>
				<div class='generatedFormFieldContainer'>
					<input value='$ORDERDATA[DELIVERYCITY]' class='generatedFormField' type='text' name='cart_checkoutForm_DELIVERYCITY' id='cart_checkoutForm_DELIVERYCITY'/>
				</div>				
			</div>
			</td></tr></table>
			<table class='table_orderbuttons' width='100%'>
				<tr id='cartBottomButtons2'>
					<td><input type='submit' id='BACKTOCART_CLICKED' name='BACKTOCART_CLICKED' value='&laquo;&nbsp;".cmsTranslate("CartEditCart")."' /></td> 
					<td><input type='submit' id='APPROVEORDER_CLICKED' name='APPROVEORDER_CLICKED' value='".cmsTranslate("CartVerifyAndApprove")."&nbsp;&raquo;' onclick='return cart_verifyCheckout();' class='forward' /></td> 
				</tr>
			</table>
		</form>
	";
	return $html;
 }
 
 function serversideCheckoutValidate($POSTVARS){
 	$errors = array();
	if (isset($POSTVARS["cart_checkoutForm_NAME"]) && $POSTVARS["cart_checkoutForm_NAME"] == ""){
		$errors[] = "Udfyld venligst navn";
	}
	if (isset($POSTVARS["cart_checkoutForm_ADDRESS"]) && $POSTVARS["cart_checkoutForm_ADDRESS"] == ""){
		$errors[] = "Udfyld venligst adresse";
	}
	if (isset($POSTVARS["cart_checkoutForm_ZIPCODE"]) && $POSTVARS["cart_checkoutForm_ZIPCODE"] == ""){
		$errors[] = "Udfyld venligst postnummer";
	}
	if (isset($POSTVARS["cart_checkoutForm_CITY"]) && $POSTVARS["cart_checkoutForm_CITY"] == ""){
		$errors[] = "Udfyld venligst by";
	}
	if ((isset($POSTVARS["cart_checkoutForm_PHONE"]) && isset($POSTVARS["cart_checkoutForm_CELLPHONE"])) && ($POSTVARS["cart_checkoutForm_PHONE"] == "" && $POSTVARS["cart_checkoutForm_CELLPHONE"] == "")){
		$errors[] = "Udfyld venligst enten telefon eller mobiltelefon";
	}
	if (isset($POSTVARS["cart_checkoutForm_EMAIL"]) && $POSTVARS["cart_checkoutForm_EMAIL"] == ""){
		$errors[] = "Udfyld venligst e-mail";
	}
	
	$levAdd = 0;
	if (isset($POSTVARS["cart_checkoutForm_DELIVERYNAME"]) && $POSTVARS["cart_checkoutForm_DELIVERYNAME"] != ""){
		$levAdd++;
	}
	if (isset($POSTVARS["cart_checkoutForm_DELIVERYADDRESS"]) && $POSTVARS["cart_checkoutForm_DELIVERYADDRESS"] != ""){
		$levAdd++;
	}
	if (isset($POSTVARS["cart_checkoutForm_DELIVERYZIPCODE"]) && $POSTVARS["cart_checkoutForm_DELIVERYZIPCODE"] != ""){
		$levAdd++;
	}
	if (isset($POSTVARS["cart_checkoutForm_DELIVERYCITY"]) && $POSTVARS["cart_checkoutForm_DELIVERYCITY"] != ""){
		$levAdd++;
	}
	if ($levAdd > 0 && $levAdd != 4){
		$errors[] = "Udfyld venligst en komplet leveringsadresse (eller evt. ingen seperat leveringsadresse)";
	}
	return $errors;
 }
 
 function receiptPage($arr_content){
 	global $checkoutSteps;
 	$html .= returnOrderFlow(count($checkoutSteps), $arr_content);
	$html .= "<h1>".returnFieldValue("PAGES", "HEADING", "ID", cmsTranslate("CartThanksPageId"))."</h1>";
	$html .= returnFieldValue("PAGES", "CONTENT", "ID", cmsTranslate("CartThanksPageId"));
	return $html;
 }
 
 /***
 function sendCartPage(){
 	$html .= "<h1>".cmsTranslate("CartSendQuote")."</h1>";
	$html .= "<p>".cmsTranslate("CartSendQuoteTopText")."</p>";
	$html .= "<form id='cart_checkoutForm' name='cart_checkoutForm' method='post'>";
	$html .= "
		<table width='100%' id='cartAddressTable'>
		<tr><td>
		<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderName")."</div>
		<div class='generatedFormFieldContainer'>
			<input value='' class='generatedFormField' type='text' name='cart_sendCart_NAME' id='cart_sendCart_NAME'/>
		</div>
		<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderEmail")."</div>
		<div class='generatedFormFieldContainer'>
			<input value='' class='generatedFormField' type='text' name='cart_sendCart_EMAIL' id='cart_sendCart_EMAIL'/>
		</div>
		</td></tr></table>
	";
	$html .= "
			<table class='table_orderbuttons' width='100%'>
				<tr id='cartBottomButtons2'>
					<td><input type='submit' name='BACKTOCART_CLICKED' value='&laquo;&nbsp;".cmsTranslate("CartEditCart")."' /></td> 
					<td><input type='submit' name='SENDCART_CLICKED' value='".cmsTranslate("CartSendQuote")."&nbsp;&raquo;' onclick='return cart_verifySendQuote();' class='forward' /></td> 
				</tr>
			</table>
	";
	$html .= "</form>";
	return $html;
 }
 ***/
 
  function sendCartPage(){
 	$html .= "<h1>".cmsTranslate("CartSendQuote")."</h1>";
	$html .= "<p>".cmsTranslate("CartSendQuoteTopText")."</p>";
	$html .= "<form id='cart_checkoutForm' name='cart_checkoutForm' method='post'>";
	$html .= "<input type='hidden' name='cameFrom' value='$_GET[cfrom]' />";
	$html .= "<input type='hidden' name='getCartid' value='$_GET[cartid]' />";
	/*
	$html .= "
		<table width='100%' id='cartAddressTable'>
		<tr><td>
		<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderName")."</div>
		<div class='generatedFormFieldContainer'>
			<input value='' class='generatedFormField' type='text' name='cart_sendCart_NAME' id='cart_sendCart_NAME'/>
		</div>
		<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderEmail")."</div>
		<div class='generatedFormFieldContainer'>
			<input value='' class='generatedFormField' type='text' name='cart_sendCart_EMAIL' id='cart_sendCart_EMAIL'/>
		</div>
		</td></tr></table>
	";
	*/
	/// BETALINGSBETINGELSE -- TILFØJET 15/9/2006 AF CJS /////////////////////////////
 	$sql = "select ID, TITLE from CART_PAYMENTTERMS order by ID asc";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result)){
		$loop++;
		$paymentTermHtml .= "<input ".($loop == 1 ? " checked " : "")."type='radio' name='cart_sendCart_PAYMENTTERM' value='$row[ID]' />&nbsp;$row[TITLE]<br/>";
	}
	/// END OF BETALINGSBETINGELSE /////////////////////////////
	$html .= "
		<table width='100%' id='cartAddressTable'>
		<tr><td>	
				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderName")."</div>
				<div class='generatedFormFieldContainer'>
					<input onkeypress='return noenter()' value='$ORDERDATA[NAME]' class='generatedFormField' type='text' name='cart_sendCart_NAME' id='cart_sendCart_NAME'/>
				</div>
				
				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderAddress")."</div>
				<div class='generatedFormFieldContainer'>
					<input onkeypress='return noenter()' value='$ORDERDATA[ADDRESS]' class='generatedFormField' type='text' name='cart_sendCart_ADDRESS' id='cart_sendCart_ADDRESS'/>
				</div>
	
				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderZipcode")."</div>
				<div class='generatedFormFieldContainer'>
					<input onkeypress='return noenter()' value='' class='generatedFormField' type='text' name='cart_sendCart_ZIPCODE' id='cart_sendCart_ZIPCODE'/>
				</div>
	
				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderCity")."</div>
				<div class='generatedFormFieldContainer'>
					<input onkeypress='return noenter()' value='$ORDERDATA[CITY]' class='generatedFormField' type='text' name='cart_sendCart_CITY' id='cart_sendCart_CITY'/>
				</div>
	
				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderPhone")."</div>
				<div class='generatedFormFieldContainer'>
					<input onkeypress='return noenter()' value='$ORDERDATA[PHONE]' class='generatedFormField' type='text' name='cart_sendCart_PHONE' id='cart_sendCart_PHONE'/>
				</div>
	
				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderCellphone")."</div>
				<div class='generatedFormFieldContainer'>
					<input onkeypress='return noenter()' value='$ORDERDATA[CELLPHONE]' class='generatedFormField' type='text' name='cart_sendCart_CELLPHONE' id='cart_sendCart_CELLPHONE'/>
				</div>
	
				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderEmail")."</div>
				<div class='generatedFormFieldContainer'>
					<input onkeypress='return noenter()' value='$ORDERDATA[EMAIL]' class='generatedFormField' type='text' name='cart_sendCart_EMAIL' id='cart_sendCart_EMAIL'/>
				</div>
				<div class='generatedFormFieldHeader'>".cmsTranslate("CartPlaceOrderPaymentterm")."</div>
				<div class='generatedFormFieldContainer'>
					$paymentTermHtml
				</div>

				<div class='generatedFormFieldHeader'>Evt. bemærkninger til tilbuddet</div>
				<div class='generatedFormFieldContainer'>
					<textarea class='generatedFormField' name='cart_sendCart_NOTES' id='cart_sendCart_NOTES'/>$ORDERDATA[NOTES]</textarea>
				</div>

		</td></tr></table>
	";		
	$html .= "
			<table class='table_orderbuttons' width='100%'>
				<tr id='cartBottomButtons2'>
					<td><input type='submit' name='BACKTOCART_CLICKED' value='&laquo;&nbsp;".cmsTranslate("CartEditCart")."' /></td> 
					<td><input type='submit' name='SENDCART_CLICKED' value='".cmsTranslate("CartSendQuote")."&nbsp;&raquo;' onclick='return cart_verifySendQuote();' class='forward' /></td> 
				</tr>
			</table>
	";
	$html .= "</form>";
	return $html;
 }
 
 function sendCartCompletePage(){
	$html .= "<h1>".returnFieldValue("PAGES", "HEADING", "ID", cmsTranslate("CartSendQuoteThanksPageId"))."</h1>";
	$html .= returnFieldValue("PAGES", "CONTENT", "ID", cmsTranslate("CartSendQuoteThanksPageId"));
	$html .= "<form id='cart_checkoutForm' name='cart_checkoutForm' method='post'>";	
	$html .= "
			<table class='table_orderbuttons' width='100%'>
				<tr id='cartBottomButtons2'>
					<td><input type='submit' name='BACKTOFRONTPAGE_CLICKED' value='&laquo;&laquo;&nbsp;".cmsTranslate("CartBackToFrontpage")."' /></td> 
					<td><input type='submit' name='BACKTOCART_CLICKED' value='&laquo;&nbsp;".cmsTranslate("CartEditCart")."' /></td> 
				</tr>
			</table>
	";
	$html .= "</form>";
	return $html;
 }
 
 function parseCheckoutErrors($errors){
	foreach ($errors as $val){
		$html .= "&raquo;&nbsp;$val<br/>";
	}
	return $html;
 }

 function updateCart(){
 	foreach ($_POST as $key => $val){
		if (strstr($key, "cartAmount_")){
			$temp = explode("_", $key);
			$id = $temp[1];
			if ($val > 0){
				$sql = "update CART_CONTENTS set AMOUNT='$val' where ID='$id'";
				$result = mysql_query($sql);
			} else {
				$sql = "delete from CART_CONTENTS where ID='$id'";
				$result = mysql_query($sql);
			}
		}
	}
 }
  
 function addToCart($orderId, $productId, $productTableName, $productAmount, $productFragt, $productDelDays, $productCustomPrice=0, $productCustomDescription="", $colliId){
	$sql = "select ID, AMOUNT from CART_CONTENTS where CART_ORDERS_ID='$orderId' and PRODUCT_ID='$productId' and PRODUCT_TABLENAME='$productTableName' and CUSTOM_PRICE='$productCustomPrice' and CUSTOM_DESCRIPTION='$productCustomDescription' and COLLI_ID='$colliId'";
	$resultExistingProd = mysql_query($sql);	 
	if (mysql_num_rows($resultExistingProd) == 0){
		$sql = "
			insert into CART_CONTENTS (
				CART_ORDERS_ID, PRODUCT_ID, PRODUCT_TABLENAME, AMOUNT, FRAGT, DELIVERY_DAYS, CUSTOM_PRICE, CUSTOM_DESCRIPTION, TIME_ADDED, COLLI_ID
			) values (
				'$orderId', '$productId', '$productTableName', '$productAmount', '$productFragt', '$productDelDays', '$productCustomPrice', '$productCustomDescription', '".time()."', '$colliId'
			)
		";
		$result = mysql_query($sql);	 
	} else {
		$rowExistingProd = mysql_fetch_array($resultExistingProd);
		$cartRowId = $rowExistingProd["ID"];
		$newAmount = (int) $rowExistingProd["AMOUNT"] + $productAmount;
		$sql = "update CART_CONTENTS set AMOUNT='$newAmount', TIME_ADDED='".time()."' where ID='$cartRowId'";
		$result = mysql_query($sql);
	}
 }
 
 function calculateShopPrice($price, $colliAmount, $colliDiscountPct, $colliDiscountAmount, $userDiscountPct){
	if ($colliAmount){
		$price = $price * $colliAmount; 
	}
	if ($colliDiscountPct > 0){
		$price = $price * ((100-$colliDiscountPct)/100);
	}
	if ($colliDiscountAmount > 0){
		$price = $price - $colliDiscountAmount;
	}
	if ($userDiscountPct > 0){
		$price = $price * ((100-$userDiscountPct)/100);
	}
	return $price;
 }

function returnUserPrice($user_id, $product_id) {
	$sql = "select USERPRICE from SHOP_USERPRICES where USER_ID = '$user_id' and PRODUCT_ID = '$product_id' limit 1";
	if ($res = mysql_query($sql)) {
		if (mysql_num_rows($res)>0) {
			return mysql_result($res,0);
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function return_include_payment_step($arr_content, $orderId=0){
	// Figure out if step "Payment" is to be shown
	// First check that there is a valid online payment option in the database
	$include_payment_step = true;
	$sql = "select ID from CART_PAYMENTTERMS where IS_CARDPAYMENT = 1";
	$res = mysql_query($sql);
	if (mysql_num_rows($res)==0) {
		$include_payment_step = false;
	}
	
	// Then check that the paymentterm set on the current orderId (if set) requires payment step
	// Not applicable on action "checkoutform" since this is where paymentterm is selected
	if ($include_payment_step && is_numeric($orderId) && $orderId > 0 && $arr_content[action] != "checkoutform") {
		$sql = "select count(*) from CART_ORDERS CO, CART_PAYMENTTERMS CP
					where 
						CO.PAYMENTTERM = CP.ID and
						CO.ID='$orderId' and
						CP.IS_CARDPAYMENT = '1'";
		$res = mysql_query($sql);	
		if (mysql_result($res,0)==0) {
			$include_payment_step = false;
		}
	}
	return $include_payment_step;
}

function returnOrderflow($active, $arr_content, $orderId=0) {
	global $checkoutSteps;
		
	$include_payment_step = return_include_payment_step($arr_content, $orderId);
	// Remove payment step from array
	if (!$include_payment_step) {
		$checkoutSteps = array_diff($checkoutSteps, array("CartCheckoutFlowPay"));
	}
		$i=1;
		foreach ($checkoutSteps as $k => $v) {
			$checkoutStepsIndexed[$i]=$v;
			$i++;
		}
		$checkoutSteps = $checkoutStepsIndexed;

		$steps = count($checkoutSteps);
	
		if ($steps > 0) {
			$html .= "\n<table id='cartCheckoutFlow'>\n\t<tr>";

			$loop = 0;			
			foreach ($checkoutSteps as $k => $v) {
				$loop++;
				if ($k == $active) {
					$a = "active";
				} else {
					$a = "";
				}				
				$html .= "\n\t\t<td class='cartCheckoutFlowStep'><img src='includes/images/cartstatus_$k$a.gif' alt='$k' /></td>";
				if ($steps > $loop) {
					$html .= "\n\t\t<td class='cartCheckoutFlowArrow'><img src='includes/images/cartstatus_next.gif' alt='$k' /></td>";
				}				
			}
			$html .= "\n\t</tr>\n\t<tr>";
			$loop = 0;			
			foreach ($checkoutSteps as $k => $v) {
				$loop++;
				if ($k == $active) {
					$a = " cartCheckoutActive";
				} else {
					$a = "";
				}				
				$html .= "\n\t\t<td class='cartCheckoutFlowLabel$a'>".cmsTranslate($v)."</td>";
				if ($steps > $loop) {
					$html .= "\n\t\t<td></td>";
				}				
			}
			$html .= "\n\t</tr>\n</table>";
		}
	return $html;
}

 function showCart($arr_content, $cartId, $for=''){
	// Allowed $for variable values:
	// "mail", "print", "summary"
	////////////////////////////////////////////	
 	global 	$showMomsForEachLine, $showFragtForEachLine,
			$cartMomsPct, $dbProductsMomsState, $cartProductsMomsState, $cartTotals,
			$exchangeRates, $customFragtFunctionName, $cartCustomButtons,
			$showDeliveryDateInCart,$showRelatedProductsInCart;
			
	/// VALUTA //////////////////////////////////
	$rate 		= $exchangeRates[$_SESSION[CURRENT_LANGUAGE]];
	$rateName	= $rate["FORKORTELSE"];
	$rateFactor	= 100 / (int) $rate["KURS"];
	////////////////////////////////////////////
	$orderId = createOrder($cartId);
	$sql = "select * from CART_CONTENTS where CART_ORDERS_ID='$orderId' order by TIME_ADDED desc";
	$result = mysql_query($sql);
	if ($for == "") {
		$html .= "<h1>".cmsTranslate("Cart")."</h1>";
		$html .= "<p>".cmsTranslate("CartTextAbove")."</p>";
	}
	if (mysql_num_rows($result) == 0){
		$html .= "<p class='cartempty'>".cmsTranslate("CartEmpty")."</p>";
	} else {
		if ($for == "") {
			$html .= "<form method='post' action='$arr_content[baseurl]/index.php?mode=cart'>";
			$html .= "<input type='hidden' value='' name='action'/>";
			$html .= "<input type='hidden' value='' name='safarisubmit'/>";
			$html .= "<input type='hidden' value='".(!strstr($_SERVER[HTTP_REFERER],"mode=cart") ? $_SERVER[HTTP_REFERER] : $_GET["r"])."' name='referer'/>";
		}
		if ($for == "mail") {
			$table_width = "width='100%'";
		} else {
			$table_width = "";
		}
		$html .= "<table id='cart' $table_width class='table_cart' cellspacing='0'>";
		$html .= "<tr valign='top' id='cartHeadingsTR'>
				<td>".cmsTranslate("CartProductNumber")."</td>
				<td>".cmsTranslate("CartProductName")."</td>
				<td align='right'>".cmsTranslate("CartAmount")."</td>
				<td align='right'>".cmsTranslate("CartPricePerUnit")."</td>";
		if ($showFragtForEachLine) {
			$html .= "				
				<td align='right'>".cmsTranslate("CartFreight")."</td>
			";
		}
		$html .= "
				<td align='right'>".cmsTranslate("CartSubtotal")."</td>
		";
		if ($showMomsForEachLine){
			if ($dbProductsMomsState == 1 && $cartProductsMomsState == 1){
				$html .= "<td align='right'>".cmsTranslate("CartVATPart")."</td>";
			} else {
				$html .= "<td align='right'>".cmsTranslate("CartVAT")."</td>";
			}
		}
		$html .= "</tr>";
		while ($row = mysql_fetch_array($result)){
			$loop++;
			$alternater = "";
			$productalternater = "";
			if ($loop%2 == 0) {
				$alternater = " even";
				$productalternater = " evenproduct";
			}

			// Calculate price per unit

			// 1. Check for custom price
			if ($row[CUSTOM_PRICE] != 0) {
				// Custom price exists, use this!
				$pricePerUnit = $row[CUSTOM_PRICE];
			} else {
				$pricePerUnit = false;
			}
			
			// 2. Check for user price
			if (!$pricePerUnit && $_SESSION[LOGGED_IN]) {
				if ($userprice = returnUserPrice($_SESSION[USERDETAILS][0][ID], $row["PRODUCT_ID"])) {
					$pricePerUnit = $userprice;
				}
			}
			
			// 3. Calculate group discount
			if (!$pricePerUnit && $_SESSION[LOGGED_IN]) {
				$discount_percentage = returnDiscountPercentage($_SESSION[USERDETAILS][0][ID], $row["PRODUCT_ID"], $arr_content);
			}
			
			// 4. Use base price, apply group discount & colli discount
			if (!$pricePerUnit) {
				$pricePerUnit = returnProductPrice($row["PRODUCT_ID"], $row["PRODUCT_TABLENAME"]);
				unset($colliDetails);
				if ($row[COLLI_ID]){
					$colliDetails = returnColliDetails($row[COLLI_ID]);
				}
				$pricePerUnit = calculateShopPrice($pricePerUnit, $colliDetails[QUANTITY], $colliDetails[DISCOUNT_PERCENTAGE], $colliDetails[DISCOUNT_AMOUNTPERCOLLI], $discount_percentage);
			}

			if ($dbProductsMomsState == 1 && $cartProductsMomsState == 1){
				$pricePerUnit 	= $pricePerUnit;
				$subTotal 		= $pricePerUnit * $row[AMOUNT];
				$moms 			= $pricePerUnit * $row[AMOUNT] * (1-(100/(100+$cartMomsPct)));
			}
			if ($dbProductsMomsState == 1 && $cartProductsMomsState == 0){
				$pricePerUnit 	= $pricePerUnit * 100/(100+$cartMomsPct);
				$subTotal 		= $pricePerUnit * $row[AMOUNT];
				$moms 			= $pricePerUnit * $row[AMOUNT] * ($cartMomsPct/100);
			}
			if ($dbProductsMomsState == 0 && $cartProductsMomsState == 1){
				$pricePerUnit 	= $pricePerUnit * (1+$cartMomsPct/100);
				$subTotal 		= $pricePerUnit * $row[AMOUNT];
				$moms 			= $pricePerUnit * $row[AMOUNT] * (1-(100/(100+$cartMomsPct)));
			}
			if ($dbProductsMomsState == 0 && $cartProductsMomsState == 0){
				$pricePerUnit 	= $pricePerUnit * 1;
				$subTotal 		= $pricePerUnit * $row[AMOUNT];
				$moms			= $pricePerUnit * $row[AMOUNT] * ($cartMomsPct/100);
				
			}
			///////////////
			$fragtPerUnit = $row[FRAGT];
			$fragtPerLine = $fragtPerUnit * $row[AMOUNT];
			$total += $subTotal;
			$html .= "</tr>
				<tr valign='top' class='cartProductsTR$productalternater";
			if (!$row[CUSTOM_DESCRIPTION]){
				$html .= "$alternater";
			}	
			$html .= "' id='cartProductsTR_$loop'>
					<td class='cartProductNumberTD'><span class='cartProductNumberSPAN'>".returnProductProductnumber($row["PRODUCT_ID"], $row["PRODUCT_TABLENAME"])."</span></td>
					<td class='cartProductNameTD'>".returnProductName($row["PRODUCT_ID"], $row["PRODUCT_TABLENAME"]).($colliDetails[0] ? " (".number_format($colliDetails[0],0,"",".")." ".cmsTranslate("CartUnits").")" : "");


			if ($showRelatedProductsInCart && $for!="mail" && $for != "print") {
				$rps = return_related_products($row[PRODUCT_ID]);
				if ($rps) {
					$rps = group_related_products($rps, $arr_content);
					$plinks = array();
					foreach ($rps as $rp) {
						$plinks[] = "<a href='$arr_content[baseurl]/index.php?mode=shop&action=showproduct&amp;group=$rp[GROUP_ID]&product=".urlencode($rp[PRODUCT_NUMBER])."' title='$rp[PRODUCT_NAME]'>$rp[PRODUCT_NAME]</a>";
					}
					$plinks = implode("",$plinks);
					if (isset($arr_content[showrelated])) {
						$html .= "<div class='cart_related' id='cart_related_$row[PRODUCT_ID]'>";
						$html .= cmsTranslate("CartRelated").$plinks;
						$html .= "</div>";
					} else {
						$html .= "<span class='cart_showrelated' id='cart_showrelated_$row[PRODUCT_ID]'><a class='cart_showrelated_link' id='cart_showrelatedlink_$row[PRODUCT_ID]' href='$arr_content[baseurl]/index.php?mode=cart&action=showcart&showrelated=1'>".cmsTranslate("CartShowRelated")."</a></span>";
						$html .= "<div class='cart_related hide_for_ajax' id='cart_related_$row[PRODUCT_ID]'>";
						$html .= cmsTranslate("CartRelated").$plinks;
						$html .= "</div>";
					}	
				}		
				
			}
			$html .= "</td>
					<td align='right' class='cartAmountTD'>";
					
			if ($for == "") {		
				$html .= "<input type='text' size='5' class='cartAmountField' name='cartAmount_$row[ID]' id='cartAmount_$row[ID]' value='".$row[AMOUNT]."'/>";
			} else {
				$html .= $row[AMOUNT];
			}	
			
			$html .= "</td>
					<td align='right' class='cartPriceTD'>".niceCartPrice($pricePerUnit * $rateFactor)."</td>";
			if ($showFragtForEachLine) {
				$html .= "<td align='right' class='cartPriceTD'>".niceCartPrice($fragtPerLine)."</td>";
			}
			$html .= "
					<td align='right' class='cartSubtotalTD'>".niceCartPrice($subTotal + $fragtPerLine)."</td>
			";
			if ($showMomsForEachLine){
				$html .= "<td align='right' class='cartVatTD'>".niceCartPrice($moms)."</td>";
			} 
			$html .= "
				</tr>
			";
			if ($row[CUSTOM_DESCRIPTION]){
				$html .= "
					<tr valign='top' class='cartCustomDescriptionTR$alternater' id='cartCustomDescriptionTR_$loop'>
						<td colspan='10' class='cartCustomDescriptionTD'>".$row[CUSTOM_DESCRIPTION]."</td>
					</tr>
				";
			}
		}
		$html .= "</table>";
		$html .= "<table class='table_carttotals";
		if ($for != "") {
			$html .= " bottomborder";
		}
		$html .= "' width='100%'>";
		////////////////////////////////////////////
		if (function_exists($customFragtFunctionName)){
			$fragtTotal = call_user_func($customFragtFunctionName, $cartId, $total);
		}
		$totalWithFragt = $total + $fragtTotal;
		$maxDeliveryTime = returnDeliveryTime($cartId);
		////////////////////////////////////////////
		foreach ($cartTotals as $k => $v){
			$html .= "
				<tr id='".$v["ID"]."'>
					<td";
			if ($v["ID"] == "cartTotal") {
				$html .= " class='cartTotal'";
			}
			$html .= ">".cmsTranslate($v["TEXT"]).":</td>
					<td align='right'";
			if ($v["ID"] == "cartTotal") {
				$html .= " class='cartTotal'";
			}
			$html .= ">$rateName. ".niceCartPrice($$v["USEVAR"] * $v["FRACTION"])."</td>
				</tr>
			";
		}
		if ($showDeliveryDateInCart){
			$html .= "
				<tr id='cartDeliverytimeTR'>
						<td>".cmsTranslate("CartEstimatedDelivery").":</td>
						<td align='right'>".returnDeliveryDate($maxDeliveryTime)."</td>
				</tr>
			";
		}
		$html .= "</table>";
		////////////////////////////////////////////
		if ($for == "") {
			$html .= "<table class='table_cartbuttons' width='100%'>";
			$html .= "
				<tr id='cartBottomButtons1'>
					<td align='right'>
						<input type='submit' value='".cmsTranslate("CartUpdatePrice")."' name='UPDATECART_CLICKED' />
						<input type='submit' value='".cmsTranslate("CartEmptyCart")."' name='EMPTYCART_CLICKED' onclick='if (confirm(\"".cmsTranslate("CartConfirmEmpty")."\")) {this.form.safarisubmit.value=\"EMPTYCART_CLICKED\"; this.form.submit();} else return false;' />
						<input type='button' value='".cmsTranslate("CartPrintCart")."' name='PRINTCART_CLICKED' onclick='window.open(\"$arr_content[baseurl]/index.php?mode=cart&action=printcart&printversion=1\")' />
					</td>
				</tr></table>
			";
			$html .= "<p>".cmsTranslate("CartTextBelow")."</p>";
		}
		if ($for == "") {
			$html .= "<table class='table_orderbuttons' width='100%'>";
			$html .= "<tr id='cartBottomButtons2'>
					<td align='right'>
						<input type='submit' id='CONTINUE_CLICKED' value='&laquo; ".cmsTranslate("CartContinueShopping")."' name='CONTINUE_CLICKED' onclick='' />
					</td>";
			if ($cartCustomButtons["SENDQUOTE"]) {
				$html .="
					<td>
						<input type='submit' id='SENDQUOTE_CLICKED' value='".cmsTranslate("CartSendOffer")." &raquo;' name='SENDQUOTE_CLICKED' onclick='' />
					</td>
				";
			}
			$html .= "
					<td>
						<input type='submit' id='CHECKOUT_CLICKED' value='".cmsTranslate("CartEnterAddress")." &raquo;' name='CHECKOUT_CLICKED' onclick='' class='forward' />
					</td>
				</tr>
			";
			////////////////////////////////////////////	
		}
		if ($for == "") {
			$html .= "</table>";
			$html .= "</form>";
		}
		if ($for == "summary"){
			$sql = "select ZIPCODE, DELIVERYZIPCODE from CART_ORDERS where CART_ID='$cartId' limit 1";
			$res = mysql_query($sql);
			$row_zc = mysql_fetch_assoc($res);
			if ($row_zc[ZIPCODE] != $row_zc[DELIVERYZIPCODE] && is_numeric($row_zc[DELIVERYZIPCODE]) && $row_zc[DELIVERYZIPCODE] > 0){
				$html .= "
					<div id='zipcode_changed'>
						<strong>BEMÆRK!</strong> Fragten er blevet genberegnet på baggrund af det postnummer ($row_zc[DELIVERYZIPCODE]), du har angivet på leveringsadressen. Den endelige totale fragt udgør <strong>kr. ".niceCartPrice($fragtTotal)."</strong>.
					</div>
				";
			}
		}
	}
//	$html .= "</table>";
	return $html; 	
 }

 function showMicroCart($arr_content, $cartId, $action){
	////////////////////////////////////////////	
 	global 	$showMomsForEachLine, $showFragtForEachLine,
			$cartMomsPct, $dbProductsMomsState, $cartProductsMomsState, $cartTotals,
			$exchangeRates, $customFragtFunctionName;
	/// VALUTA //////////////////////////////////
	$rate 		= $exchangeRates[$_SESSION[CURRENT_LANGUAGE]];
	$rateName	= $rate["FORKORTELSE"];
	$rateFactor	= 100 / (int) $rate["KURS"];
	////////////////////////////////////////////
	$orderId = createOrder($cartId);
	$sql = "select * from CART_CONTENTS where CART_ORDERS_ID='$orderId' order by TIME_ADDED desc";
	$result = mysql_query($sql);
	$html .= "<table id='microCartTable'>";
	$html .= "
		<tr class='microCartHeading'>
			<td colspan='2'>".cmsTranslate("Cart")."</td>
		</tr>
	";
	if (mysql_num_rows($result) == 0){
		$html .= "
			<tr>
				<td colspan='2'>".cmsTranslate("CartEmpty")."</td>
			</tr>
		";
	} else {
		$html .= "
			<tr class='microCartCheckoutTR'>
				<td colspan='2'>
					<a href='$arr_content[baseurl]/index.php?mode=cart&action=checkoutform'>".cmsTranslate("CartPlaceOrder")."</a>
				</td>
			</tr>
		";
		while ($row = mysql_fetch_array($result)){
			$loop++;
				if ($loop % 2 == 0) {
					$oddeven = " microCartEven";
				} else {
					$oddeven = "";
				}			
			$pricePerUnit = ($row[CUSTOM_PRICE] != 0 ? $row[CUSTOM_PRICE] : returnProductPrice($row["PRODUCT_ID"], $row["PRODUCT_TABLENAME"]));	
			///////////////
			if ($dbProductsMomsState == 1 && $cartProductsMomsState == 1){
				$pricePerUnit 	= $pricePerUnit;
				$subTotal 		= $pricePerUnit * $row[AMOUNT];
				$moms 			= $pricePerUnit * $row[AMOUNT] * (1-(100/(100+$cartMomsPct)));
				$VATtxt			= cmsTranslate("CartWithVAT");
			}
			if ($dbProductsMomsState == 1 && $cartProductsMomsState == 0){
				$pricePerUnit 	= $pricePerUnit * 100/(100+$cartMomsPct);
				$subTotal 		= $pricePerUnit * $row[AMOUNT];
				$moms 			= $pricePerUnit * $row[AMOUNT] * ($cartMomsPct/100);
				$VATtxt			= cmsTranslate("CartWithoutVAT");
			}
			if ($dbProductsMomsState == 0 && $cartProductsMomsState == 1){
				$pricePerUnit 	= $pricePerUnit * (1+$cartMomsPct/100);
				$subTotal 		= $pricePerUnit * $row[AMOUNT];
				$moms 			= $pricePerUnit * $row[AMOUNT] * (1-(100/(100+$cartMomsPct)));
				$VATtxt			= cmsTranslate("CartWithVAT");
			}
			if ($dbProductsMomsState == 0 && $cartProductsMomsState == 0){
				$pricePerUnit 	= $pricePerUnit * 1;
				$subTotal 		= $pricePerUnit * $row[AMOUNT];
				$moms			= $pricePerUnit * $row[AMOUNT] * ($cartMomsPct/100);
				$VATtxt			= cmsTranslate("CartWithoutVAT");				
			}
			///////////////
			$fragtPerUnit = $row[FRAGT];
			$fragtPerLine = $fragtPerUnit * $row[AMOUNT];
			// $fragtTotal += $fragtPerLine;
			///////////////
			$total += $subTotal;
			if ($loop == 1 && $action == "addtocart"){
				$imagePath = returnProductTableVar($row["PRODUCT_TABLENAME"], "PRODUCT_IMAGE_PATH")."/".rawurlencode(returnProductSomeColumn($row["PRODUCT_ID"], $row["PRODUCT_TABLENAME"], "PRODUCT_IMAGE_COLUMN"));
				$imageSizes = getimagesize($imagePath );
				$imageRatio = $imageSizes[0]/$imageSizes[1];
				$html .= "
					<tr class='microCartLatestTR'>
						<td colspan='2'>".cmsTranslate("CartJustAdded").":</td>
					</tr>
					<tr class='microCartProductsTR$oddeven'>
						<td>" .
							returnProductProductnumber($row["PRODUCT_ID"], $row["PRODUCT_TABLENAME"]) . "-" . 
							returnProductName($row["PRODUCT_ID"], $row["PRODUCT_TABLENAME"]) .
						"</td>
						<td><img ".($imageRatio >=1 ? "height='40'" : "width='40'")."' src='".$imagePath."'/></td>
					</tr>
					<tr class='microCartAmountTR$oddeven'>
						<td class='microCartLabel'>".cmsTranslate("CartAmount")."</td>
						<td class='microCartNumber'>".$row[AMOUNT]."</td>
					</tr>
					<tr class='microCartPriceTR$oddeven'>
						<td class='microCartLabel'>".cmsTranslate("CartPricePerUnit")."</td>
						<td class='microCartNumber'>".niceCartPrice($pricePerUnit)."</td>
					</tr>
					<tr class='microCartSeperatorTR'>
						<td colspan='2'></td>
					</tr>
				";
			} else {
				$html .= "
					<tr class='microCartProductsTR$oddeven'>
						<td colspan='2'>" .
							returnProductProductnumber($row["PRODUCT_ID"], $row["PRODUCT_TABLENAME"]) . ":&nbsp;" . 
							returnProductName($row["PRODUCT_ID"], $row["PRODUCT_TABLENAME"]) .
						"</td>
					</tr>
					<tr class='microCartAmountTR$oddeven'>
						<td class='microCartLabel'>".cmsTranslate("CartAmount")."</td>
						<td class='microCartNumber'>".$row[AMOUNT]."</td>
					</tr>
					<tr class='microCartPriceTR$oddeven'>
						<td class='microCartLabel'>".cmsTranslate("CartPricePerUnit")."</td>
						<td class='microCartNumber'>".niceCartPrice($pricePerUnit)."</td>
					</tr>
				";			
			}
		}
		if (function_exists($customFragtFunctionName)){
			$fragtTotal = call_user_func($customFragtFunctionName, $cartId);
		}
		$totalWithFragt = $total + $fragtTotal;
		$maxDeliveryTime = returnDeliveryTime($cartId);	
		$html .= "
			<tr class='microCartSeperatorTR'>
				<td colspan='2'></td>
			</tr>
			<tr class='microCartFragtTR'>
				<td class='microCartLabel'>".cmsTranslate("CartFreight")."</td>
				<td class='microCartNumber'>".niceCartPrice($fragtTotal)."</td>
			</tr>
			<tr class='microCartTotalTR'>
				<td class='microCartLabel'>".cmsTranslate("CartTotal")." ".$VATtxt."</td>
				<td class='microCartNumber'>".niceCartPrice($totalWithFragt)."</td>
			</tr>
			<tr class='microCartDeliveryTR'>
				<td class='microCartLabel'>".cmsTranslate("CartEstimatedDelivery")."</td>
				<td class='microCartNumber'>".returnDeliveryDate($maxDeliveryTime)."</td>
			</tr>
			<tr class='microCartEditTR'>
				<td colspan='2'>
					<a href='$arr_content[baseurl]/index.php?mode=cart&action=showcart'>".cmsTranslate("CartEditCart")."</a>
				</td>
			</tr>
			<tr class='microCartCheckoutTR'>
				<td colspan='2'>
					<a href='$arr_content[baseurl]/index.php?mode=cart&action=checkoutform'>".cmsTranslate("CartPlaceOrder")."</a>
				</td>
			</tr>
		";
	}
	$html .= "</table>";
	$html .= "<div id='cartid' class='hideforajax'>$cartId</div>";
	return $html;
 }
 
 function niceCartPrice($val){
 	/// HUSK CMS TRANSLATE i number_format!
 	return number_format($val, cmsTranslate("CartNumberOfDecimals"), cmsTranslate("CartDecimalPoint"), cmsTranslate("CartThousandsSeperator"));
 }
 
 function returnProductName($productId, $productTable){
	global $productTables;
	$idColumn = $productTables[$productTable]["PRODUCT_ID_COLUMN"];
	$nameColumn = $productTables[$productTable]["PRODUCT_NAME_COLUMN"];
	if (!$nameColumn){
		return "[?] NAMEKOLONNE IKKE DEFINERET";
	}
	$sql = "select $nameColumn from $productTable where $idColumn='$productId'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	return $row[$nameColumn];
 }

 function returnProductPrice($productId, $productTable){
	global $productTables;
	$idColumn = $productTables[$productTable]["PRODUCT_ID_COLUMN"];
	$priceColumn = $productTables[$productTable]["PRODUCT_PRICE_COLUMN"];
	if (!$priceColumn){
		return "[?] PRISKOLONNE IKKE DEFINERET";
	}
	$sql = "select $priceColumn from $productTable where $idColumn='$productId'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	return $row[$priceColumn];
 }
 
 function returnProductProductnumber($productId, $productTable){
	global $productTables;
	$idColumn = $productTables[$productTable]["PRODUCT_ID_COLUMN"];
	$productNoColumn = $productTables[$productTable]["PRODUCT_PRODUCTNUMBER_COLUMN"];
	if (!$productNoColumn){
		return "[?]";
	}
	$sql = "select $productNoColumn from $productTable where $idColumn='$productId'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	return $row[$productNoColumn];
 }
 
 function returnProductImage($productId, $productTable){
	global $productTables;
	$idColumn = $productTables[$productTable]["PRODUCT_ID_COLUMN"];
	$imageColumn = $productTables[$productTable]["PRODUCT_IMAGE_COLUMN"];
	if (!$imageColumn){
		return "[?]";
	}
	$sql = "select $imageColumn from $productTable where $idColumn='$productId'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	return $row[$imageColumn];
 }
 
 function returnProductSomeColumn($productId, $productTable, $VAR){
	global $productTables;
	$idColumn 	= $productTables[$productTable]["PRODUCT_ID_COLUMN"];
	$varColumn 	= $productTables[$productTable]["$VAR"];
	if (!$varColumn){
		return "[?]";
	}
	$sql = "select $varColumn from $productTable where $idColumn='$productId'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	return $row[$varColumn];
 }
 
  function returnProductTableVar($productTable, $VAR){
	global $productTables;
	$varColumn 	= $productTables[$productTable]["$VAR"];
	if (!$varColumn){
		return "[?]";
	} else {
		return $varColumn;
	}
 }
 
 function returnDeliveryTime($cartId){
	$orderId = createOrder($cartId); 
 	$sql = "select DELIVERY_DAYS from CART_CONTENTS where CART_ORDERS_ID='$orderId' order by DELIVERY_DAYS desc limit 1";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	return $row[DELIVERY_DAYS];
 }

 function returnDeliveryDate($numberOfDays){
	return date(cmsTranslate("CartDeliveryDateFormat"), mktime(0,0,0,date('m'), date('d')+$numberOfDays, date('Y')));
 }
 
   
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: LANGUAGES AND TRANSLATION
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function cmsTranslate($key, $optionalKey=false, $str_translate_to_shortname="") {	
	/*
	// $key is language-element to translate
	// $optionalKey is when language-elements to translate isan array position
	// $str_translate_to_shortname is used to override $_SESSION[CURRENT_LANGUAGE]. Must match a language shortname as defines in the LANGUAGES table
	*/
	global $cmsLang;	
	
	if ($str_translate_to_shortname == "") {
		$lang = $_SESSION[CURRENT_LANGUAGE];	
	} else {
		$lang = $str_translate_to_shortname;
	}

	if (!$optionalKey) {
		return $cmsLang[$lang][$key];
	} else {
		return $cmsLang[$lang][$key][$optionalKey];
	}
} 
 
 function languageSelectorMenu($arr_content, $seperator){
  $sql = "select NAME, SHORTNAME from LANGUAGES";
  $result = mysql_query($sql);
  while($row = mysql_fetch_array($result)){
   $langs[] = "<a href='$arr_content[baseurl]/index.php?lang=$row[SHORTNAME]'>$row[NAME]</a>";
  }
  $html .= "<div id='languageSelector'>"; 
  $html .= implode($seperator, $langs);
  $html .= "</div>";  
  return $html;
 }

 function returnLanguageId($shortname){
  $sql = "select ID from LANGUAGES where SHORTNAME='$shortname'";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  return $row[ID];
 }

 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: PAGE CONTENT GENERATION
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function return_printerFriendly_url($arr_content) {
	if ($arr_content[rw] == "false"){
		if ($arr_content[baseurl]."/" == "http://".$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI]) {
			$url = $arr_content[baseurl]."/index.php?pageid=".getFrontpageId($arr_content[lang], $arr_content[site])."&amp;printversion=1";
			global $useModRewrite_enforce_url_rewrite;
			if ($useModRewrite_enforce_url_rewrite) {
				// is front page, special case, so...
				$url = $arr_content[baseurl]."/".return_rewrite_keyword("", getFrontpageId($arr_content[lang], $arr_content[site]), "PAGES", $arr_content[site])."/print/";
			}
		} else {
			$url = $_SERVER[REQUEST_URI]."&amp;printversion=1".($arr_content[mode]=="search" ? "&amp;searchwords=".$arr_content[searchwords] : "");
		}
		global $useModRewrite_enforce_url_rewrite;
	} else {
		if ($_SERVER[REQUEST_URI][strlen($_SERVER[REQUEST_URI])-1] != "/") {
			$addslash = "/";
		}
        $url = $_SERVER[REQUEST_URI].$addslash."print/";
	}
	return $url;
}

function printerFriendly($arr_content, $topofpage=true, $printversion=true, $tipafriend=true){
    if (!$arr_content[show_printversion_link]) {
        return false;
    }
    global $cmsLang, $imagesFolderPath;    
    $html .= "<div id='printerfriendly'>";
    if ($topofpage){
        $html .= "&uarr;&nbsp;";
            $html .= "<a title='".cmsTranslate("ToTheTop")."' href='$_SERVER[REDIRECT_URL]#'>".cmsTranslate("ToTheTop")."</a>";
    }
    if (hasPrintTemplate($arr_content[pageid]) && $printversion){
        if ($topofpage && $printversion){
            $html .= "&nbsp;&middot;&nbsp;";
        }
          $html .= "<img src='".$imagesFolderPath."/printvenlig.gif' alt='".cmsTranslate("PrinterFriendly")."' />&nbsp;";
		  $html .= "<a title='".cmsTranslate("PrinterFriendly")."' href='".return_printerFriendly_url($arr_content)."' target='_blank'>".cmsTranslate("PrinterFriendly")."</a>";
    }
    if ($tipafriend){
        if ($topofpage || $printversion){
            $html .= "&nbsp;&middot;&nbsp;";
        }
        $html .= "<a title='".cmsTranslate("SendToFriend")."' href='$arr_content[baseurl]/index.php?mode=stf'>".cmsTranslate("SendToFriend")."</a>";
    }
    $html .= "</div>";
    return $html;
}

function return_default_template($site_id) {
	// Function to return default template_id for given site_id
	$sql = "select DEFAULT_TEMPLATE from SITES where SITE_ID = '$site_id'";
	if ($result = mysql_query($sql)) {
		return mysql_result($result,0);
	} else {
		return false;
	}
}
 
 function hasPrintTemplate($pageid){
	//////////////////
	$sql = "select DEFAULT_TEMPLATE from SITES where SITE_ID='$_SESSION[CURRENT_SITE]'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);	
 	$defaultTemplateId = $row[DEFAULT_TEMPLATE];
	//////////////////
	$sql = "select PRINTTEMPLATE_PATH from TEMPLATES where ID='$defaultTemplateId'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);	
	$defaultPrinttemplatePath = $row[PRINTTEMPLATE_PATH];
	//////////////////
	$sql = "select TEMPLATE from PAGES where ID='$pageid'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	$templateId = $row["TEMPLATE"];
	//////////////////
	$sql = "select PRINTTEMPLATE_PATH from TEMPLATES where ID='$templateId'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);	
	//////////////////
	if ($templateId) {
		if ($row[PRINTTEMPLATE_PATH] == "") {
			return false;
		} else {
			return true;
		}
	} else {
		if ($defaultPrinttemplatePath == "") {
			return false;
		} else {
			return true;
		}
	}
 }
 
 
function buildPageMainContent($arr_content){ // $pageid, $mode, $show_printerfriendly="TRUE"
// 2007-04-16	-	Site seperation: $arr_content[site] added to all where clauses
	global $tablesToSearch, $pluginsPath, $arr_content_exclude; //$pointtopage, $searchwords, 

	// Check for point to page
	if ($pointtopage = return_pointtopage_id($arr_content[pageid])) {
		$arr_content[pageid] = $pointtopage;
	}
/*
	echo "ARR_CONTENT:";
	echo "<pre>";
	print_r($arr_content);
	echo "</pre>";
*/	
	if (!$arr_content[mode] && $arr_content[pageid]){  
		if ($arr_content[http_status] == "404"){
			return page404();
		}
		
		if ($arr_content[ignoreunfinished] != 1){
			$sql = "select * from PAGES where UNFINISHED='0' and DELETED='0' and PUBLISHED='1' and ID='$arr_content[pageid]' and SITE_ID = '$arr_content[site]'";
		} else {
			if ($arr_content[ignoreunfinished] == 1){
				$sql = "select * from PAGES where DELETED='0' and PUBLISHED>-1 and UNFINISHED>-1 and ID='$arr_content[pageid]' and SITE_ID = '$arr_content[site]'";
			}
   		}
		$result = mysql_query($sql) or die(__FUNCTION__." ".mysql_error());   
		if ($arr_content[ignoreunfinished] == 1){
			$previewaccess = 0;
			$grant_sql = "select * from GRANTS where PAGE_ID='$arr_content[pageid]' and GRANTCODE='$arr_content[grant]'";
			$grant_result = mysql_query($grant_sql);
			if (mysql_num_rows($grant_result)!=1){
				return mustBeLoggedIn($arr_content); 
			} else {
				$previewaccess = 1;
   			}
		}
		if (mysql_num_rows($result)==0){
			return page404();
		} else {
			if ($arr_content[pageid] && !checkPageRights($arr_content[pageid])){
				if ($previewaccess != 1){
					return mustBeLoggedIn($arr_content);
				}
			}  
			$row = mysql_fetch_array($result);
			if ($row[BOOK_ID]){
				$pinc = $pluginsPath . "/bookmaker/bookmaker.inc.php";
				if (file_exists($pinc) && is_file($pinc)){
					include($pinc);
				} else {
					return page404()."<br /><strong>KUNNE IKKE INKLUDERE BOOKMAKER PLUGIN</strong>";
				}		
			}
			$pinc = $row[PHP_INCLUDE_PATH];
   			if ($pinc && $pinc != "" && !strstr($pinc, "://")){
				if (file_exists($pinc) && is_file($pinc)){
					include($pinc);
				} else {
					return page404()."<br /><strong>KUNNE IKKE INKLUDERE SIDETOP</strong>";
				}
   			}
			$gallery = galleryBuilder($arr_content[pageid]);
			$related_content = relatedBox($arr_content[baseurl], $arr_content[pageid], "PAGES");
			$attached_files = fileBox($arr_content[pageid], "PAGES", $arr_content);  
			$attached_form = formularBox($arr_content[baseurl], $arr_content[pageid], "PAGES", $arr_content); 

			if (trim($row[HEADING]) != "" && !in_array("HEADING", $arr_content_exclude)){
				$html .= "<h1 class='heading'>$row[HEADING]</h1>";
			} 
			if (trim($row[SUBHEADING]) != "" && !in_array("SUBHEADING", $arr_content_exclude)){
				$html .= "<p class='subheading'>$row[SUBHEADING]</p>";
			}
			$html .= "
				<div id='content_block'>
					$row[CONTENT]
					$attached_form
					$gallery
					$related_content
					$attached_files	 
				</div>
			";
			$html .= lastEdited($row[CHANGED_DATE], $row[EDIT_AUTHOR_ID], $arr_content);
			echo $html; // .$pf
			$pinc = $row[PHP_INCLUDEAFTER_PATH];
			if ($pinc && $pinc != "" && !strstr($pinc, "://")){
				if (file_exists($pinc) && is_file($pinc)){
					include($pinc);
				} else {
					return page404()."<br /><strong>KUNNE IKKE INKLUDERE SIDEBUND</strong>";
				}
   			}
		}
	}
	if ($arr_content[mode]){
		if ($modePlugin = modePluginExists($arr_content, "content")){
			include($modePlugin);
		}
 	}
	if ($arr_content[mode] == "gallery"){
	}

/*
//	2007-04-16	-	Temporarily disabled, implement later
	if ($arr_content[mode] == "users"){
		echo "USERS!";
		print_r($arr_content);
	}
*/

	if ($arr_content[mode] == "blogs"){
		if (!$arr_content[blogid] && !$arr_content[postid]){
			return blog_posts_overview($arr_content);
		}
		if ($arr_content[blogid]){
			$pemission_to_read = check_data_permission("DATA_FE_BLOG_READ", "BLOGS", $arr_content[blogid], "", $_SESSION[USERDETAILS][0][0], "loose");
			if ($pemission_to_read){
				if ($arr_content[blogid] && !$arr_content[postid]){
					return blog_posts_overview($arr_content);
				} else if ($arr_content[blogid] && $arr_content[postid]){
					return blog_post_complete($arr_content);
				}
			} else {
				return "<div class='blog_useralert_red'>".cmsTranslate("BlogsNoAccess")."</div>";
			}
		}
	}
	if ($arr_content[mode] == "news"){
		if (!$arr_content[newsid]){
			return newsArchive($arr_content);
  		} else {
			return displayNews($arr_content); //.$pf
		}
	}
	if ($arr_content[mode] == "events"){
		if (!$arr_content[eventid]){
			return calendar($arr_content); // .$pf
		} else {
			return displayEvent($arr_content); // .$pf
		}
	}
	if ($arr_content[mode] == "login"){
		return loginPage($arr_content);
	}
	if ($arr_content[mode] == "loginfailure"){
		return loginFailure($arr_content);
	}
	if ($arr_content[mode] == "stf"){
		return sendToFriend($_SERVER[HTTP_REFERER], $arr_content[from], $arr_content[sent], $arr_content[baseurl]);
	}  
/*
// 2007-04-16	-	Obsolete! Marked for deletion!
	if ($arr_content[mode] == "sub"){
		return "
			<h1 class='heading'>Du er nu tilmeldt nyhedsbrevet</h1>
				Du har tilmeldt adressen $arr_content[adr]. Du kan til enhver tid vende tilbage til websitet og afmelde dig igen.
		";
	}  
	if ($arr_content[mode] == "unsub"){
		return "
			<h1 class='heading'>Du er nu afmeldt nyhedsbrevet</h1>
			Du har afmeldt adressen $arr_content[adr]. Du kan til enhver tid vende tilbage til websitet og tilmelde dig igen.
		";
	}  
	if ($arr_content[mode] == "profileupdated"){
		return "<h1 class='heading'>Tak fordi du opdaterede dine oplysninger</h1>Ændringerne er nu registreret hos os.";
	}  
*/
	if ($arr_content[mode] == "formware" && $arr_content[formid] != ""){
		return formGenerator($arr_content);
	}  
	if ($arr_content[mode] == "thanks" && $arr_content[formid] != ""){
		return formTakTekst($arr_content);
	}  
	if ($arr_content[mode] == "search"){ 
		foreach ($tablesToSearch as $key => $val){
			$val[functionParameters][0] = $arr_content[searchwords];
			$funcName 	= $val[functionName];
			$params		= $val[functionParameters];
			$resultsTxt	= $val[resultsHeading];
			$resHtml = call_user_func("$funcName", $arr_content, $params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6], $params[7], $params[8], $params[9]);
			if ($resHtml != ""){
				$html .= "<h1>".cmsTranslate($resultsTxt)."</h1>";
				$html .= $resHtml;
			}
		}
		if (trim($html) == ""){
			$html .= "<h1>"."\"".$arr_content["searchwords"]."\" ";
			$html .= cmsTranslate("SearchNoResultsHeading");
			$html .= "</h1>";
			$html .= "<p>";
			$html .= cmsTranslate("SearchNoResults");
			$html .= "</p>";
		}
		return $html; // .$pf
	}  
}

 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: PAGES MISC.
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function return_pointtopage_id($pageid) {
	$sql = "select POINTTOPAGE_ID from PAGES where ID='$pageid'";
	$ptpresult = mysql_query($sql);
	$ptprow = mysql_fetch_array($ptpresult);
	if ($ptprow[POINTTOPAGE_ID] != 0) {
		return $ptprow[POINTTOPAGE_ID];
	} else {
		return false;
  	}
}
 
 function isOnline($id){
  $sql = "select PUBLISHED from PAGES where ID='$id'";
  $result = mysql_query($sql) or die(__FUNCTION__." ".mysql_error());
  $row = mysql_fetch_array($result);
  if ($row["PUBLISHED"]==1) return true;
  return false;
 }
 
function checkPageRights($cid) {
	$protected_state = recursive_page_rights($cid);
	if ($protected_state == 1) {
		return true;
	}
	if ($protected_state == 2 && !$_SESSION["USERDETAILS"]){
		return false;
	}
	if ($protected_state == 2 &&  $_SESSION["USERDETAILS"]){
		$sql = "select GROUP_ID from GROUPS_PAGES where PAGE_ID='$cid'";
		$result2 = mysql_query($sql) or die(__FUNCTION__." ".mysql_error());
		while ($row2 = mysql_fetch_row($result2)) {
			$allowed[] = $row2[0];
		}
		if (is_array($_SESSION["USERDETAILS"][1])){
			$allowed_for_user = array_intersect($allowed, $_SESSION["USERDETAILS"][1]);
		}
		if (!$allowed_for_user || count($allowed_for_user) == 0){
			return false;
		} else { 
			return true;
		}
	}
}

function recursive_page_rights($start_at_pageid){
	$sql = "select PROTECTED from PAGES where ID='".$start_at_pageid."' and PUBLISHED='1' and DELETED='0'";
	$result = mysql_query($sql) or die(__FUNCTION__." ".mysql_error());
	$row = mysql_fetch_array($result);
	return $row["PROTECTED"];
	/*
	$sql = "select PARENT_ID, PROTECTED from PAGES where ID='".$start_at_pageid."' and PUBLISHED='1' and DELETED='0'";
	$result = mysql_query($sql) or die(__FUNCTION__." ".mysql_error());
	while ($row = mysql_fetch_array($result)){
		if ($protected_state != 2){
			$protected_state = $row["PROTECTED"];
		}
		recursive_page_rights($row[PARENT_ID]);
	}
	*/
}

 function getLanguage($page_id){
  $sql 	= "select LANGUAGE from PAGES where ID='$page_id'";
  $result = mysql_query($sql) or die(__FUNCTION__." ".mysql_error());      
  $row = mysql_fetch_row($result);
  $sql = "select SHORTNAME from LANGUAGES where ID='$row[0]'";
  $result 	= mysql_query($sql) or die(__FUNCTION__." ".mysql_error());      
  $row = mysql_fetch_row($result);
  return $row[0];
 }  

 function getFrontpageId($lang, $site) {
  $sql = "select ID from LANGUAGES where SHORTNAME='$lang'";
  $result = mysql_query($sql) or die(__FUNCTION__." ".mysql_error());
  $temprow = mysql_fetch_row($result);
  $sql = "select ID from PAGES where IS_FRONTPAGE='1' and SITE_ID='$site' and LANGUAGE='$temprow[0]'";
  $result = mysql_query($sql) or die(__FUNCTION__." ".mysql_error());
  $row = mysql_fetch_row($result);
  return $row[0]; 
 }
   
function returnPageTitleTag($arr_content, $prefix){
	//print_r($arr_content);
	if ($m = $arr_content[mode]) {
		if ($m == "news")   	$title = cmsTranslate("NewsPlural");
		if ($m == "events") 	$title = cmsTranslate("EventPlural");
		if ($m == "search") 	$title = cmsTranslate("SearchFor") . " '" . $arr_content[searchwords] . "'";
		if ($m == "stf")    	$title = cmsTranslate("SendToFriend");
		if ($m == "sub")    	$title = cmsTranslate("Newsletter");
		if ($m == "unsub")  	$title = cmsTranslate("Newsletter");
		if ($m == "cart")   	$title = cmsTranslate("Cart");
		if ($m == "formware")	$title = "";
		if ($m == "shop")		$title = cmsTranslate("shopShop");
		if ($m == "blogs")		$title = cmsTranslate("BlogsBlogs");
		if ($m == "picturearchive")		$title = cmsTranslate("gallery_galleries");
	}
	if($arr_content[pageid] && !$m) {
		$title .= returnPageTitle($arr_content[pageid], "titletag");
	} else if($arr_content[newsid]) {
		$title .= " - " . returnNewsTitle($arr_content[newsid]); 
	} else if($arr_content[eventid]) {
		$title .= " - " . returnEventTitle($arr_content[eventid]); 
	} else if ($arr_content[formid]){
		$title .= returnFormTitle($arr_content[formid]);
	} else if ($arr_content[group] && $m == "shop"){
		$title .= " - " . returnFieldValue("SHOP_PRODUCTGROUPS", "NAME", "ID ", $arr_content[group]);
	} else if ($arr_content[product] && $m == "shop"){
		$title .= " - " . returnFieldValue("SHOP_PRODUCTS", "NAME", "PRODUCT_NUMBER ", $arr_content[product]);
	} else if ($m == "blogs"){
		if ($arr_content[blogid]){
			$title .= " &raquo; " . returnFieldValue("BLOGS", "TITLE", "ID", $arr_content[blogid]);
		}
		if ($arr_content[postid]){
			$title .= " &raquo; " . returnFieldValue("BLOGPOSTS", "HEADING", "ID", $arr_content[postid]);
		}
	} else if ($m == "picturearchive"){
		if ($arr_content[folderid]){
			$title .= " &raquo; " . returnFieldValue("PICTUREARCHIVE_FOLDERS", "TITLE", "ID", $arr_content[folderid]);
		}	
	}
	return $prefix.$title;
}

 function determineCSS($pageid){
  $sql = "select NEWS, EVENTS, STF, SEARCH, NEWSLETTER, CUSTOM from BOX_SETTINGS where PAGE_ID='$pageid'";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  $css = "";
  if ($row[0] + $row[1] + $row[2] + $row[3] + $row[4] == 0 && $row[5] == "") {
   $css = "_fullwidth";
  }
  return $css;
 }

function includeHeaderPlugin($arr_content){
// 2007-04-13	-	Cinput changed from $pageid to $arr_content to make site seperation possible
	// Returns array [0]=status, [1]=path
	// Status 0 = no include
	// Status 1 = valid include
	// Status 2 = invalid include

	$pageid = $arr_content[pageid];
	$sql = "select PHP_HEADERINCLUDE_PATH from PAGES where ID='$pageid' and SITE_ID = '$arr_content[site]'";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	if ($row[PHP_HEADERINCLUDE_PATH]) {
		// Include IS defined for this page
		if (file_exists($row[PHP_HEADERINCLUDE_PATH])) {
			// Include found, now include it
			include_once($row[PHP_HEADERINCLUDE_PATH]);
//			return array(1, $row[PHP_HEADERINCLUDE_PATH]);
		} else {
			// Include NOT found, return error message to that effect
			return "Kunne ikke inkludere side-header / Could not include page-header";
//			return array(2, "");
		}
	}
/*
	 else {
		return array(0, "");
	}
*/
}

function include_mode_plugin($arr_content, $where) {
	if ($plugin = modePluginExists($arr_content, $where)){
		include($plugin);
	}
}

function modePluginExists($arr_content, $where){
 	global $cmsAbsoluteServerPath;
	if ($where == "content"){
 		if ((is_file($modePlugin = $cmsAbsoluteServerPath . "/modules/$arr_content[mode]/frontend/".$arr_content[mode]."_index.php")) || (is_file($modePlugin = $_SERVER['DOCUMENT_ROOT'] . "/includes/cms_plugins/$arr_content[mode]/frontend/".$arr_content[mode]."_index.php"))){
			return $modePlugin;
		} else {
			return false;
		}
	}
	if ($where == "pagetop"){
 		if ((is_file($modePlugin = $cmsAbsoluteServerPath . "/modules/$arr_content[mode]/frontend/".$arr_content[mode]."_actions.inc.php")) || (is_file($modePlugin = $_SERVER['DOCUMENT_ROOT'] . "/includes/cms_plugins/$arr_content[mode]/frontend/".$arr_content[mode]."_actions.inc.php"))){
			return $modePlugin;
		} else {
			return false;
		}
	}
 }
 
function modeHtmlHeaderIncludes($arr_content, $type) {
	// Function used in template to return mode-specific stuff
	// Will include any files in the cms/modules/modulename/frontend folder 
	// as well as in the includes/ folder outside the cms folder.
 	global $cmsURL, $cmsAbsoluteServerPath;
	if ($arr_content[mode] != "") {
		if ($type == "javascript") {
	 		if (is_file($cmsAbsoluteServerPath . "/modules/$arr_content[mode]/frontend/$arr_content[mode].js")){
				$html = "\n<script src='$cmsURL/modules/$arr_content[mode]/frontend/$arr_content[mode].js' type='text/javascript'></script>";
			}
			// js.php files in the modules/frontend folder are also included, but they are written
			// directly from this function to avoid re-including config.inc & common.inc in the js.php file.
	 		if (is_file($file = $cmsAbsoluteServerPath . "/modules/$arr_content[mode]/frontend/$arr_content[mode].js.php")){
				include_once($file);
			}
	 		if (is_file($_SERVER['DOCUMENT_ROOT']."/includes/javascript/$arr_content[mode].js")){
				$html .= "\n<script src='$arr_content[baseurl]/includes/javascript/$arr_content[mode].js' type='text/javascript'></script>";
			}
		}
		if ($type == "css") {
			if (is_file($cmsAbsoluteServerPath . "/modules/$arr_content[mode]/frontend/$arr_content[mode].css")) {
				$html = "\n<link rel='stylesheet' type='text/css' href='$cmsURL/modules/$arr_content[mode]/frontend/$arr_content[mode].css' />";
			}
			if (is_file($_SERVER['DOCUMENT_ROOT']."/includes/css/$arr_content[mode].css")) {
				$html .= "\n<link rel='stylesheet' type='text/css' href='$arr_content[baseurl]/includes/css/$arr_content[mode].css' />";
			}
		}
	}
	return $html;
}

function metaTagGenerator($arr_content){
	global $cmsVersion;
	$html .= "\n<meta http-equiv='content-type' content='text/html;charset=UTF-8' />
		\n<meta name='generator' content='Instans CMS $cmsVersion' />";
	if (check_method($arr_content["methods"], "print")) {
		$html .= "\n<meta name='robots' content='noindex,follow' />";
	} elseif ($arr_content["mode"] == "search") {
		$html .= "\n<meta name='robots' content='noindex,follow' />";
	} else {
		$html .= "\n<meta name='robots' content='index,follow' />";
	}
	$html .= "\n<meta name='DC.language' scheme='DCTERMS.RFC1766' content='".$arr_content[lang]."' />";
	if (!$arr_content[pageid]) {
		if ($arr_content[mode]){
			if ($arr_content[mode] == "news" && $arr_content[feedid]){
				if (returnFieldValue("NEWSFEEDS", "SYNDICATION_ALLOWED", "ID", $arr_content[feedid]) == 1){
					$rss_key = returnFieldValue("NEWSFEEDS", "SYNDICATION_KEY", "ID", $arr_content[feedid]);
					$rss_title	= returnFieldValue("NEWSFEEDS", "NAME", "ID", $arr_content[feedid]) . " - RSS-feed";
					$rss_href 	= $arr_content[baseurl]."/feeds/newsfeed_".$arr_content[feedid]."_".$rss_key.".xml";
					$html .= "
		\n<link rel='alternate' type='application/rss+xml' title='$rss_title' href='$rss_href' />";
				}
			}
			if ($arr_content[mode] == "news" && $arr_content[newsid]){
				$meta_desc = substr(strip_tags(returnFieldValue("NEWS", "CONTENT", "ID", $arr_content["newsid"])), 0, 100) . "...";				
				$html .= "\n<meta name='DC.title' lang='".$arr_content[lang]."' content='".returnNewsTitle($arr_content["newsid"])."' />
  		\n<meta name='DC.subject' lang='".$arr_content[lang]."' content='".returnNewsTitle($arr_content["newsid"])."' />
  		\n<meta name='DC.description' lang='".$arr_content[lang]."' content='".$meta_desc."' />
  		\n<meta name='description' content='" . $meta_desc . "' />";
			}
			if ($arr_content[mode] == "events" && $arr_content[eventid]){
				$meta_desc = substr(strip_tags(returnFieldValue("EVENTS", "CONTENT", "ID", $arr_content["newsid"])), 0, 100) . "...";				
				$html .= "
  		\n<meta name='DC.title' lang='".$arr_content[lang]."' content='".returnEventTitle($arr_content["eventid"])."' />
  		\n<meta name='DC.subject' lang='".$arr_content[lang]."' content='".returnEventTitle($arr_content["eventid"])."' />
  		\n<meta name='DC.description' lang='".$arr_content[lang]."' content='".$meta_desc."' />
  		\n<meta name='description' content='" . $meta_desc . "' />";
			}
			if ($arr_content[mode] == "blogs" && $arr_content[blogid]){
				$pemission_to_read = check_data_permission("DATA_FE_BLOG_READ", "BLOGS", $arr_content[blogid], "", $_SESSION[USERDETAILS][0][0], "loose");
				if ($pemission_to_read){
					if (returnFieldValue("BLOGS", "SYNDICATION_ALLOWED", "ID", $arr_content[blogid]) == 1){
						$rss_key = returnFieldValue("BLOGS", "SYNDICATION_KEY", "ID", $arr_content[blogid]);
						$rss_title	= returnFieldValue("BLOGS", "TITLE", "ID", $arr_content[blogid]) . " - RSS-feed";
						$rss_href 	= $arr_content[baseurl]."/feeds/blog_".$arr_content[blogid]."_".$rss_key.".xml";
						$html .= "
			\n<link rel='alternate' type='application/rss+xml' title='$rss_title' href='$rss_href' />";
					}
				}
				if (!$arr_content[postid]){
					$blog_title = returnFieldValue("BLOGS", "TITLE", "ID", $arr_content[blogid]);
					$blog_desc = strip_tags(returnFieldValue("BLOGS", "DESCRIPTION", "ID", $arr_content[blogid]));
					$html .= "
		\n<meta name='DC.title' lang='".$arr_content[lang]."' content='".$blog_title."' />
  		\n<meta name='DC.subject' lang='".$arr_content[lang]."' content='".$blog_title."' />
  		\n<meta name='DC.description' lang='".$arr_content[lang]."' content='".$blog_desc."' />
  		\n<meta name='description' content='" . $blog_desc . "' />";
				} else {
					$blog_title = returnFieldValue("BLOGPOSTS", "HEADING", "ID", $arr_content[postid]);
					$blog_desc = strip_tags(returnFieldValue("BLOGPOSTS", "CONTENTSNIPPET", "ID", $arr_content[postid]));
					$html .= "
		\n<meta name='DC.title' lang='".$arr_content[lang]."' content='".$blog_title."' />
  		\n<meta name='DC.subject' lang='".$arr_content[lang]."' content='".$blog_title."' />
  		\n<meta name='DC.description' lang='".$arr_content[lang]."' content='".$blog_desc."' />
  		\n<meta name='description' content='" . $blog_desc . "' />";
				}
			}
		} else {
			return;
		}
	} else {
		// First get page-specific meta tags:
		$sql = "select META_DESCRIPTION, META_KEYWORDS from PAGES where ID=".$arr_content["pageid"];
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		$meta_desc = $row[META_DESCRIPTION];
		$meta_key = $row[META_KEYWORDS];
		// If not set, fetch from GENERAL_SETTINGS
		if ($meta_desc == "") {
			$meta_desc = returnGeneralSetting("META_DESCRIPTION");
		}
		if ($meta_key == "") {
			$meta_key = returnGeneralSetting("META_KEYWORDS");
		}
		$html .= "
  		\n<meta name='DC.title' lang='".$arr_content[lang]."' content='".returnPageTitle($arr_content["pageid"], "titletag")."' /> 
  		\n<meta name='DC.subject' lang='".$arr_content[lang]."' content='".returnPageTitle($arr_content["pageid"], "titletag")."' />
  		\n<meta name='DC.description' lang='".$arr_content[lang]."' content='" . $meta_desc . "' />
  		\n<meta name='description' content='" . $meta_desc . "' />
  		\n<meta name='keywords' content='" . $meta_key . "' />";
	}
  	return $html;
 }
    
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: FRONTEND MENU
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

 function returnMenuSeperator($heading){
  $html .= "<div class='menuSeperator'>$heading</div>";
  return $html;
 }

 function initFrontendMenu() {
  unset($_SESSION["CURRENT_OPEN_MENUS"]);
  $_SESSION["CURRENT_OPEN_MENUS"][0] = "__START";
  unset($_SESSION["CURRENT_OPEN_PRODUCTMENUS"]);
  $_SESSION["CURRENT_OPEN_PRODUCTMENUS"][0] = "__START";
 }

 function hasChildren($id){		
  $sql = "select count(ID) from PAGES where PARENT_ID='$id' and DELETED='0' and UNFINISHED='0' and NO_DISPLAY='0' and PUBLISHED='1'";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  if ($row[0]>0) return true;
  return false;
 }

 function returnFirstPageInMenuPlaceholder($pageId){
  $sql = "select ID from PAGES where PARENT_ID='$pageId' and DELETED='0' and UNFINISHED='0' and PUBLISHED='1' order by POSITION asc limit 1";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  return $row[ID];
 }
    
function newBuildFrontendMenu($siteid, $menuid, $parentid, $level, $arr_content, $prefixIfHasChilds="", $css_id="", $arrPath=array(), $maxLevels=false, $only_thread_id=false){
    global $menuPageIdsWithOtherMenus;
    if ($maxLevels === false) $maxLevels = 10000;
// 2007-04-16    -    $siteid can be used to EXPLICITLY OVERRIDE the site set in $arr_content. 
//                    $menuid can be used to EXPLICITLY OVERRIDE automatic menu selection from parameters set in $arr_content. 
//                    This function should be called only directly from the template. 
// 2008-06-09	-     No longer active links to menuitem pointing to current page (MAP)


    //    If $siteid id not set (""), the site will come from $arr_content. This is the INTENDED DEFAULT BEHAVIOR of this function!
    if (!is_numeric($siteid)) {
        $siteid = $arr_content[site];
    }
    //    If $menuid id not set (""), the menu will be auto-selected from parameters set in $arr_content. This is the INTENDED DEFAULT BEHAVIOR of this function!
    if (!is_numeric($menuid)) {
        $sql = "select MENU_ID from MENUS where SITE_ID in (0, $arr_content[site]) and DEFAULT_LANGUAGE in (0,".returnLanguageId($arr_content[lang]).") LIMIT 1";
        $res = mysql_query($sql);
        if (mysql_num_rows($res)>0) {
            $menuid = mysql_result($res,0);
        }
    }
    $pageid = $arr_content[pageid];
     if (($parentid == 0 || array_search($parentid, $_SESSION["CURRENT_OPEN_MENUS"])) && ($level <= $maxLevels)) {
           $sql = "
            select 
                 ID, PARENT_ID, BOOK_ID, SITE_ID, MENU_ID, PROTECTED, BREADCRUMB, 
                 IS_MENUPLACEHOLDER, POINTTOPAGE_URL 
            from 
                 PAGES 
            where 
                 SITE_ID='$siteid' and MENU_ID='$menuid' and PARENT_ID='$parentid' and 
                DELETED='0' and UNFINISHED='0' and PUBLISHED='1' and NO_DISPLAY='0'".($only_thread_id ? " and THREAD_ID='$only_thread_id'" : "")."
            order by
                 POSITION asc
           ";
        $result = mysql_query($sql) or die(mysql_error());
        echo "\n<ul".($css_id != "" && $level == 0 ? " id='$css_id'" : "").">";
        while ($row = mysql_fetch_array($result)) {
            if ($menuFunctionName = $menuPageIdsWithOtherMenus[$row["ID"]]){
                echo call_user_func($menuFunctionName, $row["ID"]);
            } else {
                if (($row["PARENT_ID"] == 0 || array_search($row[ID], $_SESSION["CURRENT_OPEN_MENUS"]) || array_search($row[PARENT_ID], $_SESSION["CURRENT_OPEN_MENUS"]))){
                    if ($row["PROTECTED"]==1 || $row["PROTECTED"]==2 && checkPageRights($row[ID])){
                        $hasChilds = hasChildren($row[ID]); 
                        if (in_array($row[ID], $arrPath)){
                            $inPath = "inpath";
                        } else {
                            $inPath = "";
                        }
                        if ($row[ID] == $pageid) {
                            $class = " class='$inPath selected'";
                        } else {
                            $class = " class='$inPath'";
                        }    
                        echo "\n\t<li$class>";
                        if ($row[POINTTOPAGE_URL]) {
                            if ($prefixIfHasChilds){
                                echo "
                                    <span class='".($hasChilds ? "menuPrefix_hasSubpoints" : "menuPrefix_noSubpoints")."'>".
                                        $prefixIfHasChilds."
                                    </span>
                                ";
                            }
							if ($row[ID] != $arr_content[pageid]) {
	                            echo "<a title='$row[BREADCRUMB]' href='".$row[POINTTOPAGE_URL]."'$class>".$row[BREADCRUMB]."</a>";
	                        } else {
	                        	echo $row[BREADCRUMB];
	                        }
                        } else if ($row[IS_MENUPLACEHOLDER]==1) {
                            $actualPageId = returnFirstPageInMenuPlaceholder($row[ID]);
                            if ($prefixIfHasChilds){
                                echo "
                                    <span class='".($hasChilds ? "menuPrefix_hasSubpoints" : "menuPrefix_noSubpoints")."'>".
                                        $prefixIfHasChilds."
                                    </span>
                                ";
                            }
							if ($row[ID] != $arr_content[pageid]) {
                            	echo "<a title='$row[BREADCRUMB]' href='$arr_content[baseurl]/index.php?pageid=".$actualPageId.($row[BOOK_ID] ? "&amp;bookid=".$row[BOOK_ID] : "")."'$class>".$row[BREADCRUMB]."</a>";
                            } else {
                            	echo $row[BREADCRUMB];
                            }
                        } else {
                            if ($prefixIfHasChilds){
                                echo "
                                    <span class='".($hasChilds ? "menuPrefix_hasSubpoints" : "menuPrefix_noSubpoints")."'>".
                                        $prefixIfHasChilds."
                                    </span>
                                ";
                            }
							if ($row[ID] != $arr_content[pageid]) {
	                            echo "<a title='$row[BREADCRUMB]' href='$arr_content[baseurl]/index.php?pageid=".$row[ID].($row[BOOK_ID] ? "&amp;bookid=".$row[BOOK_ID] : "")."'$class>".$row[BREADCRUMB]."</a>";
							} else {
								echo $row[BREADCRUMB];
	                        }
                        }                
                        if ($hasChilds) {
                            newBuildFrontendMenu($row[SITE_ID], $row[MENU_ID], $row[ID], $level+1, $arr_content, $prefixIfHasChilds, $css_id, $arrPath, $maxLevels, $only_thread_id);
                            echo "</li>";
                        } else {
                            echo "</li>";
                        }
                    }
                }
            }
        }
        echo "</ul>";
    }
 }
 
function frontendMenuPath($pageid, $mode, $arr_content) {
// 2007-04-16	-	Added $arr_content[site] to sql where clause (MAP)
	global $sti, $eventid, $newsid, $menuPageIdsWithOtherMenus;
	if (!$mode) {
		$sql = "select SITE_ID, MENU_ID, PARENT_ID, ID, BREADCRUMB, POINTTOPAGE_URL, BOOK_ID from PAGES where ID='$pageid' and PUBLISHED='1' and DELETED='0' and SITE_ID in (0,'$arr_content[site]')";
		$result 	= mysql_query($sql) or die(__FUNCTION__." ".mysql_error());
		while ($row = mysql_fetch_array($result)) {
			if (!$menuPageIdsWithOtherMenus[$row["ID"]] && checkPageRights($row[ID])){
				if ($row[POINTTOPAGE_URL]) {
					if (strstr($row[POINTTOPAGE_URL], "http://")){
						$sti[] = "<a href='$row[POINTTOPAGE_URL]'" . ($row[BOOK_ID] ? "&amp;bookid=$row[BOOK_ID]" : "") . "'>".$row[BREADCRUMB]."</a>";		
					} else {
						$sti[] = "<a href='$arr_content[baseurl]/$row[POINTTOPAGE_URL]'" . ($row[BOOK_ID] ? "&amp;bookid=$row[BOOK_ID]" : "") . "'>".$row[BREADCRUMB]."</a>";		
					}
				} else {
					$sti[] = "<a href='$arr_content[baseurl]/index.php?pageid=$row[ID]" . ($row[BOOK_ID] ? "&amp;bookid=$row[BOOK_ID]" : "") . "'>".$row[BREADCRUMB]."</a>";
				}
			}
		$_SESSION["CURRENT_OPEN_MENUS"][] = $row[ID];
		frontendMenuPath($row[PARENT_ID], $mode, $arr_content);
		}
	}
	if ($mode == "news") {
		if ($arr_content[newsid]) {
			$sti[] = "<a href='$arr_content[baseurl]/index.php?mode=news&amp;feedid=$arr_content[feedid]&amp;newsid=$arr_content[newsid]'>".newsHeading($arr_content[newsid])."</a>";
		}	
		$sti[] = "<a href='$arr_content[baseurl]/index.php?mode=news&amp;feedid=$arr_content[feedid]'>".returnNewsfeedTitle($arr_content)."</a>";
	}
	if ($mode == "events") {
		if ($arr_content[eventid]) {
			$sti[] = "<a href='$arr_content[baseurl]/index.php?mode=events&amp;calendarid=$arr_content[calendarid]&amp;eventid=$arr_content[eventid]'>".eventHeading($arr_content[eventid])."</a>";
		}
		if ($arr_content[calendarid]){
			$sti[] = "<a href='$arr_content[baseurl]/index.php?mode=events&amp;calendarid=$arr_content[calendarid]'>".returnCalendarTitle($arr_content[calendarid])."</a>";
		} else {
			foreach ($_POST as $k=>$v){
				if (strstr($k, "calendarBox_")){
					$temp = explode("_", $k);
					$selectedCalsIds[] = $temp[1];
					$selectedCalsTitles[] = "<a href='$arr_content[baseurl]/index.php?mode=events&amp;calendarid=$temp[1]'>".returnCalendarTitle($temp[1])."</a>";
				}
			}
			if (is_array($selectedCalsTitles)) {
				$sti[] = implode(", ", $selectedCalsTitles);
			}
		}
	}

  if ($mode == "login" || $mode == "loginfailure") {	
   $sti[] = "<a href='$arr_content[baseurl]/index.php?mode=login'>".cmsTranslate("Login")."</a>";
  }
  if ($mode == "stf") {
   $sti[] = cmsTranslate("SendToFriend");
  }
  if ($mode == "sub" || $mode == "unsub") {
   $sti[] = cmsTranslate("Newsletter");
  }
  if ($mode == "formware") {
   $sti[] = returnFormTitle($arr_content[formid]);
   $sti[] = cmsTranslate("Registration");
  }  
  if ($mode == "thanks") {
   $sti[] = returnFormTitle($arr_content[formid]);
   $sti[] = cmsTranslate("Registration");
  }  
  if ($mode == "search") {
   $sti[] = cmsTranslate("SearchFor")." <em>$arr_content[searchwords]</em>";
   $sti[] = cmsTranslate("SearchRes");
  }
  if ($mode == "profileupdated") {
   $sti[] = "Opdatering af oplysninger";
  }  
  if ($mode == "cart") {
  	if ($arr_content["action"] == "checkoutform"){
   		$sti[] = cmsTranslate("CartEnterAddress");
   	}
  	if ($arr_content["action"] == "checkoutfinalize"){
   		$sti[] = cmsTranslate("CartVerifyAndApprove");
   	}
  	if ($arr_content["action"] == "checkoutcomplete"){
   		$sti[] = cmsTranslate("CartOrderComplete");
   	}
  	if ($arr_content["action"] == "sendcart"){
   		$sti[] = cmsTranslate("CartSendQuote");
   	}
  	if ($arr_content["action"] == "sendcartcomplete"){
   		$sti[] = cmsTranslate("CartSendQuote");
   	}
   	$sti[] = cmsTranslate("Cart");
  }  
  if ($mode == "shop") {
  	if ($arr_content[group]){
		$sti[] = returnFieldValue("SHOP_PRODUCTGROUPS", "NAME", "ID ", $arr_content[group]);
	}
	if ($arr_content[product]){
		$sti[] = returnFieldValue("SHOP_PRODUCTS", "NAME", "PRODUCT_NUMBER ", $arr_content[product]);
	}
	$sti[] = "<a href='$arr_content[baseurl]/index.php?mode=shop'>".cmsTranslate("shopShop")."</a>";
  }
  if ($mode == "blogs"){
	if ($arr_content[postid]){
		$sti[] = "<a href='".$arr_content[baseurl]."/index.php?mode=blogs&blogid=$arr_content[blogid]&postid=$arr_content[postid]'>".returnFieldValue("BLOGPOSTS", "HEADING", "ID", $arr_content[postid])."</a>";
	}
	if ($arr_content[blogid]){
		$sti[] = "<a href='".$arr_content[baseurl]."/index.php?mode=blogs&blogid=$arr_content[blogid]'>".returnFieldValue("BLOGS", "TITLE", "ID", $arr_content[blogid])."</a>";
	}
  	$sti[] = "<a href='".$arr_content[baseurl]."/index.php?mode=blogs'>".cmsTranslate("BlogsBlogs")."</a>";
  }
  return $sti;
 } 

 function returnHTMLMenuPath($pageid, $mode, $prefix, $postfix, $seperator, $arr_content){
  	$path = frontendMenuPath($pageid, $mode, $arr_content);
  	if (!is_array($path)) {
  		return;
  	} else {
		$path = array_reverse($path);
  		$html .= "<div id='menupath'>";
 		$html .= $prefix;
  		$html .= implode($seperator, $path);
  		$html .= $postfix;
  		$html .= "</div>";
  		return $html;
  	}
 }

 function newReturnHTMLMenuPath($pageid, $mode, $prefix, $postfix, $seperator, $arr_content){
  	$path = frontendMenuPath($pageid, $mode, $arr_content);
  	if (!is_array($path)) {
  		return;
  	} else {
		$path = array_reverse($path);
 		$html .= $prefix;
  		$html .= implode($seperator, $path);
  		$html .= $postfix;
  		return $html;
  	}
 }

 
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: NEWSLETTER
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

 function subscribe($letter_id, $email, $status) {
  $not_listed = false;
  $sql = "select * from NEWSLETTERS_RECIPIENTS where EMAIL='$email'";
  $result = mysql_query($sql) or die(__FUNCTION__." ".mysql_error());
  if (mysql_num_rows($result) == 0) {
   $not_listed = true;
  }
  if ($not_listed) {
   if ($status == "subscribe") {
    $sql = "insert into NEWSLETTERS_RECIPIENTS (NEWSLETTER_ID, EMAIL, STATUS) values ($letter_id, '$email', 1)";
	$result = mysql_query($sql) or die(__FUNCTION__." ".mysql_error());
   }
   if ($status == "unsubscribe") {
   }
  }
  if (!$not_listed) {
   if ($status == "subscribe") {
    $sql = "update NEWSLETTERS_RECIPIENTS set STATUS=1 where EMAIL='$email'";
	$result = mysql_query($sql) or die(__FUNCTION__." ".mysql_error());
   }
   if ($status == "unsubscribe") {
    $sql = "update NEWSLETTERS_RECIPIENTS set STATUS=0 where EMAIL='$email'";
	$result = mysql_query($sql) or die(__FUNCTION__." ".mysql_error());
   }
  }
 }

 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: SEND PAGE-LINK TO FRIEND
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function safeForMailheader($value) {
	if ($value == '') {
		return $value;
	}
	if (is_numeric($value)) {
		return $value;
	}
	// Fjern newline karakterer for at forhindre mail header injection
	return preg_replace("/%0A/","",preg_replace("/%0D/","",preg_replace("/\\n+/","",preg_replace("/\\r+/","",$value))));
}


 function sendLinkToFriend($POSTVARS) {
  foreach ($POSTVARS as $key=>$value) {
   $$key = $value;
  }
	$besked = stripslashes($besked);
	$linktekst = str_replace("http://", "", $linktosend);

	// Bemærk <br> i stedet for <br/> for kompatibilitet med ældre mail klienter
	$mailhtml = "<div style='font-family:arial'><p><strong>".cmsTranslate('Salutation')." $vens_navn!</strong></p>";
	$mailhtml .= "<p>" . ($besked != "" ? cmsTranslate('rtf_greeting')." $dit_navn:<br>$besked" : "")."</p>";
	$mailhtml .= "<p>".cmsTranslate('rtf_mailintro')." ".returnSiteName($_SESSION[CURRENT_SITE]).":<br><a href='$linktosend' target='_blank' style='color:black'>$linktekst</a></p>";
	$mailhtml .= "<p>".cmsTranslate('rtf_bestregards').",<br>$dit_navn</p></div>";

	$textpart = str_replace('<br>', "\\n", $mailhtml);
	$textpart = strip_tags($textpart);

	$vens_navn = safeForMailheader($vens_navn);
	$vens_email = safeForMailheader($vens_email);
	$dit_navn = safeForMailheader($dit_navn);
	$din_email = safeForMailheader($din_email);

    $mail = new htmlMimeMail();

	// Change to UFT-8 encoding
	$mail->setTextCharset("UTF-8");
	$mail->setHTMLCharset("UTF-8");
	$mail->setHeadCharset("UTF-8"); 

    $mail->setHtml($mailhtml, $textpart);
	$mail->setFrom("$dit_navn <$din_email>");
	$mail->setSubject(cmsTranslate("rtf_seepage"));
	$mail->send(array("$vens_navn <$vens_email>"), 'mail');

 }
 
 function sendToFriend($link, $from, $sent, $baseurl) {
   if (!$sent) { 
   	if (!strstr($link, "&lang=")){
		// Sandsynligvis ikke nødvendigt mere (?) (CJS, 15/6/07)
		// $link .= "&lang=" . $_SESSION[CURRENT_LANGUAGE];
	}
    $html = "
    <h1 class='heading'>".cmsTranslate('rtf_heading')."</h1>".cmsTranslate('rtf_pageintro')."<br/><br/>
    <form action='$baseurl' name='sendtofriendform' method='post'>
    <input type='hidden' name='dothis' value=''>
    <input type='hidden' name='from' value='$from'>
    <input type='hidden' name='linktosend' value='$link'>
	<div class='generatedFormFieldHeader'>".cmsTranslate('rtf_formname').":</div>
    <div class='generatedFormFieldContainer'>
		<input class='generatedFormField' type='text' name='dit_navn' size='50'/>
	</div>
	<div class='generatedFormFieldHeader'>".cmsTranslate('rtf_formemail').":</div>
    <div class='generatedFormFieldContainer'>
		<input class='generatedFormField' type='text' name='din_email' size='50'/>
	</div>
	<div class='generatedFormFieldHeader'>".cmsTranslate('rtf_formfriendname').":</div>
    <div class='generatedFormFieldContainer'>
		<input class='generatedFormField' type='text' name='vens_navn' size='50'/>
	</div>
    <div class='generatedFormFieldHeader'>".cmsTranslate('rtf_formfriendemail').":</div>
    <div class='generatedFormFieldContainer'>
		<input class='generatedFormField' type='text' name='vens_email' size='50'/>
	</div>
	<div class='generatedFormFieldHeader'>".cmsTranslate('rtf_formfriendmessage').":</div>
	<div class='generatedFormFieldContainer'>
		<textarea name='besked' class='generatedFormField' cols='49' rows='10'></textarea>
	</div>
	<div class='generatedFormFieldHeader'>".cmsTranslate('rtf_formlink').":</div>
	<div class='generatedFormFieldContainer'>
		<a class='generel' href='$link' target='_blank'>".cmsTranslate('rtf_linktext')."</a> (".cmsTranslate('rtf_newwindow').")
	</div>
	<div class='generatedFormButtonBar'><input type='button' value='".cmsTranslate('rtf_formsendbutton')."' class='inputfelt' onclick='verifySendToFriend()'/></div>
    </form>
    ";
   }
   if ($sent) {
    $html = "
    <h1 class='heading'>".cmsTranslate('rtf_messagesent')."</h1>
    ".cmsTranslate('rtf_thankyou');
   }
  return $html;  
 }
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: SEARCH ENGINE
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

 function searchBox($arr_content){
	$pageid = $arr_content[pageid];
  if ($pageid){
  	if (getBoxState("SEARCH", $pageid) == 0) {
		return "";
  	}
  }  
  $html = "
    <div class='marginBox'>
	 <div class='boxTitle'>".cmsTranslate("SearchBoxHeading")."</div>
	  <form id='searchform' name='searchform' method='post' action='$arr_content[baseurl]/index.php?mode=search'>
	   <input type='text' id='searchwords' name='searchwords' class='searchFormField' ";
	   $html .= "value='".cmsTranslate("enterSearchterm")."'";
	   $html .= "onfocus=\"if(this.value=='";
	   $html .= cmsTranslate("enterSearchterm");
	   $html .= "')this.value=''\" onblur=\"if(this.value=='')this.value='";
	   $html .= cmsTranslate("enterSearchterm");
	   $html .= "'\" onkeypress='entsub(this.form)' />";
	   $html .= "<input type='hidden' id='searchwords_x' name='searchwords_x' />
	    <input type='button' id='searchbutton' name='searchbutton' value='".cmsTranslate("SearchButton")."' class='searchFormButton' onclick='search(this.form.searchwords.value)' />
	  </form>
	</div>
  ";
  return $html;
 } 
  
function returnSearchResults($arr_content, $searchwords, $contentType){
	if ($searchwords == "") {
		return;
	}
	$site_id = $_SESSION[CURRENT_SITE];
	if (!is_numeric($site_id)) {
		return;
	}
	global $tablesToSearch;
	$entities_searchwords = htmlentities($searchwords);
	$langId = returnLanguageId($_SESSION[CURRENT_LANGUAGE]); 

	// HUSK: Pt.søger funktionen alle sider der er tilgængelig for alle. Dvs. sider, som kræver særlige rettigheder søges ikke!!
	
	switch ($contentType) {
		case "PAGES":
			$sql = "
			 select 
			 	PAGES.ID, 
			 	PAGES.HEADING, 
			 	PAGES.SUBHEADING, 
			 	PAGES.CONTENT, 
			 	PAGES.BOOK_ID, 
			 	PAGES.META_DESCRIPTION, 
			 	PAGES.META_KEYWORDS, 
			 	PAGES.META_SEOTITLE, 
			 	MENUS.MENU_TITLE
			 from
			 	PAGES, MENUS 
			 where
			 	PAGES.MENU_ID = MENUS.MENU_ID
			 	and PAGES.LANGUAGE='$langId' 
			 	and (
			 		PAGES.BREADCRUMB like '%$searchwords%' 
			 		or PAGES.HEADING like '%$searchwords%' 
			 		or PAGES.SUBHEADING like '%$searchwords%' 
			 		or PAGES.CONTENT like '%$searchwords%'
			 		or PAGES.META_DESCRIPTION like '%$searchwords%'
			 		or PAGES.META_KEYWORDS like '%$searchwords%'
			 		or PAGES.META_SEOTITLE like '%$searchwords%'
			 		or PAGES.BREADCRUMB like '%$searchwords%'
			 		or PAGES.BREADCRUMB like '%$entities_searchwords%' 
			 		or PAGES.HEADING like '%$entities_searchwords%' 
			 		or PAGES.SUBHEADING like '%$entities_searchwords%' 
			 		or PAGES.CONTENT like '%$entities_searchwords%'
			 		or PAGES.META_DESCRIPTION like '%$entities_searchwords%'
			 		or PAGES.META_KEYWORDS like '%$entities_searchwords%'
			 		or PAGES.META_SEOTITLE like '%$entities_searchwords%'
			 		or PAGES.BREADCRUMB like '%$entities_searchwords%'
			 		) 
			 		and PAGES.DELETED='0' 
			 	and PAGES.UNFINISHED='0' 
			 	and PAGES.PROTECTED='1'
			 	and PAGES.PUBLISHED='1' 
			 	and PAGES.POINTTOPAGE_URL=''
			 	and PAGES.POINTTOPAGE_ID='0'
			 	and PAGES.SITE_ID='$site_id'";
			$result = mysql_query($sql);
			$num_results = mysql_num_rows($result);   
			while ($row = mysql_fetch_array($result)){
				// First score it!
				$score = 0;
				$score = $score + countOccurences($row[HEADING], $searchwords, 2);
				$score = $score + countOccurences($row[SUBHEADING], $searchwords, 1.5);
				$score = $score + countOccurences($row[CONTENT], $searchwords);
				$score = $score + countOccurences($row[MENU_TITLE], $searchwords);
				$score = $score + countOccurences($row[BREADCRUMB], $searchwords);
				$score = $score + countOccurences($row[META_SEOTITLE], $searchwords, 0.5);
				$score = $score + countOccurences($row[META_KEYWORDS], $searchwords, 0.5);
				$score = $score + countOccurences($row[META_DESCRIPTION], $searchwords, 0.5);

				// Then reduce to snippet
				$snippet = $row[SUBHEADING]." - ".$row[CONTENT];
				$snippet = returnResultSnippet($arr_content, $snippet, $searchwords);

				// Finally highlight!
				$row[HEADING] = searchengineHighlight($row[HEADING], $searchwords);
				$row[MENU_TITLE] = searchengineHighlight($row[MENU_TITLE], $searchwords);
				$snippet = searchengineHighlight($snippet, $searchwords);

				// And output!
				$link = $arr_content[baseurl]."/index.php?pageid=$row[ID]"; //&amp;searchterm=$searchwords
				if ($row[BOOK_ID]) {
					$link .= "&amp;bookid=$row[BOOK_ID]";
				}
				$html_one = "<p>";
				$html_one .= "<a class='soegeres' href='$link'>$row[HEADING]</a><br />";
				$html_one .= "$snippet";

				$html_one .= " <a class='generel' href='$link '>".cmsTranslate("SearchResViewPage")."</a>";
				$html_one .= "</p>";

				$result_arr[] = array($score, $html_one);
			}
			break;
		case "NEWS":
			$sql = "
			 select 
			 	NEWS.ID, NEWS.NEWS_DATE, NEWS.HEADING, NEWS.CONTENT, NEWS.NEWSFEED_ID, NEWSFEEDS.NAME as NEWSFEEDNAME
			 from 
			 	NEWS, NEWSFEEDS 
			 where 
			 	NEWS.NEWSFEED_ID = NEWSFEEDS.ID
			 	and NEWS.LANGUAGE='$langId' 
			 	and (NEWS.HEADING like '%$searchwords%' 
			 		or NEWS.SUBHEADING like '%$searchwords%' 
			 		or NEWSFEEDS.NAME like '%$searchwords%' 
			 		or NEWS.CONTENT like '%$searchwords%'
			 		or NEWS.HEADING like '%$entities_searchwords%' 
			 		or NEWS.SUBHEADING like '%$entities_searchwords%' 
			 		or NEWSFEEDS.NAME like '%$entities_searchwords%' 
			 		or NEWS.CONTENT like '%$entities_searchwords%') 
			 	and NEWS.DELETED='0' 
			 	and NEWS.UNFINISHED='0' 
			 	and NEWS.PUBLISHED='1' 
				and (NEWS.SITE_ID='$site_id' or NEWS.GLOBAL_STATUS='1')";
			$result = mysql_query($sql);
			$num_results = mysql_num_rows($result);   
			while ($row = mysql_fetch_array($result)){
				// First score it!
				$score = 0;
				$score = $score + countOccurences($row[HEADING], $searchwords, 2);
				$score = $score + countOccurences($row[SUBHEADING], $searchwords, 1.5);
				$score = $score + countOccurences($row[CONTENT], $searchwords);
				$score = $score + countOccurences($row[NEWSFEEDNAME], $searchwords);

				// Then reduce to snippet
				$snippet = $row[SUBHEADING]." - ".$row[CONTENT];
				$snippet = returnResultSnippet($arr_content, $snippet, $searchwords);

				// Finally highlight!
				$row[HEADING] = searchengineHighlight($row[HEADING], $searchwords);
				$row[NEWSFEEDNAME] = searchengineHighlight($row[NEWSFEEDNAME], $searchwords);
				$snippet = searchengineHighlight($snippet, $searchwords);

				// And output!
				$link = $arr_content[baseurl]."/index.php?mode=news&amp;newsid=$row[ID]&amp;feedid=$row[NEWSFEED_ID]";
				$html_one = "<p>";
				$html_one .= "<a class='soegeres' href='$link'>$row[HEADING]";
				$html_one .= "</a><br />";
				$html_one .= reverseDate($row[NEWS_DATE]);
				$html_one .= " ";
				$html_one .= cmsTranslate("NewsArchiveIn")." \"$row[NEWSFEEDNAME]\"<br />";
				$html_one .= $snippet;

				$html_one .= " <a class='generel' href='$link '>[".cmsTranslate("SearchResViewNews")."]</a>";
				$html_one .= "</p>";

				$result_arr[] = array($score, $html_one);
			}
			break;
		case "EVENTS":
			$sql = "
				select 
					EVENTS.ID, EVENTS.HEADING, EVENTS.SUBHEADING, EVENTS.CONTENT, 
					EVENTS.STARTDATE, EVENTS.ENDDATE,
					CALENDARS.ID as CALENDARID, CALENDARS.NAME as CALENDARNAME
				from EVENTS, CALENDARS 
				where 
					EVENTS.CALENDAR_ID = CALENDARS.ID
					and EVENTS.LANGUAGE='$langId' 
					and (EVENTS.HEADING like '%$searchwords%' 
						or EVENTS.SUBHEADING like '%$searchwords%' 
						or EVENTS.CONTENT like '%$searchwords%'
						or CALENDARS.NAME like '%$searchwords%'
						or EVENTS.HEADING like '%$entities_searchwords%' 
						or EVENTS.SUBHEADING like '%$entities_searchwords%' 
						or EVENTS.CONTENT like '%$entities_searchwords%'
						or CALENDARS.NAME like '%$entities_searchwords%')
					and EVENTS.DELETED='0' 
					and EVENTS.UNFINISHED='0' 
					and EVENTS.PUBLISHED='1'
				 	and (EVENTS.SITE_ID='$site_id' or EVENTS.GLOBAL_STATUS='1')";
			$result = mysql_query($sql);
			$num_results = mysql_num_rows($result);   
			while ($row = mysql_fetch_array($result)){
				// First score it!
				$score = 0;
				$score = $score + countOccurences($row[HEADING], $searchwords, 2);
				$score = $score + countOccurences($row[SUBHEADING], $searchwords, 1.5);
				$score = $score + countOccurences($row[CONTENT], $searchwords);
				$score = $score + countOccurences($row[CALENDARNAME], $searchwords);

				// Then reduce to snippet
				$snippet = $row[SUBHEADING]." - ".$row[CONTENT];
				$snippet = returnResultSnippet($arr_content, $snippet, $searchwords);

				// Finally highlight!
				$row[HEADING] = searchengineHighlight($row[HEADING], $searchwords);
				$row[CALENDARNAME] = searchengineHighlight($row[CALENDARNAME], $searchwords);
				$snippet = searchengineHighlight($snippet, $searchwords);

				// And output!
				$link = $arr_content[baseurl]."/index.php?mode=events&amp;calendar=$row[CALENDARID]&amp;eventid=$row[ID]";
				$html_one = "<p>";
				$html_one .= "<a class='soegeres' href='$link'>$row[HEADING]";
				$html_one .= "</a><br />";
				$html_one .= reverseDate($row[STARTDATE]);
				if ($row[ENDDATE] != "0000-00-00") {
					$html_one .= " - ";
					$html_one .= reverseDate($row[ENDDATE]);
				}			
				$html_one .= " ";
				$html_one .= cmsTranslate("CalendarIn")." \"$row[CALENDARNAME]\"<br />";
				$html_one .= $snippet;

				$html_one .= " <a class='generel' href='$link '>[".cmsTranslate("SearchResViewEvent")."]</a>";
				$html_one .= "</p>";

				$result_arr[] = array($score, $html_one);
			}
			break;
		case "PRODUCTS":
			$sql = "
			 select 
			 	SHOP_PRODUCTS.ID, 
			 	SHOP_PRODUCTGROUPS.NAME as GROUPNAME, 
			 	SHOP_PRODUCTGROUPS.ID as GROUP_ID,
			 	SHOP_PRODUCTS.PRODUCT_NUMBER, 
			 	SHOP_PRODUCTS.ALT_PRODUCT_NUMBER, 
			 	SHOP_PRODUCTS.NAME, SHOP_PRODUCTS.DESCRIPTION 
			 from
			 	SHOP_PRODUCTS, 
			 	SHOP_PRODUCTGROUPS,
			 	SHOP_PRODUCTS_GROUPS
			 where 
				SHOP_PRODUCTS.ID = SHOP_PRODUCTS_GROUPS.PRODUCT_ID
				and SHOP_PRODUCTGROUPS.ID = SHOP_PRODUCTS_GROUPS.GROUP_ID
				and SHOP_PRODUCTS.DELETED = '0' 
				and SHOP_PRODUCTGROUPS.PUBLISHED = '1' 
				and (SHOP_PRODUCTS.PRODUCT_NUMBER = '$searchwords' 
					or SHOP_PRODUCTS.ALT_PRODUCT_NUMBER = '$searchwords' 
					or SHOP_PRODUCTS.NAME like '%$searchwords%' 
					or SHOP_PRODUCTGROUPS.NAME like '%$searchwords%' 
					or SHOP_PRODUCTS.DESCRIPTION like '%$searchwords%'
					or SHOP_PRODUCTS.PRODUCT_NUMBER = '$entities_searchwords' 
					or SHOP_PRODUCTS.ALT_PRODUCT_NUMBER = '$entities_searchwords' 
					or SHOP_PRODUCTS.NAME like '%$entities_searchwords%' 
					or SHOP_PRODUCTGROUPS.NAME like '%$entities_searchwords%' 
					or SHOP_PRODUCTS.DESCRIPTION like '%$entities_searchwords%')
				and SHOP_PRODUCTGROUPS.SITE_ID = '$site_id'";
			$result = mysql_query($sql);
			$num_results = mysql_num_rows($result);   
			while ($row = mysql_fetch_array($result)){
				// First score it!
				$score = 0;
				$score = $score + countOccurences($row[PRODUCT_NUMBER], $searchwords, 10);
				$score = $score + countOccurences($row[ALT_PRODUCT_NUMBER], $searchwords, 10);
				$score = $score + countOccurences($row[NAME], $searchwords, 2);
				$score = $score + countOccurences($row[DESCRIPTION], $searchwords);
				$score = $score + countOccurences($row[GROUPNAME], $searchwords);

				// Then reduce to snippet
				$row[DESCRIPTION] = returnResultSnippet($row[DESCRIPTION], $searchwords);

				// Finally highlight!
				// But preserve product_number for use in link
				$productnumber = $row[PRODUCT_NUMBER];
				foreach ($row as $key => $value) {
					$row[$key] = searchengineHighlight($row[$key], $searchwords);
				}

				// And output!
				$link = $arr_content[baseurl]."/index.php?mode=shop&amp;action=showproduct&amp;group=$row[GROUP_ID]&amp;product=$productnumber";
				$html_one = "<p>";
				$html_one .= "<a class='soegeres' href='$link'>$row[NAME]";
				$html_one .= "</a><br />";
				$html_one .= cmsTranslate("shopProductNumber")." \"$row[PRODUCT_NUMBER]\" ";
				if ($row[ALT_PRODUCT_NUMBER] != "") {
					$html_one .= "($row[ALT_PRODUCT_NUMBER]) ";
				}
				$html_one .= cmsTranslate("shopInGroup")." \"$row[GROUPNAME]\"<br />";
				$html_one .= $row[DESCRIPTION];

				$html_one .= " <a class='generel' href='$link '>[".cmsTranslate("SearchResViewProd")."]</a>";
				$html_one .= "</p>";

				$result_arr[] = array($score, $html_one);
			}
			break;
		case "BLOGPOSTS":
			$sql = "
			 select 
			 	B.TITLE,
			 	BP.BLOG_ID,
			 	BP.ID,
			 	BP.HEADING,
			 	BP.CONTENT,
			 	BP.CONTENTSNIPPET,
			 	BP.PUBLISHED_DATE,
			 	BP.AUTHOR_ID
			 from
			 	BLOGS B,
			 	BLOGPOSTS BP
			 where 
				BP.BLOG_ID = B.ID
				and BP.PUBLISHED = 1
				and BP.UNFINISHED = 0
				and BP.DELETED = 0
				and B.PUBLISHED = 1
				and B.DELETED = 0
				and B.UNFINISHED = 0
				and B.SITE_ID in (0,$site_id)
				and (BP.HEADING like '%$searchwords%' 
					or BP.CONTENT like '%$searchwords%' 
					or BP.CONTENTSNIPPET like '%$searchwords%' 
					or BP.HEADING like '%$entities_searchwords%' 
					or BP.CONTENT like '%$entities_searchwords%' 
					or BP.CONTENTSNIPPET like '%$entities_searchwords%')";
			$result = mysql_query($sql);
			$num_results = mysql_num_rows($result);   
			while ($row = mysql_fetch_array($result)){
				if (check_data_permission("DATA_FE_BLOG_READ", "BLOGS", $row[BLOG_ID], "", $_SESSION[USERDETAILS][0][0], "loose")){
					// First score it!
					$score = 0;
					$score = $score + countOccurences($row[HEADING], $searchwords, 3);
					$score = $score + countOccurences($row[CONTENTSNIPPET], $searchwords, 2);
					$score = $score + countOccurences($row[CONTENT], $searchwords);
	
					// Then reduce to snippet
					if ($row[CONTENTSNIPPET] != "") {
						$snippet = $row[CONTENTSNIPPET];
					} else {
						$snippet = returnResultSnippet($row[CONTENT], $searchwords);
					}
	
					// Finally highlight!
					foreach ($row as $key => $value) {
						$row[$key] = searchengineHighlight($row[$key], $searchwords);
					}
	
					// And output!
					$link = $arr_content[baseurl]."/index.php?mode=blogs&amp;blogid=$row[BLOG_ID]&amp;postid=$row[ID]";
					$html_one = "<p>";
					$html_one .= "<a class='soegeres' href='$link'>$row[HEADING]";
					$html_one .= "</a><br />";
					$html_one .= $snippet;
					$html_one .= "<br/>".cmsTranslate("BlogsPostInBlog")." \"$row[TITLE]\"";
					$html_one .= "&nbsp;<a class='generel' href='$link '>[".cmsTranslate("SearchResViewBlogpost")."]</a>";
					$html_one .= "</p>";
	
					$result_arr[] = array($score, $html_one);
				}
			}
			break;
	}
	// sort results based on score
	if (is_array($result_arr)) {
		arsort($result_arr);
		foreach ($result_arr as $inner_arr) {
			$html .= $inner_arr[1];			
		}
	}
	return $html;
}

function countOccurences($haystack, $needle, $importance=1) {
	if ($haystack == "" || $needle == "") {
		return 0;
	}
	//  initiate lowercase instances of search text and needles
	$haystack_lower=unhtmlentities(strtolower(htmlentities($haystack)));
	$needle_lower=unhtmlentities(strtolower(htmlentities($needle)));
 	$count = substr_count($haystack_lower, $needle_lower) * $importance;
	return $count;
}

function searchengineHighlight($x, $var) {
	// htmlentities to count ÆØÅ
	$x=htmlentities($x);
	$var=htmlentities($var);

	//$x is the string, $var is the text to be highlighted 
	if ($var != "") { 
		$xtemp = ""; 
		$i=0; 
		while($i<strlen($x)){ 
			if((($i + strlen($var)) <= strlen($x)) && (strcasecmp($var, substr($x, $i, strlen($var))) == 0)) { 
				$xtemp .= "<span class='searchengineHighlight'>" . substr($x, $i , strlen($var)) . "</span>"; 
				$i += strlen($var); 
			} else {
				$xtemp .= $x{$i}; 
				$i++; 
			} 
		} 
		$x = $xtemp; 
	}

	// unhtmlentities
	// replace numeric entities
	$x = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $x);
	$x = preg_replace('~&#([0-9]+);~e', 'chr(\\1)', $x);
	// replace literal entities
	$trans_tbl = get_html_translation_table(HTML_ENTITIES);
	$trans_tbl = array_flip($trans_tbl);
	$x = strtr($x, $trans_tbl);
	return $x; 
}

function returnResultSnippet($arr_content, $text, $searchwords, $pos_arr=false, $start_search_at=0) {
	if ($text == "" || $searchwords == "") {
		return;
	}
	// Funktionen skal returnere 25 karakterer før og 150 karakterer efter hver occ af $searchwords
	// Hvis næste occ af $searchwords forekommer indenfor denne tekststump udvides tekststump til 
	// 150 karakterer efter denne, så tekst ikke optræder flere gange.
	$snippet_length = 150;
	$snippet_leadin = 25;

	// Make sure that any internal links point to the correct current domain
	$text = str_replace(returnBASE_URL($arr_content[site]), $arr_content[baseurl], $text);

	if ($arr_content[usemodrewrite] == 1) {
		$text = rewrite_links($text, $arr_content);
	}

	// Transform HTML links into plain-text "links" with the URL visible
	$text = eregi_replace('(<a [^<]*href=["|\']?([^ "\']*)["|\']?[^>]*>([^<]*)</a>)','[\\3] (link: \\2)', $text);
	$text = unhtmlentities($text);

	// Then strip tags
	$text = strip_tags($text);

	// Først bygges et array med placeringen af forekomster af søgeordet.
	if ($start = stripos($text, $searchwords, $start_search_at)) {
		$length = strlen($searchwords);
		$end = $start+$length;

		$pos_arr[] = $start;
		$found_pos = returnResultSnippet($arr_content, $text, $searchwords, $pos_arr, $end);
		if (is_array($pos_arr) && is_array($found_pos)) {
			$pos_arr = array_merge($pos_arr, $found_pos);
		} else {
			return $found_pos;
		}
	} else {
		// Her skal funktionen parse arrayet og sikre at der ikke er overlap mellem snippets. Derefter skal den returnere én samlet snippet!
		if (count($pos_arr) == 0) {
			// hvis der ikke er nogen forekomster, returner start af text!
			$snippet .= substr($text, 0, $snippet_length);
		} else if (count($pos_arr) == 1) {
			$sstart = $pos_arr[0] - $snippet_leadin;
			if ($sstart < 0) {
				$sstart = 0;
			}
			$snippet .= substr($text, $sstart, $snippet_length);
		} else {
			// loop fundne positioner
			for ($i = 0; $i <= count($pos_arr)-1; $i++) {
				if ($i > 0) {
					$end_of_prev_snippet = $pos_arr[$i-1]-$snippet_leadin+$snippet_length;
					$start_of_this_snippet = $pos_arr[$i]-$snippet_leadin;
					if ($end_of_prev_snippet > $start_of_this_snippet) {
						$start_of_this_snippet = $end_of_prev_snippet;
						$reduced_snippet_length = $snippet_length-($end_of_prev_snippet-$start_of_this_snippet);
						$snippet .= substr($text, $start_of_this_snippet, $reduced_snippet_length);
					} else {
						$snippet .= " ... ";
						$sstart = $pos_arr[$i] - $snippet_leadin;
						if ($sstart < 0) {
							$sstart = 0;
						}
						$snippet .= substr($text, $sstart, $snippet_length);
					}
				} else {
					$sstart = $pos_arr[$i] - $snippet_leadin;
					if ($sstart < 0) {
						$sstart = 0;
					}
					$snippet .= substr($text, $sstart, $snippet_length);
				}
			}
		}
		// Endelig skal der fjernes "halve ord" i start + slut samt tilføjes [...]
		if (stripos($snippet,$searchwords) > 0) {
			// Snippet starter ikke med searchword, så trim start
			$snippet = strstr($snippet, " ");
		}
		// Fjern sidste ord da det kan være halvt
		$lastspacepos = strrpos($snippet," ");
		$snippet = substr($snippet, 0, $lastspacepos);
		
		// Begræns snippet til X antal ord
		$antal_ord = 50;
		$snippet = implode(" ", array_slice(explode(" ", $snippet),0,$antal_ord+1)); 
		$snippet .= " ...";
		return $snippet;
	}
}



 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: LOGIN OF USERS
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function returnDistinctUserPermissionsFE($userid, $arr_content){
// 2007-04-18	-	Added $arr_content for site separation
// RETURN ALL DISTINCT PERMISSIONS FOR USER $userid. THESE PERMISSIONS ARE THE ACCUMULATED PERMISSIONS
// FROM _ALL_ GROUPS
	$sql = "
		select distinct
			PERMISSIONS.NAME AS NAME
		from 
			USERS_GROUPS, GROUPS_PERMISSIONS, PERMISSIONS, GROUPS
		where
			USERS_GROUPS.GROUP_ID=GROUPS.ID and
			USERS_GROUPS.GROUP_ID=GROUPS_PERMISSIONS.GROUPS_ID and
			GROUPS_PERMISSIONS.PERMISSIONS_ID = PERMISSIONS.ID and 
			USERS_GROUPS.USER_ID='$userid' and
			GROUPS.SITE_ID in (0,'$arr_content[site]')
		order by
			PERMISSIONS.NAME asc
	";
	$result = mysql_query($sql);
	while($row = mysql_fetch_array($result)){
 		$userPermissions[] = $row[NAME];
	}
	return $userPermissions;
} 

function checkPermissionFE($permission_str, $permissionsArray, $terminate_bol=false) {
	// Takes permission NAME as string and checkes it against session variable.
	// Returns TRUE if user has permission (or permission_str is empty). 
	//	If user doesn't have permission the function returns 
	// FALSE except if $terminate_bol is TRUE in which case the function outputs an error message and
	// terminates php script.
	if ($permission_str == "") {
		return true;
	}
	if (!is_array($permissionsArray)) {
		return false;
	}
	if (in_array($permission_str, $permissionsArray)) {
		return true;
	} else {
		if ($terminate_bol) {
			$message = "Du er logget ind, men er ikke tildelt rettigheder til at benytte denne funktion. Kontakt os venligst, hvis det er en fejl. <a href='index.php'>Tilbage til forsiden</a>";
			echo "<div class='usermessage_error'>$message</div>";
			exit;
		} else {
			return false;
		}
	}
 }
 

function loginUser($username, $password, $arr_content){
// 2007-04-18	-	Added $arr_content for site separation
 	$sql = "
		select 
    		ID, FIRSTNAME, LASTNAME, ADDRESS, ZIPCODE, CITY, PHONE, CELLPHONE, EMAIL, CV, RECEIVE_LETTERS
   		from 
			USERS 
		where 
    		DELETED='0' and UNFINISHED='0' and USERNAME='$username' and PASSWORD='$password' 
		limit 1
  	";
  	$result = mysql_query($sql) or die(__FUNCTION__." ".mysql_error());
 	if (mysql_num_rows($result) != 1){
		return false;
  	} else {
		$user_row = mysql_fetch_array($result);
		// Get usergroups for this site (or shared) to which the user belongs
  		$sql = "select UG.GROUP_ID, G.LOGIN_TO_URL from USERS_GROUPS UG, GROUPS G where UG.GROUP_ID = G.ID and UG.USER_ID='$user_row[ID]' and G.SITE_ID in (0,'$arr_content[site]')"; 
  		$result = mysql_query($sql) or die(__FUNCTION__." ".mysql_error());
  		while ($groups_row = mysql_fetch_assoc($result)){
   			$user_groups[] = $groups_row[GROUP_ID];
			if ($groups_row[LOGIN_TO_URL] != "") {
				$login_to_url = $groups_row[LOGIN_TO_URL];
			}
  		}
		$user_rights = returnDistinctUserPermissionsFE($user_row[ID], $arr_content);
//		print_r($user_rights);
  		return array($user_row, $user_groups, $user_rights, $login_to_url);
	}
 }

 function loginPage($arr_content) {
	// Where to go after login?
	if (check_method($arr_content["methods"], "redirect") == "referer") {
		// Go to refering page
		$ref = $_SERVER["HTTP_REFERER"];
		// Not allowed to go back to loginfailure page
		if (strstr($ref, "mode=loginfailure") || $arr_content[mode]=="loginfailure") {
			$ref="";
		}
	} elseif ($arr_content[mode]=="login") {
		// Go to baseurl or location defined in database
		$ref = "";
	} else {
		// Go to current page (protected)
		$ref = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	}
	if ($_SESSION["LOGGED_IN"]) {
  		$html .= "
			<script type='text/javascript'>
		
				function doLogout()
				{
				 if (confirm('".cmsTranslate("login_wanna")."')){
				  document.controlForm.dothis.value = 'logout';
				  document.controlForm.submit();
				 }
				}
			</script>
		";
        $html .= "<div class='fieldHeader'>".cmsTranslate("login_alreadyloggedin")."</div><p><form><input type='button' value='".cmsTranslate("login_clicktologout")."' onclick='location=\"/index.php?dothis=logout\"' /></form>";
	} else {	
		$html = "
		  <h1>".cmsTranslate("login_login")."</h1>
		  <div>".cmsTranslate("login_textabove")."</div>
			<script type='text/javascript'>
				function KeyDownHandler(e,btn)	{  
					// process only the Enter key  
					if(e && e.which){   
						characterCode = e.which  
					} else {  
						characterCode = e.keyCode;  
					}	  
					if (characterCode == 13)	{  
						// cancel the default submit  
						e.returnValue=false;  
						e.cancel = true;  
						// submit the form by programmatically clicking the specified button  
						btn.click();  
					}  
				}
				function doLogin()
					{
					L_FORM = document.loginForm;
					if (L_FORM.username.value == '' || L_FORM.password.value == '') {
					alert('".cmsTranslate("login_filloutboth")."');
					return;
					}
					L_FORM.dothis.value = 'login';
					L_FORM.submit();
					}
			</script>
		  <form name='loginForm' id='loginForm' method='post' onkeypress='KeyDownHandler(event,this.loginsubmitbutton)' action='$arr_content[baseurl]' >
		   <input type='hidden' name='ref' value='$ref' />
		   <input type='hidden' name='dothis' value='' />
		   <div class='generatedFormFieldHeader'>".cmsTranslate("login_username").":</div>
		   <div class='generatedFormFieldContainer'>
			   <input class='generatedFormField' type='text' name='username' size='40' />
		   </div>
		   <div class='generatedFormFieldHeader'>".cmsTranslate("login_password").":</div>
		   <div class='generatedFormFieldContainer'>
				<input class='generatedFormField' type='password' name='password' size='40' />
		   </div>
		  <div class='generatedFormButtonBar'>
			<input type='button' id='loginsubmitbutton' value='".cmsTranslate("login_login")."' onclick='doLogin()' />
		  </div>
		  </form>
		  ";
	}
	return $html;
} 
 
function loginFailure() {
	$html = "<h1 class='heading'>".cmsTranslate("login_failure")."</h1>".cmsTranslate("login_failuretext")."";
	return $html;
}

 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: NEWS ARCHIVE
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function newsBox($feed, $arr_content, $newsdate="after") {
// 	2007-04-16	-	site-seperation not added in this function since $feed is site-specific
//					this function should only be called as a template function
	// Third argument sets position of date before/after newsitem. Use "none" for no date.
	$pageid = $arr_content[pageid];
  if (getBoxState("NEWS", $pageid) == 0) {
   return "";
  }
  $LIGE_NU = date("Y-m-d");
  $sql = "
   select 
    ID, HEADING, NEWS_DATE
   from 
    NEWS 
   where
    UNFINISHED='0' and DELETED='0' and PUBLISHED='1' and FRONTPAGE_STATUS='1' and 
	(LIMITED='0' or (LIMITED='1' and '$LIGE_NU' >= LIMIT_START and '$LIGE_NU' <= LIMIT_END))
	and (NEWSFEED_ID='$feed' or (NEWSFEED_ID>0 and GLOBAL_STATUS='1'))
   order by
    NEWS_DATE desc
  ";
  $result = mysql_query($sql);
	if (mysql_num_rows($result) == 0) {
		return false;
	}
  if (file_exists(return_feed_savepath().return_feed_filename("NEWSFEEDS", $feed))){
	$feedlink .= "<a title='".returnNewsfeedTitle($arr_content, $feed).": RSS feed' href='".return_feed_url("NEWSFEEDS", $feed)."'><img alt='".returnNewsfeedTitle($arr_content, $feed).": RSS feed' src='/includes/images/rssfeedicon.gif' align='absmiddle'/></a>";
  } 
  $html  = "<div class='marginBox'>";
  $html .= "<div class='boxTitle'>".returnNewsfeedTitle($arr_content, $feed)." &nbsp;$feedlink</div>";
  if (mysql_num_rows($result)>0) {	
   while ($row = mysql_fetch_array($result)) {
    $temp1 = explode("-", $row[NEWS_DATE]);
	if ($newsdate == "before") {
		$html .= "<div class='newsdate'>" . $temp1[2] . ". " . cmsTranslate("MonthsShorthand", 1*$temp1[1]) . ". " . $temp1[0] . "</div>";
	}
	$html .= "<p>";
    $html .= "<a title='$row[HEADING]' href='$arr_content[baseurl]/index.php?mode=news&amp;feedid=$feed&amp;newsid=$row[ID]'>$row[HEADING]</a><br/>";
	$html .= "</p>";
	if ($newsdate == "after") {
		$html .= "<div class='newsdate'>" . $temp1[2] . ". " . cmsTranslate("MonthsShorthand", 1*$temp1[1]) . ". " . $temp1[0] . "</div>";
	}
   }
  }
  $html .= "
  	<p class='boxComment'>
		<a title='".cmsTranslate("NewsFullArchive")."' href='$arr_content[baseurl]/index.php?mode=news&amp;feedid=$feed&amp;offset=0'>".cmsTranslate("NewsFullArchive")."</a>
	</p>
  ";
  $html .= "</div>";
  return $html;
 }
  
function returnNewsfeedTitle($arr_content, $feed_id = ""){
// 2007-04-16	-	Now takes $arr_content as input instead of feed_id (site-separation)	
// 2007-04-17	-	The optional $feed_id overrides $arr_content[feedid]

if ($feed_id == "") {
	$feed_id = $arr_content[feedid];
}
if ($feed_id == "") {
	return "";
}
 					
  $sql = "select NAME from NEWSFEEDS where ID='$feed_id'";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  return $row[NAME];
 } 

 function returnFeedIdFromNewsId($newsId){
  $sql = "select NEWSFEED_ID from NEWS where ID='$newsId'";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  return $row[NEWSFEED_ID];
 }  
  
function newsArchive($arr_content){
	global $newsarchive_newsPerPage;
	$newsarchive_newsPerPage;
	$offset = (check_method($arr_content["methods"], "offset") ? check_method($arr_content["methods"], "offset") : 0);
	$nu = date("Y-m-d");
	$sql = "
		select 
			ID, NEWS_DATE, HEADING 
		from 
			NEWS 
		where 
			UNFINISHED='0' and 
			DELETED='0' and 
			PUBLISHED='1' and
			(LIMITED='0' or (LIMITED='1' and '$nu' >= LIMIT_START and '$nu' <= LIMIT_END)) and 
			((NEWSFEED_ID='$arr_content[feedid]' and SITE_ID in (0,'$arr_content[site]')) or GLOBAL_STATUS = '1') 
		order by 
			NEWS_DATE desc, ID desc limit $offset, $newsarchive_newsPerPage
	";
	$result = mysql_query($sql);
	$html .= "<h1>".returnNewsfeedTitle($arr_content)."</h1>";
	$numrows = mysql_num_rows($result);
	if ($numrows == 0){
		$html .= "<p>".cmsTranslate("NewsArchiveEmpty")."</p>";
	} else {
   		$maaned = "";
   		$aar = "";
   		while ($row = mysql_fetch_array($result)){
    		$maaned = substr($row[NEWS_DATE],5,2);
    		$aar = substr($row[NEWS_DATE],0,4);
    		if ($maaned != $old_maaned || $aar != $old_aar){
     			$html .= "<p class='month_seperator'>" . cmsTranslate("MonthsUpper", $maaned*1) . " " . $aar ."</p>";
    		}  
    		$html .= "
				<p class='newsArchiveNewsLine'>
					<a title='$row[HEADING]' class='nyhed' href='$arr_content[baseurl]/index.php?mode=news&amp;feedid=$arr_content[feedid]&amp;newsid=$row[ID]'>$row[HEADING]</a> (" . returnNiceArchiveDate(UKtimeToUNIXtime($row[NEWS_DATE]),0) . ")
				</p>
			";
    		$old_maaned = $maaned;
    		$old_aar = $aar;	
   		}
  	}
	$html .= "<div class='newsArchiveOffset'>";
	$html .= ($numrows >= $newsarchive_newsPerPage ? "<a href='$arr_content[baseurl]/index.php?mode=news&amp;feedid=$arr_content[feedid]&amp;offset=".($offset+$newsarchive_newsPerPage)."'>".cmsTranslate("NewsArchivePrevPage")."</a>" : "");
	$html .= ($offset > 0 ? "<a href='$arr_content[baseurl]/index.php?mode=news&amp;feedid=$arr_content[feedid]&amp;offset=".($offset-$newsarchive_newsPerPage)."'>".cmsTranslate("NewsArchiveNextPage")."</a>" : "");
	$html .= "</div>";
	return $html; 
}

 function displayNews($arr_content) {
  $row = hentRow($arr_content[newsid], "NEWS");

	// Site must match or the newsitem must be global (site = 0 or global_status = 1)
	if ($row[SITE_ID] != $arr_content[site] && $row[SITE_ID] != 0 && $row[GLOBAL_STATUS] == 0) {
		return false;
	}

  $related_content = relatedBox($arr_content[baseurl], $arr_content[newsid], "NEWS");
  $attached_files = fileBox($arr_content[newsid], "NEWS", $arr_content);  
  $feedId = returnFeedIdFromNewsId($arr_content[newsid]);
  $html = "<h1 class='heading'>".$row[HEADING]."</h1><p class='newsdate'>".
   returnNiceArchiveDate(UKtimeToUNIXtime($row[NEWS_DATE]),0) . "
  </p>" . ($row[SUBHEADING]!="" ? "
  <p class='subheading'>
   $row[SUBHEADING]
  </p>" : "") . " 
  <div id='content_block'>
   $row[CONTENT]
   $attached_files
   $related_content
   <div id='newsBackToArchive'>
    <a href='$arr_content[baseurl]/index.php?mode=news&amp;feedid=$feedId'>&laquo;&nbsp;".cmsTranslate("BackTo").returnNewsfeedTitle($arr_content)."</a>
   </div>   
  </div>  
  ";
  $html .= lastEdited($row[CHANGED_DATE], $row[AUTHOR_ID], $arr_content);
  return $html;
 }
 
 function newsHeading($id) {
  $sql = "select HEADING from NEWS where UNFINISHED='0' and DELETED='0' and PUBLISHED='1' and ID='$id'";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  return $row[0];
 } 

 function returnNiceArchiveDate($unixtime, $medklokkeslet) {
  $ind = date("Y-m-d H:i:s", $unixtime);
  $dato_ind = substr($ind, 0, 10);
  $tid = substr($ind, -8, 5);
  if ($dato_ind == "0000-00-00") {
   return "Ikke angivet";
  }
  $temp = explode("-", $dato_ind);
  $danish_months = array(1 => "januar", "februar", "marts", "april", "maj", "juni", "juli", "august", "september", "oktober", "november", "december");
  $dato_ud = 1*$temp[2] . ". " . cmsTranslate("MonthsLower", 1*$temp[1]) . " " . $temp[0];
  if ($medklokkeslet == 1) {
   return $dato_ud . " " . $tid;
  } else {
   return $dato_ud;
  }
 } 
      
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: CALENDAR ARCHIVE (EVENTS)
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

 function calendarBox($feed, $arr_content) {
 	global $calendarBoxCount;
// 	2007-04-16	-	site-seperation not added in this function since $feed is site-specific
//					this function should only be called as a template function
 $pageid = $arr_content[pageid];
  if (getBoxState("EVENTS", $pageid) == 0) {
   return "";
  }
  $nu = date("Y-m-d");
  $sql = "
   select 
    ID, HEADING, DURATION, STARTDATE, ENDDATE 
   from 
    EVENTS 
   where
    UNFINISHED='0' and 
    DELETED='0' and 
    PUBLISHED='1' and 
    (CALENDAR_ID='$feed' or (CALENDAR_ID>0 and GLOBAL_STATUS='1'))
	and (STARTDATE>='$nu' or ENDDATE>='$nu')
   order by
    STARTDATE asc limit ".($calendarBoxCount ? $calendarBoxCount : "2")."
  ";
  $result = mysql_query($sql);
  $html  = "<div class='marginBox'>";
  $html .= "<div class='boxTitle'>".returnCalendarTitle($feed)."</div>";
  if (mysql_num_rows($result)==0) {
   $html .= "<p>".cmsTranslate("CalendarEmptyBox")."</p>";
  }
  else { 
   while ($row = mysql_fetch_array($result)) {
    $temp1 = explode("-", $row[STARTDATE]);
    $temp2 = explode("-", $row[ENDDATE]);
	$html .= "<p>";
    $html .= "<a title='".reverseDate($row[STARTDATE]).": $row[HEADING]' href='$arr_content[baseurl]/index.php?mode=events&amp;calendarid=$feed&amp;eventid=$row[ID]'>$row[HEADING]</a><br/>";
    if ($row["DURATION"]==0) {
	 $html .= ($temp1[2] . ". " . cmsTranslate("MonthsShorthand", 1*$temp1[1]) . ".") . " " . $temp1[0];
	}
	if ($row["DURATION"]==1) {
	 $html .= ($temp1[2] . ". " . cmsTranslate("MonthsShorthand", 1*$temp1[1]) . "."); 
	 $html .= "<span class='til'>&nbsp;&mdash;&nbsp;</span>";
	 $html .= ($temp2[2] . ". " . cmsTranslate("MonthsShorthand", 1*$temp2[1]) . ".");
	}
	$html .= "</p>";	
   }
  }
  $html .= "
  	<p class='boxComment'>
		<a title='".cmsTranslate("CalendarFullArchive")."' href='$arr_content[baseurl]/index.php?mode=events&amp;calendarid=$feed&amp;offset=0&amp;clearcals=1'>".cmsTranslate("CalendarFullArchive")."</a>
	</p>
  ";
  $html .= "</div>";
  return $html;
 }

 function eventHeading($id) {
  $sql = "select HEADING from EVENTS where UNFINISHED='0' and DELETED='0' and PUBLISHED='1' and ID='$id'";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  return $row[0];
 } 

 function returnCalendarTitle($calendarId){
  $sql = "select NAME from CALENDARS where ID='$calendarId'";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  return $row[NAME];
 } 

 function returnCalendarIdFromEventId($eventId){
  $sql = "select CALENDAR_ID from EVENTS where ID='$eventId'";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  return $row[CALENDAR_ID];
 }   
  
 function calendar($arr_content) {
  $nu = date("Y-m-d");
  if (!$arr_content[offset]) {
   $offset=0;
  }
  if ($arr_content[calendarid]){
    $selectedCalsIds[] = $arr_content[calendarid];
  	$selectedCalsTitles[] = returnCalendarTitle($arr_content[calendarid]);
  }
  foreach ($_POST as $k=>$v){
	if (strstr($k, "calendarBox_")){
		$temp = explode("_", $k);
		$selectedCalsIds[] = $temp[1];
		$selectedCalsTitles[] = returnCalendarTitle($temp[1]);
	}
  }
	if (!is_array($selectedCalsIds)) {
    	return;
    }

  $numberOfSelectedCals = count($selectedCalsIds);
  foreach ($selectedCalsIds as $calId){
  	$sqlOrs[] = "CALENDAR_ID='$calId'";
  }
  $sqlOrStr = implode(" OR ", $sqlOrs);
  $temp = explode("-", $_POST[monthSelector]);
  $endOfSelectedMonth = date("Y-m-d", mktime(0,0,0,1*$temp[1],1*$temp[2]+date("t", mktime(0,0,0,1*$temp[1],1,1*$temp[0]))-1,1*$temp[0]));
  $sql .= "select ID, HEADING, DURATION, STARTDATE, ENDDATE, CALENDAR_ID from EVENTS where UNFINISHED='0' and SITE_ID in (0,$arr_content[site]) and DELETED='0' and PUBLISHED='1' and ($sqlOrStr)";
  if ($_POST[monthSelector] == "ALL_MONTHS" || !$_POST[monthSelector]){
  	$sql .= " and (STARTDATE>='$nu' or ENDDATE>='$nu') ";
  } else {
  	$sql .= " and (STARTDATE>='$_POST[monthSelector]' and STARTDATE<='$endOfSelectedMonth') ";
  }
  $sql .= "order by STARTDATE asc, ENDDATE asc, ID asc limit $offset,100";
  $result = mysql_query($sql);
  $html .= "<div id='calendarContainer'>";
  $html .= "<h1 class='heading'>".implode(", ", $selectedCalsTitles)."</h1>";
  if (mysql_num_rows($result)==0) {
   $html .= "<p>".cmsTranslate("CalendarEmpty")."</p>";
  } 
  else {
   $maaned = "";
   $aar = "";
   while ($row = mysql_fetch_array($result)) {
    $maaned = substr($row[STARTDATE],5,2);
    $aar = substr($row[STARTDATE],0,4);
    if ($maaned != $old_maaned || $aar != $old_aar) {
     $html .= "
	 <p class='month_seperator'>" . cmsTranslate("MonthsUpper", (int)$maaned) . " " . $aar ."</p>";
    }  
    $html .= "<p class='calendarEventLine'>&raquo;&nbsp;<a title='".returnNiceArchiveDate(UKtimeToUNIXtime($row[STARTDATE]),0).": $row[HEADING]"."' class='nyhed' href='$arr_content[baseurl]/index.php?mode=events&amp;calendarid=$row[CALENDAR_ID]&amp;eventid=$row[ID]'>" . returnNiceArchiveDate(UKtimeToUNIXtime($row[STARTDATE]),0) . ": $row[HEADING]</a>";
	if ($numberOfSelectedCals > 1) {
		$html .= "&nbsp;(".returnCalendarTitle($row[CALENDAR_ID]).")";
	}
	$html .= "</p>";
    $old_maaned = $maaned;
    $old_aar = $aar;	
   }
  }
  $html .= multipleCalendarSelector($arr_content);
  $html .= "</div>";
  return $html; 
 } 

 function multipleCalendarSelector($arr_content){
 	$html .= "<div id='multipleCalendarSelector'>";
	$sql = "select ID, NAME from CALENDARS where SITE_ID in (0,'$arr_content[site]')";
	$result = mysql_query($sql);
	$html .= "<form name='multipleCalendarForm' method='post' action='$arr_content[baseurl]/index.php?mode=events'>";
	$html .= "<input name='shiftCals' type='hidden' value='shiftCals' />";
  	$danish_months = array(1 => "Januar", "Februar", "Marts", "April", "Maj", "Juni", "Juli", "August", "September", "Oktober", "November", "December");
  	for($i=0; $i<12; $i++){
   		$monthselector .= "<option " .($_POST[monthSelector]==date("Y-m-d",mktime(0,0,0,date("m")+$i,1,date("y"))) ? "selected" : "")." value='".date("Y-m-d",mktime(0,0,0,date("m")+$i,1,date("y")))."'>".cmsTranslate("MonthsUpper", 1*date("m",mktime(0,0,0,date("m")+$i,1,date("y")))). " " . date("Y",mktime(0,0,0,date("m")+$i,1,date("y")))."</option>";
  	}
	$html .= "<h2>".cmsTranslate("CalendarSelectTimespan")."</h2>";
	$html .= "<select onchange='if (shiftCalendars()) this.form.submit()' name='monthSelector'><option value='ALL_MONTHS'>".cmsTranslate("CalendarAllMonths")."</option>$monthselector</select>";
	$html .= "<h2>".cmsTranslate("CalendarMultiple")."</h2>";
	while ($row = mysql_fetch_array($result)){
		$checkedStr = "";
		if ($_POST["calendarBox_".$row[ID]]){
			$checkedStr = "checked='checked'";
		}
		if ($row[ID] == $arr_content[calendarid]){
			$checkedStr = "checked='checked'";
		}
		$html .= "<input type='checkbox' $checkedStr name='calendarBox_$row[ID]' id='calendarBox_$row[ID]' value='$row[ID]' onclick='if (shiftCalendars()) this.form.submit(); else alert(\"".cmsTranslate("CalendarSelectOne")."\")' />$row[NAME]&nbsp;&nbsp;";
	}
	$html .= "</form>";
	$html .= "</div>";
	return $html;
 }
 
function displayEvent($arr_content) {
// 2007-04-16	-	Implemented site-check
  $row = hentRow($arr_content[eventid], "EVENTS");

	// Site must match or the event must be global (site = 0 or global_status = 1)
	if ($row[SITE_ID] != $arr_content[site] && $row[SITE_ID] != 0 && $row[GLOBAL_STATUS] == 0) {
		return false;
	}

  $related_content = relatedBox($arr_content[baseurl], $arr_content[eventid], "EVENTS");  
  $attached_files = fileBox($arr_content[eventid], "EVENTS", $arr_content);  
  $calendarId = returnCalendarIdFromEventId($arr_content[eventid]);
  $html = "<h1 class='heading'>".$row[HEADING]."</h1><p class='newsdate'>".
   returnNiceArchiveDate(UKtimeToUNIXtime($row[STARTDATE]),0) . ($row[DURATION]==1 ? " &mdash; " . returnNiceArchiveDate(UKtimeToUNIXtime($row[ENDDATE]),0) : "") . ", $row[TIMEOFDAY] 
  </p>" . ($row[SUBHEADING]!="" ? "
  <p class='subheading'>
   $row[SUBHEADING]
  </p>" : "") . " 
  <div id='content_block'>
   $row[CONTENT]
	$attached_files      
	$related_content   
   <p>
    <a href='$arr_content[baseurl]/index.php?mode=events&amp;calendarid=$calendarId'>".cmsTranslate("BackTo").returnCalendarTitle($calendarId)."</a>
   </p>      
  </div>  
  ";
  $html .= lastEdited($row[CHANGED_DATE], $row[AUTHOR_ID], $arr_content);
  return $html;
 } 
   
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: DATABASE
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 
 function connect_to_db() {
	global $db_user, $db_pass, $db_host, $db_name;
	// Connect to the database-server:
	if (!($db=mysql_connect($db_host,$db_user, $db_pass))) {
		echo "Error connecting to database!";
		exit();
	}
	if (!(mysql_select_db($db_name,$db))) {
		echo "Error selecting database!";
		exit();
	}
	mysql_query("SET NAMES utf8");
	mysql_query("SET CHARACTER_SET utf8");
 } 

 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: HTML SNIPPETS
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 
 function page404() {
  $html .= "<h1 class='heading error_404'>".cmsTranslate("PageNotFoundHeading")."</h1>";
  $html .= "<p>".cmsTranslate("PageNotFoundText")."</p>";  
  return $html;
 }

 
 
 
 function mustBeLoggedIn($arr_content) {
/*  $html = "
  <h1 class='heading'>".cmsTranslate("login_failure")."</h1>
  ".cmsTranslate("login_mustbeloggedin")."
  ";
  return $html; */
  if ($_SESSION[LOGGED_IN]){
      echo "<h1>".cmsTranslate("login_limitedaccess")."</h1>";
      echo "<p>".cmsTranslate("login_limitedaccesstext")."</p>";
  } else {
      return loginPage($arr_content);
  }
 }

 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: INLINE BOX CREATION / OUTPUTTING
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

 function lastEdited($changed_date, $edit_author_id, $arr_content){
  if (getBoxState("LASTEDITED", $arr_content[pageid]) == 0) return "";
  $html .= "<div id='last_edited'>";
  $html .= cmsTranslate("LastEdited") . "&nbsp;" . returnNiceArchiveDate($changed_date, 1) . "&nbsp;" . cmsTranslate("EditedBy") . "&nbsp;" . returnAuthorName($edit_author_id, 0);
  $html .= "</div>";
  return $html;
 }
 
 function relatedBox($baseurl, $content_id, $type) {
  $sql = "select REL_ID, REL_TABEL from RELATED_CONTENT where SRC_ID='$content_id' and SRC_TABEL='$type' and REL_TABEL!='CUSTOM_BOXES'";
  $result = mysql_query($sql);
  $html = "<div class='inlineBox'>";
  $html .= "<div class='inlineBoxHeader'>".cmsTranslate("Related")."</div>";
  $html .= "<div class='inlineBoxContent'>";
  while ($row = mysql_fetch_array($result)) {
   if (returnPageTitle($row[REL_ID]) || returnNewsTitle($row[REL_ID]) || returnEventTitle($row[REL_ID])) {
    if ($row[REL_TABEL]=="PAGES") {
	 $html .= "&raquo;&nbsp;<a href='$baseurl/index.php?pageid=$row[REL_ID]'>".returnPageTitle($row[REL_ID])."</a><br/>";
	}
    if ($row[REL_TABEL]=="NEWS") {
	 $html .= "&raquo;&nbsp;<a href='$baseurl/index.php?mode=news&newsid=$row[REL_ID]'>".returnNewsTitle($row[REL_ID])."</a><br/>";
	}
    if ($row[REL_TABEL]=="EVENTS") {
	 $html .= "&raquo;&nbsp;<a href='$baseurl/index.php?mode=events&eventid=$row[REL_ID]'>".returnEventTitle($row[REL_ID])."</a><br/>";
	}
   }
  }
  $html .= "</div></div>";
  if (mysql_num_rows($result)==0) $html="";
  return $html;
 } 

 function fileBox($content_id, $type, $arr_content) {
  $sql = "select FILE_ID from ATTACHMENTS where PAGE_ID='$content_id' and TABEL='$type'";
  $result = mysql_query($sql);
  $html = "<div class='inlineBox'>";
  $html .= "<div class='inlineBoxHeader'>".cmsTranslate("DownloadFiles")."</div>";
  $html .= "<div class='inlineBoxContent'>";
  while ($row = mysql_fetch_array($result)) {
   $temp = returnFileTitle($row[FILE_ID]);
   $html .= "&raquo;&nbsp;<a href='".$arr_content[baseurl]."/includes/download.php?id=$row[FILE_ID]' onclick='' alt='$temp[1] ($temp[2])' title='$temp[1] ($temp[2])'>".$temp[0]."</a><br/>";
  }
  $html .= "</div></div>";
  if (mysql_num_rows($result)==0) $html="";
  return $html;
 }
  
 function formularBox($baseurl, $content_id, $type, $arr_content) {
  $sql = "select FORM_ID, INLINE from PAGES_FORMS where PAGE_ID='$content_id'";
  $result = mysql_query($sql);
  $row = mysql_fetch_assoc($result);
  $inline = $row[INLINE];
  $sql = "select ID, FORM_OPENDATE, FORM_CLOSEDATE, LINKTEXT from DEFINED_FORMS where ID='$row[FORM_ID]'";
  $result = mysql_query($sql);
  $html = "<div class='inlineBox'>";
  $html .= "<div class='inlineBoxHeader'>".cmsTranslate("Forms")."</div>";
  $html .= "<div class='inlineBoxContent'>";
  $row = mysql_fetch_array($result);
  $temp1 = explode("-", $row[FORM_OPENDATE]);
  $t1 = mktime(23,59,59,1*$temp1[1], 1*$temp1[2], 1*$temp1[0]);
  $temp2 = explode("-", $row[FORM_CLOSEDATE]);
  $t2 = mktime(23,59,59,1*$temp2[1], 1*$temp2[2], 1*$temp2[0]);
  if (($row[FORM_OPENDATE] == "0000-00-00" && $row[FORM_CLOSEDATE] == "0000-00-00") || (time() >= $t1 && time() <= $t2)) {
   $okay_to_show = true;
   $html .= "&raquo;&nbsp;<a href='$baseurl/index.php?mode=formware&amp;formid=$row[0]'><strong>$row[LINKTEXT]</strong></a><br/>";
  } else {   
   $okay_to_show = false;
   if (time() < $t1) $html .= "&raquo;&nbsp;" . cmsTranslate("RegistrationNotOpenYet") . returnNiceArchiveDate($t1, 2). "<br/>";
   if (time() > $t2) $html .= "&raquo;&nbsp;" . cmsTranslate("RegistrationClosed") . returnNiceArchiveDate($t2, 2). "<br/>";
  }
  $html .= "</div></div>";
  if (mysql_num_rows($result)==0) $html="";
  if ($inline == 1){
  	$arr_content[formid] = $row[0];
  	$html = formGenerator($arr_content);
  }
  return $html;
 }
 
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: RIGHT-MARGIN BOX CREATION / OUTPUTTING
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function getBoxState($boxname, $pageid) {
	if ($pageid == "" || $pageid == 0) {
		$pageid = getFrontpageId($_SESSION["CURRENT_LANGUAGE"], $_SESSION["CURRENT_SITE"]);
	}
	$sql = "select $boxname from BOX_SETTINGS where PAGE_ID='$pageid'";
	$result = mysql_query($sql);
	$row = mysql_fetch_row($result);
	return $row[0];
}
  
 function stfBox($arr_content){
	$pageid = $arr_content[pageid];
 	if (getBoxState("STF", $pageid) == 0) {
		return "";
  	}
	$html .= "<div class='marginBox'>";
	$html .= "<div class='boxTitle'>".cmsTranslate("rtf_boxheading")."</div>";
	$html .= "<p><a href='$arr_content[baseurl]/index.php?mode=stf'>".cmsTranslate("rtf_boxtext")."</a></p>";
	$html .= "</div>";
  return $html;
 } 

 function customBoxes($arr_content){
	$pageid = $arr_content[pageid];
	/*
	$sql = "select CUSTOM from BOX_SETTINGS where PAGE_ID='$_GET[pageid]'";
	$result = mysql_query($sql);
	$row = mysql_fetch_row($result);
	$row[0] = substr(str_replace("__", "_", $row[0]), 0, -1);
	$row[0] = substr($row[0], 1, strlen($row[0]));
	$temp = explode("_", $row[0]);
	*/
	$sql = "select REL_ID from RELATED_CONTENT where REL_TABEL='CUSTOM_BOXES' and SRC_TABEL='PAGES' and SRC_ID='$pageid'";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result)){
		$temp[] = $row["REL_ID"];
	}
	for($i=0; $i<count($temp); $i++){
		$sql = "select * from CUSTOM_BOXES where ID='$temp[$i]'";
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		// TEXT BOX
		if ($row[TYPE]==1){
			$html .= "<div class='marginBox'>";
			$html .= "<div class='boxTitle' style='background-color:$row[HEADING_BGCOL]; color:$row[HEADING_TEXTCOL]'>$row[HEADING]</div>";
			$html .= "<div style='background-color:$row[CONTENT_BGCOL]; color:$row[CONTENT_TEXTCOL]'>$row[CONTENT]</div>";
			$html .= "</div>";
		}
		if ($row[TYPE]==2){
			$html .= "<div class='marginBox'>";
			$html .= "<div class='boxTitle' style='background-color:$row[HEADING_BGCOL]; color:$row[HEADING_TEXTCOL]'>$row[HEADING]</div>";
			$html .= "<div style='background-color:$row[CONTENT_BGCOL]; color:$row[CONTENT_TEXTCOL]'>";
			$sql = "select REL_ID from RELATED_CONTENT where CUSTOMBOX_ID='$temp[$i]'";
			$result_links = mysql_query($sql);
			while ($row_links = mysql_fetch_row($result_links)){
				$sql_extURL = "select POINTTOPAGE_URL from PAGES where ID='$row_links[0]'";
				$result_extURL = mysql_query($sql_extURL);
				$row_extURL = mysql_fetch_array($result_extURL);
				$row_extURL[POINTTOPAGE_URL];
				$html .= "<p>";
				if ($row_extURL[POINTTOPAGE_URL] == ""){
					$html .= "<a href='$arr_content[baseurl]/index.php?pageid=$row_links[0]' class='" . ($row_links[0]==$pageid ? "selectedBoxLink" : "") . "'>";
				} else {
					$html .= "<a href='$arr_content[baseurl]/$row_extURL[POINTTOPAGE_URL]'>";
				}
				$html .= returnPageTitle($row_links[0]);
				$html .= "</a>";
				$html .= "</p>";
			}
			$html .= "</div>";
			$html .= "</div>";
		}
	}
	return $html;
 }

 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: FORMS / FORM GENERATOR
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function FE_check_form_integrity($form_id){
		$sql = "
			select
				DFF.MAPPED_FIELD_ID, NTF.MANDATORY
			from
				DEFINED_FORMFIELDS DFF, NEWSLETTER_TEMPLATES_FORMFIELDS NTF,
				DEFINED_FORMS DF
			where
				DFF.FORM_ID='$form_id' and
				DFF.MAPPED_FIELD_ID=NTF.FIELD_ID and
				DF.MAPPED_NEWSLETTER_ID=NTF.TEMPLATE_ID and
				DFF.FORM_ID=DF.ID
		";
		$res = mysql_query($sql);
		$used_mandatory_fields = array();
		while ($row = mysql_fetch_assoc($res)){
			if ($row[MANDATORY] == 1){
				$used_mandatory_fields[] = $row[MAPPED_FIELD_ID];
			}
		}
		$used_mandatory_fields = array_unique($used_mandatory_fields);
		sort($used_mandatory_fields);
		$newsletter_template_id = returnFieldValue("DEFINED_FORMS", "MAPPED_NEWSLETTER_ID", "ID", $form_id);
		$sql = "
			select 
				NTF.FIELD_ID, NTF.MANDATORY
			from
				NEWSLETTER_TEMPLATES_FORMFIELDS NTF, DEFINED_FORMS DF
			where
				DF.ID='$form_id' and
				DF.MAPPED_NEWSLETTER_ID=NTF.TEMPLATE_ID
		";
		$res = mysql_query($sql);
		$all_mandatory_fields = array();
		while ($row = mysql_fetch_assoc($res)){
			if ($row[MANDATORY] == 1){
				$all_mandatory_fields[] = $row[FIELD_ID];
			}
		}
		
		$all_mandatory_fields = array_unique($all_mandatory_fields);
		sort($all_mandatory_fields);
		if (count(array_diff($all_mandatory_fields, $used_mandatory_fields)) != 0){
			return false;
		} else {
			return true;
		}
}
	 
 function return_mapped_fields($template_id, $FORM_POSTVARS){
 	require_once($_SERVER["DOCUMENT_ROOT"]."/cms/modules/newsletter/frontend/newsletter_common.inc.php");
	foreach ($FORM_POSTVARS as $k => $v){
		if (strstr($k, "formfield_")){
			$temp = explode("_", $k);
			$formfield_values[$temp[1]] = $v;
		}
	}
	$sql = " 
		select 
			DFF.ID, DFF.MAPPED_FIELD_ID, NF.FIELD_NAME 
		from 
			DEFINED_FORMFIELDS DFF, NEWSLETTER_FORMFIELDS  NF
		where
			DFF.FORM_ID='$FORM_POSTVARS[formid]' and
			NF.ID=DFF.MAPPED_FIELD_ID
	";
	$res = mysql_query($sql);
	while ($row = mysql_fetch_assoc($res)){
		$POST_TO_NEWSLETTER["cfield___".$row[FIELD_NAME]] = $formfield_values[$row[ID]];
	}
	$email = $POST_TO_NEWSLETTER["cfield___EMAIL"];
	unset($POST_TO_NEWSLETTER["cfield___EMAIL"]);
	$user_id = newsletter_is_user($email);
	if (!$user_id){
		$user_id = newsletter_insert_user($email, $POST_TO_NEWSLETTER);
		$new_user = true;
	}
	$status = newsletter_subscription_engine($user_id, $template_id);
	if ($status == "OKAY_subscribed" || $status == "OKAY_resubscribed"){
		if (require_validation($template_id)){
			$status .= "confirm";
			newsletter_send_subscribe_verification_mail($user_id, $template_id);
		} else {
			$validate_key = md5($email.$user_id.$template_id."1nstansNewsletter098");
			newsletter_verify_subscription($user_id, $template_id, $validate_key, "quiet");
		}
	}
 }

 function parseSubmittedForm(){
  if ($_POST[SUBSCRIBE_TO_NEWSLETTER]){
	if (FE_check_form_integrity($_POST[formid])){
	  	$newsletter_template_id = $_POST[SUBSCRIBE_TO_NEWSLETTER];
		return_mapped_fields($newsletter_template_id, $_POST);
	}
  }
  $sql = "select SEND_MAIL, SAVE_IN_DB, EMAIL, TITLE from DEFINED_FORMS where ID='$_POST[formid]'";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  if ($row[SAVE_IN_DB] > -1) {
   foreach($_POST as $key=>$value) {
    if ($key == "formid") {
	 $this_form_id = $value;
	}
    if ($key != "formid" && $key != "dothis" && $key!="") {
     if (strstr($key, "formfield_")) {
	  $temp = explode("_", $key);
	  $field_ids[] = $temp[1];				// FELTETS UNIKKE ID -> DEFINED_FORMFIELDS.ID
      $field_values[$temp[1]][] = $value;	// FOR HVERT ID LAVES EN ARRAY MED VALUES
	 }
    }
   }
   foreach ($field_values as $key => $value) {
    if (count($value)>1) {
     $field_values[$key] = implode(",",$value); // kollaps checkbox-values til en kommasep. string
    } else {
     $field_values[$key] = implode("",$value);
    }
   }
   foreach ($field_values as $key=>$value){
    $final_ids[] = $key;
    $final_values[] = $value;
   }
   $nu = time();
   $ids = implode("|¤|", $final_ids);
   $values = implode("|¤|", $final_values);
   $unique = md5(rand(1,time()));
   $sql = "insert into TILMELDINGER (FORM_ID, FIELD_IDS, FIELD_VALUES, CREATED_DATE, UNIK) values ('$this_form_id', '$ids', '$values', '$nu', '$unique')";
   $result = mysql_query($sql);
   $nyt_tilmeldings_id = mysql_insert_id();
  }
  if ($row[SEND_MAIL] == 1 && $row[EMAIL]!="") {

  /// CJS, 7/3/2007: Tilføjelse, som gør det muligt at override default e-mail i
  /// formularen og sende til en bestemt user i stedet
  	if ($_POST[uid] && $_POST[ticket] && $_POST[ticket] == md5($_POST[uid].returnFieldValue("USERS", "FIRSTNAME", "ID", $_POST[uid])."1nstansFlyvema5kine")){
		$user_email = returnFieldValue("USERS", "EMAIL", "ID", $_POST[uid]);
		if (trim($user_email) != ""){
			$row[EMAIL] = $user_email;
		}
	}
  /// END CJS, 7/3/2007

   $mail_indhold = outputTilmeldingsPlaintext(1, $nyt_tilmeldings_id);
	$mailfrom = returnSiteName($_SESSION[CURRENT_SITE]);
   // mail($row[EMAIL], $row[TITLE], $mail_indhold, "From: $mailfrom <no-reply@no-reply.dk>\nContent-Type: text/html; charset=UTF-8");
	$mail_domain = returnFieldValue("SITES", "EMAIL_DOMAIN", "SITE_ID", $_SESSION["CURRENT_SITE"]);
   // mail($row[EMAIL], $row[TITLE], $mail_indhold, "From: $mailfrom <no-reply@".$mail_domain.">\nContent-Type: text/html; charset=UTF-8");

        $mail = new htmlMimeMail();

		// Change to UFT-8 encoding
		$mail->setTextCharset("UTF-8");
		$mail->setHTMLCharset("UTF-8");
		$mail->setHeadCharset("UTF-8"); 

        $mail->setText($mail_indhold);
		$mail->setFrom('"'.$mailfrom.'" <no-reply@'.$mail_domain.'>');
		$mail->setSubject($row[TITLE]);
		$mail->send(array("'".$row[EMAIL]."' <".$row[EMAIL].">"), 'mail');

   if ($row[SAVE_IN_DB] != 1) {
    header("location: index.php?saveformdata=0&mode=thanks&formid=$this_form_id&x=$unique&rd=1");
    exit;
   }
  }
  header("location: index.php?saveformdata=0&mode=thanks&formid=$this_form_id&x=$unique");
 } 
 
 function formGenerator($arr_content) {
	$form_id = $arr_content[formid];
  global $cmsLang;
  $nu = date("Y-m-d");
  $sql = "select TITLE, INTROTEXT, MAPPED_NEWSLETTER_ID, SPAMPREVENT_CAPTCHA from DEFINED_FORMS where ID='$form_id' and ((FORM_OPENDATE='0000-00-00' and FORM_CLOSEDATE='0000-00-00') or ('$nu'>=FORM_OPENDATE and '$nu'<=FORM_CLOSEDATE)) and DELETED='0' and SITE_ID = '$arr_content[site]'";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  $newsletter_template_id = $row["MAPPED_NEWSLETTER_ID"];
  $captcha = $row[SPAMPREVENT_CAPTCHA];
  if (mysql_num_rows($result)>0) {
   $html .= "<div class='generatedFormWrapper'>";
	
	if ($arr_content["captcha"] == "failed") {
		$html .= "<div class='formvalidation_error'>".cmsTranslate("FormSpamError")."</div>";
	}
	
   $html .= "<h1 class='heading'>".$row[TITLE]."</h1>";
   $html .= "<p>".$row[INTROTEXT]."</p>";
   $html .= "
   <form action='$arr_content[baseurl]/index.php?saveformdata=1&amp;mode=thanks&amp;formid=$form_id' method='post' name='generatedForm' class='generatedForm'>
   <input type='hidden' name='formid' value='$form_id' />
   <input type='hidden' name='uid' value='$arr_content[uid]' />
   <input type='hidden' name='ticket' value='$arr_content[ticket]' />";
   $sql = "select * from  DEFINED_FORMFIELDS where FORM_ID = '$form_id' and DELETED='0' order by POSITION asc";
   $result = mysql_query($sql);
   while ($row = mysql_fetch_array($result)) {
	if ($row[FIELDTYPE] != 5){
        $html .= "<div class='generatedFormFieldHeader'>" . ($row[VERIFY_FILLED]==1?"* ":"") . "$row[CAPTION]" . ($row[HELPTEXT]!=""?" <a href='#' onclick='showHideHelpText($row[ID]);return false'>(?)</a>":"")."</div>";
	}
	$html .= "<div class='generatedFormFieldHelpText' id='fieldhelp_$row[ID]' style='display:none'>".($row[HELPTEXT]!=""?"$row[HELPTEXT]":"")."</div>";
    
	  // Textfield
    if ($row[FIELDTYPE] == 1) {
	 $html .= "
	 <div class='generatedFormFieldContainer'>
	  <input type='text' value='";
		$postkey="formfield_".$row[ID];
	  if ($_GET[setfields][$row[ID]]) {
	  	$html .= $_GET[setfields][$row[ID]];
	  } elseif ($_POST[$postkey]) {
	  	$html .= $_POST[$postkey];
	  } else {
	  	$html .= $row[TEXT_DEFAULTTEXT];
	  }
	  $html .= "' class='generatedFormField' name='formfield_$row[ID]' size='" . ($row[TEXT_SIZE] != 0 ? $row[TEXT_SIZE] : 50) . "'" . ($row[TEXT_MAXLENGTH] != 0 ? " maxlength='$row[TEXT_MAXLENGTH]' " : "") . ($row[READONLY] != 0 ? " readonly='readonly'" : "") . ($row[DISABLED] != 0 ? " disabled='disabled'" : "") . "/>
	 </div>
	 ";
	 if ($row[VERIFY_FILLED]==1) {
	  $script .= "if (!verifyFilled($row[ID])) {alert(\"".cmsTranslate("formPleaseFillOut")." '$row[CAPTION]'.\"); return};\n";
     }
	 if ($row[VERIFY_NUMBER]==1) {
	  $script .= "if (!verifyNumber($row[ID])) {alert(\"".cmsTranslate("formTheField")." '$row[CAPTION]' ".cmsTranslate("formOnlyNumbers").".\"); return};\n";
     }
	 if ($row[VERIFY_EMAIL]==1) {
	  $script .= "if (!verifyEmail($row[ID])) {alert(\"".cmsTranslate("formValidEmail")." '$row[CAPTION]'.\"); return};\n";
     }
    }

	  // Textarea
    if ($row[FIELDTYPE] == 2) { 
     $html .= "
	 <div class='generatedFormFieldContainer'>
	  <textarea class='generatedFormField' name='formfield_$row[ID]' cols='" . ($row[TEXTAREA_COLS] != 0 ? $row[TEXTAREA_COLS] : 50) . "' rows='" . ($row[TEXTAREA_ROWS] != 0 ? $row[TEXTAREA_ROWS] : 5) . "'" . ($row[TEXT_MAXLENGTH] != 0 ? " maxlength='$row[TEXT_MAXLENGTH]' " : "") . ($row[READONLY] != 0 ? " readonly='readonly'" : "") . ($row[DISABLED] != 0 ? " disabled='disabled'" : "") . ">";
	  //($_GET[setfields][$row[ID]] ? $_GET[setfields][$row[ID]] : $row[TEXTAREA_DEFAULTTEXT]).
		$postkey="formfield_".$row[ID];
	  if ($_GET[setfields][$row[ID]]) {
	  	$html .= $_GET[setfields][$row[ID]];
	  } elseif ($_POST[$postkey]) {
	  	$html .= $_POST[$postkey];
	  } else {
	  	$html .= $row[TEXTAREA_DEFAULTTEXT];
	  }
	 $html .= "</textarea>
	 </div>
	 ";
	 if ($row[VERIFY_FILLED]==1) {
	  $script .= "if (!verifyFilled($row[ID])) {alert(\"".cmsTranslate("formPleaseFillOut")." '$row[CAPTION]'.\"); return};\n";
     }
	 if ($row[VERIFY_NUMBER]==1) {
	  $script .= "if (!verifyNumber($row[ID])) {alert(\"".cmsTranslate("formTheField")." '$row[CAPTION]' ".cmsTranslate("formOnlyNumbers").".\"); return};\n";
     }
	 if ($row[VERIFY_EMAIL]==1) {
	  $script .= "if (!verifyEmail($row[ID])) {alert(\"".cmsTranslate("formValidEmail")." '$row[CAPTION]'.\"); return};\n";
     }
    }

	// Radiogroup
    if ($row[FIELDTYPE] == 3) {
	 $radiocaptions = explode("|", $row[RADIO_CAPTIONS]);
	 $radiodisabledstates = explode("|", $row[RADIO_DISABLEDSTATES]);
	 $radioslettetstates = explode("|", $row[RADIO_SLETTETSTATES]);
	 $html .= "
	 <div class='generatedFormFieldContainer'>";
	 for($i=0; $i<$row[RADIO_COUNT]; $i++){
		$postkey = "formfield_".$row[ID];
		if ($_POST[$postkey] == $i) {
			$radio_checked = "checked ";
		} else {
			$radio_checked = "";
		}

	  if ($radioslettetstates[$i]==0) $html.="<input value='$i' " . ($radiodisabledstates[$i]==1?" disabled ":"") . "type='radio' name='formfield_$row[ID]' $radio_checked />&nbsp;$radiocaptions[$i]<br/>";
	 }
	 $html .= "</div>";
	 if ($row[VERIFY_FILLED]==1) {
	  $script .= "if (!verifyRadioFilled($row[ID])) {alert(\"".cmsTranslate("formOneOption")." '$row[CAPTION]'.\"); return};\n";
     }
    }

	// Checkboxes
    if ($row[FIELDTYPE] == 4) {
	 $checkcaptions = explode("|", $row[CHECKBOX_CAPTIONS]);
	 $checkdisabledstates = explode("|", $row[CHECKBOX_DISABLEDSTATES]);
	 $checkslettetstates = explode("|", $row[CHECKBOX_SLETTETSTATES]);
	 $html .= "
	 <div class='generatedFormFieldContainer'>";
	 for($i=0; $i<$row[CHECKBOX_COUNT]; $i++){

		$postkey = "formfield_".$row[ID]."_checkboks_".$i;
		if (isset($_POST[$postkey]) && $_POST[$postkey] == $i) {
			$checkboxchecked = "checked ";
		} else {
			$checkboxchecked = "";
		}
	  if ($checkslettetstates[$i]==0) $html.="<input value='$i' " . ($checkdisabledstates[$i]==1?" disabled ":"") . "type='checkbox' name='formfield_$row[ID]_checkboks_$i' $checkboxchecked />&nbsp;$checkcaptions[$i]<br/>";
	 }
	 $html .= "</div>";
	 if ($row[CHECKBOX_MINFILLED] != 0) {
	  $script .= "if (!verifyCheckMinFilled($row[ID], $row[CHECKBOX_MINFILLED])) {alert(\"".cmsTranslate("formAtLeast")." $row[CHECKBOX_MINFILLED] ".cmsTranslate("formOptionsInField")." '$row[CAPTION]'.\"); return};\n";
     }
	 if ($row[CHECKBOX_MAXFILLED] != 0) {
	  $script .= "if (!verifyCheckMaxFilled($row[ID], $row[CHECKBOX_MAXFILLED])) {alert(\"".cmsTranslate("formAtMost")." $row[CHECKBOX_MAXFILLED] ".cmsTranslate("formOptionsInField")." '$row[CAPTION]'.\"); return};\n";
     }
    }
	if ($row[FIELDTYPE] == 5 && FE_check_form_integrity($form_id)){
		global $newsletter_subscribe_formintegration_checkedstate;
		$nl_checked = $newsletter_subscribe_formintegration_checkedstate;
		if ($row[CHECKBOX_CAPTIONS] != "") {
			$nl_caption = $row[CHECKBOX_CAPTIONS];
		} else {
			$nl_caption = "Ja tak";
		}
		$html .= "<div class='generatedFormFieldHeader'>$row[CAPTION]" . ($row[VERIFY_FILLED]==1?" *":"") . ($row[HELPTEXT]!=""?" <a href='#' onclick='showHideHelpText($row[ID]);return false'>(?)</a>":"")."</div>";   		$html .= "<div class='generatedFormFieldContainer'>";
		$html .= "<input type='checkbox' $nl_checked name='SUBSCRIBE_TO_NEWSLETTER' value='$newsletter_template_id'>&nbsp;$nl_caption";
		$html .= "</div>";
   	}
   }

	// CAPTCHA
	if ($captcha == 1) {
		$html .= "
				<div class='generatedFormFieldHeader'>".cmsTranslate("FormSpamPrevention").": (*)</div>
				<div class='generatedFormFieldContainer'>
					".cmsTranslate("FormSpamExplanation")."
					<input type='text' name='formware_captcha' id='formware_captcha' />&nbsp;
					<img alt='' class='captcha' src='/cms/scripts/captcha/CaptchaSecurityImages.php?width=100&height=30&characters=5' />
				</div>
		";
	}

	// SUBMIT THE FORM
   $script .= "document.generatedForm.submit()";
   $html .= "<div class='generatedFormButtonBar'>";
   $html .= "<input type='button' value='Send' onclick='verifyGeneratedForm()' class='generatedFormButton' />";
   $html .= "</div>";
   $html .= "</form>";
   $html .= "</div>";
   $html = "<script type='text/javascript'>function verifyGeneratedForm(){".$script."}</script>" . $html;
  } else {
   $html .= "<h1 class='heading'>".cmsTranslate("formError")."</h1>";
   $html .= "<p>".cmsTranslate("formUnavailable")."</p>";
   $html .= "<p>&laquo;&nbsp;<a href='$_SERVER[HTTP_REFERER]'>".cmsTranslate("formBack")."</a></p>";
  }
  return $html;
 } 

function formTakTekst($arr_content) {
	$form_id = $arr_content[formid];
  $sql = "select TITLE, ENDTEXT from DEFINED_FORMS where ID='$form_id' and SITE_ID = '$arr_content[site]'";
  $result = mysql_query($sql);
  $row = mysql_fetch_row($result);
  $html = "
   <h1 class='heading'>$row[0]</h1>
   <p>$row[1]</p>
   <p>".cmsTranslate("formReceipt")."</p>
   <p>". outputTilmeldingsHtml(2, $_GET[x]) .  "</p><p><div style='float: right;'><input type='button' value='".cmsTranslate("formToFrontpage")."' onclick='location=\"$arr_content[baseurl]\";'/></div></p>
  ";
  if ($_GET[rd]==1) {
   $sql = "delete from TILMELDINGER where UNIK='$_GET[x]'";
   mysql_query($sql);
  }
  return $html;
 }

 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: IMAGE GALLERY
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 
 function old__galleryBuilder($pageid){
	global $picturearchive_UploaddirAbs, $gallery_maxwidth;
	$sql = "select FOLDER_ID from GALLERIES where PAGE_ID='$pageid'";
  	$result = mysql_query($sql);
  	$row = mysql_fetch_row($result);
  	$folder_id = $row[0];
  	$sql = "select FOLDERNAME from PICTUREARCHIVE_FOLDERS where ID='$folder_id'";
  	$result = mysql_query($sql);
  	$row = mysql_fetch_row($result);
  	$foldername = $row[0];
  	$imagePath = $picturearchive_UploaddirAbs."/".$foldername;
  	if ($folder_id){
		$sql = "select FILENAME, DESCRIPTION, ALTTEXT, SIZE_X, SIZE_Y from PICTUREARCHIVE_PICS where FOLDER_ID='$folder_id' order by POSITION asc";
   		$result = mysql_query($sql);
		$i = 1;
		while ($row = mysql_fetch_array($result)){
			$script .= "pic_$i = new Image();\n";
			$script .= "pic_$i.src = '$imagePath/$row[FILENAME]';\n";
			if ($row[SIZE_X] <= $gallery_maxwidth){
				$script .= "pic_$i"."_width = $row[SIZE_X];\n";
			} else {
				$script .= "pic_$i"."_width = $gallery_maxwidth;\n";
			}
			$row[DESCRIPTION] = nl2br($row[DESCRIPTION]);
			$row[DESCRIPTION] = str_replace("\r", "", $row[DESCRIPTION]);
			$row[DESCRIPTION] = str_replace("\n", "", $row[DESCRIPTION]);
			// $desc = addslashes($desc);
			$desc = nl2br($desc);
			$script .= "pic_$i"."_desc = '".$row[DESCRIPTION]."';\n";
			$nav .= "<a href='#' onclick='javascript:galleryShifter($i); return false'><img src='$imagePath/thumbs/$row[FILENAME]' border='0'/></a>";
			$i++;
		}
  	}
	$script = "
   		<div class='gallery' id='theGallery' style='height:auto'>
    		<div class='galleryNav'>$nav</div>
			<div><img src='' name='galleryImage' id='galleryImage' class='galleryImage' alt='' width='$gallery_maxwidth'></div>
			<div class='galleryDescription' id='galleryDescription'></div>
			<script type=\"text/javascript\">$script</script>
   		</div>
	";
  	if ($i > 1){
   		return $script;
  	} else { 
		return "";
  	}
}

 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: IMAGE GALLERY
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 
 function galleryBuilder($pageid){
 	/*
		LAV:

		1) Check om billedet er bredere end maxwidth (eller evt højere end maxheight)
		
		2) Hvis nej, brug "originalen", idet denne ikke er for stor
		
		3) Hvis ja: Check, om der allerede findes en cachet, nedskaleret udgave. Hvis den findes,
		brug denne. (Lav check på billednavnet, fx /gallerycache/cache_162372373.jpg).
		Hvis den ikke findes: Nedskaler og gem i cache.
		
		4) Indhentning af billder: Load alle thumbs + første store udgave. Lav link på billede, hvis
		der findes en endnu større original (større end den cachede). Ved klik på thumbnails ud over nr. 1:
		Generer nyt billede med javascript, giv det nye billede en onload-handler, så det vises "øverst" i bunken,
		når det er læst færdigt. Check evt. hvordan dmg-galleriet er lavet.
	*/
	global $picturearchive_UploaddirAbs, $gallery_maxwidth;
	$sql = "select FOLDER_ID from GALLERIES where PAGE_ID='$pageid'";
  	$result = mysql_query($sql);
  	$row = mysql_fetch_row($result);
  	$folder_id = $row[0];
  	$sql = "select FOLDERNAME from PICTUREARCHIVE_FOLDERS where ID='$folder_id'";
  	$result = mysql_query($sql);
  	$row = mysql_fetch_row($result);
  	$foldername = $row[0];
  	$imagePath = $picturearchive_UploaddirAbs."/".$foldername;
	$cachePath = $picturearchive_UploaddirAbs."/gallerycache";
	$cachePathServer = $_SERVER[DOCUMENT_ROOT]."/includes/uploaded_pictures/gallerycache";
	$html .= "
		<div id='inline_gallery'>
            <script>images_arr = new Array();</script>
            <input type='hidden' id='cur_img' value='' />
			<div id='thumbs_container'>
				<table width>
					<tr>
	";
  	if ($folder_id){
		$sql = "select ID, FILENAME, DESCRIPTION, ALTTEXT, SIZE_X, SIZE_Y from PICTUREARCHIVE_PICS where FOLDER_ID='$folder_id' order by POSITION asc";
   		$result = mysql_query($sql);
		$i = 1;
		while ($row = mysql_fetch_assoc($result)){
			$smallPath = $imagePath."/thumbs/".$row[FILENAME];
			$imagewidth = $row[SIZE_X];
			if ($imagewidth > $gallery_maxwidth){
				$mediumPath = $cachePath."/".$row[FILENAME];
				$largePath = $imagePath."/".$row[FILENAME];
				if (file_exists($cachePathServer."/".$row[FILENAME])){
					$sizes = getimagesize($cachePathServer."/".$row[FILENAME]);
				} else {
					$sizes = gallery_downsample($foldername, $row[FILENAME], $row[SIZE_X], $row[SIZE_Y]);
				}
			} else {
				$sizes = array($row[SIZE_X], $row[SIZE_Y]);
				$mediumPath = $imagePath."/".$row[FILENAME];
				$largePath = "";
			}
			$alt = $row[ALTTEXT];
			$alt = trim($alt);
			$alt = str_replace("\"", "", $alt);
			$alt = str_replace("'", "", $alt);
			$alt = str_replace("\r", "", $alt);
			$alt = str_replace("\n", "", $alt);
			$alt = str_replace("\r\n", "", $alt);
			$script .= "images_arr[$i] = [\"$mediumPath\", \"$largePath\", \"$alt\", \"$sizes[0]\", \"$sizes[1]\"];\n";
			$html .= "<td><a href='#' onclick='show_gallery_image(\"".$mediumPath."\", \"".$largePath."\", \"$alt\", ".$sizes[0].", ".$sizes[1].", $i); return false;'><img class='thumbnail' style='float:left' src='$smallPath' border='0' alt='".$alt."' /></a></td>";
            //$html .= "<td><a href='#' onclick='show_gallery_image($i); return false;'><img class='thumbnail' style='float:left' src='$smallPath' border='0' alt='".$row[ALTTEXT]."' /></a></td>";
			if ($i == 1){
				$firstImage = "<div id='mediumsize_container'><p><img id='current_image' src='$mediumPath' alt='".$alt."' /></p><p id='alttext'>".$alt."</p></div>";
			}
			$i++;
		}
		$html .= "
					</tr>
				</table>
                <script>$script</script>
			</div>
            <div id='gallery_prevnext'><a href='#' onclick='gallery_prev(); return false;' id='gallery_prevlink'>".cmsTranslate("gallery_prev")."</a>&nbsp;<a href='#' onclick='gallery_next(); return false;' id='gallery_nextlink'>".cmsTranslate("gallery_next")."</a></div>
			<div id='gallery_selected_image' style='margin-top:20px; text-align:center;'>
				$firstImage
			</div>
		</div>";
  	}
	/*
	$script = "
   		<div class='gallery' id='theGallery' style='height:auto'>
    		<div class='galleryNav'>$nav</div>
			<div><img src='' name='galleryImage' id='galleryImage' class='galleryImage' alt='' width='$gallery_maxwidth'></div>
			<div class='galleryDescription' id='galleryDescription'></div>
			<script type=\"text/javascript\">$script</script>
   		</div>
	";
	*/
	if ($folder_id) return $html;
}

function gallery_downsample($foldername, $filename, $width, $height){
	global $gallery_maxwidth, $gallery_quality;
	$originalPath = $_SERVER[DOCUMENT_ROOT]."/includes/uploaded_pictures/".$foldername."/".$filename;
	$cachePath = $_SERVER[DOCUMENT_ROOT]."/includes/uploaded_pictures/gallerycache/".$filename;
	$factor = $width/$height;
	$new_width = $gallery_maxwidth;
	$new_height = $new_width / $factor;
 	$placeholder = imagecreatetruecolor($new_width, $new_height);	
	$imagehandle = imagecreatefromjpeg($originalPath);
	imagecopyresampled($placeholder, $imagehandle, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
	imagejpeg($placeholder, $cachePath, $gallery_quality);
	return array($new_width, $new_height);
}
  
 
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // * FUNCTIONS: TREDJEPARTS FUNKTIONER
 // - safeAddress
 // - replacePngTags
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

 function safeAddress($emailAddress, $theText, $theTitle, $xhtml, $isItSafe) {
 // Version 1.5 - by Dan Benjamin - http://www.hivelogic.com/
 // set $isItSafe = 1 to get escaped HTML, 0 for normal HTML
 // set $xhtml = 1 if you want your page to be valid for XHTML 1.x
 // you can call it like this: 
 // echo safeAddress($entity, $linkText, $titleText, 1, 1);
    $ent = "";
    $userName = "";
    $domainName = "";
    for ($i = 0; $i < strlen($emailAddress); $i++) {
        $c = substr($emailAddress, $i, 1);
        if ($c == "@") {
            $userName = $ent;
            $ent = "";
            } else {
            $ent .= "&#" . ord($c) . ";";
            }
    }
    $domainName = $ent;
    if ($xhtml == 1) {
    $endResult = "<script type=\"text/javascript\">
	<!--
	document.write('<a href=\"mailto:$userName&#64;$domainName\" title=\"$theTitle\">$theText<\/a>');
	// -->
	</script>";

    } else {
        $endResult = "<script language=\"JavaScript\" type=\"text/javascript\">
	<!--
	document.write('<a href=\"mailto:$userName&#64;$domainName\" title=\"$theTitle\">$theText<\/a>');
	// -->	
	</script>";
    }
    if ($isItSafe) {
        return(htmlentities($endResult));
    } else {
        return($endResult);
    }
  } 

/**
*  KOIVI PNG Alpha IMG Tag Replacer for PHP (C) 2004 Justin Koivisto
*  Version 2.0.12
*  Last Modified: 12/30/2005
*  
*  This library is free software; you can redistribute it and/or modify it
*  under the terms of the GNU Lesser General Public License as published by
*  the Free Software Foundation; either version 2.1 of the License, or (at
*  your option) any later version.
*  
*  This library is distributed in the hope that it will be useful, but
*  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
*  or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
*  License for more details.
*  
*  You should have received a copy of the GNU Lesser General Public License
*  along with this library; if not, write to the Free Software Foundation,
*  Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*  
*  Full license agreement notice can be found in the LICENSE file contained
*  within this distribution package.
*  
*  Justin Koivisto
*  justin.koivisto@gmail.com
*  http://koivi.com
*
*  Modifies IMG and INPUT tags for MSIE5+ browsers to ensure that PNG-24
*  transparencies are displayed correctly.  Replaces original SRC attribute
*  with a binary transparent PNG file (spacer.png) that is located in the same
*  directory as the orignal image, and adds the STYLE attribute needed to for
*  the browser. (Matching is case-insensitive. However, the width attribute
*  should come before height.
*  
*  Also replaces code for PNG images specified as backgrounds via:
*  background-image: url(image.png); or background-image: url('image.png');
*  When using PNG images in the background, there is no need to use a spacer.png
*  image. (Only supports inline CSS at this point.)
*  
*  @param string $x  String containing the content to search and replace in.
*  @param string $img_path   The path to the directory with the spacer image relative to
*                      the DOCUMENT_ROOT. If none os supplied, the spacer.png image
*                      should be in the same directory as PNG-24 image.
*  @param string $sizeMeth   String containing the sizingMethod to be used in the
*                      Microsoft.AlphaImageLoader call. Possible values are:
*                      crop - Clips the image to fit the dimensions of the object.
*                      image - Enlarges or reduces the border of the object to fit
*                              the dimensions of the image.
*                      scale - Default. Stretches or shrinks the image to fill the borders
*                              of the object.
*  @param bool   $inScript  Boolean flag indicating whether or not to replace IMG tags that
*                      appear within SCRIPT tags in the passed content. If used, may cause
*                      javascript parse errors when the IMG tags is defined in a javascript
*                      string. (Which is why the options was added.)
*  @return string
*/
function replacePngTags($x,$img_path='',$sizeMeth='scale',$inScript=FALSE){
    $arr2=array();
    // make sure that we are only replacing for the Windows versions of Internet
    // Explorer 5.5+
    $msie='/msie\s(5\.[5-9]|[6]\.[0-9]*).*(win)/i';
    if( !isset($_SERVER['HTTP_USER_AGENT']) ||
        !preg_match($msie,$_SERVER['HTTP_USER_AGENT']) ||
        preg_match('/opera/i',$_SERVER['HTTP_USER_AGENT']))
        return $x;

    if($inScript){
        // first, I want to remove all scripts from the page...
        $saved_scripts=array();
        $placeholders=array();
        preg_match_all('`<script[^>]*>(.*)</script>`isU',$x,$scripts);
        for($i=0;$i<count($scripts[0]);$i++){
            $x=str_replace($scripts[0][$i],'replacePngTags_ScriptTag-'.$i,$x);
            $saved_scripts[]=$scripts[0][$i];
            $placeholders[]='replacePngTags_ScriptTag-'.$i;
        }
    }

    // find all the png images in backgrounds
    preg_match_all('/background-image:\s*url\(([\\"\\\']?)([^\)]+\.png)\1\);/Uis',$x,$background);
    for($i=0;$i<count($background[0]);$i++){
        // simply replace:
        //  "background-image: url('image.png');"
        // with:
        //  "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(
        //      enabled=true, sizingMethod=scale, src='image.png');"
        // I don't think that the background-repeat styles will work with this...
        $x=str_replace($background[0][$i],'filter:progid:DXImageTransform.'.
                'Microsoft.AlphaImageLoader(enabled=true, sizingMethod='.$sizeMeth.
                ', src=\''.$background[2][$i].'\');',$x);
    }

    // find all the IMG tags with ".png" in them
    $pattern='/<(input|img)[^>]*src=([\\"\\\']?)([^>]*\.png)\2[^>]*>/i';
    preg_match_all($pattern,$x,$images);
    for($num_images=0;$num_images<count($images[0]);$num_images++){
        // for each found image pattern
        $original=$images[0][$num_images];
        $quote=$images[2][$num_images];
        $atts=''; $width=0; $height=0; $modified=$original;

        // We do this so that we can put our spacer.png image in the same
        // directory as the image - if a path wasn't passed to the function
        if(empty($img_path)){
            $tmp=split('[\\/]',$images[3][$num_images]);
            $this_img=array_pop($tmp);
            $img_path=join('/',$tmp);
            if(empty($img_path)){
                // this was a relative URI, image should be in this directory
                $tmp=split('[\\/]',$_SERVER['SCRIPT_NAME']);
                array_pop($tmp);    // trash the script name, we only want the directory name
                $img_path=join('/',$tmp).'/';
            }else{
                $img_path.='/';
            }
        }else if(substr($img_path,-1)!='/'){
            // in case the supplied path didn't end with a /
            $img_path.='/';
        }

        // If the size is defined by styles, find them
        preg_match_all(
            '/style=([\\"\\\']).*(\s?width:\s?([0-9]+(px|%));).*'.
            '(\s?height:\s?([0-9]+(px|%));).*\\1/Ui',
               $images[0][$num_images],$arr2); 
        if(is_array($arr2) && count($arr2[0])){
            // size was defined by styles, get values
            $width=$arr2[3][0];
            $height=$arr2[6][0];

            // remove the width and height from the style
            $stripper=str_replace(' ','\s','/('.$arr2[2][0].'|'.$arr2[5][0].')/');
            // Also remove any empty style tags
            $modified=preg_replace(
                '`style='.$arr2[1][0].$arr2[1][0].'`i',
                '',
                preg_replace($stripper,'',$modified));
        }else{
            // size was not defined by styles, get values from attributes
            preg_match_all('/width=([\\"\\\']?)([0-9%]+)\\1/i',$images[0][$num_images],$arr2);
            if(is_array($arr2) && count($arr2[0])){
                $width=$arr2[2][0];
                if(is_numeric($width))
                    $width.='px';
    
                // remove width from the tag
                $modified=str_replace($arr2[0][0],'',$modified);
            }
            preg_match_all('/height=([\\"\\\']?)([0-9%]+)\\1/i',$images[0][$num_images],$arr2);
            if(is_array($arr2) && count($arr2[0])){
                $height=$arr2[2][0];
                if(is_numeric($height))
                    $height.='px';
    
                // remove height from the tag
                $modified=str_replace($arr2[0][0],'',$modified);
            }
        }

        if($width==0 || $height==0){
            // width and height not defined in HTML attributes or css style, try to get
            // them from the image itself
            // this does not work in all conditions... It is best to define width and
            // height in your img tag or with inline styles..
            if(file_exists($_SERVER['DOCUMENT_ROOT'].$img_path.$images[3][$num_images])){
                // image is on this filesystem, get width & height
                $size=getimagesize($_SERVER['DOCUMENT_ROOT'].$img_path.$images[3][$num_images]);
                $width=$size[0].'px';
                $height=$size[1].'px';
            }else if(file_exists($_SERVER['DOCUMENT_ROOT'].$images[3][$num_images])){
                // image is on this filesystem, get width & height
                $size=getimagesize($_SERVER['DOCUMENT_ROOT'].$images[3][$num_images]);
                $width=$size[0].'px';
                $height=$size[1].'px';
            }
        }
        
        // end quote is already supplied by originial src attribute
        $replace_src_with=$quote.$img_path.'spacer.png'.$quote.' style="width: '.$width.
            '; height: '.$height.'; filter: progid:DXImageTransform.'.
            'Microsoft.AlphaImageLoader(src=\''.$images[3][$num_images].'\', sizingMethod='.
            $sizeMeth.');"';

        // now create the new tag from the old
        $new_tag=str_replace($quote.$images[3][$num_images].$quote,$replace_src_with,
            str_replace('  ',' ',$modified));
        // now place the new tag into the content
        $x=str_replace($original,$new_tag,$x);
    }
    
    if($inScript){
        // before the return, put the script tags back in. (I was having problems when there was
        // javascript that had image tags for PNGs in it when using this function...
        $x=str_replace($placeholders,$saved_scripts,$x);
    }
    
    return $x;
 }
 
 function randomString($length=7){ 
	$randstr=''; 
	srand((double)microtime()*1000000); 
	$chars = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'); 
	for ($rand = 0; $rand <= $length; $rand++){ 
		$random = rand(0, count($chars) -1); 
		$randstr .= $chars[$random]; 
	} 
	return $randstr; 
 } 

function requestLogin($message, $arr_content) {
	$html .= "<div class='loginmessage'>$message</div>";
	$html .= loginPage($arr_content);
	$_SESSION["LOGIN_FROM_PAGE"] = "";	// Reset to avoid wrong redirect!
	return $html;
}

/*
	FUNCTIONS USED FOR SITE SEPERATION - TO VALIDATE URL AGAINST CURRENT SITE & RETURN DEFAULT SITE FOR URL
*/
function return_site_to_show() {
	// IS A SPECIFIC SITE_ID REQUESTED THROUGH $_SESSION VARIABLE OR SITE SWITCH $_GET OG REWRITE COMMAND?
	$previous_siteid = $_SESSION[CURRENT_SITE];

	if (!isset($_GET[rw])) {
		// 1. URL has not been rewritten, check site parameter in url $_GET variable
		if ($_GET[site]) {
			// Trying to switch site with $_GET variable?
			$testing_siteid = $_GET[site];
		} elseif ($_SESSION[CURRENT_SITE]) {
			$testing_siteid = $_SESSION[CURRENT_SITE];
		} else {
			$testing_siteid = "";
		}
	} else {
		// 2. URL has been rewritten, get $arr_path
		$arr_url_elements = return_url_elements_array();
		$str_path = convert_url_parameterstring($arr_url_elements[path]); // Make sure all is safe (and lowercase)
		$arr_path = return_rewrite_parameters_array($str_path);
//		print_r($arr_path);
		// 2.1 Check arr_path for site rewritekeys in any position
		foreach ($arr_path as $key => $value) {
			$sql = "select REQUEST_ID from REWRITE_KEYWORDS where TABLENAME = 'SITES' and KEYWORD = '$value' order by id asc limit 1";
			if ($res = mysql_query($sql)) {
				if (mysql_num_rows($res)>0) {
					$testing_siteid = mysql_result($res,0);
					break; // Stop looping path elements, most likely after first iteration since site keywords are often first in array
				} else {
					$testing_siteid = "";
				}
			} else {
				$testing_siteid = "";
			}
			
		}
		// 2.2 If no site rewritekey is found, use session if set
		if ($testing_siteid == "" && $_SESSION[CURRENT_SITE]) {
			$testing_siteid = $_SESSION[CURRENT_SITE];
		}
	}

//	echo "<br/>Previous: $previous_siteid";
//	echo "<br/>Trying to switch to: $testing_siteid";
	
	if (is_numeric($testing_siteid)) {
		// Validate the site against the url
		if (!validate_siteid($testing_siteid)) {
			// The site-id $testing_siteid is invalid for this URL, get default site for this URL
			$site_to_show = getdefaultsite();
		} else {
			// The site-id $testing_siteid is VALID for this URL, return it
			$site_to_show = $testing_siteid;
		}
	} else {
		// No site to test given, return default site for this URL
		$site_to_show = getdefaultsite();
	}
	if (is_numeric($site_to_show)) {
		$_SESSION[CURRENT_SITE] = $site_to_show;
		return $site_to_show;
	} else {
		unset($_SESSION[CURRENT_SITE]);
		die("<html><head><meta http-equiv='content-type' content='text/html;charset=UTF-8' /></head><body><h1>Ingen hjemmeside på denne adresse / No site configured at this address</h1></body></html>");
	}	
}

function getdefaultsite() {
//echo"<br/><br/>Attempting to get default site for current url...<pre>";

	// 1. Parse the URL to create $arr_url 
	$arr_url = return_url_array();

//echo "url array:<br/>";
//print_r($arr_url);
	// 2. Get candidate sites from DOMAIN ONLY
	$arr_candidate_sites = return_candidate_sites_from_domain($arr_url);
	if (!is_array($arr_candidate_sites)) {
		return false;
	}

//echo "domain candidates array:<br/>";
//print_r($arr_candidate_sites);

	// 3. NARROW LIST OF CANDIDATES BY LOOKING AT URL-SUBDOMAIN
	$arr_candidate_sites = reduce_candidate_sites_by_subdomain($arr_candidate_sites, $arr_url);
	if (!is_array($arr_candidate_sites)) {
		return false;
	}

//echo "subdomain candidates array:<br/>";
//print_r($arr_candidate_sites);


	// 4. NARROW LIST OF CANDIDATES BY LOOKING AT URL-SITEPATH
	$arr_candidate_sites = reduce_candidate_sites_by_sitepath($arr_candidate_sites, $arr_url);

//echo "sitepath candidates array:<br/>";
//print_r($arr_candidate_sites);
	
	// 5. DETERMINE WHICH SITE TO SHOW
	if (count($arr_candidate_sites) == 1) {
		// One valid candidate remaining, return it!
		$arr_candidate_sites = array_merge($arr_candidate_sites); // Reindex array
		check_for_redirect($arr_candidate_sites[0]);
		return $arr_candidate_sites[0][site_id];
	} elseif (count($arr_candidate_sites) > 1) {
		// More than one valid candidate remaining, find default(s) if any
		$arr_candidate_sites = reduce_candidate_sites_by_defaults($arr_candidate_sites, $arr_url);
		// ... and return first remaning candidate
		$arr_candidate_sites = array_merge($arr_candidate_sites); // Reindex array
		check_for_redirect($arr_candidate_sites[0]);
		return $arr_candidate_sites[0][site_id];
	} else {
		// No sites found, error
		return false;
	}
}

function check_for_redirect($arr_candidate_site) {
	// First check for url redirect, 
	if ($arr_candidate_site[redirect_to_url] != "") {
		// Perform 301 redirect
		header( "HTTP/1.1 301 Moved Permanently" );
		header( "Status: 301 Moved Permanently" );
		header( "Location: ".$arr_candidate_site[redirect_to_url] );
		exit(0); // This is Optional but suggested, to avoid any accidental output
	}
	if ($arr_candidate_site[redirect] > 0) {
		// Redirect to this CMS_DOMAINS row, so get necessary info
		$sql = "select 
					*
				from 
					CMS_SITEDOMAINS
				where 
					CMS_SITEDOMAINS.ID = '$arr_candidate_site[redirect]'";

		$res = mysql_query($sql);
		$row = mysql_fetch_assoc($res);

		$location = "http://";
		if ($row[SUBDOMAIN] != "" && $row[SUBDOMAIN] != "*") {
			$location .= "$row[SUBDOMAIN].";
		}
		$location .= $row[DOMAIN].$_SERVER["REQUEST_URI"];

		// Perform 301 redirect
		header( "HTTP/1.1 301 Moved Permanently" );
		header( "Status: 301 Moved Permanently" );
		header( "Location: $location" );
		exit(0); // This is Optional but suggested, to avoid any accidental output
	}
}

function reduce_candidate_sites_by_defaults($arr_candidate_sites, $arr_url) {
	foreach ($arr_candidate_sites as $key => $candidate_site) {
		if ($candidate_site["default"] == 1) {
			$arr_default_sites[] = $candidate_site;
		}
	}
	if (is_array($arr_default_sites)) {
		// Default sites found
		return $arr_default_sites;
	} else {
		// No default sites found, continue with original candidates
		return $arr_candidate_sites;
	}
}

function reduce_candidate_sites_by_sitepath($arr_candidate_sites, $arr_url) {
	if ($arr_url[sitepath] == "") {
		// Explicitly no sitepath in URL, so disqualify all $arr_candidate_sites with a sitepath defined
		foreach ($arr_candidate_sites as $key => $candidate_site) {
			if ($candidate_site[sitepath] != "") {
				unset($arr_candidate_sites[$key]);
			}
		}
		return $arr_candidate_sites;
	} else {
		// Sitepath in url, need to check if this is a SITES.SITE_PATH or a simple rewrite key
		// If there is a matching site_path among the candidate sites, it is a SITE_PATH, if not it is a rewrite key
		foreach ($arr_candidate_sites as $key => $candidate_site) {
			if ($candidate_site[sitepath] == "$arr_url[sitepath]") {
				$arr_sites_with_matching_sitepath[] = $candidate_site;
			}
		}
		if (is_array($arr_sites_with_matching_sitepath)) {
			// One or more candidates with matching sitepath exist, return these
			return $arr_sites_with_matching_sitepath;
		} else {
			// No matching candidates found, return original candidates
			return $arr_candidate_sites;
		}
	}
}

function reduce_candidate_sites_by_subdomain($arr_candidate_sites, $arr_url) {
	// NARROW LIST OF CANDIDATES BY LOOKING AT URL-SUBDOMAIN
	// 3.1 First check for exact subdomain match
	foreach ($arr_candidate_sites as $key => $candidate_site) {
		if ($candidate_site[subdomain] == $arr_url[subdomain]) {
			$arr_subdomain_matches_exact[] = $arr_candidate_sites[$key];
		}
	}
	if (is_array($arr_subdomain_matches_exact)) {
	// 3.1.1 Exact match found, reduce candidate list to exact subdomain matches
		$arr_candidate_sites = $arr_subdomain_matches_exact;
	} else {
	// 3.1.2 Exact match not found, check for wildcard subdomain match
		foreach ($arr_candidate_sites as $key => $candidate_site) {
			if ($candidate_site[subdomain] == "*") {
				$arr_subdomain_matches_wildcard[] = $candidate_site;
			}
		}
		if (is_array($arr_subdomain_matches_wildcard)) {
			// 3.1.1.1 Wildcard match found, reduce candidate list to wildcard subdomain matches
			$arr_candidate_sites = $arr_subdomain_matches_wildcard;
		} else {
			// 3.1.1.1 Wildcard match not found, invalid site
			return false;
		}
	} 
	return $arr_candidate_sites;
}

function return_candidate_sites_from_domain($arr_url, $testing_siteid="") {
	// Get candidate sites from DOMAIN ONLY
	$sql = "select CS.*, S.SITE_PATH from CMS_SITEDOMAINS CS, SITES S where CS.SITE_ID = S.SITE_ID and CS.DOMAIN = '$arr_url[domain]'";
	if (is_numeric($testing_siteid)) {
		$sql .= " and CS.SITE_ID = $testing_siteid";
	}
	if ($res = mysql_query($sql)) {
		if (mysql_num_rows($res)>0) {
			while ($row = mysql_fetch_assoc($res)) {
				$row[SITE_PATH] = substr($row[SITE_PATH],1);
				$arr_candidate_sites[] = array(	"id"=>"$row[ID]",
												"site_id"=>"$row[SITE_ID]",
												"subdomain"=>"$row[SUBDOMAIN]",
												"domain"=>"$row[DOMAIN]",
												"sitepath"=>"$row[SITE_PATH]",
												"default"=>"$row[DEFAULT]",
												"redirect"=>"$row[REDIRECT]",
												"redirect_to_url"=>"$row[REDIRECT_TO_URL]"
												);
			}
		}
	}

	// Add candidates with sitepath
	$sql = "select 
				CS.*, 
				RK.KEYWORD as SITE_PATH
			from 
				CMS_SITEDOMAINS CS, 
				REWRITE_KEYWORDS RK
			where 
				CS.SITE_ID = RK.REQUEST_ID and 
				CS.DOMAIN = '$arr_url[domain]' and
				RK.TABLENAME = 'SITES'				
	";

	if (is_numeric($testing_siteid)) {
		$sql .= " and CS.SITE_ID = $testing_siteid";
	}
	if ($res = mysql_query($sql)) {
		if (mysql_num_rows($res)>0) {
			while ($row = mysql_fetch_assoc($res)) {
				$arr_candidate_sites[] = array(	"id"=>"$row[ID]",
												"site_id"=>"$row[SITE_ID]",
												"subdomain"=>"$row[SUBDOMAIN]",
												"domain"=>"$row[DOMAIN]",
												"sitepath"=>"$row[SITE_PATH]",
												"default"=>"$row[DEFAULT]",
												"redirect"=>"$row[REDIRECT]"
												);
			}
		}
	}
	return $arr_candidate_sites;
}

function return_url_array() {
	// Parse the URL to create $arr_url 
	$arr_http_host = explode(".", $_SERVER["HTTP_HOST"]);
	$arr_request_uri = explode("/", $_SERVER["REQUEST_URI"]);
	if (count($arr_http_host) == 3) {
		$arr_url[subdomain] = $arr_http_host[0];
		$arr_url[domain] 	= $arr_http_host[1].".".$arr_http_host[2];
	} else {
		$arr_url[subdomain] = "";
		$arr_url[domain] 	= $arr_http_host[0].".".$arr_http_host[1];
	}
	$arr_url[sitepath] 	= $arr_request_uri[1];

//	print_r($arr_url);
	return $arr_url;
}

function validate_siteid($testing_siteid) {
//	echo "<br/>validating site: $testing_siteid<br/>";
	// MAKE SURE THAT THE SITE_ID WE'RE ABOUT TO SHOW IS VALID FOR THE (SUB)DOMAIN+SITE_PATH GIVEN IN THE URL
	// Function returns TRUE if $testing_siteid is a valid SITE for the url
	// Function returns FALSE if $testing_siteid is not valid SITE for the url

	// 1. Parse the URL to create $arr_url 
	$arr_url = return_url_array();
	
	// 2. Get candidate sites from DOMAIN ONLY
	$arr_candidate_sites = return_candidate_sites_from_domain($arr_url, $testing_siteid);
	if (!is_array($arr_candidate_sites)) {
		return false;
	}

//echo "<br/>DOMAIN CANDIDATES: <pre>";
//print_r($arr_candidate_sites);
//echo "</pre>";

	// 3. NARROW LIST OF CANDIDATES BY LOOKING AT URL-SUBDOMAIN
	$arr_candidate_sites = reduce_candidate_sites_by_subdomain($arr_candidate_sites, $arr_url);
	if (!is_array($arr_candidate_sites)) {
		return false;
	}

//echo "<br/>SUBDOMAIN CANDIDATES: <pre>";
//print_r($arr_candidate_sites);
//echo "</pre>";
	
	// 4. NARROW LIST OF CANDIDATES BY LOOKING AT SITEPATH
	$arr_candidate_sites = reduce_candidate_sites_by_sitepath($arr_candidate_sites, $arr_url);

//echo "<br/>SITEPATH CANDIDATES: <pre>";
//print_r($arr_candidate_sites);
//echo "</pre>";

	// 5. ALLOW THE SITE_ID FOR THE CURRENT URL?
	// Allow if one of the remaining candidates has site_id = $testing_siteid
	foreach ($arr_candidate_sites as $key => $candidate_site) {
		if ($candidate_site[site_id] == $testing_siteid) {
			$arr_candidates_with_correct_siteid[] = $candidate_site;
		}
	}
	if (is_array($arr_candidates_with_correct_siteid)) {
		// Check if any of the candidates are allowed without redirect
		foreach ($arr_candidates_with_correct_siteid as $key => $candidate_site) {
			if ($candidate_site[redirect] == 0) {
				$arr_candidates_without_redirect[] = $candidate_site;
			}
		}
		if (is_array($arr_candidates_without_redirect)) {
			// Non-redirecting candidate exists, url/site combo is ok
			return true;
		} else {
			// Only redirecting candidates exist, redirect
			check_for_redirect($arr_candidates_with_correct_siteid[0]);
			return false;
		}
	} else {
		return false;
	}
}

function return_customfielddata($arr_content) {
	// This function will return an array containing all customfielddata for the requested content
	// Currently only implemented for use on PAGES
	// To extend for all uses, simply add switch to get correct $request_id and $tablename from $arr_content
	$request_id = $arr_content[pageid];
	$tablename = "PAGES";

	$sql = "select distinct
				C.FIELDKEY, CA.ATTRIBUTEKEY, CA.ATTRIBUTETYPE, CD.VALUE
			from
				CUSTOMFIELDS C,
				CUSTOMFIELDTYPES CT,
				CUSTOMFIELDATTRIBUTES CA,
				CUSTOMFIELDDATA CD
			where
				C.TYPE_ID = CT.ID and
				CT.ID = CA.CUSTOMFIELDTYPE_ID and
				CA.ID = CD.ATTRIBUTE_ID and
				CD.REQUEST_ID = '$request_id' and
				C.TABLENAME = '$tablename' and
				C.ID = CD.CUSTOMFIELD_ID and
				DELETED = '0'
			order by 
				C.POSITION asc, CA.POSITION asc";
	$res = mysql_query($sql);
	while ($row = mysql_fetch_assoc($res)) {
		if ($row[ATTRIBUTETYPE] == "IMAGESELECTOR") {
			$image_url = returnImageUrl($row[VALUE]);
			$thumburl = explode("/",$image_url);
			$lastpart = array_pop($thumburl);
			$thumburl[] = "thumbs";
			$thumburl[] = $lastpart;
			$thumburl = implode("/", $thumburl); 

			$arr_data[$row[FIELDKEY]][$row[ATTRIBUTEKEY]][ID] = $row[VALUE]; 
			$arr_data[$row[FIELDKEY]][$row[ATTRIBUTEKEY]][URL] = $image_url; 
			$arr_data[$row[FIELDKEY]][$row[ATTRIBUTEKEY]][THUMBURL] = $thumburl; 

		} else {
			$arr_data[$row[FIELDKEY]][$row[ATTRIBUTEKEY]] = $row[VALUE]; 
		}
	}			
	return $arr_data;
}  

function return_related_products($productid, $groupid=""){
	// Function to return related shop products
	// First it looks for products related specifically to this product id
	// Then it looks for products related specifically to the group the which the product belongs
	// Finally it looks (recursively) for products related specifically to the parent group of this group
	// If a product belongs to more than one group, related products can only be defined directly on the product!!!
	// When data is found at any level, the recursion stops. 
	// Thus related products are not inherited, if related products are defined for a group further down the 
	// hierarchy or for a specific product.

	if (is_numeric($productid)) {
		// Check for products related to the given productid
		$sql = "select SRP.RELATED_ITEM_ID from SHOP_PRODUCTS SP, SHOP_RELATED_PRODUCTS SRP where SRP.ITEM_ID = SP.ID and SRP.ITEM_ID = '$productid' and SP.DELETED = '0'";
		if ($res = mysql_query($sql)) {
			if (mysql_num_rows($res)>0) {
				// Yes, products found
				$arr_products = array();
				while ($row = mysql_fetch_assoc($res)) {
					$arr_products[] = $row[RELATED_ITEM_ID];
				}
			} else {
				// No products found, find group(s) to which the product belongs
				$gsql = "select 
								SPG.GROUP_ID 
							from 
								SHOP_PRODUCTGROUPS SP, 
								SHOP_PRODUCTS_GROUPS SPG
							where
								SPG.GROUP_ID = SP.ID and
								SPG.PRODUCT_ID = '$productid' and
								SP.DELETED = '0' and
								SP.PUBLISHED = '1'";
				if ($gres = mysql_query($gsql)) {
					if (mysql_num_rows($gres)==1) {
						if ($g = mysql_result($gres,0)) {
							// A single productgroup found, get products 
							$arr_products = return_related_products("",$g);
						} else {
							return false;
						}
					} else {
						return false;
					}
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
	} else {
		// Check for products related to given groupid	
		$sql = "select 
						SRP.RELATED_ITEM_ID 
					from 
						SHOP_PRODUCTGROUPS SP, 
						SHOP_RELATED_PRODUCTS SRP 
					where
						SRP.GROUP_ID = SP.ID and 
						SRP.GROUP_ID = '$groupid' and 
						SP.DELETED = '0' and
						SP.PUBLISHED = '1'";
		if ($res = mysql_query($sql)) {
			if (mysql_num_rows($res)>0) {
				// Yes, products found
				$arr_products = array();
				while ($row = mysql_fetch_assoc($res)) {
					$arr_products[] = $row[RELATED_ITEM_ID];
				}
			} else {
				// No products found, try same with parent id (unless parent id is 0)
				$parentid = returnFieldValue("SHOP_PRODUCTGROUPS", "PARENT_ID", "ID", $groupid);
				if ($parentid == "0") {
					return false;
				} else {
					$arr_products = return_related_products("",$parentid);
				}
			}
		} else {
			return false;
		}
	}
	// Before returning any products, make sure that a product is not related to itself
	if (is_numeric($productid) && is_array($arr_products)) {
		if ($found = array_search($productid, $arr_products)) {
			unset($arr_products[$found]);
			if (count($arr_products)==0) {
				return false;
			}
		}
	}
	// Also, the product can NOT have defined colli
	if (is_numeric($productid) && is_array($arr_products)) {
		foreach ($arr_products as $key => $value) {

			$colsql = "select count(*)
						from SHOP_PRODUCTS_COLLI
						where PRODUCT_ID = $value[PRODUCT_ID]
						and DELETED = '0'";
			$colres = mysql_query($colsql);
			$colcount = mysql_result($colres,0);
			if ($colcount > 0) {
				unset($arr_products[$key]);
			}
		}
	}	
	return $arr_products;
}

function group_related_products($arr_relproducts, $arr_content) {
	// Function to find group association for related products
	// If a related product exists in more than one group, it will be placed 
	// in the group with the highest id (probably the most specific)
//echo "In:<pre>";
//print_r($arr_relproducts);
//echo "</pre>";

	$grp = array();
	foreach ($arr_relproducts as $rp) {
		$sql = "select 
						G.ID as GROUP_ID, 
						G.NAME as GROUP_NAME,
						SP.ID as PRODUCT_ID,
						SP.NAME as PRODUCT_NAME,
						SP.PRODUCT_NUMBER,
						SP.ALT_PRODUCT_NUMBER,
						SP.PRICE
					from 
						SHOP_PRODUCTGROUPS G, 
						SHOP_PRODUCTS_GROUPS SPG,
						SHOP_PRODUCTS SP
					where
						G.ID = SPG.GROUP_ID and
						G.DELETED = '0' and
						G.PUBLISHED = '1' and
						SPG.PRODUCT_ID = SP.ID and
						SP.ID = '$rp' and
						SP.DELETED = '0'
					order by 
						G.ID desc
					limit 1";
		if ($res = mysql_query($sql)) {
			if (mysql_num_rows($res)>0) {
				$row = mysql_fetch_assoc($res);
				if ($_SESSION[LOGGED_IN]) {
					// Check for user specific price
					if ($userprice = returnUserPrice($_SESSION[USERDETAILS][0][ID], $row["PRODUCT_ID"])) {
						$row[PRICE] = $userprice;
						unset($discount_percentage);
						unset($discount_factor);
					} else {
						// No userprice, check for group discount
						$discount_percentage = returnDiscountPercentage($_SESSION[USERDETAILS][0][ID], $row["PRODUCT_ID"], $arr_content);
						$discount_factor = (100-$discount_percentage)/100;
						if ($discount_factor > 0) {
								$row[PRICE] = $row[PRICE] * $discount_factor;
						}
					}
				}							
				$grp[] = array(
								"GROUP_ID" => $row[GROUP_ID],
								"GROUP_NAME" => $row[GROUP_NAME],
								"PRODUCT_ID" => $row[PRODUCT_ID],
								"PRODUCT_NAME" => $row[PRODUCT_NAME],
								"PRODUCT_NUMBER" => $row[PRODUCT_NUMBER],
								"ALT_PRODUCT_NUMBER" => $row[ALT_PRODUCT_NUMBER],
								"PRICE" => $row[PRICE]
							);
			}
		} else {
			return false;
		}
	}
	if (count($grp)>0) {
		// Finally sort the array by group_name asc and product_number asc
		
		// Obtain a list of columns
		foreach ($grp as $key => $row) {
			$group_name[$key]  = $row['GROUP_NAME'];
			$product_number[$key] = $row['PRODUCT_NUMBER'];
		}
		
		// Sort the data with group_name ascending, product_number ascending
		// Add $grp as the last parameter, to sort by the common key
		array_multisort($group_name, SORT_ASC, $product_number, SORT_ASC, $grp);		
//echo "out:<pre>";
//print_r($grp);
//echo "</pre>";

		return $grp;



	} else {
		return false;
	}
}

function returnMD5key($variable) {
	global $md5hash;
	return md5($varible.$md5hash);
}

function checkMD5key($str_md5, $variable) {
	global $md5hash;
	if (md5($varible.$md5hash) == $str_md5) {
		return true;
	} else {
		return false;
	}
}

function add_content_redirect($arr_content) {
	if (is_numeric($arr_content[pageid])) {
		// Showing a page, check for redirect
		$redirect_to_url = returnFieldValue("PAGES", "REDIRECT_TO_URL", "ID", $arr_content[pageid]);
		if ($redirect_to_url != "") {
			$arr_content[redirect_to_url] = $redirect_to_url;
			$arr_content[redirect_key] = returnMD5key($redirect_to_url);
		}
	}
	return $arr_content;
}

function spam_captcha($real_code, $entered_code){
	if ($real_code != $entered_code){
		$captcha_okay = false;
		unset($_SESSION["security_code"]);
	} else {
		$captcha_okay = true;
		unset($_SESSION["security_code"]);					
	}
	return $captcha_okay;
}

?>