function attachFile(file_id, content_id, tablename){
	location = "index.php?content_identifier=filearchive&dothis=oldschool_addfile&file_id="+file_id+"&content_id="+content_id+"&tablename="+tablename;
}

function opretNyMappe()
{
 location = "index.php?content_identifier=filearchive&dothis=opretnymappe";
}

function opretFil(folder_id)
{
 location = "index.php?content_identifier=filearchive&dothis=opretfil&folderid=" + folder_id;
}

function verify_mappe()
{
 theForm = document.forms[0];
 if (theForm.mappenavn.value == "")
 {
  alert("Indtast venligst mappenavn.");
  return;
 }
 theForm.dothis.value = "gem_mappe";
 theForm.submit(); 
}

function verify_fil()
{
 theForm = document.forms[0];
 if (theForm.userfile.value == "")
 {
  alert("Vælg venligst en fil.");
  return;
 }
 if (theForm.title.value == "")
 {
  alert("Indtast venligst en kort titel.");
  return;
 }
 theForm.dothis.value = "upload_fil";
 theForm.submit(); 
}

function sletFil(id,returntofolder)
{
 if (confirm("Vil du slette filen?")) {
  location = "index.php?content_identifier=filearchive&dothis=sletfil&fileid="+id+"&folderid="+returntofolder;
 }
}

function sletMappe(id,mmo)
{
 if (confirm("Vil du slette mappen?")) {
  location = "index.php?content_identifier=filearchive&dothis=sletmappe&folderid="+id+"&mainmenuoff="+mmo;
 }
}