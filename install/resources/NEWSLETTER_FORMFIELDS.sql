-- phpMyAdmin SQL Dump
-- version 2.11.5
-- http://www.phpmyadmin.net
--
-- Host: mysql01.dlx.dk
-- Generation Time: Apr 15, 2008 at 07:34 PM
-- Server version: 4.1.21
-- PHP Version: 5.2.5

--
-- Database: `web00228_dev`
--

--
-- Dumping data for table `NEWSLETTER_FORMFIELDS`
--

INSERT INTO `NEWSLETTER_FORMFIELDS` (`ID`, `FIELD_NAME`, `TABLE_NAME`, `ID_COLUMN_NAME`, `TEMPLATE_TAG`, `POSITION`, `TEMPLATETAG_ONLY`, `CMS_LABEL`, `DEFAULT_VALUE`) VALUES
(1, 'FIRSTNAME', 'USERS', 'ID', '{{FORNAVN}}', 1, 0, 'Fornavn', 'NewsletterDefaultFirstname'),
(2, 'LASTNAME', 'USERS', 'ID', '{{EFTERNAVN}}', 2, 0, 'Efternavn', 'NewsletterDefaultLastname'),
(3, 'ADDRESS', 'USERS', 'ID', '{{ADRESSE}}', 3, 0, 'Adresse', 'NewsletterDefaultAddress'),
(4, 'ZIPCODE', 'USERS', 'ID', '{{POSTNR}}', 4, 0, 'Postnummer', 'NewsletterDefaultZipcode'),
(5, 'CITY', 'USERS', 'ID', '{{BY}}', 5, 0, 'By', 'NewsletterDefaultCity'),
(6, 'PHONE', 'USERS', 'ID', '{{TLF}}', 6, 0, 'Telefon', 'NewsletterDefaultPhone'),
(7, 'CELLPHONE', 'USERS', 'ID', '{{MOBILTLF}}', 7, 0, 'Mobiltlf.', 'NewsletterDefaultCellphone'),
(8, 'EMAIL', 'USERS', 'ID', '{{EMAIL}}', 8, 1, 'E-mail', 'NewsletterDefaultEmail'),
(9, 'COMPANY', 'USERS', 'ID', '{{FIRMA}}', 9, 0, 'Firma / organisation', 'NewsletterDefaultCompany'),
(10, 'CV', 'USERS', 'ID', '{{CV}}', 10, 0, 'CV', 'NewsletterDefaultCV'),
(11, 'USERNAME', 'USERS', 'ID', '{{USERNAME}}', 11, 0, 'Brugernavn', ''),
(12, 'PASSWORD', 'USERS', 'ID', '{{PASSWORD}}', 12, 0, 'Kodeord', '');
