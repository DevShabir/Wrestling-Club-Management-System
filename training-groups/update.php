<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $level = $_POST['level'] ?? 'beginner';
    $coachId = !empty($_POST['coach_id']) ? (int)$_POST['coach_id'] : null;
    $days = $_POST['days'] ?? 'odd';
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    $status = $_POST['status'] ?? 'active';

    if ($id > 0 && !empty($name) && !empty($startTime) && !empty($endTime)) {
        $stmt = $pdo->prepare("UPDATE training_groups SET name = ?, level = ?, coach_id = ?, days = ?, start_time = ?, end_time = ?, status = ? WHERE id = ?");
        $stmt->execute([$name, $level, $coachId, $days, $startTime, $endTime, $status, $id]);
    }

    header('Location: index.php?msg=updated');
    exit;
}