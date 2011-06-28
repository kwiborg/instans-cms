<?php
function update_dropbox_que($id, $status, $batch_number) {
	// Mark item as processed $status 1 = ok, 2 = error
	$sql = "update PICTUREARCHIVE_IMPORTQUE set PROCESSED = '$status' where ID = $id";
	mysql_query($sql);
	// Check if que contains unprocessed items
	$sql = "select
				count(*)
			from
				PICTUREARCHIVE_IMPORTQUE
			where
				BATCH_NUMBER = '$batch_number' and
				PROCESSED = '0'";
	$res = mysql_query($sql);
	$remaining = mysql_result($res,0);
	return $remaining;
}

function process_dropbox_que($batch_number) {
	global $picturearchive_Uploaddir;
	$sql = "select
				*
			from
				PICTUREARCHIVE_IMPORTQUE
			where
				BATCH_NUMBER = '$batch_number' and
				PROCESSED = '0'
			order by
				ID asc
			LIMIT 1";
	$res = mysql_query($sql);
	if (mysql_num_rows($res) > 0) {
		$row = mysql_fetch_assoc($res);
/*
		echo "<pre>";
		print_r($row);
		echo "</pre>";
*/
		if ($fileid = uploadDropboxBillede($row[PATH], $row[TARGET_GROUP], $row[NAME])) {
			if (generateThumb($row[TARGET_GROUP], $fileid)) {
				// Upload and thumbnail generation successful
				// Mark item as processed
				$rem_items = update_dropbox_que($row[ID], 1, $batch_number);
				if ($rem_items > 0) {
					return "success|||continue|||$rem_items|||$row[NAME]";
				} else {
					$sql = "select
								count(*)
							from
								PICTUREARCHIVE_IMPORTQUE
							where
								BATCH_NUMBER = '$batch_number' and
								PROCESSED = '1'";
					$res = mysql_query($sql);
					$success_count = mysql_result($res,0);
					$sql = "select
								count(*)
							from
								PICTUREARCHIVE_IMPORTQUE
							where
								BATCH_NUMBER = '$batch_number' and
								PROCESSED = '2'";
					$res = mysql_query($sql);
					$error_count = mysql_result($res,0);
					return "success|||complete|||$success_count|||$error_count|||$row[NAME]";
				}
			} else {
				// Remove from database
				$sql = "delete from PICTUREARCHIVE_PICS where ID = '$fileid'";
				mysql_query($sql);
				// Mark item as processed with error
				$rem_items = update_dropbox_que($row[ID], 2, $batch_number);
				$errorfile_path = str_replace($picturearchive_Uploaddir."/temp/", "", $row[PATH]);
				if ($rem_items > 0) {
					return "error|||continue|||thumb|||$row[ID]|||$row[NAME]|||$row[EXTENSION]|||$row[SIZE]|||$errorfile_path|||$rem_items";
				} else {
					$sql = "select
								count(*)
							from
								PICTUREARCHIVE_IMPORTQUE
							where
								BATCH_NUMBER = '$batch_number' and
								PROCESSED = '1'";
					$res = mysql_query($sql);
					$success_count = mysql_result($res,0);
					$sql = "select
								count(*)
							from
								PICTUREARCHIVE_IMPORTQUE
							where
								BATCH_NUMBER = '$batch_number' and
								PROCESSED = '2'";
					$res = mysql_query($sql);
					$error_count = mysql_result($res,0);
					return "error|||complete|||thumb|||$row[ID]|||$row[NAME]|||$row[EXTENSION]|||$row[SIZE]|||$errorfile_path|||$success_count|||$error_count";
				}
			}
		} else {
			// Mark item as processed with error
			$rem_items = update_dropbox_que($row[ID], 2, $batch_number);
			$errorfile_path = str_replace($picturearchive_Uploaddir."/temp/", "", $row[PATH]);
			if ($rem_items > 0) {
				return "error|||continue|||upload|||$row[ID]|||$row[NAME]|||$row[EXTENSION]|||$row[SIZE]|||$errorfile_path";
			} else {
				$sql = "select
							count(*)
						from
							PICTUREARCHIVE_IMPORTQUE
						where
							BATCH_NUMBER = '$batch_number' and
							PROCESSED = '1'";
				$res = mysql_query($sql);
				$success_count = mysql_result($res,0);
				$sql = "select
							count(*)
						from
							PICTUREARCHIVE_IMPORTQUE
						where
							BATCH_NUMBER = '$batch_number' and
							PROCESSED = '2'";
				$res = mysql_query($sql);
				$error_count = mysql_result($res,0);
				return "error|||complete|||thumb|||$row[ID]|||$row[NAME]|||$row[EXTENSION]|||$row[SIZE]|||$errorfile_path|||$success_count|||$error_count";
			}
		}	
	} else {
		// No unprocessed items in que
		return "success|||complete|||0|||0";
	}
}


