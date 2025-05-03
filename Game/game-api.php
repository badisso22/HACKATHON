<?php
require_once '../Configurations/db.php';
requireLogin(); // Ensure user is logged in

header('Content-Type: application/json');

// Get the action from the request
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'getBosses':
        // Get language from user's onboarding preferences
        $userId = $_SESSION['user_id'];
        $language = getUserLanguage($userId);
        
        $bosses = getBosses($language);
        echo json_encode(['success' => true, 'bosses' => $bosses]);
        break;
        
    case 'getBossQuestions':
        $bossId = isset($_GET['bossId']) ? intval($_GET['bossId']) : 0;
        
        if ($bossId > 0) {
            $questions = getBossQuestions($bossId);
            echo json_encode(['success' => true, 'questions' => $questions]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid boss ID']);
        }
        break;
        
    case 'getNextQuestion':
        $bossId = isset($_GET['bossId']) ? intval($_GET['bossId']) : 0;
        $usedQuestions = isset($_GET['usedQuestions']) ? $_GET['usedQuestions'] : '';
        
        if ($bossId > 0) {
            $usedQuestionsArray = !empty($usedQuestions) ? explode(',', $usedQuestions) : [];
            $question = getNextQuestion($bossId, $usedQuestionsArray);
            echo json_encode(['success' => true, 'question' => $question]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid boss ID']);
        }
        break;
        
    case 'getUserProgress':
        $userId = $_SESSION['user_id'];
        $language = getUserLanguage($userId);
        
        $progress = getUserProgress($userId, $language);
        echo json_encode(['success' => true, 'progress' => $progress]);
        break;
        
    case 'getUserStats':
        $userId = $_SESSION['user_id'];
        $stats = getUserStats($userId);
        echo json_encode(['success' => true, 'stats' => $stats]);
        break;
        
    case 'getUserStreak':
        $userId = $_SESSION['user_id'];
        $streak = getUserStreak($userId);
        echo json_encode(['success' => true, 'streak' => $streak]);
        break;
        
    case 'saveProgress':
        $userId = $_SESSION['user_id'];
        $language = getUserLanguage($userId);
        $level = isset($_POST['level']) ? intval($_POST['level']) : 0;
        $score = isset($_POST['score']) ? intval($_POST['score']) : 0;
        $completed = isset($_POST['completed']) ? intval($_POST['completed']) : 0;
        
        $result = saveProgress($userId, $language, $level, $score);
        
        // If game is completed or failed, save the session
        if (isset($_POST['endGame']) && $_POST['endGame'] == 1) {
            $maxLevel = isset($_POST['maxLevel']) ? intval($_POST['maxLevel']) : $level;
            saveGameSession($userId, $language, $score, $maxLevel, $completed);
            
            // Update user stats
            if (isset($_POST['correctAnswers']) && isset($_POST['totalQuestions'])) {
                $correctAnswers = intval($_POST['correctAnswers']);
                $totalQuestions = intval($_POST['totalQuestions']);
                updateUserStats($userId, $score, $correctAnswers, $totalQuestions);
            }
            
            // Update user streak
            updateUserStreak($userId);
        }
        
        // Check for level up and streak rewards
        $response = ['success' => $result];
        
        // Get updated user stats to check for level up
        $oldStats = getUserStats($userId);
        $xpGained = $score;
        
        // Add XP to user
        addUserXP($userId, $xpGained);
        
        // Get updated stats after XP addition
        $newStats = getUserStats($userId);
        
        // Check if user leveled up
        if ($newStats['level'] > $oldStats['level']) {
            $response['levelUp'] = true;
            $response['newLevel'] = $newStats['level'];
        }
        
        // Check for streak rewards
        $streak = getUserStreak($userId);
        $streakReward = getStreakReward($streak['currentStreak']);
        
        if ($streakReward > 0) {
            addUserXP($userId, $streakReward);
            $response['streakUpdated'] = true;
            $response['currentStreak'] = $streak['currentStreak'];
            $response['streakReward'] = $streakReward;
        }
        
        echo json_encode($response);
        break;
        
    case 'changeLanguage':
        $userId = $_SESSION['user_id'];
        $language = isset($_POST['language']) ? $_POST['language'] : '';
        
        if (!empty($language)) {
            $result = changeUserLanguage($userId, $language);
            echo json_encode(['success' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid language']);
        }
        break;
        
    case 'getAvailableLanguages':
        $languages = getAvailableLanguages();
        echo json_encode(['success' => true, 'languages' => $languages]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

// Get user's selected language from onboarding
function getUserLanguage($userId) {
    global $conn;
    
    $sql = "SELECT selected_language FROM user_onboarding WHERE user_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['selected_language'];
    }
    
    // Default to Spanish if no language is set
    return 'Spanish';
}

// Change user's selected language
function changeUserLanguage($userId, $language) {
    global $conn;
    
    $sql = "UPDATE user_onboarding SET selected_language = ? WHERE user_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $language, $userId);
    
    return $stmt->execute();
}

// Get all available languages
function getAvailableLanguages() {
    global $conn;
    
    $languages = [];
    
    $sql = "SELECT DISTINCT language FROM game_bosses ORDER BY language";
    $result = $conn->query($sql);
    
    while ($row = $result->fetch_assoc()) {
        $languages[] = $row['language'];
    }
    
    return $languages;
}

// Get bosses for a specific language
function getBosses($language) {
    global $conn;
    
    $bosses = [];
    
    // Get all bosses for the language
    $sql = "SELECT * FROM game_bosses WHERE language = ? ORDER BY level_order";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $language);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($boss = $result->fetch_assoc()) {
        // Format boss data
        $bosses[] = [
            'id' => (int)$boss['boss_id'],
            'name' => $boss['name'],
            'hp' => (int)$boss['hp'],
            'emoji' => $boss['emoji'],
            'level_order' => (int)$boss['level_order']
        ];
    }
    
    return $bosses;
}

// Get all questions for a specific boss
function getBossQuestions($bossId) {
    global $conn;
    
    $questions = [];
    
    // Get all questions for the boss
    $sql = "SELECT * FROM boss_questions WHERE boss_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bossId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($question = $result->fetch_assoc()) {
        // Get options for this question
        $optionsSql = "SELECT option_text FROM question_options WHERE question_id = ?";
        $optionsStmt = $conn->prepare($optionsSql);
        $optionsStmt->bind_param("i", $question['question_id']);
        $optionsStmt->execute();
        $optionsResult = $optionsStmt->get_result();
        
        $options = [];
        while ($option = $optionsResult->fetch_assoc()) {
            $options[] = $option['option_text'];
        }
        
        // Format question data
        $questions[] = [
            'id' => (int)$question['question_id'],
            'question' => $question['question_text'],
            'correct' => $question['correct_answer'],
            'options' => $options
        ];
    }
    
    return $questions;
}

// Get a random question for a boss that hasn't been used yet
function getNextQuestion($bossId, $usedQuestionIds = []) {
    global $conn;
    
    $whereClause = '';
    $params = [$bossId];
    $types = "i";
    
    // If there are used questions, exclude them
    if (!empty($usedQuestionIds)) {
        $placeholders = implode(',', array_fill(0, count($usedQuestionIds), '?'));
        $whereClause = " AND question_id NOT IN ($placeholders)";
        
        foreach ($usedQuestionIds as $qid) {
            $params[] = $qid;
            $types .= "i";
        }
    }
    
    // Get a random question for the boss that hasn't been used
    $sql = "SELECT * FROM boss_questions WHERE boss_id = ?$whereClause ORDER BY RAND() LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($question = $result->fetch_assoc()) {
        // Get options for this question
        $optionsSql = "SELECT option_text FROM question_options WHERE question_id = ?";
        $optionsStmt = $conn->prepare($optionsSql);
        $optionsStmt->bind_param("i", $question['question_id']);
        $optionsStmt->execute();
        $optionsResult = $optionsStmt->get_result();
        
        $options = [];
        while ($option = $optionsResult->fetch_assoc()) {
            // Filter out any remaining generic wrong answers
            $optionText = $option['option_text'];
            if (strpos($optionText, 'Falsche Antwort') === false && 
                strpos($optionText, 'Risposta Sbagliata') === false) {
                $options[] = $optionText;
            }
        }
        
        // Make sure the correct answer is in the options
        if (!in_array($question['correct_answer'], $options)) {
            $options[] = $question['correct_answer'];
        }
        
        // If we don't have enough options, get some from other questions of the same boss
        if (count($options) < 3) {
            $otherOptionsSql = "SELECT DISTINCT qo.option_text 
                               FROM question_options qo 
                               JOIN boss_questions bq ON qo.question_id = bq.question_id 
                               WHERE bq.boss_id = ? 
                               AND qo.option_text != ? 
                               AND qo.option_text NOT LIKE 'Falsche Antwort%'
                               AND qo.option_text NOT LIKE 'Risposta Sbagliata%'
                               ORDER BY RAND() 
                               LIMIT ?";
            $otherOptionsStmt = $conn->prepare($otherOptionsSql);
            $neededOptions = 3 - count($options);
            $otherOptionsStmt->bind_param("isi", $bossId, $question['correct_answer'], $neededOptions);
            $otherOptionsStmt->execute();
            $otherOptionsResult = $otherOptionsStmt->get_result();
            
            while ($otherOption = $otherOptionsResult->fetch_assoc()) {
                if (!in_array($otherOption['option_text'], $options)) {
                    $options[] = $otherOption['option_text'];
                }
            }
        }
        
        // Limit to 3 options
        if (count($options) > 3) {
            // Keep the correct answer and randomly select others
            $correctAnswer = $question['correct_answer'];
            $otherOptions = array_filter($options, function($option) use ($correctAnswer) {
                return $option !== $correctAnswer;
            });
            
            // Shuffle and take only what we need
            shuffle($otherOptions);
            $otherOptions = array_slice($otherOptions, 0, 2);
            
            // Combine with correct answer
            $options = array_merge([$correctAnswer], $otherOptions);
        }
        
        // Format question data
        return [
            'id' => (int)$question['question_id'],
            'question' => $question['question_text'],
            'correct' => $question['correct_answer'],
            'options' => $options
        ];
    }
    
    // If all questions have been used, return the first one again
    $sql = "SELECT * FROM boss_questions WHERE boss_id = ? ORDER BY RAND() LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bossId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($question = $result->fetch_assoc()) {
        // Get options for this question
        $optionsSql = "SELECT option_text FROM question_options WHERE question_id = ?";
        $optionsStmt = $conn->prepare($optionsSql);
        $optionsStmt->bind_param("i", $question['question_id']);
        $optionsStmt->execute();
        $optionsResult = $optionsStmt->get_result();
        
        $options = [];
        while ($option = $optionsResult->fetch_assoc()) {
            // Filter out any remaining generic wrong answers
            $optionText = $option['option_text'];
            if (strpos($optionText, 'Falsche Antwort') === false && 
                strpos($optionText, 'Risposta Sbagliata') === false) {
                $options[] = $optionText;
            }
        }
        
        // Make sure the correct answer is in the options
        if (!in_array($question['correct_answer'], $options)) {
            $options[] = $question['correct_answer'];
        }
        
        // If we don't have enough options, get some from other questions of the same boss
        if (count($options) < 3) {
            $otherOptionsSql = "SELECT DISTINCT qo.option_text 
                               FROM question_options qo 
                               JOIN boss_questions bq ON qo.question_id = bq.question_id 
                               WHERE bq.boss_id = ? 
                               AND qo.option_text != ? 
                               AND qo.option_text NOT LIKE 'Falsche Antwort%'
                               AND qo.option_text NOT LIKE 'Risposta Sbagliata%'
                               ORDER BY RAND() 
                               LIMIT ?";
            $otherOptionsStmt = $conn->prepare($otherOptionsSql);
            $neededOptions = 3 - count($options);
            $otherOptionsStmt->bind_param("isi", $bossId, $question['correct_answer'], $neededOptions);
            $otherOptionsStmt->execute();
            $otherOptionsResult = $otherOptionsStmt->get_result();
            
            while ($otherOption = $otherOptionsResult->fetch_assoc()) {
                if (!in_array($otherOption['option_text'], $options)) {
                    $options[] = $otherOption['option_text'];
                }
            }
        }
        
        // Limit to 3 options
        if (count($options) > 3) {
            // Keep the correct answer and randomly select others
            $correctAnswer = $question['correct_answer'];
            $otherOptions = array_filter($options, function($option) use ($correctAnswer) {
                return $option !== $correctAnswer;
            });
            
            // Shuffle and take only what we need
            shuffle($otherOptions);
            $otherOptions = array_slice($otherOptions, 0, 2);
            
            // Combine with correct answer
            $options = array_merge([$correctAnswer], $otherOptions);
        }
        
        // Format question data
        return [
            'id' => (int)$question['question_id'],
            'question' => $question['question_text'],
            'correct' => $question['correct_answer'],
            'options' => $options
        ];
    }
    
    return null;
}

