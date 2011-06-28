<?php 
// Output user messages
	if ($_GET[usermessage_ok] != "") {
		usermessage("usermessage_ok", $_GET[usermessage_ok]);
	}
	if ($_GET[usermessage_error] != "") {
		usermessage("usermessage_error", $_GET[usermessage_error]);
	}

// What to do?
switch ($_REQUEST['do']) {
	case 'add_book':
		add_book();
		break;
	case 'edit_book':
		echo book_form($_GET['book_id']);
		break;
	case 'edit_book_contents':
		show_sectionlist($_GET['book_id']);	
		show_matrixlist($_GET['book_id']);
		break;
	case 'add_section': 
		add_section($_GET['book_id'], $_GET['parent_section_id']);
		break;
	case 'edit_section':
		edit_section($_GET['section_id']);
		break;
	case 'edit_matrix':
		$book_id = db_returnBookfromMatrixId($_GET['matrix_id']);
		echo matrix_form($book_id, $_GET['matrix_id']);
		break;
	case 'add_matrix':
		echo matrix_form($_GET['book_id']);
		break;
	default:
		show_booklist();
}
?>
