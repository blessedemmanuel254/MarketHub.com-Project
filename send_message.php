<?php
session_start();
include "connection.php";

header("Content-Type: application/json");

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$conversation_id = isset($data['conversation_id']) ? (int) $data['conversation_id'] : 0;
$message = trim($data['message'] ?? "");

// Validate message
if ($conversation_id <= 0 || $message === "") {
    echo json_encode(["success" => false, "error" => "Invalid input"]);
    exit;
}

// Insert message
$stmt = $conn->prepare("
  INSERT INTO messages (conversation_id, sender_id, message, created_at) 
  VALUES (?, ?, ?, NOW())
");

$stmt->bind_param("iis", $conversation_id, $user_id, $message);

if ($stmt->execute()) {
    $message_id = $stmt->insert_id;

    echo json_encode([
        "success" => true,
        "message" => [
            "id" => $message_id,
            "sender_id" => $user_id,
            "message" => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
            "time" => date("h:i A")
        ]
    ]);
} else {
    echo json_encode(["success" => false, "error" => "Database error"]);
}
?>