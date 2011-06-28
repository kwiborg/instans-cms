addLoadEvent(picturearchive_init);

function ajax_load_images(folder_id){
	$("fileView").innerHTML = "Henter data - vent venligst...";
	var url = '/cms/modules/picturearchive/picturearchive.ajaxresponders.php';
	var pars = 'do=new_show_images&folder_id='+folder_id
	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'get', 
			parameters: pars, 
			onComplete: new_show_images,
			onFailure: function(){alert("Der opstod en AJAX-relateret fejl. Prøv venligst igen senere.")}
		});
}

function new_show_images(originalRequest){
	temp = originalRequest.responseText.split("¤¤¤¤¤||¤¤¤¤¤");
	$("folderView").innerHTML = temp[0];
	$("fileView").innerHTML = temp[1];
}

function picturearchive_init() {
	var picturecontainerRows = document.getElementsByClassName('picturecontainerRow');
	for (var i=0; i < picturecontainerRows.length; i++) {
		picturecontainerRows[i].setAttribute("loaded", "false"); 
	}

	// Set Row displayMethod (outside scope of function to allow access by other functions)
	// Take browser variations of <tr> display property into account
	if ((navigator.appName).indexOf("Microsoft")!=-1) {
		displayMethod = "block";
	} else {
		displayMethod = "table-row";
	}
	
	if (undefined!==window.openFolder) {
		hideShowFolder(window.openFolder);
	}

	// Hide dropbox ajax loadindicator
	if ($('ajaxloader_dropbox')) {
		Element.hide('ajaxloader_dropbox');
		
		Element.hide('dropbox_help');
		Element.hide('dropbox_errorlog');
		Element.hide('dropbox_importlog');
	
		$('dropbox_help_activate').onclick = function sdp(){Element.toggle('dropbox_help');return false;};

	
		// Create global eventhandler for dropbox
		var globalDropboxHandler = {
			onCreate: function(){
				Element.show('ajaxloader_dropbox');
			},
	
			onComplete: function() {
				if(Ajax.activeRequestCount == 0){
					Element.hide('ajaxloader_dropbox');
				}
			}
		};
	
		Ajax.Responders.register(globalDropboxHandler);
	}

}

function toggleAdvancedOptions() {
	var advButton = $('toggleAdvancedButton');
	var advDiv = $('advancedOptions');
	if (advButton.value == "Flere muligheder") {
		advButton.value = "Færre muligheder";
		advDiv.style.display = "block";
	} else {
		advButton.value = "Flere muligheder";
		advDiv.style.display = "none";
	}
}

function loadImages(fid) {
	var currentRow = $('picturecontainerRow_' + fid);
	currentRow.style.display = displayMethod;
	var dothis = "picturearchive_returnImages";
	var url = '/cms/modules/picturearchive/picturearchive.ajaxresponders.php';
	var pars = 'do=' + dothis + '&fid=' + fid;
	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'get', 
			parameters: pars, 
			onComplete: function(originalRequest) { showImages(originalRequest, fid); }
		});
}

function showImages(originalRequest, fid) {
	var currentRow = $('picturecontainerRow_' + fid);
	var currentCell = $('picturecontainerCell_' + fid);
	currentCell.innerHTML = originalRequest.responseText;
	currentRow.setAttribute("loaded", "true");
	size();
}

function hideShowFolder(folderID) {
	// state -1 = minus (expanded)
	// state  1 = plus  (folded)

	// get currents
	var currentButton = $('foldeknap_' + folderID);
	var currentRow = $('picturecontainerRow_' + folderID);
	var currentCell = $('picturecontainerCell_' + folderID);
	var currentState = $('foldeknap_state_' + folderID);
	
	// Return if folder doesn't exist on page
	if (!currentButton) return;

	// Swap button state
	if (currentState.value == 1) {
		currentState.value = -1;
		currentButton.value = "-";
	} else {
		currentState.value = 1;
		currentButton.value = "+";
	}	
	
	// Set display of Row
	if (currentState.value == -1) {
		if (currentRow.loaded != "true") {
			loadImages(folderID);
		} else {
			currentRow.style.display = displayMethod;
		}
	} else {
		currentRow.style.display = "none";
	}
	 size();
}

