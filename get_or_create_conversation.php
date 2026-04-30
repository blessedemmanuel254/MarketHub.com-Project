<?php
session_start();
include "connection.php";

$data = json_decode(file_get_contents("php://input"), true);

$buyer_id = $data['buyer_id'];
$seller_id = $data['seller_id'];
$order_code = $data['order_code'];

// Check if conversation exists
$stmt = $conn->prepare("
  SELECT id FROM conversations 
  WHERE order_code = ? AND buyer_id = ? AND seller_id = ?
  LIMIT 1
");
$stmt->bind_param("sii", $order_code, $buyer_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
  echo json_encode(["conversation_id" => $row['id']]);
  exit;
}

// Create new conversation
$stmt = $conn->prepare("
  INSERT INTO conversations (order_code, buyer_id, seller_id) 
  VALUES (?, ?, ?)
");
$stmt->bind_param("sii", $order_code, $buyer_id, $seller_id);
$stmt->execute();

echo json_encode(["conversation_id" => $stmt->insert_id]);
?>