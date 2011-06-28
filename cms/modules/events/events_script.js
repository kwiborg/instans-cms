var oldurl = "";
var oldimageid = "";
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

function sletCalendar(id, content_identifier, filter_calendar)
{
 if (confirm("Er du sikker på, at du vil slette?"))
 {
  location = "index.php?content_identifier=" + content_identifier + "&dothis=slet&id=" + id + "&filter_calendar=" +filter_calendar;
 }
}

function calendarSelected(selected_id) {
	var t = document.getElementById("filter_time").value;
	var fd = document.getElementById("filter_display").value;
	var loc = "index.php?content_identifier=events&filter_calendar=" + selected_id;
	if (t != "") {
		loc += "&filter_time=" + t;
	}
	if (fd != "") {
		loc += "&filter_display=" + fd;
	}
	location = loc;
}

function checkCalendarSelected() {
	var cid = document.getElementById("filter_calendar").value;
	if (cid == "ALL_CALENDARS") {
		alert("Du skal vælge én kalender for at oprette et arrangement!");
	} else {
		opretNy();
	}
}

function verify()
{
 theForm = document.forms[0];
 if (theForm.heading.value == "")
 {
  alert("Udfyld venligst feltet Overskrift.");
  return;
 }
 if (theForm.duration_selector_res.value == "")
 {
  alert("Vælg venligst, om arrangementet løber over en eller flere dage.");
  return;
 } 
 if (!validDate(theForm.startdate.value))
 {
  alert("Indtast venligst en gyldig dato i feltet Startdato.");
  return;
 }
 if (theForm.duration_selector_res.value == "1" && !validDate(theForm.enddate.value))
 {
  alert("Indtast venligst en gyldig dato i feltet Slutdato.");
  return;
 }
 if (theForm.published_res.value == "")
 {
  alert("Vælg venligst arrangementets status - færdigredigeret eller ej?");
  return;
 }
 if (theForm.title.value == "")
 {
  alert("Udfyld venligst feltet Titel.");
  return;
 }
/*
 if (theForm.protection_selector_res.value == "")
 {
  alert("Vælg venligst arrangementets beskyttelse - kan det redigeres af alle eller kun af dig selv?");
  return;
 }
*/
 document.forms[0].dothis.value = "gem";
 document.forms[0].submit();
}

function skiftDuration(mode)
{
 theForm = document.forms[0];
 if (mode == 0) 
 {
  theForm.enddate.disabled = true;
  theForm.enddate.value = "";
  document.getElementById("enddate").className = "inputfelt_kort_disabled";
 }
 if (mode == 1) 
 {
  theForm.enddate.disabled = false;
  document.getElementById("enddate").className = "inputfelt_kort";
 }
}

function setCalendar(id, calendar, sortby, sortdir)
{
 location = "index.php?content_identifier=events&dothis=calendar&id=" + id + "&calendar=" + calendar + "&sortby=" + sortby + "&sortdir=" + sortdir;
}

function indstilCalendarDropdown(id, calendarid)
{
 theForm = document.forms[0];
 if (eval("theForm.calendars_dropdown_" + id)) eval("theForm.calendars_dropdown_" + id + ".value = " + calendarid); 
}

/*
Calendar-specific tab scripts
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
	// Init rewrite
	if (document.getElementById("ajaxloader_rewrite")) {
		Element.hide('ajaxloader_rewrite');
	}
	// Only set Duration after the page has loaded and the date-picker script has run
	// setTimeout used to allow the date-picker which also runs onload to go first
	if ($("startdate")) {
		setTimeout("skiftDuration(orginal_Duration)",1000);
	}
}

if (window.addEventListener)
window.addEventListener("load", do_onload, false)
else if (window.attachEvent)
window.attachEvent("onload", do_onload)
else if (document.getElementById)
window.onload=do_onload