function opretNyMappe()
{
 location = "index.php?content_identifier=picturearchive&dothis=opretnymappe";
}

function opretBillede(folder_id)
{
 location = "index.php?content_identifier=picturearchive&dothis=opretbillede&trin=1&folderid=" + folder_id;
}

function redigerMappe(folder_id)
{
 location = "index.php?content_identifier=picturearchive&dothis=redigermappe&trin=1&folderid=" + folder_id;
}

function verify_mappe()
{
 theForm = document.forms[0];
 if (theForm.mappenavn.value == "")
 {
  alert("Indtast venligst mappenavn.");
  return;
 }
 theForm.dothis.value = "gem_mappe";
 theForm.submit(); 
}

function verify_billede(trin, type)
{
 theForm = document.forms[0];
 if (trin==1)
 {
  if (theForm.billede.value == "")
  {
   alert("Vælg venligst et billede.");
   return;
  }
 }
 if (trin == 2)  theForm.billedtype.value = type;
 if (trin == 2 && type == 2)
 {
  if (theForm.imagewidth.value == "" || theForm.imageheight.value == "")
  {
   alert("Udfyld venligst både højde og bredde.");
   return;
  }
  if (theForm.quality.value == "" || theForm.quality.value < 0 || theForm.quality.value > 100)
  {
   alert("Indtast venligst ønsket komprimeringskvalitet (0-100).");
   return;
  }
 }
 theForm.dothis.value = "upload_billede";
 theForm.submit(); 
}

function prop(what) {
	theForm = document.forms[0];
	if (!theForm.proportioner.checked) {
		document.getElementById('thisimage').width = theForm.imagewidth.value;
		document.getElementById('thisimage').height = theForm.imageheight.value;
	} else {
		var oX = theForm.original_bredde.value;
		var oY = theForm.original_hoejde.value;
		var gX = theForm.grundtal_bredde.value;
		var gY = theForm.grundtal_hoejde.value;
		var X  = theForm.imagewidth.value;
		var Y  = theForm.imageheight.value;

		// Calculate new size
		if (what=="x") {
			var scale_factor = X / oX;
		} else {
			var scale_factor = Y / oY;
		}
		var new_w = Math.round(oX * scale_factor);
		var new_h = Math.round(oY * scale_factor);

		// Calculate display size
		if ((new_w > oX) || (new_h > oY)){
			var display_w = oX;
			var display_h = oY;
			var new_w = oX;
			var new_h = oY;
		} else {
			var display_w = new_w;
			var display_h = new_h;
		}

		theForm.imagewidth.value = new_w;
		theForm.imageheight.value = new_h;
		document.getElementById('thisimage').width = display_w;
		document.getElementById('thisimage').height = display_h; 
		theForm.size_changed.value = "1";
	}
}

function nulstil()
{
 theForm = document.forms[0];
 gX = theForm.grundtal_bredde.value;
 gY = theForm.grundtal_hoejde.value;
 theForm.imageheight.value = gY;
 theForm.imagewidth.value = gX;
 theForm.size_changed.value = "0";
 document.getElementById('thisimage').width 	= theForm.imagewidth.value;
 document.getElementById('thisimage').height 	= theForm.imageheight.value;
}

function sletBillede(id,returntofolder)
{
 if (confirm("Du er ved at slette et billede. Hvis billedet er benyttet på hjemmesiden, vil der komme en fejl på siden. Er du sikker på at du vil slette billedet?")) {
  location = "index.php?content_identifier=picturearchive&dothis=sletbillede&imageid="+id+"&folder_id="+returntofolder;
 }
}

