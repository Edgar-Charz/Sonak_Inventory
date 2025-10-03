-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 03, 2025 at 10:32 AM
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
(1, 'Michael Mussa', 'michael.mussa@example.com', '255711234010', 1, '2025-09-17 18:19:00', '2025-09-24 12:19:22'),
(3, 'John Mushi', 'john@example.com', '255711234010', 1, '2025-09-18 18:37:43', '2025-09-19 10:33:32');

-- --------------------------------------------------------

--
-- Table structure for table `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `bankAccountUId` bigint(20) UNSIGNED NOT NULL,
  `bankAccountNumber` varchar(255) NOT NULL,
  `bankAccountSupplierId` bigint(20) UNSIGNED DEFAULT NULL,
  `bankAccountAgentId` bigint(20) UNSIGNED DEFAULT NULL,
  `bankAccountBankName` varchar(255) NOT NULL,
  `bankAccountHolderName` varchar(255) NOT NULL,
  `bankAccountStatus` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0 = Inactive, 1 = Active',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bank_accounts`
--

INSERT INTO `bank_accounts` (`bankAccountUId`, `bankAccountNumber`, `bankAccountSupplierId`, `bankAccountAgentId`, `bankAccountBankName`, `bankAccountHolderName`, `bankAccountStatus`, `created_at`, `updated_at`) VALUES
(46, '0987654321', NULL, 3, 'NBC', 'John Mushi', 1, '2025-09-19 10:33:32', '2025-09-19 10:33:32'),
(40, '12345678090', 2, NULL, 'NBC', 'Mysel Us', 1, '2025-09-18 18:36:20', '2025-09-18 18:36:20'),
(42, '12345678091', NULL, 3, 'CRDB', 'John Mushi', 1, '2025-09-18 18:37:43', '2025-09-19 10:33:32'),
(37, '200100300', 1, NULL, 'NMB', 'Kilimanjaro', 1, '2025-09-17 18:16:37', '2025-09-17 18:16:37'),
(45, '200100500', NULL, 1, 'CRDB', 'Michael Mussa', 1, '2025-09-19 10:33:17', '2025-09-19 10:33:17'),
(38, '300200100', 1, NULL, 'CRDB', 'Kilimanjaro', 1, '2025-09-17 18:16:37', '2025-09-17 18:16:37'),
(39, '400200100', NULL, 1, 'NMB', 'Michael Mussa', 1, '2025-09-17 18:19:00', '2025-09-19 10:33:17');

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
(2, 'Electronics', '2025-09-09 11:01:39', '2025-09-09 11:01:39'),
(4, 'Home Appliances', '2025-08-12 17:35:03', '2025-08-12 17:35:03'),
(6, 'Health & Beauty', '2025-08-12 17:35:03', '2025-08-12 17:35:03');

-- --------------------------------------------------------

--
-- Table structure for table `company_payment_accounts`
--

CREATE TABLE `company_payment_accounts` (
  `paymentAccountUId` int(11) NOT NULL,
  `paymentAccountType` enum('Bank','Mobile Money') NOT NULL,
  `paymentAccountProviderName` varchar(255) NOT NULL,
  `paymentAccountNumber` varchar(255) NOT NULL,
  `paymentAccountHolderName` varchar(255) NOT NULL,
  `paymentAccountStatus` tinytext NOT NULL DEFAULT '1' COMMENT '0= Inactive, 1= Active ',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_payment_accounts`
--

INSERT INTO `company_payment_accounts` (`paymentAccountUId`, `paymentAccountType`, `paymentAccountProviderName`, `paymentAccountNumber`, `paymentAccountHolderName`, `paymentAccountStatus`, `created_at`, `updated_at`) VALUES
(1, 'Bank', 'NMB Bank', '1234567890', 'John Peter', '1', '2025-10-02 10:16:09', '2025-10-02 10:18:28'),
(2, 'Bank', 'NMB Bank', '9876543210', 'Mary Joseph', '1', '2025-10-02 10:16:09', '2025-10-02 10:18:33'),
(3, 'Bank', 'CRDB Bank', '111222333', 'Alpha Ltd', '1', '2025-10-02 10:16:09', '2025-10-02 10:19:15'),
(4, 'Bank', 'CRDB Bank', '444555666', 'Beta Traders', '1', '2025-10-02 10:16:09', '2025-10-02 10:19:15'),
(5, 'Bank', 'Equity Bank', '777888999', 'Charles K', '1', '2025-10-02 10:16:09', '2025-10-02 10:34:55'),
(6, 'Bank', 'NBC Bank', '555444333', 'Omega Supplies', '1', '2025-10-02 10:16:09', '2025-10-02 10:19:15'),
(7, 'Mobile Money', 'Vodacom M-Pesa', '0754001122', 'John Peter', '1', '2025-10-02 10:16:09', '2025-10-02 10:19:15'),
(8, 'Mobile Money', 'Vodacom M-Pesa', '0754001133', 'Mary Joseph', '1', '2025-10-02 10:16:09', '2025-10-02 10:19:15'),
(9, 'Mobile Money', 'Tigo Pesa', '0715004455', 'Alpha Ltd', '1', '2025-10-02 10:16:09', '2025-10-02 10:19:15'),
(10, 'Mobile Money', 'Tigo Pesa', '0715004466', 'Beta Traders', '1', '2025-10-02 10:16:09', '2025-10-02 10:19:15'),
(11, 'Mobile Money', 'Airtel Money', '0689007788', 'Charles K', '1', '2025-10-02 10:16:09', '2025-10-02 10:19:15'),
(12, 'Mobile Money', 'Halopesa', '0622009900', 'Omega Supplies', '1', '2025-10-02 10:16:09', '2025-10-02 10:19:15');

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
  `customerStatus` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1 = Active, 0 = Inactive',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customerId`, `customerName`, `customerEmail`, `customerPhone`, `customerAddress`, `customerStatus`, `created_at`, `updated_at`) VALUES
