<?php
// ====================================================================
// CMS VERSION
// --------------------------------------------------------------------
	$cmsVersion = "2.7.10";
	$cmsBuild 	= "20090709001"; // Releasedate for current version (YYYYMMDD) + seq. number (001+)

// ====================================================================
// SITE SETTINS
// --------------------------------------------------------------------
	$cmsDomain				= 	"http://www.client_domain.dk"; // REMEMBER TO ALSO REDECLARE VARIABLES USING $cmsDomain!
	$cmsURL 				= 	$cmsDomain."/cms"; 
	$cmsAbsoluteServerPath 	= 	$_SERVER['DOCUMENT_ROOT'] . "/cms";
	$cmsAbsolutePath		=	"/cms";
	$fckEditorPath 			= 	"/cms/fckeditor";
	$fckEditorCustomConfigPath = "";
	$defaultSite			= 	1;
	$defaultLanguage		= 	"da";	
	$pluginsPath			= $_SERVER["DOCUMENT_ROOT"] . "/includes/plugins";
	$pluginsPathBrowser		= "/includes/plugins";
	$cmsSiteId = 1;	
	$cookieDomain			= ".client-domain.dk";
    $utf8_site = true;

// ====================================================================
// FRONTEND SETTINGS
// --------------------------------------------------------------------
	$imagesFolderPath = $cmsDomain . "/includes/images";
	$shopimagesdirRel = $_SERVER['DOCUMENT_ROOT']."/includes/images/shopimages_cache";
	$shopimagesFolderPath = $imagesFolderPath . "/shopimages_cache";
	$useGrahicalHeadings = false;
	$useModRewrite		 = false;
	$useModRewrite_preserve_page_hierarchy = false;
	$useModRewrite_enforce_url_rewrite = false;
	$replacePNGimages	 = false;

	// encryption hash
	$md5hash = $_SERVER['DOCUMENT_ROOT'];

	// Exclude content-items when outputting main content with buildPageMainContent 
	// Include an array-item with the value "HEADING" to exclude the page title
	// Include an array-item with the value "SUBHEADING" to exclude the page summary
	$arr_content_exclude = array();

// ====================================================================
// PAGES SETTINGS 
// --------------------------------------------------------------------
	$pages_AdvancedOn		= true;
	$pages_searchoptimizerOn= true;
// ====================================================================
// DATABASE SETTINGS
// --------------------------------------------------------------------
	$db_host = "mysql.client_domain.dk";
	$db_user = "username";
	$db_pass = "password";
	$db_name = "database_name";

// ====================================================================
// NEWSARCHIVE SETTINGS
// --------------------------------------------------------------------
	$newsarchive_newsPerPage = 25;
	$newsarchive_newsPerPage_cms = 25;

// ====================================================================
// CALENDAR SETTINGS
// --------------------------------------------------------------------
	$calendar_eventsPerPage_cms = 25;

// ====================================================================
// NEWSLETTER SETTINGS
// --------------------------------------------------------------------
function ____newsletter_settings(){};
	$newsletter_subject = "Nyhedsbrev";
	$newsletter_fromName = "Nyhedsbrev Online";
	$newsletter_fromMail = "no-reply@no-reply.dk";	

	$newsletter_subscribe_formintegration_checkedstate = "checked";

// ====================================================================
// PICTURE ARCHIVE SETTINGS - REQ BY: 
// --------------------------------------------------------------------
function ____picturearchive_settings(){};
	$picturearchive_AdvancedOn = true;
	$picturearchive_UploaddirRel = '/includes/uploaded_pictures';
	$picturearchive_Uploaddir = $_SERVER['DOCUMENT_ROOT'].$picturearchive_UploaddirRel;
	$picturearchive_UploaddirAbs = $cmsDomain.$picturearchive_UploaddirRel;
	$gallery_showMaxLevels = 3;

	// Inline gallery
	$gallery_maxwidth 	= 400;
	$gallery_quality 	= 75;

// ====================================================================
// FILE ARCHIVE SETTINGS
// --------------------------------------------------------------------
function ____filearchive_settings(){};
	$fileUploaddir = $_SERVER['DOCUMENT_ROOT'].'/includes/uploaded_files';
	$fileUploaddirAbs = '/includes/uploaded_files';

