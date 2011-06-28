-- phpMyAdmin SQL Dump
-- version 2.11.5
-- http://www.phpmyadmin.net
--
-- Host: mysql01.dlx.dk
-- Generation Time: Apr 15, 2008 at 07:48 PM
-- Server version: 4.1.21
-- PHP Version: 5.2.5

--
-- Database: `web00228_dev`
--

--
-- Dumping data for table `REWRITE_MODES`
--

INSERT INTO `REWRITE_MODES` (`ID`, `NAME`, `INTERNALNAME`, `LANGUAGE_ID`) VALUES
(1, 'nyheder', 'news', 1),
(2, 'news', 'news', 2),
(3, 'kalender', 'events', 1),
(4, 'events', 'events', 2),
(5, 'galleri', 'picturearchive', 1),
(6, 'gallery', 'picturearchive', 2),
(12, 'blog', 'blogs', 1),
(13, 'blog', 'blogs', 2),
(10, 'blogs', 'blogs', 1),
(11, 'blogs', 'blogs', 2),
(14, 'shop', 'shop', 1),
(15, 'shop', 'shop', 2),
(16, 'login', 'login', 1),
(17, 'login', 'login', 2),
(18, 'nyhedsbrev', 'newsletter', 1),
(19, 'newsletter', 'newsletter', 2);
