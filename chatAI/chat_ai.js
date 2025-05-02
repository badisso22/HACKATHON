document.addEventListener('DOMContentLoaded', function() {
    const chatLog = document.getElementById('chatLog');
    const userInput = document.getElementById('userInput');
    const languageSelector = document.getElementById('language');
    const talkButton = document.getElementById('talkButton');
    const sendButton = document.getElementById('sendButton');
    const testConnectionBtn = document.getElementById('testConnectionBtn');
    const debugOutput = document.getElementById('debugOutput');
    
    let chatHistory = [
      {
        role: "system",
        content: "You are a friendly and helpful language tutor who always responds in the target language, helping the user learn through fun conversation."
      }
    ];
  
    // Initialize with a welcome message
    appendMessage('ai', "Hello! I'm your language tutor. How can I help you today?");
  
    // Function to add a message to the chat log
    function appendMessage(role, text) {
      const p = document.createElement('p');
      p.className = role;
      p.textContent = (role === 'user' ? 'You: ' : role === 'ai' ? 'AI: ' : '') + text;
      chatLog.appendChild(p);
      chatLog.scrollTop = chatLog.scrollHeight;
    }
  
    // Function to log debug information
    function logDebug(message, data = null) {
      const timestamp = new Date().toLocaleTimeString();
      let logMessage = `[${timestamp}] ${message}`;
      
      if (data) {
        if (typeof data === 'object') {
          logMessage += '\n' + JSON.stringify(data, null, 2);
        } else {
          logMessage += '\n' + data;
        }
      }
      
      debugOutput.textContent += logMessage + '\n\n';
      debugOutput.scrollTop = debugOutput.scrollHeight;
      console.log(message, data);
    }
  
    // Function to send a message to the AI
    async function sendMessage() {
      const message = userInput.value.trim();
      if (!message) return;
      
      appendMessage('user', message);
      chatHistory.push({ role: 'user', content: message });
      userInput.value = '';
      
      try {
        // Show loading indicator
        const loadingIndicator = document.createElement('p');
        loadingIndicator.className = 'ai';
        loadingIndicator.textContent = 'AI: Thinking...';
        chatLog.appendChild(loadingIndicator);
        
        logDebug('Sending request to PHP backend', { messages: chatHistory });
        
        // IMPORTANT: Change this line to point to your huggingface.php file
        const response = await fetch('huggingface.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ messages: chatHistory })
        });
        
        // Remove loading indicator
        chatLog.removeChild(loadingIndicator);
        
        logDebug('Received response', { status: response.status });
        
        if (response.ok) {
          const data = await response.json();
          logDebug('Response data', data);
          
          if (data.error) {
            appendMessage('error', "Error: " + data.error);
            return;
          }
          
          if (data.choices && data.choices[0] && data.choices[0].message) {
            const reply = data.choices[0].message.content;
            appendMessage('ai', reply);
            chatHistory.push({ role: 'assistant', content: reply });
            speak(reply);
          } else {
            appendMessage('error', "Invalid response format from API");
            logDebug('Invalid response format', data);
          }
        } else {
          const errorText = await response.text();
          appendMessage('error', "Server error. Please try again.");
          logDebug('Server error', { status: response.status, response: errorText });
        }
      } catch (error) {
        console.error('Error:', error);
        appendMessage('error', "Network error. Please check your connection and try again.");
        logDebug('Network error', { message: error.message, stack: error.stack });
      }
    }
  
    // Function to test the connection to the backend
    async function testConnection() {
      debugOutput.textContent = ''; // Clear previous debug output
      logDebug('Testing connection to backend...');
      
      try {
        // IMPORTANT: Change this to test huggingface.php
        const response = await fetch('huggingface.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ 
            messages: [
              { role: "system", content: "You are a helpful assistant." },
              { role: "user", content: "Hello" }
            ] 
          })
        });
        
        logDebug('Connection test response', { status: response.status });
        
        if (response.ok) {
          const data = await response.json();
          logDebug('✅ Backend connection successful', data);
        } else {
          const errorText = await response.text();
          logDebug('❌ Backend connection failed', { status: response.status, response: errorText });
        }
      } catch (error) {
        logDebug('❌ Connection test failed', { message: error.message });
      }
    }
  
    // Function to speak text using the selected language
    function speak(text) {
      if ('speechSynthesis' in window) {
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = languageSelector.value;
        speechSynthesis.speak(utterance);
      }
    }
  
    // Function to start speech recognition
    function startListening() {
      if (!('SpeechRecognition' in window) && !('webkitSpeechRecognition' in window)) {
        alert('Speech recognition is not supported in your browser');
        return;
      }
      
      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      const recognition = new SpeechRecognition();
      
      recognition.lang = languageSelector.value;
      recognition.continuous = false;
      recognition.interimResults = false;
      
      talkButton.classList.add('listening');
      talkButton.textContent = '🔴 Listening...';
      
      recognition.start();
      
      recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript;
        userInput.value = transcript;
        talkButton.classList.remove('listening');
        talkButton.textContent = '🎤 Talk';
        sendMessage();
      };
      
      recognition.onerror = function(event) {
        console.error('Speech recognition error:', event.error);
        talkButton.classList.remove('listening');
        talkButton.textContent = '🎤 Talk';
        logDebug('Speech recognition error', event.error);
      };
      
      recognition.onend = function() {
        talkButton.classList.remove('listening');
        talkButton.textContent = '🎤 Talk';
      };
    }
  
    // Event listeners
    sendButton.addEventListener('click', sendMessage);
    
    userInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        sendMessage();
      }
    });
    
    talkButton.addEventListener('click', startListening);
    
    testConnectionBtn.addEventListener('click', testConnection);
  });