<?php

/*
|--------------------------------------------------------------------------
| MAKETHUB - M-PESA BATTERY RECHARGE CALLBACK
|--------------------------------------------------------------------------
|
| Safaricom/Daraja calls this URL after an STK Push is completed.
|
| Responsibilities:
|
| 1. Receive and decode the Daraja callback.
| 2. Confirm the M-Pesa transaction succeeded.
| 3. Identify the Makethub seller.
| 4. Extract the M-Pesa receipt/reference.
| 5. Prevent duplicate processing.
| 6. Save the financial transaction.
| 7. Activate/extend the seller battery.
| 8. Increase economic_period_count.
|
|--------------------------------------------------------------------------
*/

require_once 'connection.php';

header('Content-Type: application/json; charset=utf-8');


/*
|--------------------------------------------------------------------------
| ERROR LOGGING
|--------------------------------------------------------------------------
*/

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);


/*
|--------------------------------------------------------------------------
| HELPER: RETURN JSON
|--------------------------------------------------------------------------
*/

function callbackResponse(
    bool $success,
    string $message,
    array $extra = []
) {

    echo json_encode(
        array_merge(
            [
                'success' => $success,
                'message' => $message
            ],
            $extra
        )
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| READ RAW CALLBACK
|--------------------------------------------------------------------------
*/

$rawInput = file_get_contents('php://input');


if (
    $rawInput === false ||
    trim($rawInput) === ''
) {

    callbackResponse(
        false,
        'Empty M-Pesa callback.'
    );

}


/*
|--------------------------------------------------------------------------
| DECODE CALLBACK
|--------------------------------------------------------------------------
*/

$data = json_decode(
    $rawInput,
    true
);


if (
    !is_array($data) ||
    !isset($data['Body']['stkCallback'])
) {

    error_log(
        'Makethub M-Pesa callback: Invalid payload: ' .
        $rawInput
    );

    callbackResponse(
        false,
        'Invalid callback payload.'
    );

}


$callback = $data['Body']['stkCallback'];


/*
|--------------------------------------------------------------------------
| BASIC CALLBACK VALUES
|--------------------------------------------------------------------------
*/

$merchantRequestId =
    trim(
        $callback['MerchantRequestID'] ?? ''
    );

$checkoutRequestId =
    trim(
        $callback['CheckoutRequestID'] ?? ''
    );

$resultCode =
    isset($callback['ResultCode'])
        ? (int) $callback['ResultCode']
        : null;

$resultDesc =
    trim(
        $callback['ResultDesc'] ?? ''
    );


/*
|--------------------------------------------------------------------------
| CHECK REQUIRED IDENTIFIERS
|--------------------------------------------------------------------------
*/

if (
    empty($checkoutRequestId)
) {

    error_log(
        'Makethub M-Pesa callback: Missing CheckoutRequestID.'
    );

    callbackResponse(
        false,
        'Missing M-Pesa CheckoutRequestID.'
    );

}


/*
|--------------------------------------------------------------------------
| M-PESA RESULT
|--------------------------------------------------------------------------
|
| Daraja:
|
| ResultCode = 0
|
| means the transaction was successful.
|
|--------------------------------------------------------------------------
*/

if ($resultCode !== 0) {

    /*
     * The payment was not successful.
     *
     * IMPORTANT:
     * Do NOT activate the battery.
     */

    error_log(
        'Makethub M-Pesa failed callback. ' .
        'CheckoutRequestID=' .
        $checkoutRequestId .
        ' ResultCode=' .
        $resultCode .
        ' ResultDesc=' .
        $resultDesc
    );

    callbackResponse(
        true,
        'Payment was not completed.',
        [
            'result_code' => $resultCode
        ]
    );

}


/*
|--------------------------------------------------------------------------
| EXTRACT CALLBACK METADATA
|--------------------------------------------------------------------------
*/

$metadata = [];

if (
    isset($callback['CallbackMetadata']['Item']) &&
    is_array($callback['CallbackMetadata']['Item'])
) {

    foreach (
        $callback['CallbackMetadata']['Item']
        as $item
    ) {

        if (
            isset($item['Name'])
        ) {

            $metadata[
                $item['Name']
            ] =
                $item['Value'] ?? null;

        }

    }

}


/*
|--------------------------------------------------------------------------
| M-PESA TRANSACTION DETAILS
|--------------------------------------------------------------------------
*/

$mpesaReceipt =
    trim(
        (string) (
            $metadata['MpesaReceiptNumber']
            ?? ''
        )
    );


$amountPaid =
    isset($metadata['Amount'])
        ? (float) $metadata['Amount']
        : 0;


$phoneNumber =
    trim(
        (string) (
            $metadata['PhoneNumber']
            ?? ''
        )
    );


$transactionDate =
    isset($metadata['TransactionDate'])
        ? (string) $metadata['TransactionDate']
        : null;


/*
|--------------------------------------------------------------------------
| RECEIPT IS REQUIRED
|--------------------------------------------------------------------------
*/

if (
    empty($mpesaReceipt)
) {

    error_log(
        'Makethub M-Pesa callback: Successful payment without receipt. ' .
        'CheckoutRequestID=' .
        $checkoutRequestId
    );

    callbackResponse(
        false,
        'M-Pesa receipt number was not received.'
    );

}


/*
|--------------------------------------------------------------------------
| AMOUNT VALIDATION
|--------------------------------------------------------------------------
*/

if (
    $amountPaid <= 0
) {

    error_log(
        'Makethub M-Pesa callback: Invalid amount. ' .
        'CheckoutRequestID=' .
        $checkoutRequestId
    );

    callbackResponse(
        false,
        'Invalid M-Pesa payment amount.'
    );

}


/*
|--------------------------------------------------------------------------
| IDENTIFY SELLER
|--------------------------------------------------------------------------
|
| rechargeBattery.php currently sends:
|
| AccountReference = MAKETHUB-{user_id}
|
|--------------------------------------------------------------------------
*/

$accountReference =
    trim(
        (string) (
            $callback['AccountReference']
            ?? ''
        )
    );


$userId = 0;


/*
|--------------------------------------------------------------------------
| EXTRACT USER ID FROM ACCOUNT REFERENCE
|--------------------------------------------------------------------------
*/

if (
    preg_match(
        '/^MAKETHUB-(\d+)$/i',
        $accountReference,
        $matches
    )
) {

    $userId = (int) $matches[1];

}


/*
|--------------------------------------------------------------------------
| FALLBACK:
| TRY TO IDENTIFY USER FROM PHONE NUMBER
|--------------------------------------------------------------------------
*/

if (
    $userId <= 0 &&
    !empty($phoneNumber)
) {

    $normalizedPhone =
        preg_replace(
            '/\D+/',
            '',
            $phoneNumber
        );


    if (
        substr($normalizedPhone, 0, 3) === '254'
    ) {

        $normalizedPhone =
            '0' .
            substr(
                $normalizedPhone,
                3
            );

    }


    $phoneStmt =
        $conn->prepare(
            "
            SELECT user_id
            FROM users
            WHERE phone = ?
            AND account_type = 'seller'
            LIMIT 1
            "
        );


    if ($phoneStmt) {

        $phoneStmt->bind_param(
            "s",
            $normalizedPhone
        );

        $phoneStmt->execute();

        $phoneResult =
            $phoneStmt->get_result();

        if (
            $phoneResult &&
            $phoneResult->num_rows === 1
        ) {

            $phoneUser =
                $phoneResult->fetch_assoc();

            $userId =
                (int) $phoneUser['user_id'];

        }

        $phoneStmt->close();

    }

}


/*
|--------------------------------------------------------------------------
| SELLER MUST BE IDENTIFIED
|--------------------------------------------------------------------------
*/

if (
    $userId <= 0
) {

    error_log(
        'Makethub M-Pesa callback: Unable to identify seller. ' .
        'CheckoutRequestID=' .
        $checkoutRequestId .
        ' AccountReference=' .
        $accountReference
    );

    callbackResponse(
        false,
        'Unable to identify the Makethub seller.'
    );

}


/*
|--------------------------------------------------------------------------
| GET SELLER
|--------------------------------------------------------------------------
*/

$sellerStmt =
    $conn->prepare(
        "
        SELECT
            user_id,
            account_type,
            phone,
            subscription_status,
            subscription_started_at,
            subscription_expires_at,
            economic_period_count,
            timezone
        FROM users
        WHERE user_id = ?
        LIMIT 1
        "
    );


if (!$sellerStmt) {

    error_log(
        'Makethub callback seller query failed: ' .
        $conn->error
    );

    callbackResponse(
        false,
        'Unable to access seller account.'
    );

}


$sellerStmt->bind_param(
    "i",
    $userId
);

$sellerStmt->execute();

$sellerResult =
    $sellerStmt->get_result();

$seller =
    $sellerResult
        ? $sellerResult->fetch_assoc()
        : null;

$sellerStmt->close();


/*
|--------------------------------------------------------------------------
| VERIFY SELLER
|--------------------------------------------------------------------------
*/

if (
    !$seller ||
    $seller['account_type'] !== 'seller'
) {

    callbackResponse(
        false,
        'Invalid seller account.'
    );

}


/*
|--------------------------------------------------------------------------
| DUPLICATE CHECK #1
|--------------------------------------------------------------------------
|
| Check M-Pesa receipt number.
|
| The M-Pesa receipt is the actual payment reference.
|
|--------------------------------------------------------------------------
*/

$duplicateStmt =
    $conn->prepare(
        "
        SELECT
            transaction_id,
            status
        FROM financial_transactions
        WHERE reference_code = ?
        LIMIT 1
        "
    );


if ($duplicateStmt) {

    $duplicateStmt->bind_param(
        "s",
        $mpesaReceipt
    );

    $duplicateStmt->execute();

    $duplicateResult =
        $duplicateStmt->get_result();

    $existingTransaction =
        $duplicateResult
            ? $duplicateResult->fetch_assoc()
            : null;

    $duplicateStmt->close();


    if ($existingTransaction) {

        /*
         * The callback has already been processed.
         *
         * Do NOT extend the battery again.
         */

        callbackResponse(
            true,
            'Payment has already been processed.',
            [
                'duplicate' => true,
                'transaction_id' =>
                    (int) $existingTransaction[
                        'transaction_id'
                    ]
            ]
        );

    }

}


/*
|--------------------------------------------------------------------------
| DUPLICATE CHECK #2
|--------------------------------------------------------------------------
|
| Also check CheckoutRequestID.
|
| This protects against repeated callbacks even if
| the M-Pesa receipt is handled differently.
|
|--------------------------------------------------------------------------
*/

$checkoutDuplicateStmt =
    $conn->prepare(
        "
        SELECT
            transaction_id,
            status
        FROM financial_transactions
        WHERE source_id = ?
        AND source_type = 'battery_recharge'
        LIMIT 1
        "
    );


if ($checkoutDuplicateStmt) {

    $checkoutDuplicateStmt->bind_param(
        "s",
        $checkoutRequestId
    );

    $checkoutDuplicateStmt->execute();

    $checkoutDuplicateResult =
        $checkoutDuplicateStmt->get_result();

    $existingCheckout =
        $checkoutDuplicateResult
            ? $checkoutDuplicateResult->fetch_assoc()
            : null;

    $checkoutDuplicateStmt->close();


    if ($existingCheckout) {

        callbackResponse(
            true,
            'Payment has already been processed.',
            [
                'duplicate' => true,
                'transaction_id' =>
                    (int) $existingCheckout[
                        'transaction_id'
                    ]
            ]
        );

    }

}


/*
|--------------------------------------------------------------------------
| DATABASE TRANSACTION
|--------------------------------------------------------------------------
|
| Everything from this point should happen atomically.
|
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();


try {

    /*
    |--------------------------------------------------------------------------
    | LOCK SELLER ROW
    |--------------------------------------------------------------------------
    |
    | Prevent two simultaneous successful callbacks from extending
    | the same seller subscription independently.
    |
    |--------------------------------------------------------------------------
    */

    $lockStmt =
        $conn->prepare(
            "
            SELECT
                subscription_status,
                subscription_started_at,
                subscription_expires_at,
                economic_period_count
            FROM users
            WHERE user_id = ?
            LIMIT 1
            FOR UPDATE
            "
        );


    if (!$lockStmt) {

        throw new Exception(
            'Unable to lock seller account.'
        );

    }


    $lockStmt->bind_param(
        "i",
        $userId
    );

    $lockStmt->execute();

    $lockedResult =
        $lockStmt->get_result();

    $lockedSeller =
        $lockedResult
            ? $lockedResult->fetch_assoc()
            : null;

    $lockStmt->close();


    if (!$lockedSeller) {

        throw new Exception(
            'Seller account disappeared during transaction.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | SECOND DUPLICATE CHECK INSIDE TRANSACTION
    |--------------------------------------------------------------------------
    */

    $safeDuplicateStmt =
        $conn->prepare(
            "
            SELECT transaction_id
            FROM financial_transactions
            WHERE reference_code = ?
            LIMIT 1
            "
        );


    if (!$safeDuplicateStmt) {

        throw new Exception(
            'Unable to perform duplicate transaction check.'
        );

    }


    $safeDuplicateStmt->bind_param(
        "s",
        $mpesaReceipt
    );

    $safeDuplicateStmt->execute();

    $safeDuplicateResult =
        $safeDuplicateStmt->get_result();

    $safeExisting =
        $safeDuplicateResult
            ? $safeDuplicateResult->fetch_assoc()
            : null;

    $safeDuplicateStmt->close();


    if ($safeExisting) {

        /*
         * Another callback already processed this payment
         * while this request was waiting.
         */

        $conn->commit();

        callbackResponse(
            true,
            'Payment has already been processed.',
            [
                'duplicate' => true,
                'transaction_id' =>
                    (int) $safeExisting[
                        'transaction_id'
                    ]
            ]
        );

    }


    /*
    |--------------------------------------------------------------------------
    | DETERMINE SUBSCRIPTION PERIOD
    |--------------------------------------------------------------------------
    |
    | Makethub's recharge model uses 28-day economic periods.
    |
    |--------------------------------------------------------------------------
    */

    $periodDays = 28;


    /*
    |--------------------------------------------------------------------------
    | TIMEZONE
    |--------------------------------------------------------------------------
    */

    $sellerTimezone =
        trim(
            (string) (
                $seller['timezone']
                ?? ''
            )
        );


    if (
        empty($sellerTimezone)
    ) {

        $sellerTimezone =
            'Africa/Nairobi';

    }


    try {

        $timezone =
            new DateTimeZone(
                $sellerTimezone
            );

    } catch (Exception $e) {

        $timezone =
            new DateTimeZone(
                'Africa/Nairobi'
            );

    }


    $now =
        new DateTimeImmutable(
            'now',
            $timezone
        );


    /*
    |--------------------------------------------------------------------------
    | DETERMINE START DATE
    |--------------------------------------------------------------------------
    |
    | If the current battery is still active:
    |
    |     existing expiry → +28 days
    |
    | If expired/no battery:
    |
    |     now → +28 days
    |
    |--------------------------------------------------------------------------
    */

    $existingExpiry =
        $lockedSeller[
            'subscription_expires_at'
        ] ?? null;


    $existingExpiryTimestamp =
        !empty($existingExpiry)
            ? strtotime($existingExpiry)
            : false;


    $nowTimestamp =
        $now->getTimestamp();


    if (
        $existingExpiryTimestamp !== false &&
        $existingExpiryTimestamp > $nowTimestamp
    ) {

        /*
         * Recharge while battery is still active.
         *
         * Add the new period to the existing expiry.
         */

        $subscriptionStart =
            new DateTimeImmutable(
                $existingExpiry,
                $timezone
            );

    } else {

        /*
         * Recharge after expiry or first recharge.
         */

        $subscriptionStart =
            $now;

    }


    $subscriptionExpires =
        $subscriptionStart
            ->modify(
                '+' . $periodDays . ' days'
            );


    /*
    |--------------------------------------------------------------------------
    | ECONOMIC PERIOD COUNT
    |--------------------------------------------------------------------------
    */

    $currentPeriodCount =
        (int) (
            $lockedSeller[
                'economic_period_count'
            ] ?? 0
        );


    $newPeriodCount =
        $currentPeriodCount + 1;


    /*
    |--------------------------------------------------------------------------
    | SUBSCRIPTION STATUS
    |--------------------------------------------------------------------------
    */

    $subscriptionStatus =
        'active';


    /*
    |--------------------------------------------------------------------------
    | UPDATE SELLER BATTERY
    |--------------------------------------------------------------------------
    */

    $updateStmt =
        $conn->prepare(
            "
            UPDATE users
            SET
                subscription_status = ?,
                subscription_started_at = ?,
                subscription_expires_at = ?,
                economic_period_count = ?,
                updated_at = NOW()
            WHERE user_id = ?
            "
        );


    if (!$updateStmt) {

        throw new Exception(
            'Unable to prepare subscription update.'
        );

    }


    $subscriptionStartSql =
        $subscriptionStart
            ->format('Y-m-d H:i:s');


    $subscriptionExpiresSql =
        $subscriptionExpires
            ->format('Y-m-d H:i:s');


    $updateStmt->bind_param(
        "sssii",
        $subscriptionStatus,
        $subscriptionStartSql,
        $subscriptionExpiresSql,
        $newPeriodCount,
        $userId
    );


    if (
        !$updateStmt->execute()
    ) {

        throw new Exception(
            'Unable to activate battery subscription.'
        );

    }


    $updateStmt->close();


    /*
    |--------------------------------------------------------------------------
    | SAVE FINANCIAL TRANSACTION
    |--------------------------------------------------------------------------
    |
    | source_type:
    |     battery_recharge
    |
    | source_id:
    |     Daraja CheckoutRequestID
    |
    | payer_id:
    |     seller/user who paid
    |
    | receiver_id:
    |     NULL because Makethub system receives the payment
    |
    | reference_code:
    |     actual M-Pesa receipt number
    |
    |--------------------------------------------------------------------------
    */

    $transactionType =
        'subscription_recharge';

    $currency =
        'KES';

    $paymentMethod =
        'mpesa';

    $transactionStatus =
        'completed';

    $sourceType =
        'battery_recharge';

    $sourceId =
        $checkoutRequestId;

    $description =
        'Makethub seller battery recharge - M-Pesa receipt ' .
        $mpesaReceipt;


    $insertStmt =
        $conn->prepare(
            "
            INSERT INTO financial_transactions
            (
                source_type,
                source_id,
                wallet_id,
                payer_id,
                receiver_id,
                transaction_type,
                amount,
                currency,
                payment_method,
                status,
                reference_code,
                description,
                created_at,
                updated_at
            )
            VALUES
            (
                ?,
                ?,
                NULL,
                ?,
                NULL,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                NOW(),
                NOW()
            )
            "
        );


    if (!$insertStmt) {

        throw new Exception(
            'Unable to prepare financial transaction.'
        );

    }


    $insertStmt->bind_param(
        "ssisdsssss",
        $sourceType,
        $sourceId,
        $userId,
        $transactionType,
        $amountPaid,
        $currency,
        $paymentMethod,
        $transactionStatus,
        $mpesaReceipt,
        $description
    );


    if (
        !$insertStmt->execute()
    ) {

        throw new Exception(
            'Unable to record financial transaction.'
        );

    }


    $transactionId =
        $conn->insert_id;


    $insertStmt->close();


    /*
    |--------------------------------------------------------------------------
    | COMMIT
    |--------------------------------------------------------------------------
    */

    $conn->commit();


    /*
    |--------------------------------------------------------------------------
    | LOG SUCCESS
    |--------------------------------------------------------------------------
    */

    error_log(
        'Makethub battery recharge successful. ' .
        'UserID=' . $userId .
        ' Amount=' . $amountPaid .
        ' Receipt=' . $mpesaReceipt .
        ' CheckoutRequestID=' . $checkoutRequestId .
        ' TransactionID=' . $transactionId .
        ' NewExpiry=' . $subscriptionExpiresSql
    );


    /*
    |--------------------------------------------------------------------------
    | SUCCESS RESPONSE
    |--------------------------------------------------------------------------
    */

    callbackResponse(
        true,
        'Battery subscription activated successfully.',
        [
            'transaction_id' =>
                $transactionId,

            'user_id' =>
                $userId,

            'amount' =>
                $amountPaid,

            'mpesa_receipt' =>
                $mpesaReceipt,

            'subscription_expires_at' =>
                $subscriptionExpiresSql,

            'economic_period_count' =>
                $newPeriodCount
        ]
    );


} catch (Throwable $e) {

    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    */

    if (
        $conn->errno ||
        $conn->connect_errno === 0
    ) {

        /*
         * rollback() is safe here even if the transaction
         * has already failed.
         */

        $conn->rollback();

    }


    error_log(
        'Makethub M-Pesa callback ERROR: ' .
        $e->getMessage() .
        ' | CheckoutRequestID=' .
        $checkoutRequestId .
        ' | UserID=' .
        $userId
    );


    callbackResponse(
        false,
        'Payment was received, but the subscription could not be updated automatically. Please contact Makethub support.'
    );

}