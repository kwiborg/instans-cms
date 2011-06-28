Event.observe(window, 'load', registration_toggle, false);

function verify()
{
 theForm = document.forms[0];
 if (theForm.name.value == "") {
  alert("Udfyld venligst feltet Gruppenavn.");
  return;
 }
 document.forms[0].dothis.value = "gem";
 document.forms[0].submit();
}

function opretNyGruppe(parent_id)
{
 location = "index.php?content_identifier=groups&dothis=opret&parent_id=" + parent_id;
}

function sletGruppe(id)
{
 txt = "Er du sikker på, at du vil nedlægge denne gruppe? (Brugerne i den bliver ikke slettet.)";
 if (confirm(txt)) {
  location = "index.php?content_identifier=groups&dothis=slet&id=" + id;
 } 
}

function medlemmer(id)
{
 location = "index.php?content_identifier=groups&dothis=medlemmer&id=" + id;
}

function makeMember(user_id, group_id)
{
 location = "index.php?content_identifier=groups&dothis=addmember&user_id=" + user_id + "&group_id=" + group_id;
}

function removeMember(user_id, group_id)
{
 location = "index.php?content_identifier=groups&dothis=removemember&user_id=" + user_id + "&group_id=" + group_id;
}

function registration_toggle(){
	if (document.forms[0].registration_open){
		if (document.forms[0].registration_open.checked || document.forms[0].editing_open.checked){
			dis = false;
		} else {
			dis = true;
		}
		selects = document.getElementsByClassName("reg");
		for (i=0; i<selects.length; i++){
			selects[i].disabled = dis;
		}
	}
}

function updateSortable(){
	group_id = $("det_nye_id").value;
	var order = Sortable.serialize('sortme');
	order = encodeURIComponent(order);
		var url = '/cms/modules/groups/groups.ajaxresponders.php';
		var pars = 'do=reorder&order='+order+"&group_id="+group_id;
		var myAjax = new Ajax.Request(
			url,
			{	
				method: 'post',
				parameters: pars,
				onFailure: reportAjaxError,
				onComplete: nothing
			}
		);	
}

function reportAjaxError(){
	alert("Der opstod en AJAX-fejl. Prøv venligst igen.");
}

function nothing(){
}