(1, 'GreenTech Solutions Ltd', 'contact@greentech.co.tz', '+255712345678', 'Plot 45, Mikocheni Industrial Area, Dar es Salaam', 1, '2025-09-18 15:20:34', '2025-09-25 10:21:06'),
(2, 'Jembe Traders', 'sales@jembe.co.tz', '+255784567890', 'Market Street, Block B, Mbeya', 1, '2025-09-18 15:20:34', '2025-09-25 10:21:11');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `orderUId` bigint(20) UNSIGNED NOT NULL,
  `orderInvoiceNumber` varchar(255) NOT NULL,
  `orderCustomerId` bigint(20) UNSIGNED NOT NULL,
  `orderCreatedBy` bigint(20) UNSIGNED NOT NULL,
  `orderUpdatedBy` bigint(20) UNSIGNED NOT NULL,
  `orderDate` date NOT NULL,
  `orderTotalProducts` int(11) NOT NULL,
  `orderSubTotal` decimal(11,2) NOT NULL,
  `orderVat` int(11) NOT NULL,
  `orderVatAmount` decimal(11,2) NOT NULL,
  `orderDiscount` int(11) NOT NULL,
  `orderDiscountAmount` decimal(11,2) NOT NULL,
  `orderShippingAmount` decimal(11,2) NOT NULL,
  `orderTotalAmount` decimal(11,2) NOT NULL,
  `orderPaidAmount` decimal(11,2) NOT NULL,
  `orderDueAmount` decimal(11,2) NOT NULL,
  `orderStatus` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 = Pending, 1 = Completed, 2 = Cancelled, 3 = Deleted',
  `orderDescription` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`orderUId`, `orderInvoiceNumber`, `orderCustomerId`, `orderCreatedBy`, `orderUpdatedBy`, `orderDate`, `orderTotalProducts`, `orderSubTotal`, `orderVat`, `orderVatAmount`, `orderDiscount`, `orderDiscountAmount`, `orderShippingAmount`, `orderTotalAmount`, `orderPaidAmount`, `orderDueAmount`, `orderStatus`, `orderDescription`, `created_at`, `updated_at`) VALUES
(1, 'SNK-S001', 1, 10, 10, '2025-09-23', 100, 1550000.00, 18, 279000.00, 5, 77500.00, 50000.00, 1751500.00, 1751500.00, 0.00, 1, NULL, '2025-09-23 15:17:30', '2025-09-23 18:00:41'),
(2, 'SNK-S002', 2, 10, 10, '2025-09-24', 95, 1475000.00, 18, 265500.00, 5, 73750.00, 50000.00, 1666750.00, 1666750.00, 0.00, 1, NULL, '2025-09-24 10:30:12', '2025-09-25 11:42:01'),
(4, 'SNK-S003', 1, 10, 10, '2025-09-26', 5, 85000.00, 18, 15300.00, 5, 4250.00, 10000.00, 96050.00, 10000.00, 86050.00, 2, NULL, '2025-09-26 09:55:39', '2025-09-26 10:27:03'),
(5, 'SNK-S004', 1, 9, 9, '2025-10-01', 8, 130000.00, 18, 23400.00, 10, 13000.00, 10000.00, 140400.00, 32000.00, 108400.00, 0, NULL, '2025-10-01 14:00:45', '2025-10-02 18:28:17'),
(12, 'SNK-S005', 1, 10, 10, '2025-10-02', 40, 650000.00, 18, 117000.00, 5, 32500.00, 50000.00, 734500.00, 0.00, 734500.00, 0, NULL, '2025-10-02 09:59:11', '2025-10-02 09:59:11'),
(13, 'SNK-S006', 1, 10, 10, '2025-10-02', 8, 130000.00, 18, 23400.00, 10, 13000.00, 10000.00, 140400.00, 0.00, 140400.00, 0, NULL, '2025-10-02 10:02:25', '2025-10-02 10:02:25'),
(14, 'SNK-S007', 1, 9, 9, '2025-10-02', 8, 130000.00, 18, 23400.00, 10, 13000.00, 10000.00, 140400.00, 0.00, 140400.00, 0, NULL, '2025-10-02 10:40:20', '2025-10-02 10:40:20'),
(15, 'SNK-S008', 1, 9, 9, '2025-10-02', 2, 30000.00, 0, 0.00, 0, 0.00, 0.00, 30000.00, 2000.00, 28000.00, 0, NULL, '2025-10-02 10:47:30', '2025-10-02 10:47:30'),
(16, 'SNK-S009', 1, 9, 10, '2025-10-02', 8, 130000.00, 18, 23400.00, 10, 13000.00, 10000.00, 140400.00, 2224.00, 138176.00, 0, NULL, '2025-10-02 10:49:34', '2025-10-02 11:06:56'),
(17, 'SNK-S010', 1, 10, 10, '2025-10-02', 6, 90000.00, 18, 16200.00, 10, 10000.00, 10000.00, 106200.00, 20000.00, 86200.00, 0, NULL, '2025-10-02 11:54:09', '2025-10-02 11:54:09'),
(18, 'SNK-S011', 1, 10, 10, '2025-10-02', 6, 90000.00, 18, 16200.00, 10, 10000.00, 10000.00, 106200.00, 0.00, 106200.00, 0, NULL, '2025-10-02 11:56:08', '2025-10-02 11:56:08');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `orderDetailUId` bigint(20) UNSIGNED NOT NULL,
  `orderDetailInvoiceNumber` varchar(255) NOT NULL,
  `orderDetailProductId` bigint(20) UNSIGNED NOT NULL,
  `orderDetailQuantity` int(11) NOT NULL,
  `orderDetailUnitCost` decimal(11,2) NOT NULL,
  `orderDetailTotalCost` decimal(11,2) NOT NULL,
  `orderDetailStatus` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 = Pending, 1 = Completed, 2 = Cancelled, 3 = Deleted',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`orderDetailUId`, `orderDetailInvoiceNumber`, `orderDetailProductId`, `orderDetailQuantity`, `orderDetailUnitCost`, `orderDetailTotalCost`, `orderDetailStatus`, `created_at`, `updated_at`) VALUES
