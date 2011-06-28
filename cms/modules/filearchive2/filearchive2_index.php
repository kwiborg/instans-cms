<?php
	if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
	if ($_GET[usermessage_ok] != "") {
		usermessage("usermessage_ok", $_GET[usermessage_ok]);
	}
	if ($_GET[usermessage_error] != "") {
		usermessage("usermessage_error", $_GET[usermessage_error]);
	}
?>
<?php
	if ($_GET["dothis"] == "oversigt" || !$_GET["dothis"]){
		unset($_SESSION[UPLOADED_FILES]);
		unset($_SESSION[FOLDER_ID]);
?>
	<h1>Filarkiv: Oversigt</h1>
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
	</form>
	<?php }
	if ($dothis == "opretnymappe") {
	?>
		<h1>Opret ny mappe</h1>
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
		 	Ny mappe som undermappe til: <span class="yellow"><?=returnFieldValue("FILEARCHIVE_FOLDERS", "TITLE", "ID", $_GET[parent_id])?></span>
		<?php } else { ?>
			Ny hovedmappe
		<?php } ?>
		</div>
		<div class="feltblok_wrapper">
		 <h2>Mappens navn:</h2>
		 <input type="text" name="mappenavn" class="inputfelt" value="<?php if ($entrydata) echo $entrydata["ENTRY_LABEL"] ?>">
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
if ($dothis == "editfil") {
	
	if(is_numeric($_GET[imageid])){		
		$arr_filestoedit[] = $_GET[imageid];
		$str_editedfiles = $_GET[imageid];
		$folderid = $_GET["folderid"];
	} elseif ($_SESSION[UPLOADED_FILES] != "") {
		$arr_filestoedit = explode("__", $_SESSION[UPLOADED_FILES]);
		$str_editedfiles = $_SESSION[UPLOADED_FILES];
		$folderid = $_SESSION["FOLDER_ID"];
	} else {
		echo "Ingen filer at redigere";
		exit;	
	}
	
	
	
?>	
	<h1>Rediger eller tilf&oslash;j titel, beskrivelse og type</h1>
	<div class="broedtekst">
	<br />Her kan du rette titel, beskrivelse og type for filerne.<br /><br />
	</div>
	<!-- /// -->
	<!-- FORM -->
	<form id="defaultForm" method="post" action="">
	<input type="hidden" name="dothis" value="gem_editfil" />
	<input type="hidden" name="folderid" value="<?=$folderid;?>" />
	<input type="hidden" id="editedfiles" name="editedfiles" value="<?=$str_editedfiles; ?>" />
		<div class="feltblok_wrapper">
		
	<?php
	
	$fil = 1;

	foreach ($arr_filestoedit as $key => $value) {
		$data = hentRow($value, "FILEARCHIVE_FILES");
		$realfilename = returnFileName($value, 2, "FILEARCHIVE_FILES");		
		
		$sql ="
		  		select 
		  			INTERNAL_NAME, DESCRIPTION, ID
		  		from 
		  			FILEARCHIVE_TYPE";
		  			
		$res = mysql_query($sql);
		
		echo "<h2>Fil ".$fil.": ".$realfilename."</h2>";
		echo "Titel:<br /> <input type='text' class='inputfelt_kort' size='70' name='alttext_".$value."' value='".$data["TITLE"]."' /><br />";
		echo "<br />Beskrivelse (ikke p&aring;kr&aelig;vet):<br />  <textarea name='description_".$value."' class='inputfelt_kort' cols='58' rows='2'>".$data["DESCRIPTION"]."</textarea><br />";
		echo "<br />Type: <select id='type_".$value."' name='type_".$value."'>";
				while($row = mysql_fetch_assoc($res)){
					echo "<option value='".$row[ID]."'";
		 			if($row[ID] == $data[FILETYPE_ID]){
		 				echo " selected='selected'";
		 			}
		 			echo ">".$row[DESCRIPTION];
		 			if (!strstr($row[INTERNAL_NAME], "generic")){
		 				echo " (".$row[INTERNAL_NAME].")";
		 			}
		 			echo "</option>";
		 		}
		 		echo "</select>";
		
		
		$fil++;
	}
	?>
		</div>
		<div class="knapbar">
		 <?php if ($_GET[context]!="upload") { ?>
		 	<input type="button" value="Afbryd" onclick="location='index.php?mainmenuoff=<?php echo $mainmenuoff ?>&amp;folder_id=<?=$_GET[folderid]?>&amp;content_identifier=<?php echo $content_identifier ?>'" />
		 <?php } ?>
		 <input type="button" value="Gem ændringer" onclick="this.form.submit()" />
		</div>
		<!-- /// -->
		</form>
		<?php
	}
	if ($dothis == "redigermappe") {
			// Get folder name from db
			$foldername = returnFieldValue("FILEARCHIVE_FOLDERS", "TITLE", "ID", $_GET[folderid]);
			$folderdesc = returnFieldValue("FILEARCHIVE_FOLDERS", "FOLDER_DESCRIPTION", "ID", $_GET[folderid]);
?>
			
		<!-- ØVRE TEKST -->
		<h1>
		 Rediger filmappe
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
		 <input type="text" name="folder_description" class="inputfelt" value="<?php echo $folderdesc; ?>" />
		<?php
			if (checkPermission("CMS_SETDATAPERMISSIONS_FILEARCHIVE_FOLDERS", false)) {
		?>
		 <h2>Flyt mappen til:</h2>
		 	<select name="new_parent_id" class="inputfelt">
				<option value="DO_NOT_MOVE">Flyt ikke mappen</option>
				<option value="0">Øverste niveau</option>
				<?=select_folders($_GET[folderid], " Undermappe til: ")?>
			</select>
			
		<?php
// 2008-05-28	-	Rettigheder for filmapper endnu ikke implementeret (MAP)
//				echo datapermission_set("DATA_FILEARCHIVE_USEINCMS", "FILEARCHIVE_FOLDERS", $_GET[folderid]);
//				echo datapermission_set("DATA_FILEARCHIVE_MANAGEFOLDER", "FILEARCHIVE_FOLDERS", $_GET[folderid]);
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
 if ($dothis == "opretfil") {
?>
	

</head>
<body>

	<div id="container">

		<h1>Upload filer til arkivet</h1>
		<div class="broedtekst">
		Find de filer du vil uploade via [Vælg filer]. De bliver derefter lagt i en kø, hvorefter de så kan uploades med et enkelt klik.<br /> 
		Bemærk: Du kan lægge alle typer filer i arkivet, som kunne være interessante for brugere 
		at downloade eller dele.<br /> Der kan max uploades uploades 5 filer ad gangen. 
		</div>
		
		<div class="feltblok_header">
 		Fil og beskrivelse af fil
		</div>
		<div class="feltblok_wrapper">
		
		<h2>Vælg filer:</h2>

		<iframe body bg="background-color: #eeeeee;" src="/cms/modules/filearchive2/filearchive2_fancyuploader.php?content_identifier=filearchive2&folderid=<?=$_GET['folderid']; ?>" height="450" width="749" frameborder="0" scrolling="no" >

		
		</iframe>

	</div>

<?php
}

?>