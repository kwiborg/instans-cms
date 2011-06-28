<?php
	if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
	if ($_GET[usermessage_ok] != "") {
		usermessage("usermessage_ok", $_GET[usermessage_ok]);
	}
	if ($_GET[usermessage_error] != "") {
		usermessage("usermessage_error", $_GET[usermessage_error]);
	}
	if ($_GET["dothis"] == "reorganize" && $_GET[folderid]){
?>
	<form method="post" action="">
		<input type="hidden" name="dothis" value="" />
		<input type="hidden" name="order" value="" />
		<?php
 			$html .= "<h1>Billedarkiv: Ændre rækkefølgen i mappen <em>".returnFieldValue("PICTUREARCHIVE_FOLDERS", "TITLE", "ID", $_GET[folderid])."</em></h1>";
			$html .= "<div class='feltblok_header'>Træk i billederne for at reorganisere dem, og tryk på \"Gem og afslut\", når du er færdig.</div>";
			$html .= "<div class='feltblok_wrapper'>";
			$foldername = returnFieldValue("PICTUREARCHIVE_FOLDERS", "FOLDERNAME", "ID", $_GET[folderid]);
			$sql = "select ID, FILENAME from PICTUREARCHIVE_PICS where FOLDER_ID='$_GET[folderid]' order by POSITION asc";
			$result = mysql_query($sql);
			$html .= "<div style='border:1px solid #aaa; padding:10px;' id='sortThis'>";
			while ($row = mysql_fetch_array($result)){
				$i++;
				$html .= "<img id='thumb_$row[ID]' style='margin:5px; border:1px solid #000' src='".$picturearchive_UploaddirRel."/$foldername/thumbs/".$row[FILENAME]."' />";
			}
			$html .= "</div>";		
			$html .= "</div>";
			$html .= "
				<script type='text/javascript'>
					Sortable.create('sortThis', {tag:'img', constraint:false});
					theOrder = Sortable.serialize('sortThis');
				</script>
			";
			$html .= "
				<div class='knapbar'>
					<input type='button' value='Gem og afslut' onclick='reorderSave()'>
					<input type='button' value='Afbryd' onclick='location=\"index.php?content_identifier=picturearchive&folderid=$_GET[folderid]\"'>
				</div>
			";
			$html .= "</form>";
			echo $html;		
	}
