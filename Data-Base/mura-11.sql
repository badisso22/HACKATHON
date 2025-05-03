-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 03, 2025 at 01:18 PM
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
-- Table structure for table `boss_questions`
--

CREATE TABLE `boss_questions` (
  `question_id` int(11) NOT NULL,
  `boss_id` int(11) NOT NULL,
  `question_text` varchar(255) NOT NULL,
  `correct_answer` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `boss_questions`
--

INSERT INTO `boss_questions` (`question_id`, `boss_id`, `question_text`, `correct_answer`) VALUES
(1, 1, 'Translate: \"Apple\"', 'Manzana'),
(2, 1, 'Translate: \"Orange\"', 'Naranja'),
(3, 1, 'Translate: \"Banana\"', 'Plátano'),
(4, 1, 'Translate: \"Strawberry\"', 'Fresa'),
(5, 1, 'Translate: \"Grape\"', 'Uva'),
(6, 2, 'Translate: \"Dog\"', 'Perro'),
(7, 2, 'Translate: \"Cat\"', 'Gato'),
(8, 2, 'Translate: \"Bird\"', 'Pájaro'),
(9, 2, 'Translate: \"Fish\"', 'Pez'),
(10, 2, 'Translate: \"Horse\"', 'Caballo'),
(11, 3, 'Translate: \"House\"', 'Casa'),
(12, 3, 'Translate: \"Car\"', 'Carro'),
(13, 3, 'Translate: \"Door\"', 'Puerta'),
(14, 3, 'Translate: \"Window\"', 'Ventana'),
(15, 3, 'Translate: \"Kitchen\"', 'Cocina'),
(16, 4, 'Translate: \"Book\"', 'Libro'),
(17, 4, 'Translate: \"Paper\"', 'Papel'),
(18, 4, 'Translate: \"Pencil\"', 'Lápiz'),
(19, 4, 'Translate: \"School\"', 'Escuela'),
(20, 4, 'Translate: \"Teacher\"', 'Maestro'),
(21, 5, 'Translate: \"Apple\"', 'Apfel'),
(22, 5, 'Translate: \"Orange\"', 'Orange'),
(23, 5, 'Translate: \"Banana\"', 'Banane'),
(24, 5, 'Translate: \"Strawberry\"', 'Erdbeere'),
(25, 5, 'Translate: \"Grape\"', 'Traube'),
(26, 6, 'Translate: \"Dog\"', 'Hund'),
(27, 6, 'Translate: \"Cat\"', 'Katze'),
(28, 6, 'Translate: \"Bird\"', 'Vogel'),
(29, 6, 'Translate: \"Fish\"', 'Fisch'),
(30, 6, 'Translate: \"Horse\"', 'Pferd'),
(31, 7, 'Translate: \"House\"', 'Haus'),
(32, 7, 'Translate: \"Car\"', 'Auto'),
(33, 7, 'Translate: \"Door\"', 'Tür'),
(34, 7, 'Translate: \"Window\"', 'Fenster'),
(35, 7, 'Translate: \"Kitchen\"', 'Küche'),
(36, 8, 'Translate: \"Book\"', 'Buch'),
(37, 8, 'Translate: \"Paper\"', 'Papier'),
(38, 8, 'Translate: \"Pencil\"', 'Bleistift'),
(39, 8, 'Translate: \"School\"', 'Schule'),
(40, 8, 'Translate: \"Teacher\"', 'Lehrer'),
(41, 9, 'Translate: \"Apple\"', 'Mela'),
(42, 9, 'Translate: \"Orange\"', 'Arancia'),
(43, 9, 'Translate: \"Banana\"', 'Banana'),
(44, 9, 'Translate: \"Strawberry\"', 'Fragola'),
(45, 9, 'Translate: \"Grape\"', 'Uva'),
(46, 10, 'Translate: \"Dog\"', 'Cane'),
(47, 10, 'Translate: \"Cat\"', 'Gatto'),
(48, 10, 'Translate: \"Bird\"', 'Uccello'),
(49, 10, 'Translate: \"Fish\"', 'Pesce'),
(50, 10, 'Translate: \"Horse\"', 'Cavallo'),
(51, 11, 'Translate: \"House\"', 'Casa'),
(52, 11, 'Translate: \"Car\"', 'Macchina'),
(53, 11, 'Translate: \"Door\"', 'Porta'),
(54, 11, 'Translate: \"Window\"', 'Finestra'),
(55, 11, 'Translate: \"Kitchen\"', 'Cucina'),
(56, 12, 'Translate: \"Book\"', 'Libro'),
(57, 12, 'Translate: \"Paper\"', 'Carta'),
(58, 12, 'Translate: \"Pencil\"', 'Matita'),
(59, 12, 'Translate: \"School\"', 'Scuola'),
(60, 12, 'Translate: \"Teacher\"', 'Insegnante'),
(61, 5, 'Translate: \"Apple\"', 'Apfel'),
(62, 5, 'Translate: \"Orange\"', 'Orange'),
(63, 5, 'Translate: \"Banana\"', 'Banane'),
(64, 5, 'Translate: \"Strawberry\"', 'Erdbeere'),
(65, 5, 'Translate: \"Grape\"', 'Traube');

-- --------------------------------------------------------

--
-- Table structure for table `game_bosses`
--

CREATE TABLE `game_bosses` (
  `boss_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'e.g. "Novice Scholar"',
  `hp` int(11) NOT NULL COMMENT 'Boss health points',
  `emoji` varchar(10) NOT NULL COMMENT 'Boss emoji character',
  `level_order` int(11) NOT NULL COMMENT 'Order of appearance',
  `language` varchar(50) NOT NULL COMMENT 'Language this boss belongs to'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `game_bosses`
--

INSERT INTO `game_bosses` (`boss_id`, `name`, `hp`, `emoji`, `level_order`, `language`) VALUES
(1, 'Novice Scholar', 3, '🧙‍♂️', 0, 'Spanish'),
(2, 'Language Master', 4, '🐉', 1, 'Spanish'),
(3, 'Word Wizard', 5, '🧛', 2, 'Spanish'),
(4, 'Linguistics Professor', 5, '🧙‍♂️', 3, 'Spanish'),
(5, 'Anfänger Gelehrter', 3, '🧙‍♂️', 0, 'German'),
(6, 'Sprachmeister', 4, '🐉', 1, 'German'),
(7, 'Wortzauberer', 5, '🧛', 2, 'German'),
(8, 'Linguistikprofessor', 5, '🧙‍♂️', 3, 'German'),
(9, 'Studioso Principiante', 3, '🧙‍♂️', 0, 'Italian'),
(10, 'Maestro di Lingua', 4, '🐉', 1, 'Italian'),
(11, 'Mago delle Parole', 5, '🧛', 2, 'Italian'),
(12, 'Professore di Linguistica', 5, '🧙‍♂️', 3, 'Italian');

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

--
-- Dumping data for table `game_sessions`
--

INSERT INTO `game_sessions` (`session_id`, `user_id`, `start_time`, `end_time`, `score`, `max_level_reached`, `completed`, `language`) VALUES
(1, 2, '2025-05-03 11:01:27', '2025-05-03 11:01:27', 10, 0, 0, 'Spanish'),
(2, 2, '2025-05-03 11:01:54', '2025-05-03 11:01:54', 0, 0, 0, 'Spanish');

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
(2, 2, 'mehdi', 'moussous', '2003-11-27', 555555555, 'male', 'alger', '2025-05-02 08:31:21'),
(3, 3, 'anis', 'ferrah', '2005-08-04', 555555555, 'male', 'alger', '2025-05-03 00:34:03'),
(4, 4, 'ethan', 'alexander', '2004-05-21', 666666666, 'male', 'alger', '2025-05-03 01:13:36'),
(5, 5, 'rayan', 'miloudi', '2006-05-02', 777777777, 'male', 'Harrach', '2025-05-03 08:30:02');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_categories`
--

CREATE TABLE `lesson_categories` (
  `category_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `icon` varchar(10) NOT NULL,
  `color` varchar(20) NOT NULL,
  `slug` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lesson_categories`
--

INSERT INTO `lesson_categories` (`category_id`, `title`, `description`, `icon`, `color`, `slug`) VALUES
(1, 'Numbers 1-10', 'Learn to count from 1 to 10', '🔢', '#7e57c2', 'numbers_basic'),
(2, 'Numbers 20-1000', 'Learn larger numbers and counting', '📊', '#5c6bc0', 'numbers_advanced'),
(3, 'Colors', 'Learn the names of common colors', '🎨', '#26a69a', 'colors'),
(4, 'Animals', 'Learn the names of common animals', '🐾', '#ec407a', 'animals'),
(5, 'Greetings', 'Learn common greetings and introductions', '👋', '#ffa726', 'greetings'),
(6, 'Food & Drinks', 'Learn vocabulary for food and beverages', '🍽️', '#66bb6a', 'food'),
(7, 'Family Members', 'Learn words for family relationships', '👪', '#8d6e63', 'family'),
(8, 'Common Phrases', 'Learn essential everyday phrases', '💬', '#42a5f5', 'phrases');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_content`
--

CREATE TABLE `lesson_content` (
  `content_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `language` varchar(50) NOT NULL,
  `term` varchar(100) NOT NULL,
  `translation` varchar(100) NOT NULL,
  `pronunciation` varchar(100) NOT NULL,
  `audio_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lesson_content`
--

INSERT INTO `lesson_content` (`content_id`, `category_id`, `language`, `term`, `translation`, `pronunciation`, `audio_file`) VALUES
(1, 1, 'Spanish', '1', 'Uno', 'oo-no', NULL),
(2, 1, 'Spanish', '2', 'Dos', 'dose', NULL),
(3, 1, 'Spanish', '3', 'Tres', 'trace', NULL),
(4, 1, 'Spanish', '4', 'Cuatro', 'kwah-tro', NULL),
(5, 1, 'Spanish', '5', 'Cinco', 'seen-ko', NULL),
(6, 1, 'Spanish', '6', 'Seis', 'says', NULL),
(7, 1, 'Spanish', '7', 'Siete', 'see-eh-teh', NULL),
(8, 1, 'Spanish', '8', 'Ocho', 'oh-cho', NULL),
(9, 1, 'Spanish', '9', 'Nueve', 'noo-eh-veh', NULL),
(10, 1, 'Spanish', '10', 'Diez', 'dee-ess', NULL),
(11, 1, 'French', '1', 'Un', 'uh', NULL),
(12, 1, 'French', '2', 'Deux', 'duh', NULL),
(13, 1, 'French', '3', 'Trois', 'twah', NULL),
(14, 1, 'French', '4', 'Quatre', 'katr', NULL),
(15, 1, 'French', '5', 'Cinq', 'sank', NULL),
(16, 1, 'French', '6', 'Six', 'sees', NULL),
(17, 1, 'French', '7', 'Sept', 'set', NULL),
(18, 1, 'French', '8', 'Huit', 'weet', NULL),
(19, 1, 'French', '9', 'Neuf', 'nuhf', NULL),
(20, 1, 'French', '10', 'Dix', 'dees', NULL),
(21, 1, 'German', '1', 'Eins', 'eyns', NULL),
(22, 1, 'German', '2', 'Zwei', 'tsvey', NULL),
(23, 1, 'German', '3', 'Drei', 'dry', NULL),
(24, 1, 'German', '4', 'Vier', 'feer', NULL),
(25, 1, 'German', '5', 'Fünf', 'fuenf', NULL),
(26, 1, 'German', '6', 'Sechs', 'zeks', NULL),
(27, 1, 'German', '7', 'Sieben', 'zee-ben', NULL),
(28, 1, 'German', '8', 'Acht', 'akht', NULL),
(29, 1, 'German', '9', 'Neun', 'noyn', NULL),
(30, 1, 'German', '10', 'Zehn', 'tsayn', NULL),
(31, 1, 'Italian', '1', 'Uno', 'oo-no', NULL),
(32, 1, 'Italian', '2', 'Due', 'doo-eh', NULL),
(33, 1, 'Italian', '3', 'Tre', 'treh', NULL),
(34, 1, 'Italian', '4', 'Quattro', 'kwat-tro', NULL),
(35, 1, 'Italian', '5', 'Cinque', 'cheen-kweh', NULL),
(36, 1, 'Italian', '6', 'Sei', 'say', NULL),
(37, 1, 'Italian', '7', 'Sette', 'set-teh', NULL),
(38, 1, 'Italian', '8', 'Otto', 'ot-to', NULL),
(39, 1, 'Italian', '9', 'Nove', 'no-veh', NULL),
(40, 1, 'Italian', '10', 'Dieci', 'dee-eh-chee', NULL),
(41, 1, 'English', '1', 'One', 'wun', NULL),
(42, 1, 'English', '2', 'Two', 'too', NULL),
(43, 1, 'English', '3', 'Three', 'three', NULL),
(44, 1, 'English', '4', 'Four', 'for', NULL),
(45, 1, 'English', '5', 'Five', 'fayv', NULL),
(46, 1, 'English', '6', 'Six', 'siks', NULL),
(47, 1, 'English', '7', 'Seven', 'seh-ven', NULL),
(48, 1, 'English', '8', 'Eight', 'ayt', NULL),
(49, 1, 'English', '9', 'Nine', 'nayn', NULL),
(50, 1, 'English', '10', 'Ten', 'ten', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `status` enum('published','draft','archived') NOT NULL DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`post_id`, `user_id`, `title`, `content`, `created_at`, `updated_at`, `status`) VALUES
(1, 2, 'crazy', 'I learned a new word today hola in English is hello !!!', '2025-05-03 00:31:36', NULL, 'published'),
(2, 4, 'hello', 'I am a new member here , hallo !☺️', '2025-05-03 01:21:33', NULL, 'published'),
(3, 4, 'I love this app', 'i\'m level 2 now , looking like a pro sehr schön😏😎', '2025-05-03 04:01:44', NULL, 'published'),
(4, 4, 'test', 'hello !!!', '2025-05-03 04:13:15', NULL, 'published');

-- --------------------------------------------------------

--
-- Table structure for table `question_options`
--

CREATE TABLE `question_options` (
  `option_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `question_options`
--

INSERT INTO `question_options` (`option_id`, `question_id`, `option_text`) VALUES
(1, 1, 'Manzana'),
(2, 1, 'Naranja'),
(3, 1, 'Plátano'),
(4, 2, 'Naranja'),
(5, 2, 'Manzana'),
(6, 2, 'Limón'),
(7, 3, 'Plátano'),
(8, 3, 'Manzana'),
(9, 3, 'Pera'),
(10, 4, 'Fresa'),
(11, 4, 'Cereza'),
(12, 4, 'Frambuesa'),
(13, 5, 'Uva'),
(14, 5, 'Manzana'),
(15, 5, 'Pera'),
(16, 6, 'Perro'),
(17, 6, 'Gato'),
(18, 6, 'Ratón'),
(19, 7, 'Gato'),
(20, 7, 'Perro'),
(21, 7, 'León'),
(22, 8, 'Pájaro'),
(23, 8, 'Pez'),
(24, 8, 'Insecto'),
(25, 9, 'Pez'),
(26, 9, 'Tiburón'),
(27, 9, 'Delfín'),
(28, 10, 'Caballo'),
(29, 10, 'Vaca'),
(30, 10, 'Oveja'),
(31, 11, 'Casa'),
(32, 11, 'Apartamento'),
(33, 11, 'Edificio'),
(34, 12, 'Carro'),
(35, 12, 'Bicicleta'),
(36, 12, 'Autobús'),
(37, 13, 'Puerta'),
(38, 13, 'Ventana'),
(39, 13, 'Pared'),
(40, 14, 'Ventana'),
(41, 14, 'Puerta'),
(42, 14, 'Techo'),
(43, 15, 'Cocina'),
(44, 15, 'Baño'),
(45, 15, 'Dormitorio'),
(46, 16, 'Libro'),
(47, 16, 'Revista'),
(48, 16, 'Periódico'),
(49, 17, 'Papel'),
(50, 17, 'Cartón'),
(51, 17, 'Plástico'),
(52, 18, 'Lápiz'),
(53, 18, 'Bolígrafo'),
(54, 18, 'Marcador'),
(55, 19, 'Escuela'),
(56, 19, 'Universidad'),
(57, 19, 'Biblioteca'),
(58, 20, 'Maestro'),
(59, 20, 'Estudiante'),
(60, 20, 'Director'),
(61, 21, 'Apfel'),
(62, 22, 'Orange'),
(63, 23, 'Banane'),
(64, 24, 'Erdbeere'),
(65, 25, 'Traube'),
(66, 26, 'Hund'),
(67, 27, 'Katze'),
(68, 28, 'Vogel'),
(69, 29, 'Fisch'),
(70, 30, 'Pferd'),
(71, 31, 'Haus'),
(72, 32, 'Auto'),
(73, 33, 'Tür'),
(74, 34, 'Fenster'),
(75, 35, 'Küche'),
(76, 36, 'Buch'),
(77, 37, 'Papier'),
(78, 38, 'Bleistift'),
(79, 39, 'Schule'),
(80, 40, 'Lehrer'),
(92, 21, 'Falsche Antwort 1'),
(93, 22, 'Falsche Antwort 1'),
(94, 23, 'Falsche Antwort 1'),
(95, 24, 'Falsche Antwort 1'),
(96, 25, 'Falsche Antwort 1'),
(97, 26, 'Falsche Antwort 1'),
(98, 27, 'Falsche Antwort 1'),
(99, 28, 'Falsche Antwort 1'),
(100, 29, 'Falsche Antwort 1'),
(101, 30, 'Falsche Antwort 1'),
(102, 31, 'Falsche Antwort 1'),
(103, 32, 'Falsche Antwort 1'),
(104, 33, 'Falsche Antwort 1'),
(105, 34, 'Falsche Antwort 1'),
(106, 35, 'Falsche Antwort 1'),
(107, 36, 'Falsche Antwort 1'),
(108, 37, 'Falsche Antwort 1'),
(109, 38, 'Falsche Antwort 1'),
(110, 39, 'Falsche Antwort 1'),
(111, 40, 'Falsche Antwort 1'),
(123, 21, 'Falsche Antwort 2'),
(124, 22, 'Falsche Antwort 2'),
(125, 23, 'Falsche Antwort 2'),
(126, 24, 'Falsche Antwort 2'),
(127, 25, 'Falsche Antwort 2'),
(128, 26, 'Falsche Antwort 2'),
(129, 27, 'Falsche Antwort 2'),
(130, 28, 'Falsche Antwort 2'),
(131, 29, 'Falsche Antwort 2'),
(132, 30, 'Falsche Antwort 2'),
(133, 31, 'Falsche Antwort 2'),
(134, 32, 'Falsche Antwort 2'),
(135, 33, 'Falsche Antwort 2'),
(136, 34, 'Falsche Antwort 2'),
(137, 35, 'Falsche Antwort 2'),
(138, 36, 'Falsche Antwort 2'),
(139, 37, 'Falsche Antwort 2'),
(140, 38, 'Falsche Antwort 2'),
(141, 39, 'Falsche Antwort 2'),
(142, 40, 'Falsche Antwort 2'),
(154, 41, 'Mela'),
(155, 42, 'Arancia'),
(156, 43, 'Banana'),
(157, 44, 'Fragola'),
(158, 45, 'Uva'),
(159, 46, 'Cane'),
(160, 47, 'Gatto'),
(161, 48, 'Uccello'),
(162, 49, 'Pesce'),
(163, 50, 'Cavallo'),
(164, 51, 'Casa'),
(165, 52, 'Macchina'),
(166, 53, 'Porta'),
(167, 54, 'Finestra'),
(168, 55, 'Cucina'),
(169, 56, 'Libro'),
(170, 57, 'Carta'),
(171, 58, 'Matita'),
(172, 59, 'Scuola'),
(173, 60, 'Insegnante'),
(185, 41, 'Risposta Sbagliata 1'),
(186, 42, 'Risposta Sbagliata 1'),
(187, 43, 'Risposta Sbagliata 1'),
(188, 44, 'Risposta Sbagliata 1'),
(189, 45, 'Risposta Sbagliata 1'),
(190, 46, 'Risposta Sbagliata 1'),
(191, 47, 'Risposta Sbagliata 1'),
(192, 48, 'Risposta Sbagliata 1'),
(193, 49, 'Risposta Sbagliata 1'),
(194, 50, 'Risposta Sbagliata 1'),
(195, 51, 'Risposta Sbagliata 1'),
(196, 52, 'Risposta Sbagliata 1'),
(197, 53, 'Risposta Sbagliata 1'),
(198, 54, 'Risposta Sbagliata 1'),
(199, 55, 'Risposta Sbagliata 1'),
(200, 56, 'Risposta Sbagliata 1'),
(201, 57, 'Risposta Sbagliata 1'),
(202, 58, 'Risposta Sbagliata 1'),
(203, 59, 'Risposta Sbagliata 1'),
(204, 60, 'Risposta Sbagliata 1'),
(216, 41, 'Risposta Sbagliata 2'),
(217, 42, 'Risposta Sbagliata 2'),
(218, 43, 'Risposta Sbagliata 2'),
(219, 44, 'Risposta Sbagliata 2'),
(220, 45, 'Risposta Sbagliata 2'),
(221, 46, 'Risposta Sbagliata 2'),
(222, 47, 'Risposta Sbagliata 2'),
(223, 48, 'Risposta Sbagliata 2'),
(224, 49, 'Risposta Sbagliata 2'),
(225, 50, 'Risposta Sbagliata 2'),
(226, 51, 'Risposta Sbagliata 2'),
(227, 52, 'Risposta Sbagliata 2'),
(228, 53, 'Risposta Sbagliata 2'),
(229, 54, 'Risposta Sbagliata 2'),
(230, 55, 'Risposta Sbagliata 2'),
(231, 56, 'Risposta Sbagliata 2'),
(232, 57, 'Risposta Sbagliata 2'),
(233, 58, 'Risposta Sbagliata 2'),
(234, 59, 'Risposta Sbagliata 2'),
(235, 60, 'Risposta Sbagliata 2');

-- --------------------------------------------------------

--
-- Table structure for table `streak_rewards`
--

CREATE TABLE `streak_rewards` (
  `streak_days` int(11) NOT NULL,
  `xp_bonus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `streak_rewards`
--

INSERT INTO `streak_rewards` (`streak_days`, `xp_bonus`) VALUES
(1, 10),
(3, 25),
(5, 50),
(7, 100),
(14, 200),
(21, 350),
(30, 500),
(60, 1000),
(90, 1500),
(180, 3000),
(365, 10000);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_ID` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `email` varchar(40) NOT NULL,
  `password` varchar(100) NOT NULL,
  `onboarding_complete` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_ID`, `username`, `profile_picture`, `email`, `password`, `onboarding_complete`) VALUES
(2, 'mehdi.moussous2162', NULL, 'mehdi.moussous2162@gmail.com', '$2y$10$ucEZ/V4BESifbzpkGz1TbeQ0UkuXWGKxhQdKMpcUtp2HxNGvIaStS', 1),
(3, 'aniso', NULL, 'anis.ferrah101@gmail.com', '$2y$10$EdKYaTXtwh92xwiWQnAbkOtNhpU6/dYoUasIHGUOjJDYFYVh.6eR2', 1),
(4, 'Ethan', NULL, 'ethan.ax@gmail.com', '$2y$10$IqPWRL.9tfFRlQ2KmlcHf.heymTdwPnlXuiSOSOKk1XbCHImnE4z2', 1),
(5, 'rayanM', NULL, 'rayan.miloudi@gmail.com', '$2y$10$9ew2JpDEzGYEvBYCgUA4SuCa9m0MeNRjReuiWG81QpGRfeko0S1Pm', 1);

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

--
-- Dumping data for table `user_game_progress`
--

INSERT INTO `user_game_progress` (`progress_id`, `user_id`, `current_level`, `highest_level`, `total_score`, `language`, `last_played`) VALUES
(1, 2, 0, 0, 10, 'Spanish', '2025-05-03 11:01:54');

-- --------------------------------------------------------

--
-- Table structure for table `user_lesson_progress`
--

CREATE TABLE `user_lesson_progress` (
  `progress_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `last_accessed` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_lesson_progress`
--

INSERT INTO `user_lesson_progress` (`progress_id`, `user_id`, `category_id`, `completed`, `last_accessed`) VALUES
(1, 4, 2, 1, '2025-05-03 03:58:33'),
(2, 4, 6, 1, '2025-05-03 03:58:44'),
(3, 4, 7, 1, '2025-05-03 03:58:53'),
(4, 4, 1, 1, '2025-05-03 04:02:53'),
(5, 5, 6, 1, '2025-05-03 08:32:42');

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
(1, 2, 'Spanish', 'connections', '3 min', 'advanced', 1, '2025-05-02 08:41:58', '2025-05-02 12:00:04'),
(2, 3, 'Spanish', 'connections', '30 min', 'beginner', 1, '2025-05-03 00:34:03', '2025-05-03 00:34:16'),
(3, 4, 'German', 'studies', '15 min', 'beginner', 1, '2025-05-03 01:13:36', '2025-05-03 01:13:45'),
(4, 5, 'French', 'fun', '40 min', 'advanced', 1, '2025-05-03 08:30:02', '2025-05-03 08:30:38');

-- --------------------------------------------------------

--
-- Table structure for table `user_stats`
--

CREATE TABLE `user_stats` (
  `stat_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `xp` int(11) NOT NULL DEFAULT 0,
  `level` int(11) NOT NULL DEFAULT 1,
  `total_games_played` int(11) NOT NULL DEFAULT 0,
  `total_questions_answered` int(11) NOT NULL DEFAULT 0,
  `correct_answers` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_stats`
--

INSERT INTO `user_stats` (`stat_id`, `user_id`, `xp`, `level`, `total_games_played`, `total_questions_answered`, `correct_answers`) VALUES
(1, 2, 30, 1, 2, 6, 1),
(2, 4, 260, 3, 0, 0, 0),
(3, 5, 10, 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_streaks`
--

CREATE TABLE `user_streaks` (
  `streak_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `current_streak` int(11) NOT NULL DEFAULT 0,
  `longest_streak` int(11) NOT NULL DEFAULT 0,
  `last_play_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_streaks`
--

INSERT INTO `user_streaks` (`streak_id`, `user_id`, `current_streak`, `longest_streak`, `last_play_date`) VALUES
(1, 2, 1, 1, '2025-05-03'),
(2, 4, 1, 1, '2025-05-03'),
(3, 5, 1, 1, '2025-05-03');

-- --------------------------------------------------------

--
-- Table structure for table `xp_level_thresholds`
--

CREATE TABLE `xp_level_thresholds` (
  `level` int(11) NOT NULL,
  `xp_required` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `xp_level_thresholds`
--

INSERT INTO `xp_level_thresholds` (`level`, `xp_required`) VALUES
(1, 0),
(2, 100),
(3, 250),
(4, 500),
(5, 1000),
(6, 1750),
(7, 2750),
(8, 4000),
(9, 5500),
(10, 7500),
(11, 10000),
(12, 13000),
(13, 16500),
(14, 20500),
(15, 25000),
(16, 30000),
(17, 35500),
(18, 41500),
(19, 48000),
(20, 55000);

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
-- Indexes for table `boss_questions`
--
ALTER TABLE `boss_questions`
  ADD PRIMARY KEY (`question_id`),
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
-- Indexes for table `lesson_categories`
--
ALTER TABLE `lesson_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `lesson_content`
--
ALTER TABLE `lesson_content`
  ADD PRIMARY KEY (`content_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `question_options`
--
ALTER TABLE `question_options`
  ADD PRIMARY KEY (`option_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `streak_rewards`
--
ALTER TABLE `streak_rewards`
  ADD PRIMARY KEY (`streak_days`);

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
-- Indexes for table `user_lesson_progress`
--
ALTER TABLE `user_lesson_progress`
  ADD PRIMARY KEY (`progress_id`),
  ADD UNIQUE KEY `user_category` (`user_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `user_onboarding`
--
ALTER TABLE `user_onboarding`
  ADD PRIMARY KEY (`onboarding_id`),
  ADD KEY `user_ID` (`user_ID`);

--
-- Indexes for table `user_stats`
--
ALTER TABLE `user_stats`
  ADD PRIMARY KEY (`stat_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `user_streaks`
--
ALTER TABLE `user_streaks`
  ADD PRIMARY KEY (`streak_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `xp_level_thresholds`
--
ALTER TABLE `xp_level_thresholds`
  ADD PRIMARY KEY (`level`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `boss_options`
--
ALTER TABLE `boss_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `boss_questions`
--
ALTER TABLE `boss_questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `game_bosses`
--
ALTER TABLE `game_bosses`
  MODIFY `boss_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `game_sessions`
--
ALTER TABLE `game_sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `learner`
--
ALTER TABLE `learner`
  MODIFY `learner_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `lesson_categories`
--
ALTER TABLE `lesson_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `lesson_content`
--
ALTER TABLE `lesson_content`
  MODIFY `content_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `question_options`
--
ALTER TABLE `question_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=247;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_game_progress`
--
ALTER TABLE `user_game_progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_lesson_progress`
--
ALTER TABLE `user_lesson_progress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_onboarding`
--
ALTER TABLE `user_onboarding`
  MODIFY `onboarding_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_stats`
--
ALTER TABLE `user_stats`
  MODIFY `stat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_streaks`
--
ALTER TABLE `user_streaks`
  MODIFY `streak_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `boss_options`
--
ALTER TABLE `boss_options`
  ADD CONSTRAINT `boss_options_ibfk_1` FOREIGN KEY (`boss_id`) REFERENCES `game_bosses` (`boss_id`) ON DELETE CASCADE;

--
-- Constraints for table `boss_questions`
--
ALTER TABLE `boss_questions`
  ADD CONSTRAINT `boss_questions_ibfk_1` FOREIGN KEY (`boss_id`) REFERENCES `game_bosses` (`boss_id`) ON DELETE CASCADE;

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
-- Constraints for table `lesson_content`
--
ALTER TABLE `lesson_content`
  ADD CONSTRAINT `lesson_content_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `lesson_categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_ID`);

--
-- Constraints for table `question_options`
--
ALTER TABLE `question_options`
  ADD CONSTRAINT `question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `boss_questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_game_progress`
--
ALTER TABLE `user_game_progress`
  ADD CONSTRAINT `user_game_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_ID`);

--
-- Constraints for table `user_lesson_progress`
--
ALTER TABLE `user_lesson_progress`
  ADD CONSTRAINT `user_lesson_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_ID`),
  ADD CONSTRAINT `user_lesson_progress_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `lesson_categories` (`category_id`);

--
-- Constraints for table `user_onboarding`
--
ALTER TABLE `user_onboarding`
  ADD CONSTRAINT `user_onboarding_ibfk_1` FOREIGN KEY (`user_ID`) REFERENCES `users` (`user_ID`);

--
-- Constraints for table `user_stats`
--
ALTER TABLE `user_stats`
  ADD CONSTRAINT `user_stats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_ID`) ON DELETE CASCADE;

--
-- Constraints for table `user_streaks`
--
ALTER TABLE `user_streaks`
  ADD CONSTRAINT `user_streaks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
