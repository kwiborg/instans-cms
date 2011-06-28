-- phpMyAdmin SQL Dump
-- version 2.11.5
-- http://www.phpmyadmin.net
--
-- Vært: mysql01.dlx.dk
-- Genereringstid: 15. 04 2008 kl. 13:47:38
-- Serverversion: 4.1.21
-- PHP-version: 5.2.5

--
-- Database: `web00228_dev`
--

--
-- Data dump for tabellen `PERMISSIONS`
--

INSERT INTO `PERMISSIONS` (`ID`, `NAME`, `DESCRIPTION`, `PERMISSIONGROUPS_ID`, `IS_DATAPERMISSION`) VALUES
(17, 'FE_LOGIN', 'Må logge ind på hjemmesiden (frontend login)', 2, 0),
(3, 'CMS_LOGIN', 'Må logge ind på cms', 1, 0),
(4, 'CMS_PAGES', 'Adgang til side-redigering', 7, 0),
(5, 'CMS_NEWS', 'Adgang til nyheds-redigering', 8, 0),
(6, 'CMS_EVENTS', 'Adgang til kalender-redigering', 9, 0),
(7, 'CMS_NEWSLETTERSEND', 'Adgang til at udsende nyhedsbreve', 10, 0),
(8, 'CMS_NEWSLETTERADMIN', 'Adgang til at nyhedsbrev-skabeloner', 10, 0),
(9, 'CMS_USERS', 'Adgang til at administrere brugere', 1, 0),
(10, 'CMS_GROUPS', 'Adgang til at administrere brugergrupper', 1, 0),
(11, 'CMS_PICTUREARCHIVE', 'Adgang til billedarkiv', 5, 0),
(12, 'CMS_FILEARCHIVE', 'Adgang til filarkiv', 11, 0),
(13, 'CMS_FORMS', 'Adgang til formular-editor', 12, 0),
(14, 'CMS_CUSTOMBOXES', 'Adgang til redigering af brugerdefinerede bokse', 7, 0),
(15, 'CMS_BOOKMAKER', 'Adgang til bogværktøj', 7, 0),
(16, 'CMS_GENERAL', 'Adgang til at redigere generelle indstillinger', 1, 0),
(19, 'CMS_SHOPPRODUCTS', 'Bruger kan redigere varer og varegrupper i shoppen', 4, 0),
(20, 'CMS_SHOPDISCOUNTS', 'Bruger kan se og ændre grupperabatter i shoppen', 4, 0),
(21, 'FE_SHOPBROWSE', 'Brugeren har adgang til at se shop-siderne på websitet', 4, 0),
(22, 'FE_SHOPVIEWPRICES', 'Brugeren har adgang til at se priser i shoppen (frontend rettighed)', 4, 0),
(23, 'FE_SHOPCHECKOUT', 'Brugeren har lov til at lægge varer i indkøbskurven og checke ud (frontend rettighed)', 4, 0),
(24, 'CMS_SHOPORDERHISTORY', 'Brugeren kan se ordrehistorik', 4, 0),
(25, 'CMS_MAILLIST', 'Adgang til maillist modulet', 3, 0),
(26, 'CMS_MAILLISTADMIN', 'Adgang til maillist manager modulet', 3, 0),
(27, 'CMS_MAILLISTMANAGER', 'Kan oprette / slette mailinglister', 3, 0),
(28, 'DATA_CMS_ACCESSSITE', 'Brugeren har adgang til CMS for dette site', 0, 1),
(29, 'DATA_CMS_MAILLISTACCESS', 'Adgang til at sende mails ud til listen og til at redigere stamoplysninger.', 0, 1),
(30, 'CMS_SETDATAPERMISSIONS', 'Brugeren må ændre datarettigheder (Maillist)', 3, 0),
(31, 'CMS_SITESTATS', 'Adgang til statistik for hjemmesiden', 1, 0),
(32, 'CMS_BLOGS', 'Adgang til at oprette / redigere / slette indlæg på relevante blogs.', 6, 0),
(33, 'CMS_BLOGMANAGER', 'Adgang til at oprette nye blogs og redigere / slette eksisterende blogs.', 6, 0),
(34, 'DATA_FE_BLOG_READ', 'Brugere har adgang til at læse bloggen', 6, 1),
(35, 'DATA_FE_BLOG_COMMENT', 'Brugere har adgang til at kommentere på bloggen', 6, 1),
(36, 'DATA_CMS_BLOG_PUBLISH', 'Brugere må skrive indlæg på bloggen', 6, 1),
(38, 'CMS_SETDATAPERMISSIONS_PICTUREARCHIVE_FOLDERS', 'Adgang til flere muligheder i billedarkiv. Brug dropbox, opret mapper på øverste niveau, sæt rettigheder på billedmapper', 5, 0),
(39, 'DATA_PICTUREARCHIVE_USEINCMS', 'Brugeren må benytte billeder fra mappen til indholdsredigering', 5, 1),
(40, 'DATA_PICTUREARCHIVE_MANAGEFOLDER', 'Brugeren må tilføje / redigere / slette billeder i mappen', 5, 1),
(41, 'CMS_TAGMANAGER', 'Adgang til at slette / sammenlægge tags', 1, 0),
(42, 'CMS_GENERALPERMISSIONS', 'Må ændre generelle rettigheder til menuer, nyhedsarkiver og kalendere.', 1, 0),
(43, 'DATA_CMS_MENU_ACCESS', 'Brugeren har adgang til menuen under "Sider"', 1, 1),
(44, 'DATA_CMS_NEWSARCHIVE_ACCESS', 'Brugeren har adgang til nyhedsarkivet under "Nyheder"', 1, 1),
(45, 'DATA_CMS_CALENDAR_ACCESS', 'Brugeren har adgang til kalenderen under "Kalender"', 1, 1),
(46, 'DATA_CMS_PAGE_ACCESS', 'Brugeren har adgang til at redigere / slette siden', 7, 1),
(47, 'DATA_CMS_NEWSITEM_ACCESS', 'Brugeren har adgang til at redigere / slette nyheden', 8, 1),
(48, 'DATA_CMS_EVENT_ACCESS', 'Brugeren har adgang til at redigere / slette arrangementet', 9, 1),
(49, 'CMS_SETDATAPERMISSIONS_PAGES', 'Brugeren må ændre rettigheder til enkeltsider', 7, 0),
(50, 'CMS_SETDATAPERMISSIONS_NEWS', 'Brugeren må ændre rettigheder til enkeltnyheder', 8, 0),
(51, 'CMS_SETDATAPERMISSIONS_EVENTS', 'Brugeren må ændre rettigheder til enkeltbegivenheder', 9, 0),
(52, 'CMS_SETDATAPERMISSIONS_CALENDARS', 'Brugeren må ændre rettigheder til kalenderarkiver', 9, 0),
(53, 'CMS_SETDATAPERMISSIONS_NEWSFEEDS', 'Brugeren må ændre rettigheder til nyhedsarkiver', 8, 0),
(54, 'CMS_SETDATAPERMISSIONS_MENUS', 'Brugeren må ændre rettigheder til menuer', 7, 0),
(55, 'CMS_SETDATAPERMISSIONS_BLOGS', 'Adgang til at sætte datarettigheder på blogs', 6, 0),
(56, 'CMS_SETDATAPERMISSIONS_FILEARCHIVE_FOLDERS', 'Udvidede rettigheder i filarkiv', 11, 0);
