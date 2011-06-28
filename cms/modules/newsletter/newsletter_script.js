// Global variables set outside this file:
// var newsletter_id
// var batchsize
// var batchwait
batchsize = batchsize * 1;
batchwait = batchwait * 1;

function sendNewsletter(newsletter_id, total_recipients) {
	if (total_recipients > 1) {
		var plural_e = "e";
	} else {
		var plural_e = "";
	}	
	// Confirm sendout
	if (!confirm("Du er ved at udsende nyhedsbrevet på email til "+total_recipients+" modtager"+plural_e+". Er du sikker?")) {
		return false;
	}
	Element.show('ajaxloader_sendout');
	$('newsletter_send').disabled = true;
	$('newsletter_sendstop').disabled = false;

	// Init sendout
	$('newsletter_sendstatus').InnerHTML = 'Initialiserer udsendelse...';
	var url = cmsUrl+'/modules/newsletter/newsletter.ajaxresponders.php';
	var pars = 'do=ajax_InitNewsletterSend&nid='+newsletter_id;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onFailure: reportAjaxError,
					onComplete: sendNewsletter_buildRecipientsList
				});
}

function sendNewsletter_buildRecipientsList(originalRequest) {
	var arr_response = originalRequest.responseText.split("|||||");
	if (arr_response[0] == "SUCCESS") {
		var history_id = arr_response[1];
		updateStatus("Bygger modtagerliste...");
		var url = cmsUrl+'/modules/newsletter/newsletter.ajaxresponders.php';
		var pars = 'do=ajax_buildNewsletterRecipientlist&hid='+history_id;
		var myAjax = new Ajax.Request(
					url,
					{	
						method: 'post',
						parameters: pars,
						onFailure: reportAjaxError,
						onComplete: sendNewsletter_buildRecipientsList_complete
					});
	} else {
		reportError(arr_response[1]);
		$('newsletter_sendstop').disabled = true;
		Element.hide('ajaxloader_sendout');
	}
}

function sendNewsletter_buildRecipientsList_complete(originalRequest) {
	var arr_response = originalRequest.responseText.split("|||||");
	if (arr_response[0] == "SUCCESS") {
		updateStatus("Udsender mails...");
		sendNewsletter_doSendout(arr_response[1]);
	} else {
		reportError(arr_response[1]);
		$('newsletter_sendstop').disabled = true;
		Element.hide('ajaxloader_sendout');
	}
}

function sendNewsletter_doSendout(history_id) {
	var url = cmsUrl+'/modules/newsletter/newsletter.ajaxresponders.php';
	var pars = 'do=ajax_newsletterSendout_do&hid='+history_id+'&batch='+batchsize;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onFailure: reportAjaxError,
					onComplete: sendNewsletter_sendoutComplete
				});
}

function sendNewsletter_sendoutComplete(originalRequest) {
	var arr_response = originalRequest.responseText.split("|||||");
	if (arr_response[0] == "SUCCESS") {
		Element.show('recipients_list_container');
		
		// Update progress count
		var cur_count = $('recipient_count').innerHTML*1;
		var new_count = cur_count+batchsize;
		var count_total = $('recipient_count_total').innerHTML*1;
		if (new_count > count_total) {
			new_count = count_total;
		}		
		$('recipient_count').innerHTML = new_count;

		// Send recipients to list
		$('recipients_list').innerHTML = arr_response[3] + $('recipients_list').innerHTML;
		if (arr_response[1] == "CONTINUE") {
			if ($('sendout_status').value == "STOP") {
				// Sendout aborted by user
				$('sendout_status').value = "CONTIUNE";
				$('newsletter_send').value = "Genoptag udsendelse";
				$('newsletter_send').onclick = function() { 
													sendNewsletter_reassume(history_id, 0, 0);
												};
				updateStatus("<strong>Udsendelse afbrudt</strong>");
				Element.hide('ajaxloader_sendout');
				$('newsletter_send').disabled = false;
			} else {
				// Wait for batchwait miliseconds before sending next batch		
				history_id = arr_response[2]; // setTimeout needs global scoped var
				setTimeout("sendNewsletter_doSendout(history_id);",batchwait);
			}
		} else if (arr_response[1] == "COMPLETE") {
			sendNewsletter_cleanup(arr_response[2]);
		}
	} else {
		reportError(arr_response[1]);
		$('newsletter_sendstop').disabled = true;
		Element.hide('ajaxloader_sendout');
	}
		
}

