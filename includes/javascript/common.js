Event.observe(window, 'load', initPage, false);

function initPage() {
	/* size Illustrations */
	var illcontainers = document.getElementsByClassName("illustration");
	illcontainers.each( function(illcontainer) { 
			if (illcontainer.firstChild) {
				var ill = illcontainer.firstChild;
				var target_width = ill.width;
				illcontainer.style.width = target_width+"px";
			}	
		}
	)	
	/* If page has gallery switch to first image */
/*
	if ($('theGallery')) {
		galleryShifter(1);
	}
*/
	//setTimeout("resize()", 100);
}

function resize() {
  /*
  X = document.getElementsByTagName("div");
  S="";
  for(i=0; i<X.length; i++){
   if (X[i].id != "") S = S + X[i].id + ".offsetheight=" + X[i].offsetHeight + "\n";
  }
  alert(S);
  */
  var H, H_BOX;
  H = document.getElementById("content_container").offsetHeight*1;
  if (document.getElementById("boxes_container")) {
   document.getElementById("boxes_container").style.height = H;
   H_BOX = document.getElementById("boxes_container").offsetHeight*1;
  }
  if (H_BOX>H) H=H_BOX + 20;
  if (H<590) H=590;
  document.getElementById("main_container").style.height = H + 40 + "px";
  document.getElementById("leftsidebar_container").style.height = H  + "px";
  if (document.getElementById("boxes_container")) document.getElementById("boxes_container").style.height = H - 50 +  "px";
  if (document.getElementById("dotted_1")) document.getElementById("dotted_1").style.height = H - 50 +  "px";
  if (document.getElementById("dotted_2")) document.getElementById("dotted_2").style.height = H - 50 +  "px";
}

function emptyCart(msg){
	if (confirm(msg)){
		location = "index.php?mode=cart&action=emptycart";
	}
}

function doLogout()
{
 if (confirm("Vil du logge af?")){
  document.controlForm.dothis.value = "logout";
  document.controlForm.submit();
 }
}

function opdaterBrugerinfo()
{
 if (document.userProfileForm.firstname.value == "") {
  alert("Udfyld venligst Fornavn");
  return;
 }
 if (document.userProfileForm.lastname.value == "") {
  alert("Udfyld venligst Efternavn,");
  return;
 }
 if (document.userProfileForm.address.value == "") {
  alert("Udfyld venligst Adresse.");
  return;
 }
 if (document.userProfileForm.zipcode.value == "") {
  alert("Udfyld venligst Postnummer.");
  return;
 }
 if (document.userProfileForm.city.value == "") {
  alert("Udfyld venligst By.");
  return;
 }
 if (document.userProfileForm.oldpass.value == "") {
  if (document.userProfileForm.newpass1.value != "" || document.userProfileForm.newpass2.value != "") {
   alert("Du skal indtaste dit gamle password for at få lov til at ændre password.");
   return;
  }
 }
 if (document.userProfileForm.oldpass.value != "") {
  if (document.userProfileForm.newpass1.value != document.userProfileForm.newpass2.value) {
   alert("Du har ikke indtastet det samme, nye password 2 gange. Prøv venligst igen.");
   return;
  }
  if (document.userProfileForm.newpass1.value.length < 6) {
   alert("Dit nye password skal mindst være 6 tegn langt.");
   return;
  }
  if (!isAlphaNumeric(document.userProfileForm.newpass1.value)) {
   alert("Dit nye password må kun indeholde bogstaver (a-z) og tal (0-9).");
   return;
  }
 }
 document.userProfileForm.dothis.value = "updateProfile";
 document.userProfileForm.submit();
}

function createGalleryFolder(){
 if (document.createFolderForm.foldername.value == "") {
  alert("Udfyld venligst Mappenavn.");
  return;
 }
 if (document.createFolderForm.folderdescription.value == "") {
  alert("Udfyld venligst Kort beskrivelse.");
  return;
 }
 document.createFolderForm.dothis.value = "gemgallerimappe";
 document.createFolderForm.submit();
}

function uploadGalleryImage(){
 if (document.createImageForm.userfile.value == "") {
  alert("Vælg venligst et billede ved at trykke på Gennemse.");
  return;
 }
 if (document.createImageForm.filedescription.value == "") {
  alert("Udfyld venligst Kort beskrivelse.");
  return;
 }
 document.createImageForm.dothis.value = "gembillede";
 document.createImageForm.submit();
}


