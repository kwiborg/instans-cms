<?php
header("Content-type: text/html; charset=UTF-8");
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/common.inc.php');
checkLoggedIn();
// Make data safe
foreach ($_POST as $key => $value) {
    $_POST[$key] = db_safedata($value);
}

// Check permissions
if (strstr($_POST['do'], "datapermission")) {
	$permission_to_check = "CMS_SETDATAPERMISSIONS_".$_POST["datatablename"];
//	if (!checkpermission("CMS_SETDATAPERMISSIONS")) {
	if (!checkpermission($permission_to_check) && $_POST['do'] != "ajax_datapermission_listgrants_readonly_plaintext") {
		echo "Du har ikke ret til at ændre datarettigheder";
		exit;
	}
}

// datatablename
/*
echo "<pre>";
print_r($_POST);
echo "</pre>";
*/
switch ($_POST['do']) {
	case 'ajax_datapermission_listgrants':
        echo datapermission_listgrants($_POST["permissionname"], $_POST["datatablename"], $_POST["dataid"], $_POST["request_mode"]);
		break;
	case 'ajax_datapermission_listgrants_readonly_plaintext':
        $str_grants_usergroups = datapermission_listgrants($_POST["permissionname"], $_POST["datatablename"], $_POST["dataid"], "usergroups", true, true);
		$arr_grants_usergroups = explode("|||", $str_grants_usergroups);
		$str_grants_users = datapermission_listgrants($_POST["permissionname"], $_POST["datatablename"], $_POST["dataid"], "users", true, true);
		$arr_grants_users = explode("|||", $str_grants_users);
		
//		print_r($arr_grants_usergroups);
//		print_r($arr_grants_users);
		
		if ($arr_grants_usergroups[0] == "success" && $arr_grants_users[0] == "success") {
			echo "success|||".$arr_grants_usergroups[2]."\n\n".$arr_grants_users[2];
		} else {
			echo "error|||Fejl: Kunne ikke hente rettigheder fra databasen.";
		}
		
		break;
	case 'ajax_datapermission_revokegrant':
		if (datapermission_revokegrant($_POST[grantid])) {
	        echo datapermission_listgrants($_POST["permissionname"], $_POST["datatablename"], $_POST["dataid"], $_POST["request_mode"]);
	    } else {
	    	echo "error|||$_POST[request_mode]|||Kunne ikke slette rettighed.";
	    }
		break;
	case 'ajax_datapermission_listusergroups':
        echo datapermission_returngrouplist($_POST["permissionname"], $_POST["datatablename"], $_POST["dataid"]);
		break;
	case 'ajax_datapermission_listusers':
        echo datapermission_returnuserlist($_POST["permissionname"], $_POST["datatablename"], $_POST["dataid"], $_POST["usergroupid"]);
		break;
	case 'ajax_datapermission_grant':
		echo datapermission_grant($_POST["permissionname"], $_POST["datatablename"], $_POST["dataid"], $_POST["request_mode"], $_POST["grantrecieverid"]);
		break;
	default:
		echo "ajaxresponder running";
		break;	
}

function datapermission_checkgrant($str_permissionname, $str_datatablename, $int_dataid, $str_dbcolumn, $int_grantrecieverid) {
	/*
		Function to check if a grant has alredy been given
	*/
	$sql = "select count(*) 
				from 
					DATA_PERMISSIONS DP, PERMISSIONS P 
				where 
					DP.$str_dbcolumn = '$int_grantrecieverid' and
					DP.DATA_ID = '$int_dataid' and
					DP.DATA_TABLE_NAME = '$str_datatablename' and
					DP.PERMISSION_ID = P.ID and
					P.NAME = '$str_permissionname'";
	if (!$res = mysql_query($sql)) {
		return false;
	} else {
		if (mysql_result($res,0) > 0) {
			return true;
		} else {
			return false;
		}
	}
}

