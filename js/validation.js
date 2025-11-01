document.addEventListener('DOMContentLoaded', function() {
    // Registration form validation
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        const idNumber = document.getElementById('id_number');
        const firstName = document.getElementById('first_name');
        const middleName = document.getElementById('middle_name');
        const lastName = document.getElementById('last_name');
        const birthDate = document.getElementById('birth_date');
        const age = document.getElementById('age');
        const username = document.getElementById('username');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');

        // ID Number validation
        idNumber.addEventListener('input', () => {
            let value = idNumber.value.replace(/\D/g, '');
            if (value.length > 4) {
                value = value.slice(0, 4) + '-' + value.slice(4);
            }
            idNumber.value = value.slice(0, 9);
        });

        // Name validation
        [firstName, middleName, lastName].forEach(nameField => {
            nameField.addEventListener('input', () => {
                validateName(nameField);
            });
        });

        // Age calculation
        birthDate.addEventListener('change', () => {
            const birthDateVal = new Date(birthDate.value);
            const today = new Date();
            let calculatedAge = today.getFullYear() - birthDateVal.getFullYear();
            const m = today.getMonth() - birthDateVal.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDateVal.getDate())) {
                calculatedAge--;
            }
            age.value = calculatedAge;
            if (calculatedAge < 18) {
                showError(birthDate, 'You must be at least 18 years old.');
            } else {
                clearError(birthDate);
            }
        });

        // Username validation
        username.addEventListener('blur', () => {
            const usernameVal = username.value;
            if (usernameVal.length > 0) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'check_username.php', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (this.status === 200) {
                        if (this.responseText === 'unavailable') {
                            showError(username, 'Username is already taken.');
                        } else {
                            clearError(username);
                        }
                    }
                }
                xhr.send('username=' + usernameVal);
            }
        });

        // Password strength
        password.addEventListener('input', () => {
            checkPasswordStrength(password);
            validatePasswordMatch(password, confirmPassword);
        });

        // Confirm password
        confirmPassword.addEventListener('input', () => {
            validatePasswordMatch(password, confirmPassword);
        });

        registerForm.addEventListener('submit', function(e) {
            // Final validation check before submission
            let isValid = true;
            const fieldsToValidate = [idNumber, firstName, lastName, birthDate, username, password, confirmPassword];
            fieldsToValidate.forEach(field => {
                const errorMessage = field.parentElement.querySelector('.error-message');
                if (errorMessage && errorMessage.textContent.trim() !== '') {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fix the errors before submitting.');
            }
        });
    }

    // Login form validation
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        const showPassword = document.getElementById('show-password');
        const passwordField = document.getElementById('password');

        showPassword.addEventListener('change', () => {
            passwordField.type = showPassword.checked ? 'text' : 'password';
        });
    }
});

function validateName(field) {
    let value = field.value;
    const errorContainer = field.parentElement.querySelector('.error-message');
    errorContainer.textContent = '';

    if (/[^a-zA-Z\s]/.test(value)) {
        errorContainer.textContent = 'Name can only contain letters and spaces.';
    } else if (/\d/.test(value)) {
        errorContainer.textContent = 'Name cannot contain numbers.';
    } else if (/\s{2,}/.test(value)) {
        errorContainer.textContent = 'Name cannot contain double spaces.';
    } else if (/([a-zA-Z])\1\1/.test(value)) {
        errorContainer.textContent = 'Name cannot contain three consecutive identical letters.';
    } else if (value.toUpperCase() === value && value.length > 1) {
        errorContainer.textContent = 'Name cannot be all uppercase.';
    } else {
        // Capitalization
        field.value = value.toLowerCase().replace(/\b\w/g, char => char.toUpperCase());
    }
}

function checkPasswordStrength(passwordField) {
    const password = passwordField.value;
    const strengthBar = passwordField.parentElement.querySelector('.password-strength');
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    switch (strength) {
        case 0:
        case 1:
        case 2:
            strengthBar.style.backgroundColor = 'red';
            strengthBar.style.width = '33%';
            break;
        case 3:
        case 4:
            strengthBar.style.backgroundColor = 'orange';
            strengthBar.style.width = '66%';
            break;
        case 5:
            strengthBar.style.backgroundColor = 'green';
            strengthBar.style.width = '100%';
            break;
    }
}

function validatePasswordMatch(passwordField, confirmPasswordField) {
    const errorContainer = confirmPasswordField.parentElement.querySelector('.error-message');
    if (passwordField.value !== confirmPasswordField.value) {
        errorContainer.textContent = 'Passwords do not match.';
    } else {
        errorContainer.textContent = '';
    }
}

function showError(field, message) {
    const errorContainer = field.parentElement.querySelector('.error-message');
    errorContainer.textContent = message;
}

function clearError(field) {
    const errorContainer = field.parentElement.querySelector('.error-message');
    errorContainer.textContent = '';
}
