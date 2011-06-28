<?php
if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_BOOKMAKER", true);

// include_once("bookmaker.common.inc.php");
// What to do?
switch ($_REQUEST['do']) {
	case 'db_sectioninsert':
		db_sectioninsert();
		break;
	case 'db_sectionupdate':
		db_sectionupdate();
		break;
	case 'db_bookinsert':
		db_bookinsert();
		show_booklist();
		break;
	case 'db_bookupdate':
		db_bookupdate();
		break;
	case 'db_matrixinsert':
		db_matrixinsert();
		break;
	case 'db_matrixupdate':
		db_matrixupdate();
		break;
	case 'db_delete_book':
		db_setdeleted($_GET[book_id], "BOOKS");
		break;
	case 'db_delete_section':
		db_setdeleted($_GET[section_id], "BOOKSECTIONS");
		break;
	case 'db_delete_matrix':
		db_setdeleted($_GET[matrix_id], "BOOKMATRICES");
		break;
	case 'db_section_up':
		moveOneUp($_GET[section_id]);
		break;
	case 'db_section_down':
		moveOneDown($_GET[section_id]);
		break;
}
?>