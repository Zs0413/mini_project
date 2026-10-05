-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql103.byetcluster.com
-- Generation Time: Oct 05, 2026 at 10:07 AM
-- Server version: 11.4.13-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `icei_42968571_splash_mania_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

CREATE TABLE `albums` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` enum('visible','hidden') NOT NULL DEFAULT 'visible',
  `type` enum('Gallery','Promotion') NOT NULL DEFAULT 'Gallery',
  `date_of_activity` date DEFAULT NULL,
  `start_promotion_date` date DEFAULT NULL,
  `end_promotion_date` date DEFAULT NULL,
  `about` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `albums`
--

INSERT INTO `albums` (`id`, `title`, `created_at`, `status`, `type`, `date_of_activity`, `start_promotion_date`, `end_promotion_date`, `about`) VALUES
(10, 'food', '2026-09-28 21:26:40', 'hidden', 'Gallery', '2026-09-24', NULL, NULL, 'fruit'),
(11, 'Discount', '2026-09-28 21:28:23', 'visible', 'Promotion', NULL, '2026-09-27', '2026-10-08', 'school holiday'),
(12, 'Summer in the Splash', '2026-10-03 21:44:51', 'hidden', 'Gallery', '2026-10-06', NULL, NULL, 'splash mania');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` enum('Pending','Paid','Confirm','Rejected','Re-upload') NOT NULL DEFAULT 'Pending',
  `receipt_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `booking_date`, `total_price`, `created_at`, `status`, `receipt_path`) VALUES
