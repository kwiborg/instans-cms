<?php
 if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
 if ($dothis == "oversigt" || !$dothis) {
?>
<!-- ØVRE TEKST -->
<h1>
 Filarkiv: Oversigt
</h1>
<!-- /// -->
<!-- FORM -->
<form id="defaultForm" method="post" action="">
<input type="hidden" name="dothis" value="" />
<div class="feltblok_header">
 Mapper
</div>
<div class="feltblok_wrapper">
 <?php
  $output = filMappeListe();
  echo $output[0];
 ?>
</div>
<div class="knapbar"><input type="button" class="lilletekst_knap" value="Opret ny mappe" onclick="opretNyMappe()" /></div>

<!-- /// -->
</form>

<script type="text/javascript">
 <?php
  echo $output[1];
  if ($folderid) echo "hideShowFolder($folderid, 1);\n"
 ?>
</script>

<?php
 }
?>

<?php
 if ($dothis == "opretnymappe") {
?>
	
<!-- ØVRE TEKST -->
<h1>
 Opret ny mappe til filer
</h1>
<div class="broedtekst">
</div>
<!-- /// -->
<!-- FORM -->
<form id="defaultForm" method="post" action="">
<input type="hidden" name="dothis" value="" />
<div class="feltblok_header">
 Ny mappe
</div>
<div class="feltblok_wrapper">
 <h2>Mappens navn:</h2>
 <input type="text" name="mappenavn" class="inputfelt" value="<?php if ($entrydata) echo $entrydata["ENTRY_LABEL"] ?>">
 
 <!--<h2>Er mappen privat (kun synlig for dig?):</h2>
 <input type="hidden" name="privat_mappe_res" value="" />  
 <input type="radio" name="privat_mappe" onclick='setResValue(this.name, 1);' />&nbsp;Nej<br />
 <input type="radio" name="privat_mappe" onclick='setResValue(this.name, 2);' />&nbsp;Ja<br />-->
</div>
 
<div class="knapbar">
 <input type="button" value="Afbryd" onclick="location='index.php?content_identifier=<?php echo $content_identifier ?>'" />
 <input type="button" value="Gem" onclick="verify_mappe()" />
</div>

<!-- /// -->
</form>
<?php
 }
?>

<?php
 if ($dothis == "opretfil") {
?>
	
<!-- ØVRE TEKST -->
<h1>
 Upload en fil til arkivet
</h1>
<div class="broedtekst">
 Find den fil, du vil uploade via [gennemse], giv den en kort titel og beskrivelse, og klik [videre]. Så bliver filen lagt i arkivet. Bemærk: Du kan lægge alle typer filer i arkivet, som må være interessante for brugere at downloade eller dele.
</div>
<!-- /// -->
<!-- FORM -->
<form id="defaultForm" method="post" action="" enctype="multipart/form-data">
<input type="hidden" name="dothis" value="" />
<input type="hidden" name="filtype" value="" />
<div class="feltblok_header">
 Fil og beskrivelse af fil
</div>
<div class="feltblok_wrapper">

 <h2>Vælg fil:</h2>
 <input type="file" name="userfile" class="inputfelt" onkeypress="this.blur()">
 <?php if ($error==1) { ?>
  <br />Fejl: Den fil, du forsøger at uploade, fylder 0 bytes.
 <?php } ?>

 <h2>Kort titel:</h2>
 <input type="text" name="title" class="inputfelt" value="<?php echo $data["TITLE"] ?>">

 <h2>Beskrivelse (ikke påkrævet):</h2>
 <textarea name="description" class="inputfelt_kort" cols="70" rows="5"><?php echo $data["DESCRIPTION"] ?></textarea>
</div>

<div class="knapbar">
 <input type="button" value="Afbryd" onclick="location='index.php?content_identifier=<?php echo $content_identifier ?>'" />
 <input type="button" value="Videre" onclick="verify_fil()" />
</div>

<!-- /// -->
</form>
<?php
 }
?>