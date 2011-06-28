<?php
 global $fckEditorPath;
 if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
 // if (!$dothis) unset($_SESSION["OPEN_MENUS"]);
 if (!$_SESSION["OPEN_MENUS"]) $_SESSION["OPEN_MENUS"] = array("_START");
 if ($_GET["addtoopen"]) {
  if (!in_array($_GET["addtoopen"], $_SESSION["OPEN_MENUS"])) $_SESSION["OPEN_MENUS"][] = $_GET["addtoopen"];
  else {
   $k = array_search($_GET["addtoopen"], $_SESSION["OPEN_MENUS"]);
   unset($_SESSION["OPEN_MENUS"][$k]);
  }
 }

function _______________oversigt() {}
if ($_GET[dothis] == "oversigt" || !$_GET[dothis]) {
	checkPermission("CMS_PAGES", true);	
	if (is_numeric($_GET[menuid])) {
		if (!check_data_permission("DATA_CMS_MENU_ACCESS", "MENUS", $_GET[menuid], "", $_SESSION["CMS_USER"]["USER_ID"])) {
			echo "<p>Du har ikke adgang til denne menu!</p>";
			exit;
		}
	}
?>
 <h1>
  Sider: Oversigt
 </h1>
 <form id="defaultForm" method="post" action="">
  <input type="hidden" name="dothis" value="" />
  <input type="hidden" name="tempPageToMove" value="" />
  <input type="hidden" name="filter_display" value="<?php echo $filter_display ?>" />
  <?php
	if ($_GET[menuid] =="") {
/*
		$sql = "select MENU_ID from MENUS where SITE_ID in (0,'$_SESSION[SELECTED_SITE]') order by MENU_ID	 asc limit 1"; 
		$result = mysql_db_query($dbname, $sql) or die(mysql_error());
		$row = mysql_fetch_row($result);
		$menuid = $row[0];
*/
		$menuid = get_default_menu();
	}
  ?>
	<input type="hidden" name="menuid" value="<?php echo $menuid ?>" />
   <div class="feltblok_header">
    Sider i menuen <select name="menuselector" class="inputfelt" onchange="selectMenu()">
    <?php
	echo menuSelector();
    ?>
   </select>
   </div>
   <div class="feltblok_wrapper_hidden" id="filter">
    <table class="filter">
     <tr>
      <td>
       <h2>Vis kun sider af denne forfatter:</h2>
       <?php
        echo buildAuthorDropdown();
       ?>
      </td>
      <td>
       <h2>Vis kun sider fra dette tidspunkt:</h2>
       <select name="filter_time" class="standard_select">
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
       <input type="submit" value="Anvend filter" class="lilletekst_knap" />
     </td>
    </tr>
   </table>
   <script type="text/javascript">
    <?php if ($filter_author && $filter_time) echo "indstilFilter('$filter_author', '$filter_time');"; ?>
    filterOnOff(<?php echo $filter_display ?>);
   </script>
  </div>
  <div class="feltblok_wrapper">
   <?php
    if (!$sortby || !$sortdir) {
     $sortby = "ID";
     $sortdir = "DESC";
    }
	echo "<div id='oversigten'>";
    echo nySideOversigt($menuid, 0, 0, $filter_author, $filter_time);  
	 echo "</div>";
   ?>
  </div>
  <div class="knapbar">
   <input type="button" value="Opret ny side" onclick="location='index.php?content_identifier=pages&amp;dothis=opret&amp;menuid=<?php echo $menuid; ?>&amp;parentid=0'" />
  </div>
 </form>
 <script type="text/javascript">
<?php if ($menuid) echo "document.forms[0].menuselector.value='$menuid';"; ?>
</script>
<?php
 /*
  <script type="text/javascript">
   window.onload=function(){document.getElementById("oversigten").style.display="block"; size(); location.href='#sideanker_<?php echo $_GET[addtoopen] ?>'}
  </script> 
 */
?>
<?php
 }
?>

<?php
function _______________opret_rediger() {}

