<?php
// Start session at the very beginning
session_start();

// Check if clear action was requested
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    // Destroy the session
    session_unset();
    session_destroy();
    
    // Redirect to clean URL to avoid resubmission
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Force session destruction on page refresh (not POST)
// Use a more reliable method to detect page refresh
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Store the current timestamp in a cookie to detect refresh
    $lastVisit = isset($_COOKIE['last_visit']) ? $_COOKIE['last_visit'] : 0;
    $currentTime = time();
    
    // Set the cookie for next time
    setcookie('last_visit', $currentTime, time() + 86400, '/');
    
    // If this is a refresh (not first visit or direct navigation)
    if ($lastVisit > 0) {
        // Destroy the session completely
        session_unset();
        session_destroy();
        // Start a new session
        session_start();
    }
}

$translatedText = '';
$apiError = false;

$languageFullNames = [
    'fr' => 'French',
    'es' => 'Spanish',
    'de' => 'German',
    'ja' => 'Japanese'
];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {
    $text = trim($_POST["text"]);
    $language = $_POST["language"];
    
    $_SESSION['text'] = $text;
    $_SESSION['language'] = $language;

    // Try API translation with Gemini
    try {
        $apiKey = "AIzaSyCKSDqxeEsq4UWQCHstFmyquxg5YHi8y6Q";
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

        // Get the full language name
        $languageName = isset($languageFullNames[$language]) ? $languageFullNames[$language] : $language;

        // Prepare data for Gemini API
        $data = [
            "contents" => [
                [
                    "parts" => [
                        [
                            "text" => "Translate the following text to $languageName. Only respond with the translation, nothing else: \"$text\""
                        ]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.2,
                "topK" => 40,
                "topP" => 0.95,
                "maxOutputTokens" => 1024
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15); 

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (!curl_errno($ch) && $httpCode >= 200 && $httpCode < 300) {
            $decoded = json_decode($response, true);

            if (isset($decoded["candidates"][0]["content"]["parts"][0]["text"])) {
                $translatedText = trim($decoded["candidates"][0]["content"]["parts"][0]["text"]);
                
                $_SESSION['translatedText'] = $translatedText;
                
                $translatedText = preg_replace('/^["\'](.*)["\']\s*$/', '$1', $translatedText);
                
                if (strpos(strtolower($translatedText), 'translation:') !== false) {
                    $parts = preg_split('/translation:\s*/i', $translatedText, 2);
                    if (count($parts) > 1) {
                        $translatedText = trim($parts[1]);
                    }
                }
            } else {
                $apiError = true;
                $translatedText = "Translation failed. Please try again.";
                error_log("Gemini API invalid response format: " . $response);
            }
        } else {
            $apiError = true;
            $translatedText = "Translation service unavailable. Please try again later.";
            error_log("Gemini API error: Status code $httpCode, Response: $response");
        }

        curl_close($ch);
    } catch (Exception $e) {
        $apiError = true;
        $translatedText = "An error occurred during translation.";
        error_log("Translation error: " . $e->getMessage());
    }
} else {
    
    if (isset($_SESSION['translatedText'])) {
        $translatedText = $_SESSION['translatedText'];
    }
}


$languageNames = [
    'fr' => 'French',
    'es' => 'Spanish',
    'de' => 'German',
    'ja' => 'Japanese'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Translator</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <style>
        body {
            background-color: #552a6c;
            font-family: Arial, sans-serif;
            color: #44226d;
            text-align: center;
            padding: 50px;
            overflow: hidden;
            position: relative;
        }

        h1 {
            color: #2c3e50;
            position: relative;
            z-index: 2;
        }
        
        .container {
            max-width: 600px;
            height: 400px;
            margin: auto;
            margin-top: 0%;
            padding: 20px;
            background-color: #cfcccc;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 2;
        }
        
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
        }
        
        #text {
            width: 80%;
            height: 110px;
            padding: 10px;
            border-radius: 8px;
            border: 2px solid #ccc;
            margin-bottom: 0px;
            background-color: rgb(255, 255, 255);
            color: #44226d;
            resize: none;
        }
        
        label {
            font-size: 18px;
            color: #44226d;
        }
        
        #language {
            width: 30%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #663f6d;
            margin-bottom: 20px;
            background-color: rgb(255, 255, 255);
            color: #44226d;
            text-align: center;
        }
        
        #buttons {
            display: flex;
            justify-content: space-between;
            width: 40%;
            margin-top: -20px;
            margin-bottom: 20px;
        }
        
        #btn {
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            background-color: #44226d;
            color: white;
            cursor: pointer;
            font-size: 12px;
        }
        
        #btn:hover {
            background-color: #663f6d;
        }
        
        .last {
            max-width: 600px;
            height: 85px;
            margin: auto;
            margin-top: 1%;
            padding: 20px;
            background-color: #cfcccc;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 2;
        }
        
        .status {
            font-size: 12px;
            font-style: italic;
            color: #44226d;
            margin-top: 5px;
        }
        
        .offline-note {
            font-size: 12px;
            color: #663f6d;
            margin-top: 5px;
        }
        
        #show {
            font-size: 18px;
            margin-top: -2%;
            color: #44226d;
            background-color: white;
            border: 1px solid #44226d;  
            height: 110px;
            border-radius: 20px;
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            overflow-y: auto;
            text-align: left;
            position: relative;
        }
        
        .copy-btn {
            position: absolute;
            bottom: 5px;
            left: 5px;
            background-color: #44226d;
            color: white;
            border: none;
            border-radius: 3px;
            padding: 3px 8px;
            font-size: 12px;
            cursor: pointer;
            opacity: 0.7;
        }
        
        .copy-btn:hover {
            opacity: 1;
        }
        
        #backgroundWords {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
            overflow: hidden;
        }
        
        /* Responsive design for smaller screens */
        @media (max-width: 768px) {
            body {
                padding: 20px;
            }
            
            .container, .last {
                max-width: 100%;
            }
            
            #text {
                width: 90%;
            }
            
            #language {
                width: 50%;
            }
            
            #buttons {
                width: 60%;
            }
        }
    </style>
