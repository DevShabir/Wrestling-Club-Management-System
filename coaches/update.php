<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $status = $_POST['status'] ?? 'active';

    if ($id > 0 && !empty($fullName) && !empty($phone)) {
        // بررسی آپلود عکس جدید
        if (!empty($_FILES['photo']['name'])) {
            $uploadDir = '../uploads/coaches/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExt = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($fileExt, $allowedExts)) {
                // حذف عکس قبلی
                $stmtOld = $pdo->prepare("SELECT photo FROM coaches WHERE id = ?");
                $stmtOld->execute([$id]);
                $oldCoach = $stmtOld->fetch();
                if ($oldCoach && !empty($oldCoach['photo'])) {
                    $oldPath = $uploadDir . $oldCoach['photo'];
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $newPhoto = 'coach_' . time() . '_' . uniqid() . '.' . $fileExt;
                move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $newPhoto);

                $stmt = $pdo->prepare("UPDATE coaches SET full_name = ?, phone = ?, specialization = ?, status = ?, photo = ? WHERE id = ?");
                $stmt->execute([$fullName, $phone, $specialization, $status, $newPhoto, $id]);
            }
        } else {
            // ویرایش بدون تغییر عکس
            $stmt = $pdo->prepare("UPDATE coaches SET full_name = ?, phone = ?, specialization = ?, status = ? WHERE id = ?");
            $stmt->execute([$fullName, $phone, $specialization, $status, $id]);
        }
    }

    header('Location: index.php?msg=updated');
    exit;
}