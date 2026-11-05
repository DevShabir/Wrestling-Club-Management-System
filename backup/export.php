<?php
require_once '../config/database.php';

$tables = [];
$result = $pdo->query("SHOW TABLES");
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}

$sqlScript = "-- نسخه پشتیبان دیتابیس کلپ ورزشی پهلوانی\n";
$sqlScript .= "-- تاریخ تهیه بک‌آپ: " . date('Y-m-d H:i:s') . "\n\n";
$sqlScript .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

foreach ($tables as $table) {
    // ساختار جدول
    $query = "SHOW CREATE TABLE `$table`";
    $stmt = $pdo->query($query);
    $row = $stmt->fetch(PDO::FETCH_NUM);
    
    $sqlScript .= "DROP TABLE IF EXISTS `$table`;\n";
    $sqlScript .= $row[1] . ";\n\n";

    // داده‌های جدول
    $stmtData = $pdo->query("SELECT * FROM `$table`");
    $columnCount = $stmtData->columnCount();

    while ($row = $stmtData->fetch(PDO::FETCH_NUM)) {
        $sqlScript .= "INSERT INTO `$table` VALUES(";
        for ($j = 0; $j < $columnCount; $j++) {
            if (isset($row[$j])) {
                $sqlScript .= $pdo->quote($row[$j]);
            } else {
                $sqlScript .= "NULL";
            }
            if ($j < ($columnCount - 1)) {
                $sqlScript .= ', ';
            }
        }
        $sqlScript .= ");\n";
    }
    $sqlScript .= "\n";
}

$sqlScript .= "SET FOREIGN_KEY_CHECKS=1;\n";

// ارسال فایل برای دانلود در مرورگر
$backupFilename = 'wrestling_club_backup_' . date('Y-m-d_H-i') . '.sql';
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $backupFilename . "\"");
echo $sqlScript;
exit;