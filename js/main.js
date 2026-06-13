/* ===========================================================================
   Task Tracker - client-side interactivity + validation
   Progressive enhancement only: the server validates everything too.
   =========================================================================== */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        setupMobileNav();
        setupDeleteConfirm();
        setupRegisterValidation();
        setupLoginValidation();
        setupTaskFormValidation();
        autoSubmitFilters();
    });

    /* ---- Mobile navigation toggle ---- */
    function setupMobileNav() {
        var toggle = document.getElementById('navToggle');
        var nav = document.getElementById('mainNav');
        if (!toggle || !nav) return;
        toggle.addEventListener('click', function () {
            nav.classList.toggle('open');
        });
    }

    /* ---- Confirm before delete ---- */
    function setupDeleteConfirm() {
        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (!window.confirm(form.getAttribute('data-confirm'))) {
                    e.preventDefault();
                }
            });
        });
    }

    /* ---- Small helper: show an inline error under a field ---- */
    function setError(input, message) {
        clearError(input);
        if (!message) return;
        var span = document.createElement('span');
        span.className = 'field-error';
        span.textContent = message;
        input.parentNode.appendChild(span);
        input.setAttribute('aria-invalid', 'true');
    }
    function clearError(input) {
        input.removeAttribute('aria-invalid');
        var next = input.parentNode.querySelector('.field-error');
        if (next) next.remove();
    }
    function isEmail(v) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
    }

    /* ---- Registration form validation ---- */
    function setupRegisterValidation() {
        var form = document.getElementById('registerForm');
        if (!form) return;
        form.addEventListener('submit', function (e) {
            var ok = true;
            var name = form.name;
            var email = form.email;
            var pw = form.password;
            var confirm = form.confirm_password;

            if (!name.value.trim()) { setError(name, 'Name is required.'); ok = false; }
            else clearError(name);

            if (!isEmail(email.value.trim())) { setError(email, 'Enter a valid email address.'); ok = false; }
            else clearError(email);

            if (pw.value.length < 6) { setError(pw, 'Password must be at least 6 characters.'); ok = false; }
            else clearError(pw);

            if (confirm.value !== pw.value) { setError(confirm, 'Passwords do not match.'); ok = false; }
            else clearError(confirm);

            if (!ok) e.preventDefault();
        });
    }

    /* ---- Login form validation ---- */
    function setupLoginValidation() {
        var form = document.getElementById('loginForm');
        if (!form) return;
        form.addEventListener('submit', function (e) {
            var ok = true;
            if (!isEmail(form.email.value.trim())) { setError(form.email, 'Enter a valid email.'); ok = false; }
            else clearError(form.email);
            if (!form.password.value) { setError(form.password, 'Password is required.'); ok = false; }
            else clearError(form.password);
            if (!ok) e.preventDefault();
        });
    }

    /* ---- Add/Edit task form validation ---- */
    function setupTaskFormValidation() {
        document.querySelectorAll('form[data-task-form]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                var title = form.title;
                if (!title.value.trim()) {
                    setError(title, 'Title is required.');
                    e.preventDefault();
                } else if (title.value.trim().length > 200) {
                    setError(title, 'Title must be 200 characters or fewer.');
                    e.preventDefault();
                } else {
                    clearError(title);
                }
            });
        });
    }

    /* ---- Auto-submit the filter form when status/sort changes ---- */
    function autoSubmitFilters() {
        var form = document.getElementById('filterForm');
        if (!form) return;
        form.querySelectorAll('select').forEach(function (sel) {
            sel.addEventListener('change', function () { form.submit(); });
        });
    }
})();
