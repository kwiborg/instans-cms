<?php
// blogs_common.inc.php
function blogpostForm() {
	global $fckEditorPath;
	$blogentrydata = hentRow($_GET[id], "BLOGPOSTS");
//	print_r($blogentrydata);
	if ($_GET[dothis]=="rediger") {
		$html .= "<h1>Rediger \"$blogentrydata[HEADING]\"</h1><br/>";
	} else {
		$html .= "<h1>Opret indlæg</h1><br/>";
	}
	$html .= "<form action='' method='post' name='form_blogentry' id='form_blogentry'>";	
	$html .= "<ul id='tablist'>
					<li><a href='#' class='current' onClick='return expandcontent(\"sc1\", this)'>Indhold</a></li>
					<li><a href='#' onClick='return expandcontent(\"sc2\", this)'>Kommentarer</a></li>
					<li><a href='#' onClick='return expandcontent(\"sc3\", this)'>Søgeoptimering</a></li>";
	$html .= "</ul>";
	$html .= "<div id='tabcontentcontainer'>";
	$html .= "<div id='sc1' class='tabcontent'>
					<h2>Publiceret</h2>";
	$html .= "<input type='hidden' id='published_date' name='published_date' value='$blogentrydata[PUBLISHED_DATE]' />";
	$html .= createSelectYesNo("blogentry_published", $blogentrydata[PUBLISHED]);
	$html .= "<h2>Overskrift</h2>
				<input id='blogentry_heading' name='blogentry_heading' value='$blogentrydata[HEADING]' class='inputfelt' />
				<h2>Resumé</h2><p>Resumé fremhæves øverst i indlæg og vises desuden i oversigten samt i feeds, hvis dette er valgt for denne blog.</p>";
					$oFCKeditor = new FCKeditor('blogentry_contentsnippet') ;
					$oFCKeditor->BasePath = $fckEditorPath . "/";
					$oFCKeditor->ToolbarSet	= "CMS_BasicEditing";
					$oFCKeditor->Height	= "100";
					$oFCKeditor->Value	= $blogentrydata["CONTENTSNIPPET"];
					$oFCKeditor->Config['CustomConfigurationsPath']	= $fckEditorCustomConfigPath . "/cms_fckconfig.js";
					$html .= $oFCKeditor->CreateHtml() ;
	$html .= "<h2>Brødtekst</h2>";
	$oFCKeditor = new FCKeditor('blogentry_content') ;
	$oFCKeditor->BasePath = $fckEditorPath . "/";
	$oFCKeditor->ToolbarSet	= "CMS_Default";
	$oFCKeditor->Height	= "300";
	$oFCKeditor->Value	= $blogentrydata["CONTENT"];
	$oFCKeditor->Config['CustomConfigurationsPath']	= $fckEditorCustomConfigPath . "/cms_fckconfig.js";
	$html .= $oFCKeditor->CreateHtml() ;
	$html .= "</div>";
	$html .= "<div id='sc2' class='tabcontent'>
				<h2>Tillad kommentarer</h2>";
	$html .= createSelectYesNo("blogentry_comments_allowed", $blogentrydata[COMMENTS_ALLOWED]);

	$html .= "<h2>
					<div style='float:left;'>Kommentarer på dette indlæg</div>
					<div id='ajaxloader_comments'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></div>
				</h2>";
	$html .= return_blogcomments($blogentrydata);
	$html .= "</div>";
	$html .= "<div id='sc3' class='tabcontent'>";
	$html .= "<h2>Tags / nøgleord (kommasepareret)</h2>";
	$html .= build_tag_form($_GET[id], "BLOGPOSTS", $_SESSION[SELECTED_SITE]);
	$html .= build_rewritekey_input("Meningsfuld side-adresse", "BLOGPOSTS", $_GET[id], "this.form.blogentry_heading.value");
	$html .= "</div>";
	$html .= "</div>"; // tabcontentcontainer
	$html .= "<input type='hidden' id='dothis' name='dothis' value='update' />";
	$html .= "<input type='hidden' id='blogentry_id' name='blogentry_id' value='$_GET[id]' />";
	$html .= "<input type='hidden' id='blog_id' name='blog_id' value='$_GET[blogid]' />";
	$html .= "<div class='knapbar'>
				<input type='button' value='Afbryd' onclick='location=\"index.php?content_identifier=blogs\"' />
				<input type='button' value='Gem' onclick='verify()' />
			</div>";
	$html .= "</form>";
	return $html;
}