?>
<?php
	if ($_GET["dothis"] == "oversigt" || !$_GET["dothis"]){
//		billederRydOp();
?>
	<h1>Billedarkiv: Oversigt</h1>
	<form id="defaultForm" method="post" action="">
		<input type="hidden" name="dothis" value="" />
		<div class="feltblok_header"></div>
		<div class="feltblok_wrapper">
			<div id="folderView">
				<?php
					$akkumulated_foldermenu = "";
					new_folder_view(0);
					echo $akkumulated_foldermenu;
				?>
			</div>
			<div id="fileView">
				<?=($_GET[folder_id] ? new_file_view($_GET[folder_id]) : "")?>
			</div>
			<div id="clearer"></div>
			<?php if ($picturearchive_AdvancedOn && checkpermission("CMS_SETDATAPERMISSIONS_PICTUREARCHIVE_FOLDERS")) { ?>

				<div class="knapbar"><input id="toggleAdvancedButton" type="button" class="lilletekst_knap" value="Flere muligheder" onclick="toggleAdvancedOptions()" /></div>

			<?php } ?>
			<?php 	if ($picturearchive_AdvancedOn) {
						echo "<div id='advancedOptions' style='display:none;'>";
					}
			?>

			<?php if ($picturearchive_DropboxOn && $picturearchive_AdvancedOn) { ?>
			<div id="importOptions">
				<h2>
					<div style='float:left;'>Importér fra dropbox</div>
					<div id='ajaxloader_dropbox'><img src='images/ajax-loader.gif' class='loadIndicator' alt='load-indicator' /></div>
				</h2>
				<p>Dropbox-funktionen gør det nemt at importere mange billeder til billedarkivet. Billeder i jeres "dropbox" ftp mappe bliver automatisk importeret til en eksisterende eller ny billedmappe, når du trykker på "Importér fra dropbox"-knappen. Kun billedfiler med endelsen .jpg, .gif og .png bliver uploaded. Alle andre filer bliver slettet. <a id="dropbox_help_activate" href="#">Få hjælp til at uploade billeder til "dropbox" ftp mappen</a></p>
				<div id="dropbox_help">
				<h3>Hjælp til at uploade billeder til "dropbox" ftp mappen</h3>
				<p>Du skal benytte et FTP program til at uploade dine billeder til dropbox'en. Du skal bruge følgende oplysninger til at forbinde til ftp-mappen:</p><ul><li>Server: <?=$dropbox_ftp_server?></li><li>Brugernavn: <?=$dropbox_ftp_user?></li><li>Password: <?=$dropbox_ftp_pass?></li></ul>
				<p><strong>Genvej i Internet Explorer 7 til Windows</strong>:</p>
				<ol><li><a href='ftp://<?=$dropbox_ftp_user?>:<?=$dropbox_ftp_pass?>@<?=$dropbox_ftp_server?>' target='_blank'>Klik på dette link for at åbne din "dropbox" i et nyt vindue</a></li><li>Følg vejledning på skærmen for at åbne ftp mappen i "Windows Explorer".</li><li>Træk de ønskede billeder over i det nye "Windows Explorer"-vindue.</li></ol>
				<p><strong>Genvej i Internet Explorer 6 til Windows</strong>:</p>
				<ol><li><a href='ftp://<?=$dropbox_ftp_user?>:<?=$dropbox_ftp_pass?>@<?=$dropbox_ftp_server?>' target='_blank'>Klik på dette link for at åbne din "dropbox" i et nyt vindue</a></li><li>Træk de ønskede billeder over i det nye "Internet Explorer"-vindue.</li></ol>
				</div>
				<h3>Placering</h3>
				<p>
				<input type="radio" name="imageFolder" value="0" checked />Placer indhold af dropbox i ny hovedmappe<br/>
				<input type="radio" name="imageFolder" value="<?=$_GET[folder_id]?>" <?=($_GET[folder_id] ? "" : "disabled")?> />Placer indhold af dropbox i den valgte mappe <? if ($_GET[folder_id]) { echo "(\"".returnFieldValue("PICTUREARCHIVE_FOLDERS", "TITLE", "ID", "$_GET[folder_id]")."\")"; }?></p>
				<p><input type="checkbox" id="imageKeepFolderstructure" name="imageKeepFolderstructure" value="1"  checked />Opret billedmappestruktur som svarer til mappestruktur i dropbox</p>
				<h3>Rettigheder</h3>
				<p><input type="checkbox" id="imagepublicFolder" name="imagepublicFolder" value="1" />Gør alle mapper og undermapper offentligt tilgængelig som galleri</p>
				<h3>Størrelser</h3>
				<p><input type="checkbox" id="imageMakeThumb" value="1"  checked disabled />Thumbnail størrelse, <?=$imageThumbSize?> pixels på den største led<br/>
				<input type="checkbox" id="imageMakeStandard" name="imageMakeStandard" value="1"  checked disabled />Standard størrelse, max. <input type="text" size="4" value="<?=$imageStandardSize?>" id="imageMaxSize" name="imageMaxSize" /> pixels på den største led<br/>
				<input type="checkbox" id="imageKeepOriginal" name="imageKeepOriginal" value="1" />Original størrelse, hvis større end <input type="text" size="4" value="<?=$imageOriginalMinsize?>" id="imageMinOriginalsize" name="imageMinOriginalsize" /> pixels på den største led</p>
				<p><strong>Bemærk:</strong> Billeder som gemmes i original størrelse fylder meget på serveren. Gem derfor kun originaler, hvis du ønsker at originalerne skal være tilgængelig på hjemmesiden. Ellers bør du gemme originalerne på din egen computer. Husk at tage backup!</p>
				<h3>Billedtitler</h3>
				<p>
				<input type="radio" name="imageTitle" value="useFilename" checked />Brug originalt filnavn<br/>
				<input type="radio" name="imageTitle" value="useCustom" />Brug denne titel: <input type="text" size="30" value="" id="imageAlt" /><br/>
				<input type="radio" name="imageTitle" value="useUploadtime" />Brug tidspunkt for upload<br/>
				<input type="radio" name="imageTitle" value="useCapturetime" />Brug tidspunkt for billedets optagelse</p>
				<p><strong>Bemærk:</strong> De fleste digitalkameraer og mobiltelefoner gemmer tidspunkt for billedets optagelse i billedfilen. Hvis tidspunktet ikke er gemt i billedfilen, anvendes i stedet tidspunkt for upload.</p>
				<h3>Billedbeskrivelse</h3>
				<p>
				<input type="radio" name="imageDescription" value="useFilename" />Brug originalt filnavn<br/>
				<input type="radio" name="imageDescription" value="useCustom" />Brug denne beskrivelse: <input type="text" size="30" value="" id="imageCustomDescription" /><br/>
				<input type="radio" name="imageDescription" value="useUploadtime" />Brug tidspunkt for upload<br/>
				<input type="radio" name="imageDescription" value="useCapturetime" checked  />Brug tidspunkt for billedets optagelse</p>
				<p><strong>Bemærk:</strong> De fleste digitalkameraer og mobiltelefoner gemmer tidspunkt for billedets optagelse i billedfilen. Hvis tidspunktet ikke er gemt i billedfilen, anvendes i stedet tidspunkt for upload.</p>
<br/>
				<input type="hidden" id="dropbox_batchnumber" value="0" />
				<div style='border: 1px solid #ddd; padding: 5px;'>
					<div style='text-align: left; float: left;'><strong>Status: </strong><span id="dropbox_importstatus">Import ikke påbegyndt</span></div>
					<div style='text-align: right; float: right;'><input id="importBtn" type="button" class="lilletekst_knap" value="Importér fra dropbox" onclick="dropboxImport()" /></div>
					<div style='clear:both'></div>
					<div id="dropbox_errorlog"><strong>ERROR LOG:</strong><br/></div>
					<div id="dropbox_importlog"><strong>IMPORT LOG:</strong><br/></div>
				</div>
								
			</div>
			<?php } ?>

			<?php 	if ($picturearchive_AdvancedOn) {
						echo "</div>"; // slut id=advancedOptions
					}
			?>		</div>
	</form>
<?php } ?>
<?php
	if ($dothis == "opretnymappe") {
?>
		<h1>Billedarkiv: Opret ny mappe til billeder</h1>
		<div class="broedtekst">
		</div>
		<!-- /// -->
		<!-- FORM -->
		<form id="defaultForm" method="post" action="">
		<input type="hidden" name="dothis" value="" />
		<input type="hidden" name="parent_id" value="<?=$_GET[parent_id]?>" />
		<input type="hidden" name="mmo" value="" />
		<div class="feltblok_header">
		 <?php if ($_GET[parent_id]>0) { ?>
		 	Ny mappe som undermappe til: <span class="yellow"><?=returnFieldValue("PICTUREARCHIVE_FOLDERS", "TITLE", "ID", $_GET[parent_id])?></span>
		<?php } else { ?>
			Ny hovedmappe
		<?php } ?>
		</div>
		<div class="feltblok_wrapper">
		 <h2>Mappens navn:</h2>
		 <input type="text" name="mappenavn" class="inputfelt" value="<?php if ($entrydata) echo $entrydata["ENTRY_LABEL"] ?>">
		 
		 <!--<h2>Er mappen privat (kun synlig for dig?):</h2>
		 <input type="hidden" name="privat_mappe_res" value="" />  
		 <input type="radio" name="privat_mappe" onclick='setResValue(this.name, 1);' />&nbsp;Nej<br />
		 <input type="radio" name="privat_mappe" onclick='setResValue(this.name, 2);' />&nbsp;Ja<br />-->
		</div>
		 
		<div class="knapbar">
		 <input type="button" value="Afbryd" onclick="location='index.php?content_identifier=<?php echo $content_identifier ?>&amp;folder_id=<?=$_GET[parent_id]?>'" />
		 <input type="button" value="Gem" onclick="verify_mappe(<?php echo $mainmenuoff ?>)" />
		</div>
		
		<!-- /// -->
		</form>
		<?php
		 }