// --------------------------------------------------------------------
// DROPBOX IMPORT SETTINGS - REQ BY: dropbox_import.php
// --------------------------------------------------------------------
function ____dropbox_import_settings(){};

	$picturearchive_DropboxOn = true;
	$dropboxDropdir = $_SERVER['DOCUMENT_ROOT'].'/dropbox';
	$dropboxUploaddir = $picturearchive_Uploaddir;
	$dropboxTempdir = $dropboxUploaddir.'/temp';
	$imageThumbSize = 100;
	$imageStandardSize = 600;
	$imageOriginalMinsize = 750;
	
	$dropbox_ftp_server 	= "ikke-defineret";
	$dropbox_ftp_username 	= "ikke-defineret";
	$dropbox_ftp_pass   	= "ikke-defineret";
	

// ====================================================================

// ====================================================================
// CMS MODULES
// --------------------------------------------------------------------
function ____cms_modules(){};

	$modules = array();
	$modules_path = $cmsAbsoluteServerPath."/modules"; // relativt til common.inc.php
	$modules_browserPath = $cmsAbsolutePath."/modules"; // Til brug for CSS+JS includes

/*
	// GENERIC_MODULE
	$modules["NAME_OF_MODULE"] = array(
		[STRING: MODULE_MENUNAME],
		[STRING: PATH TO MODULE PHP-INCLUDE FOLDER],
		[STRING: PATH TO MODULE CLIENT SIDE INCLUDE (JS/CSS) FOLDER],
		[STRING: OPTIONAL: PERMISSION NEEDED TO ACCESS MODULE]
	);
*/

	// SIDER
 	$modules["pages"] = array(
 		"Sider",
		$modules_path,
		$modules_browserPath,
		"CMS_PAGES"
	);

	// NYHEDER
	$modules["news"] = array(
		"Nyheder", 
		$modules_path,
		$modules_browserPath,
		"CMS_NEWS"
	);

	// KALENDER
	$modules["events"] = array(
		"Kalender", 
		$modules_path,
		$modules_browserPath,
		"CMS_EVENTS"
	); 

	// NYHEDSBREV
	$modules["newsletter"] = array(
		"Udsend nyhedsbrev", 
		$modules_path,
		$modules_browserPath,
		"CMS_NEWSLETTERSEND"
	); 

	$modules["newslettercategories"] = array(
		"Interessekategorier", 
		$modules_path,
		$modules_browserPath,
		"CMS_NEWSLETTERSEND"
	); 

	// NYHEDSBREV-SKABELONER
	$modules["newslettertemplates"] = array(
		"Skabeloner", 
		$modules_path,
		$modules_browserPath,
		"CMS_NEWSLETTERADMIN"
	); 

	// NYHEDSBREV-ABONNENTER
	$modules["newslettersubscribers"] = array(
		"Abonnenter", 
		$modules_path,
		$modules_browserPath,
		"CMS_NEWSLETTERADMIN"
	); 

    // NYHEDSBREV-IMPORT
    $modules["newsletterimport"] = array(
        "Import", 
        $modules_path,
        $modules_browserPath,
        "CMS_NEWSLETTERADMIN"
    ); 
    
	// BRUGERE 
	$modules["users"] = array(
		"Brugere", 
		$modules_path,
		$modules_browserPath,
		"CMS_USERS"
	);

	// GRUPPER 
	$modules["groups"] = array(
		"Grupper", 
		$modules_path,
		$modules_browserPath,
		"CMS_GROUPS"
	);

	// FORMULARER
	$modules["formeditor2"] = array(
		"Formular", 
		$modules_path,
		$modules_browserPath,
		"CMS_FORMS"
	);

	// BOKSE
	$modules["customboxes"] = array(
		"Bokse", 
		$modules_path,
		$modules_browserPath,
		"CMS_CUSTOMBOXES"
	);

	// BILLEDER
	$modules["picturearchive"] = array(
		"Billedarkiv", 
		$modules_path,
		$modules_browserPath,
		"CMS_PICTUREARCHIVE"
	);  

	$modules["filearchive2"] = array(
		"Filarkiv", 
		$modules_path,
		$modules_browserPath,
		"CMS_FILEARCHIVE"
	);
	
	// GENERELLE INDST.
	$modules["general"] = array(
		"Nøgleord og metatags", 
		$modules_path,
		$modules_browserPath,
		"CMS_GENERAL"
	);  

	// RETTIGHEDER
		$modules["permissions"] = array(
		"Rettigheder", 
		$modules_path,
		$modules_browserPath,
		"CMS_GENERALPERMISSIONS"
	);


	// STATISTIK
		$modules["stats"] = array(
		"Statistik", 
		$modules_path,
		$modules_browserPath,
		"CMS_SITESTATS"
	);  