function return_blogcomments($blogentrydata) {
	$sql = "select
				C.ID, C.COMMENT, C.COMMENTER_NAME, C.COMMENTER_ID, C.COMMENTER_URL, C.COMMENTER_EMAIL, UNIX_TIMESTAMP(C.CREATED_DATE) as TIMESTAMP, C.APPROVED, C.IS_SPAM, BP.AUTHOR_ID
			from
				COMMENTS C
			left join
				BLOGPOSTS BP on BP.AUTHOR_ID=C.COMMENTER_ID and BP.ID=C.REQUEST_ID
			where
				C.TABLENAME='BLOGPOSTS' and C.REQUEST_ID='".$blogentrydata[ID]."' and C.DELETED='0'
			order by
				C.ID desc";
	$res = mysql_query($sql);
	if (mysql_num_rows($res) == 0) {
		return "<p>Endnu ingen kommentarer til dette indlæg.</p>";
	}
	$html .= "<ul>";
//	$script_html = "<script type='text/javascript'>";
	while ($row = mysql_fetch_array($res)) {
		$class = "";
		if ($row[AUTHOR_ID] == $row[COMMENTER_ID]) {
			$class .= " authorcomment";
		}
		if ($row[APPROVED] == 0) {
			$class .= " notapprovedcomment";
			$approvetool = "approve";
		} else {
			$approvetool = "reject";
			$spamtool = "";
		}
		if ($row[IS_SPAM] == 1) {
			$class .= " spamcomment";
			$spamtool = "ham";
			$approvetool = "";
		} else {
			$spamtool = "spam";
		}

		unset($comment_tools);
		if ($approvetool == "approve") {
			$comment_tools[] = "<a href='#' onclick='comment_approve($row[ID]);'>Godkend</a>";
			if (!check_whitelist($row[COMMENTER_EMAIL], "BLOGS", $blogentrydata[BLOG_ID])) {
				$comment_tools[] = "<a href='#' onclick='comment_approve_whitelist($blogentrydata[BLOG_ID], $row[ID]);'>Godkend og tilføj whitelist</a>";
			}
		}
		if ($approvetool == "reject") {
			$comment_tools[] = "<a href='#' onclick='comment_reject($row[ID]);'>Afvis</a>";
			if (check_whitelist($row[COMMENTER_EMAIL], "BLOGS", $blogentrydata[BLOG_ID])) {
				$comment_tools[] = "<a href='#' onclick='comment_reject_whitelist($blogentrydata[BLOG_ID], $row[ID], \"$row[COMMENTER_EMAIL]\");'>Afvis og fjern fra whitelist</a>";
			}

		}
		if ($spamtool == "ham") {
			$comment_tools[] = "<a href='#' onclick='comment_markham($row[ID]);'>Fjern spam markering</a>";
		}
		if ($spamtool == "spam") {
			$comment_tools[] = "<a href='#' onclick='comment_markspam($row[ID]);'>Marker som spam</a>";
		}
		$comment_tools[] = "<a href='#' onclick='comment_makeeditable($row[ID]);'>Rediger kommentar</a>";
		$comment_tools[] = "<a href='#' onclick='comment_delete($row[ID]);'>Slet kommentar</a>";

		$str_comment_tool = implode(" | ", $comment_tools);
		$datetime = date("d-m-Y H:i", $row[TIMESTAMP]);
		$html .= "<li id='comment_$row[ID]' class='blogcomment $class'>
		<div class='blogpost_comment_byline'>".(trim($row[COMMENTER_URL]) != "" && $row[COMMENTER_URL] != "http://" ? "<a href='".$row[COMMENTER_URL]."'>$row[COMMENTER_NAME]</a> ($row[COMMENTER_EMAIL])" : "$row[COMMENTER_NAME] ($row[COMMENTER_EMAIL])").", $datetime
		</div>
		<div id='commenttext_$row[ID]' class='blogcomment'>$row[COMMENT]</div>
		<div id='commenttools_$row[ID]' class='blogpost_commenttools $class'>$str_comment_tool</div>
		</li>";
	}
	$html .= "</ul>";
	return $html;
}

