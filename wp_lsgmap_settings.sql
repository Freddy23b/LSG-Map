-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 15, 2017 at 05:21 PM
-- Server version: 5.7.14
-- PHP Version: 5.6.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lessallesgosses`
--

-- --------------------------------------------------------

--
-- Table structure for table `wp_lsgmap_settings`
--

CREATE TABLE `wp_lsgmap_settings` (
  `id` int(11) NOT NULL,
  `centerlat` double DEFAULT NULL,
  `centerlng` double DEFAULT NULL,
  `mapzoom` tinyint(4) DEFAULT NULL,
  `markerzoom` tinyint(4) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_lsgmap_settings`
--

INSERT INTO `wp_lsgmap_settings` (`id`, `centerlat`, `centerlng`, `mapzoom`, `markerzoom`) VALUES
(1, 44.6642282, -0.8634052, 8, 25);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wp_lsgmap_settings`
--
ALTER TABLE `wp_lsgmap_settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wp_lsgmap_settings`
--
ALTER TABLE `wp_lsgmap_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
