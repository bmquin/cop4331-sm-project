async function doLogout(event) {
  event.preventDefault();

  await fetch("api/logout.php", {
    method: "POST",
    credentials: "include",
  });

  window.location.href = "index.html";
}

async function loadContacts() {
  const q = document.getElementById("search").value;

  const res = await fetch(`/api/contacts.php?q=` + encodeURIComponent(q), {
    credentials: "include",
  });

  if (res.status == 401 || !res.ok) {
    window.location.href = "index.html";
    return;
  }

  const data = await res.json();

  const container = document.getElementById("contacts");
  container.innerHTML = "";

  if (!data.result || data.result.length === 0) {
    container.innerHTML = "<tr><td colspan='4'>No contacts found.</td></tr>";
    return;
  }

  data.result.forEach((c) => {
    const row = document.createElement("tr");

    row.innerHTML = `
            <td>${c.first_name} ${c.last_name}</td>
            <td>${c.email ?? ""}</td>
            <td>${c.phone ?? ""}</td>
            <td class="actions">
                <button onclick="editContact(
                    ${c.id},
                    '${escapeStr(c.first_name)}',
                    '${escapeStr(c.last_name)}',
                    '${escapeStr(c.phone)}',
                    '${escapeStr(c.email)}'
                )">Edit</button>
                <button class="delete" onclick="deleteContact(${c.id})">
                    Delete
                </button>
            </td>
        `;

    container.appendChild(row);
  });
}

async function addContact() {
  const first = document.getElementById("first_name").value;
  const last = document.getElementById("last_name").value;
  const phone = document.getElementById("phone").value;
  const email = document.getElementById("email").value;

  if (!first || !last) {
    alert("First and last name required");
    return;
  }

  const res = await fetch(`/api/contacts.php`, {
    method: "POST",
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      first_name: first,
      last_name: last,
      phone: phone,
      email: email,
    }),
  });

  if (!res.ok) {
    console.error("Failed to add contact:", res.status);
    const data = await res.json();
    showAlert(
      data.error || "Contact addition failed",
      "error",
      "close-outline",
    );
    if (data.error == "Not logged in") {
      setTimeout(() => {
        window.location.href = "index.html";
      }, 3000);
    }
    return;
  }

  document.getElementById("first_name").value = "";
  document.getElementById("last_name").value = "";
  document.getElementById("phone").value = "";
  document.getElementById("email").value = "";

  loadContacts();
  showAlert("Contact added", "success", "checkmark-outline");
}

async function deleteContact(id) {
  if (!confirm("Delete this contact?")) return;

  const res = await fetch(`/api/contacts.php`, {
    method: "DELETE",
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ contact_id: id }),
  });

  if (!res.ok) {
    console.error("Failed to delete contact:", res.status);
    const data = await res.json();
    showAlert(
      data.error || "Contact deletion failed",
      "error",
      "close-outline",
    );
    return;
  }

  loadContacts();
  showAlert("Contact deleted", "success", "checkmark-outline");
}

async function editContact(id, first, last, phone, email) {
  const newFirst = prompt("First name:", first);
  const newLast = prompt("Last name:", last);
  const newPhone = prompt("Phone:", phone);
  const newEmail = prompt("Email:", email);

  if (!newFirst || !newLast) return;

  const res = await fetch(`/api/contacts.php`, {
    method: "PUT",
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      contact_id: id,
      first_name: newFirst,
      last_name: newLast,
      phone: newPhone,
      email: newEmail,
    }),
  });

  if (!res.ok) {
    console.error("Failed to update contact:", res.status);
    const data = await res.json();
    showAlert(data.error || "Contact edit failed", "error", "close-outline");
    return;
  }

  loadContacts();
  showAlert("Contact edited", "success", "checkmark-outline");
}

// Prevent quote-breaking in inline JS
function escapeStr(str) {
  if (!str) return "";
  return str.replace(/'/g, "\\'");
}

// Load contacts on page load (after login)
loadContacts();
