-- phpMyAdmin SQL Dump
-- version 4.0.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 23, 2013 at 02:03 PM
-- Server version: 5.5.32-cll
-- PHP Version: 5.3.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `lennaert_multiplayertest`
--

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `playercodes` varchar(32) NOT NULL,
  `message` text NOT NULL,
  `stamped` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE IF NOT EXISTS `players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `playercode` varchar(32) NOT NULL,
  `locx` float NOT NULL,
  `locy` float NOT NULL,
  `playerangle` float NOT NULL,
  `state` varchar(5) NOT NULL,
  `kills` int(11) NOT NULL,
  `killed` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `players`
--

INSERT INTO `players` (`id`, `playercode`, `locx`, `locy`, `playerangle`, `state`, `kills`, `killed`) VALUES
(1, 'dbac50bd5637c4f39e371cc8a6035896', 100, 511, 0, 'no', 2, 15),
(2, 'e6564ad047faccfe230da31b5826e3c9', 94, 114, 0, 'yes', 0, 23),
(3, 'b26a443cc7798405afde6bb257c6ff1c', 690, 505, 0, 'yes', 0, 3);

-- --------------------------------------------------------

--
-- Table structure for table `shotsfired`
--

CREATE TABLE IF NOT EXISTS `shotsfired` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shootercode` varchar(32) NOT NULL,
  `playercodes` varchar(32) NOT NULL,
  `angle` float NOT NULL,
  `stamped` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `shooter` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=92 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
