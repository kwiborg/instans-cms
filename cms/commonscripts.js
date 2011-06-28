addLoadEvent(size);

theForm = document.forms[0];
var edState = 0;

function udvidEditor() {
 theForm = document.forms[0];
 if (theForm.editorSize.value == "small") {
  document.getElementById('Indhold___Frame').height = '800';
  theForm.editorSize.value = "big";
  theForm.udvidknap.value = "Mindre tekstfelt";
  size(); 
  return;
 }
 if (theForm.editorSize.value == "big") {
  document.getElementById('Indhold___Frame').height = '550';
  theForm.editorSize.value = "small";
  theForm.udvidknap.value = "Større tekstfelt";
  size();
  return;
 }
}

function udvidHTMLEditor(){
 if (edState==0){
  document.getElementById('allFunctionsWrapperLeft').style.width='100%';
  document.getElementById('allFunctionsWrapperRight').style.display='none';
  document.getElementById('udvidLink').innerHTML="FLERE MULIGHEDER";
  edState=1;
  size();
  return;
 }
 if (edState==1){
  document.getElementById('allFunctionsWrapperLeft').style.width='400px';
  document.getElementById('allFunctionsWrapperRight').style.display='block';
  document.getElementById('udvidLink').innerHTML="FÆRRE MULIGHEDER";
  edState=0;
  size();
  return;
 }
}

function indstilFilter(val1, val2, val3)
{
 theForm = document.forms[0];
 if (theForm.filter_author) theForm.filter_author.value = val1;
 if (theForm.filter_time) theForm.filter_time.value = val2;
 if (theForm.filter_calendar) theForm.filter_calendar.value = val3;
}

function verifyLogin()
{
 theForm = document.forms[0];
 if (theForm.username.value == "" || theForm.pass.value == "")
 {
  alert("Indtast venligst både brugernavn og kodeord.");
  return;
 }
 theForm.dothis.value = "logind";
 theForm.submit();
}

function verifySiteSel()
{
 theForm = document.forms[0];
 if (theForm.site_id.value == "")
 {
  alert("Vælg venligst et site.");
  return;
 }
 theForm.dothis.value = "videre";
 theForm.submit();
}

function setResValue(felt, x)
{
 theForm = document.forms[0];
 if (eval("theForm." + felt))
 {
  eval("theForm." + felt + "_res.value = " + x);
 }
}

