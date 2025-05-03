<?php
session_start();
require_once '../Configurations/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("
    SELECT u.username, u.onboarding_complete, uo.selected_language, uo.proficiency_level,
           l.first_name, l.last_name, us.level
    FROM users u 
    LEFT JOIN user_onboarding uo ON u.user_ID = uo.user_ID 
    LEFT JOIN learner l ON u.user_ID = l.user_ID
    LEFT JOIN user_stats us ON u.user_ID = us.user_id
    WHERE u.user_ID = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../Login/signin.php");
    exit();
}

$user = $result->fetch_assoc();

$username = $user['username'] ?? 'User';
$first_name = $user['first_name'] ?? 'User';
$selected_language = $user['selected_language'] ?? 'Spanish';
$proficiency_level = $user['proficiency_level'] ?? 'Beginner';
$user_level = $user['level'] ?? 1;

// Fetch streak data
$streakStmt = $conn->prepare("
    SELECT current_streak, longest_streak, last_play_date
    FROM user_streaks 
    WHERE user_id = ?
");
$streakStmt->bind_param("i", $user_id);
$streakStmt->execute();
$streakResult = $streakStmt->get_result();
$streakData = $streakResult->fetch_assoc();

$current_streak = $streakData['current_streak'] ?? 0;

// Update streak if it's a new day
$today = date('Y-m-d');
$lastPlayDate = $streakData['last_play_date'] ?? null;

if ($lastPlayDate !== $today) {
    // If last play date was yesterday, increment streak
    if ($lastPlayDate && date('Y-m-d', strtotime($lastPlayDate . ' +1 day')) === $today) {
        $current_streak++;
        $longest_streak = max($current_streak, $streakData['longest_streak'] ?? 0);
        
        // Update streak in database
        $updateStreakStmt = $conn->prepare("
            UPDATE user_streaks 
            SET current_streak = ?, longest_streak = ?, last_play_date = ?
            WHERE user_id = ?
        ");
        $updateStreakStmt->bind_param("iisi", $current_streak, $longest_streak, $today, $user_id);
        $updateStreakStmt->execute();
        
        // If no rows were affected, insert a new record
        if ($updateStreakStmt->affected_rows === 0) {
            $insertStreakStmt = $conn->prepare("
                INSERT INTO user_streaks (user_id, current_streak, longest_streak, last_play_date)
                VALUES (?, ?, ?, ?)
            ");
            $insertStreakStmt->bind_param("iiis", $user_id, $current_streak, $longest_streak, $today);
            $insertStreakStmt->execute();
        }
        
        // Award XP for streak milestone if applicable
        $streakRewardStmt = $conn->prepare("
            SELECT xp_bonus FROM streak_rewards 
            WHERE streak_days = ? LIMIT 1
        ");
        $streakRewardStmt->bind_param("i", $current_streak);
        $streakRewardStmt->execute();
        $streakRewardResult = $streakRewardStmt->get_result();
        
        if ($streakRewardResult->num_rows > 0) {
            $streakReward = $streakRewardResult->fetch_assoc();
            $xpBonus = $streakReward['xp_bonus'];
            
            // Add XP bonus to user
            awardXP($conn, $user_id, $xpBonus);
        }
    } 
    // If streak was broken (more than 1 day since last play), reset to 1
    elseif ($lastPlayDate && date('Y-m-d', strtotime($lastPlayDate . ' +1 day')) !== $today) {
        $current_streak = 1;
        $longest_streak = $streakData['longest_streak'] ?? 1;
        
        $updateStreakStmt = $conn->prepare("
            UPDATE user_streaks 
            SET current_streak = ?, last_play_date = ?
            WHERE user_id = ?
        ");
        $updateStreakStmt->bind_param("isi", $current_streak, $today, $user_id);
        $updateStreakStmt->execute();
        
        if ($updateStreakStmt->affected_rows === 0) {
            $insertStreakStmt = $conn->prepare("
                INSERT INTO user_streaks (user_id, current_streak, longest_streak, last_play_date)
                VALUES (?, ?, ?, ?)
            ");
            $insertStreakStmt->bind_param("iiis", $user_id, $current_streak, $longest_streak, $today);
            $insertStreakStmt->execute();
        }
    }
    // First time user
    elseif (!$lastPlayDate) {
        $current_streak = 1;
        $longest_streak = 1;
        
        $insertStreakStmt = $conn->prepare("
            INSERT INTO user_streaks (user_id, current_streak, longest_streak, last_play_date)
            VALUES (?, ?, ?, ?)
        ");
        $insertStreakStmt->bind_param("iiis", $user_id, $current_streak, $longest_streak, $today);
        $insertStreakStmt->execute();
    }
}

// Function to award XP to user
function awardXP($conn, $user_id, $xp_amount) {
    // Get current XP and level
    $statsStmt = $conn->prepare("
        SELECT xp, level FROM user_stats WHERE user_id = ?
    ");
    $statsStmt->bind_param("i", $user_id);
    $statsStmt->execute();
    $statsResult = $statsStmt->get_result();
    
    if ($statsResult->num_rows > 0) {
        $stats = $statsResult->fetch_assoc();
        $currentXP = $stats['xp'];
        $currentLevel = $stats['level'];
        
        // Add new XP
        $newXP = $currentXP + $xp_amount;
        
        // Check if user should level up
        $levelUpStmt = $conn->prepare("
            SELECT level FROM xp_level_thresholds 
            WHERE xp_required <= ? 
            ORDER BY level DESC LIMIT 1
        ");
        $levelUpStmt->bind_param("i", $newXP);
        $levelUpStmt->execute();
        $levelUpResult = $levelUpStmt->get_result();
        
        if ($levelUpResult->num_rows > 0) {
            $levelData = $levelUpResult->fetch_assoc();
            $newLevel = $levelData['level'];
            
            // Update XP and level if needed
            $updateStatsStmt = $conn->prepare("
                UPDATE user_stats 
                SET xp = ?, level = ? 
                WHERE user_id = ?
            ");
            $updateStatsStmt->bind_param("iii", $newXP, $newLevel, $user_id);
            $updateStatsStmt->execute();
            
            return $newLevel > $currentLevel; // Return true if leveled up
        } else {
            // Just update XP
            $updateStatsStmt = $conn->prepare("
                UPDATE user_stats 
                SET xp = ? 
                WHERE user_id = ?
            ");
            $updateStatsStmt->bind_param("ii", $newXP, $user_id);
            $updateStatsStmt->execute();
        }
    } else {
        // Create new stats record for user
        $insertStatsStmt = $conn->prepare("
            INSERT INTO user_stats (user_id, xp, level)
            VALUES (?, ?, 1)
        ");
        $insertStatsStmt->bind_param("ii", $user_id, $xp_amount);
        $insertStatsStmt->execute();
    }
    
    return false;
}

// Function to get language flag emoji
function getLanguageFlag($language) {
    $flags = [
        'Spanish' => '🇪🇸',
        'French' => '🇫🇷',
        'English' => '🇬🇧',
        'German' => '🇩🇪',
        'Italian' => '🇮🇹',
        'Portuguese' => '🇵🇹',
        'Russian' => '🇷🇺',
        'Japanese' => '🇯🇵',
        'Chinese' => '🇨🇳',
        'Korean' => '🇰🇷',
        'Arabic' => '🇸🇦',
    ];
    
    return $flags[$language] ?? '🌐';
}

// Get lesson type from URL parameter
$lesson_type = isset($_GET['type']) ? $_GET['type'] : 'numbers_basic';

// Define lesson content based on type and language
// In a real application, this would come from a database
$lesson_content = [];

// Sample lesson content for different languages
switch ($selected_language) {
    case 'Spanish':
        switch ($lesson_type) {
            case 'numbers_basic':
                $lesson_title = "Numbers 1-10";
                $lesson_icon = "🔢";
                $lesson_content = [
                    ['term' => '1', 'translation' => 'Uno', 'pronunciation' => 'oo-no', 'audio' => 'spanish/numbers/uno.mp3'],
                    ['term' => '2', 'translation' => 'Dos', 'pronunciation' => 'dose', 'audio' => 'spanish/numbers/dos.mp3'],
                    ['term' => '3', 'translation' => 'Tres', 'pronunciation' => 'trace', 'audio' => 'spanish/numbers/tres.mp3'],
                    ['term' => '4', 'translation' => 'Cuatro', 'pronunciation' => 'kwah-tro', 'audio' => 'spanish/numbers/cuatro.mp3'],
                    ['term' => '5', 'translation' => 'Cinco', 'pronunciation' => 'seen-ko', 'audio' => 'spanish/numbers/cinco.mp3'],
                    ['term' => '6', 'translation' => 'Seis', 'pronunciation' => 'says', 'audio' => 'spanish/numbers/seis.mp3'],
                    ['term' => '7', 'translation' => 'Siete', 'pronunciation' => 'see-eh-teh', 'audio' => 'spanish/numbers/siete.mp3'],
                    ['term' => '8', 'translation' => 'Ocho', 'pronunciation' => 'oh-cho', 'audio' => 'spanish/numbers/ocho.mp3'],
                    ['term' => '9', 'translation' => 'Nueve', 'pronunciation' => 'noo-eh-veh', 'audio' => 'spanish/numbers/nueve.mp3'],
                    ['term' => '10', 'translation' => 'Diez', 'pronunciation' => 'dee-ess', 'audio' => 'spanish/numbers/diez.mp3']
                ];
                break;
            case 'numbers_advanced':
                $lesson_title = "Numbers 20-1000";
                $lesson_icon = "📊";
                $lesson_content = [
                    ['term' => '20', 'translation' => 'Veinte', 'pronunciation' => 'bay-een-tay', 'audio' => 'spanish/numbers/veinte.mp3'],
                    ['term' => '30', 'translation' => 'Treinta', 'pronunciation' => 'tray-een-tah', 'audio' => 'spanish/numbers/treinta.mp3'],
                    ['term' => '40', 'translation' => 'Cuarenta', 'pronunciation' => 'kwar-en-tah', 'audio' => 'spanish/numbers/cuarenta.mp3'],
                    ['term' => '50', 'translation' => 'Cincuenta', 'pronunciation' => 'seen-kwen-tah', 'audio' => 'spanish/numbers/cincuenta.mp3'],
                    ['term' => '100', 'translation' => 'Cien', 'pronunciation' => 'see-en', 'audio' => 'spanish/numbers/cien.mp3'],
                    ['term' => '200', 'translation' => 'Doscientos', 'pronunciation' => 'dose-see-en-tose', 'audio' => 'spanish/numbers/doscientos.mp3'],
                    ['term' => '500', 'translation' => 'Quinientos', 'pronunciation' => 'keen-ee-en-tose', 'audio' => 'spanish/numbers/quinientos.mp3'],
                    ['term' => '1000', 'translation' => 'Mil', 'pronunciation' => 'meel', 'audio' => 'spanish/numbers/mil.mp3']
                ];
                break;
            case 'colors':
                $lesson_title = "Colors";
                $lesson_icon = "🎨";
                $lesson_content = [
                    ['term' => 'Red', 'translation' => 'Rojo', 'pronunciation' => 'ro-ho', 'audio' => 'spanish/colors/rojo.mp3'],
                    ['term' => 'Blue', 'translation' => 'Azul', 'pronunciation' => 'ah-sool', 'audio' => 'spanish/colors/azul.mp3'],
                    ['term' => 'Green', 'translation' => 'Verde', 'pronunciation' => 'vehr-deh', 'audio' => 'spanish/colors/verde.mp3'],
                    ['term' => 'Yellow', 'translation' => 'Amarillo', 'pronunciation' => 'ah-mah-ree-yo', 'audio' => 'spanish/colors/amarillo.mp3'],
                    ['term' => 'Black', 'translation' => 'Negro', 'pronunciation' => 'neh-gro', 'audio' => 'spanish/colors/negro.mp3'],
                    ['term' => 'White', 'translation' => 'Blanco', 'pronunciation' => 'blahn-ko', 'audio' => 'spanish/colors/blanco.mp3'],
                    ['term' => 'Orange', 'translation' => 'Naranja', 'pronunciation' => 'nah-rahn-ha', 'audio' => 'spanish/colors/naranja.mp3'],
                    ['term' => 'Purple', 'translation' => 'Morado', 'pronunciation' => 'mo-rah-do', 'audio' => 'spanish/colors/morado.mp3']
                ];
                break;
            case 'animals':
                $lesson_title = "Animals";
                $lesson_icon = "🐾";
                $lesson_content = [
                    ['term' => 'Dog', 'translation' => 'Perro', 'pronunciation' => 'peh-rro', 'audio' => 'spanish/animals/perro.mp3'],
                    ['term' => 'Cat', 'translation' => 'Gato', 'pronunciation' => 'gah-to', 'audio' => 'spanish/animals/gato.mp3'],
                    ['term' => 'Bird', 'translation' => 'Pájaro', 'pronunciation' => 'pah-ha-ro', 'audio' => 'spanish/animals/pajaro.mp3'],
                    ['term' => 'Fish', 'translation' => 'Pez', 'pronunciation' => 'pess', 'audio' => 'spanish/animals/pez.mp3'],
                    ['term' => 'Horse', 'translation' => 'Caballo', 'pronunciation' => 'kah-bah-yo', 'audio' => 'spanish/animals/caballo.mp3'],
                    ['term' => 'Cow', 'translation' => 'Vaca', 'pronunciation' => 'bah-kah', 'audio' => 'spanish/animals/vaca.mp3'],
                    ['term' => 'Pig', 'translation' => 'Cerdo', 'pronunciation' => 'sehr-do', 'audio' => 'spanish/animals/cerdo.mp3'],
                    ['term' => 'Lion', 'translation' => 'León', 'pronunciation' => 'leh-on', 'audio' => 'spanish/animals/leon.mp3']
                ];
                break;
            case 'greetings':
                $lesson_title = "Greetings";
                $lesson_icon = "👋";
                $lesson_content = [
                    ['term' => 'Hello', 'translation' => 'Hola', 'pronunciation' => 'oh-lah', 'audio' => 'spanish/greetings/hola.mp3'],
                    ['term' => 'Good morning', 'translation' => 'Buenos días', 'pronunciation' => 'bweh-nos dee-as', 'audio' => 'spanish/greetings/buenos_dias.mp3'],
                    ['term' => 'Good afternoon', 'translation' => 'Buenas tardes', 'pronunciation' => 'bweh-nas tar-des', 'audio' => 'spanish/greetings/buenas_tardes.mp3'],
                    ['term' => 'Good night', 'translation' => 'Buenas noches', 'pronunciation' => 'bweh-nas no-ches', 'audio' => 'spanish/greetings/buenas_noches.mp3'],
                    ['term' => 'How are you?', 'translation' => '¿Cómo estás?', 'pronunciation' => 'ko-mo es-tas', 'audio' => 'spanish/greetings/como_estas.mp3'],
                    ['term' => 'Nice to meet you', 'translation' => 'Mucho gusto', 'pronunciation' => 'moo-cho goo-sto', 'audio' => 'spanish/greetings/mucho_gusto.mp3'],
                    ['term' => 'Goodbye', 'translation' => 'Adiós', 'pronunciation' => 'ah-dee-os', 'audio' => 'spanish/greetings/adios.mp3'],
                    ['term' => 'See you later', 'translation' => 'Hasta luego', 'pronunciation' => 'ah-sta loo-eh-go', 'audio' => 'spanish/greetings/hasta_luego.mp3']
                ];
                break;
            case 'food':
                $lesson_title = "Food & Drinks";
                $lesson_icon = "🍽️";
                $lesson_content = [
                    ['term' => 'Water', 'translation' => 'Agua', 'pronunciation' => 'ah-gwa', 'audio' => 'spanish/food/agua.mp3'],
                    ['term' => 'Bread', 'translation' => 'Pan', 'pronunciation' => 'pahn', 'audio' => 'spanish/food/pan.mp3'],
                    ['term' => 'Cheese', 'translation' => 'Queso', 'pronunciation' => 'keh-so', 'audio' => 'spanish/food/queso.mp3'],
                    ['term' => 'Meat', 'translation' => 'Carne', 'pronunciation' => 'kar-neh', 'audio' => 'spanish/food/carne.mp3'],
                    ['term' => 'Fruit', 'translation' => 'Fruta', 'pronunciation' => 'froo-tah', 'audio' => 'spanish/food/fruta.mp3'],
                    ['term' => 'Vegetable', 'translation' => 'Verdura', 'pronunciation' => 'vehr-doo-rah', 'audio' => 'spanish/food/verdura.mp3'],
                    ['term' => 'Coffee', 'translation' => 'Café', 'pronunciation' => 'kah-feh', 'audio' => 'spanish/food/cafe.mp3'],
                    ['term' => 'Wine', 'translation' => 'Vino', 'pronunciation' => 'vee-no', 'audio' => 'spanish/food/vino.mp3']
                ];
                break;
            case 'family':
                $lesson_title = "Family Members";
                $lesson_icon = "👪";
                $lesson_content = [
                    ['term' => 'Mother', 'translation' => 'Madre', 'pronunciation' => 'mah-dreh', 'audio' => 'spanish/family/madre.mp3'],
                    ['term' => 'Father', 'translation' => 'Padre', 'pronunciation' => 'pah-dreh', 'audio' => 'spanish/family/padre.mp3'],
                    ['term' => 'Brother', 'translation' => 'Hermano', 'pronunciation' => 'ehr-mah-no', 'audio' => 'spanish/family/hermano.mp3'],
                    ['term' => 'Sister', 'translation' => 'Hermana', 'pronunciation' => 'ehr-mah-nah', 'audio' => 'spanish/family/hermana.mp3'],
                    ['term' => 'Son', 'translation' => 'Hijo', 'pronunciation' => 'ee-ho', 'audio' => 'spanish/family/hijo.mp3'],
                    ['term' => 'Daughter', 'translation' => 'Hija', 'pronunciation' => 'ee-hah', 'audio' => 'spanish/family/hija.mp3'],
                    ['term' => 'Grandfather', 'translation' => 'Abuelo', 'pronunciation' => 'ah-bweh-lo', 'audio' => 'spanish/family/abuelo.mp3'],
                    ['term' => 'Grandmother', 'translation' => 'Abuela', 'pronunciation' => 'ah-bweh-lah', 'audio' => 'spanish/family/abuela.mp3']
                ];
                break;
            case 'phrases':
                $lesson_title = "Common Phrases";
                $lesson_icon = "💬";
                $lesson_content = [
                    ['term' => 'Thank you', 'translation' => 'Gracias', 'pronunciation' => 'grah-see-as', 'audio' => 'spanish/phrases/gracias.mp3'],
                    ['term' => 'You\'re welcome', 'translation' => 'De nada', 'pronunciation' => 'deh nah-dah', 'audio' => 'spanish/phrases/de_nada.mp3'],
                    ['term' => 'Please', 'translation' => 'Por favor', 'pronunciation' => 'por fah-vor', 'audio' => 'spanish/phrases/por_favor.mp3'],
                    ['term' => 'Excuse me', 'translation' => 'Disculpe', 'pronunciation' => 'dees-kool-peh', 'audio' => 'spanish/phrases/disculpe.mp3'],
                    ['term' => 'I don\'t understand', 'translation' => 'No entiendo', 'pronunciation' => 'no en-tee-en-do', 'audio' => 'spanish/phrases/no_entiendo.mp3'],
                    ['term' => 'Can you help me?', 'translation' => '¿Puede ayudarme?', 'pronunciation' => 'pweh-deh ah-yoo-dar-meh', 'audio' => 'spanish/phrases/puede_ayudarme.mp3'],
                    ['term' => 'Where is...?', 'translation' => '¿Dónde está...?', 'pronunciation' => 'don-deh es-tah', 'audio' => 'spanish/phrases/donde_esta.mp3'],
                    ['term' => 'How much is it?', 'translation' => '¿Cuánto cuesta?', 'pronunciation' => 'kwan-to kwes-tah', 'audio' => 'spanish/phrases/cuanto_cuesta.mp3']
                ];
                break;
            default:
                $lesson_title = "Basic Lesson";
                $lesson_icon = "📚";
                $lesson_content = [
                    ['term' => 'Hello', 'translation' => 'Hola', 'pronunciation' => 'oh-lah', 'audio' => 'spanish/greetings/hola.mp3'],
                    ['term' => 'Goodbye', 'translation' => 'Adiós', 'pronunciation' => 'ah-dee-os', 'audio' => 'spanish/greetings/adios.mp3']
                ];
        }
        break;
    case 'French':
        switch ($lesson_type) {
            case 'numbers_basic':
                $lesson_title = "Numbers 1-10";
                $lesson_icon = "🔢";
                $lesson_content = [
                    ['term' => '1', 'translation' => 'Un', 'pronunciation' => 'uh', 'audio' => 'french/numbers/un.mp3'],
                    ['term' => '2', 'translation' => 'Deux', 'pronunciation' => 'duh', 'audio' => 'french/numbers/deux.mp3'],
                    ['term' => '3', 'translation' => 'Trois', 'pronunciation' => 'twah', 'audio' => 'french/numbers/trois.mp3'],
                    ['term' => '4', 'translation' => 'Quatre', 'pronunciation' => 'katr', 'audio' => 'french/numbers/quatre.mp3'],
                    ['term' => '5', 'translation' => 'Cinq', 'pronunciation' => 'sank', 'audio' => 'french/numbers/cinq.mp3'],
                    ['term' => '6', 'translation' => 'Six', 'pronunciation' => 'sees', 'audio' => 'french/numbers/six.mp3'],
                    ['term' => '7', 'translation' => 'Sept', 'pronunciation' => 'set', 'audio' => 'french/numbers/sept.mp3'],
                    ['term' => '8', 'translation' => 'Huit', 'pronunciation' => 'weet', 'audio' => 'french/numbers/huit.mp3'],
                    ['term' => '9', 'translation' => 'Neuf', 'pronunciation' => 'nuhf', 'audio' => 'french/numbers/neuf.mp3'],
                    ['term' => '10', 'translation' => 'Dix', 'pronunciation' => 'dees', 'audio' => 'french/numbers/dix.mp3']
                ];
                break;
            case 'numbers_advanced':
                $lesson_title = "Numbers 20-1000";
                $lesson_icon = "📊";
                $lesson_content = [
                    ['term' => '20', 'translation' => 'Vingt', 'pronunciation' => 'van', 'audio' => 'french/numbers/vingt.mp3'],
                    ['term' => '30', 'translation' => 'Trente', 'pronunciation' => 'tront', 'audio' => 'french/numbers/trente.mp3'],
                    ['term' => '40', 'translation' => 'Quarante', 'pronunciation' => 'kah-ront', 'audio' => 'french/numbers/quarante.mp3'],
                    ['term' => '50', 'translation' => 'Cinquante', 'pronunciation' => 'sank-ont', 'audio' => 'french/numbers/cinquante.mp3'],
                    ['term' => '100', 'translation' => 'Cent', 'pronunciation' => 'son', 'audio' => 'french/numbers/cent.mp3'],
                    ['term' => '200', 'translation' => 'Deux cents', 'pronunciation' => 'duh son', 'audio' => 'french/numbers/deux_cents.mp3'],
                    ['term' => '500', 'translation' => 'Cinq cents', 'pronunciation' => 'sank son', 'audio' => 'french/numbers/cinq_cents.mp3'],
                    ['term' => '1000', 'translation' => 'Mille', 'pronunciation' => 'meel', 'audio' => 'french/numbers/mille.mp3']
                ];
                break;
            case 'colors':
                $lesson_title = "Colors";
                $lesson_icon = "🎨";
                $lesson_content = [
                    ['term' => 'Red', 'translation' => 'Rouge', 'pronunciation' => 'roozh', 'audio' => 'french/colors/rouge.mp3'],
                    ['term' => 'Blue', 'translation' => 'Bleu', 'pronunciation' => 'bluh', 'audio' => 'french/colors/bleu.mp3'],
                    ['term' => 'Green', 'translation' => 'Vert', 'pronunciation' => 'vehr', 'audio' => 'french/colors/vert.mp3'],
                    ['term' => 'Yellow', 'translation' => 'Jaune', 'pronunciation' => 'zhohn', 'audio' => 'french/colors/jaune.mp3'],
                    ['term' => 'Black', 'translation' => 'Noir', 'pronunciation' => 'nwahr', 'audio' => 'french/colors/noir.mp3'],
                    ['term' => 'White', 'translation' => 'Blanc', 'pronunciation' => 'blahn', 'audio' => 'french/colors/blanc.mp3'],
                    ['term' => 'Orange', 'translation' => 'Orange', 'pronunciation' => 'oh-ronzh', 'audio' => 'french/colors/orange.mp3'],
                    ['term' => 'Purple', 'translation' => 'Violet', 'pronunciation' => 'vee-oh-lay', 'audio' => 'french/colors/violet.mp3']
                ];
                break;
            case 'animals':
                $lesson_title = "Animals";
                $lesson_icon = "🐾";
                $lesson_content = [
                    ['term' => 'Dog', 'translation' => 'Chien', 'pronunciation' => 'shee-en', 'audio' => 'french/animals/chien.mp3'],
                    ['term' => 'Cat', 'translation' => 'Chat', 'pronunciation' => 'shah', 'audio' => 'french/animals/chat.mp3'],
                    ['term' => 'Bird', 'translation' => 'Oiseau', 'pronunciation' => 'wah-zoh', 'audio' => 'french/animals/oiseau.mp3'],
                    ['term' => 'Fish', 'translation' => 'Poisson', 'pronunciation' => 'pwah-son', 'audio' => 'french/animals/poisson.mp3'],
                    ['term' => 'Horse', 'translation' => 'Cheval', 'pronunciation' => 'shuh-val', 'audio' => 'french/animals/cheval.mp3'],
                    ['term' => 'Cow', 'translation' => 'Vache', 'pronunciation' => 'vash', 'audio' => 'french/animals/vache.mp3'],
                    ['term' => 'Pig', 'translation' => 'Cochon', 'pronunciation' => 'ko-shon', 'audio' => 'french/animals/cochon.mp3'],
                    ['term' => 'Lion', 'translation' => 'Lion', 'pronunciation' => 'lee-on', 'audio' => 'french/animals/lion.mp3']
                ];
                break;
            case 'greetings':
                $lesson_title = "Greetings";
                $lesson_icon = "👋";
                $lesson_content = [
                    ['term' => 'Hello', 'translation' => 'Bonjour', 'pronunciation' => 'bon-zhoor', 'audio' => 'french/greetings/bonjour.mp3'],
                    ['term' => 'Good morning', 'translation' => 'Bonjour', 'pronunciation' => 'bon-zhoor', 'audio' => 'french/greetings/bonjour.mp3'],
                    ['term' => 'Good afternoon', 'translation' => 'Bon après-midi', 'pronunciation' => 'bon ah-preh-mee-dee', 'audio' => 'french/greetings/bon_apres_midi.mp3'],
                    ['term' => 'Good evening', 'translation' => 'Bonsoir', 'pronunciation' => 'bon-swahr', 'audio' => 'french/greetings/bonsoir.mp3'],
                    ['term' => 'Good night', 'translation' => 'Bonne nuit', 'pronunciation' => 'bon nwee', 'audio' => 'french/greetings/bonne_nuit.mp3'],
                    ['term' => 'How are you?', 'translation' => 'Comment allez-vous?', 'pronunciation' => 'ko-mohn tah-lay voo', 'audio' => 'french/greetings/comment_allez_vous.mp3'],
                    ['term' => 'Nice to meet you', 'translation' => 'Enchanté', 'pronunciation' => 'ahn-shahn-tay', 'audio' => 'french/greetings/enchante.mp3'],
                    ['term' => 'Goodbye', 'translation' => 'Au revoir', 'pronunciation' => 'oh ruh-vwahr', 'audio' => 'french/greetings/au_revoir.mp3']
                ];
                break;
            case 'food':
                $lesson_title = "Food & Drinks";
                $lesson_icon = "🍽️";
                $lesson_content = [
                    ['term' => 'Water', 'translation' => 'Eau', 'pronunciation' => 'oh', 'audio' => 'french/food/eau.mp3'],
                    ['term' => 'Bread', 'translation' => 'Pain', 'pronunciation' => 'pan', 'audio' => 'french/food/pain.mp3'],
                    ['term' => 'Cheese', 'translation' => 'Fromage', 'pronunciation' => 'fro-mahzh', 'audio' => 'french/food/fromage.mp3'],
                    ['term' => 'Meat', 'translation' => 'Viande', 'pronunciation' => 'vee-ahnd', 'audio' => 'french/food/viande.mp3'],
                    ['term' => 'Fruit', 'translation' => 'Fruit', 'pronunciation' => 'frwee', 'audio' => 'french/food/fruit.mp3'],
                    ['term' => 'Vegetable', 'translation' => 'Légume', 'pronunciation' => 'lay-goom', 'audio' => 'french/food/legume.mp3'],
                    ['term' => 'Coffee', 'translation' => 'Café', 'pronunciation' => 'kah-fay', 'audio' => 'french/food/cafe.mp3'],
                    ['term' => 'Wine', 'translation' => 'Vin', 'pronunciation' => 'van', 'audio' => 'french/food/vin.mp3']
                ];
                break;
            case 'family':
                $lesson_title = "Family Members";
                $lesson_icon = "👪";
                $lesson_content = [
                    ['term' => 'Mother', 'translation' => 'Mère', 'pronunciation' => 'mehr', 'audio' => 'french/family/mere.mp3'],
                    ['term' => 'Father', 'translation' => 'Père', 'pronunciation' => 'pehr', 'audio' => 'french/family/pere.mp3'],
                    ['term' => 'Brother', 'translation' => 'Frère', 'pronunciation' => 'frehr', 'audio' => 'french/family/frere.mp3'],
                    ['term' => 'Sister', 'translation' => 'Sœur', 'pronunciation' => 'sir', 'audio' => 'french/family/soeur.mp3'],
                    ['term' => 'Son', 'translation' => 'Fils', 'pronunciation' => 'fees', 'audio' => 'french/family/fils.mp3'],
                    ['term' => 'Daughter', 'translation' => 'Fille', 'pronunciation' => 'fee-yuh', 'audio' => 'french/family/fille.mp3'],
                    ['term' => 'Grandfather', 'translation' => 'Grand-père', 'pronunciation' => 'grahn-pehr', 'audio' => 'french/family/grand_pere.mp3'],
                    ['term' => 'Grandmother', 'translation' => 'Grand-mère', 'pronunciation' => 'grahn-mehr', 'audio' => 'french/family/grand_mere.mp3']
                ];
                break;
            case 'phrases':
                $lesson_title = "Common Phrases";
                $lesson_icon = "💬";
                $lesson_content = [
                    ['term' => 'Thank you', 'translation' => 'Merci', 'pronunciation' => 'mehr-see', 'audio' => 'french/phrases/merci.mp3'],
                    ['term' => 'You\'re welcome', 'translation' => 'De rien', 'pronunciation' => 'duh ree-en', 'audio' => 'french/phrases/de_rien.mp3'],
                    ['term' => 'Please', 'translation' => 'S\'il vous plaît', 'pronunciation' => 'seel voo pleh', 'audio' => 'french/phrases/sil_vous_plait.mp3'],
                    ['term' => 'Excuse me', 'translation' => 'Excusez-moi', 'pronunciation' => 'ex-koo-zay mwah', 'audio' => 'french/phrases/excusez_moi.mp3'],
                    ['term' => 'I don\'t understand', 'translation' => 'Je ne comprends pas', 'pronunciation' => 'zhuh nuh kom-prahn pah', 'audio' => 'french/phrases/je_ne_comprends_pas.mp3'],
                    ['term' => 'Can you help me?', 'translation' => 'Pouvez-vous m\'aider?', 'pronunciation' => 'poo-vay voo may-day', 'audio' => 'french/phrases/pouvez_vous_maider.mp3'],
                    ['term' => 'Where is...?', 'translation' => 'Où est...?', 'pronunciation' => 'oo eh', 'audio' => 'french/phrases/ou_est.mp3'],
                    ['term' => 'How much is it?', 'translation' => 'Combien ça coûte?', 'pronunciation' => 'kom-bee-en sah koot', 'audio' => 'french/phrases/combien_ca_coute.mp3']
                ];
                break;
            default:
                $lesson_title = "French Lesson";
                $lesson_icon = "📚";
                $lesson_content = [
                    ['term' => 'Hello', 'translation' => 'Bonjour', 'pronunciation' => 'bon-zhoor', 'audio' => 'french/greetings/bonjour.mp3'],
                    ['term' => 'Goodbye', 'translation' => 'Au revoir', 'pronunciation' => 'oh ruh-vwahr', 'audio' => 'french/greetings/au_revoir.mp3']
                ];
        }
        break;
    case 'German':
        switch ($lesson_type) {
            case 'numbers_basic':
                $lesson_title = "Numbers 1-10";
                $lesson_icon = "🔢";
                $lesson_content = [
                    ['term' => '1', 'translation' => 'Eins', 'pronunciation' => 'eyns', 'audio' => 'german/numbers/eins.mp3'],
                    ['term' => '2', 'translation' => 'Zwei', 'pronunciation' => 'tsvey', 'audio' => 'german/numbers/zwei.mp3'],
                    ['term' => '3', 'translation' => 'Drei', 'pronunciation' => 'dry', 'audio' => 'german/numbers/drei.mp3'],
                    ['term' => '4', 'translation' => 'Vier', 'pronunciation' => 'feer', 'audio' => 'german/numbers/vier.mp3'],
                    ['term' => '5', 'translation' => 'Fünf', 'pronunciation' => 'fuenf', 'audio' => 'german/numbers/funf.mp3'],
                    ['term' => '6', 'translation' => 'Sechs', 'pronunciation' => 'zeks', 'audio' => 'german/numbers/sechs.mp3'],
                    ['term' => '7', 'translation' => 'Sieben', 'pronunciation' => 'zee-ben', 'audio' => 'german/numbers/sieben.mp3'],
                    ['term' => '8', 'translation' => 'Acht', 'pronunciation' => 'akht', 'audio' => 'german/numbers/acht.mp3'],
                    ['term' => '9', 'translation' => 'Neun', 'pronunciation' => 'noyn', 'audio' => 'german/numbers/neun.mp3'],
                    ['term' => '10', 'translation' => 'Zehn', 'pronunciation' => 'tsayn', 'audio' => 'german/numbers/zehn.mp3']
                ];
                break;
            case 'numbers_advanced':
                $lesson_title = "Numbers 20-1000";
                $lesson_icon = "📊";
                $lesson_content = [
                    ['term' => '20', 'translation' => 'Zwanzig', 'pronunciation' => 'tsvan-tsikh', 'audio' => 'german/numbers/zwanzig.mp3'],
                    ['term' => '30', 'translation' => 'Dreißig', 'pronunciation' => 'dry-sikh', 'audio' => 'german/numbers/dreissig.mp3'],
                    ['term' => '40', 'translation' => 'Vierzig', 'pronunciation' => 'feer-tsikh', 'audio' => 'german/numbers/vierzig.mp3'],
                    ['term' => '50', 'translation' => 'Fünfzig', 'pronunciation' => 'fuenf-tsikh', 'audio' => 'german/numbers/funfzig.mp3'],
                    ['term' => '100', 'translation' => 'Hundert', 'pronunciation' => 'hoon-dert', 'audio' => 'german/numbers/hundert.mp3'],
                    ['term' => '200', 'translation' => 'Zweihundert', 'pronunciation' => 'tsvey-hoon-dert', 'audio' => 'german/numbers/zweihundert.mp3'],
                    ['term' => '500', 'translation' => 'Fünfhundert', 'pronunciation' => 'fuenf-hoon-dert', 'audio' => 'german/numbers/funfhundert.mp3'],
                    ['term' => '1000', 'translation' => 'Tausend', 'pronunciation' => 'tow-zent', 'audio' => 'german/numbers/tausend.mp3']
                ];
                break;
            case 'colors':
                $lesson_title = "Colors";
                $lesson_icon = "🎨";
                $lesson_content = [
                    ['term' => 'Red', 'translation' => 'Rot', 'pronunciation' => 'roht', 'audio' => 'german/colors/rot.mp3'],
                    ['term' => 'Blue', 'translation' => 'Blau', 'pronunciation' => 'blau', 'audio' => 'german/colors/blau.mp3'],
                    ['term' => 'Green', 'translation' => 'Grün', 'pronunciation' => 'gruen', 'audio' => 'german/colors/grun.mp3'],
                    ['term' => 'Yellow', 'translation' => 'Gelb', 'pronunciation' => 'gelp', 'audio' => 'german/colors/gelb.mp3'],
                    ['term' => 'Black', 'translation' => 'Schwarz', 'pronunciation' => 'shvarts', 'audio' => 'german/colors/schwarz.mp3'],
                    ['term' => 'White', 'translation' => 'Weiß', 'pronunciation' => 'vise', 'audio' => 'german/colors/weiss.mp3'],
                    ['term' => 'Orange', 'translation' => 'Orange', 'pronunciation' => 'oh-rahn-juh', 'audio' => 'german/colors/orange.mp3'],
                    ['term' => 'Purple', 'translation' => 'Lila', 'pronunciation' => 'lee-la', 'audio' => 'german/colors/lila.mp3']
                ];
                break;
            case 'animals':
                $lesson_title = "Animals";
                $lesson_icon = "🐾";
                $lesson_content = [
                    ['term' => 'Dog', 'translation' => 'Hund', 'pronunciation' => 'hoont', 'audio' => 'german/animals/hund.mp3'],
                    ['term' => 'Cat', 'translation' => 'Katze', 'pronunciation' => 'kah-tseh', 'audio' => 'german/animals/katze.mp3'],
                    ['term' => 'Bird', 'translation' => 'Vogel', 'pronunciation' => 'foh-gel', 'audio' => 'german/animals/vogel.mp3'],
                    ['term' => 'Fish', 'translation' => 'Fisch', 'pronunciation' => 'fish', 'audio' => 'german/animals/fisch.mp3'],
                    ['term' => 'Horse', 'translation' => 'Pferd', 'pronunciation' => 'pfehrt', 'audio' => 'german/animals/pferd.mp3'],
                    ['term' => 'Cow', 'translation' => 'Kuh', 'pronunciation' => 'koo', 'audio' => 'german/animals/kuh.mp3'],
                    ['term' => 'Pig', 'translation' => 'Schwein', 'pronunciation' => 'shvine', 'audio' => 'german/animals/schwein.mp3'],
                    ['term' => 'Lion', 'translation' => 'Löwe', 'pronunciation' => 'luh-veh', 'audio' => 'german/animals/lowe.mp3']
                ];
                break;
            case 'greetings':
                $lesson_title = "Greetings";
                $lesson_icon = "👋";
                $lesson_content = [
                    ['term' => 'Hello', 'translation' => 'Hallo', 'pronunciation' => 'hah-loh', 'audio' => 'german/greetings/hallo.mp3'],
                    ['term' => 'Good morning', 'translation' => 'Guten Morgen', 'pronunciation' => 'goo-ten mor-gen', 'audio' => 'german/greetings/guten_morgen.mp3'],
                    ['term' => 'Good afternoon', 'translation' => 'Guten Tag', 'pronunciation' => 'goo-ten tahk', 'audio' => 'german/greetings/guten_tag.mp3'],
                    ['term' => 'Good evening', 'translation' => 'Guten Abend', 'pronunciation' => 'goo-ten ah-bent', 'audio' => 'german/greetings/guten_abend.mp3'],
                    ['term' => 'How are you?', 'translation' => 'Wie geht es dir?', 'pronunciation' => 'vee gayt es deer', 'audio' => 'german/greetings/wie_geht_es_dir.mp3'],
                    ['term' => 'Nice to meet you', 'translation' => 'Schön, dich kennenzulernen', 'pronunciation' => 'shurn, dikh ken-en-tsoo-lair-nen', 'audio' => 'german/greetings/schon_dich_kennenzulernen.mp3'],
                    ['term' => 'Goodbye', 'translation' => 'Auf Wiedersehen', 'pronunciation' => 'owf vee-der-zayn', 'audio' => 'german/greetings/auf_wiedersehen.mp3']
                ];
                break;
            case 'food':
                $lesson_title = "Food & Drinks";
                $lesson_icon = "🍽️";
                $lesson_content = [
                    ['term' => 'Water', 'translation' => 'Wasser', 'pronunciation' => 'vah-ser', 'audio' => 'german/food/wasser.mp3'],
                    ['term' => 'Bread', 'translation' => 'Brot', 'pronunciation' => 'broht', 'audio' => 'german/food/brot.mp3'],
                    ['term' => 'Cheese', 'translation' => 'Käse', 'pronunciation' => 'keh-zeh', 'audio' => 'german/food/kase.mp3'],
                    ['term' => 'Meat', 'translation' => 'Fleisch', 'pronunciation' => 'flysh', 'audio' => 'german/food/fleisch.mp3'],
                    ['term' => 'Fruit', 'translation' => 'Obst', 'pronunciation' => 'ohpst', 'audio' => 'german/food/obst.mp3'],
                    ['term' => 'Vegetable', 'translation' => 'Gemüse', 'pronunciation' => 'geh-moo-zeh', 'audio' => 'german/food/gemuse.mp3'],
                    ['term' => 'Coffee', 'translation' => 'Kaffee', 'pronunciation' => 'kah-feh', 'audio' => 'german/food/kaffee.mp3'],
                    ['term' => 'Wine', 'translation' => 'Wein', 'pronunciation' => 'vine', 'audio' => 'german/food/wein.mp3']
                ];
                break;
            case 'family':
                $lesson_title = "Family Members";
                $lesson_icon = "👪";
                $lesson_content = [
                    ['term' => 'Mother', 'translation' => 'Mutter', 'pronunciation' => 'moo-ter', 'audio' => 'german/family/mutter.mp3'],
                    ['term' => 'Father', 'translation' => 'Vater', 'pronunciation' => 'fah-ter', 'audio' => 'german/family/vater.mp3'],
                    ['term' => 'Brother', 'translation' => 'Bruder', 'pronunciation' => 'broo-der', 'audio' => 'german/family/bruder.mp3'],
                    ['term' => 'Sister', 'translation' => 'Schwester', 'pronunciation' => 'shves-ter', 'audio' => 'german/family/schwester.mp3'],
                    ['term' => 'Son', 'translation' => 'Sohn', 'pronunciation' => 'zone', 'audio' => 'german/family/sohn.mp3'],
                    ['term' => 'Daughter', 'translation' => 'Tochter', 'pronunciation' => 'tokh-ter', 'audio' => 'german/family/tochter.mp3'],
                    ['term' => 'Grandfather', 'translation' => 'Großvater', 'pronunciation' => 'grohs-fah-ter', 'audio' => 'german/family/grossvater.mp3'],
                    ['term' => 'Grandmother', 'translation' => 'Großmutter', 'pronunciation' => 'grohs-moo-ter', 'audio' => 'german/family/grossmutter.mp3']
                ];
                break;
            case 'phrases':
                $lesson_title = "Common Phrases";
                $lesson_icon = "💬";
                $lesson_content = [
                    ['term' => 'Thank you', 'translation' => 'Danke', 'pronunciation' => 'dahn-keh', 'audio' => 'german/phrases/danke.mp3'],
                    ['term' => 'You\'re welcome', 'translation' => 'Bitte', 'pronunciation' => 'bit-teh', 'audio' => 'german/phrases/bitte.mp3'],
                    ['term' => 'Please', 'translation' => 'Bitte', 'pronunciation' => 'bit-teh', 'audio' => 'german/phrases/bitte.mp3'],
                    ['term' => 'Excuse me', 'translation' => 'Entschuldigung', 'pronunciation' => 'ent-shool-di-goong', 'audio' => 'german/phrases/entschuldigung.mp3'],
                    ['term' => 'I don\'t understand', 'translation' => 'Ich verstehe nicht', 'pronunciation' => 'ikh fer-shtey-eh nikht', 'audio' => 'german/phrases/ich_verstehe_nicht.mp3'],
                    ['term' => 'Can you help me?', 'translation' => 'Können Sie mir helfen?', 'pronunciation' => 'kur-nen zee meer hel-fen', 'audio' => 'german/phrases/konnen_sie_mir_helfen.mp3'],
                    ['term' => 'Where is...?', 'translation' => 'Wo ist...?', 'pronunciation' => 'voh ist', 'audio' => 'german/phrases/wo_ist.mp3'],
                    ['term' => 'How much is it?', 'translation' => 'Wie viel kostet das?', 'pronunciation' => 'vee feel kos-tet das', 'audio' => 'german/phrases/wie_viel_kostet_das.mp3']
                ];
                break;
            default:
                $lesson_title = "German Lesson";
                $lesson_icon = "📚";
                $lesson_content = [
                    ['term' => 'Hello', 'translation' => 'Hallo', 'pronunciation' => 'hah-loh', 'audio' => 'german/greetings/hallo.mp3'],
                    ['term' => 'Goodbye', 'translation' => 'Auf Wiedersehen', 'pronunciation' => 'owf vee-der-zayn', 'audio' => 'german/greetings/auf_wiedersehen.mp3']
                ];
        }
        break;
    case 'Italian':
        switch ($lesson_type) {
            case 'numbers_basic':
                $lesson_title = "Numbers 1-10";
                $lesson_icon = "🔢";
                $lesson_content = [
                    ['term' => '1', 'translation' => 'Uno', 'pronunciation' => 'oo-no', 'audio' => 'italian/numbers/uno.mp3'],
                    ['term' => '2', 'translation' => 'Due', 'pronunciation' => 'doo-eh', 'audio' => 'italian/numbers/due.mp3'],
                    ['term' => '3', 'translation' => 'Tre', 'pronunciation' => 'treh', 'audio' => 'italian/numbers/tre.mp3'],
                    ['term' => '4', 'translation' => 'Quattro', 'pronunciation' => 'kwat-tro', 'audio' => 'italian/numbers/quattro.mp3'],
                    ['term' => '5', 'translation' => 'Cinque', 'pronunciation' => 'cheen-kweh', 'audio' => 'italian/numbers/cinque.mp3'],
                    ['term' => '6', 'translation' => 'Sei', 'pronunciation' => 'say', 'audio' => 'italian/numbers/sei.mp3'],
                    ['term' => '7', 'translation' => 'Sette', 'pronunciation' => 'set-teh', 'audio' => 'italian/numbers/sette.mp3'],
                    ['term' => '8', 'translation' => 'Otto', 'pronunciation' => 'ot-to', 'audio' => 'italian/numbers/otto.mp3'],
                    ['term' => '9', 'translation' => 'Nove', 'pronunciation' => 'no-veh', 'audio' => 'italian/numbers/nove.mp3'],
                    ['term' => '10', 'translation' => 'Dieci', 'pronunciation' => 'dee-eh-chee', 'audio' => 'italian/numbers/dieci.mp3']
                ];
                break;
            case 'numbers_advanced':
                $lesson_title = "Numbers 20-1000";
                $lesson_icon = "📊";
                $lesson_content = [
                    ['term' => '20', 'translation' => 'Venti', 'pronunciation' => 'ven-tee', 'audio' => 'italian/numbers/venti.mp3'],
                    ['term' => '30', 'translation' => 'Trenta', 'pronunciation' => 'tren-tah', 'audio' => 'italian/numbers/trenta.mp3'],
                    ['term' => '40', 'translation' => 'Quaranta', 'pronunciation' => 'kwah-rahn-tah', 'audio' => 'italian/numbers/quaranta.mp3'],
                    ['term' => '50', 'translation' => 'Cinquanta', 'pronunciation' => 'cheen-kwan-tah', 'audio' => 'italian/numbers/cinquanta.mp3'],
                    ['term' => '100', 'translation' => 'Cento', 'pronunciation' => 'chen-toh', 'audio' => 'italian/numbers/cento.mp3'],
                    ['term' => '200', 'translation' => 'Duecento', 'pronunciation' => 'doo-eh-chen-toh', 'audio' => 'italian/numbers/duecento.mp3'],
                    ['term' => '500', 'translation' => 'Cinquecento', 'pronunciation' => 'cheen-kweh-chen-toh', 'audio' => 'italian/numbers/cinquecento.mp3'],
                    ['term' => '1000', 'translation' => 'Mille', 'pronunciation' => 'meel-leh', 'audio' => 'italian/numbers/mille.mp3']
                ];
                break;
            case 'colors':
                $lesson_title = "Colors";
                $lesson_icon = "🎨";
                $lesson_content = [
                    ['term' => 'Red', 'translation' => 'Rosso', 'pronunciation' => 'ros-so', 'audio' => 'italian/colors/rosso.mp3'],
                    ['term' => 'Blue', 'translation' => 'Blu', 'pronunciation' => 'bloo', 'audio' => 'italian/colors/blu.mp3'],
                    ['term' => 'Green', 'translation' => 'Verde', 'pronunciation' => 'vehr-deh', 'audio' => 'italian/colors/verde.mp3'],
                    ['term' => 'Yellow', 'translation' => 'Giallo', 'pronunciation' => 'jahl-lo', 'audio' => 'italian/colors/giallo.mp3'],
                    ['term' => 'Black', 'translation' => 'Nero', 'pronunciation' => 'neh-ro', 'audio' => 'italian/colors/nero.mp3'],
                    ['term' => 'White', 'translation' => 'Bianco', 'pronunciation' => 'bee-ahn-ko', 'audio' => 'italian/colors/bianco.mp3'],
                    ['term' => 'Orange', 'translation' => 'Arancione', 'pronunciation' => 'ah-rahn-cho-neh', 'audio' => 'italian/colors/arancione.mp3'],
                    ['term' => 'Purple', 'translation' => 'Viola', 'pronunciation' => 'vee-oh-lah', 'audio' => 'italian/colors/viola.mp3']
                ];
                break;
            case 'animals':
                $lesson_title = "Animals";
                $lesson_icon = "🐾";
                $lesson_content = [
                    ['term' => 'Dog', 'translation' => 'Cane', 'pronunciation' => 'kah-neh', 'audio' => 'italian/animals/cane.mp3'],
                    ['term' => 'Cat', 'translation' => 'Gatto', 'pronunciation' => 'gat-toh', 'audio' => 'italian/animals/gatto.mp3'],
                    ['term' => 'Bird', 'translation' => 'Uccello', 'pronunciation' => 'oo-chel-loh', 'audio' => 'italian/animals/uccello.mp3'],
                    ['term' => 'Fish', 'translation' => 'Pesce', 'pronunciation' => 'peh-sheh', 'audio' => 'italian/animals/pesce.mp3'],
                    ['term' => 'Horse', 'translation' => 'Cavallo', 'pronunciation' => 'kah-vahl-loh', 'audio' => 'italian/animals/cavallo.mp3'],
                    ['term' => 'Cow', 'translation' => 'Mucca', 'pronunciation' => 'mook-kah', 'audio' => 'italian/animals/mucca.mp3'],
                    ['term' => 'Pig', 'translation' => 'Maiale', 'pronunciation' => 'mah-yah-leh', 'audio' => 'italian/animals/maiale.mp3'],
                    ['term' => 'Lion', 'translation' => 'Leone', 'pronunciation' => 'leh-oh-neh', 'audio' => 'italian/animals/leone.mp3']
                ];
                break;
            case 'greetings':
                $lesson_title = "Greetings";
                $lesson_icon = "👋";
                $lesson_content = [
                    ['term' => 'Hello', 'translation' => 'Ciao', 'pronunciation' => 'chow', 'audio' => 'italian/greetings/ciao.mp3'],
                    ['term' => 'Good morning', 'translation' => 'Buongiorno', 'pronunciation' => 'bwon-jor-no', 'audio' => 'italian/greetings/buongiorno.mp3'],
                    ['term' => 'Good afternoon', 'translation' => 'Buon pomeriggio', 'pronunciation' => 'bwon po-meh-ree-jee-o', 'audio' => 'italian/greetings/buon_pomeriggio.mp3'],
                    ['term' => 'Good evening', 'translation' => 'Buonasera', 'pronunciation' => 'bwon-ah-seh-rah', 'audio' => 'italian/greetings/buonasera.mp3'],
                    ['term' => 'How are you?', 'translation' => 'Come stai?', 'pronunciation' => 'ko-meh stai', 'audio' => 'italian/greetings/come_stai.mp3'],
                    ['term' => 'Nice to meet you', 'translation' => 'Piacere di conoscerti', 'pronunciation' => 'pya-cheh-reh dee ko-no-sher-tee', 'audio' => 'italian/greetings/piacere_di_conoscerti.mp3'],
                    ['term' => 'Goodbye', 'translation' => 'Arrivederci', 'pronunciation' => 'ah-ree-veh-der-chee', 'audio' => 'italian/greetings/arrivederci.mp3']
                ];
                break;
            case 'food':
                $lesson_title = "Food & Drinks";
                $lesson_icon = "🍽️";
                $lesson_content = [
                    ['term' => 'Water', 'translation' => 'Acqua', 'pronunciation' => 'ah-kwah', 'audio' => 'italian/food/acqua.mp3'],
                    ['term' => 'Bread', 'translation' => 'Pane', 'pronunciation' => 'pah-neh', 'audio' => 'italian/food/pane.mp3'],
                    ['term' => 'Cheese', 'translation' => 'Formaggio', 'pronunciation' => 'for-maj-jo', 'audio' => 'italian/food/formaggio.mp3'],
                    ['term' => 'Meat', 'translation' => 'Carne', 'pronunciation' => 'kar-neh', 'audio' => 'italian/food/carne.mp3'],
                    ['term' => 'Fruit', 'translation' => 'Frutta', 'pronunciation' => 'froot-tah', 'audio' => 'italian/food/frutta.mp3'],
                    ['term' => 'Vegetable', 'translation' => 'Verdura', 'pronunciation' => 'vehr-doo-rah', 'audio' => 'italian/food/verdura.mp3'],
                    ['term' => 'Coffee', 'translation' => 'Caffè', 'pronunciation' => 'kaf-feh', 'audio' => 'italian/food/caffe.mp3'],
                    ['term' => 'Wine', 'translation' => 'Vino', 'pronunciation' => 'vee-no', 'audio' => 'italian/food/vino.mp3']
                ];
                break;
            case 'family':
                $lesson_title = "Family Members";
                $lesson_icon = "👪";
                $lesson_content = [
                    ['term' => 'Mother', 'translation' => 'Madre', 'pronunciation' => 'mah-dreh', 'audio' => 'italian/family/madre.mp3'],
                    ['term' => 'Father', 'translation' => 'Padre', 'pronunciation' => 'pah-dreh', 'audio' => 'italian/family/padre.mp3'],
                    ['term' => 'Brother', 'translation' => 'Fratello', 'pronunciation' => 'frah-tel-lo', 'audio' => 'italian/family/fratello.mp3'],
                    ['term' => 'Sister', 'translation' => 'Sorella', 'pronunciation' => 'so-rel-lah', 'audio' => 'italian/family/sorella.mp3'],
                    ['term' => 'Son', 'translation' => 'Figlio', 'pronunciation' => 'fee-lyo', 'audio' => 'italian/family/figlio.mp3'],
                    ['term' => 'Daughter', 'translation' => 'Figlia', 'pronunciation' => 'fee-lya', 'audio' => 'italian/family/figlia.mp3'],
                    ['term' => 'Grandfather', 'translation' => 'Nonno', 'pronunciation' => 'non-no', 'audio' => 'italian/family/nonno.mp3'],
                    ['term' => 'Grandmother', 'translation' => 'Nonna', 'pronunciation' => 'non-na', 'audio' => 'italian/family/nonna.mp3']
                ];
                break;
            case 'phrases':
                $lesson_title = "Common Phrases";
                $lesson_icon = "💬";
                $lesson_content = [
                    ['term' => 'Thank you', 'translation' => 'Grazie', 'pronunciation' => 'grah-tsee-eh', 'audio' => 'italian/phrases/grazie.mp3'],
                    ['term' => 'You\'re welcome', 'translation' => 'Prego', 'pronunciation' => 'preh-go', 'audio' => 'italian/phrases/prego.mp3'],
                    ['term' => 'Please', 'translation' => 'Per favore', 'pronunciation' => 'pehr fah-voh-reh', 'audio' => 'italian/phrases/per_favore.mp3'],
                    ['term' => 'Excuse me', 'translation' => 'Scusi', 'pronunciation' => 'skoo-zee', 'audio' => 'italian/phrases/scusi.mp3'],
                    ['term' => 'I don\'t understand', 'translation' => 'Non capisco', 'pronunciation' => 'non kah-pee-sko', 'audio' => 'italian/phrases/non_capisco.mp3'],
                    ['term' => 'Can you help me?', 'translation' => 'Può aiutarmi?', 'pronunciation' => 'pwoh ah-yoo-tar-mee', 'audio' => 'italian/phrases/puo_aiutarmi.mp3'],
                    ['term' => 'Where is...?', 'translation' => 'Dov\'è...?', 'pronunciation' => 'doh-veh', 'audio' => 'italian/phrases/dove.mp3'],
                    ['term' => 'How much is it?', 'translation' => 'Quanto costa?', 'pronunciation' => 'kwan-toh kos-tah', 'audio' => 'italian/phrases/quanto_costa.mp3']
                ];
                break;
            default:
                $lesson_title = "Italian Lesson";
                $lesson_icon = "📚";
                $lesson_content = [
                    ['term' => 'Hello', 'translation' => 'Ciao', 'pronunciation' => 'chow', 'audio' => 'italian/greetings/ciao.mp3'],
                    ['term' => 'Goodbye', 'translation' => 'Arrivederci', 'pronunciation' => 'ah-ree-veh-der-chee', 'audio' => 'italian/greetings/arrivederci.mp3']
                ];
        }
        break;
    case 'English':
        switch ($lesson_type) {
            case 'numbers_basic':
                $lesson_title = "Numbers 1-10";
                $lesson_icon = "🔢";
                $lesson_content = [
                    ['term' => '1', 'translation' => 'One', 'pronunciation' => 'wun', 'audio' => 'english/numbers/one.mp3'],
                    ['term' => '2', 'translation' => 'Two', 'pronunciation' => 'too', 'audio' => 'english/numbers/two.mp3'],
                    ['term' => '3', 'translation' => 'Three', 'pronunciation' => 'three', 'audio' => 'english/numbers/three.mp3'],
                    ['term' => '4', 'translation' => 'Four', 'pronunciation' => 'for', 'audio' => 'english/numbers/four.mp3'],
                    ['term' => '5', 'translation' => 'Five', 'pronunciation' => 'fayv', 'audio' => 'english/numbers/five.mp3'],
                    ['term' => '6', 'translation' => 'Six', 'pronunciation' => 'siks', 'audio' => 'english/numbers/six.mp3'],
                    ['term' => '7', 'translation' => 'Seven', 'pronunciation' => 'seh-ven', 'audio' => 'english/numbers/seven.mp3'],
                    ['term' => '8', 'translation' => 'Eight', 'pronunciation' => 'ayt', 'audio' => 'english/numbers/eight.mp3'],
                    ['term' => '9', 'translation' => 'Nine', 'pronunciation' => 'nayn', 'audio' => 'english/numbers/nine.mp3'],
                    ['term' => '10', 'translation' => 'Ten', 'pronunciation' => 'ten', 'audio' => 'english/numbers/ten.mp3']
                ];
                break;
            case 'numbers_advanced':
                $lesson_title = "Numbers 20-1000";
                $lesson_icon = "📊";
                $lesson_content = [
                    ['term' => '20', 'translation' => 'Twenty', 'pronunciation' => 'twen-tee', 'audio' => 'english/numbers/twenty.mp3'],
                    ['term' => '30', 'translation' => 'Thirty', 'pronunciation' => 'thur-tee', 'audio' => 'english/numbers/thirty.mp3'],
                    ['term' => '40', 'translation' => 'Forty', 'pronunciation' => 'for-tee', 'audio' => 'english/numbers/forty.mp3'],
                    ['term' => '50', 'translation' => 'Fifty', 'pronunciation' => 'fif-tee', 'audio' => 'english/numbers/fifty.mp3'],
                    ['term' => '100', 'translation' => 'One hundred', 'pronunciation' => 'wun hun-dred', 'audio' => 'english/numbers/one_hundred.mp3'],
                    ['term' => '200', 'translation' => 'Two hundred', 'pronunciation' => 'too hun-dred', 'audio' => 'english/numbers/two_hundred.mp3'],
                    ['term' => '500', 'translation' => 'Five hundred', 'pronunciation' => 'fayv hun-dred', 'audio' => 'english/numbers/five_hundred.mp3'],
                    ['term' => '1000', 'translation' => 'One thousand', 'pronunciation' => 'wun thou-zand', 'audio' => 'english/numbers/one_thousand.mp3']
                ];
                break;
            case 'colors':
                $lesson_title = "Colors";
                $lesson_icon = "🎨";
                $lesson_content = [
                    ['term' => 'Red', 'translation' => 'Red', 'pronunciation' => 'red', 'audio' => 'english/colors/red.mp3'],
                    ['term' => 'Blue', 'translation' => 'Blue', 'pronunciation' => 'bloo', 'audio' => 'english/colors/blue.mp3'],
                    ['term' => 'Green', 'translation' => 'Green', 'pronunciation' => 'green', 'audio' => 'english/colors/green.mp3'],
                    ['term' => 'Yellow', 'translation' => 'Yellow', 'pronunciation' => 'yel-low', 'audio' => 'english/colors/yellow.mp3'],
                    ['term' => 'Black', 'translation' => 'Black', 'pronunciation' => 'black', 'audio' => 'english/colors/black.mp3'],
                    ['term' => 'White', 'translation' => 'White', 'pronunciation' => 'white', 'audio' => 'english/colors/white.mp3'],
                    ['term' => 'Orange', 'translation' => 'Orange', 'pronunciation' => 'or-ange', 'audio' => 'english/colors/orange.mp3'],
                    ['term' => 'Purple', 'translation' => 'Purple', 'pronunciation' => 'pur-ple', 'audio' => 'english/colors/purple.mp3']
                ];
                break;
            case 'animals':
                $lesson_title = "Animals";
                $lesson_icon = "🐾";
                $lesson_content = [
                    ['term' => 'Dog', 'translation' => 'Dog', 'pronunciation' => 'dog', 'audio' => 'english/animals/dog.mp3'],
                    ['term' => 'Cat', 'translation' => 'Cat', 'pronunciation' => 'cat', 'audio' => 'english/animals/cat.mp3'],
                    ['term' => 'Bird', 'translation' => 'Bird', 'pronunciation' => 'bird', 'audio' => 'english/animals/bird.mp3'],
                    ['term' => 'Fish', 'translation' => 'Fish', 'pronunciation' => 'fish', 'audio' => 'english/animals/fish.mp3'],
                    ['term' => 'Horse', 'translation' => 'Horse', 'pronunciation' => 'horse', 'audio' => 'english/animals/horse.mp3'],
                    ['term' => 'Cow', 'translation' => 'Cow', 'pronunciation' => 'cow', 'audio' => 'english/animals/cow.mp3'],
                    ['term' => 'Pig', 'translation' => 'Pig', 'pronunciation' => 'pig', 'audio' => 'english/animals/pig.mp3'],
                    ['term' => 'Lion', 'translation' => 'Lion', 'pronunciation' => 'lie-on', 'audio' => 'english/animals/lion.mp3']
                ];
                break;
            case 'greetings':
                $lesson_title = "Greetings";
                $lesson_icon = "👋";
                $lesson_content = [
                    ['term' => 'Hello', 'translation' => 'Hello', 'pronunciation' => 'heh-loh', 'audio' => 'english/greetings/hello.mp3'],
                    ['term' => 'Good morning', 'translation' => 'Good morning', 'pronunciation' => 'good mor-ning', 'audio' => 'english/greetings/good_morning.mp3'],
                    ['term' => 'Good afternoon', 'translation' => 'Good afternoon', 'pronunciation' => 'good af-ter-noon', 'audio' => 'english/greetings/good_afternoon.mp3'],
                    ['term' => 'Good evening', 'translation' => 'Good evening', 'pronunciation' => 'good ee-vning', 'audio' => 'english/greetings/good_evening.mp3'],
                    ['term' => 'How are you?', 'translation' => 'How are you?', 'pronunciation' => 'how are you', 'audio' => 'english/greetings/how_are_you.mp3'],
                    ['term' => 'Nice to meet you', 'translation' => 'Nice to meet you', 'pronunciation' => 'nice to meet you', 'audio' => 'english/greetings/nice_to_meet_you.mp3'],
                    ['term' => 'Goodbye', 'translation' => 'Goodbye', 'pronunciation' => 'good-bye', 'audio' => 'english/greetings/goodbye.mp3']
                ];
                break;
            case 'food':
                $lesson_title = "Food & Drinks";
                $lesson_icon = "🍽️";
                $lesson_content = [
                    ['term' => 'Water', 'translation' => 'Water', 'pronunciation' => 'wah-ter', 'audio' => 'english/food/water.mp3'],
                    ['term' => 'Bread', 'translation' => 'Bread', 'pronunciation' => 'bred', 'audio' => 'english/food/bread.mp3'],
                    ['term' => 'Cheese', 'translation' => 'Cheese', 'pronunciation' => 'cheez', 'audio' => 'english/food/cheese.mp3'],
                    ['term' => 'Meat', 'translation' => 'Meat', 'pronunciation' => 'meet', 'audio' => 'english/food/meat.mp3'],
                    ['term' => 'Fruit', 'translation' => 'Fruit', 'pronunciation' => 'froot', 'audio' => 'english/food/fruit.mp3'],
                    ['term' => 'Vegetable', 'translation' => 'Vegetable', 'pronunciation' => 'vej-ta-bul', 'audio' => 'english/food/vegetable.mp3'],
                    ['term' => 'Coffee', 'translation' => 'Coffee', 'pronunciation' => 'kaw-fee', 'audio' => 'english/food/coffee.mp3'],
                    ['term' => 'Wine', 'translation' => 'Wine', 'pronunciation' => 'wine', 'audio' => 'english/food/wine.mp3']
                ];
                break;
            case 'family':
                $lesson_title = "Family Members";
                $lesson_icon = "👪";
                $lesson_content = [
                    ['term' => 'Mother', 'translation' => 'Mother', 'pronunciation' => 'muh-ther', 'audio' => 'english/family/mother.mp3'],
                    ['term' => 'Father', 'translation' => 'Father', 'pronunciation' => 'fah-ther', 'audio' => 'english/family/father.mp3'],
                    ['term' => 'Brother', 'translation' => 'Brother', 'pronunciation' => 'bruh-ther', 'audio' => 'english/family/brother.mp3'],
                    ['term' => 'Sister', 'translation' => 'Sister', 'pronunciation' => 'sis-ter', 'audio' => 'english/family/sister.mp3'],
                    ['term' => 'Son', 'translation' => 'Son', 'pronunciation' => 'sun', 'audio' => 'english/family/son.mp3'],
                    ['term' => 'Daughter', 'translation' => 'Daughter', 'pronunciation' => 'daw-ter', 'audio' => 'english/family/daughter.mp3'],
                    ['term' => 'Grandfather', 'translation' => 'Grandfather', 'pronunciation' => 'grand-fah-ther', 'audio' => 'english/family/grandfather.mp3'],
                    ['term' => 'Grandmother', 'translation' => 'Grandmother', 'pronunciation' => 'grand-muh-ther', 'audio' => 'english/family/grandmother.mp3']
                ];
                break;
            case 'phrases':
                $lesson_title = "Common Phrases";
                $lesson_icon = "💬";
                $lesson_content = [
                    ['term' => 'Thank you', 'translation' => 'Thank you', 'pronunciation' => 'thank you', 'audio' => 'english/phrases/thank_you.mp3'],
                    ['term' => 'You\'re welcome', 'translation' => 'You\'re welcome', 'pronunciation' => 'your wel-come', 'audio' => 'english/phrases/youre_welcome.mp3'],
                    ['term' => 'Please', 'translation' => 'Please', 'pronunciation' => 'pleez', 'audio' => 'english/phrases/please.mp3'],
                    ['term' => 'Excuse me', 'translation' => 'Excuse me', 'pronunciation' => 'ex-kyooz me', 'audio' => 'english/phrases/excuse_me.mp3'],
                    ['term' => 'I don\'t understand', 'translation' => 'I don\'t understand', 'pronunciation' => 'i dont un-der-stand', 'audio' => 'english/phrases/i_dont_understand.mp3'],
                    ['term' => 'Can you help me?', 'translation' => 'Can you help me?', 'pronunciation' => 'can you help me', 'audio' => 'english/phrases/can_you_help_me.mp3'],
                    ['term' => 'Where is...?', 'translation' => 'Where is...?', 'pronunciation' => 'where iz', 'audio' => 'english/phrases/where_is.mp3'],
                    ['term' => 'How much is it?', 'translation' => 'How much is it?', 'pronunciation' => 'how much iz it', 'audio' => 'english/phrases/how_much_is_it.mp3']
                ];
                break;
            default:
                $lesson_title = "English Lesson";
                $lesson_icon = "📚";
                $lesson_content = [
                    ['term' => 'Hello', 'translation' => 'Hello', 'pronunciation' => 'heh-loh', 'audio' => 'english/greetings/hello.mp3'],
                    ['term' => 'Goodbye', 'translation' => 'Goodbye', 'pronunciation' => 'good-bye', 'audio' => 'english/greetings/goodbye.mp3']
                ];
        }
        break;
    default:
        // Default content for other languages (simplified)
        $lesson_title = "Basic Lesson";
        $lesson_icon = "📚";
        $lesson_content = [
            ['term' => 'Hello', 'translation' => 'Hello in ' . $selected_language, 'pronunciation' => '', 'audio' => ''],
            ['term' => 'Goodbye', 'translation' => 'Goodbye in ' . $selected_language, 'pronunciation' => '', 'audio' => '']
        ];
}

// Check if this is a lesson completion request
if (isset($_POST['complete_lesson']) && $_POST['complete_lesson'] === 'true') {
    // Award XP for completing the lesson
    $xp_earned = 10; // Base XP for completing a lesson
    
    // Check if this is the first time completing this lesson
    $checkCompletionStmt = $conn->prepare("
        SELECT completed FROM user_lesson_progress 
        WHERE user_id = ? AND category_id = (
            SELECT category_id FROM lesson_categories WHERE slug = ? LIMIT 1
        )
    ");
    $checkCompletionStmt->bind_param("is", $user_id, $lesson_type);
    $checkCompletionStmt->execute();
    $completionResult = $checkCompletionStmt->get_result();
    
    if ($completionResult->num_rows === 0 || !$completionResult->fetch_assoc()['completed']) {
        // First time completion or not completed yet, award full XP
        $leveledUp = awardXP($conn, $user_id, $xp_earned);
        
        // Update lesson progress
        $categoryIdStmt = $conn->prepare("SELECT category_id FROM lesson_categories WHERE slug = ? LIMIT 1");
        $categoryIdStmt->bind_param("s", $lesson_type);
        $categoryIdStmt->execute();
        $categoryResult = $categoryIdStmt->get_result();
        
        if ($categoryResult->num_rows > 0) {
            $categoryId = $categoryResult->fetch_assoc()['category_id'];
            
            // Check if progress record exists
            $checkProgressStmt = $conn->prepare("
                SELECT progress_id FROM user_lesson_progress 
                WHERE user_id = ? AND category_id = ?
            ");
            $checkProgressStmt->bind_param("ii", $user_id, $categoryId);
            $checkProgressStmt->execute();
            $progressResult = $checkProgressStmt->get_result();
            
            if ($progressResult->num_rows > 0) {
                // Update existing record
                $updateProgressStmt = $conn->prepare("
                    UPDATE user_lesson_progress 
                    SET completed = 1, last_accessed = NOW() 
                    WHERE user_id = ? AND category_id = ?
                ");
                $updateProgressStmt->bind_param("ii", $user_id, $categoryId);
                $updateProgressStmt->execute();
            } else {
                // Create new record
                $insertProgressStmt = $conn->prepare("
                    INSERT INTO user_lesson_progress (user_id, category_id, completed, last_accessed)
                    VALUES (?, ?, 1, NOW())
                ");
                $insertProgressStmt->bind_param("ii", $user_id, $categoryId);
                $insertProgressStmt->execute();
            }
        }
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'xp_earned' => $xp_earned,
            'leveled_up' => $leveledUp
        ]);
        exit;
    } else {
        // Already completed, award reduced XP for practice
        $practice_xp = floor($xp_earned / 2);
        $leveledUp = awardXP($conn, $user_id, $practice_xp);
        
        // Update last accessed timestamp
        $updateAccessStmt = $conn->prepare("
            UPDATE user_lesson_progress 
            SET last_accessed = NOW() 
            WHERE user_id = ? AND category_id = (
                SELECT category_id FROM lesson_categories WHERE slug = ? LIMIT 1
            )
        ");
        $updateAccessStmt->bind_param("is", $user_id, $lesson_type);
        $updateAccessStmt->execute();
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'xp_earned' => $practice_xp,
            'leveled_up' => $leveledUp,
            'practice' => true
        ]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lesson_title; ?> | Mura</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
        }

        .app-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #5a3b5d 0%, #3f3d56 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 100;
        }

        .logo {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mura-logo {
            width: 40px;
            height: 40px;
            margin-right: 10px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .mura-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .logo h1 {
            font-size: 28px;
            font-weight: bold;
            color: #b39ddb;
            letter-spacing: 2px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
            overflow-y: auto;
        }

        .sidebar-nav ul {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #e8e8e8;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .nav-item a:hover {
            background-color: rgba(179, 157, 219, 0.1);
            border-left: 3px solid #b39ddb;
        }

        .nav-item.active a {
            background-color: rgba(179, 157, 219, 0.2);
            border-left: 3px solid #b39ddb;
            color: #b39ddb;
        }

        .nav-item i {
            margin-right: 15px;
            font-size: 18px;
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            color: #e8e8e8;
            text-decoration: none;
            padding: 10px;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .logout-btn i {
            margin-right: 10px;
        }

        .main-content {
            flex: 1;
            background-color: #f5f7fa;
            margin-left: 250px;
            position: relative;
            width: calc(100% - 250px);
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .menu-toggle {
            display: none;
            font-size: 24px;
            cursor: pointer;
            margin-right: 15px;
        }

        .left-stats {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .level-indicator {
            display: flex;
            align-items: center;
            background-color: #fff;
            padding: 8px 15px;
            border-radius: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #eee;
        }

        .level-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, #7e57c2, #5a3b5d);
            border-radius: 50%;
            margin-right: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .level-number {
            color: #fff;
            font-weight: bold;
            font-size: 14px;
        }

        .level-label {
            font-size: 14px;
            color: #666;
        }

        .streak-counter {
            display: flex;
            align-items: center;
            background-color: #fff;
            padding: 8px 15px;
            border-radius: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #eee;
        }

        .streak-number {
            font-size: 18px;
            font-weight: bold;
            color: #5a3b5d;
            margin-right: 5px;
        }

        .streak-icon {
            position: relative;
            width: 24px;
            height: 24px;
            margin: 0 5px;
        }

        .fire-emoji {
            font-size: 20px;
            position: absolute;
            top: 0;
            left: 0;
            animation: flame 0.8s infinite alternate;
        }

        @keyframes flame {
            0% {
                transform: scale(1) rotate(-5deg);
                text-shadow: 0 0 5px rgba(255, 100, 0, 0.5);
            }
            100% {
                transform: scale(1.1) rotate(5deg);
                text-shadow: 0 0 10px rgba(255, 100, 0, 0.8), 0 0 20px rgba(255, 200, 0, 0.4);
            }
        }

        .streak-label {
            font-size: 14px;
            color: #666;
        }

        .language-indicator {
            display: flex;
            align-items: center;
            background-color: #fff;
            padding: 8px 15px;
            border-radius: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #eee;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .language-indicator:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .language-name {
            font-size: 14px;
            font-weight: 500;
            color: #5a3b5d;
            margin-right: 8px;
        }

        .language-flag {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .content {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .lesson-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .back-button {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .back-button:hover {
            background-color: #f0f0f0;
            transform: translateX(-3px);
        }

        .lesson-title {
            font-size: 28px;
            color: #5a3b5d;
            display: flex;
            align-items: center;
        }

        .lesson-title-icon {
            font-size: 32px;
            margin-right: 15px;
        }

        .lesson-cards {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 30px;
        }

        .lesson-card {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .lesson-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .lesson-card-inner {
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .term-container {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .term {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .translation {
            font-size: 28px;
            color: #5a3b5d;
            font-weight: bold;
        }

        .pronunciation {
            font-size: 14px;
            color: #666;
            font-style: italic;
        }

        .lesson-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .practice-button {
            background-color: #5a3b5d;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .practice-button:hover {
            background-color: #7e57c2;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .complete-button {
            background-color: #4caf50;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .complete-button:hover {
            background-color: #66bb6a;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* XP Gain Animation */
        .xp-gain {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            font-weight: bold;
            color: #7e57c2;
            z-index: 1000;
            animation: float-up 2s forwards;
            pointer-events: none;
        }

        @keyframes float-up {
            0% { opacity: 0; transform: translate(-50%, -50%); }
            20% { opacity: 1; }
            100% { opacity: 0; transform: translate(-50%, -200%); }
        }

        /* Level Up Animation */
        .level-up {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            animation: fade-in 0.5s forwards;
        }

        .level-up-content {
            background: linear-gradient(135deg, #7e57c2, #5a3b5d);
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: scale-in 0.5s forwards;
        }

        .level-up-title {
            font-size: 32px;
            color: white;
            margin-bottom: 15px;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .level-up-badge {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #b39ddb, #7e57c2);
            border-radius: 50%;
            margin: 20px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: bold;
            color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
            animation: pulse 1.5s infinite;
        }

        .level-up-message {
            font-size: 18px;
            color: white;
            margin-bottom: 25px;
        }

        .level-up-button {
            background-color: white;
            color: #5a3b5d;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .level-up-button:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        @keyframes fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes scale-in {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

/* Quiz Styles */
.quiz-container {
    display: none;
    background-color: #fff;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    padding: 30px;
    margin-top: 30px;
    animation: fade-in 0.5s ease;
}

.quiz-container.active {
    display: block;
}

.quiz-title {
    font-size: 24px;
    color: #5a3b5d;
    margin-bottom: 20px;
    text-align: center;
}

.quiz-description {
    font-size: 16px;
    color: #666;
    margin-bottom: 25px;
    text-align: center;
}

.quiz-question {
    margin-bottom: 30px;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 10px;
    border-left: 4px solid #5a3b5d;
}

.quiz-question-text {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
}

.quiz-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.quiz-option {
    padding: 12px 15px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
}

.quiz-option:hover {
    background-color: #f0f0f0;
    border-color: #ccc;
}

.quiz-option.selected {
    background-color: #e8e0f0;
    border-color: #5a3b5d;
}

.quiz-option.correct {
    background-color: #e6f7e6;
    border-color: #4caf50;
}

.quiz-option.incorrect {
    background-color: #ffebee;
    border-color: #f44336;
}

.quiz-option-text {
    flex: 1;
}

.quiz-option-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 10px;
    opacity: 0;
    transition: opacity 0.3s;
}

.quiz-option.correct .quiz-option-icon {
    background-color: #4caf50;
    color: white;
    opacity: 1;
}

.quiz-option.incorrect .quiz-option-icon {
    background-color: #f44336;
    color: white;
    opacity: 1;
}

.quiz-navigation {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

.quiz-button {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.quiz-button-next {
    background-color: #5a3b5d;
    color: white;
}

.quiz-button-next:hover {
    background-color: #7e57c2;
    transform: translateY(-2px);
}

.quiz-button-prev {
    background-color: #f5f5f5;
    color: #333;
}

.quiz-button-prev:hover {
    background-color: #e0e0e0;
}

.quiz-button-submit {
    background-color: #4caf50;
    color: white;
}

.quiz-button-submit:hover {
    background-color: #66bb6a;
    transform: translateY(-2px);
}

.quiz-button-disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.quiz-button-disabled:hover {
    transform: none;
}

.quiz-results {
    text-align: center;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 10px;
    margin-top: 20px;
}

.quiz-score {
    font-size: 24px;
    font-weight: bold;
    color: #5a3b5d;
    margin-bottom: 15px;
}

.quiz-feedback {
    font-size: 18px;
    margin-bottom: 20px;
}

.quiz-continue {
    margin-top: 20px;
}

@keyframes fade-in {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.pronunciation-button {
    background-color: #5a3b5d;
    color: white;
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
}

.pronunciation-button:hover {
    background-color: #7e57c2;
    transform: scale(1.1);
}

.pronunciation-button i {
    font-size: 20px;
}

.pronunciation-tooltip {
    position: fixed;
    background-color: #333;
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 14px;
    z-index: 1000;
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.3s ease;
    pointer-events: none;
}

.pronunciation-tooltip.active {
    opacity: 1;
    transform: translateY(0);
}

.pronunciation-tooltip:after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: #333 transparent transparent transparent;
}
        /* Responsive Styles */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -250px;
                height: 100%;
                z-index: 999;
            }

            .sidebar.active {
                left: 0;
            }

            .menu-toggle {
                display: block;
            }

            .main-content {
                width: 100%;
                margin-left: 0;
            }

            .top-bar {
                padding: 15px 20px;
            }

            .left-stats {
                gap: 10px;
            }

            .level-indicator,
            .streak-counter,
            .language-indicator {
                padding: 6px 10px;
            }

            .level-label,
            .streak-label,
            .language-name {
                display: none;
            }

            .content {
                padding: 20px 15px;
            }

            .lesson-title {
                font-size: 24px;
            }

            .lesson-actions {
                flex-direction: column;
                gap: 15px;
            }

            .practice-button,
            .complete-button {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .top-bar {
                padding: 15px 10px;
            }

            .left-stats {
                gap: 5px;
            }

            .content {
                padding: 15px 10px;
            }

            .lesson-card-inner {
                padding: 15px;
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .term {
                font-size: 20px;
            }

            .translation {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="sidebar">
            <div class="logo">
                <div class="logo-container">
                    <div class="mura-logo">
                        <img src="../image/mura.png" alt="Mura Logo">
                    </div>
                    <h1>Mura</h1>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item">
                        <a href="../Dashboard/dashboard.php">
                            <i class="fas fa-home"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item active">
                        <a href="lessons.php">
                            <i class="fas fa-book-open"></i>
                            Lessons
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../chatai/chatai.php">
                            <i class="fas fa-robot"></i>
                            TutorBot
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../Game/index.php">
                            <i class="fas fa-gamepad"></i>
                            Language Combat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../Dashboard/translation.php">
                            <i class="fas fa-language"></i>
                            Translation
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../ChatRoom/videochat.php">
                            <i class="fas fa-video"></i>
                            Video Chat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../Achievements/achievements.php">
                            <i class="fas fa-trophy"></i>
                            Achievements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../community/community.php">
                            <i class="fas fa-users"></i>
                            Community
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../Settings/settings.php">
                            <i class="fas fa-cog"></i>
                            Settings
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="../Dashboard/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>

        <div class="main-content">
            <div class="top-bar">
                <div class="menu-toggle">
                    <i class="fas fa-bars"></i>
                </div>
                <div class="left-stats">
                    <div class="level-indicator">
                        <div class="level-badge">
                            <span class="level-number"><?php echo $user_level; ?></span>
                        </div>
                        <span class="level-label">Level</span>
                    </div>
                    <div class="streak-counter">
                        <span class="streak-number"><?php echo $current_streak; ?></span>
                        <div class="streak-icon">
                            <span class="fire-emoji">🔥</span>
                        </div>
                        <span class="streak-label">Streak</span>
                    </div>
                </div>
                <div class="language-indicator">
                    <span class="language-name"><?php echo htmlspecialchars($selected_language); ?></span>
                    <div class="language-flag">
                        <span><?php echo getLanguageFlag($selected_language); ?></span>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="lesson-header">
                    <a href="lessons.php" class="back-button">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1 class="lesson-title">
                        <span class="lesson-title-icon"><?php echo $lesson_icon; ?></span>
                        <?php echo htmlspecialchars($lesson_title); ?> in <?php echo htmlspecialchars($selected_language); ?>
                    </h1>
                </div>

                <div class="lesson-cards">
                    <?php foreach ($lesson_content as $index => $item): ?>
                        <div class="lesson-card">
                            <div class="lesson-card-inner">
                                <div class="term-container">
                                    <div class="term"><?php echo htmlspecialchars($item['term']); ?></div>
                                    <div class="translation"><?php echo htmlspecialchars($item['translation']); ?></div>
                                    <div class="pronunciation">Pronunciation: <?php echo htmlspecialchars($item['pronunciation']); ?></div>
                                </div>
                                <button class="pronunciation-button" data-pronunciation="<?php echo htmlspecialchars($item['pronunciation']); ?>">
                                    <i class="fas fa-comment"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="lesson-actions">
                    <button class="practice-button">
                        <i class="fas fa-sync-alt"></i> Practice This Lesson
                    </button>
                    <button class="complete-button" id="complete-button">
                        <i class="fas fa-check"></i> Mark as Complete
                    </button>
                </div>
<!-- Quiz Container -->
<div id="quiz-container" class="quiz-container">
    <h2 class="quiz-title">Test Your Knowledge</h2>
    <p class="quiz-description">Let's see how much you've learned from this lesson!</p>
    
    <div id="quiz-questions">
        <!-- Questions will be dynamically inserted here -->
    </div>
    
    <div class="quiz-navigation">
        <button id="quiz-prev" class="quiz-button quiz-button-prev quiz-button-disabled">Previous</button>
        <button id="quiz-next" class="quiz-button quiz-button-next">Next</button>
    </div>
    
    <div id="quiz-results" style="display: none;">
        <div class="quiz-score">Your Score: <span id="quiz-score">0</span>/<span id="quiz-total">0</span></div>
        <div class="quiz-feedback" id="quiz-feedback"></div>
        <button id="quiz-continue" class="quiz-button quiz-button-submit quiz-continue">Complete Lesson</button>
    </div>
</div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                });
            }

// Pronunciation button functionality
const pronunciationButtons = document.querySelectorAll('.pronunciation-button');

// Add tooltip functionality for pronunciation buttons
pronunciationButtons.forEach(button => {
    button.addEventListener('click', function() {
        const pronunciation = this.getAttribute('data-pronunciation');
        
        // Create or update tooltip
        let tooltip = document.getElementById('pronunciation-tooltip');
        if (!tooltip) {
            tooltip = document.createElement('div');
            tooltip.id = 'pronunciation-tooltip';
            tooltip.className = 'pronunciation-tooltip';
            document.body.appendChild(tooltip);
        }
        
        // Position tooltip near the button
        const rect = this.getBoundingClientRect();
        tooltip.style.top = (rect.top - 40) + 'px';
        tooltip.style.left = (rect.left - 20) + 'px';
        
        // Set content and show
        tooltip.textContent = pronunciation;
        tooltip.classList.add('active');
        
        // Hide after delay
        setTimeout(() => {
            tooltip.classList.remove('active');
        }, 2000);
    });
});

// Quiz functionality
const completeButton = document.getElementById('complete-button');
const quizContainer = document.getElementById('quiz-container');
const quizQuestions = document.getElementById('quiz-questions');
const quizPrev = document.getElementById('quiz-prev');
const quizNext = document.getElementById('quiz-next');
const quizResults = document.getElementById('quiz-results');
const quizScore = document.getElementById('quiz-score');
const quizTotal = document.getElementById('quiz-total');
const quizFeedback = document.getElementById('quiz-feedback');
const quizContinue = document.getElementById('quiz-continue');

// Generate quiz questions from lesson content
function generateQuizQuestions() {
    const lessonContent = <?php echo json_encode($lesson_content); ?>;
    const questions = [];
    
    // Shuffle lesson content to randomize questions
    const shuffledContent = [...lessonContent].sort(() => Math.random() - 0.5);
    
    // Take up to 4 items for questions
    const questionItems = shuffledContent.slice(0, 4);
    
    questionItems.forEach((item, index) => {
        // Create a question based on the item
        const question = {
            id: index,
            text: `What is the translation of "${item.term}" in ${<?php echo json_encode($selected_language); ?>}?`,
            correctAnswer: item.translation,
            options: [item.translation]
        };
        
        // Add incorrect options from other items
        const otherItems = lessonContent.filter(i => i.translation !== item.translation);
        const shuffledOtherItems = [...otherItems].sort(() => Math.random() - 0.5);
        
        // Add 3 incorrect options
        for (let i = 0; i < 3 && i < shuffledOtherItems.length; i++) {
            question.options.push(shuffledOtherItems[i].translation);
        }
        
        // Shuffle options
        question.options = question.options.sort(() => Math.random() - 0.5);
        
        questions.push(question);
    });
    
    return questions;
}

// Initialize quiz
let currentQuestionIndex = 0;
let quizQuestionsList = [];
let userAnswers = [];

function startQuiz() {
    // Generate questions
    quizQuestionsList = generateQuizQuestions();
    userAnswers = new Array(quizQuestionsList.length).fill(null);
    
    // Show quiz container
    quizContainer.classList.add('active');
    
    // Scroll to quiz
    quizContainer.scrollIntoView({ behavior: 'smooth' });
    
    // Show first question
    showQuestion(0);
    
    // Update quiz total
    quizTotal.textContent = quizQuestionsList.length;
}

function showQuestion(index) {
    // Clear previous questions
    quizQuestions.innerHTML = '';
    
    // Get current question
    const question = quizQuestionsList[index];
    
    // Create question element
    const questionElement = document.createElement('div');
    questionElement.className = 'quiz-question';
    questionElement.innerHTML = `
        <div class="quiz-question-text">${question.text}</div>
        <div class="quiz-options">
            ${question.options.map((option, optionIndex) => `
                <div class="quiz-option ${userAnswers[index] === option ? 'selected' : ''}" data-option="${option}">
                    <span class="quiz-option-text">${option}</span>
                    <span class="quiz-option-icon">
                        <i class="fas ${option === question.correctAnswer ? 'fa-check' : 'fa-times'}"></i>
                    </span>
                </div>
            `).join('')}
        </div>
    `;
    
    // Add question to container
    quizQuestions.appendChild(questionElement);
    
    // Add event listeners to options
    const optionElements = questionElement.querySelectorAll('.quiz-option');
    optionElements.forEach(optionElement => {
        optionElement.addEventListener('click', () => {
            // Remove selected class from all options
            optionElements.forEach(el => el.classList.remove('selected'));
            
            // Add selected class to clicked option
            optionElement.classList.add('selected');
            
            // Save user answer
            userAnswers[index] = optionElement.dataset.option;
            
            // Enable next button if not last question
            if (index < quizQuestionsList.length - 1) {
                quizNext.classList.remove('quiz-button-disabled');
            } else {
                // Show submit button instead of next button
                quizNext.textContent = 'Submit Quiz';
                quizNext.classList.remove('quiz-button-next');
                quizNext.classList.add('quiz-button-submit');
            }
        });
    });
    
    // Update navigation buttons
    if (index === 0) {
        quizPrev.classList.add('quiz-button-disabled');
    } else {
        quizPrev.classList.remove('quiz-button-disabled');
    }
    
    if (index === quizQuestionsList.length - 1) {
        if (userAnswers[index] !== null) {
            quizNext.textContent = 'Submit Quiz';
            quizNext.classList.remove('quiz-button-next');
            quizNext.classList.add('quiz-button-submit');
        } else {
            quizNext.textContent = 'Next';
            quizNext.classList.add('quiz-button-next');
            quizNext.classList.remove('quiz-button-submit');
        }
    } else {
        quizNext.textContent = 'Next';
        quizNext.classList.add('quiz-button-next');
        quizNext.classList.remove('quiz-button-submit');
        
        if (userAnswers[index] === null) {
            quizNext.classList.add('quiz-button-disabled');
        } else {
            quizNext.classList.remove('quiz-button-disabled');
        }
    }
    
    // Update current question index
    currentQuestionIndex = index;
}

function showResults() {
    // Calculate score
    let score = 0;
    quizQuestionsList.forEach((question, index) => {
        if (userAnswers[index] === question.correctAnswer) {
            score++;
        }
    });
    
    // Update score
    quizScore.textContent = score;
    
    // Show feedback based on score
    const percentage = (score / quizQuestionsList.length) * 100;
    let feedback = '';
    
    if (percentage === 100) {
        feedback = 'Perfect! You\'ve mastered this lesson!';
    } else if (percentage >= 75) {
        feedback = 'Great job! You\'re doing very well!';
    } else if (percentage >= 50) {
        feedback = 'Good effort! Keep practicing to improve.';
    } else {
        feedback = 'Keep practicing! You\'ll get better with time.';
    }
    
    quizFeedback.textContent = feedback;
    
    // Show results
    quizQuestions.style.display = 'none';
    document.querySelector('.quiz-navigation').style.display = 'none';
    quizResults.style.display = 'block';
}

function showAnswers() {
    // Clear previous questions
    quizQuestions.innerHTML = '';
    
    // Show all questions with correct/incorrect answers
    quizQuestionsList.forEach((question, index) => {
        const questionElement = document.createElement('div');
        questionElement.className = 'quiz-question';
        questionElement.innerHTML = `
            <div class="quiz-question-text">${question.text}</div>
            <div class="quiz-options">
                ${question.options.map((option) => {
                    let optionClass = '';
                    if (userAnswers[index] === option) {
                        optionClass = option === question.correctAnswer ? 'correct' : 'incorrect';
                    } else if (option === question.correctAnswer) {
                        optionClass = 'correct';
                    }
                    
                    return `
                        <div class="quiz-option ${optionClass}" data-option="${option}">
                            <span class="quiz-option-text">${option}</span>
                            <span class="quiz-option-icon">
                                <i class="fas ${option === question.correctAnswer ? 'fa-check' : 'fa-times'}"></i>
                            </span>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
        
        quizQuestions.appendChild(questionElement);
    });
    
    // Show questions
    quizQuestions.style.display = 'block';
}

// Event listeners for quiz navigation
quizPrev.addEventListener('click', () => {
    if (currentQuestionIndex > 0) {
        showQuestion(currentQuestionIndex - 1);
    }
});

quizNext.addEventListener('click', () => {
    if (quizNext.classList.contains('quiz-button-disabled')) {
        return;
    }
    
    if (quizNext.textContent === 'Submit Quiz') {
        showResults();
    } else if (currentQuestionIndex < quizQuestionsList.length - 1) {
        showQuestion(currentQuestionIndex + 1);
    }
});

quizContinue.addEventListener('click', () => {
    // Show answers
    showAnswers();
    
    // Change continue button to complete lesson
    quizContinue.textContent = 'Complete Lesson';
    quizContinue.removeEventListener('click', arguments.callee);
    
    // Add new event listener to complete lesson
    quizContinue.addEventListener('click', () => {
        // Send AJAX request to mark lesson as complete
        const xhr = new XMLHttpRequest();
        xhr.open('POST', window.location.href, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (this.status === 200) {
                try {
                    const response = JSON.parse(this.responseText);
                    if (response.success) {
                        // Update button appearance
                        completeButton.innerHTML = '<i class="fas fa-check-circle"></i> Completed!';
                        completeButton.style.backgroundColor = '#2e7d32';
                        
                        // Show XP gain animation
                        const xpGain = document.createElement('div');
                        xpGain.className = 'xp-gain';
                        xpGain.textContent = '+' + response.xp_earned + ' XP';
                        document.body.appendChild(xpGain);
                        
                        setTimeout(() => {
                            document.body.removeChild(xpGain);
                            
                            // Show level up animation if applicable
                            if (response.leveled_up) {
                                showLevelUpAnimation();
                            }
                        }, 2000);
                    }
                } catch (e) {
                    console.error('Error parsing JSON response:', e);
                }
            }
        };
        xhr.send('complete_lesson=true');
    });
});

// Update the complete button to start quiz instead of completing lesson
completeButton.addEventListener('click', function() {
    startQuiz();
});

            // Function to show level up animation
            function showLevelUpAnimation() {
                const levelUp = document.createElement('div');
                levelUp.className = 'level-up';
                levelUp.innerHTML = `
                    <div class="level-up-content">
                        <h2 class="level-up-title">Level Up!</h2>
                        <div class="level-up-badge">${parseInt(document.querySelector('.level-number').textContent) + 1}</div>
                        <p class="level-up-message">Congratulations! You've reached the next level.</p>
                        <button class="level-up-button">Continue</button>
                    </div>
                `;
                document.body.appendChild(levelUp);
                
                // Update level number in UI
                document.querySelector('.level-number').textContent = parseInt(document.querySelector('.level-number').textContent) + 1;
                
                // Close level up animation when button is clicked
                levelUp.querySelector('.level-up-button').addEventListener('click', function() {
                    document.body.removeChild(levelUp);
                });
            }
        });
    </script>
</body>
</html>