function sendNewsletter_reassume(history_id, sent_count, total_count) {
	Element.show('ajaxloader_sendout');
	if (sent_count != "0") {
		$('recipient_count').innerHTML = sent_count;
		// sent_count only set when function called from list-buttons
		// so use this for doing a little cleanup
		$('recipients_list').innerHTML = "";
	}
	if (total_count != "0") {
		$('recipient_count_total').innerHTML = total_count;
	}
	$('newsletter_sendstop').disabled = false;
	$('newsletter_send').disabled = true;
	sendNewsletter_doSendout(history_id);
}

function sendNewsletter_stop() {
	$('newsletter_sendstop').disabled = true;
	$('sendout_status').value = "STOP";
	updateStatus("Stopper udsendelse...");
}

function sendNewsletter_cleanup(history_id) {
	// Clean up
	var url = cmsUrl+'/modules/newsletter/newsletter.ajaxresponders.php';
	var pars = 'do=ajax_newsletterSendoutCleanup&hid='+history_id;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onFailure: reportAjaxError,
					onComplete: sendNewsletter_cleanup_complete
				});
}

function sendNewsletter_cleanup_complete(originalRequest) {
	updateStatus("Nyhedsbrevet er nu udsendt!");
	$('newsletter_sendstop').disabled = true;
	Element.hide('ajaxloader_sendout');
}

function reportError(str_msg) {
	$('newsletter_sendstatus').innerHTML = "FEJL: "+str_msg;
}

function updateStatus(str_msg) {
	$('newsletter_sendstatus').innerHTML = str_msg;
}

function toggle_newsletterinterestgroup(str_id, value) {
	// Get interest group id
	var id  = str_id.split("_");
	var int_id = id[1];
	
	// Set mode
	if (value) {
		var mode = "create";
	} else {
		var mode = "destroy";
	}

	// Get Newsletter ID
	if ($('newsletter_id').value != "") {
		var nid = $('newsletter_id').value;
		var temp = 0;
	} else {
		var nid = $('newsletter_id_temporary').value;
		var temp = 1;
	}		
	
	// Get template ID
	var ntid = $('newsletter_template_id').value;	

	Element.show('ajaxloader_interestgroups'); 
	var url = cmsUrl+'/modules/newsletter/newsletter.ajaxresponders.php';
	var pars = 'do=ajax_updateInterestgroup&id='+int_id+'&mode='+mode+'&nid='+nid+'&ntid='+ntid+'&temp='+temp;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onFailure: reportAjaxError,
					onComplete: updateRecipientCount
				});
}

function updateRecipientCount(originalRequest) {
	$('no_recipients_tab').innerHTML = originalRequest.responseText;
	Element.hide('ajaxloader_interestgroups'); 
}

function newsletter_item_edit(int_id) {
	if (abort_add_item()) {
		// Load item
		Element.show('ajaxloader_newsitemlist'); 
		var url = cmsUrl+'/modules/newsletter/newsletter.ajaxresponders.php';
		var pars = 'do=ajax_loadSingleItem&id='+int_id;
		var myAjax = new Ajax.Request(
					url,
					{	
						method: 'post',
						parameters: pars,
						onFailure: reportAjaxError,
						onComplete: showItem
					});
	}
}

