<?php
 ///////////////////////////////////////////////////////////////////////////////////////////
 // * TEMPLATE: WWW.EASYFOOD.AS -- 14.03.06 -- CJS
 ///////////////////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
 <title>
  <?php echo returnPageTitleTag("PUBLIKATOR: ") ?>
 </title>
 <link rel="stylesheet" type="text/css" href="includes/css/easyfood_general.css" />
</head>
<body style="margin:0;">
  <div id="topWrapper">
   <h1>SITE 3</h1></div>
  </div>
  <div id="centerWrapper" style="width:80%; margin:0;left:0;">
   <div id="contentWrapper" style="width:100%; margin:0;left:0;">
    <?php echo buildPageMainContent($_GET[pageid], $_GET[mode]) ?>
   </div>
  </div>
</body>
</html>