function setRadioCheckedMark(radiogruppenavn, x, offset)
{
 theForm = document.forms[0];
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

function disableRadioGroup(navn, state)
{
 R = eval("document.forms[0]." + navn);
 if (R[0].type == "radio")
 {
  for(i=0; i<R.length; i++)
  {
   R[i].disabled = state;
   if (state==true) R[i].checked = false;
  }
 }
 if (state==true) 
 {
  if (eval("document.forms[0]." + navn + "_res")) eval("document.forms[0]." + navn + "_res.value=''");
 }
}

function disableFormFields(searchFor)
{
 ALL = document.getElementsByTagName("input");
 for (i=0; i<ALL.length; i++) {
  if (ALL[i].id.indexOf(searchFor)>-1) {
   ALL[i].disabled=true;
   ALL[i].checked=false;
  }
 }
}

function IEColorShift(ID) // For at kompensere for manglende TR:HOVER i CSS...  
{
 oldBGC = document.getElementById(ID).className;
 document.getElementById(ID).className = "oversigtHover";
}

function IEColorUnShift(ID, X)
{
 document.getElementById(ID).className = "oversigt1";
}

function bookmakerIEColorShift(ID) // For at kompensere for manglende TR:HOVER i CSS...  
{
	$(ID).style.backgroundColor = "#ffffff";
}

function bookmakerIEColorUnShift(ID, X)
{
	$(ID).style.backgroundColor = "#eeeeee";
}

function opretNy()
{
 document.forms[0].dothis.value = "opret";
 document.forms[0].submit();
}

function slet(id, content_identifier)
{
 if (confirm("Er du sikker på, at du vil slette?"))
 {
  location = "index.php?content_identifier=" + content_identifier + "&dothis=slet&id=" + id;
 }
}

function filterOnOff(state)
{
 theForm = document.forms[0];
 e = document.getElementById('filter').style;
 if ((e.display == "" || e.display == "none") && state != 2)
 {
  e.display = "block";
  theForm.filter_display.value = "1";
  size();
  return;
 }
 if (e.display == "block" || state == 2)
 {
  e.display = "none";
  theForm.filter_display.value = "2";
  size();
  return;
 }
}

function validDate(dato) // mm-dd-yyyy
{
 if (dato.length != 10) return false;
 D = dato.split("-");
 if (D[0].length != 2 && D[1].length != 2 && D[2].length != 4) return false;
 return true;
}


function checkCheckbox(navn, state)
{
 theForm = document.forms[0];
 if (state == 1) {
  if (eval("theForm." + navn)) eval ("theForm." + navn + ".checked = true");
 }
 if (state == 0) {
  if (eval("theForm." + navn)) eval ("theForm." + navn + ".checked = false");
 } 
}

function ValidDateX(y, m, d) { // m = 0..11 ; y m d integers, y!=0
  with (new Date(y, m, d))
    return (getMonth()==m && getDate()==d) /* was y, m */ 
}

function ReadISO8601date(Q) { var T // adaptable for other layouts
  if ((T = /^(\d+)([-\/])(\d\d)(\2)(\d\d)$/.exec(Q)) == null)
    { return -2 } // bad format
  for (var j=1; j<=5; j+=2) T[j] = +T[j] // some use needs numbers
  if (!ValidDate(T[1], T[3]-1, T[5])) { return -1 } // bad value
  return [ T[1], T[3], T[5] ] }

function DiffDays(S1, S2) { // ISO date strings
  var X = ReadISO8601date(S1) ; if (X<0) return 'Date 1 bad'
  var Y = ReadISO8601date(S2) ; if (Y<0) return 'Date 2 bad'
  var Dx = Date.UTC(X[0], X[1]-1, X[2])
  var Dy = Date.UTC(Y[0], Y[1]-1, Y[2])
  return (Dx-Dy)/864e5 }
  
function checkUnderDogs(TOP, mode)
{
 ja=0;
 ALL = document.getElementsByTagName("input");
 for (i=0; i<ALL.length; i++) {
  if (ALL[i].id.indexOf("B_") > -1) {
   ID = ALL[i].id.split("_")[1];
   if (baglaens(ID, TOP)==1) {
    ALL[i].checked  = mode;
    ALL[i].disabled = mode;
   }
   ja=0;
  }
 }
}
	
function baglaens(child, ancestor) 
{
 p = boxes[child];
 if (p == ancestor) {
  ja = 1;
 }
 else if (p>0) baglaens(p, ancestor);
 return ja;
}

function attachment(id, tabel, menuid) {
 location = "index.php?content_identifier=attachments&menuid="+menuid+"&id="+id+"&tabel="+tabel;
}

function disableAllGroups(state)
{
 A = document.getElementsByTagName("input");
 for (i=0; i<A.length; i++) {
  if (A[i].name.indexOf("B_") > -1 && state == 1) {A[i].disabled = true; A[i].checked = false;}
  if (A[i].name.indexOf("B_") > -1 && state == 2) {A[i].disabled = false; A[i].checked = false;}
 }
}

function checkAndDisableIfParentChecked() // re-etablerer den "state" af disablede bokse, som opstod, da menupunktet blev gemt.
{
 A = document.getElementsByTagName("input");
 for(i=0; i<A.length; i++) {
  if (A[i].id.indexOf("B_") > -1) {
   parentID  = boxes[A[i].id.split("_")[1]];
   parentOBJ = document.getElementById("B_" + parentID);
   if (A[i].checked && parentOBJ && parentOBJ.checked) A[i].disabled=true;
  }
 }
}

function related(id, tabel, menuid) {
 location = "index.php?content_identifier=pages&menuid="+menuid+"&&pageid="+id+"&tabel="+tabel+"&dothis=related";
}

function addRelated(this_tabel, this_page_id, rel_tabel, rel_page_id, menuid) {
 if (rel_page_id>0) {
  location = "index.php?content_identifier=pages&dothis=addrel&thistabel="+this_tabel+"&thispageid="+this_page_id+"&relpageid="+rel_page_id+"&reltabel="+rel_tabel+"&menuid="+menuid;
 }
}

function addRelatedForBoxes(pageid, boxid) {
 if (pageid>0) {
  document.forms[0].dothis.value="addlink";
  document.forms[0].page_to_add.value=pageid;
  document.forms[0].submit();
 }
}

function removeRel(relid, pageid, tabel, menuid) {
 if (relid>0) {
  location = "index.php?content_identifier=pages&dothis=removerel&relid="+relid+"&pageid="+pageid+"&tabel="+tabel+"&menuid="+menuid;
 }
}	

function removeRelForBoxes(id) {
 if (id>0) {
  document.forms[0].dothis.value="removelink";
  document.forms[0].rel_id.value=id;
  document.forms[0].submit();
 }
}

function size() {

 if (document.getElementById('allFunctionsWrapperLeft')) {
	h1 = document.getElementById('allFunctionsWrapperLeft').offsetHeight;
	h2 = document.getElementById('allFunctionsWrapperRight').offsetHeight;
	if (h1 > h2) {
		maxH = h1;
	} else {
		maxH = h2;
	}
  document.getElementById('wrapper2').style.height = maxH + "px";
  document.getElementById('wrapitall').style.height = document.getElementById('content').offsetHeight + 50 + "px";
 } else {
  document.getElementById('wrapitall').style.height = "0px";
  bodyHeight = document.getElementsByTagName("body")[0].offsetHeight; 
  contentHeight = document.getElementById('content').offsetHeight;
  bodyHeight>contentHeight ? document.getElementById('wrapitall').style.height = bodyHeight + 100 + "px" : document.getElementById('wrapitall').style.height = contentHeight + 100 + "px";
 }
}

function pageFunction(pageid, funktion, menuid, threadid, level) {
	if (funktion == 4) {
		related(pageid, "PAGES", menuid);
	}
	if (funktion == 7) {
		var inputs = document.getElementsByTagName("input");
		for(input=0; input<inputs.length; input++){
				var thisId = "flytknap_"+pageid;
				if (inputs[input].id == thisId) {
					inputs[input].value = "Afbryd";
					inputs[input].onclick = function() {
						window.location.href=window.location.href;
					}
				} else {
					inputs[input].disabled = true;
				}
		}
  document.forms[0].tempPageToMove.value = pageid;
  A = document.getElementsByTagName("div");
  for(i=0; i<A.length; i++){
   if (A[i].id.indexOf("insertAsSubPointTo_")>-1 ) {
    L1 = A[i].id.split("_");
    L2 = L1[3].split("level");
    L3 = L2[1];
    MoveToLevel = L3;
    if (A[i].id.indexOf("_newparent"+pageid)<0 && (A[i].id.indexOf("_newthread"+threadid)<0 || (A[i].id.indexOf("_newthread"+threadid)>-1 && level >= MoveToLevel))) {
     A[i].style.display = "block";
    }
   } 
  }
 }
 if (funktion == 8) {
  location = 'index.php?content_identifier=pages&dothis=boxes&pageid='+pageid+"&menuid="+menuid+"&tabel=PAGES";
 }
}

function movePageToNewParent(newparentid, menuid){
 location = "index.php?content_identifier=pages&dothis=newparent&pagetomoveid=" + document.forms[0].tempPageToMove.value + "&newparentid="+newparentid+"&menuid="+menuid;
}

function savePreview() {
 document.forms[0].dothis.value = "preview";
 document.forms[0].is_sitelang_frontpage.disabled = false;
 document.forms[0].submit();
}

function showPreview(id, langvar, current_site, tabel, grant, otherVars) {
 if (tabel == "PAGES") {
  url = "../index.php?ignoreunfinished=1&pageid=" + id + "&grant=" + grant;
  if (otherVars != "") {
   url = url + otherVars;
  }
 }
 preview = window.open(url, "preview", "toolbar=no, scrollbars=yes");
 preview.focus();
}

function addLoadEvent(func) {
  var oldonload = window.onload;
  if (typeof window.onload != 'function') {
    window.onload = func;
  } else {
    window.onload = function() {
      oldonload();
      func();
    }
  }
}

function insertAfter(newElement,targetElement) {
  var parent = targetElement.parentNode;
  if (parent.lastChild == targetElement) {
    parent.appendChild(newElement);
  } else {
    parent.insertBefore(newElement,targetElement.nextSibling);
  }
}

// Removes leading whitespaces
function LTrim( value ) {
	var re = /\s*((\S+\s*)*)/;
	return value.replace(re, "$1");
}

// Removes ending whitespaces
function RTrim( value ) {
	var re = /((\s*\S+)*)\s*/;
	return value.replace(re, "$1");	
}

// Removes leading and ending whitespaces
function trim( value ) {	
	return LTrim(RTrim(value));
}

function hideShowFolder(folderID, state) {
	var tname = "foldeknap_state_" + folderID;
	var clicked_state = $(tname);
	if (!clicked_state) {
 		return;
	}

	FF = navigator.appName.indexOf("Netscape");
	TR = document.getElementsByTagName("TR");
	for (i=0; i<TR.length; i++) {
		nowState = clicked_state.value;
		
		if (nowState == 1) newState = 0;
		if (nowState == 0) newState = 1;
		if (state != -1) newState = state;
		if (TR[i].id.indexOf("parent" + folderID + "_") > -1 && newState == 0) TR[i].style.display = "none";
		if (TR[i].id.indexOf("parent" + folderID + "_") > -1 && newState == 1 && FF > -1) TR[i].style.display = "table-row";
		if (TR[i].id.indexOf("parent" + folderID + "_") > -1 && newState == 1 && FF == -1) TR[i].style.display = "block";
	}
	if (K = document.getElementById("foldeknap_" + folderID)) {
		if (newState==0) {
			K.value = "+";
			clicked_state.value = newState;
		}
		if (newState==1) {
  			K.value = "-";
			clicked_state.value = newState;
		}
	}
}

/***********************************************
* Tab Content script- © Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
***********************************************/
////////Stop editting////////////////

function cascadedstyle(el, cssproperty, csspropertyNS){
if (el.currentStyle)
return el.currentStyle[cssproperty]
else if (window.getComputedStyle){
var elstyle=window.getComputedStyle(el, "")
return elstyle.getPropertyValue(csspropertyNS)
}
}

var previoustab=""

function expandcontent(cid, aobject){
if (document.getElementById){
highlighttab(aobject)
detectSourceindex(aobject)
if (previoustab!="")
document.getElementById(previoustab).style.display="none"
document.getElementById(cid).style.display="block"
normaltabs()
var agt = navigator.userAgent.toLowerCase();
if(agt.indexOf('firefox') >=0){
	/*
		This is needed to activate fckeditors on multiple tabs. Function will fail in Safari, thus the need for browser detection. (MAP)
	*/
	switchEditors(document.getElementById(cid),"on")
} 
/* aobject.setAttribute("class", "current") */
aobject.className = "current";
aobject.style.backgroundColor = "#eeeeee";
aobject.style.color = "#000000";
previoustab=cid
if (aobject.blur)
aobject.blur()
return false
}
else
return true
}

function highlighttab(aobject){
	if (typeof tabobjlinks=="undefined") {
		collecttablinks();
		for (i=0; i<tabobjlinks.length; i++) {
			tabobjlinks[i].style.backgroundColor=initTabcolor;
			if (themecolor=aobject.getAttribute("theme")) {
				aobject.getAttribute("theme");
			} else {
				initTabpostcolor;
			}
			aobject.style.backgroundColor=document.getElementById("tabcontentcontainer").style.backgroundColor=themecolor;
		}
	}
}

function collecttablinks() {
	var tabobj=document.getElementById("tablist");
	tabobjlinks=tabobj.getElementsByTagName("A");
}

function normaltabs(){
	var tabobj=document.getElementById("tablist");
	tabobjlinks=tabobj.getElementsByTagName("A");
	for (i=0; i<tabobjlinks.length; i++) {
		tabobjlinks[i].className = "";
		tabobjlinks[i].style.backgroundColor = "#aaaaaa";
		tabobjlinks[i].style.color = "#ffffff";
	}
}

function detectSourceindex(aobject){
for (i=0; i<tabobjlinks.length; i++){
if (aobject==tabobjlinks[i]){
tabsourceindex=i //source index of tab bar relative to other tabs
break
}
}
}

function styleTableRows(OBJID, col1, col2){
	var E = $(OBJID).getElementsByTagName('tr');
	for (i=0; i<E.length; i++){
		if (i % 2 == 0){
			E[i].style.backgroundColor = col1;
		} else {
			E[i].style.backgroundColor = col2;
		}
	}	
}

/// URL REWRITE
function suggest_rewrite_keyword(strHeading, pageid, tablename, siteid){
	Element.show('ajaxloader_rewrite');
	var url = '/cms/modules/pages/pages.ajaxresponders.php';
	var pars = 'do=ajax_returnRewriteKeyword&pageid='+pageid+"&heading="+strHeading+"&tablename="+tablename+"&siteid="+siteid;
	var myAjax = new Ajax.Request(
				url,
				{	
					method: 'post',
					parameters: pars,
					onComplete: updateSuggestedKeyword
				}
	);
}

function updateSuggestedKeyword(originalRequest){
	$("rewrite_keyword").value = originalRequest.responseText;
	$("rewrite_keyword").disabled = true;
	Element.hide('ajaxloader_rewrite');
}

function keyword_onblur(heading, keyword, pageid, tablename, siteid){
	if (keyword == ''){
		suggest_rewrite_keyword(heading, pageid, tablename, siteid)
	} else {
		suggest_rewrite_keyword(keyword, pageid, tablename, siteid)
	}
	$("rewrite_keyword").disabled = true;
}

function edit_keyword(){
	if (confirm(
		"BEMÆRK: Hvis du retter i sidens adresse, kan det have negativ betydning for søgemaskinerne (hvis de har indekseret sidens gamle adresse).\n"+
		"Det kan også medføre, at links til denne side fra andre sites samt bookmarks ikke længere vil fungere.\n\n"+
		"Tryk OK for at rette adressen eller Annuller for at lade være.")){
			$("rewrite_keyword").disabled = false;
			return true;
	}
	return false;
}

/*
	Functions used by datapermission_set
*/
function datapermission_showgrants(str_permissionname, str_datatablename, int_dataid) {
	Element.toggle($('datapermission_usergroups_'+str_permissionname+'_'+int_dataid));
	Element.toggle($('datapermission_users_'+str_permissionname+'_'+int_dataid));

	if ($('datapermission__showgrantsbutton_'+str_permissionname+'_'+int_dataid).value == "Vis rettigheder") {
		$('datapermission__showgrantsbutton_'+str_permissionname+'_'+int_dataid).value = "Skjul rettigheder";
	} else {
		$('datapermission__showgrantsbutton_'+str_permissionname+'_'+int_dataid).value = "Vis rettigheder";
		return;
	}
	// Load exitsting grants
	datapermission_loadgrants(str_permissionname, str_datatablename, int_dataid, "users");
	datapermission_loadgrants(str_permissionname, str_datatablename, int_dataid, "usergroups");
}		

function datapermission_loadgrants(str_permissionname, str_datatablename, int_dataid, str_mode) {
    /*
        Function loads grants for this combination of str_permissionname, str_datatablename, and int_dataid.
        If called with str_mode "users", individual user rights are loaded.
        If called with str_mode "usergroups", rights for usergroups are loaded.
    */
	Element.show($('ajaxloader_datapermission_'+str_permissionname+'_'+int_dataid));
    var dothis = "ajax_datapermission_listgrants";
	var url = '/cms/common.ajaxresponders.php';
	var pars = 'do=' + dothis;
	pars += '&permissionname=' + str_permissionname;
	pars += '&datatablename=' + str_datatablename;
	pars += '&dataid=' + int_dataid;
	pars += '&request_mode=' + str_mode;

	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'post', 
			parameters: pars, 
			onComplete: datapermission_updategrants
		});
}