(1, 'SNK-S001', 1, 45, 15000.00, 675000.00, 1, '2025-09-23 15:17:30', '2025-09-23 15:17:30'),
(3, 'SNK-S002', 1, 55, 15000.00, 825000.00, 1, '2025-09-24 10:30:12', '2025-09-24 10:30:38'),
(7, 'SNK-S004', 1, 5, 15000.00, 75000.00, 1, '2025-10-01 14:00:45', '2025-10-01 14:00:45'),
(8, 'SNK-S004', 36, 2, 15000.00, 30000.00, 1, '2025-10-01 14:00:45', '2025-10-01 14:00:45'),
(9, 'SNK-S004', 2, 1, 15000.00, 15000.00, 1, '2025-10-01 14:00:45', '2025-10-01 14:00:45'),
(16, 'SNK-S005', 2, 40, 15000.00, 600000.00, 0, '2025-10-02 09:59:11', '2025-10-02 09:59:11'),
(17, 'SNK-S006', 1, 5, 15000.00, 75000.00, 0, '2025-10-02 10:02:25', '2025-10-02 10:02:25'),
(18, 'SNK-S006', 36, 2, 15000.00, 30000.00, 0, '2025-10-02 10:02:25', '2025-10-02 10:02:25'),
(19, 'SNK-S006', 2, 1, 15000.00, 15000.00, 0, '2025-10-02 10:02:25', '2025-10-02 10:02:25'),
(20, 'SNK-S007', 1, 5, 15000.00, 75000.00, 0, '2025-10-02 10:40:20', '2025-10-02 10:40:20'),
(21, 'SNK-S007', 36, 2, 15000.00, 30000.00, 0, '2025-10-02 10:40:20', '2025-10-02 10:40:20'),
(22, 'SNK-S007', 2, 1, 15000.00, 15000.00, 0, '2025-10-02 10:40:20', '2025-10-02 10:40:20'),
(23, 'SNK-S008', 1, 2, 15000.00, 30000.00, 0, '2025-10-02 10:47:30', '2025-10-02 10:47:30'),
(24, 'SNK-S009', 1, 5, 15000.00, 75000.00, 0, '2025-10-02 10:49:34', '2025-10-02 10:49:34'),
(25, 'SNK-S009', 36, 2, 15000.00, 30000.00, 0, '2025-10-02 10:49:34', '2025-10-02 10:49:34'),
(26, 'SNK-S009', 2, 1, 15000.00, 15000.00, 0, '2025-10-02 10:49:34', '2025-10-02 10:49:34'),
(28, 'SNK-S011', 1, 6, 15000.00, 90000.00, 0, '2025-10-02 11:56:08', '2025-10-02 11:56:08');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `productId` bigint(20) UNSIGNED NOT NULL,
  `productCategoryId` bigint(20) UNSIGNED NOT NULL,
  `productUnitId` bigint(20) UNSIGNED NOT NULL,
  `productName` varchar(255) NOT NULL,
  `productType` varchar(255) DEFAULT NULL,
  `productQuantity` int(11) NOT NULL,
  `productBuyingPrice` decimal(11,2) NOT NULL,
  `productSellingPrice` decimal(11,2) NOT NULL,
  `productQuantityAlert` int(11) DEFAULT NULL,
  `productTax` int(11) DEFAULT NULL,
  `productNotes` text DEFAULT NULL,
  `productStatus` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=OutOfStock, 1=Available, 2=LowStock',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`productId`, `productCategoryId`, `productUnitId`, `productName`, `productType`, `productQuantity`, `productBuyingPrice`, `productSellingPrice`, `productQuantityAlert`, `productTax`, `productNotes`, `productStatus`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'Battery', '0', 94, 10000.00, 15000.00, 20, 18, 'Battery', 1, '2025-08-19 11:43:10', '2025-10-02 18:26:09'),
(2, 2, 2, 'Flow Sensors', 'Panasonic', 89, 12000.00, 15000.00, 20, 18, 'Flow Sensors', 1, '2025-09-26 10:28:34', '2025-10-02 18:26:29'),
(36, 2, 1, 'Valve', 'Inch 1 Black', 140, 10000.00, 15000.00, 50, 18, 'Valve', 1, '2025-10-01 13:01:21', '2025-10-02 10:49:34'),
(38, 1, 1, 'Battery Meter', '', 0, 12000.00, 15000.00, 20, 18, '', 0, '2025-10-01 16:30:50', '2025-10-01 16:30:50');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `purchaseUId` int(11) NOT NULL,
  `purchaseNumber` varchar(255) NOT NULL,
  `purchaseSupplierId` bigint(20) UNSIGNED NOT NULL,
  `purchaseSupplierAccountNumber` varchar(255) DEFAULT NULL,
  `purchaseCreatedBy` bigint(20) UNSIGNED NOT NULL,
  `purchaseUpdatedBy` bigint(20) UNSIGNED NOT NULL,
  `purchaseDate` date NOT NULL,
  `purchaseStatus` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 = Pending, 1 = Completed, 2= Cancelled, 3 = Deleted ',
  `purchaseDescription` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`purchaseUId`, `purchaseNumber`, `purchaseSupplierId`, `purchaseSupplierAccountNumber`, `purchaseCreatedBy`, `purchaseUpdatedBy`, `purchaseDate`, `purchaseStatus`, `purchaseDescription`, `created_at`, `updated_at`) VALUES
