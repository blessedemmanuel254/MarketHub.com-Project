<?php
session_start();
include 'connection.php';

header('Content-Type: application/json');

if (!isset($_POST['user_id'], $_POST['action'])) {
    echo json_encode(["success" => false, "message" => "Missing parameters"]);
    exit;
}

$userId = intval($_POST['user_id']);
$action = $_POST['action'];

/* =========================
   SUSPEND / RESTORE / DELETE
========================= */

if ($action === "suspend") {
    $stmt = $conn->prepare("UPDATE users SET status='suspended' WHERE user_id=?");
}

elseif ($action === "restore") {
    $stmt = $conn->prepare("UPDATE users SET status='active' WHERE user_id=?");
}

elseif ($action === "delete") {
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
}

elseif ($action === "activate") {

    /* -----------------------------
       PREVENT DOUBLE ACTIVATION
    ----------------------------- */
    $check = $conn->prepare("SELECT is_verified FROM users WHERE user_id=?");
    $check->bind_param("i", $userId);
    $check->execute();
    $check->bind_result($isVerified);
    $check->fetch();
    $check->close();

    if ($isVerified == 1) {
        echo json_encode(["success" => false, "message" => "Already activated"]);
        exit;
    }

    /* -----------------------------
       ACTIVATE USER
    ----------------------------- */
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

    /* -----------------------------
       CREATE WALLETS
    ----------------------------- */
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

    /* -----------------------------
       DISTRIBUTE COMMISSIONS
    ----------------------------- */
    $levels = [100, 40, 20];
    $level = 0;

    $stmt = $conn->prepare("SELECT referred_by FROM users WHERE user_id=?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($referrerId);
    $stmt->fetch();
    $stmt->close();

    while ($referrerId && $level < 3) {

        $amount = $levels[$level];

        // Ensure wallet exists
        $walletCheck = $conn->prepare("
            SELECT wallet_id FROM wallets 
            WHERE user_id=? AND wallet_type='agency'
        ");
        $walletCheck->bind_param("i", $referrerId);
        $walletCheck->execute();
        $walletCheck->store_result();

        if ($walletCheck->num_rows === 0) {
            $create = $conn->prepare("
                INSERT INTO wallets (user_id, wallet_type, balance, total_transacted)
                VALUES (?, 'agency', 0, 0)
            ");
            $create->bind_param("i", $referrerId);
            $create->execute();
            $walletId = $create->insert_id;
            $create->close();
        } else {
            $walletCheck->bind_result($walletId);
            $walletCheck->fetch();
        }
        $walletCheck->close();

        // Credit wallet
        $update = $conn->prepare("
            UPDATE wallets 
            SET balance = balance + ?, total_transacted = total_transacted + ?
            WHERE wallet_id=?
        ");
        $update->bind_param("ddi", $amount, $amount, $walletId);
        $update->execute();
        $update->close();

        // Log transaction
        $desc = "Level " . ($level+1) . " commission from agent $userId";

        $log = $conn->prepare("
            INSERT INTO wallet_transactions 
            (wallet_id, amount, transaction_type, status, description, reference_id)
            VALUES (?, ?, 'credit', 'completed', ?, ?)
        ");
        $log->bind_param("idss", $walletId, $amount, $desc, $userId);
        $log->execute();
        $log->close();
        
        $levelNumber = $level + 1;
        $commissionType = "activation";

        $commissionStmt = $conn->prepare("
            INSERT INTO agent_commissions 
            (agent_id, source_user_id, level, amount, commission_type, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $commissionStmt->bind_param(
            "iiids",
            $referrerId,
            $userId,
            $levelNumber,
            $amount,
            $commissionType
        );

        $commissionStmt->execute();
        $commissionStmt->close();

        // Move up chain
        $stmt = $conn->prepare("SELECT referred_by FROM users WHERE user_id=?");
        $stmt->bind_param("i", $referrerId);
        $stmt->execute();
        $stmt->bind_result($referrerId);
        $stmt->fetch();
        $stmt->close();

        $level++;
    }

    echo json_encode(["success" => true]);
    exit;
}

/* =========================
   DEACTIVATE
========================= */

elseif ($action === "deactivate") {

    $stmt = $conn->prepare("
        UPDATE users 
        SET is_verified = 0,
            economic_period_count = GREATEST(economic_period_count - 1, 0)
        WHERE user_id = ?
    ");
}

/* =========================
   EXECUTE SIMPLE ACTIONS
========================= */

if (isset($stmt)) {
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $stmt->error]);
    }
    exit;
}

/* =========================
   INVALID ACTION
========================= */

echo json_encode(["success" => false, "message" => "Invalid action"]);
exit;
?>