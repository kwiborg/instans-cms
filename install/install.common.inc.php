<?php

/*
	$query  = "LOAD DATA INFILE '$file'";
	if ($result = mysql_query($query)) {
		echo "ok";
	} else {
		echo "not ok";
	}
*/

function html_output($title, $content) {
	include("html_header.php");
	echo "\n<h1 class='heading'>$title</h1>\n<div id='content_block'>$content</div>";
	include("html_footer.php");
}

function db_populate_structure() {
	// Read structure dump
	$file = $_SERVER[DOCUMENT_ROOT]."/install/resources/db-structure.sql";
	$fh = fopen($file, 'r');
	$contents = fread($fh, filesize($file));
	$arr_dbstruct = explode("-- --------------------------------------------------------", $contents);
	foreach ($arr_dbstruct as $key => $value) {
		if (strstr($value, "CREATE TABLE")) {
			if (mysql_query($value)) {
				$arr_sqlstatements_ok[] = $value;
			} else {
				$arr_sqlstatements_error[] = $value;
			}
		}
	}
	if (count($arr_sqlstatements_ok)>0 || count($arr_sqlstatements_error)>0) {
		$html = "<h2>Import af database-struktur</h2>";
	}
	if (count($arr_sqlstatements_ok)>0) {
		$html .= "<p class='install_good'>".count($arr_sqlstatements_ok)." tabeller oprettet korrekt.</p>";
	}
	if (count($arr_sqlstatements_error)>0) {
		$html .= "<p class='install_bad'>".count($arr_sqlstatements_error)." tabeller <strong>ikke</strong> oprettet korrekt. Fejl:</p>";
	}
	return $html;
}


function db_create_site(&$arr_install) {
	$arr_email = explode("@", $arr_install[admin_email]);
	$arr_install[emaildomain] = $arr_email[1];
	$arr_install[baseurl] = "http://".$_SERVER[HTTP_HOST];

	$sql = "select SITE_ID from SITES where SITE_NAME = '".$arr_install[sitename]."' limit 1";
	$res = mysql_query($sql);
	if (mysql_num_rows($res)==0) {
		$sql = 'INSERT INTO `SITES` (`SITE_ID`, `SITE_NAME`, `BASE_URL`, `SITE_PATH`, `DEFAULT_TEMPLATE`, `EMAIL_DOMAIN`) VALUES (NULL, \''.$arr_install[sitename].'\', \''.$arr_install[baseurl].'\', \'\', \'.$arr_install[default_template].\', \''.$arr_install[emaildomain].'\');';
		if (mysql_query($sql)) {
			$arr_install[site] = mysql_insert_id();
			$html .= "<li class='install_good'>Site '".$arr_install[sitename]."' oprettet på ".$arr_install[baseurl]."</li>";
		} else {
			$html .= "<li class='install_bad'>Site '".$arr_install[sitename]."' <strong>ikke</strong> oprettet på ".$arr_install[baseurl]."</li>";
		}
	} else {
		$arr_install[site] = mysql_result($res,0);
		$html .= "<li class='install_neutral'>Site '".$arr_install[sitename]."' er allerede oprettet på databasen.</li>";
	}
	return $html;		
}

function db_create_template(&$arr_install) {
	$sql = "select ID from TEMPLATES where SITE_ID = '".$arr_install[site]."' and TYPE = 'PAGE' limit 1";
	$res = mysql_query($sql);
	if (mysql_num_rows($res)==0) {
		$sql = "INSERT INTO `TEMPLATES` (`ID`, `NAME`, `DESCRIPTION`, `PATH`, `PRINTTEMPLATE_PATH`, `CARTTEMPLATE_PATH`, `CHECKOUTTEMPLATE_PATH`, `TYPE`, `FOLDER_NAME`, `SITE_ID`) VALUES
	('', '".$arr_install[sitename]." template', 'Template eksempel. Kan redigeres i includes/templates/eksempel.template.php/eksempel_printerfriendly.template.php', 'includes/templates/eksempel.template.php', 'includes/templates/eksempel_printerfriendly.template.php', 'includes/templates/eksempel.template.php', 'includes/templates/eksempel.template.php', 'PAGE', '', ".$arr_install[site].");";
		if (mysql_query($sql)) {
			$arr_install[default_template] = mysql_insert_id();
			$html .= "<li class='install_good'>'".$arr_install[sitename]." template' oprettet.</li>";
			$sql = "update SITES set DEFAULT_TEMPLATE = '".$arr_install[default_template]."' where SITE_ID = ".$arr_install[site];
			mysql_query($sql);
		} else {
			$html .= "<li class='install_bad'>'".$arr_install[sitename]." template' <strong>ikke</strong> oprettet.</li>";
		}
	} else {
		$arr_install[default_template] = mysql_result($res,0);
		$html .= "<li class='install_neutral'>Templaten '".$arr_install[sitename]." template' er allerede oprettet på databasen.</li>";
	}
	return $html;
}

