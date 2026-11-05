<?php
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

// خواندن لیست مربیان فعال برای استفاده در فرم‌های مودال
$coachesStmt = $pdo->query("SELECT id, full_name FROM coaches WHERE status = 'active' ORDER BY full_name ASC");
$allCoaches = $coachesStmt->fetchAll();

// دریافت گروه‌ها با نام استاد و تعداد شاگردان عضو
$sql = "SELECT tg.*, c.full_name AS coach_name, 
        (SELECT COUNT(*) FROM members WHERE members.group_id = tg.id) AS member_count 
        FROM training_groups tg 
        LEFT JOIN coaches c ON tg.coach_id = c.id 
        ORDER BY tg.id DESC";
$groups = $pdo->query($sql)->fetchAll();

// آمار کارت‌های بالا
$totalActiveGroups = 0;
$totalAssignedMembers = 0;
foreach ($groups as $g) {
    if ($g['status'] === 'active') $totalActiveGroups++;
    $totalAssignedMembers += (int)$g['member_count'];
}
?>

<div class="main-wrapper">
    <?php require_once '../includes/navbar.php'; ?>

    <div class="content-container">
        <!-- پیام‌های عملیات -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-check-circle-fill"></i>
                <span>صنف تمرینی جدید با موفقیت اضافه شد.</span>
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
            <div class="alert alert-primary border-0 shadow-sm d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-check-circle-fill"></i>
                <span>مشخصات صنف تمرینی بروزرسانی شد.</span>
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-trash-fill"></i>
                <span>صنف تمرینی با موفقیت حذف گردید.</span>
            </div>
        <?php endif; ?>

        <!-- هدر بالای جدول -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">گروه‌ها و تایم‌های تمرین</h4>
                <p class="text-muted small mb-0">مدیریت صنف‌های آموزشی، تایم‌های تمرینی، اساتید مسئول و شاگردان</p>
            </div>
            <button type="button" class="btn btn-primary px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addGroupModal">
                <i class="bi bi-plus-circle-fill"></i>
                <span>ایجاد صنف / گروه جدید</span>
            </button>
        </div>

        <!-- کارت‌های خلاصه تایم‌ها -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-collection-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">مجموع صنف‌های فعال</span>
                        <h4 class="fw-bold mb-0 text-dark"><?php echo $totalActiveGroups; ?> صنف</h4>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">شاگردان تقسیم‌شده</span>
                        <h4 class="fw-bold mb-0 text-success"><?php echo $totalAssignedMembers; ?> شاگرد</h4>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="stat-card">
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">تعداد کل صنف‌ها</span>
                        <h4 class="fw-bold mb-0 text-info"><?php echo count($groups); ?> تایم ثبت‌شده</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- جدول گروه‌ها -->
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th scope="col" style="width: 50px;">#</th>
                            <th scope="col">نام صنف / گروه</th>
                            <th scope="col">سطح</th>
                            <th scope="col">استاد مسئول</th>
                            <th scope="col">روزهای تمرین</th>
                            <th scope="col">ساعت تمرین</th>
                            <th scope="col">شاگردان</th>
                            <th scope="col">وضعیت</th>
                            <th scope="col" class="text-center" style="width: 120px;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php if (empty($groups)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">هیچ صنف یا گروه تمرینی ثبت نشده است.</td>
                            </tr>
                        <?php else: ?>
                            <?php $i = 1; foreach ($groups as $group): ?>
                                <tr>
                                    <td class="text-muted fw-bold"><?php echo $i++; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($group['name']); ?></td>
                                    <td>
                                        <?php 
                                            if ($group['level'] === 'beginner') echo '<span class="badge bg-secondary-subtle text-dark">مبتدی</span>';
                                            elseif ($group['level'] === 'intermediate') echo '<span class="badge bg-info-subtle text-info">متوسط</span>';
                                            else echo '<span class="badge bg-danger-subtle text-danger">پیشرفته</span>';
                                        ?>
                                    </td>
                                    <td class="fw-semibold text-primary">
                                        <?php echo !empty($group['coach_name']) ? htmlspecialchars($group['coach_name']) : '<span class="text-muted small">تعیین نشده</span>'; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            if ($group['days'] === 'odd') echo 'روزهای طاق (شنبه، دوشنبه، چهارشنبه)';
                                            elseif ($group['days'] === 'even') echo 'روزهای جفت (یکشنبه، سه‌شنبه، پنج‌شنبه)';
                                            else echo 'همه روزه (به جز جمعه)';
                                        ?>
                                    </td>
                                    <td class="text-muted" dir="ltr">
                                        <?php echo date("h:i A", strtotime($group['start_time'])) . ' - ' . date("h:i A", strtotime($group['end_time'])); ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo (int)$group['member_count']; ?> شاگرد</span></td>
                                    <td>
                                        <?php if ($group['status'] === 'active'): ?>
                                            <span class="badge-status badge-active">فعال</span>
                                        <?php else: ?>
                                            <span class="badge-status badge-inactive">غیرفعال</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <!-- دکمه ویرایش -->
                                        <button type="button" class="btn btn-sm btn-outline-primary p-1 px-2 me-1" data-bs-toggle="modal" data-bs-target="#editGroupModal<?php echo $group['id']; ?>" title="ویرایش">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <!-- دکمه حذف -->
                                        <a href="delete.php?id=<?php echo $group['id']; ?>" class="btn btn-sm btn-outline-danger p-1 px-2" onclick="return confirm('آیا از حذف این صنف تمرینی مطمئن هستید؟')" title="حذف">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>

                                <!-- مودال ویرایش صنف -->
                                <div class="modal fade" id="editGroupModal<?php echo $group['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header border-bottom-0 d-flex justify-content-between align-items-center">
                                                <h5 class="modal-title fw-bold m-0">ویرایش صنف تمرینی</h5>
                                                <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="بستن"></button>
                                            </div>
                                            <form action="update.php" method="POST">
                                                <input type="hidden" name="id" value="<?php echo $group['id']; ?>">
                                                <div class="modal-body pt-1">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">نام صنف یا گروه <span class="text-danger">*</span></label>
                                                        <input type="text" name="name" class="form-control bg-light" value="<?php echo htmlspecialchars($group['name']); ?>" required>
                                                    </div>

                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label small fw-bold">سطح گروه</label>
                                                            <select name="level" class="form-select bg-light">
                                                                <option value="beginner" <?php echo $group['level'] === 'beginner' ? 'selected' : ''; ?>>مبتدی</option>
                                                                <option value="intermediate" <?php echo $group['level'] === 'intermediate' ? 'selected' : ''; ?>>متوسط</option>
                                                                <option value="advanced" <?php echo $group['level'] === 'advanced' ? 'selected' : ''; ?>>پیشرفته</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small fw-bold">استاد مسئول</label>
                                                            <select name="coach_id" class="form-select bg-light">
                                                                <option value="">بدون استاد</option>
                                                                <?php foreach ($allCoaches as $c): ?>
                                                                    <option value="<?php echo $c['id']; ?>" <?php echo $group['coach_id'] == $c['id'] ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($c['full_name']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">روزهای تمرین</label>
                                                        <select name="days" class="form-select bg-light">
                                                            <option value="odd" <?php echo $group['days'] === 'odd' ? 'selected' : ''; ?>>روزهای طاق (شنبه، دوشنبه، چهارشنبه)</option>
                                                            <option value="even" <?php echo $group['days'] === 'even' ? 'selected' : ''; ?>>روزهای جفت (یکشنبه، سه‌شنبه، پنج‌شنبه)</option>
                                                            <option value="everyday" <?php echo $group['days'] === 'everyday' ? 'selected' : ''; ?>>همه روزه (به جز جمعه)</option>
                                                        </select>
                                                    </div>

                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label small fw-bold">ساعت شروع <span class="text-danger">*</span></label>
                                                            <input type="time" name="start_time" class="form-control bg-light" value="<?php echo htmlspecialchars($group['start_time']); ?>" required>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small fw-bold">ساعت ختم <span class="text-danger">*</span></label>
                                                            <input type="time" name="end_time" class="form-control bg-light" value="<?php echo htmlspecialchars($group['end_time']); ?>" required>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">وضعیت صنف</label>
                                                        <select name="status" class="form-select bg-light">
                                                            <option value="active" <?php echo $group['status'] === 'active' ? 'selected' : ''; ?>>فعال</option>
                                                            <option value="inactive" <?php echo $group['status'] === 'inactive' ? 'selected' : ''; ?>>غیرفعال</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top-0 pt-0 d-flex justify-content-end gap-2">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">انصراف</button>
                                                    <button type="submit" class="btn btn-primary px-4">بروزرسانی صنف</button>
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

<!-- مودال ثبت گروه جدید -->
<div class="modal fade" id="addGroupModal" tabindex="-1" aria-labelledby="addGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-bold m-0" id="addGroupModalLabel">ایجاد صنف / تایم تمرین جدید</h5>
                <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <form action="store.php" method="POST">
                <div class="modal-body pt-1">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">نام صنف یا گروه <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control bg-light" placeholder="مثلاً: صنف نونهالان و پایه‌ها" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">سطح گروه</label>
                            <select name="level" class="form-select bg-light">
                                <option value="beginner" selected>مبتدی</option>
                                <option value="intermediate">متوسط</option>
                                <option value="advanced">پیشرفته</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">استاد مسئول</label>
                            <select name="coach_id" class="form-select bg-light">
                                <option value="">بدون استاد / انتخاب بعداً</option>
                                <?php foreach ($allCoaches as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['full_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">روزهای تمرین <span class="text-danger">*</span></label>
                        <select name="days" class="form-select bg-light" required>
                            <option value="odd">روزهای طاق (شنبه، دوشنبه، چهارشنبه)</option>
                            <option value="even">روزهای جفت (یکشنبه، سه‌شنبه، پنج‌شنبه)</option>
                            <option value="everyday">همه روزه (به جز جمعه)</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">ساعت شروع <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control bg-light" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">ساعت ختم <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control bg-light" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">وضعیت صنف</label>
                        <select name="status" class="form-select bg-light">
                            <option value="active" selected>فعال</option>
                            <option value="inactive">غیرفعال</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary px-4">ثبت صنف</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>