document.addEventListener('DOMContentLoaded', function() {
    const progressBar = document.getElementById('progressBar');
    const progress = sessionStorage.getItem('progress') || '25%';
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

        progressBar.style.transition = 'width 1.2s ease-in-out';
        progressBar.style.width = '50%';

        setTimeout(() => {
            window.location.href = 'time3.php';
        }, 1200);
    });
});