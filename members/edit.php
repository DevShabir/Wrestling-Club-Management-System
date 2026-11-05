<?php
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
$stmt->execute([$id]);
$member = $stmt->fetch();

if (!$member) {
    header('Location: index.php');
    exit;
}

$groupsStmt = $pdo->query("SELECT id, name FROM training_groups WHERE status = 'active' ORDER BY name ASC");
$trainingGroups = $groupsStmt->fetchAll();
?>

<div class="main-wrapper">
    <?php require_once '../includes/navbar.php'; ?>

    <div class="content-container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1">ویرایش معلومات ورزشکار</h4>
                <p class="text-muted small mb-0">کد عضویت: <?php echo htmlspecialchars($member['member_code']); ?></p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary px-3 py-2 rounded-3">
                <i class="bi bi-arrow-right ms-1"></i> بازگشت به لیست
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3 p-4">
            <form action="update.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold">نام و تخلص <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control bg-light" value="<?php echo htmlspecialchars($member['full_name']); ?>" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">نام پدر</label>
                        <input type="text" name="father_name" class="form-control bg-light" value="<?php echo htmlspecialchars($member['father_name'] ?? ''); ?>">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold">شماره تماس <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control bg-light" value="<?php echo htmlspecialchars($member['phone']); ?>" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">وزن تقریبی (کیلوگرم)</label>
                        <input type="number" step="0.1" name="weight" class="form-control bg-light" value="<?php echo htmlspecialchars($member['weight'] ?? ''); ?>">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">تاریخ تولد (هجری شمسی)</label>
                        <div class="row g-2">
                            <div class="col-4">
                                <select name="birth_day" class="form-select bg-light">
                                    <option value="">روز</option>
                                    <?php for($i=1; $i<=31; $i++): $d = sprintf("%02d", $i); ?>
                                        <option value="<?php echo $d; ?>" <?php echo $member['birth_day'] == $d ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-4">
                                <select name="birth_month" class="form-select bg-light">
                                    <option value="">ماه</option>
                                    <?php 
                                    $months = ['01'=>'حمل', '02'=>'ثور', '03'=>'جوزا', '04'=>'سرطان', '05'=>'اسد', '06'=>'سنبله', '07'=>'میزان', '08'=>'عقرب', '09'=>'قوس', '10'=>'جدی', '11'=>'دلو', '12'=>'حوت'];
                                    foreach($months as $k => $v): ?>
                                        <option value="<?php echo $k; ?>" <?php echo $member['birth_month'] == $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-4">
                                <select name="birth_year" class="form-select bg-light">
                                    <option value="">سال</option>
                                    <?php for($y=1405; $y>=1370; $y--): ?>
                                        <option value="<?php echo $y; ?>" <?php echo $member['birth_year'] == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold">انتخاب صنف / گروه تمرینی</label>
                        <select name="group_id" class="form-select bg-light">
                            <option value="">بدون گروه</option>
                            <?php foreach($trainingGroups as $tg): ?>
                                <option value="<?php echo $tg['id']; ?>" <?php echo $member['group_id'] == $tg['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($tg['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">تغییر عکس (اختیاری)</label>
                        <input type="file" name="photo" class="form-control bg-light" accept="image/*">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold">وضعیت عضویت</label>
                        <select name="status" class="form-select bg-light">
                            <option value="active" <?php echo $member['status'] === 'active' ? 'selected' : ''; ?>>فعال</option>
                            <option value="inactive" <?php echo $member['status'] === 'inactive' ? 'selected' : ''; ?>>غیرفعال</option>
                        </select>
                    </div>

                    <div class="col-12 text-end pt-3">
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-3">
                            <i class="bi bi-check-lg ms-1"></i> ذخیره تغییرات
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>