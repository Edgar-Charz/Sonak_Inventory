-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 27, 2025 at 10:16 AM
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

INSERT INTO `orders` (`orderUId`, `invoiceNumber`, `customerId`, `createdBy`, `updatedBy`, `orderDate`, `totalProducts`, `subTotal`, `vat`, `total`, `paymentType`, `paid`, `due`, `orderStatus`, `created_at`, `updated_at`) VALUES
(21, '1212121233', 4, 10, 10, '2025-08-21', 3, 4500.00, 0, 4500.00, '', 0.00, 4500.00, 1, '2025-08-21 17:36:33', '2025-08-21 17:36:33'),
(24, '12121212nn', 4, 10, 10, '2025-08-21', 6, 9000.00, 0, 9000.00, '', 0.00, 9000.00, 0, '2025-08-21 18:10:21', '2025-08-21 18:10:21'),
(23, '12133', 4, 10, 10, '2025-08-21', 1, 2500.00, 0, 2500.00, '', 0.00, 2500.00, 1, '2025-08-21 18:08:24', '2025-08-21 18:08:24'),
(16, 'ABCH3005', 7, 9, 9, '2025-08-21', 4, 6000.00, 17, 7020.00, 'Credit Card', 6000.00, 1020.00, 0, NULL, '2025-08-27 10:01:20'),
(17, 'ABCH30051', 7, 9, 9, '2025-08-21', 10, 19000.00, 18, 22420.00, 'Cash', 10000.00, 12420.00, 0, '2025-08-20 10:08:58', '2025-08-20 10:08:58'),
(14, 'ASSS12', 7, 9, 9, '2025-08-21', 1, 1500.00, 0, 1500.00, 'Cash', 0.00, 1500.00, 1, NULL, NULL),
(27, 'SNK-S001', 6, 10, 10, '0000-00-00', 1, 1500.00, 0, 1500.00, '', 0.00, 1500.00, 0, '2025-08-21 18:50:26', '2025-08-21 18:50:26'),
(28, 'SNK-S002', 4, 10, 10, '0000-00-00', 2, 3000.00, 0, 3000.00, '', 0.00, 3000.00, 0, '2025-08-21 19:17:53', '2025-08-21 19:17:53'),
(30, 'SNK-S004', 4, 10, 10, '2025-08-22', 4, 6000.00, 0, 6000.00, '', 0.00, 6000.00, 0, '2025-08-21 19:21:57', '2025-08-21 19:21:57'),
(33, 'SNK-S005', 4, 10, 10, '2025-08-26', 5, 7500.00, 0, 7500.00, 'Cash', 0.00, 7500.00, 1, '2025-08-26 11:53:50', '2025-08-26 11:53:50');

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
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1 = Paid, 0 = Unpaid',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`orderDetailsId`, `invoiceNumber`, `productId`, `quantity`, `unitCost`, `totalCost`, `status`, `created_at`, `updated_at`) VALUES
(24, 'ABCH30051', 23, 6, 1500.00, 9000.00, 1, '2025-08-20 10:08:58', '2025-08-20 10:08:58'),
(38, '1212121233', 23, 3, 1500.00, 4500.00, 1, '2025-08-21 17:36:33', '2025-08-21 17:36:33'),
(41, '12121212nn', 23, 6, 1500.00, 9000.00, 1, '2025-08-21 18:10:21', '2025-08-21 18:10:21'),
(44, 'SNK-S001', 23, 1, 1500.00, 1500.00, 1, '2025-08-21 18:50:26', '2025-08-21 18:50:26'),
(45, 'SNK-S002', 23, 2, 1500.00, 3000.00, 1, '2025-08-21 19:17:53', '2025-08-21 19:17:53'),
(47, 'SNK-S004', 23, 4, 1500.00, 6000.00, 1, '2025-08-21 19:21:57', '2025-08-21 19:21:57'),
(52, 'SNK-S005', 23, 5, 1500.00, 7500.00, 1, '2025-08-26 11:53:50', '2025-08-26 11:53:50'),
(55, 'ABCH3005', 23, 2, 1500.00, 3000.00, 1, '2025-08-27 10:01:20', '2025-08-27 10:01:20'),
(56, 'ABCH3005', 23, 2, 1500.00, 3000.00, 1, '2025-08-27 10:01:20', '2025-08-27 10:01:20');

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
(23, 1, 2, 'Battery', 'Brand', 43, 1200.00, 1500.00, 2, 2, 'Battery', 1, '2025-08-19 11:43:10', '2025-08-27 10:01:20');

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
(13, 'SNK-P001', 5, 10, 10, '2025-08-26', 10, 62500000.00, 1, '2025-08-26 11:11:47', '2025-08-26 11:11:47'),
(14, 'SNK-P002', 5, 10, 10, '2025-08-26', 1, 1000000.00, 1, '2025-08-26 11:23:40', '2025-08-26 11:23:40'),
(15, 'SNK-P003', 5, 10, 10, '2025-08-26', 1, 2200.00, 1, '2025-08-26 11:36:57', '2025-08-26 11:36:57'),
(16, 'SNK-P004', 1004, 9, 9, '2025-08-27', 12, 60006000.00, 0, '2025-08-27 10:29:40', '2025-08-27 10:33:40');

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
(36, 'SNK-P001', 23, NULL, '', NULL, 10, 2500.00, 2500, 62500000.00, 1, 0.00, NULL, NULL, NULL, '2025-08-26 11:11:47', '2025-08-26 11:11:47'),
(37, 'SNK-P002', 23, 20, '', NULL, 1, 1000.00, 1000, 1000000.00, 1, 0.00, NULL, NULL, NULL, '2025-08-26 11:23:40', '2025-08-26 11:23:40'),
(38, 'SNK-P003', 23, NULL, '', NULL, 1, 100.00, 22, 2200.00, 1, 0.00, NULL, NULL, NULL, '2025-08-26 11:36:57', '2025-08-26 11:36:57'),
(40, 'SNK-P004', 23, 22, '20260827', NULL, 12, 2000.00, 2500, 60000000.00, 1, 6000.00, NULL, NULL, NULL, '2025-08-27 10:33:40', '2025-08-27 10:33:40');

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
  `quotationStatus` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 = Pending, 1 = Completed, 2 = Cancelled',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`quotationUId`, `referenceNumber`, `customerId`, `createdBy`, `updatedBy`, `quotationDate`, `totalProducts`, `subTotal`, `taxPercentage`, `taxAmount`, `discountPercentage`, `discountAmount`, `shippingAmount`, `totalAmount`, `note`, `quotationStatus`, `created_at`, `updated_at`) VALUES
