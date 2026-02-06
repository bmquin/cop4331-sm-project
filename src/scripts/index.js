const authModal = document.querySelector(".auth-modal");
const loginLink = document.querySelector(".login-link");
const registerLink = document.querySelector(".register-link");

const alertBox = document.querySelector(".alert-box");

const loginForm = document.getElementById("login-form");
const signupForm = document.getElementById("signup-form");

registerLink.addEventListener("click", () => {
  authModal.classList.add("active");
});

loginLink.addEventListener("click", () => {
  authModal.classList.remove("active");
});

setTimeout(() => alertBox.classList.add("show"), 50);

setTimeout(() => {
  alertBox.classList.remove('show');
  setTimeout(() => alertBox.remove(), 500)
}, 3000);

/* Signup functionality */
signupForm.addEventListener('submit', async (event) => {
  event.preventDefault();

  const formData = new FormData(event.target);
  const url = "/api/user/signup.php";

  try {
    const request = await fetch(url, {
      method: "POST",
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', },
      body: new URLSearchParams(formData)
    });

    if (request.ok) {
      console.log("Successfully created new account");
    }
  } catch (e) {
    console.error("Error creating an acconut:", e);
  }
})

/* Login functionality */
loginForm.addEventListener('submit', async (event) => {
  event.preventDefault();

  const formData = new FormData(event.target);
  const url = "/api/user/login.php";

  try {
    const request = await fetch(url, {
      method: "POST",
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', },
      body: new URLSearchParams(formData)
    });

    if (request.ok) {
      console.log("Successfully logged in");
      window.location.href = '/contacts.html';
    }
  } catch (e) {
    console.error("Error logging in:", e);
  }
})
