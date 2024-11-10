document.getElementById('registrationForm').addEventListener('submit', function(event) {
    const form = event.target;
    const password = form.password.value;
    const retypePassword = form.retypePassword.value;

    if (password !== retypePassword) {
        alert("Passwords do not match!");
        event.preventDefault();  // Prevent submission only if there's an error
        return;
    }

    console.log("Form is ready to be submitted");  // Add this to check if it's reached this point
    // If everything is correct, the form will submit naturally
});
