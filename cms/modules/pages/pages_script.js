function setuseTitleValue(value) {
	if (value == "CUSTOM") {
		$('usetitle_customtitle').disabled = false;
	} else {
		$('usetitle_customtitle').disabled = true;
	}
	$('usetitle_res').value = value;
}

function toggleButton(button_id){
	var btnobj = $(button_id);
	if (btnobj.disabled == false) {
		btnobj.disabled = true;
	} else {
		btnobj.disabled = false;
	}
}

// --------> Functions to handle attached/related boxes
function attachBox(obj) {
	obj.disabled = true;
	if (obj.name.indexOf("custombox_") > -1) {
		var boxtype = "custom";
		var box_id = obj.name.split("_")[1];
	} else {
		var box_id = obj.name.split("_")[1];
		var boxtype = "normal";
	}

	if (obj.checked) {
		var perform = "attach";
	} else {
		var perform = "remove";
	}

	attachremoveRelatedBox(boxtype, box_id, perform);
}

function attachRelatedBoxDone(originalRequest, boxtype, box_id) {
	if (originalRequest.responseText == "ok") {
		Element.hide('ajaxloader_relboxes');
		if (boxtype == "custom") {
			box_id = "custombox_"+box_id;
		} else {
			box_id = "show_"+box_id;
		}
		$(box_id).disabled = false;	
	} else {
		alert(originalRequest.responseText);
		Element.hide('ajaxloader_relboxes');
	}
}

function attachremoveRelatedBox(boxtype, box_id, perform) {
	// perform = "attach" or "remove"
	// boxtype = "custom" or "normal"
	Element.show('ajaxloader_relboxes');
	var url = cmsUrl+'/modules/pages/pages.ajaxresponders.php';
	var pars = 'do=ajax_'+perform+'RelatedBox'+boxtype+'&rel_id='+box_id+'&page_id='+pageid;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: function(originalRequest) { attachRelatedBoxDone(originalRequest, boxtype, box_id); }
				});
}


// --------> Functions to handle attached/related events
function addRelatedEvent(related_eventid) {
	Element.show('ajaxloader_relboxes');
	var url = cmsUrl+'/modules/pages/pages.ajaxresponders.php';
	var pars = 'do=ajax_addRelatedEvent&rel_id='+related_eventid+'&page_id='+pageid;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: processAddRemoveRelatedEvent
				});
}

function removeRelatedEvent(related_eventid) {
	Element.show('ajaxloader_relevents');
	var url = cmsUrl+'/modules/pages/pages.ajaxresponders.php';
	var pars = 'do=ajax_removeRelatedEvent&rel_id='+related_eventid+'&page_id='+pageid;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: processAddRemoveRelatedEvent
				});
}

function processAddRemoveRelatedEvent(originalRequest) {
	if (originalRequest.responseText == "ok") {
		loadAttachedEvents();
	} else {
		alert(originalRequest.responseText);
		Element.hide('ajaxloader_relevents');
	}
}

function loadAvaliableEvents() {
	// Fold in only if visible and only process load after foldout
	if ($('availableevents').style.display != "none") {
		new Effect.Combo('availableevents', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false, afterFinish: function(){ loadAvailableEvents_do(); }});
	} else {
		loadAvailableEvents_do();
	}
}

function loadAvailableEvents_do() {
	var selected_calendar = $('calendarselector-relevents').value
	// Load content and fold out
	Element.show('ajaxloader_relevents');
	var url = cmsUrl+'/modules/events/events.ajaxresponders.php';
	var pars = 'do=ajax_returnAvailableEvents&page_id='+pageid+'&calendar_id='+selected_calendar;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onFailure: reportAjaxError,
					onComplete: updateAvailableEvents
				});
}

function updateAvailableEvents(originalRequest) {
	var container = $('availableevents')
	container.innerHTML = originalRequest.responseText;
	// alert(container.innerHTML);
	// Only fold out if hidden
	if (container.style.display == "none" || container.style.display == "") {
		new Effect.Combo('availableevents', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false});
	}
	Element.hide('ajaxloader_relevents');
}