/*
	// BOOKMAKER
	$modules["bookmaker"] = array(
		"Bogværktøj", 
		$modules_path,
		$modules_browserPath,
		"CMS_BOOKMAKER"
	);  
*/
	 
	// FILER 
	$modules["attachments"] = array(
		"Vedhæftede filer", 
		$modules_path,
		$modules_browserPath,
		"CMS_FILEARCHIVE"
	);

	// SHOP PRODUCT CATALOGUE
	$modules["shopproductgroups"] = array(
		"Varegrupper",
		$modules_path,
		$modules_browserPath,
		"CMS_SHOPPRODUCTS"
	);
	$modules["shopproducts"] = array(
		"Varekatalog",
		$modules_path,
		$modules_browserPath,
		"CMS_SHOPPRODUCTS"
	);
	$modules["shopgroupdiscounts"] = array(
		"Grupperabatter",
		$modules_path,
		$modules_browserPath,
		"CMS_SHOPDISCOUNTS"
	);
	$modules["shopuserdiscounts"] = array(
		"Brugerrabatter",
		$modules_path,
		$modules_browserPath,
		"CMS_SHOPDISCOUNTS"
	);
	$modules["shoporderhistory"] = array(
		"Ordrehistorik",
		$modules_path,
		$modules_browserPath,
		"CMS_SHOPORDERHISTORY"
	);

	// BLOGS
	$modules["blogs"] = array(
		"Blog",
		$modules_path,
		$modules_browserPath,
		"CMS_BLOGS"
	);
	$modules["blogmanager"] = array(
		"Blog manager",
		$modules_path,
		$modules_browserPath,
		"CMS_BLOGMANAGER"
	);
	$modules["tags"] = array(
		"Tags",
		$modules_path,
		$modules_browserPath,
		"CMS_TAGMANAGER"
	);

	
// ====================================================================
// CMS MENU
// --------------------------------------------------------------------
function ____cms_menu(){};

/*
	// GENERIC_CMS_MENUGROUP
	$cms_Menu[]	=	array(
		[ARRAY: CONTAINS NAMES OF MODULES TO INCLUDE IN GROUP],
		[STRING-OPTIONAL: GROUP_MENUNAME]
	);
*/
	// CMS_MENU
	$cms_Menu 	= 	array();

	$cms_Menu[pages]	=	array(
		array("pages")
	);
	$cms_Menu[news]	=	array(
		array("news")
	);
	$cms_Menu[events]	=	array(
		array("events")
	);
	$cms_Menu[newsletter]	=	array(
		array("newsletter", "newslettercategories", "newslettertemplates", "newsletterimport"),
		"Nyhedsbrev"
	);
	$cms_Menu[users]	=	array(
		array("users", "groups"),
		"Brugerstyring"
	);
	$cms_Menu[picturearchive]	=	array(
		array("picturearchive")
	);
	$cms_Menu[filearchive2]	=	array(
		array("filearchive2")
	);
	$cms_Menu[formeditor2]	=	array(
		array("formeditor2")
	);
	$cms_Menu[customboxes]	=	array(
		array("customboxes")
	);
	$cms_Menu[bookmaker]	=	array(
		array("bookmaker")
	);
	$cms_Menu[blogs]	=	array(
		array("blogs", "blogmanager", "tags"),
		"Weblogs"
	);
	$cms_Menu[shop]	=	array(
		array("shopproducts", "shopproductgroups", "shopgroupdiscounts", "shopuserdiscounts", "shoporderhistory"),
		"Webshop"
	);
	$cms_Menu[general]	=	array(
		array("general", "permissions"),
		"Generelle indstillinger"
	);
	$cms_Menu[stats]	=	array(
		array("stats")
	);

// --------------------------------------------------------------------
// SEARCH ENGINE
function ____searchEngine() {}
// --------------------------------------------------------------------

/* Note that position[0] in "functionParameters" holds the searchwords (defined later). */
$tablesToSearch = 
	array(
		array(
			"name" => "PAGES", 
			"functionName" => "returnSearchResults",
			"functionParameters" => array("", "PAGES"),
			"resultsHeading" => "SearchResPages"
		),
		array(
			"name" => "NEWS",
			"functionName" => "returnSearchResults",
			"functionParameters" => array("", "NEWS"),
			"resultsHeading" => "SearchResNews"
		),
		array(
			"name" => "EVENTS", 
			"functionName" => "returnSearchResults",
			"functionParameters" => array("", "EVENTS"),
			"resultsHeading" => "SearchResEvents"
		),
		array(
			"name" => "PRODUCTS", 
			"functionName" => "returnSearchResults",
			"functionParameters" => array("", "PRODUCTS"),
			"resultsHeading" => "SearchResProducts"
		),
		array(
			"name" => "BLOGPOSTS", 
			"functionName" => "returnSearchResults",
			"functionParameters" => array("", "BLOGPOSTS"),
			"resultsHeading" => "SearchResBlogposts"
		)
	);

