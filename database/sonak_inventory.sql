-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 02, 2025 at 04:47 PM
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
-- Database: `sonak_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `agentId` bigint(20) UNSIGNED NOT NULL,
  `agentName` varchar(255) NOT NULL,
  `agentEmail` varchar(255) DEFAULT NULL,
  `agentPhone` varchar(255) DEFAULT NULL,
  `agentStatus` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1 = Active, 0 = Inactive',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agents`
--

INSERT INTO `agents` (`agentId`, `agentName`, `agentEmail`, `agentPhone`, `agentStatus`, `created_at`, `updated_at`) VALUES
(10, 'Michael Mussa', 'michael.mussa@example.com', '255711234010', 1, '2025-08-12 17:33:12', '2025-08-12 17:33:12'),
(11, 'Amina Mwakyusa', 'amina.mwakyusa@example.com', '255712345011', 1, '2025-08-12 17:33:12', '2025-08-12 17:33:12'),
(12, 'John Mushi', 'john.mushi@example.com', '255713456012', 1, '2025-08-12 17:33:12', '2025-08-12 17:33:12'),
(13, 'Neema Ally', 'neema.ally@example.com', '255714567013', 1, '2025-08-12 17:33:12', '2025-08-12 17:33:12'),
(14, 'Peter Yusuf', 'peter.yusuf@example.com', '255715678014', 1, '2025-08-12 17:33:12', '2025-08-12 17:33:12'),
(15, 'Fatma Mrema', 'fatma.mrema@example.com', '255716789015', 1, '2025-08-12 17:33:12', '2025-08-12 17:33:12'),
(16, 'Josephine Komba', 'josephine.komba@example.com', '255717890016', 1, '2025-08-12 17:33:12', '2025-08-12 17:33:12'),
(17, 'Grace Mwakyusa', 'grace.mwakyusa@example.com', '255718901017', 1, '2025-08-12 17:33:12', '2025-08-12 17:33:12'),
(18, 'David Ally', 'david.ally@example.com', '255719012018', 1, '2025-08-12 17:33:12', '2025-08-12 17:33:12'),
(19, 'Rehema Nnko', 'rehema.nnko@example.com', '255710123019', 1, '2025-08-12 17:33:12', '2025-08-12 17:33:12'),
(20, 'Michael Juma', 'michael.juma@example.com', '255711234020', 1, '2025-08-12 17:33:12', '2025-08-12 17:33:12'),
(22, 'Younek', 'younek@yahoo.com', '0745412312', 1, '2025-08-13 12:04:46', '2025-08-13 12:04:46'),
(24, 'Edgar Amb', 'amb@gmail.com', '0679799406', 1, '2025-08-13 14:17:10', '2025-08-13 14:17:10'),
(25, 'Joseph Mrema', 'joseph.mrema@example.com', '0071755555', 1, '2025-08-14 09:56:35', '2025-08-14 09:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `categoryId` bigint(20) UNSIGNED NOT NULL,
  `categoryName` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`categoryId`, `categoryName`, `created_at`, `updated_at`) VALUES
(1, 'Electronic', '2025-08-12 17:35:03', '2025-08-13 21:10:20'),
(4, 'Home Appliances', '2025-08-12 17:35:03', '2025-08-12 17:35:03'),
(6, 'Health & Beauty', '2025-08-12 17:35:03', '2025-08-12 17:35:03');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customerId` bigint(20) UNSIGNED NOT NULL,
  `customerName` varchar(255) DEFAULT NULL,
  `customerEmail` varchar(255) DEFAULT NULL,
  `customerPhone` varchar(255) DEFAULT NULL,
  `customerAddress` varchar(255) DEFAULT NULL,
  `customerPhoto` varchar(255) DEFAULT NULL,
  `customerAccountHolder` varchar(255) DEFAULT NULL,
  `customerAccountNumber` varchar(255) DEFAULT NULL,
  `bankName` varchar(255) DEFAULT NULL,
  `customerStatus` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1 = Active, 0 = Inactive',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customerId`, `customerName`, `customerEmail`, `customerPhone`, `customerAddress`, `customerPhoto`, `customerAccountHolder`, `customerAccountNumber`, `bankName`, `customerStatus`, `created_at`, `updated_at`) VALUES
