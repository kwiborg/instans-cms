<?php
if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");

if (is_numeric($_GET["filter_calendar"])) {
	if (!check_data_permission("DATA_CMS_CALENDAR_ACCESS", "CALENDARS", $_GET["filter_calendar"], "", $_SESSION["CMS_USER"]["USER_ID"])) {
		echo "<p>Du har ikke adgang til denne kalender!</p>";
		exit;
	}
}

function ________oversigt() {}
 if ($dothis == "oversigt" || !$dothis) {

if ($_GET["filter_calendar"] == "") {
	$filter_calendar = get_default_calendar();
	$_GET["filter_calendar"] = $filter_calendar;
}



?>
<script type="text/javascript" src="/cms/scripts/date-picker/js/datepicker.js"></script>
<link href="/cms/scripts/date-picker/css/datepicker.css" rel="stylesheet" type="text/css" />
<!-- ØVRE TEKST -->
<h1>
 Kalender: Oversigt
</h1>
<!-- FORM -->
<form id="defaultForm" method="get" action="">
<input type="hidden" name="content_identifier" value="events" />
<input type="hidden" name="dothis" value="" />
<input type="hidden" id="filter_display" name="filter_display" value="<?php echo $filter_display ?>" />
<?php
	$cal_select = calendarSelector($filter_calendar);
	if ($cal_select == false) {
		echo "<p>Du har ikke adgang til nogen kalendere på dette site!</p>";
		echo "</form>";
		echo "</body>";
		exit;
	}

?>
<div class="feltblok_header">
Arrangementer i kalenderen <?php echo $cal_select; ?>
 <div style="display: inline;"><a href="#" onclick="filterOnOff()" class="whitelink">Vis/skjul filter</a></div>
</div>
<div class="feltblok_wrapper_hidden" id="filter">
<table class="filter">
<tr>
 <td>
  <h2>Vis kun arrangementer med startdato i dette interval:</h2>
	Fra: <input type="text" id="filter_startdate_from" name="filter_startdate_from" maxlength="10" class="inputfelt_kort format-d-m-y divider-dash highlight-days-67" value="<?php echo $_GET["filter_startdate_from"]; ?>" /> 
	Til: <input type="text" id="filter_startdate_to" name="filter_startdate_to" maxlength="10" class="inputfelt_kort format-d-m-y divider-dash highlight-days-67" value="<?php echo $_GET["filter_startdate_to"]; ?>" />  
 </td>
 <td>
  <h2>Vis kun arrangementer fra:</h2>
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
  <td colspan="2" class="submitbar">
   <input type="submit" value="Anvend filter" class="lilletekst_knap filterknap_apply"> 
  </td>
 </tr>
</table>
<script type="text/javascript">
 <?php if ($filter_author && $filter_time) echo "indstilFilter('$filter_author', '$filter_time', '$filter_calendar');"; ?>
 filterOnOff(<?php echo $filter_display ?>);
</script>
</div>
<div class="knapbar">
	<div class="pagination">
<?php
	if($_GET["offset"] != "" && $_GET["offset"] > 0) {
		$nextoffset = $_GET["offset"]+$calendar_eventsPerPage_cms;
		$prevoffset = $_GET["offset"]-$calendar_eventsPerPage_cms;
		if ($prevoffset<0) {
			$prevoffset = 0;
		}
		echo "<span><a href='index.php?content_identifier=events&filter_calendar=$filter_calendar&filter_startdate_from=".$_GET['filter_startdate_from']."&filter_startdate_to=".$_GET['filter_startdate_to']."&offset=$nextoffset'>Vis ældre begivenheder</a> | <a href='index.php?content_identifier=events&filter_calendar=$filter_calendar&filter_startdate_from=".$_GET['filter_startdate_from']."&filter_startdate_to=".$_GET['filter_startdate_to']."&offset=$prevoffset'>Vis nyere begivenheder</a></span>";
	}else{
		$nextoffset = $_GET["offset"]+$calendar_eventsPerPage_cms;
		echo "<span><a href='index.php?content_identifier=events&filter_calendar=$filter_calendar&filter_startdate_from=".$_GET['filter_startdate_from']."&filter_startdate_to=".$_GET['filter_startdate_to']."&offset=$nextoffset'>Vis ældre begivenheder</a></span>";
	}
?>
	</div>
	<input type="button" value="Opret nyt arrangement" onclick="checkCalendarSelected();">
</div>
<br/>
<div class="feltblok_wrapper">
 <?php
  if (!$sortby || !$sortdir)
  {
   $sortby = "STARTDATE";
   $sortdir = "DESC";
  }
  $returned = kalenderOversigt($sortby, $sortdir, $filter_author, $filter_time, $filter_calendar, $_GET["filter_startdate_from"], $_GET["filter_startdate_to"], $_GET["offset"]);
  echo $returned[0]; // HTML
 ?>
 <p class="feltkommentar">
 Tip: Du kan klikke på kolonne-navnene øverst i listen for at sortere listen efter den ønskede kolonne.
 </p>
</div>

<div class="knapbar">
 <input type="button" value="Opret nyt arrangement" onclick="checkCalendarSelected();">
</div>
<!-- /// -->
</form>
<?php
 echo $returned[1]; // SCRIPT
 }