(4, 'SNK-RF004', 7, 10, 10, '2025-08-22', 19, 37500.00, 18, 6750.00, 10, 3750.00, 10000.00, 50500.00, '', 1, '2025-08-22 18:47:53', '2025-08-22 18:47:53');

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
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1 = , 0 = ',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotation_details`
--

INSERT INTO `quotation_details` (`quotationDetailsId`, `referenceNumber`, `productId`, `quantity`, `unitPrice`, `subTotal`, `status`, `created_at`, `updated_at`) VALUES
(5, 'SNK-RF004', 23, 10, 1500.00, 15000.00, 1, '2025-08-22 18:47:53', '2025-08-22 18:47:53');

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
(9, 'Edgar Mainda', '0679799406', 'edgarcharles360@gmail.com', 'Admin', '$2y$10$O4LJRq2mM9JW.3SDDlPo2OjraHo8XmTQYs8xeqUSMAteEvDUIBhq6', NULL, 1, '2025-08-12 09:35:09', '2025-08-26 10:10:55'),
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
  MODIFY `orderUId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `orderDetailsId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `productId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchaseUId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `purchase_details`
--
ALTER TABLE `purchase_details`
  MODIFY `purchaseDetailsId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `quotationUId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `quotation_details`
--
ALTER TABLE `quotation_details`
  MODIFY `quotationDetailsId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
