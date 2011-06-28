<?php
 if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");

echo editform();


function pagetab() {
	// Get menus for current site
	$sql = "select * from MENUS where SITE_ID in (0,'$_SESSION[SELECTED_SITE]')";
	$res = mysql_query($sql);
	if (mysql_num_rows($res)>0) {
		while ($row = mysql_fetch_assoc($res)) {
			$str_label = "Brugeren har adgang til menuen: \"".$row[MENU_TITLE]."\"";
			$html .= datapermission_set("DATA_CMS_MENU_ACCESS", "MENUS", $row[MENU_ID], $str_label);
		}
	} else {
		$html .= "<p>Der er ingen menuer på det site, du redigerer.</p>";
	}
	return $html;
}
function newstab() {
	// Get newsarchives for current site
	$sql = "select * from NEWSFEEDS where SITE_ID in (0,'$_SESSION[SELECTED_SITE]')";
	$res = mysql_query($sql);
	if (mysql_num_rows($res)>0) {
		while ($row = mysql_fetch_assoc($res)) {
			$str_label = "Brugeren har adgang til nyhedsarkivet: \"".$row[NAME]."\"";
			$html .= datapermission_set("DATA_CMS_NEWSARCHIVE_ACCESS", "NEWSFEEDS", $row[ID], $str_label);
		}
	} else {
		$html .= "<p>Der er ingen nyhedsarkiver på det site, du redigerer.</p>";
	}
	return $html;
}
function calendartab() {
	// Get calendars for current site
	$sql = "select * from CALENDARS where SITE_ID in (0,'$_SESSION[SELECTED_SITE]')";
	$res = mysql_query($sql);
	if (mysql_num_rows($res)>0) {
		while ($row = mysql_fetch_assoc($res)) {
			$str_label = "Brugeren har adgang til kalenderen: \"".$row[NAME]."\"";
			$html .= datapermission_set("DATA_CMS_CALENDAR_ACCESS", "CALENDARS", $row[ID], $str_label);
		}
	} else {
		$html .= "<p>Der er ingen kalendere på det site, du redigerer.</p>";
	}
	return $html;
}



function editform() {
	$html = "<h1>Generelle rettigheder</h1>";
	$html .= "<div class='broedtekst'>Her kan du angive rettigheder for adgang til hjemmesidens indhold på arkiv-niveau. Bemærk at rettigheder ændres, efterhånden som du udfører dem. Der er derfor ikke nogen \"Gem\"-knap på denne side.</div>";
	
	$html .= "<form id='defaultForm' method='post' action=''><br />
		<ul id='tablist'>
			<li><a href='#' class='current' onClick='return expandcontent(\"sc1\", this)'>Menuer</a></li>
			<li><a href='#' onClick='return expandcontent(\"sc2\", this)'>Nyhedsarkiver</a></li>
			<li><a href='#' onClick='return expandcontent(\"sc3\", this)'>Kalendere</a></li>
		</ul>";
	
	$html .= "<div id='tabcontentcontainer'>";
	$html .= "<h2>Om rettigheder</h2>";
	$html .= "<p>Hvis der ikke er sat specifikke rettigheder, har alle ret til at udføre den beskrevne funktion. Dog kræver det altid adgang til CMS + adgang til det relevante modul.</p>";
	$html .= "\n\t<div id='sc1' class='tabcontent'>";
	$html .= pagetab();
	$html .= "</div>";
	$html .= "\n\t<div id='sc2' class='tabcontent'>";
	$html .= newstab();
	$html .= "</div>";
	$html .= "\n\t<div id='sc3' class='tabcontent'>";
	$html .= calendartab();
	$html .= "</div>";
	
	$html .= "</div>"; // end tabcontainer
/*
	// All will be AJAX driven, I assume
	$html .= 	"<div class='knapbar'>
					<input type='button' value='Afbryd' onclick='location='index.php?content_identifier=permissions' />
					<!--<input type='button' value='Gem' onclick='verify()' />-->
				</div>";
*/
	$html .= "</form>"; // end defaultForm
	return $html;
}


?>