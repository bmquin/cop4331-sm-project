async function doLogout(event) {
  event.preventDefault(); // Prevent default anchor navigation

  await fetch("api/logout.php", {
    method: "POST",
    credentials: "include", // Include session cookies
  });

  window.location.href = "index.html"; // Redirect after logout
}

// Verify authentication immediately when page loads
(async () => {
  const res = await fetch("../api/user/auth_status.php", {
    credentials: "include",
  });

  // If not logged in, force redirect to login page
  if (!(await res.json()).logged_in) location.replace("index.html");
})();

async function loadContacts() {
  const q = document.getElementById("search").value; // Current search query

  const res = await fetch(
      `/api/user/contacts.php?q=` + encodeURIComponent(q),
      { credentials: "include" } // Maintain session
  );

  // Handle auth errors or server failures
  if (res.status == 401 || !res.ok) {
    showAlert("Server Error", "error", "close-outline");
    return;
  }

  const data = await res.json();

  const container = document.getElementById("contacts");
  container.innerHTML = ""; // Clear previous results

  // If no contacts returned
  if (!data.result || data.result.length === 0) {
    container.innerHTML =
        "<tr><td colspan='4'>No contacts found.</td></tr>";
    return;
  }

  // Dynamically render contact rows
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
          '${escapeStr(c.phone ?? "")}',
          '${escapeStr(c.email ?? "")}'
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

  // Basic required field validation
  if (!first || !last) {
    showAlert("First and last name required", "error", "close-outline");
    return;
  }

  const res = await fetch(`/api/user/contacts.php`, {
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

  // Handle API error response
  if (!res.ok) {
    const data = await res.json();
    showAlert(
        data.error || "Contact addition failed",
        "error",
        "close-outline"
    );

    // If session expired, redirect after delay
    if (data.error == "Not logged in") {
      setTimeout(() => {
        window.location.href = "index.html";
      }, 3000);
    }
    return;
  }

  // Reset form inputs after success
  document.getElementById("first_name").value = "";
  document.getElementById("last_name").value = "";
  document.getElementById("phone").value = "";
  document.getElementById("email").value = "";

  loadContacts(); // Refresh table
  showAlert("Contact added", "success", "checkmark-outline");
}

async function deleteContact(id) {
  if (!confirm("Delete this contact?")) return; // Confirmation prompt

  const res = await fetch(`/api/user/contacts.php`, {
    method: "DELETE",
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ contact_id: id }),
  });

  if (!res.ok) {
    const data = await res.json();
    showAlert(
        data.error || "Contact deletion failed",
        "error",
        "close-outline"
    );
    return;
  }

  loadContacts(); // Refresh list
  showAlert("Contact deleted", "success", "checkmark-outline");
}

// Inline edit mode for a contact row
function editContact(id, first, last, phone, email) {
  const row = event.target.closest("tr"); // Get current table row

  row.innerHTML = `
    <td class="name-edit">
      <input type="text" id="edit_first_${id}" value="${first}" />
      <input type="text" id="edit_last_${id}" value="${last}" />
    </td>
    <td>
      <input type="email" id="edit_email_${id}" value="${email ?? ""}" />
    </td>
    <td>
      <input type="tel" id="edit_phone_${id}" value="${phone ?? ""}" />
    </td>
    <td class="actions">
      <button onclick="saveContact(${id})">Save</button>
      <button class="delete" onclick="loadContacts()">Cancel</button>
    </td>
  `;
}

async function saveContact(id) {
  const first = document.getElementById(`edit_first_${id}`).value;
  const last = document.getElementById(`edit_last_${id}`).value;
  const email = document.getElementById(`edit_email_${id}`).value;
  const phone = document.getElementById(`edit_phone_${id}`).value;

  // Validate required fields before update
  if (!first || !last) {
    showAlert("First and last name required", "error", "close-outline");
    return;
  }

  const res = await fetch(`/api/user/contacts.php`, {
    method: "PUT",
    credentials: "include",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      contact_id: id,
      first_name: first,
      last_name: last,
      phone: phone,
      email: email,
    }),
  });

  if (!res.ok) {
    const data = await res.json();
    showAlert(data.error || "Update failed", "error", "close-outline");
    return;
  }

  // Reload updated list
  loadContacts();
  showAlert("Contact updated", "success", "checkmark-outline");
}

// Prevent quote-breaking in inline JS strings
function escapeStr(str) {
  if (!str) return "";
  return str.replace(/'/g, "\\'");
}

// Load contacts when page initializes
loadContacts();