-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 02, 2025 at 11:25 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mura`
--

-- --------------------------------------------------------

--
-- Table structure for table `learner`
--

CREATE TABLE `learner` (
  `learner_ID` int(11) NOT NULL,
  `user_ID` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `date_of_birth` date NOT NULL,
  `phone_number` int(10) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `address` varchar(100) NOT NULL,
  `date_of_registrations` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `learner`
--

INSERT INTO `learner` (`learner_ID`, `user_ID`, `first_name`, `last_name`, `date_of_birth`, `phone_number`, `gender`, `address`, `date_of_registrations`) VALUES
(2, 2, 'mehdi', 'moussous', '2003-11-27', 555555555, 'male', 'alger', '2025-05-02 08:31:21');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_ID` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(40) NOT NULL,
  `password` varchar(100) NOT NULL,
  `onboarding_complete` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_ID`, `username`, `email`, `password`, `onboarding_complete`) VALUES
(2, 'mehdi.moussous2162', 'mehdi.moussous2162@gmail.com', '$2y$10$ucEZ/V4BESifbzpkGz1TbeQ0UkuXWGKxhQdKMpcUtp2HxNGvIaStS', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_onboarding`
--

CREATE TABLE `user_onboarding` (
  `onboarding_id` int(11) NOT NULL,
  `user_ID` int(11) NOT NULL,
  `selected_language` varchar(50) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `daily_goal` int(11) DEFAULT NULL,
  `proficiency_level` varchar(20) DEFAULT NULL,
  `is_complete` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_onboarding`
--

INSERT INTO `user_onboarding` (`onboarding_id`, `user_ID`, `selected_language`, `reason`, `daily_goal`, `proficiency_level`, `is_complete`, `created_at`, `updated_at`) VALUES
(1, 2, 'Spanish', 'fun', NULL, NULL, 0, '2025-05-02 08:41:58', '2025-05-02 09:16:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `learner`
--
ALTER TABLE `learner`
  ADD PRIMARY KEY (`learner_ID`),
  ADD KEY `user_ID` (`user_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_ID`);

--
-- Indexes for table `user_onboarding`
--
ALTER TABLE `user_onboarding`
  ADD PRIMARY KEY (`onboarding_id`),
  ADD KEY `user_ID` (`user_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `learner`
--
ALTER TABLE `learner`
  MODIFY `learner_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_onboarding`
--
ALTER TABLE `user_onboarding`
  MODIFY `onboarding_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `learner`
--
ALTER TABLE `learner`
  ADD CONSTRAINT `learner_ibfk_1` FOREIGN KEY (`user_ID`) REFERENCES `users` (`user_ID`);

--
-- Constraints for table `user_onboarding`
--
ALTER TABLE `user_onboarding`
  ADD CONSTRAINT `user_onboarding_ibfk_1` FOREIGN KEY (`user_ID`) REFERENCES `users` (`user_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