function showItem(originalRequest) {
		Element.hide('ajaxloader_newsitemlist'); 
		var str_vars = originalRequest.responseText;
		var arr_vars = str_vars.split("|||||");
	
		var original_itemid = arr_vars[0];
		var original_id = arr_vars[4];
		var original_type = arr_vars[5];
		var heading = arr_vars[6];
		var content = arr_vars[7];
		var imagemode = arr_vars[8];
		var imageurl = arr_vars[9];
		var linkmode = arr_vars[10];
		var linkurl = arr_vars[11];
	
		$('newsletter_newsletteritem_originalid').value = original_itemid;
		$('newsletter_itemoriginalid').value = original_id;
		$('newsletter_itemtype').value = original_type;
		$('newsletter_itemtitle').value = heading;
		setRadiogroupValue('form_newsletter', 'newsletter_edititem_image', imagemode)
		
		if (imagemode != "noimage") {
			new_image_preview(imageurl);
		}
		setRadiogroupValue('form_newsletter', 'newsletter_edititem_link', linkmode)
		$('newsletter_itemlinkurl').value = linkurl;

		$('newsletter_edititem_buttonadd').disabled = false;
		$('newsletter_additem_init_button').disabled = true;
		Element.hide('newsletter_additem_importoptions');

		if (imagemode != "noimage") {
			Element.show('newsletter_edititem_showimage');
			Element.show('newsletter_edititem_showimage_image');
		}
		if (imagemode != "item") {
			Element.hide('newsletter_edititem_image_item_container');		
		}
		if (imagemode == "archive") {
			$('selectImageButton').disabled = false;
		}
		Element.show('newsletter_listitem_knapbar');
		$('newsletter_edititem_buttonadd').value = "Gem ændringer i element";
		Element.show('newsletter_edititem');
		set_fck_content(content);
}

function newsletter_item_delete(int_id) {
	Element.show('ajaxloader_newsitemlist'); 
	var url = cmsUrl+'/modules/newsletter/newsletter.ajaxresponders.php';
	var pars = 'do=ajax_deleteItem&id='+int_id;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onFailure: reportAjaxError,
					onComplete: updateItemlist
				});

}


function setRadiogroupValue(str_formid, str_radiogroupname, str_value) {
	var theForm = $(str_formid);
	// get value of radio group
	var forminputs = theForm.getElementsByTagName("input");
	var inputs = $A(forminputs);
	inputs.each(function(input){
		if (input.type == "radio" && input.name == str_radiogroupname && input.value == str_value) {
			input.checked = true;
		}
	});
}

function getRadiogroupValue(str_formid, str_radiogroupname) {
	var rv;
	var theForm = $(str_formid);	
	// get value of radio group
	var forminputs = theForm.getElementsByTagName("input");
	var inputs = $A(forminputs);
	inputs.each(function(input){
		if (input.type == "radio" && input.name == str_radiogroupname && input.checked == true) {
			rv = input.value;
		}
	});
	return rv;
}

function save_item() {

	// Do save
	Element.show('ajaxloader_additem');
	if (newsletter_id == "") {
		if ($('newsletter_id_temporary').value == "") {
			// Generate temp ID
			$('newsletter_id_temporary').value = Math.floor(Math.random()*10001)+10000;
		}
		var item_istemp = 1;
		var save_id = $('newsletter_id_temporary').value
	} else {
		var save_id = newsletter_id;
	}

	var item_updateid = $('newsletter_newsletteritem_originalid').value;
	if (item_updateid == "") {
		var savemode = "insert";
	} else {
		var savemode = "update";	
	}
	// Collect inputs
	var item_original_id = $('newsletter_itemoriginalid').value;
	var item_original_type = $('newsletter_itemtype').value;
	var item_heading = encodeURIComponent($('newsletter_itemtitle').value);
	var item_content = get_fck_content("content_item");
	item_content = encodeURIComponent(item_content);
	var item_imagemode = getRadiogroupValue('form_newsletter', 'newsletter_edititem_image');
	var item_imageurl;
	if ($('newsletter_edititem_showimage_image')) {
		var item_imageurl = $('newsletter_edititem_showimage_image').src;
	}
	var item_linkmode = getRadiogroupValue('form_newsletter', 'newsletter_edititem_link');
	var item_linkurl = encodeURIComponent($('newsletter_itemlinkurl').value);
	// Post it
	var url = cmsUrl+'/modules/newsletter/newsletter.ajaxresponders.php';
	var pars = 'do=ajax_saveNewsletterItem&mode='+savemode
		+'&item_updateid='+item_updateid
		+'&item_original_id='+item_original_id
		+'&item_original_type='+item_original_type
		+'&item_heading='+item_heading
		+'&item_content='+item_content
		+'&item_imagemode='+item_imagemode
		+'&item_imageurl='+item_imageurl
		+'&item_linkmode='+item_linkmode
		+'&item_linkurl='+item_linkurl
		+'&item_istemp='+item_istemp
		+'&newsletter_id='+save_id;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onFailure: reportAjaxError,
					onComplete: updateItemlist
				});
}

