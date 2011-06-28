var oldurl = "";
var oldimageid = "";

if (window.addEventListener) {
	window.addEventListener("load", do_onload, false);
} else if (window.attachEvent) {
	window.attachEvent("onload", do_onload);
} else if (document.getElementById) {
	window.onload=do_onload;
}

function useImage(obj) {
	// toggle disable
	if (obj.checked) {
		if (oldurl != "") {
			//restore old values
			$("image_url").value = oldurl;
			$("imageid").value = oldimageid;
			// Show thumb and enable button
			$('imgthumb').style.display = "block";
			$("selectImageButton").disabled = false;
		} else {
			selectImage(); 
		}
	} else {
		$('selectImageDiv').style.display = "none";
		$('imgthumb').style.display = "none";
		$('imageid').value = "";
		oldurl = $("image_url").value;
		oldimageid = $("imageid").value;
		$("selectImageButton").disabled = true;
	}
}

function closeSelectImage() {
	$('selectImageDiv').style.display = "none";
	$('imgthumb').style.display = "block";
	$('image_url').disabled = false;
	$('selectImageButton').disabled = false;
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


function imageClicked(obj) {
	var clickedId = obj.id;
	clickedId = clickedId.split("_")[1];
	$('imageid').value = clickedId;
	
	var clickedRadio = obj.getElementsByTagName("input");
	clickedRadio[0].checked = true;
	var clickedImage = obj.getElementsByTagName("img");
	var newUrl = clickedImage[0].src.replace("/thumbs/","/")
	$('imgthumb').src = clickedImage[0].src;
	$('image_url').value = newUrl;
	closeSelectImage();
} 

function showImages(originalRequest) {
	$('imageList').innerHTML = originalRequest.responseText;
}

function selectImage() {
	$('selectImageButton').disabled = true;

	// Get checkbox for positioning
	var icheck = document.getElementById("use_coverimage");

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


function saveProductGroup(){
	var valid = true;
	var errorMsg = 'Udfyld venligst følgende felter:\n';

	$('form_productgroup').group_number.value = trim($('form_productgroup').group_number.value);
	if ($('form_productgroup').group_number.value == "") {
		errorMsg += '(*) Varegruppe nummer\n';
		valid = false;
	}
	$('form_productgroup').group_name.value = trim($('form_productgroup').group_name.value);
	if ($('form_productgroup').group_name.value == "") {
		errorMsg += '(*) Varegruppe navn\n';
		valid = false;
	}
	$('form_productgroup').group_desc.value = trim($('form_productgroup').group_desc.value);
/*
	if ($('form_productgroup').group_desc.value == "") {
		errorMsg += '(*) Varegruppe beskrivelse\n';
		valid = false;
	}
*/
	if (valid) {
		$('form_productgroup').submit();
	} else {
		alert(errorMsg);
	}
}

function groupDelete(groupid) {
	if(confirm("Er du sikker på at du vil slette varegruppen? Denne handling kan ikke fortrydes.")) {
//		$('dothis').value = "deleteproductgroup";
//		$('groupid').value = groupid;
		window.location='index.php?content_identifier=shopproductgroups&dothis=deleteproductgroup&groupid='+groupid;
		return false;
	} else {
		return false;
	}
}

function showFolders(originalRequest) {
			$('folderList').innerHTML = originalRequest.responseText;
}

function highlight(obj) {
	obj.style.backgroundColor = "#ffffbe";
}

function highlight_off(obj) {
	obj.style.backgroundColor = "#f1f1e3";
}


function do_onload() {
	Element.hide('ajaxloader_rewrite');
}