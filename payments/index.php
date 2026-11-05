<?php
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/jdf.php';

// تاریخ امروز هجری شمسی
$todayParts = explode('-', date('Y-m-d'));
list($jy, $jm, $jd) = gregorian_to_shamsi((int)$todayParts[0], (int)$todayParts[1], (int)$todayParts[2]);
$todayShamsi = sprintf("%04d/%02d/%02d", $jy, $jm, $jd);
$currentMonth = sprintf("%02d", $jm);
$currentYear  = (string)$jy;

// دریافت شاگردان فعال برای سلکت فرم
$membersList = $pdo->query("SELECT id, member_code, full_name FROM members WHERE status = 'active' ORDER BY full_name ASC")->fetchAll();

// فیلتر جستجو
$search = trim($_GET['search'] ?? '');
$monthFilter = trim($_GET['month'] ?? '');

$sql = "SELECT p.*, m.full_name, m.member_code 
        FROM payments p 
        JOIN members m ON p.member_id = m.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (m.full_name LIKE ? OR m.member_code LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($monthFilter)) {
    $sql .= " AND p.shamsi_month = ?";
    $params[] = $monthFilter;
}

$sql .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$payments = $stmt->fetchAll();

// محاسبه آمارهای مالی بالای صفحه
$totalIncome = 0;
$totalDiscounts = 0;
$totalAdmission = 0;
foreach ($payments as $p) {
    $totalIncome += (float)$p['total_paid'];
    $totalDiscounts += (float)$p['discount_amount'];
    $totalAdmission += (float)$p['admission_amount'];
}

$afghanMonths = [
    '01'=>'حمل', '02'=>'ثور', '03'=>'جوزا', '04'=>'سرطان',
    '05'=>'اسد', '06'=>'سنبله', '07'=>'میزان', '08'=>'عقرب',
    '09'=>'قوس', '10'=>'جدی', '11'=>'دلو', '12'=>'حوت'
];
?>

