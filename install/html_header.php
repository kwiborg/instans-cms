<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title>Instans CMS - It's Just For Websites</title>
			<meta http-equiv='content-type' content='text/html;charset=UTF-8' />
			<meta name='generator' content='Instans CMS - installer' />
			<meta name='robots' content='index,follow' />
			<meta name='DC.language' scheme='DCTERMS.RFC1766' content='da' />
			<meta name='description' content='Instans er lukket. Men Instans CMS er nu open source.' />
			<meta name='keywords' content='instans, cms, open source, php, seo, søgeoptimering' />
			<link rel="stylesheet" href="css/instans2008.css" type="text/css" />
			<script src="/includes/javascript/prototype.js" type="text/javascript"></script>
			<script src="/includes/javascript/scriptaculous.js" type="text/javascript"></script>
			<script src="/includes/javascript/common.js" type="text/javascript"></script>
	</head>
    <body>
        <div id="sitewrapper">
            <div id="topwrapper">
                <a href="/"><img id="logo" border="0" src="images/instans_logo_mirrored.gif" alt="Instans CMS" title="Instans CMS"/></a>
                <div id="mainmenu"></div>
                <div id="submenu">
                </div>
            </div>
            <div id="mainwrapper">
                <div id="leftmenu">
                	<ol>
                		<li<?php if ($_GET[step]!=1 && $_GET[step]!="") { echo " class='dimmed'"; }?>>Konfiguration</li>
                		<li<?php if ($_GET[step]!=2) { echo " class='dimmed'"; }?>>Installation / færdig</li>
                	</ol>
                </div>
                <div id="maincontent">
