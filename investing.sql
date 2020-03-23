-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: מרץ 23, 2020 בזמן 11:40 PM
-- גרסת שרת: 10.4.8-MariaDB
-- PHP Version: 7.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `investing`
--

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `catalogs`
--

CREATE TABLE `catalogs` (
  `CatalogID` int(11) NOT NULL,
  `CatalogName` varchar(100) NOT NULL,
  `CatalogDescription` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- הוצאת מידע עבור טבלה `catalogs`
--

INSERT INTO `catalogs` (`CatalogID`, `CatalogName`, `CatalogDescription`) VALUES
(111, 'Technology', 'Technology Products'),
(222, 'Sport', 'Sport Products'),
(333, 'Cosmetics', 'Cosmetics Products'),
(444, '\'aass\'', '\'rrr\''),
(555, '12345', 'dsdsa');

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `products`
--

CREATE TABLE `products` (
  `ProductID` int(11) NOT NULL,
  `ProductName` varchar(100) NOT NULL,
  `ProductDescription` text NOT NULL,
  `ProductPrice` double NOT NULL,
  `Stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- הוצאת מידע עבור טבלה `products`
--

INSERT INTO `products` (`ProductID`, `ProductName`, `ProductDescription`, `ProductPrice`, `Stock`) VALUES
(1, 'iphone 10', ' new iphone 10 Apple', 900, 43),
(2, 'computer lenovo', 'lenovo abc', 600, 28),
(3, 'trx', 'trx band', 90, 20),
(4, 'shampo', 'shampo anti scurf', 15, 0),
(5, 'ems10', 'Body fit abs muscle tuning', 250, 30),
(6, 'iphone 10111', ' new iphone 10 Appl11e', 900, 40);

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `productscatalogsrelation`
--

CREATE TABLE `productscatalogsrelation` (
  `ProductID` int(11) NOT NULL,
  `CatalogID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- הוצאת מידע עבור טבלה `productscatalogsrelation`
--

INSERT INTO `productscatalogsrelation` (`ProductID`, `CatalogID`) VALUES
(1, 111),
(2, 111),
(3, 222),
(5, 111),
(5, 222);

-- --------------------------------------------------------

--
-- מבנה טבלה עבור טבלה `shoppingcarts`
--

CREATE TABLE `shoppingcarts` (
  `CartID` int(11) NOT NULL,
  `ProductId` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- הוצאת מידע עבור טבלה `shoppingcarts`
--

INSERT INTO `shoppingcarts` (`CartID`, `ProductId`, `Quantity`) VALUES
(1, 1, 7),
(1, 3, 20),
(1, 5, 5),
(2, 4, 10),
(3, 4, 10);

--
-- Indexes for dumped tables
--

--
-- אינדקסים לטבלה `catalogs`
--
ALTER TABLE `catalogs`
  ADD PRIMARY KEY (`CatalogID`),
  ADD UNIQUE KEY `CatalogName` (`CatalogName`);

--
-- אינדקסים לטבלה `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ProductID`),
  ADD UNIQUE KEY `ProductName` (`ProductName`);

--
-- אינדקסים לטבלה `productscatalogsrelation`
--
ALTER TABLE `productscatalogsrelation`
  ADD PRIMARY KEY (`ProductID`,`CatalogID`),
  ADD KEY `fk_2` (`CatalogID`);

--
-- אינדקסים לטבלה `shoppingcarts`
--
ALTER TABLE `shoppingcarts`
  ADD PRIMARY KEY (`CartID`,`ProductId`),
  ADD KEY `ProductId` (`ProductId`);

--
-- הגבלות לטבלאות שהוצאו
--

--
-- הגבלות לטבלה `productscatalogsrelation`
--
ALTER TABLE `productscatalogsrelation`
  ADD CONSTRAINT `fk_1` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`),
  ADD CONSTRAINT `fk_2` FOREIGN KEY (`CatalogID`) REFERENCES `catalogs` (`CatalogID`);

--
-- הגבלות לטבלה `shoppingcarts`
--
ALTER TABLE `shoppingcarts`
  ADD CONSTRAINT `shoppingcarts_ibfk_1` FOREIGN KEY (`ProductId`) REFERENCES `products` (`ProductID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