(1, 'SNK-P001', 2, '12345678090', 10, 10, '2025-09-23', 1, NULL, '2025-09-23 15:12:10', '2025-09-23 15:14:24'),
(2, 'SNK-P002', 1, '300200100', 10, 10, '2025-09-26', 1, NULL, '2025-09-26 11:03:39', '2025-09-26 11:07:42'),
(3, 'SNK-P003', 1, '200100300', 10, 10, '2025-10-01', 3, 'Imekosewa', '2025-10-01 11:56:29', '2025-10-01 12:33:54'),
(4, 'SNK-P004', 1, '200100300', 9, 9, '2025-10-01', 1, NULL, '2025-10-01 12:53:49', '2025-10-01 15:45:50'),
(5, 'SNK-P005', 1, '200100300', 9, 9, '2025-10-01', 1, NULL, '2025-10-01 13:03:03', '2025-10-01 13:39:54'),
(6, 'SNK-P006', 2, '12345678090', 9, 9, '2025-10-01', 1, NULL, '2025-10-01 13:06:14', '2025-10-01 13:45:15'),
(7, 'SNK-P007', 1, '200100300', 9, 9, '2025-10-01', 1, NULL, '2025-10-01 15:47:37', '2025-10-01 15:50:37'),
(11, 'SNK-P008', 1, '200100300', 9, 9, '2025-10-01', 1, NULL, '2025-10-01 16:07:09', '2025-10-01 16:12:08'),
(12, 'SNK-P009', 1, '200100300', 9, 9, '2025-10-01', 1, NULL, '2025-10-01 16:07:39', '2025-10-01 16:14:32'),
(13, 'SNK-P010', 1, '200100300', 9, 10, '2025-10-01', 1, NULL, '2025-10-01 16:11:26', '2025-10-01 17:26:21'),
(14, 'SNK-P011', 1, '200100300', 10, 9, '2025-10-01', 1, NULL, '2025-10-01 20:34:37', '2025-10-02 18:26:29');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_details`
--

CREATE TABLE `purchase_details` (
  `purchaseDetailUId` bigint(20) UNSIGNED NOT NULL,
  `purchaseDetailPurchaseNumber` varchar(255) NOT NULL,
  `purchaseDetailProductId` bigint(20) UNSIGNED NOT NULL,
  `purchaseDetailAgentId` bigint(20) UNSIGNED DEFAULT NULL,
  `purchaseAgentBankAccountNumber` varchar(255) DEFAULT NULL,
  `purchaseDetailTrackingNumber` varchar(255) DEFAULT NULL,
  `purchaseDetailProductSize` varchar(255) DEFAULT NULL,
  `purchaseDetailQuantity` int(11) NOT NULL,
  `purchaseDetailUnitCost` decimal(11,2) NOT NULL,
  `purchaseDetailRate` int(11) NOT NULL,
  `purchaseDetailTotalCost` decimal(11,2) NOT NULL,
  `purchaseDetailStatus` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 = Pending, 1 = Completed, 2= Cancelled, 3 = Deleted ',
  `agentTransportationCost` decimal(11,2) DEFAULT NULL,
  `dateToAgentAbroadWarehouse` date DEFAULT NULL,
  `dateReceivedByAgentInCountryWarehouse` date DEFAULT NULL,
  `dateReceivedByCompany` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_details`
--

