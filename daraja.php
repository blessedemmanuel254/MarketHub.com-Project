<?php

class Daraja {

  private $consumerKey = "4xH6kSn3yxLj3HxNMctGUTwYLLFec6vD4PknjioTqitdcHC3";
  private $consumerSecret = "tZkYAdAHuKm7L86c7pzG3mdVARJqwcMly89fPASc4JY1gpQqjOmwHZJu9arqlBV9";
  private $shortcode = "174379"; // sandbox default
  private $passkey = "bfb279f9aa9bdbcf158bf6f5a7b8c4b93c4e4e9b3e3f3f1f7f7c4b93c4e4e9b";

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
          "CallBackURL" => "https://makethub.shop/mpesa.php?action=callback",
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
          "QueueTimeOutURL" => "https://makethub.shop/mpesa.php?action=timeout",
          "ResultURL" => "https://makethub.shop/mpesa.php?action=b2c_callback",
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