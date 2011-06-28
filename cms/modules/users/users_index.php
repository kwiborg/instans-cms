<?php
	if (!$_SESSION["CMS_USER"]){
 		header("location: ../../login.php");	
	}
 	if ($dothis == "oversigt" || !$dothis){
?>
<h1>Brugere: Oversigt</h1>
<form id="defaultForm" method="post" action="">
	<input type="hidden" name="dothis" value="" />
	<div class="feltblok_header"> 
	<?php
		if (isset($_GET[group_id]) && $_GET[group_id] > 0){
			echo "Brugere i gruppen <span class='yellow'>".returnFieldValue("GROUPS", "GROUP_NAME", "ID", $_GET[group_id])."</span>";
		} else if (isset($_GET[group_id]) && $_GET[group_id] == 0){
			echo "Brugere i alle grupper";
		} else if ($_GET[usersearch]){
			echo "Brugere, der matcher en søgning på: <span class='yellow'>$_GET[usersearch]</span>";
		} else {
			echo "Definer søgekriterier nedenfor";
		}
	?>
	</div>
	<div class="feltblok_wrapper">
		<h2>Vis kun brugere fra gruppen:</h2>
		<select class='inputfelt_kort' id="show_group_id" name="show_group_id">
			<?=group_selector_items($_GET[group_id])?>
			<option value="0">Vis samtlige brugere i alle grupper</option>
		</select>
		<input type='button' value='Opdater visning' class='lilleknap' onclick="this.form.dothis.value='switch_group'; this.form.submit()" />
		<h2>Søg efter brugere på navn, e-mail eller brugernavn:</h2>
		<input class='inputfelt_kort' onkeypress="if (this.form.usersearch.value){this.form.dothis.value='usersearch'; entsub(this.form)}" type='text' name='usersearch' id='usersearch' size='40' value='<?=$_GET[usersearch]?>' />&nbsp;<input type='button' value='Søg' class='lilleknap' onclick="if (this.form.usersearch.value){this.form.dothis.value='usersearch'; this.form.submit()}" />
		<?php
			if (!$sortby || !$sortdir){
				$sortby = "ID";
				$sortdir = "DESC";
			}
			if (isset($_GET[group_id]) || trim($_GET[usersearch]) != ""){
				echo "<h2>Fundne brugere:</h2>";
				echo new_user_overview($_GET[group_id], $_GET[usersearch]);
				echo "<p class='feltkommentar'>Tip: Du kan klikke på kolonne-navnene øverst i listen for at sortere listen efter den ønskede kolonne.</p>";
			}
		?>
	</div>
	<div class="knapbar">
		<input type="button" value="Opret ny bruger" onclick="opretNy()">
	</div>
</form>
<script type="text/javascript">
	<?php if($infoResent) echo "alert('Oplysningerne blev sendt.');"; ?>
</script>
<?php 
	} 
?>

