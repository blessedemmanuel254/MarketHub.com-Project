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
       FETCH USER STATE
    ----------------------------- */
    $check = $conn->prepare("
        SELECT is_verified, subscription_expires_at
        FROM users 
        WHERE user_id=?
    ");
    $check->bind_param("i", $userId);
    $check->execute();
    $check->bind_result($isVerified, $expiresAt);
    $check->fetch();
    $check->close();

    $now = date('Y-m-d H:i:s');
    $isFirstTime = empty($expiresAt);
    $isExpired   = !empty($expiresAt) && ($expiresAt < $now);

    $shouldPayCommission = false;

    /* -----------------------------
       DETERMINE ACTION
    ----------------------------- */
    if ($isFirstTime) {
        // First activation (payment)
        $shouldPayCommission = true;
        $stmt = $conn->prepare("
            UPDATE users 
            SET is_verified = 1,
                economic_period_count = economic_period_count + 1,
                subscription_expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY)
            WHERE user_id = ?
        ");
    } elseif ($isVerified == 0) {
        // Reactivation (no payment)
        $stmt = $conn->prepare("
            UPDATE users 
            SET is_verified = 1
            WHERE user_id = ?
        ");
    } elseif ($isExpired) {
        // Renewal (payment)
        $shouldPayCommission = true;
        $stmt = $conn->prepare("
            UPDATE users 
            SET economic_period_count = economic_period_count + 1,
                subscription_expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY)
            WHERE user_id = ?
        ");
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Subscription still active"
        ]);
        exit;
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    /* -----------------------------
       CREATE WALLETS IF MISSING
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
       PROCESS COMMISSIONS (ONLY IF PAYMENT)
       Update pending commissions to completed instead of inserting new
    ----------------------------- */
    if ($shouldPayCommission) {

        $levels = [100, 40, 20]; // Level payouts
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

            // Update wallet balance
            $update = $conn->prepare("
                UPDATE wallets 
                SET balance = balance + ?, total_transacted = total_transacted + ?
                WHERE wallet_id=?
            ");
            $update->bind_param("ddi", $amount, $amount, $walletId);
            $update->execute();
            $update->close();

            $desc = "Level " . ($level + 1) . " commission from agent $userId";

            // Log wallet transaction
            $log = $conn->prepare("
                INSERT INTO wallet_transactions 
                (wallet_id, amount, transaction_type, status, description, reference_id)
                VALUES (?, ?, 'credit', 'completed', ?, ?)
            ");
            $log->bind_param("idss", $walletId, $amount, $desc, $userId);
            $log->execute();
            $log->close();

            $levelNumber = $level + 1;

            // UPDATE pending commissions to completed
            $commissionUpdate = $conn->prepare("
                UPDATE agent_commissions
                SET status = 'paid',
                    amount = ?,
                    created_at = NOW()
                WHERE source_user_id = ? AND level = ? AND status = 'pending'
            ");
            $commissionUpdate->bind_param("dii", $amount, $userId, $levelNumber);
            $commissionUpdate->execute();
            $commissionUpdate->close();

            // Move up the chain
            $stmt = $conn->prepare("SELECT referred_by FROM users WHERE user_id=?");
            $stmt->bind_param("i", $referrerId);
            $stmt->execute();
            $stmt->bind_result($referrerId);
            $stmt->fetch();
            $stmt->close();

            $level++;
        }
    }
    
    /* -----------------------------
    FETCH UPDATED VALUES
    ----------------------------- */
    $stmtFetch = $conn->prepare("
        SELECT economic_period_count
        FROM users
        WHERE user_id = ?
    ");
    $stmtFetch->bind_param("i", $userId);
    $stmtFetch->execute();
    $stmtFetch->bind_result($newEconomicCount);
    $stmtFetch->fetch();
    $stmtFetch->close();

    /* ---------- COUNT SUB AGENTS ---------- */
    $stmtSub = $conn->prepare("
        SELECT COUNT(*) 
        FROM users 
        WHERE referred_by = ?
    ");
    $stmtSub->bind_param("i", $userId);
    $stmtSub->execute();
    $stmtSub->bind_result($newSubAgents);
    $stmtSub->fetch();
    $stmtSub->close();

    /* ✅ RETURN DATA */
    echo json_encode([
        "success" => true,
        "economic_period_count" => $newEconomicCount,
        "total_sub_agents" => $newSubAgents
    ]);
    exit;
}

/* =========================
   DEACTIVATE
========================= */

elseif ($action === "deactivate") {

    /* -----------------------------
       CHECK CURRENT STATE
    ----------------------------- */
    $check = $conn->prepare("
        SELECT is_verified 
        FROM users 
        WHERE user_id=?
    ");
    $check->bind_param("i", $userId);
    $check->execute();
    $check->bind_result($isVerified);
    $check->fetch();
    $check->close();

    if ($isVerified == 0) {
        echo json_encode([
            "success" => false,
            "message" => "Already inactive"
        ]);
        exit;
    }

    /* -----------------------------
       DEACTIVATE (NO PAYMENT REVERSAL)
    ----------------------------- */
    $stmt = $conn->prepare("
        UPDATE users 
        SET is_verified = 0
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