function db_create_generalsettings(&$arr_install) {
	$sql = "select * from GENERAL_SETTINGS where ID = '".$arr_install[site]."' limit 1";
	$res = mysql_query($sql);
	if (mysql_num_rows($res)==0) {
		$sql = "INSERT INTO `GENERAL_SETTINGS` (`ID`, `META_DESCRIPTION`, `META_KEYWORDS`, `META_TITLE_USEPAGESCOLUMN`, `CONTACT_EMAILS`, `NEWSLETTER_GROUPID`) VALUES
(".$arr_install[site].", '', '', 'HEADING', '".$arr_install[admin_email]."', 0);";
		if (mysql_query($sql)) {
			$html .= "<li class='install_good'>General settings for '".$arr_install[sitename]."' oprettet.</li>";
		} else {
			$html .= "<li class='install_bad'>General settings for '".$arr_install[sitename]."' <strong>ikke</strong> oprettet.</li>";
		}
	} else {
		$html .= "<li class='install_neutral'>General settings for '".$arr_install[sitename]."' er allerede oprettet på databasen.</li>";
	}
	return $html;
}

function db_create_admingroup(&$arr_install) {
	$sql = "select ID from GROUPS where GROUP_NAME = 'Admin' limit 1";
	$res = mysql_query($sql);
	if (mysql_num_rows($res)==0) {
		$sql = "INSERT INTO `GROUPS` (`ID`, `PARENT_ID`, `GROUP_NAME`, `DESCRIPTION`, `AUTHOR_ID`, `CREATED_DATE`, `CHANGED_DATE`, `UNFINISHED`, `HIDDEN`, `DELETED`, `REGISTRATION_OPEN`, `EDITING_OPEN`, `NOTIFY_USER_ID`, `LANDING_GROUP_ID`, `USERLIST_OPEN`, `DEFAULT_CONTENT_IDENTIFIER`, `SORT_BY`, `SITE_ID`, `LOGIN_TO_URL`) VALUES
		('', 0, 'Admin', 'Brugere med alle rettigheder', 0, '".time()."', '".time()."', 0, 0, 0, 0, 0, 0, 0, 0, '', 'FIRSTNAME', ".$arr_install[site].", '');";
		if (mysql_query($sql)) {
			$arr_install[admingroup] = mysql_insert_id();
			$html .= "<li class='install_good'>Admin brugergruppe for '".$arr_install[sitename]."' oprettet.</li>";
		} else {
			$html .= "<li class='install_bad'>Admin brugergruppe for '".$arr_install[sitename]."' <strong>ikke</strong> oprettet.</li>";
		}
	} else {
		$arr_install[admingroup] = mysql_result($res,0);
		$html .= "<li class='install_neutral'>Admin brugergruppe for '".$arr_install[sitename]."' er allerede oprettet på databasen.</li>";
	}
	return $html;
}

function db_create_newslettergroup(&$arr_install) {
	$sql = "select ID from GROUPS where GROUP_NAME = 'Nyhedsbrev' limit 1";
	$res = mysql_query($sql);
	if (mysql_num_rows($res)==0) {
		$sql = "INSERT INTO `GROUPS` (`ID`, `PARENT_ID`, `GROUP_NAME`, `DESCRIPTION`, `AUTHOR_ID`, `CREATED_DATE`, `CHANGED_DATE`, `UNFINISHED`, `HIDDEN`, `DELETED`, `REGISTRATION_OPEN`, `EDITING_OPEN`, `NOTIFY_USER_ID`, `LANDING_GROUP_ID`, `USERLIST_OPEN`, `DEFAULT_CONTENT_IDENTIFIER`, `SORT_BY`, `SITE_ID`, `LOGIN_TO_URL`) VALUES
		('', 0, 'Nyhedsbrev', 'Indeholder brugere som er tilmeldt et eller flere nyhedsbreve. Bemærk at man _skal_ benytte abonnemements- eller import-funktionen for at tilføje nye abonnenter til et nyhedsbrev. Det er _ikke_ tilstrækkeligt at tilføje brugere til denne gruppe.', 0, '".time()."', '".time()."', 0, 0, 0, 0, 0, 0, 0, 0, '', 'FIRSTNAME', ".$arr_install[site].", '');";
		if (mysql_query($sql)) {
			$arr_install[newslettergroup] = mysql_insert_id();
			$html .= "<li class='install_good'>Nyhedsbrev brugergruppe for '".$arr_install[sitename]."' oprettet.</li>";
			$sql = "update GENERAL_SETTINGS set NEWSLETTER_GROUPID = '".$arr_install[newslettergroup]."' where ID = ".$arr_install[site];
			mysql_query($sql);
		} else {
			$html .= "<li class='install_bad'>Nyhedsbrev brugergruppe for '".$arr_install[sitename]."' <strong>ikke</strong> oprettet.</li>";
		}
	} else {
		$arr_install[admingroup] = mysql_result($res,0);
		$html .= "<li class='install_neutral'>Admin brugergruppe for '".$arr_install[sitename]."' er allerede oprettet på databasen.</li>";
	}
	return $html;
}