?>

<?php
		if ($dothis == "redigermappe") {
			// Get folder name from db
			$foldername = returnFieldValue("PICTUREARCHIVE_FOLDERS", "TITLE", "ID", $_GET[folderid]);
			$folderdesc = returnFieldValue("PICTUREARCHIVE_FOLDERS", "FOLDER_DESCRIPTION", "ID", $_GET[folderid]);
			$thumbmode 	= returnFieldValue("PICTUREARCHIVE_FOLDERS", "THUMBMODE", "ID", $_GET[folderid]);
?>
			
		<!-- ØVRE TEKST -->
		<h1>
		 Rediger billedmappe
		</h1>
		<div class="broedtekst">
		</div>
		<!-- /// -->
		<!-- FORM -->
		<form id="defaultForm" method="post" action="">
		<input type="hidden" name="dothis" value="" />
		<input type="hidden" name="mmo" value="" />
		<input type="hidden" name="mode" value="edit" />
		<input type="hidden" name="folderid" value="<?php echo $_GET[folderid]; ?>" />
		<div class="feltblok_wrapper">
		 <h2>Mappens navn:</h2>
		 <input type="text" name="mappenavn" class="inputfelt" value="<?php echo $foldername; ?>">
		 <h2>Kort beskrivelse af mappen:</h2>
		 <input type="text" name="folder_description" class="inputfelt" value="<?php echo $folderdesc; ?>">
		 <h2>Tilgængelig som galleri?</h2>
		 <input type="checkbox" name="public_folder" value="1" <?=(returnFieldValue("PICTUREARCHIVE_FOLDERS", "PUBLIC_FOLDER", "ID", $_GET[folderid])==1 ? "checked" : "")?> />&nbsp;Ja, denne mappe er tilgængelig som galleri
		 <h2>Brug følgende billede som mappens "thumbnail":</h2>
		 <input type="radio" name="thumbmode" value="NEWEST" <?=(returnFieldValue("PICTUREARCHIVE_FOLDERS", "THUMBMODE", "ID", $_GET[folderid])=="NEWEST" ? "checked" : "")?> />&nbsp;Nyeste billede i mappen<br/>
		 <input type="radio" name="thumbmode" value="FIRSTPOS" <?=(returnFieldValue("PICTUREARCHIVE_FOLDERS", "THUMBMODE", "ID", $_GET[folderid])=="FIRSTPOS" ? "checked" : "")?>/>&nbsp;Første billede i mappen (ift. den måde, billederne er organiseret på)
		<?php
			if (checkPermission("CMS_SETDATAPERMISSIONS_PICTUREARCHIVE_FOLDERS", false)) {
		?>
		 <h2>Flyt mappen til:</h2>
		 	<select name="new_parent_id" class="inputfelt">
				<option value="DO_NOT_MOVE">Flyt ikke mappen</option>
				<option value="0">Øverste niveau</option>
				<?=select_folders($_GET[folderid], " Undermappe til: ")?>
			</select>
		<?php
				echo datapermission_set("DATA_PICTUREARCHIVE_USEINCMS", "PICTUREARCHIVE_FOLDERS", $_GET[folderid]);
				echo datapermission_set("DATA_PICTUREARCHIVE_MANAGEFOLDER", "PICTUREARCHIVE_FOLDERS", $_GET[folderid]);
			}
		?>


		</div>
		 
		<div class="knapbar">
		 <input type="button" value="Afbryd" onclick="location='index.php?content_identifier=<?php echo $content_identifier ?>&mainmenuoff=<?php echo $mainmenuoff ?>'" />
		 <input type="button" value="Gem" onclick="verify_mappe(<?php echo $mainmenuoff ?>)" />
		</div>
		</form>
		<?php
		 }
