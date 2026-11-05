<?php
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/jdf.php';

// تاریخ و ماه پیش‌فرض هجری شمسی
$todayParts = explode('-', date('Y-m-d'));
list($jy, $jm, $jd) = gregorian_to_shamsi((int)$todayParts[0], (int)$todayParts[1], (int)$todayParts[2]);
$defaultMonth = sprintf("%02d", $jm);
$defaultYear  = (string)$jy;

$afghanMonths = [
    '01'=>'حمل', '02'=>'ثور', '03'=>'جوزا', '04'=>'سرطان',
    '05'=>'اسد', '06'=>'سنبله', '07'=>'میزان', '08'=>'عقرب',
    '09'=>'قوس', '10'=>'جدی', '11'=>'دلو', '12'=>'حوت'
];

$tab = $_GET['tab'] ?? 'financial';

// --- گزارش مالی ---
$selectedMonth = $_GET['month'] ?? $defaultMonth;
$selectedYear  = $_GET['year'] ?? $defaultYear;

$finSql = "SELECT p.*, m.full_name, m.member_code 
           FROM payments p 
           JOIN members m ON p.member_id = m.id 
           WHERE 1=1";
$finParams = [];

if ($selectedMonth !== 'all') {
    $finSql .= " AND p.shamsi_month = ?";
    $finParams[] = $selectedMonth;
}
if (!empty($selectedYear)) {
    $finSql .= " AND p.shamsi_year = ?";
    $finParams[] = $selectedYear;
}
$finSql .= " ORDER BY p.id DESC";
$stmtFin = $pdo->prepare($finSql);
$stmtFin->execute($finParams);
$financialReport = $stmtFin->fetchAll();

$totalFinIncome = 0;
$totalFinAdmission = 0;
$totalFinDiscount = 0;
foreach ($financialReport as $fr) {
    $totalFinIncome += (float)$fr['total_paid'];
    $totalFinAdmission += (float)$fr['admission_amount'];
    $totalFinDiscount += (float)$fr['discount_amount'];
}
$regularMonthlyIncome = $totalFinIncome - $totalFinAdmission;
if ($regularMonthlyIncome < 0) $regularMonthlyIncome = 0;

// --- گزارش حاضری ---
$selectedDate = $_GET['att_date'] ?? sprintf("%04d/%02d/%02d", $jy, $jm, $jd);
$selectedGroup = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;

$groupsList = $pdo->query("SELECT id, name FROM training_groups WHERE status = 'active' ORDER BY name ASC")->fetchAll();

$attSql = "SELECT a.*, m.full_name, m.member_code, tg.name AS group_name 
           FROM attendances a 
           JOIN members m ON a.member_id = m.id 
           LEFT JOIN training_groups tg ON m.group_id = tg.id 
           WHERE a.shamsi_date = ?";
$attParams = [$selectedDate];

if ($selectedGroup > 0) {
    $attSql .= " AND m.group_id = ?";
    $attParams[] = $selectedGroup;
}
$attSql .= " ORDER BY a.id DESC";
$stmtAtt = $pdo->prepare($attSql);
$stmtAtt->execute($attParams);
$attendanceReport = $stmtAtt->fetchAll();

$attPresent = 0;
$attAbsent = 0;
foreach ($attendanceReport as $ar) {
    if ($ar['status'] === 'present') $attPresent++;
    else $attAbsent++;
}
?>

<!-- کتابخانه چارت -->
<script src="../assets/js/chart.min.js"></script>

<style>
@media print {
    body * { visibility: hidden; }
    .sidebar, .navbar-custom, .no-print, .btn, form, .chart-container-box { display: none !important; }
    .main-wrapper, .content-container { margin: 0 !important; padding: 0 !important; width: 100% !important; }
    .print-section, .print-section * { visibility: visible; }
    .print-section { position: absolute; left: 0; top: 0; width: 100%; }
}
</style>