function scan_directory_recursively($directory, $filter=FALSE) {
// to use this function to get all files and directories in an array, write:
// $filestructure = scan_directory_recursively('path/to/directory');

// to use this function to scan a directory and filter the results, write:
// $fileselection = scan_directory_recursively('directory', 'extension');


    // if the path has a slash at the end we remove it here
    if(substr($directory,-1) == '/')
    {
        $directory = substr($directory,0,-1);
    }
 
    // if the path is not valid or is not a directory ...
    if(!file_exists($directory) || !is_dir($directory))
    {
        // ... we return false and exit the function
        return FALSE;
 
    // ... else if the path is readable
    }elseif(is_readable($directory))
    {
        // we open the directory
        $directory_list = opendir($directory);
 
        // and scan through the items inside
        while (FALSE !== ($file = readdir($directory_list)))
        {
            // if the filepointer is not the current directory
            // or the parent directory
            if($file != '.' && $file != '..')
            {
                // we build the new path to scan
                $path = $directory.'/'.$file;
 
                // if the path is readable
                if(is_readable($path))
                {
                    // we split the new path by directories
                    $subdirectories = explode('/',$path);
 
                    // if the new path is a directory
                    if(is_dir($path))
                    {
                        // add the directory details to the file list
                        $directory_tree[] = array(
                            'path'    => $path,
                            'name'    => end($subdirectories),
                            'kind'    => 'directory',
 
                            // we scan the new path by calling this function
                            'content' => scan_directory_recursively($path, $filter));
 
                    // if the new path is a file
                    }elseif(is_file($path))
                    {
                        // get the file extension by taking everything after the last dot
                        $extension = end(explode('.',end($subdirectories)));
 
                        // if there is no filter set or the filter is set and matches
                        if($filter === FALSE || $filter == $extension)
                        {
                            // add the file details to the file list
                            $directory_tree[] = array(
                                'path'      => $path,
                                'name'      => end($subdirectories),
                                'extension' => $extension,
                                'size'      => filesize($path),
                                'kind'      => 'file');
                        }
                    }
                }
            }
        }
        // close the directory
        closedir($directory_list); 
 
        // return file list
        return $directory_tree;
 
     // if the path is not readable ...
    }else{
        // ... we return false
        return FALSE;    
    }
}


function copydirr($fromDir,$toDir,$chmod=0757,$verbose=false) {
/*
26.07.2005
Author: Anton Makarenko
   makarenkoa at ukrpost dot net
   webmaster at eufimb dot edu dot ua
*/
/*
   copies everything from directory $fromDir to directory $toDir
   and sets up files mode $chmod
   Returns array with created directories
*/
/* sample usage:
WARNING:
if You set wrong $chmod then You'll not be able to access files and directories
in destination directory.
For example: once upon a time I've called the function with parameters:
copydir($fromDir,$toDir,true);
What happened? I've forgotten one parameter (chmod)
What happened next? Those files and directories became inaccessible for me
(they had mode 0001), so I had to ask sysadmin to delete them from root account
Be careful :-)
copydirr('./testSRC','D:/srv/Apache2/htdocs/testDEST',0777,true);
*/

//* Check for some errors
$errors=array();
$messages=array();
if (!is_writable($toDir))
   $errors[]='target '.$toDir.' is not writable';
if (!is_dir($toDir))
   $errors[]='target '.$toDir.' is not a directory';
if (!is_dir($fromDir))
   $errors[]='source '.$fromDir.' is not a directory';
if (!empty($errors))
   {
   if ($verbose)
       foreach($errors as $err)
           echo '<strong>Error</strong>: '.$err.'<br />';
   return false;
   }
//*/
$exceptions=array('.','..','.ftpquota','.htaccess');
//* Processing
$handle=opendir($fromDir);
while (false!==($item=readdir($handle)))
   if (!in_array($item,$exceptions))
       {
       //* cleanup for trailing slashes in directories destinations
       $from=str_replace('//','/',$fromDir.'/'.$item);

       $to=str_replace('//','/',$toDir.'/'.$item);
		$to = utf8_encode($to);

       //*/
       if (is_file($from))
           {
           if (@copy($from,$to))
               {
               chmod($to,$chmod);
               touch($to,filemtime($from)); // to track last modified time
               $messages[]='File copied from '.$from.' to '.$to;
               }
           else
               $errors[]='cannot copy file from '.$from.' to '.$to;
           }
       if (is_dir($from))
           {
           if (@mkdir($to, 0777))
               {
               chmod($to,$chmod);
               $messages[]='Directory created: '.$to;
               }
           else
               $errors[]='cannot create directory '.$to;
	           copydirr($from,$to,$chmod,$verbose);
           }
       }
closedir($handle);
// Output
/*
if ($verbose) {
   foreach($errors as $err)
       echo '<strong>Error</strong>: '.$err.'<br />';
   foreach($messages as $msg)
       echo $msg.'<br />';
   }
*/
return true;
}


