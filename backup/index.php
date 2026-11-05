<?php
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="main-wrapper">
    <?php require_once '../includes/navbar.php'; ?>

    <div class="content-container">
        <!-- پیام‌های اعلان -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'restored'): ?>
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <span>اطلاعات دیتابیس با موفقیت و بدون نقص بازیابی (Restore) شد.</span>
            </div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <span>خطا در بازیابی! لطفاً مطمئن شوید فایل انتخابی یک فایل SQL معتبر است.</span>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">پشتیبان‌گیری و بازیابی دیتابیس</h4>
                <p class="text-muted small mb-0">تهیه نسخه پشتیبان (Backup) از تمام جداول کلپ و بازگردانی اطلاعات</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- کارت دانلود پشتیبان -->
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm rounded-3 p-4 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="stat-icon bg-primary-subtle text-primary m-0" style="width: 50px; height: 50px; font-size: 1.4rem;">
                                <i class="bi bi-cloud-arrow-down-fill"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">تهیه فایل پشتیبان (Export)</h6>
                                <small class="text-muted">دانلود نسخه کامل پایگاه‌داده</small>
                            </div>
                        </div>
                        <p class="text-muted small leading-relaxed">
                            این بخش تمامی جداول شامل ورزشکاران، مربیان، گروه‌های تمرینی، سوابق حاضری و تراکنش‌های فیس را در قالب یک فایل استاندارد <code>.sql</code> استخراج می‌کند. توصیه می‌شود به صورت هفتگی پشتیبان تهیه کنید.
                        </p>
                    </div>

                    <div class="pt-3">
                        <a href="export.php" class="btn btn-primary w-100 py-2 rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-download"></i>
                            <span>دانلود نسخه پشتیبان (.sql)</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- کارت بازیابی اطلاعات -->
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm rounded-3 p-4 h-100">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="stat-icon bg-danger-subtle text-danger m-0" style="width: 50px; height: 50px; font-size: 1.4rem;">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">بازیابی پایگاه‌داده (Restore)</h6>
                            <small class="text-muted">بازگردانی دیتابیس از فایل پشتیبان</small>
                        </div>
                    </div>
                    <p class="text-muted small">
                        در صورت نیاز به بازگردانی اطلاعات یا انتقال سیستم به کامپیوتر دیگر، فایل <code>.sql</code> قبلی را از این قسمت آپلود کنید:
                    </p>

                    <form action="import.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <input type="file" name="sql_file" class="form-control bg-light" accept=".sql" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100 py-2 rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2" onclick="return confirm('توجه: با بازیابی اطلاعات، دیتابیس فعلی بازنویسی خواهد شد. آیا مطمئن هستید؟')">
                            <i class="bi bi-upload"></i>
                            <span>شروع بازیابی اطلاعات</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>