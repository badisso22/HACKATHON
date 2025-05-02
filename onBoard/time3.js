document.addEventListener('DOMContentLoaded', function() {
    const progressBar = document.querySelector('.progress-bar');
    const progress = sessionStorage.getItem('progress') || '50%';
    progressBar.style.transition = 'width 1.2s ease-in-out';
    progressBar.style.width = progress;

    const options = document.querySelectorAll('.option');
    const continueBtn = document.getElementById('continue-btn');
    const customTimeInput = document.getElementById('custom-time');

    options.forEach(option => {
        option.addEventListener('click', function() {
            options.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');

            const selectedOption = this.getAttribute('data-value');
            if (selectedOption === 'other') {
                document.querySelector('.custom-time-container').classList.add('active');
                customTimeInput.focus();
                continueBtn.disabled = !customTimeInput.value;
            } else {
                document.querySelector('.custom-time-container').classList.remove('active');
                continueBtn.disabled = false;
            }
        });
    });

    customTimeInput.addEventListener('input', function() {
        continueBtn.disabled = !this.value;
    });

    continueBtn.addEventListener('click', function() {
        const selectedOption = document.querySelector('.option.selected');
        const minutes = selectedOption?.getAttribute('data-value') === 'other' 
            ? customTimeInput.value 
            : selectedOption?.getAttribute('data-value');

        if (!minutes) return;

        fetch('set_onboarding_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                daily_goal: minutes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                progressBar.style.transition = 'width 1.2s ease-in-out';
                progressBar.style.width = '75%';

                setTimeout(() => {
                    window.location.href = 'adv4.php';
                }, 1200);
            } else {
                alert('Error saving daily goal');
            }
        });
    });
});