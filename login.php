<?php
session_start();
require_once 'config/database.php';

if (isset($_GET['lang']) && in_array($_GET['lang'], ['fa', 'en'], true)) {
    $_SESSION['language'] = $_GET['lang'];
}
$isEnglish = ($_SESSION['language'] ?? 'fa') === 'en';
$languageUrl = 'login.php?' . http_build_query(['lang' => $isEnglish ? 'fa' : 'en']);

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && $password === $user['password']) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['role']      = $user['role'] ?? 'admin';

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'نام کاربری یا رمز عبور اشتباه است.';
        }
    } else {
        $error = 'لطفاً نام کاربری و رمز عبور را وارد کنید.';
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $isEnglish ? 'en' : 'fa'; ?>" dir="<?php echo $isEnglish ? 'ltr' : 'rtl'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به سیستم | کلپ ورزشی پهلوانی</title>
    <style>
        @font-face {
            font-family: Vazirmatn;
            src: url('./assets/fonts/Yekan.ttf') format('truetype');
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Vazirmatn';
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.25), transparent 45%),
                radial-gradient(circle at bottom left, rgba(234, 179, 8, 0.15), transparent 40%),
                linear-gradient(135deg, rgba(10, 15, 29, 0.85), rgba(15, 23, 42, 0.95)),
                url('assets/images/wrestling-bg.jpg') no-repeat center center fixed;
            background-size: cover;
            padding: 20px;
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
            position: relative;
        }

        /* لایه نئونی دور کادر */
        .auth-container::before {
            content: '';
            position: absolute;
            inset: -2px;
            background: linear-gradient(135deg, #2563eb, #fbbf24, #1d4ed8);
            border-radius: 26px;
            z-index: 0;
            opacity: 0.5;
            filter: blur(8px);
        }

        .auth-box {
            position: relative;
            z-index: 1;
            background: rgba(17, 24, 39, 0.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
            padding: 40px 32px 35px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
        }

        .language-switch {
            display: block;
            width: fit-content;
            margin: 0 auto 16px;
            padding: 6px 12px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 8px;
            color: #ffffff;
            text-decoration: none;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-emblem {
            width: 76px;
            height: 76px;
            margin: 0 auto 16px;
            background: linear-gradient(145deg, rgba(37, 99, 235, 0.3), rgba(251, 191, 36, 0.15));
            border: 2px solid rgba(251, 191, 36, 0.6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 25px rgba(251, 191, 36, 0.25);
        }

        .logo-emblem svg {
            width: 44px;
            height: 44px;
            fill: #fbbf24;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.5));
        }

        .brand-title {
            color: #ffffff;
            font-size: 1.45rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }

        .brand-subtitle {
            color: #94a3b8;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .input-group-custom {
            margin-bottom: 20px;
        }

        .input-label {
            display: block;
            color: #cbd5e1;
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            right: 14px;
            width: 20px;
            height: 20px;
            fill: #64748b;
            transition: fill 0.25s;
            pointer-events: none;
        }

        .custom-input {
            width: 100%;
            background: rgba(30, 41, 59, 0.7);
            border: 1.5px solid rgba(71, 85, 105, 0.6);
            border-radius: 14px;
            padding: 13px 44px 13px 16px;
            color: #f8fafc;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.25s ease;
        }

        .custom-input::placeholder {
            color: #64748b;
        }

        .custom-input:focus {
            background: rgba(30, 41, 59, 0.95);
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
        }

        .custom-input:focus+.input-icon {
            fill: #3b82f6;
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            border: none;
            border-radius: 14px;
            padding: 14px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 10px;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.5);
            transition: all 0.25s ease;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            box-shadow: 0 12px 30px -5px rgba(37, 99, 235, 0.65);
            transform: translateY(-2px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .error-badge {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #fca5a5;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 0.85rem;
            text-align: center;
            margin-bottom: 22px;
        }

        .footer-note {
            text-align: center;
            margin-top: 26px;
            color: #64748b;
            font-size: 0.78rem;
        }
    </style>
    <?php if ($isEnglish): ?>
        <style>
            body, body * { font-family: Arial, Helvetica, sans-serif; }
            body { direction: ltr; text-align: left; }
            .input-icon { right: auto; left: 14px; }
            .custom-input { padding: 13px 16px 13px 44px; text-align: left; }
        </style>
    <?php endif; ?>
</head>

<body>

    <div class="auth-container">
        <div class="auth-box">
            <a href="<?php echo htmlspecialchars($languageUrl, ENT_QUOTES, 'UTF-8'); ?>" class="language-switch" lang="<?php echo $isEnglish ? 'fa' : 'en'; ?>">
                <?php echo $isEnglish ? 'فارسی' : 'English'; ?>
            </a>
            <div class="brand-header">
                <div class="logo-emblem">
                    <!-- نشان وکتور سرشاخ و زورآزمایی پهلوانی -->
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                    </svg>
                </div>
                <h1 class="brand-title">کلپ ورزشی پهلوانی</h1>
                <p class="brand-subtitle">سامانه هوشمند و یکپارچه مدیریت کلپ</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="error-badge">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="input-group-custom">
                    <label class="input-label" for="username">نام کاربری</label>
                    <div class="input-wrapper">
                        <input type="text" id="username" name="username" class="custom-input" placeholder="نام کاربری تان را وارد کنید" required autofocus>
                        <svg class="input-icon" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                        </svg>
                    </div>
                </div>

                <div class="input-group-custom">
                    <label class="input-label" for="password">رمز عبور</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" class="custom-input" placeholder="رمز عبور تان را وارد کنید" required>
                        <svg class="input-icon" viewBox="0 0 24 24">
                            <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z" />
                        </svg>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    ورود به پنل مدیریت
                </button>
            </form>

            <div class="footer-note">
                نسخه اختصاصی آفلاین - باشگاه ورزشی پهلوانی
            </div>
        </div>
    </div>

    <?php if ($isEnglish): ?>
        <script src="assets/js/translations-en.js"></script>
    <?php endif; ?>
</body>

</html>