function db_create_adminuser(&$arr_install) {
	// Opret bruger
	$sql = "select ID from USERS where EMAIL = '".$arr_install[admin_email]."' limit 1";
	$res = mysql_query($sql);
	if (mysql_num_rows($res)==0) {
		$sql = "INSERT INTO `USERS` (`ID`, `USERNAME`, `PASSWORD`, `FIRSTNAME`, `LASTNAME`, `ADDRESS`, `ZIPCODE`, `CITY`, `PHONE`, `CELLPHONE`, `EMAIL`, `CV`, `COMPANY`, `CREATED_DATE`, `CHANGED_DATE`, `UNFINISHED`, `AUTHOR_ID`, `DELETED`, `RECEIVE_LETTERS`, `EMAIL_VERIFIED`, `INITIALS`, `DATE_OF_BIRTH`, `DATE_OF_HIRING`, `DEPARTMENT`, `JOB_TITLE`, `TRANSFER_TO_GROUP`, `NEVER_PUBLIC`, `IMAGE_ID`, `PASSWORD_ENCRYPTED`, `COUNTRY`) VALUES
('', '".$arr_install[admin_username]."', '".$arr_install[admin_password]."', '".$arr_install[admin_firstname]."', '".$arr_install[admin_lastname]."', '', '', '', '', '', '".$arr_install[admin_email]."', '', '', '".time()."', '".time()."', 0, 0, 0, 0, 1, '', '0000-00-00', '0000-00-00', '', '', 0, 0, 0, '".md5($arr_install[admin_password])."', '');";
		if (mysql_query($sql)) {
			$arr_install[adminuser] = mysql_insert_id();
			// Opret Gruppe tilhør
			$sql = "INSERT INTO `USERS_GROUPS` (`ID`, `USER_ID`, `GROUP_ID`, `POSITION`) VALUES
					('', ".$arr_install[adminuser].", ".$arr_install[admingroup].", 0);";
			if (mysql_query($sql)) {
				$html .= "<li class='install_good'>Admin bruger for '".$arr_install[sitename]."' oprettet.</li>";
			} else {
				$html .= "<li class='install_bad'>Admin bruger for '".$arr_install[sitename]."' oprettet, men gruppe-tilhør blev <strong>ikke</strong> oprettet.</li>";
			}
		} else {
			$html .= "<li class='install_bad'>Admin bruger for '".$arr_install[sitename]."' <strong>ikke</strong> oprettet.</li>";
		}
	} else {
		$arr_install[adminuser] = mysql_result($res,0);
		$html .= "<li class='install_neutral'>Admin bruger for '".$arr_install[sitename]."' er allerede oprettet på databasen.</li>";
	}
	return $html;
}

function db_populate_permissions(&$arr_install) {
	$sql = "select count(*) from GROUPS_PERMISSIONS";
	$res = mysql_query($sql);
	if (mysql_result($res,0)==0) {
		$file = $_SERVER[DOCUMENT_ROOT]."/install/resources/PERMISSIONGROUPS.sql";
		$fh = fopen($file, 'r');
		$sql = fread($fh, filesize($file));
		if (mysql_query($sql)) {
			$html .= "<li class='install_good'>Rettighedsgrupper oprettet.</li>";
			$file = $_SERVER[DOCUMENT_ROOT]."/install/resources/PERMISSIONS.sql";
			$fh = fopen($file, 'r');
			$sql = fread($fh, filesize($file));
			if (mysql_query($sql)) {
				$html .= "<li class='install_good'>Rettigheder oprettet.</li>";
				$sql = "select * from PERMISSIONS where IS_DATAPERMISSION = 0";
				$res = mysql_query($sql);
				$i=0;
				while ($p = mysql_fetch_assoc($res)) {
					$isql = "INSERT INTO `GROUPS_PERMISSIONS` (`ID`, `GROUPS_ID`, `PERMISSIONS_ID`) VALUES
		('', ".$arr_install[admingroup].", ".$p[ID].");";
					if (mysql_query($isql)) {
						$i++;
					}
				}
				if ($i>0) {
					$html .= "<li class='install_good'>$i rettigheder tildelt admin-gruppen.</li>";
				} else {
					$html .= "<li class='install_bad'>Ingen rettigheder tildelt admin-gruppen.</li>";
				}
			} else {
				$html .= "<li class='install_bad'>Rettigheder <strong>ikke</strong> oprettet.</li>";
			}
		} else {
			$html .= "<li class='install_bad'>Rettighedsgrupper <strong>ikke</strong> oprettet.</li>";
		}
	} else {
		$html .= "<li class='install_neutral'>Rettigheder allerede oprettet på databasen.</li>";
	}
	return $html;
}

