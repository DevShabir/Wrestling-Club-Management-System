<?php
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

// دریافت گروه‌های تمرینی فعال از دیتابیس
$groupsStmt = $pdo->query("SELECT id, name FROM training_groups WHERE status = 'active' ORDER BY name ASC");
$trainingGroups = $groupsStmt->fetchAll();
?>

<div class="main-wrapper">
    <?php require_once '../includes/navbar.php'; ?>

    <div class="content-container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1">ثبت‌نام ورزشکار (شاگرد جدید)</h4>
                <p class="text-muted small mb-0">اطلاعات هویتی و گروه تمرینی شاگرد را درج نمایید</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary px-3 py-2 rounded-3">
                <i class="bi bi-arrow-right ms-1"></i> بازگشت به لیست
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3 p-4">
            <form action="store.php" method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold">نام و تخلص <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control bg-light" placeholder="مثلاً: احمد رحیمی" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">نام پدر</label>
                        <input type="text" name="father_name" class="form-control bg-light" placeholder="مثلاً: محمد داوود">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold">شماره تماس <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control bg-light" placeholder="مثلاً: 0799XXXXXX" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">وزن تقریبی (کیلوگرم)</label>
                        <input type="number" step="0.1" name="weight" class="form-control bg-light" placeholder="مثلاً: 65">
                    </div>

                    <!-- بخش تاریخ تولد هجری شمسی -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">تاریخ تولد (هجری شمسی)</label>
                        <div class="row g-2">
                            <div class="col-4">
                                <select name="birth_day" class="form-select bg-light">
                                    <option value="">روز</option>
                                    <?php for($i=1; $i<=31; $i++): ?>
                                        <option value="<?php echo sprintf("%02d", $i); ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-4">
                                <select name="birth_month" class="form-select bg-light">
                                    <option value="">ماه</option>
                                    <option value="01">حمل</option>
                                    <option value="02">ثور</option>
                                    <option value="03">جوزا</option>
                                    <option value="04">سرطان</option>
                                    <option value="05">اسد</option>
                                    <option value="06">سنبله</option>
                                    <option value="07">میزان</option>
                                    <option value="08">عقرب</option>
                                    <option value="09">قوس</option>
                                    <option value="10">جدی</option>
                                    <option value="11">دلو</option>
                                    <option value="12">حوت</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <select name="birth_year" class="form-select bg-light">
                                    <option value="">سال</option>
                                    <?php for($y=1405; $y>=1370; $y--): ?>
                                        <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold">انتخاب صنف / گروه تمرینی</label>
                        <select name="group_id" class="form-select bg-light">
                            <option value="">بدون گروه (تعیین بعداً)</option>
                            <?php foreach($trainingGroups as $tg): ?>
                                <option value="<?php echo $tg['id']; ?>"><?php echo htmlspecialchars($tg['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">عکس شاگرد (اختیاری)</label>
                        <input type="file" name="photo" class="form-control bg-light" accept="image/*">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold">وضعیت عضویت</label>
                        <select name="status" class="form-select bg-light">
                            <option value="active" selected>فعال</option>
                            <option value="inactive">غیرفعال</option>
                        </select>
                    </div>

                    <div class="col-12 text-end pt-3">
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-3">
                            <i class="bi bi-check-lg ms-1"></i> ثبت شاگرد
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>