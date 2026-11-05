<?php
require_once '../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // حذف تصویر شاگرد
    $stmt = $pdo->prepare("SELECT photo FROM members WHERE id = ?");
    $stmt->execute([$id]);
    $member = $stmt->fetch();

    if ($member && !empty($member['photo'])) {
        $filePath = '../uploads/members/' . $member['photo'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // حذف رکورد
    $delStmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
    $delStmt->execute([$id]);
}

header('Location: index.php?msg=deleted');
exit;