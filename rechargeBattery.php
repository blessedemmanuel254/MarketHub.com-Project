<?php

/*
|--------------------------------------------------------------------------
| MAKETHUB - SELLER BATTERY RECHARGE
|--------------------------------------------------------------------------
|
| This file is called by JavaScript using fetch().
|
| It does NOT display a page.
| It returns JSON.
|
*/


/*
|--------------------------------------------------------------------------
| SESSION
|--------------------------------------------------------------------------
|
| We need the existing seller session so we can identify the seller.
|
*/

session_start();

header('Content-Type: application/json; charset=utf-8');


/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

require_once 'connection.php';


/*
|--------------------------------------------------------------------------
| GET LOGGED-IN USER
|--------------------------------------------------------------------------
|
| sellerPage.php already creates the session.
| This request uses the same PHP session cookie.
|
*/

if (!isset($_SESSION['user_id'])) {

    echo json_encode([
        'success' => false,
        'message' => 'Your session has expired. Please log in again.'
    ]);

    exit;
}


$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| GET SELLER INFORMATION
|--------------------------------------------------------------------------
|
| We do not need to repeat the entire sellerPage.php
| authentication system here.
|
| We only retrieve the information required for
| calculating and initiating the recharge.
|
*/

$stmt = $conn->prepare("
    SELECT
        account_type,
        phone,
        subscription_status,
        subscription_started_at,
        subscription_expires_at,
        economic_period_count
    FROM users
    WHERE user_id = ?
    LIMIT 1
");


if (!$stmt) {

    echo json_encode([
        'success' => false,
        'message' => 'Unable to access your account information.'
    ]);

    exit;
}


$stmt->bind_param(
    "i",
    $user_id
);


$stmt->execute();


$result = $stmt->get_result();


$seller = $result->fetch_assoc();


$stmt->close();


/*
|--------------------------------------------------------------------------
| VERIFY SELLER
|--------------------------------------------------------------------------
*/

if (
    !$seller ||
    $seller['account_type'] !== 'seller'
) {

    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized account.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| SELLER PHONE NUMBER
|--------------------------------------------------------------------------
|
| Phone numbers are stored encoded in the users table.
| Decode the stored value before using it with M-Pesa.
|
*/

$encodedPhone =
    trim(
        $seller['phone'] ?? ''
    );


if (empty($encodedPhone)) {

    echo json_encode([
        'success' => false,
        'message' => 'Your M-Pesa phone number is missing from your account.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| DECODE STORED PHONE NUMBER
|--------------------------------------------------------------------------
*/

function decodePhone($encodedPhone) {
  if (empty($encodedPhone)) {
      return '';
  }

  $decoded = base64_decode($encodedPhone, true);

  // If decoding fails, return original safely
  if ($decoded === false) {
      return htmlspecialchars($encodedPhone, ENT_QUOTES, 'UTF-8');
  }

  return htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8');
}

$phone =
    decodePhone(
        $encodedPhone
    );


$phone =
    trim(
        $phone ?? ''
    );


if (empty($phone)) {

    error_log(
        'Makethub battery recharge: Unable to decode seller phone. ' .
        'User ID=' . $user_id
    );

    echo json_encode([
        'success' => false,
        'message' => 'Your M-Pesa phone number could not be read. Please update your phone number in your account.'
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| NORMALIZE KENYAN PHONE NUMBER
|--------------------------------------------------------------------------
|
| 0712345678
|      ↓
| 254712345678
|
| +254712345678
|      ↓
| 254712345678
|
| 254712345678
|      ↓
| 254712345678
|
*/

$phone = preg_replace(
    '/\s+/',
    '',
    $phone
);


if (
    substr($phone, 0, 1) === '+'
) {

    $phone = substr(
        $phone,
        1
    );
}


if (
    substr($phone, 0, 1) === '0'
) {

    $phone =
        '254' .
        substr($phone, 1);
}


/*
|--------------------------------------------------------------------------
| VALIDATE PHONE NUMBER
|--------------------------------------------------------------------------
*/

if (
    !preg_match(
        '/^2547\d{8}$/',
        $phone
    )
) {

    echo json_encode([
        'success' => false,
        'message' => 'Please update your account with a valid Kenyan M-Pesa number.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| CALCULATE RECHARGE AMOUNT
|--------------------------------------------------------------------------
|
| FIRST RECHARGE
| KES 200
|
| SUBSEQUENT RECHARGES
| 0.5% of previous 28-day sales
|
| MINIMUM
| KES 200
|
| MAXIMUM
| KES 999
|
*/

$periodCount =
    (int) (
        $seller['economic_period_count'] ?? 0
    );


/*
|--------------------------------------------------------------------------
| FIRST RECHARGE
|--------------------------------------------------------------------------
*/

if ($periodCount <= 0) {

    $rechargeAmount = 200;

}


/*
|--------------------------------------------------------------------------
| SUBSEQUENT RECHARGES
|--------------------------------------------------------------------------
*/

else {

    /*
    |--------------------------------------------------------------------------
    | PREVIOUS SUBSCRIPTION PERIOD
    |--------------------------------------------------------------------------
    */

    $periodStart =
        $seller['subscription_started_at'] ?? null;


    $periodEnd =
        $seller['subscription_expires_at'] ?? null;


    /*
    |--------------------------------------------------------------------------
    | VALIDATE SUBSCRIPTION DATES
    |--------------------------------------------------------------------------
    */

    if (
        empty($periodStart) ||
        empty($periodEnd)
    ) {

        echo json_encode([
            'success' => false,
            'message' => 'Your previous subscription period could not be determined.'
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | CALCULATE SALES
    |--------------------------------------------------------------------------
    |
    | Only paid and delivered seller sales
    | within the previous subscription period
    | are included.
    |
    */

    $salesStmt = $conn->prepare("
        SELECT
            COALESCE(
                SUM(oi.subtotal),
                0
            ) AS total_sales

        FROM order_items oi

        WHERE oi.seller_id = ?

          AND oi.payment_status = 'paid'

          AND oi.order_status = 'delivered'

          AND oi.delivered_at >= ?

          AND oi.delivered_at < ?
    ");


    if (!$salesStmt) {

        echo json_encode([
            'success' => false,
            'message' => 'Unable to calculate your previous sales.'
        ]);

        exit;
    }


    $salesStmt->bind_param(
        "iss",
        $user_id,
        $periodStart,
        $periodEnd
    );


    $salesStmt->execute();


    $salesResult =
        $salesStmt
        ->get_result()
        ->fetch_assoc();


    $salesStmt->close();


    /*
    |--------------------------------------------------------------------------
    | TOTAL SALES
    |--------------------------------------------------------------------------
    */

    $totalSales =
        (float) (
            $salesResult['total_sales'] ?? 0
        );


    /*
    |--------------------------------------------------------------------------
    | 0.5% RECHARGE
    |--------------------------------------------------------------------------
    */

    $rechargeAmount =
        $totalSales * 0.005;


    /*
    |--------------------------------------------------------------------------
    | MINIMUM KES 200
    |--------------------------------------------------------------------------
    */

    if (
        $rechargeAmount < 200
    ) {

        $rechargeAmount = 200;
    }


    /*
    |--------------------------------------------------------------------------
    | MAXIMUM KES 999
    |--------------------------------------------------------------------------
    */

    if (
        $rechargeAmount > 999
    ) {

        $rechargeAmount = 999;
    }


    /*
    |--------------------------------------------------------------------------
    | ROUND
    |--------------------------------------------------------------------------
    */

    $rechargeAmount =
        round(
            $rechargeAmount,
            2
        );
}


/*
|--------------------------------------------------------------------------
| M-PESA AMOUNT
|--------------------------------------------------------------------------
|
| STK Push amount is sent as a whole-number KES amount.
|
*/

$mpesaAmount =
    (int) ceil(
        $rechargeAmount
    );


/*
|--------------------------------------------------------------------------
| DARAJA CONFIGURATION
|--------------------------------------------------------------------------
|
| IMPORTANT:
| Keep these credentials ONLY on the server.
|
| Do not place them in JavaScript.
|
*/


$consumerKey =
    '4xH6kSn3yxLj3HxNMctGUTwYLLFec6vD4PknjioTqitdcHC3';


$consumerSecret =
    'tZkYAdAHuKm7L86c7pzG3mdVARJqwcMly89fPASc4JY1gpQqjOmwHZJu9arqlBV9';


$shortCode =
    '174379';


$passkey =
    'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';


/*
|--------------------------------------------------------------------------
| CALLBACK URL
|--------------------------------------------------------------------------
|
| Safaricom will send the final STK Push result here.
|
*/

$callbackUrl =
    'https://makethub.shop/mpesa/rechargeCallback.php';


/*
|--------------------------------------------------------------------------
| DARAJA ENVIRONMENT
|--------------------------------------------------------------------------
|
| SANDBOX:
| https://sandbox.safaricom.co.ke
|
| PRODUCTION:
| https://api.safaricom.co.ke
|
*/

$darajaBaseUrl =
    'https://sandbox.safaricom.co.ke';


/*
|--------------------------------------------------------------------------
| GET DARAJA ACCESS TOKEN
|--------------------------------------------------------------------------
*/

$credentials =
    base64_encode(
        $consumerKey .
        ':' .
        $consumerSecret
    );


$ch = curl_init(
    $darajaBaseUrl .
    '/oauth/v1/generate?grant_type=client_credentials'
);


curl_setopt_array(
    $ch,
    [

        CURLOPT_HTTPHEADER => [

            'Authorization: Basic ' .
            $credentials

        ],

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_TIMEOUT => 30

    ]
);


$tokenResponse =
    curl_exec($ch);


$tokenHttpCode =
    curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );


$tokenCurlError =
    curl_error($ch);


curl_close($ch);


/*
|--------------------------------------------------------------------------
| TOKEN CONNECTION ERROR
|--------------------------------------------------------------------------
*/

if (
    $tokenResponse === false ||
    !empty($tokenCurlError)
) {

    error_log(
        'M-Pesa TOKEN CURL ERROR: ' .
        $tokenCurlError
    );

    echo json_encode([
        'success' => false,
        'message' => 'Unable to connect to M-Pesa.',
        'debug' => $tokenCurlError
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| DECODE TOKEN RESPONSE
|--------------------------------------------------------------------------
*/

$tokenData =
    json_decode(
        $tokenResponse,
        true
    );


/*
|--------------------------------------------------------------------------
| TOKEN ERROR
|--------------------------------------------------------------------------
*/

if (
    $tokenHttpCode !== 200 ||
    empty($tokenData['access_token'])
) {

    error_log(
        'M-Pesa TOKEN ERROR: HTTP=' .
        $tokenHttpCode .
        ' RESPONSE=' .
        $tokenResponse
    );

    echo json_encode([
        'success' => false,
        'message' => 'Unable to authenticate with M-Pesa.',
        'http_code' => $tokenHttpCode,
        'daraja_response' => $tokenData
    ]);

    exit;
}


$accessToken =
    $tokenData['access_token'];


/*
|--------------------------------------------------------------------------
| GENERATE STK PASSWORD
|--------------------------------------------------------------------------
*/

$timestamp =
    date('YmdHis');


$password =
    base64_encode(
        $shortCode .
        $passkey .
        $timestamp
    );


/*
|--------------------------------------------------------------------------
| STK PUSH DATA
|--------------------------------------------------------------------------
*/

$stkData = [

    'BusinessShortCode' =>
        $shortCode,

    'Password' =>
        $password,

    'Timestamp' =>
        $timestamp,

    'TransactionType' =>
        'CustomerPayBillOnline',

    'Amount' =>
        $mpesaAmount,

    'PartyA' =>
        $phone,

    'PartyB' =>
        $shortCode,

    'PhoneNumber' =>
        $phone,

    'CallBackURL' =>
        $callbackUrl,

    'AccountReference' =>
        'MAKETHUB-' . $user_id,

    'TransactionDesc' =>
        'Makethub Store Battery Recharge'

];


/*
|--------------------------------------------------------------------------
| SEND STK PUSH
|--------------------------------------------------------------------------
*/

$ch = curl_init(
    $darajaBaseUrl .
    '/mpesa/stkpush/v1/processrequest'
);


curl_setopt_array(
    $ch,
    [

        CURLOPT_POST =>
            true,

        CURLOPT_POSTFIELDS =>
            json_encode(
                $stkData
            ),

        CURLOPT_HTTPHEADER => [

            'Authorization: Bearer ' .
            $accessToken,

            'Content-Type: application/json'

        ],

        CURLOPT_RETURNTRANSFER =>
            true,

        CURLOPT_TIMEOUT =>
            30

    ]
);


$stkResponse =
    curl_exec($ch);


$stkHttpCode =
    curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );


$stkCurlError =
    curl_error($ch);


curl_close($ch);


/*
|--------------------------------------------------------------------------
| STK CONNECTION ERROR
|--------------------------------------------------------------------------
*/

if (
    $stkResponse === false ||
    !empty($stkCurlError)
) {

    error_log(
        'M-Pesa STK CURL ERROR: ' .
        $stkCurlError
    );

    echo json_encode([
        'success' => false,
        'message' => 'Unable to contact M-Pesa.',
        'debug' => $stkCurlError
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| DECODE DARAJA RESPONSE
|--------------------------------------------------------------------------
*/

$stkResult =
    json_decode(
        $stkResponse,
        true
    );


/*
|--------------------------------------------------------------------------
| STK PUSH SUCCESSFULLY INITIATED
|--------------------------------------------------------------------------
|
| At this point Safaricom has accepted the STK Push and returned a
| CheckoutRequestID.
|
| IMPORTANT:
|
| The payment is NOT successful yet.
|
| Therefore:
|
|     status = pending
|
| The callback will later change it to:
|
|     status = completed
|
| and replace the temporary reference_code with the actual
| M-Pesa receipt number.
|
|--------------------------------------------------------------------------
*/

if (
    $stkHttpCode >= 200 &&
    $stkHttpCode < 300 &&
    !empty(
        $stkResult['CheckoutRequestID']
    )
) {

    /*
    |--------------------------------------------------------------------------
    | GET CHECKOUT REQUEST ID
    |--------------------------------------------------------------------------
    */

    $checkoutRequestId =
        trim(
            $stkResult['CheckoutRequestID']
        );


    /*
    |--------------------------------------------------------------------------
    | FINANCIAL TRANSACTION VALUES
    |--------------------------------------------------------------------------
    */

    $sourceType =
        'battery_recharge';

    $sourceId =
        $checkoutRequestId;

    $transactionType =
        'subscription_recharge';

    $currency =
        'KES';

    $paymentMethod =
        'mpesa';

    $transactionStatus =
        'pending';


    /*
    |--------------------------------------------------------------------------
    | TEMPORARY REFERENCE
    |--------------------------------------------------------------------------
    |
    | The actual M-Pesa receipt does not exist yet.
    |
    | Therefore we temporarily use CheckoutRequestID.
    |
    | rechargeCallback.php will later replace this with:
    |
    |     MpesaReceiptNumber
    |
    |--------------------------------------------------------------------------
    */

    $referenceCode =
        $checkoutRequestId;


    $description =
        'Makethub Store Battery Recharge - Payment Pending';
    /*
    |--------------------------------------------------------------------------
    | MAKETHUB ADMINISTRATOR ACCOUNT
    |--------------------------------------------------------------------------
    */

    $systemUserId = 21;

    $walletStmt = $conn->prepare("
        SELECT wallet_id
        FROM wallets
        WHERE user_id = ?
        AND wallet_type = 'administrator'
        LIMIT 1
    ");

    if (!$walletStmt) {

        error_log(
            'Battery recharge: Failed to prepare administrator wallet lookup. ' .
            $conn->error
        );

        echo json_encode([
            'success' => false,
            'message' => 'Unable to prepare the Makethub payment account.'
        ]);

        exit;
    }

    $walletStmt->bind_param(
        "i",
        $systemUserId
    );

    $walletStmt->execute();

    $walletResult = $walletStmt->get_result();

    $adminWallet = $walletResult->fetch_assoc();

    $walletStmt->close();

    if (!$adminWallet) {

        error_log(
            'Battery recharge: Administrator wallet not found. ' .
            'System User ID=' . $systemUserId
        );

        echo json_encode([
            'success' => false,
            'message' => 'Makethub payment account could not be found. Please contact support.'
        ]);

        exit;
    }

    $adminWalletId = (int) $adminWallet['wallet_id'];
    /*
    |--------------------------------------------------------------------------
    | SAVE PENDING FINANCIAL TRANSACTION
    |--------------------------------------------------------------------------
    */

    $transactionStmt = $conn->prepare("
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
            ?,
            ?,
            ?,
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
    ");
    Then:

    $currency =
        'KES';

    $paymentMethod =
        'mpesa';

    $transactionStatus =
        'pending';

    $description =
        'Makethub Store Battery Recharge - Payment Pending';




    /*
    |--------------------------------------------------------------------------
    | TRANSACTION PREPARATION ERROR
    |--------------------------------------------------------------------------
    */

    if (!$transactionStmt) {

        error_log(
            'Makethub battery recharge: Unable to prepare pending financial transaction. ' .
            'CheckoutRequestID=' .
            $checkoutRequestId .
            ' DB Error=' .
            $conn->error
        );


        echo json_encode([

            'success' =>
                false,

            'message' =>
                'M-Pesa payment was initiated, but we could not create the payment record. Please contact support before trying again.',

            'checkout_request_id' =>
                $checkoutRequestId

        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | BIND VALUES
    |--------------------------------------------------------------------------
    */

    $transactionStmt->bind_param(
        "siiiiisdssss",
        $sourceType,
        $sourceId,
        $adminWalletId,
        $user_id,
        $systemUserId,
        $transactionType,
        $mpesaAmount,
        $currency,
        $paymentMethod,
        $transactionStatus,
        $referenceCode,
        $description
    );


    /*
    |--------------------------------------------------------------------------
    | INSERT PENDING TRANSACTION
    |--------------------------------------------------------------------------
    */

    if (
        !$transactionStmt->execute()
    ) {

        error_log(
            'Makethub battery recharge: Failed to save pending financial transaction. ' .
            'CheckoutRequestID=' .
            $checkoutRequestId .
            ' DB Error=' .
            $transactionStmt->error
        );


        $transactionStmt->close();


        echo json_encode([

            'success' =>
                false,

            'message' =>
                'M-Pesa payment was initiated, but we could not save the payment record. Please contact support before trying again.',

            'checkout_request_id' =>
                $checkoutRequestId

        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | GET FINANCIAL TRANSACTION ID
    |--------------------------------------------------------------------------
    */

    $transactionId =
        $conn->insert_id;


    $transactionStmt->close();


    /*
    |--------------------------------------------------------------------------
    | RETURN SUCCESS TO JAVASCRIPT
    |--------------------------------------------------------------------------
    */

    echo json_encode([

        'success' =>
            true,

        'message' =>
            'M-Pesa payment request sent. Please enter your M-Pesa PIN on your phone.',

        'amount' =>
            $mpesaAmount,

        'checkout_request_id' =>
            $checkoutRequestId,

        'transaction_id' =>
            $transactionId

    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| STK PUSH FAILED
|--------------------------------------------------------------------------
*/
echo json_encode([
    'success' => false,
    'message' =>
        $stkResult['errorMessage']
        ??
        $stkResult['ResponseDescription']
        ??
        'Unable to initiate M-Pesa payment.',
    'http_code' => $stkHttpCode,
    'daraja_response' => $stkResult
]);
exit;