function loadAvaliableCalendarsList() {
	// Fold in only
	var blabel = $('availableevents_button').value; 
	if (blabel != "Tilføj relateret begivenhed") {
		new Effect.Combo('availablecalendars', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false, beforeStart: function(){ toggleButton('availableevents_button'); }, afterFinish: function(){ toggleButton('availableevents_button'); } });
		// Also fold-in availablenews is visible
		if ($('availableevents').style.display != "none") {
			new Effect.Combo('availableevents', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false});
		}
		$('availableevents_button').value = "Tilføj relateret begivenhed";
		return;		
	}

	// Load content and fold out
	Element.show('ajaxloader_relevents');
	var url = cmsUrl+'/modules/events/events.ajaxresponders.php';
	var pars = 'do=ajax_returnCalendarslist';
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onFailure: reportAjaxError,
					onComplete: updateAvailableCalendars
				});
}

function updateAvailableCalendars(originalRequest) {
	var container = $('availablecalendars')
	container.innerHTML = originalRequest.responseText;
//	alert(container.innerHTML);
	new Effect.Combo('availablecalendars', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false, beforeStart: function(){ toggleButton('availableevents_button'); }, afterFinish: function(){ toggleButton('availableevents_button'); }});
	$('availableevents_button').value = "Skjul begivenheder";
	Element.hide('ajaxloader_relevents');
}

function loadAttachedEvents() {
	Element.show('ajaxloader_relevents');
	var url = cmsUrl+'/modules/events/events.ajaxresponders.php';
	var pars = 'do=ajax_returnAttachedEvents&page_id='+pageid;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: updateAttachedEvents
				});
}

function updateAttachedEvents(originalRequest) {
	$('attachedevents').innerHTML = originalRequest.responseText;
	Element.hide('ajaxloader_relevents');
}

// --------> Functions to handle attached/related news
function addRelatedNews(related_newsid) {
	Element.show('ajaxloader_relnews');
	var url = cmsUrl+'/modules/pages/pages.ajaxresponders.php';
	var pars = 'do=ajax_addRelatedNews&rel_id='+related_newsid+'&page_id='+pageid;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: processAddRemoveRelatedNews
				});
}

function removeRelatedNews(related_newsid) {
	Element.show('ajaxloader_relnews');
	var url = cmsUrl+'/modules/pages/pages.ajaxresponders.php';
	var pars = 'do=ajax_removeRelatedNews&rel_id='+related_newsid+'&page_id='+pageid;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: processAddRemoveRelatedNews
				});
}

function processAddRemoveRelatedNews(originalRequest) {
	if (originalRequest.responseText == "ok") {
		loadAttachedNews();
	} else {
		alert(originalRequest.responseText);
		Element.hide('ajaxloader_relnews');
	}
}

function loadAvaliableNews() {
	// Fold in only if visible and only process load after foldout
	if ($('availablenews').style.display != "none") {
		new Effect.Combo('availablenews', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false, afterFinish: function(){ loadAvailableNews_do(); }});
	} else {
		loadAvailableNews_do();
	}
}

function loadAvailableNews_do() {
	var selected_newsarchive = $('newsarchiveselector-relnews').value
	// Load content and fold out
	Element.show('ajaxloader_relnews');
	var url = cmsUrl+'/modules/news/news.ajaxresponders.php';
	var pars = 'do=ajax_returnAvailableNews&page_id='+pageid+'&newsarchive_id='+selected_newsarchive;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onFailure: reportAjaxError,
					onComplete: updateAvailableNews
				});
}

function updateAvailableNews(originalRequest) {
	var container = $('availablenews')
	container.innerHTML = originalRequest.responseText;
	// alert(container.innerHTML);
	// Only fold out if hidden
	if (container.style.display == "none" || container.style.display == "") {
		new Effect.Combo('availablenews', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false});
	}
	Element.hide('ajaxloader_relnews');
}

function loadAvaliableNewsarchivesList() {
	// Fold in only
	var blabel = $('availablenews_button').value; 
	if (blabel != "Tilføj relateret nyhed") {
		new Effect.Combo('availablenewsarchives', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false, beforeStart: function(){ toggleButton('availablenews_button'); }, afterFinish: function(){ toggleButton('availablenews_button'); } });
		// Also fold-in availablenews is visible
		if ($('availablenews').style.display != "none") {
			new Effect.Combo('availablenews', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false});
		}
		$('availablenews_button').value = "Tilføj relateret nyhed";
		return;		
	}

	// Load content and fold out
	Element.show('ajaxloader_relnews');
	var url = cmsUrl+'/modules/news/news.ajaxresponders.php';
	var pars = 'do=ajax_returnNewsarchiveslist';
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onFailure: reportAjaxError,
					onComplete: updateAvailableNewsarchives
				});
}