function datapermission_updategrants(originalRequest) {
	var response = originalRequest.responseText;
	arr_response = response.split("|||");
	if (arr_response[0] == "success") {
		$('datapermission_'+arr_response[1]+'_'+arr_response[3]+'_'+arr_response[4]).innerHTML = arr_response[2];
	} else {
		alert(arr_response[0]+": "+arr_response[2]+"["+arr_response[1]+"/"+arr_response[3]+"/"+arr_response[4]+"]");
	}
	Element.hide($('ajaxloader_datapermission_'+arr_response[3]+'_'+arr_response[4]));
}

function datapermission_revokegrant(str_permissionname, str_datatablename, int_dataid, str_mode, int_grantid) {
    /*
        Function revokes the given grant
    */
	Element.show($('ajaxloader_datapermission_'+str_permissionname+'_'+int_dataid));

	// Clear grant candidates to force ajax reload
	Element.hide($('datapermission_grantcandidates_'+str_permissionname+'_'+int_dataid));
	$('datapermission__showusergroupsbutton_'+str_permissionname+'_'+int_dataid).value = "Tildel rettigheder";
	$('datapermission_grantcandidates_'+str_permissionname+'_'+int_dataid).innerHTML = "";

    var dothis = "ajax_datapermission_revokegrant";
	var url = '/cms/common.ajaxresponders.php';
	var pars = 'do=' + dothis;
	pars += '&permissionname=' + str_permissionname;
	pars += '&datatablename=' + str_datatablename;
	pars += '&dataid=' + int_dataid;
	pars += '&request_mode=' + str_mode;
	pars += '&grantid=' + int_grantid;

	// Make revoke call synchronous - and update both display panels on finish!
	var myAjax = new Ajax.Request(
		url, 
		{
			asynchronous: false,
			method: 'post', 
			parameters: pars, 
			onComplete: datapermission_updategrants
		});

	// Make sure both users and usergroups grants display are updated.
	datapermission_loadgrants(str_permissionname, str_datatablename, int_dataid, "users");
	datapermission_loadgrants(str_permissionname, str_datatablename, int_dataid, "usergroups");

		
}

