<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['sql_file'])) {
    if ($_FILES['sql_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['sql_file']['tmp_name'];
        $fileName = $_FILES['sql_file']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileExt === 'sql') {
            $sqlContent = file_get_contents($fileTmpPath);

            try {
                $pdo->exec($sqlContent);
                header('Location: index.php?msg=restored');
                exit;
            } catch (PDOException $e) {
                header('Location: index.php?error=db_error');
                exit;
            }
        } else {
            header('Location: index.php?error=invalid_file');
            exit;
        }
    }
}

header('Location: index.php?error=no_file');
exit;