<?php

class Daraja {

    private $consumerKey = "YOUR_CONSUMER_KEY";
    private $consumerSecret = "YOUR_CONSUMER_SECRET";
    private $shortcode = "174379"; // sandbox default
    private $passkey = "YOUR_PASSKEY";

    public function getAccessToken() {
        $credentials = base64_encode($this->consumerKey . ":" . $this->consumerSecret);

        $url = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $data = json_decode($response);

        return $data->access_token;
    }

    public function stkPush($phone, $amount, $reference) {
        $token = $this->getAccessToken();

        $timestamp = date("YmdHis");
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $url = "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest";

        $payload = [
            "BusinessShortCode" => $this->shortcode,
            "Password" => $password,
            "Timestamp" => $timestamp,
            "TransactionType" => "CustomerPayBillOnline",
            "Amount" => $amount,
            "PartyA" => $phone,
            "PartyB" => $this->shortcode,
            "PhoneNumber" => $phone,
            "CallBackURL" => "https://yourdomain.com/mpesa.php?action=callback",
            "AccountReference" => $reference,
            "TransactionDesc" => "Payment"
        ];

        return $this->sendRequest($url, $token, $payload);
    }

    public function b2c($phone, $amount) {
        $token = $this->getAccessToken();

        $url = "https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest";

        $payload = [
            "InitiatorName" => "testapi",
            "SecurityCredential" => "YOUR_SECURITY_CREDENTIAL",
            "CommandID" => "BusinessPayment",
            "Amount" => $amount,
            "PartyA" => $this->shortcode,
            "PartyB" => $phone,
            "Remarks" => "Withdrawal",
            "QueueTimeOutURL" => "https://yourdomain.com/mpesa.php?action=timeout",
            "ResultURL" => "https://yourdomain.com/mpesa.php?action=b2c_callback",
            "Occasion" => "Withdrawal"
        ];

        return $this->sendRequest($url, $token, $payload);
    }

    private function sendRequest($url, $token, $payload) {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ]);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return curl_exec($ch);
    }
}