function datapermission_showusergroups(str_permissionname, str_datatablename, int_dataid) {
	/*
		Function loads a list of usergroups into the matching "grantcandidates"-div.
	*/
	Element.show($('ajaxloader_datapermission_'+str_permissionname+'_'+int_dataid));
	Element.toggle($('datapermission_grantcandidates_'+str_permissionname+'_'+int_dataid));
	if ($('datapermission__showusergroupsbutton_'+str_permissionname+'_'+int_dataid).value == "Tildel rettigheder") {
		$('datapermission__showusergroupsbutton_'+str_permissionname+'_'+int_dataid).value = "Skjul brugere/grupper";
	} else {
		$('datapermission__showusergroupsbutton_'+str_permissionname+'_'+int_dataid).value = "Tildel rettigheder";
		Element.hide($('ajaxloader_datapermission_'+str_permissionname+'_'+int_dataid));
		return;
	}

    var dothis = "ajax_datapermission_listusergroups";
	var url = '/cms/common.ajaxresponders.php';
	var pars = 'do=' + dothis;
	pars += '&permissionname=' + str_permissionname;
	pars += '&datatablename=' + str_datatablename;
	pars += '&dataid=' + int_dataid;

	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'post', 
			parameters: pars, 
			onComplete: datapermission_updateusergroups
		});

}