function updateAvailableNewsarchives(originalRequest) {
	var container = $('availablenewsarchives')
	container.innerHTML = originalRequest.responseText;
//	alert(container.innerHTML);
	new Effect.Combo('availablenewsarchives', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false, beforeStart: function(){ toggleButton('availablenews_button'); }, afterFinish: function(){ toggleButton('availablenews_button'); }});
	$('availablenews_button').value = "Skjul nyheder";
	Element.hide('ajaxloader_relnews');
}

function loadAttachedNews() {
	Element.show('ajaxloader_relnews');
	var url = cmsUrl+'/modules/news/news.ajaxresponders.php';
	var pars = 'do=ajax_returnAttachedNews&page_id='+pageid;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: updateAttachedNews
				});
}

function updateAttachedNews(originalRequest) {
	$('attachednews').innerHTML = originalRequest.responseText;
	Element.hide('ajaxloader_relnews');
}


// --------> Functions to handle attached/related pages
function addRelatedPage(related_pageid) {
	Element.show('ajaxloader_relpages');
	var url = cmsUrl+'/modules/pages/pages.ajaxresponders.php';
	var pars = 'do=ajax_addRelatedPage&rel_id='+related_pageid+'&page_id='+pageid;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: processAddRemoveRelatedPage
				});
}

function removeRelatedPage(related_pageid) {
	Element.show('ajaxloader_relpages');
	var url = cmsUrl+'/modules/pages/pages.ajaxresponders.php';
	var pars = 'do=ajax_removeRelatedPage&rel_id='+related_pageid+'&page_id='+pageid;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: processAddRemoveRelatedPage
				});
}

function processAddRemoveRelatedPage(originalRequest) {
	if (originalRequest.responseText == "ok") {
		loadAttachedPages();
	} else {
		alert(originalRequest.responseText);
		Element.hide('ajaxloader_relpages');
	}
}

function loadAvaliablePages() {
	// Fold in only if visible and only process load after foldout
	if ($('availablepages').style.display != "none") {
		new Effect.Combo('availablepages', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false, afterFinish: function(){ loadAvailablePages_do(); }});
	} else {
		loadAvailablePages_do();
	}
}

function loadAvailablePages_do() {
	var selected_menu = $('menuselector-relpages').value
	// Load content and fold out
	Element.show('ajaxloader_relpages');
	var url = cmsUrl+'/modules/pages/pages.ajaxresponders.php';
	var pars = 'do=ajax_returnAvailablePages&page_id='+pageid+'&menu_id='+selected_menu;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onFailure: reportAjaxError,
					onComplete: updateAvailablePages
				});
}

function updateAvailablePages(originalRequest) {
	var container = $('availablepages')
	container.innerHTML = originalRequest.responseText;
	// alert(container.innerHTML);
	// Only fold out if hidden
	if (container.style.display == "none" || container.style.display == "") {
		new Effect.Combo('availablepages', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false});
	}
	Element.hide('ajaxloader_relpages');
}

function loadAvaliableMenusList() {
	// Fold in only
	var blabel = $('availablepages_button').value; 
	if (blabel != "Tilføj relateret side") {
		new Effect.Combo('availablemenus', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false, beforeStart: function(){ toggleButton('availablepages_button'); }, afterFinish: function(){ toggleButton('availablepages_button'); } });
		// Also fold-in availablepages is visible
		if ($('availablepages').style.display != "none") {
			new Effect.Combo('availablepages', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false});
		}
		$('availablepages_button').value = "Tilføj relateret side";
		return;		
	}

	// Load content and fold out
	Element.show('ajaxloader_relpages');
	var url = cmsUrl+'/modules/pages/pages.ajaxresponders.php';
	var pars = 'do=ajax_returnMenuslist';
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onFailure: reportAjaxError,
					onComplete: updateAvailableMenus
				});
}

