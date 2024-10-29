import * as bootstrap from 'bootstrap';

document.querySelectorAll('.nav-link').forEach((navLink) => {
    navLink.addEventListener('click', (event) => {
        const collapsableArrow = navLink.querySelector('.collapsable-arrow');
        if (collapsableArrow) {
            collapsableArrow.classList.toggle('expanded');
        }
    });
});

// when clicking on .toggle-menu, toggle the display of  .side-menu-container element even if it's not shown on mobile devices
document.querySelectorAll('.toggle-menu-button').forEach((toggleMenu) => {
    toggleMenu.addEventListener('click', (event) => {
        document.querySelector('.side-menu-container').classList.toggle('active');
        document.querySelector('.page-content-container').classList.toggle('full-width');
    });
});

// when clicking outside .side-menu-container, hide it if it's mobile device
document.addEventListener('click', (event) => {
    if (!event.target.closest('.side-menu-container') && !event.target.closest('.toggle-menu')) {
        if (window.innerWidth < 1200) {
            document.querySelector('.side-menu-container').classList.remove('active');
            document.querySelector('.page-content-container').classList.add('full-width');
        }
    }
});

// if the width of the window is less than 1200px, hide the side menu
window.addEventListener('resize', (event) => {
    if (window.innerWidth < 1200) {
        document.querySelector('.side-menu-container').classList.remove('active');
        document.querySelector('.page-content-container').classList.add('full-width');
    }
});

// when clicking on .generate-password-button button, generate a random password and put it in the #password input
document.querySelectorAll('.generate-password-button').forEach((generatePasswordButton) => {
    generatePasswordButton.addEventListener('click', (event) => {
        event.preventDefault();
        document.querySelector('#password').value = generateRandomPassword();
    });
});

Livewire.on('laraveltable:action:confirm', (actionType, actionIdentifier, modelPrimary, confirmationQuestion) => {
    if (window.confirm(confirmationQuestion)) {
        Livewire.emit('laraveltable:action:confirmed', actionType, actionIdentifier, modelPrimary);
    }
});

Livewire.on('laraveltable:action:feedback', (feedbackMessage) => {
    const toastContainer = document.querySelector('.toast-container');
    const toast = document.createElement('div');
    toast.classList.add('toast', 'align-items-center', 'text-white', 'bg-success');
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${feedbackMessage}
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"
            aria-label="Close"></button>
        </div>
    `;
    toastContainer.appendChild(toast);
    const toastBootstrap = new bootstrap.Toast(toast);
    toastBootstrap.show();
});


// on page load, show all toasts
document.querySelectorAll('.toast').forEach((toast) => {
    const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toast);
    toastBootstrap.show();
});


function generateRandomPassword() {
    let length = 18,
        charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+~|}{[]:;?><,./-=",
        password = "";
    for (let i = 0, n = charset.length; i < length; ++i) {
        password += charset.charAt(Math.floor(Math.random() * n));
    }

    return password;
}
