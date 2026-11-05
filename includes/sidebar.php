<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));

// تعیین پیشوند مسیرها بر اساس عمق پوشه
$basePrefix = ($currentDir === 'wrestling-club' || $currentDir === 'htdocs') ? '' : '../';
?>

<div class="sidebar">
    <div class="sidebar-header d-flex align-items-center gap-2 px-3 py-4">
        <i class="bi bi-trophy-fill text-warning fs-3"></i>
        <h5 class="m-0 fw-bold text-white">کلپ ورزشی پهلوانی</h5>
    </div>

    <ul class="nav flex-column px-2 gap-1 mt-2">
        <li class="nav-item">
            <a href="<?php echo $basePrefix; ?>dashboard.php" class="nav-link <?php echo ($currentPage === 'dashboard.php' || ($currentPage === 'index.php' && !in_array($currentDir, ['reports', 'backup', 'members', 'payments', 'coaches', 'attendance', 'training-groups']))) ? 'active' : ''; ?>">
                <i class="bi bi-grid-fill"></i>
                <span>داشبورد (صفحه اصلی)</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?php echo $basePrefix; ?>members/index.php" class="nav-link <?php echo ($currentDir === 'members') ? 'active' : ''; ?>">
                <i class="bi bi-people-fill"></i>
                <span>ورزشکاران (شاگردان)</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?php echo $basePrefix; ?>coaches/index.php" class="nav-link <?php echo ($currentDir === 'coaches') ? 'active' : ''; ?>">
                <i class="bi bi-person-badge-fill"></i>
                <span>استادان (مربیان)</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?php echo $basePrefix; ?>attendance/index.php" class="nav-link <?php echo ($currentDir === 'attendance') ? 'active' : ''; ?>">
                <i class="bi bi-calendar-check-fill"></i>
                <span>حاضری روزانه</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?php echo $basePrefix; ?>payments/index.php" class="nav-link <?php echo ($currentDir === 'payments') ? 'active' : ''; ?>">
                <i class="bi bi-wallet2"></i>
                <span>فیس و پرداخت‌ها</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?php echo $basePrefix; ?>training-groups/index.php" class="nav-link <?php echo ($currentDir === 'training-groups') ? 'active' : ''; ?>">
                <i class="bi bi-diagram-3-fill"></i>
                <span>گروه‌ها و تایم‌های تمرین</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?php echo $basePrefix; ?>reports/index.php" class="nav-link <?php echo ($currentDir === 'reports') ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-bar-graph-fill"></i>
                <span>راپورها (گزارشات)</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?php echo $basePrefix; ?>backup/index.php" class="nav-link <?php echo ($currentDir === 'backup') ? 'active' : ''; ?>">
                <i class="bi bi-database-fill-gear"></i>
                <span>بک‌آپ و بازیابی دیتابیس</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?php echo $basePrefix; ?>profile.php" class="nav-link <?php echo ($currentPage === 'profile.php') ? 'active' : ''; ?>">
                <i class="bi bi-key-fill"></i>
                <span>تنظیمات و تغییر رمز</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer mt-auto p-3">
        <a href="<?php echo $basePrefix; ?>logout.php" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2 rounded-3">
            <i class="bi bi-box-arrow-right"></i>
            <span>خروج از سیستم</span>
        </a>
    </div>
</div>