function updateAvailableMenus(originalRequest) {
	var container = $('availablemenus')
	container.innerHTML = originalRequest.responseText;
	new Effect.Combo('availablemenus', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false, beforeStart: function(){ toggleButton('availablepages_button'); }, afterFinish: function(){ toggleButton('availablepages_button'); }});
	$('availablepages_button').value = "Skjul sider";
	Element.hide('ajaxloader_relpages');
}

function loadAttachedPages() {
	Element.show('ajaxloader_relpages');
	var url = cmsUrl+'/modules/pages/pages.ajaxresponders.php';
	var pars = 'do=ajax_returnAttachedPages&page_id='+pageid;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: updateAttachedPages
				});
}

function updateAttachedPages(originalRequest) {
	$('attachedpages').innerHTML = originalRequest.responseText;
	Element.hide('ajaxloader_relpages');
}



// --------> Functions to handle attached form
function addForm(form_id) {
    if ($("inlineform_"+form_id).checked){
		inline = 1;
	} else {
		inline = 0;
	}
	Element.show('ajaxloader_form');
	var url = cmsUrl+'/modules/formeditor2/formeditor2.ajaxresponders.php';
	var pars = 'do=ajax_addFormToPage&form_id='+form_id+'&page_id='+pageid+'&tabel=PAGES&inline='+inline;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: processAddFormReply
				});
}

function processAddFormReply(originalRequest) {
	if (originalRequest.responseText == "ok") {
		loadAttachedForm();
	} else {
		alert(originalRequest.responseText);
		Element.hide('ajaxloader_form');
	}
}

function removeForm(form_id) {
	Element.show('ajaxloader_form');
	var url = cmsUrl+'/modules/formeditor2/formeditor2.ajaxresponders.php';
	var pars = 'do=ajax_removeFormFromPage&form_id='+form_id+'&page_id='+pageid+'&tabel=PAGES';
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: loadAttachedForm
				});
}

function loadAvaliableFormsList() {
	// Fold in only
	var blabel = $('availableforms_button').value; 
	if (blabel != "Tilføj formular") {
		new Effect.Combo('availableforms', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false, beforeStart: function(){ toggleButton('availableforms_button'); }, afterFinish: function(){ toggleButton('availableforms_button'); } });
		$('availableforms_button').value = "Tilføj formular";
		return;		
	}

	// Warn if form is already attached
	if ($('page_has_form').value == "yes") {
		if (confirm("Der er allerede vedhæftet en formular til siden. Ønsker du at erstatte formularen med en andet?")) {
			var form_replace = true;
		} else {
			return false;
		}
	}
	
	// Load content and fold out
	Element.show('ajaxloader_form');
	var url = cmsUrl+'/modules/formeditor2/formeditor2.ajaxresponders.php';
	var pars = 'do=ajax_returnAvailableForms';
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'get',
					parameters: pars,
					onFailure: reportAjaxError,
					onComplete: updateAvailableForms
				});
}

function updateAvailableForms(originalRequest) {
	var container = $('availableforms')
	container.innerHTML = originalRequest.responseText;
	new Effect.Combo('availableforms', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false, beforeStart: function(){ toggleButton('availableforms_button'); }, afterFinish: function(){ toggleButton('availableforms_button'); }});
	$('availableforms_button').value = "Skjul formularer";
	Element.hide('ajaxloader_form');
}


function loadAttachedForm() {
	Element.show('ajaxloader_form');
	var url = cmsUrl+'/modules/formeditor2/formeditor2.ajaxresponders.php';
	var pars = 'do=ajax_returnAttachedForm&page_id='+pageid;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: updateAttachedForm
				});
}

function updateAttachedForm(originalRequest) {
	$('attachedform').innerHTML = originalRequest.responseText;
	Element.hide('ajaxloader_form');
}

// --------> Functions to handle attached gallery
function addGallery(tablename, folder_id) {
	Element.show('ajaxloader_gallery');
	var url = cmsUrl+'/modules/picturearchive/picturearchive.ajaxresponders.php';
	var pars = 'do=ajax_addGalleryToPage&folder_id='+folder_id+'&page_id='+pageid+'&tabel=PAGES';
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: processAddGalleryReply
				});
}

function processAddGalleryReply(originalRequest) {
	if (originalRequest.responseText == "ok") {
		loadAttachedGallery();
	} else {
		alert(originalRequest.responseText);
		Element.hide('ajaxloader_gallery');
	}
}

