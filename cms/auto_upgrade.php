<?php
/*
	auto_upgrade.php is included on each login (via site_selector.php)

	This script contains will automatically perform certain 
	database operations needed when upgrading to a new version
	
	This could eventually be expanded into a complete auto-upgrade process
	but for now it is only used for data-conversion in conjunction with manual upgrades
	
	Each operation is marked with a CMS version and build number
	when the current build number equals or exceeds this mark, 
	the process is run ONCE and registered in the table
	CMS_UPGRADES
	
	Each operation is also maked with a unique identifyer, used for making
	sure upgrades are run only once
*/



/*	
	Upgrades for versions above 2.7.2 (20071214000)
*/
	if ($cmsBuild > 20071214000) {
		$upgrade_id				= "20081122001";
		$upgrade_description 	= "Convert permissions to new data-permissions structure - PAGES";
		upgrade_20081122001($upgrade_id, $upgrade_description);

		$upgrade_id				= "20081122002";
		$upgrade_description 	= "Convert permissions to new data-permissions structure - NEWS";
		upgrade_20081122002($upgrade_id, $upgrade_description);

		$upgrade_id				= "20081122003";
		$upgrade_description 	= "Convert permissions to new data-permissions structure - EVENTS";
		upgrade_20081122003($upgrade_id, $upgrade_description);
	}



/* */
	function ______Build_specific_upgrade_functions(){}
/*
	Example:
	
	function upgrade_[upgrade_id]($upgrade_id, $upgrade_description) {
		if (!upgrade_executed($upgrade_id)) {
			upgrade_begin($upgrade_id, $upgrade_description);
			// UPGRADE INSTRUCTIONS HERE
			upgrade_end($upgrade_id, $str_comment);
		}
	}
*/
function upgrade_20081122003($upgrade_id, $upgrade_description) {
	if (!upgrade_executed($upgrade_id)) {
		upgrade_begin($upgrade_id, $upgrade_description);
		
		$str_permission_name = "DATA_CMS_NEWSITEM_ACCESS";
		$str_permission_table = "NEWS";
		
		// Get any user-locked pages
		$sql = "select ID, AUTHOR_ID from $str_permission_table where LOCKED_BY_USER = '1'";
		if ($res = mysql_query($sql)) {
			if (mysql_num_rows($res)>0) {
				$int_permission_id = return_permission_id($str_permission_name);
				if (is_numeric($int_permission_id)) {
					// Insert datapermissions
					while ($row = mysql_fetch_assoc($res)) {
						$arr_insertvalues[] = "($row[AUTHOR_ID], '$str_permission_table', $row[ID], $int_permission_id)";
					}
					$sql = "insert into DATA_PERMISSIONS (USER_ID, DATA_TABLE_NAME, DATA_ID, PERMISSION_ID) values ";
					$sql .= implode(",", $arr_insertvalues);
					if (mysql_query($sql)) {
						// Remove existing locks
						$sql = "update $str_permission_table set LOCKED_BY_USER = '0' where LOCKED_BY_USER = '1'";
						if (mysql_query($sql)) {
							$str_comment = "Success: Data-Permission added for ";
							$str_comment .= count($arr_insertvalues);
							$str_comment .= " $str_permission_table, matching user-lock removed from ";
							$str_comment .= mysql_affected_rows();
							$str_comment .= " ".$str_permission_table;
						} else {
							$str_comment = "Error: Could not execute sql: $sql";
						}					
					} else {
						$str_comment = "Error: Could not execute sql: $sql";
					}
				} else {
					$str_comment = "Error: Permission '$str_permission_name' not found in PERMISSIONS table";
				}
			} else {
				$str_comment = "No $str_permission_table needs to be converted";
			}
		} else {
			$str_comment = "Error: Could not execute sql: $sql";
		}
		upgrade_end($upgrade_id, $str_comment);
	}
}


function upgrade_20081122002($upgrade_id, $upgrade_description) {
	if (!upgrade_executed($upgrade_id)) {
		upgrade_begin($upgrade_id, $upgrade_description);
		
		$str_permission_name = "DATA_CMS_EVENT_ACCESS";
		$str_permission_table = "EVENTS";
		
		// Get any user-locked pages
		$sql = "select ID, AUTHOR_ID from $str_permission_table where LOCKED_BY_USER = '1'";
		if ($res = mysql_query($sql)) {
			if (mysql_num_rows($res)>0) {
				$int_permission_id = return_permission_id($str_permission_name);
				if (is_numeric($int_permission_id)) {
					// Insert datapermissions
					while ($row = mysql_fetch_assoc($res)) {
						$arr_insertvalues[] = "($row[AUTHOR_ID], '$str_permission_table', $row[ID], $int_permission_id)";
					}
					$sql = "insert into DATA_PERMISSIONS (USER_ID, DATA_TABLE_NAME, DATA_ID, PERMISSION_ID) values ";
					$sql .= implode(",", $arr_insertvalues);
					if (mysql_query($sql)) {
						// Remove existing locks
						$sql = "update $str_permission_table set LOCKED_BY_USER = '0' where LOCKED_BY_USER = '1'";
						if (mysql_query($sql)) {
							$str_comment = "Success: Data-Permission added for ";
							$str_comment .= count($arr_insertvalues);
							$str_comment .= " $str_permission_table, matching user-lock removed from ";
							$str_comment .= mysql_affected_rows();
							$str_comment .= " ".$str_permission_table;
						} else {
							$str_comment = "Error: Could not execute sql: $sql";
						}					
					} else {
						$str_comment = "Error: Could not execute sql: $sql";
					}
				} else {
					$str_comment = "Error: Permission '$str_permission_name' not found in PERMISSIONS table";
				}
			} else {
				$str_comment = "No $str_permission_table needs to be converted";
			}
		} else {
			$str_comment = "Error: Could not execute sql: $sql";
		}
		upgrade_end($upgrade_id, $str_comment);
	}
}


