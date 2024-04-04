// Caller
formValidation()
authAnimation()

// Functions
function formValidation() {
    // Select the forms
    let forms = document.querySelectorAll('.needs-validation');

    // Loop over each form
    for (let form of forms) {
        form.addEventListener('submit', function (event) {
            // Additional validation for password re-entry
            if (form.id === 'form-signUp') {
                let password = form.querySelector('#passwdSu');
                let passwordRe = form.querySelector('#passwdReSu');

                if (password.value !== passwordRe.value) {
                    passwordRe.setCustomValidity('Passwords do not match.');
                } else {
                    passwordRe.setCustomValidity('');
                }
            }

            // If the form is not valid, prevent it from being submitted
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        }, false);
    }
}

function authAnimation() {
    // Select the checkbox
    let checkbox = document.querySelector('#div-cover-chk');

    // Select the h1, label, and form elements
    let h1 = document.querySelector('.div-cover h1');
    let label = document.querySelector('.div-cover label');
    let formSignIn = document.querySelector('.form-signIn');
    let formSignUp = document.querySelector('.form-signUp');

    // Check if the checkbox is not checked when the page loads
    if (!checkbox.checked) {
        // If not checked, disable the sign-up form
        formSignUp.classList.add('form-disabled');
    }

    // Add an event listener to the checkbox
    checkbox.addEventListener('change', function () {
        // Fade out the text and form
        h1.style.opacity = '0';
        label.style.opacity = '0';
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

            // Fade in the text
            h1.style.opacity = '1';
            label.style.opacity = '1';
        }, 500); // The timeout should be the same as the transition duration
    });
}