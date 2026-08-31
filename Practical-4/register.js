const togglePassword = document.getElementById("togglePassword");
const password = document.getElementById("password");

togglePassword.addEventListener("click", function ()
{
    if (password.type === "password")
    {
        password.type = "text";
        togglePassword.textContent = "HIDE";
    }
    else
    {
        password.type = "password";
        togglePassword.textContent = "SHOW";
    }
});

const form = document.getElementById("registerForm");

form.addEventListener("submit", function (event)
{
    event.preventDefault();

    document.getElementById("nameError").textContent = "";
    document.getElementById("studentIdError").textContent = "";
    document.getElementById("emailError").textContent = "";
    document.getElementById("contactError").textContent = "";
    document.getElementById("usernameError").textContent = "";
    document.getElementById("passwordError").textContent = "";
    document.getElementById("successMessage").textContent = "";

    let valid = true;

    if (document.getElementById("name").value === "")
    {
        document.getElementById("nameError").textContent =
        "Please enter your name.";
        valid = false;
    }
    if (document.getElementById("studentId").value === "")
    {
        document.getElementById("studentIdError").textContent =
        "Please enter your Student ID.";
        valid = false;
    }
    if (document.getElementById("email").value === "")
    {
        document.getElementById("emailError").textContent =
        "Please enter your email.";
        valid = false;
    }
    if (document.getElementById("contact").value === "")
    {
        document.getElementById("contactError").textContent =
        "Please enter your contact number.";
        valid = false;
    }
    if (document.getElementById("username").value === "")
    {
        document.getElementById("usernameError").textContent =
        "Please enter a username.";
        valid = false;
    }
    if (document.getElementById("password").value === "")
    {
        document.getElementById("passwordError").textContent =
        "Please enter a password.";
        valid = false;
    }
    if (valid)
    {
    document.getElementById("successMessage").textContent =
    "Registered Successfully!";
    setTimeout(function ()
    {
        sessionStorage.setItem("loggedIn", "true");
        window.location.href = "../home.html";
    }, 2000);
    }
});