<?php 
if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_BLOGS", true);
if (is_numeric($_GET[id])) {
	// Make sure that the user has access to this particular blog
	// First, get blogid
	$valid = false;
	$sql = "select B.ID from BLOGS B, BLOGPOSTS BP where B.ID = BP.BLOG_ID and BP.ID = $_GET[id] and B.DELETED = 0 and BP.DELETED = 0";
	$res = mysql_query($sql);
	if (mysql_num_rows($res)>0) {
		$check_blogid = mysql_result($res,0);
		if (check_data_permission("DATA_CMS_BLOG_PUBLISH", "BLOGS", $check_blogid, "", $_SESSION["CMS_USER"]["USER_ID"])||checkpermission("CMS_BLOGMANAGER")) {
			$valid = true;
			include_once("blogs_common.inc.php");
		} else {
			$valid = false;
		}
	} else {
		$valid = false;
	}
	if (!$valid) {
		echo "Du har ikke tilstrækkelige rettigheder til at redigere denne blog.";
		exit;
	}
}

if ($_GET[dothis] == "opret") {
	if (!$_GET[id]) {
		$sql = "insert into 
					BLOGPOSTS (
						BLOG_ID, 
						COMMENTS_ALLOWED,
						CREATED_DATE, 
						CHANGED_DATE, 
						AUTHOR_ID, 
						UNFINISHED
						)
					values (
						$_GET[blogid], 
						1,
						NOW(), 
						NOW(), 
						".$_SESSION[CMS_USER][USER_ID].", 
						1
						)";
		mysql_query($sql);
		$nyt_id = mysql_insert_id();
		header("location: index.php?content_identifier=$_GET[content_identifier]&dothis=opret&blogid=$_GET[blogid]&id=$nyt_id"); // kald siden igen, nu med ID
		exit;
	}  
}
if ($_POST[dothis] == "update") {
/*
	echo "Update with values:<pre>";
	print_r($_POST);
	echo "</pre>";
*/
	$sql = "update BLOGPOSTS
				set
					PUBLISHED = 					'$_POST[blogentry_published]',
					HEADING = 						'$_POST[blogentry_heading]',
					CONTENTSNIPPET =				'$_POST[blogentry_contentsnippet]',
					CONTENT =						'$_POST[blogentry_content]',
					COMMENTS_ALLOWED = 				'$_POST[blogentry_comments_allowed]',";
					
	if ($_POST[published_date] == "0000-00-00 00:00:00" && $_POST[blogentry_published] == 1) {	
		$sql .=		"PUBLISHED_DATE = 				NOW(),";
	}
	$sql .= "		UNFINISHED = 0
				where
					ID = '$_POST[blogentry_id]'";
	if (mysql_query($sql)) {
		save_tags($_POST[taglist], "BLOGPOSTS", $_POST[blogentry_id], $_SESSION[SELECTED_SITE]);
		update_feed("BLOGS", $_POST[blog_id]);
		sitemap_generator();
		header("location: index.php?content_identifier=$_GET[content_identifier]&filter_blog=$_POST[blog_id]");
	} else {
		echo "Fejl: Kunne ikke gemme ændringerne!";
		exit;
	}
}
if ($_GET[dothis] == "delete") {
	sletRow($_GET[id], "BLOGPOSTS");
	update_feed("BLOGS", $_GET[blogid]);
	header("location: index.php?content_identifier=$_GET[content_identifier]&filter_blog=$_GET[blogid]");
}

?>