INSERT INTO `purchase_details` (`purchaseDetailUId`, `purchaseDetailPurchaseNumber`, `purchaseDetailProductId`, `purchaseDetailAgentId`, `purchaseAgentBankAccountNumber`, `purchaseDetailTrackingNumber`, `purchaseDetailProductSize`, `purchaseDetailQuantity`, `purchaseDetailUnitCost`, `purchaseDetailRate`, `purchaseDetailTotalCost`, `purchaseDetailStatus`, `agentTransportationCost`, `dateToAgentAbroadWarehouse`, `dateReceivedByAgentInCountryWarehouse`, `dateReceivedByCompany`, `created_at`, `updated_at`) VALUES
(1, 'SNK-P001', 1, 1, '400200100', '1275675675', '50', 100, 400.00, 2500, 100000000.00, 1, 12000.00, '2025-09-22', '2025-09-23', '2025-09-23', '2025-09-23 15:12:10', '2025-09-23 15:14:24'),
(3, 'SNK-P002', 2, 1, '200100500', '1234ABC', '12', 100, 1200.00, 400, 48000000.00, 1, 50000.00, '2025-09-24', '2025-09-25', '2025-09-26', '2025-09-26 11:03:39', '2025-09-26 11:07:42'),
(5, 'SNK-P003', 1, 1, '200100500', NULL, NULL, 10, 400.00, 380, 1520000.00, 1, 50000.00, NULL, NULL, NULL, '2025-10-01 11:56:29', '2025-10-01 12:33:54'),
(6, 'SNK-P003', 2, NULL, NULL, NULL, NULL, 10, 450.00, 380, 1710000.00, 1, NULL, NULL, NULL, NULL, '2025-10-01 11:56:29', '2025-10-01 12:33:54'),
(7, 'SNK-P004', 1, 1, NULL, 'ABC1234', '12', 10, 400.00, 500, 2000000.00, 1, 12000.00, '2025-09-30', '2025-10-01', '2025-10-01', '2025-10-01 12:53:49', '2025-10-01 15:29:53'),
(8, 'SNK-P004', 2, NULL, NULL, NULL, NULL, 10, 450.00, 500, 2250000.00, 1, NULL, '2025-09-30', '2025-10-01', '2025-10-01', '2025-10-01 12:53:49', '2025-10-01 15:45:50'),
(9, 'SNK-P005', 36, 1, '200100500', 'VALVE680678', '12', 10, 200.00, 280, 560000.00, 1, 100000.00, '2025-10-01', '2025-10-01', '2025-10-01', '2025-10-01 13:03:03', '2025-10-01 13:39:54'),
(10, 'SNK-P005', 2, 3, '12345678091', 'T25AQZ', '50', 8, 100.00, 280, 224000.00, 1, 2000.00, '2025-10-01', '2025-10-01', '2025-10-01', '2025-10-01 13:03:03', '2025-10-01 13:39:54'),
(11, 'SNK-P006', 1, 1, '200100500', 'ABC1234', '12', 15, 60000.00, 1, 900000.00, 1, 12000.00, '2025-09-30', '2025-10-01', '2025-10-01', '2025-10-01 13:06:14', '2025-10-01 13:45:15'),
(12, 'SNK-P007', 2, NULL, NULL, 'ABC1234', '12', 1, 200.00, 380, 76000.00, 1, 12000.00, '2025-09-30', '2025-10-01', '2025-10-01', '2025-10-01 15:47:37', '2025-10-01 15:48:58'),
(13, 'SNK-P007', 36, NULL, NULL, NULL, NULL, 12, 400.00, 380, 1824000.00, 1, NULL, '2025-09-30', '2025-10-01', '2025-10-01', '2025-10-01 15:47:37', '2025-10-01 15:50:37'),
(20, 'SNK-P008', 1, NULL, NULL, NULL, NULL, 1, 122.00, 1, 122.00, 1, NULL, '2025-09-30', '2025-10-01', '2025-10-01', '2025-10-01 16:07:09', '2025-10-01 16:12:08'),
(21, 'SNK-P009', 1, NULL, NULL, NULL, NULL, 1, 111.00, 1, 111.00, 1, NULL, '2025-09-29', '2025-10-01', '2025-10-01', '2025-10-01 16:07:39', '2025-10-01 16:14:02'),
(22, 'SNK-P009', 2, NULL, NULL, NULL, NULL, 1, 111.00, 1, 111.00, 1, NULL, '2025-09-30', '2025-10-01', '2025-10-01', '2025-10-01 16:07:39', '2025-10-01 16:14:17'),
(23, 'SNK-P009', 36, NULL, NULL, NULL, NULL, 1, 111.00, 1, 111.00, 1, NULL, '2025-09-30', '2025-10-01', '2025-10-01', '2025-10-01 16:07:39', '2025-10-01 16:14:32'),
(24, 'SNK-P010', 1, NULL, NULL, NULL, NULL, 100, 1000.00, 1, 100000.00, 1, NULL, '2025-10-01', '2025-10-01', '2025-10-01', '2025-10-01 16:11:26', '2025-10-01 17:19:12'),
(25, 'SNK-P010', 2, NULL, NULL, NULL, NULL, 100, 2000.00, 1, 200000.00, 1, NULL, '2025-10-01', '2025-10-01', '2025-10-01', '2025-10-01 16:11:26', '2025-10-01 17:25:58'),
(26, 'SNK-P010', 36, NULL, NULL, NULL, NULL, 100, 3000.00, 1, 300000.00, 1, NULL, '2025-10-01', '2025-10-01', '2025-10-01', '2025-10-01 16:11:26', '2025-10-01 17:26:21'),
(27, 'SNK-P011', 1, 1, '200100500', NULL, '12', 1, 1000.00, 1, 1000.00, 1, 10000.00, '2025-10-01', '2025-10-02', '2025-10-02', '2025-10-01 20:34:37', '2025-10-02 18:26:09'),
(28, 'SNK-P011', 2, NULL, NULL, NULL, '12', 1, 10000.00, 1, 10000.00, 1, NULL, NULL, NULL, '2025-10-02', '2025-10-01 20:34:37', '2025-10-02 18:26:29');

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `quotationUId` bigint(20) UNSIGNED NOT NULL,
  `quotationReferenceNumber` varchar(255) NOT NULL,
  `quotationCustomerId` bigint(20) UNSIGNED NOT NULL,
  `quotationCreatedBy` bigint(20) UNSIGNED NOT NULL,
  `quotationUpdatedBy` bigint(20) UNSIGNED NOT NULL,
  `quotationDate` date NOT NULL,
  `quotationTotalProducts` int(11) NOT NULL,
  `quotationSubTotal` decimal(11,2) NOT NULL,
  `quotationTaxPercentage` int(11) NOT NULL,
  `quotationTaxAmount` decimal(11,2) NOT NULL,
  `quotationDiscountPercentage` int(11) NOT NULL,
  `quotationDiscountAmount` decimal(11,2) NOT NULL,
  `quotationShippingAmount` decimal(11,2) NOT NULL,
  `quotationTotalAmount` decimal(11,2) NOT NULL,
  `quotationDescription` text DEFAULT NULL,
  `quotationStatus` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 = Sent, 1 = Approved, 2 = Cancelled, 3 = Deleted ',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`quotationUId`, `quotationReferenceNumber`, `quotationCustomerId`, `quotationCreatedBy`, `quotationUpdatedBy`, `quotationDate`, `quotationTotalProducts`, `quotationSubTotal`, `quotationTaxPercentage`, `quotationTaxAmount`, `quotationDiscountPercentage`, `quotationDiscountAmount`, `quotationShippingAmount`, `quotationTotalAmount`, `quotationDescription`, `quotationStatus`, `created_at`, `updated_at`) VALUES
(1, 'SNK-RF001', 1, 10, 10, '2025-09-23', 100, 1550000.00, 18, 279000.00, 5, 77500.00, 50000.00, 1751500.00, 'Valid for 30  days', 1, '2025-09-23 15:16:28', '2025-09-23 15:17:30'),
(2, 'SNK-RF002', 2, 10, 10, '2025-09-24', 95, 1475000.00, 18, 265500.00, 5, 73750.00, 50000.00, 1666750.00, '', 1, '2025-09-24 10:29:00', '2025-09-24 10:30:12'),
(3, 'SNK-RF003', 1, 10, 10, '2025-09-24', 40, 650000.00, 18, 117000.00, 5, 32500.00, 50000.00, 734500.00, 'Kachelewesha', 1, '2025-09-24 10:29:36', '2025-10-02 09:59:11'),
(4, 'SNK-RF004', 1, 10, 10, '2025-09-26', 5, 85000.00, 18, 15300.00, 5, 4250.00, 10000.00, 96050.00, 'Valid for 30  days', 1, '2025-09-26 09:44:17', '2025-09-26 09:55:39'),
(5, 'SNK-RF005', 1, 9, 9, '2025-10-01', 8, 130000.00, 18, 23400.00, 10, 13000.00, 10000.00, 140400.00, 'CBWSO weza', 1, '2025-10-01 13:50:57', '2025-10-02 10:49:34'),
(6, 'SNK-RF006', 1, 9, 9, '2025-10-02', 2, 30000.00, 0, 0.00, 0, 0.00, 0.00, 30000.00, '', 1, '2025-10-02 10:47:08', '2025-10-02 10:47:30'),
(7, 'SNK-RF007', 1, 10, 10, '2025-10-02', 6, 90000.00, 18, 16200.00, 10, 10000.00, 10000.00, 106200.00, '', 1, '2025-10-02 11:43:31', '2025-10-02 11:56:08');