function setResValue(formname, felt, x)
{
 theForm = eval("document." + formname);
 if (eval("theForm." + felt))
 {
  eval("theForm." + felt + "_res.value = " + x);
 }
}

function setRadioCheckedMark(formname, radiogruppenavn, x, offset)
{
 theForm = eval("document." + formname);
 eval("theForm."+radiogruppenavn+"_res.value="+x);
 if (eval("theForm."+radiogruppenavn+"[0]"))
 {
  L = eval("theForm."+radiogruppenavn+".length");
  for (i=0; i<L; i++)
  {
   R = eval("theForm."+radiogruppenavn+"[" + i + "]");
   R.checked = false;
   if (i==(x-offset)) R.checked = true;
  }
 }
}

function isAlphaNumeric(mystring)
{
 var re = /^[a-zA-Z0-9]*$/;
 return re.test(mystring);
}

function isMail(e)
{
 if (e.indexOf("@")<0 || e.indexOf(".")<0) return false
 return true;
}

function verifySendToFriend()
{
 theForm = document.sendtofriendform;
 if (theForm.dit_navn.value=='') {
  alert(rtf_filloutname);
  return;
 }
 if (theForm.din_email.value=='') {
  alert(rtf_filloutemail);
  return;
 }
 if (theForm.vens_navn.value=='') {
  alert(rtf_filloutfriendsname);
  return;
 }
 if (theForm.vens_email.value=='') {
  alert(rtf_filloutfriendsemail);
  return;
 }
 if (!isMail(theForm.vens_email.value)) {
  alert(rtf_validmail);
  return;
 }
 if (!isMail(theForm.din_email.value)) {
  alert(rtf_validmail);
  return;
 }
 theForm.dothis.value="sendtilven";
 theForm.submit();
}

function subscribe(email, newsletter, state)
{
 if (!isMail(email)) {
  alert("Indtast venligst en gyldig e-mail-adresse.");
  return;
 }
 if (state==1) {
  document.newsletterform.dothis.value="subscribe";
  document.newsletterform.letter_id.value = newsletter;
  document.newsletterform.submit();
 }
 if (state==0) {
  if (confirm("Er du sikker på, at du vil framelde dig vores nyhedsbrev?")) {
   document.newsletterform.dothis.value="unsubscribe";
   document.newsletterform.letter_id.value = newsletter;
   document.newsletterform.submit();
  }
 }
}

function search(words) {
	words = words.replace(/^\s*|\s*$/g,"");
	if (words=="") return;
	if (words.length<3) {
		alert(SearchMinchars);
		return;
	}
	document.searchform.searchwords_x.value = words;
	document.searchform.submit();
}

function showhidecv(uid) {
 if (document.getElementById("cvinfo_uid"+uid).style.display=="none") { 
  document.getElementById("cvinfo_uid"+uid).style.display="block";
  return;
 }
 if (document.getElementById("cvinfo_uid"+uid).style.display=="block") { 
  document.getElementById("cvinfo_uid"+uid).style.display="none";
  return;
 }
}

/*
function galleryShifter(nr){
	eval("document.galleryImage.src=pic_" + nr + ".src");
	eval("document.getElementById(\"galleryImage\").width = pic_" + nr + "_width");
	eval("document.getElementById(\"galleryDescription\").innerHTML = pic_" + nr + "_desc");
	resize();	
}
*/

function show_gallery_image(mediumURL, largeURL, alt, x, y, i){
    $("cur_img").value = i; 
	$("mediumsize_container").style.height = y + "px";
	$("mediumsize_container").style.height = y + "px";
	$("alttext").style.display = "none";
	if ($("printerfriendly")) {
		$("printerfriendly").style.display = "none";
	}
	new Effect.Fade(
		'current_image', {
			duration: 0.2, 
      		from: 1, 
			to: 0, 
			afterFinish: function(){
				gallery_finishcallback(mediumURL, largeURL, alt);
			}
		}
	);
}

function gallery_next(){
    current = 1*$("cur_img").value;
    if (current == 0){
        current = 1;
    }
    if (current == images_arr.length-1){
        current -= 1;
    }
    next = current + 1;
    show_gallery_image(images_arr[next][0], images_arr[next][1], images_arr[next][2], images_arr[next][3], images_arr[next][4], next)
}