(13, 3, '2026-08-25', '725.00', '2026-08-19 21:18:44', 'Pending', NULL),
(18, 6, '2026-10-03', '315.00', '2026-09-24 23:04:25', 'Confirm', 'uploads/receipts/receipt_18_1790579913.jpg'),
(19, 6, '2026-10-08', '1285.00', '2026-09-26 17:25:35', 'Confirm', 'uploads/receipts/receipt_19_1790579484.jpg'),
(152, 101, '2026-08-05', '220.00', '2026-07-28 10:30:00', 'Pending', NULL),
(153, 102, '2026-08-08', '345.00', '2026-07-29 14:15:00', 'Pending', NULL),
(154, 103, '2026-08-10', '125.00', '2026-07-30 09:45:00', 'Pending', NULL),
(155, 104, '2026-08-12', '440.00', '2026-08-01 11:20:00', 'Pending', NULL),
(156, 105, '2026-08-15', '250.00', '2026-08-02 16:10:00', 'Pending', NULL),
(157, 106, '2026-08-18', '190.00', '2026-08-04 08:50:00', 'Pending', NULL),
(158, 107, '2026-08-20', '315.00', '2026-08-05 19:30:00', 'Pending', NULL),
(159, 108, '2026-08-22', '125.00', '2026-08-07 13:15:00', 'Pending', NULL),
(160, 109, '2026-08-25', '220.00', '2026-08-09 21:40:00', 'Pending', NULL),
(161, 110, '2026-08-28', '440.00', '2026-08-11 10:05:00', 'Pending', NULL),
(162, 111, '2026-09-01', '95.00', '2026-08-13 15:55:00', 'Pending', NULL),
(163, 112, '2026-09-03', '250.00', '2026-08-15 18:45:00', 'Pending', NULL),
(164, 113, '2026-09-05', '375.00', '2026-08-17 09:20:00', 'Pending', NULL),
(165, 114, '2026-09-08', '125.00', '2026-08-19 11:35:00', 'Pending', NULL),
(166, 115, '2026-09-10', '345.00', '2026-08-22 14:10:00', 'Pending', NULL),
(167, 116, '2026-09-12', '440.00', '2026-08-24 17:50:00', 'Pending', NULL),
(168, 117, '2026-09-15', '125.00', '2026-08-26 20:15:00', 'Pending', NULL),
(169, 118, '2026-09-18', '250.00', '2026-08-28 08:30:00', 'Pending', NULL),
(170, 119, '2026-09-20', '375.00', '2026-08-30 12:45:00', 'Pending', NULL),
(171, 120, '2026-09-22', '220.00', '2026-09-02 15:20:00', 'Pending', NULL),
(172, 121, '2026-09-25', '315.00', '2026-09-04 10:05:00', 'Pending', NULL),
(173, 122, '2026-09-28', '500.00', '2026-09-06 18:30:00', 'Pending', NULL),
(174, 123, '2026-10-01', '95.00', '2026-09-08 09:15:00', 'Pending', NULL),
(175, 124, '2026-10-03', '220.00', '2026-09-09 14:50:00', 'Pending', NULL),
(176, 125, '2026-10-05', '345.00', '2026-09-10 21:35:00', 'Pending', NULL),
(177, 126, '2026-10-08', '190.00', '2026-09-11 11:20:00', 'Pending', NULL),
(178, 127, '2026-10-10', '250.00', '2026-09-12 16:10:00', 'Pending', NULL),
(179, 128, '2026-10-12', '375.00', '2026-09-13 08:40:00', 'Pending', NULL),
(180, 129, '2026-10-15', '125.00', '2026-09-14 13:55:00', 'Pending', NULL),
(181, 130, '2026-10-18', '220.00', '2026-09-15 19:25:00', 'Pending', NULL),
(182, 131, '2026-10-20', '440.00', '2026-09-16 10:10:00', 'Pending', NULL),
(183, 132, '2026-10-22', '125.00', '2026-09-17 15:45:00', 'Pending', NULL),
(184, 133, '2026-10-25', '250.00', '2026-09-18 09:30:00', 'Pending', NULL),
(185, 134, '2026-10-28', '375.00', '2026-09-19 14:15:00', 'Pending', NULL),
(186, 135, '2026-10-30', '220.00', '2026-09-20 20:05:00', 'Pending', NULL),
(187, 136, '2026-11-01', '315.00', '2026-09-21 11:50:00', 'Pending', NULL),
(188, 137, '2026-11-03', '440.00', '2026-09-21 16:30:00', 'Pending', NULL),
(189, 138, '2026-11-05', '125.00', '2026-09-22 08:15:00', 'Pending', NULL),
(190, 139, '2026-11-08', '250.00', '2026-09-22 13:40:00', 'Pending', NULL),
(191, 140, '2026-11-10', '375.00', '2026-09-23 19:05:00', 'Pending', NULL),
(192, 141, '2026-11-12', '220.00', '2026-09-23 10:20:00', 'Pending', NULL),
(193, 142, '2026-11-15', '345.00', '2026-09-24 15:55:00', 'Pending', NULL),
(194, 143, '2026-11-18', '440.00', '2026-09-24 09:45:00', 'Pending', NULL),
(195, 144, '2026-11-20', '125.00', '2026-09-25 14:30:00', 'Pending', NULL),
(196, 145, '2026-11-22', '220.00', '2026-09-25 11:05:00', 'Pending', NULL),
(197, 146, '2026-11-25', '345.00', '2026-09-26 16:20:00', 'Pending', NULL),
(198, 147, '2026-11-28', '190.00', '2026-09-26 08:35:00', 'Pending', NULL),
(199, 148, '2026-11-30', '250.00', '2026-09-26 13:10:00', 'Pending', NULL),
(200, 149, '2026-12-02', '375.00', '2026-09-26 10:00:00', 'Pending', NULL),
(201, 150, '2026-12-05', '125.00', '2026-09-26 14:55:00', 'Pending', NULL),
(202, 101, '2026-09-01', '250.00', '2026-08-15 10:20:00', 'Pending', NULL),
(203, 105, '2026-09-10', '345.00', '2026-08-20 14:10:00', 'Pending', NULL),
(204, 112, '2026-09-25', '220.00', '2026-09-01 09:30:00', 'Pending', NULL),
(205, 118, '2026-10-05', '315.00', '2026-09-10 11:45:00', 'Pending', NULL),
(206, 125, '2026-10-15', '440.00', '2026-09-15 16:20:00', 'Pending', NULL),
(207, 130, '2026-10-25', '125.00', '2026-09-18 08:15:00', 'Pending', NULL),
(208, 135, '2026-11-05', '250.00', '2026-09-20 13:40:00', 'Pending', NULL),
(209, 142, '2026-11-20', '375.00', '2026-09-22 19:05:00', 'Pending', NULL),
(210, 148, '2026-12-10', '220.00', '2026-09-25 10:50:00', 'Pending', NULL),
(211, 103, '2026-10-08', '250.00', '2026-09-12 14:35:00', 'Pending', NULL),
(212, 107, '2026-10-22', '345.00', '2026-09-17 09:25:00', 'Pending', NULL),
(213, 115, '2026-11-12', '125.00', '2026-09-20 11:10:00', 'Pending', NULL),
(214, 122, '2026-11-28', '250.00', '2026-09-24 16:55:00', 'Pending', NULL),
(215, 139, '2026-12-15', '440.00', '2026-09-26 08:20:00', 'Pending', NULL),
(216, 145, '2026-12-25', '220.00', '2026-09-26 13:45:00', 'Pending', NULL),
(217, 6, '2026-09-30', '1440.00', '2026-09-28 15:54:17', 'Confirm', 'uploads/receipts/receipt_217_1790582073.jpg'),
(218, 6, '2026-10-10', '4190.00', '2026-09-28 15:59:18', 'Confirm', 'uploads/receipts/receipt_218_1790582420.jpg'),
(219, 152, '2026-10-15', '345.00', '2026-09-27 10:30:00', 'Paid', 'uploads/receipts/2026/09/SM-0219_b4a1f3.jpg'),
(220, 153, '2026-10-18', '250.00', '2026-09-27 12:00:00', 'Confirm', 'uploads/receipts/2026/09/SM-0220_c9x2n1.jpg'),
(221, 154, '2026-10-20', '535.00', '2026-09-27 15:10:00', 'Pending', NULL),
(222, 155, '2026-10-25', '125.00', '2026-09-28 09:30:00', 'Rejected', NULL),
(223, 156, '2026-10-28', '440.00', '2026-09-28 11:00:00', 'Re-upload', 'uploads/receipts/2026/09/SM-0223_v7c3x2.jpg'),
(224, 6, '2026-11-05', '910.00', '2026-09-28 12:15:00', 'Confirm', 'uploads/receipts/2026/09/SM-0224_h5g6b2.pdf'),
(225, 157, '2026-11-10', '125.00', '2026-09-28 13:00:00', 'Rejected', NULL),
(226, 158, '2026-11-15', '315.00', '2026-09-28 14:00:00', 'Confirm', 'uploads/receipts/2026/09/SM-0226_x3y4z5.jpg'),
(227, 159, '2026-10-07', '4160.00', '2026-10-03 09:44:17', 'Paid', 'uploads/receipts/2026/10/SM-0227_db3fd0.jpg'),
(228, 6, '2026-10-07', '500.00', '2026-10-03 21:42:26', 'Paid', 'uploads/receipts/2026/10/SM-0228_c66dce.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `booking_details`
--

CREATE TABLE `booking_details` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `ticket_type` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_per_unit` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_details`
--

INSERT INTO `booking_details` (`id`, `booking_id`, `ticket_type`, `quantity`, `price_per_unit`, `subtotal`) VALUES
(39, 13, 'adult', 2, '125.00', '250.00'),
(40, 13, 'child', 5, '95.00', '475.00'),
(49, 18, 'adult', 1, '125.00', '125.00'),
(50, 18, 'child', 1, '95.00', '95.00'),
(51, 18, 'senior', 1, '95.00', '95.00'),
(52, 19, 'adult', 8, '125.00', '1000.00'),
(53, 19, 'child', 3, '95.00', '285.00'),
(58, 217, 'adult', 10, '125.00', '1250.00'),
(59, 217, 'child', 2, '95.00', '190.00'),
(62, 218, 'adult', 32, '125.00', '4000.00'),
(63, 218, 'child', 2, '95.00', '190.00'),
(64, 219, 'adult', 2, '125.00', '250.00'),
(65, 219, 'child', 1, '95.00', '95.00'),
(66, 220, 'adult', 2, '125.00', '250.00'),
(67, 221, 'adult', 2, '125.00', '250.00'),
(68, 221, 'child', 3, '95.00', '285.00'),
(69, 222, 'adult', 1, '125.00', '125.00'),
(70, 223, 'adult', 2, '125.00', '250.00'),
(71, 223, 'child', 2, '95.00', '190.00'),
(72, 224, 'adult', 5, '125.00', '625.00'),
(73, 224, 'child', 3, '95.00', '285.00'),
(74, 225, 'adult', 1, '125.00', '125.00'),
(75, 226, 'adult', 1, '125.00', '125.00'),
(76, 226, 'child', 1, '95.00', '95.00'),
(77, 226, 'senior', 1, '95.00', '95.00'),
(78, 227, 'adult', 31, '125.00', '3875.00'),
(79, 227, 'child', 3, '95.00', '285.00'),
(80, 228, 'adult', 4, '125.00', '500.00');

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `album_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `status` enum('visible','hidden') DEFAULT 'visible',
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `album_id`, `image_path`, `caption`, `status`, `uploaded_at`) VALUES
(62, 11, 'uploads/2026/09/Discount_6aba87623ef92_0.webp', 'Discount', 'visible', '2026-09-28 23:27:30'),
(63, 11, 'uploads/2026/09/Discount_6aba876248bba_1.webp', 'Discount', 'visible', '2026-09-28 23:27:30'),
(64, 11, 'uploads/2026/09/Discount_6aba8762520cf_2.webp', 'Discount', 'visible', '2026-09-28 23:27:30'),
(65, 11, 'uploads/2026/09/Discount_6aba87625c62e_3.webp', 'Discount', 'visible', '2026-09-28 23:27:30'),
(66, 11, 'uploads/2026/09/Discount_6aba876267719_4.webp', 'Discount', 'visible', '2026-09-28 23:27:30'),
(68, 12, 'uploads/2026/10/Summer_in_the_Splash_6ac1d9e4c622e_1.webp', 'Discount', 'visible', '2026-10-03 21:45:25'),
(69, 12, 'uploads/2026/10/Summer_in_the_Splash_6ac1d9e4dc650_2.webp', 'Discount', 'visible', '2026-10-03 21:45:25'),
(70, 12, 'uploads/2026/10/Summer_in_the_Splash_6ac1d9e4ed9cc_3.webp', 'Discount', 'visible', '2026-10-03 21:45:25'),
(71, 12, 'uploads/2026/10/Summer_in_the_Splash_6ac1d9e504c5c_4.webp', 'Discount', 'visible', '2026-10-03 21:45:25'),
(72, 12, 'uploads/2026/10/Summer_in_the_Splash_6ac1d9e51c20a_5.webp', 'Discount', 'visible', '2026-10-03 21:45:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `role` varchar(20) DEFAULT 'customer',
  `regdate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `username`, `password`, `phone`, `role`, `regdate`) VALUES