// Get user's game progress
function getUserProgress($userId, $language) {
    global $conn;
    
    $sql = "SELECT * FROM user_game_progress WHERE user_id = ? AND language = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $language);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return [
            'current_level' => (int)$row['current_level'],
            'highest_level' => (int)$row['highest_level'],
            'total_score' => (int)$row['total_score']
        ];
    } else {
        // Create a new progress record if none exists
        $insertSql = "INSERT INTO user_game_progress (user_id, language) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("is", $userId, $language);
        $insertStmt->execute();
        
        return [
            'current_level' => 0,
            'highest_level' => 0,
            'total_score' => 0
        ];
    }
}

// Get user stats (level, XP)
function getUserStats($userId) {
    global $conn;
    
    // Check if user has stats record
    $checkSql = "SELECT COUNT(*) as count FROM user_stats WHERE user_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $checkRow = $checkResult->fetch_assoc();
    
    // If no stats record exists, create one
    if ($checkRow['count'] == 0) {
        $insertSql = "INSERT INTO user_stats (user_id, xp, level) VALUES (?, 0, 1)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("i", $userId);
        $insertStmt->execute();
    }
    
    $sql = "SELECT us.*, xlt.xp_required as current_level_xp, 
           (SELECT xp_required FROM xp_level_thresholds WHERE level = us.level + 1) as next_level_xp 
           FROM user_stats us 
           JOIN xp_level_thresholds xlt ON us.level = xlt.level 
           WHERE us.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Calculate XP needed for next level and progress percentage
        $currentLevelXP = (int)$row['current_level_xp'];
        $nextLevelXP = (int)$row['next_level_xp'];
        $userXP = (int)$row['xp'];
        
        $xpForNextLevel = $nextLevelXP - $currentLevelXP;
        $userXPInCurrentLevel = $userXP - $currentLevelXP;
        $xpProgress = ($userXPInCurrentLevel / $xpForNextLevel) * 100;
        
        return [
            'level' => (int)$row['level'],
            'xp' => $userXP,
            'xpForNextLevel' => $xpForNextLevel,
            'xpProgress' => $xpProgress,
            'total_games_played' => (int)$row['total_games_played'],
            'total_questions_answered' => (int)$row['total_questions_answered'],
            'correct_answers' => (int)$row['correct_answers']
        ];
    }
    
    return null;
}