<div class="main-wrapper">
    <?php require_once '../includes/navbar.php'; ?>

    <div class="content-container">
        <!-- پیام‌های عملیات -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-check-circle-fill"></i>
                <span>رسید پرداخت با موفقیت ثبت شد.</span>
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="alert alert-primary border-0 shadow-sm d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-check-circle-fill"></i>
                <span>رسید پرداخت با موفقیت ویرایش شد.</span>
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-trash-fill"></i>
                <span>سند پرداختی با موفقیت حذف گردید.</span>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">مدیریت فیس‌ها و پرداخت‌ها</h4>
                <p class="text-muted small mb-0">ثبت فیس ماهوار، داخله، تخفیف‌ها و اسناد مالی شاگردان</p>
            </div>
            <button type="button" class="btn btn-primary px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                <i class="bi bi-cash-stack"></i>
                <span>ثبت رسید پرداخت جدید</span>
            </button>
        </div>

        <!-- کارت‌های آمار زنده مالی -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">مجموع عواید ثبت‌شده</span>
                        <h4 class="fw-bold mb-0 text-success"><?php echo number_format($totalIncome); ?> <small class="fs-6 fw-normal">افغانی</small></h4>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">عواید فیس داخله</span>
                        <h4 class="fw-bold mb-0 text-primary"><?php echo number_format($totalAdmission); ?> <small class="fs-6 fw-normal">افغانی</small></h4>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="stat-card">
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="bi bi-tag-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">مجموع تخفیف‌های داده‌شده</span>
                        <h4 class="fw-bold mb-0 text-danger"><?php echo number_format($totalDiscounts); ?> <small class="fs-6 fw-normal">افغانی</small></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- فیلتر و جدول لیست پرداخت‌ها -->
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <form method="GET" action="index.php" class="row g-3 mb-4 align-items-center justify-content-between">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-start-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control border-end-0 bg-light" placeholder="جستجو بر اساس نام یا کد عضویت شاگرد...">
                    </div>
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <select name="month" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="">همه ماه‌ها</option>
                        <?php foreach ($afghanMonths as $key => $mName): ?>
                            <option value="<?php echo $key; ?>" <?php echo $monthFilter === $key ? 'selected' : ''; ?>><?php echo $mName; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($search) || !empty($monthFilter)): ?>
                        <a href="index.php" class="btn btn-light" title="پاک کردن فیلتر"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th scope="col" style="width: 40px;">#</th>
                            <th scope="col">شاگرد</th>
                            <th scope="col">کد عضویت</th>
                            <th scope="col">نوعیت پرداخت</th>
                            <th scope="col">بابت ماه</th>
                            <th scope="col">داخله</th>
                            <th scope="col">ماهوار</th>
                            <th scope="col">تخفیف</th>
                            <th scope="col">مبلغ دریافتی</th>
                            <th scope="col">تاریخ پرداخت</th>
                            <th scope="col" class="text-center" style="width: 110px;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php if (empty($payments)): ?>
                            <tr>
                                <td colspan="11" class="text-center py-4 text-muted">هیچ سند پرداختی در دیتابیس ثبت نشده است.</td>
                            </tr>
                        <?php else: ?>
                            <?php $i = 1; foreach ($payments as $p): ?>
                                <tr>
                                    <td class="text-muted fw-bold"><?php echo $i++; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($p['full_name']); ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($p['member_code']); ?></span></td>
                                    <td>
                                        <?php 
                                            if ($p['payment_type'] === 'first_time') echo '<span class="badge bg-primary-subtle text-primary">داخله + فیس</span>';
                                            elseif ($p['payment_type'] === 'monthly') echo '<span class="badge bg-info-subtle text-info">فیس ماهوار</span>';
                                            else echo '<span class="badge bg-secondary-subtle text-dark">فقط داخله</span>';
                                        ?>
                                    </td>
                                    <td class="fw-semibold text-dark"><?php echo $afghanMonths[$p['shamsi_month']] ?? $p['shamsi_month']; ?> <?php echo htmlspecialchars($p['shamsi_year']); ?></td>
                                    <td class="text-muted"><?php echo (float)$p['admission_amount'] > 0 ? number_format($p['admission_amount']) : '---'; ?></td>
                                    <td class="text-muted"><?php echo (float)$p['monthly_amount'] > 0 ? number_format($p['monthly_amount']) : '---'; ?></td>
                                    <td class="text-danger"><?php echo (float)$p['discount_amount'] > 0 ? '-' . number_format($p['discount_amount']) : '۰'; ?></td>
                                    <td class="fw-bold text-success"><?php echo number_format($p['total_paid']); ?> افغانی</td>
                                    <td class="text-muted small"><?php echo htmlspecialchars($p['shamsi_payment_date']); ?></td>
                                    <td class="text-center">
                                        <!-- دکمه ویرایش -->
                                        <button type="button" class="btn btn-sm btn-outline-primary p-1 px-2 me-1" data-bs-toggle="modal" data-bs-target="#editPaymentModal<?php echo $p['id']; ?>" title="ویرایش">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <!-- دکمه حذف -->
                                        <a href="delete.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-danger p-1 px-2" onclick="return confirm('آیا از حذف این رسید پرداخت مطمئن هستید؟')" title="حذف">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>

                                <!-- مودال ویرایش فیس -->
                                <div class="modal fade" id="editPaymentModal<?php echo $p['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header border-bottom-0 d-flex justify-content-between align-items-center">
                                                <h5 class="modal-title fw-bold m-0">ویرایش رسید پرداخت</h5>
                                                <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="بستن"></button>
                                            </div>
                                            <form action="update.php" method="POST">
                                                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                                <div class="modal-body pt-1">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">انتخاب شاگرد <span class="text-danger">*</span></label>
                                                        <select name="member_id" class="form-select bg-light" required>
                                                            <?php foreach ($membersList as $mb): ?>
                                                                <option value="<?php echo $mb['id']; ?>" <?php echo $p['member_id'] == $mb['id'] ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($mb['full_name']); ?> (<?php echo htmlspecialchars($mb['member_code']); ?>)
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">نوعیت پرداخت <span class="text-danger">*</span></label>
                                                        <select name="payment_type" class="form-select bg-light">
                                                            <option value="monthly" <?php echo $p['payment_type'] === 'monthly' ? 'selected' : ''; ?>>فیس ماهوار</option>
                                                            <option value="first_time" <?php echo $p['payment_type'] === 'first_time' ? 'selected' : ''; ?>>ثبت نام اول (داخله + فیس)</option>
                                                            <option value="admission_only" <?php echo $p['payment_type'] === 'admission_only' ? 'selected' : ''; ?>>فقط داخله</option>
                                                        </select>
                                                    </div>

                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label small fw-bold text-muted">فیس داخله</label>
                                                            <input type="number" step="1" name="admission_amount" id="adm_<?php echo $p['id']; ?>" class="form-control bg-light" value="<?php echo (int)$p['admission_amount']; ?>" oninput="recalcEdit(<?php echo $p['id']; ?>)">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small fw-bold text-muted">فیس ماهوار</label>
                                                            <input type="number" step="1" name="monthly_amount" id="mon_<?php echo $p['id']; ?>" class="form-control bg-light" value="<?php echo (int)$p['monthly_amount']; ?>" oninput="recalcEdit(<?php echo $p['id']; ?>)">
                                                        </div>
                                                    </div>

                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label small fw-bold text-danger">تخفیف (افغانی)</label>
                                                            <input type="number" step="1" name="discount_amount" id="disc_<?php echo $p['id']; ?>" class="form-control bg-light" value="<?php echo (int)$p['discount_amount']; ?>" oninput="recalcEdit(<?php echo $p['id']; ?>)">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small fw-bold text-success">مبلغ نهایی دریافتی</label>
                                                            <input type="text" id="total_display_<?php echo $p['id']; ?>" class="form-control bg-light fw-bold text-success" value="<?php echo number_format($p['total_paid']); ?> افغانی" readonly>
                                                        </div>
                                                    </div>

                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label small fw-bold">بابت ماه</label>
                                                            <select name="shamsi_month" class="form-select bg-light">
                                                                <?php foreach ($afghanMonths as $key => $name): ?>
                                                                    <option value="<?php echo $key; ?>" <?php echo $p['shamsi_month'] === $key ? 'selected' : ''; ?>><?php echo $name; ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small fw-bold">سال هجری شمسی</label>
                                                            <input type="text" name="shamsi_year" class="form-control bg-light" value="<?php echo htmlspecialchars($p['shamsi_year']); ?>" required>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">تاریخ پرداخت</label>
                                                        <input type="text" name="shamsi_payment_date" class="form-control bg-light" value="<?php echo htmlspecialchars($p['shamsi_payment_date']); ?>" required>
                                                    </div>

                                                    <div class="mb-2">
                                                        <label class="form-label small fw-bold text-muted">ملاحظات / توضیحات</label>
                                                        <input type="text" name="note" class="form-control bg-light" value="<?php echo htmlspecialchars($p['note'] ?? ''); ?>">
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top-0 pt-0 d-flex justify-content-end gap-2">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">انصراف</button>
                                                    <button type="submit" class="btn btn-primary px-4">بروزرسانی رسید</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- مودال ثبت فیس جدید با محاسبه خودکار مقادیر -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-bold m-0">ثبت رسید پرداخت جدید</h5>
                <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <form action="store.php" method="POST">
                <div class="modal-body pt-1">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">انتخاب شاگرد <span class="text-danger">*</span></label>
                        <select name="member_id" class="form-select bg-light" required>
                            <option value="">انتخاب شاگرد...</option>
                            <?php foreach ($membersList as $mb): ?>
                                <option value="<?php echo $mb['id']; ?>">
                                    <?php echo htmlspecialchars($mb['full_name']); ?> (<?php echo htmlspecialchars($mb['member_code']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">نوعیت پرداخت <span class="text-danger">*</span></label>
                        <select name="payment_type" id="payment_type" class="form-select bg-light" onchange="applyPresetAmounts()">
                            <option value="monthly" selected>فیس ماهوار عادی (۴۰۰ افغانی)</option>
                            <option value="first_time">ثبت نام اول (۵۰۰ داخله + ۴۰۰ فیس = ۹۰۰ افغانی)</option>
                            <option value="admission_only">فقط داخله (۵۰۰ افغانی)</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">فیس داخله</label>
                            <input type="number" step="1" name="admission_amount" id="admission_amount" class="form-control bg-light" value="0" oninput="calculateTotal()">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted">فیس ماهوار</label>
                            <input type="number" step="1" name="monthly_amount" id="monthly_amount" class="form-control bg-light" value="400" oninput="calculateTotal()">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-danger">تخفیف (افغانی)</label>
                            <input type="number" step="1" name="discount_amount" id="discount_amount" class="form-control bg-light" value="0" oninput="calculateTotal()">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-success">مبلغ نهایی دریافتی</label>
                            <input type="text" id="total_display" class="form-control bg-light fw-bold text-success" value="400 افغانی" readonly>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">بابت ماه</label>
                            <select name="shamsi_month" class="form-select bg-light">
                                <?php foreach ($afghanMonths as $key => $name): ?>
                                    <option value="<?php echo $key; ?>" <?php echo $currentMonth === $key ? 'selected' : ''; ?>><?php echo $name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">سال هجری شمسی</label>
                            <input type="text" name="shamsi_year" class="form-control bg-light" value="<?php echo $currentYear; ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">تاریخ پرداخت</label>
                        <input type="text" name="shamsi_payment_date" class="form-control bg-light" value="<?php echo $todayShamsi; ?>" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted">ملاحظات / توضیحات (اختیاری)</label>
                        <input type="text" name="note" class="form-control bg-light" placeholder="مثلاً: تخفیف با تایید استاد">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary px-4">ثبت رسید</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function applyPresetAmounts() {
    const type = document.getElementById('payment_type').value;
    const admission = document.getElementById('admission_amount');
    const monthly = document.getElementById('monthly_amount');

    if (type === 'first_time') {
        admission.value = 500;
        monthly.value = 400;
    } else if (type === 'monthly') {
        admission.value = 0;
        monthly.value = 400;
    } else if (type === 'admission_only') {
        admission.value = 500;
        monthly.value = 0;
    }
    calculateTotal();
}

function calculateTotal() {
    const adm = parseFloat(document.getElementById('admission_amount').value) || 0;
    const mon = parseFloat(document.getElementById('monthly_amount').value) || 0;
    const disc = parseFloat(document.getElementById('discount_amount').value) || 0;

    let total = (adm + mon) - disc;
    if (total < 0) total = 0;

    document.getElementById('total_display').value = total.toLocaleString() + ' افغانی';
}

function recalcEdit(id) {
    const adm = parseFloat(document.getElementById('adm_' + id).value) || 0;
    const mon = parseFloat(document.getElementById('mon_' + id).value) || 0;
    const disc = parseFloat(document.getElementById('disc_' + id).value) || 0;

    let total = (adm + mon) - disc;
    if (total < 0) total = 0;

    document.getElementById('total_display_' + id).value = total.toLocaleString() + ' افغانی';
}
</script>

<?php require_once '../includes/footer.php'; ?>