<?php
	if ($dothis == "opret" || $dothis == "rediger"){
?>
<h1><?=($_GET[dothis] == "opret" ? "Opret ny bruger" : "Rediger bruger: $pagedata[FIRSTNAME] $pagedata[LASTNAME]")?></h1><br/>
<form id="defaultForm" method="post" action="">
	<ul id="tablist">
		<li><a href="#" class="current" onclick="return expandcontent('sc1', this)">Brugernavn og password</a></li>
		<li><a href="#" onclick="return expandcontent('sc3', this)">Stamdata</a></li>
		<li><a href="#" onclick="return expandcontent('sc4', this)">Virksomhedsdata</a></li>
		<li><a href="#" onclick="return expandcontent('sc5', this)">Uddybende data</a></li>
	</ul>
	<input type="hidden" name="dothis" value="" />
	<input type="hidden" name="backtosearch" value="<?=$_GET[backtosearch]?>" />
	<input type="hidden" name="backtogroup" value="<?=$_GET[backtogroup]?>" />
	<input type="hidden" name="mode" value="<?php echo $dothis ?>" />
	<input type="hidden" name="det_nye_id" value="<?php echo $id ?>" />
	<?php if ($userExistError){ ?>
		<div class="feltblok_header_alert">
			Fejl: Der eksisterer allerede en bruger med det valgte navn "<?php echo $_POST[username]?>"
		</div>
		<div class="feltblok_wrapper">
			Vælg venligst et andet brugernavn og prøv igen.
		</div>
	<?php } ?>
	<div id="tabcontentcontainer">
		<div id="sc1" class="tabcontent">
				<h2>Brugernavn:</h2>
				<input type="text" name="username" class="inputfelt_kort" value="<?=$pagedata["USERNAME"]?>" onblur="ajax_check_user_exists(this.value, document.forms[0].det_nye_id.value)" />
				<p class="feltkommentar">(Kun bogstaver, tal og underscore [ _ ] - ingen danske tegn [æ ø å Æ Ø Å]).</p>
				<h2>Password (to gange):</h2>
				<input type="password" name="password1" class="inputfelt_kort" value="<?=$pagedata["PASSWORD"]?>" />&nbsp;&nbsp;
				<input type="password" name="password2" class="inputfelt_kort" value="<?=$pagedata["PASSWORD"]?>" />
				<p class="feltkommentar">(Kun bogstaver, tal og underscore [ _ ] - ingen danske tegn [æ ø å Æ Ø Å]).</p>
				<h2>
					<?php
						if ($_GET["dothis"] == "rediger"){
							echo "Vælg hvilke grupper, ".returnUserName($id)." er medlem af:";
						} else {
							echo "Vælg hvilke grupper, den nye bruger er medlem af:";
						}
		 			?>
				</h2>
	 			<?php  
					gruppeOversigtShort(0,0,0,1,$id);
	  				echo "
	  					<script>
							boxes = new Array();
							$script;
						</script>
	  				";
	 			?>
				<h2>Giv besked om ændringer?</h2>
				<input type="hidden" name="notify_user_res" value="1">
				<input type="checkbox" name="notify_user">&nbsp;Send en mail til brugeren om, at vedkommende er blevet oprettet/redigeret, når der trykkes "Gem".
				<h2>Andre indstillinger</h2>
				<input type="checkbox" name="never_public" value="1" <?=($pagedata["NEVER_PUBLIC"] ? "checked" : "")?>>&nbsp;Vis ALDRIG denne bruger i kontaktsider eller brugerlister på websitet.
		</div>
		<div id="sc3" class="tabcontent">
			<h2>Fornavn og evt. mellemnavn:</h2>
			 <input type="text" name="firstname" class="inputfelt" value="<?=$pagedata["FIRSTNAME"]?>" />
			<h2>Efternavn:</h2>
			 <input type="text" name="lastname" class="inputfelt" value="<?=$pagedata["LASTNAME"]?>" />
			<h2>Initialer:</h2>
			 <input type="text" name="initials" class="inputfelt_kort" value="<?=$pagedata["INITIALS"]?>" />
			<h2>Firma:</h2>
			 <input type="text" name="company" class="inputfelt" value="<?=$pagedata["COMPANY"]?>" />
			<h2>Adresse:</h2>
			 <input type="text" name="address" class="inputfelt" value="<?=$pagedata["ADDRESS"]?>" />
			<h2>Postnummer:</h2>
			 <input type="text" name="zipcode" class="inputfelt_kort" value="<?=$pagedata["ZIPCODE"]?>" />
			<h2>By:</h2>
			 <input type="text" name="city" class="inputfelt" value="<?=$pagedata["CITY"]?>" />
            <h2>Land:</h2>
             <input type="text" name="country" class="inputfelt" value="<?=$pagedata["COUNTRY"]?>" />
			<h2>Telefon (fastnet):</h2>
			 <input type="text" name="phone" class="inputfelt_kort" value="<?=$pagedata["PHONE"]?>" />
			<h2>Telefon (mobil):</h2>
			 <input type="text" name="cellphone" class="inputfelt_kort" value="<?=$pagedata["CELLPHONE"]?>" />
			<h2>E-mail:</h2>
			<input type="text" name="email" class="inputfelt" value="<?=$pagedata["EMAIL"]?>" />
			<h2>Fødselsdato (DD-MM-ÅÅÅÅ):</h2>
			<input type="text" name="date_of_birth" class="inputfelt_kort" value="<?php echo ($pagedata["DATE_OF_BIRTH"] != "0000-00-00" ? reverseDate($pagedata["DATE_OF_BIRTH"]) : "") ?>" />
			<h2>Billede:</h2>
			<input type="hidden" id="imageid" name="imageid" size="2" value="<?=$pagedata["IMAGE_ID"]?>" />
			<input type="hidden" id="image_url" name="image_url" size="2" value="" />
			<?php 
				$imageid = $pagedata["IMAGE_ID"];
				if (is_numeric($imageid)) {
			  		$image_url = returnImageUrl($imageid);
					$thumburl = explode("/",$image_url);
					$lastpart = array_pop($thumburl);
					$thumburl[] = "thumbs";
					$thumburl[] = $lastpart;
					$thumburl = implode("/", $thumburl); 
				}
				echo "<img id='imgthumb' src='$thumburl' border='1'";
				if ($image_url == "") {
					echo " style='display:none;'";
				}
				echo "/>";
		
				echo "<input type='button' id='selectImageButton' name='newsletter_itemimage_selectbutton' class='lilleknap' value='Vælg billede' onclick='selectImage($folder_id);' /><br />
											<div id='newsletter_edititem_showimage'>
											</div>
					<div id='selectImageDiv'></div>						
				";
			?>	
		</div>
		<div id="sc4" class="tabcontent">
			<h2>Afdeling:</h2>
			 <input type="text" name="department" class="inputfelt" value="<?=$pagedata["DEPARTMENT"]?>" />
			<h2>Funktion eller jobtitel:</h2>
			 <input type="text" name="job_title" class="inputfelt" value="<?=$pagedata["JOB_TITLE"]?>" />
			 <?php
			 	if ($dbfields_vary["JOB_TITLE"]){
					echo varied_fields_per_group($_GET["id"], "JOB_TITLE", "USERS");
				}
			 ?>
			<h2>Ansættelsesdato (DD-MM-ÅÅÅÅ):</h2>
			 <input type="text" name="date_of_hiring" class="inputfelt" value="<?php echo ($pagedata["DATE_OF_HIRING"] != "0000-00-00" ? reverseDate($pagedata["DATE_OF_HIRING"]) : "") ?>" />
		</div>
		<div id="sc5" class="tabcontent">
			<h2>Personbeskrivelse:</h2>
 			<textarea name="cv" cols="70" rows="10"><?=$pagedata["CV"] ?></textarea>
		</div>
		<div class="knapbar">
	 		<input type="button" value="Afbryd" onclick="location='index.php?content_identifier=users&group_id=<?=$_GET[backtogroup]?>&usersearch=<?=$_GET[backtosearch]?>'" />
 			<input type="button" value="Gem" onclick="verify()" />
		</div>
	</div>
</form>
<?php
	}
?>



