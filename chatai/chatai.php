<?php
// Language Buddy AI - Conversation Partner
// A single file PHP application for language learning with error correction

// Language full names mapping
$languageFullNames = [
  'en-US' => 'English',
  'fr-FR' => 'French',
  'es-ES' => 'Spanish',
  'de-DE' => 'German',
  'it-IT' => 'Italian'
];

// API key for Google Gemini API
$apiKey = "AIzaSyCKSDqxeEsq4UWQCHstFmyquxg5YHi8y6Q";

// Handle API requests if this is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  header('Content-Type: application/json');
  
  if ($_POST['action'] === 'sendMessage') {
    // Get the message and language from the POST data
    $message = isset($_POST['message']) ? $_POST['message'] : '';
    $language = isset($_POST['language']) ? $_POST['language'] : 'en-US';
    $conversationHistory = isset($_POST['history']) ? json_decode($_POST['history'], true) : [];
    
    // Get the language name
    $languageName = isset($languageFullNames[$language]) ? $languageFullNames[$language] : 'English';
    
    // Create the prompt for the AI
    $systemPrompt = "You are a helpful language tutor for {$languageName}. Your primary role is to help the user practice {$languageName} through conversation.

IMPORTANT INSTRUCTIONS (FOLLOW THESE EXACTLY):
1. ALWAYS check the user's message for grammatical errors, spelling mistakes, or incorrect word usage.
2. If you find ANY errors, start your response with \"Did you mean: [corrected version]\"
3. THEN, respond to the actual content of their message as a normal conversation.
4. ALWAYS respond in {$languageName}.
5. NEVER just repeat what the user said or give a generic response.
6. NEVER say \"How can I help you learn languages today?\" - instead, have a natural conversation.
7. If the user asks a question, answer it. If they make a statement, respond appropriately.
8. Keep your responses conversational, friendly, and helpful.

Examples of correct responses:

Example 1:
User: \"Hwo are you today\"
Your response: 
\"Did you mean: How are you today?
I'm doing well, thank you! The weather is nice today. What about you? How has your day been so far?\"

Example 2:
User: \"Im feel nice\"
Your response:
\"Did you mean: I feel nice
That's wonderful to hear! What made your day so good? I'd love to hear more about it.\"

Example 3:
User: \"I wanna learn english\"
Your response:
\"Did you mean: I want to learn English
That's great! English is a fascinating language. What aspects of English would you like to focus on? Vocabulary, grammar, pronunciation, or conversation skills?\"";

    // Format conversation history for the API
    $formattedHistory = "";
    if (count($conversationHistory) > 1) {
      // Only include the last few exchanges to avoid context length issues
      $recentHistory = array_slice($conversationHistory, -6);
      $formattedHistory = implode("\n", array_map(function($msg) {
        $role = $msg['role'] === 'user' ? 'User' : 'You';
        return "{$role}: {$msg['content']}";
      }, $recentHistory));
    }
    
    $prompt = $systemPrompt . "\n\n" . ($formattedHistory ? "Recent conversation:\n" . $formattedHistory . "\n\n" : "") . "User's latest message: \"{$message}\"\n\nYour response:";
    
    // Prepare data for Gemini API
    $data = [
      "contents" => [
        [
          "parts" => [
            [
              "text" => $prompt
            ]
          ]
        ]
      ],
      "generationConfig" => [
        "temperature" => 0.9,
        "topK" => 40,
        "topP" => 0.95,
        "maxOutputTokens" => 1024
      ]
    ];
    
    // Make the API request
    $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Process the response
    if ($httpCode === 200) {
      $responseData = json_decode($response, true);
      
      if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        $aiResponse = trim($responseData['candidates'][0]['content']['parts'][0]['text']);
        
        // Check if the response contains a correction
        $hasCorrection = strpos($aiResponse, "Did you mean:") === 0;
        $parts = [];
        
        if ($hasCorrection) {
          // Split the correction from the rest of the response
          $parts = preg_split('/\n+/', $aiResponse, 2);
        }
        
        echo json_encode([
          'success' => true,
          'response' => $aiResponse,
          'hasCorrection' => $hasCorrection,
          'correction' => $hasCorrection && count($parts) > 0 ? $parts[0] : '',
          'conversation' => $hasCorrection && count($parts) > 1 ? $parts[1] : $aiResponse
        ]);
      } else {
        echo json_encode([
          'success' => false,
          'error' => 'Invalid response format from API'
        ]);
      }
    } else {
      echo json_encode([
        'success' => false,
        'error' => 'API error. Status code: ' . $httpCode,
        'response' => $response
      ]);
    }
    
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Language Buddy AI - Conversation Partner</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/chatai.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
.navigation-buttons {
  display: flex;
  gap: 15px;
  margin-top: 20px;
  z-index: 10;
  position: relative;
}

