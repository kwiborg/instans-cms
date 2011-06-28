function addfile(id, tabel, menuid) {
 location = "index.php?content_identifier=filearchive&menuid="+menuid+"&dothis=&selectfile=1&returntoid="+id+"&returntotabel="+tabel;
}

function remove_attachment(id, file_id, tabel, allfiles, menuid) {
 if (confirm("Vil du fjerne vedhæftningen? (Filen bliver ikke slettet, den ligger stadig i filarkivet).")) {
  location = "index.php?content_identifier=attachments&dothis=remove_attachment&menuid="+menuid+"&id="+id+"&fileid="+file_id+"&tabel="+tabel+"&allfiles="+allfiles;
 }
}