(4, 'Peter Mrema', 'peter.mrema@example.com', '255715678004', 'Njombe Central', 'peter.jpg', 'Peter Mrema', '01100456789', 'CRDB Bank', 1, '2025-08-12 14:02:14', '2025-08-12 14:02:14'),
(6, 'Josephine Komba', 'josephine.komba@example.com', '255717890006', 'Mbinga', 'josephine.jpg', 'Josephine Komba', '01100678901', 'NBC Bank', 1, '2025-08-12 14:02:14', '2025-08-12 14:02:14'),
(7, 'Michael Mussa', 'michael.mussa@example.com', '255718901007', 'Lindi Street', 'michael.jpg', 'Michael Mussa', '01100789012', 'CRDB Bank', 1, '2025-08-12 14:02:14', '2025-08-12 14:02:14'),
(8, 'Grace Nnko', 'grace.nnko@example.com', '255719012008', 'Iringa Town', 'grace.jpg', 'Grace Nnko', '01100890123', 'NMB Bank', 1, '2025-08-12 14:02:14', '2025-08-12 14:02:14'),
(9, 'David Kweka', 'david.kweka@example.com', '255710123009', 'Dodoma Uzunguni', 'david.jpg', 'David Kweka', '01100901234', 'NBC Bank', 1, '2025-08-12 14:02:14', '2025-08-12 14:02:14'),
(11, 'Edd & Sons Ltd.', 'edsona@example.com', '0679799406', 'Dar Es Salaam, Tanzania', NULL, 'Edgar Charles', '0989897867565', 'NMB Bank', 1, '2025-08-12 14:56:00', '2025-08-20 15:27:00'),
(12, 'Charles', 'mainda@gmail.com', '0766987878', 'Mbeya, Tanzania', NULL, 'Chale Mainda', '54565656757', 'NMB', 1, '2025-08-13 17:34:54', '2025-08-13 17:34:54');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `orderUId` bigint(20) UNSIGNED NOT NULL,
  `invoiceNumber` varchar(255) NOT NULL,
  `customerId` bigint(20) UNSIGNED NOT NULL,
  `createdBy` bigint(20) UNSIGNED NOT NULL,
  `updatedBy` bigint(20) UNSIGNED NOT NULL,
  `orderDate` date NOT NULL,
  `totalProducts` int(11) NOT NULL,
  `subTotal` decimal(10,2) NOT NULL,
  `vat` int(11) NOT NULL,
  `vatAmount` decimal(10,2) NOT NULL,
  `discount` int(11) NOT NULL,
  `discountAmount` decimal(10,2) NOT NULL,
  `shippingAmount` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `paymentType` varchar(255) NOT NULL,
  `paid` decimal(10,2) NOT NULL,
  `due` decimal(10,2) NOT NULL,
  `orderStatus` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 = Pending, 1 = Completed, 2 = Cancelled',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`orderUId`, `invoiceNumber`, `customerId`, `createdBy`, `updatedBy`, `orderDate`, `totalProducts`, `subTotal`, `vat`, `vatAmount`, `discount`, `discountAmount`, `shippingAmount`, `total`, `paymentType`, `paid`, `due`, `orderStatus`, `created_at`, `updated_at`) VALUES
