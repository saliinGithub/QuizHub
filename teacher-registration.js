document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registrationForm');

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        // Clear previous errors
        clearErrors();

        // Get form values
        const username = form.username.value.trim();
        const email = form.email.value.trim();
        const firstName = form.firstName.value.trim();
        const lastName = form.lastName.value.trim();
        const contactNumber = form.contactNumber.value.trim();
        const qualifications = form.qualifications.value.trim();
        const subject = form.subject.value.trim();
        const password = form.password.value;
        const retypePassword = form.retypePassword.value;

        // Validate required fields
        if (!username || !email || !firstName || !lastName || !contactNumber || !qualifications || !subject || !password || !retypePassword) {
            showError('All fields are required.');
            return;
        }

        // Validate password
        if (password !== retypePassword) {
            showError('Passwords do not match.');
            return;
        }

        // Submit form if validation passes
        form.submit();
    });

    function showError(message) {
        alert(message);
    }

    function clearErrors() {
        // Clear previous errors if any
        const errorElements = document.querySelectorAll('.error');
        errorElements.forEach(element => element.remove());
    }
});
