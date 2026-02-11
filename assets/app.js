/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

function importAll(r) {
    return r.keys().map(r);
}
importAll(require.context('./images', false, /\.(png|jpe?g|svg|webp)$/));

import * as bootstrap from 'bootstrap';

document.addEventListener("DOMContentLoaded", (event) => {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el, {
            html: true // allow raw HTML inside title
        });
    });
});

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');

    // создаём элемент тоста
    const toastEl = document.createElement('div');
    toastEl.className = `toast text-bg-${type} border-0`;
    toastEl.role = 'alert';
    toastEl.ariaLive = 'assertive';
    toastEl.ariaAtomic = 'true';

    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    container.appendChild(toastEl);

    const toast = new bootstrap.Toast(toastEl, {
        delay: 3500,
        autohide: true
    });
    toast.show();

    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

window.showToast = showToast;

function updateThemeIcon(theme) {
    document.querySelectorAll('.theme-icon').forEach(function (el) {
        if (theme === 'dark') {
            el.classList.remove('bi-moon-stars-fill');
            el.classList.add('bi-sun-fill');
        } else {
            el.classList.remove('bi-sun-fill');
            el.classList.add('bi-moon-stars-fill');
        }
    });
}
window.updateThemeIcon = updateThemeIcon;

updateThemeIcon(document.documentElement.getAttribute('data-bs-theme'));

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.theme-toggle').forEach(function (el) {
        el.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
    });
});
