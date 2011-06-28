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

function newsfeedSelected(selected_id) {
	var t = document.getElementById("filter_time").value;
	var fd = document.getElementById("filter_display").value;
	var loc = "index.php?content_identifier=news&newsfeedid=" + selected_id;
	if (t != "") {
		loc += "&filter_time=" + t;
	}
	if (fd != "") {
		loc += "&filter_display=" + fd;
	}
	location = loc;
}

function slet_nyhed(id, content_identifier, newsfeedid)
{
 if (confirm("Er du sikker på, at du vil slette?"))
 {
  location = "index.php?content_identifier=" + content_identifier + "&dothis=slet&id=" + id + "&newsfeedid=" + newsfeedid;
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
 if (theForm.news_date.value == "")
 {
  alert("Udfyld venligst feltet Nyhedens dato.");
  return;
 } 
 if (theForm.published_res.value == "")
 {
  alert("Vælg venligst nyhedens status - færdigredigeret eller ej?");
  return;
 }
 if (theForm.timelimit[1].checked && (theForm.limit_start.value=="" || theForm.limit_end.value==""))
 {
  alert("Udfyld venligst Startdato og Slutdato for tidsbegrænset visning.");
  return;
 }
 if (theForm.timelimit[1].checked && !validDate(theForm.limit_start.value))
 {
  alert("Indtast venligst en gyldig dato i feltet for tidsbegrænsningens startdato.");
  return;
 }
 if (theForm.timelimit[1].checked && !validDate(theForm.limit_end.value))
 {
  alert("Indtast venligst en gyldig dato i feltet for tidsbegrænsningens slutdato.");
  return;
 }
/*
 if (theForm.protection_selector_res.value == "")
 {
  alert("Vælg venligst nyhedens beskyttelse - kan den redigeres af alle eller kun af dig selv?");
  return;
 }
*/
 document.forms[0].dothis.value = "gem";
 document.forms[0].submit();
}

/*function newsletter(id, state)
{
 location = "index.php?content_identifier=news&dothis=newsletter&state=" + state + "&id=" + id;
}*/

function setFeed(id, feed, sortby, sortdir)
{
 location = "index.php?content_identifier=news&dothis=newsfeed&id=" + id + "&feed=" + feed + "&sortby=" + sortby + "&sortdir=" + sortdir;
}

function setNewsletter(id, newsletter_id, sortby, sortdir)
{
 location = "index.php?content_identifier=news&dothis=newsletter&id=" + id + "&newsletter_id=" + newsletter_id + "&sortby=" + sortby + "&sortdir=" + sortdir;
}

function setFrontpage(id, state, sortby, sortdir, newsfeedid, offset)
{
 location = "index.php?content_identifier=news&dothis=frontpage&id=" + id + "&state=" + state + "&sortby=" + sortby + "&sortdir=" + sortdir + "&newsfeedid=" + newsfeedid + "&offset=" + offset;
}

function indstilNewsfeedDropdown(id, feedid)
{
 theForm = document.forms[0];
 if (eval("theForm.newsfeeds_dropdown_" + id)) eval("theForm.newsfeeds_dropdown_" + id + ".value = " + feedid); 
}

function indstilNewsletterDropdown(id, letterid)
{
 theForm = document.forms[0];
 if (eval("theForm.newsletters_dropdown_" + id)) eval("theForm.newsletters_dropdown_" + id + ".value = " + letterid); 
}

function enableLimit(s)
{
 theForm = document.forms[0];
 theForm.limit_res.value = s;
 if (s==0) {
  theForm.limit_start.disabled = true;
  theForm.limit_start.value = "";
  theForm.limit_end.disabled = true;
  theForm.limit_end.value = "";
  document.getElementById("limit_start").className = "inputfelt_kort_disabled";
  document.getElementById("limit_end").className = "inputfelt_kort_disabled";
 }
 if (s==1) {
  theForm.limit_start.disabled = false;
  theForm.limit_start.value = "";
  theForm.limit_end.disabled = false;
  theForm.limit_end.value = "";
  document.getElementById("limit_start").className = "inputfelt_kort";
  document.getElementById("limit_end").className = "inputfelt_kort";
 }
}

/*
News-specific tab functions
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
}

if (window.addEventListener)
window.addEventListener("load", do_onload, false)
else if (window.attachEvent)
window.attachEvent("onload", do_onload)
else if (document.getElementById)
window.onload=do_onload