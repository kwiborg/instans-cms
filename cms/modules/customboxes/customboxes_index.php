 <h1>Brugerdefinerede bokse</h1>
 <div class="broedtekst">
 </div>

<?php if ($dothis=="") { ?>
 <?php if (!$_SESSION["CMS_USER"]) { header("location: ../../login.php"); } // rydOp("CUSTOM_BOXES") 
 ?>
 <form id="defaultForm" method="post" action="">
  <input type="hidden" name="dothis" value="" />
  <input type="hidden" name="det_nye_id" value="<?php echo $id ?>" />
  <div class="feltblok_header">Følgende bokse er oprettet</div>
  <div class="feltblok_wrapper">
   <?php
    $html = "
	 <table class='oversigt'>   
	  <tr class='trtop'>
       <td class='kolonnetitel'>Titel</td>
       <td class='kolonnetitel'>Oprettet</td>
       <td class='kolonnetitel'>Oprettet af</td>
       <td class='kolonnetitel'>Funktioner</td>
      </tr>
	";
    $sql = "select * from CUSTOM_BOXES where SITE_ID='$_SESSION[SELECTED_SITE]' order by CREATED_DATE desc, ID desc";
	$result = mysql_db_query($dbname, $sql);
	while ($row = mysql_fetch_array($result)) {
	 $html .= "
	  <tr id='boxrow_$row[ID]' onmouseover='IEColorShift(this.id)' onmouseout='IEColorUnShift(this.id, 0)'>
	   <td>$row[TITLE]</td>
	   <td>" . returnNiceDateTime($row[CHANGED_DATE], 1) . "</td>
	   <td>" . returnAuthorName($row[AUTHOR_ID],1) . "</td>
	   <td>
	    <input type='button' class='lilleknap' value='Rediger' onclick='location=\"index.php?content_identifier=customboxes&dothis=rediger&trin=1&id=$row[ID]\"' />
	    <input type='button' class='lilleknap' value='Slet' onclick='if (confirm(\"Vil du slette denne boks?\")) location=\"index.php?content_identifier=customboxes&dothis=sletbox&id=$row[ID]\"' />
	   </td>
	  </tr>
	 ";
	}
	if(mysql_num_rows($result)==0) $html.="<tr><td colspan='4'>Ingen bokse oprettet.</td></tr>";
	$html .= "</table>";
	echo $html;
   ?>
  </div>
  <div class="knapbar">
   <input type="button" value="Opret ny boks" onclick="location='index.php?content_identifier=customboxes&dothis=opret&trin=1'" />
  </div>
 </form>
<?php
 }
?>

<?php if (($dothis=="opret" || $dothis=="rediger") && $trin=="1") { ?>
 <form id="defaultForm" method="post" action="index.php?content_identifier=customboxes&trin=2&dothis=opret">
  <input type="hidden" name="dothis" value="" />
  <input type="hidden" name="mode" value="<?php echo $dothis ?>" />
  <input type="hidden" name="currenttrin" value="<?php echo $trin ?>" />
  <input type="hidden" name="det_nye_id" value="<?php echo $id ?>" />
  <div class="feltblok_header">Trin 1: Angiv titel og type for boks</div>
  <div class="feltblok_wrapper">
   <h2>Boksens titel (internt i CMS):</h2>
   <input type="text" name="title" / class="inputfelt" value="<?php echo $datarow[TITLE] ?>"/>
   <h2>Boksens type:</h2>
   <input type="hidden" name="boxtype_res" value="<?php echo $datarow[TYPE] ?>"/>
   <!--
   <input type="radio" name="boxtype" onclick="document.getElementById('farver').style.display='block'; document.getElementById('set2').style.display='none'; document.getElementById('set1').style.display='block'" />&nbsp;Fritekst-boks (fx til citatklip eller lignende)<br/>
   <input type="radio" name="boxtype" onclick="document.getElementById('farver').style.display='block'; document.getElementById('set1').style.display='none'; document.getElementById('set2').style.display='block'" />&nbsp;Boks med links til andre sider  
   -->
   <input type="radio" name="boxtype" onclick="setResValue('boxtype', 1)" />&nbsp;Fritekst-boks (fx til citatklip eller lignende)<br/>
   <input type="radio" name="boxtype" onclick="setResValue('boxtype', 2)" />&nbsp;Boks med links til andre sider  
  </div>
  <div class="knapbar">
   <input type="button" value="Afbryd" onclick="location='index.php?content_identifier=customboxes'" />
   <input type="button" value="Næste trin" onclick="verify(1,0)" />
  </div>
 </form>
 <script type="text/javascript">
 <?php
  if ($dothis == "rediger") echo "setRadioCheckedMark('boxtype', $datarow[TYPE], 1);";
 ?>
 </script>
<?php
 }