function updateItemlist() {
	$('newsletter_newsletteritem_originalid').value = "";
	Element.show('ajaxloader_newsitemlist'); 
	var nid = $('newsletter_id_temporary').value;
	var url = cmsUrl+'/modules/newsletter/newsletter.ajaxresponders.php';
	var pars = 'do=ajax_updateItemlist&newsletter_id='+nid;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onFailure: reportAjaxError,
					onComplete: refreshItemlist
				});
}

function refreshItemlist(originalRequest) {
	$('newsletter_itemlist').innerHTML = originalRequest.responseText;	

	// Make itemlist sortable
	Sortable.create('newsletter_itemlist', {tag:'table', constraint:'vertical', onUpdate:updateSortable});
	reset_additem();
	Element.hide('ajaxloader_newsitemlist');
}

function abort_add_item() {
	var do_abort = true;
	var do_confirm = Element.visible('newsletter_edititem');

	if (do_confirm) {
		if (confirm("Er du sikker? Ændringer i dette element vil blive tabt!")){
			do_abort = true;	
		} else {
			do_abort = false;
		}
	}
	if (do_abort) {
		reset_additem();
		$("newsletter_newsletteritem_originalid").value = "";
		return true;
	} else {
		return false;
	}
}

function reset_additem() {
		Element.hide('ajaxloader_additem');
		Element.hide('newsletter_selectitemtype');
		Element.hide('newsletter_additem'); 
		Element.hide('newsletter_edititem'); 
		Element.hide('newsletter_listitem_knapbar');
		$('newsletter_edititem_buttonadd').disabled = true;

		// Reset itemtype selectors
		$('itemtype_select').disabled = false;
		$('itemtype_select').value = "";
		$('newsarchiveselector-relnews').value = "null";
		$('calendarselector-relevents').value = "null";
		$('menuselector-relpages').value = "null";
		Element.hide('newsletter_additem_newsitems');
		Element.hide('newsletter_additem_calendarevents');
		Element.hide('newsletter_additem_menupages');

		// Reset imported content
		$('newsletter_itemtitle').value = "";
		$('newsletter_imported_content').value = "content";
		$('newsletter_imported_summary').value = "summary";
		setRadiogroupValue('form_newsletter', 'newsletter_imported', 'content');
		Element.show('newsletter_additem_importoptions');

		// Reset image + link settings
		setRadiogroupValue('form_newsletter', 'newsletter_edititem_image', 'noimage');
		kill_image_preview();
		setRadiogroupValue('form_newsletter', 'newsletter_edititem_link', 'item');
		$('newsletter_itemlinkurl').value = "";
		$('newsletter_imported_contenthidden').innerHTML = "";
		Element.show('newsletter_edititem_image_item_container');		
		
		$('newsletter_edititem_buttonadd').value = "Tilføj til nyhedslisten";
		$('newsletter_additem_init_button').disabled = false;
}

function newsletter_additem_init() {
	$('newsletter_itemoriginalid').value = "";
	$('newsletter_additem_init_button').disabled = true;
	foldout('newsletter_selectitemtype');
	Element.show('newsletter_listitem_knapbar');
}

function addRelatedNews(int_id) {
	importContent(int_id);
}
function addRelatedEvent(int_id) {
	importContent(int_id);
}
function addRelatedPage(int_id) {
	importContent(int_id);
}


function importContent(int_id) {
	$('newsletter_itemoriginalid').value = int_id;
	var str_type = $('newsletter_itemtype').value;
	Element.show('ajaxloader_additem');
	
	var url = cmsUrl+'/modules/newsletter/newsletter.ajaxresponders.php';
	var pars = 'do=ajax_returnSingleitem&type='+str_type+'&id='+int_id+'&rich=0';
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onFailure: reportAjaxError,
					onComplete: updateImportedContent
				});

}
function newsletter_edititem_imagemode(mode) {
	// Kill existing img-preview
	kill_image_preview();
	switch(mode) {
		case "item":
			if ($('newsletter_edititem_imageselector')) {
				$('newsletter_edititem_imageselector').value = "Vælg billede...";
				$('newsletter_edititem_imageselector').disabled = false;
			}
			$('selectImageButton').disabled = true;
			Element.hide('selectImageDiv');
			break;
		case "archive":
			if ($('newsletter_edititem_imageselector')) {
				$('newsletter_edititem_imageselector').disabled = true;
			}
			$('selectImageButton').disabled = false;
			break;
		case "noimage":
			if ($('newsletter_edititem_imageselector')) {
				$('newsletter_edititem_imageselector').disabled = true;
			}
			$('selectImageButton').disabled = true;
			kill_image_preview();
			Element.hide('selectImageDiv');
			break;
	}
}
function newsletter_edititem_linkmode(mode) {
	var url_input = $('newsletter_itemlinkurl');
	switch(mode) {
		case "item":
			url_input.disabled = true;
			if (url_input.value == "http://") {
				url_input.value = "";
			}
			break;
		case "url":
			if (url_input.value == "") {
				url_input.value = "http://";
			}
			url_input.disabled = false;
			break;
		case "nolink":
			url_input.disabled = true;
			if (url_input.value == "http://") {
				url_input.value = "";
			}
			break;
	}
}

