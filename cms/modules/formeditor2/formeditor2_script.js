function validEmail(val){
 if (val.indexOf("@")<0 || val.indexOf(".")<0) return false;
 return true;
}

function verify_form_general() {
 theForm = document.forms[0];
 if (theForm.form_title.value=="") {
  alert("Udfyld venligst titel.");
  return;
 }
 if (theForm.form_email.value!="" && !validEmail(theForm.form_email.value)) {
  alert("Indtast venligst en gyldig e-mail-adresse.");
  return;
 }
 if (!theForm.form_save_email.checked && !theForm.form_save_database.checked) {
  alert("Vælg venligst en måde at gemme dataene på.");
  return;
 }
 if (theForm.form_save_email.checked && theForm.form_email.value=="") {
  alert("Indtast venlist en e-mail-adresse.");
  return;
 }
 theForm.dothis.value = "save_general";
 theForm.submit();
}

function sletFelt(feltid, formid){
 if (confirm("Vil du slette dette felt i formularen?")) location="index.php?content_identifier=formeditor2&dothis=sletfelt&mode=rediger&formid="+formid+"&feltid="+feltid;
}

function verify_felt(){
 F = document.forms[0];
 T = F.form__felttype_res.value;
 if (T=="") { 
  alert("Vælg venligst en felttype.");
  return;
 }
 if (F.form__felttitle.value==""){
  alert("Indtast venligst en titel/overskrift for feltet.");
  return;
 }
 if (T==3){
  liveAntal = 0;
  for(i=0; i<radioknapper.length; i++){
   if (radioknapper[i][2] == 0) liveAntal += 1;
  }
  if(liveAntal<2) {
   alert("En radioknap-gruppe skal indeholde mindst 2 radioknapper.");
   return;
  }
  if (!checkRadioCaptions()) {
   alert("Angiv venligst en ledetekst til alle radioknapper.");
   return;
  }
  F.radiocaptions.value = buildCaptionString("radio");
  F.radiodisabledstates.value = buildDisabledString("radio");
  F.radioslettetstates.value = buildSlettetString("radio");
  F.radiocount.value = radioknapper.length;
 }
 if (T==4){
  liveAntal = 0;
  for(i=0; i<checkbokse.length; i++){
   if (checkbokse[i][2] == 0) liveAntal += 1;
  }
  if(liveAntal<1) {
   alert("En kryds-af-boks gruppe skal indeholde mindst 1 kryds-af-boks.");
   return;
  }
  if (!checkCheckCaptions()) {
   alert("Angiv venligst en ledetekst til alle kryds-af-bokse.");
   return;
  }
  F.checkcaptions.value = buildCaptionString("check");
  F.checkdisabledstates.value = buildDisabledString("check");
  F.checkslettetstates.value = buildSlettetString("check");
  F.checkboxcount.value = checkbokse.length;
 }
 F.form_felt_text_verifyfilled.disabled = false;
 F.dothis.value="gemfelt";
 F.submit();
}

function buildCaptionString(type){
 F = document.forms[0];
 S="";
 if (type=="radio"){
  for(i=0; i<radioknapper.length; i++){
   S = S + radioknapper[i][0] + "|";
  }
 }
 if (type=="check"){
  for(i=0; i<checkbokse.length; i++){
   S = S + checkbokse[i][0] + "|";
  }
 }
 return S;
}

function buildDisabledString(type){
 F = document.forms[0];
 S="";
 if (type=="radio"){
  for(i=0; i<radioknapper.length; i++){
   S = S + radioknapper[i][1] + "|";
  }
 }
 if (type=="check"){
  for(i=0; i<checkbokse.length; i++){
   S = S + checkbokse[i][1] + "|";
  }
 }
 return S;
}

function buildSlettetString(type){
 F = document.forms[0];
 S="";
 if (type=="radio"){
  for(i=0; i<radioknapper.length; i++){
   S = S + radioknapper[i][2] + "|";
  }
 }
 if (type=="check"){
  for(i=0; i<checkbokse.length; i++){
   S = S + checkbokse[i][2] + "|";
  }
 }
 return S;
}