?>

<?php
function ________opret_rediger() {}
 if ($dothis == "opret" || $dothis == "rediger") {
	if ($filter_calendar == "") {
		$filter_calendar = $pagedata[CALENDAR_ID];
	}
?>

<script type="text/javascript" src="/cms/scripts/date-picker/js/datepicker.js"></script>
<link href="/cms/scripts/date-picker/css/datepicker.css" rel="stylesheet" type="text/css" />

<!-- ØVRE TEKST -->
<h1>
 <?php korrektVerbum($_GET[dothis]); ?> arrangement i kalenderen "<?php echo returnCalendarName($filter_calendar); ?>"
</h1>
<div class="broedtekst">
Herunder kan du redigere arrangementets indhold. I sammenhæng hermed kan du angive arrangementets varighed m.v.
</div>
<!-- /// -->
<!-- FORM -->
<form id="defaultForm" method="post" action=""><br />
	<ul id="tablist">
		<li><a href="#" class="current" onClick="return expandcontent('sc1', this)">Indhold</a></li>
		<li><a href="#" onClick="return expandcontent('sc2', this)">Tidspunkt og varighed</a></li>
		<li><a href="#" onClick="return expandcontent('sc3', this)">Visning</a></li>
		<li><a href="#" onClick="return expandcontent('sc4', this)">Rettigheder</a></li>
		<li><a href="#" onClick="return expandcontent('sc5', this)">Søgeoptimering</a></li>
	</ul>
	<input type="hidden" name="dothis" value="" />
	<input type="hidden" name="det_nye_id" value="<?php echo $id ?>" />
	<input type="hidden" name="mode" value="<?php echo $dothis ?>" />
	<div id="tabcontentcontainer">
		<div id="sc1" class="tabcontent">
			  <h2>Kladde eller færdig?</h2>
			  <input type="hidden" name="published_res" value="<?php echo $pagedata["PUBLISHED"] ?>">
			  <input type="radio" name="published" onclick='setResValue(this.name, 0)'>&nbsp;Dette arrangement <em>er en kladde</em> og derfor ikke publiceret<br/>
			  <input type="radio" name="published" onclick='setResValue(this.name, 1)'>&nbsp;Denne arrangement <em>er færdigredigeret</em> og publiceret<br/>
				<?php
				if (returnLanguageCount() > 1) {
				?>
					<h2>Vælg sprog for dette arrangement</h2>
					<?php
					if ($pagedata) {
						$current_language = $pagedata["LANGUAGE"];
					} else {
						$current_language = returnCalendarLanguage($_GET[filter_calendar]);
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
			  <h2>Resumé (ikke påkrævet)</h2>
			  <textarea name="subheading" cols="70" rows="5"><?php echo $pagedata["SUBHEADING"] ?></textarea>
			  <h2>Brødtekst - arrangement-tekstens øvrige indhold</h2>
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
			  <h2>Arrangementets varighed</h2>
			  <input type="hidden" name="duration_selector_res" value="<?php echo $pagedata["DURATION"] ?>"> 
			  <input type="radio" name="duration_selector" class="" onclick='setResValue(this.name, 0); skiftDuration(0)' />&nbsp;Arrangementet varer kun én dag<br />
			  <input type="radio" name="duration_selector" class="" onclick='setResValue(this.name, 1); skiftDuration(1)' />&nbsp;Arrangementet løber over flere dage
			  <h2>Startdato og evt. slutdato</h2>
			  Den&nbsp;&nbsp;
			  <input type="text" id="startdate" name="startdate" maxlength="10" class="inputfelt_kort format-d-m-y divider-dash highlight-days-67" value="<?php
				if ($pagedata) {
					echo reverseDate($pagedata["STARTDATE"]);
				} else {
					echo reverseDate();
				}
				?>">
			  &nbsp;&nbsp;til den&nbsp;&nbsp;
			  <input type="text" id="enddate" name="enddate" maxlength="10" class="inputfelt_kort format-d-m-y divider-dash highlight-days-67" value="<?php if ($pagedata) echo reverseDate($pagedata["ENDDATE"]) == ("00-00-0000") ? "" : reverseDate($pagedata["ENDDATE"]); else echo "" ?>">
			  <p class="feltkommentar">
			   Datoer skal skrives i formatet DD-MM-ÅÅÅÅ, f.eks. <?php echo date("d-m-Y"); ?>.
			  </p>
			  <h2>Tidsrum / klokkeslet</h2>
			  <input type="text" name="timeofday" class="inputfelt" value="<?php echo $pagedata["TIMEOFDAY"] ?>">
			  <p class="feltkommentar">
			   Skriv i fri tekst, f.eks. "kl. 14-16" eller "Alle dage 9-15, dog torsdag 9-17", etc.
			  </p>
		</div>
		<div id="sc3" class="tabcontent">
			<input type='hidden' name='imageid' id='imageid' value='<? echo $imageid ?>' />
			<input type='hidden' name='image_url' id='image_url' value='<? echo $image_url ?>' />
			<input type="hidden" id="calendar_id_res" name="calendar_id_res" value="<?php echo $filter_calendar; ?>"/>
			<input type="checkbox" style="display:none;" name="global_status" <?php if ($pagedata["GLOBAL_STATUS"]==1) echo "checked" ?>>
			<h2>Fremhæv arrangement?</h2>
				<input type="checkbox" id="focusevent" name="focusevent" <?php if ($pagedata["FOCUSEVENT"]==1) echo "checked" ?>>&nbsp;Fremhæv dette arrangement 
			<?php
			$html = "<h2>Benyt oversigtsbillede?</h2><p>Vis dette billede udfor begivenheden i oversigter, der understøtter billedvisning</p>";
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
		<div id="sc4" class="tabcontent">
<?php
			echo datapermission_set("DATA_CMS_EVENT_ACCESS", "EVENTS", $pagedata["ID"]);
?>
		</div>
		<div id="sc5" class="tabcontent">
			<h2>
				<div style='float:left;'>Meningsfuld side-adresse:</div>
				<div id='ajaxloader_rewrite'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></div>
			</h2>
			<input disabled type="text" id="rewrite_keyword" name="rewrite_keyword" class="inputfelt" value="<?=($pagedata ? return_rewrite_keyword("", $pagedata["ID"], "EVENTS", $_SESSION[SELECTED_SITE]) : "")?>" onblur="keyword_onblur(this.form.heading.value, this.value, this.form.det_nye_id.value, 'EVENTS', <?=$_SESSION[SELECTED_SITE]?>)" />
			&nbsp;
			<input type="button" value="Ret"  class="inputfelt_kort" onclick="edit_keyword()" />
			<input type="button" value="Foreslå"  class="inputfelt_kort" onclick="if (edit_keyword()) suggest_rewrite_keyword(this.form.heading.value, this.form.det_nye_id.value, 'EVENTS', <?=$_SESSION[SELECTED_SITE]?>)" />
		</div>
		<div class="knapbar">
			<input type="button" value="Afbryd" onclick="location='index.php?content_identifier=events&menuid=<?php echo $menuid ?>&filter_calendar=<?php echo $filter_calendar; ?>'" />
			<input type="button" value="Gem" onclick="verify()" />
		</div>
	</div>
</form>
<script type="text/javascript">
<?php if ($pagedata) { ?> 
 setRadioCheckedMark("published", <?php echo $pagedata["PUBLISHED"] ?>, 0);
// setRadioCheckedMark("protection_selector", <?php echo $pagedata["LOCKED_BY_USER"] ?>, 0);
 setRadioCheckedMark("duration_selector", <?php echo $pagedata["DURATION"] ?>, 0);
 var orginal_Duration = <?=$pagedata["DURATION"]?>;
// skiftDuration(<?php echo $pagedata["DURATION"] ?>);
<?php } else { ?>
		// Set defaults
		setRadioCheckedMark("published", 0, 0);
//		setRadioCheckedMark("protection_selector", 0, 0);
		setRadioCheckedMark("duration_selector", 0, 0);
		var orginal_Duration = 0;
//		skiftDuration(0);
<?php } ?> 
 document.forms[0].languageselector.disabled  = false;
</script>
<?php
 }
?>