// Get user streak information
function getUserStreak($userId) {
    global $conn;
    
    // Check if user has streak record
    $checkSql = "SELECT COUNT(*) as count FROM user_streaks WHERE user_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $checkRow = $checkResult->fetch_assoc();
    
    // If no streak record exists, create one
    if ($checkRow['count'] == 0) {
        $insertSql = "INSERT INTO user_streaks (user_id, current_streak, longest_streak) VALUES (?, 0, 0)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("i", $userId);
        $insertStmt->execute();
    }
    
    $sql = "SELECT * FROM user_streaks WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return [
            'currentStreak' => (int)$row['current_streak'],
            'longestStreak' => (int)$row['longest_streak'],
            'lastPlayDate' => $row['last_play_date']
        ];
    }
    
    return [
        'currentStreak' => 0,
        'longestStreak' => 0,
        'lastPlayDate' => null
    ];
}

// Update user streak
function updateUserStreak($userId) {
    global $conn;
    
    // Get current date
    $today = date('Y-m-d');
    
    // Get user's streak info
    $sql = "SELECT * FROM user_streaks WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $currentStreak = (int)$row['current_streak'];
        $longestStreak = (int)$row['longest_streak'];
        $lastPlayDate = $row['last_play_date'];
        
        // If this is the first time playing or last play was yesterday, increment streak
        if ($lastPlayDate === null) {
            $currentStreak = 1;
        } else {
            $lastDate = new DateTime($lastPlayDate);
            $currentDate = new DateTime($today);
            $diff = $currentDate->diff($lastDate)->days;
            
            if ($diff === 1) {
                // Played yesterday, increment streak
                $currentStreak++;
            } else if ($diff > 1) {
                // Missed a day, reset streak
                $currentStreak = 1;
            }
            // If diff is 0, played today already, don't change streak
        }
        
        // Update longest streak if needed
        if ($currentStreak > $longestStreak) {
            $longestStreak = $currentStreak;
        }
        
        // Update streak in database
        $updateSql = "UPDATE user_streaks SET current_streak = ?, longest_streak = ?, last_play_date = ? WHERE user_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("iisi", $currentStreak, $longestStreak, $today, $userId);
        $updateStmt->execute();
    } else {
        // Create new streak record
        $insertSql = "INSERT INTO user_streaks (user_id, current_streak, longest_streak, last_play_date) VALUES (?, 1, 1, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("is", $userId, $today);
        $insertStmt->execute();
    }
}