function selectImage() {
	$('selectImageButton').disabled = true;
	Element.hide('newsletter_edititem_showimage');
	kill_image_preview();

	// SetUp selectImageDiv
	$("selectImageDiv").innerHTML = "<table id='plainTable' width='100%' height='100%'><tr><td valign='top' width='25%' id='folderList'></td><td valign='top'><div id='imageList'></div></td></tr></table>";
	
	// Show it
	$("selectImageDiv").style.display = "block";

	// Load imageFolders
	$('folderList').innerHTML = "Henter billedmapper...";
	var dothis = "returnImageFolders";
	var url = '/cms/fckeditor_plugins/customImage/customImage.ajaxresponders.php';
	var pars = 'do=' + dothis;
	var myAjaxImages = new Ajax.Request(
		url, 
		{
			method: 'get', 
			parameters: pars, 
			onComplete: showFolders
		});
}

function showFolders(originalRequest) {
			$('folderList').innerHTML = originalRequest.responseText;
}
function folderClicked(obj) {
	$('imageList').innerHTML = "Henter billeder...";
	var fid = obj.id;
	fid = fid.split("_")[1];
	loadImages(fid);
}

function loadImages(fid) {
	var dothis = "returnFolderImages";
	var url = '/cms/fckeditor_plugins/customImage/customImage.ajaxresponders.php';
	var pars = 'do=' + dothis + '&fid=' + fid;
	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'get', 
			parameters: pars, 
			onComplete: showImages
		});
}
function kill_image_preview() {
	if ($('newsletter_edititem_showimage_image')) {
		var node = $('newsletter_edititem_showimage_image')
		var pnode = node.parentNode;
		pnode.removeChild(node);
	}
}

function new_image_preview(str_url) {
	var newimgtag = "<img id='newsletter_edititem_showimage_image' name='newsletter_edititem_showimage_image' src='"+str_url+"' width='150' alt='Preview' />";
	$('newsletter_edititem_showimage').innerHTML = newimgtag;
}

function imageClicked(obj) {
	var clickedRadio = obj.getElementsByTagName("input");
	clickedRadio[0].checked = true;
	var clickedImage = obj.getElementsByTagName("img");
	var newUrl = clickedImage[0].src.replace("/thumbs/","/")

	// Kill existing preview-IMG tag
	kill_image_preview();
	// Generate + insert new preview-IMG tag
	new_image_preview(newUrl);
	
	Element.show('newsletter_edititem_showimage');
	Element.show('newsletter_edititem_showimage_image');

	closeSelectImage();

} 

function closeSelectImage() {
	$('selectImageDiv').style.display = "none";
	$('selectImageButton').disabled = false;
}

function showImages(originalRequest) {
	$('imageList').innerHTML = originalRequest.responseText;
}

function highlight(obj) {
	obj.style.backgroundColor = "#ffffbe";
}

function highlight_off(obj) {
	obj.style.backgroundColor = "#f1f1e3";
}

function set_newsletter_itemimage(img_src) {
	if (img_src == "Vælg billede...") {
		kill_image_preview();
	} else {
		new_image_preview(img_src);
		Element.show('newsletter_edititem_showimage_image');
		Element.show('newsletter_edititem_showimage');
	}
}