if ($dothis == "opret" || $dothis == "rediger") {
	if ($pagedata[TEMPLATE] != "") {
		$current_template = $pagedata[TEMPLATE];
	} else {
		$current_template = returnDefaultTemplateId($_SESSION[SELECTED_SITE]);
	}
?>
 <h1>
  <?php korrektVerbum($_GET[dothis]); 
  if (is_array($pagedata)) {
  	echo " side: ".returnFieldValue("PAGES", "BREADCRUMB", "ID", $pagedata["ID"]);
  } else {
  	echo " side";
  }
  ?>
 </h1>
<br/>
<form id="defaultForm" method="post" action="">
	<ul id="tablist">
		<li><a href="#" class="current" onclick="return expandcontent('sc1', this)">Indhold</a></li>
	<?php if (hasCustomfields("PAGES",$current_template)) { ?>
		<li><a href="#" onclick="return expandcontent('sc8', this)">Specialfelter</a></li>
	<? } ?>
		<li><a href="#" onclick="return expandcontent('sc2', this)">Menu</a></li>
		<li><a href="#" onclick="return expandcontent('sc3', this)">Rettigheder</a></li>
		<li><a href="#" onclick="return expandcontent('sc7', this)">Relateret indhold</a></li>
		<!--<li><a href="#" onclick="return expandcontent('sc9', this)">Kommentarer</a></li>-->
		<?php if ($pages_searchoptimizerOn) { ?>
		<li><a href="#" onclick="return expandcontent('sc4', this)">Søgeoptimering</a></li>
		<? } ?>
		<?php 
		if (moduleInstalled("bookmaker")) { ?>
		<li><a href="#" onclick="return expandcontent('sc5', this)">Bog</a></li>
		<? } ?>
		<?php if ($pages_AdvancedOn) { ?>
		<li><a href="#" onclick="return expandcontent('sc6', this)">Avanceret</a></li>
		<?php } ?>
	</ul>
	<input type="hidden" name="dothis" value="" />
	<?php 
	$sql = "select * from PAGES where LANGUAGE='$pagedata[LANGUAGE]' and SITE_ID='$_SESSION[SELECTED_SITE]' and IS_FRONTPAGE='1'";
	$result = mysql_db_query($dbname, $sql) or die(mysql_error());
	if (mysql_num_rows($result) > 0) {
		$row = mysql_fetch_array($result);	
		echo "<input type='hidden' name='current_sitelang_frontpage' value='" . menuPath($row[ID], false) . "' />";
	} else {
		echo "<input type='hidden' name='current_sitelang_frontpage' value='' />";
	} ?>   
	<input type="hidden" name="det_nye_id" value="<?php echo $id ?>" />
	<input type="hidden" name="current_position" value="<?php echo $pagedata[POSITION] ?>" />
	<input type="hidden" name="menuid" value="<?php echo $menuid ?>" />
	<input type="hidden" name="parentid" value="<?php echo $parentid ?>" />
	<input type="hidden" name="mode" value="<?php echo $dothis ?>" />

	<div id="tabcontentcontainer">
		<div id="sc1" class="tabcontent">
			<h2>Er siden en kladde eller publiceret?</h2>
				<input type="hidden" name="published_res" value="<?php echo $pagedata["PUBLISHED"] ?>" />
				<input type="radio" name="published" onclick='setResValue(this.name, 0)' />&nbsp;KLADDE - ikke publiceret på websitet<br/>
				<input type="radio" name="published" onclick='setResValue(this.name, 1)' />&nbsp;PUBLICERET på websitet<br/>
			   <?php
				if (returnLanguageCount() > 1) {
				?>
					<h2>Vælg sprog for denne side:</h2>
					<?php
					if ($pagedata) {
						$current_language = $pagedata["LANGUAGE"];
					} else {
						$current_language = returnMenuLanguage($_GET[menuid]);
					}					
					echo buildLanguageDropdown($current_language, false);
				} else {
					?>
					<input type="hidden" id="languageselector" name="languageselector" value="<?php echo returnMenuLanguage($_GET[menuid]); ?>" />
					<?php
				}
			   ?>
			<h2>Titel i menu</h2>
				<input type="text" id="breadcrumb_mirror" name="breadcrumb_mirror" onblur="sync_breadcrumb(this)" class="inputfelt" value="<?php echo $pagedata["BREADCRUMB"] ?>" /><br/><br/>	
			<h2>Overskrift</h2>
			<input type="text" name="heading" class="inputfelt" onblur="makeTitle(this.value)" value="<?php echo $pagedata["HEADING"] ?>" />
			<h2>Resumé (ikke påkrævet)</h2>
			<textarea name="subheading" cols="70" rows="5"><?php echo $pagedata["SUBHEADING"] ?></textarea>
			<div class="knapbar">
				<input type="button" value="Gem og Preview" onclick="savePreview()" />    
				<input type="button" value="Afbryd" onclick="location='index.php?content_identifier=pages&amp;dothis=oversigt&amp;menuid=<?php echo $menuid ?>'" />
				<input type="button" value="Gem" onclick="verify()" />
			</div>
			<h2>Brødtekst - sidens øvrige indhold</h2>
			<?php
			$oFCKeditor = new FCKeditor('Indhold') ;
			$oFCKeditor->BasePath = $fckEditorPath . "/";
			$oFCKeditor->ToolbarSet	= "CMS_Default";
			$oFCKeditor->Height	= "400";
			$oFCKeditor->Value	= $pagedata["CONTENT"];
			$oFCKeditor->Config['CustomConfigurationsPath']	= $fckEditorCustomConfigPath . "/cms_fckconfig.js";
			$oFCKeditor->Create() ;
			?>
			<div style="text-align:right">
				<input type="hidden" name="editorSize" value="small" />
				<input type="button" name="udvidknap" class="lilleknap" value="Større tekstfelt" onclick="udvidEditor()" />
			</div> 
		</div>
	<?php if (hasCustomfields("PAGES", $current_template)) { ?>
		<div id="sc8" class="tabcontent">
		<?
			// Show any custom fields here
			echo return_customfields_input("PAGES", $current_template);
		?>
		</div>
	<?php } ?>
		<div id="sc2" class="tabcontent">
			<h2>Titel i menu</h2>
				<input type="text" id="breadcrumb" name="breadcrumb" onblur="sync_breadcrumb(this)" class="inputfelt" value="<?php echo $pagedata["BREADCRUMB"] ?>" />
			<h2>Skjul siden i menu</h2>
				<input type="checkbox" name="no_display" <?php if ($pagedata["NO_DISPLAY"]==1) echo "checked"; ?> />&nbsp;Siden er skjult
			<h2>Forside</h2>
				<input type="checkbox" onchange="toggle_is_sitelang_frontpage();" id="is_sitelang_frontpage" name="is_sitelang_frontpage" <?php
				if ($pagedata["IS_FRONTPAGE"]==1) {
					echo "checked disabled";
				}
				echo " />&nbsp;Denne side er startside for <strong>";
				echo returnSiteName($_SESSION[SELECTED_SITE]) . "</strong> på&nbsp;&nbsp;".buildLanguageDropdown($pagedata["LANGUAGE"], true, "frontpage_languageselector");

				if ($pagedata["IS_FRONTPAGE"]==1) { ?>
					<div style="padding-left:24px">
						<p class="feltkommentar">TIP: Du kan ikke ændre denne sides startside-status, fordi sitet i så fald ikke har nogen startside. 
						Hvis du vil gøre en anden side til startside, skal du i stedet krydse feltet af under redigering af den pågældende side.</p>
					</div>
				<?php
				}
				?>
			<h2>Link</h2>
				<input type="checkbox" onchange="toggle_pointToPageSelector();" id="pointing_page" name="pointing_page" <?php if ($pagedata["POINTTOPAGE_ID"]!=0) echo " checked " ?>/>&nbsp;Denne side er et link som peger på&nbsp;&nbsp;
				<select id="pointToPageSelector" name="pointToPageSelector" class="inputselect" disabled><?php echo buildPagesDropdown("",0,0); ?></select><br />

				<input type="checkbox" onchange="toggle_pointtopage_url();" id="pointing_url" name="pointing_url" <?php 	if ($pagedata["POINTTOPAGE_URL"] != "") {
							echo " checked ";
						}
						?>/>
						Denne side er et link som peger på URL'en:<br />
				<div style="padding-left:24px">
					<input type="text" id="pointtopage_url" name="pointtopage_url" class="inputfelt" value="<?php echo $pagedata["POINTTOPAGE_URL"] ?>"<?php 	if ($pagedata["POINTTOPAGE_URL"] == "") {
								echo " disabled";
							}
					?> />
				</div>
				<input type="checkbox" name="popup" value="1" <?=($pagedata["POPUP"]==1 ? "checked" : "")?>/>&nbsp;Åben siden i et nyt browser-vindue
			<h2>Tomt menupunkt</h2>
				<input type="checkbox" id="is_menuplaceholder" name="is_menuplaceholder" <?php if ($pagedata["IS_MENUPLACEHOLDER"]!=0) echo " checked " ?> />&nbsp;Siden har underpunkter men ikke noget indhold
		</div>
		<div id="sc3" class="tabcontent">
<?php
			echo datapermission_set("DATA_CMS_PAGE_ACCESS", "PAGES", $_GET["id"]);
/*
			<h2>Beskyttelse</h2>
				<input type="hidden" name="protection_selector_res" value="" />
				<input type="radio" name="protection_selector" class="" onclick="setResValue(this.name, 0)" />&nbsp;Denne side kan redigeres af alle<br />
				<input type="radio" name="protection_selector" class="" onclick="setResValue(this.name, 1)" />&nbsp;Denne side kan kun redigeres af mig
*/
?>
			<h2>Siden er tilgængelig for medlemmer af disse grupper</h2>
				<input type="hidden" name="beskyttet_res" />
				<input type="radio" name="beskyttet" onclick='setResValue(this.name, 1); disableAllGroups(1)' />&nbsp;Alle besøgende på websitet<br />
				<input type="radio" name="beskyttet" onclick='setResValue(this.name, 2); disableAllGroups(2)' />&nbsp;Kun brugere fra valgte grupper:<br />
			<div style="margin-left:15px">
				<?php 
				gruppeOversigtShortPages(0,0,0,1,$id);
				echo "<script>\n
				boxes = new Array();\n
				$script;
				\n</script>";
				?>
			</div>
			<h2>Rettigheder for undersider</h2>
			<input type="checkbox" name="children_inherit_rights" id="children_inherit_rights" checked />
			&nbsp;Denne sides undersider skal også have ovenstående rettigheder			
		</div>
		<div id="sc4" class="tabcontent">
			<h2>
				<div style='float:left;'>Meningsfuld side-adresse:</div>
				<div id='ajaxloader_rewrite'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></div>
			</h2>
			<input disabled type="text" id="rewrite_keyword" name="rewrite_keyword" class="inputfelt" value="<?=($pagedata ? return_rewrite_keyword("", $pagedata["ID"], "PAGES", $_SESSION[SELECTED_SITE]) : "")?>" onblur="keyword_onblur(this.form.heading.value, this.value, this.form.det_nye_id.value, 'PAGES', <?=$_SESSION[SELECTED_SITE]?>)" />
			&nbsp;
			<input type="button" value="Ret"  class="inputfelt_kort" onclick="edit_keyword()" />
			<input type="button" value="Foreslå"  class="inputfelt_kort" onclick="if (edit_keyword()) suggest_rewrite_keyword(this.form.heading.value, this.form.det_nye_id.value, 'PAGES', <?=$_SESSION[SELECTED_SITE]?>)" />
			<h2>Side titler (<em>TITLE tag og META title</em>):</h2>
			<input type="hidden" id="usetitle_res" name="usetitle_res" value="<?php 
			if ($pagedata[META_SEOTITLE] == "") {
				$useTitleselected = returnGeneralSetting("META_TITLE_USEPAGESCOLUMN");
			} else {
				$useTitleselected = $pagedata[META_SEOTITLE];
			}
			echo $useTitleselected;
			if ($pagedata[META_SEOTITLE] == "BREADCRUMB" || $pagedata[META_SEOTITLE] == "HEADING") {
				$pagedata[META_SEOTITLE] = "";
			}
			?>" />
				<input type="radio" name="usetitle" class="" onclick="setuseTitleValue('BREADCRUMB')" <?php if ($useTitleselected == "BREADCRUMB") { echo "checked"; } ?> />&nbsp;Brug sidens navn i menuen som titel<br />
				<input type="radio" name="usetitle" class="" onclick="setuseTitleValue('HEADING')" <?php if ($useTitleselected == "HEADING") { echo "checked"; } ?> />&nbsp;Brug sidens overskrift som titel<br />
				<input type="radio" name="usetitle" class="" onclick="setuseTitleValue('CUSTOM')" <?php if ($useTitleselected != "BREADCRUMB" && $useTitleselected != "HEADING") { echo "checked"; } ?> />&nbsp;Brug denne titel:
				<input type="text" id="usetitle_customtitle" name="usetitle_customtitle" value="<?php echo $pagedata[META_SEOTITLE]; ?>" size="40" <?php if ($useTitleselected == "HEADING" || $useTitleselected == "BREADCRUMB") { echo "disabled"; } ?> />
			 <h2>Viderestilling (301 Redirect)</h2>
			 <input type="text" id="redirect_to_url" name="redirect_to_url" class="inputfelt" value="<?=$pagedata["REDIRECT_TO_URL"];?>" />
			 <p class="feltkommentar">Siden er flyttet permanent. Søgemaskiner og besøgende bliver automatisk sendt videre til den url du undtaster herover. Bemærk at du skal taste en komplet og gyldig url - f.eks. http://www.domain.dk/</p>
			 <h2>Beskrivelse til søgemaskiner (<em>META description</em>):</h2>
			 <textarea name="meta_description" cols="70" rows="5"><?php echo $pagedata["META_DESCRIPTION"] ?></textarea>
			 <p class="feltkommentar">Hvis du ikke udfylder feltet, anvender siden standardindstillingerne: "<?php echo returnGeneralSetting("META_DESCRIPTION"); ?>"</p>
			 <h2>Nøgleord til søgemaskiner (<em>META keywords</em>) - komma-separeret:</h2>
			 <textarea name="meta_keywords" cols="70" rows="5"><?php echo $pagedata["META_KEYWORDS"] ?></textarea>
			 <p class="feltkommentar">Hvis du ikke udfylder feltet, anvender siden standardindstillingerne: "<?php echo returnGeneralSetting("META_KEYWORDS"); ?>"</p>
		</div>
		<div id="sc5" class="tabcontent">
			<?php include_once($_SERVER[DOCUMENT_ROOT]."/cms/modules/bookmaker/bookmaker_selectbook.inc.php"); ?>
		</div>
		<?php
			$khtml .= "<div id='sc9' class='tabcontent'>
				<h2>Tillad kommentarer</h2>";
			$khtml .= createSelectYesNo("page_comments_allowed", $pagedata[COMMENTS_ALLOWED]);

			$khtml .= "<h2>
					<div style='float:left;'>Kommentarer til denne side</div>
					<div id='ajaxloader_comments'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></div>
				</h2>";
//			$khtml .= return_blogcomments($pagedata);
			$khtml .= "</div>";
			echo $khtml;
		?>


		<div id="sc6" class="tabcontent">
			<h2>Template</h2>
			Brug denne template til siden:
			<?php 
				echo buildTemplateDropdown($current_template, "templateselector");
			?>
			<h2>PHP-fil der inkluderes i toppen af siden (før browser-output)</h2>
				<input type="text" name="php_headerinclude_path" class="inputfelt" value="<?php echo $pagedata[PHP_HEADERINCLUDE_PATH] ?>" />
			<h2>PHP-fil der inkluderes <em>før</em> sidens indhold</h2>
				<input type="text" name="php_include_path" class="inputfelt" value="<?php echo $pagedata[PHP_INCLUDE_PATH] ?>" />
			<h2>PHP-fil der inkluderes <em>efter</em> sidens indhold</h2>
				<input type="text" name="php_includeafter_path" class="inputfelt" value="<?php echo $pagedata[PHP_INCLUDEAFTER_PATH] ?>" />
		</div>
<?php 
function ________relateret_indhold() {}
?>		<div id="sc7" class="tabcontent">
			<h2>
				<div style='float:left;'>Relaterede bokse</div>
				<div id='ajaxloader_relboxes'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></div>
			</h2>
			<p class="feltkommentar">Brug denne funktion til at slå faste bokse og brugerdefinerede bokse til/fra på siden. <em>Faste bokse</em> er f.eks. nyhedsboks, kalenderboks, eller søgeboks. <em>Brugerdefinerede bokse</em> kan være enhver link- eller fritekstbokst oprettet med boks-værktøjet.</p>
			<table class="oversigt">
				<tr>
					<td class="kolonnetitel">Faste bokse</td>
				</tr>
				<tr>
					<td>
						<?php 
						$bsql = "select * from BOX_SETTINGS where PAGE_ID='$_GET[id]'";
						$bresult = mysql_query($bsql) or die(mysql_error());
						$brow = mysql_fetch_array($bresult);
						?>
						<div id="attachedboxes">
							<input type="checkbox" id="show_news" name="show_news" <?php if ($brow[NEWS]==1) echo "checked" ?> onclick="attachBox(this);" />&nbsp;Vis nyhedsboks<br/>
							<input type="checkbox" id="show_events" name="show_events" <?php if ($brow[EVENTS]==1) echo "checked" ?> onclick="attachBox(this);" />&nbsp;Vis kalenderboks<br/>
							<input type="checkbox" id="show_search" name="show_search" <?php if ($brow[SEARCH]==1) echo "checked" ?> onclick="attachBox(this);" />&nbsp;Vis søgeboks<br/>
							<input type="checkbox" id="show_stf" name="show_stf" <?php if ($brow[STF]==1) echo "checked" ?> onclick="attachBox(this);" />&nbsp;Vis send-til-en-ven boks<br/>
							<input type="checkbox" id="show_newsletter" name="show_newsletter" <?php if ($brow[NEWSLETTER]==1) echo "checked" ?> onclick="attachBox(this);" />&nbsp;Vis nyhedsbrev-boks<br/>     
							<input type="checkbox" id="show_lastedited" name="show_lastedited" <?php if ($brow[LAST_EDITED]==1) echo "checked" ?> onclick="attachBox(this);" />&nbsp;Vis "sidst redigeret" i bunden af siden
							</div>
					</td>
				</tr>
			</table>
			<table class="oversigt">
				<tr>
					<td class="kolonnetitel">Brugerdefinerede bokse</td>
				</tr>
				<tr>
					<td>
						<div id="attachedboxescustom">
							<?php 
							$sql = "select CUSTOM_BOXES.ID, CUSTOM_BOXES.TITLE, RELATED_CONTENT.REL_ID
									from CUSTOM_BOXES
									left outer join RELATED_CONTENT
									on CUSTOM_BOXES.ID = RELATED_CONTENT.REL_ID
									and RELATED_CONTENT.SRC_TABEL = 'PAGES'
									and RELATED_CONTENT.SRC_ID = '$_GET[id]'
									and RELATED_CONTENT.REL_TABEL = 'CUSTOM_BOXES'
									where CUSTOM_BOXES.SITE_ID in (0,'$_SESSION[SELECTED_SITE]') and UNFINISHED = 0
								";
							$result = mysql_query($sql) or die(mysql_error());
							if (mysql_num_rows($result)>0) {
								while ($crow = mysql_fetch_array($result)){
									if ($crow[REL_ID]) $c = "checked";
									echo "<input type='checkbox' id='custombox_$crow[ID]' name='custombox_$crow[ID]' $c onclick='attachBox(this);' />&nbsp;$crow[TITLE]<br/>";
									$c="";
								}
							} else {
								echo "<p>Der er ikke oprettet nogen brugerdefinerede bokse.</p>";
							}
							?>
						</div>
					</td>
				</tr>
			</table>


			<h2><div style='float:left;'>Relaterede sider</div><div id='ajaxloader_relpages'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></div></h2><p class="feltkommentar">Brug denne funktion til at tilføje relaterede sider. Links til relaterede sider bliver vist i bunden af denne side.</p>
			<div id="attachedpages">Undersøger om der er tilføjet relaterede sider...</div>
			<div id="availablemenus"></div>	
			<div id="availablepages"></div>
			<div class="knapbar_inline">
				<input id='availablepages_button' type="button" value="Tilføj relateret side" onclick="loadAvaliableMenusList();" />
			</div>

			<h2><div style='float:left;'>Relaterede nyheder</div><div id='ajaxloader_relnews'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></div></h2><p class="feltkommentar">Brug denne funktion til at tilføje relaterede nyheder. Links til nyheder sider bliver vist i bunden af denne side.</p>
			<div id="attachednews">Undersøger om der er tilføjet relaterede nyheder...</div>
			<div id="availablenewsarchives"></div>	
			<div id="availablenews"></div>
			<div class="knapbar_inline">
				<input id='availablenews_button' type="button" value="Tilføj relateret nyhed" onclick="loadAvaliableNewsarchivesList();" />
			</div>

			<h2><div style='float:left;'>Relaterede kalender-begivenheder</div><div id='ajaxloader_relevents'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></div></h2><p class="feltkommentar">Brug denne funktion til at tilføje relaterede begivenheder. Links til begivenheder sider bliver vist i bunden af denne side.</p>
			<div id="attachedevents">Undersøger om der er tilføjet relaterede begivenheder...</div>
			<div id="availablecalendars"></div>	
			<div id="availableevents"></div>
			<div class="knapbar_inline">
				<input id='availableevents_button' type="button" value="Tilføj relateret begivenhed" onclick="loadAvaliableCalendarsList();" />
			</div>


			<h2><div style='float:left;'>Vedhæftede filer</div><div id='ajaxloader_files'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></div></h2>
			<p class="feltkommentar">Brug denne funktion til at vedhæfte filer til siden. Du kan tilføje alle filer, som er uploaded i filarkivet.</p>
			<div id="attachedfiles">Henter liste med vedhæftede filer...</div>
			<div id="availablefiles"></div>
			<div class="knapbar_inline">
				<input id='availablefiles_button' type="button" value="Vedhæft fil" onclick="loadAvaliableFileList();" />
			</div>
		<h2><div style='float:left;'>Tilføj billedgalleri</div><div id='ajaxloader_gallery'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></div></h2><p class="feltkommentar">Brug denne funktion til at tilføje et billedgalleri til siden. Et billedgalleri kan være hvilkensomhelst mappe i billedarkivet.</p>
			<div id="attachedgallery">Undersøger om der er tilføjet et galleri til siden...</div>
			<div id="availablepicturefolders"></div>

			<div class="knapbar_inline">
				<input id='availablepicturefolders_button' type="button" value="Tilføj galleri" onclick="loadAvaliablePicturefoldersList();" />
			</div>

			<h2><div style='float:left;'>Tilføj formular</div><div id='ajaxloader_form'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></div></h2><p class="feltkommentar">Brug denne funktion til at tilføje en formular til siden. Du kan tilføje enhver formular som er oprettet med formular-værktøjet.</p>
			<div id="attachedform">Undersøger om der er tilføjet en formular til siden...</div>
			<div id="availableforms"></div>

			<div class="knapbar_inline">
				<input id='availableforms_button' type="button" value="Tilføj formular" onclick="loadAvaliableFormsList();" />
			</div>
		</div>
		<div class="knapbar">
			<input type="button" value="Gem og preview" onclick="savePreview()" />    
			<input type="button" value="Afbryd" onclick="location='index.php?content_identifier=pages&amp;dothis=oversigt&amp;menuid=<?php echo $menuid ?>'" />
			<input type="button" value="Gem" onclick="verify()" />
		</div>
	</div>
 </form>

<script type="text/javascript">
	<?php if ($pagedata) {
		// Loaded values
	?>
//	setRadioCheckedMark("protection_selector", <?php echo $pagedata["LOCKED_BY_USER"] ?>, 0);
	setRadioCheckedMark("published", <?php echo $pagedata["PUBLISHED"] ?>, 0);
	setRadioCheckedMark("beskyttet", <?php echo $pagedata["PROTECTED"] ?>, 1);
	<?php
		if ($pagedata["PROTECTED"] == 1) {
			echo "disableAllGroups(1);";
		}
		if ($pagedata["POINTTOPAGE_ID"] !=0) {
			echo "document.forms[0].pointToPageSelector.disabled = false; document.forms[0].pointToPageSelector.disabled = false; document.forms[0].pointToPageSelector.value='$pagedata[POINTTOPAGE_ID]'";
		}
	} else {
		?>
		// Default values
//		setRadioCheckedMark("protection_selector", 0, 0);
		setRadioCheckedMark("published", 0, 0);
		setRadioCheckedMark("beskyttet", 1, 1);
		<?php
	}
	if ($showpreview == 1) {
		$langvar = returnFieldValue("LANGUAGES", "SHORTNAME", "ID", $pagedata["LANGUAGE"]);
		echo "showPreview($id, '$langvar', $_SESSION[SELECTED_SITE], 'PAGES', '$grant', ";
		if ($pagedata[BOOK_ID]) {
			echo "'&amp;bookid=$pagedata[BOOK_ID]'";
		} else {
			echo "''";
		}
		echo ");";
	}
	?>
</script>
<?php
 }