function sletMappe(id)
{
 if (confirm("Vil du slette mappen?")) {
  location = "index.php?content_identifier=picturearchive&dothis=sletmappe&folderid="+id;
 }
}

function getDropboxSettings() {
		// Get values from dropbox form
		var arr_dropbox = new Array();
		arr_dropbox["imageFolder"] = getRadiogroupValue("defaultForm", "imageFolder");
		arr_dropbox["imageKeepFolderstructure"] = Form.Element.getValue('imageKeepFolderstructure');
		if (arr_dropbox["imageKeepFolderstructure"] != "1") {
			arr_dropbox["imageKeepFolderstructure"] = 0;
		}
		arr_dropbox["imagepublicFolder"] = Form.Element.getValue('imagepublicFolder');
		if (arr_dropbox["imagepublicFolder"] != "1") {
			arr_dropbox["imagepublicFolder"] = 0;
		}
		arr_dropbox["imageMaxSize"] = $('imageMaxSize').value; // Max size for standard image
		arr_dropbox["imageMinOriginalsize"] = $('imageMinOriginalsize').value; // Min size for original image
		arr_dropbox["imageKeepOriginal"] = Form.Element.getValue('imageKeepOriginal');
		if (arr_dropbox["imageKeepOriginal"] != "1") {
			arr_dropbox["imageKeepOriginal"] = 0;
		}
		arr_dropbox["imageTitle"] = getRadiogroupValue("defaultForm", "imageTitle");
		arr_dropbox["imageAlt"] = $('imageAlt').value;
		arr_dropbox["imageDescription"] = getRadiogroupValue("defaultForm", "imageDescription");
		arr_dropbox["imageCustomDescription"] = $('imageCustomDescription').value;
		arr_dropbox["batchNumber"] = $('dropbox_batchnumber').value;
		return arr_dropbox;
}

function dropboxImport() {
	if (confirm("Vil du importere billederne i din dropbox? Billederne bliver flyttet fra dropbox'en til en ny billedmappe.")) {
		// Disable button
		var btn = $('importBtn');
		btn.disabled = true;
		dropboximport_setstatus("Scanner indhold af dropbox mappe og etablerer import kø");

		var arr_dropbox = getDropboxSettings();

		var dothis = "ajax_dropboximport_init";
		var url = '/cms/modules/picturearchive/picturearchive.ajaxresponders.php';
		var pars = 'do=' + dothis;
		pars += '&imageFolder=' + arr_dropbox["imageFolder"];
		pars += '&imageKeepFolderstructure=' + arr_dropbox["imageKeepFolderstructure"];
		pars += '&imagepublicFolder=' + arr_dropbox["imagepublicFolder"];
		var myAjax = new Ajax.Request(
			url, 
			{
				method: 'post', 
				parameters: pars, 
				onComplete: dropboximport_init_complete
			});
	}
}

function dropboximport_init_complete(originalRequest) {
	var arr_status = originalRequest.responseText.split("|||");
	if (arr_status[0] = "success") {
		$('dropbox_batchnumber').value = arr_status[1];
		dropboximport_setstatus("Begynder upload af billedfiler...");
		dropboximport_processuploads();
	} else {
		dropboximport_setstatus("Der opstod en ukendt fejl ved import af dropbox folder. Slet indhold af dropbox mappen og prøv igen.");
		$('importBtn').disabled = false;
	}
}

function dropboximport_processuploads(batch_number) {
	var arr_dropbox = getDropboxSettings();

	var dothis = "ajax_dropboximport_upload";
	var url = '/cms/modules/picturearchive/picturearchive.ajaxresponders.php';
	var pars = 'do=' + dothis;
	pars += '&batchNumber=' + arr_dropbox["batchNumber"];
	pars += '&imageMaxSize=' + arr_dropbox["imageMaxSize"];
	pars += '&imageMinOriginalsize=' + arr_dropbox["imageMinOriginalsize"];
	pars += '&imageKeepOriginal=' + arr_dropbox["imageKeepOriginal"];
	pars += '&imageTitle=' + arr_dropbox["imageTitle"];
	pars += '&imageAlt=' + arr_dropbox["imageAlt"];
	pars += '&imageDescription=' + arr_dropbox["imageDescription"];
	pars += '&imageCustomDescription=' + arr_dropbox["imageCustomDescription"];

	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'post', 
			parameters: pars, 
			onComplete: dropboximport_processuploads_complete
		});
}

