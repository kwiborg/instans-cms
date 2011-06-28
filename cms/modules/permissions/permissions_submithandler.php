<?php
 if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_GENERALPERMISSIONS", true);
?>