<div class="main-wrapper">
    <?php require_once '../includes/navbar.php'; ?>

    <div class="content-container">
        <!-- هدر بالای صفحه -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 no-print">
            <div>
                <h4 class="fw-bold mb-1">راپورها و گراف‌های تحلیلی سیستم</h4>
                <p class="text-muted small mb-0">گزارش‌گیری تصویری و آماری از عواید، تخفیف‌ها و سوابق حاضری</p>
            </div>
            <button type="button" class="btn btn-outline-dark px-4 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" onclick="window.print()">
                <i class="bi bi-printer-fill"></i>
                <span>چاپ راپور (Print)</span>
            </button>
        </div>

        <!-- تب‌های انتخاب نوع راپور -->
        <ul class="nav nav-pills mb-4 no-print">
            <li class="nav-item">
                <a class="nav-link px-4 <?php echo $tab === 'financial' ? 'active' : 'bg-white shadow-sm text-dark'; ?>" href="index.php?tab=financial">
                    <i class="bi bi-pie-chart-fill ms-1"></i> راپور مالی و گراف عواید
                </a>
            </li>
            <li class="nav-item ms-2">
                <a class="nav-link px-4 <?php echo $tab === 'attendance' ? 'active' : 'bg-white shadow-sm text-dark'; ?>" href="index.php?tab=attendance">
                    <i class="bi bi-calendar-check-fill ms-1"></i> راپور و گراف حاضری شاگردان
                </a>
            </li>
        </ul>

        <?php if ($tab === 'financial'): ?>
            <!-- فیلتر راپور مالی -->
            <div class="card border-0 shadow-sm rounded-3 p-3 mb-4 no-print">
                <form method="GET" action="index.php" class="row g-2 align-items-center">
                    <input type="hidden" name="tab" value="financial">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1">ماه هجری شمسی</label>
                        <select name="month" class="form-select bg-light">
                            <option value="all" <?php echo $selectedMonth === 'all' ? 'selected' : ''; ?>>تمام ماه‌های سال</option>
                            <?php foreach ($afghanMonths as $k => $name): ?>
                                <option value="<?php echo $k; ?>" <?php echo $selectedMonth === $k ? 'selected' : ''; ?>><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1">سال هجری شمسی</label>
                        <input type="text" name="year" class="form-control bg-light" value="<?php echo htmlspecialchars($selectedYear); ?>" required>
                    </div>
                    <div class="col-12 col-md-4 pt-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel ms-1"></i> اعمال فیلتر راپور
                        </button>
                    </div>
                </form>
            </div>

            <!-- باکس گراف دایروی منابع مالی -->
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4 chart-container-box no-print">
                <div class="row align-items-center">
                    <div class="col-12 col-md-5 text-center">
                        <h6 class="fw-bold mb-3 text-dark">تفکیک منابع عواید و تخفیف‌ها</h6>
                        <div style="max-width: 250px; margin: 0 auto;">
                            <canvas id="incomeChart"></canvas>
                        </div>
                    </div>
                    <div class="col-12 col-md-7">
                        <div class="p-3 bg-light rounded-3">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <span class="text-muted"><i class="bi bi-circle-fill text-primary ms-1"></i> عواید فیس داخله:</span>
                                <strong class="text-primary"><?php echo number_format($totalFinAdmission); ?> افغانی</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <span class="text-muted"><i class="bi bi-circle-fill text-success ms-1"></i> عواید فیس ماهوار:</span>
                                <strong class="text-success"><?php echo number_format($regularMonthlyIncome); ?> افغانی</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted"><i class="bi bi-circle-fill text-danger ms-1"></i> مجموع تخفیف‌های داده‌شده:</span>
                                <strong class="text-danger"><?php echo number_format($totalFinDiscount); ?> افغانی</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- بخش چاپی و جدول راپور مالی -->
            <div class="print-section">
                <div class="d-none d-print-block text-center mb-4">
                    <h4 class="fw-bold m-0">کلپ ورزشی پهلوانی</h4>
                    <p class="text-muted small">راپور تفصیلی مالی و عواید - ماه: <?php echo $selectedMonth === 'all' ? 'کل سال' : $afghanMonths[$selectedMonth]; ?> سال <?php echo htmlspecialchars($selectedYear); ?></p>
                    <hr>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-4">
                        <div class="stat-card p-3">
                            <span class="text-muted small d-block">مجموع کل دریافتی</span>
                            <h4 class="fw-bold mb-0 text-success"><?php echo number_format($totalFinIncome); ?> افغانی</h4>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card p-3">
                            <span class="text-muted small d-block">درآمد فیس داخله</span>
                            <h4 class="fw-bold mb-0 text-primary"><?php echo number_format($totalFinAdmission); ?> افغانی</h4>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card p-3">
                            <span class="text-muted small d-block">مجموع تخفیف‌ها</span>
                            <h4 class="fw-bold mb-0 text-danger"><?php echo number_format($totalFinDiscount); ?> افغانی</h4>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3 p-4">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th>#</th>
                                    <th>نام شاگرد</th>
                                    <th>کد عضویت</th>
                                    <th>بابت ماه</th>
                                    <th>فیس داخله</th>
                                    <th>فیس ماهوار</th>
                                    <th>تخفیف</th>
                                    <th>مبلغ دریافتی</th>
                                    <th>تاریخ پرداخت</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                <?php if (empty($financialReport)): ?>
                                    <tr><td colspan="9" class="text-center py-4 text-muted">رکوردی با این مشخصات یافت نشد.</td></tr>
                                <?php else: ?>
                                    <?php $i = 1; foreach ($financialReport as $row): ?>
                                        <tr>
                                            <td class="text-muted"><?php echo $i++; ?></td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['member_code']); ?></span></td>
                                            <td><?php echo $afghanMonths[$row['shamsi_month']] ?? $row['shamsi_month']; ?> <?php echo htmlspecialchars($row['shamsi_year']); ?></td>
                                            <td><?php echo (float)$row['admission_amount'] > 0 ? number_format($row['admission_amount']) : '---'; ?></td>
                                            <td><?php echo (float)$row['monthly_amount'] > 0 ? number_format($row['monthly_amount']) : '---'; ?></td>
                                            <td class="text-danger"><?php echo (float)$row['discount_amount'] > 0 ? '-' . number_format($row['discount_amount']) : '۰'; ?></td>
                                            <td class="fw-bold text-success"><?php echo number_format($row['total_paid']); ?> افغانی</td>
                                            <td class="text-muted small"><?php echo htmlspecialchars($row['shamsi_payment_date']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- فیلتر راپور حاضری -->
            <div class="card border-0 shadow-sm rounded-3 p-3 mb-4 no-print">
                <form method="GET" action="index.php" class="row g-2 align-items-center">
                    <input type="hidden" name="tab" value="attendance">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1">تاریخ هجری شمسی</label>
                        <input type="text" name="att_date" class="form-control bg-light" value="<?php echo htmlspecialchars($selectedDate); ?>" placeholder="1405/06/25" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1">صنف تمرینی</label>
                        <select name="group_id" class="form-select bg-light">
                            <option value="0">تمام صنف‌ها</option>
                            <?php foreach ($groupsList as $gl): ?>
                                <option value="<?php echo $gl['id']; ?>" <?php echo $selectedGroup == $gl['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($gl['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 pt-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel ms-1"></i> مشاهده گزارش حاضری
                        </button>
                    </div>
                </form>
            </div>

            <!-- باکس گراف دایروی حاضری -->
            <div class="row g-3 mb-4 chart-container-box no-print">
                <div class="col-12 col-md-5">
                    <div class="card border-0 shadow-sm rounded-3 p-4 h-100 text-center">
                        <h6 class="fw-bold mb-3 text-dark">نسبت حاضرین به غیرحاضرین</h6>
                        <div style="max-width: 230px; margin: 0 auto;">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-7">
                    <div class="card border-0 shadow-sm rounded-3 p-4 h-100 d-flex flex-column justify-content-center">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="stat-card p-3">
                                    <span class="text-muted small d-block">مجموع حاضرین</span>
                                    <h4 class="fw-bold mb-0 text-success"><?php echo $attPresent; ?> نفر</h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card p-3">
                                    <span class="text-muted small d-block">مجموع غیرحاضرین</span>
                                    <h4 class="fw-bold mb-0 text-danger"><?php echo $attAbsent; ?> نفر</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- جدول راپور حاضری -->
            <div class="print-section">
                <div class="d-none d-print-block text-center mb-4">
                    <h4 class="fw-bold m-0">کلپ ورزشی پهلوانی</h4>
                    <p class="text-muted small">راپور حضور و غیاب شاگردان به تاریخ: <?php echo htmlspecialchars($selectedDate); ?></p>
                    <hr>
                </div>

                <div class="card border-0 shadow-sm rounded-3 p-4">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th>#</th>
                                    <th>نام شاگرد</th>
                                    <th>کد عضویت</th>
                                    <th>صنف تمرینی</th>
                                    <th>تاریخ ثبت</th>
                                    <th class="text-center">وضعیت حضور</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                <?php if (empty($attendanceReport)): ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted">هیچ رکوردی برای این تاریخ ثبت نشده است.</td></tr>
                                <?php else: ?>
                                    <?php $i = 1; foreach ($attendanceReport as $arow): ?>
                                        <tr>
                                            <td class="text-muted"><?php echo $i++; ?></td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($arow['full_name']); ?></td>
                                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($arow['member_code']); ?></span></td>
                                            <td><?php echo htmlspecialchars($arow['group_name'] ?? 'نامشخص'); ?></td>
                                            <td class="text-muted small"><?php echo htmlspecialchars($arow['shamsi_date']); ?></td>
                                            <td class="text-center">
                                                <?php if ($arow['status'] === 'present'): ?>
                                                    <span class="badge bg-success-subtle text-success px-3 py-1">حاضر</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger px-3 py-1">غیرحاضر</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    <?php if ($tab === 'financial'): ?>
    const ctxIncome = document.getElementById('incomeChart');
    if (ctxIncome && typeof Chart !== 'undefined') {
        const admissionVal = <?php echo (float)$totalFinAdmission; ?>;
        const monthlyVal   = <?php echo (float)$regularMonthlyIncome; ?>;
        const discountVal  = <?php echo (float)$totalFinDiscount; ?>;

        new Chart(ctxIncome, {
            type: 'doughnut',
            data: {
                labels: ['فیس داخله', 'فیس ماهوار', 'تخفیف‌ها'],
                datasets: [{
                    data: [admissionVal, monthlyVal, discountVal],
                    backgroundColor: ['#2563eb', '#16a34a', '#dc2626'],
                    borderWidth: 2,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { family: 'inherit', size: 12 } }
                    }
                },
                cutout: '70%'
            }
        });
    }
    <?php else: ?>
    const ctxAtt = document.getElementById('attendanceChart');
    if (ctxAtt && typeof Chart !== 'undefined') {
        new Chart(ctxAtt, {
            type: 'doughnut',
            data: {
                labels: ['حاضر', 'غیرحاضر'],
                datasets: [{
                    data: [<?php echo $attPresent; ?>, <?php echo $attAbsent; ?>],
                    backgroundColor: ['#16a34a', '#dc2626'],
                    borderWidth: 2,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { family: 'inherit', size: 12 } }
                    }
                },
                cutout: '70%'
            }
        });
    }
    <?php endif; ?>
});
</script>

<?php require_once '../includes/footer.php'; ?>