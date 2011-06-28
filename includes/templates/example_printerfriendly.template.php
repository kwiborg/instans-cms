<?php
	global $cmsAbsoluteServerPath;
 ///////////////////////////////////////////////////////////////////////////////////////////
 // * TEMPLATE: EXAMPLE
 ///////////////////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <title>PRINT!!!!<?php echo returnPageTitleTag($arr_content, "ExampleSite: ") ?></title>
  <?php echo metaTagGenerator($arr_content); ?>
  <?php echo modeHtmlHeaderIncludes($arr_content, "css"); ?>
  <link rel="stylesheet" type="text/css" href="<?$arr_content[baseurl];?>/includes/css/example.css" />
  <?php include ($cmsAbsoluteServerPath . "/frontend/frontend_headerJS.php"); ?> 
  <script src="<?=$arr_content[baseurl];?>/includes/javascript/prototype.js" type="text/javascript"></script>
  <script src="<?$arr_content[baseurl];?>/includes/javascript/scriptaculous.js" type="text/javascript"></script>    
  <script src="<?$arr_content[baseurl];?>/includes/javascript/common.js" type="text/javascript"></script>
  <script src="<?$arr_content[baseurl];?>/includes/javascript/example.js" type="text/javascript"></script>
  <?php echo modeHtmlHeaderIncludes($arr_content, "javascript"); ?>
</head>
<body onload="resizeDivs()">
 <form name="controlForm" method="post" action="<?=$arr_content[baseurl];?>">
  <input type="hidden" name="dothis" />
 </form>
 <div id="siteWrapper">
  <div id="topWrapper">
   <?php echo languageSelectorMenu($arr_content, "&nbsp;|&nbsp;"); ?>
   <h1>SITE 10000000</h1></div>
  <div id="redLine">
   	<?php 
  
 $productMenuPath = shopProductMenuPath($arr_content, shopCategoryPathArray($arr_content, $arr_content["group"], array()), "&nbsp;&nbsp;&raquo;&nbsp;&nbsp;");
  
 echo $path = returnHTMLMenuPath(
	$arr_content[pageid], 
	$arr_content[mode], 
	"<a href='$arr_content[baseurl]/index.php' title='".cmsTranslate("Home")."'>".cmsTranslate("Home")."</a>&nbsp;&nbsp;&raquo;&nbsp;&nbsp;", 
	$productMenuPath ? "&nbsp;&nbsp;&raquo;&nbsp;&nbsp;".$productMenuPath : "", 
	"&nbsp;&nbsp;&raquo;&nbsp;&nbsp;", $arr_content ); 
	?>  
  </div>
  <div id="centerWrapper">
   <div id="menuWrapper">
    <?php echo returnMenuSeperator(cmsTranslate("MainMenu")); ?>
    <?php 
	 if ($arr_content[lang]=="da") echo newBuildFrontendMenu("","",0,0, $arr_content, ""); 
	 if ($arr_content[lang]=="en") echo newBuildFrontendMenu("","",0,0, $arr_content, ""); 
	?>
    <?php echo returnMenuSeperator(cmsTranslate("ProductMenu")); ?>
	<?php echo "<div>".shopProductMenu(0, $arr_content)."</div>";	 ?>
	<?php if ($_SESSION[LOGGED_IN]) {
			echo "<br /><br />Logget ind: " . returnFieldValue("USERS", "FIRSTNAME", "ID", $_SESSION[USERDETAILS][0][0]) . " <a href='javascript:doLogout()'>Log af</a>";
		  }		  
	?>
   </div>
   <div id="contentWrapper">
    <?php
	/*
	$arr_customfielddata = return_customfielddata($arr_content);
	echo "<br/>\$arr_customfielddata:<pre>";
	print_r($arr_customfielddata);
	echo "</pre>";
	*/
	echo "<br/>\$arr_content:<pre>";
	print_r($arr_content);
	echo "</pre>";
//  echo buildPageMainContent($arr_content[pageid], $arr_content[mode], false);
	$arr_content[show_printversion_link] = 0; // override default set in content_build_arrray
    echo buildPageMainContent($arr_content);
    echo printerFriendly($arr_content);  
    ?>
   </div>
   <div id="contentWrapperEnd"></div>
   <div id="rightWrapper">
	<?php echo customBoxes($arr_content); ?>	
    <?php 
	 if ($arr_content[lang]=="da") echo newsBox(1, $arr_content); 
	 if ($arr_content[lang]=="en") echo newsBox(2, $arr_content); 
	?>
    <?php if ($arr_content[lang]=="da") echo calendarBox(1, $arr_content); ?>	
	<?php echo searchBox($arr_content); ?>
	<?php echo stfBox($arr_content); ?>	
	<?php // echo newsletterBox($arr_content[pageid], 1); ?>	
   </div>
  </div>
  <div id="bottomWrapper">
   <img src="<?$arr_content[baseurl];?>/includes/images/footer.gif" 
   /><div 
   id="adresseBar">
    ExampleSite <span class="footerbullet">&nbsp;&curren;&nbsp;</span> Exampleroad 1 <span class="footerbullet">&nbsp;&curren;&nbsp;</span> 1000 Exampletown <span class="footerbullet">&nbsp;&curren;&nbsp;</span> Tlf. +45 11 22 33 44  <span class="footerbullet">&nbsp;&curren;&nbsp;</span> Fax +45 11 22 33 44 <span class="footerbullet">&nbsp;&curren;&nbsp;</span> <a class="footer" href="mailto:post@instans.dk">post@instans.dk</a>   
   </div>
  </div>
 </div> 
</body>
</html>
