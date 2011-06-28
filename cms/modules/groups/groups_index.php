<?php
	if (!$_SESSION["CMS_USER"]){
		header("location: ../../login.php");
	}
	if ($dothis == "oversigt" || !$dothis){
?>
<h1>Grupper: Oversigt</h1>
<div class="broedtekst">
	Ændringer træder i kraft, når brugeren <strong>logger ind</strong> næste gang.
</div>
<form id="defaultForm" method="post" action="">
	<input type="hidden" name="dothis" value="" />
	<div class="feltblok_header">Gruppe-hierarki</div>
	<div class="feltblok_wrapper">
 		<?php
			if (!$sortby || !$sortdir){
				$sortby = "GROUP_NAME";
				$sortdir = "DESC";
			}
			$tomt_ikon = "<img src='images/piltom.gif' border='0'>";
			if ($sortdir == "DESC"){
				$sortdir = "ASC"; 
				$sortdir_changed=true;
				$ikon = "<img src='images/pilned.gif' border='0'>";
			}
			if ($sortdir == "ASC" && !$sortdir_changed){
				$sortdir = "DESC";
				$ikon = "<img src='images/pilop.gif' border='0'>";
			}  
			echo "
				<table class='oversigt'>
					<tr class='trtop'>
    					<td class='kolonnetitel'><a href='?content_identifier=groups&dothis=oversigt&sortby=GROUP_NAME&sortdir=$sortdir' class='kolonnetitel'>Gruppenavn&nbsp;" . (($sortby=="GROUP_NAME") ?  $ikon : $tomt_ikon) . "</td>
						<td class='kolonnetitel'><a href='?content_identifier=groups&dothis=oversigt&sortby=DESCRIPTION&sortdir=$sortdir' class='kolonnetitel'>Beskrivelse&nbsp;" . (($sortby=="DESCRIPTION") ?  $ikon : $tomt_ikon) . "</td>
						<td class='kolonnetitel'>Funktioner</td>
					</tr>
			";
			echo gruppeOversigt(0,0,0,1);
			echo "</table>\n";
		?>
	</div>
	<div class="knapbar">
		<input type="button" value="Opret ny gruppe" onclick="opretNyGruppe(0)">
	</div>
</form>
<?php
	}
?>

<?php
	if ($dothis == "opret" || $dothis == "rediger"){
?>
<h1>Opret ny gruppe</h1>
<div class="broedtekst"></div>
<form id="defaultForm" method="post" action="">
	<input type="hidden" name="dothis" value="" />
	<input type="hidden" name="det_nye_id" id="det_nye_id" value="<?php echo $id ?>" />
	<input type="hidden" name="parent_id" value="<?php echo $parent_id ?>" />
	<div class="feltblok_header">Gruppens stamdata</div>
	<div class="feltblok_wrapper">
		<h2>Gruppenavn:</h2>
		<input type="text" name="name" class="inputfelt" value="<?php echo $pagedata["GROUP_NAME"] ?>" />
		<h2>Beskrivelse:</h2>
		<input type="text" name="description" class="inputfelt" value="<?php echo $pagedata["DESCRIPTION"] ?>" />
	</div>
	<div class="feltblok_header">Gruppens rettigheder</div>
	<div class="feltblok_wrapper">
		<?=listGroupRights($id)?> 
	</div>
	<div class="feltblok_header">Andre indstillinger på gruppen</div>
	<div class="feltblok_wrapper">
		<h2>Adresselister og brugerlister</h2>
		<input type="checkbox" name="userlist_open" value="1" onclick="" <?=($pagedata["USERLIST_OPEN"] == 1 ? "checked" : "")?> />&nbsp;Der må vises adresseliste over brugere i denne gruppe<br/>
		Sortér som standard visninger efter: 
		<select class="inputfelt_kort" name="sort_by">
			<option value="FIRSTNAME" <?=($pagedata["SORT_BY"]=="FIRSTNAME" ? "selected" : "")?>>Fornavn</option>
			<option value="LASTNAME"> <?=($pagedata["SORT_BY"]=="LASTNAME" ? "selected" : "")?>Efternavn</option>
			<option value="USERNAME" <?=($pagedata["SORT_BY"]=="USERNAME" ? "selected" : "")?>>Brugernavn</option>
			<option value="POSITION" <?=($pagedata["SORT_BY"]=="POSITION" ? "selected" : "")?>>Position</option>
		</select>
		<h2>Registrering på website</h2>
		<input type="checkbox" name="registration_open" value="1" onclick="registration_toggle()" <?=($pagedata["REGISTRATION_OPEN"] == 1 ? "checked" : "")?> />&nbsp;Nye brugere kan tilmelde sig denne gruppe via websitet<br/>
		<input type="checkbox" name="editing_open" value="1" onclick="registration_toggle()" <?=($pagedata["EDITING_OPEN"] == 1 ? "checked" : "")?> />&nbsp;Eksisterende brugere kan rette deres data via websitet<br/>
		<h2>Tilgængelige felter ved registrering</h2>
		<?=groups_return_allowed_formfields($id)?>
		<h2>Notificering ved ny bruger</h2>
		Ved ny bruger sendes en e-mail til: <?php echo groups_notify_user($id, $pagedata["NOTIFY_USER_ID"]) ?>
 		<h2>Midlertidig gruppe</h2>
		Placér nye brugere midlertidigt i denne gruppe: <?php echo groups_temp_group($id, $pagedata["LANDING_GROUP_ID"]) ?>
		<p class="feltkommentar">
 			Bemærk: Den midlertidige gruppe bør være en gruppe med begrænsede/ingen rettigheder, således at nye
			brugere først får rettigheder, når de flyttes "på plads".
		</p>
	</div>
	<div class="knapbar">
		<input type="button" value="Afbryd" onclick="location='index.php?content_identifier=groups'" />
		<input type="button" value="Gem" onclick="verify()" />
	</div>
</form>
<?php
	}
?>

<?php
	if ($dothis == "medlemmer"){
?>
<h1>Medlemmer</h1>
<form id="defaultForm" method="post" action="">
	<input type="hidden" name="dothis" value="" />
	<input type="hidden" name="det_nye_id" id="det_nye_id" value="<?php echo $id ?>" />
	<div class="feltblok_header">Medlemmer af <span class="yellow"><?php echo returnGroupName($id) ?></span></div>
	<div class="feltblok_wrapper" id="sortme">
		<?=new_show_groupmembers($id)?>
	</div>
	<div class="feltblok_header">Tilføj medlemmer til <span class="yellow"><?php echo returnGroupName($id) ?></span></div>
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
		if (isset($_GET[group_id]) || trim($_GET[usersearch]) != ""){
				echo "<h2>Fundne brugere, som ikke i forvejen er medlem af gruppen:</h2>";
				echo new_user_overview($_GET[group_id], $_GET[usersearch], "groupmembers");
				echo "<p class='feltkommentar'>Tip: Du kan klikke på kolonne-navnene øverst i listen for at sortere listen efter den ønskede kolonne.</p>";
			}
		?>
	</div>
	<div class="knapbar">
		<input type="button" value="Tilbage til gruppeoversigt" onclick="location='index.php?content_identifier=groups'" />
	</div>
</form>
<script>
	if ($("sortme")){
		Sortable.create('sortme', {tag:'div', constraint:'vertical',  onUpdate:updateSortable});
	}
</script>
<?php
	}
?>
