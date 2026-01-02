<?php
// submit-form.php
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // CORS preflight response
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

// Use environment variables in production
$db_host = getenv('DB_HOST') ?: 'mysql.hostinger.in';
$db_user = getenv('DB_USER') ?: 'u132079503_creatoschool';
$db_pass = getenv('DB_PASS') ?: 'Creatoschool1';
$db_name = getenv('DB_NAME') ?: 'u132079503_creatoschool';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    http_response_code(500);
    error_log("DB connect error: " . $conn->connect_error);
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// Collect + sanitize
$name   = trim($_POST['name'] ?? '');
$phone  = trim($_POST['phone'] ?? '');
$email  = trim($_POST['email'] ?? '');
$city   = trim($_POST['city'] ?? '');
$course = trim($_POST['course'] ?? '');
$reason = trim($_POST['reason'] ?? '');
$batch  = trim($_POST['batch'] ?? '');

// Basic validation
if (!$name || !$phone || !$email) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Required fields missing"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid email address"]);
    exit;
}

// Normalize phone: keep digits only
$phoneDigits = preg_replace('/\D+/', '', $phone);
if (strlen($phoneDigits) < 7 || strlen($phoneDigits) > 15) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid phone number"]);
    exit;
}

// Prepared statement — matches frontend fields
$stmt = $conn->prepare("
    INSERT INTO digital-marketing_contact
    (name, phone, email, city, course, reason, batch, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
");

if (!$stmt) {
    http_response_code(500);
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(["success" => false, "message" => "Server error"]);
    exit;
}

$stmt->bind_param("sssssss",
    $name,
    $phoneDigits,
    $email,
    $city,
    $course,
    $reason,
    $batch
);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Registration successful"]);
} else {
    http_response_code(500);
    error_log("Execute failed: " . $stmt->error);
    echo json_encode(["success" => false, "message" => "Database insert failed"]);
}

$stmt->close();
$conn->close();
