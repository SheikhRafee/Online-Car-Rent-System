/**
 * Client-side validation for the auth / profile forms.
 *
 * This only exists to give the user instant feedback — every one of these
 * rules is enforced again on the server in the matching controller, since
 * client-side checks can always be bypassed.
 */

const EMAIL_PATTERN = /^\S+@\S+\.\S+$/;
const PHONE_PATTERN  = /^[0-9+\-\s]{7,20}$/;

function setFieldError(fieldId, message) {
    const el = document.getElementById(fieldId);
    if (el) {
        el.textContent = message;
    }
    return message === "";
}

function clearErrors(ids) {
    ids.forEach((id) => setFieldError(id, ""));
}

// ---------------------------------------------------------------
// Registration form
// ---------------------------------------------------------------
function validateRegistrationForm() {
    const errorIds = [
        "nameError", "emailError", "passwordError",
        "confirmError", "addressError", "phoneError", "roleError",
    ];
    clearErrors(errorIds);

    const name     = document.getElementById("name").value.trim();
    const email    = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;
    const confirm  = document.getElementById("confirm").value;
    const address  = document.getElementById("address").value.trim();
    const phone    = document.getElementById("phone").value.trim();
    const role     = document.getElementById("role").value;

    let ok = true;

    if (name === "") {
        setFieldError("nameError", "Name must not be empty");
        ok = false;
    } else if (name.length > 100) {
        setFieldError("nameError", "Name must be 100 characters or fewer");
        ok = false;
    }

    if (email === "") {
        setFieldError("emailError", "Email must not be empty");
        ok = false;
    } else if (!EMAIL_PATTERN.test(email)) {
        setFieldError("emailError", "Enter a valid email address");
        ok = false;
    }

    if (password === "") {
        setFieldError("passwordError", "Password must not be empty");
        ok = false;
    } else if (password.length < 8) {
        setFieldError("passwordError", "Password must be at least 8 characters");
        ok = false;
    }

    if (confirm === "") {
        setFieldError("confirmError", "Please confirm your password");
        ok = false;
    } else if (password !== confirm) {
        setFieldError("confirmError", "Passwords do not match");
        ok = false;
    }

    if (address === "") {
        setFieldError("addressError", "Address must not be empty");
        ok = false;
    }

    if (phone === "") {
        setFieldError("phoneError", "Phone must not be empty");
        ok = false;
    } else if (!PHONE_PATTERN.test(phone)) {
        setFieldError("phoneError", "Enter a valid phone number");
        ok = false;
    }

    if (role === "") {
        setFieldError("roleError", "Please select a role");
        ok = false;
    }

    return ok;
}

// ---------------------------------------------------------------
// Login form
// ---------------------------------------------------------------
function validateLoginForm() {
    clearErrors(["emailError", "passwordError"]);

    const email    = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;

    let ok = true;

    if (email === "") {
        setFieldError("emailError", "Email must not be empty");
        ok = false;
    }

    if (password === "") {
        setFieldError("passwordError", "Password must not be empty");
        ok = false;
    }

    return ok;
}

// ---------------------------------------------------------------
// Profile details form
// ---------------------------------------------------------------
function validateProfileForm() {
    clearErrors(["nameError", "emailError", "addressError", "phoneError", "pictureError"]);

    const name    = document.getElementById("name").value.trim();
    const email   = document.getElementById("email").value.trim();
    const address = document.getElementById("address").value.trim();
    const phone   = document.getElementById("phone").value.trim();
    const picture = document.getElementById("profile_picture");

    let ok = true;

    if (name === "") {
        setFieldError("nameError", "Name must not be empty");
        ok = false;
    } else if (name.length > 100) {
        setFieldError("nameError", "Name must be 100 characters or fewer");
        ok = false;
    }

    if (email === "") {
        setFieldError("emailError", "Email must not be empty");
        ok = false;
    } else if (!EMAIL_PATTERN.test(email)) {
        setFieldError("emailError", "Enter a valid email address");
        ok = false;
    }

    if (address === "") {
        setFieldError("addressError", "Address must not be empty");
        ok = false;
    }

    if (phone === "") {
        setFieldError("phoneError", "Phone must not be empty");
        ok = false;
    } else if (!PHONE_PATTERN.test(phone)) {
        setFieldError("phoneError", "Enter a valid phone number");
        ok = false;
    }

    if (picture && picture.files && picture.files.length > 0) {
        const file = picture.files[0];
        const allowedTypes = ["image/jpeg", "image/png"];

        if (!allowedTypes.includes(file.type)) {
            setFieldError("pictureError", "Only JPEG or PNG images are allowed");
            ok = false;
        } else if (file.size > 2 * 1024 * 1024) {
            setFieldError("pictureError", "Image must be 2MB or smaller");
            ok = false;
        }
    }

    return ok;
}

// ---------------------------------------------------------------
// Change password form
// ---------------------------------------------------------------
function validatePasswordForm() {
    clearErrors(["currentPasswordError", "newPasswordError", "confirmPasswordError"]);

    const current = document.getElementById("current_password").value;
    const newPass = document.getElementById("new_password").value;
    const confirm = document.getElementById("confirm_password").value;

    let ok = true;

    if (current === "") {
        setFieldError("currentPasswordError", "Enter your current password");
        ok = false;
    }

    if (newPass === "") {
        setFieldError("newPasswordError", "Enter a new password");
        ok = false;
    } else if (newPass.length < 8) {
        setFieldError("newPasswordError", "Password must be at least 8 characters");
        ok = false;
    }

    if (confirm === "") {
        setFieldError("confirmPasswordError", "Please confirm your new password");
        ok = false;
    } else if (newPass !== confirm) {
        setFieldError("confirmPasswordError", "Passwords do not match");
        ok = false;
    }

    return ok;
}
