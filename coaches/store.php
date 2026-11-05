<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $photoName = null;

    // مدیریت آپلود عکس مربی
    if (!empty($_FILES['photo']['name'])) {
        $uploadDir = '../uploads/coaches/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExt = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExt, $allowedExts)) {
            $photoName = 'coach_' . time() . '_' . uniqid() . '.' . $fileExt;
            move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photoName);
        }
    }

    if (!empty($fullName) && !empty($phone)) {
        $stmt = $pdo->prepare("INSERT INTO coaches (full_name, phone, specialization, photo, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$fullName, $phone, $specialization, $photoName, $status]);
    }

    header('Location: index.php?msg=added');
    exit;
}