function db_create_languages(&$arr_install) {
	$sql = "select count(*) from LANGUAGES limit 1";
	$res = mysql_query($sql);
	if (mysql_result($res,0)==0) {
		$file = $_SERVER[DOCUMENT_ROOT]."/install/resources/LANGUAGES.sql";
		$fh = fopen($file, 'r');
		$sql = fread($fh, filesize($file));
		if (mysql_query($sql)) {
			$html .= "<li class='install_good'>Sprog oprettet.</li>";
		} else {
			$html .= "<li class='install_bad'>Sprog <strong>ikke</strong> oprettet.</li>";
		}
	} else {
		$html .= "<li class='install_neutral'>Sprog allerede oprettet på databasen.</li>";
	}
	$arr_install[default_language_id] = "1";
	$arr_install[default_language_shortname] = "da";
	return $html;
}

function db_create_menu(&$arr_install) {
	$sql = "select MENU_ID from MENUS limit 1";
	$res = mysql_query($sql);
	if (mysql_num_rows($res)==0) {
		$sql = "INSERT INTO `MENUS` (`MENU_ID`, `MENU_TITLE`, `DEFAULT_LANGUAGE`, `SITE_ID`) VALUES
('', 'Hovedmenu', '".$arr_install[default_language_id]."', '".$arr_install[site]."');";
		if (mysql_query($sql)) {
			$arr_install[menu_id] = mysql_insert_id();
			$html .= "<li class='install_good'>Menu oprettet.</li>";
		} else {
			$html .= "<li class='install_bad'>Menu <strong>ikke</strong> oprettet.</li>";
		}
	} else {
		$arr_install[menu_id] = mysql_result($res,0);
		$html .= "<li class='install_neutral'>Menu allerede oprettet på databasen.</li>";
	}
	return $html;
}

function db_create_newsarchive(&$arr_install) {
	$sql = "select count(*) from NEWSFEEDS limit 1";
	$res = mysql_query($sql);
	if (mysql_result($res,0)==0) {
		$sql = "INSERT INTO `NEWSFEEDS` (`ID`, `NAME`, `SITE_ID`, `DEFAULT_LANGUAGE`, `SYNDICATION_SHOWCOMPLETEPOST`, `SYNDICATION_SNIPPETLENGTH`, `SYNDICATION_ALLOWED`, `SYNDICATION_KEY`, `SHOW_IMAGES`) VALUES
('', 'Seneste nyt', '".$arr_install[site]."', '".$arr_install[default_language_id]."', 1, 5, 1, '".md5($arr_install[sitename])."', 1);";
		if (mysql_query($sql)) {
			$arr_install[newsfeed_id] = mysql_insert_id();
			$html .= "<li class='install_good'>Nyhedsarkiv oprettet.</li>";
		} else {
			$html .= "<li class='install_bad'>Nyhedsarkiv <strong>ikke</strong> oprettet.</li>";
		}
	} else {
		$html .= "<li class='install_neutral'>Nyhedsarkiv allerede oprettet på databasen.</li>";
	}
	return $html;
}

function db_create_calendar(&$arr_install) {
	$sql = "select count(*) from CALENDARS limit 1";
	$res = mysql_query($sql);
	if (mysql_result($res,0)==0) {
		$sql = "INSERT INTO `CALENDARS` (`ID`, `NAME`, `SITE_ID`, `DEFAULT_LANGUAGE`) VALUES
('', 'Aktivitetskalender', '".$arr_install[site]."', '".$arr_install[default_language_id]."');";
		if (mysql_query($sql)) {
			$arr_install[calendar_id] = mysql_insert_id();
			$html .= "<li class='install_good'>Kalender oprettet.</li>";
		} else {
			$html .= "<li class='install_bad'>Kalender <strong>ikke</strong> oprettet.</li>";
		}
	} else {
		$html .= "<li class='install_neutral'>Kalender allerede oprettet på databasen.</li>";
	}
	return $html;
}