function listBlogposts() {
	$html .= "<h1>Blog indlæg</h1>";
	// First make dropdown of blogs available to current user (only if more than one)
	$sql = "select 
					* 
				from 
					BLOGS 
				where 
					DELETED = 0 and 
					UNFINISHED = 0 and
					SITE_ID in (0,$_SESSION[SELECTED_SITE])
				order by
					TITLE asc";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$html .= "<form action='' method='get' id='form_blogselector' name='form_blogselector'>
					<input type='hidden' id='content_identifier' name='content_identifier' value='blogs' />
					<div class='feltblok_header'>Vis indlæg for bloggen&nbsp;
						<select id='filter_blog' name='filter_blog' class='standard_select' onchange='submit()'>";
		while ($blogs = mysql_fetch_assoc($result)) {
			if (check_data_permission("DATA_CMS_BLOG_PUBLISH", "BLOGS", $blogs[ID], "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_BLOGMANAGER")) {
				// Default to filter by first found template
				if (!$_GET[filter_blog]) {
					$_GET[filter_blog] = $blogs[ID];
				}
				$html .=			"<option value='$blogs[ID]'";
									if ($_GET[filter_blog] == $blogs[ID]) {
										$html .= " selected";
									}
				$html .=			">$blogs[TITLE]</option>";
			}
		}
		$html .= 			"</select>";
	} else {
		$html .= "<p><strong>Du er ikke tildelt rettigheder til nogen blogs</strong></p>";
	}
	$html .= "</div>"; // feltblok_header
	$html .= "<div class='feltblok_wrapper'>";
	if ($_GET[filter_blog]) {
		if (check_data_permission("DATA_CMS_BLOG_PUBLISH", "BLOGS", $_GET[filter_blog], "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_BLOGMANAGER")) {
			// OK, vis indlæg
			// Find samlet antal indlæg på denne blog
			$sql = "select count(*) from BLOGPOSTS where DELETED = 0 and UNFINISHED = 0 and BLOG_ID = '$_GET[filter_blog]'";
			$res = mysql_query($sql);
			$totalcount = mysql_result($res,0);
			if (is_numeric($_GET[offset])) {
				$offset = $_GET[offset];
			} else {
				$offset = 0;
			}
			$showcount = 20;
			$offseturl = "index.php?content_identifier=blogs";
			if (is_numeric($_GET[filter_blog])) {
				$offseturl .= "&amp;filter_blog=$_GET[filter_blog]";
			}
			$offseturl .= "&amp;offset=";
			$ohtml .= "<div style='text-align: right;'>";
			if ($offset > 0) {
				$ohtml .= "<a href='$offseturl";
				$ohtml .= $offset-$showcount;
				$ohtml .= "'>Vis nyere</a>";
				$forrige = "vist";
				
			}
			if ($totalcount > $offset+$showcount) {
				if ($forrige == "vist") {
					$ohtml .= " - ";
				}
				$ohtml .= "<a href='$offseturl";
				$ohtml .= $offset+$showcount;
				$ohtml .= "'>Vis ældre</a>";
			}
			$ohtml .= "</div>";
			$html .= $ohtml; // Bladring

			$sql = "select ID, AUTHOR_ID, PUBLISHED, BLOG_ID, HEADING, UNIX_TIMESTAMP(CREATED_DATE) as POSTTIME from BLOGPOSTS where DELETED = 0 and UNFINISHED = 0 and BLOG_ID = '$_GET[filter_blog]' order by Date(CREATED_DATE) desc, Time(CREATED_DATE) desc limit $offset, $showcount";
	$result = mysql_query($sql);

//	if (mysql_num_rows($result) > 0) {
	if ($totalcount > 0) {
		$html .= 	"<table class='oversigt'>
						<tr class='trtop'>
							<td class='kolonnetitel'>Titel</td>
							<td class='kolonnetitel'>Kommentarer</td>
							<td class='kolonnetitel'>Forfatter</td>
							<td class='kolonnetitel'>Dato</td>
							<td class='kolonnetitel'>Funktioner</td>
						</tr>";
		while ($row = mysql_fetch_array($result)) {
			$html .= "<tr>
					<td>$row[HEADING]";
			if ($row[PUBLISHED] == 0) {
				$html .= " <span class='pageInfo'><em>(Kladde)</em></span>";
			}
//			$html .= "($row[ID])";
			$html .= "</td>
					<td>".comment_count($row[ID]);
			$approvecount = comment_count($row[ID], BLOGPOSTS, "", $approved=0);
			if ($approvecount > 0) {
				$html .= "<span class='awaiting_approval'> ($approvecount ikke godkendt)</span>";
			}
			$html .= "</td>
					<td>".returnAuthorName($row[AUTHOR_ID], 1)."</td>
					<td>".returnNiceDateTime($row[POSTTIME], 1)."</td>
					<td>
						<input type='button' class='lilleknap' value='Slet'  onclick='if (confirm(\"Vil du slette indlægget?\")) location=\"index.php?content_identifier=blogs&amp;dothis=delete&amp;id=$row[ID]&amp;blogid=$row[BLOG_ID]\"' />
						<input type='button' class='lilleknap' value='Rediger' onclick='location=\"index.php?content_identifier=blogs&amp;dothis=rediger&amp;blogid=$row[BLOG_ID]&amp;id=$row[ID]\"' />
					</td>
				</tr>";
		}
		$html .= "</table>";
	} else {
		$html .= "Der er ingen indlæg på denne blog.";
	}
	$html .= $ohtml; // Bladring
	$html .= "</div>";
	$html .= "<div class='knapbar'>
					<input type='button' value='Opret nyt indlæg' onclick='location=\"index.php?content_identifier=blogs&amp;blogid=$_GET[filter_blog]&amp;dothis=opret\"' />
				</div>";


			$html .= "</div>"; // feltblok_wrapper
		} else {
			$html .= "Du har ikke adgang til at redigere denne blog!";
			$html .= "</div>"; // feltblok_wrapper
		}
	}
	return $html;
}

?>