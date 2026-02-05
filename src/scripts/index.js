const authModal = document.querySelector(".auth-modal");
const loginLink = document.querySelector(".login-link");
const registerLink = document.querySelector(".register-link");

const alertBox = document.querySelector(".alert-box");

registerLink.addEventListener("click", () => {
  authModal.classList.add("active");
});

loginLink.addEventListener("click", () => {
  authModal.classList.remove("active");
});

if (loginBtnModal)
  loginBtnModal: addEventListener("click", () => authModal.classList("show"));

setTimeout(() => alertBox.classList.add("show"), 50);

setTimeout(() => {
  alertBox.classList.remove("show");
  setTimeout(() => alertBox.remove(), 500);
}, 3000);