?>


<?php
if ($dothis == "opretbillede" && $trin == 1) {
		?>
			
		<!-- ØVRE TEKST -->
		<h1>
		 Upload et billede til arkivet
		</h1>
		<div class="broedtekst">
		Følg trin-for-trin vejledningen til at uploade et billede: Start her med at finde det billede, der skal i arkivet. Hvis dit billede er meget stort, må du have tålmodighed efter at der er trykket [videre], da billedet først skal uploades i sin fulde størrelse. (Bemærk: Du får hjælp til at bestemme størrelsen af det billede, der skal i arkivet. Så selvom din original er meget stor, kan den godt vælges og uploades til arkivet).
		</div>
		<!-- /// -->
		<!-- FORM -->
		<form id="defaultForm" method="post" enctype="multipart/form-data" action="">
		<input type="hidden" name="dothis" value="" />
		<input type="hidden" name="mmo" value="" />
		<div class="feltblok_header">
		 Trin 1: Valg af billede
		</div>
		<div class="feltblok_wrapper">
		 <h2>Vælg billede:</h2>
		 <input type="file" name="billede" class="inputfelt" onkeypress="this.blur()">
		 <?php if ($error==1) { ?>
		  <br />Fejl: Filtypen, som du uploadede, understøttes ikke. Vælg venligst JPEG, GIF eller PNG-billede.
		 <?php } ?>
		</div>
		 
		<div class="knapbar">
		 <input type="button" value="Afbryd" onclick="location='index.php?mainmenuoff=<?php echo $mainmenuoff ?>&content_identifier=<?php echo $content_identifier ?>'" />
		 <input type="button" value="Videre" onclick="verify_billede(<?php echo $trin . ",0," . "'$mainmenuoff'" ?>)" />
		</div>
		
		<!-- /// -->
		</form>
		<?php
}
?>

