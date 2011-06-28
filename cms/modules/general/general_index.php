<?php
 if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
 if ($dothis == "oversigt" || !$dothis) {
?>

<!-- ØVRE TEKST -->
<h1>
 Generelle indstillinger
</h1>
<div class="broedtekst">
</div>
<!-- /// -->
<!-- FORM -->
<form id="defaultForm" method="post" action="">
<input type="hidden" name="dothis" value="" />
<input type="hidden" name="handling" value="<?php echo $dothis ?>" />
<div class="feltblok_header">
 Indstillinger for website
</div>
<div class="feltblok_wrapper">
 <h2>Beskrivelse til søgemaskiner (<em>META description</em>):</h2>
 <textarea name="meta_description" cols="70" rows="5"><?php echo $data["META_DESCRIPTION"] ?></textarea>
 <p class="feltkommentar">Beskrivelse kan tilpasses på sidebasis under sideredigering.</p>
 <h2>Nøgleord til søgemaskiner (<em>META keywords</em>) - komma-separeret:</h2>
 <textarea name="meta_keywords" cols="70" rows="5"><?php echo $data["META_KEYWORDS"] ?></textarea>
 <p class="feltkommentar">Nøgleord kan tilpasses på sidebasis under sideredigering.</p>
</div>
<div class="knapbar">
 <input type="button" value="Afbryd" onclick="location='index.php?content_identifier=pages'" />
 <input type="button" value="Gem ændringer" onclick="verify()" />
</div>

<!-- /// -->
</form>
<script>
 <?php
   if ($saved==1) echo "alert('Ændringerne blev gemt.');\n";
 ?>
</script>
<?php
 }
?>



