<?php
	// REMEMBER TO EXIT HERE WHEN SCRIPT IS NOT IN USE 
	exit;
	/*
		Search and Replace
		CJS, 23/03/2007
		Script to replace link URLs in the body text of pages, news and events. Works for
		<a> and <img> tags, and maybe some other tags as well.

		2008-01-30	-	Fixet så det er nok at inkludere cms_config og der er tilføjet mysql_real_escape_string på det content der skal updates med (MAP).
		
	*/

	include_once($_SERVER[DOCUMENT_ROOT]."/cms_config.inc.php");
	
	/// Change these lines so that they match the TMP-site at DLX and the 
	/// real domain name which the TMP-domain should be replaced with
	$str_search 	= "http://web00XXX.tmp.dlx.dk/";
	$str_replace 	= "http://www.client-domain.dk/";
	
	/// PAGES
	$sql = "
  		select 
			ID, CONTENT 
		from 
			PAGES
		where
			CONTENT like '%".$str_search."%'
		order by ID asc
	";
	$res = mysql_query($sql);
	
	while ($row = mysql_fetch_assoc($res)){
		$content = str_replace($str_search, $str_replace, $row["CONTENT"]);
		$content = mysql_real_escape_string($content);
		$sql = "
			update PAGES
			set CONTENT='$content'
			where ID='$row[ID]'
			limit 1
		";
		mysql_query($sql);
		echo "$sql<hr/>";
	}
	
	/// NEWS
	$sql = "
  		select 
			ID, CONTENT 
		from 
			NEWS
		where
			CONTENT like '%".$str_search."%'
		order by ID asc
	";
	$res = mysql_query($sql);
	
	while ($row = mysql_fetch_assoc($res)){
		$content = str_replace($str_search, $str_replace, $row["CONTENT"]);
		$content = mysql_real_escape_string($content);
		$sql = "
			update NEWS
			set CONTENT='$content'
			where ID='$row[ID]'
			limit 1
		";
		mysql_query($sql);
		echo "$sql<hr/>";
	}

	/// EVENTS
	$sql = "
  		select 
			ID, CONTENT 
		from 
			EVENTS
		where
			CONTENT like '%".$str_search."%'
		order by ID asc
	";
	$res = mysql_query($sql);
	
	while ($row = mysql_fetch_assoc($res)){
		$content = str_replace($str_search, $str_replace, $row["CONTENT"]);
		$content = mysql_real_escape_string($content);
		$sql = "
			update EVENTS
			set CONTENT='$content'
			where ID='$row[ID]'
			limit 1
		";
		mysql_query($sql);
		echo "$sql<hr/>";
	}

?>