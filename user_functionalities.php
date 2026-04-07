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
    PROCESS COMMISSIONS (PRODUCTION - FULL SAFE)
    ----------------------------- */
    if ($shouldPayCommission) {

        $SYSTEM_USER_ID = 21; // system account
        $levels = [100, 40, 20];
        $level = 0;

        // Get first referrer
        $stmt = $conn->prepare("SELECT referred_by FROM users WHERE user_id=?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($referrerId);
        $stmt->fetch();
        $stmt->close();

        /* =====================================================
        CASE 1: NO REFERRER → SYSTEM EARNS
        ===================================================== */
        if (!$referrerId) {

            $amount = $levels[0];

            /* GET OR CREATE SYSTEM WALLET */
            $walletStmt = $conn->prepare("
                SELECT wallet_id 
                FROM wallets 
                WHERE user_id=? AND wallet_type='administrator' LIMIT 1
            ");
            $walletStmt->bind_param("i", $SYSTEM_USER_ID);
            $walletStmt->execute();
            $walletStmt->store_result();

            if ($walletStmt->num_rows === 0) {

                // 🔥 CREATE SYSTEM WALLET
                $createWallet = $conn->prepare("
                    INSERT INTO wallets 
                    (user_id, wallet_type, balance, total_transacted, created_at, updated_at)
                    VALUES (?, 'main', 0, 0, NOW(), NOW())
                ");
                $createWallet->bind_param("i", $SYSTEM_USER_ID);
                $createWallet->execute();
                $walletId = $createWallet->insert_id;
                $createWallet->close();

            } else {
                $walletStmt->bind_result($walletId);
                $walletStmt->fetch();
            }

            $walletStmt->close();

            if ($walletId) {

                $txn = $conn->prepare("
                    UPDATE financial_transactions
                    SET status = 'completed'
                    WHERE source_type = 'agency_commission'
                    AND source_id = ?
                    AND payer_id = ?
                    AND receiver_id = ?
                    AND amount = ?
                    AND status = 'pending'
                    LIMIT 1
                ");

                $txn->bind_param(
                    "iiid",
                    $userId,
                    $userId,
                    $SYSTEM_USER_ID,
                    $amount
                );

                $txn->execute();
                $txn->close();

                // Update wallet
                $update = $conn->prepare("
                    UPDATE wallets 
                    SET balance = balance + ?, 
                        total_transacted = total_transacted + ?
                    WHERE wallet_id=?
                ");
                $update->bind_param("ddi", $amount, $amount, $walletId);
                $update->execute();
                $update->close();
            }

        }

        /* =====================================================
        CASE 2: HAS REFERRERS → PAY UPLINE
        ===================================================== */
        while ($referrerId && $level < 3) {

            $amount = $levels[$level];
            $levelNumber = $level + 1;

            /* GET OR CREATE AGENCY WALLET */
            $walletStmt = $conn->prepare("
                SELECT wallet_id 
                FROM wallets 
                WHERE user_id=? AND wallet_type='agency'
                LIMIT 1
            ");
            $walletStmt->bind_param("i", $referrerId);
            $walletStmt->execute();
            $walletStmt->store_result();

            if ($walletStmt->num_rows === 0) {

                $createWallet = $conn->prepare("
                    INSERT INTO wallets 
                    (user_id, wallet_type, balance, total_transacted, created_at, updated_at)
                    VALUES (?, 'agency', 0, 0, NOW(), NOW())
                ");
                $createWallet->bind_param("i", $referrerId);
                $createWallet->execute();
                $walletId = $createWallet->insert_id;
                $createWallet->close();

            } else {
                $walletStmt->bind_result($walletId);
                $walletStmt->fetch();
            }
            $walletStmt->close();

            /* INSERT TRANSACTION */
            $description = "Level $levelNumber commission from agent $userId";

            $txn = $conn->prepare("
                UPDATE financial_transactions
                SET status = 'completed'
                WHERE source_type = 'commission'
                AND source_id = ?
                AND payer_id = ?
                AND receiver_id = ?
                AND amount = ?
                AND status = 'pending'
                LIMIT 1
            ");

            $txn->bind_param(
                "iiid",
                $userId,
                $userId,
                $referrerId,
                $amount
            );

            $txn->execute();
            $txn->close();

            /* UPDATE WALLET */
            $update = $conn->prepare("
                UPDATE wallets 
                SET balance = balance + ?,total_transacted = total_transacted + ?
                WHERE wallet_id=?
            ");
            $update->bind_param("ddi", $amount, $amount, $walletId);
            $update->execute();
            $update->close();
            /* -----------------------------
            SEND EMAIL TO REFERRER
            ----------------------------- */
            $stmtEmail = $conn->prepare("SELECT email, full_name FROM users WHERE user_id=? LIMIT 1");
            $stmtEmail->bind_param("i", $referrerId);
            $stmtEmail->execute();
            $stmtEmail->bind_result($referrerEmail, $referrerName);
            $stmtEmail->fetch();
            $stmtEmail->close();

            if ($referrerEmail) {
                $referrerEmail = base64_decode($referrerEmail);
                $subject = "Level $levelNumber Earnings";
                $body = "Hooray! 🥳 You've just earned KES " . number_format($amount, 2) .
                        " for inviting " . htmlspecialchars($userId) . ". Let's continue building!";
                $headers = "From: Market Hub <no-reply@makethub.shop>\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                @mail($referrerEmail, $subject, $body, $headers);
            }

            /* MOVE UP */
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