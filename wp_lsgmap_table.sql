-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 15, 2017 at 05:22 PM
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
-- Table structure for table `wp_lsgmap_table`
--

CREATE TABLE `wp_lsgmap_table` (
  `id` int(11) NOT NULL,
  `type` varchar(55) CHARACTER SET utf8 NOT NULL,
  `lat` double DEFAULT NULL,
  `lng` double DEFAULT NULL,
  `address` varchar(500) NOT NULL,
  `title` varchar(30) DEFAULT NULL,
  `linkhref` varchar(500) CHARACTER SET utf8 DEFAULT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `wp_lsgmap_table`
--

INSERT INTO `wp_lsgmap_table` (`id`, `type`, `lat`, `lng`, `address`, `title`, `linkhref`, `description`) VALUES
(37, 'skatepark', 3, NULL, 'Eysines', '', '', ''),
(41, 'spot', NULL, 1, 'Biganos', 'BIGANOSPOT', 'http://caniuse.com/', 'Super spot'),
(40, 'asso', NULL, -1.2422112, 'Salles', 'Les SALLES gosses', '', '(en fait à l\'Herbe)'),
(45, 'shop', 44.6886468, -1.2422112, 'L\'Herbe', 'Skate Cap\' Shopping', '', 'Un magasin petit mais sympa.'),
(46, 'skatepark', 44.394378, -1.1702977, 'Biscarosse', 'Biska Skate', 'http://sales.maplinkbusiness.com/google_maplink_event_fr/', 'Un skatepark très fun.');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `wp_lsgmap_table`
--
ALTER TABLE `wp_lsgmap_table`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `wp_lsgmap_table`
--
ALTER TABLE `wp_lsgmap_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
