<?php 
	$path = gallery_folderpath($arr_content[folderid], $akku=array(), $arr_content); 
	$html .= "<h1><a href='".$arr_content[baseurl]."/index.php?mode=picturearchive'>".cmsTranslate("gallery_galleries")."</a>".($path ? "&nbsp;&raquo;&nbsp;".$path : "")."</h1>";
	$html .= gallery_imagebrowser_folderselect($arr_content);
	if (!$arr_content[folderid]){
		$html .= gallery_imagebrowser_folderdivs($arr_content);
	} else if ($arr_content[folderid] && !$arr_content[imageid]){
		$html .= gallery_thumbnail_index($arr_content[folderid], $arr_content);
	} else if ($arr_content[folderid] && $arr_content[imageid]){
		$html .= gallery_display_image($arr_content);
		update_viewcount($arr_content[imageid]);
	}
	echo $html;
?>

