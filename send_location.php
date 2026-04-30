<?php
session_start();
include "connection.php";

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
  INSERT INTO messages 
  (conversation_id, sender_id, type, latitude, longitude, address, message) 
  VALUES (?, ?, 'location', ?, ?, ?, ?)
");

$stmt->bind_param(
  "iiddss",
  $data['conversation_id'],
  $user_id,
  $data['lat'],
  $data['lng'],
  $data['address'],
  $data['manualText']
);

$stmt->execute();

echo json_encode(["success" => true]);
?>