function db_create_homepage(&$arr_install) {
	$sql = "select count(*) from PAGES limit 1";
	$res = mysql_query($sql);
	if (mysql_result($res,0)==0) {
		$sql = "INSERT INTO `PAGES` (`ID`, `PARENT_ID`, `THREAD_ID`, `MENU_ID`, `SITE_ID`, `ENTRY_TYPE`, `BREADCRUMB`, `HEADING`, `SUBHEADING`, `CONTENT`, `CREATED_DATE`, `CHANGED_DATE`, `AUTHOR_ID`, `EDIT_AUTHOR_ID`, `DELETED`, `UNFINISHED`, `PUBLISHED`, `NO_DISPLAY`, `IS_FRONTPAGE`, `IS_MENUPLACEHOLDER`, `CHECKED_OUT`, `CHECKED_OUT_AUTHOR`, `LOCKED_BY_USER`, `LANGUAGE`, `PROTECTED`, `POSITION`, `POPUP`, `POINTTOPAGE_URL`, `MAILTO_ADDRESS`, `BOOK_ID`, `PHP_INCLUDE_PATH`, `PHP_INCLUDEAFTER_PATH`, `PHP_HEADERINCLUDE_PATH`, `POINTTOPAGE_ID`, `TEMPLATE`, `META_DESCRIPTION`, `META_KEYWORDS`, `META_SEOTITLE`, `REDIRECT_TO_URL`) VALUES
(1, 0, 1, 1, 1, 0, 'Velkommen', 'Velkommen til ".$arr_install[sitename]."', '', '<p>Velkommen til ".$arr_install[sitename].". Siden her er auto-genereret af <a href=\'http://cms.instans.dk/\'>Instans CMS</a>. Du kan redigere den ved at <a href=\'".$arr_install[baseurl]."/cms/\'>logge ind på cms\'et</a> med det brugernavn og password, du indtastede under installationen.</p>', '".time()."', '".time()."', '".$arr_install[adminuser]."', '".$arr_install[adminuser]."', 0, 0, 1, 0, '".$arr_install[default_language_id]."', 0, 0, 0, 0, '".$arr_install[default_language_id]."', 1, 1, 0, '', '', 0, '', '', '', 0, '".$arr_install[default_template]."', '', '', '', '');";
		if (mysql_query($sql)) {
			$html .= "<li class='install_good'>Forside oprettet.</li>";
			// The following is needed to insert a preference row for fixed/standard boxes. Normally auto-created on page creation.
			$sql = "insert into BOX_SETTINGS (PAGE_ID, NEWS, EVENTS, SEARCH, STF, NEWSLETTER) values ('1', '1', '1', '1', '1', '1')";     
			mysql_query($sql);
		} else {
			$html .= "<li class='install_bad'>Forside <strong>ikke</strong> oprettet.</li>";
		}
	} else {
		$html .= "<li class='install_neutral'>Forside allerede oprettet på databasen.</li>";
	}
	$arr_install[homepage_id] = 1;
	return $html;
}

function db_populate_newsletter_formfields(&$arr_install) {
	$sql = "select count(*) from NEWSLETTER_FORMFIELDS";
	$res = mysql_query($sql);
	if (mysql_result($res,0)==0) {
		$file = $_SERVER[DOCUMENT_ROOT]."/install/resources/NEWSLETTER_FORMFIELDS.sql";
		$fh = fopen($file, 'r');
		$sql = fread($fh, filesize($file));
		if (mysql_query($sql)) {
			$html .= "<li class='install_good'>Formular-felter til brugeroprettelse og nyhedsbrev oprettet.</li>";
		} else {
			$html .= "<li class='install_bad'>Formular-felter til brugeroprettelse og nyhedsbrev <strong>ikke</strong> oprettet.</li>";
		}
	} else {
		$html .= "<li class='install_neutral'>Formular-felter til brugeroprettelse og nyhedsbrev allerede oprettet på databasen.</li>";
	}
	return $html;
}

function db_populate_rewrite_modes(&$arr_install) {
	$sql = "select count(*) from REWRITE_MODES";
	$res = mysql_query($sql);
	if (mysql_result($res,0)==0) {
		$file = $_SERVER[DOCUMENT_ROOT]."/install/resources/REWRITE_MODES.sql";
		$fh = fopen($file, 'r');
		$sql = fread($fh, filesize($file));
		if (mysql_query($sql)) {
			$html .= "<li class='install_good'>Rewrite modes oprettet.</li>";
		} else {
			$html .= "<li class='install_bad'>Rewrite modes <strong>ikke</strong> oprettet.</li>";
		}
	} else {
		$html .= "<li class='install_neutral'>Rewrite modes allerede oprettet på databasen.</li>";
	}
	return $html;
}