.dashboard-btn, .back-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 20px;
  background: #9d4edd;
  color: white;
  text-decoration: none;
  border-radius: 10px;
  font-weight: bold;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.dashboard-btn:hover, .back-btn:hover {
  background: #7b2cbf;
  transform: translateY(-2px);
  box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
}

.dashboard-btn i, .back-btn i {
  margin-right: 8px;
}

@media (max-width: 480px) {
  .navigation-buttons {
    flex-direction: column;
    width: 100%;
    max-width: 600px;
  }
  
  .dashboard-btn, .back-btn {
    width: 100%;
  }
}
</style>
</head>
<body>
  <div id="backgroundWords" class="background-words-container"></div>
  <h1>🧠 Language Buddy AI - Conversation Partner</h1>
  <div class="chat-container">
    <div class="controls">
      <select id="language">
        <option value="en-US">English</option>
        <option value="fr-FR">Français</option>
        <option value="es-ES">Español</option>
        <option value="de-DE">Deutsch</option>
        <option value="it-IT">Italiano</option>
      </select>
      <button id="talkButton">🎤 Talk</button>
      <button id="stopButton">🔇 Stop</button>
      <button id="muteButton">🔊 Mute</button>
      <button id="clearButton" class="clear-button">Clear Chat</button>
    </div>
    <div class="chat-log" id="chatLog"></div>
    <div class="input-area">
      <input type="text" id="userInput" placeholder="Type something...">
      <button id="sendButton">Send</button>
    </div>
  </div>
  <div class="diagnostic-panel">
    <h3>Speech Recognition Diagnostics</h3>
    <div id="diagnosticInfo"></div>
    <div class="diagnostic-actions">
      <button id="testMicrophoneBtn">Test Microphone</button>
      <button id="checkNetworkBtn">Check Network</button>
    </div>
    <div class="troubleshooting">
      <h4>Troubleshooting Tips:</h4>
      <ul>
        <li>Make sure you're using Chrome, Edge, or Safari (Firefox has limited support)</li>
        <li>Check that your microphone is properly connected and has permission</li>
        <li>Try disabling VPN or proxy if you're using one</li>
        <li>Some corporate networks block speech recognition services</li>
        <li>Try using a different network connection if possible</li>
      </ul>
    </div>
  </div>
  <div class="navigation-buttons">
    <a href="../Dashboard/dashboard.php" class="dashboard-btn" id="dashboard-button">
      <i class="fas fa-home"></i> Back to Dashboard
    </a>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const chatLog = document.getElementById('chatLog');
      const userInput = document.getElementById('userInput');
      const languageSelector = document.getElementById('language');
      const talkButton = document.getElementById('talkButton');
      const stopButton = document.getElementById('stopButton');
      const muteButton = document.getElementById('muteButton');
      const sendButton = document.getElementById('sendButton');
      const clearButton = document.getElementById('clearButton');
      const backgroundWordsContainer = document.getElementById('backgroundWords');
      
      // Language full names mapping
      const languageFullNames = {
        'en-US': 'English',
        'fr-FR': 'French',
        'es-ES': 'Spanish',
        'de-DE': 'German',
        'it-IT': 'Italian'
      };
      
      // Common phrases in different languages for background animation
      const commonPhrases = {
        'en-US': ["Hello", "Thank you", "How are you", "Good morning", "Please", "Excuse me", "I understand", "See you later", "Good night", "You're welcome"],
        'fr-FR': ["Bonjour", "Merci", "Comment ça va", "Bon matin", "S'il vous plaît", "Excusez-moi", "Je comprends", "À plus tard", "Bonne nuit", "De rien"],
        'es-ES': ["Hola", "Gracias", "Cómo estás", "Buenos días", "Por favor", "Disculpe", "Entiendo", "Hasta luego", "Buenas noches", "De nada"],
        'de-DE': ["Hallo", "Danke", "Wie geht es dir", "Guten Morgen", "Bitte", "Entschuldigung", "Ich verstehe", "Bis später", "Gute Nacht", "Gern geschehen"],
        'it-IT': ["Ciao", "Grazie", "Come stai", "Buongiorno", "Per favore", "Scusa", "Capisco", "A più tardi", "Buonanotte", "Prego"]
      };
      
      // Conversation history to maintain context
      let conversationHistory = [];
      
      // Speech recognition variables
      let recognition = null;
      let isListening = false;
      
      // Speech synthesis variables
      let isMuted = false;
      let voices = [];
      
      // Initialize with a welcome message
      appendMessage('ai', "Hello! I'm your language conversation partner. Let's practice together. Just start typing or speaking in the language you want to practice!");
    
      // Function to add a message to the chat log
      function appendMessage(role, text, isCorrection = false, isInterim = false) {
        const p = document.createElement('p');
        p.className = isCorrection ? 'correction' : (isInterim ? role + ' interim-text' : role);
        p.textContent = (role === 'user' ? 'You: ' : role === 'ai' && !isCorrection ? 'AI: ' : '') + text;
        
        // If it's an interim message, give it an ID so we can update it
        if (isInterim) {
          p.id = 'interim-message';
          
          // Remove any existing interim message
          const existingInterim = document.getElementById('interim-message');
          if (existingInterim) {
            chatLog.removeChild(existingInterim);
          }
        }
        
        chatLog.appendChild(p);
        chatLog.scrollTop = chatLog.scrollHeight;
        
        return p; // Return the element in case we need to reference it later
      }
    
      // Function to send a message to the AI
      async function sendMessage() {
        const message = userInput.value.trim();
        if (!message) return;
        
        appendMessage('user', message);
        userInput.value = '';
        
        try {
          // Show loading indicator
          const loadingIndicator = document.createElement('p');
          loadingIndicator.className = 'ai';
          loadingIndicator.textContent = 'AI: Thinking...';
          chatLog.appendChild(loadingIndicator);
          
          // Get selected language
          const language = languageSelector.value;
          
          // Add user message to conversation history
          conversationHistory.push({ role: 'user', content: message });
          
          // Create form data for the request
          const formData = new FormData();
          formData.append('action', 'sendMessage');
          formData.append('message', message);
          formData.append('language', language);
          formData.append('history', JSON.stringify(conversationHistory));
          
          // Send the request to the PHP backend
          const response = await fetch('<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', {
            method: 'POST',
            body: formData
          });
          
          // Remove loading indicator
          chatLog.removeChild(loadingIndicator);
          
          if (response.ok) {
            const responseData = await response.json();
            
            if (responseData.success) {
              if (responseData.hasCorrection) {
                // Display the correction separately
                appendMessage('ai', responseData.correction, true);
                
                // Display the conversation response
                appendMessage('ai', responseData.conversation);
              } else {
                // No correction, just display the response
                appendMessage('ai', responseData.response);
              }
              
              // Add response to conversation history
              conversationHistory.push({ role: 'assistant', content: responseData.response });
              
              // Speak the response if not muted
              if (!isMuted) {
                speak(responseData.hasCorrection ? responseData.conversation : responseData.response);
              }
            } else {
              appendMessage('error', responseData.error || "Error processing request");
            }
          } else {
            const errorText = await response.text();
            appendMessage('error', "Server error. Please try again.");
          }
        } catch (error) {
          console.error('Error:', error);
          appendMessage('error', "Network error. Please check your connection and try again.");
        }
      }
    
      // Function to speak text using the selected language with female voice
      function speak(text) {
        if (!('speechSynthesis' in window)) {
          console.error("Speech synthesis not supported");
          return;
        }
        
        try {
          // Cancel any ongoing speech
          speechSynthesis.cancel();
          
          // If muted, don't speak
          if (isMuted) {
            return;
          }
          
          // Remove any correction part for speech
          const cleanText = text.replace(/Did you mean:.*?\n/s, '');
          
          const utterance = new SpeechSynthesisUtterance(cleanText);
          utterance.lang = languageSelector.value;
          
          // Make sure we have the latest voices
          voices = speechSynthesis.getVoices();
          
          // Try to find a female voice for the selected language
          const femaleVoice = voices.find(voice => 
            voice.lang.startsWith(languageSelector.value.split('-')[0]) && 
            (voice.name.toLowerCase().includes('female') || 
             voice.name.toLowerCase().includes('woman') || 
             voice.name.toLowerCase().includes('girl'))
          );
          
          // If a female voice is found, use it
          if (femaleVoice) {
            utterance.voice = femaleVoice;
          } else {
            // Otherwise try to find any voice for the selected language
            const languageVoice = voices.find(voice => 
              voice.lang.startsWith(languageSelector.value.split('-')[0])
            );
            
            if (languageVoice) {
              utterance.voice = languageVoice;
            }
          }
          
          // Set a slightly higher pitch for a more feminine voice if no specific female voice found
          if (!utterance.voice || !(utterance.voice.name.toLowerCase().includes('female') || 
                                   utterance.voice.name.toLowerCase().includes('woman') || 
                                   utterance.voice.name.toLowerCase().includes('girl'))) {
            utterance.pitch = 1.2;
          }
          
          // Add error handling for speech synthesis
          utterance.onerror = function(event) {
            console.error("Speech synthesis error:", event);
          };
          
          // Speak the text
          speechSynthesis.speak(utterance);
        } catch (error) {
          console.error("Error in speech synthesis:", error);
        }
      }
    
      // Function to start speech recognition
      function startListening() {
        // Check if browser supports speech recognition
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
          appendMessage('error', "Speech recognition is not supported in your browser. Try Chrome or Edge.");
          showDiagnosticPanel("Your browser doesn't support speech recognition. Please use Chrome, Edge, or Safari.");
          return;
        }
        
        // If already listening, stop first
        if (isListening) {
          stopListening();
          return;
        }

        const SpeechRecognition = window.webkitSpeechRecognition || window.SpeechRecognition;
        recognition = new SpeechRecognition();
        
        // Set recognition parameters
        recognition.lang = languageSelector.value;
        recognition.interimResults = true; // Get interim results for better feedback
        recognition.continuous = true; // Keep listening until stopped
        recognition.maxAlternatives = 3; // Get multiple alternatives to improve accuracy
        
        // Visual feedback
        talkButton.classList.add('listening');
        talkButton.textContent = '🎤 Listening...';
        userInput.placeholder = "Listening... Speak now";
        isListening = true;
        
        // Create a temporary element to show interim results
        let interimElement = null;
        let finalTranscript = '';
        
        // Add a small delay before starting recognition to ensure browser is ready
        setTimeout(() => {
          try {
            recognition.start();
            
            recognition.onstart = function() {
              console.log("Voice recognition started");
              // Create interim element when recognition starts
              interimElement = appendMessage('user', '...', false, true);
              
              // Update diagnostic info
              updateDiagnosticInfo("Speech recognition started successfully");
            };
            
            recognition.onresult = function(event) {
              let interimTranscript = '';
              
              // Collect results
              for (let i = event.resultIndex; i < event.results.length; i++) {
                const transcript = event.results[i][0].transcript;
                
                if (event.results[i].isFinal) {
                  finalTranscript += transcript + ' ';
                  // Update the input field with final transcript
                  userInput.value = finalTranscript.trim();
                } else {
                  interimTranscript += transcript;
                }
              }
              
              // Update the interim element with current speech
              if (interimElement) {
                interimElement.textContent = 'You: ' + (finalTranscript + interimTranscript).trim();
              }
              
              // Update diagnostic info
              updateDiagnosticInfo("Receiving speech input...");
            };
            
            recognition.onerror = function(event) {
              console.error("Speech recognition error", event.error);
              
              let errorMessage = "Error occurred in recognition: ";
              let shouldRetry = false;
              let showDiagnostics = false;
              
              switch(event.error) {
                case 'no-speech':
                  errorMessage = "No speech was detected. Please try again.";
                  shouldRetry = true;
                  break;
                case 'audio-capture':
                  errorMessage = "No microphone was found. Please ensure a microphone is connected.";
                  showDiagnostics = true;
                  updateDiagnosticInfo("Microphone not detected or not accessible. Check your microphone connection and browser permissions.");
                  break;
                case 'not-allowed':
                  errorMessage = "Permission to use microphone was denied. Please allow microphone access.";
                  showDiagnostics = true;
                  updateDiagnosticInfo("Microphone permission denied. Click the lock icon in your address bar and allow microphone access.");
                  break;
                case 'network':
                  errorMessage = "Network error occurred. Switching to keyboard input mode.";
                  showDiagnostics = true;
                  updateDiagnosticInfo("Network error detected. This usually happens when the speech recognition service can't be reached. Try the troubleshooting tips below.");
                  showKeyboardInputMode();
                  break;
                case 'aborted':
                  // Don't show error for user-initiated aborts
                  shouldRetry = false;
                  break;
                default:
                  errorMessage += event.error;
                  shouldRetry = true;
                  showDiagnostics = true;
                  updateDiagnosticInfo("Unknown error: " + event.error + ". Try the troubleshooting tips below.");
              }
              
              if (errorMessage && event.error !== 'aborted') {
                appendMessage('error', errorMessage);
              }
              
              if (showDiagnostics) {
                showDiagnosticPanel();
              }
              
              stopListening();
              
              // Retry logic for certain errors, but not for network errors (we switch to keyboard mode instead)
              if (shouldRetry && event.error !== 'network' && retryCount < 2) {
                console.log(`Retrying speech recognition (attempt ${retryCount + 1})`);
                setTimeout(() => {
                  startListening(retryCount + 1);
                }, 1000); // Wait 1 second before retrying
              }
            };
            
            recognition.onend = function() {
              console.log("Speech recognition ended");
              
              // If we have a final transcript, send the message
              if (finalTranscript.trim()) {
                // Remove the interim element
                if (interimElement && document.getElementById('interim-message')) {
                  chatLog.removeChild(interimElement);
                }
                
                // Send the message
                userInput.value = finalTranscript.trim();
                sendMessage();
              } else if (interimElement && document.getElementById('interim-message')) {
                // If no final transcript and interim element exists, remove it
                chatLog.removeChild(interimElement);
              }
              
              stopListening();
            };
            
            // Add a timeout to stop listening after 10 seconds of silence
            setTimeout(() => {
              if (recognition && isListening) {
                recognition.stop();
              }
            }, 10000);
          } catch (error) {
            console.error("Error starting speech recognition:", error);
            appendMessage('error', "Failed to start speech recognition. Please try typing instead.");
            updateDiagnosticInfo("Error initializing speech recognition: " + error.message);
            showDiagnosticPanel();
            stopListening();
          }
        }, 200); // Small delay before starting recognition
      }
      
      // Function to stop speech recognition
      function stopListening() {
        if (recognition) {
          try {
            recognition.stop();
          } catch (error) {
            console.error("Error stopping recognition:", error);
          }
        }
        
        talkButton.classList.remove('listening');
        talkButton.textContent = '🎤 Talk';
        userInput.placeholder = "Type something...";
        isListening = false;
      }
      
      // Function to toggle mute state
      function toggleMute() {
        isMuted = !isMuted;
        
        if (isMuted) {
          muteButton.textContent = '🔈 Unmute';
          muteButton.classList.add('muted');
          
          // Stop any ongoing speech
          if ('speechSynthesis' in window) {
            speechSynthesis.cancel();
          }
        } else {
          muteButton.textContent = '🔊 Mute';
          muteButton.classList.remove('muted');
        }
      }

      // Function to show the diagnostic panel
      function showDiagnosticPanel(message) {
        const diagnosticPanel = document.querySelector('.diagnostic-panel');
        diagnosticPanel.style.display = 'block';
        
        if (message) {
          updateDiagnosticInfo(message);
        }
        
        // Scroll to the diagnostic panel
        setTimeout(() => {
          diagnosticPanel.scrollIntoView({ behavior: 'smooth' });
        }, 100);
      }

      // Function to update diagnostic information
      function updateDiagnosticInfo(message) {
        const diagnosticInfo = document.getElementById('diagnosticInfo');
        const timestamp = new Date().toLocaleTimeString();
        
        const messageElement = document.createElement('div');
        messageElement.textContent = `[${timestamp}] ${message}`;
        
        diagnosticInfo.appendChild(messageElement);
        diagnosticInfo.scrollTop = diagnosticInfo.scrollHeight;
      }

      // Function to show keyboard input mode message
      function showKeyboardInputMode() {
        // Check if the message already exists
        if (document.querySelector('.keyboard-input-mode')) {
          return;
        }
        
        const keyboardMessage = document.createElement('div');
        keyboardMessage.className = 'keyboard-input-mode';
        keyboardMessage.innerHTML = `
          <p>Speech recognition is unavailable due to network issues.</p>
          <p>Please type your messages in the text box below instead.</p>
        `;
        
        chatLog.appendChild(keyboardMessage);
        chatLog.scrollTop = chatLog.scrollHeight;
        
        // Focus on the input field
        userInput.focus();
      }
      
      // Function to create background words animation
      function createBackgroundWords() {
        // Clear any existing words
        backgroundWordsContainer.innerHTML = "";
        
        // Get current language
        const language = languageSelector.value;
        const phrases = commonPhrases[language] || commonPhrases['en-US'];
        
        // Create words
        phrases.forEach((phrase) => {
          const wordElement = document.createElement("div");
          wordElement.className = "background-word";
          wordElement.textContent = phrase;
          
          // Random positioning and styling
          const size = Math.floor(Math.random() * 24) + 12; // Font size between 12px and 36px
          const left = Math.random() * 100; // Position from left (0-100%)
          const top = Math.random() * 100; // Position from top (0-100%)
          const opacity = Math.random() * 0.2 + 0.05; // Opacity between 0.05 and 0.25
          const animationDuration = Math.floor(Math.random() * 20) + 30; // Animation duration between 30s and 50s
          const animationDelay = Math.floor(Math.random() * 10); // Animation delay between 0s and 10s
          
          wordElement.style.fontSize = `${size}px`;
          wordElement.style.left = `${left}%`;
          wordElement.style.top = `${top}%`;
          wordElement.style.opacity = opacity.toString();
          wordElement.style.animation = `float ${animationDuration}s ease-in-out ${animationDelay}s infinite`;
          
          backgroundWordsContainer.appendChild(wordElement);
        });
      }
      
      // Function to clear the chat
      function clearChat() {
        // Keep only the first welcome message
        while (chatLog.childNodes.length > 1) {
          chatLog.removeChild(chatLog.lastChild);
        }
        
        // Reset conversation history
        conversationHistory = [];
        
        // Add a system message about clearing the chat
        appendMessage('ai', "Chat cleared. Let's start a new conversation!");
      }
      
      // Initialize speech synthesis voices
      function initVoices() {
        if ('speechSynthesis' in window) {
          // Get available voices
          voices = speechSynthesis.getVoices();
          
          // If voices array is empty, wait for the voiceschanged event
          if (voices.length === 0) {
            speechSynthesis.onvoiceschanged = function() {
              voices = speechSynthesis.getVoices();
            };
          }
        }
      }
    
      // Event listeners
      sendButton.addEventListener('click', sendMessage);
      
      userInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          sendMessage();
        }
      });
      
      talkButton.addEventListener('click', startListening);
      
      stopButton.addEventListener('click', function() {
        // Stop speech synthesis
        if ('speechSynthesis' in window) {
          speechSynthesis.cancel();
        }
        
        // Also stop speech recognition if it's active
        stopListening();
      });
      
      muteButton.addEventListener('click', toggleMute);
      
      clearButton.addEventListener('click', clearChat);
      
      // Update background words and reset conversation when language changes
      languageSelector.addEventListener('change', function() {
        const selectedLang = languageSelector.options[languageSelector.selectedIndex].text;
        
        // Reset conversation history when language changes
        conversationHistory = [];
        
        // Add a system message about the language change
        appendMessage('ai', `Now practicing in ${selectedLang}. Let's continue our conversation!`);
        
        // Update background words
        createBackgroundWords();
      });
      
      // Initialize background words and voices
      createBackgroundWords();
      initVoices();

      // Test microphone button
      document.getElementById('testMicrophoneBtn').addEventListener('click', function() {
        updateDiagnosticInfo("Testing microphone...");
        
        // Check if browser supports getUserMedia
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
          updateDiagnosticInfo("Your browser doesn't support microphone access. Try using Chrome or Edge.");
          return;
        }
        
        // Try to access the microphone
        navigator.mediaDevices.getUserMedia({ audio: true })
          .then(function(stream) {
            updateDiagnosticInfo("Microphone test successful! Your microphone is working.");
            
            // Create an audio context to analyze the stream
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const analyser = audioContext.createAnalyser();
            const microphone = audioContext.createMediaStreamSource(stream);
            microphone.connect(analyser);
            
            // Stop the stream after the test
            setTimeout(() => {
              stream.getTracks().forEach(track => track.stop());
            }, 2000);
          })
          .catch(function(error) {
            updateDiagnosticInfo("Microphone test failed: " + error.message);
          });
      });

      // Check network button
      document.getElementById('checkNetworkBtn').addEventListener('click', function() {
        updateDiagnosticInfo("Checking network connection...");
        
        // Try to fetch a small resource to test connectivity
        fetch('https://www.google.com/favicon.ico', { mode: 'no-cors', cache: 'no-store' })
          .then(() => {
            updateDiagnosticInfo("Network connection is working. You can access the internet.");
            
            // Now check if we can reach a speech recognition service
            updateDiagnosticInfo("Testing speech recognition service connectivity...");
            
            // Create a temporary recognition object to test the connection
            try {
              const SpeechRecognition = window.webkitSpeechRecognition || window.SpeechRecognition;
              const testRecognition = new SpeechRecognition();
              
              testRecognition.onstart = function() {
                updateDiagnosticInfo("Speech recognition service is accessible!");
                testRecognition.abort();
              };
              
              testRecognition.onerror = function(event) {
                if (event.error === 'network') {
                  updateDiagnosticInfo("Speech recognition service is not accessible. This might be due to firewall settings or network restrictions.");
                } else {
                  updateDiagnosticInfo("Speech recognition test failed: " + event.error);
                }
              };
              
              testRecognition.start();
              
              // Stop the test after a short time if it hasn't already stopped
              setTimeout(() => {
                try {
                  testRecognition.abort();
                } catch (e) {
                  // Ignore errors if already aborted
                }
              }, 3000);
            } catch (error) {
              updateDiagnosticInfo("Failed to initialize speech recognition test: " + error.message);
            }
          })
          .catch(() => {
            updateDiagnosticInfo("Network connection test failed. You appear to be offline or have limited connectivity.");
          });
      });
    });
  </script>
</body>
</html>
