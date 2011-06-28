-- phpMyAdmin SQL Dump
-- version 2.11.5
-- http://www.phpmyadmin.net
--
-- Host: mysql01.dlx.dk
-- Generation Time: Jul 09, 2009 at 07:42 PM
-- Server version: 4.1.21
-- PHP Version: 5.2.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `web00228_dev`
--

-- --------------------------------------------------------

--
-- Table structure for table `ACTIVITY_LOG`
--

CREATE TABLE IF NOT EXISTS `ACTIVITY_LOG` (
  `ID` int(11) NOT NULL auto_increment,
  `NAME` text collate utf8_unicode_ci NOT NULL COMMENT 'Name of activity being logged',
  `LASTRUN` datetime NOT NULL default '0000-00-00 00:00:00' COMMENT 'Start of last run',
  `LASTRUN_COMPLETE` datetime NOT NULL default '0000-00-00 00:00:00' COMMENT 'End of last run',
  `DESCRIPTION` text collate utf8_unicode_ci NOT NULL,
  `STATUS` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Monitor activities f.instance google ping';

-- --------------------------------------------------------

--
-- Table structure for table `ATTACHMENTS`
--

CREATE TABLE IF NOT EXISTS `ATTACHMENTS` (
  `PAGE_ID` int(11) NOT NULL default '0',
  `FILE_ID` int(11) NOT NULL default '0',
  `TABEL` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Deprecated, replaced by RELATED_CONTENT';

-- --------------------------------------------------------

--
-- Table structure for table `BLOGPOSTS`
--

CREATE TABLE IF NOT EXISTS `BLOGPOSTS` (
  `ID` int(11) NOT NULL auto_increment,
  `BLOG_ID` int(11) NOT NULL default '0' COMMENT 'Refers to BLOGS.ID',
  `PUBLISHED` tinyint(1) NOT NULL default '0',
  `HEADING` text NOT NULL,
  `CONTENT` text NOT NULL,
  `CONTENTSNIPPET` text NOT NULL COMMENT 'Optional summary of content',
  `COMMENTS_ALLOWED` tinyint(1) NOT NULL default '0' COMMENT 'Allow comments on blogpost',
  `AUTHOR_ID` int(11) NOT NULL default '0' COMMENT 'Refers to USERS.ID',
  `CREATED_DATE` datetime NOT NULL default '0000-00-00 00:00:00',
  `CHANGED_BY` int(11) NOT NULL default '0',
  `CHANGED_DATE` datetime NOT NULL default '0000-00-00 00:00:00',
  `PUBLISHED_DATE` datetime NOT NULL default '0000-00-00 00:00:00',
  `UNFINISHED` tinyint(1) NOT NULL default '0',
  `DELETED` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds blogposts for all BLOGS';

-- --------------------------------------------------------

--
-- Table structure for table `BLOGS`
--

CREATE TABLE IF NOT EXISTS `BLOGS` (
  `ID` int(11) NOT NULL auto_increment,
  `SITE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SITES.ID',
  `TEMPLATE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to TEMPLATES.ID',
  `PUBLISHED` tinyint(1) NOT NULL default '0',
  `TITLE` text NOT NULL,
  `SUBTITLE` text NOT NULL,
  `DESCRIPTION` text NOT NULL,
  `COMMENTS_ALLOWED` tinyint(1) NOT NULL default '0' COMMENT 'Allow comments to be turned on for inividual posts',
  `COMMENTS_STRIPTAGS` text NOT NULL COMMENT 'Strip these tags from comments eg. <p><a>',
  `COMMENTS_EMAIL` tinyint(1) NOT NULL default '0' COMMENT 'Send comments to this email',
  `APPROVECOMMENTS` tinyint(1) NOT NULL default '0' COMMENT 'Require comments to be approved before showing them on the site',
  `SPAMPREVENT_AKISMETKEY` text NOT NULL COMMENT 'Optional Akismet key, for spam-prevention',
  `SPAMPREVENT_CAPTCHA` tinyint(1) NOT NULL default '0' COMMENT 'Use a captcha for spam-prevention',
  `SYNDICATION_ALLOWED` tinyint(1) NOT NULL default '0' COMMENT 'Publish rss feed',
  `SYNDICATION_SHOWCOMPLETEPOST` tinyint(4) NOT NULL default '0' COMMENT 'Include entire posts in rss feed',
  `SYNDICATION_SNIPPETLENGTH` int(11) NOT NULL default '0' COMMENT 'Length in number of sentences',
  `SYNDICATION_KEY` text NOT NULL COMMENT 'Key used in rss feed filename - change it to kick all subscribers',
  `CREATED_DATE` datetime NOT NULL default '0000-00-00 00:00:00',
  `CHANGED_DATE` datetime NOT NULL default '0000-00-00 00:00:00',
  `AUTHOR_ID` int(11) NOT NULL default '0' COMMENT 'Refers to USERS.ID',
  `UNFINISHED` tinyint(1) NOT NULL default '0',
  `LANGUAGE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to LANGUAGES.ID',
  `SHOW_PROFILEIMAGE` tinyint(1) NOT NULL default '0',
  `SHOW_COMPLETEPOST` tinyint(1) NOT NULL default '0' COMMENT 'Show complete posts on blog frontpage',
  `DELETED` tinyint(1) NOT NULL default '0',
  `ITEMS_DISPLAYCOUNT` int(11) NOT NULL default '0' COMMENT 'Number of blog-entries to display on blog frontpage',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds all blogs created in the blog manager module';

-- --------------------------------------------------------

--
-- Table structure for table `BOOKMATRICES`
--

CREATE TABLE IF NOT EXISTS `BOOKMATRICES` (
  `ID` int(11) NOT NULL auto_increment,
  `BOOK_ID` int(11) NOT NULL default '0',
  `MATRIX_TITLE` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `MATRIX_CONTENT` text,
  `CREATED_DATE` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `CHANGED_DATE` text character set utf8 collate utf8_unicode_ci NOT NULL,
  `CHANGED_BY` int(11) default NULL,
  `DELETED` char(1) NOT NULL default 'N',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Deprecated (module bookmaker). Indeholder tema matricer';

-- --------------------------------------------------------

--
-- Table structure for table `BOOKS`
--

CREATE TABLE IF NOT EXISTS `BOOKS` (
  `ID` int(11) NOT NULL auto_increment,
  `BOOKTITLE` text collate utf8_unicode_ci NOT NULL,
  `SUBTITLE` text collate utf8_unicode_ci,
  `PUBLISHER` text collate utf8_unicode_ci,
  `PUBMONTH` tinyint(2) default NULL,
  `PUBYEAR` smallint(4) default NULL,
  `PUBLISHED` tinyint(1) default NULL,
  `CREATED_DATE` text collate utf8_unicode_ci NOT NULL,
  `CHANGED_DATE` text collate utf8_unicode_ci NOT NULL,
  `DELETED` char(1) collate utf8_unicode_ci NOT NULL default 'N',
  `CHANGED_BY` int(11) default NULL,
  `PICTUREFOLDER_ID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Deprecated (module bookmaker). Indeholder bog-stamdata';

-- --------------------------------------------------------

--
-- Table structure for table `BOOKSECTIONS`
--

CREATE TABLE IF NOT EXISTS `BOOKSECTIONS` (
  `ID` int(11) NOT NULL auto_increment,
  `BOOK_ID` int(11) NOT NULL default '0',
  `PARENT_ID` int(11) NOT NULL default '0',
  `SECTIONTHREAD_ID` int(11) NOT NULL default '0',
  `POSITION` int(11) NOT NULL default '0',
  `TITLE` text collate utf8_unicode_ci NOT NULL,
  `CONTENT` text collate utf8_unicode_ci NOT NULL,
  `CONTENT_SIDE` text collate utf8_unicode_ci NOT NULL,
  `DELETED` char(1) collate utf8_unicode_ci NOT NULL default 'N',
  `CREATED_DATE` text collate utf8_unicode_ci NOT NULL,
  `CHANGED_DATE` text collate utf8_unicode_ci NOT NULL,
  `CHANGED_BY` int(11) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Deprecated (module bookmaker). Bog kapitler og afsnit.';

-- --------------------------------------------------------

--
-- Table structure for table `BOOKSTYLES`
--

CREATE TABLE IF NOT EXISTS `BOOKSTYLES` (
  `ID` int(11) NOT NULL auto_increment,
  `BOOK_ID` int(11) NOT NULL default '0',
  `HEADING_FONT` text collate utf8_unicode_ci,
  `MAIN_FONT` text collate utf8_unicode_ci,
  `SIDEBAR_FONT` text collate utf8_unicode_ci,
  `CAPTION_FONT` text collate utf8_unicode_ci,
  `ILL_BORDERWIDTH` int(11) NOT NULL default '0',
  `ILL_BORDERCOLOUR` int(11) NOT NULL default '0',
  `NUMBER_TO` tinyint(4) NOT NULL default '0',
  `SHOW_TOGETHER_FROM` tinyint(4) NOT NULL default '0',
  `SHOW_PARENT_LINKS` tinyint(4) NOT NULL default '0',
  `SHOW_CHAPTER_FRONTPAGES` char(1) collate utf8_unicode_ci NOT NULL default 'N',
  `COVERIMAGE_URL` text collate utf8_unicode_ci NOT NULL,
  `CREATED_DATE` text collate utf8_unicode_ci NOT NULL,
  `CHANGED_DATE` text collate utf8_unicode_ci NOT NULL,
  `CHANGED_BY` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Deprecated (module bookmaker). Bog stylesheet.';

-- --------------------------------------------------------

--
-- Table structure for table `BOOKTEMPLATES`
--

CREATE TABLE IF NOT EXISTS `BOOKTEMPLATES` (
  `ID` int(11) NOT NULL default '0',
  `BOOK_ID` int(11) NOT NULL default '0',
  `TEMPLATE_HTML` text collate utf8_unicode_ci NOT NULL,
  `TEMPLATE_CSS` text collate utf8_unicode_ci NOT NULL,
  `CREATED_DATE` text collate utf8_unicode_ci NOT NULL,
  `CHANGED_DATE` text collate utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Deprecated (module bookmaker). Bog-browser templates';

-- --------------------------------------------------------

--
-- Table structure for table `BOX_SETTINGS`
--

CREATE TABLE IF NOT EXISTS `BOX_SETTINGS` (
  `PAGE_ID` int(11) NOT NULL default '0' COMMENT 'Page that has boxes shown/hidden. Refers to PAGES.ID',
  `CUSTOM` text NOT NULL COMMENT 'Deprecated, custom boxes are related using RELATED_CONTENT',
  `NEWS` tinyint(4) NOT NULL default '0' COMMENT 'Show (1) / Hide (0) newsbox on PAGE_ID',
  `EVENTS` tinyint(4) NOT NULL default '0' COMMENT 'Show (1) / Hide (0) calendarbox on PAGE_ID',
  `SEARCH` tinyint(4) NOT NULL default '0' COMMENT 'Show (1) / Hide (0) searchbox on PAGE_ID',
  `STF` tinyint(4) NOT NULL default '0' COMMENT 'Show (1) / Hide (0) send-to-friend box on PAGE_ID',
  `NEWSLETTER` tinyint(4) NOT NULL default '0' COMMENT 'Deprecated, show/hide newsletter box',
  `LASTEDITED` tinyint(4) NOT NULL default '0' COMMENT 'Show (1) / Hide (0) last-edited box on PAGE_ID'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Where to show boxes (standard and custom)';

-- --------------------------------------------------------

--
-- Table structure for table `CALENDARS`
--

CREATE TABLE IF NOT EXISTS `CALENDARS` (
  `ID` int(11) NOT NULL auto_increment,
  `NAME` text NOT NULL COMMENT 'Name of calendar',
  `SITE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SITES.ID / 0 = kan vises på alle sites',
  `DEFAULT_LANGUAGE` int(11) NOT NULL default '0' COMMENT 'Refers to LANGUAGES.ID',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds calendars / event archives';

-- --------------------------------------------------------

--
-- Table structure for table `CART_CONTENTS`
--

CREATE TABLE IF NOT EXISTS `CART_CONTENTS` (
  `ID` int(11) NOT NULL auto_increment,
  `CART_ORDERS_ID` int(11) NOT NULL default '0' COMMENT 'Refers to CART_ORDERS.ID',
  `CART_ID` text collate utf8_unicode_ci NOT NULL COMMENT 'Deprecated, show/hide newsletter box',
  `PRODUCT_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SHOP_PRODUCTS.ID',
  `PRODUCT_TABLENAME` text collate utf8_unicode_ci NOT NULL COMMENT 'Name of product table. Usually SHOP_PRODUCTS but override is possible in cms_config',
  `COLLI_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SHOP_PRODUCTS_COLLI.ID',
  `AMOUNT` int(11) NOT NULL default '0' COMMENT 'Quantity added to cart',
  `FRAGT` float NOT NULL default '0' COMMENT 'Freight price if calculated per item',
  `DELIVERY_DAYS` int(11) NOT NULL default '0' COMMENT 'Days until delivery, if calculated per item',
  `CUSTOM_PRICE` float NOT NULL default '0' COMMENT 'Custom price if calculated',
  `CUSTOM_DESCRIPTION` text collate utf8_unicode_ci NOT NULL COMMENT 'Custom description for customized items',
  `TIME_ADDED` int(11) NOT NULL default '0' COMMENT 'time added to cart, unix timestamp',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Holds shopping cart items/orderlines';

-- --------------------------------------------------------

--
-- Table structure for table `CART_ORDERS`
--

CREATE TABLE IF NOT EXISTS `CART_ORDERS` (
  `ID` int(11) NOT NULL auto_increment,
  `UNIQUE_ORDERID` text collate utf8_unicode_ci NOT NULL COMMENT 'Deprecated. Autogenerated unique order id',
  `CART_ID` text collate utf8_unicode_ci NOT NULL COMMENT 'Cookie/session id used to reload cart on revisit',
  `USER_ID` int(11) NOT NULL default '0' COMMENT 'User logged in while shopping. Refers to USERS.ID',
  `NAME` text collate utf8_unicode_ci NOT NULL,
  `ADDRESS` text collate utf8_unicode_ci NOT NULL,
  `CITY` text collate utf8_unicode_ci NOT NULL,
  `ZIPCODE` int(11) NOT NULL default '0',
  `PHONE` text collate utf8_unicode_ci NOT NULL,
  `CELLPHONE` text collate utf8_unicode_ci NOT NULL,
  `FAX` text collate utf8_unicode_ci NOT NULL,
  `EMAIL` text collate utf8_unicode_ci NOT NULL,
  `NOTES` text collate utf8_unicode_ci NOT NULL,
  `COMPANY` text collate utf8_unicode_ci NOT NULL,
  `VAT_NUMBER` text collate utf8_unicode_ci NOT NULL,
  `ATTENTION` text collate utf8_unicode_ci NOT NULL,
  `PAYMENTTERM` int(11) NOT NULL default '0' COMMENT 'Refers to CART_PAYMENTTERMS.ID',
  `DELIVERYNAME` text collate utf8_unicode_ci NOT NULL,
  `DELIVERYADDRESS` text collate utf8_unicode_ci NOT NULL,
  `DELIVERYZIPCODE` text collate utf8_unicode_ci NOT NULL,
  `DELIVERYCITY` text collate utf8_unicode_ci NOT NULL,
  `DELETED` tinyint(4) NOT NULL default '0' COMMENT 'Cart orders are deleted upon checkout / transfer to SHOP_ORDERS',
  `DELIVERYCOMPANY` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Shopping-cart orders';

-- --------------------------------------------------------

--
-- Table structure for table `CART_ORDERSTATUS`
--

CREATE TABLE IF NOT EXISTS `CART_ORDERSTATUS` (
  `ID` int(11) NOT NULL auto_increment,
  `NAME` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Holds possible orderstates';

-- --------------------------------------------------------

--
-- Table structure for table `CART_ORDERS_ORDERSTATUS`
--

CREATE TABLE IF NOT EXISTS `CART_ORDERS_ORDERSTATUS` (
  `ID` int(11) NOT NULL auto_increment,
  `ORDER_ID` int(11) NOT NULL default '0' COMMENT 'Refers to CART_ORDERS.ID',
  `ORDERSTATUS_ID` int(11) NOT NULL default '0' COMMENT 'Refers to CART_ORDERSTATUS.ID',
  `CREATED_DATE` int(11) NOT NULL default '0' COMMENT 'Time status was set, unix timestamp',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Intersection table to join CART_ORDERS and CART_ORDERSTATUS';

-- --------------------------------------------------------

--
-- Table structure for table `CART_PAYMENTTERMS`
--

CREATE TABLE IF NOT EXISTS `CART_PAYMENTTERMS` (
  `ID` int(11) NOT NULL auto_increment,
  `TITLE` text collate utf8_unicode_ci NOT NULL COMMENT 'Name of payment term',
  `PERMISSION` text collate utf8_unicode_ci NOT NULL COMMENT 'Name of permission needed by logged in user to select this payment term. Refers to PERMISSIONS.NAME',
  `IS_CARDPAYMENT` tinyint(1) NOT NULL default '0' COMMENT 'If a paymentterm with IS_PAYMENT = 1 is chosen, user is presented with card-entry-step (not fully implemented)',
  `POSITION` int(11) NOT NULL default '0' COMMENT 'Paymentterms are ordered by POSITION',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Holds possible payment terms';

-- --------------------------------------------------------

--
-- Table structure for table `CMS_SITEDOMAINS`
--

CREATE TABLE IF NOT EXISTS `CMS_SITEDOMAINS` (
  `ID` int(11) NOT NULL auto_increment,
  `SITE_ID` int(11) NOT NULL default '0' COMMENT 'Refers til SITES.ID',
  `SUBDOMAIN` text NOT NULL COMMENT 'Subdomain (www f.instance) or wildcard (*)',
  `DOMAIN` text NOT NULL COMMENT 'Example: mydomain.com',
  `DEFAULT` tinyint(1) NOT NULL default '0' COMMENT 'Set one domain as default per site',
  `REDIRECT` int(11) NOT NULL default '0' COMMENT 'Redirect requests for this subdomain.domain to this row. Refers to CMS_SITEDOMAINS.ID',
  `PREFERRED_FOR_LANGUAGE` int(11) default NULL COMMENT 'Set to language_id if domain is preferred for site/language combination. Only set each language once per site.',
  `LANGUAGE` int(11) NOT NULL default '0' COMMENT 'Refers to LANGUAGES.ID. Set this language as default for this domain',
  `REDIRECT_TO_URL` text NOT NULL COMMENT 'Redirect to external url. Only use if REDIRECT is 0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Table holds all domains used for this CMS installation';

-- --------------------------------------------------------

--
-- Table structure for table `CMS_UPGRADES`
--

CREATE TABLE IF NOT EXISTS `CMS_UPGRADES` (
  `ID` int(11) NOT NULL auto_increment,
  `UPGRADE_ID` text NOT NULL,
  `UPGRADE_DESCRIPTION` text NOT NULL,
  `UPGRADE_BEGIN` datetime NOT NULL default '0000-00-00 00:00:00',
  `UPGRADE_END` datetime NOT NULL default '0000-00-00 00:00:00',
  `USER_LOGGED_IN` int(11) NOT NULL default '0' COMMENT 'Contains ID of user logged in when performing upgrade',
  `CMS_VERSION` text NOT NULL COMMENT 'CMS version at the time of upgrade',
  `CMS_BUILD` text NOT NULL COMMENT 'CMS build at the time of upgrade',
  `COMMENTS` text NOT NULL COMMENT 'Comments added by the upgrade process',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Log of CMS upgrades performed automatically';

-- --------------------------------------------------------

--
-- Table structure for table `COMMENTS`
--

CREATE TABLE IF NOT EXISTS `COMMENTS` (
  `ID` int(11) NOT NULL auto_increment,
  `TABLENAME` text NOT NULL COMMENT 'Name of table to which holds the item being commented',
  `REQUEST_ID` int(11) NOT NULL default '0' COMMENT 'Refers to TABLENAME.ID - the id of the item being commented',
  `COMMENT` text NOT NULL COMMENT 'The comment',
  `COMMENTER_ID` int(11) NOT NULL default '0' COMMENT 'If commenter is logged in, this refers to USERS.ID',
  `COMMENTER_NAME` text NOT NULL COMMENT 'Name of commenter',
  `COMMENTER_EMAIL` text NOT NULL COMMENT 'E-mail of commenter',
  `COMMENTER_URL` text NOT NULL COMMENT 'Website of commenter',
  `APPROVED` tinyint(1) NOT NULL default '0' COMMENT 'Comment approved yes(1) / no (0)',
  `IS_SPAM` tinyint(1) NOT NULL default '0' COMMENT 'Marked as span (1) / ham (0) by spam prevention measures or by admin-user',
  `CREATED_DATE` datetime NOT NULL default '0000-00-00 00:00:00',
  `DELETED` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds comments, f.instance blog-comments';

-- --------------------------------------------------------

--
-- Table structure for table `CUSTOMFIELDATTRIBUTES`
--

CREATE TABLE IF NOT EXISTS `CUSTOMFIELDATTRIBUTES` (
  `ID` int(11) NOT NULL auto_increment,
  `CUSTOMFIELDTYPE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to CUSTOMFIELDTYPES.ID',
  `ATTRIBUTENAME` text NOT NULL,
  `ATTRIBUTEKEY` text NOT NULL COMMENT 'Key used as a unique attribute identifyer in combination with CUSTOMFIELD_ID',
  `ATTRIBUTETYPE` text NOT NULL COMMENT 'Valid attributetypes are INT/TEXT/TEXTAREA/TEXTEDITOR/DROPDOWN/RADIOGROUP/IMAGESELECTOR/IMAGEARCHIVE/FILEARCHIVE or defined in custom_customfieldattributes.inc.php',
  `OPTIONS` text NOT NULL COMMENT 'option1___value1|||option2___value2 etc... (Only valid for types DROPDOWN and RADIOGROUP)',
  `DEFAULTVALUE` text NOT NULL,
  `POSITION` int(11) NOT NULL default '0' COMMENT 'Attributes are ordered by POSITION within each customfield',
  `PREFERENCES` text NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds attributes for custom field types';

-- --------------------------------------------------------

--
-- Table structure for table `CUSTOMFIELDDATA`
--

CREATE TABLE IF NOT EXISTS `CUSTOMFIELDDATA` (
  `ID` int(11) NOT NULL auto_increment,
  `CUSTOMFIELD_ID` int(11) NOT NULL default '0' COMMENT 'Refers to CUSTOMFIELDS.ID',
  `ATTRIBUTE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to CUSTOMFIELDATTRIBUTES.ID',
  `REQUEST_ID` int(11) NOT NULL default '0' COMMENT 'Refers to ID of item with customfield. Table is defined in CUSTOMFIELDS.TABLENAME',
  `VALUE` text NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds data for custom fields';

-- --------------------------------------------------------

--
-- Table structure for table `CUSTOMFIELDS`
--

CREATE TABLE IF NOT EXISTS `CUSTOMFIELDS` (
  `ID` int(11) NOT NULL auto_increment,
  `TEMPLATE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to TEMPLATES.ID, Use 0 for all templates',
  `TABLENAME` text NOT NULL COMMENT 'Show customfield on rows in this table. Currently only implemented for PAGES',
  `DESCRIPTION` text NOT NULL COMMENT 'Description of the custom field, displayed as a heading when editing',
  `FIELDKEY` text NOT NULL COMMENT 'Use a unique key to refer to this customfield in the template',
  `TYPE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to CUSTOMFIELDTYPES.ID',
  `POSITION` int(11) NOT NULL default '0' COMMENT 'Customfields are displayed in the "Specialfelter"-tab ordered by POSITION',
  `DELETED` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds custom field definitions';

-- --------------------------------------------------------

--
-- Table structure for table `CUSTOMFIELDTYPES`
--

CREATE TABLE IF NOT EXISTS `CUSTOMFIELDTYPES` (
  `ID` int(11) NOT NULL auto_increment,
  `TYPENAME` text NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds custom field types';

-- --------------------------------------------------------

--
-- Table structure for table `CUSTOM_BOXES`
--

CREATE TABLE IF NOT EXISTS `CUSTOM_BOXES` (
  `ID` int(11) NOT NULL auto_increment,
  `TYPE` tinyint(4) NOT NULL default '0' COMMENT '1 = fritekst / 2 = linkboks',
  `CREATED_DATE` text NOT NULL,
  `CHANGED_DATE` text NOT NULL,
  `AUTHOR_ID` int(11) NOT NULL default '0' COMMENT 'Refers to USERS.ID',
  `TITLE` text NOT NULL,
  `HEADING` text NOT NULL,
  `HEADING_BGCOL` text NOT NULL,
  `HEADING_TEXTCOL` text NOT NULL,
  `CONTENT` text NOT NULL,
  `CONTENT_BGCOL` text NOT NULL,
  `CONTENT_TEXTCOL` text NOT NULL,
  `UNFINISHED` tinyint(4) NOT NULL default '0',
  `SITE_ID` int(11) NOT NULL default '0' COMMENT 'Refers til SITES.ID / 0 = all sites',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Customboxes displayed by the customBoxes template function';

-- --------------------------------------------------------

--
-- Table structure for table `DATA_PERMISSIONS`
--

CREATE TABLE IF NOT EXISTS `DATA_PERMISSIONS` (
  `ID` int(11) NOT NULL auto_increment,
  `USER_ID` int(11) NOT NULL default '0' COMMENT 'Refers to USERS.ID. Used if datapermission is granted to individual user.',
  `GROUP_ID` int(11) NOT NULL default '0' COMMENT 'Refers to GROUPS.ID. Used when datapermission is granted to a usergroup',
  `DATA_TABLE_NAME` text NOT NULL COMMENT 'Permission granted for a row in this table',
  `DATA_ID` int(11) NOT NULL default '0' COMMENT 'Permission granted for this id on DATA_TABLE_NAME',
  `PERMISSION_ID` int(11) NOT NULL default '0' COMMENT 'Refers to PERMISSIONS.ID',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds permission-settings to access specific data.';

-- --------------------------------------------------------

--
-- Table structure for table `DEBATE`
--

CREATE TABLE IF NOT EXISTS `DEBATE` (
  `ID` int(11) NOT NULL auto_increment,
  `PARENT_ID` int(11) NOT NULL default '0',
  `AUTHOR_ID` int(11) NOT NULL default '0',
  `GROUP_ID` int(11) NOT NULL default '0',
  `HEADING` text NOT NULL,
  `CONTENT` text NOT NULL,
  `DATE` text NOT NULL,
  `THREAD_ID` int(11) NOT NULL default '0',
  KEY `ID` (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Deprecated';

-- --------------------------------------------------------

--
-- Table structure for table `DEFINED_FORMFIELDS`
--

CREATE TABLE IF NOT EXISTS `DEFINED_FORMFIELDS` (
  `ID` int(11) NOT NULL auto_increment,
  `FORM_ID` int(11) NOT NULL default '0' COMMENT 'Refers to DEFINED_FORMS.ID',
  `FIELDTYPE` tinyint(4) NOT NULL default '0',
  `CAPTION` text NOT NULL,
  `TEXT_SIZE` int(11) NOT NULL default '0',
  `TEXT_MAXLENGTH` int(11) NOT NULL default '0',
  `TEXT_DEFAULTTEXT` text NOT NULL,
  `TEXTAREA_COLS` int(11) NOT NULL default '0',
  `TEXTAREA_ROWS` int(11) NOT NULL default '0',
  `TEXTAREA_MAXLENGTH` int(11) NOT NULL default '0',
  `TEXTAREA_DEFAULTTEXT` text NOT NULL,
  `RADIO_COUNT` int(11) NOT NULL default '0',
  `RADIO_CAPTIONS` text NOT NULL,
  `RADIO_DISABLEDSTATES` text NOT NULL,
  `RADIO_SLETTETSTATES` text NOT NULL,
  `CHECKBOX_COUNT` int(11) NOT NULL default '0',
  `CHECKBOX_CAPTIONS` text NOT NULL,
  `CHECKBOX_DISABLEDSTATES` text NOT NULL,
  `CHECKBOX_SLETTETSTATES` text NOT NULL,
  `CHECKBOX_MINFILLED` int(11) NOT NULL default '0',
  `CHECKBOX_MAXFILLED` int(11) NOT NULL default '0',
  `VERIFY_FILLED` tinyint(4) NOT NULL default '0',
  `VERIFY_EMAIL` tinyint(4) NOT NULL default '0',
  `VERIFY_NUMBER` tinyint(4) NOT NULL default '0',
  `DISABLED` tinyint(4) NOT NULL default '0',
  `READONLY` tinyint(4) NOT NULL default '0',
  `EMAIL_MODTAGER` tinyint(4) NOT NULL default '0',
  `POSITION` int(11) NOT NULL default '0',
  `DELETED` tinyint(4) NOT NULL default '0',
  `HELPTEXT` text NOT NULL,
  `MAPPED_FIELD_ID` int(11) NOT NULL default '0',
  `LOCKED` tinyint(4) NOT NULL default '0',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds field-definitions for DEFINED_FORMS';

-- --------------------------------------------------------

--
-- Table structure for table `DEFINED_FORMS`
--

CREATE TABLE IF NOT EXISTS `DEFINED_FORMS` (
  `ID` int(11) NOT NULL auto_increment,
  `TITLE` text NOT NULL,
  `EMAIL` text NOT NULL,
  `CREATED_DATE` text NOT NULL,
  `AUTHOR_ID` int(11) NOT NULL default '0' COMMENT 'Refers to USERS.ID',
  `INTROTEXT` text NOT NULL,
  `ENDTEXT` text NOT NULL,
  `FORM_OPENDATE` date NOT NULL default '0000-00-00',
  `FORM_CLOSEDATE` date NOT NULL default '0000-00-00',
  `LINKTEXT` text NOT NULL,
  `SITE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SITES.ID / 0 = kan vises på alle sites',
  `SEND_MAIL` tinyint(4) NOT NULL default '0',
  `SAVE_IN_DB` tinyint(4) NOT NULL default '0',
  `DELETED` tinyint(1) NOT NULL default '0',
  `MAPPED_NEWSLETTER_ID` int(11) NOT NULL default '0' COMMENT 'Refers to NEWSLETTER_TEMPLATES.ID',
  `MAPPED_USERGROUP_ID` int(11) NOT NULL default '0',
  `SPAMPREVENT_CAPTCHA` tinyint(1) NOT NULL default '0',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds forms created in Module: formeditor2';

-- --------------------------------------------------------

--
-- Table structure for table `EMAIL_WHITELIST`
--

CREATE TABLE IF NOT EXISTS `EMAIL_WHITELIST` (
  `ID` int(11) NOT NULL auto_increment,
  `EMAIL` text NOT NULL,
  `TABLENAME` text NOT NULL COMMENT 'Address whitelisted for a row in this table',
  `REQUEST_ID` int(11) NOT NULL default '0' COMMENT 'Address whitelisted for this id on TABLENAME',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Automatically approve comments from people on whitelist';

-- --------------------------------------------------------

--
-- Table structure for table `EVENTS`
--

CREATE TABLE IF NOT EXISTS `EVENTS` (
  `ID` int(11) NOT NULL auto_increment,
  `TITLE` text NOT NULL,
  `HEADING` text NOT NULL,
  `SUBHEADING` text NOT NULL,
  `CONTENT` text NOT NULL,
  `AUTHOR_ID` int(11) NOT NULL default '0' COMMENT 'Refers to USERS.ID',
  `CREATED_DATE` text NOT NULL,
  `CHANGED_DATE` text NOT NULL,
  `UNFINISHED` tinyint(4) NOT NULL default '0',
  `DELETED` tinyint(4) NOT NULL default '0',
  `BEING_EDITED` tinyint(4) NOT NULL default '0' COMMENT 'Deprecated',
  `PUBLISHED` tinyint(4) NOT NULL default '0',
  `LOCKED_BY_USER` tinyint(4) NOT NULL default '0' COMMENT 'Deprecated',
  `DURATION` tinyint(4) NOT NULL default '0' COMMENT 'Deprecated',
  `STARTDATE` date NOT NULL default '0000-00-00',
  `ENDDATE` date NOT NULL default '0000-00-00',
  `TIMEOFDAY` text NOT NULL,
  `CALENDAR_ID` int(11) NOT NULL default '0' COMMENT 'Refers to CALENDARS.ID',
  `SITE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SITES.ID',
  `LANGUAGE` int(11) NOT NULL default '0' COMMENT 'Refers to LANGUAGES.ID',
  `GLOBAL_STATUS` tinyint(4) NOT NULL default '0' COMMENT 'Show on all sites',
  `FOCUSEVENT` tinyint(1) NOT NULL default '0' COMMENT 'Used to put focus on the event, can be used in plugins to draw out only focused elements or for css',
  `IMAGE_ID` int(11) NOT NULL default '0' COMMENT 'Used for event list-image',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds calendar events';

-- --------------------------------------------------------

--
-- Table structure for table `FILEARCHIVE_FILES`
--

CREATE TABLE IF NOT EXISTS `FILEARCHIVE_FILES` (
  `ID` int(11) NOT NULL auto_increment,
  `FOLDER_ID` int(11) NOT NULL default '0' COMMENT 'Refers to FILEARCHIVE_FOLDERS.ID ',
  `FILENAME` text NOT NULL,
  `ORIGINAL_FILENAME` text NOT NULL,
  `CREATED_DATE` text NOT NULL,
  `AUTHOR_ID` int(11) NOT NULL default '0' COMMENT 'Refers to USERS.ID',
  `TITLE` text NOT NULL,
  `DESCRIPTION` text NOT NULL,
  `EXTENSION` text NOT NULL,
  `MIMETYPE` text NOT NULL,
  `FILETYPE_ID` int(11) NOT NULL default '1' COMMENT 'Refers to FILEARCHIVE_TYPE.ID',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds files in filearchive';

-- --------------------------------------------------------

--
-- Table structure for table `FILEARCHIVE_FOLDERS`
--

CREATE TABLE IF NOT EXISTS `FILEARCHIVE_FOLDERS` (
  `ID` int(11) NOT NULL auto_increment,
  `TITLE` text NOT NULL,
  `FOLDERNAME` text NOT NULL,
  `CREATED_DATE` text NOT NULL,
  `AUTHOR_ID` int(11) NOT NULL default '0' COMMENT 'Refers to USERS.ID',
  `PRIVATE` tinyint(4) NOT NULL default '0' COMMENT 'Deprecated',
  `SITE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SITES.ID / 0 = kan vises på alle sites',
  `PARENT_ID` int(11) NOT NULL default '0' COMMENT 'ID of parent folder',
  `PUBLIC_FOLDER` tinyint(1) NOT NULL default '0' COMMENT 'Allow folder to be displayed on the website as a gallery (mode=picturearchive)',
  `FOLDER_DESCRIPTION` text NOT NULL,
  `THREAD_ID` int(11) NOT NULL default '0' COMMENT 'ID of top folder',
  `LEVEL` int(11) NOT NULL default '0' COMMENT 'Folder nested LEVELs deep',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds filearchive folders';

-- --------------------------------------------------------

--
-- Table structure for table `FILEARCHIVE_TYPE`
--

CREATE TABLE IF NOT EXISTS `FILEARCHIVE_TYPE` (
  `ID` int(11) NOT NULL auto_increment,
  `INTERNAL_NAME` text character set utf8 NOT NULL,
  `ICON_PATH` text character set utf8 NOT NULL,
  `DESCRIPTION` text NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Holds filetypes for use in filearchive';

-- --------------------------------------------------------

--
-- Table structure for table `GALLERIES`
--

CREATE TABLE IF NOT EXISTS `GALLERIES` (
  `PAGE_ID` int(11) NOT NULL default '0',
  `FOLDER_ID` int(11) NOT NULL default '0',
  `TABEL` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Deprecated';

-- --------------------------------------------------------

--
-- Table structure for table `GENERAL_SETTINGS`
--

CREATE TABLE IF NOT EXISTS `GENERAL_SETTINGS` (
  `ID` int(11) NOT NULL default '0' COMMENT 'Refers to SITES.ID',
  `META_DESCRIPTION` text NOT NULL COMMENT 'Default meta description',
  `META_KEYWORDS` text NOT NULL COMMENT 'Defailt meta keywords',
  `META_TITLE_USEPAGESCOLUMN` text NOT NULL COMMENT 'Default setting for page title',
  `CONTACT_EMAILS` text NOT NULL,
  `NEWSLETTER_GROUPID` int(11) default NULL COMMENT 'Holds ID of the usergroup containing newsletter subscribers'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Site-wide settings editable in cms';

-- --------------------------------------------------------

--
-- Table structure for table `GRANTS`
--

CREATE TABLE IF NOT EXISTS `GRANTS` (
  `PAGE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to PAGES.ID',
  `GRANTCODE` text NOT NULL,
  `USER_ID` int(11) NOT NULL default '0' COMMENT 'Refers to USERS.ID'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Holds temporary grants used to view unpublished pages.';

-- --------------------------------------------------------

--
-- Table structure for table `GROUPS`
--

CREATE TABLE IF NOT EXISTS `GROUPS` (
  `ID` int(11) NOT NULL auto_increment,
  `PARENT_ID` int(11) NOT NULL default '0',
  `GROUP_NAME` text NOT NULL,
  `DESCRIPTION` text NOT NULL,
  `AUTHOR_ID` int(11) NOT NULL default '0',
  `CREATED_DATE` text NOT NULL,
  `CHANGED_DATE` text NOT NULL,
  `UNFINISHED` tinyint(4) NOT NULL default '0',
  `HIDDEN` tinyint(1) NOT NULL default '0' COMMENT 'Hide group from all lists',
  `DELETED` tinyint(4) NOT NULL default '0',
  `REGISTRATION_OPEN` tinyint(4) NOT NULL default '0',
  `EDITING_OPEN` tinyint(4) NOT NULL default '0',
  `NOTIFY_USER_ID` int(11) NOT NULL default '0',
  `LANDING_GROUP_ID` int(11) NOT NULL default '0',
  `USERLIST_OPEN` tinyint(4) NOT NULL default '0',
  `DEFAULT_CONTENT_IDENTIFIER` text NOT NULL,
  `SORT_BY` text NOT NULL,
  `SITE_ID` int(11) NOT NULL default '0',
  `LOGIN_TO_URL` text NOT NULL,
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds usergroups';

-- --------------------------------------------------------

--
-- Table structure for table `GROUPS_FORMFIELDS`
--

CREATE TABLE IF NOT EXISTS `GROUPS_FORMFIELDS` (
  `ID` int(11) NOT NULL auto_increment,
  `GROUP_ID` int(11) NOT NULL default '0' COMMENT 'Refers to GROUPS.ID',
  `FIELD_ID` int(11) NOT NULL default '0' COMMENT 'Refers to NEWSLETTER_FORMFIELDS.ID (sorry)',
  `MANDATORY` tinyint(4) NOT NULL default '0',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds formfields for user self-registration form';

-- --------------------------------------------------------

--
-- Table structure for table `GROUPS_PAGES`
--

CREATE TABLE IF NOT EXISTS `GROUPS_PAGES` (
  `ID` int(11) NOT NULL auto_increment,
  `GROUP_ID` int(11) NOT NULL default '0' COMMENT 'Refers to GROUPS.ID',
  `PAGE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to PAGES.ID',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds frontend access restriction for pages';

-- --------------------------------------------------------

--
-- Table structure for table `GROUPS_PERMISSIONS`
--

CREATE TABLE IF NOT EXISTS `GROUPS_PERMISSIONS` (
  `ID` int(11) NOT NULL auto_increment,
  `GROUPS_ID` int(11) NOT NULL default '0' COMMENT 'Refers to GROUPS.ID',
  `PERMISSIONS_ID` int(11) NOT NULL default '0' COMMENT 'Refers to PERMISSIONS.ID',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Holds relation between usergroups and permissions in CMS';

-- --------------------------------------------------------

--
-- Table structure for table `LANGUAGES`
--

CREATE TABLE IF NOT EXISTS `LANGUAGES` (
  `ID` int(11) NOT NULL auto_increment,
  `NAME` text NOT NULL COMMENT 'Name of language',
  `SHORTNAME` text NOT NULL COMMENT 'Two-letter shortname matching shortname in cms_baselanguage.inc.php',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds languages. Find translatons in cms_baselanguage';

-- --------------------------------------------------------

--
-- Table structure for table `MENUS`
--

CREATE TABLE IF NOT EXISTS `MENUS` (
  `MENU_ID` int(11) NOT NULL auto_increment,
  `MENU_TITLE` text NOT NULL,
  `DEFAULT_LANGUAGE` int(11) NOT NULL default '0' COMMENT 'Refers to LANGUAGES.ID',
  `SITE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SITES.ID / 0 = kan vises på alle sites',
  KEY `MENU_ID` (`MENU_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds website menus / page archives';

-- --------------------------------------------------------

--
-- Table structure for table `NEWS`
--

CREATE TABLE IF NOT EXISTS `NEWS` (
  `ID` int(11) NOT NULL auto_increment,
  `NEWSFEED_ID` int(11) NOT NULL default '0' COMMENT 'Refers to NEWSFEEDS.ID',
  `TITLE` text NOT NULL,
  `HEADING` text NOT NULL,
  `SUBHEADING` text NOT NULL,
  `CONTENT` text NOT NULL,
  `AUTHOR_ID` int(11) NOT NULL default '0' COMMENT 'Refers to USERS.ID. Used if datapermission is granted to individual user.',
  `CREATED_DATE` text NOT NULL,
  `CHANGED_DATE` text NOT NULL,
  `UNFINISHED` tinyint(4) NOT NULL default '0',
  `DELETED` tinyint(4) NOT NULL default '0',
  `BEING_EDITED` tinyint(4) NOT NULL default '0' COMMENT 'Deprecated',
  `PUBLISHED` tinyint(4) NOT NULL default '0',
  `LOCKED_BY_USER` tinyint(4) NOT NULL default '0' COMMENT 'Deprecated',
  `NEWS_DATE` date NOT NULL default '0000-00-00',
  `FRONTPAGE_STATUS` tinyint(4) NOT NULL default '0' COMMENT 'Show newsitem on frontpage',
  `LIMITED` tinyint(4) NOT NULL default '0' COMMENT 'Show newsitem within a timespan',
  `LIMIT_START` date NOT NULL default '0000-00-00',
  `LIMIT_END` date NOT NULL default '0000-00-00',
  `SITE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SITES.ID',
  `LANGUAGE` int(11) NOT NULL default '0' COMMENT 'Refers to LANGUAGES.ID',
  `GLOBAL_STATUS` tinyint(4) NOT NULL default '0' COMMENT 'Show on all sites',
  `IMAGE_ID` int(11) NOT NULL default '0' COMMENT 'Newsitem image. Refers to PICTUREARCHIVE_PICS.ID',
  KEY `NEWS_ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds newsitems';

-- --------------------------------------------------------

--
-- Table structure for table `NEWSFEEDS`
--

CREATE TABLE IF NOT EXISTS `NEWSFEEDS` (
  `ID` int(11) NOT NULL auto_increment,
  `NAME` text NOT NULL,
  `SITE_ID` int(11) NOT NULL default '0' COMMENT '0 = kan vises på alle sites',
  `DEFAULT_LANGUAGE` int(11) NOT NULL default '0',
  `SYNDICATION_SHOWCOMPLETEPOST` tinyint(1) NOT NULL default '1' COMMENT 'Include complete newsitems in rss feed',
  `SYNDICATION_SNIPPETLENGTH` int(11) NOT NULL default '5' COMMENT 'Length of snippet, number of sentences',
  `SYNDICATION_ALLOWED` tinyint(1) NOT NULL default '0' COMMENT 'Publish rss feed',
  `SYNDICATION_KEY` text NOT NULL COMMENT 'Key used in rss feed filename - change it to kick all subscribers',
  `SHOW_IMAGES` tinyint(1) NOT NULL default '0' COMMENT 'Show images in newsarchive (not implemented)',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds newsarchives';

-- --------------------------------------------------------

--
-- Table structure for table `NEWSLETTERS`
--

CREATE TABLE IF NOT EXISTS `NEWSLETTERS` (
  `ID` int(11) NOT NULL auto_increment,
  `ARCHIVE_TITLE` text NOT NULL,
  `TITLE` text NOT NULL,
  `CONTENT_TOP` text NOT NULL,
  `CONTENT_BOTTOM` text NOT NULL,
  `IMAGES_DISPLAY` text NOT NULL COMMENT 'Value is: NONE, LEFT, RIGHT or ALTERNATING',
  `TEMPLATE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to row in NEWSLETTER_TEMPLATES table',
  `PROOF_TO` text NOT NULL COMMENT 'Holds e-mail adress to recieve the proof',
  `APPROVED` tinyint(1) NOT NULL default '0' COMMENT 'Only approved newsletters can be sent out',
  `APPROVED_BY` int(11) NOT NULL default '0',
  `APPROVED_TIME` text NOT NULL,
  `DELETED` tinyint(1) NOT NULL default '0',
  `SHOW_INDEX` tinyint(4) NOT NULL default '0' COMMENT 'Show newsitem index in mail',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds newsletter mailouts';

-- --------------------------------------------------------

--
-- Table structure for table `NEWSLETTER_CATEGORIES`
--

CREATE TABLE IF NOT EXISTS `NEWSLETTER_CATEGORIES` (
  `ID` int(11) NOT NULL auto_increment,
  `GROUP_ID` int(11) NOT NULL default '0' COMMENT 'Refers to NEWSLETTER_CATEGORYGROUPS.ID',
  `NAME` text NOT NULL,
  `DELETED` tinyint(4) NOT NULL default '0',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Newsletter interestgroup-items';

-- --------------------------------------------------------

--
-- Table structure for table `NEWSLETTER_CATEGORIES_OPTOUT`
--

CREATE TABLE IF NOT EXISTS `NEWSLETTER_CATEGORIES_OPTOUT` (
  `ID` int(11) NOT NULL auto_increment,
  `TEMPLATE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to NEWSLETTER_TEMPLATES.ID',
  `USER_ID` int(11) NOT NULL default '0' COMMENT 'Refers to USERS.ID',
  `CATEGORY_ID` int(11) NOT NULL default '0' COMMENT 'Refers to NEWSLETTER_CATEGORIES.ID',
  KEY `ID` (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Holds records of users opted out of interestgroup-items';

-- --------------------------------------------------------

--
-- Table structure for table `NEWSLETTER_CATEGORYGROUPS`
--

CREATE TABLE IF NOT EXISTS `NEWSLETTER_CATEGORYGROUPS` (
  `ID` int(11) NOT NULL auto_increment,
  `NAME` text NOT NULL,
  `DELETED` tinyint(4) NOT NULL default '0',
  `SITE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SITES.ID',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Newsletter interestgroups';

-- --------------------------------------------------------

--
-- Table structure for table `NEWSLETTER_FORMFIELDS`
--

CREATE TABLE IF NOT EXISTS `NEWSLETTER_FORMFIELDS` (
  `ID` int(11) NOT NULL auto_increment,
  `FIELD_NAME` text NOT NULL,
  `TABLE_NAME` text NOT NULL,
  `ID_COLUMN_NAME` text NOT NULL,
  `TEMPLATE_TAG` text NOT NULL,
  `POSITION` int(11) NOT NULL default '0',
  `TEMPLATETAG_ONLY` tinyint(4) NOT NULL default '0',
  `CMS_LABEL` text NOT NULL,
  `DEFAULT_VALUE` text NOT NULL COMMENT 'Holds $cmsLang[key]-key for translating',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Also used to hold formfields used when subscribing to GROUPS';

-- --------------------------------------------------------

--
-- Table structure for table `NEWSLETTER_HISTORY`
--

CREATE TABLE IF NOT EXISTS `NEWSLETTER_HISTORY` (
  `ID` int(11) NOT NULL auto_increment,
  `NEWSLETTER_ID` int(11) NOT NULL default '0' COMMENT 'Refers to NEWSLETTERS.ID',
  `TEMPLATE_ID` int(11) NOT NULL default '0' COMMENT 'newsletter_templates.id',
  `USER_ID` int(11) NOT NULL default '0' COMMENT 'id of user who sent the mail',
  `NO_RECIPIENTS` int(11) NOT NULL default '0',
  `SENDOUT_BEGINTIME` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `SENDOUT_COMPLETETIME` timestamp NOT NULL default '0000-00-00 00:00:00',
  `SENDOUT_SUBJECT` text NOT NULL,
  `SENDOUT_HTML` text NOT NULL,
  `SENDOUT_PLAINTEXT` text NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds history of newsletter mailings';

-- --------------------------------------------------------

--
-- Table structure for table `NEWSLETTER_HISTORY_MAILLOG`
--

CREATE TABLE IF NOT EXISTS `NEWSLETTER_HISTORY_MAILLOG` (
  `ID` int(11) NOT NULL auto_increment,
  `HISTORY_ID` int(11) NOT NULL default '0' COMMENT 'Refers to NEWSLETTER_HISTORY.ID',
  `USER_ID` int(11) NOT NULL default '0' COMMENT 'Refers to USERS.ID',
  `SENDOUT_COMPLETED` tinyint(1) NOT NULL default '0',
  `SENDOUT_COMPLETETIME` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds log of every mail sent to users through the newsletter';

-- --------------------------------------------------------

--
-- Table structure for table `NEWSLETTER_ITEMS`
--

CREATE TABLE IF NOT EXISTS `NEWSLETTER_ITEMS` (
  `ID` int(11) NOT NULL auto_increment,
  `CREATED_BY` int(11) NOT NULL default '0',
  `TEMPORARY` int(11) NOT NULL default '0',
  `NEWSLETTER_ID` int(11) NOT NULL default '0' COMMENT 'Refers to NEWSLETTER.ID',
  `ORIGINAL_ID` int(11) NOT NULL default '0',
  `ORIGINAL_TYPE` text NOT NULL,
  `HEADING` text NOT NULL,
  `CONTENT` text NOT NULL,
  `IMAGEMODE` text NOT NULL,
  `IMAGEURL` text NOT NULL,
  `LINKMODE` text NOT NULL,
  `LINKURL` text NOT NULL,
  `POSITION` int(11) NOT NULL default '0' COMMENT 'Items are shown in newsletter sorted by POSITION asc',
  `DELETED` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds newsletter items (mail elements)';

-- --------------------------------------------------------

--
-- Table structure for table `NEWSLETTER_NEWSLETTER_CATEGORIES`
--

CREATE TABLE IF NOT EXISTS `NEWSLETTER_NEWSLETTER_CATEGORIES` (
  `ID` int(11) NOT NULL auto_increment,
  `NEWSLETTER_ID` int(11) NOT NULL default '0' COMMENT 'Refers to NEWSLETTERS.ID',
  `CATEGORY_ID` int(11) NOT NULL default '0' COMMENT 'Refers to NEWSLETTER_CATEGORIES.ID',
  `USER_ID` int(11) NOT NULL default '0' COMMENT 'NOTE! This holds user_id of user who created intersection from newsletter. Used for deleting temporary items',
  `TEMPORARY` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Send newsletter to subscribers not opted out of these cats';

-- --------------------------------------------------------

--
-- Table structure for table `NEWSLETTER_STATS`
--

CREATE TABLE IF NOT EXISTS `NEWSLETTER_STATS` (
  `ID` int(11) NOT NULL auto_increment,
  `NEWSLETTER_ID` int(11) NOT NULL default '0' COMMENT 'Refers to NEWSLETTERS.ID',
  `USER_ID` int(11) NOT NULL default '0' COMMENT 'Refers to USERS.ID',
  `USER_ACTION` text NOT NULL,
  `TABLENAME` text NOT NULL,
  `REQUEST_ID` int(11) NOT NULL default '0',
  `CLICKED_URL` text NOT NULL,
  `TIMES_REPEATED` int(11) NOT NULL default '0',
  `CREATED_DATE` datetime NOT NULL default '0000-00-00 00:00:00',
  `CHANGED_DATE` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds stats for newsletter opened/clicked items';

-- --------------------------------------------------------

--
-- Table structure for table `NEWSLETTER_SUBSCRIPTIONS`
--

CREATE TABLE IF NOT EXISTS `NEWSLETTER_SUBSCRIPTIONS` (
  `ID` int(11) NOT NULL auto_increment,
  `USER_ID` int(11) NOT NULL default '0' COMMENT 'Refers to USERS.ID',
  `TEMPLATE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to NEWSLETTER_TEMPLATES.ID',
  `SUBSCRIBED` tinyint(4) NOT NULL default '0' COMMENT 'User is subscribed (1) or unsubscribed (0)',
  `CHANGED_DATE` int(11) NOT NULL default '0',
  `CONFIRMED` tinyint(4) NOT NULL default '0' COMMENT 'Usr has confirmed subscription, if required for this template',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds newsletter subscribtions';

-- --------------------------------------------------------

--
-- Table structure for table `NEWSLETTER_TEMPLATES`
--

CREATE TABLE IF NOT EXISTS `NEWSLETTER_TEMPLATES` (
  `ID` int(11) NOT NULL auto_increment,
  `OPEN_FOR_SUBSCRIPTIONS` tinyint(1) NOT NULL default '0' COMMENT 'OPen for new subscribers, 1 (yes) or 0 (no)',
  `SHOW_IN_NEWSARCHIVE` tinyint(1) NOT NULL default '0' COMMENT 'Show in public newsletter archive',
  `REQ_EMAIL_VALIDATION` tinyint(1) NOT NULL default '0' COMMENT 'Require email confirmation from new users',
  `SENDER_NAME` text NOT NULL,
  `SENDER_EMAIL` text NOT NULL,
  `REPLYTO_EMAIL` text NOT NULL,
  `BOUNCETO_EMAIL` text NOT NULL,
  `SUBSCRIPTIONPAGE_TEXTTOP` text NOT NULL,
  `SUBSCRIPTIONPAGE_TEXTBOTTOM` text NOT NULL,
  `SUBSCRIPTIONPAGE_TEXTTHANKS` text NOT NULL,
  `LANGUAGE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to LANGUAGES.ID',
  `SITE_ID` int(11) NOT NULL default '0' COMMENT 'Refers til SITES.ID',
  `TEMPLATE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to a template in the TEMPLATES table',
  `TITLE` text NOT NULL,
  `DELETED` tinyint(1) NOT NULL default '0',
  `NEWSUBSCRIBER_NOTIFY_EMAIL` text NOT NULL COMMENT 'Notify this mail about new subscribers',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds newsletters (templates for actual newsletter mailouts)';

-- --------------------------------------------------------

--
-- Table structure for table `NEWSLETTER_TEMPLATES_CATEGORYGROUPS`
--

CREATE TABLE IF NOT EXISTS `NEWSLETTER_TEMPLATES_CATEGORYGROUPS` (
  `ID` int(11) NOT NULL auto_increment,
  `TEMPLATE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to NEWSLETTER_TEMPLATES.ID',
  `CATEGORYGROUP_ID` int(11) NOT NULL default '0' COMMENT 'Refers to NEWSLETTER_CATEGORYGROUPS.ID',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Use these interest groups on the newsletter template';

-- --------------------------------------------------------

--
-- Table structure for table `NEWSLETTER_TEMPLATES_FORMFIELDS`
--

CREATE TABLE IF NOT EXISTS `NEWSLETTER_TEMPLATES_FORMFIELDS` (
  `ID` int(11) NOT NULL auto_increment,
  `TEMPLATE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to NEWSLETTER_TEMPLATES.ID',
  `FIELD_ID` int(11) NOT NULL default '0' COMMENT 'Refers to NEWSLETTER_FORMFIELDS.ID',
  `MANDATORY` tinyint(4) NOT NULL default '0' COMMENT '1 (yes) or 0 (no)',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Formfields on newsletter subscription form';

-- --------------------------------------------------------

--
-- Table structure for table `NEWSLETTER_TEMPLATES_USERGROUPS`
--

CREATE TABLE IF NOT EXISTS `NEWSLETTER_TEMPLATES_USERGROUPS` (
  `ID` int(11) NOT NULL auto_increment,
  `GROUP_ID` int(11) NOT NULL default '0' COMMENT 'Refers to GROUPS.ID',
  `TEMPLATE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to NEWSLETTER_TEMPLATES.ID',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Subscribed usergroups (in addition to subscribers)';

-- --------------------------------------------------------

--
-- Table structure for table `PAGES`
--

CREATE TABLE IF NOT EXISTS `PAGES` (
  `ID` int(11) NOT NULL auto_increment,
  `PARENT_ID` int(11) NOT NULL default '0' COMMENT 'ID of parent page',
  `THREAD_ID` int(11) NOT NULL default '0' COMMENT 'ID of top page',
  `MENU_ID` int(11) NOT NULL default '0' COMMENT 'Refers to MENUS.MENU_ID',
  `SITE_ID` int(11) NOT NULL default '0' COMMENT 'Refers til SITES.ID',
  `ENTRY_TYPE` tinyint(4) NOT NULL default '0' COMMENT 'Deprecated',
  `BREADCRUMB` text NOT NULL COMMENT 'Menu title',
  `HEADING` text NOT NULL,
  `SUBHEADING` text NOT NULL,
  `CONTENT` text NOT NULL,
  `CREATED_DATE` text NOT NULL,
  `CHANGED_DATE` text NOT NULL,
  `AUTHOR_ID` int(11) NOT NULL default '0' COMMENT 'Page created by. Refers to USERS.ID',
  `EDIT_AUTHOR_ID` int(11) NOT NULL default '0' COMMENT 'Page last edited by. Refers to USERS.ID',
  `DELETED` tinyint(4) NOT NULL default '0',
  `UNFINISHED` tinyint(4) NOT NULL default '0',
  `PUBLISHED` tinyint(4) NOT NULL default '0',
  `NO_DISPLAY` tinyint(4) NOT NULL default '0' COMMENT 'Hide in menus 1 (yes) / 0 (no)',
  `IS_FRONTPAGE` tinyint(4) NOT NULL default '0',
  `IS_MENUPLACEHOLDER` tinyint(11) NOT NULL default '0',
  `CHECKED_OUT` tinyint(4) NOT NULL default '0' COMMENT 'Deprecated',
  `CHECKED_OUT_AUTHOR` int(11) NOT NULL default '0' COMMENT 'Deprecated',
  `LOCKED_BY_USER` tinyint(4) NOT NULL default '0' COMMENT 'Deprecated',
  `LANGUAGE` int(11) NOT NULL default '0' COMMENT 'Refers to LANGUAGES.ID',
  `PROTECTED` tinyint(4) NOT NULL default '0' COMMENT 'Deprecated',
  `POSITION` int(11) NOT NULL default '0',
  `POPUP` tinyint(4) NOT NULL default '0',
  `POINTTOPAGE_URL` text NOT NULL,
  `MAILTO_ADDRESS` text NOT NULL,
  `BOOK_ID` int(11) default NULL COMMENT 'Deprecated',
  `PHP_INCLUDE_PATH` text NOT NULL,
  `PHP_INCLUDEAFTER_PATH` text NOT NULL,
  `PHP_HEADERINCLUDE_PATH` text,
  `POINTTOPAGE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to PAGES.ID',
  `TEMPLATE` int(11) NOT NULL default '0' COMMENT 'Refers to TEMPLATES.ID',
  `META_DESCRIPTION` text NOT NULL,
  `META_KEYWORDS` text NOT NULL,
  `META_SEOTITLE` text NOT NULL,
  `REDIRECT_TO_URL` text NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ID` (`PARENT_ID`,`PUBLISHED`,`DELETED`,`UNFINISHED`,`MENU_ID`,`SITE_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds pages';

-- --------------------------------------------------------

--
-- Table structure for table `PAGES_FORMS`
--

CREATE TABLE IF NOT EXISTS `PAGES_FORMS` (
  `PAGE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to PAGES.ID',
  `FORM_ID` int(11) NOT NULL default '0' COMMENT 'Refers to DEFINED_FORMS.ID',
  `TABEL` text NOT NULL COMMENT 'On which table the PAGE_ID resides (usually PAGES)',
  `INLINE` tinyint(4) NOT NULL default '0' COMMENT '1 (Show inline with page content) or (0) show as related content link'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Intersection table, relates DEFINED_FORMS to PAGES';

-- --------------------------------------------------------

--
-- Table structure for table `PERMISSIONGROUPS`
--

CREATE TABLE IF NOT EXISTS `PERMISSIONGROUPS` (
  `ID` bigint(20) NOT NULL auto_increment,
  `TITLE` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Holds permission groups for use in CMS';

-- --------------------------------------------------------

--
-- Table structure for table `PERMISSIONS`
--

CREATE TABLE IF NOT EXISTS `PERMISSIONS` (
  `ID` int(11) NOT NULL auto_increment,
  `NAME` text NOT NULL COMMENT 'Name of permission. This is a unique key used when querying the permission.',
  `DESCRIPTION` text NOT NULL COMMENT 'Description of the permission',
  `PERMISSIONGROUPS_ID` int(11) NOT NULL default '0' COMMENT 'Refers to PERMISSIONGROUPS.ID',
  `IS_DATAPERMISSION` tinyint(1) NOT NULL default '0' COMMENT '1 if the permission is a datapermission - that is a permission that you can grant usesrs/groups with respect to certain data elements. As opposed to normal permissions that are not data-relative and are granted to usergroups only',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds permissions';

-- --------------------------------------------------------

--
-- Table structure for table `PICTUREARCHIVE_FOLDERS`
--

CREATE TABLE IF NOT EXISTS `PICTUREARCHIVE_FOLDERS` (
  `ID` int(11) NOT NULL auto_increment,
  `TITLE` text NOT NULL,
  `FOLDERNAME` text NOT NULL,
  `CREATED_DATE` text NOT NULL,
  `AUTHOR_ID` int(11) NOT NULL default '0' COMMENT 'Refers to USERS.ID',
  `PRIVATE` tinyint(4) NOT NULL default '0' COMMENT 'Deprecated',
  `PARENT_ID` int(11) NOT NULL default '0' COMMENT 'ID of parent folder',
  `PUBLIC_FOLDER` tinyint(4) NOT NULL default '0' COMMENT 'Allow folder to display as public archive? 1 (Yes) or 0 (No)',
  `FOLDER_DESCRIPTION` text NOT NULL,
  `THUMBMODE` text NOT NULL COMMENT 'NEWEST eller FIRSTPOS',
  `THREAD_ID` int(11) NOT NULL default '0' COMMENT 'ID of top folder',
  `LEVEL` int(11) NOT NULL default '0' COMMENT 'Folder nested LEVELs deep',
  `SITE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SITES.ID, 0 = available on all sites',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds imagearchive folders';

-- --------------------------------------------------------

--
-- Table structure for table `PICTUREARCHIVE_IMPORTQUE`
--

CREATE TABLE IF NOT EXISTS `PICTUREARCHIVE_IMPORTQUE` (
  `ID` int(11) NOT NULL auto_increment,
  `BATCH_NUMBER` int(11) NOT NULL default '0',
  `TARGET_GROUP` int(11) NOT NULL default '0' COMMENT 'Refers to PICTUREARCHIVE_FOLDERS.ID',
  `PATH` text NOT NULL,
  `NAME` text NOT NULL,
  `EXTENSION` text NOT NULL,
  `SIZE` int(11) NOT NULL default '0',
  `PROCESSED` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds import que used when importing images from dropbox';

-- --------------------------------------------------------

--
-- Table structure for table `PICTUREARCHIVE_PICS`
--

CREATE TABLE IF NOT EXISTS `PICTUREARCHIVE_PICS` (
  `ID` int(11) NOT NULL auto_increment,
  `FOLDER_ID` int(11) NOT NULL default '0' COMMENT 'Refers to PICTUREARCHIVE_FOLDERS.ID',
  `FILENAME` text NOT NULL,
  `ORIGINAL_FILENAME` text NOT NULL,
  `AUTHOR_ID` int(11) NOT NULL default '0' COMMENT 'Refers to USERS.ID',
  `UNFINISHED` tinyint(4) NOT NULL default '0',
  `CREATED_DATE` text NOT NULL,
  `IMAGETYPE` int(11) NOT NULL default '0' COMMENT '1 = gif, 2 = jpg, 3 = png',
  `QUALITY` int(11) NOT NULL default '0' COMMENT 'jpeg compression quality, if relevant',
  `DESCRIPTION` text NOT NULL,
  `ALTTEXT` text NOT NULL,
  `SIZE_X` int(11) NOT NULL default '0' COMMENT 'Width in pixels',
  `SIZE_Y` int(11) NOT NULL default '0' COMMENT 'Height in pixels',
  `POSITION` int(11) NOT NULL default '0' COMMENT 'Sort order of images in folders and public galleries, POSITION asc',
  `VIEW_COUNT` int(11) NOT NULL default '0' COMMENT 'number of full image views in public image gallery',
  `ORIGINAL_ARCHIVED` tinyint(1) NOT NULL default '0' COMMENT '1 (original file archived) or 0 (original not archived)',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds imagearchive images';

-- --------------------------------------------------------

--
-- Table structure for table `PINGCHECK`
--

CREATE TABLE IF NOT EXISTS `PINGCHECK` (
  `ID` mediumint(8) unsigned NOT NULL auto_increment,
  `URL` tinytext NOT NULL,
  `SERVER` tinytext NOT NULL,
  `TIMECHECKED` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='USED TO ENFORCE PING INTERVAL';

-- --------------------------------------------------------

--
-- Table structure for table `RELATED_CONTENT`
--

CREATE TABLE IF NOT EXISTS `RELATED_CONTENT` (
  `ID` int(11) NOT NULL auto_increment,
  `SRC_TABEL` text NOT NULL COMMENT 'Name of table that holds thr item to which something is related',
  `SRC_ID` int(11) NOT NULL default '0' COMMENT 'ID of row that holds thr item to which something is related',
  `REL_TABEL` text NOT NULL COMMENT 'Name of table that holds whatever SRC_TABLE.SRC_ID is related to',
  `REL_ID` int(11) NOT NULL default '0' COMMENT 'ID of  item that SRC_TABLE.SRC_ID is related to',
  `CUSTOMBOX_ID` int(11) NOT NULL default '0' COMMENT 'Deprecated, custom boxes use same structure as other related content',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Intersection table to relate one content-item to another';

-- --------------------------------------------------------

--
-- Table structure for table `REWRITE_KEYWORDS`
--

CREATE TABLE IF NOT EXISTS `REWRITE_KEYWORDS` (
  `ID` int(11) NOT NULL auto_increment,
  `KEYWORD` text NOT NULL COMMENT 'Keyword which will appear in the URL',
  `TABLENAME` text NOT NULL COMMENT 'Name of table for data lookup',
  `REQUEST_ID` int(11) NOT NULL default '0' COMMENT 'ID to look-up in TABLENAME',
  `SITE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SITES.ID',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds keywords for rewriting URLs';

-- --------------------------------------------------------

--
-- Table structure for table `REWRITE_METHODS`
--

CREATE TABLE IF NOT EXISTS `REWRITE_METHODS` (
  `ID` int(11) NOT NULL auto_increment,
  `NAME` text NOT NULL COMMENT 'Method to run on content, will optionally appear as last part of URLs ',
  `INTERNALNAME` text NOT NULL,
  `LANGUAGE_ID` int(11) NOT NULL default '0' COMMENT 'Used when rewriting urls to ensure correct language',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds rewrite methods used in URLs';

-- --------------------------------------------------------

--
-- Table structure for table `REWRITE_MODES`
--

CREATE TABLE IF NOT EXISTS `REWRITE_MODES` (
  `ID` int(11) NOT NULL auto_increment,
  `NAME` text NOT NULL COMMENT 'Modename which will appear in URLS',
  `INTERNALNAME` text NOT NULL COMMENT 'Site-internal modename',
  `LANGUAGE_ID` int(11) NOT NULL default '0' COMMENT 'Used when rewriting urls to ensure correct language',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds MODES for rewriting URLs';

-- --------------------------------------------------------

--
-- Table structure for table `SHOP_GROUPDISCOUNTS`
--

CREATE TABLE IF NOT EXISTS `SHOP_GROUPDISCOUNTS` (
  `ID` int(11) NOT NULL auto_increment,
  `GROUP_ID` int(11) NOT NULL default '0' COMMENT 'Refers to GROUPS.ID',
  `DISCOUNT_PERCENTAGE` float NOT NULL default '0',
  `CHANGED_DATE` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  `PRODUCTGROUP_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SHOP_PRODUCTGROUPS.ID',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds group discounts used in shop';

-- --------------------------------------------------------

--
-- Table structure for table `SHOP_ORDERDETAILS`
--

CREATE TABLE IF NOT EXISTS `SHOP_ORDERDETAILS` (
  `ID` int(11) NOT NULL auto_increment,
  `ORDERNUMBER_SEQ` int(11) NOT NULL default '0' COMMENT 'Refers to SHOP_ORDERS.ORDERNUMBER_SEQ',
  `ORIGINAL_COOKIE_ID` text NOT NULL COMMENT 'Copied from CART_ORDERS.CART_ID',
  `PRODUCT_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SHOP_PRODUCTS.ID',
  `PRODUCT_TABLENAME` text NOT NULL,
  `AMOUNT` int(11) NOT NULL default '0' COMMENT 'Quantity',
  `FRAGT` float NOT NULL default '0',
  `FROZEN_PRODUCTNUMBER` text NOT NULL,
  `FROZEN_PRODUCTNAME` text NOT NULL,
  `FROZEN_PRODUCTDESCRIPTION` text NOT NULL,
  `FROZEN_PRODUCTPRICE` float NOT NULL default '0',
  `FROZEN_GROUPDISCOUNT` float NOT NULL default '0',
  `FROZEN_COLLIQUANTITY` int(11) NOT NULL default '0',
  `FROZEN_COLLIDISCOUNT_PCT` float NOT NULL default '0',
  `FROZEN_COLLIDISCOUNT_AMOUNT` float NOT NULL default '0',
  `FROZEN_TIME_ADDED` int(11) NOT NULL default '0',
  `FROZEN_USERPRICE` float default NULL,
  `FROZEN_CUSTOMPRICE` float default NULL,
  `FROZEN_CUSTOMDESCRIPTION` text NOT NULL,
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds orderdetails / orderlines for completed orders';

-- --------------------------------------------------------

--
-- Table structure for table `SHOP_ORDERS`
--

CREATE TABLE IF NOT EXISTS `SHOP_ORDERS` (
  `ID` int(11) NOT NULL auto_increment,
  `UNIQUE_ORDERID` text NOT NULL COMMENT 'Copied from CART_ORDERS.UNIQUE_ORDERID. (Deprecated. Autogenerated unique order id)',
  `ORDERNUMBER_SEQ` int(11) NOT NULL default '0' COMMENT 'Ordernumber shown to customer. Drawn from SHOP_ORDERSEQ',
  `ORIGINAL_COOKIE_ID` text NOT NULL COMMENT 'Copied from CART_ORDERS.CART_ID',
  `USER_ID` int(11) NOT NULL default '0' COMMENT 'Copied from CART_ORDERS.USER_ID (User logged in while shopping. Refers to USERS.ID)',
  `NAME` text NOT NULL,
  `ADDRESS` text NOT NULL,
  `CITY` text NOT NULL,
  `ZIPCODE` text NOT NULL,
  `PHONE` text NOT NULL,
  `CELLPHONE` text NOT NULL,
  `FAX` text NOT NULL,
  `EMAIL` text NOT NULL,
  `COMPANY` text NOT NULL,
  `VAT_NUMBER` text NOT NULL,
  `ATTENTION` text NOT NULL,
  `NOTES` text NOT NULL,
  `DELIVERYNAME` text NOT NULL,
  `DELIVERYADDRESS` text NOT NULL,
  `DELIVERYZIPCODE` text NOT NULL,
  `DELIVERYCITY` text NOT NULL,
  `FROZEN_PAYMENTTERM` text NOT NULL,
  `SITE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SITES.ID',
  `DELIVERYCOMPANY` text NOT NULL,
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds completed shop orders';

-- --------------------------------------------------------

--
-- Table structure for table `SHOP_ORDERSEQ`
--

CREATE TABLE IF NOT EXISTS `SHOP_ORDERSEQ` (
  `ID` int(11) NOT NULL auto_increment,
  `RESERVED_TIME` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'Holds time when the ordernumber was drawn, unix timestamp',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds sequence of order numbers';

-- --------------------------------------------------------

--
-- Table structure for table `SHOP_PRODUCTGROUPS`
--

CREATE TABLE IF NOT EXISTS `SHOP_PRODUCTGROUPS` (
  `ID` int(11) NOT NULL auto_increment,
  `PARENT_ID` int(11) NOT NULL default '0' COMMENT 'ID of parent productgroup',
  `NUMBER` text collate utf8_unicode_ci NOT NULL,
  `NAME` text collate utf8_unicode_ci NOT NULL,
  `DESCRIPTION` text collate utf8_unicode_ci NOT NULL,
  `IMAGE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to PICTUREARCHIVE_PICS.ID',
  `PUBLISHED` int(1) NOT NULL default '0',
  `DELETED` tinyint(1) NOT NULL default '0',
  `LISTMODE` tinyint(1) NOT NULL default '1',
  `SITE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SITES.ID',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table to hold shop product groups';

-- --------------------------------------------------------

--
-- Table structure for table `SHOP_PRODUCTGROUPS_FORMFIELDS`
--

CREATE TABLE IF NOT EXISTS `SHOP_PRODUCTGROUPS_FORMFIELDS` (
  `ID` int(11) NOT NULL auto_increment,
  `PRODUCTGROUP_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SHOP_PRODUCTGROUPS.ID',
  `FIELDNAME` text NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds information on which formfields to show for each produ';

-- --------------------------------------------------------

--
-- Table structure for table `SHOP_PRODUCTS`
--

CREATE TABLE IF NOT EXISTS `SHOP_PRODUCTS` (
  `ID` int(11) NOT NULL auto_increment,
  `GROUP_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SHOP_PRODUCTGROUPS.ID',
  `PRODUCT_NUMBER` text collate utf8_unicode_ci NOT NULL,
  `ALT_PRODUCT_NUMBER` text collate utf8_unicode_ci NOT NULL,
  `NAME` text collate utf8_unicode_ci NOT NULL,
  `DESCRIPTION` text collate utf8_unicode_ci NOT NULL,
  `IMAGE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to PICTUREARCHIVE_PICS.ID',
  `QUALITY_ID` int(11) NOT NULL default '0' COMMENT 'Referst to SHOP_PRODUCTS_QUALITIES.ID',
  `DIAMETER` float NOT NULL default '0',
  `LENGTH` float NOT NULL default '0',
  `PRICE` float NOT NULL default '0',
  `DELETED` tinyint(1) NOT NULL default '0',
  `DESCRIPTION_COMPLETE` text collate utf8_unicode_ci NOT NULL,
  `URL_EXT_INFO` text collate utf8_unicode_ci NOT NULL,
  `URL_EXT_PRODUCTSHEET` text collate utf8_unicode_ci NOT NULL,
  `SHOW_RELATED_PRODUCTS` tinyint(1) NOT NULL default '1' COMMENT 'Show related products when displaying this item in the shop',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table holds shop products';

-- --------------------------------------------------------

--
-- Table structure for table `SHOP_PRODUCTS_COLLI`
--

CREATE TABLE IF NOT EXISTS `SHOP_PRODUCTS_COLLI` (
  `ID` int(11) NOT NULL auto_increment,
  `PRODUCT_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SHOP_PRODUCTS.ID',
  `QUANTITY` int(11) NOT NULL default '0',
  `DISCOUNT_PERCENTAGE` float NOT NULL default '0',
  `DISCOUNT_AMOUNTPERCOLLI` float NOT NULL default '0',
  `DELETED` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Table to hold shop product collis';

-- --------------------------------------------------------

--
-- Table structure for table `SHOP_PRODUCTS_GROUPS`
--

CREATE TABLE IF NOT EXISTS `SHOP_PRODUCTS_GROUPS` (
  `ID` int(11) NOT NULL auto_increment,
  `PRODUCT_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SHOP_PRODUCTS.ID',
  `GROUP_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SHOP_PRODUCTGROUPS.ID',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds product-group association';

-- --------------------------------------------------------

--
-- Table structure for table `SHOP_PRODUCTS_QUALITIES`
--

CREATE TABLE IF NOT EXISTS `SHOP_PRODUCTS_QUALITIES` (
  `ID` int(11) NOT NULL auto_increment,
  `NAME` text collate utf8_unicode_ci NOT NULL,
  `DELETED` tinyint(1) NOT NULL default '0',
  `SITE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SITES.ID',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Holds shop product qualities';

-- --------------------------------------------------------

--
-- Table structure for table `SHOP_PRODUCTS_USERS`
--

CREATE TABLE IF NOT EXISTS `SHOP_PRODUCTS_USERS` (
  `ID` int(11) NOT NULL auto_increment,
  `USER_ID` int(11) NOT NULL default '0' COMMENT 'Refers to USERS.ID',
  `PRODUCT_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SHOP_PRODUCTS.ID',
  `CONTEXT` enum('defined','bought','viewed') NOT NULL default 'defined',
  `CREATED_DATE` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds association of SHOP_PRODUCTS and USERS for use in diff';

-- --------------------------------------------------------

--
-- Table structure for table `SHOP_RELATED_PRODUCTS`
--

CREATE TABLE IF NOT EXISTS `SHOP_RELATED_PRODUCTS` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `GROUP_ID` int(10) unsigned NOT NULL default '0' COMMENT 'Used if relating products to a productgroup. Refers to SHOP_PRODUCTGROUPS.ID',
  `ITEM_ID` int(10) unsigned NOT NULL default '0' COMMENT 'Used if relating products to a product. Refers to SHOP_PRODUCTS.ID',
  `RELATED_ITEM_ID` int(10) unsigned NOT NULL default '0' COMMENT 'ID of related product. Refers to SHOP_PRODUCTS.ID',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds information on related products';

-- --------------------------------------------------------

--
-- Table structure for table `SHOP_USERPRICES`
--

CREATE TABLE IF NOT EXISTS `SHOP_USERPRICES` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `USER_ID` int(10) unsigned NOT NULL default '0' COMMENT 'Refers to USERS.ID',
  `PRODUCT_ID` int(10) unsigned NOT NULL default '0' COMMENT 'Refers to SHOP_PRODUCTS.ID',
  `USERPRICE` decimal(9,2) NOT NULL default '0.00',
  `CHANGED_DATE` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds specific user prices';

-- --------------------------------------------------------

--
-- Table structure for table `SITES`
--

CREATE TABLE IF NOT EXISTS `SITES` (
  `SITE_ID` int(11) NOT NULL auto_increment,
  `SITE_NAME` text NOT NULL COMMENT 'Name of site',
  `BASE_URL` text NOT NULL COMMENT 'Site base url, f.instance "http://www.mydomain.com" Deprecated. Use CMS_SITEDOMAINS for new applications',
  `SITE_PATH` text NOT NULL COMMENT 'Make site appear to live in "/path-to-site". Deprecated',
  `DEFAULT_TEMPLATE` int(11) NOT NULL default '0' COMMENT 'Refers to TEMPLATES.ID',
  `EMAIL_DOMAIN` text NOT NULL COMMENT 'f.instance "mydomain.com". Only used sporadically',
  KEY `SITE_ID` (`SITE_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds sites managed by cms installation';

-- --------------------------------------------------------

--
-- Table structure for table `TAGS`
--

CREATE TABLE IF NOT EXISTS `TAGS` (
  `ID` int(11) NOT NULL auto_increment,
  `SITE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SITES.SITE_ID',
  `TAGNAME` text NOT NULL,
  `DELETED` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Tags used';

-- --------------------------------------------------------

--
-- Table structure for table `TAG_REFERENCES`
--

CREATE TABLE IF NOT EXISTS `TAG_REFERENCES` (
  `ID` int(11) NOT NULL auto_increment,
  `TAG_ID` int(11) NOT NULL default '0' COMMENT 'Refers to TAGS.ID',
  `TABLENAME` text NOT NULL COMMENT 'Name of table that holds item being tagged',
  `REQUEST_ID` int(11) NOT NULL default '0' COMMENT 'ID of item being tagged',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Intersection table to tag content items';

-- --------------------------------------------------------

--
-- Table structure for table `TEMPLATES`
--

CREATE TABLE IF NOT EXISTS `TEMPLATES` (
  `ID` int(11) NOT NULL auto_increment,
  `NAME` text collate utf8_unicode_ci NOT NULL,
  `DESCRIPTION` text collate utf8_unicode_ci NOT NULL,
  `PATH` text collate utf8_unicode_ci NOT NULL COMMENT 'Path to template, f.instance includes/templates/example.template.php (TYPE = PAGE only)',
  `PRINTTEMPLATE_PATH` text collate utf8_unicode_ci NOT NULL COMMENT 'Path to printable version of same template, f.instance includes/templates/example_printerfriendly.template.php (TYPE = PAGE only)',
  `CARTTEMPLATE_PATH` text collate utf8_unicode_ci NOT NULL COMMENT 'Path to shopping-cart template (TYPE = PAGE only)',
  `CHECKOUTTEMPLATE_PATH` text collate utf8_unicode_ci NOT NULL COMMENT 'Path to checkout template (remove distractions) (TYPE = PAGE only)',
  `TYPE` text collate utf8_unicode_ci NOT NULL COMMENT 'PAGE / NEWSLETTER',
  `FOLDER_NAME` text collate utf8_unicode_ci NOT NULL COMMENT 'Name of subfolder in /includes/templates that holds the template files  (TYPE = NEWSLETTER only)',
  `SITE_ID` int(11) NOT NULL default '0' COMMENT 'Refers to SITES.SITE_ID, 0 = template available on all sites',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Holds templates for use in CMS';

-- --------------------------------------------------------

--
-- Table structure for table `TILMELDINGER`
--

CREATE TABLE IF NOT EXISTS `TILMELDINGER` (
  `ID` int(11) NOT NULL auto_increment,
  `FORM_ID` int(11) NOT NULL default '0',
  `FIELD_IDS` text NOT NULL,
  `FIELD_VALUES` text NOT NULL,
  `CREATED_DATE` text NOT NULL,
  `UNIK` text NOT NULL,
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds data collected by DEFINED_FORMS (sorry)';

-- --------------------------------------------------------

--
-- Table structure for table `USERS`
--

CREATE TABLE IF NOT EXISTS `USERS` (
  `ID` int(11) NOT NULL auto_increment,
  `USERNAME` text NOT NULL COMMENT 'Must be unique, not enforced on database',
  `PASSWORD` text NOT NULL,
  `FIRSTNAME` text NOT NULL,
  `LASTNAME` text NOT NULL,
  `ADDRESS` text NOT NULL,
  `ZIPCODE` text NOT NULL,
  `CITY` text NOT NULL,
  `PHONE` text NOT NULL,
  `CELLPHONE` text NOT NULL,
  `EMAIL` text NOT NULL,
  `CV` text NOT NULL,
  `COMPANY` text NOT NULL,
  `CREATED_DATE` text NOT NULL,
  `CHANGED_DATE` text NOT NULL,
  `UNFINISHED` tinyint(4) NOT NULL default '0',
  `AUTHOR_ID` int(11) NOT NULL default '0' COMMENT 'ID of user who created this user, Refers til USERS.ID',
  `DELETED` tinyint(4) NOT NULL default '0',
  `RECEIVE_LETTERS` tinyint(4) NOT NULL default '0' COMMENT 'Deprecated',
  `EMAIL_VERIFIED` tinyint(4) NOT NULL default '0' COMMENT 'Only send newsletters to users with verified emails. Users created manually are automatically verified.',
  `INITIALS` text NOT NULL,
  `DATE_OF_BIRTH` date NOT NULL default '0000-00-00',
  `DATE_OF_HIRING` date NOT NULL default '0000-00-00',
  `DEPARTMENT` text NOT NULL,
  `JOB_TITLE` text NOT NULL,
  `TRANSFER_TO_GROUP` int(11) NOT NULL default '0' COMMENT 'Target group for user self-registration. user is moved to this group after approval.',
  `NEVER_PUBLIC` tinyint(4) NOT NULL default '0' COMMENT 'Exclude from lists generated by the system (by userlist plugin, f.instance)',
  `IMAGE_ID` int(11) NOT NULL default '0' COMMENT 'Photo of user, refers to PICTUREARCHIVE_PICS.ID',
  `PASSWORD_ENCRYPTED` text NOT NULL COMMENT 'md5 encrypted version of password. If no password is given in PASSWORD, login will check against this. Can be used when importing users from other systems. No plaintext password = problem sending out password per mail. Should probably be replaced by/combined with a reset password feature.',
  `COUNTRY` text NOT NULL,
  KEY `USER_ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds all cms users, website users, newsletter subscribers';

-- --------------------------------------------------------

--
-- Table structure for table `USERS_GROUPS`
--

CREATE TABLE IF NOT EXISTS `USERS_GROUPS` (
  `ID` int(11) NOT NULL auto_increment,
  `USER_ID` int(11) NOT NULL default '0' COMMENT 'Referst to USERS.ID',
  `GROUP_ID` int(11) NOT NULL default '0' COMMENT 'Refers to GROUPS.ID',
  `POSITION` int(11) NOT NULL default '0',
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Intersection table to include and position users in groups';

-- --------------------------------------------------------

--
-- Table structure for table `USERS_VARIED_FIELDS`
--

CREATE TABLE IF NOT EXISTS `USERS_VARIED_FIELDS` (
  `ID` int(11) NOT NULL auto_increment,
  `USER_ID` int(11) NOT NULL default '0' COMMENT 'Refers to USERS.ID',
  `GROUP_ID` int(11) NOT NULL default '0' COMMENT 'Refers to GROUPS.ID',
  `FIELD_NAME` text NOT NULL,
  `TABLE_NAME` text NOT NULL,
  `FIELD_VALUE` text NOT NULL,
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds userdata that vary across groups, see cms_config.inc.p';
