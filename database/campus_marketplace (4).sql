-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 04, 2026 at 11:22 AM
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
-- Database: `campus_marketplace`
--

-- --------------------------------------------------------

--
-- Table structure for table `applies_to`
--

CREATE TABLE `applies_to` (
  `promotionID` int(11) NOT NULL,
  `productID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applies_to`
--

INSERT INTO `applies_to` (`promotionID`, `productID`) VALUES
(2, 10),
(3, 2),
(4, 3),
(4, 11),
(5, 9),
(5, 10);

-- --------------------------------------------------------

--
-- Table structure for table `buyer`
--

CREATE TABLE `buyer` (
  `BuyerID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Phone` varchar(20) NOT NULL,
  `Email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buyer`
--

INSERT INTO `buyer` (`BuyerID`, `Name`, `Phone`, `Email`, `password`) VALUES
(1, 'Anika Rahman', '01714103562', 'anikarahman@gamil.com', '$2y$10$NBVN4ZZwrj.Dej1TaleMj.DgNDS3g4AspbYzF1Mcz/setKKT2xdrm'),
(2, 'Rodoshi Tasnim', '01714103578', 'rods20@gmail.com', '$2y$10$RugcMin2gmKZyQaf9NtHmu7HZ5OSsLXCe7wvQb0aLhDXbhe7SuaCW');

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `ChatID` int(11) NOT NULL,
  `sellerID` int(11) NOT NULL,
  `buyerID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat`
--

INSERT INTO `chat` (`ChatID`, `sellerID`, `buyerID`) VALUES
(1, 3, 2),
(2, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `contains`
--

CREATE TABLE `contains` (
  `reservationID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `paidQuantity` int(11) DEFAULT NULL,
  `freeQuantity` int(11) DEFAULT NULL,
  `unitPrice` decimal(10,2) DEFAULT NULL,
  `promotionID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contains`
--

INSERT INTO `contains` (`reservationID`, `productID`, `quantity`, `paidQuantity`, `freeQuantity`, `unitPrice`, `promotionID`) VALUES
(1, 3, 2, NULL, NULL, NULL, NULL),
(2, 9, 1, NULL, NULL, NULL, NULL),
(3, 9, 3, NULL, NULL, NULL, NULL),
(4, 3, 2, NULL, NULL, NULL, NULL),
(5, 5, 2, NULL, NULL, NULL, NULL),
(6, 7, 1, NULL, NULL, NULL, NULL),
(7, 7, 1, NULL, NULL, NULL, NULL),
(8, 2, 2, 2, 0, 400.00, 3),
(9, 3, 4, 2, 2, 200.00, 4),
(10, 10, 3, 2, 1, 70.00, 5),
(11, 9, 1, 1, 0, 200.00, 5),
(12, 11, 3, 2, 1, 110.00, 4);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `FeedbackID` int(11) NOT NULL,
  `Comment` text DEFAULT NULL,
  `Rating` decimal(2,1) NOT NULL,
  `FeedbackDate` datetime DEFAULT current_timestamp(),
  `sellerID` int(11) NOT NULL,
  `buyerID` int(11) NOT NULL,
  `purchaseID` int(11) DEFAULT NULL,
  `productID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`FeedbackID`, `Comment`, `Rating`, `FeedbackDate`, `sellerID`, `buyerID`, `purchaseID`, `productID`) VALUES
(1, 'the chocolate was very fres and thei management was great', 4.0, '2026-09-03 20:34:54', 1, 1, NULL, 2),
(2, 'the milkshake is very tasty', 5.0, '2026-09-03 20:37:45', 1, 1, NULL, 11),
(3, 'Their service was very nice also the mint lemonade was very fresh', 4.0, '2026-09-03 20:40:27', 1, 2, NULL, 10);

-- --------------------------------------------------------

--
-- Table structure for table `has`
--

CREATE TABLE `has` (
  `purchaseID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `paidQuantity` int(11) DEFAULT NULL,
  `freeQuantity` int(11) DEFAULT NULL,
  `unitPrice` decimal(10,2) DEFAULT NULL,
  `promotionID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `has`
--

INSERT INTO `has` (`purchaseID`, `productID`, `quantity`, `paidQuantity`, `freeQuantity`, `unitPrice`, `promotionID`) VALUES
(1, 3, 2, NULL, NULL, NULL, NULL),
(2, 9, 3, NULL, NULL, NULL, NULL),
(3, 2, 2, 2, 0, 400.00, 3),
(4, 3, 4, 2, 2, 200.00, 4),
(5, 11, 3, 2, 1, 110.00, 4);

-- --------------------------------------------------------

--
-- Table structure for table `includes`
--

CREATE TABLE `includes` (
  `announcementID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `includes`
--

INSERT INTO `includes` (`announcementID`, `productID`, `quantity`) VALUES
(1, 4, 10),
(1, 7, 25),
(2, 5, 15),
(2, 6, 20),
(3, 3, 10),
(3, 9, 12),
(5, 11, 15);

-- --------------------------------------------------------

--
-- Table structure for table `mentions`
--

CREATE TABLE `mentions` (
  `productID` int(11) NOT NULL,
  `messageID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `MessageID` int(11) NOT NULL,
  `Text` text NOT NULL,
  `SenderType` varchar(20) NOT NULL,
  `timeStamp` datetime DEFAULT current_timestamp(),
  `chatID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`MessageID`, `Text`, `SenderType`, `timeStamp`, `chatID`) VALUES
(1, 'hey', 'Buyer', '2026-09-02 14:50:26', 1),
(2, 'Can I customized Cake?', 'Buyer', '2026-09-02 14:50:45', 1),
(3, 'Yes you can', 'Seller', '2026-09-02 14:52:53', 1),
(4, 'Please share your ideas', 'Seller', '2026-09-02 14:53:11', 1),
(5, 'hey tasnim', 'Buyer', '2026-09-03 15:12:20', 1),
(6, 'do u customized cake', 'Buyer', '2026-09-03 15:12:30', 1),
(7, 'yes sure', 'Seller', '2026-09-03 15:15:09', 1),
(8, 'please share your ideas', 'Seller', '2026-09-03 15:15:23', 1);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `ProductID` int(11) NOT NULL,
  `ProductName` varchar(150) NOT NULL,
  `Description` text DEFAULT NULL,
  `Category` varchar(100) NOT NULL,
  `Stock` int(11) NOT NULL DEFAULT 0,
  `Price` decimal(10,2) NOT NULL,
  `Status` varchar(50) DEFAULT 'Available',
  `SellerID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`ProductID`, `ProductName`, `Description`, `Category`, `Stock`, `Price`, `Status`, `SellerID`) VALUES
(2, 'Chocolate cake', 'used chocos whith fresh chocolate', 'Bakery', 8, 500.00, 'Available', 1),
(3, 'Strawberry muffin cupcake', 'Vanila with strawberry', 'Bakery', 9, 200.00, 'Available', 1),
(4, 'Chocolate Cake', 'Fresh homemade chocolate cake', 'Food', 10, 120.00, 'Available', 3),
(5, 'Chocolate Brownie', 'Fresh homemade chocolate brownie', 'Food', 15, 70.00, 'Available', 3),
(6, 'Red Velvet Cupcakes', 'Soft red velvet cupcakes with cream cheese frosting', 'Food', 20, 100.00, 'Available', 3),
(7, 'Choco Chip Cookies', 'Freshly baked chocolate chip cookies', 'Food', 25, 40.00, 'Available', 3),
(9, 'vanilla cake', '', 'bakery', 10, 200.00, 'Available', 1),
(10, 'Mint Lemonade', '50ml cold lemonade juice', 'Drinks', 22, 70.00, 'Available', 1),
(11, 'Chocolate milkshake', '50ml milkshake with cocoPwoder at topings', 'Drinks', 0, 110.00, 'Available', 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_launch`
--

CREATE TABLE `product_launch` (
  `LaunchID` int(11) NOT NULL,
  `ProductName` varchar(150) NOT NULL,
  `Description` text DEFAULT NULL,
  `Category` varchar(100) DEFAULT NULL,
  `Price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `LaunchDate` datetime NOT NULL,
  `LaunchTime` time DEFAULT NULL,
  `CampusLocation` varchar(200) DEFAULT NULL,
  `Deadline` datetime NOT NULL,
  `Status` varchar(50) DEFAULT 'Upcoming',
  `RequiredReservations` int(11) NOT NULL DEFAULT 1,
  `CurrentReservation` int(11) DEFAULT 0,
  `sellerID` int(11) NOT NULL,
  `productID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_launch`
--

INSERT INTO `product_launch` (`LaunchID`, `ProductName`, `Description`, `Category`, `Price`, `LaunchDate`, `LaunchTime`, `CampusLocation`, `Deadline`, `Status`, `RequiredReservations`, `CurrentReservation`, `sellerID`, `productID`) VALUES
(1, 'Mint Lemonade', '50ml cold lemonade juice', 'Drinks', 70.00, '2026-09-12 11:00:00', '11:00:00', 'Cafeteria', '2026-09-10 00:00:00', 'Launched', 20, 22, 1, 10),
(2, 'Chocolate milkshake', '50ml milkshake with cocoPwoder at topings', 'Drinks', 110.00, '2026-09-06 11:30:00', '11:30:00', 'Cafeteria', '2026-09-06 00:00:00', 'Launched', 15, 15, 1, 11),
(3, 'strawberry milkshake', '100ml large size with fresh milk and strawberry', 'Drinks', 270.00, '2026-09-15 10:00:00', '10:00:00', 'Cafeteria', '2026-09-13 00:00:00', 'Upcoming', 10, 2, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `promotion`
--

CREATE TABLE `promotion` (
  `PromotionId` int(11) NOT NULL,
  `OfferType` varchar(50) NOT NULL,
  `DiscountValue` decimal(10,2) DEFAULT 0.00,
  `BuyQuantity` int(11) DEFAULT NULL,
  `GetQuantity` int(11) DEFAULT NULL,
  `StartDate` datetime NOT NULL,
  `EndDate` datetime NOT NULL,
  `SellerId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotion`
--

INSERT INTO `promotion` (`PromotionId`, `OfferType`, `DiscountValue`, `BuyQuantity`, `GetQuantity`, `StartDate`, `EndDate`, `SellerId`) VALUES
(2, 'BuyXGetY', 0.00, 1, 1, '2026-09-04 11:00:00', '2026-09-10 00:00:00', 1),
(3, 'Percentage', 10.00, 0, 0, '2026-09-03 01:00:00', '2026-09-10 00:00:00', 1),
(4, 'BuyXGetY', 0.00, 2, 1, '2026-09-02 01:00:00', '2026-09-25 02:00:00', 1),
(5, 'BuyXGetY', 0.00, 3, 1, '2026-09-02 11:00:00', '2026-09-12 01:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `purchase`
--

CREATE TABLE `purchase` (
  `PurchaseID` int(11) NOT NULL,
  `PurchaseType` varchar(50) DEFAULT NULL,
  `purchaseDate` datetime DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Completed',
  `sellerID` int(11) NOT NULL,
  `BuyerID` int(11) NOT NULL,
  `ReservationID` int(11) DEFAULT NULL,
  `FeedbackCode` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase`
--

INSERT INTO `purchase` (`PurchaseID`, `PurchaseType`, `purchaseDate`, `status`, `sellerID`, `BuyerID`, `ReservationID`, `FeedbackCode`) VALUES
(1, 'Online', '2026-09-03 21:11:26', 'Completed', 1, 1, 4, NULL),
(2, 'Online', '2026-09-03 21:11:29', 'Completed', 1, 1, 3, NULL),
(3, 'Online', '2026-09-04 02:40:28', 'Completed', 1, 1, 8, NULL),
(4, 'Online', '2026-09-04 02:47:04', 'Completed', 1, 1, 9, NULL),
(5, 'Online', '2026-09-04 13:04:55', 'Completed', 1, 1, 12, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `ReservationID` int(11) NOT NULL,
  `ReservationDate` datetime DEFAULT current_timestamp(),
  `ReservationTarget` varchar(200) DEFAULT NULL,
  `Status` varchar(50) DEFAULT 'Pending',
  `buyerID` int(11) NOT NULL,
  `announcementID` int(11) DEFAULT NULL,
  `offerID` int(11) DEFAULT NULL,
  `OfferUnitPrice` decimal(10,2) DEFAULT NULL,
  `PaidQuantity` int(11) DEFAULT NULL,
  `FreeQuantity` int(11) DEFAULT NULL,
  `productID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `contactNumber` varchar(20) NOT NULL,
  `sellerID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation`
--

INSERT INTO `reservation` (`ReservationID`, `ReservationDate`, `ReservationTarget`, `Status`, `buyerID`, `announcementID`, `offerID`, `OfferUnitPrice`, `PaidQuantity`, `FreeQuantity`, `productID`, `quantity`, `contactNumber`, `sellerID`) VALUES
(1, '2026-09-02 01:02:58', '01714103562', 'Rejected', 1, 3, NULL, NULL, NULL, NULL, 0, 0, '', 1),
(2, '2026-09-02 01:06:24', '01714103562', 'Completed', 1, 3, NULL, NULL, NULL, NULL, 0, 0, '', 1),
(3, '2026-09-02 01:54:20', '01714103562', 'Completed', 1, 3, NULL, NULL, NULL, NULL, 0, 0, '', 1),
(4, '2026-09-02 01:54:38', '01714103562', 'Completed', 1, 3, NULL, NULL, NULL, NULL, 0, 0, '', 1),
(5, '2026-09-02 02:01:16', '01714103562', 'Pending', 1, 2, NULL, NULL, NULL, NULL, 0, 0, '', 3),
(6, '2026-09-03 14:43:08', '01714103562', 'Pending', 1, 1, NULL, NULL, NULL, NULL, 0, 0, '', 3),
(7, '2026-09-03 14:49:35', '01714103562', 'Pending', 1, 1, NULL, NULL, NULL, NULL, 0, 0, '', 3),
(8, '2026-09-04 02:36:52', '01714103562', 'Completed', 1, NULL, NULL, NULL, NULL, NULL, 0, 0, '', 1),
(9, '2026-09-04 02:45:50', '01714103562', 'Completed', 1, NULL, NULL, NULL, NULL, NULL, 0, 0, '', 1),
(10, '2026-09-04 02:49:32', '01714103562', 'Pending', 1, NULL, NULL, NULL, NULL, NULL, 0, 0, '', 1),
(11, '2026-09-04 02:49:37', '01714103562', 'Pending', 1, NULL, NULL, NULL, NULL, NULL, 0, 0, '', 1),
(12, '2026-09-04 13:03:35', '01714103562', 'Completed', 1, NULL, NULL, NULL, NULL, NULL, 0, 0, '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `reserves`
--

CREATE TABLE `reserves` (
  `BuyerID` int(11) NOT NULL,
  `launchID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL DEFAULT 1,
  `reservationDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reserves`
--

INSERT INTO `reserves` (`BuyerID`, `launchID`, `Quantity`, `reservationDate`) VALUES
(1, 1, 22, '2026-09-02 03:21:38'),
(1, 2, 15, '2026-09-02 19:50:52'),
(1, 3, 2, '2026-09-04 13:02:01');

-- --------------------------------------------------------

--
-- Table structure for table `sales_announcement`
--

CREATE TABLE `sales_announcement` (
  `AnnouncementId` int(11) NOT NULL,
  `SellingTime` time NOT NULL,
  `SellingDate` date NOT NULL,
  `CampusLocation` varchar(200) NOT NULL,
  `Status` varchar(50) DEFAULT 'Upcoming',
  `SellerId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_announcement`
--

INSERT INTO `sales_announcement` (`AnnouncementId`, `SellingTime`, `SellingDate`, `CampusLocation`, `Status`, `SellerId`) VALUES
(1, '12:30:00', '2026-08-30', 'BRAC UNIVERSITY (6THFLOOR-PILAR 14)', 'Upcoming', 3),
(2, '11:00:00', '2026-08-31', 'BRAC UNIVERSITY (6THFLOOR-PILAR 14)', 'Upcoming', 3),
(3, '11:00:00', '2026-09-05', 'BRAC UNIVERSITY (6TH FLOOR-PILAR 13)', 'Upcoming', 1),
(5, '11:30:00', '2026-09-06', '', 'Available', 1);

-- --------------------------------------------------------

--
-- Table structure for table `seller`
--

CREATE TABLE `seller` (
  `sellerID` int(11) NOT NULL,
  `StudentID` varchar(30) NOT NULL,
  `department` varchar(100) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Mail` varchar(150) NOT NULL,
  `bussinessName` varchar(150) NOT NULL,
  `Phone` varchar(20) NOT NULL,
  `AvgRating` decimal(3,2) DEFAULT 0.00,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seller`
--

INSERT INTO `seller` (`sellerID`, `StudentID`, `department`, `Name`, `Mail`, `bussinessName`, `Phone`, `AvgRating`, `password`) VALUES
(1, '24101485', 'CSE', 'Afeefah Nusaybah', 'anfavns@gmail.com', 'Cake Bakery', '01714103578', 4.33, '$2y$10$Jgr8Ybtv4RQJ8.NRuJihnOFTxucCPB8lyqfC7vnRjViIC8YumK0nu'),
(2, '24101525', 'CSE', 'Tasfia Rahman', 'tasfia@gmail.com', 'Donut shop', '01714103987', 0.00, '$2y$10$dHcrP2Vy2/xFCwMrM81ms.dT.oh7iclBK1PDBH/jZjccd.2oGT2OG'),
(3, '24101600', 'CSE', 'ZARIN TASNIM', 'zarin.tasnim32@g.bracu.ac.bd', 'Tasnim\'s Bakery', '01517260219', 0.00, '$2y$10$CN57SNG1x0sayxRJO0rtkOFKZ.ScjM9cwqAGTat/Xq0ILeVbjBsK6');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applies_to`
--
ALTER TABLE `applies_to`
  ADD PRIMARY KEY (`promotionID`,`productID`),
  ADD KEY `productID` (`productID`);

--
-- Indexes for table `buyer`
--
ALTER TABLE `buyer`
  ADD PRIMARY KEY (`BuyerID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`ChatID`),
  ADD KEY `sellerID` (`sellerID`),
  ADD KEY `buyerID` (`buyerID`);

--
-- Indexes for table `contains`
--
ALTER TABLE `contains`
  ADD PRIMARY KEY (`reservationID`,`productID`),
  ADD KEY `productID` (`productID`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`FeedbackID`),
  ADD KEY `sellerID` (`sellerID`),
  ADD KEY `buyerID` (`buyerID`),
  ADD KEY `purchaseID` (`purchaseID`),
  ADD KEY `productID` (`productID`),
  ADD KEY `idx_feedback_product` (`productID`);

--
-- Indexes for table `has`
--
ALTER TABLE `has`
  ADD PRIMARY KEY (`purchaseID`,`productID`),
  ADD KEY `productID` (`productID`);

--
-- Indexes for table `includes`
--
ALTER TABLE `includes`
  ADD PRIMARY KEY (`announcementID`,`productID`),
  ADD KEY `productID` (`productID`);

--
-- Indexes for table `mentions`
--
ALTER TABLE `mentions`
  ADD PRIMARY KEY (`productID`,`messageID`),
  ADD KEY `messageID` (`messageID`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`MessageID`),
  ADD KEY `chatID` (`chatID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`ProductID`),
  ADD KEY `SellerID` (`SellerID`);

--
-- Indexes for table `product_launch`
--
ALTER TABLE `product_launch`
  ADD PRIMARY KEY (`LaunchID`),
  ADD KEY `sellerID` (`sellerID`),
  ADD KEY `productID` (`productID`);

--
-- Indexes for table `promotion`
--
ALTER TABLE `promotion`
  ADD PRIMARY KEY (`PromotionId`),
  ADD KEY `SellerId` (`SellerId`);

--
-- Indexes for table `purchase`
--
ALTER TABLE `purchase`
  ADD PRIMARY KEY (`PurchaseID`),
  ADD UNIQUE KEY `ReservationID` (`ReservationID`),
  ADD UNIQUE KEY `FeedbackCode` (`FeedbackCode`),
  ADD KEY `sellerID` (`sellerID`),
  ADD KEY `BuyerID` (`BuyerID`);

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`ReservationID`),
  ADD KEY `buyerID` (`buyerID`),
  ADD KEY `announcementID` (`announcementID`),
  ADD KEY `sellerID` (`sellerID`),
  ADD KEY `idx_reservation_offerID` (`offerID`);

--
-- Indexes for table `reserves`
--
ALTER TABLE `reserves`
  ADD PRIMARY KEY (`BuyerID`,`launchID`),
  ADD KEY `launchID` (`launchID`);

--
-- Indexes for table `sales_announcement`
--
ALTER TABLE `sales_announcement`
  ADD PRIMARY KEY (`AnnouncementId`),
  ADD KEY `SellerId` (`SellerId`);

--
-- Indexes for table `seller`
--
ALTER TABLE `seller`
  ADD PRIMARY KEY (`sellerID`),
  ADD UNIQUE KEY `StudentID` (`StudentID`),
  ADD UNIQUE KEY `Mail` (`Mail`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `buyer`
--
ALTER TABLE `buyer`
  MODIFY `BuyerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `ChatID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `FeedbackID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `MessageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `product_launch`
--
ALTER TABLE `product_launch`
  MODIFY `LaunchID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `promotion`
--
ALTER TABLE `promotion`
  MODIFY `PromotionId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `purchase`
--
ALTER TABLE `purchase`
  MODIFY `PurchaseID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `ReservationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sales_announcement`
--
ALTER TABLE `sales_announcement`
  MODIFY `AnnouncementId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `seller`
--
ALTER TABLE `seller`
  MODIFY `sellerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applies_to`
--
ALTER TABLE `applies_to`
  ADD CONSTRAINT `applies_to_ibfk_1` FOREIGN KEY (`promotionID`) REFERENCES `promotion` (`PromotionId`) ON DELETE CASCADE,
  ADD CONSTRAINT `applies_to_ibfk_2` FOREIGN KEY (`productID`) REFERENCES `product` (`ProductID`) ON DELETE CASCADE;

--
-- Constraints for table `chat`
--
ALTER TABLE `chat`
  ADD CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`sellerID`) REFERENCES `seller` (`sellerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_ibfk_2` FOREIGN KEY (`buyerID`) REFERENCES `buyer` (`BuyerID`) ON DELETE CASCADE;

--
-- Constraints for table `contains`
--
ALTER TABLE `contains`
  ADD CONSTRAINT `contains_ibfk_1` FOREIGN KEY (`reservationID`) REFERENCES `reservation` (`ReservationID`) ON DELETE CASCADE,
  ADD CONSTRAINT `contains_ibfk_2` FOREIGN KEY (`productID`) REFERENCES `product` (`ProductID`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`sellerID`) REFERENCES `seller` (`sellerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`buyerID`) REFERENCES `buyer` (`BuyerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_3` FOREIGN KEY (`purchaseID`) REFERENCES `purchase` (`PurchaseID`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_product` FOREIGN KEY (`productID`) REFERENCES `product` (`ProductID`) ON DELETE CASCADE;

--
-- Constraints for table `has`
--
ALTER TABLE `has`
  ADD CONSTRAINT `has_ibfk_1` FOREIGN KEY (`purchaseID`) REFERENCES `purchase` (`PurchaseID`) ON DELETE CASCADE,
  ADD CONSTRAINT `has_ibfk_2` FOREIGN KEY (`productID`) REFERENCES `product` (`ProductID`) ON DELETE CASCADE;

--
-- Constraints for table `includes`
--
ALTER TABLE `includes`
  ADD CONSTRAINT `includes_ibfk_1` FOREIGN KEY (`announcementID`) REFERENCES `sales_announcement` (`AnnouncementId`) ON DELETE CASCADE,
  ADD CONSTRAINT `includes_ibfk_2` FOREIGN KEY (`productID`) REFERENCES `product` (`ProductID`) ON DELETE CASCADE;

--
-- Constraints for table `mentions`
--
ALTER TABLE `mentions`
  ADD CONSTRAINT `mentions_ibfk_1` FOREIGN KEY (`productID`) REFERENCES `product` (`ProductID`) ON DELETE CASCADE,
  ADD CONSTRAINT `mentions_ibfk_2` FOREIGN KEY (`messageID`) REFERENCES `message` (`MessageID`) ON DELETE CASCADE;

--
-- Constraints for table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`chatID`) REFERENCES `chat` (`ChatID`) ON DELETE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`SellerID`) REFERENCES `seller` (`sellerID`) ON DELETE CASCADE;

--
-- Constraints for table `product_launch`
--
ALTER TABLE `product_launch`
  ADD CONSTRAINT `product_launch_ibfk_1` FOREIGN KEY (`sellerID`) REFERENCES `seller` (`sellerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_launch_ibfk_2` FOREIGN KEY (`productID`) REFERENCES `product` (`ProductID`) ON DELETE CASCADE;

--
-- Constraints for table `promotion`
--
ALTER TABLE `promotion`
  ADD CONSTRAINT `promotion_ibfk_1` FOREIGN KEY (`SellerId`) REFERENCES `seller` (`sellerID`) ON DELETE CASCADE;

--
-- Constraints for table `purchase`
--
ALTER TABLE `purchase`
  ADD CONSTRAINT `purchase_ibfk_1` FOREIGN KEY (`sellerID`) REFERENCES `seller` (`sellerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_ibfk_2` FOREIGN KEY (`BuyerID`) REFERENCES `buyer` (`BuyerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_ibfk_3` FOREIGN KEY (`ReservationID`) REFERENCES `reservation` (`ReservationID`) ON DELETE SET NULL;

--
-- Constraints for table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`buyerID`) REFERENCES `buyer` (`BuyerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`announcementID`) REFERENCES `sales_announcement` (`AnnouncementId`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservation_ibfk_3` FOREIGN KEY (`sellerID`) REFERENCES `seller` (`sellerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservation_ibfk_offer` FOREIGN KEY (`offerID`) REFERENCES `promotion` (`PromotionId`);

--
-- Constraints for table `reserves`
--
ALTER TABLE `reserves`
  ADD CONSTRAINT `reserves_ibfk_1` FOREIGN KEY (`BuyerID`) REFERENCES `buyer` (`BuyerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `reserves_ibfk_2` FOREIGN KEY (`launchID`) REFERENCES `product_launch` (`LaunchID`) ON DELETE CASCADE;

--
-- Constraints for table `sales_announcement`
--
ALTER TABLE `sales_announcement`
  ADD CONSTRAINT `sales_announcement_ibfk_1` FOREIGN KEY (`SellerId`) REFERENCES `seller` (`sellerID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