function db_populate_rewrite_keywords(&$arr_install) {
	$sql = "select count(*) from REWRITE_KEYWORDS";
	$res = mysql_query($sql);
	if (mysql_result($res,0)==0) {
		$file = $_SERVER[DOCUMENT_ROOT]."/install/resources/REWRITE_KEYWORDS.sql";
		$fh = fopen($file, 'r');
		$sql = fread($fh, filesize($file));
		if (mysql_query($sql)) {
			$html .= "<li class='install_good'>Rewrite keywords oprettet.</li>";
		} else {
			$html .= "<li class='install_bad'>Rewrite keywords <strong>ikke</strong> oprettet.</li>";
		}
	} else {
		$html .= "<li class='install_neutral'>Rewrite keywords allerede oprettet på databasen.</li>";
	}
	return $html;
}

function db_populate_rewrite_methods(&$arr_install) {
	$sql = "select count(*) from REWRITE_METHODS";
	$res = mysql_query($sql);
	if (mysql_result($res,0)==0) {
		$file = $_SERVER[DOCUMENT_ROOT]."/install/resources/REWRITE_METHODS.sql";
		$fh = fopen($file, 'r');
		$sql = fread($fh, filesize($file));
		if (mysql_query($sql)) {
			$html .= "<li class='install_good'>Rewrite methods oprettet.</li>";
		} else {
			$html .= "<li class='install_bad'>Rewrite methods <strong>ikke</strong> oprettet.</li>";
		}
	} else {
		$html .= "<li class='install_neutral'>Rewrite methods allerede oprettet på databasen.</li>";
	}
	return $html;
}

function db_populate_filetypes(&$arr_install) {
	$sql = "select count(*) from FILEARCHIVE_TYPE";
	$res = mysql_query($sql);
	if (mysql_result($res,0)==0) {
		$file = $_SERVER[DOCUMENT_ROOT]."/install/resources/FILEARCHIVE_TYPE.sql";
		$fh = fopen($file, 'r');
		$sql = fread($fh, filesize($file));
		if (mysql_query($sql)) {
			$html .= "<li class='install_good'>Filtyper oprettet.</li>";
		} else {
			$html .= "<li class='install_bad'>Filtyper <strong>ikke</strong> oprettet.</li>";
		}
	} else {
		$html .= "<li class='install_neutral'>Filtyper allerede oprettet på databasen.</li>";
	}
	return $html;
}


function db_create_template_newsletter(&$arr_install) {
	$sql = "select ID from TEMPLATES where SITE_ID = '".$arr_install[site]."' and TYPE = 'NEWSLETTER' limit 1";
	$res = mysql_query($sql);
	if (mysql_num_rows($res)==0) {
		$sql = "INSERT INTO `TEMPLATES` (`ID`, `NAME`, `DESCRIPTION`, `PATH`, `PRINTTEMPLATE_PATH`, `CARTTEMPLATE_PATH`, `CHECKOUTTEMPLATE_PATH`, `TYPE`, `FOLDER_NAME`, `SITE_ID`) VALUES
	('', '".$arr_install[sitename]." nyhedsbrev template', 'Nyhedsbrev-template eksempel. Kan redigeres i includes/templates/newsletter_eksempel/', '', '', '', '', 'NEWSLETTER', 'newsletter_eksempel', ".$arr_install[site].");";
		if (mysql_query($sql)) {
			$arr_install[newsletter_template] = mysql_insert_id();
			$html .= "<li class='install_good'>'".$arr_install[sitename]." nyhedsbrev template' oprettet.</li>";
		} else {
			$html .= "<li class='install_bad'>'".$arr_install[sitename]." nyhedsbrev template' <strong>ikke</strong> oprettet.</li>";
		}
	} else {
		$arr_install[newsletter_template] = mysql_result($res,0);
		$html .= "<li class='install_neutral'>Templaten '".$arr_install[sitename]." nyhedsbrev template' er allerede oprettet på databasen.</li>";
	}
	return $html;
}

