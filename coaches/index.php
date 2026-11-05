<?php
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

// خواندن استادان از دیتابیس
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$sql = "SELECT * FROM coaches WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (full_name LIKE ? OR phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($statusFilter)) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$coaches = $stmt->fetchAll();
?>

<div class="main-wrapper">
    <?php require_once '../includes/navbar.php'; ?>

    <div class="content-container">
        <!-- پیام‌های عملیات -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-check-circle-fill"></i>
                <span>استاد جدید با موفقیت در دیتابیس ثبت شد.</span>
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="alert alert-primary border-0 shadow-sm d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-check-circle-fill"></i>
                <span>معلومات استاد با موفقیت ویرایش و بروزرسانی شد.</span>
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-trash-fill"></i>
                <span>مشخصات استاد با موفقیت از سیستم حذف گردید.</span>
            </div>
        <?php endif; ?>

        <!-- هدر بالای جدول و دکمه افزودن -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">مدیریت استادان (مربیان)</h4>
                <p class="text-muted small mb-0">لیست استادان کلپ، رشته‌های تخصصی و سوابق مربی‌گری</p>
            </div>
            <button type="button" class="btn btn-primary px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addCoachModal">
                <i class="bi bi-person-plus-fill"></i>
                <span>ثبت استاد جدید</span>
            </button>
        </div>

        <!-- کارت فیلتر و جدول مربیان -->
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <form method="GET" action="index.php" class="row g-3 mb-4 align-items-center justify-content-between">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-start-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control border-end-0 bg-light" placeholder="جستجو بر اساس نام مربی یا شماره تماس...">
                    </div>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <select name="status" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="">تمام وضعیت‌ها</option>
                        <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>فعال</option>
                        <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>غیرفعال</option>
                    </select>
                    <?php if (!empty($search) || !empty($statusFilter)): ?>
                        <a href="index.php" class="btn btn-light" title="پاک کردن فیلتر"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th scope="col" style="width: 50px;">#</th>
                            <th scope="col" style="width: 70px;">عکس</th>
                            <th scope="col">نام و تخلص استاد</th>
                            <th scope="col">شماره تماس</th>
                            <th scope="col">رشته تخصصی</th>
                            <th scope="col">وضعیت</th>
                            <th scope="col" class="text-center" style="width: 120px;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php if (empty($coaches)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">هیچ استادی در دیتابیس یافت نشد.</td>
                            </tr>
                        <?php else: ?>
                            <?php $i = 1; foreach ($coaches as $coach): ?>
                                <tr>
                                    <td class="text-muted fw-bold"><?php echo $i++; ?></td>
                                    <td>
                                        <?php if (!empty($coach['photo']) && file_exists('../uploads/coaches/' . $coach['photo'])): ?>
                                            <img src="../uploads/coaches/<?php echo htmlspecialchars($coach['photo']); ?>" alt="عکس" class="rounded-circle object-fit-cover border" style="width: 40px; height: 40px;">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border text-secondary" style="width: 40px; height: 40px;">
                                                <i class="bi bi-person fs-5"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($coach['full_name']); ?></td>
                                    <td class="text-muted" dir="ltr"><?php echo htmlspecialchars($coach['phone']); ?></td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">
                                            <?php echo !empty($coach['specialization']) ? htmlspecialchars($coach['specialization']) : 'عمومی'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($coach['status'] === 'active'): ?>
                                            <span class="badge-status badge-active">فعال</span>
                                        <?php else: ?>
                                            <span class="badge-status badge-inactive">غیرفعال</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <!-- دکمه ویرایش -->
                                        <button type="button" class="btn btn-sm btn-outline-primary p-1 px-2 me-1" data-bs-toggle="modal" data-bs-target="#editCoachModal<?php echo $coach['id']; ?>" title="ویرایش اطلاعات">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <!-- دکمه حذف -->
                                        <a href="delete.php?id=<?php echo $coach['id']; ?>" class="btn btn-sm btn-outline-danger p-1 px-2" onclick="return confirm('آیا از حذف این استاد مطمئن هستید؟')" title="حذف">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>

                                <!-- مودال ویرایش برای هر استاد -->
                                <div class="modal fade" id="editCoachModal<?php echo $coach['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header border-bottom-0 d-flex justify-content-between align-items-center">
                                                <h5 class="modal-title fw-bold m-0">ویرایش معلومات استاد</h5>
                                                <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="بستن"></button>
                                            </div>
                                            <form action="update.php" method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="id" value="<?php echo $coach['id']; ?>">
                                                <div class="modal-body pt-1">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">نام و تخلص استاد <span class="text-danger">*</span></label>
                                                        <input type="text" name="full_name" class="form-control bg-light" value="<?php echo htmlspecialchars($coach['full_name']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">شماره تماس <span class="text-danger">*</span></label>
                                                        <input type="text" name="phone" class="form-control bg-light" value="<?php echo htmlspecialchars($coach['phone']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-muted">رشته تخصصی</label>
                                                        <input type="text" name="specialization" class="form-control bg-light" value="<?php echo htmlspecialchars($coach['specialization'] ?? ''); ?>">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-muted">تغییر عکس (اختیاری)</label>
                                                        <input type="file" name="photo" class="form-control bg-light" accept="image/png, image/jpeg, image/webp">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">وضعیت فعالیت</label>
                                                        <select name="status" class="form-select bg-light">
                                                            <option value="active" <?php echo $coach['status'] === 'active' ? 'selected' : ''; ?>>فعال</option>
                                                            <option value="inactive" <?php echo $coach['status'] === 'inactive' ? 'selected' : ''; ?>>غیرفعال</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top-0 pt-0 d-flex justify-content-end gap-2">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">انصراف</button>
                                                    <button type="submit" class="btn btn-primary px-4">بروزرسانی معلومات</button>
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

<!-- مودال ثبت استاد جدید -->
<div class="modal fade" id="addCoachModal" tabindex="-1" aria-labelledby="addCoachModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-bold m-0" id="addCoachModalLabel">ثبت استاد (مربی) جدید</h5>
                <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <form action="store.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body pt-1">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">نام و تخلص استاد <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control bg-light" placeholder="مثلاً: استاد رحمان سعیدی" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">شماره تماس <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control bg-light" placeholder="مثلاً: 0788XXXXXX" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">رشته تخصصی <span class="badge bg-light text-secondary border fw-normal">اختیاری</span></label>
                        <input type="text" name="specialization" class="form-control bg-light" placeholder="مثلاً: کشتی آزاد، فرنگی یا بدنسازی">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">عکس مربی <span class="badge bg-light text-secondary border fw-normal">اختیاری</span></label>
                        <input type="file" name="photo" class="form-control bg-light" accept="image/png, image/jpeg, image/webp">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">وضعیت فعالیت</label>
                        <select name="status" class="form-select bg-light">
                            <option value="active" selected>فعال</option>
                            <option value="inactive">غیرفعال</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary px-4">ذخیره مشخصات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>