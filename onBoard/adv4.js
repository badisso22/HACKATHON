document.addEventListener('DOMContentLoaded', function() {
    const progressBar = document.querySelector('.progress-bar');
    const progress = sessionStorage.getItem('progress') || '75%';
    progressBar.style.transition = 'width 1.2s ease-in-out';
    progressBar.style.width = progress;

    document.querySelectorAll('.button').forEach(button => {
        button.addEventListener('click', function() {
            const level = this.textContent.includes('Beginner') ? 'beginner' : 'advanced';
            completeOnboarding(level);
        });
    });

    document.querySelector('.link a').addEventListener('click', function(e) {
        e.preventDefault();
        completeOnboarding(null);
    });
});

function completeOnboarding(level) {
    const progressBar = document.querySelector('.progress-bar');

    const savePromise = level ? 
        fetch('set_onboarding_data.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({proficiency_level: level})
        }) : Promise.resolve({json: () => ({success: true})});

    savePromise
        .then(response => response.json())
        .then(data => {
            if (!data.success) throw new Error('Failed to save level');

            progressBar.style.transition = 'width 1.2s ease-in-out';
            progressBar.style.width = '100%';

            return fetch('complete_onboarding.php', {method: 'POST'});
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 1200);
            } else {
                throw new Error('Failed to complete onboarding');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
}