function db_create_cms_sitedomains(&$arr_install) {
	$sql = "select ID from CMS_SITEDOMAINS limit 1";
	$res = mysql_query($sql);
	if (mysql_num_rows($res)==0) {
		$arr_url = parse_url($arr_install[baseurl]);
		$arr_host = explode(".",$arr_url[host]); 
		if (count($arr_host)==2) {
			$arr_install[subdomain] = "*";
			$arr_install[domain] = $arr_host[0].".".$arr_host[1];
		} else {
			$arr_install[subdomain] = $arr_host[0];
			$arr_install[domain] = $arr_host[1].".".$arr_host[2];
		}		
		$sql = "INSERT INTO `CMS_SITEDOMAINS` (`ID`, `SITE_ID`, `SUBDOMAIN`, `DOMAIN`, `DEFAULT`, `REDIRECT`, `PREFERRED_FOR_LANGUAGE`, `LANGUAGE`, `REDIRECT_TO_URL`) VALUES
('', ".$arr_install[site].", '".$arr_install[subdomain]."', '".$arr_install[domain]."', 1, 0, 0, 1, '');";
		if (mysql_query($sql)) {
			$arr_install[menu_id] = mysql_insert_id();
			$html .= "<li class='install_good'>".$arr_install[baseurl]." oprettet som gyldigt domæne for hjemmesiden.</li>";
		} else {
			$html .= "<li class='install_bad'>".$arr_install[baseurl]." <strong>ikke</strong> oprettet som gyldigt domæne for hjemmesiden.</li>";
		}
	} else {
		$html .= "<li class='install_neutral'>".$arr_install[baseurl]." allerede oprettet som gyldigt domæne for hjemmesiden.</li>";
	}
	return $html;
}

function file_cmsconfig(&$arr_install) {
	$t_file = $_SERVER[DOCUMENT_ROOT]."/cms_config.inc.php";
	if (filesize($t_file) === 0) {
		// Read basic config-file
		$o_file = $_SERVER[DOCUMENT_ROOT]."/install/resources/cms_config.inc.php";
		$o_fh = fopen($o_file, 'r');
		$o_config = fread($o_fh, filesize($o_file));

		// Replace variables
		$t_config = str_replace("___DB_HOST___", $arr_install[db_host], $o_config);
		$t_config = str_replace("___DB_USER___", $arr_install[db_user], $t_config);
		$t_config = str_replace("___DB_PASS___", $arr_install[db_pass], $t_config);
		$t_config = str_replace("___DB_NAME___", $arr_install[db_name], $t_config);
		$t_config = str_replace("___CMS_DOMAIN___", $arr_install[baseurl], $t_config);
		$t_config = str_replace("___COOKIE_DOMAIN___", $_SERVER[HTTP_HOST], $t_config);
		$t_config = str_replace("___STAT_URL___", $arr_install[stat_url], $t_config);
		$t_config = str_replace("___DROPBOX_FTP_SERVER___", $arr_install[dropbox_ftp_server], $t_config);
		$t_config = str_replace("___DROPBOX_FTP_USER___", $arr_install[dropbox_ftp_user], $t_config);
		$t_config = str_replace("___DROPBOX_FTP_PASS___", $arr_install[dropbox_ftp_pass], $t_config);
		$t_config = str_replace("___CMS_SITE_ID___", $arr_install[site], $t_config);

		// Attempt to create file
		$t_fh = fopen($t_file, 'a');
		
		// Save file
		if (is_writable($t_file)) {
			fwrite($t_fh, $t_config);
			fclose($t_fh);
			$html .= "<li class='install_good'>Filen /cms_config.inc.php er oprettet.</li>";

		} else {
			$html .= "<li class='install_neutral'>Kunne ikke skrive til filen /cms_config.inc.php.</li>";
		}
	} else {
			$html .= "<li class='install_bad'>Filen /cms_config.inc.php findes allerede. Tøm evt. den eksisterende fil og kør installeren igen.</li>";
	}
	return $html;
}

function file_htaccess(&$arr_install) {
	$t_file = $_SERVER[DOCUMENT_ROOT]."/.htaccess";
	if (filesize($t_file) === 0) {
		// Read basic config-file
		$o_file = $_SERVER[DOCUMENT_ROOT]."/install/resources/_htaccess";
		$o_fh = fopen($o_file, 'r');
		$o_config = fread($o_fh, filesize($o_file));

		// Replace variables
		$t_config = str_replace("___DOCUMENT_ROOT___", $_SERVER[DOCUMENT_ROOT], $o_config);

		// Attempt to create file
		$t_fh = fopen($t_file, 'a');
		
		// Save file
		if (is_writable($t_file)) {
			fwrite($t_fh, $t_config);
			fclose($t_fh);
			$html .= "<li class='install_good'>Filen /.htaccess er oprettet.</li>";

		} else {
			$html .= "<li class='install_neutral'>Kunne ikke skrive til filen /.htaccess.</li>";
		}
	} else {
			$html .= "<li class='install_bad'>Filen /.htaccess findes allerede. Tøm evt. den eksisterende fil og kør installeren igen.</li>";
	}
	return $html;
}
/*
function folder_permissions_set() {
	if (chmod($_SERVER[DOCUMENT_ROOT]."/includes/uploaded_files", 0777)) {
		$html .= "<li class='install_good'>Rettigheder på mappen /includes/uploaded_files er sat til 0777</li>";
	} else {
		$html .= "<li class='install_good'>Kunne ikke sætte rettigheder på mappen /includes/uploaded_file, du skal manuelt sætte rettigheder til 0777</li>";
	}
}
*/

