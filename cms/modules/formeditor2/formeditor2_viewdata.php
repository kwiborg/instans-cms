<?php 
include($_SERVER[DOCUMENT_ROOT]."/cms_config.inc.php");
session_start();
if (!$_SESSION["CMS_USER"]) {
		header("location: ../../login.php");
}
 include($_SERVER[DOCUMENT_ROOT]."/cms/common.inc.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='content-type' content='text/html; charset=UTF-8' />
<title>Dataudtræk fra formen <?php echo returnFormTitle($formid) ?></title>
<style type="text/css">
 body{
  margin: 2% 2%;
 }
 td.dataUdHead{
  font-weight:bold;
 }
 table{
  font-size:10px;
 }
</style>
</head>
<body>
<div class="page">
<div style="font-size:18px; font-weight:bold; margin-bottom:20px">
 Data udtrukket fra formularen <?php echo "<em>" . returnFormTitle($_GET[formid]) . "</em>, " . returnNiceDateTime(time(), 1); ?>
</div>
<div>
<?php 
 $sql = "select * from TILMELDINGER where FORM_ID='$_GET[formid]' order by CREATED_DATE asc";
 $result = mysql_query($sql);
 while ($row = mysql_fetch_array($result)) {
  echo "<div style='border:1px solid black; padding:5px; margin:5px 5px 0 5px; background-color:#aaa; font-weight:bold;'>Oprettet " . returnNiceDateTime($row[CREATED_DATE], 1) . "</div>";
  echo "<div style='border:1px solid black; padding:5px; margin:0 5px 15px 5px'>".outputTilmeldingsHtml(1, $row[ID])."</div>";
 }
?>
</div>
</body>
</html>