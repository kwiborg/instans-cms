<?php
include($_SERVER[DOCUMENT_ROOT]."/cms_config.inc.php");
include_once($_SERVER[DOCUMENT_ROOT]."/cms/common.inc.php");
include($_SERVER[DOCUMENT_ROOT]."/cms/modules/filearchive2/filearchive2_common.inc.php");


$result = array();

	$original_filename = $_FILES[Filedata][name];
	$extension = extension($original_filename);
	$nu = time();
	$des_filename = $nu.str_makerand(4,4);
	$des_extension_filename = $des_filename.".".$extension;
	$des = $_SERVER[DOCUMENT_ROOT]."/includes/uploaded_files/".$des_extension_filename;

	if ($_FILES['Filedata']['name'])
	{	
		if (move_uploaded_file($_FILES[Filedata][tmp_name], $des)){	 			
		
			$folder_id = $_GET[folderid];
//			$filetype = return_file_type($_FILES);
			$filetype = return_file_type($original_filename);
			$mimetype = $_FILES[Filedata][type];
		
			$sql = "insert into FILEARCHIVE_FILES (FOLDER_ID, FILENAME, ORIGINAL_FILENAME, TITLE, DESCRIPTION, AUTHOR_ID, CREATED_DATE, EXTENSION, MIMETYPE, FILETYPE_ID) 
			values ('$folder_id', '$des_extension_filename', '$original_filename', '$original_filename', '','" . $_SESSION["CMS_USER"]["USER_ID"] . "', '$nu', '$extension', '$mimetype', '$filetype')";
					
			mysql_query($sql);
			
		
			if ($_SESSION["UPLOADED_FILES"] != "") {		
				$_SESSION["UPLOADED_FILES"] .= "__".mysql_insert_id();
			} else {
				$_SESSION["UPLOADED_FILES"] .= mysql_insert_id();
				$_SESSION["FOLDER_ID"] = $folder_id;
			}			

			$return = array(
				'status' => '1'
			);

		} else {
			$error = 'Der opstod en fejl';
			$return = array(
				'status' => '0',
				'error' => $error
			);

		}
	}
 
if (!headers_sent()) {
	header('Content-type: application/json');
}
echo json_encode($return);
?>