</head>

<body>
    <!-- Background words container -->
    <div id="backgroundWords"></div>
    
    <div class="container">
        <h1>Translator</h1>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="translationForm">
            <label for="text">Enter text to translate:</label><br>
            <textarea id="text" name="text" rows="4" cols="50" required><?php echo isset($_SESSION['text']) ? htmlspecialchars($_SESSION['text']) : ''; ?></textarea><br><br>
            
            <select id="language" name="language" required>
                <option value="">--Select Language--</option>
                <option value="fr" <?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'fr') ? 'selected' : ''; ?>>French</option>
                <option value="es" <?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'es') ? 'selected' : ''; ?>>Spanish</option>
                <option value="de" <?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'de') ? 'selected' : ''; ?>>German</option>
                <option value="ja" <?php echo (isset($_SESSION['language']) && $_SESSION['language'] == 'ja') ? 'selected' : ''; ?>>Japanese</option>
            </select><br><br>
            
            <div id="buttons">
                <input id="btn" type="submit" name="submit" value="Translate">
                <button id="btn" type="button" onclick="clearAll();">Clear</button>
            </div>
            
            <div class="offline-note">
                Common phrases are available offline
            </div>
        </form>
    </div>
    
    <div class="last">
        <div id="show">
            <?php if (!empty($translatedText)): ?>
                <?php echo $translatedText; ?>
                <button class="copy-btn" onclick="copyTranslation()">Copy</button>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($translatedText) && isset($_SESSION['language']) && array_key_exists($_SESSION['language'], $languageNames)): ?>
            <div class="status">
                <?php if (!$apiError): ?>
                    <span>Translated to <?php echo $languageNames[$_SESSION['language']]; ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
    // Background floating words animation
    const backgroundWordsContainer = document.getElementById("backgroundWords");

    const words = [
      "Hello", "Hola", "Bonjour", "Ciao", "Hallo", "こんにちは", "Adiós", "Merci",
      "Gracias", "Danke", "ありがとう", "Oui", "Sí", "Ja", "はい",
      "Non", "No", "Nein", "いいえ", "Au revoir", "Auf Wiedersehen", "さようなら"
    ];

    function createFloatingWord(text) {
      const word = document.createElement("span");
      word.textContent = text;

      const x = Math.random() * window.innerWidth;
      const y = window.innerHeight + Math.random() * 100;

      word.style.position = "fixed";
      word.style.left = `${x}px`;
      word.style.top = `${y}px`;
      word.style.fontSize = `${14 + Math.random() * 20}px`;
      word.style.opacity = "0.17";
      word.style.color = "#ffffff";
      word.style.fontWeight = "600";
      word.style.pointerEvents = "none";
      word.style.zIndex = "0";
      word.style.transition = "top 0.1s linear";

      backgroundWordsContainer.appendChild(word);

      let currentY = y;
      const speed = 0.8 + Math.random() * 0.6; 

      function animate() {
        currentY -= speed;
        
        // If the word reaches the top, remove it instead of repositioning
        if (currentY < -50) {
          word.remove(); // Remove the word from the DOM
          return; // Stop the animation for this word
        }
        
        word.style.top = `${currentY}px`;
        requestAnimationFrame(animate);
      }

      animate();
    }

    // Create new floating words at intervals
    setInterval(() => {
      const wordText = words[Math.floor(Math.random() * words.length)];
      createFloatingWord(wordText);
    }, 400);

    // Copy translation to clipboard
    function copyTranslation() {
      const translationText = document.getElementById('show').innerText.replace('Copy', '').trim();
      
      if (navigator.clipboard) {
        navigator.clipboard.writeText(translationText)
          .then(() => {
            const copyBtn = document.querySelector('.copy-btn');
            copyBtn.textContent = 'Copied!';
            setTimeout(() => {
              copyBtn.textContent = 'Copy';
            }, 2000);
          })
          .catch(err => {
            console.error('Failed to copy: ', err);
          });
      } else {
        // Fallback for browsers that don't support clipboard API
        const textarea = document.createElement('textarea');
        textarea.value = translationText;
        
        textarea.style.position = 'fixed';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        
        try {
          document.execCommand('copy');
          const copyBtn = document.querySelector('.copy-btn');
          copyBtn.textContent = 'Copied!';
          setTimeout(() => {
            copyBtn.textContent = 'Copy';
          }, 2000);
        } catch (err) {
          console.error('Failed to copy: ', err);
        }
        
        document.body.removeChild(textarea);
      }
    }

    // Clear all data and destroy session
    function clearAll() {
      // Clear UI elements
      document.getElementById('text').value = '';
      document.getElementById('language').value = '';
      document.getElementById('show').innerHTML = '';
      const statusElement = document.querySelector('.status');
      if (statusElement) {
        statusElement.innerHTML = '';
      }
      
      // Redirect to the same page with clear action to destroy session
      window.location.href = '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?action=clear';
    }

    // Force session destruction on page refresh
    window.addEventListener('beforeunload', function() {
      // Create a synchronous request to clear the session
      var xhr = new XMLHttpRequest();
      xhr.open('GET', '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?action=clear', false);
      xhr.send();
    });
    </script>
</body>
</html>