<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shamsiDate = trim($_POST['shamsi_date'] ?? '');
    $groupId = isset($_POST['group_id']) ? (int)$_POST['group_id'] : 0;
    $attendanceData = $_POST['attendance'] ?? []; // ساختار: [member_id => 'present'|'absent']

    if (!empty($shamsiDate) && $groupId > 0) {
        // حذف رکوردهای ثبت‌شده قبلی برای این تاریخ و این شاگردان (جهت جلوگیری از ثبت تکراری)
        $memberIds = array_keys($attendanceData);
        if (!empty($memberIds)) {
            $inQuery = implode(',', array_fill(0, count($memberIds), '?'));
            $deleteStmt = $pdo->prepare("DELETE FROM attendances WHERE shamsi_date = ? AND member_id IN ($inQuery)");
            $deleteStmt->execute(array_merge([$shamsiDate], $memberIds));
        }

        // ثبت وضعیت جدید شاگردان
        $insertStmt = $pdo->prepare("INSERT INTO attendances (member_id, shamsi_date, status) VALUES (?, ?, ?)");
        foreach ($attendanceData as $memberId => $status) {
            $statusVal = ($status === 'absent') ? 'absent' : 'present';
            $insertStmt->execute([(int)$memberId, $shamsiDate, $statusVal]);
        }
    }

    header("Location: index.php?group_id=$groupId&date=" . urlencode($shamsiDate) . "&msg=saved");
    exit;
}