-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 02, 2025 at 02:28 PM
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
-- Table structure for table `boss_options`
--

CREATE TABLE `boss_options` (
  `option_id` int(11) NOT NULL,
  `boss_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `boss_options`
--

INSERT INTO `boss_options` (`option_id`, `boss_id`, `option_text`) VALUES
(1, 1, 'Manzana'),
(2, 1, 'Banana'),
(3, 1, 'Pera'),
(4, 2, 'Perro'),
(5, 2, 'Gato'),
(6, 2, 'Caballo'),
(7, 3, 'Casa'),
(8, 3, 'Carro'),
(9, 3, 'Puerta'),
(10, 4, 'Libro'),
(11, 4, 'Papel'),
(12, 4, 'Lápiz');

-- --------------------------------------------------------

--
-- Table structure for table `game_bosses`
--

CREATE TABLE `game_bosses` (
  `boss_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'e.g. "Novice Scholar"',
  `hp` int(11) NOT NULL COMMENT 'Boss health points',
  `emoji` varchar(10) NOT NULL COMMENT 'Boss emoji character',
  `question` varchar(255) NOT NULL COMMENT 'The question text',
  `correct` varchar(255) NOT NULL COMMENT 'Correct answer',
  `level_order` int(11) NOT NULL COMMENT 'Order of appearance',
  `language` varchar(50) NOT NULL COMMENT 'Language this boss belongs to'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `game_bosses`
--

INSERT INTO `game_bosses` (`boss_id`, `name`, `hp`, `emoji`, `question`, `correct`, `level_order`, `language`) VALUES
(1, 'Novice Scholar', 3, '🧙‍♂️', 'Translate: \'Apple\'', 'Manzana', 0, 'Spanish'),
(2, 'Language Master', 4, '🐉', 'Translate: \'Dog\'', 'Perro', 1, 'Spanish'),
(3, 'Word Wizard', 5, '🧛', 'Translate: \'House\'', 'Casa', 2, 'Spanish'),
(4, 'Linguistics Professor', 5, '🧙‍♂️', 'Translate: \'Book\'', 'Libro', 3, 'Spanish');

-- --------------------------------------------------------

--
-- Table structure for table `game_sessions`
--

CREATE TABLE `game_sessions` (
  `session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `end_time` timestamp NULL DEFAULT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `max_level_reached` int(11) NOT NULL DEFAULT 0,
  `completed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 if all bosses defeated',
  `language` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(2, 'mehdi.moussous2162', 'mehdi.moussous2162@gmail.com', '$2y$10$ucEZ/V4BESifbzpkGz1TbeQ0UkuXWGKxhQdKMpcUtp2HxNGvIaStS', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_game_progress`
--

CREATE TABLE `user_game_progress` (
  `progress_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `current_level` int(11) NOT NULL DEFAULT 0 COMMENT 'Current boss index',
  `highest_level` int(11) NOT NULL DEFAULT 0 COMMENT 'Highest boss index reached',
  `total_score` int(11) NOT NULL DEFAULT 0 COMMENT 'Accumulated score',
  `language` varchar(50) NOT NULL COMMENT 'Language being learned',
  `last_played` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_onboarding`
--

CREATE TABLE `user_onboarding` (
  `onboarding_id` int(11) NOT NULL,
  `user_ID` int(11) NOT NULL,
  `selected_language` varchar(50) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `daily_goal` varchar(255) DEFAULT NULL,
  `proficiency_level` varchar(20) DEFAULT NULL,
  `is_complete` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_onboarding`
--

INSERT INTO `user_onboarding` (`onboarding_id`, `user_ID`, `selected_language`, `reason`, `daily_goal`, `proficiency_level`, `is_complete`, `created_at`, `updated_at`) VALUES
(1, 2, 'Spanish', 'connections', '3 min', 'advanced', 1, '2025-05-02 08:41:58', '2025-05-02 12:00:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `boss_options`
--
ALTER TABLE `boss_options`
  ADD PRIMARY KEY (`option_id`),
  ADD KEY `boss_id` (`boss_id`);

--
-- Indexes for table `game_bosses`
--
ALTER TABLE `game_bosses`
  ADD PRIMARY KEY (`boss_id`);

--
-- Indexes for table `game_sessions`
--
ALTER TABLE `game_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user_id` (`user_id`);

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
-- Indexes for table `user_game_progress`
--
ALTER TABLE `user_game_progress`
  ADD PRIMARY KEY (`progress_id`),
  ADD UNIQUE KEY `user_language` (`user_id`,`language`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `boss_options`
--
ALTER TABLE `boss_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `game_bosses`
--
ALTER TABLE `game_bosses`
  MODIFY `boss_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `game_sessions`
--
ALTER TABLE `game_sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `user_game_progress`
--
ALTER TABLE `user_game_progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_onboarding`
--
ALTER TABLE `user_onboarding`
  MODIFY `onboarding_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `boss_options`
--
ALTER TABLE `boss_options`
  ADD CONSTRAINT `boss_options_ibfk_1` FOREIGN KEY (`boss_id`) REFERENCES `game_bosses` (`boss_id`) ON DELETE CASCADE;

--
-- Constraints for table `game_sessions`
--
ALTER TABLE `game_sessions`
  ADD CONSTRAINT `game_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_ID`);

--
-- Constraints for table `learner`
--
ALTER TABLE `learner`
  ADD CONSTRAINT `learner_ibfk_1` FOREIGN KEY (`user_ID`) REFERENCES `users` (`user_ID`);

--
-- Constraints for table `user_game_progress`
--
ALTER TABLE `user_game_progress`
  ADD CONSTRAINT `user_game_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_ID`);

--
-- Constraints for table `user_onboarding`
--
ALTER TABLE `user_onboarding`
  ADD CONSTRAINT `user_onboarding_ibfk_1` FOREIGN KEY (`user_ID`) REFERENCES `users` (`user_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
