-- Table for game bosses/levels exactly as in your game.js
CREATE TABLE `game_bosses` (
  `boss_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'e.g. "Novice Scholar"',
  `hp` int(11) NOT NULL COMMENT 'Boss health points',
  `emoji` varchar(10) NOT NULL COMMENT 'Boss emoji character',
  `question` varchar(255) NOT NULL COMMENT 'The question text',
  `correct` varchar(255) NOT NULL COMMENT 'Correct answer',
  `level_order` int(11) NOT NULL COMMENT 'Order of appearance',
  `language` varchar(50) NOT NULL COMMENT 'Language this boss belongs to',
  PRIMARY KEY (`boss_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table for boss answer options
CREATE TABLE `boss_options` (
  `option_id` int(11) NOT NULL AUTO_INCREMENT,
  `boss_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL,
  PRIMARY KEY (`option_id`),
  KEY `boss_id` (`boss_id`),
  CONSTRAINT `boss_options_ibfk_1` FOREIGN KEY (`boss_id`) REFERENCES `game_bosses` (`boss_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table for user game progress
CREATE TABLE `user_game_progress` (
  `progress_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `current_level` int(11) NOT NULL DEFAULT 0 COMMENT 'Current boss index',
  `highest_level` int(11) NOT NULL DEFAULT 0 COMMENT 'Highest boss index reached',
  `total_score` int(11) NOT NULL DEFAULT 0 COMMENT 'Accumulated score',
  `language` varchar(50) NOT NULL COMMENT 'Language being learned',
  `last_played` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`progress_id`),
  UNIQUE KEY `user_language` (`user_id`, `language`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_game_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table for game sessions
CREATE TABLE `game_sessions` (
  `session_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `end_time` timestamp NULL DEFAULT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `max_level_reached` int(11) NOT NULL DEFAULT 0,
  `completed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 if all bosses defeated',
  `language` varchar(50) NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `game_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert bosses from your game.js file
INSERT INTO `game_bosses` (`name`, `hp`, `emoji`, `question`, `correct`, `level_order`, `language`) VALUES
('Novice Scholar', 3, '🧙‍♂️', 'Translate: ''Apple''', 'Manzana', 0, 'Spanish'),
('Language Master', 4, '🐉', 'Translate: ''Dog''', 'Perro', 1, 'Spanish'),
('Word Wizard', 5, '🧛', 'Translate: ''House''', 'Casa', 2, 'Spanish'),
('Linguistics Professor', 5, '🧙‍♂️', 'Translate: ''Book''', 'Libro', 3, 'Spanish');

-- Insert options for the first boss
INSERT INTO `boss_options` (`boss_id`, `option_text`) VALUES
(1, 'Manzana'),
(1, 'Banana'),
(1, 'Pera');

-- Insert options for the second boss
INSERT INTO `boss_options` (`boss_id`, `option_text`) VALUES
(2, 'Perro'),
(2, 'Gato'),
(2, 'Caballo');

-- Insert options for the third boss
INSERT INTO `boss_options` (`boss_id`, `option_text`) VALUES
(3, 'Casa'),
(3, 'Carro'),
(3, 'Puerta');

-- Insert options for the fourth boss
INSERT INTO `boss_options` (`boss_id`, `option_text`) VALUES
(4, 'Libro'),
(4, 'Papel'),
(4, 'Lápiz');
