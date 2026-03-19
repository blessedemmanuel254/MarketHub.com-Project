<?php
session_start();
include 'connection.php';

header('Content-Type: application/json');

if (!isset($_POST['user_id']) || !isset($_POST['action'])) {
    echo json_encode(["success" => false, "message" => "Missing parameters"]);
    exit;
}

$userId = intval($_POST['user_id']);
$action = $_POST['action'];

// -----------------------------
// ACTIVATE AGENT
// -----------------------------
if ($action === "activate") {

    // Prevent double activation (VERY IMPORTANT)
    $check = $conn->prepare("SELECT is_verified FROM users WHERE user_id=?");
    $check->bind_param("i", $userId);
    $check->execute();
    $check->bind_result($isVerified);
    $check->fetch();
    $check->close();

    if ($isVerified == 1) {
        echo json_encode(["success" => false, "message" => "Agent already activated"]);
        exit;
    }

    // 1. Activate user
    $stmt = $conn->prepare("
        UPDATE users 
        SET is_verified = 1,
            economic_period_count = economic_period_count + 1,
            agent_activated_at = NOW()
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    // 2. Create wallets if not exist
    $walletTypes = ['sales', 'agency'];

    foreach ($walletTypes as $type) {

        $checkWallet = $conn->prepare("
            SELECT wallet_id FROM wallets 
            WHERE user_id = ? AND wallet_type = ?
        ");
        $checkWallet->bind_param("is", $userId, $type);
        $checkWallet->execute();
        $checkWallet->store_result();

        if ($checkWallet->num_rows === 0) {

            $createWallet = $conn->prepare("
                INSERT INTO wallets (user_id, wallet_type, balance, total_transacted)
                VALUES (?, ?, 0, 0)
            ");
            $createWallet->bind_param("is", $userId, $type);
            $createWallet->execute();
            $createWallet->close();
        }

        $checkWallet->close();
    }

    // 3. Commission distribution
    $commissionLevels = [
        1 => 100,
        2 => 40,
        3 => 20
    ];

    $level = 1;

    $stmt = $conn->prepare("SELECT referred_by FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($referrerId);
    $stmt->fetch();
    $stmt->close();

    while ($referrerId && $level <= 3) {

        $amount = $commissionLevels[$level];

        // Credit wallet
        $stmtWallet = $conn->prepare("
            UPDATE wallets 
            SET balance = balance + ?, total_transacted = total_transacted + ?
            WHERE user_id = ? AND wallet_type = 'agency'
        ");
        $stmtWallet->bind_param("dii", $amount, $amount, $referrerId);
        $stmtWallet->execute();
        $stmtWallet->close();

        // Log transaction
        $desc = "Level $level referral commission from agent $userId";

        $stmtLog = $conn->prepare("
            INSERT INTO wallet_transactions 
            (wallet_id, amount, transaction_type, status, description, reference_id, created_at)
            SELECT wallet_id, ?, 'credit', 'completed', ?, ?, NOW()
            FROM wallets 
            WHERE user_id = ? AND wallet_type = 'agency'
        ");
        $stmtLog->bind_param("dsii", $amount, $desc, $userId, $referrerId);
        $stmtLog->execute();
        $stmtLog->close();

        // Move up
        $stmt = $conn->prepare("SELECT referred_by FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $referrerId);
        $stmt->execute();
        $stmt->bind_result($referrerId);
        $stmt->fetch();
        $stmt->close();

        $level++;
    }

    echo json_encode(["success" => true, "message" => "Agent activated successfully"]);
    exit;
}


// -----------------------------
// DEACTIVATE AGENT
// -----------------------------
if ($action === "deactivate") {

    // Prevent double deactivation
    $check = $conn->prepare("SELECT is_verified FROM users WHERE user_id=?");
    $check->bind_param("i", $userId);
    $check->execute();
    $check->bind_result($isVerified);
    $check->fetch();
    $check->close();

    if ($isVerified == 0) {
        echo json_encode(["success" => false, "message" => "Agent already inactive"]);
        exit;
    }

    // Deactivate user
    $stmt = $conn->prepare("
        UPDATE users 
        SET is_verified = 0,
            economic_period_count = GREATEST(economic_period_count - 1, 0)
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        "success" => true,
        "message" => "Agent deactivated successfully (commissions not affected)"
    ]);
    exit;
}


// -----------------------------
// INVALID ACTION
// -----------------------------
echo json_encode(["success" => false, "message" => "Invalid action"]);
exit;
?>