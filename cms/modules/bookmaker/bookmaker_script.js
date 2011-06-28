addLoadEvent(bookmaker_init);
addLoadEvent(colorizeSectionDivs);

function bookmaker_init() {
	var thematrix = document.getElementById("thematrix");
	if (thematrix) {
		tds = thematrix.getElementsByTagName("td");
		addEditButtons(tds);
		ths = thematrix.getElementsByTagName("th");
		addEditButtons(ths);
	}
}

var oldurl = "";
function useCoverImage(obj) {
	// toggle disable
	var iurl = document.getElementById("coverimage_url");
	var ibutton = document.getElementById("selectImageButton");

	if (iurl.disabled && obj.checked) { /// Hvis der ikke er en url og box er checket
		if (oldurl != "") {
			iurl.value = oldurl;
			$('coverthumb').style.display = "block";
			ibutton.disabled = false;
			iurl.disabled = false;
		} else {
			var folderid = $('use_coverimage_heading').getAttribute("folderid");
			selectImage(folderid); 
		}
	} else {
		$('selectImageDiv').style.display = "none";
		$('coverthumb').style.display = "none";
		oldurl = iurl.value;
		iurl.value = "";
		iurl.disabled = true;
		ibutton.disabled = true;
	}		
}

function closeSelectImage() {
	$('selectImageDiv').style.display = "none";
	$('coverthumb').style.display = "block";
	$('coverimage_url').disabled = false;
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
	var clickedRadio = obj.getElementsByTagName("input");
	clickedRadio[0].checked = true;

	var clickedImage = obj.getElementsByTagName("img");
	var newUrl = clickedImage[0].src.replace("/thumbs/","/")
	$('coverthumb').src = clickedImage[0].src;
	$('coverimage_url').value = newUrl;
	closeSelectImage();
} 

function showImages(originalRequest) {
	$('imageList').innerHTML = originalRequest.responseText;
}

function selectImage(folder_id) {
	$('selectImageButton').disabled = true;

	// Get checkbox for positioning
	var icheck = document.getElementById("use_coverimage");

	// SetUp selectImageDiv
	var selectImageDiv = document.getElementById("selectImageDiv");
	
	selectImageDiv.innerHTML = "<table id='plainTable' width='100%' height='100%'><tr><td valign='top' width='25%' id='folderList'></td><td valign='top'><div id='imageList'></div></td></tr></table>";
	
	// Show it
	selectImageDiv.style.display = "block";

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
	loadImages(folder_id);
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

function addEditButtons(what) {
	for (var i=0; i < what.length; i++) {
		var br = document.createElement("br");
		var br2 = document.createElement("br");
		var button = document.createElement("input");
		br.setAttribute("class", "edit_br");
		br2.setAttribute("class", "edit_br");
		button.setAttribute("type", "button");
		button.setAttribute("class", "lilleknap");
		button.setAttribute("className", "lilleknap");
		button.setAttribute("value", "Rediger");
		button.onclick = function() {edit(this.parentNode)};
		what[i].appendChild(br);
		what[i].appendChild(br2);
		what[i].appendChild(button);
	}
	document.getElementById("hiddenmatrix").value = document.getElementById("thematrix").innerHTML;
}

function removeEditButtons() {
	var thematrix = document.getElementById("thematrix");
	buttons = thematrix.getElementsByTagName("input");
	var looptimes = buttons.length;
	for (var i=0; i < looptimes; i++) {
		// Get parent
		pnode = buttons[0].parentNode;

		// Set farven tilbage til neutral grå
		pnode.style.backgroundColor="#DDDDDD";
		
		// Der er også 2 x <br /> som skal fjernes i hver td
		br1 = buttons[0].previousSibling;
		br2 = br1.previousSibling;
		pnode.removeChild(br1);
		pnode.removeChild(br2);

		pnode.removeChild(buttons[0]);
	}
	document.getElementById("hiddenmatrix").value = document.getElementById("thematrix").innerHTML;
}

function errorMsg(MSG){
 alert(MSG);
 return true;
}

function verifyBookStamdata(){
// verificerer, at stamdata er ok
 F = document.getElementById("addBookForm");
 F.booktitle.value = F.booktitle.value.replace(/'/g, "");
 F.booktitle.value = F.booktitle.value.replace(/"/g, "");
 F.booktitle.value = trim(F.booktitle.value);
 if (F.booktitle.value=="") {
  return (errorMsg("Udfyld venligst Bogens titel."));
 }
 if (F.pubyear.value!=""&&(isNaN(F.pubyear.value) || F.pubyear.value.length != 4)) {
  return (errorMsg("Udfyld venligst Udgivelsesår som et tal på 4 cifre, fx '2006'."));
 }
  F.coverimage_url.disabled = false;
  F.submit();
}

function gotourl(url, conf, usermessage){ 
// sender brugeren videre til en URL, evt. først efter confirm
 if (conf == "confirm"){
  if(confirm(usermessage)) {
   location = url;
  } else {
   return false;
  }
 } else {
  location = url;
 }
}

function edit(obj){ 
	obj.innerHTML = "\
	<textarea style='' id='edit_" + obj.id + "'>" + obj.firstChild.nodeValue + "</textarea>\
	<input type='button' class='lilleknap' value='Ok' onclick='editComplete(\"" + obj.id + "\")'>";
	obj.style.backgroundColor="#fff888";
	document.getElementById("gemknap").disabled = "disabled";
}

function editComplete(a){
	obj = document.getElementById(a);
	if (document.getElementById("edit_" + a).value=="") document.getElementById("edit_" + a).value = "&nbsp;";
	obj.innerHTML = document.getElementById("edit_" + a).value + "<br/><br/><input type='button' class='lilleknap' value='Rediger' onclick='edit(this.parentNode)'>";
	obj.style.backgroundColor="#ffffff";
	document.getElementById("hiddenmatrix").value = document.getElementById("thematrix").innerHTML;

	// Check om alle textareas er lukket!
	var thematrix = document.getElementById("thematrix");
	txts = thematrix.getElementsByTagName("textarea");
	if (txts.length == 0) {
		document.getElementById("gemknap").disabled = "";
	}
 }

function editMatrixComplete(){
	F = document.getElementById("matrixtitle");
	F.value = F.value.replace(/'/g, "");
	F.value = F.value.replace(/"/g, "");

	F.value = trim(F.value);
	
	if (F.value=="") {
		return (errorMsg("Udfyld venligst matricens titel."));
	}

	removeEditButtons();
	document.getElementById("matrixform").submit();
}

function colorizeSectionDivs(){
 DIVS = document.getElementsByClassName("sectionContainer");
 for(i=0; i<DIVS.length; i++){
  if (i%2==0) DIVS[i].style.backgroundColor = "#eeeeee";
 }
}