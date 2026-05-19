document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-open-modal]').forEach(function (button) {
        button.addEventListener('click', function () {
            const modal = document.querySelector(button.dataset.openModal);
            if (modal) modal.classList.add('show');
        });
    });

    document.querySelectorAll('[data-close-modal]').forEach(function (button) {
        button.addEventListener('click', function () {
            const modal = button.closest('.modal');
            if (modal) modal.classList.remove('show');
        });
    });

    document.querySelectorAll('.modal').forEach(function (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) modal.classList.remove('show');
        });
    });

    const logoutModal = document.getElementById('logoutModal');
    const logoutButton = document.querySelector('.logout-trigger');
    if (logoutButton && logoutModal) {
        logoutButton.addEventListener('click', function () {
            logoutModal.classList.add('show');
            logoutModal.setAttribute('aria-hidden', 'false');
        });
    }

    document.querySelectorAll('[data-close-logout]').forEach(function (button) {
        button.addEventListener('click', function () {
            logoutModal.classList.remove('show');
            logoutModal.setAttribute('aria-hidden', 'true');
        });
    });
});
