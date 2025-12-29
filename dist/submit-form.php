<?php
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$conn = new mysqli(
    "mysql.hostinger.in",
    "u132079503_creatoschool",
    "Creatoschool1",
    "u132079503_creatoschool"
);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// Collect data
$name          = $_POST['name'] ?? '';
$phone         = $_POST['phone'] ?? '';
$email         = $_POST['email'] ?? '';
$city          = $_POST['city'] ?? '';
$course        = $_POST['course'] ?? '';
$qualification = $_POST['qualification'] ?? '';
$joiningDate   = $_POST['joiningDate'] ?? '';
$reason        = $_POST['reason'] ?? '';

// Validation
if (!$name || !$phone || !$email) {
    echo json_encode(["success" => false, "message" => "Required fields missing"]);
    exit;
}

// ✅ FIXED PREPARED STATEMENT
$stmt = $conn->prepare("
    INSERT INTO registration 
    (name, phone, email, city, course, qualification, joiningDate, reason)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "ssssssss", // 🔥 8 parameters — PERFECT MATCH
    $name,
    $phone,
    $email,
    $city,
    $course,
    $qualification,
    $joiningDate,
    $reason
);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Registration successful"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