function datapermission_updateusergroups(originalRequest) {
	var response = originalRequest.responseText;
	arr_response = response.split("|||");
	if (arr_response[0] == "success") {
		$('datapermission_grantcandidates_'+arr_response[2]+'_'+arr_response[4]).innerHTML = arr_response[1];
	} else {
		alert(arr_response[0]+": "+arr_response[1]+"["+arr_response[2]+"/"+arr_response[4]+"]");
	}
	Element.hide($('ajaxloader_datapermission_'+arr_response[2]+'_'+arr_response[4]));
}

function datapermission_showusers(str_permissionname, str_datatablename, int_dataid, int_usergroupid) {
	/*
		On first call: Function loads a list of users into the matching "datapermission_users_int_usergroupid"-tablerow.
		On subsequent calls: Only handles toggling of button +/- and folding in/out of the tablerow
	*/
	if ($('datapermission_usergroup_foldingbutton_'+str_permissionname+'_'+int_dataid+'_'+int_usergroupid).value == "+") {
		// Show users
		if ($('datapermission_users_'+str_permissionname+'_'+int_dataid+'_'+int_usergroupid).innerHTML == "") {
			// Load users on first call
			$('datapermission_usergroup_foldingbutton_'+str_permissionname+'_'+int_dataid+'_'+int_usergroupid).value = "-";
			datapermission_loadusers(str_permissionname, str_datatablename, int_dataid, int_usergroupid);
		} else {
			// Just show the tr
			$('datapermission_usergroup_foldingbutton_'+str_permissionname+'_'+int_dataid+'_'+int_usergroupid).value = "-";
			Element.show($('datapermission_users_'+str_permissionname+'_'+int_dataid+'_'+int_usergroupid));
		}
	} else {
		// Hide users
			$('datapermission_usergroup_foldingbutton_'+str_permissionname+'_'+int_dataid+'_'+int_usergroupid).value = "+";
			Element.hide($('datapermission_users_'+str_permissionname+'_'+int_dataid+'_'+int_usergroupid));
	}
}