?>

<?php if ($dothis=="related") { 
function _______________related() {}
?>
 <h1>Relateret indhold</h1>
 <div class="broedtekst">
  Nedenfor kan du vælge, hvilket indhold, der skal relateres til den valgte side. Det relaterede indhold fremstår
  i en boks på siden.
 </div>
 <form id="defaultForm" method="post" action="">
  <input type="hidden" name="dothis" value="" />
  <input type="hidden" name="menuid" value="<?php echo $menuid ?>" />
  <input type="hidden" name="det_nye_id" value="<?php echo $id ?>" />
  <div class="feltblok_header">Relateret indhold for 
   <span class="yellow">
    <?php 
     if ($tabel=="PAGES") echo menuPath($pageid, false) ;
     if ($tabel=="NEWS") echo returnNewsTitle($pageid);
     if ($tabel=="EVENTS") echo returnEventTitle($pageid);
    ?>
   </span>
  </div>
  <div class="feltblok_wrapper">
   <h2>Sider:</h2>
   <select class="" name="rel_pages">
    <?php echo buildPagesDropdown("",0,0) ?>
   </select>&nbsp;
   <input type="button" value="Tilføj" onclick="addRelated('<?php echo $tabel ?>', <?php echo $pageid ?>,  'PAGES', this.form.rel_pages.value, <?php echo $menuid ?>)" class="lilletekst_knap" />
   <br/>
   <div style="padding:10px; margin-top:10px; border:1px solid #aaa; background-color:#bbb">
    <?php echo relatedList($pageid, "PAGES", $tabel, $menuid) ?>
   </div>
   <h2>Nyheder:</h2>
   <select class="" name="rel_news">
    <?php echo newsSelector(-1) ?>
   </select>&nbsp;
   <input type="button" value="Tilføj" onclick="addRelated('<?php echo $tabel ?>', <?php echo $pageid ?>,  'NEWS', this.form.rel_news.value, <?php echo $menuid ?>)" class="lilletekst_knap" />
   <br/>
   <div style="padding:10px; margin-top:10px; border:1px solid #aaa; background-color:#bbb">
    <?php echo relatedList($pageid, "NEWS", $tabel, $menuid) ?>
   </div>
   <h2>Kalenderblade:</h2>
   <select class="" name="rel_events">
    <?php echo eventsSelector(-1) ?>
   </select>&nbsp;
   <input type="button" value="Tilføj" onclick="addRelated('<?php echo $tabel ?>', <?php echo $pageid ?>,  'EVENTS', this.form.rel_events.value, <?php echo $menuid ?>)" class="lilletekst_knap" />
   <br/>
   <div style="padding:10px; margin-top:10px; border:1px solid #aaa; background-color:#bbb">
    <?php echo relatedList($pageid, "EVENTS", $tabel, $menuid) ?>
   </div>
  </div>
  <div class="knapbar">
   <?php if ($tabel=="PAGES") {?> 
    <input type="button" value="Færdig" onclick="location='index.php?content_identifier=pages&amp;dothis=oversigt&amp;menuid=<?php echo $menuid ?>'" />
   <?php } ?>
   <?php if ($tabel=="NEWS") {?> 
    <input type="button" value="Færdig" onclick="location='index.php?content_identifier=news'" />
   <?php } ?>
   <?php if ($tabel=="EVENTS") {?> 
    <input type="button" value="Færdig" onclick="location='index.php?content_identifier=events'" />
   <?php } ?>
  </div>
 </form>
<?php
 }
