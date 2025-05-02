<!-- lan.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mura - Learn Languages</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #9d4edd;
            --primary-color-light: #b76ee8;
            --primary-color-dark: #7b2cbf;
            --background-color: #1f1235;
            --darker-background: #170829;
            --form-background: #3c1642;
            --input-background: #f3f0ff;
            --text-color: #ffffff;
            --text-dark: #333333;
            --text-muted: #cccccc;
            --button-hover: #7b2cbf;
            --success-color: #4caf50;
            --success-color-light: #81c784;
            --danger-color: #f44336;
            --danger-color-light: #e57373;
            --progress-color: #58cc02;
            --progress-bg: rgba(88, 204, 2, 0.2);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100vh;
            font-family: "Inter", sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            position: relative;
            background-image: radial-gradient(circle at 50% 50%, #2a1745 0%, #1f1235 100%);
        }

        /* Improved progress bar styles */
        .progress-container {
            position: relative;
            height: 12px;
            background-color: var(--darker-background);
            overflow: hidden;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .progress-bar {
            height: 100%;
            width: 0; /* Start at 0 and animate to 25% */
            background: linear-gradient(90deg, #58cc02, #7dd957);
            transition: width 1.2s cubic-bezier(0.22, 1, 0.36, 1);
            box-shadow: 0 0 8px rgba(88, 204, 2, 0.5);
            position: relative;
            overflow: hidden;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(
                90deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.3) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            animation: shimmer 2s infinite;
            transform: translateX(-100%);
        }

        @keyframes shimmer {
            100% {
                transform: translateX(100%);
            }
        }

        .background-words-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .background-word {
            position: absolute;
            color: rgba(255, 255, 255, 0.15);
            font-weight: bold;
            pointer-events: none;
            z-index: 1;
            white-space: nowrap;
            text-shadow: 0 0 5px rgba(157, 78, 221, 0.3);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background-color: rgba(60, 22, 66, 0.8);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 10;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background-color: var(--success-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 10px;
        }

        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: white;
            background: linear-gradient(135deg, var(--primary-color), #c77dff);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 10px rgba(157, 78, 221, 0.3);
        }

        .language-selector {
            color: var(--text-muted);
            font-size: 14px;
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .language-selector span {
            margin-right: 5px;
        }

        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
            position: relative;
            z-index: 10;
        }

        .page-title {
            text-align: center;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 60px;
            color: var(--text-color);
            text-shadow: 0 2px 10px rgba(157, 78, 221, 0.3);
        }

        .language-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }

        .language-card {
            background-color: var(--form-background);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 20px;
            text-align: center;
            border: 1px solid rgba(157, 78, 221, 0.2);
            backdrop-filter: blur(10px);
        }

        .language-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
            border-color: var(--primary-color);
        }

        .flag {
            width: 80px;
            height: 60px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            position: relative;
        }

        /* Italian Flag */
        .italian-flag {
            display: flex;
            width: 100%;
            height: 100%;
        }
        
        .italian-green {
            background-color: #009246;
            width: 33.33%;
            height: 100%;
        }
        
        .italian-white {
            background-color: #ffffff;
            width: 33.33%;
            height: 100%;
        }
        
        .italian-red {
            background-color: #ce2b37;
            width: 33.33%;
            height: 100%;
        }

        /* Spanish Flag */
        .spanish-flag {
            display: flex;
            flex-direction: column;
            width: 100%;
            height: 100%;
        }
        
        .spanish-red {
            background-color: #c60b1e;
            height: 25%;
            width: 100%;
        }
        
        .spanish-yellow {
            background-color: #ffc400;
            height: 50%;
            width: 100%;
            position: relative;
        }
        
        .spanish-emblem {
            position: absolute;
            left: 25%;
            top: 50%;
            transform: translateY(-50%);
            width: 15px;
            height: 15px;
            background-color: #c60b1e;
            border-radius: 50%;
        }

        /* French Flag */
        .french-flag {
            display: flex;
            width: 100%;
            height: 100%;
        }
        
        .french-blue {
            background-color: #002654;
            width: 33.33%;
            height: 100%;
        }
        
        .french-white {
            background-color: #ffffff;
            width: 33.33%;
            height: 100%;
        }
        
        .french-red {
            background-color: #ce2b37;
            width: 33.33%;
            height: 100%;
        }

        /* English Flag */
        .english-flag {
            background-color: #ffffff;
            width: 100%;
            height: 100%;
            position: relative;
        }
        
        .english-horizontal {
            position: absolute;
            background-color: #ce2b37;
            width: 100%;
            height: 20%;
            top: 40%;
        }
        
        .english-vertical {
            position: absolute;
            background-color: #ce2b37;
            height: 100%;
            width: 20%;
            left: 40%;
        }

        /* German Flag */
        .german-flag {
            display: flex;
            flex-direction: column;
            width: 100%;
            height: 100%;
        }
        
        .german-black {
            background-color: #000000;
            height: 33.33%;
            width: 100%;
        }
        
        .german-red {
            background-color: #dd0000;
            height: 33.33%;
            width: 100%;
        }
        
        .german-gold {
            background-color: #ffce00;
            height: 33.33%;
            width: 100%;
        }

        .language-name {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 8px;
        }

        .learner-count {
            font-size: 14px;
            color: var(--text-muted);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(5deg);
            }
            100% {
                transform: translateY(0) rotate(0deg);
            }
        }

        .main-content {
            animation: fadeIn 1s ease forwards;
        }

        @media (max-width: 768px) {
            .header {
                padding: 15px 20px;
            }
            
            .language-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
            .page-title {
                font-size: 28px;
                margin-bottom: 40px;
            }
            
            .flag {
                width: 70px;
                height: 52px;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 10px 15px;
            }
            
            .logo-icon {
                width: 32px;
                height: 32px;
                font-size: 18px;
            }
            
            .logo-text {
                font-size: 20px;
            }
            
            .language-grid {
                grid-template-columns: 1fr 1fr;
                gap: 15px;
            }
            
            .page-title {
                font-size: 24px;
                margin-bottom: 30px;
            }
            
            .flag {
                width: 60px;
                height: 45px;
            }
            
            .language-name {
                font-size: 16px;
            }
            
            .learner-count {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Progress bar -->
    <div class="progress-container">
        <div class="progress-bar" id="progressBar"></div>
    </div>

    <div class="background-words-container" id="backgroundWords">
        <!-- Background words will be added by JavaScript -->
    </div>

    <header class="header">
        <div class="logo">
            <div class="logo-icon">🦉</div>
            <div class="logo-text">Mura</div>
        </div>
        <div class="language-selector">
            <span>SITE LANGUAGE:</span>
            <strong>ENGLISH</strong>
            <span>▼</span>
        </div>
    </header>

    <main class="main-content">
        <h1 class="page-title">I want to learn...</h1>

        <div class="language-grid">
            <div class="language-card" data-language="Spanish">
                <div class="flag">
                    <div class="spanish-flag">
                        <div class="spanish-red"></div>
                        <div class="spanish-yellow">
                            <div class="spanish-emblem"></div>
                        </div>
                        <div class="spanish-red"></div>
                    </div>
                </div>
                <div class="language-name">Spanish</div>
                <div class="learner-count">49.6M learners</div>
            </div>

            <div class="language-card" data-language="French">
                <div class="flag">
                    <div class="french-flag">
                        <div class="french-blue"></div>
                        <div class="french-white"></div>
                        <div class="french-red"></div>
                    </div>
                </div>
                <div class="language-name">French</div>
                <div class="learner-count">27.5M learners</div>
            </div>

            <div class="language-card" data-language="English">
                <div class="flag">
                    <div class="english-flag">
                        <div class="english-horizontal"></div>
                        <div class="english-vertical"></div>
                    </div>
                </div>
                <div class="language-name">English</div>
                <div class="learner-count">58.3M learners</div>
            </div>

            <div class="language-card" data-language="German">
                <div class="flag">
                    <div class="german-flag">
                        <div class="german-black"></div>
                        <div class="german-red"></div>
                        <div class="german-gold"></div>
                    </div>
                </div>
                <div class="language-name">German</div>
                <div class="learner-count">19.2M learners</div>
            </div>

            <div class="language-card" data-language="Italian">
                <div class="flag">
                    <div class="italian-flag">
                        <div class="italian-green"></div>
                        <div class="italian-white"></div>
                        <div class="italian-red"></div>
                    </div>
                </div>
                <div class="language-name">Italian</div>
                <div class="learner-count">13.5M learners</div>
            </div>
        </div>

        <form id="continueForm">
            <button type="submit">Continue</button>
        </form>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const backgroundWordsContainer = document.getElementById("backgroundWords");
            const progressBar = document.getElementById("progressBar");
            
            // Ensure the progress bar remains empty
            progressBar.style.width = '0%';
            sessionStorage.setItem('progress', '0%');
            
            // Words in different languages
            const words = [
                "Hello", "Hola", "Bonjour", "Ciao", "Hallo", "Olá", "Namaste", "Salaam",
                "Zdravstvuyte", "Nǐ hǎo", "Konnichiwa", "Anyoung", "Merhaba", "Hej", "Ahoj",
                "Szia", "Shalom", "Yassou", "Salve", "Mingalaba", "Sawubona", "Habari", "Halo",
                "Gracias", "Merci", "Thank you", "Danke", "Grazie", "Obrigado", "Dhanyavaad",
                "Spasibo", "Xièxiè", "Arigatō", "Kamsahamnida", "Teşekkür ederim", "Tack",
                "Děkuji", "Köszönöm", "Todah", "Efcharistó", "Gratias", "Kyay zu tin ba de",
                "Ngiyabonga", "Asante", "Terima kasih"
            ];
            
            // Create 40 random words
            for (let i = 0; i < 40; i++) {
                const word = document.createElement("div");
                word.className = "background-word";
                
                // Random word from the array
                word.textContent = words[Math.floor(Math.random() * words.length)];
                
                // Random position
                const top = Math.random() * 100;
                const left = Math.random() * 100;
                
                // Random size
                const size = Math.floor(Math.random() * 20) + 14; // 14px to 34px
                
                // Random opacity
                const opacity = Math.random() * 0.1 + 0.05; // 0.05 to 0.15
                
                // Set styles
                word.style.top = `${top}%`;
                word.style.left = `${left}%`;
                word.style.fontSize = `${size}px`;
                word.style.opacity = opacity;
                
                // Random animation duration between 15-30s
                const duration = Math.random() * 15 + 15;
                // Random delay between 0-10s
                const delay = Math.random() * 10;
                
                word.style.animation = `float ${duration}s ease-in-out ${delay}s infinite`;
                
                // Add to container
                backgroundWordsContainer.appendChild(word);
            }
            
            // Add click event to language cards
            const languageCards = document.querySelectorAll('.language-card');
            
            languageCards.forEach(card => {
                card.addEventListener('click', function() {
                    const language = this.getAttribute('data-language');
                    
                    // Store the selected language in session storage
                    sessionStorage.setItem('selectedLanguage', language);
                    
                    // Increase progress to 50% on first continue
                    progressBar.style.width = '50%';
                    progressBar.style.boxShadow = '0 0 10px rgba(88, 204, 2, 0.8)';
                    
                    // Store progress in session storage
                    sessionStorage.setItem('progress', '50%');
                    
                    // Wait for animation to complete before redirecting
                    setTimeout(() => {
                        // Redirect to the question page
                        window.location.href = 'Qst.php';
                    }, 500);
                });
            });
        });

        document.getElementById('continueForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent form submission

            const progressBar = document.getElementById('progressBar');

            // Increase progress to 100% on continue
            progressBar.style.width = '100%';
            progressBar.style.boxShadow = '0 0 10px rgba(88, 204, 2, 0.8)';

            // Store progress in session storage
            sessionStorage.setItem('progress', '100%');

            // Redirect to the next page after a short delay
            setTimeout(() => {
                window.location.href = 'nextPage.php'; // Replace with the actual next page
            }, 500);
        });
    </script>
</body>
</html>