<?php
		if ($dothis == "opretbillede" && $trin == 2) {
		?>
			
		<!-- ØVRE TEKST -->
		<h1>
		 Upload et billede til arkivet
		</h1>
		<div class="broedtekst">
		Vælg billedet størrelse og angiv eventuelt beskrivelse og alt. Vigtigt: Der må udvises tålmodighed efter at der er blevet trykket [videre]. Billedet behandles i den mellemliggende tid.
		</div>
		<!-- /// -->
		<!-- FORM -->
		<form id="defaultForm" method="post" action="">
		<input type="hidden" name="dothis" value="" />
		<input type="hidden" name="billedtype" value="" />
		<input type="hidden" name="mmo" value="" />
		<div class="feltblok_header">
		 Trin 2: Billedbehandling
		</div>
		<div class="feltblok_wrapper">
		 <?php
		  $foldername = returnFolderName($folderid, 1, "PICTUREARCHIVE_FOLDERS");
		  $filename = returnFileName($imageid, 1, "PICTUREARCHIVE_PICS");
		  $realfilename = returnFileName($imageid, 2, "PICTUREARCHIVE_PICS");
		  $image = "$picturearchive_Uploaddir/$foldername/$filename";
		  $imageAbs = "$picturearchive_UploaddirAbs/$foldername/$filename";
		  $info = getimagesize($image);
		  	  
		 ?>
		 
		 <?php if ($info[2] > 1) {


			 $original_w = $info[0];
			 $original_h = $info[1];
			 if (($original_w > $imageStandardSize) || ($original_h > $imageStandardSize)){
			 	// Calculate new size
				if ($original_w > $original_h) {
					$scale_factor = $imageStandardSize / $original_w;
				} else {
					$scale_factor = $imageStandardSize / $original_h;
				}
				$new_w = round($original_w * $scale_factor);
				$new_h = round($original_h * $scale_factor);
			 } else {
			 	$new_w = $original_w;
			 	$new_h = $original_h;
			 }
	
		 ?>


		 <h2>Valgt billede:</h2>
		<?php
		  if ($new_w > 730) {
		  	$tving_ned = "width='730'";
		  } else {
		  	$tving_ned = "width='$new_w'";
		  }
		  echo "<div id='uploaded_image_preview'><img src='$imageAbs?rand=" . rand(1,1000000) . "' $tving_ned border='0' id='thisimage'></div><br />($realfilename)";

		?>		
		 <h2>Upload i størrelsen:</h2>
		 <input type="hidden" name="image_standard_size" value="<?=$imageStandardSize?>" />
		 <input type="hidden" name="size_changed" value="0" />
		 <input type="hidden" name="grundtal_bredde" value="<?php echo $new_w; ?>" />
		 <input type="hidden" name="grundtal_hoejde" value="<?php echo $new_h; ?>" />
		 <input type="hidden" name="original_bredde" value="<?php echo $original_w; ?>" />
		 <input type="hidden" name="original_hoejde" value="<?php echo $original_h; ?>" />
		 B:&nbsp;<input type="text" size="4" name="imagewidth" onkeyup="prop('x')" value="<?php echo $new_w ?>" />
		 &nbsp;x&nbsp;
		 H:&nbsp;<input type="text" size="4" name="imageheight" onkeyup="prop('y')" value="<?php echo $new_h ?>" />
		 &nbsp;
		 <input type="checkbox" name="proportioner" checked="checked" />&nbsp;Fasthold proportioner
		 &nbsp;&nbsp;<input type="button" class="lilleknap" value="Nulstil" onclick="nulstil()" />
		 <h2>Original:</h2>
		 <input type="checkbox" id="imageKeepOriginal" name="imageKeepOriginal" value="1" />&nbsp;Gem original (<?=$info[0]?> x <?=$info[1]?> pixels)<br/>
		<p class="feltkommentar"><strong>Bemærk:</strong> Billeder som gemmes i original størrelse fylder meget på serveren. Gem derfor kun originaler, hvis du ønsker at originalerne skal være tilgængelig på hjemmesiden. Ellers bør du gemme originalerne på din egen computer. Husk at tage backup!</p>
		 <h2>Kvalitet (0-100):</h2>
		 <input type="text" size="4" name="quality" class="inputfelt_kort" value="75" />
		 <p class="feltkommentar">Lavt tal = lille filstørrelse + dårlig kvalitet. Højt tal = stor filstørrelse + god kvalitet.</p>
		 <?php
		  }
		 ?>
		
		 <h2>ALT-tekst (ikke påkrævet):</h2>
		 <input type="text" class="inputfelt_kort" size="70" name="alttext" value="<?php echo $data["ALTTEXT"] ?>" />
		 <p class="feltkommentar">Billedets alt-tekst vises i den "gule boks", som fremkommer ved mouseover - og hvis billedet ikke kan indlæses. Bruges også til indeksering i søgemaskiner.</p>

		 <h2>Beskrivelse (ikke påkrævet):</h2>
		 <textarea name="description" class="inputfelt_kort" cols="70" rows="5"><?php echo $data["DESCRIPTION"] ?></textarea>

		</div>
		
		<div class="knapbar">
		 <input type="button" value="Afbryd" onclick="location='index.php?mainmenuoff=<?php echo $mainmenuoff ?>&content_identifier=<?php echo $content_identifier ?>'" />
		 <input type="button" value="Videre" onclick="verify_billede(<?php echo $trin . "," . $info[2] ?>)" />
		</div>
		
		<!-- /// -->
		</form>
		
		<?php
}
?>

