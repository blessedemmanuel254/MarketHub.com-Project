-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 05, 2026 at 08:23 PM
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
-- Database: `makethub`
--

-- --------------------------------------------------------

--
-- Table structure for table `financial_transactions`
--

CREATE TABLE `financial_transactions` (
  `transaction_id` bigint(20) UNSIGNED NOT NULL,
  `source_type` enum('order','wallet','commission','withdrawal','bonus','refund','seller_withdrawal') NOT NULL,
  `source_id` bigint(20) UNSIGNED DEFAULT NULL,
  `wallet_id` bigint(20) UNSIGNED NOT NULL,
  `payer_id` bigint(20) UNSIGNED NOT NULL,
  `receiver_id` bigint(20) UNSIGNED NOT NULL,
  `transaction_type` enum('credit','debit','commission','withdrawal','refund','bonus') NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'KES',
  `payment_method` enum('mpesa','bank','paypal','crypto','internal') DEFAULT 'internal',
  `status` enum('pending','processing','completed','failed','reversed') DEFAULT 'pending',
  `reference_code` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_transactions`
--

INSERT INTO `financial_transactions` (`transaction_id`, `source_type`, `source_id`, `wallet_id`, `payer_id`, `receiver_id`, `transaction_type`, `amount`, `currency`, `payment_method`, `status`, `reference_code`, `description`, `created_at`, `updated_at`) VALUES
(1, 'commission', 47, 11, 47, 29, 'commission', 100.00, 'KES', 'internal', 'pending', NULL, 'Agent activation commission - Level 1', '2026-04-01 08:46:43', '2026-04-01 08:46:43'),
(2, 'commission', 47, 12, 47, 29, 'commission', 100.00, 'KES', 'internal', 'completed', NULL, 'Level 1 commission from agent 47', '2026-04-01 08:55:02', '2026-04-01 08:55:02'),
(3, 'commission', 48, 42, 48, 47, 'commission', 100.00, 'KES', 'internal', 'pending', NULL, 'Pending Level 1 commission from agent 48', '2026-04-01 09:28:12', '2026-04-01 09:28:12'),
(4, 'commission', 48, 11, 48, 29, 'commission', 40.00, 'KES', 'internal', 'pending', NULL, 'Pending Level 2 commission from agent 48', '2026-04-01 09:28:12', '2026-04-01 09:28:12'),
(5, 'withdrawal', 29, 12, 29, 29, 'withdrawal', 500.00, 'KES', 'internal', 'pending', NULL, 'Agency wallet withdrawal request', '2026-04-01 16:53:48', '2026-04-01 16:53:48'),
(6, 'withdrawal', 29, 12, 29, 29, 'withdrawal', 400.00, 'KES', 'internal', 'pending', NULL, 'Agency wallet withdrawal request', '2026-04-01 17:02:51', '2026-04-01 17:02:51'),
(7, 'withdrawal', 29, 12, 29, 29, 'withdrawal', 600.00, 'KES', 'internal', 'pending', NULL, 'Agency wallet withdrawal request', '2026-04-01 17:03:06', '2026-04-01 17:03:06'),
(8, 'withdrawal', 29, 12, 29, 29, 'withdrawal', 600.00, 'KES', 'internal', 'pending', NULL, 'Agency wallet withdrawal request', '2026-04-01 17:03:39', '2026-04-01 17:03:39'),
(9, 'withdrawal', 29, 12, 29, 29, 'withdrawal', 600.00, 'KES', 'internal', 'pending', NULL, 'Agency wallet withdrawal request', '2026-04-01 17:03:54', '2026-04-01 17:03:54'),
(10, 'withdrawal', 29, 12, 29, 29, 'withdrawal', 600.00, 'KES', 'internal', 'pending', NULL, 'Agency wallet withdrawal request', '2026-04-01 17:04:02', '2026-04-01 17:04:02'),
(11, 'withdrawal', 29, 12, 29, 29, 'withdrawal', 50.00, 'KES', 'internal', 'pending', NULL, 'Agency wallet withdrawal request', '2026-04-01 17:05:17', '2026-04-01 17:05:17'),
(12, 'withdrawal', 29, 12, 29, 29, 'withdrawal', 6.00, 'KES', 'internal', 'pending', NULL, 'Agency wallet withdrawal request', '2026-04-01 17:05:32', '2026-04-01 17:05:32'),
(13, 'withdrawal', 29, 12, 29, 29, 'withdrawal', 4.00, 'KES', 'internal', 'pending', NULL, 'Agency wallet withdrawal request', '2026-04-01 17:05:50', '2026-04-01 17:05:50'),
(14, 'withdrawal', 29, 12, 29, 29, 'withdrawal', 4.00, 'KES', 'internal', 'pending', NULL, 'Agency wallet withdrawal request', '2026-04-01 17:12:08', '2026-04-01 17:12:08'),
(15, 'withdrawal', 29, 12, 29, 29, 'withdrawal', 5.00, 'KES', 'internal', 'pending', NULL, 'Agency wallet withdrawal request', '2026-04-01 17:14:32', '2026-04-01 17:14:32'),
(16, 'withdrawal', 29, 12, 29, 29, 'withdrawal', 5.00, 'KES', 'internal', 'pending', NULL, 'Agency wallet withdrawal request', '2026-04-01 17:15:02', '2026-04-01 17:15:02'),
(17, 'withdrawal', 29, 12, 29, 29, 'withdrawal', 10.00, 'KES', 'internal', 'pending', NULL, 'Agency wallet withdrawal request', '2026-04-01 17:15:41', '2026-04-01 17:15:41'),
(18, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 500.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 17:16:23', '2026-04-01 17:16:23'),
(19, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 3000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 17:29:01', '2026-04-01 17:29:01'),
(20, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 10000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 17:29:24', '2026-04-01 17:29:24'),
(21, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 3000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 17:39:37', '2026-04-01 17:39:37'),
(22, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 90000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 17:39:50', '2026-04-01 17:39:50'),
(23, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 3000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 17:50:19', '2026-04-01 17:50:19'),
(24, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 10000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 17:50:46', '2026-04-01 17:50:46'),
(25, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 100000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 17:51:19', '2026-04-01 17:51:19'),
(26, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 100000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 17:52:02', '2026-04-01 17:52:02'),
(27, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 7000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 17:53:38', '2026-04-01 17:53:38'),
(28, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 15000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 17:54:12', '2026-04-01 17:54:12'),
(29, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 20000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 17:55:18', '2026-04-01 17:55:18'),
(30, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 35000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 17:56:10', '2026-04-01 17:56:10'),
(31, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 49000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 17:57:23', '2026-04-01 17:57:23'),
(32, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 2600.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 18:06:01', '2026-04-01 18:06:01'),
(34, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 5000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 18:10:01', '2026-04-01 18:10:01'),
(35, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 5000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 18:12:50', '2026-04-01 18:12:50'),
(36, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 5000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 18:13:07', '2026-04-01 18:13:07'),
(37, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 5000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 18:14:04', '2026-04-01 18:14:04'),
(38, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 500.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 18:14:43', '2026-04-01 18:14:43'),
(39, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 500.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 18:15:01', '2026-04-01 18:15:01'),
(40, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 5000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 18:19:30', '2026-04-01 18:19:30'),
(41, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 8000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 18:20:39', '2026-04-01 18:20:39'),
(43, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 3000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 18:24:31', '2026-04-01 18:24:31'),
(44, 'withdrawal', 31, 17, 31, 31, 'withdrawal', 500.00, 'KES', 'internal', 'pending', NULL, 'Agency wallet withdrawal request', '2026-04-01 23:11:17', '2026-04-01 23:11:17'),
(45, 'withdrawal', 31, 20, 31, 31, 'withdrawal', 500.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-01 23:22:07', '2026-04-01 23:22:07'),
(46, 'withdrawal', 31, 17, 31, 31, 'withdrawal', 460.00, 'KES', 'internal', 'pending', NULL, 'Agency wallet withdrawal request', '2026-04-01 23:48:28', '2026-04-01 23:48:28'),
(47, 'withdrawal', 29, 12, 29, 29, 'withdrawal', 2000.00, 'KES', 'internal', 'pending', NULL, 'Agency wallet withdrawal request', '2026-04-02 01:35:01', '2026-04-02 01:35:01'),
(48, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 1000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-02 01:52:07', '2026-04-02 01:52:07'),
(49, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 1000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-02 01:52:33', '2026-04-02 01:52:33'),
(50, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 3768.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-02 01:52:56', '2026-04-02 01:52:56'),
(51, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 3434.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-02 02:13:05', '2026-04-02 02:13:05'),
(52, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 4354.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-02 02:14:02', '2026-04-02 02:14:02'),
(53, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 500.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-02 02:14:23', '2026-04-02 02:14:23'),
(54, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 690.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-02 02:16:19', '2026-04-02 02:16:19'),
(55, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 788.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-02 02:17:27', '2026-04-02 02:17:27'),
(56, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 89879.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-02 02:18:03', '2026-04-02 02:18:03'),
(57, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 550.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-02 02:18:52', '2026-04-02 02:18:52'),
(58, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 1000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-02 02:19:18', '2026-04-02 02:19:18'),
(59, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 1000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-02 02:19:55', '2026-04-02 02:19:55'),
(60, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 566.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-02 02:20:44', '2026-04-02 02:20:44'),
(61, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 600.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-02 02:21:25', '2026-04-02 02:21:25'),
(62, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 3000.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-02 02:21:39', '2026-04-02 02:21:39'),
(63, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 500.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-03 20:50:40', '2026-04-03 20:50:40'),
(64, 'withdrawal', 15, 47, 15, 15, 'withdrawal', 78978.00, 'KES', 'internal', 'pending', NULL, 'Seller withdrawal request', '2026-04-03 23:55:13', '2026-04-03 23:55:13'),
(65, 'withdrawal', 15, 47, 15, 15, 'withdrawal', 70000.00, 'KES', 'internal', 'pending', NULL, 'Seller withdrawal request', '2026-04-03 23:55:26', '2026-04-03 23:55:26'),
(66, 'withdrawal', 15, 47, 15, 15, 'withdrawal', 78797.00, 'KES', 'internal', 'pending', NULL, 'Seller withdrawal request', '2026-04-03 23:55:50', '2026-04-03 23:55:50'),
(67, 'withdrawal', 15, 47, 15, 15, 'withdrawal', 500.00, 'KES', 'internal', 'pending', NULL, 'Seller withdrawal request', '2026-04-03 23:56:02', '2026-04-03 23:56:02'),
(68, 'withdrawal', 15, 47, 15, 15, 'withdrawal', 500.00, 'KES', 'internal', 'pending', NULL, 'Seller withdrawal request', '2026-04-03 23:56:17', '2026-04-03 23:56:17'),
(69, '', 17, 53, 17, 17, 'withdrawal', 600.00, 'KES', 'internal', 'pending', NULL, 'Withdrawal request', '2026-04-05 05:12:52', '2026-04-05 05:12:52'),
(70, '', 17, 53, 17, 17, 'withdrawal', 600.00, 'KES', 'internal', 'pending', NULL, 'Withdrawal request', '2026-04-05 05:13:04', '2026-04-05 05:13:04'),
(71, 'seller_withdrawal', 17, 53, 17, 17, 'withdrawal', 8000.00, 'KES', 'internal', 'pending', NULL, 'Withdrawal request', '2026-04-05 05:19:40', '2026-04-05 05:19:40'),
(72, 'seller_withdrawal', 17, 53, 17, 17, 'withdrawal', 1000.00, 'KES', 'internal', 'pending', NULL, 'Withdrawal request', '2026-04-05 05:20:12', '2026-04-05 05:20:12'),
(73, 'seller_withdrawal', 17, 53, 17, 17, 'withdrawal', 500.00, 'KES', 'internal', 'pending', NULL, 'Withdrawal request', '2026-04-05 05:21:57', '2026-04-05 05:21:57'),
(74, 'seller_withdrawal', 17, 53, 17, 17, 'withdrawal', 600.00, 'KES', 'internal', 'pending', NULL, 'Withdrawal request', '2026-04-05 05:22:16', '2026-04-05 05:22:16'),
(75, 'seller_withdrawal', 17, 53, 17, 17, 'withdrawal', 700.00, 'KES', 'internal', 'pending', NULL, 'Withdrawal request', '2026-04-05 05:22:34', '2026-04-05 05:22:34'),
(76, 'commission', 48, 43, 48, 47, 'commission', 100.00, 'KES', 'internal', 'pending', NULL, 'Level 1 commission from agent 48', '2026-04-05 07:13:52', '2026-04-05 07:46:52'),
(77, 'commission', 48, 12, 48, 29, 'commission', 40.00, 'KES', 'internal', 'pending', NULL, 'Level 2 commission from agent 48', '2026-04-05 07:13:52', '2026-04-05 07:46:52'),
(78, 'commission', 51, 34, 51, 42, 'commission', 100.00, 'KES', 'internal', 'completed', NULL, 'Pending Level 1 commission from agent 51', '2026-04-05 08:08:07', '2026-04-05 08:08:43'),
(79, 'commission', 51, 20, 51, 31, 'commission', 40.00, 'KES', 'internal', 'completed', NULL, 'Pending Level 2 commission from agent 51', '2026-04-05 08:08:07', '2026-04-05 08:08:43'),
(80, 'commission', 51, 11, 51, 29, 'commission', 20.00, 'KES', 'internal', 'completed', NULL, 'Pending Level 3 commission from agent 51', '2026-04-05 08:08:07', '2026-04-05 08:08:43'),
(81, 'commission', 52, 56, 52, 51, 'commission', 100.00, 'KES', 'internal', 'completed', NULL, 'Pending Level 1 commission from agent 52', '2026-04-05 08:12:15', '2026-04-05 08:12:40'),
(82, 'commission', 52, 34, 52, 42, 'commission', 40.00, 'KES', 'internal', 'completed', NULL, 'Pending Level 2 commission from agent 52', '2026-04-05 08:12:15', '2026-04-05 08:12:40'),
(83, 'commission', 52, 20, 52, 31, 'commission', 20.00, 'KES', 'internal', 'completed', NULL, 'Pending Level 3 commission from agent 52', '2026-04-05 08:12:15', '2026-04-05 08:12:40'),
(84, 'commission', 53, 56, 53, 51, 'commission', 100.00, 'KES', 'internal', 'completed', NULL, 'Pending Level 1 commission from agent 53', '2026-04-05 08:15:52', '2026-04-05 08:17:12'),
(85, 'commission', 53, 34, 53, 42, 'commission', 40.00, 'KES', 'internal', 'completed', NULL, 'Pending Level 2 commission from agent 53', '2026-04-05 08:15:52', '2026-04-05 08:17:12'),
(86, 'commission', 53, 20, 53, 31, 'commission', 20.00, 'KES', 'internal', 'completed', NULL, 'Pending Level 3 commission from agent 53', '2026-04-05 08:15:52', '2026-04-05 08:17:12'),
(87, 'commission', 54, 60, 54, 53, 'commission', 100.00, 'KES', 'internal', 'completed', NULL, 'Pending Level 1 commission from agent 54', '2026-04-05 08:18:51', '2026-04-05 08:21:46'),
(88, 'commission', 54, 56, 54, 51, 'commission', 40.00, 'KES', 'internal', 'completed', NULL, 'Pending Level 2 commission from agent 54', '2026-04-05 08:18:51', '2026-04-05 08:21:46'),
(89, 'commission', 54, 34, 54, 42, 'commission', 20.00, 'KES', 'internal', 'completed', NULL, 'Pending Level 3 commission from agent 54', '2026-04-05 08:18:51', '2026-04-05 08:21:46'),
(90, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 500.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-05 18:21:16', '2026-04-05 18:21:16'),
(91, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 500.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-05 18:21:27', '2026-04-05 18:21:27'),
(92, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 771.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-05 18:21:41', '2026-04-05 18:21:41'),
(93, 'withdrawal', 29, 11, 29, 29, 'withdrawal', 500.00, 'KES', 'internal', 'pending', NULL, 'Sales wallet withdrawal request', '2026-04-05 18:21:54', '2026-04-05 18:21:54'),
(94, 'seller_withdrawal', 15, 48, 15, 15, 'withdrawal', 500.00, 'KES', 'internal', 'pending', NULL, 'Withdrawal request', '2026-04-05 18:37:51', '2026-04-05 18:37:51');

-- --------------------------------------------------------

--
-- Table structure for table `markethub_products`
--

CREATE TABLE `markethub_products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'KES',
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `download_file` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `image_hash` varchar(255) DEFAULT NULL,
  `image_phash` text DEFAULT NULL,
  `image_size_kb` int(11) DEFAULT NULL,
  `last_activated_at` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `markethub_products`
--

INSERT INTO `markethub_products` (`id`, `product_name`, `price`, `currency`, `description`, `category`, `image`, `download_file`, `is_active`, `created_at`, `image_hash`, `image_phash`, `image_size_kb`, `last_activated_at`) VALUES
(32, 'Jeep Executive Laptop BAG', 1300.00, 'KES', 'Ideal for work and business use. Lightweight and travel-friendly.', 'Fashions', 'uploads/company_products/prod_69ccf7a2e23ee6.77517088.webp', NULL, 1, '2026-04-01 10:46:59', '804627a9d7837cca20f6e3b92730dd15', '0101000011110000110111101110000011101000110011001100110010000100', 21, '2026-04-05 06:58:25'),
(34, 'Stanely MUG', 1300.00, 'KES', 'Ideal for travel and outdoor use and it\'s easy to clean.', 'Home Items', 'uploads/company_products/prod_69ccf8e16f04a2.67801133.webp', NULL, 0, '2026-04-01 10:52:17', '1da8ded9b14b02d393a6fca0c8a7c62a', '0101000011110000101010001010100010100000101011001100110010000100', 22, '2026-04-02 14:13:55'),
(30, '6 Litre Electric Pressure Cooker', 5200.00, 'KES', 'Energy efficient performance. Easy to clean design. Ideal for family meals', 'Home Items', 'uploads/company_products/prod_69c94ee0bbc083.85638684.webp', NULL, 1, '2026-03-29 16:10:09', '759957fb777f833d5a0b3f3f94370d38', '0101000011110000110010001100100011000000111001001100110010000100', 24, '2026-04-05 06:58:25');

-- --------------------------------------------------------

--
-- Table structure for table `mpesa_payments`
--

CREATE TABLE `mpesa_payments` (
  `payment_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `order_id` bigint(20) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `mpesa_receipt` varchar(50) DEFAULT NULL,
  `checkout_request_id` varchar(100) DEFAULT NULL,
  `merchant_request_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `confirmed_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_code` varchar(50) DEFAULT NULL,
  `buyer_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_code`, `buyer_id`, `total_amount`, `created_at`) VALUES
(278, 'ORD-20260404-44609', 50, 250.00, '2026-04-04 12:08:35'),
(279, 'ORD-20260404-69554', 50, 240.00, '2026-04-04 12:13:19'),
(280, 'ORD-20260404-05131', 39, 600.00, '2026-04-04 13:20:43'),
(281, 'ORD-20260405-61746', 39, 600.00, '2026-04-04 23:04:08'),
(282, 'ORD-20260405-89057', 39, 200.00, '2026-04-05 00:12:30'),
(283, 'ORD-20260405-30824', 39, 60.00, '2026-04-05 03:32:29');

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
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `order_status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipped_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `seller_id`, `quantity`, `price`, `subtotal`, `order_status`, `shipped_at`, `delivered_at`, `payment_status`) VALUES
(319, 278, 1, 17, 1, 250.00, 250.00, 'pending', NULL, NULL, 'paid'),
(320, 279, 10, 17, 2, 50.00, 100.00, 'pending', NULL, NULL, 'paid'),
(321, 279, 2, 17, 1, 60.00, 60.00, 'pending', NULL, NULL, 'paid'),
(322, 279, 4, 19, 2, 40.00, 80.00, 'pending', NULL, NULL, 'paid'),
(323, 280, 9, 15, 1, 600.00, 600.00, 'pending', NULL, NULL, 'paid'),
(324, 281, 9, 15, 1, 600.00, 600.00, 'pending', NULL, NULL, 'paid'),
(325, 282, 3, 17, 2, 100.00, 200.00, 'pending', NULL, NULL, 'paid'),
(326, 283, 2, 17, 1, 60.00, 60.00, 'pending', NULL, NULL, 'paid');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `transaction_id` varchar(150) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `productservicesrentals`
--

CREATE TABLE `productservicesrentals` (
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `image_path` varchar(255) NOT NULL,
  `image_hash` varchar(32) DEFAULT NULL,
  `image_width` int(11) DEFAULT NULL,
  `image_height` int(11) DEFAULT NULL,
  `image_size_kb` int(11) DEFAULT NULL,
  `image_format` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image_phash` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productservicesrentals`
--

INSERT INTO `productservicesrentals` (`product_id`, `user_id`, `product_name`, `category`, `price`, `stock_quantity`, `image_path`, `image_hash`, `image_width`, `image_height`, `image_size_kb`, `image_format`, `status`, `created_at`, `updated_at`, `image_phash`) VALUES
(1, 17, 'Crocks', 'Fashions', 250.00, 32, 'uploads/products/product_69ce09790bc7f3.91898276.webp', '5aa4d20c6335cde41c6a2c9aea32b4ba', 700, 700, 57, 'webp', 'active', '2026-02-25 21:50:12', '2026-04-04 12:08:35', '00010011000000110111000011101000'),
(2, 17, 'Juice', 'Food & Snacks', 60.00, 35, 'uploads/products/product_69ce08113fe1d8.66003342.webp', '306c060f642f661d508f4f9f86d4d364', 700, 519, 55, 'webp', 'active', '2026-02-25 22:52:53', '2026-04-05 03:32:29', '00000010000011000001100001110110'),
(3, 17, 'Githeri', 'Food & Snacks', 100.00, 15, 'uploads/products/product_69ce0643920929.02512567.webp', '3cc341a7c025694fc566ac521537ca37', 700, 700, 40, 'webp', 'active', '2026-02-25 22:54:35', '2026-04-05 00:12:30', '00001111001000110110000101110001'),
(4, 19, 'Passion Juice', 'Food & Snacks', 40.00, 21, 'uploads/products/product_69a017086d9a78.65234946.webp', '57cce22a22b1386a867d231a364f5109', 700, 700, 33, 'webp', 'active', '2026-02-26 09:48:56', '2026-04-04 12:13:19', NULL),
(5, 20, 'Matress', 'Home Items', 4500.00, 3, 'uploads/products/product_69a08decb192e1.62675128.webp', 'e24629049379675f3a1b4309bb699f4a', 700, 527, 27, 'webp', 'active', '2026-02-26 18:16:12', '2026-03-06 19:00:34', NULL),
(6, 15, 'Bicycle', 'Home Items', 4008.00, 3, 'uploads/products/product_69cdf7aee10672.32901299.webp', 'd0f5ffc4f53661cebbe35d9bdf3e30e3', 700, 393, 10, 'webp', 'active', '2026-03-02 14:14:36', '2026-04-04 05:18:55', '11110000111110000110000001110001'),
(8, 15, 'HP Elite Book G', 'Home Items', 64948.00, 5, 'uploads/products/product_69cdf34d930028.52520233.webp', '060abafb42a131b41a83ba7c94d7d944', 700, 525, 6, 'webp', 'active', '2026-03-09 23:13:50', '2026-04-04 07:41:34', '01110001110100101111000001101110'),
(9, 15, 'Passion Juice3', 'Food & Snacks', 600.00, 45, 'uploads/products/product_69cd3cf11d9a38.85948077.webp', 'b308caf21abeb85d8acc179d77a28ad5', 700, 393, 40, 'webp', 'active', '2026-04-01 15:42:41', '2026-04-04 23:04:08', '11001101011010100110101101100110'),
(10, 17, 'Soda', 'Food & Snacks', 50.00, 68, 'uploads/products/product_69ce0fb19c76a6.38926367.webp', '7c393aaee8a7aec54e23876742907bc5', 700, 467, 29, 'webp', 'active', '2026-04-02 06:28:43', '2026-04-04 12:13:19', '01000001011011010110000101101000');

-- --------------------------------------------------------

--
-- Table structure for table `seller_ratings`
--

CREATE TABLE `seller_ratings` (
  `rating_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `account_type` enum('buyer','seller','sales_agent','administrator','property_owner') NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `status` enum('active','suspended') DEFAULT 'active',
  `country` varchar(100) NOT NULL DEFAULT 'Kenya',
  `county` varchar(100) NOT NULL,
  `ward` varchar(100) NOT NULL,
  `address` varchar(150) DEFAULT NULL,
  `business_name` varchar(50) DEFAULT NULL,
  `business_model` varchar(50) DEFAULT NULL,
  `business_type` varchar(50) DEFAULT NULL,
  `market_scope` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `agency_code` varchar(50) DEFAULT NULL,
  `referred_by` int(11) DEFAULT NULL,
  `agent_activated_at` datetime DEFAULT NULL,
  `subscription_expires_at` datetime DEFAULT NULL,
  `must_change_password` tinyint(1) DEFAULT 0,
  `economic_period_count` int(11) DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `username`, `email`, `phone`, `profile_image`, `bio`, `password`, `account_type`, `is_verified`, `status`, `country`, `county`, `ward`, `address`, `business_name`, `business_model`, `business_type`, `market_scope`, `created_at`, `updated_at`, `agency_code`, `referred_by`, `agent_activated_at`, `subscription_expires_at`, `must_change_password`, `economic_period_count`, `is_system`) VALUES
