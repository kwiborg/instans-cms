<?php
header("Content-type: text/html; charset=UTF-8");
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/common.inc.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/modules/formeditor2/formeditor2_common.inc.php');
checkLoggedIn();

switch ($_REQUEST['do']) {
	case 'ajax_returnAttachedForm':
		echo returnAttachedForm($_POST["page_id"]);
		break;
	case 'ajax_returnAvailableForms':
		echo returnAvailableForms();
		break;
	case 'ajax_addFormToPage':
		echo addForm($_POST[page_id], $_POST[form_id], "PAGES", $_POST[inline]);
		break;
	case 'ajax_removeFormFromPage':
		echo removeForm($_POST[page_id], "PAGES", $_POST[form_id]);
		break;
}

function removeForm($page_id, $tabel, $form_id='') {
	$sql = "delete from PAGES_FORMS where PAGE_ID='$page_id' and TABEL='$tabel'";
	if ($form_id != "") {
		$sql .= " and FORM_ID='$form_id' ";
	}
	$result = mysql_query($sql);
	if (!$result = mysql_query($sql)) {
		echo "Der opstod en fejl og formularen blev ikke fjernet fra siden.";
	}
}

function addForm($id, $formid, $tabel, $inline) {
	// Delete existing form attachments
	removeForm($id, $tabel);

	// Add new form attachment
	$sql = "insert into PAGES_FORMS (PAGE_ID, FORM_ID, TABEL, INLINE) values ($id, $formid, '$tabel', '$inline')";
	if ($result = mysql_query($sql)) {
		echo "ok";
	} else {
		echo "Der opstod en fejl og formularen blev ikke tilføjet siden.";
	}
} 

function returnAvailableForms() {
	# Used from Page editing
	$html = formOversigt("attachtopage");
	return $html;
}

function returnAttachedForm($page_id) {
	# Used from Page editing
	$sql = "select DEFINED_FORMS.ID, DEFINED_FORMS.TITLE 
				from DEFINED_FORMS, PAGES_FORMS 
				where DEFINED_FORMS.ID = PAGES_FORMS.FORM_ID 
				and PAGES_FORMS.PAGE_ID = '$page_id'";
	$result = mysql_query($sql);
	$n = mysql_num_rows($result);
	if ($n > 0) {
		$has_form = "yes";
	} else {
		$has_form = "no";
	}		
	$html = "<input type='hidden' id='page_has_form' value='$has_form' />";

	if ($n > 0) {
		$html .="<table class='oversigt'>
					<tr>
					<td class='kolonnetitel'>Formular der er vises på siden</td>
					<td class='kolonnetitel'>Funktioner</td>
				</tr>";
		while ($row = mysql_fetch_array($result)) {
			$i++;
			$c = $i % 2 + 1;
			$html .= "<tr>
						<td>".$row[TITLE]."</td>
						<td width='15%'><input type='button' class='lilleknap' value='Fjern' onclick='removeForm(".$row[ID].")'>
						</td>
					</tr>";
		}
	} else {
		$html .= "<table class='oversigt'>
				<tr>
					<td>Der er ikke vedhæftet nogen formular til siden.</td>
				</tr>";
	}
	$html .= "</table>";
	return $html;
}
?>