// Get streak reward XP
function getStreakReward($streakDays) {
    global $conn;
    
    // Find the highest streak reward tier that the user has reached
    $sql = "SELECT xp_bonus FROM streak_rewards WHERE streak_days <= ? ORDER BY streak_days DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $streakDays);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return (int)$row['xp_bonus'];
    }
    
    return 0;
}

// Add XP to user
function addUserXP($userId, $xpAmount) {
    global $conn;
    
    // Get current user stats
    $sql = "SELECT us.*, xlt.xp_required as current_level_xp 
           FROM user_stats us 
           JOIN xp_level_thresholds xlt ON us.level = xlt.level 
           WHERE us.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $currentXP = (int)$row['xp'];
        $currentLevel = (int)$row['level'];
        $newXP = $currentXP + $xpAmount;
        
        // Check if user should level up
        $levelUpSql = "SELECT level FROM xp_level_thresholds WHERE xp_required <= ? ORDER BY level DESC LIMIT 1";
        $levelUpStmt = $conn->prepare($levelUpSql);
        $levelUpStmt->bind_param("i", $newXP);
        $levelUpStmt->execute();
        $levelUpResult = $levelUpStmt->get_result();
        
        if ($levelUpRow = $levelUpResult->fetch_assoc()) {
            $newLevel = (int)$levelUpRow['level'];
            
            // Update user stats with new XP and possibly new level
            $updateSql = "UPDATE user_stats SET xp = ?, level = ? WHERE user_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("iii", $newXP, $newLevel, $userId);
            $updateStmt->execute();
        } else {
            // Just update XP
            $updateSql = "UPDATE user_stats SET xp = ? WHERE user_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ii", $newXP, $userId);
            $updateStmt->execute();
        }
    } else {
        // Create new stats record if none exists
        $insertSql = "INSERT INTO user_stats (user_id, xp, level) VALUES (?, ?, 1)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("ii", $userId, $xpAmount);
        $insertStmt->execute();
    }
}

