<?php
	$jsVars .= "<script type=\"text/javascript\">\n";
	$jsVars .= "\trtf_filloutname = \"" . cmsTranslate("rtf_filloutname") . "\";\n";
	$jsVars .= "\trtf_filloutemail = \"" . cmsTranslate("rtf_filloutemail") . "\";\n";
	$jsVars .= "\trtf_filloutfriendsname = \"" . cmsTranslate("rtf_filloutfriendsname") . "\";\n";
	$jsVars .= "\trtf_filloutfriendsemail = \"" . cmsTranslate("rtf_filloutfriendsemail") . "\";\n";
	$jsVars .= "\trtf_validmail = \"" . cmsTranslate("rtf_validmail") . "\";";
	$jsVars .= "</script>";
	echo $jsVars;
?>