function returnImageCaptureTime($file) {
	if (@$exif = exif_read_data($file, 0, true)) {
		if ($time = $exif[EXIF][DateTimeOriginal]) {	 // 2004:07:01 14:25:18
			$year = substr($time, 0, 4);
			$month = substr($time, 5, 2);
			$day = substr($time, 8, 2);
			$hour =  substr($time, 11, 2);
			$minute =  substr($time, 14, 2);
			$second =  substr($time, 17, 2);
			return mktime($hour, $minute, $second, $month, $day, $year);
		} else {
			return time();
		}
	} else {
		return time();
	}
}

function uploadDropboxBillede($file, $folder_id, $name) {
	global $dropboxUploaddir;
	// Translate $_POST vars
		/*
		[imageMaxSize] => 600
		[imageMinOriginalsize] => 750
		[imageKeepOriginal] => 0
		[imageTitle] => useFilename (default) / useCustom / useUploadtime / useCapturetime
		[imageAlt] => [used on imageTitle = useCustom]
		[imageDescription] => useFilename / useCustom / useUploadtime / useCapturetime  (default)
		[imageCustomDescription] =>  [used on imageDescription = useCustom]
		*/
	$imageMaxSize = $_POST["imageMaxSize"];
	$imageMinOriginalsize = $_POST["imageMinOriginalsize"];
	$imageKeepOriginal = $_POST["imageKeepOriginal"];
	$imageCustomDescription = $_POST["imageCustomDescription"];

	switch ($_POST["imageTitle"]) {
		case 'useCustom':
			$imageTitle = $_POST[imageAlt];
			break;
		case 'useFilename':
			$imageTitle = $name;
			break;
		case 'useUploadtime':
			$imageTitle = returnNiceDateTime(time(),1);
			break;
		case 'useCapturetime':
			$imageTitle = returnNiceDateTime(returnImageCaptureTime($file),1);
			break;
	}
	$alttext = $imageTitle;

	switch ($_POST["imageDescription"]) {
		case 'useCustom':
			$imageDescription = $_POST[imageCustomDescription];
			break;
		case 'useFilename':
			$imageDescription = $name;
			break;
		case 'useUploadtime':
			$imageDescription = returnNiceDateTime(time(),1);
			break;
		case 'useCapturetime':
			$imageDescription = returnNiceDateTime(returnImageCaptureTime($file),1);
			break;
	}
	$description = $imageDescription;
	
	// Copy file to correct new foldername/filename
	$new_filename = time().str_makerand(4,4);
	$dest_folder = returnFolderName($folder_id, 1, "PICTUREARCHIVE_FOLDERS");
	$dest_file = "$dropboxUploaddir/$dest_folder/$new_filename";
	if (!copy($file,$dest_file)) {
		return false;

/*		$usermessage_error = "Fejl i overførsel fra dropbox!";
		header("Location: index.php?content_identifier=picturearchive&&usermessage_error=$usermessage_error");
		exit;
*/	}
	chmod("$dropboxUploaddir/$dest_folder/$new_filename", 0755);

	// Get image information (size/filetype)
	if (@!$info = getimagesize("$dropboxUploaddir/$dest_folder/$new_filename")) {
		return false;
	}

	$imagetype = $info[2];
	$nu = time();
	switch ($imagetype = $info[2]) {
  		case 1: $extension = "gif"; break;
		case 2: $extension = "jpg"; break;	
		case 3: $extension = "png"; break;
		default: return false; break;
	}
	
	// Add extension to filename
	$new_filename_2 = $new_filename . "." . $extension;
	$nu = time();
	rename("$dropboxUploaddir/$dest_folder/$new_filename", "$dropboxUploaddir/$dest_folder/$new_filename_2");

	// Get image size
	$iw = $info[0];
	$ih = $info[1];

	// Archive original, if requested and image is above required minimum size for originals
	if ($iw > $ih) {
		$compare_to = $iw;
	} else {
		$compare_to = $ih;
	}
	if ($imageKeepOriginal == "1" && $compare_to > $imageMinOriginalsize) {
		$dest_file_original = "$dropboxUploaddir/$dest_folder/originals/$new_filename_2";
		if (!copy($file,$dest_file_original)) {
			return false;
		}
		$original_archived = 1;
		chmod("$dropboxUploaddir/$dest_folder/originals/$new_filename_2", 0755);
	} else {
		$original_archived = 0;
	}

	// Insert into database
	$sql = "insert into PICTUREARCHIVE_PICS 
   (FOLDER_ID, FILENAME, ORIGINAL_FILENAME, AUTHOR_ID, UNFINISHED, CREATED_DATE, IMAGETYPE, DESCRIPTION, ALTTEXT, SIZE_X, SIZE_Y, ORIGINAL_ARCHIVED)
  values
   ('$folder_id', '$new_filename_2', '$name', '" . $_SESSION["CMS_USER"]["USER_ID"] . "', '0', '$nu', '$imagetype', '$description', '$alttext', '$iw', '$ih', '$original_archived')"; 
  mysql_query( $sql) or die(mysql_error());
  $new_id = mysql_insert_id();
  $sql = "select max(POSITION) as MAXPOS from PICTUREARCHIVE_PICS where FOLDER_ID='$folder_id'";
  $result = mysql_query( $sql) or die(mysql_error());
  $row = mysql_fetch_array($result);
  $sql = "update PICTUREARCHIVE_PICS set POSITION='" . ($row[0]+1) . "' where ID='$new_id'";
  mysql_query( $sql) or die(mysql_error());

	// resize if necessary
	if ($ih > $imageMaxSize || $iw > $imageMaxSize) {
		$image_id = $new_id;
		if ($_GET[quality] == "") {
			$quality = 85;
		} else {
			$quality = $_GET[quality];
		}

		if ($iw > $ih) {
			$scaledownfactor = $iw / $imageMaxSize;
		} else {
			$scaledownfactor = $ih / $imageMaxSize;
		}
		$imagewidth = round($iw / $scaledownfactor);
		$imageheight = round($ih / $scaledownfactor);

		if (!resizeBillede($folder_id, $image_id, $imagewidth, $imageheight, $imagetype, $quality, $description, $alttext)) {
			return false;
		}
	}

  return $new_id;
}