function datapermission_grant($str_permissionname, $str_datatablename, $int_dataid, $str_mode, $int_grantrecieverid) {
	// Check if $str_mode is valid and set target db column
	if ($str_mode == "user") {
		$db_target = "USER_ID";
	} elseif ($str_mode == "usergroup") {
		$db_target = "GROUP_ID";
	} else {
		$status = "error";
		$message = "kunne ikke tildele rettighed (ugyldig modtager/mode)";
	}
	
	// Make sure $str_permissionname is a valid datapermission
	if ($status != "error") {
		$sql = "select ID from PERMISSIONS where NAME = '$str_permissionname' and IS_DATAPERMISSION = 1";
		if (!$res = mysql_query($sql)) {
			$status = "error";
			$message = "kunne ikke tildele rettighed (database fejl i datapermission_grant)";
		}
		if (mysql_num_rows($res) < 1) {
			$status = "error";
			$message = "kunne ikke tildele rettighed ($str_permissionname er ikke en gyldig rettighed)";
		} else {
			$permission_row = mysql_fetch_assoc($res);
			$int_permissionid = $permission_row[ID];
		}
	}

	// Check that the grant is not already given
	if ($status != "error") {
		$sql = "select count(*) 
					from 
						DATA_PERMISSIONS 
					where 
						$db_target = '$int_grantrecieverid' and
						DATA_TABLE_NAME = '$str_datatablename' and
						DATA_ID = '$int_dataid' and
						PERMISSION_ID = '$int_permissionid'";
		if (!$res = mysql_query($sql)) {
			$status = "error";
			$message = "kunne ikke tildele rettighed (database fejl i datapermission_grant/count)";
		} else {
			if (mysql_result($res,0) > 0) {
				$status = "error";
				$message = "kunne ikke tildele rettighed, rettigheden allerede tildelt";
			}
		}
	}	


	if ($status != "error") {
		// Insert grant into database
		$sql = "insert into 
						DATA_PERMISSIONS 
						($db_target, DATA_TABLE_NAME, DATA_ID, PERMISSION_ID)
					values
						('$int_grantrecieverid', '$str_datatablename', '$int_dataid', '$int_permissionid')";
		if (!mysql_query($sql)) {
			$status = "error";
			$message = "kunne ikke tildele rettighed (database fejl i datapermission_grant/insert)";
		} else {
			$status = "success";
		}
	}

	return "$status|||$message|||$str_permissionname|||$str_datatablename|||$int_dataid|||$str_mode|||$int_grantrecieverid";
}

function datapermission_returnuserlist($str_permissionname, $str_datatablename, $int_dataid, $int_usergroupid) {
	/*
		Function to list users in usergroupid
	*/
	$sql = "select U.ID, 
				CONCAT(U.FIRSTNAME, ' ', U.LASTNAME, ' (', U.USERNAME, ')') AS NAME 
			from
				USERS U, USERS_GROUPS UG, GROUPS G
			where
				UG.GROUP_ID = G.ID and
				G.SITE_ID in (0,'$_SESSION[SELECTED_SITE]') and
				U.ID = UG.USER_ID and
				UG.GROUP_ID = '$int_usergroupid' and
				U.UNFINISHED = 0 and
				U.DELETED = 0
			order by
				U.FIRSTNAME asc, U.LASTNAME asc";
	if ($res = mysql_query($sql)) {
		if (mysql_num_rows($res) > 0) {
			$html .= "<table class='oversigt' style='background-color: #fff;'>";
			while ($row = mysql_fetch_assoc($res)) {
				// Check if group already has permission
				// Check if group already has permission
				if (datapermission_checkgrant($str_permissionname, $str_datatablename, $int_dataid, "USER_ID", $row[ID])) {
					$disabled = "disabled ";
				} else {
					$disabled = "notdisabled";
				}				
				$html .= "<tr>
								<td>$row[NAME]</td>
								<td class='datapermission_functions'><input class='lilleknap' id='datapermission_user_grantbutton_".$str_permissionname."_".$int_dataid."_$row[ID]' type='button' onclick='datapermission_grant(\"$str_permissionname\", \"$str_datatablename\", \"$int_dataid\", \"user\", \"$row[ID]\")' value='Tildel rettighed' $disabled /></td>
						 	</tr>";
			}
			$html .= "</table>";
			$status 	= "success";
			$message 	= $html;
		} else {
			$status 	= "success";
			$message 	= "Der er ingen brugere i gruppen.";
		}	
	} else {
		$status 	= "error";
		$message 	= "Kunne ikke hente liste over brugere (database fejl i datapermission_returnuserlist)";
	}

	return "$status|||$message|||$str_permissionname|||$str_datatablename|||$int_dataid|||$int_usergroupid";
}

