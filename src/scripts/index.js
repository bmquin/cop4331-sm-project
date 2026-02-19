const authModal = document.querySelector(".auth-modal");
const loginLink = document.querySelector(".login-link");
const registerLink = document.querySelector(".register-link");

const loginForm = document.getElementById("login-form");
const signupForm = document.getElementById("signup-form");

(async () => {
  const res = await fetch("../api/user/auth_status.php", {
    credentials: "include",
  });
  if ((await res.json()).logged_in) location.replace("contacts.html"); // removed the / in front of contacts
})();

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
      body: JSON.stringify(Object.fromEntries(formData.entries())),
    });

    const data = await request.json();

    if (data.success) {
      showAlert("Account created", "success", "checkmark-outline");
      setTimeout(function () {
        window.location.href = "contacts.html";
      }, 500);
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
      body: JSON.stringify(Object.fromEntries(formData.entries())),
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
