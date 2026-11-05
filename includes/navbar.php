<?php
$isEnglish = $isEnglish ?? (($_SESSION['language'] ?? 'fa') === 'en');

// دریافت اطلاعات به‌روز ادمین
$navUserId   = $_SESSION['user_id'] ?? 0;
$navFullName = 'مدیر عمومی';
$navRole     = 'مدیر عمومی';
$navPhoto    = null;

if ($navUserId > 0 && isset($pdo)) {
    $photoColumn = $pdo->query("SHOW COLUMNS FROM users LIKE 'photo'")->fetch();
    $userColumns = $photoColumn ? 'full_name, username, role, photo' : 'full_name, username, role';
    $navStmt = $pdo->prepare("SELECT $userColumns FROM users WHERE id = ?");
    $navStmt->execute([$navUserId]);
    $navUser = $navStmt->fetch();

    if ($navUser) {
        $navFullName = $navUser['full_name'] ?? 'مدیر عمومی';
        $navRole     = ($navUser['role'] ?? 'admin') === 'admin' ? 'مدیر عمومی' : 'کاربر سیستم';
        $navPhoto    = $navUser['photo'] ?? null;
    }
}

// محاسبه تاریخ روز هجری شمسی
require_once __DIR__ . '/jdf.php';
$todayParts = explode('-', date('Y-m-d'));
list($jy, $jm, $jd) = gregorian_to_shamsi((int)$todayParts[0], (int)$todayParts[1], (int)$todayParts[2]);

$dayNames = [
    'Sunday'    => 'یکشنبه',
    'Monday'    => 'دوشنبه',
    'Tuesday'   => 'سه‌شنبه',
    'Wednesday' => 'چهارشنبه',
    'Thursday'  => 'پنجشنبه',
    'Friday'    => 'جمعه',
    'Saturday'  => 'شنبه'
];
$dayNameAfghan = $dayNames[date('l')] ?? '';

$afghanMonths = [
    '01'=>'حمل', '02'=>'ثور', '03'=>'جوزا', '04'=>'سرطان',
    '05'=>'اسد', '06'=>'سنبله', '07'=>'میزان', '08'=>'عقرب',
    '09'=>'قوس', '10'=>'جدی', '11'=>'دلو', '12'=>'حوت'
];
$currentMonthStr = $afghanMonths[sprintf("%02d", $jm)] ?? '';

// اتصال رشته با علامت نقطه و فاصله‌گذاری صحیح
$currentShamsiDateStr = 'امروز: ' . $dayNameAfghan . '، ' . $jd . ' ' . $currentMonthStr . ' ' . $jy;

$navPrefix = (basename(dirname($_SERVER['PHP_SELF'])) === 'wrestling-club') ? '' : '../';
$languageQuery = $_GET;
$languageQuery['lang'] = $isEnglish ? 'fa' : 'en';
$languageUrl = $_SERVER['PHP_SELF'] . '?' . http_build_query($languageQuery);
?>

<div class="navbar-custom d-flex align-items-center justify-content-between px-4 py-3 bg-white border-bottom shadow-sm">
    <div class="d-flex align-items-center gap-3">
        <!-- دراپ‌داون پروفایل کاربر -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle gap-2" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <?php if (!empty($navPhoto) && file_exists(__DIR__ . '/../uploads/users/' . $navPhoto)): ?>
                    <img src="<?php echo $navPrefix; ?>uploads/users/<?php echo htmlspecialchars($navPhoto); ?>" alt="User" class="rounded-circle border object-fit-cover shadow-sm" style="width: 42px; height: 42px;">
                <?php else: ?>
                    <div class="rounded-circle bg-primary-subtle text-primary border d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px; font-size: 1.2rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                <?php endif; ?>
                <div class="text-end">
                    <span class="fw-bold d-block text-dark small leading-tight"><?php echo htmlspecialchars($navFullName); ?></span>
                    <small class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($navRole); ?></small>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-start shadow-sm border-0 mt-2" aria-labelledby="userDropdown">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?php echo $navPrefix; ?>profile.php">
                        <i class="bi bi-gear-fill text-primary"></i>
                        <span>تنظیمات حساب</span>
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger" href="<?php echo $navPrefix; ?>logout.php">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>خروج</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="vr mx-2 text-muted"></div>
        <div class="text-muted small d-none d-md-flex align-items-center gap-1">
            <i class="bi bi-calendar3 text-primary"></i>
            <span><?php echo $currentShamsiDateStr; ?></span>
        </div>
    </div>

    <div class="d-flex align-items-center gap-3">
        <span class="fw-bold text-dark fs-6">مدیریت کلپ ورزشی پهلوانی</span>
        <a href="<?php echo htmlspecialchars($languageUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1" lang="<?php echo $isEnglish ? 'fa' : 'en'; ?>" aria-label="<?php echo $isEnglish ? 'Switch to Persian' : 'Switch to English'; ?>">
            <i class="bi bi-translate"></i>
            <span><?php echo $isEnglish ? 'فارسی' : 'English'; ?></span>
        </a>
    </div>
</div>