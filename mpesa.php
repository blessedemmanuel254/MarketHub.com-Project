<?php
require_once "daraja.php";

$daraja = new Daraja();

$action = $_GET['action'] ?? '';

/* ========================
   STK PUSH REQUEST
======================== */
if ($action == "pay") {
    $phone = $_POST['phone'];
    $amount = $_POST['amount'];

    echo $daraja->stkPush($phone, $amount, "ORDER123");
}

/* ========================
   B2C WITHDRAWAL
======================== */
if ($action == "withdraw") {
  $phone = $_POST['phone'];
  $amount = $_POST['amount'];

  echo $daraja->b2c($phone, $amount);
}

/* ========================
   STK CALLBACK
======================== */
if ($action == "callback") {
    $data = file_get_contents("php://input");
    file_put_contents("stk_log.json", $data);

    // decode and update DB here
}

/* ========================
   B2C CALLBACK
======================== */
if ($action == "b2c_callback") {
    $data = file_get_contents("php://input");
    file_put_contents("b2c_log.json", $data);

    // update withdrawal status
}

/* ========================
   TIMEOUT HANDLER
======================== */
if ($action == "timeout") {
  $data = file_get_contents("php://input");
  file_put_contents("timeout_log.json", $data);
}