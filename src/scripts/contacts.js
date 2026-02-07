async function loadContacts() {
    const q = document.getElementById("search").value;

    const res = await fetch(`/api/contacts.php?q=` + encodeURIComponent(q));
    const data = await res.json();

    const container = document.getElementById("contacts");
    container.innerHTML = "";

    if (!data.result || data.result.length === 0) {
        container.innerHTML = "<p>No contacts found.</p>";
        return;
    }

    data.result.forEach(c => {
        const div = document.createElement("div");
        div.className = "contact";

        div.innerHTML = `
      <div class="name">${c.first_name} ${c.last_name}</div>
      <div>${c.phone ?? ""}</div>
      <div>${c.email ?? ""}</div>
      <div class="actions">
        <button onclick="editContact(${c.id}, '${escapeStr(c.first_name)}', '${escapeStr(c.last_name)}', '${escapeStr(c.phone)}', '${escapeStr(c.email)}')">
          Edit
        </button>
        <button onclick="deleteContact(${c.id})">
          Delete
        </button>
      </div>
    `;

        container.appendChild(div);
    });
}

async function addContact() {
    const first = document.getElementById("first_name").value;
    const last  = document.getElementById("last_name").value;
    const phone = document.getElementById("phone").value;
    const email = document.getElementById("email").value;

    if (!first || !last) {
        alert("First and last name required");
        return;
    }

    await fetch(`/api/contacts.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            first_name: first,
            last_name: last,
            phone: phone,
            email: email
        })
    });

    // Clear inputs
    document.getElementById("first_name").value = "";
    document.getElementById("last_name").value = "";
    document.getElementById("phone").value = "";
    document.getElementById("email").value = "";

    loadContacts();
}

async function deleteContact(id) {
    if (!confirm("Delete this contact?")) return;

    await fetch(`/api/contacts.php`, {
        method: "DELETE",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ contact_id: id })
    });

    loadContacts();
}

async function editContact(id, first, last, phone, email) {
    const newFirst = prompt("First name:", first);
    const newLast  = prompt("Last name:", last);
    const newPhone = prompt("Phone:", phone);
    const newEmail = prompt("Email:", email);

    if (!newFirst || !newLast) return;

    await fetch(`/api/contacts.php`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            contact_id: id,
            first_name: newFirst,
            last_name: newLast,
            phone: newPhone,
            email: newEmail
        })
    });

    loadContacts();
}

// Prevent quote-breaking in inline JS
function escapeStr(str) {
    if (!str) return "";
    return str.replace(/'/g, "\\'");
}

// Load on page open
loadContacts();
