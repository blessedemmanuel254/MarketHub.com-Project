<?php
session_start();
include "connection.php";

header('Content-Type: application/json');

date_default_timezone_set('Africa/Nairobi');

$user_id = $_SESSION['user_id'];

$conversation_id = isset($_GET['conversation_id']) ? (int) $_GET['conversation_id'] : 0;
$last_id = isset($_GET['last_id']) ? (int) $_GET['last_id'] : 0;

// امنیت: verify user belongs to conversation
$stmtCheck = $conn->prepare("
  SELECT id FROM conversations 
  WHERE id = ? AND (buyer_id = ? OR seller_id = ?)
");
$stmtCheck->bind_param("iii", $conversation_id, $user_id, $user_id);
$stmtCheck->execute();
$check = $stmtCheck->get_result();

if ($check->num_rows === 0) {
  echo json_encode([]);
  exit;
}

// Fetch messages
$stmt = $conn->prepare("
  SELECT id, sender_id, message, type, latitude, longitude, address, created_at 
  FROM messages 
  WHERE conversation_id = ? AND id > ?
  ORDER BY id ASC
");

$stmt->bind_param("ii", $conversation_id, $last_id);

if (!$stmt->execute()) {
  echo json_encode(["error" => "Failed to fetch messages"]);
  exit;
}

$result = $stmt->get_result();

$messages = [];

while ($row = $result->fetch_assoc()) {
  $messages[] = [
    "id" => (int)$row['id'],
    "sender_id" => (int)$row['sender_id'],
    "message" => $row['message'],
    "type" => $row['type'],
    "latitude" => $row['latitude'],
    "longitude" => $row['longitude'],
    "address" => $row['address'],
    "time" => date("h:i A", strtotime($row['created_at']))
  ];
}

echo json_encode($messages);
?>