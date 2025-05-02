<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Language Selection</title>
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
            width: 50%; /* Adjust based on progress */
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

        /* Content section */
        .content-section {
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
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
            margin-bottom: 15px;
        }

        .content-text {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        /* Buttons */
        .button-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            width: 100%;
            max-width: 400px;
        }

        .button {
            padding: 15px;
            background-color: var(--card-background);
            border-radius: 10px;
            color: var(--text-color);
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .button:hover {
            background-color: #3a1857;
            border-color: var(--primary-color-light);
        }

        .button:active {
            transform: scale(0.98);
        }

        .link {
            margin-top: 20px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .link a {
            color: var(--primary-color-light);
            text-decoration: none;
        }

        .link a:hover {
            text-decoration: underline;
        }

        /* Responsive styles */
        @media (max-width: 600px) {
            .content-text {
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

    <!-- Content section -->
    <div class="content-section">
        <div class="mascot">🦉</div>
        <div class="content-text">Do you already know some Spanish?</div>

        <!-- Buttons -->
        <div class="button-container">
            <div class="button" onclick="selectLevel('beginner')">🐣 Beginner</div>
            <div class="button" onclick="selectLevel('advanced')">🦅 Advanced</div>
        </div>

        <div class="link">
            <a href="#" onclick="skipSelection()">Skip this step</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const progressBar = document.querySelector('.progress-bar');

            // Retrieve progress from session storage or set default
            const progress = sessionStorage.getItem('progress') || '75%';
            progressBar.style.transition = 'width 1.2s ease-in-out';
            progressBar.style.width = progress;

            const buttons = document.querySelectorAll('.button');

            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    // Smoothly increase progress to 100%
                    progressBar.style.transition = 'width 1.2s ease-in-out';
                    progressBar.style.width = '100%';
                    sessionStorage.setItem('progress', '100%');

                    setTimeout(() => {
                        window.location.href = 'dashboard.html';
                    }, 1200);
                });
            });
        });

        function selectLevel(level) {
            alert(`You selected: ${level.charAt(0).toUpperCase() + level.slice(1)}`);
            sessionStorage.setItem('progress', '75%');
            window.location.href = 'dashboard.html';
        }

        function skipSelection() {
            alert('Skipped level selection');
            sessionStorage.setItem('progress', '75%');
            window.location.href = 'dashboard.html';
        }
    </script>
</body>
</html>