(27, 'SNK-S001', 6, 10, 9, '2025-08-28', 7, 6114110.00, 18, 0.00, 0, 0.00, 0.00, 7214649.80, 'Cash', 3540.00, 7211109.80, 0, '2025-08-21 18:50:26', '2025-09-01 12:08:42'),
(28, 'SNK-S002', 4, 10, 9, '2025-08-28', 3, 4500.00, 0, 0.00, 0, 0.00, 0.00, 4500.00, 'Cash', 4500.00, 0.00, 1, '2025-08-21 19:17:53', '2025-09-01 12:27:46'),
(30, 'SNK-S004', 4, 10, 9, '2025-08-22', 4, 6000.00, 0, 0.00, 0, 0.00, 0.00, 6000.00, 'Cash', 6000.00, 0.00, 1, '2025-08-21 19:21:57', '2025-08-28 17:10:07'),
(33, 'SNK-S005', 4, 10, 9, '2025-08-26', 5, 7500.00, 0, 0.00, 0, 0.00, 0.00, 7500.00, 'Cash', 7500.00, 0.00, 1, '2025-08-26 11:53:50', '2025-08-28 12:13:58'),
(34, 'SNK-S006', 4, 9, 9, '2025-08-28', 2, 3000.00, 18, 0.00, 0, 0.00, 0.00, 3540.00, 'Cash', 3540.00, 0.00, 1, '2025-08-28 10:16:03', '2025-08-28 12:06:02'),
(35, 'SNK-S007', 4, 9, 9, '2025-08-28', 5, 7500.00, 0, 0.00, 0, 0.00, 0.00, 7500.00, 'Credit Card', 6000.00, 1500.00, 2, '2025-08-28 13:30:44', '2025-09-01 12:11:37'),
(36, 'SNK-S008', 8, 9, 9, '2025-08-28', 1, 1500.00, 18, 0.00, 0, 0.00, 0.00, 1770.00, 'Cash', 1770.00, 0.00, 1, '2025-08-28 16:16:10', '2025-08-28 16:16:10'),
(37, 'SNK-S009', 8, 9, 9, '2025-08-28', 1, 1500.00, 18, 0.00, 0, 0.00, 0.00, 1770.00, 'Cash', 1770.00, 0.00, 1, '2025-08-28 16:17:45', '2025-08-28 17:17:56'),
(38, 'SNK-S010', 8, 9, 9, '2025-08-28', 9, 4896388.00, 18, 0.00, 0, 0.00, 0.00, 5777737.84, 'Cash', 2887983.92, 2889753.92, 2, '2025-08-28 17:23:48', '2025-09-01 12:09:32'),
(39, 'SNK-S011', 8, 9, 9, '2025-08-28', 6, 9000.00, 20, 0.00, 0, 0.00, 0.00, 10800.00, 'Cash', 1080.00, 9720.00, 0, '2025-08-28 18:56:43', '2025-08-31 16:21:47'),
(40, 'SNK-S012', 9, 9, 9, '2025-08-29', 5, 7500.00, 18, 0.00, 0, 0.00, 0.00, 8850.00, 'Cash', 100.00, 8750.00, 2, '2025-08-29 14:00:11', '2025-08-29 14:02:12'),
(42, 'SNK-S013', 7, 9, 9, '2025-09-02', 9, 81000.00, 18, 0.00, 0, 0.00, 0.00, 95580.00, 'Cash', 95580.00, 0.00, 1, '2025-09-02 13:27:06', '2025-09-02 13:41:48'),
(43, 'SNK-S014', 6, 10, 10, '2025-09-02', 15, 240000.00, 18, 43200.00, 10, 24000.00, 15000.00, 259200.00, 'Cash', 0.00, 259200.00, 0, '2025-09-02 16:58:14', '2025-09-02 16:58:14'),
(44, 'SNK-S015', 6, 10, 10, '2025-09-02', 23, 142500.00, 0, 0.00, 0, 0.00, 0.00, 142500.00, 'Cash', 0.00, 142500.00, 2, '2025-09-02 17:00:31', '2025-09-02 17:02:22');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `orderDetailsId` bigint(20) UNSIGNED NOT NULL,
  `invoiceNumber` varchar(255) NOT NULL,
  `productId` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unitCost` decimal(10,2) NOT NULL,
  `totalCost` decimal(10,2) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1 = Paid, 0 = Unpaid',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`orderDetailsId`, `invoiceNumber`, `productId`, `quantity`, `unitCost`, `totalCost`, `status`, `created_at`, `updated_at`) VALUES
(52, 'SNK-S005', 23, 5, 1500.00, 7500.00, 1, '2025-08-26 11:53:50', '2025-08-26 11:53:50'),
(57, 'SNK-S006', 23, 2, 1500.00, 3000.00, 1, '2025-08-28 10:16:03', '2025-08-28 10:16:03'),
(94, 'SNK-S008', 23, 1, 1500.00, 1500.00, 1, '2025-08-28 16:16:10', '2025-08-28 16:16:10'),
(113, 'SNK-S004', 23, 4, 1500.00, 6000.00, 1, '2025-08-28 17:05:22', '2025-08-28 17:05:22'),
(118, 'SNK-S009', 23, 1, 1500.00, 1500.00, 0, '2025-08-28 17:17:56', '2025-08-28 17:17:56'),
(123, 'SNK-S011', 23, 6, 1500.00, 9000.00, 0, '2025-08-28 18:57:46', '2025-08-28 18:57:46'),
(124, 'SNK-S012', 23, 5, 1500.00, 7500.00, 0, '2025-08-29 14:00:11', '2025-08-29 14:00:11'),
(127, 'SNK-S010', 23, 4, 1500.00, 6000.00, 0, '2025-09-01 10:37:25', '2025-09-01 10:37:25'),
(128, 'SNK-S010', 27, 4, 1222222.00, 4888888.00, 0, '2025-09-01 10:37:25', '2025-09-01 10:37:25'),
(129, 'SNK-S010', 23, 1, 1500.00, 1500.00, 0, '2025-09-01 10:37:25', '2025-09-01 10:37:25'),
(130, 'SNK-S001', 23, 2, 1500.00, 3000.00, 0, '2025-09-01 12:08:42', '2025-09-01 12:08:42'),
(131, 'SNK-S001', 27, 5, 1222222.00, 6111110.00, 0, '2025-09-01 12:08:42', '2025-09-01 12:08:42'),
(132, 'SNK-S002', 23, 3, 1500.00, 4500.00, 1, '2025-09-01 12:09:19', '2025-09-01 12:09:19'),
(133, 'SNK-S007', 23, 5, 1500.00, 7500.00, 0, '2025-09-01 12:10:41', '2025-09-01 12:10:41'),
(136, 'SNK-S013', 23, 4, 1500.00, 6000.00, 1, '2025-09-02 13:40:52', '2025-09-02 13:40:52'),
(137, 'SNK-S013', 27, 5, 15000.00, 75000.00, 1, '2025-09-02 13:40:52', '2025-09-02 13:40:52'),
(138, 'SNK-S014', 27, 15, 15000.00, 225000.00, 0, '2025-09-02 16:58:14', '2025-09-02 16:58:14'),
(139, 'SNK-S015', 23, 10, 1500.00, 15000.00, 0, '2025-09-02 17:00:31', '2025-09-02 17:00:31'),
(140, 'SNK-S015', 27, 8, 15000.00, 120000.00, 0, '2025-09-02 17:00:31', '2025-09-02 17:00:31'),
(141, 'SNK-S015', 23, 5, 1500.00, 7500.00, 0, '2025-09-02 17:00:31', '2025-09-02 17:00:31');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `productId` bigint(20) UNSIGNED NOT NULL,
  `categoryId` bigint(20) UNSIGNED NOT NULL,
  `unitId` bigint(20) UNSIGNED NOT NULL,
  `productName` varchar(255) NOT NULL,
  `productType` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `buyingPrice` decimal(10,2) NOT NULL,
  `sellingPrice` decimal(10,2) NOT NULL,
  `quantityAlert` int(11) DEFAULT NULL,
  `tax` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `productStatus` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=OutOfStock, 1=Available, 2=LowStock',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`productId`, `categoryId`, `unitId`, `productName`, `productType`, `quantity`, `buyingPrice`, `sellingPrice`, `quantityAlert`, `tax`, `notes`, `productStatus`, `created_at`, `updated_at`) VALUES
(23, 1, 2, 'Battery', '0', 46, 1200.00, 1500.00, 2, 2, 'Battery', 1, '2025-08-19 11:43:10', '2025-09-02 17:02:22'),
(27, 1, 1, 'Meter Battery', 'Samsung', 40, 12000.00, 15000.00, 12, 12, '', 1, '2025-08-28 10:01:31', '2025-09-02 17:02:22');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `purchaseUId` int(11) NOT NULL,
  `purchaseNumber` varchar(255) NOT NULL,
  `supplierId` bigint(20) UNSIGNED NOT NULL,
  `createdBy` bigint(20) UNSIGNED NOT NULL,
  `updatedBy` bigint(20) UNSIGNED NOT NULL,
  `purchaseDate` date NOT NULL,
  `totalProducts` int(11) NOT NULL,
  `totalAmount` decimal(10,2) NOT NULL,
  `purchaseStatus` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 = Pending, 1 = Completed, 2 = Cancelled',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`purchaseUId`, `purchaseNumber`, `supplierId`, `createdBy`, `updatedBy`, `purchaseDate`, `totalProducts`, `totalAmount`, `purchaseStatus`, `created_at`, `updated_at`) VALUES
(13, 'SNK-P001', 5, 10, 10, '2025-08-26', 10, 62505000.00, 1, '2025-08-26 11:11:47', '2025-09-02 17:39:20'),
(14, 'SNK-P002', 5, 10, 10, '2025-08-26', 1, 1000000.00, 1, '2025-08-26 11:23:40', '2025-09-02 17:42:37'),
(15, 'SNK-P003', 5, 10, 9, '2025-08-26', 8, 10502200.00, 1, '2025-08-26 11:36:57', '2025-09-01 12:16:36'),
(16, 'SNK-P004', 1004, 9, 9, '2025-08-27', 12, 60006000.00, 1, '2025-08-27 10:29:40', '2025-08-28 18:15:58'),
(18, 'SNK-P005', 5, 9, 9, '2025-08-28', 12, 24000000.00, 1, '2025-08-28 18:43:09', '2025-08-28 18:43:55'),
(19, 'SNK-P006', 5, 9, 9, '2025-08-28', 29, 29005000.00, 2, '2025-08-28 18:54:09', '2025-09-01 12:13:50'),
(20, 'SNK-P007', 1005, 9, 9, '2025-09-01', 20, 35000000.00, 0, '2025-09-01 12:12:39', '2025-09-01 12:12:39'),
(21, 'SNK-P008', 5, 9, 9, '2025-09-01', 25, 99999999.99, 0, '2025-09-01 12:31:40', '2025-09-01 12:32:20');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_details`
--

