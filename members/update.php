<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $fullName   = trim($_POST['full_name'] ?? '');
    $fatherName = trim($_POST['father_name'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $birthDay   = $_POST['birth_day'] ?? null;
    $birthMonth = $_POST['birth_month'] ?? null;
    $birthYear  = $_POST['birth_year'] ?? null;
    $weight     = !empty($_POST['weight']) ? (float)$_POST['weight'] : null;
    $groupId    = !empty($_POST['group_id']) ? (int)$_POST['group_id'] : null;
    $status     = $_POST['status'] ?? 'active';

    if ($id > 0 && !empty($fullName) && !empty($phone)) {
        if (!empty($_FILES['photo']['name'])) {
            $uploadDir = '../uploads/members/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $fileExt = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileExt, $allowedExts)) {
                $stmtOld = $pdo->prepare("SELECT photo FROM members WHERE id = ?");
                $stmtOld->execute([$id]);
                $old = $stmtOld->fetch();
                if ($old && !empty($old['photo']) && file_exists($uploadDir . $old['photo'])) {
                    unlink($uploadDir . $old['photo']);
                }

                $newPhoto = 'member_' . time() . '_' . uniqid() . '.' . $fileExt;
                move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $newPhoto);

                $stmt = $pdo->prepare("UPDATE members SET full_name=?, father_name=?, phone=?, birth_day=?, birth_month=?, birth_year=?, weight=?, group_id=?, status=?, photo=? WHERE id=?");
                $stmt->execute([$fullName, $fatherName, $phone, $birthDay, $birthMonth, $birthYear, $weight, $groupId, $status, $newPhoto, $id]);
            }
        } else {
            $stmt = $pdo->prepare("UPDATE members SET full_name=?, father_name=?, phone=?, birth_day=?, birth_month=?, birth_year=?, weight=?, group_id=?, status=? WHERE id=?");
            $stmt->execute([$fullName, $fatherName, $phone, $birthDay, $birthMonth, $birthYear, $weight, $groupId, $status, $id]);
        }
    }

    header('Location: index.php?msg=updated');
    exit;
}