function dropboximport_processuploads_complete(originalRequest) {
	var arr_status = originalRequest.responseText.split("|||");
	if (arr_status[0] == "success" && arr_status[1] == "continue") {
		/* success|||continue|||$rem_items|||$row[NAME] */
		dropboximport_setstatus("Billedet '"+arr_status[3]+"' er uploadet, "+arr_status[2]+" billeder i kø...");
		dropboximport_updatelog("dropbox_importlog", "OK: "+arr_status[3]);
		dropboximport_processuploads();
	} else if (arr_status[0] == "success" && arr_status[1] == "complete")  {
		/* success|||complete|||$success_count|||$error_count|||$row[NAME] */
		dropboximport_setstatus("Import fuldført! "+arr_status[2]+" billeder uploadet korrekt, "+arr_status[3]+" fejl - se importlog herunder");
		if (arr_status[2]>0) {
			dropboximport_updatelog("dropbox_importlog", "OK: "+arr_status[4]);
		}
		Element.show('dropbox_errorlog');
		Element.show('dropbox_importlog');
		$('importBtn').disabled = false;
		$('importBtn').value = "Genindlæs billedarkiv!";
		$('importBtn').onclick = function rl(){location.reload()};
	} else if (arr_status[0] == "error" && arr_status[1] == "continue") {
		/* error|||continue|||thumb|||$row[ID]|||$row[NAME]|||$row[EXTENSION]|||$row[SIZE]|||$errorfile_path|||$rem_items */
		dropboximport_setstatus("Billedet '"+arr_status[4]+"' er IKKE uploadet (se importlog herunder), "+arr_status[8]+" billeder i kø...");
		dropboximport_updatelog("dropbox_errorlog", "FEJL: "+arr_status[7]+" ("+arr_status[6]+" bytes)");
		dropboximport_processuploads();

	} else if (arr_status[0] == "error" && arr_status[1] == "complete") {
		/* error|||complete|||thumb|||$row[ID]|||$row[NAME]|||$row[EXTENSION]|||$row[SIZE]|||$errorfile_path|||$rem_items  */
		dropboximport_setstatus("Import fuldført! "+arr_status[8]+" billeder uploadet korrekt, "+arr_status[9]+" fejl - se importlog herunder");
		dropboximport_updatelog("dropbox_errorlog", "FEJL: "+arr_status[7]+" ("+arr_status[6]+" bytes)");
		Element.show('dropbox_errorlog');
		Element.show('dropbox_importlog');
		$('importBtn').disabled = false;
		$('importBtn').value = "Genindlæs billedarkiv!";
		$('importBtn').onclick = function rl(){location.reload()};
	} else {
		dropboximport_updatelog("dropbox_errorlog", "FEJL: "+originalRequest.responseText);
		dropboximport_setstatus("FEJL! Se errorlog!");
		Element.show('dropbox_errorlog');
		Element.show('dropbox_importlog');
	}	
}

function dropboximport_updatelog(str_logid, str_message) {
	$(str_logid).innerHTML = $(str_logid).innerHTML+str_message+"<br />";
}

function dropboximport_setstatus(str_msg) {
	$('dropbox_importstatus').innerHTML = str_msg;
} 

function reorderSave(){
	document.forms[0].order.value = Sortable.serialize("sortThis");
	document.forms[0].dothis.value = "saveorder";
	document.forms[0].submit();
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
