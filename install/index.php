<?php
require_once("install.common.inc.php");
	switch ($_GET[step]) {
		case "":
			// Flow through to case "1"
		case "1":
			$title = "1. Konfiguration";
			$content = "<p>Herunder skal du angive nogle oplysninger, som vi skal bruge for at opsætte dit website med Instans CMS. Felter markeret med * skal udfyldes.</p>";
			$content .= return_install_form();
			break;
		case "2":
			$title = "2. Installation";
			$arr_install = $_POST;
			connect_to_db($arr_install);
			$content .= db_populate_structure();
			$content .= "<h2>2.1 Site-data oprettet</h2><ul>";
			$content .= db_create_site($arr_install);
			$content .= db_create_template($arr_install);
			$content .= db_create_template_newsletter($arr_install);
			$content .= db_create_generalsettings($arr_install);
			$content .= db_create_admingroup($arr_install);
			$content .= db_create_newslettergroup($arr_install);
			$content .= db_create_adminuser($arr_install);
			$content .= db_populate_permissions($arr_install);
			$content .= db_create_languages($arr_install);
			$content .= db_create_menu($arr_install);
			$content .= db_create_newsarchive($arr_install);
			$content .= db_create_calendar($arr_install);
			$content .= db_create_homepage($arr_install);
			$content .= db_populate_newsletter_formfields($arr_install);
			$content .= db_populate_rewrite_modes($arr_install);
			$content .= db_populate_rewrite_keywords($arr_install);
			$content .= db_populate_rewrite_methods($arr_install);
			$content .= db_populate_filetypes($arr_install);
			$content .= db_create_cms_sitedomains($arr_install);
			$content .= "</ul>";

			$content .= "<h2>2.2 Konfigurationsfiler oprettet</h2><ul>";
			$content .= file_cmsconfig($arr_install);
			$content .= file_htaccess($arr_install);
			$content .= "</ul>";

			$content .= "<h2>2.3 Færdig!</h2><p>Dit website er oprettet. <a href='$arr_install[baseurl]'>Gå til forsiden!</a></p><p>Der sker ikke noget ved at køre installeren igen, men for en god ordens skyld bør du slette /install mappen fra din server.</p>";
			break;
	}
	html_output($title, $content) 
?>







<?php
