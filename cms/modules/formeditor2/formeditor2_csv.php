<?php
    include_once($_SERVER[DOCUMENT_ROOT] . "/cms/common.inc.php");
	include($_SERVER[DOCUMENT_ROOT] . "/cms/modules/formeditor2/formeditor2_common.inc.php");
	outputCSV($_GET[formid]);
?>