// ====================================================================
// NEWSLETTER
function ____newsletter() {}
// --------------------------------------------------------------------
	$batchsize = 5; // Number of mails to send out in each pass
	$batchwait = 0; // Number of milliseconds to wait between each pass

// ====================================================================
// PRODUCT CATALOGUE
function ____productCatalogue() {}
// --------------------------------------------------------------------

	// Rettigheder / login

	$fallback_login_gotourl		= "";	// If not defined on GROUPS table, use this as fallback
										// If this is not defined, user will be sent to frontpage
										
	// Her defineres hvilke rettigheder, der kræves for at benytte forskellige dele af shoppen. 
	// "" indikerer at funktionen er tilgængelig uden at logge ind
	$shopPermissions[browse] 	= ""; // FE_SHOPBROWSE
	$shopPermissions[prices] 	= ""; // FE_SHOPVIEWPRICES
	$shopPermissions[buy] 		= ""; // FE_SHOPCHECKOUT

	// Billeder i shop, størrelse i pixels, placering
	$shopImageWidth = 600;
	$shopThumbimageWidth = 120;

	$shopProductsBuySetQuantity = false;
	$shopProductsOrderBy = "PRODUCT_NUMBER asc";

	/// HVORDAN ER VARETABELLERNE BYGGET OP?
	$productTables = array(
		"SHOP_PRODUCTS" => array(
			"PRODUCT_ID_COLUMN" => "ID",
			"PRODUCT_PRODUCTNUMBER_COLUMN" => "PRODUCT_NUMBER",
			"PRODUCT_NAME_COLUMN" => "NAME",
			"PRODUCT_PRICE_COLUMN" => "PRICE", 
			"PRODUCT_DESCRIPTION_COLUMN" => "DESCRIPTION"
		),
	);

	/// ER PRISERNE I DB MED=1 ELLER UDEN=0 MOMS?
	$dbProductsMomsState	= 0;
	
	/// VISES PRISERNE I KURVEN MED=1 ELLER UDEN=0 MOMS?
	$cartProductsMomsState	= 0;

	/// MOMS-PROCENT OG DEN ANDEL, SOM MOMSEN UDGØR AF PRISEN INCL. MOMS
	
	$cartMomsPct			= 25;
	// Brug hvis priser i databasen er inkl. moms
	// $cartMomsPart			= 1-(1/(1+($cartMomsPct/100)));
	$cartMomsPart			= $cartMomsPct/100;
	
	/// SKAL DER VISES UDSPECIFICERET MOMS ("HERAF MOMS", ELLER BARE "MOMS" HVIS DB UDEN MOMS) FOR HVER VARELINJE?
	$showMomsForEachLine	= false;
	$showFragtForEachLine	= false;
	$showDeliveryDateInCart	= false;

	/// SKAL DER VISES RELATEREDE VARER I KURVEN?
	$showRelatedProductsInCart = true;
	
	/// DENNE ARRAY AFGØR RÆKKEFØLGEN AF LINJERNE I SLUTNINGEN AF KURVEN.
	/// TEXT = Ledetekst
	/// FRACTION = Gang USEVAR med denne faktor (f.eks. 1*$total = 1/1 af $total)
	/// ID = ID på html-element (for styling)
	$cartTotals = array(
		array("TEXT" => "CartTotalUnits", "FRACTION" => 1, "ID" => "cartTotalNoMoms", "USEVAR" => "total"),			// totalpris på enheder ex. moms
		array("TEXT" => "CartTotalFreight", "FRACTION" => 1, "ID" => "cartTotalFragt", "USEVAR" => "fragtTotal"),	// totalpris på fragt fra custom fragt funktion
		array("TEXT" => "CartVAT", "FRACTION" => $cartMomsPart, "ID" => "cartMoms", "USEVAR" => "totalWithFragt"), // Moms del af total 
		// Brug hvis priser i databasen er inkl. moms
		// array("TEXT" => "CartToBePaid", "FRACTION" => 1, "ID" => "cartTotal", "USEVAR" => "totalWithFragt")			// = total + fragtTotal
		array("TEXT" => "CartToBePaid", "FRACTION" => 1.25, "ID" => "cartTotal", "USEVAR" => "totalWithFragt")			// = total + fragtTotal * 1.25
	);
	
	/// VALUTAINDSTILLINGER
	$exchangeRates = array(
		"da" => array("FORKORTELSE" => "kr", "KURS" => 100)
	);
	
	/// FUNKTION TIL CUSTOM FRAGTBEREGNING
	$customFunctionsPath = $_SERVER[DOCUMENT_ROOT]. "/includes/plugins/phpfile_containing_function.php";
	$customFragtFunctionName = "name_of_custom_freight_function";
	
	/// FUNKTION TIL CHECKOUT FORMULAR
	$customCheckoutFunctionName = "";

	/// HVILKE TRIN SKAL DER VÆRE I CHECKOUT-FORMULAR
	$checkoutSteps = array(
		1 => "CartCheckoutFlowEnterAdress", 
		"CartCheckoutFlowSummary",
		"CartCheckoutFlowPay",		
		"CartCheckoutFlowReciept"
	);

	$cartCustomButtons = array(
		"SENDQUOTE" => false
	);
	
	$cartShowNoVATPerItem = 1;
	
	/// INDSTILLINGER TIL VAREKATALOG BACKEND
	$productDiameterUnit = "mm";
	$productLengthUnit = "mm";
	$shop_ordersPerHistoryPage = 20;