function removeGallery(folder_id) {
	Element.show('ajaxloader_gallery');
	var url = cmsUrl+'/modules/picturearchive/picturearchive.ajaxresponders.php';
	var pars = 'do=ajax_removeGalleryFromPage&folder_id='+folder_id+'&page_id='+pageid+'&tabel=PAGES';
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: loadAttachedGallery
				});
}

function loadAvaliablePicturefoldersList() {
	// Fold in only
	var blabel = $('availablepicturefolders_button').value; 
	if (blabel != "Tilføj galleri") {
		new Effect.Combo('availablepicturefolders', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false, beforeStart: function(){ toggleButton('availablepicturefolders_button'); }, afterFinish: function(){ toggleButton('availablepicturefolders_button'); } });
		$('availablepicturefolders_button').value = "Tilføj galleri";
		return;		
	}

	// Warn if gallery is already attached
	if ($('page_has_gallery').value == "yes") {
		if (confirm("Der er allerede vedhæftet et galleri til siden. Ønsker du at erstatte galleriet med et andet?")) {
			var gallery_replace = true;
		} else {
			return false;
		}
	}
	
	// Load content and fold out
	Element.show('ajaxloader_gallery');
	var url = cmsUrl+'/modules/picturearchive/picturearchive.ajaxresponders.php';
	var pars = 'do=ajax_returnAvailablePicturefolders';
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'get',
					parameters: pars,
					onFailure: reportAjaxError,
					onComplete: updateAvailablePicturefolders
				});
}

function updateAvailablePicturefolders(originalRequest) {
	var container = $('availablepicturefolders')
//	alert(originalRequest.responseText);
	container.innerHTML = originalRequest.responseText;
/*
	allNodes = document.getElementsByClassName("plusminus", container);
	for(ii = 0; ii < allNodes.length; ii++) {
		if (allNodes[ii].id.indexOf("foldeknap_") > -1 && allNodes[ii].type == "button") {
			var aid = allNodes[ii].id.split("_")[1];
			hideShowFolder(aid, -1);
		}
	}
*/
	new Effect.Combo('availablepicturefolders', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false, beforeStart: function(){ toggleButton('availablepicturefolders_button'); }, afterFinish: function(){ toggleButton('availablepicturefolders_button'); }});
	$('availablepicturefolders_button').value = "Skjul billedmapper";
	Element.hide('ajaxloader_gallery');
}


function loadAttachedGallery() {
	Element.show('ajaxloader_gallery');
	var url = cmsUrl+'/modules/picturearchive/picturearchive.ajaxresponders.php';
	var pars = 'do=ajax_returnAttachedGallery&page_id='+pageid;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: updateAttachedGallery
				});
}

function updateAttachedGallery(originalRequest) {
	$('attachedgallery').innerHTML = originalRequest.responseText;
	Element.hide('ajaxloader_gallery');
}

// --------> Functions to handle attached files
function removeAttachment(file_id) {
	// Function to remove attached file from page. 
	// Will make ajax call to perform necessary database delete and then update the attachedFilesList.
	Element.show('ajaxloader_files');
	var url = cmsUrl+'/modules/attachments/attachments.ajaxresponders.php';
	var pars = 'do=ajax_removeAttachment&file_id='+file_id+'&id='+pageid+'&tabel=PAGES';
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: loadAttachedFilelist
				});
}

function attachFile(file_id) {
	// Function to attach file to page. 
	// Will make ajax call to perform necessary database insert and then update the attachedFilesList.
	Element.show('ajaxloader_files');
	var url = cmsUrl+'/modules/attachments/attachments.ajaxresponders.php';
	var pars = 'do=ajax_attachFile&file_id='+file_id+'&id='+pageid+'&tabel=PAGES';
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: processAttachFileReply
				});
}

function processAttachFileReply(originalRequest) {
	if (originalRequest.responseText == "ok") {
		loadAttachedFilelist();
	} else {
		alert(originalRequest.responseText);
		Element.hide('ajaxloader_files');
	}
}