CREATE TABLE `purchase_details` (
  `purchaseDetailsId` bigint(20) UNSIGNED NOT NULL,
  `purchaseNumber` varchar(255) NOT NULL,
  `productId` bigint(20) UNSIGNED NOT NULL,
  `agentId` bigint(20) UNSIGNED DEFAULT NULL,
  `trackingNumber` varchar(255) DEFAULT NULL,
  `productSize` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unitCost` decimal(10,2) NOT NULL,
  `rate` int(11) NOT NULL,
  `totalCost` decimal(10,2) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1 = Completed, 0 = Pending, 2= Cancelled ',
  `agentTransportationCost` decimal(10,2) DEFAULT NULL,
  `dateToAgentAbroadWarehouse` date DEFAULT NULL,
  `dateReceivedByAgentInCountryWarehouse` date DEFAULT NULL,
  `dateReceivedByCompany` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_details`
--

INSERT INTO `purchase_details` (`purchaseDetailsId`, `purchaseNumber`, `productId`, `agentId`, `trackingNumber`, `productSize`, `quantity`, `unitCost`, `rate`, `totalCost`, `status`, `agentTransportationCost`, `dateToAgentAbroadWarehouse`, `dateReceivedByAgentInCountryWarehouse`, `dateReceivedByCompany`, `created_at`, `updated_at`) VALUES
(40, 'SNK-P004', 23, 22, '20260827', NULL, 12, 2000.00, 2500, 60000000.00, 1, 6000.00, NULL, NULL, NULL, '2025-08-27 10:33:40', '2025-08-27 10:33:40'),
(44, 'SNK-P005', 23, 24, '', NULL, 12, 2000.00, 1000, 24000000.00, 1, 0.00, NULL, NULL, NULL, '2025-08-28 18:43:09', '2025-08-28 18:43:55'),
(58, 'SNK-P006', 23, 22, '2025082802', NULL, 14, 1000.00, 1000, 14000000.00, 2, 5000.00, NULL, NULL, NULL, '2025-09-01 10:03:39', '2025-09-01 12:13:50'),
(59, 'SNK-P006', 27, 22, '2025082802', NULL, 14, 1000.00, 1000, 14000000.00, 2, 5000.00, NULL, NULL, NULL, '2025-09-01 10:03:39', '2025-09-01 12:13:50'),
(60, 'SNK-P006', 23, 22, '2025082802', NULL, 1, 1000.00, 1000, 1000000.00, 2, 5000.00, NULL, NULL, NULL, '2025-09-01 10:03:39', '2025-09-01 12:13:50'),
(61, 'SNK-P007', 23, 22, '', NULL, 10, 2000.00, 1000, 20000000.00, 1, 0.00, NULL, NULL, NULL, '2025-09-01 12:12:39', '2025-09-01 12:12:39'),
(62, 'SNK-P007', 27, 22, '', NULL, 10, 1500.00, 1000, 15000000.00, 1, 0.00, NULL, NULL, NULL, '2025-09-01 12:12:39', '2025-09-01 12:12:39'),
(63, 'SNK-P003', 23, 10, '', NULL, 1, 100.00, 22, 2200.00, 1, 0.00, NULL, NULL, NULL, '2025-09-01 12:16:36', '2025-09-01 12:16:36'),
(64, 'SNK-P003', 27, 10, '', NULL, 7, 1500.00, 1000, 10500000.00, 1, 0.00, NULL, NULL, NULL, '2025-09-01 12:16:36', '2025-09-01 12:16:36'),
(67, 'SNK-P008', 23, 10, '', NULL, 10, 3000.00, 2500, 75000000.00, 1, 0.00, '2025-09-01', '2025-09-01', '2025-09-01', '2025-09-01 12:32:20', '2025-09-01 12:32:20'),
(68, 'SNK-P008', 27, 10, '', NULL, 15, 2500.00, 2500, 93750000.00, 1, 0.00, '2025-09-01', '2025-09-01', '2025-09-01', '2025-09-01 12:32:20', '2025-09-01 12:32:20'),
(69, 'SNK-P001', 23, 10, '', '2', 10, 2500.00, 2500, 62500000.00, 1, 5000.00, NULL, NULL, NULL, '2025-09-02 17:39:20', '2025-09-02 17:39:20'),
(70, 'SNK-P002', 23, 20, '2025082801', '', 1, 1000.00, 1000, 1000000.00, 1, 0.00, NULL, NULL, NULL, '2025-09-02 17:42:37', '2025-09-02 17:42:37');

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `quotationUId` bigint(20) UNSIGNED NOT NULL,
  `referenceNumber` varchar(255) NOT NULL,
  `customerId` bigint(20) UNSIGNED NOT NULL,
  `createdBy` bigint(20) UNSIGNED NOT NULL,
  `updatedBy` bigint(20) UNSIGNED NOT NULL,
  `quotationDate` date NOT NULL,
  `totalProducts` int(11) NOT NULL,
  `subTotal` decimal(10,2) NOT NULL,
  `taxPercentage` int(11) NOT NULL,
  `taxAmount` decimal(10,2) NOT NULL,
  `discountPercentage` int(11) NOT NULL,
  `discountAmount` decimal(10,2) NOT NULL,
  `shippingAmount` decimal(10,2) NOT NULL,
  `totalAmount` decimal(10,2) NOT NULL,
  `note` text DEFAULT NULL,
  `quotationStatus` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 = Sent, 1 = Approved, 2 = Cancelled',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`quotationUId`, `referenceNumber`, `customerId`, `createdBy`, `updatedBy`, `quotationDate`, `totalProducts`, `subTotal`, `taxPercentage`, `taxAmount`, `discountPercentage`, `discountAmount`, `shippingAmount`, `totalAmount`, `note`, `quotationStatus`, `created_at`, `updated_at`) VALUES
(5, 'SNK-RF005', 6, 10, 10, '2025-08-29', 21, 18342330.00, 18, 3301619.40, 0, 0.00, 15000.00, 21658949.40, '', 1, '2025-08-29 10:14:01', '2025-09-02 16:58:14'),
(7, 'SNK-RF007', 6, 10, 10, '2025-08-29', 13, 7343832.00, 18, 1321889.76, 2, 146876.64, 10000.00, 8528845.12, '', 0, '2025-08-29 11:07:32', '2025-09-01 11:03:34'),
(8, 'SNK-RF008', 6, 10, 10, '2025-09-01', 23, 9800276.00, 0, 0.00, 0, 0.00, 0.00, 9800276.00, '', 1, '2025-09-01 10:19:48', '2025-09-02 17:00:31'),
(9, 'SNK-RF009', 7, 9, 9, '2025-09-01', 4, 6000.00, 18, 1080.00, 2, 120.00, 1200.00, 8160.00, '', 1, '2025-09-01 12:35:05', '2025-09-02 13:27:06');

-- --------------------------------------------------------

--
-- Table structure for table `quotation_details`
--

CREATE TABLE `quotation_details` (
  `quotationDetailsId` bigint(20) UNSIGNED NOT NULL,
  `referenceNumber` varchar(255) NOT NULL,
  `productId` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unitPrice` decimal(10,2) NOT NULL,
  `subTotal` decimal(10,2) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 = Sent, 1 = Approved, 2 = Cancelled ',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotation_details`
--

INSERT INTO `quotation_details` (`quotationDetailsId`, `referenceNumber`, `productId`, `quantity`, `unitPrice`, `subTotal`, `status`, `created_at`, `updated_at`) VALUES
(30, 'SNK-RF007', 27, 6, 1222222.00, 7333332.00, 0, '2025-09-01 10:14:40', '2025-09-01 11:03:34'),
(31, 'SNK-RF005', 27, 15, 1222222.00, 18333330.00, 1, '2025-09-01 10:16:20', '2025-09-02 16:58:14'),
(39, 'SNK-RF008', 23, 10, 1500.00, 15000.00, 1, '2025-09-01 10:33:45', '2025-09-02 17:00:31'),
(40, 'SNK-RF008', 27, 8, 1222222.00, 9777776.00, 1, '2025-09-01 10:33:45', '2025-09-02 17:00:31'),
(41, 'SNK-RF008', 23, 5, 1500.00, 7500.00, 1, '2025-09-01 10:33:45', '2025-09-02 17:00:31'),
(42, 'SNK-RF009', 23, 4, 1500.00, 6000.00, 1, '2025-09-01 12:35:05', '2025-09-02 13:27:06');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplierId` bigint(20) UNSIGNED NOT NULL,
  `supplierName` varchar(255) NOT NULL,
  `supplierEmail` varchar(255) DEFAULT NULL,
  `supplierPhone` varchar(255) DEFAULT NULL,
  `supplierAddress` varchar(255) DEFAULT NULL,
  `shopName` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `supplierPhoto` varchar(255) DEFAULT NULL,
  `supplierAccountHolder` varchar(255) DEFAULT NULL,
  `supplierAccountNumber` varchar(255) DEFAULT NULL,
  `bankName` varchar(255) DEFAULT NULL,
  `supplierStatus` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1 = Active, 0 = Inactive',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplierId`, `supplierName`, `supplierEmail`, `supplierPhone`, `supplierAddress`, `shopName`, `type`, `supplierPhoto`, `supplierAccountHolder`, `supplierAccountNumber`, `bankName`, `supplierStatus`, `created_at`, `updated_at`) VALUES
(5, 'Bahati Traders', 'bahati.traders@example.com', '0716789012', 'Maji Street, Mbinga', 'Bahati Hardware', 'Hardware', NULL, 'Bahati Peter', '05567890123', 'NMB Bank', 1, NULL, '2025-08-12 12:02:07'),
(7, 'Mwanzo Foods', 'mwanzo.foods@example.com', '255718901234', 'Kisutu, Dar es Salaam', 'Mwanzo Foods', 'Groceries', NULL, 'Mwanzo Ally', '07789012345', 'CRDB Bank', 1, NULL, NULL),
(8, 'Twende Motors', 'twende.motors@example.com', '255719012345', 'Industrial Area, Morogoro', 'Twende Motors', 'Automotive', NULL, 'Twende Mussa', '08890123456', 'NMB Bank', 1, NULL, NULL),
(9, 'Zawadi Decor', 'zawadi.decor@example.com', '255710123456', 'Uzunguni, Dodoma', 'Zawadi Decor', 'Furniture', NULL, 'Zawadi Grace', '09901234567', 'NBC Bank', 1, NULL, NULL),
(1004, 'Ahati traders', 'simwe@gmail.com', '0679799406', 'Mwanza, Tanzania', 'Bahati hardware', 'Hardware', NULL, 'Bahati peter', '05567890123', 'CRDB', 1, '2025-08-12 13:48:49', '2025-08-12 15:41:21'),
(1005, 'Daud', 'james@gmail.com', '0898911111', 'Mwanza, Tanzania', 'Jaime', 'wholesaler', NULL, 'Jaime James', '8987867564534', 'CRDB', 1, '2025-08-13 17:22:08', '2025-08-13 17:22:08');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `unitId` bigint(20) UNSIGNED NOT NULL,
  `unitName` varchar(255) NOT NULL,
  `unitShortCode` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`unitId`, `unitName`, `unitShortCode`, `created_at`, `updated_at`) VALUES
