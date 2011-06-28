<?php
if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");

function ________oversigt() {}
if ($dothis == "oversigt" || !$dothis) {

if ($_GET["newsfeedid"] == "") {
	$filter_newsfeedid = get_default_newsfeed();
	$_GET["newsfeedid"] = $filter_newsfeedid;
}


?>
<?php
// rydOp("NEWS"); 
?>
<!-- ØVRE TEKST -->
<h1>
 Nyheder: Oversigt
</h1>

<!-- /// -->
<!-- FORM -->
<form id="defaultForm" method="GET" action="">
<input type="hidden" name="content_identifier" value="news" />
<input type="hidden" name="newsfeedid" value="<?php echo $filter_newsfeedid; ?>" />
<input type="hidden" name="dothis" value="" />
<input type="hidden" id="filter_display" name="filter_display" value="<?php echo $filter_display ?>" />
<div class="feltblok_header">
 Nyheder i arkivet <?php echo newsfeedSelector($filter_newsfeedid); ?>&nbsp;
 <div style="display: inline;"><a href="#" onclick="filterOnOff()" class="whitelink">Vis/skjul filter</a></div>
</div>
<div class="knapbar">
	<div class="pagination">
<?php
	if($_GET["offset"] != "" && $_GET["offset"] > 0) {
		$nextoffset = $_GET["offset"]+$newsarchive_newsPerPage_cms;
		$prevoffset = $_GET["offset"]-$newsarchive_newsPerPage_cms;
		if ($prevoffset<0) {
			$prevoffset = 0;
		}
		echo "<span><a href='index.php?content_identifier=news&newsfeedid=$filter_newsfeedid&offset=$nextoffset'>Vis ældre nyheder</a> | <a href='index.php?content_identifier=news&newsfeedid=$filter_newsfeedid&offset=$prevoffset'>Vis nyere nyheder</a></span>";
	}else{
		$nextoffset = $_GET["offset"]+$newsarchive_newsPerPage_cms;
		echo "<span><a href='index.php?content_identifier=news&newsfeedid=$filter_newsfeedid&offset=$nextoffset'>Vis ældre nyheder</a></span>";
	}
?>
	</div>
	<input type="button" value="Opret ny nyhed" onclick="opretNy()">
</div>
<br/>
<div class="feltblok_wrapper_hidden" id="filter">
<table class="filter">
<tr>
 <td>
  <h2>Vis kun nyheder fra dette tidspunkt:</h2>
  <select id="filter_time" name="filter_time" class="standard_select">
   <option value="ALL_TIMES">Alle tidspunkter</option>
   <option value="1">Redigeret inden for 1 dag</option>
   <option value="7">Redigeret inden for 1 uge</option>
   <option value="30">Redigeret inden for 1 måned</option>
   <option value="365">Redigeret inden for 1 år</option>
  </select>
 </td>
 </tr>
 <tr>
  <td colspan="3" class="submitbar">
   <input type="submit" value="Anvend filter" class="lilletekst_knap"> 
  </td>
 </tr>
</table>
<script type="text/javascript">
 <?php if ($filter_author && $filter_time) echo "indstilFilter('$filter_author', '$filter_time', '');"; ?>
 filterOnOff(<?php echo $filter_display ?>);
</script>
</div>
<div class="feltblok_wrapper">
 <?php
  if (!$sortby || !$sortdir)
  {
   $sortby = "NEWS_DATE";
   $sortdir = "DESC";
  }
	$returned = nyhedsOversigt($sortby, $sortdir, $filter_newsfeedid, $filter_author, $filter_time, $_GET["offset"], $_GET["count"]);
  echo $returned[0]; // HTML
 ?>
 <p class="feltkommentar">
 Tip: Du kan klikke på kolonne-navnene øverst i listen for at sortere listen efter den ønskede kolonne.
 </p>
</div>

<div class="knapbar">
 <input type="button" value="Opret ny nyhed" onclick="opretNy()">
</div>
</form>
<script type="text/javascript">
<?php
 echo $returned[1]; // SCRIPT
 }
?>
</script>