function datapermission_returngrouplist($str_permissionname, $str_datatablename, $int_dataid) {
	/*
		Function to list available groups
	*/
	$sql = "select G.ID, G.GROUP_NAME
			from
				GROUPS G
			where
				G.SITE_ID in (0,'$_SESSION[SELECTED_SITE]') and
				G.UNFINISHED = 0 and
				G.HIDDEN = 0 and
				G.DELETED = 0
			order by
				G.GROUP_NAME asc";
	if ($res = mysql_query($sql)) {
		if (mysql_num_rows($res) > 0) {
			$html .= "<h3>Tildel rettigheden til brugere og/eller brugergrupper</h3>";
			$html .= "<table class='oversigt'>";
			while ($row = mysql_fetch_assoc($res)) {
				// Check if group already has permission
				if (datapermission_checkgrant($str_permissionname, $str_datatablename, $int_dataid, "GROUP_ID", $row[ID])) {
					$disabled = "disabled ";
				} else {
					$disabled = "notdisabled";
				}				
				$html .= "<tr>
								<td class='datapermission_plusminus'><input id='datapermission_usergroup_foldingbutton_".$str_permissionname."_".$int_dataid."_$row[ID]' class='plusminus' type='button' onclick='datapermission_showusers(\"$str_permissionname\", \"$str_datatablename\", \"$int_dataid\", \"$row[ID]\")' value='+' /></td>
								<td>$row[GROUP_NAME]</td>
								<td class='datapermission_functions'><input class='lilleknap' id='datapermission_usergroup_grantbutton_".$str_permissionname."_".$int_dataid."_$row[ID]' type='button' onclick='datapermission_grant(\"$str_permissionname\", \"$str_datatablename\", \"$int_dataid\", \"usergroup\", \"$row[ID]\")' value='Tildel rettighed' $disabled /></td>
						 	</tr>
						 	<tr style='display:none;' id='datapermission_usersrow_".$str_permissionname."_".$int_dataid."_$row[ID]'>
						 		<td colspan='3' style='background-color: #fff;' id='datapermission_users_".$str_permissionname."_".$int_dataid."_$row[ID]'></td>
						 	</tr>";
			}
			$html .= "</table>";
			$status 	= "success";
			$message 	= $html;
		} else {
			$status 	= "success";
			$message 	= "Der er ikke oprettet nogen brugergrupper.";
		}	
	} else {
		$status 	= "error";
		$message 	= "Kunne ikke hente liste over brugergrupper (database fejl i datapermission_returngrouplist)";
	}

	return "$status|||$message|||$str_permissionname|||$str_datatablename|||$int_dataid";
}

