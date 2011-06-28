<?php
include_once($_SERVER[DOCUMENT_ROOT]."/cms_config.inc.php");
?><script type="text/javascript">
var cmsUrl = "<?php echo $cmsURL; ?>";
var newsletter_id = "<?php echo $_GET[nid]; ?>";
var batchsize = "<?php echo $batchsize; ?>";
var batchwait = "<?php echo $batchwait; ?>";
</script>