<?php
function ________opret_rediger() {}
 if ($dothis == "opret" || $dothis == "rediger") {
	if ($filter_newsfeedid == "") {
		$filter_newsfeedid = $pagedata[NEWSFEED_ID];
	}
?>
<script type="text/javascript" src="/cms/scripts/date-picker/js/datepicker.js"></script>
<link href="/cms/scripts/date-picker/css/datepicker.css" rel="stylesheet" type="text/css" />
<h1>
	<?php korrektVerbum($_GET[dothis]); ?> nyhed i arkivet "<?php echo returnNewsfeedName($filter_newsfeedid); ?>"
</h1>
<div class="broedtekst">
<!--Nedenstående redigeres og tilføjes indhold til nyheden. I sammenhæng hermed må vælges om nyheden skal være tidsbegrænset m.v.-->
</div><br />
<form id="defaultForm" method="post" action="">
	<ul id="tablist">
		<li><a href="#" class="current" onClick="return expandcontent('sc1', this)">Indhold</a></li>
		<li><a href="#" onClick="return expandcontent('sc2', this)">Visning</a></li>
		<li><a href="#" onClick="return expandcontent('sc3', this)">Rettigheder</a></li>
		<li><a href="#" onClick="return expandcontent('sc4', this)">Søgeoptimering</a></li>
	</ul>

	<input type="hidden" name="dothis" value="" />
	<input type="hidden" name="newsfeedid" value="<?php echo $filter_newsfeedid; ?>" />
	<input type="hidden" name="det_nye_id" value="<?php echo $id ?>" />
	<input type="hidden" name="mode" value="<?php echo $dothis ?>" />
	<input type='hidden' name='imageid' id='imageid' value='<? echo $imageid ?>' />
	<input type='hidden' name='image_url' id='image_url' value='<? echo $image_url ?>' />
	<div id="tabcontentcontainer">
		<div id="sc1" class="tabcontent">
			<h2>Kladde eller færdig?</h2>
			<input type="hidden" name="published_res" value="<?php echo $pagedata["PUBLISHED"] ?>">
			<input type="radio" name="published" onclick='setResValue(this.name, 0)'>&nbsp;Denne nyhed <em>er en kladde</em> og derfor ikke publiceret<br />
			<input type="radio" name="published" onclick='setResValue(this.name, 1)'>&nbsp;Denne nyhed <em>er færdigredigeret</em> og publiceret<br/>
				<?php
				if (returnLanguageCount() > 1) {
				?>
					<h2>Vælg sprog for denne nyhed:</h2>
					<?php
					if ($pagedata) {
						$current_language = $pagedata["LANGUAGE"];
					} else {
						$current_language = returnNewsfeedLanguage($_GET[newsfeedid]);
					}					
					echo buildLanguageDropdown($current_language, false);
				} else {
					?>
					<input type="hidden" id="languageselector" name="languageselector" value="<?php echo $current_language; ?>" />
					<?php
				}
			   ?>
			  <h2>Overskrift</h2>
			  <input type="text" name="heading" class="inputfelt" value="<?php echo $pagedata["HEADING"] ?>" />
			  <h2>Nyhedsdato</h2>
			  <input type="text" name="news_date" maxlength="10" class="inputfelt_kort format-d-m-y divider-dash highlight-days-67" onblur="" value="<?php if ($pagedata) echo reverseDate($pagedata["NEWS_DATE"]); else echo date("d-m-Y"); ?>" />
			  <h2>Resumé (ikke påkrævet)</h2>
			  <textarea name="subheading" cols="70" rows="5"><?php echo $pagedata["SUBHEADING"] ?></textarea>
			  <h2>Brødtekst - nyhedens øvrige indhold</h2>
			  <?php
				$oFCKeditor = new FCKeditor('Indhold') ;
				$oFCKeditor->BasePath = $fckEditorPath . "/";
				$oFCKeditor->ToolbarSet	= "CMS_Default";
				$oFCKeditor->Height	= "400";
				$oFCKeditor->Value	= $pagedata["CONTENT"];
				$oFCKeditor->Config['CustomConfigurationsPath']	= $fckEditorCustomConfigPath . "/cms_fckconfig.js";
				$oFCKeditor->Create() ;
			  ?>
		</div>
		<div id="sc2" class="tabcontent">
			  <h2>Forsideindstilling</h2>
			  <input type="checkbox" name="frontpage_status" <?php if ($pagedata["FRONTPAGE_STATUS"]==1) echo "checked" ?>>&nbsp;Vis nyhed i nyhedsboksen på forsiden<br/>
			  <h2>Tidsbegrænset visning</h2>
			  <input type="radio" name="timelimit" onclick="enableLimit(0)" <?php if (!$pagedata) echo "checked "; if ($pagedata[LIMITED]==0) echo "checked" ?>>&nbsp;Nyheden vises altid<br>
			  <input type="radio" name="timelimit" onclick	="enableLimit(1)" <?php if ($pagedata[LIMITED]==1) echo "checked" ?>>&nbsp;Vis kun nyheden fra 
			  <input type="text" name="limit_start" class="inputfelt_kort" value="<?php if ($pagedata[LIMITED]==1) echo reverseDate($pagedata["LIMIT_START"]) ?>"> til 
			  <input type="text" name="limit_end" class="inputfelt_kort" value="<?php if ($pagedata[LIMITED]==1) echo reverseDate($pagedata["LIMIT_END"]) ?>"> 
			  <input type="hidden" name="limit_res" class="inputfelt_kort" value="<?php echo ($pagedata ? $pagedata["LIMITED"] : "0") ?>">
			  <?php 
			  /*
			  <h2>Global nyhed?</h2>
			  <input type="checkbox" name="global_status" <?php if ($pagedata["GLOBAL_STATUS"]==1) echo "checked" ?>>&nbsp;Vis nyheden i alle nyhedsarkiver på alle sites<br/>
			  */
			$html .= "<h2>Benyt nyhedsbillede?</h2>";
			if ($imageid == "") {
				$use_image_checked="N";
				$disabled_2 = "disabled";
			} else {
				$use_image_checked="Y";
				$disabled_2 = "";
			}		
			$html .= "<table><tr><td valign='top'>";
			$html .= createCheckbox("Ja tak", "use_image", "Y", "$use_image_checked", "useImage(this);", $image_disabled);
			$html .= "&nbsp;&nbsp;<input type='button' class='lilleknap' name='selectImageButton' id='selectImageButton' value='Vælg' $disabled_2 onclick='selectImage($folder_id);' />";
			$html .= "</td><td>";
		
			$thumburl = explode("/",$image_url);
			$lastpart = array_pop($thumburl);
			$thumburl[] = "thumbs";
			$thumburl[] = $lastpart;
			$thumburl = implode("/", $thumburl); 
			 
			$html .= "&nbsp;&nbsp;\n<img id='imgthumb' src='$thumburl' border='1'";
			if ($image_url == "") {
				$html .= " style='display:none;'";
			}
			$html .= "/>";
			$html .= "</td></tr></table>";
			// Div for selecting images
			$html .= "<div id='selectImageDiv' style='
				display: none;
				width: 725px; 
				height: auto; 
				border: 1px solid #999; 
				background-color: #FFF;'></div>";
			 echo $html; 
			  ?>


		</div>
		<div id="sc3" class="tabcontent">
			  <h2>Beskyttelse</h2>
<?php
			echo datapermission_set("DATA_CMS_NEWSITEM_ACCESS", "NEWS", $_GET["id"]);

/*
			  <input type="hidden" name="protection_selector_res" value="">
			  <input type="radio" name="protection_selector" class="" / onclick="setResValue(this.name, 0)">&nbsp;Denne nyhed kan redigeres af alle<br />
			  <input type="radio" name="protection_selector" class="" / onclick="setResValue(this.name, 1)">&nbsp;Denne nyhed kan kun redigeres af mig
*/
?>
		</div>
		<div id="sc4" class="tabcontent">
			<h2>
				<div style='float:left;'>Meningsfuld side-adresse:</div>
				<div id='ajaxloader_rewrite'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></div>
			</h2>
			<input disabled type="text" id="rewrite_keyword" name="rewrite_keyword" class="inputfelt" value="<?=($pagedata ? return_rewrite_keyword("", $pagedata["ID"], "NEWS", $_SESSION[SELECTED_SITE]) : "")?>" onblur="keyword_onblur(this.form.heading.value, this.value, this.form.det_nye_id.value, 'NEWS', <?=$_SESSION[SELECTED_SITE]?>)" />
			&nbsp;
			<input type="button" value="Ret"  class="inputfelt_kort" onclick="edit_keyword()" />
			<input type="button" value="Foreslå"  class="inputfelt_kort" onclick="if (edit_keyword()) suggest_rewrite_keyword(this.form.heading.value, this.form.det_nye_id.value, 'NEWS', <?=$_SESSION[SELECTED_SITE]?>)" />
		</div>
		<div class="knapbar">
			<input type="button" value="Afbryd" onclick="location='index.php?content_identifier=news&newsfeedid=<?php echo $filter_newsfeedid ?>'" />
			<input type="button" value="Gem" onclick="verify()" />
		</div>
	</div>
<script type="text/javascript">
	<?php if (isset($pagedata)) { ?> 
 setRadioCheckedMark("published", <?php echo $pagedata["PUBLISHED"] ?>, 0);
// setRadioCheckedMark("protection_selector", <?php echo $pagedata["LOCKED_BY_USER"] ?>, 0);
	<?php } else { ?>
 setRadioCheckedMark("published", 0, 0);
// setRadioCheckedMark("protection_selector", 0, 0);
	<?php } ?>
 document.forms[0].languageselector.disabled  = false;
</script>
<?php
 }
?>