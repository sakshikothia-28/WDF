<?php
session_start();
require_once __DIR__ . "/db_mysqli.php";

$message = "";
$editEvent = null;

$uploadDir = __DIR__ . "/uploads/";
$uploadUrl = "uploads/";
$maxSize = 2 * 1024 * 1024; // 2 MB
$allowedTypes = [
    "image/jpeg" => "jpg",
    "image/png"  => "png",
    "image/webp" => "webp"
];

// Check admin access
if (empty($_SESSION["student_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Access denied. Please log in as an admin first.");
}

function clean($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Add or update an event
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int)($_POST["event_id"] ?? 0);
    $name = trim($_POST["event_name"] ?? "");
    $date = $_POST["event_date"] ?? "";
    $venue = trim($_POST["venue"] ?? "");
    $posterName = trim($_POST["existing_poster"] ?? "");

    if ($name === "" || $date === "" || $venue === "") {
        $message = "Please fill in all event fields.";
    } else {
        $uploadError = false;

        if (isset($_FILES["poster"]) &&
            $_FILES["poster"]["error"] !== UPLOAD_ERR_NO_FILE) {

            if ($_FILES["poster"]["error"] !== UPLOAD_ERR_OK) {
                $message = "Poster upload failed.";
                $uploadError = true;
            } elseif ($_FILES["poster"]["size"] > $maxSize) {
                $message = "Poster must be 2 MB or smaller.";
                $uploadError = true;
            } else {
                $tmp = $_FILES["poster"]["tmp_name"];
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);

                if (!isset($allowedTypes[$mime])) {
                    $message = "Only JPG, PNG, and WEBP posters are allowed.";
                    $uploadError = true;
                } elseif (!is_uploaded_file($tmp)) {
                    $message = "Invalid uploaded file.";
                    $uploadError = true;
                } else {
                    // Generate a safe, unique filename.
                    $newName = bin2hex(random_bytes(16)) . "." . $allowedTypes[$mime];

                    if (!move_uploaded_file($tmp, $uploadDir . $newName)) {
                        $message = "Could not save poster. Check the uploads folder.";
                        $uploadError = true;
                    } else {
                        $posterName = $newName;
                    }
                }
            }
        }

        if (!$uploadError) {
            if ($id > 0) {
                $stmt = $conn->prepare(
                    "UPDATE events
                     SET event_name=?, event_date=?, venue=?, poster=?
                     WHERE event_id=?"
                );
                $stmt->bind_param("ssssi", $name, $date, $venue, $posterName, $id);
                $message = $stmt->execute()
                    ? "Event updated successfully."
                    : "Could not update event.";
                $stmt->close();
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO events (event_name, event_date, venue, poster)
                     VALUES (?, ?, ?, ?)"
                );
                $stmt->bind_param("ssss", $name, $date, $venue, $posterName);
                $message = $stmt->execute()
                    ? "Event added successfully."
                    : "Could not add event.";
                $stmt->close();
            }
        }
    }
}

// Delete an event
if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];

    $stmt = $conn->prepare("SELECT poster FROM events WHERE event_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM events WHERE event_id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($row && !empty($row["poster"])) {
            $path = $uploadDir . basename($row["poster"]);
            if (is_file($path)) {
                unlink($path);
            }
        }
        $message = "Event deleted successfully.";
    } else {
        $message = "Could not delete event.";
    }
    $stmt->close();
}

// Load event for editing
if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];
    $stmt = $conn->prepare("SELECT * FROM events WHERE event_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editEvent = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// List events
$result = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Management</title>
</head>
<body>
<h2>Event Management</h2>

<p><?= clean($message) ?></p>

<h3><?= $editEvent ? "Edit Event" : "Add Event" ?></h3>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="event_id"
           value="<?= (int)($editEvent["event_id"] ?? 0) ?>">
    <input type="hidden" name="existing_poster"
           value="<?= clean($editEvent["poster"] ?? "") ?>">

    Event Name:
    <input type="text" name="event_name" required
           value="<?= clean($editEvent["event_name"] ?? "") ?>"><br><br>

    Event Date:
    <input type="date" name="event_date" required
           value="<?= clean($editEvent["event_date"] ?? "") ?>"><br><br>

    Venue:
    <input type="text" name="venue" required
           value="<?= clean($editEvent["venue"] ?? "") ?>"><br><br>

    Poster (JPG, PNG, WEBP; max 2 MB):
    <input type="file" name="poster" accept=".jpg,.jpeg,.png,.webp"><br><br>

    <?php if (!empty($editEvent["poster"])): ?>
        <p>Current poster:</p>
        <img src="<?= $uploadUrl . rawurlencode(basename($editEvent["poster"])) ?>"
             alt="Event poster" width="150">
        <br><br>
    <?php endif; ?>

    <button type="submit">
        <?= $editEvent ? "Update Event" : "Add Event" ?>
    </button>
    <?php if ($editEvent): ?>
        <a href="events.php">Cancel</a>
    <?php endif; ?>
</form>

<hr>
<h3>Event Records</h3>

<table border="1" cellpadding="8">
<tr>
    <th>ID</th>
    <th>Event Name</th>
    <th>Date</th>
    <th>Venue</th>
    <th>Poster</th>
    <th>Actions</th>
</tr>

<?php while ($event = $result->fetch_assoc()): ?>
<tr>
    <td><?= (int)$event["event_id"] ?></td>
    <td><?= clean($event["event_name"]) ?></td>
    <td><?= clean($event["event_date"]) ?></td>
    <td><?= clean($event["venue"]) ?></td>
    <td>
        <?php if (!empty($event["poster"])): ?>
            <img src="<?= $uploadUrl . rawurlencode(basename($event["poster"])) ?>"
                 alt="Poster" width="90">
        <?php else: ?>
            No poster
        <?php endif; ?>
    </td>
    <td>
        <a href="?edit=<?= (int)$event["event_id"] ?>">Edit</a> |
        <a href="?delete=<?= (int)$event["event_id"] ?>"
           onclick="return confirm('Delete this event?')">Delete</a>
    </td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>

<?php
$result->free();
$conn->close();
?>