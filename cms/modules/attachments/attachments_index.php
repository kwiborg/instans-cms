<?php
if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
include_once("attachments_common.inc.php");
if ($dothis == "oversigt" || !$dothis) {
?>
<h1>
 Vedhæftede filer
</h1>
<div class="broedtekst">
 Her kan du se en oversigt over vedhæftede filer. Du kan også vedhæfte flere filer.
</div>
<form id="defaultForm" method="post" action="">
 <input type="hidden" name="dothis" value="" />
 <div class="feltblok_header">
  Filer vedhæfet til "<?php echo returnHeading($id, $tabel) ?>"
 </div>
 <div class="feltblok_wrapper">
 	<?php
	echo returnAttachedfiles($id, $tabel);
	?>
 </div>
 <div class="knapbar">
  <input type="button" value="Vedhæft en fil" onclick="addfile(<?php echo "$id, '$tabel', $_GET[menuid]" ?>)">
  <input type="button" value="Færdig" onclick="<?php echo "location='index.php?content_identifier=".strtolower($tabel) ."&menuid=$menuid'" ?>">
 </div>
</form>
<?php
 }
?>
</form>