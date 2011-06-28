<?php 
if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_BLOGMANAGER", true);
include_once("blogmanager_common.inc.php");

if ($_GET[dothis] == "opret") {
	if (!$_GET[id]) {
		$sql = "insert into 
					BLOGS (
						SITE_ID, 
						COMMENTS_ALLOWED,
						COMMENTS_EMAIL,
						APPROVECOMMENTS,
						SYNDICATION_ALLOWED,
						SYNDICATION_SHOWCOMPLETEPOST,
						SYNDICATION_KEY,
						SYNDICATION_SNIPPETLENGTH,
						CREATED_DATE, 
						CHANGED_DATE, 
						AUTHOR_ID, 
						UNFINISHED,
						SHOW_PROFILEIMAGE,
						SHOW_COMPLETEPOST,
						ITEMS_DISPLAYCOUNT
						)
					values (
						$_SESSION[SELECTED_SITE], 
						1,
						1,
						1,
						1,
						1,
						'".md5(time())."',
						5,
						NOW(), 
						NOW(), 
						".$_SESSION[CMS_USER][USER_ID].", 
						1,
						1,
						1,
						10
						)";
		mysql_query($sql);
		$nyt_id = mysql_insert_id();
		header("location: index.php?content_identifier=$_GET[content_identifier]&id=$nyt_id&dothis=opret"); // kald siden igen, nu med ID
		exit;
	}  
}
if ($_POST[dothis] == "update") {
/*
	echo "Update with values:<pre>";
	print_r($_POST);
	echo "</pre>";
*/
	$sql = "update BLOGS
				set
					PUBLISHED = 	'$_POST[blog_published]',
					LANGUAGE_ID =	'$_POST[blog_language]',
					TITLE = 		'$_POST[blog_title]',
					SUBTITLE = 		'$_POST[blog_subtitle]',
					DESCRIPTION = 	'$_POST[blog_description]',
					TEMPLATE_ID = 	'$_POST[blog_template]',
					ITEMS_DISPLAYCOUNT =			'$_POST[blog_items_displaycount]',
					SYNDICATION_SHOWCOMPLETEPOST = 	'$_POST[blog_syndication_showcompletepost]',
					SYNDICATION_SNIPPETLENGTH =		'$_POST[blog_syndication_snippetlength]',
					COMMENTS_ALLOWED = 				'$_POST[blog_comments_allowed]',
					COMMENTS_EMAIL = 				'$_POST[blog_comments_email]',
					COMMENTS_STRIPTAGS = 			'$_POST[blog_comments_striptags]',
					APPROVECOMMENTS = 				'$_POST[blog_approvecomments]',
					SPAMPREVENT_AKISMETKEY = 		'$_POST[blog_spamprevent_akismetkey]',
					SPAMPREVENT_CAPTCHA = 			'$_POST[blog_spamprevent_captcha]',
					SHOW_PROFILEIMAGE = 			'$_POST[blog_show_profileimage]',
					SHOW_COMPLETEPOST = 			'$_POST[blog_show_completepost]',";
					if ($_POST[blog_syndication_newkey] == 1) {
						$sql .= "SYNDICATION_KEY = '".md5(time())."',";
					}
	$sql .= "	SYNDICATION_ALLOWED = 			'$_POST[blog_syndication_allowed]',
					UNFINISHED = 0
				where
					ID = '$_POST[blog_id]'";
	if (mysql_query($sql)) {
		update_feed("BLOGS", $_POST[blog_id]);
		header("location: index.php?content_identifier=$_GET[content_identifier]");
	} else {
		echo "Fejl: Kunne ikke gemme ændringerne!";
		exit;
	}
}

if ($_GET[dothis] == "delete") {
	sletRow($_GET[id], "BLOGS");
	header("location: index.php?content_identifier=$_GET[content_identifier]");
}
?>