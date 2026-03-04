-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 04, 2026 at 12:06 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `markethub`
--

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `seller_id`, `quantity`, `price`) VALUES
(114, 66, 8, 18, 1, 67.00),
(115, 67, 8, 18, 3, 67.00),
(116, 68, 2, 17, 2, 13000.00),
(117, 69, 3, 17, 2, 1300.00),
(118, 69, 2, 17, 2, 13000.00),
(119, 69, 7, 17, 2, 123.00),
(120, 69, 8, 18, 6, 67.00),
(121, 70, 7, 17, 1, 123.00),
(122, 70, 3, 17, 1, 1300.00),
(123, 70, 2, 17, 1, 13000.00),
(124, 70, 1, 17, 1, 40.00),
(125, 70, 8, 18, 1, 67.00),
(126, 71, 7, 17, 1, 123.00),
(127, 71, 3, 17, 1, 1300.00),
(128, 71, 2, 17, 1, 13000.00),
(129, 71, 1, 17, 1, 40.00),
(130, 71, 8, 18, 1, 67.00),
(131, 72, 7, 17, 1, 123.00),
(132, 72, 3, 17, 1, 1300.00),
(133, 72, 2, 17, 1, 13000.00),
(134, 72, 1, 17, 1, 40.00),
(135, 72, 8, 18, 1, 67.00),
(136, 73, 7, 17, 1, 123.00),
(137, 73, 3, 17, 1, 1300.00),
(138, 73, 2, 17, 1, 13000.00),
(139, 73, 1, 17, 1, 40.00),
(140, 73, 8, 18, 1, 67.00),
(141, 74, 3, 17, 1, 1300.00),
(142, 75, 7, 17, 2, 123.00),
(143, 76, 3, 17, 1, 1300.00),
(144, 76, 1, 17, 1, 40.00),
(145, 77, 8, 18, 1, 67.00),
(146, 78, 7, 17, 2, 123.00),
(147, 78, 3, 17, 2, 1300.00),
(148, 78, 8, 18, 1, 67.00),
(149, 79, 8, 18, 1, 67.00),
(150, 80, 8, 18, 2, 67.00),
(151, 81, 7, 17, 1, 123.00),
(172, 89, 3, 17, 1, 1300.00),
(173, 89, 2, 17, 1, 13000.00),
(174, 89, 1, 17, 1, 40.00),
(175, 92, 2, 17, 8, 13000.00),
(176, 92, 8, 18, 1, 67.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
