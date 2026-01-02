<?php
// submit-form.php
declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");
// If your frontend may be served from another origin, adjust this:
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // CORS preflight
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

// DB credentials: for production move to environment variables or config file
$db_host = getenv('DB_HOST') ?: 'mysql.hostinger.in';
$db_user = getenv('DB_USER') ?: 'u132079503_creatoschool';
$db_pass = getenv('DB_PASS') ?: 'Creatoschool1';
$db_name = getenv('DB_NAME') ?: 'u132079503_creatoschool';

mysqli_report(MYSQLI_REPORT_OFF); // avoid notices in output
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    error_log("DB connect error: " . $conn->connect_error);
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

$conn->set_charset('utf8mb4');

// Collect + sanitize (basic)
$name   = trim((string)($_POST['name'] ?? ''));
$phone  = trim((string)($_POST['phone'] ?? ''));
$email  = trim((string)($_POST['email'] ?? ''));
$city   = trim((string)($_POST['city'] ?? ''));
$course = trim((string)($_POST['course'] ?? ''));
$reason = trim((string)($_POST['reason'] ?? ''));
$batch  = trim((string)($_POST['batch'] ?? ''));

// Validation
if ($name === '' || $phone === '' || $email === '') {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Required fields missing"]);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid email address"]);
    exit;
}

// Normalize phone digits
$phoneDigits = preg_replace('/\D+/', '', $phone);
if (strlen($phoneDigits) < 7 || strlen($phoneDigits) > 15) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid phone number"]);
    exit;
}

// Prepare insert (note: table name has underscore)
$sql = "INSERT INTO `digital_marketing_contact`
    (`name`, `phone`, `email`, `city`, `course`, `reason`, `batch`, `created_at`)
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error (prepare)"]);
    exit;
}

$stmt->bind_param(
    "sssssss",
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
    error_log("Execute failed: " . $stmt->error);
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database insert failed"]);
}

$stmt->close();
$conn->close();
