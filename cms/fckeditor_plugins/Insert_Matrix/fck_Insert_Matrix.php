<?php 
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/common.inc.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/modules/bookmaker/bookmaker_common.inc.php');
checkLoggedIn();

if ($_GET[current_book_id] == "") {
	$current_book_id = $_SESSION[current_book_id];
} else {
	$current_book_id = $_GET[current_book_id];
}



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta content="noindex, nofollow" name="robots">
		<script type="text/javascript">
			// Here goes population of book_matrices array && build matrixselector
			var book_matrices = new Array();
<?php
$msql = "select ID, BOOK_ID, MATRIX_TITLE, MATRIX_CONTENT from BOOKMATRICES where BOOK_ID = $current_book_id and DELETED = 'N' order by MATRIX_TITLE";
if (@$mresult = mysql_query($msql)) {
	$matrixselect = "<select id='select_matrix' name='select_matrix' size='1'>";
	$matrixselect .= "<option value=''>Vælg matrix</option>";
	if (db_hasrows()) {
		// For hver matrix
		while ($mrow = mysql_fetch_array($mresult)) {
			$matrixselect .= "<option value='$mrow[ID]'>$mrow[MATRIX_TITLE]</option>";
			echo "\nbook_matrices[$mrow[ID]] = new Array();";
			echo "\nbook_matrices[$mrow[ID]][0] = '$mrow[BOOK_ID]';";
			echo "\nbook_matrices[$mrow[ID]][1] = '$mrow[MATRIX_TITLE]';";
			$mrow[MATRIX_CONTENT] = str_replace("\n", "", $mrow[MATRIX_CONTENT]);
			$mrow[MATRIX_CONTENT] = str_replace("\r", "", $mrow[MATRIX_CONTENT]);
			$mrow[MATRIX_CONTENT] = str_replace("\t", "", $mrow[MATRIX_CONTENT]);
			$mrow[MATRIX_CONTENT] = addslashes($mrow[MATRIX_CONTENT]);
			echo "\nbook_matrices[$mrow[ID]][2] = '$mrow[MATRIX_CONTENT]';";
		}
	} else {
		$matrixselect .= "<option>Der er ikke oprettet matricer til bogen</option>";
	}
	$matrixselect .= "</select>";
}

// Build bookselector
$sql = "select ID, BOOKTITLE from BOOKS where DELETED = 'N' order by BOOKTITLE";
if (@$result = mysql_query($sql)) {
	$bookselect = "<select id='current_book_id' name='current_book_id' size='1' onchange='submit(this);'>";
	if (db_hasrows()) {
		// For hver bog
		while ($row = mysql_fetch_array($result)) {
			$bookselect .= "<option value='$row[ID]'";
			if ($current_book_id == $row[ID]) {
				$bookselect .= " selected='selected'";
			}
			$bookselect .= ">$row[BOOKTITLE]</option>";
		}
	} else {
		$bookselect .= "<option>Ingen b&oslash;ger i databasen</option>";
	}
	$bookselect .= "</select>";
}
?>

var oEditor = window.parent.InnerDialogLoaded() ;
var FCKLang = oEditor.FCKLang ;
var FCK_insertMatrix = oEditor.FCK_insertMatrix ;

window.onload = function () {
	// First of all, translate the dialog box texts
	oEditor.FCKLanguageManager.TranslatePage( document ) ;

	// Show the "Ok" button.
	window.parent.SetOkButton( true ) ;
}

function Ok() {
	var selected_matrix = document.getElementById('select_matrix').value;
	if (selected_matrix == "") {
		alert("Du skal vælge en matrix");
		return false;
	}
	
	var selected_matrix_content = book_matrices[selected_matrix][2];
	FCK_insertMatrix.Insert(selected_matrix_content);
	return true ;
}

	</script>
	</head>
	<body scroll="no" style="OVERFLOW: hidden">
		<table height="100%" cellSpacing="0" cellPadding="2" width="100%" border="0">
			<tr>
				<td>
					<form action='' method='GET' id='matrixSelector'>
					<table cellSpacing="0" cellPadding="0" align="center" border="0">
						<tr>
							<td align="right">
								Vælg matrix fra bogen:&nbsp;
							</td>
							<td>
								<?php echo $bookselect; ?>
							</td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
						<tr>
							<td align="right">
								Vælg matrix:&nbsp;
							</td>
							<td>
								<?php echo $matrixselect; ?>
							</td>
						</tr>
					</table>
					</form>
					<p><strong>Bemærk:</strong> Matricerne ofte er meget brede. Du bør derfor undgå at placere indhold i sidekolonnen, når du viser en matrix i hovedkolonnen. Ellers kan du risikere at layoutet ser forkert ud.</p>
				</td>
			</tr>
		</table>
	</body>
</html>