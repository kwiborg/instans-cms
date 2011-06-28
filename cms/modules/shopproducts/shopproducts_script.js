if (window.addEventListener) {
	window.addEventListener("load", do_onload, false);
} else if (window.attachEvent) {
	window.attachEvent("onload", do_onload);
} else if (document.getElementById) {
	window.onload=do_onload;
}

function groupSelected(obj) {
	window.location = 'index.php?content_identifier=shopproducts&groupid='+obj.value;
}

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

function saveProduct(){
	var valid = true;
	var errorMsg = 'Udfyld venligst følgende felter:\n';

	/* Check at least 1 productgroup */
	var elements = document.getElementsByClassName("pg_checkbox", $("productgroups"));
	var a = 0;
	for (i=0; i<elements.length; i++) {
		if (elements[i].checked == true) {
			a++;
		}
	}
	if (a < 1) {
		errorMsg += '(*) Varegruppe - mindst 1 skal vælges\n';
		valid = false;
	}
	
	$('form_product').product_name.value = trim($('form_product').product_name.value);
	if ($('form_product').product_name.value == "") {
		errorMsg += '(*) Varens navn\n';
		valid = false;
	}

	$('form_product').product_number.value = trim($('form_product').product_number.value);
	if ($('form_product').product_number.value == "") {
		errorMsg += '(*) Varenummer\n';
		valid = false;
	}
	$('form_product').product_price.value = trim($('form_product').product_price.value);
	if ($('form_product').product_price.value == "") {
		errorMsg += '(*) Varens pris\n';
		valid = false;
	}
	$('form_product').productQualitySelector.value = trim($('form_product').productQualitySelector.value);

	if (valid) {
		Form.enable('form_product');
		$('form_product').submit();
	} else {
		alert(errorMsg);
	}
}

function productDelete(id, groupid) {
	if(confirm("Er du sikker på at du vil slette varen? Denne handling kan ikke fortrydes.")) {
		window.location='index.php?content_identifier=shopproducts&dothis=deleteproduct&id='+id+'&groupid='+groupid;
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

function validateNumber(obj, str_tekst) {
	obj.value = trim(obj.value);
	obj.value = obj.value.replace(/,/g,".");
	if (!isNumeric(obj.value)) {
		alert(str_tekst+" skal være et tal");
		obj.value = "";
	}
}

function validateColli(fieldtype, row, dbid, val) {
	obj = $(fieldtype+'_'+row+'_'+dbid);
	val = val.replace(/,/g,".");
	obj.value = val;

	// Only numeric input allowed
	if (val != "") {
		val = Number(val);
	}
	if (isNaN(val)) {
		obj.value = "";
		val = "";
		obj.focus();
		alert("Du må kun indtaste tal i feltet!");
	}


	// Check Quantity interval
	if (fieldtype == "colliQuantity") {
		if (val < 1 && val != "") {
			obj.value = "";
			val = "";
			alert("Antal skal være minimum 1!");
		}
	}
	// Round Quantity
	if (fieldtype == "colliQuantity" && val != "") {
		val = Math.round(val);
		obj.value = val;
	}
	// Check Percentage interval
	if (fieldtype == "colliDiscPerc") {
		if (val > 100) {
			obj.value = "";
			val = "";
			alert("Rabat kan makaimalt være 100%");
		}
	}

	// Only allow input in colliDiscAbs OR colliDiscPerc
	if (fieldtype == "colliDiscPerc" && val > 0) {
		$('colliDiscAbs'+'_'+row+'_'+dbid).disabled = true;
	} else {
		$('colliDiscAbs'+'_'+row+'_'+dbid).disabled = false;
	}			
	if (fieldtype == "colliDiscAbs" && val > 0) {
		$('colliDiscPerc'+'_'+row+'_'+dbid).disabled = true;
	} else {
		$('colliDiscPerc'+'_'+row+'_'+dbid).disabled = false;
	}			
	
	// no zeros
	if (val == 0) {
		val = "";
		obj.value = val;
	}


}

function isNumeric(sText) {
   var ValidChars = "0123456789.";
   var IsNumber=true;
   var Char;
   for (i = 0; i < sText.length && IsNumber == true; i++) 
      { 
      Char = sText.charAt(i); 
      if (ValidChars.indexOf(Char) == -1) 
         {
         IsNumber = false;
         }
      }
   return IsNumber;
}


function do_onload() {
	if ($("ajaxloader_rewrite")){
		Element.hide('ajaxloader_rewrite');
	}
}

function relatedProducts(product_id, group_id){
	location = "index.php?content_identifier=shopproducts&dothis=relatedproducts&productid="+product_id;
}

function showPossibleRelations(product_id, group_id, from_group_id){
	if (product_id){
		location = "index.php?content_identifier=shopproducts&dothis=relatedproducts&productid="+product_id+"&fromgroupid="+from_group_id;
	} else if (group_id){
		location = "index.php?content_identifier=shopproducts&dothis=relatedproducts&groupid="+group_id+"&fromgroupid="+from_group_id;	
	}
}

function removeRelations(){
	temp = document.getElementsByTagName("input");
	checkcount = 0;
	for(i=0; i<temp.length; i++){
		A = temp[i].name.split("_");
		if (A[0] == "removerelation" && temp[i].checked){
			checkcount++;
		}
	}
	if (checkcount == 0){
		alert("Afkryds venligst den/de relaterede varer, der skal fjernes.");
		return;
	} else {
		if (confirm("Er du sikker på, at du vil fjerne de valgte relaterede varer?")){
			document.forms[0].dothis.value = "removerelations";
			document.forms[0].submit();
		}
	}
}

function addRelations(){
	temp = document.getElementsByTagName("input");
	checkcount = 0;
	for(i=0; i<temp.length; i++){
		A = temp[i].name.split("_");
		if (A[0] == "addrelation" && temp[i].checked){
			checkcount++;
		}
	}
	if (checkcount == 0){
		alert("Afkryds venligst den/de relaterede varer, der skal tilføjes.");
		return;
	} else {
		if (confirm("Er du sikker på, at du vil tilføje de valgte relaterede varer?")){
			document.forms[0].dothis.value = "addrelations";
			document.forms[0].submit();
		}
	}
}