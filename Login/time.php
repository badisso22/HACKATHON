<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mura - Daily Goal</title>
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

        /* Progress bar styles */
        .progress-container {
            position: relative;
            height: 16px;
            background-color: var(--darker-background);
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            width: 50%; /* Adjust based on progress - 50% means halfway through */
            background-color: var(--progress-color);
            transition: width 0.3s ease;
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

        /* Custom time input */
        .custom-time-container {
            display: none;
            padding: 15px;
            margin: 15px;
            background-color: var(--card-background);
            border-radius: 15px;
        }

        .custom-time-container.active {
            display: block;
            animation: fadeIn 0.3s ease forwards;
        }

        .custom-time-input {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .custom-time-input input {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid rgba(157, 78, 221, 0.3);
            border-radius: 10px;
            font-size: 16px;
            background-color: #3a1857;
            color: var(--text-color);
        }

        .custom-time-input input:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        .custom-time-input span {
            font-size: 16px;
            color: var(--text-color);
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
        <div class="progress-bar"></div>
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
        <div class="question-text">What is your daily goal?</div>
    </div>

    <!-- Options grid -->
    <div class="options-container">
        <div class="option" data-value="3">
            <div class="option-icon icon-1">3</div>
            <div class="option-content">
                <div class="option-text">3 min/day</div>
                <div class="option-subtext">Casual</div>
            </div>
        </div>

        <div class="option" data-value="10">
            <div class="option-icon icon-2">10</div>
            <div class="option-content">
                <div class="option-text">10 min/day</div>
                <div class="option-subtext">Normal</div>
            </div>
        </div>

        <div class="option" data-value="15">
            <div class="option-icon icon-3">15</div>
            <div class="option-content">
                <div class="option-text">15 min/day</div>
                <div class="option-subtext">Intensive</div>
            </div>
        </div>

        <div class="option" data-value="30">
            <div class="option-icon icon-4">30</div>
            <div class="option-content">
                <div class="option-text">30 min/day</div>
                <div class="option-subtext">Extreme</div>
            </div>
        </div>

        <div class="option" data-value="other">
            <div class="option-icon icon-5">❓</div>
            <div class="option-content">
                <div class="option-text">Other</div>
                <div class="option-subtext">Custom</div>
            </div>
        </div>
    </div>

    <!-- Custom time input (hidden by default) -->
    <div class="custom-time-container">
        <div class="custom-time-input">
            <input type="number" id="custom-time" min="1" max="120" placeholder="Enter time">
            <span>minutes per day</span>
        </div>
    </div>

    <!-- Continue button -->
    <div class="continue-container">
        <button id="continue-btn" class="continue-btn" disabled>CONTINUE</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const options = document.querySelectorAll('.option');
            const continueBtn = document.getElementById('continue-btn');
            const customTimeContainer = document.querySelector('.custom-time-container');
            const customTimeInput = document.getElementById('custom-time');
            const progressBar = document.querySelector('.progress-bar');
            let selectedOption = null;
            
            // Retrieve progress from session storage or set default
            const progress = sessionStorage.getItem('progress') || '75%';
            progressBar.style.transition = 'width 0.5s ease-in-out';
            progressBar.style.width = progress;
            
            // Add click event listeners to options
            options.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    options.forEach(opt => opt.classList.remove('selected'));
                    
                    // Add selected class to clicked option
                    this.classList.add('selected');
                    
                    // Add pulse animation
                    this.style.animation = 'none';
                    setTimeout(() => {
                        this.style.animation = 'pulse 0.3s ease';
                    }, 10);
                    
                    // Get the time value
                    selectedOption = this.getAttribute('data-value');
                    
                    // If "other" is selected, show custom time input
                    if (selectedOption === 'other') {
                        customTimeContainer.classList.add('active');
                        customTimeInput.focus();
                        continueBtn.disabled = !customTimeInput.value;
                    } else {
                        customTimeContainer.classList.remove('active');
                        continueBtn.disabled = false;
                    }
                    
                    // Update progress bar when an option is selected
                    progressBar.style.width = '75%';
                    sessionStorage.setItem('progress', '75%');
                });
            });
            
            // Add input event to custom time input
            customTimeInput.addEventListener('input', function() {
                continueBtn.disabled = !this.value;
                
                // Update progress bar when custom time is entered
                if (this.value) {
                    progressBar.style.width = '75%';
                    sessionStorage.setItem('progress', '75%');
                }
            });
            
            // Add click event to continue button
            continueBtn.addEventListener('click', function() {
                let selectedTime;
                
                if (selectedOption === 'other') {
                    selectedTime = customTimeInput.value;
                } else {
                    selectedTime = selectedOption;
                }
                
                // Update progress bar to 100% when continuing
                sessionStorage.setItem('progress', '100%');
                progressBar.style.width = '100%';
                setTimeout(() => {
                    alert('You have completed the setup!');
                }, 500);
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