function showFeltSettings(felttype, val){
 document.forms[0].form__felttype_res.value = val;
 A = document.getElementsByTagName("input");
 for(i=0; i<A.length; i++){
  if(A[i].name.indexOf("form_felt_" + felttype + "_") > -1) A[i].disabled=false;
  else { 
   if (A[i].name.indexOf("form_felt_" + felttype) < 0 && A[i].name.indexOf("form_felt_") > -1) {
   A[i].disabled=true;
   A[i].checked=false;
   A[i].value="";
   }
  }
 }
 A = document.getElementsByTagName("div");
 for(i=0; i<A.length; i++){
  if (A[i].id.indexOf("form_felt_" + felttype) > -1) A[i].style.display="block";
  else {
   if (A[i].id.indexOf("form_felt_" + felttype) < 0 && A[i].id.indexOf("form_felt_") > -1) A[i].style.display="none";
  }
 }
 size();
}

function relation(state, listOfElements, listOfStates, disableOnUncheck){
 if (state == true){
  for(i=0; i<listOfElements.length; i++){
   eval("document.forms[0]." + listOfElements[i] + ".disabled = " + (listOfStates[i] == 0 ? "true" : "false")); 
   eval("document.forms[0]." + listOfElements[i] + ".checked  = " + (listOfStates[i] == 0 ? "false" : "true")); 
  }
 }
 if (state == false){
  for(i=0; i<listOfElements.length; i++){
   eval("document.forms[0]." + listOfElements[i] + ".disabled = " + (listOfStates[i] == 0 ? "false" : (disableOnUncheck == 0 ? "false" : "true"))); 
   eval("document.forms[0]." + listOfElements[i] + ".checked  = " + (listOfStates[i] == 0 ? "false" : "false")); 
  }
 }
}

/// RADIOBUTTONS ------
radioknapper = new Array();

function checkRadioCaptions(){
 for(i=0; i<radioknapper.length; i++){
  if (radioknapper[i][0]=="") {
   eval("document.forms[0].radiocaption_"+i+".style.border='1px solid red'")
   return false;
  }
 }
 return true;
}

function generateRadioGroup(){
  html="";
  for(i=0; i<radioknapper.length; i++){
   // html = html + "<b>{o}</b>&nbsp;<input type='text' name='radiocaption_" + i + "'/ class='inputfelt' value='" + radioknapper[i][0]  + "' onchange='setRadioCaption(this.value,"+i+")'>&nbsp;<input type='checkbox' onclick='setRadioDisabled(this.checked,"+i+")' name='radiodisabled_" + i + "' " + (radioknapper[i][1] == 1 ? "checked" : "") + ">&nbsp;Disabled&nbsp;|&nbsp;" + (radioknapper[i][2] == 0 ? "<a href='#' onclick='removeRadio(" + i + ")'>Fjern</a><br/>" : "<a href='#' onclick='reAddRadio(" + i + ")'>Bring tilbage</a><br/>");
   if (radioknapper[i][2] == 0) html = html + "<b>{o}</b>&nbsp;<input type='text' name='radiocaption_" + i + "'/ class='inputfelt' value='" + radioknapper[i][0]  + "' onchange='setRadioCaption(this.value,"+i+")'>&nbsp;<input type='checkbox' onclick='setRadioDisabled(this.checked,"+i+")' name='radiodisabled_" + i + "' " + (radioknapper[i][1] == 1 ? "checked" : "") + ">&nbsp;Disabled&nbsp;|&nbsp;<a href='#' onclick='removeRadio(" + i + ")'>Fjern</a><br/>";
  }
  html = html + "<br/>&raquo;&nbsp;<a href='#' onclick='addRadio()'>Tilføj en radioknap til gruppen</a>";
  document.getElementById("radioknapper").innerHTML = html;
  size();
}

function removeRadio(nr){
 if (confirm("Vil du fjerne denne radioknap?")) {
  //radioknapper.splice(nr,1);
  radioknapper[nr][2] = 1; // sæt slettet = 1
  generateRadioGroup();
 }
}

function addRadio(){
 radioknapper[radioknapper.length] = ["",0, 0];
 generateRadioGroup();
}

function reAddRadio(nr){
 radioknapper[nr][2] = 0;
 generateRadioGroup();
}

function setRadioCaption(text, nr){
 text = text.replace("'","");
 radioknapper[nr][0] = text;
 generateRadioGroup();
}

function setRadioDisabled(value, nr){
 if (value==true) value=1; 
 radioknapper[nr][1] = value;
 generateRadioGroup();
}

/// CHECKBOXES ------
checkbokse = new Array();

function checkCheckCaptions(){
 for(i=0; i<checkbokse.length; i++){
  if (checkbokse[i][0]=="")  {
   eval("document.forms[0].checkcaption_"+i+".style.border='1px solid red'")
   return false;
  }
 }
 return true;
}