?>

<?php if (($dothis=="opret" || $dothis=="rediger") && $trin=="2") { ?>
 <form id="defaultForm" method="post" action="">
  <input type="hidden" name="dothis" value="" />
  <input type="hidden" name="page_to_add" value="<?php echo $page_to_add ?>" />
  <input type="hidden" name="rel_id" value="<?php echo $rel_id ?>" />
  <input type="hidden" name="boxtype" value="<?php echo $boxtype ?>" />
  <input type="hidden" name="det_nye_id" value="<?php echo $id ?>" />
  <input type="hidden" name="mode" value="<?php echo $dothis ?>" />
  <div class="feltblok_header">Trin 2: Angiv boksens indhold</div>
  <div class="feltblok_wrapper">
  <?php if ($boxtype==1) { ?>
    <h2>Boksens overskrift:</h2>
    <input type="text" name="heading" class="inputfelt" value="<?php echo $datarow[HEADING] ?>"/><br/>   
    <h2>Boksens indhold:</h2>
    <!--<textarea name="content" class="inputfelt" style="height:100px"><?php echo $datarow[CONTENT] ?></textarea>   -->

              <?php
                $oFCKeditor = new FCKeditor('content') ;
                $oFCKeditor->BasePath = $fckEditorPath . "/";
                $oFCKeditor->ToolbarSet    = "CMS_Default";
                $oFCKeditor->Height    = "400";
                $oFCKeditor->Value    = $datarow["CONTENT"];
                $oFCKeditor->Config['CustomConfigurationsPath']    = $fckEditorCustomConfigPath . "/cms_fckconfig.js";
                $oFCKeditor->Create() ;
              ?>

              
  <?php } ?>
  <?php if ($boxtype==2) { ?>	
    <h2>Boksens overskrift:</h2>
    <input type="text" name="heading" class="inputfelt" value="<?php echo $datarow[HEADING] ?>" /><br/>   
    <h2>Sider, der linkes til:</h2>
	<select name="linkselector" class="inputfelt_kort">
     <?php 
	  echo buildPagesDropdown("",0,0);
	 ?>
	</select>&nbsp;
	<input type="button" class="lilletekst_knap" value="Tilføj" onclick="addRelatedForBoxes(this.form.linkselector.value, <?php echo $id ?>)">
	<div style="padding:10px; margin-top:10px; border:1px solid #aaa; background-color:#bbb">
	 <?php 
	  echo relatedListForBoxes($id);
	 ?>
	</div>
   <?php } ?>
   <div id="farver">
    <h2>Farver:</h2>
    <table>
	 <tr>
	  <td>Overskrift baggrund:</td>
	  <td>
	   <input type="hidden" name="now_picking" />
	   <input type="text" size="7" class="inputfelt_kort" name="heading_bgcol" value="<?php echo $datarow[HEADING_BGCOL] ?>"/>
	   <input type="button" name="cpicker" value="Vælg farve" class="lilleknap" onclick="draw(0, 'heading_bgcol')" />
	  </td>
	  <td rowspan="4">
	   <div id="colorpicker" style="width:100px;height:130px; margin-left:10px;"></div>
      </td>
	 </tr>
	 <tr>
	  <td>Overskrift tekst:</td>
	  <td>
	   <input type="text" size="7" class="inputfelt_kort" name="heading_textcol" value="<?php echo $datarow[HEADING_TEXTCOL] ?>"/>
	   <input type="button" name="cpicker" value="Vælg farve" class="lilleknap" onclick="draw(0, 'heading_textcol')" />
	  </td>
	 </tr>
	 <tr>
	  <td>Indhold baggrund:</td>
	  <td>
	   <input type="text" size="7" class="inputfelt_kort" name="content_bgcol" value="<?php echo $datarow[CONTENT_BGCOL] ?>"/>
	   <input type="button" name="cpicker" value="Vælg farve" class="lilleknap" onclick="draw(0, 'content_bgcol')" />
	  </td>
	 </tr>
	 <tr>
	  <td>Indhold tekst:</td>
	  <td>
	   <input type="text" size="7" class="inputfelt_kort" name="content_textcol" value="<?php echo $datarow[CONTENT_TEXTCOL] ?>"/>
	   <input type="button" name="cpicker" value="Vælg farve" class="lilleknap" onclick="draw(0, 'content_textcol')" />
	  </td>
	 </tr>
    </table>   
   </div>
  </div>
  <div class="knapbar">
   <input type="button" value="Afbryd" onclick="location='index.php?content_identifier=customboxes'" />
   <input type="button" value="Gem og afslut" onclick="verify(2, <?php echo $boxtype ?>)" />
  </div>
 </form>
<?php
 }
?>