-- --------------------------------------------------------

--
-- Table structure for table `quotation_details`
--

CREATE TABLE `quotation_details` (
  `quotationDetailUId` bigint(20) UNSIGNED NOT NULL,
  `quotationDetailReferenceNumber` varchar(255) NOT NULL,
  `quotationDetailProductId` bigint(20) UNSIGNED DEFAULT NULL,
  `quotationDetailQuantity` int(11) NOT NULL,
  `quotationDetailUnitPrice` decimal(11,2) NOT NULL,
  `quotationDetailSubTotal` decimal(11,2) NOT NULL,
  `quotationDetailStatus` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 = Sent, 1 = Approved, 2 = Cancelled, 3 = Deleted ',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotation_details`
--

INSERT INTO `quotation_details` (`quotationDetailUId`, `quotationDetailReferenceNumber`, `quotationDetailProductId`, `quotationDetailQuantity`, `quotationDetailUnitPrice`, `quotationDetailSubTotal`, `quotationDetailStatus`, `created_at`, `updated_at`) VALUES
(3, 'SNK-RF001', 1, 45, 15000.00, 675000.00, 1, '2025-09-23 15:16:50', '2025-09-23 15:17:30'),
(5, 'SNK-RF002', 1, 55, 15000.00, 825000.00, 1, '2025-09-24 10:29:00', '2025-09-24 10:30:12'),
(17, 'SNK-RF003', 2, 40, 15000.00, 600000.00, 1, '2025-10-01 11:47:10', '2025-10-02 09:59:11'),
(21, 'SNK-RF005', 1, 5, 15000.00, 75000.00, 1, '2025-10-01 13:52:48', '2025-10-02 10:49:34'),
(22, 'SNK-RF005', 36, 2, 15000.00, 30000.00, 1, '2025-10-01 13:52:48', '2025-10-02 10:49:34'),
(23, 'SNK-RF005', 2, 1, 15000.00, 15000.00, 1, '2025-10-01 13:52:48', '2025-10-02 10:49:34'),
(24, 'SNK-RF006', 1, 2, 15000.00, 30000.00, 1, '2025-10-02 10:47:08', '2025-10-02 10:47:30'),
(26, 'SNK-RF007', 1, 6, 15000.00, 90000.00, 1, '2025-10-02 11:49:56', '2025-10-02 11:56:08');

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
  `supplierShopName` varchar(255) DEFAULT NULL,
  `supplierType` varchar(255) DEFAULT NULL,
  `supplierStatus` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1 = Active, 0 = Inactive',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplierId`, `supplierName`, `supplierEmail`, `supplierPhone`, `supplierAddress`, `supplierShopName`, `supplierType`, `supplierStatus`, `created_at`, `updated_at`) VALUES
(1, 'Kilimanjaro Traders', 'kilimanjaro@gmail.com', '0712345678', 'Beijing, China', 'Guangzou', 'Wholesaler', 1, '2025-09-17 18:16:37', '2025-09-24 15:15:02'),
(2, 'Bahati Traders', 'myself@gmail.com', '075612345667', 'Dodoma, Tanzania', 'Area 1', 'Retailer', 1, '2025-09-18 18:36:20', '2025-09-24 12:43:11');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transactionUId` bigint(20) UNSIGNED NOT NULL,
  `transactionCustomerId` bigint(20) UNSIGNED NOT NULL,
  `transactionInvoiceNumber` varchar(255) NOT NULL,
  `transactionAccountUId` int(11) DEFAULT NULL,
  `transactionCreatedBy` bigint(20) UNSIGNED NOT NULL,
  `transactionPaymentType` varchar(255) NOT NULL,
  `transactionPaidAmount` decimal(11,2) NOT NULL DEFAULT 0.00,
  `transactionDueAmount` decimal(11,2) NOT NULL DEFAULT 0.00,
  `transactionDate` date NOT NULL,
  `transactionCreatedAt` datetime DEFAULT NULL,
  `transactionUpdatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transactionUId`, `transactionCustomerId`, `transactionInvoiceNumber`, `transactionAccountUId`, `transactionCreatedBy`, `transactionPaymentType`, `transactionPaidAmount`, `transactionDueAmount`, `transactionDate`, `transactionCreatedAt`, `transactionUpdatedAt`) VALUES
(1, 1, 'SNK-S004', NULL, 10, 'Cash', 10000.00, 130400.00, '2025-10-01', '2025-10-01 17:39:00', '2025-10-01 17:39:00'),
(8, 1, 'SNK-S005', 5, 10, 'Bank', 0.00, 734500.00, '2025-10-02', '2025-10-02 09:59:11', '2025-10-02 09:59:11'),
(9, 1, 'SNK-S006', 10, 10, 'Mobile Money', 0.00, 140400.00, '2025-10-02', '2025-10-02 10:02:25', '2025-10-02 10:02:25'),
(10, 1, 'SNK-S007', 5, 9, 'Bank', 0.00, 140400.00, '2025-10-02', '2025-10-02 10:40:20', '2025-10-02 10:40:20'),
(13, 1, 'SNK-S008', 5, 9, 'Bank', 2000.00, 28000.00, '2025-10-02', '2025-10-02 10:47:30', '2025-10-02 10:47:30'),
(15, 1, 'SNK-S009', NULL, 9, 'Cash', 0.00, 140400.00, '2025-10-02', '2025-10-02 10:49:34', '2025-10-02 10:49:34'),
(17, 1, 'SNK-S009', NULL, 9, 'Cash', 1000.00, 139400.00, '2025-10-02', '2025-10-02 10:53:58', '2025-10-02 10:53:58'),
(22, 1, 'SNK-S009', NULL, 10, 'Cash', 12.00, 139388.00, '2025-10-02', '2025-10-02 11:02:49', '2025-10-02 11:02:49'),
(23, 1, 'SNK-S009', 3, 10, 'Bank', 12.00, 139376.00, '2025-10-02', '2025-10-02 11:06:32', '2025-10-02 11:06:32'),
(24, 1, 'SNK-S009', 9, 10, 'Mobile Money', 1200.00, 138176.00, '2025-10-02', '2025-10-02 11:06:56', '2025-10-02 11:06:56'),
(25, 1, 'SNK-S010', NULL, 10, 'Cash', 20000.00, 86200.00, '2025-10-02', '2025-10-02 11:54:09', '2025-10-02 11:54:09'),
(26, 1, 'SNK-S011', 12, 10, 'Mobile Money', 0.00, 106200.00, '2025-10-02', '2025-10-02 11:56:08', '2025-10-02 11:56:08'),
(27, 1, 'SNK-S004', 3, 9, 'Bank', 12000.00, 118400.00, '2025-10-02', '2025-10-02 18:27:50', '2025-10-02 18:27:50'),
(28, 1, 'SNK-S004', 12, 9, 'Mobile Money', 10000.00, 108400.00, '2025-10-02', '2025-10-02 18:28:17', '2025-10-02 18:28:17');

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
(9, 'Edgar Charles', '0679799407', 'edgarcharles360@gmail.com', 'Admin', '$2y$10$N3BdAeq3nyMBxSyjWGVkuOefEGn3IKQzoatOw9flIaClOs84HOgYm', 'user_9_1757331133.jpg', 1, '2025-08-12 09:35:09', '2025-09-24 13:06:55'),
(10, 'Asimwe Bitegeko', '0679799408', 'asimwe@gmail.com', 'Admin', '$2y$10$kxo50ld.clou83mvw4dl9.qhO5EBc9bJNJCTUmLoxVlFzVqjT3yvy', 'user_10_1756891173.jpg', 1, '2025-08-12 09:35:33', '2025-09-23 18:28:30'),
(21, 'Ava Maxx', '0679123456', 'avamax@gmail.com', 'Admin', '$2y$10$sQokvY/ZWujKhtSoL1wRNOotsQ3B3trdSZhav43Z7HKlZra/DHaYK', NULL, 1, '2025-10-03 10:50:32', '2025-10-03 10:50:32');

-- --------------------------------------------------------

--
-- Table structure for table `user_certificates`
--

CREATE TABLE `user_certificates` (
  `certId` bigint(20) NOT NULL,
  `userCertId` bigint(20) UNSIGNED NOT NULL,
  `userCertPath` varchar(255) NOT NULL,
  `userCertKey` varchar(255) NOT NULL,
  `userCertKeyPassword` text NOT NULL,
  `userCertSerial` varchar(255) NOT NULL,
  `userCertIssued` date NOT NULL,
  `userCertExpiry` date NOT NULL,
  `userCertStatus` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0 = InActive, 1= Active',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_certificates`
--

INSERT INTO `user_certificates` (`certId`, `userCertId`, `userCertPath`, `userCertKey`, `userCertKeyPassword`, `userCertSerial`, `userCertIssued`, `userCertExpiry`, `userCertStatus`, `created_at`, `updated_at`) VALUES
(1, 10, 'assets/certs/users/user_10_cert.pem', 'assets/certs/users/user_10_key.pem', '12345', 'SONAK-USER10-2025', '2025-10-03', '2030-10-03', 1, '2025-10-03 10:24:05', '2025-10-03 10:24:05'),
(2, 9, 'assets/certs/users/user_9_cert.pem', 'assets/certs/users/user_9_key.pem', '', 'SONAK-USER9-2025', '2025-10-03', '2030-10-03', 1, '2025-10-03 10:31:23', '2025-10-03 10:31:23'),
(3, 21, 'assets/certs/users/user_21_cert.pem', 'assets/certs/users/user_21_key.pem', '123456', 'CERT-AVA21-2025', '2025-10-03', '2026-10-03', 1, '2025-10-03 11:00:01', '2025-10-03 11:00:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`agentId`);

--
-- Indexes for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD PRIMARY KEY (`bankAccountNumber`),
  ADD UNIQUE KEY `bankAccountUId` (`bankAccountUId`),
  ADD UNIQUE KEY `bankAccountNumber` (`bankAccountNumber`),
  ADD KEY `bankAccountAgentId` (`bankAccountAgentId`),
  ADD KEY `bankAccountSupplierId` (`bankAccountSupplierId`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`categoryId`);

--
-- Indexes for table `company_payment_accounts`
--
ALTER TABLE `company_payment_accounts`
  ADD PRIMARY KEY (`paymentAccountUId`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customerId`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`orderInvoiceNumber`),
  ADD UNIQUE KEY `orderUId` (`orderUId`),
  ADD KEY `orders_ibfk_1` (`orderCustomerId`),
  ADD KEY `orders_ibfk_2` (`orderCreatedBy`),
  ADD KEY `orders_ibfk_3` (`orderUpdatedBy`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`orderDetailUId`),
  ADD KEY `order_details_ibfk_1` (`orderDetailInvoiceNumber`),
  ADD KEY `order_details_ibfk_2` (`orderDetailProductId`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`productId`),
  ADD KEY `products_ibfk_1` (`productCategoryId`),
  ADD KEY `products_ibfk_2` (`productUnitId`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`purchaseNumber`),
  ADD UNIQUE KEY `purchaseUId` (`purchaseUId`),
  ADD KEY `purchases_ibfk_1` (`purchaseSupplierId`),
  ADD KEY `purchases_ibfk_2` (`purchaseCreatedBy`),
  ADD KEY `purchases_ibfk_3` (`purchaseUpdatedBy`),
  ADD KEY `idx_supplier_account_number` (`purchaseSupplierAccountNumber`);

--
-- Indexes for table `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD PRIMARY KEY (`purchaseDetailUId`),
  ADD KEY `purchase_details_ibfk_1` (`purchaseDetailPurchaseNumber`),
  ADD KEY `purchase_details_ibfk_2` (`purchaseDetailProductId`),
  ADD KEY `purchase_details_ibfk_3` (`purchaseDetailAgentId`),
  ADD KEY `purchaseAgentBankAccountNumber` (`purchaseAgentBankAccountNumber`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`quotationReferenceNumber`),
  ADD UNIQUE KEY `quotationUId` (`quotationUId`),
  ADD KEY `quotations_ibfk_1` (`quotationCustomerId`),
  ADD KEY `quotations_ibfk_2` (`quotationCreatedBy`),
  ADD KEY `quotations_ibfk_3` (`quotationUpdatedBy`);

--
-- Indexes for table `quotation_details`
--
ALTER TABLE `quotation_details`
  ADD PRIMARY KEY (`quotationDetailUId`),
  ADD KEY `quotation_details_ibfk_1` (`quotationDetailReferenceNumber`),
  ADD KEY `quotation_details_ibfk_2` (`quotationDetailProductId`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplierId`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transactionUId`),
  ADD KEY `transactionCustomerId` (`transactionCustomerId`),
  ADD KEY `transactionInvoiceNumber` (`transactionInvoiceNumber`),
  ADD KEY `transactions_ibfk_3` (`transactionCreatedBy`),
  ADD KEY `transactions_ibfk_4` (`transactionAccountUId`);

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
-- Indexes for table `user_certificates`
--
ALTER TABLE `user_certificates`
  ADD PRIMARY KEY (`certId`),
  ADD KEY `userCertId` (`userCertId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `agentId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `bankAccountUId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `categoryId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=224;

--
-- AUTO_INCREMENT for table `company_payment_accounts`
--
ALTER TABLE `company_payment_accounts`
  MODIFY `paymentAccountUId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customerId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `orderUId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `orderDetailUId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `productId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchaseUId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `purchase_details`
--
ALTER TABLE `purchase_details`
  MODIFY `purchaseDetailUId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `quotationUId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `quotation_details`
--
ALTER TABLE `quotation_details`
  MODIFY `quotationDetailUId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplierId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transactionUId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `unitId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `user_certificates`
--
ALTER TABLE `user_certificates`
  MODIFY `certId` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD CONSTRAINT `bank_accounts_ibfk_1` FOREIGN KEY (`bankAccountAgentId`) REFERENCES `agents` (`agentId`),
  ADD CONSTRAINT `bank_accounts_ibfk_2` FOREIGN KEY (`bankAccountSupplierId`) REFERENCES `suppliers` (`supplierId`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`orderCustomerId`) REFERENCES `customers` (`customerId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`orderCreatedBy`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`orderUpdatedBy`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`orderDetailInvoiceNumber`) REFERENCES `orders` (`orderInvoiceNumber`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`orderDetailProductId`) REFERENCES `products` (`productId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`productCategoryId`) REFERENCES `categories` (`categoryId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`productUnitId`) REFERENCES `units` (`unitId`) ON UPDATE CASCADE;

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `fk_purchase_supplier_account` FOREIGN KEY (`purchaseSupplierAccountNumber`) REFERENCES `bank_accounts` (`bankAccountNumber`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`purchaseSupplierId`) REFERENCES `suppliers` (`supplierId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`purchaseCreatedBy`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchases_ibfk_3` FOREIGN KEY (`purchaseUpdatedBy`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD CONSTRAINT `purchase_details_ibfk_1` FOREIGN KEY (`purchaseDetailPurchaseNumber`) REFERENCES `purchases` (`purchaseNumber`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_details_ibfk_2` FOREIGN KEY (`purchaseDetailProductId`) REFERENCES `products` (`productId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_details_ibfk_3` FOREIGN KEY (`purchaseDetailAgentId`) REFERENCES `agents` (`agentId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_details_ibfk_4` FOREIGN KEY (`purchaseAgentBankAccountNumber`) REFERENCES `bank_accounts` (`bankAccountNumber`);

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `quotations_ibfk_1` FOREIGN KEY (`quotationCustomerId`) REFERENCES `customers` (`customerId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `quotations_ibfk_2` FOREIGN KEY (`quotationCreatedBy`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `quotations_ibfk_3` FOREIGN KEY (`quotationUpdatedBy`) REFERENCES `users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quotation_details`
--
ALTER TABLE `quotation_details`
  ADD CONSTRAINT `quotation_details_ibfk_1` FOREIGN KEY (`quotationDetailReferenceNumber`) REFERENCES `quotations` (`quotationReferenceNumber`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `quotation_details_ibfk_2` FOREIGN KEY (`quotationDetailProductId`) REFERENCES `products` (`productId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transaction_account` FOREIGN KEY (`transactionAccountUId`) REFERENCES `company_payment_accounts` (`paymentAccountUId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`transactionCustomerId`) REFERENCES `customers` (`customerId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`transactionInvoiceNumber`) REFERENCES `orders` (`orderInvoiceNumber`) ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`transactionCreatedBy`) REFERENCES `users` (`userId`) ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_4` FOREIGN KEY (`transactionAccountUId`) REFERENCES `company_payment_accounts` (`paymentAccountUId`) ON UPDATE CASCADE;

--
-- Constraints for table `user_certificates`
--
ALTER TABLE `user_certificates`
  ADD CONSTRAINT `user_certificates_ibfk_1` FOREIGN KEY (`userCertId`) REFERENCES `users` (`userId`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