function upgrade_20081122001($upgrade_id, $upgrade_description) {
	if (!upgrade_executed($upgrade_id)) {
		upgrade_begin($upgrade_id, $upgrade_description);
		
		$str_permission_name = "DATA_CMS_PAGE_ACCESS";
		$str_permission_table = "PAGES";
		
		// Get any user-locked pages
		$sql = "select ID, EDIT_AUTHOR_ID from $str_permission_table where LOCKED_BY_USER = '1'";
		if ($res = mysql_query($sql)) {
			if (mysql_num_rows($res)>0) {
				$int_permission_id = return_permission_id($str_permission_name);
				if (is_numeric($int_permission_id)) {
					// Insert datapermissions
					while ($row = mysql_fetch_assoc($res)) {
						$arr_insertvalues[] = "($row[EDIT_AUTHOR_ID], '$str_permission_table', $row[ID], $int_permission_id)";
					}
					$sql = "insert into DATA_PERMISSIONS (USER_ID, DATA_TABLE_NAME, DATA_ID, PERMISSION_ID) values ";
					$sql .= implode(",", $arr_insertvalues);
					if (mysql_query($sql)) {
						// Remove existing locks
						$sql = "update $str_permission_table set LOCKED_BY_USER = '0' where LOCKED_BY_USER = '1'";
						if (mysql_query($sql)) {
							$str_comment = "Success: Data-Permission added for ";
							$str_comment .= count($arr_insertvalues);
							$str_comment .= " $str_permission_table, matching user-lock removed from ";
							$str_comment .= mysql_affected_rows();
							$str_comment .= " ".$str_permission_table;
						} else {
							$str_comment = "Error: Could not execute sql: $sql";
						}					
					} else {
						$str_comment = "Error: Could not execute sql: $sql";
					}
				} else {
					$str_comment = "Error: Permission '$str_permission_name' not found in PERMISSIONS table";
				}
			} else {
				$str_comment = "No $str_permission_table needs to be converted";
			}
		} else {
			$str_comment = "Error: Could not execute sql: $sql";
		}
		upgrade_end($upgrade_id, $str_comment);
	}
}

/* */
	function ______General_upgrade_functions(){}
/* */

function upgrade_executed($upgrade_id) {
	$sql = "select count(*) from CMS_UPGRADES where UPGRADE_ID = '$upgrade_id'";
	if ($res = mysql_query($sql)) {
		if (mysql_result($res,0)>0) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}
function upgrade_begin($upgrade_id, $upgrade_description) {
	global $cmsVersion, $cmsBuild;
	$upgrade_description = mysql_real_escape_string($upgrade_description);
	$sql = "insert into CMS_UPGRADES
				(UPGRADE_ID, UPGRADE_DESCRIPTION, UPGRADE_BEGIN, USER_LOGGED_IN, CMS_VERSION, CMS_BUILD)
				values
				('$upgrade_id', '$upgrade_description', NOW(), '".$_SESSION[CMS_USER][USER_ID]."', '$cmsVersion', '$cmsBuild')";
	if (mysql_query($sql)) {
		return true;
	} else {
		return false;
	}
}
function upgrade_end($upgrade_id, $str_comment) {
	$str_comment = mysql_real_escape_string($str_comment);
	$sql = "update CMS_UPGRADES set UPGRADE_END = NOW(), COMMENTS = '$str_comment' where UPGRADE_ID = '$upgrade_id'";
	if (mysql_query($sql)) {
		return true;
	} else {
		return false;
	}
}

/* */
	function ______Upgrade_helper_functions(){}
/* */
function return_permission_id($str_permission_name) {
	$sql = "select ID from PERMISSIONS where NAME = '$str_permission_name' limit 1";
	if ($res = mysql_query($sql)) {
		if (mysql_num_rows($res)>0) {
			$permission_id = mysql_result($res,0);
			if (is_numeric($permission_id)) {
				return $permission_id;
			} else {
				return false;
			}
		} else {
			return false;
		}
	} else {
		return false;
	}
}
?>