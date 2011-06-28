<?php
// For benefit of buildHTMLForPDF function
// global $numberTo, $allContent, $stamdata, $screenCSS;

/////////////// GENERELLE TING ///////////////
// Vi er i Danmark
setlocale(LC_ALL, 'da_DK.UTF-8');

// What to do on 
$_SERVER[baseurl] = "index.php?content_identifier=bookmaker";

# DATABASE FUNCTIONS
function __________anchor_database_functions() {};

function db_hentTabel($tabel) {
	# Function to get db table
	# Returns db handle on result
   $sql = "select * from $tabel";
   $result = mysql_query( $sql) or die(db_errorhandler("Funktionen '".__FUNCTION__."' fejlede. Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
   return $result;
}

function db_getSingleValue($table, $column, $id, $idcolumn = 'ID') {
	# Returns value of row ID=$id, $column on $table
	# outional argument $idcolumn
	$sql = "select $column from $table where ".$idcolumn."='".$id."' LIMIT 1";
//	echo $sql;
	$result = mysql_query($sql) or die(db_errorhandler("Funktionen '".__FUNCTION__."' fejlede. Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
	$row = mysql_fetch_row($result);
	return $row[0];
}

/*
// 2007-04-17	-	Function exists in sharedfunctions
function db_safedata($value) {
	//	echo "SAFECHECK: $value";
	# Returns safely quoted values to prevent evil database injection
	# Requires connection to the database established via mysql_connect()
	if ($value == '') {
		return $value;
	}
	if (get_magic_quotes_gpc()) {
		$value = stripslashes($value);
	}
	if (is_numeric($value)) {
		return $value;
	} else {
		if ($value = mysql_real_escape_string($value)) {
			return $value;
		} else {
			die(db_errorhandler("Funktionen '".__FUNCTION__."' fejlede med værdien '$value'. Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
		}
	}
}
*/
/*
// 2007-04-17	-	Function exists in sharedfunctions
function db_errorhandler($errormessage) {
	# Temp function to handle sql errors.
	# To be extended with logging
	usermessage("usermessage_error", $errormessage);
	echo mysql_error();
}
*/
function db_returnParentId($section_id) {
	// Takes section id and returns array with information on the book to which the section belongs
	$sql = "select PARENT_ID from BOOKSECTIONS where ID = $section_id";
	if (@$result = mysql_query($sql)) {
		if (db_hasrows()) {
			$row = mysql_fetch_array($result);
			return $row[PARENT_ID];
		} else {
			// No rows found
			die(db_errorhandler("Funktionen ".__FUNCTION__." kunne ikke hente nødvendig boginfo! Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
		}
	} else {
		// DB query failed
		die(db_errorhandler("Funktionen ".__FUNCTION__." kunne ikke hente nødvendig boginfo! Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
	}
}

function db_returnBookfromSectionId($section_id) {
	// Takes section id and returns array with information on the book to which the section belongs
	$sql = "select BOOK_ID from BOOKSECTIONS where ID = $section_id";
	if (@$result = mysql_query($sql)) {
		if (db_hasrows()) {
			$row = mysql_fetch_array($result);
			return $row[BOOK_ID];
		} else {
			// No rows found
			die(db_errorhandler("Funktionen ".__FUNCTION__." kunne ikke hente nødvendig boginfo! Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
		}
	} else {
		// DB query failed
		die(db_errorhandler("Funktionen ".__FUNCTION__." kunne ikke hente nødvendig boginfo! Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
	}
}

function db_returnBookfromMatrixId($matrix_id) {
	// Takes matrix id and returns array with information on the book to which the matrix belongs
	$sql = "select BOOK_ID from BOOKMATRICES where ID = $matrix_id";
	if (@$result = mysql_query($sql)) {
		if (db_hasrows()) {
			$row = mysql_fetch_array($result);
			return $row[BOOK_ID];
		} else {
			// No rows found
			die(db_errorhandler("Funktionen ".__FUNCTION__." kunne ikke hente nødvendig boginfo! Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
		}
	} else {
		// DB query failed
		die(db_errorhandler("Funktionen ".__FUNCTION__." kunne ikke hente nødvendig boginfo! Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
	}
}


# DISPLAY FUNCTIONS
function __________anchor_display_functions() {};

function displayUpdateTime($timestamp) {
	# Return nicely formatted updateTime in the format of:
	# 6. Februar 2006 kl. 14:48:14
	if ($timestamp != '') {
		return strftime("%e. %b %Y, %H:%M", $timestamp);
	}
}

function createSelectMonth($selectname, $month = 0, $class = "inputfelt_kort") {
	# Will return select form element used for choosing month
	# Use $selectname to set name/id of form element
	# Call with $month = 1-12 to preselect month. Use blank argument for current month
	if ($month == 0) {
		$month = date('n');
	}
	return '<select id="'.$selectname.'" name="'.$selectname.'" size="1" class="'.$class.'">
	<option value="1"'.(($month == 1) ? ' selected="selected"' : '').'>Januar</option>
	<option value="2"'.(($month == 2) ? ' selected="selected"' : '').'>Februar</option>
	<option value="3"'.(($month == 3) ? ' selected="selected"' : '').'>Marts</option>
	<option value="4"'.(($month == 4) ? ' selected="selected"' : '').'>April</option>
	<option value="5"'.(($month == 5) ? ' selected="selected"' : '').'>Maj</option>
	<option value="6"'.(($month == 6) ? ' selected="selected"' : '').'>Juni</option>
	<option value="7"'.(($month == 7) ? ' selected="selected"' : '').'>Juli</option>
	<option value="8"'.(($month == 8) ? ' selected="selected"' : '').'>August</option>
	<option value="9"'.(($month == 9) ? ' selected="selected"' : '').'>September</option>
	<option value="10"'.(($month == 10) ? ' selected="selected"' : '').'>Oktober</option>
	<option value="11"'.(($month == 11) ? ' selected="selected"' : '').'>November</option>
	<option value="12"'.(($month == 12) ? ' selected="selected"' : '').'>December</option>
</select>';
}

function createSelectSectionNumbering($selectname, $value = 3, $class = "inputfelt_kort") {
	# Will return select form element used for choosing section numbering
	# Use $selectname to set name/id of form element
	# Call with $value = 0-6 to preselect.
	return '<select id="'.$selectname.'" name="'.$selectname.'" size="1" class="'.$class.'">
	<option value="0"'.(($value == 0) ? ' selected="selected"' : '').'>Ingen nummerering</option>
	<option value="1"'.(($value == 1) ? ' selected="selected"' : '').'>Kun niveau 1 (kapitler)</option>
	<option value="2"'.(($value == 2) ? ' selected="selected"' : '').'>Niveau 1 til 2</option>
	<option value="3"'.(($value == 3) ? ' selected="selected"' : '').'>Niveau 1 til 3</option>
	<option value="4"'.(($value == 4) ? ' selected="selected"' : '').'>Niveau 1 til 4</option>
	<option value="5"'.(($value == 5) ? ' selected="selected"' : '').'>Niveau 1 til 5</option>
	<option value="6"'.(($value == 6) ? ' selected="selected"' : '').'>Niveau 1 til 6</option>
</select>';
}

function createSelectSectionCollating($selectname, $value = 3, $class = "inputfelt_kort") {
	# Will return select form element used for choosing section collating
	# Use $selectname to set name/id of form element
	# Call with $value = 0-6 to preselect.
	return '<select id="'.$selectname.'" name="'.$selectname.'" size="1" class="'.$class.'">
	<option value="0"'.(($value == 0) ? ' selected="selected"' : '').'>Alle afsnit vises på egen side</option>
	<option value="1"'.(($value == 1) ? ' selected="selected"' : '').'>Afsnit under niveau 1 samles</option>
	<option value="2"'.(($value == 2) ? ' selected="selected"' : '').'>Afsnit under niveau 2 samles</option>
	<option value="3"'.(($value == 3) ? ' selected="selected"' : '').'>Afsnit under niveau 3 samles</option>
	<option value="4"'.(($value == 4) ? ' selected="selected"' : '').'>Afsnit under niveau 4 samles</option>
	<option value="5"'.(($value == 5) ? ' selected="selected"' : '').'>Afsnit under niveau 5 samles</option>
	<option value="6"'.(($value == 6) ? ' selected="selected"' : '').'>Afsnit under niveau 6 samles</option>
</select>';
}

function createSelectFonts($selectname, $value, $class = "inputfelt_kort") {
	# Will return select form element used for choosing fonts
	# Use $selectname to set name/id of form element
	# Call with $value to preselect.
	return '<select id="'.$selectname.'" name="'.$selectname.'" size="1" class="'.$class.'">
	<option value="verdana"'.(($value == "verdana") ? ' selected="selected"' : '').'>Verdana (vises som Helvetica i PDF)</option>
	<option value="arial"'.(($value == "arial") ? ' selected="selected"' : '').'>Arial</option>
	<option value="helvetica"'.(($value == "helvetica") ? ' selected="selected"' : '').'>Helvetica</option>
	<option value="times"'.(($value == "times") ? ' selected="selected"' : '').'>Times</option>
	<option value="georgia"'.(($value == "georgia") ? ' selected="selected"' : '').'>Georgia (vises som Times i PDF)</option>
	<option value="courier"'.(($value == "courier") ? ' selected="selected"' : '').'>Courier</option>
</select>';
}
/*
function createCheckbox($label, $selectname, $value = "Y", $checked = "N", $onClickFunction = "", $coverimage_url_disabled="") {
	# Will return checkbox form element
	# Label is the text appearing after the checkbox
	# Use $selectname to set name/id of form element
	# Call with $value to set the value posted if the box is checked
	# Call with $checked = "Y" to preselect checkbox
	$cb = '<input type="checkbox" id="'.$selectname.'" name="'.$selectname.'" value="'.$value.'"';
	if ($onClickFunction != "") {
		$cb .= ' onclick="'.$onClickFunction.'"';
	}
	if ($checked == "Y") {
		$cb .= ' checked="checked"';
	}
	if ($coverimage_url_disabled != "") {
		$cb .= ' disabled="disabled"';
	}
	$cb .= "/>&nbsp;".$label;
	return $cb;
}

function createSelectYesNo($selectname, $value = 0, $class="inputfelt_kort") {
	# Will return select form element used for choosing yes / no 
	# Use $selectname to set name/id of form element
	# Call with $value = 0-1 preselect state. Use blank argument for 0 (no)
	return '<select id="'.$selectname.'" name="'.$selectname.'" class="'.$class.'" size="1">
	<option value="0"'.(($value == 0) ? ' selected="selected"' : '').'>Nej</option>
	<option value="1"'.(($value == 1) ? ' selected="selected"' : '').'>Ja</option>
	</select>';
}
*/
function returnJaNej($number) {
	switch ($number) {
		case '0':
			return("Nej");
			break;
		case '1':
			return("Ja");
			break;
	}
}

function returnMonthName($monthNumber) {
	switch ($monthNumber) {
		case '1':
			return("Januar");
			break;
		case '2':
			return("Februar");
			break;
		case '3':
			return("Marts");
			break;
		case '4':
			return("April");
			break;
		case '5':
			return("Maj");
			break;
		case '6':
			return("Juni");
			break;
		case '7':
			return("Juli");
			break;
		case '8':
			return("August");
			break;
		case '9':
			return("September");
			break;
		case '10':
			return("Oktober");
			break;
		case '11':
			return("November");
			break;
		case '12':
			return("December");
			break;
	}
}

function returnSectionType($parentid, $variant = "ubestemt") {
	// Function to determine whether section is "afsnit" or "chapter"
	// $variant: bestemt, ubestemt
	if ($parentid == 0) {
		if ($variant == "bestemt") {
			return "Kapitlet";
		} else {
			return "Kapitel";
		}
	} else {
		if ($variant == "bestemt") {
			return "Afsnittet";
		} else {
			return "Afsnit";
		}	
	}
}

function add_book() {
	echo '<h1>Bogprojekter: Opret ny bog</h1>';
	echo book_form();
}

function add_section($book_id, $parent_section_id) {
	// HAVES: parent_section_id, ØNSKES: book_id
	if ($book_id == "") {
		$book_id = db_returnBookfromSectionId($parent_section_id);
	}
	// Hent bogdata
	$bookdata = hentRow($book_id, "BOOKS");
//	echo "<h1>Opret nyt ".strtolower(returnSectionType($parent_section_id))." i bogen '$bookdata[BOOKTITLE]'</h1>";
	echo section_form($book_id, $parent_section_id);
}

function edit_section($section_id) {
	// Hent sectiondata
	$sectiondata = hentRow($section_id, "BOOKSECTIONS");
	$parent_section_id = $sectiondata['PARENT_ID'];
	// Hent bogdata
	$book_id = $sectiondata['BOOK_ID'];
	$bookdata = hentRow($book_id, "BOOKS");
//	echo "<h1>Rediger ".strtolower(returnSectionType($parentid, "bestemt"))." i bogen '$bookdata[BOOKTITLE]'</h1>";
	echo section_form($book_id, $parent_section_id, $section_id);
}

function show_booklist() {
	echo "\n\n<h1>Bogprojekter</h1>\n";
	echo "<div class='feltblok_header'>Oprettede bøger</div>\n";
	echo "<div class='feltblok_wrapper'>\n";
	
	$sql = "select ID, BOOKTITLE, PUBLISHER, PUBLISHED, PUBMONTH, PUBYEAR, CHANGED_DATE from BOOKS where DELETED = 'N' order by PUBYEAR desc, PUBMONTH desc, CHANGED_DATE desc";
	if (@$result = mysql_query($sql)) {
			if (db_hasrows()) {
				echo "\n<table class='oversigt'>";
				echo "\n<tr class='trtop'>";
				echo "<td class='kolonnetitel'>Titel</td>";
				echo "<td class='kolonnetitel'>Udgivet af</td>";
				echo "<td class='kolonnetitel'>Udgivet</td>";
				echo "<td class='kolonnetitel'>Sidst ændret</td>";
				// echo "<td class='kolonnetitel'>Publiceret</td>";
				echo "<td class='kolonnetitel'>Funktioner</td>";
				echo "</tr>\n";
				while ($row = mysql_fetch_array($result)) {
					$x++;		
					echo "\n<tr".(($x % 2) ? " class='evenrow'" : "").">\n";
					echo "<td>$row[BOOKTITLE]</td>\n";
					echo "<td>$row[PUBLISHER]</td>\n";
					echo "<td>".returnMonthName($row[PUBMONTH])." $row[PUBYEAR]</td>\n";
					echo "<td>".displayUpdateTime($row[CHANGED_DATE], 1)."</td>\n";
					// echo "<td>".returnJaNej($row[PUBLISHED])."</td>\n";
					echo "<td>
							<input type='button' class='lilleknap' value='Slet' onclick='gotourl(\"$_SERVER[baseurl]&do=db_delete_book&amp;book_id=$row[ID]\", \"confirm\", \"Er du sikker på at du vil slette bogen \\\"$row[BOOKTITLE]\\\"?\\n\\nHvis du sletter bogen sletter du samtidig alle kapitler og afsnit i bogen.\\nBemærk at billedmappen IKKE bliver slettet. Du kan evt. manuelt slette mappen, hvis den ikke indeholder billeder, som du vil gemme.\");'>
							<input type='button' class='lilleknap' value='Stamdata' onclick='gotourl(\"$_SERVER[baseurl]&do=edit_book&amp;book_id=$row[ID]\", \"\", \"\");'>
							<input type='button' class='lilleknap' value='Indhold' onclick='gotourl(\"$_SERVER[baseurl]&do=edit_book_contents&amp;book_id=$row[ID]\", \"\", \"\");'>
						</td>\n";
					echo "</tr>\n";
				}
				echo "</table>\n\n";
			} else {
				echo "<table class='oversigt' summary='Tabel med oversigt over alle bøger i systemet.'>\n";
				echo "<tr class='trtop'>\n";
				echo "<td class='kolonnetitel'>\nDer er ikke oprettet nogle bogprojekter endnu!</td>\n";
				echo "</tr>\n";
				echo "</table>\n";				
			}
				echo "</div>"; // feltblock_wrapper
				echo "<div class='knapbar'><input type='button' value='Nyt bogprojekt' onclick='gotourl(\"$_SERVER[baseurl]&do=add_book\", \"\", \"\");'></div>";
	} else {
		die(db_errorhandler("Kunne ikke hente boglisten! Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
	}
}

function show_sectionlist($book_id) {
	$data = hentRow($book_id, "BOOKS");
	$sql = "select ID, PARENT_ID, SECTIONTHREAD_ID, POSITION, TITLE, CHANGED_DATE from BOOKSECTIONS where BOOK_ID = '$book_id' and DELETED = 'N' order by PARENT_ID, POSITION";
	echo "\n\n<h1>Indhold</h1>\n";
	echo "<div class='feltblok_header'>Bogen \"$data[BOOKTITLE]\" indholder</div>";
	echo "<div class='feltblok_wrapper'>";
	if (@$result = mysql_query($sql)) {
			if (db_hasrows()) {
				show_sectionchildren($book_id, 0, 0);
			} else {
				echo "<table class='oversigt'><tr class='trtop'><td class='kolonnetitel'>Bogen har ingen kapitler endnu!</td></tr></table>";
			}
	} else {
		die(db_errorhandler("Kunne ikke hente liste med bogens kapitler og afsnit! Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
	}
	echo "<div class='knapbar'>";
	echo "<input type='button' class='lilleknap' value='Afbryd' onclick='gotourl(\"$_SERVER[baseurl]\", \"\", \"\");' />";
	echo "<input type='button' value='Opret nyt kapitel' onclick='gotourl(\"$_SERVER[baseurl]&do=add_section&amp;book_id=$book_id&amp;parent_section_id=0\", \"\", \"\");' />";
	echo "</div>";
	echo "</div>"; // feltblok_wrapper
}

function show_sectionchildren($book_id, $parent, $level) { 
	// retrieve all children of $parent 
	// $result = mysql_query('SELECT title FROM tree '.'WHERE parent="'.$parent.'";'); 
	$sql = "select ID, PARENT_ID, SECTIONTHREAD_ID, POSITION, TITLE, CHANGED_DATE from BOOKSECTIONS where BOOK_ID = '$book_id' and PARENT_ID = '$parent' and DELETED = 'N' order by POSITION;";
	$result = mysql_query($sql);

	// display each child 
	while ($row = mysql_fetch_array($result)) {
		echo "\n<div class='sectionContainer' id='row_$row[ID]' onmouseover='bookmakerIEColorShift(this.id)' onmouseout='bookmakerIEColorUnShift(this.id,0)'>\n";
			$space = ($level * 20);
			echo "\t<div class='indent' style='width:".$space."px'></div>\n";
			echo "\t<div class='sectionNumber'>".implode(returnSectionPath($row[ID]), ".")."</div>\n";
			echo "\t<div class='sectionTitle'>";
			if ($parent == 0) {			
				echo "<strong>";
			}
			echo $row[TITLE];
			if ($parent == 0) {			
				echo "</strong>";
			}
			echo "</div>\n";
			echo "\t<div class='functions'>\n";

		if ($row[POSITION] > 1) {
			echo "<a href='$_SERVER[baseurl]&do=db_section_up&amp;section_id=$row[ID]'>";
			echo 	"<img src='images/sideop.gif' border='0'>";
			echo "</a>&nbsp;";
		}
		if ($row[POSITION] < returnHighestPosition($parent, $book_id)) {
			echo "<a href='$_SERVER[baseurl]&do=db_section_down&amp;section_id=$row[ID]'>";
			echo 	"<img src='images/sidened.gif' border='0'>";
			echo "</a>&nbsp;";
		}


			echo "\t\t<input type='button' value='Slet' class='lilleknap' onclick='gotourl(\"$_SERVER[baseurl]&do=db_delete_section&amp;section_id=$row[ID]\", \"confirm\", \"Er du sikker på at du vil slette \\\"$row[TITLE]\\\"\\n\\nAlle underafsnit vil også blive slettet!\");'/>\n";
			echo "\t\t<input type='button' value='Lav underafsnit' class='lilleknap' onclick='gotourl(\"$_SERVER[baseurl]&do=add_section&amp;parent_section_id=$row[ID]\", \"\", \"\");'/>\n";
			echo "\t\t<input type='button' value='Rediger' class='lilleknap' onclick='gotourl(\"$_SERVER[baseurl]&do=edit_section&amp;section_id=$row[ID]\", \"\", \"\");'/>\n";

			echo "\t</div>\n";
		echo "</div>\n\n";
		// call this function again to display this child's children 
		show_sectionchildren($book_id, $row['ID'], $level+1); 
	}
}

function show_matrixlist($book_id) {
	echo "<div class='feltblok_header'>SMTTE matricer for denne bog</div>\n";
	echo "<div class='feltblok_wrapper'>\n";
	$sql = "select ID, MATRIX_TITLE, MATRIX_CONTENT, CREATED_DATE, CHANGED_DATE, CHANGED_BY from BOOKMATRICES where BOOK_ID = $book_id and DELETED = 'N' order by CREATED_DATE desc, CHANGED_DATE desc";
	if (@$result = mysql_query($sql)) {
			if (db_hasrows()) {
				echo "\n<table class='oversigt'>";
				echo "\n<tr class='trtop'>";
				echo "<td class='kolonnetitel'>Titel</td>";
				echo "<td class='kolonnetitel'>Sidst ændret</td>";
				echo "<td class='kolonnetitel' align='right'>Funktioner</td>";
				echo "</tr>\n";
				while ($row = mysql_fetch_array($result)) {
					$x++;		
					echo "\n<tr".(($x % 2) ? " class='evenrow'" : "").">\n";
					echo "<td>$row[MATRIX_TITLE]</td>\n";
					echo "<td>".displayUpdateTime($row[CHANGED_DATE], 1)." af ".returnAuthorName($row[CHANGED_BY], 0)."</td>\n";
					echo "<td align='right'>
							<input type='button' class='lilleknap' value='Slet' onclick='gotourl(\"$_SERVER[baseurl]&do=db_delete_matrix&amp;matrix_id=$row[ID]\", \"confirm\", \"Er du sikker på at du vil slette matricen \\\"$row[MATRIX_TITLE]\\\"?\");'>
							<input type='button' class='lilleknap' value='Rediger' onclick='gotourl(\"$_SERVER[baseurl]&do=edit_matrix&amp;matrix_id=$row[ID]\", \"\", \"\");'>
						</td>\n";
					echo "</tr>\n";
				}
				echo "</table>\n\n";
			} else {
				echo "<table class='oversigt'>\n";
				echo "<tr class='trtop'>\n";
				echo "<td class='kolonnetitel'>\nDer er endnu ikke oprettet matricer til bogen!</td>\n";
				echo "</tr>\n";
				echo "</table>\n";				
			}
				echo "<div class='knapbar'><input type='button' class='lilleknap' value='Afbryd' onclick='gotourl(\"$_SERVER[baseurl]\", \"\", \"\");' /><input type='button' value='Opret ny matrix' onclick='gotourl(\"$_SERVER[baseurl]&do=add_matrix&book_id=$book_id\", \"\", \"\");'></div>"; 
			echo "</div>"; // feltblock_wrapper
	} else {
		die(db_errorhandler("Kunne ikke hente listen med matricer! Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
	}
}

# FORMS
function __________anchor_forms() {};

function book_form($book_id = 0) {
	# Will return form used for adding/editing book
	# Call with book_id to fill data in form for editing
	# Call without arguments for blank form

	# Set variables
	if ($book_id != 0) {
		$bookdata = hentRow($book_id, "BOOKS");
		echo "<h1>Stamoplysninger '$bookdata[BOOKTITLE]'</h1>";
		$booktitle = $bookdata[BOOKTITLE];
		$subtitle = $bookdata[SUBTITLE];
		$publisher = $bookdata[PUBLISHER];
		$pubmonth = $bookdata[PUBMONTH];
		$pubyear = $bookdata[PUBYEAR];
		$published = $bookdata[PUBLISHED];
		$deleted = $bookdata[DELETED];
		$do = "db_bookupdate";
		$submitButton = "Gem ændringer";
		
		// Get style ID
		$style_id = db_getSingleValue("BOOKSTYLES", "ID", $book_id, "BOOK_ID");
		$bookstyle = hentRow($style_id, "BOOKSTYLES");
		$show_chapter_frontpages = $bookstyle[SHOW_CHAPTER_FRONTPAGES];
		$number_to = $bookstyle[NUMBER_TO];
		$show_together_from = $bookstyle[SHOW_TOGETHER_FROM];
		$heading_font = $bookstyle[HEADING_FONT];
		$main_font = $bookstyle[MAIN_FONT];
		$sidebar_font = $bookstyle[SIDEBAR_FONT];
		$caption_font = $bookstyle[CAPTION_FONT];
		$coverimage_url = $bookstyle[COVERIMAGE_URL];
		
	} else {
		// default values
		$pubyear = date("Y");
		$do = "db_bookinsert";
		$submitButton = "Opret bogprojekt";
		$number_to = 3;
		$show_together_from = 3;
		$heading_font = "helvetica";
		$main_font = "verdana";
		$sidebar_font = "verdana";
		$caption_font = "helvetica";
		$coverimage_url_disabled = "disabled";
		// ------------------------------------> HUSK
		// $folder_id = "";
	}

	# Build form
	$form = " <div class='knapbar'><input type='button' class='lilleknap' value='Afbryd' onclick='gotourl(\"$_SERVER[baseurl]\", \"\", \"\");' /><input type='button' name='submitButton' value='$submitButton' onclick='verifyBookStamdata();' /></div>";

	$form .= "<div class='feltblok_header'>Stamdata</div>\n";

	$form .= "<div class='feltblok_wrapper'>";
	$form .= "<form action='' method='POST' id='addBookForm'>";
	
	// $form .= "<h2>Er bogen publiceret?</h2>";
	// $form .= createSelectYesNo("published", $published);

	$form .= "<h2>Bogens titel</h2>";
	$form .= "<input type='text' name='booktitle' id='booktitle' class='inputfelt' value='$booktitle'/>";

	$form .= "<h2>Bogens undertitel</h2>";
	$form .= "<input type='text' name='subtitle' id='subtitle' class='inputfelt' value='$subtitle'/>";

	$form .= "<h2>Udgivet af</h2>";
	$form .= "<input type='text' name='publisher' id='publisher' class='inputfelt' value='$publisher'/>";

	$form .= "<h2>Udgivelsesmåned</h2>";
	$form .= createSelectMonth("pubmonth", $pubmonth);

	$form .= "<h2>Udgivelsesår (fx ".date('Y').")</h2>";
	$form .= "<input type='text' id='pubyear' name='pubyear' maxlength='4' class='inputfelt' size='4' value='$pubyear'/>";
	$form .= "</div>"; // feltblok_wrapper

	$form .= "<div class='knapbar'><input type='button' class='lilleknap' value='Afbryd' onclick='gotourl(\"$_SERVER[baseurl]\", \"\", \"\");' /><input type='button' name='submitButton' value='$submitButton' onclick='verifyBookStamdata();' /></div>";

	$form .= "<div class='feltblok_header'>";
	$form .= "Typografi og layout";
	$form .= "</div>";
	$form .= "<div class='feltblok_wrapper'>";

	if ($coverimage_url == "") {
		$use_coverimage_checked="N";
		$disabled_1 = "disabled";
		$disabled_2 = "disabled";
	} else {
		$use_coverimage_checked="Y";
		$disabled_1 = "";
		$disabled_2 = "";
	}		
	$folder_id = db_getSingleValue('BOOKS', 'PICTUREFOLDER_ID', $book_id, 'ID');
	$form .= "<h2 id='use_coverimage_heading' folderid='$folder_id'>Benyt forsidebillede?</h2>";
	$form .= "<table><tr><td valign='top'>";
	$form .= createCheckbox("Ja tak", "use_coverimage", "Y", "$use_coverimage_checked", "useCoverImage(this);", $coverimage_url_disabled);
	$form .= "&nbsp;&nbsp;<input type='text' name='coverimage_url' id='coverimage_url' class='inputfelt' style='width: 300px' $disabled_1 value='$coverimage_url'/>";
	$form .= "&nbsp;<input type='button' class='lilleknap' name='selectImageButton' id='selectImageButton' value='Vælg' $disabled_2 onclick='selectImage($folder_id);' />";
	$form .= "</td><td>";
	$thumburl = explode("/",$coverimage_url);
	$lastpart = array_pop($thumburl);
	$thumburl[] = "thumbs";
	$thumburl[] = $lastpart;
	$thumburl = implode("/", $thumburl); 
	 
	$form .= "&nbsp;&nbsp;<img id='coverthumb' src='$thumburl' border='1'";
	if ($coverimage_url == "") {
		$form .= " style='display:none;'";
	}
	$form .= "/>";
	$form .= "</td></tr></table>";
	// Div for selecting images
	$form .= "<div id='selectImageDiv' style='
		display: none;
		width: 725px; 
		height: auto; 
		border: 1px solid #999; 
		background-color: #FFF;'></div>";
	// Debugger
	// $form .= "<div id='debug'>debugger</div>";
	$form .= "<h2>Benyt kapitelforsider med kaptitel-titler? (kun PDF)</h2>";
	$form .= createCheckbox("Ja tak", "show_chapter_frontpages", "Y", "$show_chapter_frontpages");
	$form .= "<h2>Automatisk nummerering af afsnit</h2>";
	$form .= createSelectSectionNumbering("number_to", $number_to);
	$form .= "<h2>Automatisk samling af afsnit</h2>";
	$form .= createSelectSectionCollating("show_together_from", $show_together_from);
	$form .= "<h2>Skrifttype til overskrifter</h2>";
	$form .= createSelectFonts("heading_font", $heading_font);
	$form .= "<h2>Skrifttype til brødtekst</h2>";
	$form .= createSelectFonts("main_font", $main_font);
	$form .= "<h2>Skrifttype til sidekolonne</h2>";
	$form .= createSelectFonts("sidebar_font", $sidebar_font);
//	$form .= "<h2>Skrifttype til billedtekster</h2>";
//	$form .= createSelectFonts("caption_font", $caption_font);
	$form .= "</div>"; // feltblok_wrapper

	$form .= " <div class='knapbar'><input type='button' class='lilleknap' value='Afbryd' onclick='gotourl(\"$_SERVER[baseurl]\", \"\", \"\");' /><input type='button' name='submitButton' value='$submitButton' onclick='verifyBookStamdata();' /></div>";

	if ($book_id != 0) {
		$form .= "<input type='hidden' name='book_id' value='$book_id' />";
		$form .= "<input type='hidden' name='deleted' value='$deleted' />";
	}
	$form .= "<input type='hidden' name='do' value='$do' />";

	$form .= "</form>";
	
	# Returner formular
	return $form;
}

function section_form($book_id, $parent_section_id, $section_id = 0) {
	# Will return form used for adding/editing booksections in $book_id
	# $parent_section_id = 0 is a chapter
	# Call with $section_id to fill data in form for editing without $section_id argument for blank form

// echo "BOG: $book_id  |  PARENT: $parent_section_id  |  SECTION: $section_id <br />";

	# Set variables
	$_SESSION[current_book_id] = $book_id; // For use in Matrix FCKeditor plug-in

	if ($section_id != 0) { // EDIT EXISTING SECTION
		$sectiondata = hentRow($section_id, "BOOKSECTIONS");
		$sectiontitle = $sectiondata[TITLE];
		$content_main = unhtmlentities($sectiondata[CONTENT]);
		$content_side = unhtmlentities($sectiondata[CONTENT_SIDE]);
		$deleted = $sectiondata[DELETED];
		$do = "db_sectionupdate";
		$bookdata = hentRow($book_id, "BOOKS");
		if ($parent_section_id == 0) {
			echo "<h1>Ret ".strtolower(returnSectionType($parent_section_id, "bestemt"))." '$sectiontitle' i bogen '$bookdata[BOOKTITLE]'</h1>";
			$submitButton = "Opdater kapitel";
		} else {
			echo "<h1>Ret ".strtolower(returnSectionType($parent_section_id, "bestemt"))." '$sectiontitle' i bogen '$bookdata[BOOKTITLE]'</h1>";
			$submitButton = "Opdater afsnit";		
		}
	} else { // CREATE NEW SECTION
		$bookdata = hentRow($book_id, "BOOKS");
		echo "<h1>Opret nyt ".strtolower(returnSectionType($parent_section_id))." i bogen '$bookdata[BOOKTITLE]'</h1>";
		if ($parent_section_id != 0) { // set some sectiondata variables from parent_session is set (not a chapter)
			$parent_sectiondata = hentRow($parent_section_id, "BOOKSECTIONS");
			$sectionthread_id = $parent_sectiondata[SECTIONTHREAD_ID];
		}
		$do = "db_sectioninsert";
		$submitButton = "Opret ".strtolower(returnSectionType($parent_section_id, "bestemt"));
	}

	# Build form
	$form = "<form action='' method='POST'>";
	$form .= "<div class='feltblok_header'>Stamdata</div>";
	$form .= "<div class='feltblok_wrapper'>";

	$form .= "<h2>".returnSectionType($parent_section_id, "bestemt")."s titel</h2>";
	$form .= "<input type='text' id='sectiontitle' name='sectiontitle' class='inputfelt' value='$sectiontitle'/>";

	$form .= "<h2>Indhold i hovedkolonne</h2>";
		$mFCKeditor = new FCKeditor('content_main') ;
		$mFCKeditor->BasePath = "/cms/fckeditor/";
		$mFCKeditor->Height	= "400";
		$mFCKeditor->ToolbarSet	= "Bookmaker_HovedKolonne";
		$mFCKeditor->Value = $content_main;
		$mFCKeditor->Config['CustomConfigurationsPath']	= $fckEditorCustomConfigPath . "/cms_fckconfig.js";
    $form .= $mFCKeditor->CreateHtml() ;
	$form .= "<div class='knapbar'>";
	$form .= "<input type='button' class='lilleknap' value='Afbryd' onclick='gotourl(\"$_SERVER[baseurl]&do=edit_book_contents&amp;book_id=$book_id\", \"\", \"\");'>";
	$form .= "<input type='submit' name='submitButton' value='$submitButton' />";
	$form .= "</div>";
	$form .= "<h2>Indhold i sidekolonne</h2>";
		$sFCKeditor = new FCKeditor('content_side') ;
		$sFCKeditor->BasePath = "/cms/fckeditor/";
		$sFCKeditor->Height	= "250";
		$sFCKeditor->ToolbarSet	= "Bookmaker_SideKolonne";
		$sFCKeditor->Value = $content_side;
		$sFCKeditor->Config['CustomConfigurationsPath']	= $fckEditorCustomConfigPath . "/cms_fckconfig.js";

    $form .= $sFCKeditor->CreateHtml() ;

	$form .= "<input type='hidden' name='book_id' value='".$book_id."' />\n";
	$form .= "<input type='hidden' name='sectionthread_id' value='".$sectionthread_id."' />\n";
	$form .= "<input type='hidden' name='parent_section_id' value='".$parent_section_id."' />\n";

	if ($section_id != 0) {
		$form .= "<input type='hidden' name='section_id' value='".$section_id."' />\n";
		$form .= "<input type='hidden' name='deleted' value='".$deleted."' />\n";
	}
	$form .= "<input type='hidden' name='do' value='$do' />\n";
	
	
	$form .= "<div class='knapbar'>";
	$form .= "<input type='button' class='lilleknap' value='Afbryd' onclick='gotourl(\"$_SERVER[baseurl]&do=edit_book_contents&amp;book_id=$book_id\", \"\", \"\");'>";
	$form .= "<input type='submit' name='submitButton' value='$submitButton' />";
	$form .= "</div>";
	$form .= "</div>";
	
	$form .= "</form>";
	
	# Returner formular
	return $form;
}

function matrix_form($book_id, $matrix_id = 0) {
	# Will return form used for adding/editing a matrix
	# Call with matrix_id to fill data in form for editing
	# Call without arguments for blank form

	# Set variables
	if ($matrix_id != 0) {
		$matrixdata = hentRow($matrix_id, "BOOKMATRICES");
		$matrixtitle = $matrixdata[MATRIX_TITLE];
		$theMatrixTable = unhtmlentities($matrixdata[MATRIX_CONTENT]);
		$heading = "Rediger matricen \"$matrixtitle\"";
		$do = "db_matrixupdate";
		$submitButton = "Gem ændringer";
	} else {
		// default values
		$heading = "Opret ny matrix";
		$do = "db_matrixinsert";
		$submitButton = "Gem matrix";

		// build matrix table
		$matrix_cols = 6;
		$matrix_rows = 7; 
		$i=0;
		$default_col_titles = array(2=>"Læringsmål", "Erkendelsesmål", "Tegn", "Tiltag/aktivitet", "Evaluering");
		$default_row_titles = array(2=>"Personlige kompetencer", "Sociale kompetencer", "Sprog", "Krop og bevægelse", "Naturen og naturfænomener", "Kulturelle udtryksformer og værdier");
		$theMatrixTable = "<br /><table border='1' cellpadding='3' id='matrix'>";
		for($r=1; $r<=$matrix_rows; $r++){
			$theMatrixTable .= "<tr>";
		for($c=1; $c<=$matrix_cols; $c++){
			$i++;
				if($r==1 && $c==1) 	$theMatrixTable .= "<th width='100' bgcolor='#dddddd' align='left' valign='top' id='felt_$i'>Klik på knappen for at redigere temanavn</th>";
				if($r==1 && $c!=1) 	$theMatrixTable .= "<th width='100' align='center' valign='top' align='center' id='felt_$i'>$default_col_titles[$c]</th>";
				if($r>1  && $c==1) 	$theMatrixTable .= "<th width='100' align='left' valign='top' id='felt_$i'>$default_row_titles[$r]</th>";
				if($r>1  && $c!=1) 	$theMatrixTable .= "<td width='100' valign='top' id='felt_$i'>Klik på knappen for at redigere tekst</td>";
		}
		$theMatrixTable .= "</tr>";
		}
		$theMatrixTable .= "</table><!-- END MATRIX TABLE -->";
	}

	# Build form
	$form = "<div class='feltblok_header'>";
	$form .= $heading;
	$form .= "</div>";
	$form .= "<div class='feltblok_wrapper'>";
	$form .= "<form id='matrixform' action='' method='POST'>";
	$form .= "<h2>Matricens titel</h2>";
	$form .= "<input type='text' name='matrixtitle' id='matrixtitle' class='inputfelt' value='$matrixtitle'/>";
	$form .= "<h2>Matricens indhold</h2>";
	$form .= "<div id='thematrix'>";
	$form .= $theMatrixTable;
	$form .= "</div>";
	$form .= "<input type='hidden' name='do' value='$do' />";
	$form .= "<input type='hidden' name='matrix_id' value='$matrix_id' />";
	$form .= "<input type='hidden' name='book_id' value='$book_id' />";
	$form .= "<input type='hidden' name='hiddenmatrix' id='hiddenmatrix' class='inputfelt' value='hiddenmatrix'/>";
	$form .= "<div class='knapbar'>";
	$form .= "<input type='button' class='lilleknap' value='Afbryd' onclick='gotourl(\"$_SERVER[baseurl]&do=edit_book_contents&amp;book_id=$book_id\", \"\", \"\");'>";
	$form .= "<input id='gemknap' type='button' onclick='editMatrixComplete();' name='submitButton' value='$submitButton'/>";
	$form .= "</div>";
	$form .= "</form>";
	$form .= "</div>";

	# Returner formular
	return $form;
}



# FORMS
function __________anchor_dbfuntions() {};

function db_bookinsert() {
	$now = time();	
	$sql = "insert into BOOKS (BOOKTITLE, SUBTITLE, PUBLISHER, PUBMONTH, PUBYEAR, PUBLISHED, CREATED_DATE, CHANGED_DATE, CHANGED_BY) 
	values ('".db_safedata($_POST[booktitle])."', 
	'".db_safedata($_POST[subtitle])."',
	'".db_safedata($_POST[publisher])."',
	'".db_safedata($_POST[pubmonth])."',
	'".db_safedata($_POST[pubyear])."',
	'".db_safedata($_POST[published])."',
	'".db_safedata($now)."',
	'".db_safedata($now)."',
	'".$_SESSION[CMS_USER][USER_ID]."');";
//	echo $sql;
	 if (@mysql_query($sql)) {
		$inserted = mysql_insert_id();
		$sql = "insert into BOOKSTYLES (BOOK_ID, HEADING_FONT, MAIN_FONT, SIDEBAR_FONT, CAPTION_FONT, NUMBER_TO, SHOW_TOGETHER_FROM, SHOW_CHAPTER_FRONTPAGES, COVERIMAGE_URL, CREATED_DATE, CHANGED_DATE, CHANGED_BY) values (
		'".$inserted."',
		'".db_safedata($_POST[heading_font])."',
		'".db_safedata($_POST[main_font])."',
		'".db_safedata($_POST[sidebar_font])."',
		'".db_safedata($_POST[caption_font])."',
		'".db_safedata($_POST[number_to])."',
		'".db_safedata($_POST[show_together_from])."',
		'".db_safedata($_POST[show_chapter_frontpages])."',
		'".db_safedata($_POST[coverimage_url])."',
		'".db_safedata($now)."',
		'".db_safedata($now)."',
		'".$_SESSION[CMS_USER][USER_ID]."');";
		 if (@mysql_query($sql)) {
			$mapnavn = "Bog: ".$_POST[booktitle];
			include_once($_SERVER[DOCUMENT_ROOT]."/cms/modules/picturearchive/picturearchive_common.inc.php");
			$picture_folder_id = gemBilledMappe($mapnavn, $_SESSION[CMS_USER][USER_ID]);
			$usql = "update BOOKS set PICTUREFOLDER_ID = $picture_folder_id where ID = $inserted";
		 	@mysql_query($usql);
			if (buildHTMLForPDF($inserted)) {
				$usermessage_ok = "Bogen '$_POST[booktitle]' er oprettet! Der er også automatisk oprettet en billedmappe med titlen 'Bog: $_POST[booktitle]'. Her kan du lægge billeder, som skal bruges i bogen.";
				$location = $_SERVER[baseurl]."&usermessage_ok=$usermessage_ok";
				header("Location: $location");
				exit;
			} else {
				die(db_errorhandler("Der opstod en fejl og du kan ikke lave en PDF af bogen '$_POST[booktitle]'. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
			}
		} else {
			die(db_errorhandler("Der opstod en fejl og din bog '$_POST[booktitle]' blev ikke gemt! Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
		}
	} else {
		die(db_errorhandler("Der opstod en fejl og din bog '$_POST[booktitle]' blev ikke gemt! Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
	}
}

function db_sectioninsert() {
	$position = returnNextPosition($_POST[parent_section_id], $_POST[book_id]); // For afsnit
	
	$now = time();	
	$sql = "insert into BOOKSECTIONS (BOOK_ID, PARENT_ID, SECTIONTHREAD_ID, POSITION, TITLE, CONTENT, CONTENT_SIDE, CREATED_DATE, CHANGED_DATE) 
	values ('".db_safedata($_POST[book_id])."',
	'".db_safedata($_POST[parent_section_id])."',
	'".db_safedata($_POST[sectionthread_id])."',
	'".db_safedata($position)."',
	'".db_safedata($_POST[sectiontitle])."',
	'".db_safedata($_POST[content_main])."',
	'".db_safedata($_POST[content_side])."',
	'".db_safedata($now)."',
	'".db_safedata($now)."');";
//	echo $sql;
	 if (@mysql_query($sql)) {
		if ($_POST[parent_section_id] == 0) { // For kapitler er THREAD_ID = ID
			$inserted = mysql_insert_id();
			if ($inserted != 0) {
				$sql = "update BOOKSECTIONS set 
				SECTIONTHREAD_ID = '".$inserted."'
				where ID = ".$inserted.";";
				if (!@mysql_query($sql)) {
					die(db_errorhandler("Der opstod en fejl og dit kapitel '$_POST[sectiontitle]' blev ikke gemt! (thread_id ikke opdateret) Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));							}
			}			
		}
		if (buildHTMLForPDF($_POST[book_id])) {
			$message = returnSectionType($_POST[parent_section_id], "bestemt")." '$_POST[sectiontitle]' er oprettet!";
			$usermessage_ok = $message;
			$location_book_id = $_POST[book_id];
			header("Location: $_SERVER[baseurl]&do=edit_book_contents&book_id=$location_book_id&usermessage_ok=$usermessage_ok");
			exit;
		} else {
			die(db_errorhandler("Der opstod en fejl og du kan ikke lave en PDF af bogen '$_POST[booktitle]'. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
		}
	} else {
		die(db_errorhandler("Der opstod en fejl og dit kapitel/afsnit '$_POST[sectiontitle]' blev ikke gemt! Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
	}
}

function db_matrixinsert() {
	$now = time();	
//	$mc = db_safedata($_POST[hiddenmatrix]);
	$mc = $_POST[hiddenmatrix];
	$sql = "insert into BOOKMATRICES (BOOK_ID, MATRIX_TITLE, MATRIX_CONTENT, CREATED_DATE, CHANGED_DATE, CHANGED_BY) 
	values ('".db_safedata($_POST[book_id])."', 
	'".db_safedata($_POST[matrixtitle])."',
	'".db_safedata($mc)."',
	'".$now."',
	'".$now."',
	'".$_SESSION[CMS_USER][USER_ID]."');";
//	echo $sql;
	 if (@mysql_query($sql)) {
		$usermessage_ok = "Matrixen '$_POST[matrixtitle]' er oprettet!";
		$location = $_SERVER[baseurl]."&do=edit_book_contents&book_id=$_POST[book_id]&usermessage_ok=$usermessage_ok";
		header("Location: $location");
		exit;
	} else {
		die(db_errorhandler("Der opstod en fejl og din matrix '$_POST[matrixtitle]' blev ikke gemt! Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
	}
}


function db_sectionupdate() {
	$now = time();	
	$sql = "update BOOKSECTIONS set 
	SECTIONTHREAD_ID = '".db_safedata($_POST[sectionthread_id])."',
	TITLE = '".db_safedata($_POST[sectiontitle])."',
	CONTENT = '".db_safedata($_POST[content_main])."',
	CONTENT_SIDE = '".db_safedata($_POST[content_side])."',
	DELETED = '".db_safedata($_POST[deleted])."',
	CHANGED_DATE = '$now', 
	CHANGED_BY = ".$_SESSION[CMS_USER][USER_ID]."
	where ID = ".db_safedata($_POST[section_id]).";";
	 if (@mysql_query($sql)) {
		if (buildHTMLForPDF($_POST[book_id])) {
			$usermessage_ok = "Dine ændringer på '$_POST[sectiontitle]' er gemt!";
			header("Location: $_SERVER[baseurl]&do=edit_book_contents&book_id=$_POST[book_id]&usermessage_ok=$usermessage_ok");
		} else {
			die(db_errorhandler("Der opstod en fejl og du kan ikke lave en PDF af bogen '$_POST[booktitle]'. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
		}
	} else {
		die(db_errorhandler("Der opstod en fejl og dine ændringer på '$_POST[booktitle]' blev ikke gemt! Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
	}
}

function db_bookupdate() {
	$now = time();
	$sql = "update BOOKS set 
	BOOKTITLE = '".db_safedata($_POST[booktitle])."',
	SUBTITLE = '".db_safedata($_POST[subtitle])."',
	PUBLISHER = '".db_safedata($_POST[publisher])."',
	PUBMONTH = '".db_safedata($_POST[pubmonth])."',
	PUBYEAR = '".db_safedata($_POST[pubyear])."',
	PUBLISHED = '".db_safedata($_POST[published])."',
	CHANGED_DATE = '$now', 
	CHANGED_BY = ".$_SESSION[CMS_USER][USER_ID].",
	DELETED = '".db_safedata($_POST[deleted])."'
	where ID = ".db_safedata($_POST[book_id]).";";
	
	$sql_styleupdate = "update BOOKSTYLES set
	HEADING_FONT = '".db_safedata($_POST[heading_font])."',
	MAIN_FONT = '".db_safedata($_POST[main_font])."',
	SIDEBAR_FONT = '".db_safedata($_POST[sidebar_font])."',
	CAPTION_FONT = '".db_safedata($_POST[caption_font])."',
	NUMBER_TO = '".db_safedata($_POST[number_to])."',
	SHOW_TOGETHER_FROM = '".db_safedata($_POST[show_together_from])."',
	SHOW_CHAPTER_FRONTPAGES = '".db_safedata($_POST[show_chapter_frontpages])."',
	COVERIMAGE_URL = '".db_safedata($_POST[coverimage_url])."',
	CHANGED_DATE = '$now', 
	CHANGED_BY = ".$_SESSION[CMS_USER][USER_ID]."
	where BOOK_ID = ".db_safedata($_POST[book_id]).";";

	$newname = "Bog: ". $_POST[booktitle];
	$folderid = db_getSingleValue('BOOKS', 'PICTUREFOLDER_ID', $_POST[book_id], 'ID');
	$sql_picturearchiveupdate = "update PICTUREARCHIVE_FOLDERS set TITLE = '$newname' where ID = $folderid";

	if (@mysql_query($sql) && mysql_query($sql_styleupdate) && mysql_query($sql_picturearchiveupdate)) {
		if (buildHTMLForPDF($_POST[book_id])) {
			$usermessage_ok = "Dine ændringer på '$_POST[booktitle]' er gemt!";
			header("Location: $_SERVER[baseurl]&usermessage_ok=$usermessage_ok");
			exit;
		} else {
			die(db_errorhandler("Der opstod en fejl og du kan ikke lave en PDF af bogen '$_POST[booktitle]'. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
		}
	} else {
		die(db_errorhandler("Der opstod en fejl og dine ændringer på '$_POST[booktitle]' blev ikke gemt! Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
	}
}

function db_matrixupdate() {
	$now = time();	
	$mc = db_safedata($_POST[hiddenmatrix]);
	$sql = "update BOOKMATRICES set 
	MATRIX_TITLE = '".db_safedata($_POST[matrixtitle])."',
	MATRIX_CONTENT = '".$mc."',
	CHANGED_DATE = '$now', 
	CHANGED_BY = ".$_SESSION[CMS_USER][USER_ID]."
	where ID = ".db_safedata($_POST[matrix_id]).";";
	 if (@mysql_query($sql)) {
		$usermessage_ok = "Dine ændringer på \"$_POST[matrixtitle]\" er gemt! Husk at hvis du allerede har indsat matricen på en side skal du også sætte matricen ind igen.";
		header("Location: $_SERVER[baseurl]&do=edit_book_contents&book_id=$_POST[book_id]&usermessage_ok=$usermessage_ok");
	} else {
		die(db_errorhandler("Der opstod en fejl og dine ændringer på '$_POST[matrixtitle]' blev ikke gemt! Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
	}
}


function db_setdeleted($id, $table) {
	# Marks a row $id in $table as DELETED

	# Set variables
	$data = hentRow($id, $table);
	$now = time();
	if ($table == "BOOKS") {
		$do = "";
		$title = $data[BOOKTITLE];
		deleteHTMLForPDF($id);
	} elseif ($table == "BOOKSECTIONS") {
		$book_id = db_returnBookfromSectionId($id);
		$do = "edit_book_contents&book_id=$book_id";
		$title = $data[TITLE];
	} elseif ($table == "BOOKMATRICES") {
		$book_id = db_returnBookfromMatrixId($id);
		$do = "edit_book_contents&book_id=$book_id";
		$title = $data[MATRIX_TITLE];
	} else {
		die(db_errorhandler("Ikke slettet. Forkert tabel angivet. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
	}

	# Mark deleted
	$sql = "update $table set 
			CHANGED_DATE = '$now', 
			CHANGED_BY = ".$_SESSION[CMS_USER][USER_ID].",
			DELETED = 'Y' 			
			where ID = $id;";
	 if (@mysql_query($sql)) {
		# Move-up subsequent sections to avoid numbering gap
		if ($table == "BOOKSECTIONS") {
			moveSectionsUp($id);
		}
		$uectitle = urlencode($title);
		$usermessage_ok = "'$uectitle' er slettet!";
		header("Location: $_SERVER[baseurl]&do=$do&usermessage_ok=$usermessage_ok");
		exit;
	} else {
		die(db_errorhandler("Der opstod en fejl og '$title' blev ikke slettet! Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker."));
	}
}

function moveSectionsUp($section_id) {
	# Function to move all sections with a position higher than $section_id one position up
	# Only applies to sections on the same level (w/same parent id) as $section_id
	$section_data = hentRow($section_id, "BOOKSECTIONS");
	$parent_id = $section_data[PARENT_ID];
	$position = $section_data[POSITION];
	$book_id = $section_data[BOOK_ID];

//	if ($position > 1) { // Only move up if not top section
		$sql = "update BOOKSECTIONS set POSITION = POSITION-1 where POSITION > $position and PARENT_ID = $parent_id and BOOK_ID = $book_id;";
		if (@mysql_query($sql)) {
			return true;
		} else {
			$message = "Funktionen ".__FUNCTION__." fejlede. Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker.";
			die(db_errorhandler($message));
		}
//	}	
}

function moveOneUp($section_id) {
	# Function to move a section one position UP
	# Only applies to sections on the same level (w/same parent id) as $section_id
	$section_data = hentRow($section_id, "BOOKSECTIONS");
	$parent_id = $section_data[PARENT_ID];
	$position = $section_data[POSITION];
	$book_id = $section_data[BOOK_ID];

	if ($position > 1) { // Only move up if not top section
		$sql1 = "update BOOKSECTIONS set POSITION = 9999 where POSITION = $position-1 and PARENT_ID = $parent_id and BOOK_ID = $book_id;";
		$sql2 = "update BOOKSECTIONS set POSITION = $position-1 where POSITION = $position and PARENT_ID = $parent_id and BOOK_ID = $book_id;";
		$sql3 = "update BOOKSECTIONS set POSITION = $position where POSITION = 9999 and PARENT_ID = $parent_id and BOOK_ID = $book_id;";

		if (@mysql_query($sql1) && mysql_query($sql2) && mysql_query($sql3)) {
			header("Location: $_SERVER[baseurl]&do=edit_book_contents&book_id=$book_id");
			exit;
		} else {
			$message = "Funktionen ".__FUNCTION__." fejlede. Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker.";
			die(db_errorhandler($message));
		}
	}	
}

function moveOneDown($section_id) {
	# Function to move a section one position DOWN
	# Only applies to sections on the same level (w/same parent id) as $section_id
	$section_data = hentRow($section_id, "BOOKSECTIONS");
	$parent_id = $section_data[PARENT_ID];
	$position = $section_data[POSITION];
	$book_id = $section_data[BOOK_ID];

	if ($position < returnHighestPosition($parent_id, $book_id)) { // Only move up if not bottom section
		$sql1 = "update BOOKSECTIONS set POSITION = 9999 where POSITION = $position+1 and PARENT_ID = $parent_id and BOOK_ID = $book_id;";
		$sql2 = "update BOOKSECTIONS set POSITION = $position+1 where POSITION = $position and PARENT_ID = $parent_id and BOOK_ID = $book_id;";
		$sql3 = "update BOOKSECTIONS set POSITION = $position where POSITION = 9999 and PARENT_ID = $parent_id and BOOK_ID = $book_id;";

		if (@mysql_query($sql1) && mysql_query($sql2) && mysql_query($sql3)) {
			header("Location: $_SERVER[baseurl]&do=edit_book_contents&book_id=$book_id");
			exit;
		} else {
			$message = "Funktionen ".__FUNCTION__." fejlede. Fejlen skyldes sandsynligvis en midlertidig fejl på databasen, prøv evt. igen om lidt. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker.";
			die(db_errorhandler($message));
		}
	}	
}
function returnNextPosition($parent_section_id, $book_id) {
 	$sql = "select max(POSITION) from BOOKSECTIONS where PARENT_ID = $parent_section_id and BOOK_ID = $book_id and DELETED = 'N'";
	if (@$result = mysql_query($sql)) {
		return mysql_result($result, 0)+1;		
	} else {
		$error = "Funktionen ".__FUNCTION__." fejlede. Det nye afsnit er muligvist ikke blevet placeret korrekt i bogen. Fejlen skyldes sandsynligvis en midlertidig fejl på databasen. Fejlen er registreret og vil blive undersøgt nærmere af en tekniker.";	
		die(db_errorhandler($error));
	}
}

function returnHighestPosition($parent_section_id, $book_id) {
	$next = returnNextPosition($parent_section_id, $book_id);
	$highest = $next-1;
	return $highest;
}

function returnSectionPath($section_id) { 
	$parent_id = '';
	$current_position = '';
	$current_title = '';
	
	// Get current position + parent_ID
	$sql = "select PARENT_ID, POSITION, TITLE from BOOKSECTIONS where ID = '$section_id' and DELETED = 'N' limit 1;";
	$result = mysql_query($sql);

	// Add current position to array
	$row = mysql_fetch_row($result);
	$parent_id = $row[0];
	$current_position = $row[1];
	$current_title = $row[2];
	
	$path = array(); 
	$path[] = $current_position;

	// Repeat until PARENT_ID = 0
	if ($parent_id != 0) {
		$path = array_merge(returnSectionPath($parent_id), $path);
	}
	return $path;	
}
// --------------------------------------------------------> FRONT END FUNKTIONER
 //////////////////////////////////////////////////////////////////////////////////////////////
 // FUNKTIONER TIL BOOKMAKER																 //
 //////////////////////////////////////////////////////////////////////////////////////////////  
  
 function buildSectionSelector($bookid, $parentid=0, $level, $showTogetherAfterLevel) {
  global $HTML, $allContent, $numberTo;
  if ($level >= $showTogetherAfterLevel && $showTogetherAfterLevel > 0) return;
  $sql = "select ID, TITLE from BOOKSECTIONS where BOOK_ID='$bookid' and PARENT_ID='$parentid' and DELETED='N' order by POSITION asc";
  $result = mysql_query($sql);
  while($row = mysql_fetch_array($result)){
   $allContent[] = $row[ID];
   if ($_GET[grant] && $_GET[ignoreunfinished]==1 && validGrant($_GET[grant])) {
    $addGrantToURL = "&grant=$_GET[grant]&ignoreunfinished=1";
   }
   $HTML .= "<option value='?pageid=$_GET[pageid]&bookid=$bookid&sectionid=$row[ID]$addGrantToURL'" . ($row[ID]==$_GET[sectionid]?"selected":"") . ">";
   $HTML .=  returnIndent($level, "&nbsp;&nbsp;&nbsp;") . " ";
   $HTML .= ($numberTo >= returnSectionLevel($row[ID]) ? implode(".", returnSectionPath($row[ID])) . ". " : "");
   $HTML .= "$row[TITLE]</option>\n";
   buildSectionSelector($bookid, $row[ID], $level+1, $showTogetherAfterLevel);
  }
  return $HTML;
 }
 
 function returnIndent($level, $delimiter){
 // LAV ET INDENT * LEVEL
  $t = "";
  for($i=0;$i<$level;$i++){
   $t .= $delimiter;
  }
  return $t;
 }
 
 function indholdsFortegnelse($bookid, $parentid=0, $level, $showTogetherAfterLevel){
  global $indholdsHTML, $numberTo;
  if ($level >= $showTogetherAfterLevel && $showTogetherAfterLevel > 0) return;
  $sql = "select ID, TITLE from BOOKSECTIONS where BOOK_ID='$bookid' and PARENT_ID='$parentid' and DELETED='N' order by POSITION asc";
  $result = mysql_query($sql);
  while($row = mysql_fetch_array($result)){
   $indholdsHTML .= 
   		"<div class='indhold indhold$level'>" . 
    	returnIndent($level, "&nbsp;&nbsp;&nbsp;") 	. 
		"<a href='?pageid=$_GET[pageid]&amp;bookid=$bookid&amp;sectionid=".$row[ID]."'>" . 
		($numberTo >= returnSectionLevel($row[ID]) ? implode(".", returnSectionPath($row[ID])) . ". " : "") . 
		"$row[TITLE]" .
		"</a>" .
		"</div>\n";
   indholdsFortegnelse($bookid, $row[ID], $level+1, $showTogetherAfterLevel);
  }
  return $indholdsHTML;  
 }

 function chapterSelector($showTogetherAfterLevel){
  $pdfURL = "index.php?pageid=$_GET[pageid]&amp;bookid=$_GET[bookid]&amp;sectionid=GENERATEPDF";
  if ($_GET[grant] && $_GET[ignoreunfinished]==1 && validGrant($_GET[grant])) {
   $addGrantToURL = "&grant=$_GET[grant]&ignoreunfinished=1";
  }
  echo "<div id='sectionSelectorContainer'>";
  echo "<form method='get' action=''>";
  echo "<input type='hidden' name='pageid' value='$_GET[pageid]'/>";
  echo "<input type='hidden' name='bookid' value='$_GET[bookid]'/>";
  //echo "<strong>Her kan du bladre i bogen \"" . returnStamdata("BOOKTITLE") . "\":</strong><br/><br/>";
  echo "<strong>Gå til:</strong>&nbsp;<select id='sectionSelector' name='sectionSelector' class='sectionSelector'>\n";
  echo "<option value='?pageid=$_GET[pageid]&amp;bookid=$_GET[bookid]&amp;sectionid=FRONTPAGE$addGrantToURL' class='booksection_0'>Forside</option>\n";
  echo "<option value='?pageid=$_GET[pageid]&amp;bookid=$_GET[bookid]&amp;sectionid=CONTENTS$addGrantToURL' class='booksection_0'>Indholdsfortegnelse</option>\n";
  echo buildSectionSelector($_GET[bookid], 0, 0, $showTogetherAfterLevel);
  echo "</select>\n";  
  echo "<input class='sectionSelector' type='button' value='&nbsp;&raquo;&nbsp;' onclick=\"goToBookSection(document.getElementById('sectionSelector').value)\">&nbsp;";
  echo "&nbsp;&nbsp;<a href='$pdfURL' title='Hent \"" . returnStamdata("BOOKTITLE") . "\" som PDF-fil'><!--<img border='0' src='images/pdficon.gif' alt='' title=''>-->Hent hele bogen som PDF-fil</a>";
  echo "</form>";
  echo "</div>";
 }
 
 function returnSectionLevel($sectionid){
  return count(returnSectionPath($sectionid));
 }

 function returnSectionPosition($sectionid){
  $path = array_reverse(returnSectionPath($sectionid));
  return $path[0];
 }

 function returnPreviousSectionId($thisSectionId){
  global $allContent;
  $key = array_search($thisSectionId, $allContent);
  return $allContent[$key-1];
 }

 function returnNextSectionId($thisSectionId){
  global $allContent;
  $key = array_search($thisSectionId, $allContent);
  return $allContent[$key+1];
 }
 
 function bookNavigation($sectionid, $bookid, $pageid){
  $prevSectionId = returnPreviousSectionId($sectionid);
  $nextSectionId = returnNextSectionId($sectionid);
  $html = "<div id='bookNavigation'>";
  $html.= "<span style='float:left'>";
  if ($prevSectionId) $html.= "<a href='?pageid=$pageid&amp;bookid=$bookid&amp;sectionid=$prevSectionId'>&laquo;&nbsp;Forrige afsnit</a>";
  $html.= "</span>";
  $html.= "<span style='float:right'>";
  if ($nextSectionId) $html.= "<a href='?pageid=$pageid&amp;bookid=$bookid&amp;sectionid=$nextSectionId'>Næste afsnit&nbsp;&raquo;</a>";
  $html.= "</span>";
  $html.= "</div>";
  return $html;
 }
 
 function checkSizes_stripHeight($matches) {
  // as usual: $matches[0] is the complete match
  // $matches[1] the match for the first subpattern
  // enclosed in '(...)' and so on

	return $matches[1].$matches[5];
}

 function checkSizes_Hovedkolonne($matches) {
	$maxWidth = 538;
  // as usual: $matches[0] is the complete match
  // $matches[1] the match for the first subpattern
  // enclosed in '(...)' and so on

	$matches[2] = $matches[2]*1;
	if ($matches[2] > $maxWidth) {
		$matches[2] = $maxWidth;
		return $matches[1].$matches[2].$matches[3];
	} else {
		return $matches[0];
	}
}


 function checkSizes_HovedkolonneMedSide($matches) {
	$maxWidth = 370;
  // as usual: $matches[0] is the complete match
  // $matches[1] the match for the first subpattern
  // enclosed in '(...)' and so on

	$matches[2] = $matches[2]*1;
	if ($matches[2] > $maxWidth) {
		$matches[2] = $maxWidth;
		return $matches[1].$matches[2].$matches[3];
	} else {
		return $matches[0];
	}
}


function checkSizes_Sidekolonne($matches) {
	$maxWidth = 125;
  // as usual: $matches[0] is the complete match
  // $matches[1] the match for the first subpattern
  // enclosed in '(...)' and so on

	$matches[2] = $matches[2]*1;
	if ($matches[2] > $maxWidth) {
		$matches[2] = $maxWidth;
		return $matches[1].$matches[2].$matches[3];
	} else {
		return $matches[0];
	}
}


 
 function outputSectionTable($sectionid, $bookid, $highestLevelOnPage, $forPDF){
  global $numberTo, $screenCSS;
  if (!$numberTo) $numberTo = returnStamdata("NUMBER_TO"); 
  $sql = "select ID, TITLE, CONTENT, CONTENT_SIDE from BOOKSECTIONS where ID='$sectionid' and BOOK_ID='$bookid' and DELETED='N'";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  $thisLevel = returnSectionLevel($sectionid);
  // Her skal der lige køre check på størrelse af billeder

	if ($row[CONTENT_SIDE] != "") {
		$row[CONTENT] = preg_replace_callback(
		   "|(<img[^>]*width=\")([^\"]*)(\"[^>]*>)|i",
		   "checkSizes_HovedkolonneMedSide",
		   $row[CONTENT]);

		$row[CONTENT] = preg_replace_callback(
           "|(<img[^>]*)(height=\")([^\"]*)(\")([^>]*>)|i",
           "checkSizes_Stripheight",
           $row[CONTENT]);
		$row[CONTENT_SIDE] = preg_replace_callback(
		   "|(<img[^>]*width=\")([^\"]*)(\"[^>]*>)|i",
		   "checkSizes_Sidekolonne",
		   $row[CONTENT_SIDE]);

		$row[CONTENT_SIDE] = preg_replace_callback(
          "|(<img[^>]*)(height=\")([^\"]*)(\")([^>]*>)|i",
          "checkSizes_Stripheight",
           $row[CONTENT_SIDE]);
	} else {
		$row[CONTENT] = preg_replace_callback(
		   "|(<img[^>]*width=\")([^\"]*)(\"[^>]*>)|i",
		   "checkSizes_Hovedkolonne",
		   $row[CONTENT]);

		$row[CONTENT] = preg_replace_callback(
           "|(<img[^>]*)(height=\")([^\"]*)(\")([^>]*>)|i",
           "checkSizes_Stripheight",
           $row[CONTENT]);	
	}

  // SKÆRM-HTML-GENERERING STARTER HER
  if ($forPDF == false) {
   // LAV <h> TAGS MED KORREKT CSS
   $headingStartTagName = "<h" . ($thisLevel-$highestLevelOnPage + 1) . " style='$screenCSS[0]'>";
   $headingEndTagName	= "</h" . ($thisLevel-$highestLevelOnPage + 1) . ">";
   $html.= $headingStartTagName . ($numberTo >= $thisLevel ? implode(".", returnSectionPath($row[ID])) . ". " : "") . $row[TITLE] .  $headingEndTagName;
   if (trim($row[CONTENT_SIDE])=="") {
    // HVIS DER IKKE ER EN SIDEBAR...
//    $html .= "<table width='538' cellpadding='0' cellspacing='0' border='0'>";
    $html .= "<table width='100%' cellpadding='0' cellspacing='0' border='0'>";
	$html.= "<tr>";
	$html.= "<td valign='top' width='100%' style='$screenCSS[1]'>$row[CONTENT]</td>";
	$html.= "</tr>";
    $html.= "</table>";
    // HVIS DER FAKTISK ER EN SIDEBAR...
   } else {
//    $html.= "<table width='538' cellpadding='0' cellspacing='0' border='0'>";
    $html.= "<table width='100%' cellpadding='0' cellspacing='0' border='0'>";
	$html.= "<tr>";
//	$html.= "<td valign='top' width='392' class='hovedKolonne' style='$screenCSS[1]'>$row[CONTENT]</td>";
	$html.= "<td valign='top' class='hovedKolonne' style='$screenCSS[1]'>$row[CONTENT]</td>";
	$html.= "<td valign='top' width='11'>&nbsp;</td>";
	$html.= "<td valign='top' width='135' class='sideKolonne' style='$screenCSS[2]'>$row[CONTENT_SIDE]</td>";
	$html.= "</tr>";
    $html.= "</table>";
   }
  }
  // PDF-HTML-GENERERING STARTER HER
  if ($forPDF == true) {
   // HENT FONTE TIL PDF (FONT-TAGS, TAG HØJDE FOR AT NOGLE FONTE HAR FALLBACK-FONTE)
   $pdfFonts = determinePdfFonts(returnStamdata("HEADING_FONT"), returnStamdata("MAIN_FONT"), returnStamdata("SIDEBAR_FONT"));
   // HVIS DER SKAL VISES KAPITELFORSIDER, OG VI ER PÅ LEVEL 1 = KAPITELNIVEAU...
   if (returnStamdata("SHOW_CHAPTER_FRONTPAGES") == "Y" && returnSectionLevel($sectionid) == 1) {
    $headingStartTagName = "<h" . ($thisLevel-$highestLevelOnPage + 1) . "><font face='".$pdfFonts[0]."'>";
    $headingEndTagName	= "</font></h" . ($thisLevel-$highestLevelOnPage + 1) . ">";
    $html.= "<center>" . $headingStartTagName . ($numberTo >= $thisLevel ? implode(".", returnSectionPath($row[ID])) . ". " : "") . $row[TITLE] .  $headingEndTagName . "</center><!--NEW PAGE -->";
   } else {
    // HVIS DER IKKE SKAL VISES KAPITELFORSIDER...
    $headingStartTagName = "<h" . ($thisLevel-$highestLevelOnPage + 1) . "><font face='".$pdfFonts[0]."'>";
    $headingEndTagName	= "</font></h" . ($thisLevel-$highestLevelOnPage + 1) . ">";
    $html.= $headingStartTagName . ($numberTo >= $thisLevel ? implode(".", returnSectionPath($row[ID])) . ". " : "") . $row[TITLE] .  $headingEndTagName;
   }
   // HVIS DER IKKE ER EN SIDEKOLONNE, PDF
   if (trim($row[CONTENT_SIDE])=="") {
    $html .= "<table width='538' cellpadding='0' cellspacing='0' border='0'>";
	$html.= "<tr>";	
	$html.= "<td valign='top' align='justify' width='100%'><font face='".$pdfFonts[1]."'>";
	$html.= preg_replace("@<p[^>]*?>@si", "\\0"."<font face='".$pdfFonts[1]."'>", $row[CONTENT]);
	$html.= "</font></td>";
	$html.= "</tr>";
    $html.= "</table>";
	$html.= sectionSeperator($forPDF); // BARE EN DIV, DER LAVER LIDT LUFT
   // HVIS DER FAKTISK ER EN SIDEKOLONNE, PDF
   } else {
    $html.= "<table width='538' cellpadding='2' cellspacing='0' border='0'>";
	$html.= "<tr>";
	$html.= "<td valign='top' width='392' align='justify'><font face='".$pdfFonts[1]."'>";
	$html.= preg_replace("@<p[^>]*?>@si", "\\0"."<font face='".$pdfFonts[1]."'>", $row[CONTENT]);
	$html.= "</font></td>";
	$html.= "<td valign='top' width='11'>&nbsp;</td>";
	$html.= "<td valign='top' width='135' bgcolor='#eeeeee'><font face='".$pdfFonts[2]."'>";
	$html.= preg_replace("@<p[^>]*?>@si", "\\0"."<font face='".$pdfFonts[2]."'>", $row[CONTENT_SIDE]);
	$html.= "</font></td>";
	$html.= "</tr>";
    $html.= "</table>";    
	$html.= sectionSeperator($forPDF);
   }
  }
  return $html;
 }
  
 function outputCollatedSubsections($parentSectionId, $bookid, $highestLevelOnPage, $forPDF){
  global $collectedHTML;
  $sql = "select ID, TITLE, CONTENT, CONTENT_SIDE from BOOKSECTIONS where PARENT_ID='$parentSectionId' and DELETED='N' and BOOK_ID='$bookid' order by POSITION asc";
  $result = mysql_query($sql);
  while($row = mysql_fetch_array($result)) {
   if ($forPDF == false) {
    $collectedHTML .= outputSectionTable($row[ID], $bookid, $highestLevelOnPage, $forPDF) . sectionSeperator($forPDF); 
	outputCollatedSubsections($row[ID], $bookid, $highestLevelOnPage, $forPDF);
   }
   if ($forPDF == true) {
	if (returnSectionLevel($row[ID]) <= returnStamdata("SHOW_TOGETHER_FROM")) {
	 if ($collectedHTML != "") $collectedHTML .= pageSeperator();
    }
    $collectedHTML .= outputSectionTable($row[ID], $bookid, $highestLevelOnPage, $forPDF);
	outputCollatedSubsections($row[ID], $bookid, $highestLevelOnPage, $forPDF);
   }
  }
  return $collectedHTML; 
 }

 function sectionSeperator($forPDF){
  if ($forPDF == false) {
   $html = "<div class='sectionSeperator'></div>";
  }
  if ($forPDF == true) {
   $html = "<br>";
  }
  return $html;
 }

 function pageSeperator($forPDF = true){
  if ($forPDF == true) {
   $html = "<!-- NEW PAGE -->";
  }
  return $html;
 } 

 function outputBookTitle(){
  $danish_months = array(1 => "januar", "februar", "marts", "april", "maj", "juni", "juli", "august", "september", "oktober", "november", "december");
  if (returnStamdata("BOOKTITLE")) $html.= "<h1 class='booktitle'>".returnStamdata("BOOKTITLE")."</h1>";
  if (returnStamdata("SUBTITLE"))  $html.= "<h2 class='booksubtitle'>".returnStamdata("SUBTITLE")."</h2>";
  if (returnStamdata("PUBLISHER")) $html.= "<h3 class='bookpublisher'>Af ".returnStamdata("PUBLISHER").", ".$danish_months[returnStamdata("PUBMONTH")]." ".returnStamdata("PUBYEAR")."</h3>";
  return $html;
 }
 
 function outputPDFPage(){ 
  global $pluginsPathBrowser, $imagesFolderPath;
  $screenCSS = determineScreenCSS(returnStamdata("HEADING_FONT"), returnStamdata("MAIN_FONT"), returnStamdata("SIDEBAR_FONT"), returnStamdata("CAPTION_FONT")); 
  $html.= "<h1 style='$screenCSS[0]'><a href=\"".$pluginsPathBrowser."/bookmaker/bookmaker_pdfgenerator.php?bookid=$_GET[bookid]\">Klik her for at hente bogen \"<strong>".returnStamdata("BOOKTITLE")."</strong>\" som en PDF-fil</a></h1>
    Du kan også klikke med højre museknap på linket og vælge \"Gem destination som...\" for at gemme PDF-filen på din egen computer.
   </p>
   <p>
    Fordelen ved at have bogen som en PDF-fil er, at den kan printes ud i meget fin kvalitet. Hvis din computer ikke vil åbne PDF-filen,
	er det muligvis fordi, at du mangler programmet \"Adobe Reader\", som bruges til at vise PDF-filer med. 
	Hvis du ikke har Adobe Reader på din computer, kan du hente den ved at klikke på nedenstående knap. 
   </p>
   <p>
    <a href='http://www.adobe.com/products/acrobat/readstep2.html' title='Hent Adobe Reader'><img border='0' src='".$imagesFolderPath."/get_adobe_reader.gif' title='Hent Adobe Reader' alt='Hent Adobe Reader'/></a>
   </p>
   <p>
    <a href='http://www.adobe.com/products/acrobat/readstep2.html' title='Hent Adobe Reader'>Du kan også klikke her for at hente Adobe Reader</a>.
   </p>
   <p>
    &raquo;&nbsp;<a href='index.php?pageid=$_GET[pageid]&amp;bookid=$_GET[bookid]&amp;sectionid=CONTENTS'>Tilbage til indholdsfortegnelsen</a>.
   </p>
   ";
  return $html;
 }

 
 function determineScreenCSS($headingFont, $mainFont, $sidebarFont){
  switch ($headingFont) {
   case "verdana": 	$headingCSS = "font-family:verdana";break;
   case "arial": 	$headingCSS = "font-family:arial";break;
   case "helvetica":$headingCSS = "font-family:helvetica";break;
   case "times": 	$headingCSS = "font-family:\"times new roman\"";break;
   case "georgia": 	$headingCSS = "font-family:georgia";break;
   case "courier": 	$headingCSS = "font-family:courier";break;
   default:			$headingCSS = "font-family:verdana";break;
  }
  switch ($mainFont) {
   case "verdana": 	$mainColumnCSS = "font:11px/17px verdana";break;
   case "arial": 	$mainColumnCSS = "font:12px/17px arial";break;
   case "helvetica":$mainColumnCSS = "font:12px/17px helvetica";break;
   case "times": 	$mainColumnCSS = "font:13px/17px \"times new roman\"";break;
   case "georgia": 	$mainColumnCSS = "font:11px/17px georgia";break;
   case "courier": 	$mainColumnCSS = "font:11px/17px courier";break;
   default:			$mainColumnCSS = "font:11px/17px verdana";break;
  }
  switch ($sidebarFont) {
   case "verdana": 	$sideColumnCSS = "font:11px/17px verdana";break;
   case "arial": 	$sideColumnCSS = "font:12px/17px arial";break;
   case "helvetica":$sideColumnCSS = "font:12px/17px helvetica";break;
   case "times": 	$sideColumnCSS = "font:13px/17px \"times new roman\"";break;
   case "georgia": 	$sideColumnCSS = "font:11px/17px georgia";break;
   case "courier": 	$sideColumnCSS = "font:11px/17px courier";break;
   default:			$sideColumnCSS = "font:11px/17px verdana";break;
  }  
  return array($headingCSS, $mainColumnCSS, $sideColumnCSS);
 }

 function determinePdfFonts($headingFont, $mainFont, $sidebarFont){
  switch ($headingFont) {
   case "verdana": 	$headingFontFace = "Helvetica";break;
   case "arial": 	$headingFontFace = "Arial";break;
   case "helvetica":$headingFontFace = "Helvetica";break;
   case "times": 	$headingFontFace = "Times";break;
   case "georgia": 	$headingFontFace = "Times";break;
   case "courier": 	$headingFontFace = "Courier";break;
   default:			$headingFontFace = "Helvetica";break;
  }
  switch ($mainFont) {
   case "verdana": 	$mainColumnFontFace = "Helvetica";break;
   case "arial": 	$mainColumnFontFace = "Arial";break;
   case "helvetica":$mainColumnFontFace = "Helvetica";break;
   case "times": 	$mainColumnFontFace = "Times";break;
   case "georgia": 	$mainColumnFontFace = "Times";break;
   case "courier": 	$mainColumnFontFace = "Courier";break;
   default:			$mainColumnFontFace = "Helvetica";break;
  }
  switch ($sidebarFont) {
   case "verdana": 	$sideColumnFontFace = "Helvetica";break;
   case "arial": 	$sideColumnFontFace = "Arial";break;
   case "helvetica":$sideColumnFontFace = "Helvetica";break;
   case "times": 	$sideColumnFontFace = "Times";break;
   case "georgia": 	$sideColumnFontFace = "Times";break;
   case "courier": 	$sideColumnFontFace = "Courier";break;
   default:			$sideColumnFontFace = "Helvetica";break;
  }  
  return array($headingFontFace, $mainColumnFontFace, $sideColumnFontFace);
 } 
 
 function buildHTMLForPDF($bookid){
  global $pluginsPath;
  // BOG-FIL
  ob_start();
  echo outputCollatedSubsections(0, $bookid, 1, true);
  $PDFbook = ob_get_clean();
  $PDFbook = str_replace("<table id=\"matrix\"", "<font size='1'><table id=\"matrix\"", $PDFbook);
  $PDFbook = str_replace("<!-- END MATRIX TABLE -->", "</font><!-- END MATRIX TABLE -->", $PDFbook);
  $htmlFilePath = $pluginsPath . "/bookmaker/htmlfiles/book_".$bookid.".html";
  $fileHandle = fopen($htmlFilePath, "w");
  fwrite($fileHandle, $PDFbook);
  fclose($fileHandle);
  // FORSIDE-FIL
  ob_start();
  echo htmlFrontpage($bookid, true);
  $PDFFrontpage = ob_get_clean();
  $htmlFilePath = $pluginsPath . "/bookmaker/htmlfiles/frontpage_".$bookid.".html";
  $fileHandle = fopen($htmlFilePath, "w");
  fwrite($fileHandle, $PDFFrontpage);
  fclose($fileHandle);
  return true;
 }
 
 function returnStamdata($key){
  if ($_GET[bookid]) {
   $bookid = $_GET[bookid];
  } elseif ($_GET[book_id]) {
   $bookid = $_GET[book_id];
  } elseif ($_GET[section_id]) {
   $bookid = db_returnBookfromSectionId($_GET[section_id]);
  }
  $sql = "select BOOKTITLE, SUBTITLE, PUBLISHER, PUBMONTH, PUBYEAR, PICTUREFOLDER_ID from BOOKS where ID='$bookid' and DELETED='N'";
  $result = mysql_query($sql);
  $stamdata = mysql_fetch_array($result);
  $sql = "select * from BOOKSTYLES where BOOK_ID='$bookid'";
  $result = mysql_query($sql);
  $stamdata = array_merge($stamdata, mysql_fetch_array($result));
  return $stamdata[$key];
 }
 
 function bookExists($bookid){
  $sql = "select BOOKTITLE from BOOKS where ID='$bookid' and DELETED='N'";
  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);
  if (mysql_num_rows($result)==0) return false;
  if (mysql_num_rows($result)==1) return $row[BOOKTITLE];
 }
 
 function returnValidBookFileName($booktitle){ 
  $booktitle = str_replace("\"", "", $booktitle);
  $booktitle = str_replace("'", "", $booktitle);
  $booktitle = str_replace("/", "", $booktitle);
  $booktitle = str_replace("\\", "", $booktitle);
  $booktitle = trim($booktitle);
  $booktitle = str_replace(" ", "_", $booktitle);
  $filename = $booktitle . ".pdf";  
  return $filename;
 }
 
 function chapterFrontpage($sectionid){
  $title = returnStamdata("BOOKTITLE");
  $html .= "<!-- NEW PAGE --><center>$title</center>$sectionid";
  return $html;
 }
 
 function htmlFrontpage($bookid, $forPDF){
  $pdfFonts = determinePdfFonts(returnStamdata("HEADING_FONT"), returnStamdata("MAIN_FONT"), returnStamdata("SIDEBAR_FONT"));
  $maxImageWidth = 500;
  $danish_months = array(1 => "januar", "februar", "marts", "april", "maj", "juni", "juli", "august", "september", "oktober", "november", "december");
  if ($forPDF==false){
   if (returnStamdata("BOOKTITLE")) $html.= "<h1 class='booktitle' style='$screenCSS[0]'>".returnStamdata("BOOKTITLE")."</h1>";
   if (returnStamdata("SUBTITLE"))  $html.= "<h2 class='booksubtitle' style='$screenCSS[0]'>".returnStamdata("SUBTITLE")."</h2>";
   if (returnStamdata("COVERIMAGE_URL")) {
    $fileinfo = getimagesize(returnStamdata("COVERIMAGE_URL"));
	if ($fileinfo[0] > $maxImageWidth) $widthStr = "width='100%'";
    $html.= "<p><img src='".returnStamdata("COVERIMAGE_URL")."' border='1' $widthStr alt='Forsidebillede' title='Forsidebillede'></p>";
   }
   if (returnStamdata("PUBLISHER")) $html.= "<h3 class='bookpublisher' style='$screenCSS[0]'>Af ".returnStamdata("PUBLISHER").", ".$danish_months[returnStamdata("PUBMONTH")]." ".returnStamdata("PUBYEAR")."</h3>";
  }
  if ($forPDF==true){
   $html.= "<center>";
   if (returnStamdata("BOOKTITLE")) $html.= "<h1><font face='".$pdfFonts[0]."'>".returnStamdata("BOOKTITLE")."</h1>";
   if (returnStamdata("SUBTITLE"))  $html.= "<h2><font face='".$pdfFonts[0]."'>".returnStamdata("SUBTITLE")."</h2>";
   if (returnStamdata("COVERIMAGE_URL")) $html.= "<p><img src='".returnStamdata("COVERIMAGE_URL")."' border='1'></p>";
   if (returnStamdata("PUBLISHER")) $html.= "<h3><font face='".$pdfFonts[0]."'>	Af ".returnStamdata("PUBLISHER").", ".$danish_months[returnStamdata("PUBMONTH")]." ".returnStamdata("PUBYEAR")."</h3>";
   $html.= "</center>";
  }
  return $html;
 }
 
 function returnAllBooks(){
  $sql = "select ID, BOOKTITLE from BOOKS where DELETED='N'";
  $result = mysql_query($sql);
  while ($row = mysql_fetch_array($result)){
   $html .= "&raquo;&nbsp;<a href='?pageid=$_GET[pageid]&amp;bookid=$row[ID]'>$row[BOOKTITLE]</a><br/>";
  }
  return $html;
 }
 
 function deleteHTMLForPDF($bookid){
  global $pluginsPath;
  $bookfile  = $pluginsPath . "/bookmaker/htmlfiles/book_".$bookid.".html";
  $frontfile = $pluginsPath . "/bookmaker/htmlfiles/frontpage_".$bookid.".html";
  if (file_exists($bookfile)){
   unlink($bookfile);
  }  
  if (file_exists($frontfile)){
   unlink($frontfile);
  }  
 }
 
 function validGrant($grant){
  $sql = "select * from GRANTS where GRANTCODE='$grant'";
  $result = mysql_query($sql);
  if (mysql_num_rows($result)==1) {
   return true;
  } else {
   return false;
  }
  return false;
 }

?>