function process_dropbox_array($array, $batch_number, $keep_structure, $dir=0) {
	if (count($array) > 0) {
		# recursively walk array adding folders and images to database
		$allowed_extensions = array("jpg", "jpeg", "gif", "png");
		$today = "(".date("j/m Y")." kl. ".date("G:i").")";
		if ($dir == "0") {
			$dir = gemBilledMappe("Importeret fra FTP dropbox $today", "", $dir);
		}
		$defaultdir = $dir;
		foreach($array as $key=>$value) {
			if ($array[$key][kind] == "directory") {
				if ($dir == "0") {
					$prefix = "Importeret fra FTP dropbox $today: ";
				} else {
					$prefix = "";
				}
				if ($keep_structure == 1) {
					$dir = gemBilledMappe($prefix.$array[$key][name], "", $dir);
				}
				process_dropbox_array($array[$key][content], $batch_number, $keep_structure, $dir);
				$dir = $defaultdir;
			} else {
				if (in_array(strtolower($array[$key][extension]), $allowed_extensions)) {
					// Place file into import que
					$sql = "INSERT INTO
								PICTUREARCHIVE_IMPORTQUE 
									(  `ID` ,  `BATCH_NUMBER` ,  `TARGET_GROUP` ,  `PATH` ,  `NAME` ,  `EXTENSION` ,  `SIZE` ,  `PROCESSED` ) 
								VALUES 
									( NULL ,  '$batch_number',  '$dir',  '".$array[$key][path]."',  '".$array[$key][name]."',  '".$array[$key][extension]."',  '".$array[$key][size]."',  '0' )";
					mysql_query($sql);
				}
			}
		}
		return $array;
	}
}
?>