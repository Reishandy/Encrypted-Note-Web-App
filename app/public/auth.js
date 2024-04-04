// Caller
formUtils()
authAnimation()
closeAlertListener()

// Functions
function formUtils() {
    let forms = document.querySelectorAll('.needs-validation');

    // Loop over each form (sign-in and sign-up)
    for (let form of forms) {
        form.addEventListener('submit', function (event) {
            // Prevent the form from submitting
            event.preventDefault();
            event.stopPropagation();

            // Validation for password re-entry
            if (form.id === 'form-re') {
                validatePasswordReEntry(form);
            }

            // Check if the form is valid, then send the form data
            if (form.checkValidity()) {
                sendFormData(form)
            }

            form.classList.add('was-validated');
        }, false);
    }
}

function validatePasswordReEntry(form) {
    let password = form.querySelector('#passwdSu');
    let passwordRe = form.querySelector('#passwdReSu');

    if (password.value !== passwordRe.value) {
        passwordRe.setCustomValidity('Passwords do not match.');
    } else {
        passwordRe.setCustomValidity('');
    }
}

function sendFormData(form) {
    let formData = new FormData(form);

    // Spinner setup
    let button = form.querySelector('button');
    let spinner = button.querySelector('.spinner-border');
    spinner.style.display = 'inline-block';
    button.disabled = true;

    fetch('../logic/handler.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            // Spinner cleanup
            spinner.style.display = 'none';
            button.disabled = false;

            // Check if the request was successful, else show an error message
            if (data.status === 'sign_in_success') {
                console.log(data)
                alert(data.message)
                // TODO: Set up session stuff
                // TODO: Redirect to the notes page
            } else if (data.status === 'sign_up_success') {
                // Display the success message
                displaySuccess(data.message);

                // Reset the form
                form.reset();

                // Reset the form validation
                form.classList.remove('was-validated');
            } else {
                displayError(data.message)
            }
        })
        .catch((error) => {
            // Spinner cleanup
            spinner.style.display = 'none';
            button.disabled = false;

            displayError('An error occurred. Please try again later. \n Error: ' + error.message);
        });
}

function displaySuccess(message) {
    // Display the success message
    let successMessageBox = document.getElementById('success-message');
    let successContent = document.getElementById('success-content');
    successContent.textContent = message;
    successMessageBox.style.display = 'block';
}

function displayError(message) {
    // Display the error message
    let errorMessageBox = document.getElementById('error-message');
    let errorContent = document.getElementById('error-content');
    errorContent.textContent = message;
    errorMessageBox.style.display = 'block';
}

function authAnimation() {
    let checkbox = document.querySelector('#div-cover-chk');

    let label = document.querySelector('.div-cover label');
    let formSignIn = document.querySelector('.form-signIn');
    let formSignUp = document.querySelector('.form-signUp');

    // Check if the checkbox is not checked when the page loads
    if (!checkbox.checked) {
        formSignUp.classList.add('form-disabled');
    }

    // Add an event listener to the checkbox
    checkbox.addEventListener('change', function () {
        // Fade out the form
        formSignIn.style.opacity = '0';
        formSignUp.style.opacity = '0';

        // Reset form validation
        formSignIn.classList.remove('was-validated');
        formSignUp.classList.remove('was-validated');

        // Wait for the fade out animation to finish
        setTimeout(function () {
            // Check if the checkbox is checked
            if (checkbox.checked) {
                // If checked, change the text of the label elements
                label.textContent = 'Sign-in';

                // Show the sign-up form and hide the sign-in form
                formSignUp.style.opacity = '1';
                formSignUp.classList.remove('form-disabled');
                formSignIn.style.opacity = '0';
                formSignIn.classList.add('form-disabled');
            } else {
                // If not checked, reset the text of the label elements
                label.textContent = 'Sign-up';

                // Show the sign-in form and hide the sign-up form
                formSignIn.style.opacity = '1';
                formSignIn.classList.remove('form-disabled');
                formSignUp.style.opacity = '0';
                formSignUp.classList.add('form-disabled');
            }
        }, 500); // The timeout should be the same as the transition duration
    });
}

function closeAlertListener() {
    // Add an event listener to the close button, hide the error message when clicked instead of removing it
    let errorButton = document.getElementById('error-close');
    errorButton.addEventListener('click', function () {
        let errorMessageBox = document.getElementById('error-message');
        errorMessageBox.style.display = 'none';
    });

    // Add an event listener to the close button, hide the success message when clicked instead of removing it
    let successButton = document.getElementById('success-close');
    successButton.addEventListener('click', function () {
        let successMessageBox = document.getElementById('success-message');
        successMessageBox.style.display = 'none';
    });
}