(3, 'admin', '', 'admin', '$2y$10$Y8uMcAZIyx4ok/sJ3PtEeuNSehby.uZ7kfEBvCKYkvA4DKBpcer42', '0167567287', 'admin', '2026-08-19 21:17:12'),
(5, 'khor', 'khor@gmail.com', 'seng', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '01136951645', 'customer', '2026-08-19 21:28:12'),
(6, 'Chua Rou', 'chua@gmail.com', 'chua', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '01136951645', 'customer', '2026-09-24 23:04:08'),
(101, 'Ahmad Faizal', 'faizal@gmail.com', 'faizal88', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0123456781', 'customer', '2026-07-15 10:20:00'),
(102, 'Siti Nurhaliza', 'siti.n@yahoo.com', 'siti_nur', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0172233445', 'customer', '2026-07-16 14:15:00'),
(103, 'Lim Wei Jian', 'weijian.lim@gmail.com', 'weijian99', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0165559988', 'customer', '2026-07-18 09:10:00'),
(104, 'Tan Mei Ling', 'meiling.tan@hotmail.com', 'meiling_t', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0112345678', 'customer', '2026-07-20 11:30:00'),
(105, 'Muthusamy A/L Raj', 'muthu@gmail.com', 'muthu_r', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0198887766', 'customer', '2026-07-22 16:45:00'),
(106, 'Priya Sharma', 'priya.sharma@yahoo.com', 'priya_s', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0134445566', 'customer', '2026-07-25 08:20:00'),
(107, 'Amirul Haziq', 'amirul.h@gmail.com', 'amirul_h', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0142223344', 'customer', '2026-08-01 19:10:00'),
(108, 'Nurul Izzati', 'nurul.izzati@hotmail.com', 'izzati_n', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0187776655', 'customer', '2026-08-03 13:40:00'),
(109, 'Wong Zi Hao', 'zihao.wong@gmail.com', 'zihao_w', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0129998877', 'customer', '2026-08-05 21:05:00'),
(110, 'Lee Jia Hui', 'jiahui.lee@yahoo.com', 'jiahui_l', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0176665544', 'customer', '2026-08-08 10:50:00'),
(111, 'Kavitha Devi', 'kavitha@gmail.com', 'kavitha_d', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0163334455', 'customer', '2026-08-10 15:30:00'),
(112, 'Muhammad Shafiq', 'shafiq.m@hotmail.com', 'shafiq_m', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0191112233', 'customer', '2026-08-12 18:20:00'),
(113, 'Aisyah Humaira', 'aisyah.h@gmail.com', 'aisyah_h', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0135556677', 'customer', '2026-08-15 09:40:00'),
(114, 'Chong Wei Ling', 'weiling.chong@yahoo.com', 'weiling_c', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0124447788', 'customer', '2026-08-18 11:15:00'),
(115, 'Ng Ming Jie', 'mingjie.ng@gmail.com', 'mingjie_n', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0178889900', 'customer', '2026-08-20 14:55:00'),
(116, 'Ravi Kumar', 'ravi.kumar@hotmail.com', 'ravi_k', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0162221133', 'customer', '2026-08-22 17:10:00'),
(117, 'Faris Zikri', 'faris.z@gmail.com', 'faris_z', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0149993344', 'customer', '2026-08-25 20:30:00'),
(118, 'Nadia Nabilah', 'nadia.n@yahoo.com', 'nadia_n', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0183332211', 'customer', '2026-08-27 08:45:00'),
(119, 'Chan Jia Xin', 'jiaxin.chan@gmail.com', 'jiaxin_c', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0114445566', 'customer', '2026-08-29 12:20:00'),
(120, 'Ooi Jun Hui', 'junhui.ooi@hotmail.com', 'junhui_o', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0127778899', 'customer', '2026-09-01 15:40:00'),
(121, 'Anjali Nair', 'anjali@gmail.com', 'anjali_n', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0195554433', 'customer', '2026-09-02 10:15:00'),
(122, 'Safwan Hakim', 'safwan.h@yahoo.com', 'safwan_h', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0138887766', 'customer', '2026-09-04 18:55:00'),
(123, 'Atiqah Husna', 'atiqah.h@gmail.com', 'atiqah_h', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0171113322', 'customer', '2026-09-05 09:30:00'),
(124, 'Liew Jia Yee', 'jiayee.liew@hotmail.com', 'jiayee_l', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0164449988', 'customer', '2026-09-06 14:20:00'),
(125, 'Ho Jian Ming', 'jianming.ho@gmail.com', 'jianming_h', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0146665577', 'customer', '2026-09-08 21:10:00'),
(126, 'Dinesh Raj', 'dinesh@yahoo.com', 'dinesh_r', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0189991122', 'customer', '2026-09-09 11:45:00'),
(127, 'Syahirah Amin', 'syahirah.a@gmail.com', 'syahirah_a', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0112224455', 'customer', '2026-09-10 16:35:00'),
(128, 'Zulfadhli Osman', 'zul.osman@hotmail.com', 'zul_osman', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0125556633', 'customer', '2026-09-11 08:50:00'),
(129, 'Khoo Hui Min', 'huimin.khoo@gmail.com', 'huimin_k', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0193338877', 'customer', '2026-09-12 13:15:00'),
(130, 'Teh Jun Wei', 'junwei.teh@yahoo.com', 'junwei_t', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0137774499', 'customer', '2026-09-13 19:40:00'),
(131, 'Divya Natarajan', 'divya@gmail.com', 'divya_n', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0174442255', 'customer', '2026-09-14 10:25:00'),
(132, 'Luqman Hakim', 'luqman.h@hotmail.com', 'luqman_h', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0168885511', 'customer', '2026-09-15 15:55:00'),
(133, 'Batrisyia Ramli', 'batrisyia.r@gmail.com', 'batrisyia_r', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0143337766', 'customer', '2026-09-16 09:10:00'),
(134, 'Yeoh Jia Hui', 'jiahui.yeoh@yahoo.com', 'jiahui_y', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0186663399', 'customer', '2026-09-17 14:40:00'),
(135, 'Goh Kah Chun', 'kahchun.goh@gmail.com', 'kahchun_g', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0117771122', 'customer', '2026-09-18 20:15:00'),
(136, 'Suresh Pillai', 'suresh@hotmail.com', 'suresh_p', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0129995544', 'customer', '2026-09-19 11:30:00'),
(137, 'Amran Yassin', 'amran.y@gmail.com', 'amran_y', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0194448833', 'customer', '2026-09-19 16:50:00'),
(138, 'Fatimah Zahra', 'fatimah.z@yahoo.com', 'fatimah_z', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0132226677', 'customer', '2026-09-20 08:20:00'),
(139, 'Tee Wei Kang', 'weikang.tee@gmail.com', 'weikang_t', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0175551188', 'customer', '2026-09-21 13:45:00'),
(140, 'Low Pei Ling', 'peiling.low@hotmail.com', 'peiling_l', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0163339944', 'customer', '2026-09-21 19:10:00'),
(141, 'Sharmila Anand', 'sharmila@gmail.com', 'sharmila_a', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0148882255', 'customer', '2026-09-22 10:35:00'),
(142, 'Khairul Nizam', 'khairul.n@yahoo.com', 'khairul_n', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0181115599', 'customer', '2026-09-22 15:20:00'),
(143, 'Mastura Khalid', 'mastura.k@gmail.com', 'mastura_k', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0116667733', 'customer', '2026-09-23 09:55:00'),
(144, 'Pang Zi Xuan', 'zixuan.pang@hotmail.com', 'zixuan_p', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0124441166', 'customer', '2026-09-23 14:40:00'),
(145, 'Seow Jing Yi', 'jingyi.seow@gmail.com', 'jingyi_s', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0197775522', 'customer', '2026-09-24 11:15:00'),
(146, 'Karthik Rao', 'karthik@yahoo.com', 'karthik_r', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0135559900', 'customer', '2026-09-24 16:30:00'),
(147, 'Irfan Daniel', 'irfan.d@gmail.com', 'irfan_d', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0172228811', 'customer', '2026-09-25 08:45:00'),
(148, 'Suhaila Roslan', 'suhaila.r@hotmail.com', 'suhaila_r', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0169994477', 'customer', '2026-09-25 13:20:00'),
(149, 'Chua Jin Kang', 'jinkang.chua@gmail.com', 'jinkang_c', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0143336688', 'customer', '2026-09-26 10:10:00'),
(150, 'Kok Yee Lin', 'yeelin.kok@yahoo.com', 'yeelin_k', 'DELETED_ACCOUNT', '0186661155', 'deleted', '2026-09-26 14:05:00'),
(151, 'Wang Zhao Xian', 'zx@gmail.com', 'zx', '$2y$10$UE2ZZ7Jkp76Mk1hlT9iBi.g2.aXswcvQNH6AIYgqulTdmhJu09utW', '01195648623', 'admin', '2026-09-27 22:13:39'),
(152, 'Amirul Asyraf', 'amirul.asyraf@gmail.com', 'amirul_a', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0112341122', 'customer', '2026-09-27 10:15:00'),
(153, 'Lee Mei Hui', 'meihui.lee@yahoo.com', 'meihui_l', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0165551234', 'customer', '2026-09-27 11:20:00'),
(154, 'Vikneswaran A/L Kumar', 'viknes@gmail.com', 'viknes_k', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0198884321', 'customer', '2026-09-27 14:45:00'),
(155, 'Nurul Huda', 'nurul.huda@hotmail.com', 'nurul_h', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0172223344', 'customer', '2026-09-28 09:10:00'),
(156, 'Chong Wei Kit', 'weikit.chong@gmail.com', 'weikit_c', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0123336677', 'customer', '2026-09-28 10:30:00'),
(157, 'Siti Nur Baya', 'siti.baya@yahoo.com', 'siti_b', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0112223334', 'customer', '2026-09-28 11:00:00'),
(158, 'Goh Kah Ming', 'kahming@gmail.com', 'kahming_g', '$2y$10$iZd.A1NKj3xa8.88HMT/SeSPwX/0QBYK6SYWwB5ARQyMuygVp/aue', '0129998888', 'customer', '2026-09-28 12:00:00'),
(159, 'Zhao Shi Wang', 'wangzs0813@gmail.com', 'zs', '$2y$12$q05tzc7GU0o/vOYAnXAAJe9xMNhNCQ0.W9CE1IQRQhOUkm5jQ2kqW', '01136951645', 'customer', '2026-10-03 09:43:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `albums`
--
ALTER TABLE `albums`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `booking_details`
--
ALTER TABLE `booking_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `album_id` (`album_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `albums`
--
ALTER TABLE `albums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=230;

--
-- AUTO_INCREMENT for table `booking_details`
--
ALTER TABLE `booking_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `booking_details`
--
ALTER TABLE `booking_details`
  ADD CONSTRAINT `booking_details_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gallery`
--
ALTER TABLE `gallery`
  ADD CONSTRAINT `gallery_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
