-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3308
-- Generation Time: Dec 13, 2020 at 02:31 PM
-- Server version: 8.0.18
-- PHP Version: 7.3.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_filemanagement`
--

-- --------------------------------------------------------

--
-- Table structure for table `user_added_content`
--

DROP TABLE IF EXISTS `user_added_content`;
CREATE TABLE IF NOT EXISTS `user_added_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `type_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `updated_date` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type_id` (`type_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_added_content`
--

INSERT INTO `user_added_content` (`id`, `title`, `content`, `type_id`, `user_id`, `updated_date`) VALUES
(36, 'New File', '/uploads/20201208175404_File.docx', 1, 3, '2020-12-08 12:54:04'),
(37, 'New Link', 'http://www.google.com', 2, 3, '2020-12-08 12:54:31'),
(38, 'New Note', 'A note example.', 3, 3, '2020-12-08 12:54:50'),
(39, 'w3schools', 'w3schools.com', 2, 3, '2020-12-08 12:55:19'),
(40, 'Reminder', 'A reminder.', 3, 3, '2020-12-08 12:55:38'),
(41, 'A file', '/uploads/20201208175944_File.docx', 1, 3, '2020-12-08 12:59:44'),
(42, 'A note', 'A note.\n\nAs an example.', 3, 3, '2020-12-08 13:00:09'),
(43, 'A link', 'facebook.com', 2, 3, '2020-12-08 13:00:46'),
(44, 'Note title', 'An example.', 3, 3, '2020-12-08 13:01:25'),
(45, 'Test Note', 'Test.', 3, 3, '2020-12-08 13:01:48'),
(46, 'Test file', '/uploads/20201208180208_File.docx', 1, 3, '2020-12-08 13:02:08');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_added_content`
--
ALTER TABLE `user_added_content`
  ADD CONSTRAINT `user_added_content_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `content_types` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `user_added_content_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