function updateImportedContent(originalRequest) {
	var str_content = originalRequest.responseText;
	var arr_content = str_content.split("|||||");
	$('newsletter_itemtitle').value = arr_content[0];
	$('newsletter_imported_summary').value = arr_content[1];
	$('newsletter_imported_content').value = arr_content[2];
	$('newsletter_imported_contenthidden').innerHTML = arr_content[3];

	// Find images
	var nodes_images = $('newsletter_imported_contenthidden').getElementsByTagName('img');
	var images = $A(nodes_images);

	// Kill existing image drop-down
	if ($('newsletter_edititem_imageselector')) {
		var node = $('newsletter_edititem_imageselector')
		var pnode = node.parentNode;
		pnode.removeChild(node);
	}

	// Build image drop-down
	// Using innerHTML because of non IE compliance with setAttribute/onchange
	var html = "<select id='newsletter_edititem_imageselector' class='inputselect' onchange='set_newsletter_itemimage(this.value)'></select>";
	$('newsletter_edititem_selectimage').innerHTML = html;
	var imgselect = $('newsletter_edititem_imageselector');

	var imgselectoption = document.createElement("option");
	var imgselectoptiontext = document.createTextNode("Vælg billede...");
	imgselectoption.appendChild(imgselectoptiontext);

	imgselect.disabled = true;
	if (images.length == 0) {
		$('newsletter_edititem_image_item').disabled = true;
	}
	imgselect.appendChild(imgselectoption);

	// Add options to drop down
	images.each(function(image){
		if (image.alt == "") {
			image.alt = "(billed uden titel)";
		}
		var iopt = document.createElement("option");
		iopt.setAttribute("value", image.src);
		var iopttext = document.createTextNode(image.alt);
		iopt.appendChild(iopttext);
		imgselect.appendChild(iopt);
	});
	Element.hide('ajaxloader_additem');
	Element.show('newsletter_edititem');
	foldin('newsletter_selectitemtype');
	foldin('newsletter_additem'); 
	Element.show('newsletter_edititem');
	$('newsletter_edititem_buttonadd').disabled = false;
	set_fck_content(arr_content[2]);
}

function set_fck_content(str_content) {
	// First check if editor is loaded (iframe on page)
	var ifr = document.getElementsByTagName("iframe");
	if (ifr.length > 0) {
		// Get the editor instance that we want to interact with.
		var oEditor = FCKeditorAPI.GetInstance('content_item') ;
		oEditor.SetHTML(str_content); 
	} else {
		// Find text area that we want to interact with.
		var taa = document.getElementsByTagName("textarea");
		var tas = $A(taa);
		tas.each(function(ta){
			if (ta.name == "content_item") {
				ta.value = str_content;
			}
		});
	}
}

function get_fck_content(str_editorname) {
	// First check if editor is loaded (iframe on page)
	var ifr = document.getElementsByTagName("iframe");
	if (ifr.length > 0) {
		// Get the editor instance that we want to interact with.
		var oEditor = FCKeditorAPI.GetInstance(str_editorname) ;
		return oEditor.GetXHTML(); 
	} else {
		// Find text area that we want to interact with.
		var taa = document.getElementsByTagName("textarea");
		var tas = $A(taa);
		var return_value;
		tas.each(function(ta){
			if (ta.name == str_editorname) {
				return_value = ta.value;
			}
		});
	}
	return return_value;
}


function updateImportedSummary(originalRequest) {
	$('newsletter_imported_summary').value = originalRequest.responseText;
}

