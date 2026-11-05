<?php
require_once '../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // دریافت نام عکس برای پاک کردن از سرور
    $stmt = $pdo->prepare("SELECT photo FROM coaches WHERE id = ?");
    $stmt->execute([$id]);
    $coach = $stmt->fetch();

    if ($coach && !empty($coach['photo'])) {
        $filePath = '../uploads/coaches/' . $coach['photo'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // حذف رکورد از دیتابیس
    $delStmt = $pdo->prepare("DELETE FROM coaches WHERE id = ?");
    $delStmt->execute([$id]);
}

header('Location: index.php?msg=deleted');
exit;