let password = document.getElementById("password");
let togglePassword = document.getElementById("togglePassword");
togglePassword.addEventListener("click", function () {
    if (password.type === "password") {
        password.type = "text";
        togglePassword.textContent = "Hide";
    }
    else {
        password.type = "password";
        togglePassword.textContent = "Show";
    }
});

// Login validation
let loginForm = document.getElementById("loginForm");
let username = document.getElementById("username");
let message = document.getElementById("loginMessage");
loginForm.addEventListener("submit", function (event) {
    event.preventDefault();
    let user = username.value.trim();
    let pass = password.value.trim();
    if (user === "" || pass === "") {
        message.textContent = "Please enter username and password.";
        message.style.color = "red";
        return;
    }
    if(user < 8 && user > 8) {
        message.textcontent = "Username must contain 8 charaters.";
        message.style.color = "red";
        return;
    }
    if (pass == "") {
        message.textContent = "Please enter password.";
        message.style.color = "red";
        return;
    }
    if (pass.length < 6) {
        message.textContent = "Password must contain at least 6 characters.";
        message.style.color = "red";
        return;
    }
    message.textContent = "Login successful!";
    message.style.color = "green";
    sessionStorage.setItem("loggedIn", "true");
    window.location.href = "../home.html";
});