function itemtype_selected(str_type) {
	/*
		Possible str_type values:
			newsitem
			calendarevent
			page
			custom
	*/
	// Store value in hidden form field
	$('newsletter_itemtype').value = str_type;
	// Show relevant selector
	switch(str_type) {
	case "":
			foldin('newsletter_additem');
	case "newsitem":
		$('newsletter_additem_typetext').innerHTML = "nyhed";
		$('newsletter_edititem_linktotext').innerHTML = "nyheden";
		$('newsletter_edititem_imagefromtext').innerHTML = "nyheden";
		Element.show('newsletter_additem_h2');
		Element.show('newsletter_additem_newsitem');
		Element.hide('newsletter_additem_calendarevent');
		Element.hide('newsletter_additem_menupage');
		Element.hide('newsletter_edititem'); 
		Element.show('newsletter_edititem_image_item_container');
		Element.show('newsletter_edititem_link_item_container');
		$('newsletter_edititem_link_item').checked = true;
		Element.show('newsletter_additem_importoptions');
		break    
	case "calendarevent":
		$('newsletter_additem_typetext').innerHTML = "begivenhed";
		$('newsletter_edititem_linktotext').innerHTML = "begivenheden";
		$('newsletter_edititem_imagefromtext').innerHTML = "begivenheden";
		Element.show('newsletter_additem_h2');
		Element.hide('newsletter_additem_newsitem');
		Element.show('newsletter_additem_calendarevent');
		Element.hide('newsletter_additem_menupage');
		Element.hide('newsletter_edititem'); 
		Element.show('newsletter_edititem_image_item_container');
		Element.show('newsletter_edititem_link_item_container');
		$('newsletter_edititem_link_item').checked = true;
		Element.show('newsletter_additem_importoptions');
		break    
	case "page":
		$('newsletter_additem_typetext').innerHTML = "side";
		$('newsletter_edititem_linktotext').innerHTML = "siden";
		$('newsletter_edititem_imagefromtext').innerHTML = "siden";
		Element.show('newsletter_additem_h2');
		Element.hide('newsletter_additem_newsitem');
		Element.hide('newsletter_additem_calendarevent');
		Element.show('newsletter_additem_menupage');
		Element.hide('newsletter_edititem'); 
		Element.show('newsletter_edititem_image_item_container');
		Element.show('newsletter_edititem_link_item_container');
		$('newsletter_edititem_link_item').checked = true;
		Element.show('newsletter_additem_importoptions');
		break    
	case "custom":
		Element.hide('newsletter_additem_h2');
		Element.hide('newsletter_additem_newsitem');
		Element.hide('newsletter_additem_calendarevent');
		Element.hide('newsletter_additem_menupage');
		Element.show('newsletter_edititem'); 
		Element.hide('newsletter_edititem_image_item_container');
		Element.hide('newsletter_edititem_link_item_container');
		$('newsletter_edititem_link_nolink').checked = true;
		Element.hide('newsletter_additem_importoptions');
		$('itemtype_select').disabled = true;
		$('newsletter_edititem_buttonadd').disabled = false;
		set_fck_content("");
		break    
	}
	foldout('newsletter_additem');
}



/* Populate newsarchive selector */
function loadNewsarchivesList() {
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
	var container = $('newsletter_additem_newsarchives')
	container.innerHTML = originalRequest.responseText;
}

function loadAvaliableNews() {
	loadAvailableNews_do();
}

function loadAvailableNews_do() {
	var selected_newsarchive = $('newsarchiveselector-relnews').value
	// Load content and fold out
	Element.show('ajaxloader_additem');
	var url = cmsUrl+'/modules/news/news.ajaxresponders.php';
	var pars = 'do=ajax_returnAvailableNews&newsarchive_id='+selected_newsarchive;
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
	var container = $('newsletter_additem_newsitems')
	container.innerHTML = originalRequest.responseText;
	foldout('newsletter_additem_newsitems');
	// Only fold out if hidden
	Element.hide('ajaxloader_additem');
}


/* Populate calendar selector */
function loadCalendarsList() {
	// Load content and fold out
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
	var container = $('newsletter_additem_calendars')
	container.innerHTML = originalRequest.responseText;
}

function reportAjaxError(request)
	{
		alert('Det opstod en fejl på siden:\nAjax kald ikke gennemført');
	}

function loadAvaliableEvents() {
	loadAvailableEvents_do();
}

function loadAvailableEvents_do() {
	var selected_calendar = $('calendarselector-relevents').value
	// Load content and fold out
	Element.show('ajaxloader_additem');
	var url = cmsUrl+'/modules/events/events.ajaxresponders.php';
	var pars = 'do=ajax_returnAvailableEvents&calendar_id='+selected_calendar;
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
	var container = $('newsletter_additem_calendarevents')
	container.innerHTML = originalRequest.responseText;
	foldout('newsletter_additem_calendarevents');
	// Only fold out if hidden
	Element.hide('ajaxloader_additem');
}


/* Populate menu-selector */
function loadMenusList() {
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
	var container = $('newsletter_additem_menus')
	container.innerHTML = originalRequest.responseText;
}

function loadAvaliablePages() {
	loadAvailablePages_do();
}

