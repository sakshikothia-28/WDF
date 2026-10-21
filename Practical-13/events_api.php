<?php
header("Content-Type: application/json; charset=UTF-8");

ob_start();
require_once __DIR__ . "/db_mysqli.php";
ob_end_clean();

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $sql = "SELECT event_id, event_name, event_date, venue, poster
            FROM events ORDER BY event_id DESC";

    $result = $conn->query($sql);

    if ($result) {
        $events = [];

        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }

        echo json_encode([
            "success" => true,
            "data" => $events
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to fetch events."
        ]);
    }
    exit;
}


if ($method === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);

    if (
        !is_array($input) ||
        empty(trim($input["event_name"] ?? "")) ||
        empty($input["event_date"]) ||
        empty(trim($input["venue"] ?? ""))
    ) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Event name, date, and venue are required."
        ]);
        exit;
    }

    $name = trim($input["event_name"]);
    $date = $input["event_date"];
    $venue = trim($input["venue"]);

    $stmt = $conn->prepare(
        "INSERT INTO events (event_name, event_date, venue)
         VALUES (?, ?, ?)"
    );
    $stmt->bind_param("sss", $name, $date, $venue);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" => "Event added successfully.",
            "event_id" => $conn->insert_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Failed to add event."
        ]);
    }

    $stmt->close();
    exit;
}

if ($method === "PUT" || $method === "DELETE") {
    $input = json_decode(file_get_contents("php://input"), true);
    $id = filter_var($input["event_id"] ?? null, FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Valid event ID is required."
        ]);
        exit;
    }

    if ($method === "PUT") {
        $name = trim($input["event_name"] ?? "");
        $date = $input["event_date"] ?? "";
        $venue = trim($input["venue"] ?? "");

        if (
            $name === "" || $venue === "" ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
            !checkdate(
                (int)substr($date, 5, 2),
                (int)substr($date, 8, 2),
                (int)substr($date, 0, 4)
            )
        ) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Please enter valid event details."
            ]);
            exit;
        }

        $stmt = $conn->prepare(
            "UPDATE events
             SET event_name = ?, event_date = ?, venue = ?
             WHERE event_id = ?"
        );
        $stmt->bind_param("sssi", $name, $date, $venue, $id);
    } else {
        $stmt = $conn->prepare(
            "DELETE FROM events WHERE event_id = ?"
        );
        $stmt->bind_param("i", $id);
    }

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => $method === "PUT"
                ? "Event updated successfully."
                : "Event deleted successfully."
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Database operation failed."
        ]);
    }

    $stmt->close();
    exit;
}

http_response_code(405);
echo json_encode([
    "success" => false,
    "message" => "Method not allowed."
]);
?>