// ====================================================================
// ====================================================================
// FORMMAIL FRONTEND PLUGIN
function ____formmailFrontendPlugin() {}
// --------------------------------------------------------------------
// Secure PHP Form Mailer Script v4.0
// Created by Aleister 
// http://www.dagondesign.com/articles/secure-php-form-mailer-script/
// RECIPIENTS
// ----------
// One:       "onerecip|(email)"
// Multiple:  "mulrecip|(cc or bcc)|(email 1)|(email 2)  etc.. "
// Drop-down: "selrecip"  
// (the drop-down data must be added to the form structure below)
$formmailer_recipients = "onerecip|no-reply@no-reply.dk";

// FORM STRUCTURE
// --------------
// Please see the website for a full explanation of the structure.
// It is easy to use, but a bit too long to explain here :)
// !!!!!! Form definition is moved to the language file !!!!!!

// SPECIAL FIELDS
// ----------------
// This lets the script know the names of the 'special' fields
//
// VERIFICATION FIELD
// To disable verification, set this to "", and 
// remove the verification line from the form structure
$formmailer_field_verification = "fm_verify";

// EMAIL HEADER FIELDS
// These are the fields that will be used to generate the header
// for the email(s). If these are set to "", the email will use
// the default values (and you will not see the user's info in
// the email header - only in the message)
$formailer_field_name = "fm_name";
$formailer_field_subject = "fm_subject";
$formailer_field_email = "fm_email";

// DROP DOWN RECIPIENT
// Only applies if you are using a drop down to select a recipient
$formmailer_field_dropdownrecip = "fm_sendto";

// FORM OPTIONS
// ------------
// SHOW USER WHICH FIELDS ARE REQUIRED
//
// If enabled, this will put an asterisk next to required fields.
// (styled with .fmrequired)
$formmailer_show_required = TRUE;

// SHOW HEADER FIELDS IN MESSAGE
// If using name, email, and/or subject fields in the email header, 
// disabling this option will keep those fields from being shown in the 
// body of the message. (since the information will already be in the header)
$formmailer_show_headers_in_message = FALSE;

// SHOW VERIFICATION CODE IN MESSAGE
// If enabled, this will show the entered code in the email.
$formmailer_show_code_in_message = FALSE;

// WRAP MESSAGES
// Required to meet RFC specifications (70 characters per line).
$formmailer_wrap_messages = TRUE;
// ====================================================================


// ====================================================================
// SETTINGS FOR USER SELF REGISTRATION
// --------------------------------------------------------------------
	$dbfields_excluded = array("ID", "CREATED_DATE", "CHANGED_DATE", "UNFINISHED", "AUTHOR_ID", "DELETED", "RECEIVE_LETTERS", "EMAIL_VERIFIED");

// ====================================================================
// SETTINGS FOR USER MODULE: FIELDS TO BE VARIED PER USERGROUP (SET TO TRUE)
// --------------------------------------------------------------------
	$dbfields_vary = array(
		"JOB_TITLE" => false
	);

// ====================================================================
// BLOGS MODULE SETTINGS
// --------------------------------------------------------------------
	$blogs_overview_post_count 		= 10;
	$blogs_lastcommentedbox_count 	= 5;
	$blogs_show_tags_used_once		= true;

// ====================================================================
// SITEMAP & PING SERVICES
// --------------------------------------------------------------------
	$googlesitemap_generator = false;
	$googlesitemap_ping = false;
	$googlesitemap_pingintervalmin = 600;
	$ping_blogs = false;
?>