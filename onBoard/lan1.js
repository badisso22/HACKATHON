document.addEventListener('DOMContentLoaded', function() {
    // Background words animation (keep existing code)
    const backgroundWordsContainer = document.getElementById("backgroundWords");
    // ... (keep your existing background words code) ...

    // Progress bar setup
    const progressBar = document.getElementById("progressBar");
    progressBar.style.width = '0%';
    
    // Language selection
    const languageCards = document.querySelectorAll('.language-card');
    
    languageCards.forEach(card => {
        card.addEventListener('click', function() {
            const language = this.getAttribute('data-language');
            
            // Save to database via AJAX
            fetch('set_onboarding_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    language: language
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update UI
                    progressBar.style.transition = 'width 1.2s ease-in-out';
                    progressBar.style.width = '25%';
                    progressBar.style.boxShadow = '0 0 10px rgba(88, 204, 2, 0.8)';
                    
                    // Redirect after animation
                    setTimeout(() => {
                        window.location.href = 'qst2.php';
                    }, 1200);
                } else {
                    console.error('Error from server:', data.error);
                    alert('Error saving language selection: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('An error occurred while saving your selection. Please try again.');
            });
        });
    });
});