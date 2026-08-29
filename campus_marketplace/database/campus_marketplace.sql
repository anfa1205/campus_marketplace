-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 28, 2026 at 05:54 PM
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
(1, 'Anika Rahman', '01714103562', 'anikarahman@gamil.com', '$2y$10$NBVN4ZZwrj.Dej1TaleMj.DgNDS3g4AspbYzF1Mcz/setKKT2xdrm');

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `ChatID` int(11) NOT NULL,
  `sellerID` int(11) NOT NULL,
  `buyerID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contains`
--

CREATE TABLE `contains` (
  `reservationID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `purchaseID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `has`
--

CREATE TABLE `has` (
  `purchaseID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `includes`
--

CREATE TABLE `includes` (
  `announcementID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `product_launch`
--

CREATE TABLE `product_launch` (
  `LaunchID` int(11) NOT NULL,
  `LaunchDate` datetime NOT NULL,
  `Deadline` datetime NOT NULL,
  `Status` varchar(50) DEFAULT 'Upcoming',
  `CurrentReservation` int(11) DEFAULT 0,
  `sellerID` int(11) NOT NULL,
  `productID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotion`
--

CREATE TABLE `promotion` (
  `PromotionId` int(11) NOT NULL,
  `OfferType` varchar(50) NOT NULL,
  `DiscountValue` decimal(10,2) DEFAULT 0.00,
  `StartDate` datetime NOT NULL,
  `EndDate` datetime NOT NULL,
  `SellerId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `BuyerID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `announcementID` int(11) NOT NULL,
  `sellerID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reserves`
--

CREATE TABLE `reserves` (
  `BuyerID` int(11) NOT NULL,
  `launchID` int(11) NOT NULL,
  `reservationDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_announcement`
--

CREATE TABLE `sales_announcement` (
  `AnnouncementId` int(11) NOT NULL,
  `SellingTime` time NOT NULL,
  `SellingDate` date NOT NULL,
  `AvailableQuantity` int(11) NOT NULL,
  `CampusLocation` varchar(200) NOT NULL,
  `Status` varchar(50) DEFAULT 'Upcoming',
  `SellerId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, '24101485', 'CSE', 'Afeefah Nusaybah', 'anfavns@gmail.com', 'Cake Bakery', '01714103578', 0.00, '$2y$10$Jgr8Ybtv4RQJ8.NRuJihnOFTxucCPB8lyqfC7vnRjViIC8YumK0nu'),
(2, '24101525', 'CSE', 'Tasfia Rahman', 'tasfia@gmail.com', 'Donut shop', '01714103987', 0.00, '$2y$10$dHcrP2Vy2/xFCwMrM81ms.dT.oh7iclBK1PDBH/jZjccd.2oGT2OG');

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
  ADD KEY `purchaseID` (`purchaseID`);

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
  ADD KEY `sellerID` (`sellerID`),
  ADD KEY `BuyerID` (`BuyerID`);

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`ReservationID`),
  ADD KEY `buyerID` (`buyerID`),
  ADD KEY `announcementID` (`announcementID`),
  ADD KEY `sellerID` (`sellerID`);

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
  MODIFY `BuyerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `ChatID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `FeedbackID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `MessageID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_launch`
--
ALTER TABLE `product_launch`
  MODIFY `LaunchID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotion`
--
ALTER TABLE `promotion`
  MODIFY `PromotionId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase`
--
ALTER TABLE `purchase`
  MODIFY `PurchaseID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `ReservationID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_announcement`
--
ALTER TABLE `sales_announcement`
  MODIFY `AnnouncementId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seller`
--
ALTER TABLE `seller`
  MODIFY `sellerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  ADD CONSTRAINT `feedback_ibfk_3` FOREIGN KEY (`purchaseID`) REFERENCES `purchase` (`PurchaseID`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `purchase_ibfk_2` FOREIGN KEY (`BuyerID`) REFERENCES `buyer` (`BuyerID`) ON DELETE CASCADE;

--
-- Constraints for table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`buyerID`) REFERENCES `buyer` (`BuyerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`announcementID`) REFERENCES `sales_announcement` (`AnnouncementId`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservation_ibfk_3` FOREIGN KEY (`sellerID`) REFERENCES `seller` (`sellerID`) ON DELETE CASCADE;

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
