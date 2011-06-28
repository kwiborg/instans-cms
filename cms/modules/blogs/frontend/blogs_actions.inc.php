<?php
	include($_SERVER[DOCUMENT_ROOT]."/cms/modules/blogs/frontend/blogs_common.inc.php");
	include($_SERVER[DOCUMENT_ROOT]."/cms/scripts/microakismet/class.microakismet.inc.php");
//	include($_SERVER[DOCUMENT_ROOT]."/cms/scripts/html_mime_mail/htmlMimeMail.php");
	
	/// SAVE COMMENT
	if ($_POST[savecomment] && $_POST[savecomment] == md5($_POST[blogid].$_POST[postid]."1nstan5flyvemaskine")){
		if ($_POST[blogpost_commentfield_name] && $_POST[blogpost_commentfield_email] && $_POST[blogpost_commentfield_comment] &&
			$_POST[postid] && $_POST[blogid]
		){
			$approve_comments 	= returnFieldValue("BLOGS", "APPROVECOMMENTS", "ID", $_POST[blogid]);		
			$email_comments 	= returnFieldValue("BLOGS", "COMMENTS_EMAIL", "ID", $_POST[blogid]);		

			/// SPAM AKISMET CHECK
			$akismet_apikey = trim(returnFieldValue("BLOGS", "SPAMPREVENT_AKISMETKEY", "ID", $_POST[blogid]));
			$is_spam = spamcheck_akismet($akismet_apikey, $_POST[blogid], $_POST, $arr_content);
	
			/// SPAM CAPTCHA CHECK
			if ($_POST["blogpost_commentfield_captcha"]){
				$captcha_okay = spam_captcha($_SESSION["security_code"], $_POST["blogpost_commentfield_captcha"]);
			} else {
				$captcha_okay = true;
			}
			
			/// WHITELIST CHECK
			$whitelisted = check_whitelist($_POST[blogpost_commentfield_email], "BLOGS", $_POST[blogid]);
			
			/// APPROVE COMMENTS
			if ($approve_comments == 0){
				$approved = 1;
			} else if ($approve_comments == 1){
				if ($whitelisted){
					$approved = 1;
				} else {
					$approved = 0;
				}
			}
			
			if ($captcha_okay){
				$created_date = date("Y-m-d H:i:s");
				/// STRIP TAGS FROM NAME, EMAIL, URL - KEEP ALLOWED TAGS IN COMMENT (IF ANY)
				$strip_tags = trim(returnFieldValue("BLOGS", "COMMENTS_STRIPTAGS", "ID", $_POST[blogid]));
				if ($strip_tags != ""){					
					$_POST[blogpost_commentfield_comment] = strip_tags($_POST[blogpost_commentfield_comment], $strip_tags);
				} else {
					$_POST[blogpost_commentfield_comment] = strip_tags($_POST[blogpost_commentfield_comment]);
				}
				// $_POST[blogpost_commentfield_comment] = nl2br($_POST[blogpost_commentfield_comment]);
				$_POST[blogpost_commentfield_name] = strip_tags($_POST[blogpost_commentfield_name]);
				$_POST[blogpost_commentfield_email] = strip_tags($_POST[blogpost_commentfield_email]);
				$_POST[blogpost_commentfield_url] = strip_tags($_POST[blogpost_commentfield_url]);

				$sql = "
					insert into COMMENTS (
						TABLENAME, REQUEST_ID, 
						COMMENT, COMMENTER_ID, 
						COMMENTER_NAME, COMMENTER_EMAIL, COMMENTER_URL,
						IS_SPAM, CREATED_DATE, APPROVED
					) 
					values 
					(
						'BLOGPOSTS', '".$_POST[postid]."',  
						'".$_POST[blogpost_commentfield_comment]."', '".$_POST[userid]."', 
						'".$_POST[blogpost_commentfield_name]."', '".$_POST[blogpost_commentfield_email]."', '".$_POST[blogpost_commentfield_url]."',
						'".$is_spam."', '".$created_date."', '".$approved."'
					)
				";
				mysql_query($sql);
				$comment_id = mysql_insert_id();
				$md5 = md5($comment_id."1nstan5flyvemaskine")."_".$comment_id;

				/// SEND MAIL TO AUTHOR
				if ($email_comments){
					$author_id 		= returnFieldValue("BLOGPOSTS", "AUTHOR_ID", "ID", $_POST[postid]);
					$author_email 	= returnFieldValue("USERS", "EMAIL", "ID", $author_id);
					$author_name 	= returnFieldValue("USERS", "FIRSTNAME", "ID", $author_id)." ".returnFieldValue("USERS", "LASTNAME", "ID", $author_id);
					comment_mail(
						$_POST[blogid], $_POST[postid], $is_spam, $_POST[blogpost_commentfield_comment], $_POST[blogpost_commentfield_name], 
						$_POST[blogpost_commentfield_email], $_POST[blogpost_commentfield_url], $author_email, $author_name, 
						$approve_comments, $whitelisted, $comment_id, $arr_content
					);
				}		
				
				/// SET COOKIE IF CHECKED
				if ($_POST[save_cookie]){
					global $cookieDomain;
					$sep = "|||||";
					$cookie_exp = time()+60*60*24*30*365;
					$cookie_data =  $_POST[blogpost_commentfield_name] . $sep . $_POST[blogpost_commentfield_email] . $sep . $_POST[blogpost_commentfield_url];
					$cookie_path = "/";
					$cookie_domain = $cookieDomain;
					setcookie("instans_cms_blogs", $cookie_data, $cookie_exp, $cookie_path, $cookie_domain);
				} else {
					setcookie("instans_cms_blogs", "", time() - 3600, $cookie_path, $cookie_domain);
				}
				header("location: $arr_content[baseurl]/index.php?mode=blogs&blogid=".$_POST[blogid]."&postid=".$_POST[postid]."&c=".$md5."#commentform");
			}
		} else {
			/// MANGLER UDFYLDTE FELTER - BURDE VÆRE FANGET AF JS - DO NOTHING
		}
	}
	
?>