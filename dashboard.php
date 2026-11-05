<?php
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
require_once 'includes/jdf.php';

// محاسبه تاریخ و ماه جاری شمسی
$todayParts = explode('-', date('Y-m-d'));
list($jy, $jm, $jd) = gregorian_to_shamsi((int)$todayParts[0], (int)$todayParts[1], (int)$todayParts[2]);
$currentShamsiMonth = sprintf("%02d", $jm);
$currentShamsiYear  = (string)$jy;

$afghanMonths = [
    '01'=>'حمل', '02'=>'ثور', '03'=>'جوزا', '04'=>'سرطان',
    '05'=>'اسد', '06'=>'سنبله', '07'=>'میزان', '08'=>'عقرب',
    '09'=>'قوس', '10'=>'جدی', '11'=>'دلو', '12'=>'حوت'
];

// ۱. دریافت آمار کلی کارت‌ها از دیتابیس
$totalMembers = $pdo->query("SELECT COUNT(*) FROM members WHERE status = 'active'")->fetchColumn();
$totalCoaches = $pdo->query("SELECT COUNT(*) FROM coaches WHERE status = 'active'")->fetchColumn();
$totalGroups  = $pdo->query("SELECT COUNT(*) FROM training_groups WHERE status = 'active'")->fetchColumn();

// عواید ماه جاری
$stmtIncome = $pdo->prepare("SELECT SUM(total_paid) FROM payments WHERE shamsi_month = ? AND shamsi_year = ?");
$stmtIncome->execute([$currentShamsiMonth, $currentShamsiYear]);
$currentMonthIncome = $stmtIncome->fetchColumn() ?: 0;

// ۲. آخرین شاگردان ثبت‌نام‌شده (۵ نفر آخر)
$recentMembers = $pdo->query("SELECT m.*, tg.name AS group_name 
                              FROM members m 
                              LEFT JOIN training_groups tg ON m.group_id = tg.id 
                              ORDER BY m.id DESC LIMIT 5")->fetchAll();

// ۳. آخرین پرداخت‌های مالی (۵ رسید آخر)
$recentPayments = $pdo->query("SELECT p.*, m.full_name, m.member_code 
                               FROM payments p 
                               JOIN members m ON p.member_id = m.id 
                               ORDER BY p.id DESC LIMIT 5")->fetchAll();
?>

<div class="main-wrapper">
    <?php require_once 'includes/navbar.php'; ?>

    <div class="content-container">
        <!-- عنوان و خوش‌آمدگویی -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">داشبورد مدیریتی</h4>
                <p class="text-muted small mb-0">خلاصه وضعیت عمومی کلپ ورزشی، آمار شاگردان و گردش مالی</p>
            </div>
            <div class="d-flex gap-2">
                <a href="members/create.php" class="btn btn-primary px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>ثبت‌نام سریع شاگرد</span>
                </a>
            </div>
        </div>

        <!-- کارت‌های آمار اصلی -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">ورزشکاران فعال</span>
                        <h4 class="fw-bold mb-0 text-dark"><?php echo number_format($totalMembers); ?> نفر</h4>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">عواید ماه <?php echo $afghanMonths[$currentShamsiMonth]; ?></span>
                        <h4 class="fw-bold mb-0 text-success"><?php echo number_format($currentMonthIncome); ?> <small class="fs-6 fw-normal">افغانی</small></h4>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">استادان و مربیان</span>
                        <h4 class="fw-bold mb-0 text-dark"><?php echo number_format($totalCoaches); ?> مربی</h4>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-calendar-week-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">صنف‌های فعال</span>
                        <h4 class="fw-bold mb-0 text-dark"><?php echo number_format($totalGroups); ?> تایم</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- بخش جداول آخرین فعالیت‌ها -->
        <div class="row g-4">
            <!-- آخرین ورزشکاران ثبت‌شده -->
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-3 p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold mb-0">آخرین شاگردان تازه‌وارد</h6>
                        <a href="members/index.php" class="small text-decoration-none">مشاهده همه <i class="bi bi-arrow-left"></i></a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th>عکس</th>
                                    <th>نام شاگرد</th>
                                    <th>صنف تمرینی</th>
                                    <th class="text-center">کارت هویت</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                <?php if (empty($recentMembers)): ?>
                                    <tr><td colspan="4" class="text-center py-3 text-muted small">اطلاعاتی موجود نیست.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentMembers as $rm): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($rm['photo']) && file_exists('uploads/members/' . $rm['photo'])): ?>
                                                    <img src="uploads/members/<?php echo htmlspecialchars($rm['photo']); ?>" class="rounded-circle object-fit-cover border" style="width: 35px; height: 35px;">
                                                <?php else: ?>
                                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border text-secondary" style="width: 35px; height: 35px;">
                                                        <i class="bi bi-person"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="fw-bold d-block text-dark"><?php echo htmlspecialchars($rm['full_name']); ?></span>
                                                <small class="text-muted"><?php echo htmlspecialchars($rm['member_code']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary-subtle text-dark">
                                                    <?php echo !empty($rm['group_name']) ? htmlspecialchars($rm['group_name']) : 'بدون صنف'; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="members/card.php?id=<?php echo $rm['id']; ?>" class="btn btn-sm btn-outline-success p-1 px-2" title="چاپ کارت">
                                                    <i class="bi bi-person-badge"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- آخرین پرداخت‌ها -->
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-3 p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold mb-0">آخرین دریافت‌های مالی</h6>
                        <a href="payments/index.php" class="small text-decoration-none">مشاهده همه <i class="bi bi-arrow-left"></i></a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th>شاگرد</th>
                                    <th>نوعیت</th>
                                    <th>مبلغ</th>
                                    <th>تاریخ</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                <?php if (empty($recentPayments)): ?>
                                    <tr><td colspan="4" class="text-center py-3 text-muted small">رسیدی یافت نشد.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentPayments as $rp): ?>
                                        <tr>
                                            <td>
                                                <span class="fw-bold d-block text-dark"><?php echo htmlspecialchars($rp['full_name']); ?></span>
                                                <small class="text-muted"><?php echo htmlspecialchars($rp['member_code']); ?></small>
                                            </td>
                                            <td>
                                                <?php 
                                                    if ($rp['payment_type'] === 'first_time') echo '<span class="badge bg-primary-subtle text-primary">داخله + فیس</span>';
                                                    elseif ($rp['payment_type'] === 'monthly') echo '<span class="badge bg-info-subtle text-info">ماهوار</span>';
                                                    else echo '<span class="badge bg-secondary-subtle text-dark">فقط داخله</span>';
                                                ?>
                                            </td>
                                            <td class="fw-bold text-success"><?php echo number_format($rp['total_paid']); ?> افغانی</td>
                                            <td class="text-muted small"><?php echo htmlspecialchars($rp['shamsi_payment_date']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>