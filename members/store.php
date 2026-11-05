<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName   = trim($_POST['full_name'] ?? '');
    $fatherName = trim($_POST['father_name'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $birthDay   = $_POST['birth_day'] ?? null;
    $birthMonth = $_POST['birth_month'] ?? null;
    $birthYear  = $_POST['birth_year'] ?? null;
    $weight     = !empty($_POST['weight']) ? (float)$_POST['weight'] : null;
    $groupId    = !empty($_POST['group_id']) ? (int)$_POST['group_id'] : null;
    $status     = $_POST['status'] ?? 'active';
    
    // تاریخ ثبت نام به تاریخ امروز
    $registeredAt = date('Y-m-d');
    $photoName = null;

    // آپلود عکس شاگرد در صورت ارسال
    if (!empty($_FILES['photo']['name'])) {
        $uploadDir = '../uploads/members/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExt = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExt, $allowedExts)) {
            $photoName = 'member_' . time() . '_' . uniqid() . '.' . $fileExt;
            move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photoName);
        }
    }

    if (!empty($fullName) && !empty($phone)) {
        // ایجاد اتوماتیک کد عضویت یکتا (مثلاً W-1001)
        $lastMember = $pdo->query("SELECT id FROM members ORDER BY id DESC LIMIT 1")->fetch();
        $nextId = $lastMember ? ((int)$lastMember['id'] + 1) : 1;
        $memberCode = 'W-' . (1000 + $nextId);

        $stmt = $pdo->prepare("INSERT INTO members 
            (member_code, full_name, father_name, phone, birth_day, birth_month, birth_year, weight, photo, group_id, status, registered_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
        $stmt->execute([
            $memberCode, $fullName, $fatherName, $phone, 
            $birthDay, $birthMonth, $birthYear, $weight, 
            $photoName, $groupId, $status, $registeredAt
        ]);
    }

    header('Location: index.php?msg=added');
    exit;
}