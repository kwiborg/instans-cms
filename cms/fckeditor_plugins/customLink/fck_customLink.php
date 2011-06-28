<?php 
include_once("../../../cms_config.inc.php");
include_once($cmsAbsoluteServerPath . "/common.inc.php");

checkLoggedIn();
$current_site_id = $_SESSION[SELECTED_SITE];
$current_basepath = returnBASE_URL($current_site_id);

$current_basepath_noprotocol = substr($current_basepath,7);
$current_sitepath = $current_basepath.returnSITE_PATH($current_site_id);
$current_sitepath_noprotocol = $current_basepath_noprotocol.returnSITE_PATH($current_site_id);

?>
<!--
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2005 Frederico Caldeira Knabben
 * 
 * Licensed under the terms of the GNU Lesser General Public License:
 * 		http://www.opensource.org/licenses/lgpl-license.php
 * 
 * For further information visit:
 * 		http://www.fckeditor.net/
 * 
 * File Name: fck_link.html
 * 	Link dialog window.
 * 
 * File Authors:
 * 		Frederico Caldeira Knabben (fredck@fckeditor.net)
-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Link Properties</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="robots" content="noindex, nofollow" />
		<script src="<?php echo $cmsURL; ?>/fckeditor/editor/dialog/common/fck_dialog_common.js" type="text/javascript"></script>
		<script src="<?php echo $cmsURL; ?>/fckeditor/editor/dialog/fck_link/fck_link.js" type="text/javascript"></script>
		<script type="text/javascript">
		function onLinkSelectorChange(obj) {
			if (obj.value > 0) {
				document.getElementById('cmbLinkProtocol').value = '';
				document.getElementById('txtUrl').value = '/index.php?pageid=' + obj.value;
			}
		}
		function onNewsSelectorChange(obj) {
			if (obj.value > 0) {
				document.getElementById('cmbLinkProtocol').value = '';
				document.getElementById('txtUrl').value = '/index.php?mode=news&newsid=' + obj.value}
		}
		function onEventSelectorChange(obj) {
			if (obj.value > 0) {
				document.getElementById('cmbLinkProtocol').value = '';
				document.getElementById('txtUrl').value = '/index.php?mode=events&eventid=' + obj.value}
		}
		function onFileSelectorChange(obj) {
			if (obj.value != -99) {
				document.getElementById('cmbLinkProtocol').value = '';
				document.getElementById('txtUrl').value = '/' + obj.value}
		}
		</script>
	</head>
	<body scroll="no" style="OVERFLOW: hidden">
		<div id="divInfo" style="DISPLAY: none">
			<span fckLang="DlgLnkLocallink">Lokalt link</span><br />
			<select name="linkselector" onchange="onLinkSelectorChange(this);"> 
			 <?php 
				echo buildPagesDropdown("",0,0);
			 ?>
			</select>
			<br /><br />
			<span fckLang="DlgLnkLocalnews">Lokal nyhed</span><br />
			<select name="newsselector" onchange="onNewsSelectorChange(this);"> 
			 <?php 
	 		  echo newsSelector();			  
			 ?>
			</select>
			<br /><br />
			<span fckLang="DlgLnkLocalevent">Lokalt arrangement</span><br />
			<select name="eventsselector" onchange="onEventSelectorChange(this);"> 
			 <?php 
	 		  echo eventsSelector();			  
			 ?>
			</select>
			<br />
			<br />
			<span fckLang="DlgLnkLocalfile">Lokal fil</span><br />
			<select name="fileselector" onchange="onFileSelectorChange(this);"> 
			 <?php 
	 		  echo fileSelector();			  
			 ?>
			</select>
			<br /><br />
			<br />			
			<span fckLang="DlgLnkType">Link Type</span><br />
			<select id="cmbLinkType" onchange="SetLinkType(this.value);">
				<option value="url" fckLang="DlgLnkTypeURL" selected="selected">URL</option>
				<option value="anchor" fckLang="DlgLnkTypeAnchor">Anchor in this page</option>
				<option value="email" fckLang="DlgLnkTypeEMail">E-Mail</option>
			</select>
			<br />
			<br />
			<div id="divLinkTypeUrl">
				<table cellspacing="0" cellpadding="0" width="100%" border="0" dir="ltr">
					<tr>
						<td nowrap="nowrap">
							<span fckLang="DlgLnkProto">Protocol</span><br />
							<select id="cmbLinkProtocol">
								<option value="http://" selected="selected">http://</option>
								<option value="https://">https://</option>
								<option value="ftp://">ftp://</option>
								<option value="news://">news://</option>
								<option value="" fckLang="DlgLnkProtoOther">&lt;other&gt;</option>
							</select>
						</td>
						<td nowrap="nowrap">&nbsp;</td>
						<td nowrap="nowrap" width="100%">
							<span fckLang="DlgLnkURL">URL</span><br />
							<input id="txtUrl" style="WIDTH: 100%" type="text" onkeyup="OnUrlChange();" onchange="OnUrlChange();" />
						</td>
					</tr>
				</table>
				<br />
				<div id="divBrowseServer">
				<input type="button" value="Browse Server" fckLang="DlgBtnBrowseServer" onclick="BrowseServer();" />
				</div>
			</div>
			<div id="divLinkTypeAnchor" style="DISPLAY: none" align="center">
				<div id="divSelAnchor" style="DISPLAY: none">
					<table cellspacing="0" cellpadding="0" border="0" width="70%">
						<tr>
							<td colspan="3">
								<span fckLang="DlgLnkAnchorSel">Select an Anchor</span>
							</td>
						</tr>
						<tr>
							<td width="50%">
								<span fckLang="DlgLnkAnchorByName">By Anchor Name</span><br />
								<select id="cmbAnchorName" onchange="GetE('cmbAnchorId').value='';" style="WIDTH: 100%">
									<option value="" selected="selected"></option>
								</select>
							</td>
							<td>&nbsp;&nbsp;&nbsp;</td>
							<td width="50%">
								<span fckLang="DlgLnkAnchorById">By Element Id</span><br />
								<select id="cmbAnchorId" onchange="GetE('cmbAnchorName').value='';" style="WIDTH: 100%">
									<option value="" selected="selected"></option>
								</select>
							</td>
						</tr>
					</table>
				</div>
				<div id="divNoAnchor" style="DISPLAY: none">
					<span fckLang="DlgLnkNoAnchors">&lt;No anchors available in the document&gt;</span>
				</div>
			</div>
			<div id="divLinkTypeEMail" style="DISPLAY: none">
				<span fckLang="DlgLnkEMail">E-Mail Address</span><br />
				<input id="txtEMailAddress" style="WIDTH: 100%" type="text" /><br />
				<span fckLang="DlgLnkEMailSubject">Message Subject</span><br />
				<input id="txtEMailSubject" style="WIDTH: 100%" type="text" /><br />
				<span fckLang="DlgLnkEMailBody">Message Body</span><br />
				<textarea id="txtEMailBody" style="WIDTH: 100%" rows="3" cols="20"></textarea>
			</div>
		</div>
		<div id="divUpload" style="DISPLAY: none">
			<form method="post" target="UploadWindow" enctype="multipart/form-data" action="">
				<span fckLang="DlgLnkUpload">Upload</span><br />
				<input style="WIDTH: 100%" type="file" size="40" /><br />
				<br />
				<input id="btnUpload" onclick="uploadFile();" type="button" value="Send it to the Server"
					fckLang="DlgLnkBtnUpload" />
			</form>
		</div>
		<div id="divTarget" style="DISPLAY: none">
			<table cellspacing="0" cellpadding="0" width="100%" border="0">
				<tr>
					<td nowrap="nowrap">
						<span fckLang="DlgLnkTarget">Target</span><br />
						<select id="cmbTarget" onchange="SetTarget(this.value);">
							<option value="" fckLang="DlgGenNotSet" selected="selected">&lt;not set&gt;</option>
							<option value="frame" fckLang="DlgLnkTargetFrame">&lt;frame&gt;</option>
							<option value="popup" fckLang="DlgLnkTargetPopup">&lt;popup window&gt;</option>
							<option value="_blank" fckLang="DlgLnkTargetBlank">New Window (_blank)</option>
							<option value="_top" fckLang="DlgLnkTargetTop">Topmost Window (_top)</option>
							<option value="_self" fckLang="DlgLnkTargetSelf">Same Window (_self)</option>
							<option value="_parent" fckLang="DlgLnkTargetParent">Parent Window (_parent)</option>
						</select>
					</td>
					<td>&nbsp;</td>
					<td id="tdTargetFrame" nowrap="nowrap" width="100%">
						<span fckLang="DlgLnkTargetFrameName">Target Frame Name</span><br />
						<input id="txtTargetFrame" style="WIDTH: 100%" type="text" onkeyup="OnTargetNameChange();"
							onchange="OnTargetNameChange();" />
					</td>
					<td id="tdPopupName" style="DISPLAY: none" nowrap="nowrap" width="100%">
						<span fckLang="DlgLnkPopWinName">Popup Window Name</span><br />
						<input id="txtPopupName" style="WIDTH: 100%" type="text" />
					</td>
				</tr>
			</table>
			<br />
			<table id="tablePopupFeatures" style="DISPLAY: none" cellspacing="0" cellpadding="0" align="center"
				border="0">
				<tr>
					<td>
						<span fckLang="DlgLnkPopWinFeat">Popup Window Features</span><br />
						<table cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td valign="top" nowrap="nowrap" width="50%">
									<input id="chkPopupResizable" name="chkFeature" value="resizable" type="checkbox" /><label for="chkPopupResizable" fckLang="DlgLnkPopResize">Resizable</label><br />
									<input id="chkPopupLocationBar" name="chkFeature" value="location" type="checkbox" /><label for="chkPopupLocationBar" fckLang="DlgLnkPopLocation">Location 
										Bar</label><br />
									<input id="chkPopupManuBar" name="chkFeature" value="menubar" type="checkbox" /><label for="chkPopupManuBar" fckLang="DlgLnkPopMenu">Menu 
										Bar</label><br />
									<input id="chkPopupScrollBars" name="chkFeature" value="scrollbars" type="checkbox" /><label for="chkPopupScrollBars" fckLang="DlgLnkPopScroll">Scroll 
										Bars</label>
								</td>
								<td></td>
								<td valign="top" nowrap="nowrap" width="50%">
									<input id="chkPopupStatusBar" name="chkFeature" value="status" type="checkbox" /><label for="chkPopupStatusBar" fckLang="DlgLnkPopStatus">Status 
										Bar</label><br />
									<input id="chkPopupToolbar" name="chkFeature" value="toolbar" type="checkbox" /><label for="chkPopupToolbar" fckLang="DlgLnkPopToolbar">Toolbar</label><br />
									<input id="chkPopupFullScreen" name="chkFeature" value="fullscreen" type="checkbox" /><label for="chkPopupFullScreen" fckLang="DlgLnkPopFullScrn">Full 
										Screen (IE)</label><br />
									<input id="chkPopupDependent" name="chkFeature" value="dependent" type="checkbox" /><label for="chkPopupDependent" fckLang="DlgLnkPopDependent">Dependent 
										(Netscape)</label>
								</td>
							</tr>
							<tr>
								<td valign="top" nowrap="nowrap" width="50%">&nbsp;</td>
								<td></td>
								<td valign="top" nowrap="nowrap" width="50%"></td>
							</tr>
							<tr>
								<td valign="top">
									<table cellspacing="0" cellpadding="0" border="0">
										<tr>
											<td nowrap="nowrap"><span fckLang="DlgLnkPopWidth">Width</span></td>
											<td>&nbsp;<input id="txtPopupWidth" type="text" maxlength="4" size="4" /></td>
										</tr>
										<tr>
											<td nowrap="nowrap"><span fckLang="DlgLnkPopHeight">Height</span></td>
											<td>&nbsp;<input id="txtPopupHeight" type="text" maxlength="4" size="4" /></td>
										</tr>
									</table>
								</td>
								<td>&nbsp;&nbsp;</td>
								<td valign="top">
									<table cellspacing="0" cellpadding="0" border="0">
										<tr>
											<td nowrap="nowrap"><span fckLang="DlgLnkPopLeft">Left Position</span></td>
											<td>&nbsp;<input id="txtPopupLeft" type="text" maxlength="4" size="4" /></td>
										</tr>
										<tr>
											<td nowrap="nowrap"><span fckLang="DlgLnkPopTop">Top Position</span></td>
											<td>&nbsp;<input id="txtPopupTop" type="text" maxlength="4" size="4" /></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</div>
		<div id="divAttribs" style="DISPLAY: none">
			<table cellspacing="0" cellpadding="0" width="100%" align="center" border="0">
				<tr>
					<td valign="top" width="50%">
						<span fckLang="DlgGenId">Id</span><br />
						<input id="txtAttId" style="WIDTH: 100%" type="text" />
					</td>
					<td width="1"></td>
					<td valign="top">
						<table cellspacing="0" cellpadding="0" width="100%" align="center" border="0">
							<tr>
								<td width="60%">
									<span fckLang="DlgGenLangDir">Language Direction</span><br />
									<select id="cmbAttLangDir" style="WIDTH: 100%">
										<option value="" fckLang="DlgGenNotSet" selected>&lt;not set&gt;</option>
										<option value="ltr" fckLang="DlgGenLangDirLtr">Left to Right (LTR)</option>
										<option value="rtl" fckLang="DlgGenLangDirRtl">Right to Left (RTL)</option>
									</select>
								</td>
								<td width="1%">&nbsp;&nbsp;&nbsp;</td>
								<td nowrap="nowrap"><span fckLang="DlgGenAccessKey">Access Key</span><br />
									<input id="txtAttAccessKey" style="WIDTH: 100%" type="text" maxlength="1" size="1" />
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td valign="top" width="50%">
						<span fckLang="DlgGenName">Name</span><br />
						<input id="txtAttName" style="WIDTH: 100%" type="text" />
					</td>
					<td width="1"></td>
					<td valign="top">
						<table cellspacing="0" cellpadding="0" width="100%" align="center" border="0">
							<tr>
								<td width="60%">
									<span fckLang="DlgGenLangCode">Language Code</span><br />
									<input id="txtAttLangCode" style="WIDTH: 100%" type="text" />
								</td>
								<td width="1%">&nbsp;&nbsp;&nbsp;</td>
								<td nowrap="nowrap">
									<span fckLang="DlgGenTabIndex">Tab Index</span><br />
									<input id="txtAttTabIndex" style="WIDTH: 100%" type="text" maxlength="5" size="5" />
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td valign="top" width="50%">&nbsp;</td>
					<td width="1"></td>
					<td valign="top"></td>
				</tr>
				<tr>
					<td valign="top" width="50%">
						<span fckLang="DlgGenTitle">Advisory Title</span><br />
						<input id="txtAttTitle" style="WIDTH: 100%" type="text" />
					</td>
					<td width="1">&nbsp;&nbsp;&nbsp;</td>
					<td valign="top">
						<span fckLang="DlgGenContType">Advisory Content Type</span><br />
						<input id="txtAttContentType" style="WIDTH: 100%" type="text" />
					</td>
				</tr>
				<tr>
					<td valign="top">
						<span fckLang="DlgGenClass">Stylesheet Classes</span><br />
						<input id="txtAttClasses" style="WIDTH: 100%" type="text" />
					</td>
					<td></td>
					<td valign="top">
						<span fckLang="DlgGenLinkCharset">Linked Resource Charset</span><br />
						<input id="txtAttCharSet" style="WIDTH: 100%" type="text" />
					</td>
				</tr>
			</table>
			<table cellspacing="0" cellpadding="0" width="100%" align="center" border="0">
				<tr>
					<td>
						<span fckLang="DlgGenStyle">Style</span><br />
						<input id="txtAttStyle" style="WIDTH: 100%" type="text" />
					</td>
				</tr>
			</table>
		</div>
	</body>
</html>