function datapermission_loadusers(str_permissionname, str_datatablename, int_dataid, int_usergroupid) {
			Element.show($('ajaxloader_datapermission_'+str_permissionname+'_'+int_dataid));
			var dothis = "ajax_datapermission_listusers";
			var url = '/cms/common.ajaxresponders.php';
			var pars = 'do=' + dothis;
			pars += '&permissionname=' + str_permissionname;
			pars += '&datatablename=' + str_datatablename;
			pars += '&dataid=' + int_dataid;
			pars += '&usergroupid=' + int_usergroupid;
		
			var myAjax = new Ajax.Request(
				url, 
				{
					method: 'post', 
					parameters: pars, 
					onComplete: datapermission_updateusers
				});
}

function datapermission_updateusers(originalRequest) {
	var response = originalRequest.responseText;
	arr_response = response.split("|||");
	if (arr_response[0] == "success") {
		$('datapermission_users_'+arr_response[2]+'_'+arr_response[4]+'_'+arr_response[5]).innerHTML = arr_response[1];
	} else {
		alert(arr_response[0]+": "+arr_response[1]+"["+arr_response[2]+"]");
	}
	Element.show($('datapermission_usersrow_'+arr_response[2]+'_'+arr_response[4]+'_'+arr_response[5]));
	Element.hide($('ajaxloader_datapermission_'+arr_response[2]+'_'+arr_response[4]));
}

function datapermission_grant(str_permissionname, str_datatablename, int_dataid, str_mode, int_grantrecieverid) {
	/*
		Function to grant a "user" or "usergroup" (str_mode) with int_grantrecieverid 
		the str_permissionname datapermission on str_datatablename/int_dataid 
	*/
	Element.show($('ajaxloader_datapermission_'+str_permissionname+'_'+int_dataid));
	$('datapermission_'+str_mode+'_grantbutton_'+str_permissionname+'_'+int_dataid+'_'+int_grantrecieverid).disabled = true;

    var dothis = "ajax_datapermission_grant";
	var url = '/cms/common.ajaxresponders.php';
	var pars = 'do=' + dothis;
	pars += '&permissionname=' + str_permissionname;
	pars += '&datatablename=' + str_datatablename;
	pars += '&dataid=' + int_dataid;
	pars += '&request_mode=' + str_mode;
	pars += '&grantrecieverid=' + int_grantrecieverid;

	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'post', 
			parameters: pars, 
			onComplete: datapermission_grantgiven
		});
}

