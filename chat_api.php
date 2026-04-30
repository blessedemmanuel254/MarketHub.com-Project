<?php
session_start();
include "connection.php";

header("Content-Type: application/json");
date_default_timezone_set('Africa/Nairobi');

// أمن: must be logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// Detect action
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);

// ----------------------------------
// 1. GET OR CREATE CONVERSATION
// ----------------------------------
if ($action === "init") {

    $buyer_id = (int) $input['buyer_id'];
    $seller_id = (int) $input['seller_id'];
    $order_code = $input['order_code'];

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

    $stmt = $conn->prepare("
      INSERT INTO conversations (order_code, buyer_id, seller_id) 
      VALUES (?, ?, ?)
    ");
    $stmt->bind_param("sii", $order_code, $buyer_id, $seller_id);
    $stmt->execute();

    echo json_encode(["conversation_id" => $stmt->insert_id]);
    exit;
}

// ----------------------------------
// 2. SEND MESSAGE
// ----------------------------------
if ($action === "send") {

    $conversation_id = (int) ($input['conversation_id'] ?? 0);
    $message = trim($input['message'] ?? "");

    if ($conversation_id <= 0 || $message === "") {
        echo json_encode(["success" => false, "error" => "Invalid input"]);
        exit;
    }

    $stmt = $conn->prepare("
      INSERT INTO messages (conversation_id, sender_id, message, created_at) 
      VALUES (?, ?, ?, NOW())
    ");
    $stmt->bind_param("iis", $conversation_id, $user_id, $message);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => [
                "id" => $stmt->insert_id,
                "sender_id" => $user_id,
                "message" => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
                "time" => date("h:i A")
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "error" => "DB error"]);
    }
    exit;
}

// ----------------------------------
// 3. FETCH MESSAGES
// ----------------------------------
if ($action === "fetch") {

    $conversation_id = (int) ($_GET['conversation_id'] ?? 0);
    $last_id = (int) ($_GET['last_id'] ?? 0);

    // Security check
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

    $stmt = $conn->prepare("
      SELECT id, sender_id, message, type, latitude, longitude, address, created_at 
      FROM messages 
      WHERE conversation_id = ? AND id > ?
      ORDER BY id ASC
    ");
    $stmt->bind_param("ii", $conversation_id, $last_id);
    $stmt->execute();
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
    exit;
}

// ----------------------------------
// 4. SEND LOCATION
// ----------------------------------
if ($action === "location") {

    $stmt = $conn->prepare("
      INSERT INTO messages 
      (conversation_id, sender_id, type, latitude, longitude, address, message) 
      VALUES (?, ?, 'location', ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "iiddss",
        $input['conversation_id'],
        $user_id,
        $input['lat'],
        $input['lng'],
        $input['address'],
        $input['manualText']
    );

    $stmt->execute();

    echo json_encode(["success" => true]);
    exit;
}

// ----------------------------------
// DEFAULT
// ----------------------------------
echo json_encode(["error" => "Invalid action"]);
?>