function showAlert(message, type = "success", icon = "checkmark-outline") {
  const template = document.getElementById("alert-template");
  const clone = template.content.cloneNode(true);

  const alertBox = clone.querySelector(".alert-box");
  const alert = clone.querySelector(".alert");
  const text = clone.querySelector("span");
  const iconEl = clone.querySelector("ion-icon");

  alert.classList.add(type);
  text.textContent = message;
  iconEl.setAttribute("name", icon);

  document.body.appendChild(clone);

  const insertedAlertBox = document.body.lastElementChild;

  setTimeout(() => insertedAlertBox.classList.add("show"), 50);

  setTimeout(() => {
    insertedAlertBox.classList.remove("show");
    setTimeout(() => insertedAlertBox.remove(), 400);
  }, 3000);
}