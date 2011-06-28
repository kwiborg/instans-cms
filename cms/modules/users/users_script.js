function isAlphaNumeric(mystring)
{
 var re = /^[a-zA-Z0-9_\@\.]*$/;
 return re.test(mystring);
}

function verify()
{
 theForm = document.forms[0];
 if (theForm.username.value == "") {
  alert("Udfyld venligst feltet Brugernavn.");
  return;
 }
 if (!isAlphaNumeric(theForm.username.value)) {
  alert("Brugernavn må kun indeholde a-z, A-Z og 0-9. Mellemrum og tegn er ikke tilladt.");
  return;  
 }
 if (theForm.password1.value != theForm.password2.value) {
  alert("Du har ikke indtastet det samme password to gange. Prøv venligst igen.");
  return;
 }
 if (theForm.password1.value == "") {
  alert("Udfyld venligst Password.");
  return;
 }
 if (!isAlphaNumeric(theForm.password1.value)) {
  alert("Password må kun indeholde a-z, A-Z og 0-9. Mellemrum og tegn er ikke tilladt.");
  return;  
 }
 if (theForm.firstname.value == "" || theForm.lastname.value == "") {
  alert("Udfyld venligst som minimum Fornavn og Efternavn.");
  return;  
 }
 ALL = document.getElementsByTagName("input");
 var int_groupschecked = 0;
 for (i=0; i<ALL.length; i++) {
	if (ALL[i].id.indexOf("B_")>-1 && ALL[i].checked == true) {
		int_groupschecked++;
	}		
 }
 if (int_groupschecked < 1) {
  alert("Du skal placere brugeren i mindst én brugergruppe.");
  return;  
 }


 for (i=0; i<ALL.length; i++) {
  if (ALL[i].id.indexOf("B_")>-1 && ALL[i].disabled) ALL[i].disabled = false; 
 }
 document.forms[0].dothis.value = "gem";
 document.forms[0].submit();
}

function inArray(A, E)
{
 for(z=0; z<A.length; z++) {
  if (A[z] == E) return true;
 }
 return false;
}

function ajax_check_user_exists(username, userid){
	var url = cmsUrl + "/modules/users/users.ajaxresponders.php";
	var pars = "action=check_if_user_exists&username="+username+"&userid="+userid;
	var myAjax = new Ajax.Request(
		url,
		{	
			method: "post",
			parameters: pars,
			onFailure: reportAjaxError,
			onComplete: user_exists_alert
		}
	);		
}

function user_exists_alert(originalRequest){
	if (originalRequest.responseText == "1"){
		alert("Der findes allerede en bruger med det valgte brugernavn. Vælg venligst et andet.");
		document.forms[0].username.value = "";
	}
}

function reportAjaxError(request){
	alert("Der opstod en fejl i Ajax-forespørgslen. Data kunne ikke hentes fra serveren.");
}

/*
USERS-specific scripts for tabs
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
}

if (window.addEventListener) {
	window.addEventListener("load", do_onload, false);
} else if (window.attachEvent) {
	window.attachEvent("onload", do_onload);
} else if (document.getElementById) {
	window.onload=do_onload;
}


/// IMAGE SELECTOR

function selectImage() {
	$('selectImageButton').disabled = true;
	//Element.hide('newsletter_edititem_showimage');
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
	var clickedId = obj.id;
	clickedId = clickedId.split("_")[1];
	$('imageid').value = clickedId;
	
	var clickedRadio = obj.getElementsByTagName("input");
	clickedRadio[0].checked = true;
	var clickedImage = obj.getElementsByTagName("img");
	var newUrl = clickedImage[0].src.replace("/thumbs/","/")
	$('imgthumb').src = clickedImage[0].src;
	$('imgthumb').style.display = "inline";
	$('image_url').value = newUrl;
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

function entsub(myform) {
	if (window.event && window.event.keyCode == 13) myform.submit();
	return true;
}