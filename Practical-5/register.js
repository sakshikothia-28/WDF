// SHOW / HIDE PASSWORD
const togglePassword = document.getElementById("togglePassword");
const password = document.getElementById("password");

togglePassword.addEventListener("click", function () {

    if (password.type === "password") {

        password.type = "text";
        togglePassword.textContent = "HIDE";

    } else {

        password.type = "password";
        togglePassword.textContent = "SHOW";

    }

});

// REGISTRATION FORM
const form = document.getElementById("registerForm");

form.addEventListener("submit", function (event) {

    event.preventDefault();

    // Get values
    const name = document.getElementById("name").value.trim();
    const studentId = document.getElementById("studentId").value.trim();
    const email = document.getElementById("email").value.trim();
    const contact = document.getElementById("contact").value.trim();
    const username = document.getElementById("username").value.trim();
    const passwordValue = document.getElementById("password").value;

    // CLEAR OLD ERROR MESSAGES
    document.getElementById("nameError").textContent = "";
    document.getElementById("studentIdError").textContent = "";
    document.getElementById("emailError").textContent = "";
    document.getElementById("contactError").textContent = "";
    document.getElementById("usernameError").textContent = "";
    document.getElementById("passwordError").textContent = "";
    document.getElementById("successMessage").textContent = "";

    let valid = true;

    // REGEX
    // Name - only letters and spaces
    const nameRegex = /^[A-Za-z\s]+$/;

    // Student ID - letters and numbers
    const studentIdRegex = /^[A-Za-z0-9]+$/;

    // Email - basic email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Contact - exactly 10 digits
    const contactRegex = /^[0-9]{10}$/;

    // Username - minimum 3 characters
    // Only letters, numbers and underscore
    const usernameRegex = /^[A-Za-z0-9_]{3,}$/;

    // Password -
    // Minimum 8 characters
    // At least 1 lowercase
    // At least 1 uppercase
    // At least 1 number
    // At least 1 special character
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

    // NAME VALIDATION
    if (name === "") {

        document.getElementById("nameError").textContent =
            "Please enter your name.";

        valid = false;

    } else if (!nameRegex.test(name)) {

        document.getElementById("nameError").textContent =
            "Name should contain only letters and spaces.";

        valid = false;
    }

    // STUDENT ID VALIDATION
    if (studentId === "") {

        document.getElementById("studentIdError").textContent =
            "Please enter your Student ID.";

        valid = false;

    } else if (!studentIdRegex.test(studentId)) {

        document.getElementById("studentIdError").textContent =
            "Student ID should contain only letters and numbers.";

        valid = false;
    }

    // EMAIL VALIDATION
    if (email === "") {

        document.getElementById("emailError").textContent =
            "Please enter your email.";

        valid = false;

    } else if (!emailRegex.test(email)) {

        document.getElementById("emailError").textContent =
            "Please enter a valid email address.";

        valid = false;
    }

    // CONTACT VALIDATION
    if (contact === "") {

        document.getElementById("contactError").textContent =
            "Please enter your contact number.";

        valid = false;

    } else if (!contactRegex.test(contact)) {

        document.getElementById("contactError").textContent =
            "Contact number must contain exactly 10 digits.";

        valid = false;
    }

    // USERNAME VALIDATION
    if (username === "") {

        document.getElementById("usernameError").textContent =
            "Username is required.";

        valid = false;

    } else if (!usernameRegex.test(username)) {

        document.getElementById("usernameError").textContent =
            "Username must be at least 3 characters and contain only letters, numbers or underscore.";

        valid = false;
    }

    // PASSWORD VALIDATION
    if (passwordValue === "") {

        document.getElementById("passwordError").textContent =
            "Password is required.";

        valid = false;

    } else if (!passwordRegex.test(passwordValue)) {

        document.getElementById("passwordError").textContent =
            "Password must contain at least 8 characters, uppercase, lowercase, number and special character.";

        valid = false;
    }

    // SUCCESS
    if (valid) {

        document.getElementById("successMessage").textContent =
            "Registered Successfully!";

        sessionStorage.setItem("loggedIn", "true");

        setTimeout(function () {

            window.location.href = "../home.html";

        }, 2000);

    }

});