function datapermission_grantgiven(originalRequest) {
	/*
		Response:
		return "$status|||$message|||$str_permissionname|||$str_datatablename|||$int_dataid|||$str_mode|||$int_grantrecieverid";
	*/
	var response = originalRequest.responseText;
	arr_response = response.split("|||");
	if (arr_response[0] == "success") {
		Element.hide($('ajaxloader_datapermission_'+arr_response[2]+'_'+arr_response[4]));
		// Re-load grants
		datapermission_loadgrants(arr_response[2], arr_response[3], arr_response[4], "users");
		datapermission_loadgrants(arr_response[2], arr_response[3], arr_response[4], "usergroups");
	} else {
		alert(arr_response[0]+": "+arr_response[1]+"["+arr_response[2]+"/"+arr_response[4]+"]");
		Element.hide($('ajaxloader_datapermission_'+arr_response[2]+'_'+arr_response[4]));
	}
}

function datapermission_loadgrants_plaintext(id) {
	/*
		Function used to retrieve permissions in the context of lists
		eg. who has the permission to edit this?
		Called with an id in the form of
		nopermissions__[STR_PERMISSIONNAME]__[STR_DATATABLENAME]__[INT_DATAID]
	*/
	var arr_id = id.split("__");
	var str_permissionname = arr_id[1];
	var str_datatablename = arr_id[2];
	var int_dataid = arr_id[3];

    var dothis = "ajax_datapermission_listgrants_readonly_plaintext";
	var url = '/cms/common.ajaxresponders.php';
	var pars = 'do=' + dothis;
	pars += '&permissionname=' + str_permissionname;
	pars += '&datatablename=' + str_datatablename;
	pars += '&dataid=' + int_dataid;

	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'post', 
			parameters: pars, 
			onComplete: datapermission_alertgrants
		});
}

function datapermission_alertgrants(originalRequest) {
	var response = originalRequest.responseText;
	if (response.indexOf("|||")>-1) {
		var arr_response = response.split("|||");
		alert(arr_response[1]);
	} else {
		alert(response);
	}
}

/* Functions used for custom fields */
function customfield_selectImage(customfield_id,attribute_id) {
	// Disable button attribute_$a_row[ID]_selectImageButton
	$('customfield_'+customfield_id+'_attribute_'+attribute_id+'_selectImageButton').disabled = true;

	// SetUp selectImageDiv
	$("customfield_"+customfield_id+"_attribute_"+attribute_id+"_selectImageDiv").innerHTML = "<table id='customfield_"+customfield_id+"_attribute_"+attribute_id+"_plainTable' width='100%' height='100%'><tr><td valign='top' width='25%' id='customfield_"+customfield_id+"_attribute_"+attribute_id+"_folderList'></td><td valign='top'><div id='customfield_"+customfield_id+"_attribute_"+attribute_id+"_imageList'></div></td></tr></table>";
	
	// Show it
	$("customfield_"+customfield_id+"_attribute_"+attribute_id+"_selectImageDiv").style.display = "block";

	// Load imageFolders - attribute_$a_row[ID]_selectImageDiv
	$('customfield_'+customfield_id+'_attribute_'+attribute_id+'_folderList').innerHTML = "Henter billedmapper...";
	var dothis = "ajax_customfield_returnImageFolders";
	var url = '/cms/modules/picturearchive/picturearchive.ajaxresponders.php';
	var pars = 'do=' + dothis;
	pars += '&customfield_id=' + customfield_id;
	pars += '&attribute_id=' + attribute_id;
	var myAjaxImages = new Ajax.Request(
		url, 
		{
			method: 'post', 
			parameters: pars, 
			onComplete: customfield_showFolders
		});
}

function customfield_showFolders(originalRequest) {
	var response = originalRequest.responseText;
	var arr_response = response.split("|||")
	$('customfield_'+arr_response[1]+'_attribute_'+arr_response[2]+'_folderList').innerHTML = arr_response[0]; 
}

function customfield_folderClicked(customfield_id, attribute_id, folder_id) {
	$('customfield_'+customfield_id+'_attribute_'+attribute_id+'_imageList').innerHTML = "Henter billeder...";
	customfield_loadImages(customfield_id, attribute_id, folder_id);
}

