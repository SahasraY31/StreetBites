-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2024 at 04:11 AM
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
-- Database: `streetbites`
--

-- --------------------------------------------------------

--
-- Table structure for table `foodtruckinfo`
--

CREATE TABLE `foodtruckinfo` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `tags` text DEFAULT NULL,
  `des` text DEFAULT NULL,
  `logo` blob DEFAULT NULL,
  `hours` varchar(255) DEFAULT NULL,
  `longitude` decimal(10,8) DEFAULT NULL,
  `latitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `foodtruckinfo`
--

INSERT INTO `foodtruckinfo` (`id`, `name`, `tags`, `des`, `logo`, `hours`, `longitude`, `latitude`) VALUES
(1, 'Pancake Hut', 'Breakfast', 'Delicious pancakes and breakfast options.', NULL, '6:00 AM - 2:00 PM', 10.70000000, 20.50000000),
(2, 'Funk Dave\'s Diner', 'American', 'Classic American diner with funky vibes.', NULL, '8:00 AM - 10:00 PM', 10.90000000, 44.80000000),
(3, 'Gotham Bites', 'American, Fast Food', 'A classic New York diner serving the best burgers, fries, and shakes in town.', NULL, 'Mon-Sun: 8 AM - 10 PM', -73.98542800, 40.74881700),
(4, 'Taco Fiesta', 'Mexican', 'Authentic Mexican street food with tacos, burritos, and more.', NULL, 'Mon-Sat: 11 AM - 9 PM', -73.93524200, 40.73061000),
(5, 'Pasta Heaven', 'Italian', 'Freshly made pasta dishes and traditional Italian cuisine.', NULL, 'Mon-Sun: 12 PM - 10 PM', -73.98930800, 40.74189500),
(6, 'Dragon Express', 'Chinese', 'Delicious Chinese takeout with classic dishes like General Tso\'s chicken.', NULL, 'Mon-Sun: 10 AM - 11 PM', -73.98713900, 40.74881700),
(7, 'Curry Delight', 'Indian', 'Aromatic Indian curries, naan, and more, with vegan options.', NULL, 'Tue-Sun: 11 AM - 10 PM', -73.97800300, 40.75272500),
(8, 'American Grill', 'American', 'Classic American favorites like burgers, fries, and milkshakes.', NULL, 'Mon-Fri: 10 AM - 8 PM', -73.98674800, 40.75889600),
(9, 'Bangkok Street Eats', 'Thai', 'Authentic Thai street food with spicy noodles and curries.', NULL, 'Mon-Sun: 11 AM - 9 PM', -73.99578900, 40.70749500),
(10, 'Sushi on the Go', 'Japanese', 'Fresh sushi rolls and sashimi prepared daily.', NULL, 'Mon-Sat: 11 AM - 9 PM', -73.97498700, 40.75910200),
(11, 'Green Delights', 'Vegan', 'Healthy and tasty vegan dishes made from fresh ingredients.', NULL, 'Mon-Fri: 9 AM - 7 PM', -73.97723400, 40.78306000),
(12, 'BBQ Pit Masters', 'BBQ', 'Smoky, juicy BBQ meats with all the fixings.', NULL, 'Wed-Sun: 12 PM - 10 PM', -73.96029900, 40.77272700),
(13, 'Ocean Fresh', 'Seafood', 'Freshly caught seafood dishes served daily.', NULL, 'Mon-Sun: 11 AM - 8 PM', -73.97133400, 40.76449200),
(14, 'Sweet Treats', 'Desserts', 'Cakes, pastries, and ice cream to satisfy your sweet tooth.', NULL, 'Mon-Sun: 10 AM - 10 PM', -73.98459300, 40.75917000),
(15, 'Mediterranean Bites', 'Mediterranean', 'Delicious Mediterranean wraps, salads, and platters.', NULL, 'Mon-Sat: 11 AM - 9 PM', -73.97980500, 40.75102400),
(16, 'Athens to Go', 'Greek', 'Authentic Greek gyros, souvlaki, and more.', NULL, 'Mon-Fri: 10 AM - 8 PM', -73.98958600, 40.74855000),
(17, 'Morning Glory', 'Breakfast', 'Hearty breakfasts with eggs, pancakes, and fresh coffee.', NULL, 'Mon-Sun: 7 AM - 2 PM', -73.97276100, 40.78343600),
(18, 'Halal Cart', 'Halal', 'Flavorful and affordable halal meals with all the sauces.', NULL, 'Mon-Sat: 10 AM - 11 PM', -73.97812700, 40.75811000),
(19, 'Quick Bites', 'Fast Food', 'Fast, tasty, and affordable meals for when you’re on the go.', NULL, 'Mon-Sun: 10 AM - 10 PM', -73.95657800, 40.80538300);

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `tag_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `tag_name`) VALUES
(5, 'American'),
(9, 'BBQ'),
(14, 'Breakfast'),
(3, 'Chinese'),
(11, 'Desserts'),
(16, 'Fast Food'),
(13, 'Greek'),
(15, 'Halal'),
(4, 'Indian'),
(2, 'Italian'),
(7, 'Japanese'),
(12, 'Mediterranean'),
(1, 'Mexican'),
(10, 'Seafood'),
(6, 'Thai'),
(8, 'Vegan');

-- --------------------------------------------------------

--
-- Table structure for table `userdata`
--

CREATE TABLE `userdata` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `isadmin` tinyint(1) DEFAULT 0,
  `tags` text DEFAULT NULL,
  `foodtruck_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `foodtruckinfo`
--
ALTER TABLE `foodtruckinfo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tag_name` (`tag_name`);

--
-- Indexes for table `userdata`
--
ALTER TABLE `userdata`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `foodtruckinfo`
--
ALTER TABLE `foodtruckinfo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `userdata`
--
ALTER TABLE `userdata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