// Update user stats after a game
function updateUserStats($userId, $score, $correctAnswers, $totalQuestions) {
    global $conn;
    
    // Check if user has stats record
    $checkSql = "SELECT COUNT(*) as count FROM user_stats WHERE user_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $checkRow = $checkResult->fetch_assoc();
    
    // If no stats record exists, create one
    if ($checkRow['count'] == 0) {
        $insertSql = "INSERT INTO user_stats (user_id, xp, level, total_games_played, total_questions_answered, correct_answers) 
                     VALUES (?, 0, 1, 1, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("iii", $userId, $totalQuestions, $correctAnswers);
        $insertStmt->execute();
    } else {
        $sql = "UPDATE user_stats SET 
                total_games_played = total_games_played + 1,
                total_questions_answered = total_questions_answered + ?,
                correct_answers = correct_answers + ?
                WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $totalQuestions, $correctAnswers, $userId);
        $stmt->execute();
    }
}

// Save user's game progress
function saveProgress($userId, $language, $level, $score) {
    global $conn;
    
    // Check if progress record exists
    $checkSql = "SELECT * FROM user_game_progress WHERE user_id = ? AND language = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("is", $userId, $language);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing record
        $sql = "UPDATE user_game_progress 
                SET current_level = ?, 
                    highest_level = GREATEST(highest_level, ?), 
                    total_score = total_score + ?,
                    last_played = NOW() 
                WHERE user_id = ? AND language = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiss", $level, $level, $score, $userId, $language);
    } else {
        // Insert new record
        $sql = "INSERT INTO user_game_progress 
                (user_id, current_level, highest_level, total_score, language) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiis", $userId, $level, $level, $score, $language);
    }
    
    return $stmt->execute();
}

// Save a game session
function saveGameSession($userId, $language, $score, $maxLevel, $completed) {
    global $conn;
    
    $sql = "INSERT INTO game_sessions 
            (user_id, score, max_level_reached, completed, language, end_time) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiis", $userId, $score, $maxLevel, $completed, $language);
    
    return $stmt->execute();
}
?>
