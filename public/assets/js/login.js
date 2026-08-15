// ===== Preloader =====
window.onload = function () {
    setTimeout(() => {
        document.getElementById("preloader").style.display = "none";
    }, 800);
};

// ===== Show / Hide Password =====
function togglePassword() {
    const passwordField = document.getElementById("passwordInput");
    const icon = event.target;
    if (passwordField.type === "password") {
        passwordField.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        passwordField.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

// ===== Theme Toggle =====
function toggleTheme() {
    document.body.classList.toggle("dark-mode");
}