(1, 'Piece', 'pcs', '2025-08-13 10:01:56', '2025-08-13 21:16:09'),
(2, 'Kilogram', 'kg', '2025-08-13 10:01:56', NULL),
(3, 'Gram', 'g', '2025-08-13 10:01:56', NULL),
(4, 'Liter', 'L', '2025-08-13 10:01:56', NULL),
(5, 'Milliliter', 'ml', '2025-08-13 10:01:56', NULL),
(8, 'Dozen', 'doz', '2025-08-13 10:01:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userId` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `userPhone` varchar(255) NOT NULL,
  `userEmail` varchar(255) NOT NULL,
  `userRole` varchar(255) NOT NULL,
  `userPassword` varchar(255) NOT NULL,
  `userPhoto` varchar(255) DEFAULT NULL,
  `userStatus` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1 = Active, 0 = Inactive',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userId`, `username`, `userPhone`, `userEmail`, `userRole`, `userPassword`, `userPhoto`, `userStatus`, `created_at`, `updated_at`) VALUES
(9, 'Edgar Charles', '0679799407', 'edgarcharles360@gmail.com', 'Admin', '$2y$10$O4LJRq2mM9JW.3SDDlPo2OjraHo8XmTQYs8xeqUSMAteEvDUIBhq6', 'assets/img/profiles/1756742750_IMG_20240120_003917.jpg', 1, '2025-08-12 09:35:09', '2025-08-26 10:10:55'),
(10, 'Asimwe Bitegeko', '0679799407', 'asimwe@gmail.com', 'Admin', '$2y$10$7J9aTT.bf5FvxQKzkZnLi.XDx7OHoaN.CRvlhjv4rYJpiQ2Ncq7wG', NULL, 1, '2025-08-12 09:35:33', '2025-08-20 15:28:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`agentId`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`categoryId`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customerId`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`invoiceNumber`),
  ADD UNIQUE KEY `orderUId` (`orderUId`),
  ADD KEY `orders_ibfk_1` (`customerId`),
  ADD KEY `orders_ibfk_2` (`createdBy`),
  ADD KEY `orders_ibfk_3` (`updatedBy`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`orderDetailsId`),
  ADD KEY `order_details_ibfk_1` (`invoiceNumber`),
  ADD KEY `order_details_ibfk_2` (`productId`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`productId`),
  ADD KEY `products_ibfk_1` (`categoryId`),
  ADD KEY `products_ibfk_2` (`unitId`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`purchaseNumber`),
  ADD UNIQUE KEY `purchaseUId` (`purchaseUId`),
  ADD KEY `purchases_ibfk_1` (`supplierId`),
  ADD KEY `purchases_ibfk_2` (`createdBy`),
  ADD KEY `purchases_ibfk_3` (`updatedBy`);

--
-- Indexes for table `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD PRIMARY KEY (`purchaseDetailsId`),
  ADD KEY `purchase_details_ibfk_1` (`purchaseNumber`),
  ADD KEY `purchase_details_ibfk_2` (`productId`),
  ADD KEY `purchase_details_ibfk_3` (`agentId`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`referenceNumber`),
  ADD UNIQUE KEY `quotationUId` (`quotationUId`),
  ADD KEY `quotations_ibfk_1` (`customerId`),
  ADD KEY `quotations_ibfk_2` (`createdBy`),
  ADD KEY `quotations_ibfk_3` (`updatedBy`);

--
-- Indexes for table `quotation_details`
--
ALTER TABLE `quotation_details`
  ADD PRIMARY KEY (`quotationDetailsId`),
  ADD KEY `quotation_details_ibfk_1` (`referenceNumber`),
  ADD KEY `quotation_details_ibfk_2` (`productId`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplierId`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`unitId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `agentId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `categoryId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=224;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customerId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `orderUId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `orderDetailsId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `productId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchaseUId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `purchase_details`
--
ALTER TABLE `purchase_details`
  MODIFY `purchaseDetailsId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `quotationUId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `quotation_details`
--
ALTER TABLE `quotation_details`
  MODIFY `quotationDetailsId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplierId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1006;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `unitId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customerId`) REFERENCES `customers` (`customerId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`createdBy`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`updatedBy`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`invoiceNumber`) REFERENCES `orders` (`invoiceNumber`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`productId`) REFERENCES `products` (`productId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`categoryId`) REFERENCES `categories` (`categoryId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`unitId`) REFERENCES `units` (`unitId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`supplierId`) REFERENCES `suppliers` (`supplierId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`createdBy`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchases_ibfk_3` FOREIGN KEY (`updatedBy`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD CONSTRAINT `purchase_details_ibfk_1` FOREIGN KEY (`purchaseNumber`) REFERENCES `purchases` (`purchaseNumber`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_details_ibfk_2` FOREIGN KEY (`productId`) REFERENCES `products` (`productId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_details_ibfk_3` FOREIGN KEY (`agentId`) REFERENCES `agents` (`agentId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `quotations_ibfk_1` FOREIGN KEY (`customerId`) REFERENCES `customers` (`customerId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `quotations_ibfk_2` FOREIGN KEY (`createdBy`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `quotations_ibfk_3` FOREIGN KEY (`updatedBy`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quotation_details`
--
ALTER TABLE `quotation_details`
  ADD CONSTRAINT `quotation_details_ibfk_1` FOREIGN KEY (`referenceNumber`) REFERENCES `quotations` (`referenceNumber`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `quotation_details_ibfk_2` FOREIGN KEY (`productId`) REFERENCES `products` (`productId`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