function loadAvaliableFileList() {
	var blabel = $('availablefiles_button').value; 
	if (blabel != "Vedhæft fil") {
		new Effect.Combo('availablefiles', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false, beforeStart: function(){ toggleButton('availablefiles_button'); }, afterFinish: function(){ toggleButton('availablefiles_button'); } });
		$('availablefiles_button').value = "Vedhæft fil";
		return;		
	}
	
	Element.show('ajaxloader_files');
	var url = cmsUrl+'/modules/filearchive2/filearchive2.ajaxresponders.php';
	var pars = 'do=ajax_returnAvailablefiles&selectfile=1';
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'get',
					parameters: pars,
					onFailure: reportAjaxError,
					onComplete: updateAvailableFiles
				});
}

function updateAvailableFiles(originalRequest) {
	var container = $('availablefiles')
	container.innerHTML = originalRequest.responseText;
	allNodes = document.getElementsByClassName("plusminus", container);
	for(ii = 0; ii < allNodes.length; ii++) {
		if (allNodes[ii].id.indexOf("foldeknap_") > -1 && allNodes[ii].type == "button") {
			var aid = allNodes[ii].id.split("_")[1];
			hideShowFolder(aid, -1);
		}
	}
	new Effect.Combo('availablefiles', {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false, beforeStart: function(){ toggleButton('availablefiles_button'); }, afterFinish: function(){ toggleButton('availablefiles_button'); }});
	$('availablefiles_button').value = "Skjul filer";
	Element.hide('ajaxloader_files');
}

function reportAjaxError(request) {
	alert('Der opstod en fejl. Kunne ikke hente data fra serveren.');
}

function loadAttachedFilelist() {
	Element.show('ajaxloader_files');
	var url = cmsUrl+'/modules/attachments/attachments.ajaxresponders.php';
	var pars = 'do=ajax_returnAttachedfiles&id='+pageid+'&tabel=PAGES';
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: updateAttachedfiles
				});
}

function updateAttachedfiles(originalRequest) {
	$('attachedfiles').innerHTML = originalRequest.responseText;
	Element.hide('ajaxloader_files');
}

function toggle_is_sitelang_frontpage() {
	var selector = $('frontpage_languageselector');
	if (selector.disabled == false) {
		selector.disabled = true;
	} else {
		selector.disabled = false;
	}
}

function toggle_pointToPageSelector() {
	var lcb = $('pointing_page');
	var ucb = $('pointing_url');
	var selector = $('pointToPageSelector');

	if (lcb.checked == true && ucb.checked == false) {
		// If checked and other linkbox NOT checked, enable input
		selector.disabled = false;
	} else if (lcb.checked == true && ucb.checked == true) {
		// If both linkboxes checked, uncheck this box, disable input and alert!
		lcb.checked = false;
		selector.disabled = true;
		alert("Siden kan kun være link til ét sted. Hvis du vil linke til en side på sitet, skal du fjerne link til URL herunder.");
	} else {
		// If unchecking box, disable input
		selector.disabled = true;
	}
}

function toggle_pointtopage_url() {
	var lcb = $('pointing_page');
	var ucb = $('pointing_url');
	var selector = $('pointtopage_url');

	if (ucb.checked == true && lcb.checked == false) {
		// If checked and other linkbox NOT checked, enable input
		selector.disabled = false;
	} else if (ucb.checked == true && lcb.checked == true) {
		// If both linkboxes checked, uncheck this box, disable input and alert!
		ucb.checked = false;
		selector.disabled = true;
		alert("Siden kan kun være link til ét sted. Hvis du vil linke til en URL, skal du fjerne link til side herover.");
	} else {
		// If unchecking box, disable input
		selector.disabled = true;
	}
}