function connect_to_db(&$arr_install) {
	// Connect to the database-server:
	if (!($db=mysql_connect($arr_install[db_host],$arr_install[db_user], $arr_install[db_pass]))) {
		echo "Kunne ikke opnå forbindelse til databasen på $arr_install[db_host] med de indstillinger, du har angivet!";
		exit();
	}
	if (!(mysql_select_db($arr_install[db_name],$db))) {
		echo "Kunne ikke vælge databasen '$arr_install[db_name]'!";
		exit();
	}
	mysql_query("SET NAMES utf8");
	mysql_query("SET CHARACTER_SET utf8");
}

function return_install_form() {
			$content .= "<form action='index.php?step=2' method='post' id='installform'>";
			$content .= "<table>";
			$content .= "<tr>
							<td colspan='2'><h2>Administrator oplysninger</h2></td>
						</tr>";
			$content .= "<tr>
							<td>Navn på din hjemmeside:</td>
							<td><input id='sitename' name='sitename' type='text' />(*)</td>
						</tr>";
			$content .= "<tr>
							<td>Administrator fornavn:</td>
							<td><input id='admin_firstname' name='admin_firstname' type='text' />(*)</td>
						</tr>";
			$content .= "<tr>
							<td>Administrator efternavn:</td>
							<td><input id='admin_lastname' name='admin_lastname' type='text' />(*)</td>
						</tr>";
			$content .= "<tr>
							<td>Administrator ønsket CMS brugernavn:</td>
							<td><input id='admin_username' name='admin_username' type='text' />(*)</td>
						</tr>";
			$content .= "<tr>
							<td>Administrator ønsket CMS password:</td>
							<td><input id='admin_password' name='admin_password' type='password' />(*)</td>
						</tr>";
			$content .= "<tr>
							<td colspan='2'><h2>Database oplysninger</h2></td>
						</tr>";
			$content .= "<tr>
							<td>Database server/host:</td>
							<td><input id='db_host' name='db_host' type='text' />(*)</td>
						</tr>";
			$content .= "<tr>
							<td>Database brugernavn:</td>
							<td><input id='db_user' name='db_user' type='text' />(*)</td>
						</tr>";
			$content .= "<tr>
							<td>Database password:</td>
							<td><input id='db_pass' name='db_pass' type='text' />(*)</td>
						</tr>";
			$content .= "<tr>
							<td>Database navn:</td>
							<td><input id='db_name' name='db_name' type='text' />(*)</td>
						</tr>";
			$content .= "<tr>
							<td colspan='2'><h2>Statistik</h2><p>Kan tilføjes/ændres senere i filen /cms_config.inc.php</p></td>
						</tr>";
			$content .= "<tr>
							<td>Adresse (url) til den side hvor du har adgang til din statistik:</td>
							<td><input id='stat_url' name='stat_url' type='text' /></td>
						</tr>";
			$content .= "<tr>
							<td colspan='2'><h2>FTP-oplysninger for masse-opload af billeder via dropbox</h2><p>Kan tilføjes/ændres senere i filen /cms_config.inc.php</p></td>
						</tr>";
			$content .= "<tr>
							<td>Ftp server/host:</td>
							<td><input id='dropbox_ftp_server' name='dropbox_ftp_server' type='text' /></td>
						</tr>";
			$content .= "<tr>
							<td>Ftp brugernavn:</td>
							<td><input id='dropbox_ftp_user' name='dropbox_ftp_user' type='text' /></td>
						</tr>";
			$content .= "<tr>
							<td>Ftp password:</td>
							<td><input id='dropbox_ftp_pass' name='dropbox_ftp_pass' type='pass' /></td>
						</tr>";
			$content .= "<tr>
							<td id='knap' colspan='2'><input type='submit' value='Opret website med Instans CMS' /></td>
						</tr>";
			$content .= "</table>";
			$content .= "</form>";
	return $content;
}

?>