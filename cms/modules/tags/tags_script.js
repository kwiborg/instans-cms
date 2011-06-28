function merge_tags(tag_id, tag_name, mergeinto_id) {
	if (mergeinto_id == "") {
		return;
	} else {
		var arr_mergeinto = mergeinto_id.split("__");
		mergeinto_id = arr_mergeinto[0];
		mergeinto_name = arr_mergeinto[1];
	
		if (confirm("Er du sikker på at du vil:\n\t(*) Flytte alle referencer fra '"+tag_name+"' til '"+mergeinto_name+"'\n\t(*) Slette tagget '"+tag_name+"'\n\nVil du lægge de to tags sammen, du kan ikke fortryde?!")) {
			location="index.php?content_identifier=tags&dothis=merge&tag_id="+tag_id+"&mergeinto_id="+mergeinto_id;		
		} else {
			return;
		}
	}
}

function tag_makeeditable(tag_id) {
	var edit_element = $('tagname_'+tag_id);
	var url = '/cms/modules/tags/tags.ajaxresponders.php';

	var editor = new Ajax.InPlaceEditor(edit_element, url, {
		okText:'Gem',
		cancelText:'Afbryd redigering',
		savingText:'Gemmer',
		clickToEditText:'Rediger tag',
		callback:function(form, value) { return 'do=ajax_tag_editsave&tag_id='+tag_id+'&tagname=' + encodeURIComponent(value) }, /*Element.show('ajaxloader_tags'); */
		onComplete:tag_editcomplete
	}); 
	editor.enterEditMode();
}

function tag_editcomplete(originalRequest) {
/*
	Element.hide('ajaxloader_tags');
*/
}