<?php
if ($dothis == "editbillede") {
		 $data = hentRow($imageid, "PICTUREARCHIVE_PICS");
		?>
			
		<!-- ØVRE TEKST -->
		<h1>
		 Rediger billede
		</h1>
		<div class="broedtekst">
		Her kan du rette titel og beskrivelse for billedet.
		</div>
		<!-- /// -->
		<!-- FORM -->
		<form id="defaultForm" method="post" action="">
		<input type="hidden" name="dothis" value="" />
		<input type="hidden" name="billedtype" value="" />
		<input type="hidden" name="mmo" value="" />
		<div class="feltblok_header">
		 Rediger billede
		</div>
		<div class="feltblok_wrapper">
		 <?php
		  $foldername = returnFolderName($folderid, 1, "PICTUREARCHIVE_FOLDERS");
		  $filename = returnFileName($imageid, 1, "PICTUREARCHIVE_PICS");
		  $realfilename = returnFileName($imageid, 2, "PICTUREARCHIVE_PICS");
		  $image = "$picturearchive_Uploaddir/$foldername/$filename";
		  $imageAbs = "$picturearchive_UploaddirAbs/$foldername/$filename";
		  $info = getimagesize($image);
		 ?>
		 
		 <h2>ALT-text (ikke påkrævet):</h2>
		 <input type="text" class="inputfelt_kort" size="70" name="alttext" value="<?php echo $data["ALTTEXT"] ?>" />
		 <p class="feltkommentar">Billedets alt-tekst vises i den "gule boks", som fremkommer ved mouseover - og hvis billedet ikke kan indlæses. Bruges også til indeksering i søgemaskiner.</p>

		 <h2>Beskrivelse (ikke påkrævet):</h2>
		 <textarea name="description" class="inputfelt_kort" cols="70" rows="5"><?php echo $data["DESCRIPTION"] ?></textarea>
		<!--
		 <h2>Flyt billedet til en anden mappe:</h2>
		 	<select name="new_parent_id" class="inputfelt">
				<option value="DO_NOT_MOVE">Flyt ikke billedet</option>
				<?=""/*select_folders($_GET[folderid], "Flyt til: ")*/?>
			</select>
			-->
		<?php
		  echo "<h2>Valgt billede:</h2><div id='uploaded_image_preview'><img src='$imageAbs' border='0'></div><br />($realfilename)";
		?>
		</div>
		
		<div class="knapbar">
		 <input type="button" value="Afbryd" onclick="location='index.php?mainmenuoff=<?php echo $mainmenuoff ?>&amp;folder_id=<?=$_GET[folderid]?>&amp;content_identifier=<?php echo $content_identifier ?>'" />
		 <input type="button" value="Gem ændringer" onclick="this.form.dothis.value='gem_editbillede'; this.form.submit()" />
		</div>
		
		<!-- /// -->
		</form>
		
		<?php
}
?>

<?php
if ($dothis == "advarsel") {
		?>
		<div class="feltblok_header">
		 Du er ved at slette et billede, der er i brug
		</div>
		<div class="feltblok_wrapper">
		 Det billede, du vil slette, er tilsyneladende i brug på websitet i en eller flere nyheder, sider eller kalenderblade.
		 <br/><br/>
		 Vil du fortsætte med sletningen?
		</div>
		<div class="knapbar">
		 <input type="button" value="Nej, afbryd" onclick="location='index.php?content_identifier=<?php echo $content_identifier ?>&amp;folder_id=<?php echo $_GET[folder_id] ?>'" />
		 <input type="button" value="Ja, slet billedet" onclick="location='index.php?content_identifier=picturearchive&amp;dothis=sletbillede&amp;ignorealert=1&amp;folder_id=<?php echo $folder_id ?>&amp;imageid=<?php echo $imageid ?>'" />
		</div>
		
		<?php
}
?>