function generateCheckboxGroup(){
  html="";
  for(i=0; i<checkbokse.length; i++){
   // html = html + "<b>{x}</b>&nbsp;<input type='text' name='checkcaption_" + i + "'/ class='inputfelt' value='" + checkbokse[i][0]  + "' onchange='setCheckCaption(this.value,"+i+")'>&nbsp;<input type='checkbox' onclick='setCheckDisabled(this.checked,"+i+")' name='checkdisabled_" + i + "' " + (checkbokse[i][1] == 1 ? "checked" : "") + ">&nbsp;Disabled&nbsp;|&nbsp;" + (checkbokse[i][2] == 0 ? "<a href='#' onclick='removeCheckbox(" + i + ")'>Fjern</a><br/>" : "<a href='#' onclick='reAddCheckbox(" + i + ")'>Bring tilbage</a><br/>");
   if (checkbokse[i][2] == 0) html = html + "<b>{x}</b>&nbsp;<input type='text' name='checkcaption_" + i + "'/ class='inputfelt' value='" + checkbokse[i][0]  + "' onchange='setCheckCaption(this.value,"+i+")'>&nbsp;<input type='checkbox' onclick='setCheckDisabled(this.checked,"+i+")' name='checkdisabled_" + i + "' " + (checkbokse[i][1] == 1 ? "checked" : "") + ">&nbsp;Disabled&nbsp;|&nbsp;<a href='#' onclick='removeCheckbox(" + i + ")'>Fjern</a><br/>";
  }
  html = html + "<br/>&raquo;&nbsp;<a href='#' onclick='addCheckbox()'>Tilføj en kryds-af-boks til gruppen</a>";
  document.getElementById("checkbokse").innerHTML = html;
  size();
}

function removeCheckbox(nr){
 if (confirm("Vil du fjerne denne kryds-af-boks?")) {
  //checkbokse.splice(nr,1);
  checkbokse[nr][2] = 1;
  generateCheckboxGroup();
 }
}

function addCheckbox(){
 checkbokse[checkbokse.length] = ["",0, 0];
 generateCheckboxGroup();
}

function reAddCheckbox(nr){
 checkbokse[nr][2] = 0;
 generateCheckboxGroup();
}

function setCheckCaption(text, nr){
 text = text.replace("'","");
 checkbokse[nr][0] = text;
 generateCheckboxGroup();
}

function setCheckDisabled(value, nr){
 if (value==true) value=1; 
 checkbokse[nr][1] = value;
 generateCheckboxGroup();
}

function disableFelttypeSelector(){
 for(i=0; i<document.forms[0].form__felttype.length; i++){
  document.forms[0].form__felttype[i].disabled = true;
 }
}

///

function disable_mapping_selectors(){
	if ($("mapped_usergroup_id") && $("mapped_newsletter_id")){
		if ($("mapped_usergroup_id").value != 0){
			$("mapped_newsletter_id").disabled = true;
		} else {
			$("mapped_newsletter_id").disabled = false;
		}
		if ($("mapped_newsletter_id").value != 0){
			$("mapped_usergroup_id").disabled = true;
		} else {
			$("mapped_usergroup_id").disabled = false;
		}
	}	
}

function verify_mandatory_fields(){
	selected_fields = [];
	A = document.getElementsByTagName("select");
	for (i=0; i<A.length; i++){
		if(A[i].name.indexOf("fieldmapping_") > -1){
			selected_fields[selected_fields.length] = A[i].value;
		}
	}
	for (i=0; i<mandatory_field_ids.length; i++){
		this_id = mandatory_field_ids[i];
		times_selected = 0;
		for(k=0; k<selected_fields.length; k++){
			if (selected_fields[k] == this_id){
				times_selected++;
			}
		}
		if (times_selected != 1){
			alert("Tilknyt venligst det obligatoriske felt \"" + mandatory_field_names[i]  + "\" til ét (og kun ét) formularfelt.");
			return false;
		}
	}
	for (i=0; i<other_field_ids.length; i++){
		this_id = other_field_ids[i];
		times_selected = 0;
		for(k=0; k<selected_fields.length; k++){
			if (selected_fields[k] == this_id){
				times_selected++;
			}
		}
		if (times_selected > 1){
			alert("Tilknyt venligst feltet \"" + other_field_names[i]  + "\" til højst ét formularfelt.");
			return false;
		}
	}
	document.forms[0].dothis.value = "save_mappings";
	document.forms[0].submit();
}

/*
function attachForm(formid, pageid, menuid){
 location = "index.php?content_identifier=formeditor2&dothis=attachform&menuid="+menuid+"&formid="+formid+"&pageid="+pageid;
}
*/