function customfield_loadImages(customfield_id, attribute_id, folder_id) {
	var dothis = "ajax_customfield_returnFolderImages";
	var url = '/cms/modules/picturearchive/picturearchive.ajaxresponders.php';
	var pars = 'do=' + dothis + '&folder_id=' + folder_id;
	pars += '&customfield_id=' + customfield_id;
	pars += '&attribute_id=' + attribute_id;
	var myAjax = new Ajax.Request(
		url, 
		{
			method: 'post', 
			parameters: pars, 
			onComplete: customfield_showImages
		});
}

function customfield_showImages(originalRequest) {
	var response = originalRequest.responseText;
	var arr_response = response.split("|||")
	$('customfield_'+arr_response[1]+'_attribute_'+arr_response[2]+'_imageList').innerHTML = arr_response[0]; 
}


function customfield_imageClicked(obj,customfield_id, attribute_id, image_id) {
	$('CUSTOM___'+customfield_id+'___'+attribute_id).value = image_id;
	var clickedImage = obj.getElementsByTagName("img");
	$('customfield_'+customfield_id+'_attribute_'+attribute_id+'_imgthumb').src = clickedImage[0].src;
	$('customfield_'+customfield_id+'_attribute_'+attribute_id+'_selectImageDiv').style.display = "none";
	$('customfield_'+customfield_id+'_attribute_'+attribute_id+'_imgthumb').style.display = "block";
	$('customfield_'+customfield_id+'_attribute_'+attribute_id+'_selectImageButton').disabled = false;
	$('customfield_'+customfield_id+'_attribute_'+attribute_id+'_noImageButton').disabled = false;
} 

function customfield_noImage(customfield_id, attribute_id) {
	$('CUSTOM___'+customfield_id+'___'+attribute_id).value = 0;
	$('customfield_'+customfield_id+'_attribute_'+attribute_id+'_imgthumb').style.display = "none";
	$('customfield_'+customfield_id+'_attribute_'+attribute_id+'_noImageButton').disabled = "true";
}

function highlight(obj) {
	obj.style.backgroundColor = "#ffffbe";
}

function highlight_off(obj) {
	obj.style.backgroundColor = "#f1f1e3";
}


function switchEditors(oNode,sType){
	err = false;
	try{
		var i=0;
		for (i=0;i<oNode.childNodes.length;i++){
			childNode = oNode.childNodes.item(i);
			editor = FCKeditorAPI.GetInstance(childNode.name);
			if (editor && editor.EditorDocument && editor.EditMode == FCK_EDITMODE_WYSIWYG){
				editor.EditorDocument.designMode = sType;
			}
			switchEditors(childNode,sType);
		}
	}
	catch(err){}
	if (!err){
	/*		
		var i=0;
		for (i=0;i<oNode.childNodes.length;i++){
			childNode = oNode.childNodes.item(i);
			editor = FCKeditorAPI.GetInstance(childNode.name);
			if (editor && editor.EditorDocument && editor.EditMode == FCK_EDITMODE_WYSIWYG){
				editor.EditorDocument.designMode = sType;
			}
			switchEditors(childNode,sType);
		}
	*/
	}
}

/* TAGS */

function show_tag_suggestions(originalRequest){
	$("tags_autosuggest").innerHTML = "Foreslåede nøgleord: " + (originalRequest.responseText ? originalRequest.responseText : "Ingen pt.");
}

function add_suggested_tag(tag){
	tags = $("taglist").value.split(",");
	tags[tags.length-1] = tag;
	$("taglist").value = "";
	for (i=0; i<tags.length; i++){
		$("taglist").value += trim(tags[i]);
		if (i < tags.length-1){
			$("taglist").value += ", ";
		}
	}
	$("taglist").value += ", ";
	show_tag_suggestions("");
	if ($("taglist").createTextRange) {
		oRange = $("taglist").createTextRange();
		$("taglist").createTextRange();
		oRange.moveStart("character",$("taglist").value.length);
		oRange.moveEnd("character",$("taglist").value.length); 
		oRange.select();
	}
}

function tag_handler(site_id){
	all_words = $("taglist").value.split(",");
	being_typed = all_words[all_words.length-1];
	var url = "/cms/modules/tags/tags.ajaxresponders.php";
	var pars = "do=fetch_tags&letters="+being_typed+"&allusedtags="+$("taglist").value+"&site_id="+site_id;
	var myAjax = new Ajax.Request(
		url,
			{	
				method: "post",
				parameters: pars,
				onFailure: reportAjaxError,
				onComplete: show_tag_suggestions
			}
	);
}

function reportAjaxError(){
	alert("Der skete en AJAX-fejl. Prøv venligst igen.");
}


// Scriptaculous effects
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