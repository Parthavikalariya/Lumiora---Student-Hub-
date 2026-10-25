'use strict';
// Confirm only the intended account is toggled; the server still verifies role, CSRF and account status.
document.querySelectorAll('.status-form').forEach((form) => {
    form.addEventListener('submit', (event) => {
        const username = form.dataset.account || 'this student';
        const action = form.dataset.action || 'Change';
        if (!window.confirm(`${action} account "${username}"? This change will be logged.`)) {
            event.preventDefault();
        }
    });
});