?>

<?php if ($dothis=="boxes") { 
function _______________boxes() {}
?>
 <h1>Bokse i højremargin</h1>
 <div class="broedtekst">
  Her kan du slå de "faste bokse" i højre margin (nyhedsboks, kalenderboks, søgeboks, nyhedsbrev og "send til en ven") til
  og fra på den pågældende side. Desuden kan du oprette ekstra bokse med links eller fritekst.
 </div>
 <form id="defaultForm" method="post" action="">
  <input type="hidden" name="dothis" value="" />
  <input type="hidden" name="menuid" value="<?php echo $menuid ?>" />
  <input type="hidden" name="det_nye_id" value="<?php echo $pageid ?>" />
  <div class="feltblok_header">Bokse for 
   <span class="yellow">
    <?php 
     if ($tabel=="PAGES") echo menuPath($pageid, false) ;
     if ($tabel=="NEWS") echo returnNewsTitle($pageid);
     if ($tabel=="EVENTS") echo returnEventTitle($pageid);
    ?>
   </span>
  </div>
  <div class="feltblok_wrapper">
   <?php 
    $sql = "select * from BOX_SETTINGS where PAGE_ID='$pageid'";
    $result = mysql_db_query($dbname, $sql) or die(mysql_error());
    $row = mysql_fetch_array($result);
   ?>
   <h2>Standard-bokse:</h2>
   <input type="checkbox" name="show_news" <?php if ($row[NEWS]==1) echo "checked" ?> />&nbsp;Vis nyhedsboks<br/>
   <input type="checkbox" name="show_events" <?php if ($row[EVENTS]==1) echo "checked" ?> />&nbsp;Vis kalenderboks<br/>
   <input type="checkbox" name="show_search" <?php if ($row[SEARCH]==1) echo "checked" ?> />&nbsp;Vis søgeboks<br/>
   <input type="checkbox" name="show_stf" <?php if ($row[STF]==1) echo "checked" ?> />&nbsp;Vis send-til-en-ven boks<br/>
   <input type="checkbox" name="show_newsletter" <?php if ($row[NEWSLETTER]==1) echo "checked" ?> />&nbsp;Vis nyhedsbrev-boks<br/>     
   <input type="checkbox" name="show_lastedited" <?php if ($row[LASTEDITED]==1) echo "checked" ?> />&nbsp;Vis "sidst redigeret" i bunden af siden      
   <h2>Brugerdefinerede bokse:</h2>
   <?php 
    $sql = "select * from CUSTOM_BOXES where DELETED='0' order by TITLE asc";
    $result = mysql_db_query($dbname, $sql) or die(mysql_error());
    while ($crow = mysql_fetch_array($result)){
	 if (strstr($row[CUSTOM], "_$crow[ID]_")) $c = "checked";
	 echo "<input type='checkbox' name='custombox_$crow[ID]' $c />&nbsp;$crow[TITLE]<br/>";
	 $c="";
	}
   ?>
   <h2>Andre indstillinger:</h2>
   <input type="checkbox" name="add_to_subpages" />&nbsp;Ændringerne gælder også sidens undersider
   <div class="knapbar">
    <?php if ($tabel=="PAGES") {?> 
     <input type="button" value="Gem ændringer" onclick="this.form.dothis.value='gemfastebokse'; this.form.submit();" />
    <?php } ?>
   </div>
  </div>
  <!--
  <div class="feltblok_wrapper">
   <h2>Sider:</h2>
   <select class="" name="rel_pages">
    <?php echo buildPagesDropdown("",0,0) ?>
   </select>&nbsp;
   <input type="button" value="Tilføj" onclick="addRelated('<?php echo $tabel ?>', <?php echo $pageid ?>,  'PAGES', this.form.rel_pages.value, <?php echo $menuid ?>)" class="lilletekst_knap" />
   <br/>
   <div style="padding:10px; margin-top:10px; border:1px solid #aaa; background-color:#bbb">
    <?php echo relatedList($pageid, "PAGES", $tabel, $menuid) ?>
   </div>
   <h2>Nyheder:</h2>
   <select class="" name="rel_news">
    <?php echo newsSelector(-1) ?>
   </select>&nbsp;
   <input type="button" value="Tilføj" onclick="addRelated('<?php echo $tabel ?>', <?php echo $pageid ?>,  'NEWS', this.form.rel_news.value, <?php echo $menuid ?>)" class="lilletekst_knap" />
   <br/>
   <div style="padding:10px; margin-top:10px; border:1px solid #aaa; background-color:#bbb">
    <?php echo relatedList($pageid, "NEWS", $tabel, $menuid) ?>
   </div>
   <h2>Kalenderblade:</h2>
   <select class="" name="rel_events">
    <?php echo eventsSelector(-1) ?>
   </select>&nbsp;
   <input type="button" value="Tilføj" onclick="addRelated('<?php echo $tabel ?>', <?php echo $pageid ?>,  'EVENTS', this.form.rel_events.value,<?php echo $menuid ?>)" class="lilletekst_knap" />
   <br/>
   <div style="padding:10px; margin-top:10px; border:1px solid #aaa; background-color:#bbb">
    <?php echo relatedList($pageid, "EVENTS", $tabel, $menuid) ?>
   </div>
  </div>
  -->
  <div class="knapbar">
   <?php if ($tabel=="PAGES") {?> 
    <input type="button" value="Færdig" onclick="location='index.php?content_identifier=pages&amp;dothis=oversigt&amp;menuid=<?php echo $menuid ?>'" />
   <?php } ?>
   <?php if ($tabel=="NEWS") {?> 
    <input type="button" value="Færdig" onclick="location='index.php?content_identifier=news'" />
   <?php } ?>
   <?php if ($tabel=="EVENTS") {?> 
    <input type="button" value="Færdig" onclick="location='index.php?content_identifier=events'" />
   <?php } ?>
  </div>
 </form>
<?php
 }
