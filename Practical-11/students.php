<?php
ob_start();
require_once __DIR__ . "/db_mysqli.php";
ob_clean();

$message = "";
$editStudent = null;

// Delete student after confirmation in the browser
if (isset($_GET["delete"])) {
    $id = (int) $_GET["delete"];

    $stmt = $conn->prepare(
        "DELETE FROM students WHERE student_id = ?"
    );
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "Student deleted successfully.";
    } else {
        $message = "Failed to delete student.";
    }
    $stmt->close();
}

// Add or update student
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $enrollment = trim($_POST["enrollment_no"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $semester = (int) ($_POST["semester"] ?? 1);
    $phone = trim($_POST["phone"] ?? "");

    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Enter a name and valid email.";
    } elseif (isset($_POST["student_id"]) && $_POST["student_id"] !== "") {
        $id = (int) $_POST["student_id"];

        $stmt = $conn->prepare(
            "UPDATE students
             SET name=?, email=?, enrollment_no=?, course=?, semester=?, phone=?
             WHERE student_id=?"
        );
        $stmt->bind_param(
            "ssssisi",
            $name, $email, $enrollment, $course, $semester, $phone, $id
        );

        $message = $stmt->execute()
            ? "Student updated successfully."
            : "Update failed. Check for duplicate email or enrollment number.";
        $stmt->close();
    } else {
        // Create a random password hash for CRUD-only records.
        // These records can be assigned a real password through a secure
        // password-reset process before they are used for login.
        $placeholderHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO students
             (name, email, password_hash, enrollment_no, course, semester, phone)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "sssssis",
            $name, $email, $placeholderHash, $enrollment,
            $course, $semester, $phone
        );

        $message = $stmt->execute()
            ? "Student added successfully."
            : "Insert failed. Check for duplicate email or enrollment number.";
        $stmt->close();
    }
}

// Load record for editing
if (isset($_GET["edit"])) {
    $id = (int) $_GET["edit"];
    $stmt = $conn->prepare(
        "SELECT student_id, name, email, enrollment_no, course, semester, phone
         FROM students WHERE student_id=?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $editStudent = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Search and filter
$search = trim($_GET["search"] ?? "");
$courseFilter = trim($_GET["course"] ?? "");

$sql = "SELECT student_id, name, email, enrollment_no, course, semester, phone
        FROM students WHERE (name LIKE ? OR email LIKE ? OR course LIKE ?)";
$searchTerm = "%" . $search . "%";

if ($courseFilter !== "") {
    $sql .= " AND course = ?";
}
$sql .= " ORDER BY student_id DESC";

$stmt = $conn->prepare($sql);

if ($courseFilter !== "") {
    $stmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $courseFilter);
} else {
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
}

$stmt->execute();
$students = $stmt->get_result();

$coursesResult = $conn->query(
    "SELECT DISTINCT course FROM students
     WHERE course IS NOT NULL AND course <> '' ORDER BY course"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Management</title>
</head>
<body>
<h2>Student Management CRUD</h2>

<p><?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?></p>

<h3><?= $editStudent ? "Edit Student" : "Add Student" ?></h3>

<form method="POST">
    <input type="hidden" name="student_id"
           value="<?= htmlspecialchars((string)($editStudent["student_id"] ?? "")) ?>">

    Name:
    <input name="name" required
           value="<?= htmlspecialchars($editStudent["name"] ?? "") ?>"><br><br>

    Email:
    <input type="email" name="email" required
           value="<?= htmlspecialchars($editStudent["email"] ?? "") ?>"><br><br>

    Enrollment No:
    <input name="enrollment_no"
           value="<?= htmlspecialchars($editStudent["enrollment_no"] ?? "") ?>"><br><br>

    Course:
    <input name="course"
           value="<?= htmlspecialchars($editStudent["course"] ?? "") ?>"><br><br>

    Semester:
    <input type="number" name="semester" min="1" max="12"
           value="<?= htmlspecialchars((string)($editStudent["semester"] ?? "1")) ?>"><br><br>

    Phone:
    <input name="phone"
           value="<?= htmlspecialchars($editStudent["phone"] ?? "") ?>"><br><br>

    <button type="submit"><?= $editStudent ? "Update Student" : "Add Student" ?></button>
    <?php if ($editStudent): ?>
        <a href="students.php">Cancel Edit</a>
    <?php endif; ?>
</form>

<hr>
<h3>Search and Filter Students</h3>

<form method="GET">
    <input name="search" placeholder="Search name, email or course"
           value="<?= htmlspecialchars($search) ?>">

    <select name="course">
        <option value="">All Courses</option>
        <?php while ($c = $coursesResult->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($c["course"]) ?>"
                <?= $courseFilter === $c["course"] ? "selected" : "" ?>>
                <?= htmlspecialchars($c["course"]) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit">Search / Filter</button>
    <a href="students.php">Reset</a>
</form>

<h3>Student Records</h3>

<table border="1" cellpadding="8">
<tr>
    <th>ID</th><th>Name</th><th>Email</th><th>Enrollment</th>
    <th>Course</th><th>Semester</th><th>Phone</th><th>Actions</th>
</tr>

<?php while ($s = $students->fetch_assoc()): ?>
<tr>
    <td><?= (int)$s["student_id"] ?></td>
    <td><?= htmlspecialchars($s["name"]) ?></td>
    <td><?= htmlspecialchars($s["email"]) ?></td>
    <td><?= htmlspecialchars($s["enrollment_no"] ?? "") ?></td>
    <td><?= htmlspecialchars($s["course"] ?? "") ?></td>
    <td><?= htmlspecialchars((string)($s["semester"] ?? "")) ?></td>
    <td><?= htmlspecialchars($s["phone"] ?? "") ?></td>
    <td>
        <a href="?edit=<?= (int)$s["student_id"] ?>">Edit</a> |
        <a href="?delete=<?= (int)$s["student_id"] ?>"
           onclick="return confirm('Are you sure you want to delete this student?')">
           Delete
        </a>
    </td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>