-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 29, 2026 at 08:25 AM
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
-- Database: `Market Hub`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `account_type` enum('buyer','seller') NOT NULL DEFAULT 'buyer',
  `country` varchar(100) NOT NULL DEFAULT 'Kenya',
  `county` varchar(100) NOT NULL,
  `ward` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `phone`, `password`, `account_type`, `country`, `county`, `ward`, `created_at`) VALUES
(1, 'blessedemmanuel', 'Ymxlc3NlZGVtbWFudWVsZXZhbmdlbGlzbTIzQGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzMA==', '$2y$10$VeJuqJndhfbfyMRfDflKkehTlSNJ2uatGdbiG7JvlovkQhhU.UV6O', 'buyer', 'Kenya', 'Kilifi', '', '2026-01-29 05:59:23'),
(2, 'blessedemmanuel254', 'Ymxlc3NlZGVtbWFudWVsZXZhbmdlbGlzbTIzMjU0QGdtYWlsLmNvbQ==', 'KzI1NDc1OTU3ODYzOQ==', '$2y$10$CRg5Do77z1eFP6CT2MUMou6szIx6Ha3eq0O4z/8YqT2qlFw7NicR6', 'buyer', 'Kenya', 'Kilifi', '', '2026-01-29 06:07:15'),
(3, 'Mtesti', 'bXRlc3RpMjNAZ21haWwuY29t', 'KzI1NDcwODY3MDM5Ng==', '$2y$10$guM7j4LAiZksvYn5s4LxM.X6k5rlZV2OuN5rE20I3AJM6MIPYCn6q', 'buyer', 'Kenya', 'Kilifi', 'Sokoni Ward', '2026-01-29 06:48:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
