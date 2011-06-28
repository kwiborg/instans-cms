function impmode(v){
    $("imptemplate").disabled = true;
    $("impgroup").disabled = true;
    $("import_newgroupname").disabled = true;
    $("imptemplate").value = 0;
    $("impgroup").value = 0;
    $("import_newgroupname").value = "";
    if (v == 1){
        $("imptemplate").disabled = false;
    }
    if (v == 2){
        $("impgroup").disabled = false;
        $("import_newgroupname").disabled = false;
    }
    if (v == 3){
        $("imptemplate").disabled = false;
        $("impgroup").disabled = false;
        $("import_newgroupname").disabled = false;
    }
    $("impmode_res").value = v;
}

function verify_import(){
    if ($("impmode_res").value == ""){
        alert("Vælg venligst hvilken handling, du vil udføre.");
        return;
    }
    if ($("impmode_res").value == 1){
        if ($("imptemplate").value == 0){
            alert("Vælg venligst et nyhedsbrev.");
            return;
        }
    }
    if ($("impmode_res").value == 2){
        if ($("impgroup").value == 0 && $("import_newgroupname").value == ""){
            alert("Vælg venligst en brugergruppe.");
            return;
        }
    }
    if ($("impmode_res").value == 3){
        if ($("imptemplate").value == 0 || ($("impgroup").value == 0 && $("import_newgroupname").value == "")){
            alert("Vælg venligst både et nyhedsbrev og en brugergruppe.");
            return;
        }
    }
    if (confirm('Er du sikker på, at du vil importere?')){
        document.forms[0].submit()
    }
}

function gchange(v){
    if (v > 0){
        $("import_newgroupname").value = "";
    }
}

function ngchange(v){
    if (v != ""){
         $("impgroup").value = 0;
    } else {
         $("impgroup").value = 0;
    }
}

function ngout(v){
    if (v != ""){
         $("impgroup").value = 0;
    } else {
         $("impgroup").value = 0;
    }
    nogood = 0;
    for (i=0; i<gnames.length; i++){
        if (gnames[i].toUpperCase() == v.toUpperCase()){
            nogood = 1;        
        }
    }
    if (nogood == 1){
        alert("Der findes allerede en gruppe med navnet '"+v+"'. Vælg venligst et andet navn.");
        $("import_newgroupname").value = "";
    }
}
