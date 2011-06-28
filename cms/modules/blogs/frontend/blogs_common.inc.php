<?php
	function return_blog_title($arr_content){
		return $blog_title = returnFieldValue("BLOGS", "TITLE", "ID", $arr_content[blogid]);
	}

	function return_blog_subtitle($arr_content){
		$blog_subtitle = returnFieldValue("BLOGS", "SUBTITLE", "ID", $arr_content[blogid]);
	}

	function return_blog_description($arr_content){
		$blog_description = returnFieldValue("BLOGS", "DESCRIPTION", "ID", $arr_content[blogid]);
	}
		
	function blog_posts_overview($arr_content){
		$offset = (check_method($arr_content["methods"], "offset") ? check_method($arr_content["methods"], "offset") : 0);
		if ($arr_content[blogid] && !check_method($arr_content["methods"], "tag") && is_numeric($offset)){ 
			$display_count = returnFieldValue("BLOGS", "ITEMS_DISPLAYCOUNT", "ID", $arr_content[blogid]);
			if (!$display_count){
				$display_count = 10;
			}
			$sql = "
				select
					BP.ID as BLOGPOST_ID, B.ID as BLOG_ID,
					BP.HEADING, BP.CONTENT, BP.CONTENTSNIPPET, BP.AUTHOR_ID, BP.CREATED_DATE, BP.PUBLISHED_DATE, UNIX_TIMESTAMP(BP.PUBLISHED_DATE) as TSTAMP,
					B.SHOW_PROFILEIMAGE, B.SHOW_COMPLETEPOST, U.FIRSTNAME, U.LASTNAME, U.IMAGE_ID
				from
					BLOGPOSTS BP, BLOGS B, USERS U
				where
					BP.BLOG_ID=B.ID and B.ID='".$arr_content[blogid]."' and
					U.ID=BP.AUTHOR_ID and
					BP.DELETED='0' and BP.UNFINISHED='0' and BP.PUBLISHED='1'
				order by 
					Date(BP.CREATED_DATE) desc, Time(BP.CREATED_DATE) desc, BLOGPOST_ID desc
				".(is_numeric($offset) ? "limit $offset, $display_count" : "")."
			";
		} else if ($arr_content[blogid] && !check_method($arr_content["methods"], "tag") && !is_numeric($offset)){
			$temp = explode("_", $offset);
			if ($temp[0] == "month"){
				$sql = "
					select
						BP.ID as BLOGPOST_ID, B.ID as BLOG_ID,
						BP.HEADING, BP.CONTENT, BP.CONTENTSNIPPET, BP.AUTHOR_ID, BP.CREATED_DATE, 
						B.SHOW_PROFILEIMAGE, B.SHOW_COMPLETEPOST, U.FIRSTNAME, U.LASTNAME, U.IMAGE_ID,
						BP.PUBLISHED_DATE, UNIX_TIMESTAMP(BP.PUBLISHED_DATE) as TSTAMP
					from
						BLOGPOSTS BP, BLOGS B, USERS U
					where
						BP.BLOG_ID=B.ID and B.ID='".$arr_content[blogid]."' and
						U.ID=BP.AUTHOR_ID and
						BP.DELETED='0' and BP.UNFINISHED='0' and BP.PUBLISHED='1' and
						month(BP.PUBLISHED_DATE)='$temp[1]' and year(BP.PUBLISHED_DATE)='$temp[2]'
					order by 
						Date(BP.CREATED_DATE) desc, Time(BP.CREATED_DATE) desc, BLOGPOST_ID desc
				";
			} else {
				unset($arr_content[methods]);
				return blog_posts_overview($arr_content);
			}
		} else if ($arr_content[blogid] && check_method($arr_content["methods"], "tag")) {
			$sql = "
				select
					TR.REQUEST_ID as BLOGPOST_ID, B.ID as BLOG_ID,
					BP.HEADING, BP.CONTENT, BP.CONTENTSNIPPET, BP.AUTHOR_ID, BP.CREATED_DATE, BP.PUBLISHED_DATE,
					B.SHOW_PROFILEIMAGE, B.SHOW_COMPLETEPOST, U.FIRSTNAME, U.LASTNAME, U.IMAGE_ID, UNIX_TIMESTAMP(BP.PUBLISHED_DATE) as TSTAMP
				from
					BLOGPOSTS BP, BLOGS B, USERS U, TAGS T, TAG_REFERENCES TR
				where
					BP.BLOG_ID=B.ID and B.ID='".$arr_content[blogid]."' and
					BP.DELETED='0' and BP.UNFINISHED='0' and BP.PUBLISHED='1' and
					U.ID=BP.AUTHOR_ID and
					T.SITE_ID='".$arr_content[site]."' and T.TAGNAME='".check_method($arr_content["methods"], "tag")."' and
					TR.TAG_ID=T.ID and TR.TABLENAME='BLOGPOSTS' and TR.REQUEST_ID=BP.ID
				order by 
					Date(BP.CREATED_DATE) desc, Time(BP.CREATED_DATE) desc, BLOGPOST_ID desc
			";
		} else if (!$arr_content[blogid] && !check_method($arr_content["methods"], "tag") && $arr_content[mode] == "blogs" ){
			global $blogs_overview_post_count;
			if (!$blogs_overview_post_count) $blogs_overview_post_count = 10;
			$display_count = $blogs_overview_post_count;
			$sql = "
				select
					BP.ID as BLOGPOST_ID, B.ID as BLOG_ID,
					BP.HEADING, BP.CONTENT, BP.CONTENTSNIPPET, BP.AUTHOR_ID, BP.CREATED_DATE, BP.PUBLISHED_DATE,
					B.SHOW_PROFILEIMAGE, B.SHOW_COMPLETEPOST, U.FIRSTNAME, U.LASTNAME, U.IMAGE_ID, UNIX_TIMESTAMP(BP.PUBLISHED_DATE) as TSTAMP
				from
					BLOGPOSTS BP, BLOGS B, USERS U
				where
					BP.BLOG_ID=B.ID and
					U.ID=BP.AUTHOR_ID and
					BP.DELETED='0' and BP.UNFINISHED='0' and BP.PUBLISHED='1'
				order by 
					Date(BP.CREATED_DATE) desc, Time(BP.CREATED_DATE) desc, BLOGPOST_ID desc
				".(is_numeric($offset) ? "limit $offset, $blogs_overview_post_count" : "")."
			";
			$arr_content[allblogs] = true;
		}
		$res = mysql_query($sql);
		// $html .= blog_overview_heading($arr_content);

		$blog_title = returnFieldValue("BLOGS", "TITLE", "ID", $arr_content[blogid]);
		$html .= "<h1>";
		if ($arr_content[mode] == "blogs" && !$arr_content[blogid] && !$arr_content[postid]){
			$html .= "Seneste indlæg i alle blogs";
		} else if (!$offset && !check_method($arr_content["methods"], "tag")){
			$html .= $blog_title . " - seneste $display_count indlæg";
		} else if ($offset && is_numeric($offset) && !check_method($arr_content["methods"], "tag")){
			$html .= $blog_title . " - tidligere indlæg";// (viser indlæg nr. $offset-".($offset+$display_count).")";
		} else if (check_method($arr_content["methods"], "tag")){
			$tagname = returnFieldValue("TAGS", "TAGNAME", "TAGNAME", check_method($arr_content["methods"], "tag"));
			$html .= $blog_title . " - indlæg med nøgleord \"$tagname\"";
		} else if ($offset && !is_numeric($offset) && !check_method($arr_content["methods"], "tag")){
			$temp = explode("_", $offset);
			$html .= $blog_title . " - arkiv for ".cmsTranslate("MonthsUpper", $temp[1])." ".$temp[2];
		} 
		$html .= "</h1>";

		while ($row = mysql_fetch_assoc($res)){
			$pemission_to_read = check_data_permission("DATA_FE_BLOG_READ", "BLOGS", $row[BLOG_ID], "", $_SESSION[USERDETAILS][0][0], "loose");
			if ($pemission_to_read){
				$count++;
				if ($count <= $blogs_overview_post_count || !$arr_content[allblogs]){
					$arr_content[blogid] = $row[BLOG_ID];
					$html .= "
						<div class='blog_overviewitem'>
					";
					if ($row[SHOW_PROFILEIMAGE] && $row[IMAGE_ID] != 0){
						$html .= blogpost_profileimage($row, true,  $arr_content);
					} else {
						$html .= blogpost_profileimage($row, false,  $arr_content);
					}
					$html .= "		
							<div class='blog_overview_content'>
								".($row[SHOW_COMPLETEPOST] == 1 ? $row[CONTENT] : ($row[CONTENTSNIPPET] ? $row[CONTENTSNIPPET] : "<p>".blog_snippet($row[CONTENT])."</p>"))."
							</div>
							".blogpost_bottombar($arr_content, $row[BLOGPOST_ID], false)."
						</div>
					";
				}
			}
		}
		if ($arr_content[blogid] && !check_method($arr_content["methods"], "tag") && is_numeric($offset)){
			$res_count = mysql_num_rows($res);
			if ($res_count >= $display_count){
				$prev_offset = $offset + 1*$display_count;
				$show_prev = true;
			} else {
				$prev_offset = $offset;
				$show_prev = false;
			}
			if ($offset >= $display_count){
				$next_offset = $offset - 1*$display_count;
				$show_next = true;
			} else {
				$next_offset = 0;
				$show_next = false;
			}
			$html .= "
				<div>
			";
			if ($show_prev){
				$html .= "<a href='".$arr_content[baseurl]."/index.php?mode=blogs".(!$arr_content[allblogs] ? "&blogid=".$arr_content[blogid] : "")."&offset=".$prev_offset."'>".cmsTranslate("BlogsPrev")."</a>";
			}
			if ($show_prev && $show_next){
				$html .= cmsTranslate("BlogsPrevNextDivider");
			}
			if ($show_next){
				$html .= "<a href='".$arr_content[baseurl]."/index.php?mode=blogs".(!$arr_content[allblogs] ? "&blogid=".$arr_content[blogid] : "")."&offset=".$next_offset."'>".cmsTranslate("BlogsNext")."</a>";
			}
			$html .= "
				</div>
			";
		}
		return $html;
	}
	
	

	function blogpost_profileimage($row, $showimage, $arr_content){
		$html .= "
			<div class='blog_profilebox'>
				<div class='blog_author_time'>
					<h1><a href='".blogpost_permaurl($arr_content, $row[BLOGPOST_ID])."'>".$row[HEADING]."</a></h1>
					".cmsTranslate("BlogsBy").": $row[FIRSTNAME] $row[LASTNAME]<br/>
					".blogpost_pubdate($row[TSTAMP]).($arr_content[allblogs] ? " i <a href='".blogpost_permaurl($arr_content, false)."'>".returnFieldValue("BLOGS", "TITLE", "ID", $arr_content[blogid])."</a>" : "")."
				</div>
		";
		if ($showimage && !$arr_content[postid]){
			$html .= "
				<div class='blog_author_image'><img alt='$row[FIRSTNAME] $row[LASTNAME]' src='".image_url($row[IMAGE_ID])."' /></div>
			";
		}
		$html .= "
				<div class='clearer'></div>
			</div>
		";
		return $html;
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
	
	function blogpost_permaurl($arr_content, $blogpost_id){
		if ($blogpost_id){
			$url = $arr_content[baseurl]."/index.php?mode=blogs&amp;blogid=".$arr_content[blogid]."&amp;postid=".$blogpost_id;
		} else {
			$url = $arr_content[baseurl]."/index.php?mode=blogs&amp;blogid=".$arr_content[blogid];
		}
		return $url;
	}
	
	function blogpost_tags($arr_content, $blogpost_id){
		$arr_tags = blogpost_tags_array($blogpost_id);
		if ($arr_tags){
			$arr_links = array();
			foreach ($arr_tags as $tag){
				$arr_links[] = "<a href='".$arr_content[baseurl]."/index.php?mode=blogs&amp;blogid=".$arr_content[blogid]."&amp;tag=".$tag[TAGNAME]."'>".$tag[TAGNAME]."</a>";
			}
			return implode(", ", $arr_links);
		} else {
			return cmsTranslate("BlogsNoTags");
		}
	}
	
	function blog_post_complete($arr_content){
		$sql = "
			select
				BP.HEADING, BP.CONTENT, BP.CONTENTSNIPPET, BP.AUTHOR_ID, BP.CREATED_DATE, BP.PUBLISHED_DATE, 
				UNIX_TIMESTAMP(BP.PUBLISHED_DATE) as TSTAMP,
				B.SHOW_PROFILEIMAGE, B.SHOW_COMPLETEPOST, U.FIRSTNAME, U.LASTNAME, U.IMAGE_ID,
				BP.BLOG_ID, BP.ID as BLOGPOST_ID, 
				B.COMMENTS_ALLOWED as BLOG_COMMENTS_ALLOWED, BP.COMMENTS_ALLOWED as POST_COMMENTS_ALLOWED
			from
				BLOGPOSTS BP, BLOGS B, USERS U
			where
				BP.BLOG_ID=B.ID and B.ID='".$arr_content[blogid]."' and
				BP.ID='".$arr_content[postid]."' and
				U.ID=BP.AUTHOR_ID and
				BP.DELETED='0' and BP.UNFINISHED='0' and BP.PUBLISHED='1'
			limit 1
		";
		$res = mysql_query($sql);
		$row = mysql_fetch_assoc($res);

		/// WRONG CAPTCHA CODE
		if ($_POST["blogpost_commentfield_captcha"]){
			$html .= "
				<div class='blog_useralert_red'>
					".cmsTranslate("BlogsCodeNotValid")."
				</div>
			";
		}

/* BEGIN HANDLING OF FUNCTIONS VIA MAIL LINKS */		

		/// APPROVE COMMENT FROM _GET VARS
		if ($_GET["approve"] != ""){
			$temp = explode("_", $_GET["approve"]);
			if ($temp[0] == md5($temp[1]."1nstan5flyvemaskine")){
				$sql = "update COMMENTS set APPROVED='1' where ID='".$temp[1]."' limit 1";
				mysql_query($sql);
				$html .= "
					<div class='blog_useralert_green'>
						".cmsTranslate("BlogsCommentApproved")."
					</div>					
				";
			} else {
				$html .= "
					<div class='blog_useralert_red'>
						".cmsTranslate("BlogsInvalidUrl")."
					</div>					
				";
			}
		}
		
		/// ADD TO WHITELIST
		if ($_GET["addwhitelist"] != ""){
			$temp = explode("_", $_GET["addwhitelist"]);
			if ($temp[0] == md5($temp[1]."1nstan5flyvemaskine")){
				$added = add_whitelist("", "BLOGS", $arr_content[blogid], $temp[1], $arr_content);
				if ($added[1] == true){
					$html .= "
						<div class='blog_useralert_green'>
							".cmsTranslate("BlogsYouHaveAdded")." $added[0] ".cmsTranslate("BlogsToWhitelist")."
						</div>					
					";
				} else {
					$html .= "
						<div class='blog_useralert_red'>
							".cmsTranslate("BlogsTheAddress")." $added[0] ".cmsTranslate("BlogsIsAdded")."
						</div>					
					";
				}
			} else {
				$html .= "
					<div class='blog_useralert_red'>
						".cmsTranslate("BlogsInvalidUrl")."
					</div>					
				";
			}
		}
		
		/// REMOVE FROM WHITELIST
		if ($_GET["removewhitelist"] != ""){
			$temp = explode("_", $_GET["removewhitelist"]);
			if ($temp[0] == md5($temp[1]."1nstan5flyvemaskine")){
				$removed = remove_whitelist("", "BLOGS", $arr_content[blogid], $temp[1], $arr_content);
				if ($removed[1] == true){
					$html .= "
						<div class='blog_useralert_green'>
							".cmsTranslate("BlogsYouHaveRemoved")." $removed[0] ".cmsTranslate("BlogsFromWhitelist")."
						</div>					
					";
				} else {
					$html .= "
						<div class='blog_useralert_red'>
							".cmsTranslate("BlogsTheAddress")." $removed[0] ".cmsTranslate("BlogsIsRemoved")."
						</div>					
					";
				}
			} else {
				$html .= "
					<div class='blog_useralert_red'>
						".cmsTranslate("BlogsInvalidUrl")."
					</div>					
				";
			}
		}

/* END HANDLING OF FUNCTIONS VIA MAIL LINKS */		

		/// OVERSKRIFT OG PROFILBILLEDE
		// $html .= blogpost_profileimage($row, true, $arr_content);
		
		$html .= "<h1>$row[HEADING]</h1>";
		$html .= "
			<p class='blog_singlepost_authortime'>".
				cmsTranslate("BlogsBy").": $row[FIRSTNAME] $row[LASTNAME]<br/>".
				blogpost_pubdate($row[TSTAMP])."
			</p>";
		
		/// SUBHEADING
		if ($row[CONTENTSNIPPET]){
			$html .= "<div class='blogpost_snippet'>".$row[CONTENTSNIPPET]."</div>";
		}

		/// INDHOLD 
		if ($row[CONTENT]){
			$html .= "<div class='blogpost_content'>".$row[CONTENT]."</div>";
		}

		/// TAGS / LINK / COMMENTCOUNT
		$html .= blogpost_bottombar($arr_content, $arr_content[postid], $row[BLOG_ID]);
		
		/// COMMENTS
		$html .= blogpost_comments($arr_content);

		/// COMMENT FORM
		if (check_data_permission("DATA_FE_BLOG_COMMENT", "BLOGS", $arr_content[blogid], "", $_SESSION[USERDETAILS][0][0], "loose")){
			if ($row[BLOG_COMMENTS_ALLOWED] && $row[POST_COMMENTS_ALLOWED]){
				$html .= blogpost_comments_form($arr_content, $row[COMMENTS_ALLOWED]);
			} else {
				$html .= blogpost_comments_closed();
			}
		} else {
			$html .= "<div id='blogpost_comments_closed'>Du har ikke rettigheder til at kommentere på denne blog.</div>";
		}
		$html .= "<a name='comments_end'></a>";
		return $html;
	}
	
	function blogpost_pubdate($timestamp){
		return cmsTranslate("BlogsPublished").": ".date("j", $timestamp).". ".cmsTranslate("MonthsLower", 1*date("m", $timestamp))." ".date("Y", $timestamp)." ".cmsTranslate("BlogsKl")." ".date("H:i", $timestamp);
	}
	
	function blogpost_comments_closed(){
		$html .= "
			<div id='blogpost_comments_closed'>
				".cmsTranslate("BlogsCommentsClosed")."
			</div>
		";
		return $html;
	}
	

	function blogpost_bottombar($arr_content, $blogpost_id, $blog_id){
		$html .= "
			<div class='blog_overviewitem_bottombar'>
				";
		if ($blog_id){
			$html .= "<a href='".$arr_content[baseurl]."/index.php?mode=blogs&amp;blogid=".$blog_id."'>".cmsTranslate("BlogsOverview")."</a><span class='divider'>|</span>".cmsTranslate("BlogsTags").": ".blogpost_tags($arr_content,  $blogpost_id)."<span class='divider'>|</span>".cmsTranslate("BlogsComments")." (".comment_count($blogpost_id).")";
		} else {
			$html .= "<a href='".blogpost_permaurl($arr_content, $blogpost_id)."'>".cmsTranslate("BlogsFullPost")."</a><span class='divider'>|</span>".cmsTranslate("BlogsTags").": ".blogpost_tags($arr_content,  $blogpost_id)."<span class='divider'>|</span><a href='".blogpost_permaurl($arr_content, $blogpost_id)."#comments'>".cmsTranslate("BlogsComments")."</a> (".comment_count($blogpost_id).")";
		}
		$html .= "
			</div>
		";
		return $html;
	}

	function blogpost_comments($arr_content, $return_count=false){
		$sql = "
			select 
				C.COMMENT, C.COMMENTER_NAME, C.COMMENTER_ID,
				C.COMMENTER_URL, UNIX_TIMESTAMP(C.CREATED_DATE) as TIMESTAMP, 
				BP.AUTHOR_ID
			from
				COMMENTS C
			left join
				BLOGPOSTS BP on BP.AUTHOR_ID=C.COMMENTER_ID and BP.ID=C.REQUEST_ID
			where
				C.TABLENAME='BLOGPOSTS' and C.REQUEST_ID='".$arr_content[postid]."' and
				C.IS_SPAM='0' and C.DELETED='0' and C.APPROVED='1'
			order by 
				C.ID asc
		";
		$res = mysql_query($sql);
		if ($return_count){
			return mysql_num_rows($res);
		}
		$html .= "<a name='comments'></a>";
		$html .= "<div id='blogpost_comments_box'>";
		$html .= "<h1>".cmsTranslate("BlogsComments")."</h1>";
		if (mysql_num_rows($res) == 0){
			$html .= cmsTranslate("BlogsNoCommentsYet");
		} else {
			$html .= "<ol>";
			while ($row = mysql_fetch_assoc($res)){
				$count++;
				$datetime = date("d-m-Y H:i", $row[TIMESTAMP]);
				$html .= "
					<li ".($row[AUTHOR_ID] == $row[COMMENTER_ID] ? "class='authorcomment'" : "").">
						<div class='blogpost_comment_byline'>
							<span class='blogpost_commentcount'>$count.&nbsp;</span>
							".(trim($row[COMMENTER_URL]) != "" && $row[COMMENTER_URL] != "http://" ? "<a href='".$row[COMMENTER_URL]."'>$row[COMMENTER_NAME]</a>" : $row[COMMENTER_NAME]).", $datetime
						</div>
						".nl2br($row[COMMENT])."
					</li>
				";
			}
			$html .= "</ol>";
		}
		$html .= "</div>";
		
		/// SHOW USER MESSAGE ABOUT NEW COMMENT (IF SPAM OR NON-APPROVED)
		if ($_GET[c]){
			$temp = explode("_", $_GET[c]);
			if ($temp[0] == md5($temp[1]."1nstan5flyvemaskine")){
				$sql = "
					select APPROVED, IS_SPAM from COMMENTS where ID='".$temp[1]."' limit 1
				";
				$res = mysql_query($sql);
				$row = mysql_fetch_assoc($res);
				if ($row[APPROVED] == 0 || $row[IS_SPAM] == 1){
					$html .= "
						<div class='blog_useralert_green'>
							".cmsTranslate("BlogsAwaiting")."
						</div>					
					";
				}
			}
		}
		return $html;
	}
	
	function blogpost_comments_form($arr_content){
		$spamprevent_captcha = returnFieldValue("BLOGS", "SPAMPREVENT_CAPTCHA", "ID", $arr_content[blogid]);
		$strip_tags = returnFieldValue("BLOGS", "COMMENTS_STRIPTAGS", "ID", $arr_content[blogid]);
		if ($_SESSION[LOGGED_IN] === 1){
			$userid 	= $_SESSION[USERDETAILS][0][0]; 
			$firstname 	= $_SESSION[USERDETAILS][0][1];
			$lastname 	= $_SESSION[USERDETAILS][0][2];
			$name = $firstname." ".$lastname;
			$email 		= $_SESSION[USERDETAILS][0][8];
			$url 		= ""; // IMPLEMENT URL FIELD ON USERS TABLE FIRST
			$ghosted 	= "readonly";
		}
		if ($_COOKIE["instans_cms_blogs"]){
			$savedata = true;
			$temp = explode("|||||", $_COOKIE["instans_cms_blogs"]);
			if (!$_SESSION[LOGGED_IN]){
				$name = $temp[0];
			}
			$email 	= $temp[1];
			$url 	= $temp[2];
		}
		if ($_POST){
			$name = $_POST[blogpost_commentfield_name];
			$email = $_POST[blogpost_commentfield_email];
			$url = $_POST[blogpost_commentfield_url];
			$comment = $_POST[blogpost_commentfield_comment];
		}
		if (!$url){
			$url = "http://";
		}
		$html .= "
			<a name='commentform'></a>
			<div id='blogpost_commentform_box'>
				<h1>".cmsTranslate("BlogsAddComment")."</h1>
				<form id='blogpost_commentform_".$arr_content[postid]."' class='blogpost_commentform' action='".$arr_content[baseurl]."/index.php?mode=blogs&amp;blogid=".$arr_content[blogid]."&postid=".$arr_content[postid]."' method='post'>
					<input type='hidden' name='blogid' value='".$arr_content[blogid]."' />
					<input type='hidden' name='postid' value='".$arr_content[postid]."' />
					<input type='hidden' name='userid' value='".$userid."' />
					<input type='hidden' name='dothis' value='save_comment' />
					<input type='hidden' name='savecomment' value='".md5($arr_content[blogid].$arr_content[postid]."1nstan5flyvemaskine")."' />					
					<div class='blogpost_commentform_label'>".cmsTranslate("BlogsCName").": (*)</div>
					<div class='blogpost_commentform_field'><input $ghosted value='".$name."' type='text' name='blogpost_commentfield_name' id='blogpost_commentfield_name' class='blogpost_commentfield' value='' /></div>
					<div class='blogpost_commentform_clear'></div>
					<div class='blogpost_commentform_label'>".cmsTranslate("BlogsCMail").": (*)</div>
					<div class='blogpost_commentform_field'><input value='".$email."' type='text' name='blogpost_commentfield_email' id='blogpost_commentfield_email' class='blogpost_commentfield' /></div>
					<div class='blogpost_commentform_clear'></div>
					<div class='blogpost_commentform_label'>".cmsTranslate("BlogsCUrl").":</div>
					<div class='blogpost_commentform_field'><input value='".$url."' type='text' name='blogpost_commentfield_url' id='blogpost_commentfield_url' class='blogpost_commentfield' /></div>
					<div class='blogpost_commentform_clear'></div>
					<div class='blogpost_commentform_label'>".cmsTranslate("BlogsCComment").": (*)</div>
					<div class='blogpost_commentform_field'>
						<textarea name='blogpost_commentfield_comment' id='blogpost_commentfield_comment' class='blogpost_commentfield'>".$comment."</textarea>
					</div>
					<div class='blogpost_commentform_clear'></div>
		";
		if ($spamprevent_captcha == 1){
			$html .= return_captcha_form();
		}
		// if (!$_SESSION[LOGGED_IN]){
		$html .= "
				<div class='blogpost_commentform_label'>".cmsTranslate("BlogsRemember")."</div>
				<div class='blogpost_commentform_field'><input type='checkbox' ".($savedata ? "checked" : "")." name='save_cookie' id='save_cookie' />&nbsp;".cmsTranslate("BlogsYesRemember")."</div>
		";
		// }
		$html .= "
					<div class='blogpost_commentform_clear'></div>
					<div class='blogpost_commentform_buttons'>
						<input type='submit' value='".cmsTranslate("BlogsSaveComment")."' class='blogpost_commentfield' onclick='return verify_comment()' />
					</div>
				</form>
			</div>		
		";
		return $html;
	}
	
	function return_captcha_form(){
		$html .= "
				<div class='blogpost_commentform_label'>".cmsTranslate("BlogsSpamPrevention").": (*)</div>
				<div class='blogpost_commentform_field'>
					".cmsTranslate("BlogsSpamExplanation")."
					<input type='text' name='blogpost_commentfield_captcha' id='blogpost_commentfield_captcha' class='blogpost_commentfield' />&nbsp;
					<img alt='' class='captcha' src='/cms/scripts/captcha/CaptchaSecurityImages.php?width=100&height=30&characters=5' />
				</div>
				<div class='blogpost_commentform_clear'></div>
		";
		return $html;
	}
	
	function spamcheck_akismet($akismet_apikey, $blogid, $POSTVARS, $arr_content){
		if ($akismet_apikey != ""){
			$vars = array();
			foreach ($_SERVER as $key => $val){
				$vars[$key] = $val; 
			}
			$vars["user_ip"]           		= $_SERVER["REMOTE_ADDR"];
			$vars["user_agent"]        		= $_SERVER["HTTP_USER_AGENT"];
			$vars["comment_content"]   		= $POSTVARS["blogpost_commentfield_comment"];
			$vars["comment_author"]			= $POSTVARS["blogpost_commentfield_name"];
			$vars["comment_author_email"]	= $POSTVARS["blogpost_commentfield_email"];
			if (trim($POSTVARS["blogpost_commentfield_url"]) != "http://" && trim($POSTVARS["blogpost_commentfield_url"]) != ""){
				$vars["comment_author_url"] = $POSTVARS["blogpost_commentfield_url"];
			}
		 	$akismet = new MicroAkismet("$akismet_apikey", "$arr_content[baseurl]", "Instans CMS");
			if ($akismet->check($vars)){
				return 1;
			} else {
				return 0;
			}
		}
	}	

	function comment_mail($blogid, $postid, $is_spam, $comment, $name, $email, $url, $sendto_email, 
								$sendto_name, $approve_comments,  $whitelisted, $comment_id, $arr_content){
		$blog_title = returnFieldValue("BLOGS", "TITLE", "ID", $blogid);
		$post_title = returnFieldValue("BLOGPOSTS", "HEADING", "ID", $postid);
		$html .= "
			<p>
				".cmsTranslate("BlogsMailNewComment")." <a href='".$arr_content[baseurl]."/index.php?mode=blogs&blogid=$blogid'>$blog_title</a> 
				".cmsTranslate("BlogsMailOnPost")." <a href='".$arr_content[baseurl]."/index.php?mode=blogs&blogid=$blogid&postid=$postid'>$post_title</a>:
			</p>
			<p>
				".cmsTranslate("BlogsCName").": $name<br/>
				".cmsTranslate("BlogsCMail").": $email<br/>
				".cmsTranslate("BlogsURL").": ".($url && $url != "http://" ? $url : cmsTranslate("BlogsNotStated"))."
			</p>
			<p>
				$comment
			</p>
		";
		if ($approve_comments == 1 && !$whitelisted){
			$approve_md5 = md5($comment_id."1nstan5flyvemaskine");
			$html .= "
				<p>
					".cmsTranslate("BlogsMailMustBeApproved")." <a href='".$arr_content[baseurl]."/index.php?mode=blogs&blogid=".$blogid."&postid=".$postid."&approve=".$approve_md5."_".$comment_id."'>".cmsTranslate("BlogsMailClickToApprove")."</a>. ".cmsTranslate("BlogsMailWillBeShown")."
				</p>
			";
		} else if ($approve_comments == 1 && $whitelisted){
			$html .= "
				<p>
					".cmsTranslate("BlogsMailAutoApproved")." 
				</p>
			";
		}
		if (!$whitelisted){
			$add_md5 = md5($comment_id."1nstan5flyvemaskine")."_".$comment_id;
			$html .= "
				<p>
					".cmsTranslate("BlogsMailFutureApprove")." $email: <a href='".$arr_content[baseurl]."/index.php?mode=blogs&blogid=".$blogid."&postid=".$postid."&addwhitelist=".$add_md5."'>".cmsTranslate("BlogsMailClickToAdd")." ".$email." ".cmsTranslate("BlogsMailToWhitelist")."</a>. 
				</p>
			";
		} else {
			$remove_md5 = md5($whitelisted."1nstan5flyvemaskine")."_".$whitelisted;
			$html .= "
				<p>
					<a href='".$arr_content[baseurl]."/index.php?mode=blogs&blogid=".$blogid."&postid=".$postid."&removewhitelist=".$remove_md5."'>".cmsTranslate("BlogsMailClickToRemove")." ".$email." ".cmsTranslate("BlogsMailFromWhitelist")."</a>. 
				</p>
			";
		}
		$sender_name = $blog_title." [".$name."]";
		$subject = "".cmsTranslate("BlogsMailSubject")." \"".$post_title."\"";
        $mail = NULL;
        $mail = new htmlMimeMail();

		// Change to UFT-8 encoding
		$mail->setTextCharset("UTF-8");
		$mail->setHTMLCharset("UTF-8");
		$mail->setHeadCharset("UTF-8"); 

		$mail->setTextWrap(60);
        $mail->setHtml($html, "TEXTVERSION");
		$mail->setReturnPath("$email");		
		$mail->setFrom('"'.$sender_name.'" <'.$email.'>');
		$mail->setSubject($subject);
		$mail->setHeader('Reply-To', $email);
		$result = $mail->send(array("'$sendto_name' <".$sendto_email.">"), 'mail');
	}
	
	function blogtemplate_archive_months($blogid, $arr_content){
		$sql = "
			select 
				month(PUBLISHED_DATE) as MAANED, year(PUBLISHED_DATE) as AAR
			from 
				BLOGPOSTS
			where 
				BLOG_ID='$blogid' and DELETED='0' and UNFINISHED='0'
			group by 
				AAR, MAANED
			order by 
				AAR desc, MAANED desc
		";
		$res = mysql_query($sql);
		$offset = check_method($arr_content["methods"], "offset");
		$temp = explode("_", $offset);
		$html .= "<ul id='blogs_archive_months'>";
		while ($row = mysql_fetch_assoc($res)){
			if ($temp[1] == $row[MAANED] && $temp[2] == $row[AAR]){
				$class = "class='selected'";
			} else {
				$class = "";
			}
			$html .= "<li><a $class href='".$arr_content[baseurl]."/index.php?mode=blogs&amp;blogid=$blogid&offset=month_$row[MAANED]_$row[AAR]'>".cmsTranslate("MonthsUpper", $row[MAANED])." ".$row[AAR]."</a></li>";
		}
		$html .= "</ul>";
		return $html;
	}
	
	function blogtemplate_archive_tags($blogid, $arr_content){
		global $blogs_show_tags_used_once;
		$sql = "
			select 
				T.ID as TAGID, T.TAGNAME, COUNT(T.ID) as ANTAL
			from 
				TAGS T, TAG_REFERENCES TR, BLOGPOSTS BP 
			where 
				TR.TABLENAME='BLOGPOSTS' and TR.REQUEST_ID=BP.ID and BP.BLOG_ID='$blogid' and
				T.ID=TR.TAG_ID
			group by 
				T.ID
			order by 
				T.TAGNAME asc
		";
		$res = mysql_query($sql);
		if (mysql_num_rows($res)){
			$html .= "<ul id='blogs_archive_tags'>";
			while ($row = mysql_fetch_assoc($res)){
				if ($blogs_show_tags_used_once || $row[ANTAL] > 1){
					if (check_method($arr_content["methods"], "tag") == $row[TAGNAME]){
						$class = "class='selected'";
					} else {
						$class = "";
					}
					$html .= "<li><a $class href='".$arr_content[baseurl]."/index.php?mode=blogs&amp;blogid=$blogid&amp;tag=$row[TAGNAME]'>".$row[TAGNAME]."</a> (".$row[ANTAL].")</li>";
				}
			}
			$html .= "</ul>";
		} else {
			$html .= "<p>Endnu ingen tags.</p>";
		}
		return $html;	
	}

	function blogtemplate_latest_commented($blogid, $arr_content){
		global $blogs_lastcommentedbox_count;
		if (!$blogs_lastcommentedbox_count) $blogs_lastcommentedbox_count = 5;
		$sql = "
			select 
				C.ID, C.REQUEST_ID as POSTID, UNIX_TIMESTAMP(C.CREATED_DATE) as TSTAMP, C.COMMENTER_NAME,
				BP.HEADING, C.COMMENT
			from 
				COMMENTS C, BLOGPOSTS BP
			where 
				C.DELETED='0' and C.APPROVED='1' and C.IS_SPAM='0' and C.REQUEST_ID=BP.ID and
				BP.BLOG_ID='$blogid' and BP.DELETED='0' and BP.UNFINISHED='0' and BP.PUBLISHED='1'
			group by
				BP.ID			
			order by 
				TSTAMP desc
			limit $blogs_lastcommentedbox_count
		";
		$res = mysql_query($sql);
		if (mysql_num_rows($res)){
			$html .= "<ul id='blogs_archive_latestcommented'>";
			while ($row = mysql_fetch_assoc($res)){
				if ($temp[1] == $row[MAANED] && $temp[2] == $row[AAR]){
					$class = "class='selected'";
				} else {
					$class = "";
				}
				$html .= "<li><a title='".$row[COMMENTER_NAME]." sagde:\n".$row[COMMENT]."' $class href='".$arr_content[baseurl]."/index.php?mode=blogs&amp;blogid=$blogid&amp;postid=$row[POSTID]#comments_end'>".$row[HEADING]."</a> (".date("j/n H:i", $row[TSTAMP])." af $row[COMMENTER_NAME])</li>";
			}
			$html .= "</ul>";
		} else {
			$html .= "<p>Endnu ingen kommentarer.</p>";
		}
		return $html;	
	}
	
	function blogtemplate_aboutblog_box($blogid, $arr_content){
		$sql = "
			select
				B.TITLE, B.SUBTITLE, B.DESCRIPTION
			from 
				BLOGS B
			where 
				B.DELETED='0' and B.UNFINISHED='0' and B.PUBLISHED='1' and
				B.ID='$blogid'
			limit 1
		";
		$res = mysql_query($sql);
		$row = mysql_fetch_assoc($res);
		$html .= "
			<div id='blog_aboutbox' class='marginBox'>
				<div class='boxTitle'>Om ".$row[TITLE]."</div>
				<p>$row[DESCRIPTION]</p>
				".rss_link($arr_content)."
			</div>
		";
		return $html;				
	}
	
	function blogtemplate_aboutauthor_box($blogid, $arr_content){
		if (!$arr_content[postid]){
			return "";
		}
		$sql = "
			select
				BP.AUTHOR_ID, U.FIRSTNAME, U.LASTNAME, U.IMAGE_ID, U.CV
			from 
				BLOGPOSTS BP, USERS U
			where 
				BP.DELETED='0' and BP.UNFINISHED='0' and BP.PUBLISHED='1' and
				BP.ID='$arr_content[postid]' and U.ID=BP.AUTHOR_ID
			limit 1
		";
		$res = mysql_query($sql);
		$row = mysql_fetch_assoc($res);
		if (trim($row[CV]) == ""){
			$row[CV] = "Der er ikke yderligere oplysninger om ".$row[FIRSTNAME]." ".$row[LASTNAME].".";
		}
		if ($row[IMAGE_ID]){
			$imagehtml = "
				<div class='blog_authorbox_image'>
					<img src='".image_url($row[IMAGE_ID])."' alt='$row[FIRSTNAME] $row[LASTNAME]'
				/></div>
			";
		}
		$html .= "
			<div id='blog_authorbox' class='marginBox'>
				<div class='boxTitle'>Om ".$row[FIRSTNAME]." ".$row[LASTNAME]."</div>
				$imagehtml				
				<p>$row[CV]</p>
			</div>
		";
		return $html;				
	}
	
	function rss_link($arr_content){
		$rssurl = $arr_content[baseurl]."/feeds/blog_".$arr_content[blogid]."_".returnFieldValue("BLOGS", "SYNDICATION_KEY", "ID", $arr_content[blogid]);
		$html .= "
			<p>
				<a href='$rssurl'><img src='$arr_content[baseurl]/includes/images/rss.gif' alt='RSS-feed' border='0' align='absmiddle' /></a>&nbsp;<a href='$rssurl' title='RSS-feed'>Abonner med RSS</a>
			</p>
		";
		return $html;
	}

?>