function verify() {
	theForm = document.forms[0];
	if (theForm.breadcrumb.value == "") {
		alert("Udfyld venligst feltet Titel i menu.");
		return;
	} 
	if (theForm.pointing_page.checked && theForm.pointToPageSelector.value < 0) {
		alert("Vælg venligst, hvilken anden side, denne side skal viderepeges til.");
		return;
	}
	
	if (theForm.published_res.value == "" || (!theForm.published[0].checked && !theForm.published[1].checked)) {
		alert("Vælg venligst sidens status - færdigredigeret eller kladde?");
		return;
	} 
/*
	if (theForm.protection_selector_res.value == "" || (!theForm.protection_selector[0].checked && !theForm.protection_selector[1].checked)) {
		alert("Vælg venligst sidens beskyttelse - kan den redigeres af alle eller kun af dig selv?");
		return;
	}
*/
	if (theForm.beskyttet_res.value == "" || theForm.beskyttet_res.value == "0" || (!theForm.beskyttet[0].checked && !theForm.beskyttet[1].checked)) {
		alert("Angiv venligst, hvilke brugere, menupunktet er tilgængeligt for.");
		return;
	}
	if (theForm.beskyttet_res.value == 2) {
		OK=0;
		A = document.getElementsByTagName("input");
		for (i=0; i<A.length; i++) {
			if (A[i].name.indexOf("B_") > -1 && A[i].checked) OK=1;
		}
		if (OK==0) {
			alert("Vælg venligst (ved afkrydsning) de grupper, som menupunktet er tilgængeligt for.");
			return;
		}
	}
 
 	if (theForm.usetitle_res.value == "CUSTOM" && theForm.usetitle_customtitle.value == "") {
		alert("Du har valgt at definere din egen titel (under søgeoptimering) men har ikke udfyldt feltet. Udfyld venligst feltet eller vælg en af de andre muligheder.");
		return;
	} 	
 
 ALL = document.getElementsByTagName("input");
 for (i=0; i<ALL.length; i++) {
  if (ALL[i].id.indexOf("B_")>-1 && ALL[i].disabled) ALL[i].disabled = false; 
 }  
 document.forms[0].is_sitelang_frontpage.disabled = false;
 document.forms[0].languageselector.disabled = false;
 document.forms[0].dothis.value = "gem";
 document.forms[0].submit();
}

function makeTitle(titel)
{
 theForm = document.forms[0];
 if (theForm.title && theForm.title.value == "") theForm.title.value = titel;
}

function changeMenuOptions(menuid)
{
 x = document.forms[0].menuplaceselector.options;
 if (menuid==0) {
  document.forms[0].menuplaceselector.options.length = 0;
  document.forms[0].menuplaceselector.value = "0";
  document.forms[0].menuplaceselector.disabled = true;
  document.forms[0].menupunkt_label.disabled = true;
  document.forms[0].menupunkt_label.value = "";
  document.getElementById("menupunkt_label").className = "inputfelt_disabled";
  disableRadioGroup("eget_vindue", true)
  return;
 }
 else {
  document.forms[0].menuplaceselector.disabled=false;
  document.forms[0].menupunkt_label.disabled = false;
  document.forms[0].menupunkt_label.value = "";
  document.getElementById("menupunkt_label").className = "inputfelt";  
  disableRadioGroup("eget_vindue", false)
  document.forms[0].menuplaceselector.options.length = 0;
  x[0] = new Option("Nyt hovedpunkt", "0");
  x[1] = new Option("============ Underpunkt til ============", "-1");
  for(i=0; i<menu[menuid].length; i++)
  {
   x[i+2] = menu[menuid][i];
  }
 }
 document.forms[0].menuplaceselector.value = "0";
}

function sprogVersion(id, tabel)
{
 location = "index.php?content_identifier=pages&dothis=languageversion&id="+id;
}

function disableAllGroups(state)
{
 A = document.getElementsByTagName("input");
 for (i=0; i<A.length; i++) {
  if (A[i].name.indexOf("B_") > -1 && state == 1) {A[i].disabled = true; A[i].checked = false;}
  if (A[i].name.indexOf("B_") > -1 && state == 2) {A[i].disabled = false; A[i].checked = false;}
 }
}

function selectMenu()
{
 theForm = document.forms[0];
 location = "?content_identifier=pages&menuid=" + theForm.menuselector.value + "&dothis=oversigt";
}

function foldAlleInd() {
   x = document.getElementsByTagName("div");
   for(i=0; i<x.length; i++){
    if (x[i].id.indexOf("TID_")>-1 && x[i].id.indexOf("PARID_0")<0) {
     x[i].style.display="none";
    }
   }
  }
  
function udfoldDiv(threadid){  
   if (eval("document.forms[0].foldStatus_" + threadid + ".value == '1'")) {
    indfoldDiv(threadid);
	return;
   }
   x = document.getElementsByTagName("div");
   for(i=0; i<x.length; i++){
    if (x[i].id.indexOf("TID_"+threadid)>-1 && x[i].id.indexOf("PARID_0")<0) {
     x[i].style.display="block";
    }
   }   
   eval("document.forms[0].foldStatus_" + threadid + ".value = '1'");
   size();
  }

