(async () => {
  const res = await fetch("api/session.php", {
    credentials: "include",
  });

  const data = await res.json();

  if (data.logged_in) {
    window.location.href = "contacts.html";
  }
})();

const authModal = document.querySelector(".auth-modal");
const loginLink = document.querySelector(".login-link");
const registerLink = document.querySelector(".register-link");

const loginForm = document.getElementById("login-form");
const signupForm = document.getElementById("signup-form");

registerLink.addEventListener("click", () => {
  authModal.classList.add("active");
});

loginLink.addEventListener("click", () => {
  authModal.classList.remove("active");
});

/* Signup functionality */
signupForm.addEventListener("submit", async (event) => {
  event.preventDefault();

  const formData = new FormData(event.currentTarget);
  const url = "api/user/signup.php";

  try {
    const request = await fetch(url, {
      method: "POST",
      credentials: "include",
      body: new URLSearchParams(formData),
    });

    const data = await request.json();

    if (data.success) {
      showAlert("Account created", "success", "checkmark-outline");
      authModal.classList.remove("active");
    } else {
      showAlert(data.error || "Signup failed", "error", "close-outline");
    }
  } catch {
    showAlert("Server error", "error", "close-outline");
  }
});

/* Login functionality */
loginForm.addEventListener("submit", async (event) => {
  event.preventDefault();

  const formData = new FormData(event.currentTarget);
  const url = "api/user/login.php";

  try {
    const request = await fetch(url, {
      method: "POST",
      credentials: "include",
      body: new URLSearchParams(formData),
    });

    const data = await request.json();

    if (data.success) {
      showAlert("Authenticated", "success", "checkmark-outline");
      setTimeout(function () {
        window.location.href = "contacts.html";
      }, 500);
    } else {
      showAlert(
        data.message || data.error || "Incorrect credentials",
        "error",
        "close-outline",
      );
    }
  } catch {
    showAlert("Server error", "error", "close-outline");
  }
});