function loadAvailablePages_do() {
	var selected_menu = $('menuselector-relpages').value
	// Load content and fold out
	Element.show('ajaxloader_additem');
	var url = cmsUrl+'/modules/pages/pages.ajaxresponders.php';
	var pars = 'do=ajax_returnAvailablePages&menu_id='+selected_menu;
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
	var container = $('newsletter_additem_menupages')
	container.innerHTML = originalRequest.responseText;
	foldout('newsletter_additem_menupages');
	Element.hide('ajaxloader_additem');
}


/* VERIFY AND SAVE NEWSLETTER */
function verify() {
	var valid = true;
	var error = "Kunne ikke gemme nyhedsbrevet:\n";
	if ($('title').value == "") {
		valid = false;
		error += "(*) Du skal skriv en titel";
	}
	if (valid) {
		saveNewsletter();
	} else {
		alert(error);
		return;
	}
}

function saveNewsletter() {
	document.forms['form_newsletter'].submit();
}

// Scriptaculous effects
function toggle_foldstate(str_elementid) {
	new Effect.Combo(str_elementid, {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false});
}

function foldout(str_elementid) {
	element = $(str_elementid);
	if (element.style.display == "none") {
		new Effect.OpenUp(str_elementid, {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false});
	}
}
function foldin(str_elementid) {
	element = $(str_elementid);
	if (element.style.display != "none") {
		new Effect.CloseDown(str_elementid, {duration: 0.5, scaleX: false, scaleY: true, scaleContent: false});
	}
}


Effect.OpenUp = function(element) {
	element = $(element);
	new Effect.BlindDown(element, arguments[1] || {});
}

Effect.CloseDown = function(element) {
	element = $(element);
	new Effect.BlindUp(element, arguments[1] || {});
}

Effect.Combo = function(element) {
  element = $(element);
  if(element.style.display == 'none') { new Effect.OpenUp(element, arguments[1] || {}); }
  else { new Effect.CloseDown(element, arguments[1] || {}); }
}

function updateSortable() {
	Element.show('ajaxloader_newsitemlist');
	var order = Sortable.serialize('newsletter_itemlist');
	order = encodeURIComponent(order);
	var url = cmsUrl+'/modules/newsletter/newsletter.ajaxresponders.php';
	var pars = 'do=ajax_saveReordered&order='+order;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onFailure: reportAjaxError,
					onComplete: reorderComplete
				});
}

function reorderComplete(originalRequest) {
	Element.hide('ajaxloader_newsitemlist');
}

/*
Newsletter-specific tab functions
*/

//Set tab to intially be selected when page loads:
//[which tab (1=first tab), ID of tab content to display]:
var initialtab=[1, "sc1"]

function do_onload(){
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

	// Init nyhedsliste
	if ($('newsletter_additem')) {
		Element.hide('ajaxloader_newsitemlist');
		Element.hide('newsletter_additem'); 
		Element.hide('newsletter_selectitemtype'); 
		Element.hide('ajaxloader_additem'); 
	
		Element.hide('newsletter_additem_newsitems'); 
		Element.hide('newsletter_additem_calendarevent'); 
		Element.hide('newsletter_additem_calendarevents'); 
		Element.hide('newsletter_additem_menupage'); 
		Element.hide('newsletter_additem_menupages'); 
	
		Element.hide('newsletter_edititem'); 

		Element.hide('newsletter_imported_contenthidden');
		$('selectImageButton').disabled = true;
		$('newsletter_itemlinkurl').disabled = true;
		Element.hide('newsletter_listitem_knapbar');
		$('newsletter_edititem_buttonadd').disabled = true;

		// Make itemlist sortable
		Sortable.create('newsletter_itemlist', {tag:'table', constraint:'vertical', onUpdate:updateSortable});
				
		// Populate item-category-selectors
		loadNewsarchivesList();
		loadCalendarsList();
		loadMenusList();
		Element.hide('ajaxloader_interestgroups'); 
	}

	// Init newsletter sendout
	if ($('ajaxloader_sendout')) {
		Element.hide('ajaxloader_sendout');
		Element.hide('recipients_list_container');
	}
}

if (window.addEventListener)
window.addEventListener("load", do_onload, false)
else if (window.attachEvent)
window.attachEvent("onload", do_onload)
else if (document.getElementById)
window.onload=do_onload