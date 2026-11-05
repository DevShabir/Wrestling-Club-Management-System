<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $level = $_POST['level'] ?? 'beginner';
    $coachId = !empty($_POST['coach_id']) ? (int)$_POST['coach_id'] : null;
    $days = $_POST['days'] ?? 'odd';
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    $status = $_POST['status'] ?? 'active';

    if (!empty($name) && !empty($startTime) && !empty($endTime)) {
        $stmt = $pdo->prepare("INSERT INTO training_groups (name, level, coach_id, days, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $level, $coachId, $days, $startTime, $endTime, $status]);
    }

    header('Location: index.php?msg=added');
    exit;
}