(15, 'Pst Kip Frie', 'seller_001', 'c2VsbGVyXzAwMUBnbWFpbC5jb20=', 'KzI1NDc3MzAyOTQ0MA==', NULL, 'We deliver', '$2y$10$CqCArt9rG4O278HT4JRwz.OT8yNHssp6D9Wlvg50VHBBnVRrkGobi', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni', 'Misufii', 'Market Hub', 'products', 'shop', 'Local', '2026-02-25 14:18:40', '2026-03-24 21:31:06', NULL, NULL, NULL, NULL, 0, NULL, 0),
(38, 'STEPHEN OFTHEBIBLE', 'agent_011', 'YWdlbnRfMDExQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxMQ==', NULL, NULL, '$2y$10$Ll4PIS0M0545BnfIf81fce01fNzXQjYd9TlmJbjzUXWcPK8/JuIou', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-24 08:09:58', '2026-03-24 08:09:58', 'EEC4D699', 33, NULL, '2026-04-23 11:28:39', 1, 1, 0),
(17, 'EMMANUEL WERANGAI', 'seller_002', 'c2VsbGVyXzAwMkBnbWFpbC5jb20=', 'KzI1NDc1OTU3ODYzMg==', NULL, 'Here', '$2y$10$VABFqJUUCuGnx3M5NN6sfuDJ640MPia5z5mofYZv0.niTtM8u6cva', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni', 'Kilifi', 'Main Canteen', 'products', 'canteen', 'Local', '2026-02-25 14:29:15', '2026-04-05 00:11:10', NULL, NULL, NULL, NULL, 0, NULL, 0),
(18, 'EMMANUEL WERANGAI', 'seller_003', 'c2VsbGVyXzAwM0BnbWFpbC5jb20=', 'KzI1NDc1OTU3ODYzMw==', NULL, NULL, '$2y$10$kdTNspptrvARewNR3/9PQ.0lx4fwWXrF33TPqC7.VRsa2dXMW1sfG', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', 'BETRADES MANAGEMENT', NULL, 'supermarket', 'Local', '2026-02-25 14:31:11', '2026-02-25 14:31:11', NULL, NULL, NULL, NULL, 0, NULL, 0),
(19, 'EMMANUEL WERANGAI', 'seller_004', 'c2VsbGVyXzAwNEBnbWFsaS5jb20=', 'KzI1NDc1OTU3ODYzNQ==', NULL, NULL, '$2y$10$OR16Ab7u7PiToTnqRTGzCeNrlBf4E4Cyr.eAyoGYnLmyN2foUtvK2', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Gede', 'Fred Pharmacy', 'products', 'supermarket', 'local', '2026-02-26 09:26:57', '2026-02-26 09:26:57', NULL, NULL, NULL, NULL, 0, NULL, 0),
(46, 'Shabi ghar', 'agent_015', 'YWdlbnRfMDE1QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxNQ==', NULL, NULL, '$2y$10$W/.QI8FfMZPgBmfptdibe.VfxZASY3Qw61Q9XWhlLvicbyyqlB3/G', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Moiko', NULL, NULL, NULL, NULL, '2026-04-01 05:45:47', '2026-04-01 05:45:47', '5236F799', 42, NULL, NULL, 0, 0, 0),
(41, 'sHAMRADI IDHKNA', 'agent_013', 'YWdlbnRfMDEzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxMw==', NULL, NULL, '$2y$10$kO6zA8LwzGVv/c/FZ83f4.CQiEMiClHHa7SNUhvcrKeTWREzXGwRe', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Sarang\' dk', NULL, NULL, NULL, NULL, '2026-03-25 22:33:02', '2026-03-25 22:33:02', 'A6DA3D49', 32, NULL, '2026-04-25 01:33:12', 0, 1, 0),
(21, 'Emmanuel Werangai', 'makethubadmin_254', 'ZW1tYW51ZWx0aW5kaTIzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzNw==', NULL, 'System account for platform operations', '$2y$10$A0zeQcrmvrrk.uP8pSpwFeUfQJ3qFc9bVJentpZ4DEUwvVjb9QMZS', 'administrator', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Naivas', '', '', '', '', '2026-03-04 17:27:25', '2026-04-01 07:11:17', 'SYSTEM001', NULL, '2026-04-01 10:11:17', NULL, 0, 0, 1),
(29, 'EMMANUEL WERANGAI', 'njeri254', 'YWdlbnRfMDAxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwMQ==', NULL, 'ygyug', '$2y$10$EH6ofE.Xf/06zE4QnmmWQOA5Wfl63t9TH0pj47Atn0CsZCwnZZLyW', 'sales_agent', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-24 01:36:01', '2026-04-05 16:38:00', 'FEC011A5', NULL, NULL, '2026-04-23 04:39:24', 0, 1, 0),
(23, 'EMMANUEL TINDI', 'property_owner_001', 'cHJvcGVydHlfb3duZXJfMDAxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODY4OQ==', NULL, NULL, '$2y$10$ry31rKkNzYi9LIIpr8Ajp.7BkSj8BbovXXAbEO/iNxTff14fX/Ery', 'property_owner', 0, 'suspended', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Naivas', '', '', '', '', '2026-03-04 17:41:30', '2026-03-21 22:09:22', NULL, NULL, NULL, NULL, 0, NULL, 0),
(30, 'sam hiddgd', 'agent_002', 'YWdlbnRfMDAyQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwMw==', NULL, NULL, '$2y$10$L8h6XLjPZAq/FI/y/nYTiuY6pr73V.PSrq6daqb00xAGs653HL3Ra', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-24 01:42:06', '2026-03-24 01:42:06', '8EFB950D', 29, NULL, '2026-04-23 04:42:31', 0, 1, 0),
(31, 'Maket Hub', 'agent_003', 'YWdlbnRfMDAzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwNA==', NULL, NULL, '$2y$10$h7KtsjFR/w3TwGFtWUAzCOuSqHszv9tUTxB6KY9op1T.tThxb6NSu', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Moiko', NULL, NULL, NULL, NULL, '2026-03-24 01:59:12', '2026-03-24 01:59:12', '6D76B306', 29, NULL, '2026-04-23 05:18:10', 0, 1, 0),
(32, 'Sha iskkoe', 'agent_004', 'YWdlbnRfMDA0QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwNQ==', NULL, 'I am here', '$2y$10$KmxL5.BNgKJyGRkecHeQ1OaYAQ6p1S2xwLuKrArPBBeayiAqcQc9.', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-24 02:02:27', '2026-03-24 20:47:25', '14C1242F', 31, NULL, '2026-04-23 05:02:55', 0, 1, 0),
(33, 'vesa bledr', 'agent_006', 'YWdlbnRfMDA2QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwNg==', NULL, NULL, '$2y$10$cRX61aoXJZyhVhpHB0pvjOBbWbwPuY.J31T7amQ8LarazliknUlBK', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-24 02:06:43', '2026-03-24 02:06:43', 'D52A7567', 32, NULL, '2026-04-23 05:19:32', 0, 1, 0),
(34, 'agent Bunda', 'agent_007', 'YWdlbnRfMDA3QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwNw==', NULL, NULL, '$2y$10$OdFGFa4PykQoC73syulnEuQdor.ddvnNC5ir0JShcmYCweiCQenC.', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'kamois', NULL, NULL, NULL, NULL, '2026-03-24 02:11:06', '2026-03-24 02:11:06', '7F9D1742', 33, NULL, '2026-04-23 10:13:11', 0, 1, 0),
(35, 'sham hashi', 'agent_008', 'YWdlbnRfMDA4QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAwOA==', NULL, NULL, '$2y$10$hIFRQCX1t3aHXqmF91BmPu5/697RL6h19L3vjvAuEV6lNYPoqQGuu', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-03-24 02:16:39', '2026-03-24 02:16:39', '6676A6FC', 29, NULL, '2026-04-23 05:17:48', 1, 1, 0),
(39, 'Shanig ahsila', 'Buyer_001', 'YnV5ZXJfMDAxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3MTAwMQ==', NULL, 'Coming soon...', '$2y$10$VHIyn6QSW8k0hZTovIn7.esSC/FM6/rGrjPnqokftNCx0W1tqPI0a', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-24 20:50:39', '2026-03-25 06:14:53', NULL, NULL, NULL, NULL, 0, NULL, 0),
(42, 'Gaaffr mdsa', 'agent_014', 'YWdlbnRfMDE0QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxNA==', NULL, NULL, '$2y$10$IU2DqtE0RaLWsy5OU9uUk./Lm0lKU/0sZGEtLVsIIhCo6Wj36E3y2', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-03-25 23:40:45', '2026-03-25 23:40:45', 'F015A0BB', 31, NULL, '2026-04-25 02:41:13', 0, 1, 0),
(48, 'Bisih Jhka', 'agent_017', 'YWdlbnRfMDE3QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxNw==', NULL, NULL, '$2y$10$h/VGvMmkRpMtW099kCldIeS/gWryL4ol.vtouQOd.33V/xBt6B02u', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Tewani', NULL, NULL, NULL, NULL, '2026-04-01 06:28:12', '2026-04-01 06:28:12', '30BC5ABE', 47, NULL, '2026-05-05 07:13:52', 0, 1, 0),
(47, 'Fahsd dhkhda', 'agent_016', 'YWdlbnRfMDE2QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxNg==', NULL, NULL, '$2y$10$1TH2S2meb2fRGLGt3xNfL.AMi6yNDrChhRPju/4BdrIFN8xzPGBiG', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', '', '', '', '', '2026-04-01 05:46:43', '2026-04-01 05:46:43', '707C70DA', 29, NULL, '2026-05-01 08:55:02', 1, 1, 0),
(49, 'Khaid Sgalah', 'seller_010', 'c2VsbGVyXzAxMEBnbWFpbC5jb20=', 'KzI1NDc1OTU3ODAxMA==', NULL, NULL, '$2y$10$4P/ReMcEqfIwboQoTKaiLOKiPQFQhhpNRdE5InsRow3ZRFOywFpVe', 'seller', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Cherowamaye', 'Kishor Shop', 'products', 'shop', 'Local', '2026-04-04 05:55:01', '2026-04-04 05:55:01', NULL, NULL, NULL, NULL, 0, NULL, 0),
(50, 'EMMANUEL TINDI', 'buyer_002', 'YnV5ZXJfMDAyQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAxMg==', NULL, NULL, '$2y$10$2WpUst.v10IkucmlWZ881uVy7Egcq0RZNzBGF6GcXRY6Rt4X3G6zW', 'buyer', 0, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Shanzu', NULL, NULL, NULL, NULL, '2026-04-04 05:59:36', '2026-04-04 05:59:36', NULL, NULL, NULL, NULL, 0, NULL, 0),
(51, 'EMMANUEL WERANGAI', 'agent_020', 'YWdlbnRfMDIwQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzMA==', NULL, NULL, '$2y$10$GKbYcnRiG1qk44g39ixzZe0tXjxkawxE3l05/aPxgQJ5i3wHzFVfG', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-04-05 05:08:07', '2026-04-05 05:08:07', 'E8E4F745', 42, NULL, '2026-05-05 08:08:43', 0, 1, 0),
(52, 'agent Bunda', 'agent_021', 'YWdlbnRfMDIxQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAyMQ==', NULL, NULL, '$2y$10$2R5.Qinln2nad0kWbfo1suPzDCkQQ3lpS0nQDXmAYesSrIZl7TowG', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-04-05 05:12:15', '2026-04-05 05:12:15', 'AF2FD5DF', 51, NULL, '2026-05-05 08:12:40', 0, 1, 0),
(53, 'Blessed Emmanuel', 'agent_022', 'YWdlbnRfMDIyQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAyMg==', NULL, NULL, '$2y$10$K6z/adPGJyfW0yozDLSJy.DRyp3bzHVWoLvoi8KSLFSuCtisxvl/6', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-04-05 05:15:52', '2026-04-05 05:15:52', '0FE5BEB4', 51, NULL, '2026-05-05 08:17:12', 0, 1, 0),
(54, 'EMMANUEL WERANGAI', 'agent_023', 'YWdlbnRfMDIzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODAyMw==', NULL, NULL, '$2y$10$BA6mwT5Im2PK.xX.CJR66O7boUndoFqBiFzJYohTbU0xs60JiPCYq', 'sales_agent', 1, 'active', 'Kenya', 'Kilifi', 'Sokoni Ward', 'Kilifi', NULL, NULL, NULL, NULL, '2026-04-05 05:18:51', '2026-04-05 05:18:51', '2177B20B', 53, NULL, '2026-05-05 08:21:46', 0, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_cart`
--

CREATE TABLE `user_cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_cart`
--

INSERT INTO `user_cart` (`cart_id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(57, 29, 1, 1, '2026-03-31 07:24:51', '2026-03-31 07:24:51');

-- --------------------------------------------------------

--
-- Table structure for table `user_followers`
--

CREATE TABLE `user_followers` (
  `follower_id` int(11) NOT NULL,
  `followed_id` int(11) NOT NULL,
  `followed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_followers`
--

INSERT INTO `user_followers` (`follower_id`, `followed_id`, `followed_at`) VALUES
(14, 15, '2026-03-07 17:26:55'),
(14, 17, '2026-03-07 17:27:16'),
(14, 18, '2026-02-25 20:26:17'),
(14, 19, '2026-02-26 12:54:30'),
(16, 15, '2026-02-26 13:04:54'),
(16, 17, '2026-02-25 21:49:03'),
(16, 18, '2026-03-06 19:02:14'),
(16, 19, '2026-03-06 19:02:16'),
(16, 20, '2026-02-26 18:17:46'),
(20, 17, '2026-02-26 18:25:49'),
(39, 15, '2026-04-03 16:19:29'),
(39, 17, '2026-04-02 12:32:25'),
(50, 17, '2026-04-04 07:59:31'),
(50, 18, '2026-04-04 08:19:46'),
(50, 19, '2026-04-04 12:11:49'),
(50, 49, '2026-04-04 07:59:42');

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `wallet_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `wallet_type` enum('sales','agency','sales_agent','property_owner','administrator','buyer','seller') NOT NULL,
  `balance` decimal(12,2) DEFAULT 0.00,
  `total_transacted` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`wallet_id`, `user_id`, `wallet_type`, `balance`, `total_transacted`, `created_at`, `updated_at`) VALUES
(20, 31, 'sales', 0.00, 0.00, '2026-03-24 02:18:10', '2026-04-01 20:22:07'),
(19, 35, 'agency', 0.00, 0.00, '2026-03-24 02:17:48', '2026-03-24 02:17:48'),
(18, 35, 'sales', 0.00, 0.00, '2026-03-24 02:17:48', '2026-03-24 02:17:48'),
(17, 31, 'agency', 120.00, 580.00, '2026-03-24 02:02:55', '2026-04-05 05:17:12'),
(16, 32, 'agency', 380.00, 380.00, '2026-03-24 02:02:55', '2026-03-25 22:33:12'),
(15, 32, 'sales', 0.00, 0.00, '2026-03-24 02:02:55', '2026-03-24 02:02:55'),
(14, 30, 'agency', 0.00, 0.00, '2026-03-24 01:42:31', '2026-03-24 01:42:31'),
(13, 30, 'sales', 0.00, 0.00, '2026-03-24 01:42:31', '2026-03-24 01:42:31'),
(12, 29, 'agency', 1998060.00, 800.00, '2026-03-24 01:39:24', '2026-04-05 05:08:43'),
(11, 29, 'sales', 1404500.00, 0.00, '2026-03-24 01:39:24', '2026-04-05 15:21:54'),
(21, 33, 'sales', 0.00, 0.00, '2026-03-24 02:19:32', '2026-03-24 02:19:32'),
(22, 33, 'agency', 200.00, 200.00, '2026-03-24 02:19:32', '2026-03-24 08:28:39'),
(23, 34, 'sales', 0.00, 0.00, '2026-03-24 07:13:11', '2026-03-24 07:13:11'),
(24, 34, 'agency', 0.00, 0.00, '2026-03-24 07:13:11', '2026-03-24 07:13:11'),
(25, 38, 'sales', 0.00, 0.00, '2026-03-24 08:28:39', '2026-03-24 08:28:39'),
(26, 38, 'agency', 0.00, 0.00, '2026-03-24 08:28:39', '2026-03-24 08:28:39'),
(27, 39, 'buyer', 0.00, 0.00, '2026-03-24 20:50:39', '2026-03-24 20:50:39'),
(28, 37, 'sales', 0.00, 0.00, '2026-03-25 18:16:55', '2026-03-25 18:16:55'),
(29, 37, 'agency', 0.00, 0.00, '2026-03-25 18:16:55', '2026-03-25 18:16:55'),
(30, 40, 'sales', 0.00, 0.00, '2026-03-25 22:28:43', '2026-03-25 22:28:43'),
(31, 40, 'agency', 0.00, 0.00, '2026-03-25 22:28:43', '2026-03-25 22:28:43'),
(32, 41, 'sales', 0.00, 0.00, '2026-03-25 22:33:12', '2026-03-25 22:33:12'),
(33, 41, 'agency', 0.00, 0.00, '2026-03-25 22:33:12', '2026-03-25 22:33:12'),
(34, 42, 'sales', 0.00, 0.00, '2026-03-25 23:41:13', '2026-03-25 23:41:13'),
(35, 42, 'agency', 300.00, 300.00, '2026-03-25 23:41:13', '2026-04-05 05:21:46'),
(36, 43, 'sales', 0.00, 0.00, '2026-03-25 23:53:21', '2026-03-25 23:53:21'),
(37, 43, 'agency', 0.00, 0.00, '2026-03-25 23:53:21', '2026-03-25 23:53:21'),
(38, 44, 'sales', 0.00, 0.00, '2026-03-26 19:21:51', '2026-03-26 19:21:51'),
(39, 44, 'agency', 100.00, 100.00, '2026-03-26 19:21:51', '2026-03-30 21:30:04'),
(40, 45, 'sales', 0.00, 0.00, '2026-03-30 21:30:04', '2026-03-30 21:30:04'),
(41, 45, 'agency', 0.00, 0.00, '2026-03-30 21:30:04', '2026-03-30 21:30:04'),
(42, 47, 'sales', 0.00, 0.00, '2026-04-01 05:55:02', '2026-04-01 05:55:02'),
(43, 47, 'agency', 100.00, 100.00, '2026-04-01 05:55:02', '2026-04-05 04:13:52'),
(47, 15, 'sales', -228775.00, 0.00, '2026-04-03 20:55:06', '2026-04-03 20:56:17'),
(46, 21, 'administrator', 0.00, 0.00, '2026-04-01 07:15:44', '2026-04-01 07:15:44'),
(48, 15, 'seller', 200.00, 1200.00, '2026-04-03 21:13:25', '2026-04-05 15:37:51'),
(49, 49, 'seller', 0.00, 0.00, '2026-04-04 05:55:01', '2026-04-04 05:55:01'),
(50, 50, 'buyer', 0.00, 0.00, '2026-04-04 05:59:36', '2026-04-04 05:59:36'),
(53, 17, 'seller', 7460.00, 670.00, '2026-04-04 12:07:27', '2026-04-05 03:32:29'),
(52, 19, 'seller', 200.00, 200.00, '2026-04-04 08:18:16', '2026-04-04 12:13:19'),
(54, 48, 'sales', 0.00, 0.00, '2026-04-05 04:13:52', '2026-04-05 04:13:52'),
(55, 48, 'agency', 0.00, 0.00, '2026-04-05 04:13:52', '2026-04-05 04:13:52'),
(56, 51, 'sales', 0.00, 0.00, '2026-04-05 05:08:43', '2026-04-05 05:08:43'),
(57, 51, 'agency', 240.00, 240.00, '2026-04-05 05:08:43', '2026-04-05 05:21:46'),
(58, 52, 'sales', 0.00, 0.00, '2026-04-05 05:12:40', '2026-04-05 05:12:40'),
(59, 52, 'agency', 0.00, 0.00, '2026-04-05 05:12:40', '2026-04-05 05:12:40'),
(60, 53, 'sales', 0.00, 0.00, '2026-04-05 05:17:12', '2026-04-05 05:17:12'),
(61, 53, 'agency', 100.00, 100.00, '2026-04-05 05:17:12', '2026-04-05 05:21:46'),
(62, 54, 'sales', 0.00, 0.00, '2026-04-05 05:21:46', '2026-04-05 05:21:46'),
(63, 54, 'agency', 0.00, 0.00, '2026-04-05 05:21:46', '2026-04-05 05:21:46');

-- --------------------------------------------------------

--
-- Table structure for table `withdrawals`
--

CREATE TABLE `withdrawals` (
  `withdrawal_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `wallet_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'KES',
  `fee` decimal(12,2) DEFAULT 0.00,
  `net_amount` decimal(12,2) NOT NULL,
  `method` enum('mpesa','bank','paypal','crypto') NOT NULL,
  `account_name` varchar(150) NOT NULL,
  `account_number` varchar(100) NOT NULL,
  `status` enum('pending','processing','completed','failed','reversed') DEFAULT 'pending',
  `transaction_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference_code` varchar(100) DEFAULT NULL,
  `provider_reference` varchar(150) DEFAULT NULL,
  `requested_at` datetime DEFAULT current_timestamp(),
  `processed_at` datetime DEFAULT NULL,
  `failure_reason` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `withdrawals`
--

INSERT INTO `withdrawals` (`withdrawal_id`, `user_id`, `wallet_id`, `amount`, `currency`, `fee`, `net_amount`, `method`, `account_name`, `account_number`, `status`, `transaction_id`, `reference_code`, `provider_reference`, `requested_at`, `processed_at`, `failure_reason`, `created_at`, `updated_at`) VALUES
(1, 29, 12, 41.00, 'KES', 40.00, 1.00, 'mpesa', '', '', 'pending', 0, NULL, NULL, '2026-04-01 15:47:31', NULL, NULL, '2026-04-01 15:47:31', '2026-04-01 15:47:31'),
(2, 29, 12, 41.00, 'KES', 40.00, 1.00, 'mpesa', '', '', 'pending', 0, NULL, NULL, '2026-04-01 15:49:44', NULL, NULL, '2026-04-01 15:49:44', '2026-04-01 15:49:44'),
(3, 29, 12, 1.00, 'KES', 0.00, 1.00, 'mpesa', '', '', 'pending', 0, NULL, NULL, '2026-04-01 15:57:42', NULL, NULL, '2026-04-01 15:57:42', '2026-04-01 15:57:42'),
(4, 29, 12, 1.00, 'KES', 0.00, 1.00, 'mpesa', '', '', 'pending', 0, NULL, NULL, '2026-04-01 15:58:27', NULL, NULL, '2026-04-01 15:58:27', '2026-04-01 15:58:27'),
(5, 29, 12, 1.00, 'KES', 0.00, 1.00, 'mpesa', '', '', 'pending', 0, NULL, NULL, '2026-04-01 15:58:41', NULL, NULL, '2026-04-01 15:58:41', '2026-04-01 15:58:41'),
(6, 29, 12, 1.00, 'KES', 0.00, 1.00, 'mpesa', '', '', 'pending', 0, NULL, NULL, '2026-04-01 16:07:30', NULL, NULL, '2026-04-01 16:07:30', '2026-04-01 16:07:30'),
(7, 29, 12, 1.00, 'KES', 0.00, 1.00, 'mpesa', '', '', 'pending', 0, NULL, NULL, '2026-04-01 16:08:15', NULL, NULL, '2026-04-01 16:08:15', '2026-04-01 16:08:15'),
(8, 29, 12, 500.00, 'KES', 0.00, 500.00, 'mpesa', '', '', 'pending', 5, NULL, NULL, '2026-04-01 16:53:48', NULL, NULL, '2026-04-01 16:53:48', '2026-04-01 16:53:48'),
(9, 29, 12, 400.00, 'KES', 0.00, 400.00, 'mpesa', '', '', 'pending', 6, NULL, NULL, '2026-04-01 17:02:51', NULL, NULL, '2026-04-01 17:02:51', '2026-04-01 17:02:51'),
(10, 29, 12, 600.00, 'KES', 0.00, 600.00, 'mpesa', '', '', 'pending', 7, NULL, NULL, '2026-04-01 17:03:06', NULL, NULL, '2026-04-01 17:03:06', '2026-04-01 17:03:06'),
(11, 29, 12, 600.00, 'KES', 0.00, 600.00, 'mpesa', '', '', 'pending', 8, NULL, NULL, '2026-04-01 17:03:39', NULL, NULL, '2026-04-01 17:03:39', '2026-04-01 17:03:39'),
(12, 29, 12, 600.00, 'KES', 0.00, 600.00, 'mpesa', '', '', 'pending', 9, NULL, NULL, '2026-04-01 17:03:54', NULL, NULL, '2026-04-01 17:03:54', '2026-04-01 17:03:54'),
(13, 29, 12, 600.00, 'KES', 0.00, 600.00, 'mpesa', '', '', 'pending', 10, NULL, NULL, '2026-04-01 17:04:02', NULL, NULL, '2026-04-01 17:04:02', '2026-04-01 17:04:02'),
(14, 29, 12, 50.00, 'KES', 0.00, 50.00, 'mpesa', '', '', 'pending', 11, NULL, NULL, '2026-04-01 17:05:17', NULL, NULL, '2026-04-01 17:05:17', '2026-04-01 17:05:17'),
(15, 29, 12, 6.00, 'KES', 0.00, 6.00, 'mpesa', '', '', 'pending', 12, NULL, NULL, '2026-04-01 17:05:32', NULL, NULL, '2026-04-01 17:05:32', '2026-04-01 17:05:32'),
(16, 29, 12, 4.00, 'KES', 0.00, 4.00, 'mpesa', '', '', 'pending', 13, NULL, NULL, '2026-04-01 17:05:50', NULL, NULL, '2026-04-01 17:05:50', '2026-04-01 17:05:50'),
(17, 29, 12, 4.00, 'KES', 0.00, 4.00, 'mpesa', '', '', 'pending', 14, NULL, NULL, '2026-04-01 17:12:08', NULL, NULL, '2026-04-01 17:12:08', '2026-04-01 17:12:08'),
(18, 29, 12, 5.00, 'KES', 0.00, 5.00, 'mpesa', '', '', 'pending', 15, NULL, NULL, '2026-04-01 17:14:32', NULL, NULL, '2026-04-01 17:14:32', '2026-04-01 17:14:32'),
(19, 29, 12, 5.00, 'KES', 0.00, 5.00, 'mpesa', '', '', 'pending', 16, NULL, NULL, '2026-04-01 17:15:02', NULL, NULL, '2026-04-01 17:15:02', '2026-04-01 17:15:02'),
(20, 29, 12, 10.00, 'KES', 0.00, 10.00, 'mpesa', '', '', 'pending', 17, NULL, NULL, '2026-04-01 17:15:41', NULL, NULL, '2026-04-01 17:15:41', '2026-04-01 17:15:41'),
(21, 29, 11, 500.00, 'KES', 0.00, 500.00, 'mpesa', '', '', 'pending', 18, NULL, NULL, '2026-04-01 17:16:23', NULL, NULL, '2026-04-01 17:16:23', '2026-04-01 17:16:23'),
(22, 29, 11, 3000.00, 'KES', 40.00, 2960.00, 'mpesa', '', '', 'pending', 19, NULL, NULL, '2026-04-01 17:29:01', NULL, NULL, '2026-04-01 17:29:01', '2026-04-01 17:29:01'),
(23, 29, 11, 10000.00, 'KES', 40.00, 9960.00, 'mpesa', '', '', 'pending', 20, NULL, NULL, '2026-04-01 17:29:24', NULL, NULL, '2026-04-01 17:29:24', '2026-04-01 17:29:24'),
(24, 29, 11, 3000.00, 'KES', 40.00, 2960.00, 'mpesa', '', '', 'pending', 21, NULL, NULL, '2026-04-01 17:39:37', NULL, NULL, '2026-04-01 17:39:37', '2026-04-01 17:39:37'),
(25, 29, 11, 90000.00, 'KES', 360.00, 89640.00, 'mpesa', '', '', 'pending', 22, NULL, NULL, '2026-04-01 17:39:50', NULL, NULL, '2026-04-01 17:39:50', '2026-04-01 17:39:50'),
(26, 29, 11, 3000.00, 'KES', 56.00, 2944.00, 'mpesa', '', '', 'pending', 23, NULL, NULL, '2026-04-01 17:50:19', NULL, NULL, '2026-04-01 17:50:19', '2026-04-01 17:50:19'),
(27, 29, 11, 10000.00, 'KES', 70.00, 9930.00, 'mpesa', '', '', 'pending', 24, NULL, NULL, '2026-04-01 17:50:46', NULL, NULL, '2026-04-01 17:50:46', '2026-04-01 17:50:46'),
(28, 29, 11, 100000.00, 'KES', 300.00, 99700.00, 'mpesa', '', '', 'pending', 25, NULL, NULL, '2026-04-01 17:51:19', NULL, NULL, '2026-04-01 17:51:19', '2026-04-01 17:51:19'),
(29, 29, 11, 100000.00, 'KES', 300.00, 99700.00, 'mpesa', '', '', 'pending', 26, NULL, NULL, '2026-04-01 17:52:02', NULL, NULL, '2026-04-01 17:52:02', '2026-04-01 17:52:02'),
(30, 29, 11, 7000.00, 'KES', 64.00, 6936.00, 'mpesa', '', '', 'pending', 27, NULL, NULL, '2026-04-01 17:53:38', NULL, NULL, '2026-04-01 17:53:38', '2026-04-01 17:53:38'),
(31, 29, 11, 15000.00, 'KES', 122.50, 14877.50, 'mpesa', '', '', 'pending', 28, NULL, NULL, '2026-04-01 17:54:12', NULL, NULL, '2026-04-01 17:54:12', '2026-04-01 17:54:12'),
(32, 29, 11, 20000.00, 'KES', 130.00, 19870.00, 'mpesa', '', '', 'pending', 29, NULL, NULL, '2026-04-01 17:55:18', NULL, NULL, '2026-04-01 17:55:18', '2026-04-01 17:55:18'),
(33, 29, 11, 35000.00, 'KES', 152.50, 34847.50, 'mpesa', '', '', 'pending', 30, NULL, NULL, '2026-04-01 17:56:10', NULL, NULL, '2026-04-01 17:56:10', '2026-04-01 17:56:10'),
(34, 29, 11, 49000.00, 'KES', 173.50, 48826.50, 'mpesa', '', '', 'pending', 31, NULL, NULL, '2026-04-01 17:57:23', NULL, NULL, '2026-04-01 17:57:23', '2026-04-01 17:57:23'),
(35, 29, 11, 2600.00, 'KES', 55.20, 2544.80, 'mpesa', '', '', 'pending', 32, NULL, NULL, '2026-04-01 18:06:01', NULL, NULL, '2026-04-01 18:06:01', '2026-04-01 18:06:01'),
(37, 29, 11, 5000.00, 'KES', 60.00, 4940.00, 'mpesa', '', '', 'pending', 34, NULL, NULL, '2026-04-01 18:10:01', NULL, NULL, '2026-04-01 18:10:01', '2026-04-01 18:10:01'),
(38, 29, 11, 5000.00, 'KES', 60.00, 4940.00, 'mpesa', '', '', 'pending', 35, NULL, NULL, '2026-04-01 18:12:50', NULL, NULL, '2026-04-01 18:12:50', '2026-04-01 18:12:50'),
(39, 29, 11, 5000.00, 'KES', 60.00, 4940.00, 'mpesa', '', '', 'pending', 36, NULL, NULL, '2026-04-01 18:13:07', NULL, NULL, '2026-04-01 18:13:07', '2026-04-01 18:13:07'),
(40, 29, 11, 5000.00, 'KES', 60.00, 4940.00, 'mpesa', '', '', 'pending', 37, NULL, NULL, '2026-04-01 18:14:04', NULL, NULL, '2026-04-01 18:14:04', '2026-04-01 18:14:04'),
(41, 29, 11, 500.00, 'KES', 40.00, 460.00, 'mpesa', '', '', 'pending', 38, NULL, NULL, '2026-04-01 18:14:43', NULL, NULL, '2026-04-01 18:14:43', '2026-04-01 18:14:43'),
(42, 29, 11, 500.00, 'KES', 40.00, 460.00, 'mpesa', '', '', 'pending', 39, NULL, NULL, '2026-04-01 18:15:01', NULL, NULL, '2026-04-01 18:15:01', '2026-04-01 18:15:01'),
(43, 29, 11, 5000.00, 'KES', 60.00, 4940.00, 'mpesa', '', '', 'pending', 40, NULL, NULL, '2026-04-01 18:19:30', NULL, NULL, '2026-04-01 18:19:30', '2026-04-01 18:19:30'),
(44, 31, 20, 500.00, 'KES', 40.00, 460.00, 'mpesa', '', '', 'pending', 45, NULL, NULL, '2026-04-01 23:22:07', NULL, NULL, '2026-04-01 23:22:07', '2026-04-01 23:22:07'),
(45, 31, 17, 460.00, 'KES', 40.00, 420.00, 'mpesa', '', '', 'pending', 46, NULL, NULL, '2026-04-01 23:48:28', NULL, NULL, '2026-04-01 23:48:28', '2026-04-01 23:48:28'),
(46, 29, 12, 2000.00, 'KES', 54.00, 1946.00, 'mpesa', '', '', 'pending', 47, NULL, NULL, '2026-04-02 01:35:01', NULL, NULL, '2026-04-02 01:35:01', '2026-04-02 01:35:01'),
(47, 29, 11, 1000.00, 'KES', 40.00, 960.00, 'mpesa', '', '', 'pending', 48, NULL, NULL, '2026-04-02 01:52:07', NULL, NULL, '2026-04-02 01:52:07', '2026-04-02 01:52:07'),
(48, 29, 11, 1000.00, 'KES', 40.00, 960.00, 'mpesa', '', '', 'pending', 49, NULL, NULL, '2026-04-02 01:52:33', NULL, NULL, '2026-04-02 01:52:33', '2026-04-02 01:52:33'),
(49, 29, 11, 3768.00, 'KES', 57.54, 3710.46, 'mpesa', '', '', 'pending', 50, NULL, NULL, '2026-04-02 01:52:56', NULL, NULL, '2026-04-02 01:52:56', '2026-04-02 01:52:56'),
(50, 29, 11, 3434.00, 'KES', 56.87, 3377.13, 'mpesa', '', '', 'pending', 51, NULL, NULL, '2026-04-02 02:13:05', NULL, NULL, '2026-04-02 02:13:05', '2026-04-02 02:13:05'),
(51, 29, 11, 4354.00, 'KES', 58.71, 4295.29, 'mpesa', '', '', 'pending', 52, NULL, NULL, '2026-04-02 02:14:02', NULL, NULL, '2026-04-02 02:14:02', '2026-04-02 02:14:02'),
(52, 29, 11, 500.00, 'KES', 40.00, 460.00, 'mpesa', '', '', 'pending', 53, NULL, NULL, '2026-04-02 02:14:23', NULL, NULL, '2026-04-02 02:14:23', '2026-04-02 02:14:23'),
(53, 29, 11, 690.00, 'KES', 40.00, 650.00, 'mpesa', '', '', 'pending', 54, NULL, NULL, '2026-04-02 02:16:19', NULL, NULL, '2026-04-02 02:16:19', '2026-04-02 02:16:19'),
(54, 29, 11, 788.00, 'KES', 40.00, 748.00, 'mpesa', '', '', 'pending', 55, NULL, NULL, '2026-04-02 02:17:27', NULL, NULL, '2026-04-02 02:17:27', '2026-04-02 02:17:27'),
(55, 29, 11, 89879.00, 'KES', 289.88, 89589.12, 'mpesa', '', '', 'pending', 56, NULL, NULL, '2026-04-02 02:18:03', NULL, NULL, '2026-04-02 02:18:03', '2026-04-02 02:18:03'),
(56, 29, 11, 550.00, 'KES', 40.00, 510.00, 'mpesa', '', '', 'pending', 57, NULL, NULL, '2026-04-02 02:18:52', NULL, NULL, '2026-04-02 02:18:52', '2026-04-02 02:18:52'),
(57, 29, 11, 1000.00, 'KES', 40.00, 960.00, 'mpesa', '', '', 'pending', 58, NULL, NULL, '2026-04-02 02:19:18', NULL, NULL, '2026-04-02 02:19:18', '2026-04-02 02:19:18'),
(58, 29, 11, 1000.00, 'KES', 40.00, 960.00, 'mpesa', '', '', 'pending', 59, NULL, NULL, '2026-04-02 02:19:55', NULL, NULL, '2026-04-02 02:19:55', '2026-04-02 02:19:55'),
(59, 29, 11, 566.00, 'KES', 40.00, 526.00, 'mpesa', '', '', 'pending', 60, NULL, NULL, '2026-04-02 02:20:44', NULL, NULL, '2026-04-02 02:20:44', '2026-04-02 02:20:44'),
(60, 29, 11, 600.00, 'KES', 40.00, 560.00, 'mpesa', '', '', 'pending', 61, NULL, NULL, '2026-04-02 02:21:25', NULL, NULL, '2026-04-02 02:21:25', '2026-04-02 02:21:25'),
(61, 29, 11, 3000.00, 'KES', 56.00, 2944.00, 'mpesa', '', '', 'pending', 62, NULL, NULL, '2026-04-02 02:21:39', NULL, NULL, '2026-04-02 02:21:39', '2026-04-02 02:21:39'),
(62, 29, 11, 500.00, 'KES', 40.00, 460.00, 'mpesa', '', '', 'pending', 63, NULL, NULL, '2026-04-03 20:50:40', NULL, NULL, '2026-04-03 20:50:40', '2026-04-03 20:50:40'),
(63, 15, 47, 78978.00, 'KES', 278.98, 78699.02, 'mpesa', '', '', 'pending', 64, NULL, NULL, '2026-04-03 23:55:13', NULL, NULL, '2026-04-03 23:55:13', '2026-04-03 23:55:13'),
(64, 15, 47, 70000.00, 'KES', 270.00, 69730.00, 'mpesa', '', '', 'pending', 65, NULL, NULL, '2026-04-03 23:55:26', NULL, NULL, '2026-04-03 23:55:26', '2026-04-03 23:55:26'),
(65, 15, 47, 78797.00, 'KES', 278.80, 78518.20, 'mpesa', '', '', 'pending', 66, NULL, NULL, '2026-04-03 23:55:50', NULL, NULL, '2026-04-03 23:55:50', '2026-04-03 23:55:50'),
(66, 15, 47, 500.00, 'KES', 40.00, 460.00, 'mpesa', '', '', 'pending', 67, NULL, NULL, '2026-04-03 23:56:02', NULL, NULL, '2026-04-03 23:56:02', '2026-04-03 23:56:02'),
(67, 15, 47, 500.00, 'KES', 40.00, 460.00, 'mpesa', '', '', 'pending', 68, NULL, NULL, '2026-04-03 23:56:17', NULL, NULL, '2026-04-03 23:56:17', '2026-04-03 23:56:17'),
(68, 17, 53, 600.00, 'KES', 40.00, 560.00, 'mpesa', '', '', 'pending', 69, NULL, NULL, '2026-04-05 05:12:52', NULL, NULL, '2026-04-05 05:12:52', '2026-04-05 05:12:52'),
(69, 17, 53, 600.00, 'KES', 40.00, 560.00, 'mpesa', '', '', 'pending', 70, NULL, NULL, '2026-04-05 05:13:04', NULL, NULL, '2026-04-05 05:13:04', '2026-04-05 05:13:04'),
(70, 17, 53, 8000.00, 'KES', 66.00, 7934.00, 'mpesa', '', '', 'pending', 71, NULL, NULL, '2026-04-05 05:19:40', NULL, NULL, '2026-04-05 05:19:40', '2026-04-05 05:19:40'),
(71, 17, 53, 1000.00, 'KES', 40.00, 960.00, 'mpesa', '', '', 'pending', 72, NULL, NULL, '2026-04-05 05:20:12', NULL, NULL, '2026-04-05 05:20:12', '2026-04-05 05:20:12'),
(72, 17, 53, 500.00, 'KES', 40.00, 460.00, 'mpesa', '', '', 'pending', 73, NULL, NULL, '2026-04-05 05:21:57', NULL, NULL, '2026-04-05 05:21:57', '2026-04-05 05:21:57'),
(73, 17, 53, 600.00, 'KES', 40.00, 560.00, 'mpesa', '', '', 'pending', 74, NULL, NULL, '2026-04-05 05:22:16', NULL, NULL, '2026-04-05 05:22:16', '2026-04-05 05:22:16'),
(74, 17, 53, 700.00, 'KES', 40.00, 660.00, 'mpesa', '', '', 'pending', 75, NULL, NULL, '2026-04-05 05:22:34', NULL, NULL, '2026-04-05 05:22:34', '2026-04-05 05:22:34'),
(75, 29, 11, 500.00, 'KES', 40.00, 460.00, 'mpesa', '', '', 'pending', 90, NULL, NULL, '2026-04-05 18:21:16', NULL, NULL, '2026-04-05 18:21:16', '2026-04-05 18:21:16'),
(76, 29, 11, 500.00, 'KES', 40.00, 460.00, 'mpesa', '', '', 'pending', 91, NULL, NULL, '2026-04-05 18:21:27', NULL, NULL, '2026-04-05 18:21:27', '2026-04-05 18:21:27'),
(77, 29, 11, 771.00, 'KES', 40.00, 731.00, 'mpesa', '', '', 'pending', 92, NULL, NULL, '2026-04-05 18:21:41', NULL, NULL, '2026-04-05 18:21:41', '2026-04-05 18:21:41'),
(78, 29, 11, 500.00, 'KES', 40.00, 460.00, 'mpesa', '', '', 'pending', 93, NULL, NULL, '2026-04-05 18:21:54', NULL, NULL, '2026-04-05 18:21:54', '2026-04-05 18:21:54'),
(79, 15, 48, 500.00, 'KES', 40.00, 460.00, 'mpesa', '', '', 'pending', 94, NULL, NULL, '2026-04-05 18:37:51', NULL, NULL, '2026-04-05 18:37:51', '2026-04-05 18:37:51');

-- --------------------------------------------------------

--
-- Table structure for table `withdrawal_logs`
--

CREATE TABLE `withdrawal_logs` (
  `log_id` bigint(20) UNSIGNED NOT NULL,
  `withdrawal_id` bigint(20) UNSIGNED NOT NULL,
  `performed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `withdrawal_logs`
--

INSERT INTO `withdrawal_logs` (`log_id`, `withdrawal_id`, `performed_by`, `note`, `created_at`) VALUES
(1, 32, 29, 'User requested withdrawal of KES 2600, net amount KES 2544.8, fee KES 55.2', '2026-04-01 18:06:01'),
(2, 34, 29, 'User requested withdrawal of KES 5000, net KES 4940, fee KES 60', '2026-04-01 18:10:01'),
(3, 35, 29, 'User requested withdrawal of KES 5000, net KES 4940, fee KES 60', '2026-04-01 18:12:50'),
(4, 36, 29, 'User requested withdrawal of KES 5000, net KES 4940, fee KES 60', '2026-04-01 18:13:07'),
(5, 37, 29, 'User requested withdrawal of KES 5000, net KES 4940, fee KES 60', '2026-04-01 18:14:04'),
(6, 38, 29, 'User requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-01 18:14:43'),
(7, 39, 29, 'User requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-01 18:15:01'),
(8, 0, 29, 'User requested withdrawal of KES 5000, net KES 4940, fee KES 60', '2026-04-01 18:19:30'),
(9, 41, 29, 'User requested withdrawal of KES 8000, net KES 7934, fee KES 66', '2026-04-01 18:20:39'),
(10, 43, 29, 'User requested withdrawal of KES 3000, net KES 2944, fee KES 56', '2026-04-01 18:24:31'),
(11, 0, 15, 'Seller requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-01 18:51:25'),
(12, 0, 15, 'Seller requested withdrawal of KES 700, net KES 660, fee KES 40', '2026-04-01 18:55:45'),
(13, 0, 15, 'Seller requested withdrawal of KES 9000, net KES 8932, fee KES 68', '2026-04-01 18:57:51'),
(14, 0, 15, 'Seller requested withdrawal of KES 9000, net KES 8932, fee KES 68', '2026-04-01 18:58:07'),
(15, 0, 15, 'Seller requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-01 18:58:44'),
(16, 0, 15, 'Seller requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-01 19:00:59'),
(17, 0, 15, 'Seller requested withdrawal of KES 600, net KES 560, fee KES 40', '2026-04-01 19:12:09'),
(18, 0, 15, 'Seller requested withdrawal of KES 7878, net KES 7812.24, fee KES 65.76', '2026-04-01 19:22:34'),
(19, 44, 31, 'User requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-01 23:11:17'),
(20, 44, 31, 'User requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-01 23:22:07'),
(21, 45, 31, 'User requested withdrawal of KES 460, net KES 420, fee KES 40', '2026-04-01 23:48:28'),
(22, 46, 29, 'User requested withdrawal of KES 2000, net KES 1946, fee KES 54', '2026-04-02 01:35:01'),
(23, 47, 29, 'User requested withdrawal of KES 1000, net KES 960, fee KES 40', '2026-04-02 01:52:07'),
(24, 48, 29, 'User requested withdrawal of KES 1000, net KES 960, fee KES 40', '2026-04-02 01:52:33'),
(25, 49, 29, 'User requested withdrawal of KES 3768, net KES 3710.46, fee KES 57.54', '2026-04-02 01:52:56'),
(26, 50, 29, 'User requested withdrawal of KES 3434, net KES 3377.13, fee KES 56.87', '2026-04-02 02:13:05'),
(27, 51, 29, 'User requested withdrawal of KES 4354, net KES 4295.29, fee KES 58.71', '2026-04-02 02:14:02'),
(28, 52, 29, 'User requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-02 02:14:23'),
(29, 53, 29, 'User requested withdrawal of KES 690, net KES 650, fee KES 40', '2026-04-02 02:16:19'),
(30, 54, 29, 'User requested withdrawal of KES 788, net KES 748, fee KES 40', '2026-04-02 02:17:27'),
(31, 55, 29, 'User requested withdrawal of KES 89879, net KES 89589.12, fee KES 289.88', '2026-04-02 02:18:03'),
(32, 56, 29, 'User requested withdrawal of KES 550, net KES 510, fee KES 40', '2026-04-02 02:18:52'),
(33, 57, 29, 'User requested withdrawal of KES 1000, net KES 960, fee KES 40', '2026-04-02 02:19:18'),
(34, 58, 29, 'User requested withdrawal of KES 1000, net KES 960, fee KES 40', '2026-04-02 02:19:55'),
(35, 59, 29, 'User requested withdrawal of KES 566, net KES 526, fee KES 40', '2026-04-02 02:20:44'),
(36, 60, 29, 'User requested withdrawal of KES 600, net KES 560, fee KES 40', '2026-04-02 02:21:25'),
(37, 61, 29, 'User requested withdrawal of KES 3000, net KES 2944, fee KES 56', '2026-04-02 02:21:39'),
(38, 62, 29, 'User requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-03 20:50:40'),
(39, 63, 15, 'Seller requested withdrawal of KES 78978, net KES 78699.02, fee KES 278.98', '2026-04-03 23:55:13'),
(40, 64, 15, 'Seller requested withdrawal of KES 70000, net KES 69730, fee KES 270', '2026-04-03 23:55:26'),
(41, 65, 15, 'Seller requested withdrawal of KES 78797, net KES 78518.2, fee KES 278.8', '2026-04-03 23:55:50'),
(42, 66, 15, 'Seller requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-03 23:56:02'),
(43, 67, 15, 'Seller requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-03 23:56:17'),
(44, 0, 17, 'Seller requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-04 13:36:05'),
(45, 0, 17, 'Seller requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-05 03:13:29'),
(46, 0, 17, 'Seller requested withdrawal of KES 600, net KES 560, fee KES 40', '2026-04-05 03:19:43'),
(47, 0, 17, 'Seller requested withdrawal of KES 610, net KES 570, fee KES 40', '2026-04-05 03:22:24'),
(48, 0, 17, 'Seller requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-05 03:26:53'),
(49, 0, 17, 'Seller requested withdrawal of KES 610, net KES 570, fee KES 40', '2026-04-05 03:35:49'),
(50, 0, 17, 'Seller requested withdrawal of KES 577, net KES 537, fee KES 40', '2026-04-05 03:40:04'),
(51, 0, 17, 'Seller requested withdrawal of KES 520, net KES 480, fee KES 40', '2026-04-05 03:45:19'),
(52, 0, 17, 'Seller requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-05 03:47:02'),
(53, 0, 17, 'Seller requested withdrawal of KES 600, net KES 560, fee KES 40', '2026-04-05 03:49:09'),
(54, 0, 17, 'Seller requested withdrawal of KES 599, net KES 559, fee KES 40', '2026-04-05 03:53:31'),
(55, 0, 17, 'Seller requested withdrawal of KES 600, net KES 560, fee KES 40', '2026-04-05 03:54:42'),
(56, 0, 17, 'Seller requested withdrawal of KES 600, net KES 560, fee KES 40', '2026-04-05 04:00:49'),
(57, 0, 17, 'Seller requested withdrawal of KES 600, net KES 560, fee KES 40', '2026-04-05 04:04:12'),
(58, 68, 17, 'Seller requested withdrawal of KES 600, net KES 560, fee KES 40', '2026-04-05 05:12:52'),
(59, 69, 17, 'Seller requested withdrawal of KES 600, net KES 560, fee KES 40', '2026-04-05 05:13:04'),
(60, 70, 17, 'Seller requested withdrawal of KES 8000, net KES 7934, fee KES 66', '2026-04-05 05:19:40'),
(61, 71, 17, 'Seller requested withdrawal of KES 1000, net KES 960, fee KES 40', '2026-04-05 05:20:12'),
(62, 72, 17, 'Seller requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-05 05:21:57'),
(63, 73, 17, 'Seller requested withdrawal of KES 600, net KES 560, fee KES 40', '2026-04-05 05:22:16'),
(64, 74, 17, 'Seller requested withdrawal of KES 700, net KES 660, fee KES 40', '2026-04-05 05:22:34'),
(65, 75, 29, 'User requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-05 18:21:16'),
(66, 76, 29, 'User requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-05 18:21:27'),
(67, 77, 29, 'User requested withdrawal of KES 771, net KES 731, fee KES 40', '2026-04-05 18:21:41'),
(68, 78, 29, 'User requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-05 18:21:54'),
(69, 79, 15, 'Seller requested withdrawal of KES 500, net KES 460, fee KES 40', '2026-04-05 18:37:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `financial_transactions`
--
ALTER TABLE `financial_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD UNIQUE KEY `reference_code` (`reference_code`),
  ADD KEY `wallet_id` (`wallet_id`),
  ADD KEY `payer_id` (`payer_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `status` (`status`),
  ADD KEY `source_type` (`source_type`);

--
-- Indexes for table `markethub_products`
--
ALTER TABLE `markethub_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mpesa_payments`
--
ALTER TABLE `mpesa_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `mpesa_receipt` (`mpesa_receipt`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indexes for table `productservicesrentals`
--
ALTER TABLE `productservicesrentals`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`),
  ADD KEY `idx_user_product` (`user_id`,`product_name`),
  ADD KEY `idx_user_image` (`user_id`,`image_hash`),
  ADD KEY `idx_image_hash` (`image_hash`),
  ADD KEY `idx_image_phash` (`image_phash`);

--
-- Indexes for table `seller_ratings`
--
ALTER TABLE `seller_ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `username_2` (`username`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD UNIQUE KEY `phone_2` (`phone`),
  ADD UNIQUE KEY `referral_code` (`agency_code`);

--
-- Indexes for table `user_cart`
--
ALTER TABLE `user_cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `user_followers`
--
ALTER TABLE `user_followers`
  ADD PRIMARY KEY (`follower_id`,`followed_id`),
  ADD UNIQUE KEY `unique_follow` (`follower_id`,`followed_id`),
  ADD KEY `followed_id` (`followed_id`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`wallet_id`),
  ADD UNIQUE KEY `unique_wallet` (`user_id`,`wallet_type`),
  ADD UNIQUE KEY `unique_user_wallet` (`user_id`,`wallet_type`);

--
-- Indexes for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`withdrawal_id`),
  ADD UNIQUE KEY `reference_code` (`reference_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `wallet_id` (`wallet_id`),
  ADD KEY `status` (`status`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `withdrawal_logs`
--
ALTER TABLE `withdrawal_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `withdrawal_id` (`withdrawal_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `financial_transactions`
--
ALTER TABLE `financial_transactions`
  MODIFY `transaction_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `markethub_products`
--
ALTER TABLE `markethub_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `mpesa_payments`
--
ALTER TABLE `mpesa_payments`
  MODIFY `payment_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=284;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=327;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productservicesrentals`
--
ALTER TABLE `productservicesrentals`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `seller_ratings`
--
ALTER TABLE `seller_ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `user_cart`
--
ALTER TABLE `user_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=206;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `wallet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `withdrawal_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `withdrawal_logs`
--
ALTER TABLE `withdrawal_logs`
  MODIFY `log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

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
