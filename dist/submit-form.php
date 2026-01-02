<?php
header("Content-Type: application/json");

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

// ✅ Hostinger DB Connection
$conn = new mysqli(
    "localhost",
    "u132079503_creatoschool",
    "Creatoschool1",
    "u132079503_creatoschool"
);

// Connection check
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

// Collect Data
$name   = trim($_POST['name'] ?? '');
$phone  = trim($_POST['phone'] ?? '');
$email  = trim($_POST['email'] ?? '');
$city   = trim($_POST['city'] ?? '');
$course = trim($_POST['course'] ?? '');
$reason = trim($_POST['reason'] ?? '');
$batch  = trim($_POST['batch'] ?? '');

// Validation
if (!$name || !$phone || !$email) {
    echo json_encode([
        "success" => false,
        "message" => "Required fields missing"
    ]);
    exit;
}

// ✅ Correct SQL (table name escaped)
$stmt = $conn->prepare("
    INSERT INTO `digital_marketing_contact`
    (name, phone, email, city, course, batch, reason)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => $conn->error
    ]);
    exit;
}

// ✅ Correct parameter count
$stmt->bind_param(
    "sssssss",
    $name,
    $phone,
    $email,
    $city,
    $course,
    $batch,
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
