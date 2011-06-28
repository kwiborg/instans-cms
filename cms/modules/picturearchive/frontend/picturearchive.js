Event.observe(window, 'load', initSelector, false);
function initSelector(){
	$("gallery_folderselect").style.display="block";
	$("select_gallery_folder").onchange = function(){location=this.value};
}
