<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - AJAX Events</title>
</head>
<body>
    <h2>Event Management (Fetch API)</h2>

    <form id="eventForm">
        <label>Event Name:</label>
        <input type="text" id="event_name" required>
        <br><br>

        <label>Event Date:</label>
        <input type="date" id="event_date" required>
        <br><br>

        <label>Venue:</label>
        <input type="text" id="venue" required>
        <br><br>

        <button type="submit">Add Event</button>
    </form>

    <p id="message" role="status"></p>
    <h3>Events List</h3>
    <p id="loading" hidden>Loading events...</p>
    <ul id="eventsList"></ul>

    <script>
        const apiUrl = "events_api.php";
        const form = document.getElementById("eventForm");
        const message = document.getElementById("message");
        const eventsList = document.getElementById("eventsList");
        const loading = document.getElementById("loading");

        async function loadEvents() {
            loading.hidden = false;

            try {
                const response = await fetch(apiUrl);
                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || "Unable to load events.");
                }

                eventsList.replaceChildren();

                result.data.forEach(event => {
                    
const item = document.createElement("li");

const details = document.createElement("span");
details.textContent =
    `${event.event_name} | ${event.event_date} | ${event.venue} `;

const editButton = document.createElement("button");
editButton.textContent = "Edit";
editButton.type = "button";

editButton.addEventListener("click", async () => {
    const name = prompt("Enter event name:", event.event_name);
    if (name === null) return;

    const date = prompt("Enter event date (YYYY-MM-DD):", event.event_date);
    if (date === null) return;

    const venue = prompt("Enter venue:", event.venue);
    if (venue === null) return;

    try {
        const response = await fetch(apiUrl, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                event_id: event.event_id,
                event_name: name.trim(),
                event_date: date.trim(),
                venue: venue.trim()
            })
        });

        const result = await response.json();
        message.textContent = result.message;

        if (!response.ok || !result.success) {
            throw new Error(result.message || "Update failed.");
        }

        await loadEvents();
    } catch (error) {
        message.textContent = error.message;
    }
});

const deleteButton = document.createElement("button");
deleteButton.textContent = "Delete";
deleteButton.type = "button";

deleteButton.addEventListener("click", async () => {
    if (!confirm(`Delete "${event.event_name}"?`)) return;

    try {
        const response = await fetch(apiUrl, {
            method: "DELETE",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ event_id: event.event_id })
        });

        const result = await response.json();
        message.textContent = result.message;

        if (!response.ok || !result.success) {
            throw new Error(result.message || "Delete failed.");
        }

        await loadEvents();
    } catch (error) {
        message.textContent = error.message;
    }
});

item.append(details, editButton, document.createTextNode(" "), deleteButton);
eventsList.appendChild(item);
                });
            } catch (error) {
                message.textContent = error.message;
            } finally {
                loading.hidden = true;
            }
        }

        form.addEventListener("submit", async function (event) {
            event.preventDefault();
            message.textContent = "Adding event...";

            const data = {
                event_name: document.getElementById("event_name").value.trim(),
                event_date: document.getElementById("event_date").value,
                venue: document.getElementById("venue").value.trim()
            };

            if (!data.event_name || !data.event_date || !data.venue) {
                message.textContent = "Please fill in all fields.";
                return;
            }

            try {
                const response = await fetch(apiUrl, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || "Failed to add event.");
                }

                message.textContent = result.message;
                form.reset();
                await loadEvents();
            } catch (error) {
                message.textContent = error.message;
            }
        });

        loadEvents();
    </script>
</body>
</html>