function gallery_prev(){
    current = 1*$("cur_img").value;
    if (current == 0){
        current = 1;
    }
    if (current == 1){
        current += 1;
    }
    prev = current - 1;
    show_gallery_image(images_arr[prev][0], images_arr[prev][1], images_arr[prev][2], images_arr[prev][3], images_arr[prev][4], prev)
}

function gallery_finishcallback(mediumURL, largeURL, alt){
	$("current_image").src = mediumURL;
	if (largeURL != ""){
		$("current_image").style.cursor = "hand";
		$("current_image").alt = "Billedet findes i en større udgave - klik her for at se den store udgave.";
		$("current_image").onclick = function(){
			window.open(largeURL, "", "");
		}
		alt += "<br/><a href='"+largeURL+"' target='_blank'>Billedet findes i en større udgave - klik her for at se den store udgave.";
	} else {
		$("current_image").style.cursor = "default";
		$("current_image").alt = "";
		$("current_image").onclick = function(){
		}
	}
	new Effect.Appear(
		'current_image', {
			duration: 0.2, 
      		from: 0, 
			to: 1,
			afterFinish: function(){
				$("alttext").innerHTML = alt;
				$("alttext").style.display = "block";
				if ($("printerfriendly")) {
					$("printerfriendly").style.display = "block";
				}
				resize();
				return false; // To avoid top-of-page (#)
			}
		}
	);}


function spawnDebate(groupID){
 window.open("../debate/?gid="+groupID, "debatewindow", "width=740,height=500,scrollbars=yes");
}

///////

function verifyFilled(feltid){
 V = eval("document.generatedForm.formfield_"+feltid+".value");
 if (V=="") return false;
 return true;
}

function verifyNumber(feltid){
 V = eval("document.generatedForm.formfield_"+feltid+".value");
 if (isNaN(V)) return false;
 return true;
}

function verifyEmail(feltid){
 V = eval("document.generatedForm.formfield_"+feltid+".value");
 if (V!="" && (V.indexOf("@")<0 || V.indexOf(".")<0)) return false;
 return true;
}

function verifyRadioFilled(feltid){
 V = eval("document.generatedForm.formfield_"+feltid+".length");
 for(i=0; i<V; i++){
  if (eval("document.generatedForm.formfield_"+feltid+"["+i+"].checked")) return true;
 }
 return false;
}

function verifyCheckMinFilled(feltid, antal){
 A = document.getElementsByTagName("input");
 count=0;
 for(i=0; i<A.length; i++){
  if (A[i].type=="checkbox" && A[i].name.indexOf("formfield_"+feltid+"_checkboks_")>-1) {
   if (A[i].checked==true) count += 1;
  } 
 }
 if (antal > count) return false;
 return true;
}

function verifyCheckMaxFilled(feltid, antal){
 A = document.getElementsByTagName("input");
 count=0;
 for(i=0; i<A.length; i++){
  if (A[i].type=="checkbox" && A[i].name.indexOf("formfield_"+feltid+"_checkboks_")>-1) {
   if (A[i].checked) count += 1;
  } 
 }
 if (count > antal) return false;
 return true;
}

function entsub(myform) {
 if (window.event && window.event.keyCode == 13) search(myform.searchwords.value);
 return true;
}

function showHideHelpText(id){
 x = document.getElementById("fieldhelp_"+id);
 if(x.style.display=="none"){x.style.display="block"; resize();return}
 if(x.style.display=="block"){x.style.display="none"; resize();return}
}

function shiftCalendars(){
 T=0;
 A = document.getElementsByTagName("input");
 for(i=0; i<A.length; i++){
  if (A[i].id.indexOf("calendarBox_") > -1) {
   if (A[i].checked) {
    T++;
   }
  }
 }
 if (T==0) return false;
 return true;
}

// BOGVÆRKTØJ

function goToBookSection(URL){ 
 location = URL;
}

function modifyHrefsForPreview(grant){
 AllLinks = document.getElementsByTagName("a");
 for(i=0; i<AllLinks.length; i++){
  AllLinks[i].href += "&grant="+grant+"&ignoreunfinished=1";
 }
}
