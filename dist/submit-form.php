<?php
header("Content-Type: application/json");

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
    exit;
}

// DB Credentials (UNCHANGED – YOURS)
$host = "mysql.hostinger.in";
$user = "u132079503_creatoschool";
$pass = "Creatoschool1";
$db   = "u132079503_creatoschool";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

// Collect form data safely
$name          = $_POST['name'] ?? '';
$phone         = $_POST['phone'] ?? '';
$email         = $_POST['email'] ?? '';
$city          = $_POST['city'] ?? '';
$course        = $_POST['course'] ?? '';
$qualification = $_POST['qualification'] ?? '';
$reason        = $_POST['reason'] ?? '';
$joiningDate        = $_POST['joiningDate'] ?? '';

// Basic validation
if (empty($name) || empty($phone) || empty($email)) {
    echo json_encode([
        "success" => false,
        "message" => "Required fields are missing"
    ]);
    exit;
}

// ✅ Prepared Statement (SAFE & PROFESSIONAL)
$stmt = $conn->prepare(
    "INSERT INTO registration 
    (name, phone, email, city, course, qualification,joiningDate, reason) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "sssssss",
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
        "message" => "Unable to submit registration"
    ]);
}

$stmt->close();
$conn->close();
