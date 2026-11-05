<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['fa', 'en'], true)) {
    $_SESSION['language'] = $_GET['lang'];
}
$currentLanguage = $_SESSION['language'] ?? 'fa';
$isEnglish = $currentLanguage === 'en';

// تشخیص عمق پوشه جهت روت‌دهی داینامیک و دقیق
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
$basePath = ($currentDir === 'wrestling-club' || $currentDir === 'htdocs' || $currentDir === 'www') ? '' : '../';

// بررسی ورود و احراز هویت
if (!isset($_SESSION['user_id'])) {
    header("Location: " . $basePath . "login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $isEnglish ? 'en' : 'fa-AF'; ?>" dir="<?php echo $isEnglish ? 'ltr' : 'rtl'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت کلپ پهلوانی و کشتی</title>

    <!-- Bootstrap 5 RTL CSS -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/bootstrap.rtl.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/bootstrap-icons.css">

    <!-- Custom CSS همراه با کنترل کش -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <?php if ($isEnglish): ?>
        <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/ltr.css">
    <?php endif; ?>
</head>
<body class="<?php echo $isEnglish ? 'language-en' : 'language-fa'; ?>">