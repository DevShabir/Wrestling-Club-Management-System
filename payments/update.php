<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id              = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $memberId        = isset($_POST['member_id']) ? (int)$_POST['member_id'] : 0;
    $paymentType     = $_POST['payment_type'] ?? 'monthly';
    $admissionAmount = !empty($_POST['admission_amount']) ? (float)$_POST['admission_amount'] : 0.00;
    $monthlyAmount   = !empty($_POST['monthly_amount']) ? (float)$_POST['monthly_amount'] : 0.00;
    $discountAmount  = !empty($_POST['discount_amount']) ? (float)$_POST['discount_amount'] : 0.00;
    $shamsiMonth     = $_POST['shamsi_month'] ?? '06';
    $shamsiYear      = $_POST['shamsi_year'] ?? '1405';
    $paymentDate     = trim($_POST['shamsi_payment_date'] ?? '');
    $note            = trim($_POST['note'] ?? '');

    $totalPaid = ($admissionAmount + $monthlyAmount) - $discountAmount;
    if ($totalPaid < 0) $totalPaid = 0;

    if ($id > 0 && $memberId > 0 && !empty($paymentDate)) {
        $stmt = $pdo->prepare("UPDATE payments SET 
            member_id = ?, payment_type = ?, admission_amount = ?, 
            monthly_amount = ?, discount_amount = ?, total_paid = ?, 
            shamsi_month = ?, shamsi_year = ?, shamsi_payment_date = ?, note = ? 
            WHERE id = ?");
        $stmt->execute([
            $memberId, $paymentType, $admissionAmount, $monthlyAmount,
            $discountAmount, $totalPaid, $shamsiMonth, $shamsiYear, $paymentDate, $note, $id
        ]);
    }

    header('Location: index.php?msg=updated');
    exit;
}