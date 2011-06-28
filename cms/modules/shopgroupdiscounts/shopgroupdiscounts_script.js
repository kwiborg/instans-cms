function selectUsergroup() {
	// selectedGroup is a global variable which holds the id of the currently selected usergroup

	if ($("usergroupselector").value == "") {
		return false;
	} else {
		if ($('discount-heading')) {
			var confirmed = confirm("Du er ved at skifte til visning af en anden brugergruppe. Ændringer, som ikke er gemt, vil blive tabt. Ønsker du at skifte brugergruppe?");
		} else {
			var confirmed = true;
		}
		if (confirmed) {
			location = "index.php?content_identifier=shopgroupdiscounts&gid=" + $('usergroupselector').value;
		} else {
			$("usergroupselector").value = selectedGroup;
		}
	}
}

function saveGroupDiscounts() {
	$('form_groupdiscounts').submit();
}

function validate_generaldiscount(obj) {
	obj.value = trim(obj.value);
	var temp = obj.value;
	if (temp.indexOf(',') > -1 && temp.indexOf('.') > -1) {
		obj.value = obj.value.replace(/\./g,"");
	}
	obj.value = obj.value.replace(/,/g,".");
	if (obj.value == "") {
		alert("Rabat skal være et tal!");
		obj.value = "0";
	}
	if (obj.value < 0) {
		alert("Rabat må ikke være negativ");
		obj.value = "0";
	}
	if (!isNumeric(obj.value)) {
		alert("Rabat skal være et tal!");
		obj.value = "0";
	}
	if (obj.value > 100) {
		alert("Rabat kan maksimalt være 100%");
		obj.value = "";
	}
}

function validate_groupdiscount(obj) {
	obj.value = trim(obj.value);
	var temp = obj.value;
	if (temp.indexOf(',') > -1 && temp.indexOf('.') > -1) {
		obj.value = obj.value.replace(/\./g,"");
	}
	obj.value = obj.value.replace(/,/g,".");
	if (obj.value < 0) {
		alert("Rabat må ikke være negativ");
		obj.value = "";
	}
	if (!isNumeric(obj.value) && obj.value != "") {
		alert("Rabat skal være et tal eller blankt!");
		obj.value = "";
	}
	if (obj.value > 100) {
		alert("Rabat kan maksimalt være 100%");
		obj.value = "";
	}
}

/*
function validateDiscount(obj) {
	obj.value = trim(obj.value);
	obj.value = obj.value.replace(/,/g,".");
	if (!isNumeric(obj.value)) {
		alert("Rabat skal være et tal!");
		obj.value = "";
	}
	if (obj.value == 0) {
		obj.value = "";
	}
	if (obj.value > 100) {
		alert("Rabat kan maksimalt være 100%");
		obj.value = "";
	}
}
*/
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