?>

<?php
function _______________advarsel() {}
 if ($dothis == "advarsel") {
?>

<div class="feltblok_header_alert">
 Du er ved at slette en side, som andre sider i sitet linker til.
</div>
<div class="feltblok_wrapper">
 Følgende sider indeholder et link til den side, du er ved at slette:
 <br/><br/><strong>
 <?php 
  foreach($_SESSION[LINKED_PAGES] as $pageid) {
   $sti=""; $husk="";
   echo menuPath($pageid, false) . "<br/>";
  }
 ?></strong>
 <br/>
 Det er ikke tilrådeligt at slette siden, før du har opdateret det pågældende links. Vil du fortsætte med sletningen?
</div>
<div class="knapbar">
 <input type="button" value="Nej, afbryd" onclick="location='index.php?content_identifier=pages&amp;menuid=<?php echo $menuid ?>'" />
 <input type="button" value="Ja, slet siden" onclick="location='index.php?content_identifier=pages&amp;dothis=slet&amp;id=<?php echo $_GET[pageid]?>&amp;menuid=<?php echo $menuid ?>&amp;ignoreadvarsel=1'" />
</div>
<?php } 

function get_default_menu($arr_noaccess="") {

	if (is_array($arr_noaccess)) {
		$str_noaccess = implode($arr_noaccess, ",");
		if (count($arr_noaccess)>0) {
			$sql_filter = "and MENU_ID not in ($str_noaccess)";
		} else {
			$sql_filter = "";
		}
	}

	// Get first menu
	$sql = "select MENU_ID from MENUS where SITE_ID in (0,'$_SESSION[SELECTED_SITE]') $sql_filter order by MENU_ID asc limit 1"; 
	$result = mysql_query($sql) or die(mysql_error());
	if (mysql_num_rows($result)==0) {
		return false;
	}
	$row = mysql_fetch_row($result);
	$menuid = $row[0];

	// Check if user has permission to see it
	if (!check_data_permission("DATA_CMS_MENU_ACCESS", "MENUS", $menuid, "", $_SESSION["CMS_USER"]["USER_ID"])) {
		$arr_noaccess[] = $menuid;
		$menuid = get_default_menu($arr_noaccess);
	}
	if (is_numeric($menuid)) {
		return $menuid;
	} else {
		echo "<p>Du har ikke adgang til nogen menuer på dette site!</p>";
		exit;

	}
}

?>