function indfoldDiv(threadid){  
   x = document.getElementsByTagName("div");
   for(i=0; i<x.length; i++){
    if (x[i].id.indexOf("TID_"+threadid)>-1 && x[i].id.indexOf("PARID_0")<0) {
     x[i].style.display="none";
    }
   } 
   eval("document.forms[0].foldStatus_" + threadid + ".value = '0'");
   size();  
  }
 
function authorFilter(aid){
 if (!aid) return;
 A = document.getElementsByTagName("div");
 for(i=0;i<A.length;i++){
  if (A[i].id.indexOf("pagerow__") > -1 && A[i].id.indexOf("_PARID_0_") < 0 && A[i].id.indexOf("_AID_"+aid) < 0) A[i].style.display = "none"
 }
}

function sync_breadcrumb(obj) {
	if (obj.id == "breadcrumb") {
		$('breadcrumb_mirror').value = obj.value;
	} else {
		$('breadcrumb').value = obj.value;
	}
}

/*
Pages-specific scripts for tabs
*/

//Set tab to intially be selected when page loads:
//[which tab (1=first tab), ID of tab content to display]:
var initialtab=[1, "sc1"]

function do_onload() {
	// TAB FUNCTIONS TO DO ONLOAD
	if (document.getElementById("tablist")) {
		var cookiename = (typeof persisttype!="undefined" && persisttype=="sitewide") ? "tabcontent" : window.location.pathname;
		var cookiecheck = window.get_cookie && get_cookie(cookiename).indexOf("|") != -1;
		collecttablinks();
		initTabcolor=cascadedstyle(tabobjlinks[1], "backgroundColor", "background-color");
		initTabpostcolor=cascadedstyle(tabobjlinks[0], "backgroundColor", "background-color");
		if (typeof enablepersistence!="undefined" && enablepersistence && cookiecheck) {
			var cookieparse=get_cookie(cookiename).split("|");
			var whichtab=cookieparse[0];
			var tabcontentid=cookieparse[1];
			expandcontent(tabcontentid, tabobjlinks[whichtab]);
		} else {
			expandcontent(initialtab[1], tabobjlinks[initialtab[0]-1]);
		}
	}
	
	if (document.getElementById("attachedfiles")) {
		// Init attachBoxes
		Element.hide('ajaxloader_relboxes');
		// Init attachPages
		loadAttachedPages();
		Element.hide('ajaxloader_relpages');
		Element.hide('availablemenus');
		Element.hide('availablepages');
		// Init attachNews
		loadAttachedNews();
		Element.hide('ajaxloader_relnews');
		Element.hide('availablenewsarchives');
		Element.hide('availablenews');
		// Init attachEvents
		loadAttachedEvents();
		Element.hide('ajaxloader_relevents');
		Element.hide('availablecalendars');
		Element.hide('availableevents');
		// Init attachFiles
		loadAttachedFilelist();
		Element.hide('ajaxloader_files');
		Element.hide('availablefiles');
		// Init attachGallery
		loadAttachedGallery();
		Element.hide('ajaxloader_gallery');
		Element.hide('availablepicturefolders');		
		// Init attachForm
		loadAttachedForm();
		Element.hide('ajaxloader_form');
		Element.hide('availableforms');
		// Init rewrite
		Element.hide('ajaxloader_rewrite');
	}
	//tags_add_handlers();
}

if (window.addEventListener) {
	window.addEventListener("load", do_onload, false);
} else if (window.attachEvent) {
	window.attachEvent("onload", do_onload);
} else if (document.getElementById) {
	window.onload=do_onload;
}

function pagesIEColorShift(ID) // For at kompensere for manglende TR:HOVER i CSS...  
{
 //if (navigator.userAgent.indexOf("MSIE") < 0) return;
 oldBGC = document.getElementById(ID).className;
 document.getElementById(ID).className = "sideOversigtHover";
}

function pagesIEColorUnShift(ID)
{
 //if (navigator.userAgent.indexOf("MSIE") < 0) return;
 document.getElementById(ID).className = "sideOversigt";
}

/*
function FCKeditor_OnComplete( editorInstance )
{
    alert( editorInstance.Name ) ;
    editorInstance.MakeEditable();
    alert("is reset");
}
*/

