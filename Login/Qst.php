<!-- Qst.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mura - Learn Languages</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --background-color: #1e0b36;
            --darker-background: #170829;
            --card-background: #2d1245;
            --primary-color: #9d4edd;
            --primary-color-light: #b76ee8;
            --primary-color-dark: #7b2cbf;
            --text-color: #ffffff;
            --text-muted: #cccccc;
            --icon-bg-1: #ff7b89;
            --icon-bg-2: #8a5cf5;
            --icon-bg-3: #3db4f2;
            --icon-bg-4: #ff9e40;
            --icon-bg-5: #4caf50;
            --progress-color: #58cc02;
            --progress-bg: rgba(88, 204, 2, 0.2);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            min-height: 100vh;
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
            width: 0; /* Start at 0 and animate to 50% */
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

        /* Header styles */
        .header {
            background-color: var(--darker-background);
            padding: 15px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo-icon {
            width: 32px;
            height: 32px;
            background-color: #8a5cf5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }

        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color-light);
        }

        .settings-button {
            position: absolute;
            right: 20px;
            background: transparent;
            border: none;
            color: var(--text-color);
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        /* Question section */
        .question-section {
            padding: 20px;
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .mascot {
            width: 60px;
            height: 60px;
            background-color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin-right: 15px;
        }

        .question-text {
            font-size: 22px;
            font-weight: 700;
        }

        /* Options grid */
        .options-container {
            padding: 0 15px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            max-width: 800px;
            margin: 0 auto;
        }

        .option {
            background-color: var(--card-background);
            border-radius: 15px;
            padding: 15px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }

        .option:hover {
            background-color: #3a1857;
        }

        .option.selected {
            border-color: var(--primary-color-light);
            background-color: #3a1857;
        }

        .option-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
        }

        .icon-1 {
            background-color: var(--icon-bg-1);
        }

        .icon-2 {
            background-color: var(--icon-bg-2);
        }

        .icon-3 {
            background-color: var(--icon-bg-3);
        }

        .icon-4 {
            background-color: var(--icon-bg-4);
        }

        .icon-5 {
            background-color: var(--icon-bg-5);
        }

        .option-content {
            flex: 1;
        }

        .option-text {
            font-size: 16px;
            font-weight: 500;
            display: block;
        }

        .option-subtext {
            font-size: 14px;
            color: var(--text-muted);
            display: block;
            margin-top: 2px;
        }

        /* Continue button */
        .continue-container {
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        .continue-btn {
            padding: 15px 40px;
            background-color: var(--primary-color);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            min-width: 200px;
        }

        .continue-btn:hover:not(:disabled) {
            background-color: var(--primary-color-light);
        }

        .continue-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Responsive styles */
        @media (max-width: 600px) {
            .options-container {
                grid-template-columns: 1fr;
            }
            
            .question-text {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Progress bar -->
    <div class="progress-container">
        <div class="progress-bar" id="progressBar"></div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="logo-container">
            <div class="logo-icon">🌎</div>
            <div class="logo-text">Mura</div>
        </div>
        <button class="settings-button">⚙️ Settings</button>
    </header>

    <!-- Question section -->
    <div class="question-section">
        <div class="mascot">🦉</div>
        <div class="question-text">Why do you want to learn <span id="selected-language">this language</span>?</div>
    </div>

    <!-- Options grid -->
    <div class="options-container">
        <div class="option" data-reason="fun">
            <div class="option-icon icon-1">🎉</div>
            <div class="option-content">
                <div class="option-text">Have fun</div>
            </div>
        </div>

        <div class="option" data-reason="connections">
            <div class="option-icon icon-2">👥</div>
            <div class="option-content">
                <div class="option-text">Make connections</div>
            </div>
        </div>

        <div class="option" data-reason="travel">
            <div class="option-icon icon-3">✈️</div>
            <div class="option-content">
                <div class="option-text">Prepare for travel</div>
            </div>
        </div>

        <div class="option" data-reason="brain">
            <div class="option-icon icon-4">🧠</div>
            <div class="option-content">
                <div class="option-text">Exercise my brain</div>
            </div>
        </div>

        <div class="option" data-reason="studies">
            <div class="option-icon icon-5">📚</div>
            <div class="option-content">
                <div class="option-text">Help with my studies</div>
            </div>
        </div>

        <div class="option" data-reason="career">
            <div class="option-icon icon-1">💼</div>
            <div class="option-content">
                <div class="option-text">Boost my career</div>
            </div>
        </div>

        <div class="option" data-reason="other">
            <div class="option-icon icon-2">❓</div>
            <div class="option-content">
                <div class="option-text">Other</div>
            </div>
        </div>
    </div>

    <!-- Continue button -->
    <div class="continue-container">
        <button id="continue-btn" class="continue-btn" disabled>CONTINUE</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const progressBar = document.getElementById('progressBar');

            // Retrieve progress from session storage or set default
            const progress = sessionStorage.getItem('progress') || '0%';
            progressBar.style.transition = 'width 1.2s ease-in-out';
            progressBar.style.width = progress;

            const options = document.querySelectorAll('.option');
            const continueBtn = document.getElementById('continue-btn');

            options.forEach(option => {
                option.addEventListener('click', function() {
                    options.forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');

                    continueBtn.disabled = false;
                });
            });

            continueBtn.addEventListener('click', function(event) {
                event.preventDefault();

                // Smoothly increase progress to 50%
                progressBar.style.transition = 'width 1.2s ease-in-out';
                progressBar.style.width = '50%';
                sessionStorage.setItem('progress', '50%');

                setTimeout(() => {
                    window.location.href = 'time.php';
                }, 1200);
            });

            // Add click event to settings button
            const settingsButton = document.querySelector('.settings-button');
            
            if (settingsButton) {
                settingsButton.addEventListener('click', function() {
                    alert('Settings panel would open here.');
                });
            }
        });
    </script>
</body>
</html>