function datapermission_revokegrant($int_grantid) {
	/*
		Function to delete a grant from the DATA_PERMISSIONS table
		Returns true/false
	*/
	$sql = "delete from DATA_PERMISSIONS where ID = '$int_grantid'";
	if (mysql_query($sql)) {
		if (mysql_affected_rows() > 0) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}


function datapermission_listgrants($str_permissionname, $str_datatablename, $int_dataid, $str_mode, $readonly=false, $plaintext=false) {
    /*
        Function to list current permission granted for given input. And provide an option to revoke the grant.
        Called with $str_mode = "users" or "usergroups"
    */

	$status = "undefined";

	$u_sql = "select distinct
				 DP.ID, 
				 CONCAT(U.FIRSTNAME, ' ', U.LASTNAME, ' (', U.USERNAME, ')') AS NAME 
			from
				DATA_PERMISSIONS DP, USERS U, PERMISSIONS P, USERS_GROUPS UG, GROUPS G
			where
				UG.USER_ID = U.ID and
				UG.GROUP_ID = G.ID and
				G.SITE_ID in (0,'$_SESSION[SELECTED_SITE]') and
				DP.USER_ID = U.ID and
				DP.PERMISSION_ID = P.ID and
				P.NAME = '$str_permissionname' and
				P.IS_DATAPERMISSION = 1 and
				DP.DATA_TABLE_NAME = '$str_datatablename' and
				DP.DATA_ID = '$int_dataid' and
				U.UNFINISHED = 0 and
				U.DELETED = 0
			order by
				U.FIRSTNAME asc, U.LASTNAME asc";


	$g_sql = "select distinct
				DP.ID, G.GROUP_NAME as NAME
			from
				DATA_PERMISSIONS DP, GROUPS G, PERMISSIONS P
			where
				G.SITE_ID in (0,'$_SESSION[SELECTED_SITE]') and
				DP.GROUP_ID = G.ID and
				DP.PERMISSION_ID = P.ID and
				P.NAME = '$str_permissionname' and
				P.IS_DATAPERMISSION = 1 and
				DP.DATA_TABLE_NAME = '$str_datatablename' and
				DP.DATA_ID = '$int_dataid' and
				G.UNFINISHED = 0 and
				G.HIDDEN = 0 and
				G.DELETED = 0
			order by
				G.GROUP_NAME asc";

    switch($str_mode) {
        case "users":
			$primary_modename_plural = "brugere";
			$secondary_modename_plural = "brugergrupper";
			$primary_sql = $u_sql;
			$secondary_sql = $g_sql;
            break;
        case "usergroups":
			$primary_modename_plural = "brugergrupper";
			$secondary_modename_plural = "brugere";
			$primary_sql = $g_sql;
			$secondary_sql = $u_sql;
            break;
        default:
			$status = "error";
			$returnmessage = "no mode given in datapermission_listgrants";
    }
    
    // First get results for primary sql.
	if (!$primary_res = mysql_query($primary_sql)) {
			$status = "error";
            $returnmessage = "sql error in datapermission_listgrants";
	} else {
		if (mysql_num_rows($primary_res) == 0) {
			// No rows found in primary sql, check secondary sql for rows
			if (!$secondary_res = mysql_query($secondary_sql)) {
				$status = "error";
				$returnmessage = "sql error in datapermission_listgrants secondary";
			} else {
				$returnmessage = "<h3>Nedenstående $primary_modename_plural er tildelt rettigheden</h3>";
				$returnmessage_plain = "Nedenstående ".strtoupper($primary_modename_plural)." er tildelt rettigheden:";
				if (mysql_num_rows($secondary_res) == 0) {
					// No rows found in secondary sql, access is free for all.
					$status = "success";
					$returnmessage .= "<p>Alle $primary_modename_plural har denne rettighed.</p>";
					$returnmessage_plain .= "\nAlle $primary_modename_plural har denne rettighed.";
				} else {
					// Rows found in secondary sql, only these have access
					$status = "success";
					if ($str_mode == "users") {
						$returnmessage .= "<p>Ingen individuelle brugere er tildelt rettigheden. Men rettigheden er tildelt på gruppeniveau. Det er kun medlemmer af de grupper, du kan se herover, som har rettigheden.</p>";
						$returnmessage_plain .= "\nIngen individuelle brugere er tildelt rettigheden. Men rettigheden er tildelt på gruppeniveau. Det er kun medlemmer af de grupper, du kan se herover, som har rettigheden.";
					} else {
						$returnmessage .= "<p>Ingen brugergrupper er tildelt rettigheden. Men rettigheden er tildelt individuelle brugere. Det er kun de brugere, du kan se herunder, som har rettigheden.</p>";
						$returnmessage_plain .= "\n(*) Ingen brugergrupper er tildelt rettigheden. Men rettigheden er tildelt individuelle brugere. Det er kun de brugere, du kan se herunder, som har rettigheden.";
					}
				}
				if ($plaintext) {
					$returnmessage = $returnmessage_plain;
				}
			}
		} else {
			// List grants
			if ($plaintext) {
				$plain = "Nedenstående ".strtoupper($primary_modename_plural)." er tildelt rettigheden:";
				while ($row = mysql_fetch_assoc($primary_res)) {
					$plain .= "\n(*) $row[NAME]";
				}
				$status = "success";
				$returnmessage = $plain;
			} else {
				$html = "<h3>Nedenstående $primary_modename_plural er tildelt rettigheden</h3>
				<table class='datapermission_grantslist oversigt'>";
				while ($row = mysql_fetch_assoc($primary_res)) {
					if (!$readonly) {
						$revokegrant_button = "<input class='lilleknap' type='button' onclick='datapermission_revokegrant(\"$str_permissionname\", \"$str_datatablename\", \"$int_dataid\", \"$str_mode\", $row[ID])' value='Slet rettighed' />";
					} else {
						$revokegrant_button = "";
					}
					$html .= "<tr><td>$row[NAME]</td><td class='funktioner'>$revokegrant_button</td></tr>";
				}
				$html .= "</table>";
				$status = "success";
				$returnmessage = $html;
			}
		}
	}
	return "$status|||$str_mode|||$returnmessage|||$str_permissionname|||$int_dataid";
}

?>