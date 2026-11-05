<?php
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

// دریافت گروه‌ها برای فیلتر
$groupsList = $pdo->query("SELECT id, name FROM training_groups ORDER BY name ASC")->fetchAll();

// فیلترها و جستجو
$search = trim($_GET['search'] ?? '');
$groupFilter = trim($_GET['group_id'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$sql = "SELECT m.*, tg.name AS group_name 
        FROM members m 
        LEFT JOIN training_groups tg ON m.group_id = tg.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (m.full_name LIKE ? OR m.phone LIKE ? OR m.member_code LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($groupFilter)) {
    $sql .= " AND m.group_id = ?";
    $params[] = $groupFilter;
}

if (!empty($statusFilter)) {
    $sql .= " AND m.status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY m.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll();
?>

<div class="main-wrapper">
    <?php require_once '../includes/navbar.php'; ?>

    <div class="content-container">
        <!-- پیام‌های عملیات -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-check-circle-fill"></i>
                <span>شاگرد جدید با موفقیت ثبت شد.</span>
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-trash-fill"></i>
                <span>شاگرد با موفقیت از سیستم حذف گردید.</span>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">مدیریت ورزشکاران (شاگردان)</h4>
                <p class="text-muted small mb-0">نمایش لیست عمومی اعضای کلپ و چاپ کارت هویت</p>
            </div>
            <a href="create.php" class="btn btn-primary px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-person-plus-fill"></i>
                <span>ثبت شاگرد جدید</span>
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3 p-4">
            <!-- نوار جستجو و فیلتر -->
            <form method="GET" action="index.php" class="row g-3 mb-4 align-items-center justify-content-between">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-start-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control border-end-0 bg-light" placeholder="جستجو بر اساس نام، شماره یا کد عضویت...">
                    </div>
                </div>
                <div class="col-12 col-md-5 d-flex gap-2">
                    <select name="group_id" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="">تمام گروه‌ها</option>
                        <?php foreach ($groupsList as $g): ?>
                            <option value="<?php echo $g['id']; ?>" <?php echo $groupFilter == $g['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($g['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="status" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>فعال</option>
                        <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>غیرفعال</option>
                    </select>
                    <?php if (!empty($search) || !empty($groupFilter) || !empty($statusFilter)): ?>
                        <a href="index.php" class="btn btn-light" title="پاک کردن فیلتر"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">عکس</th>
                            <th scope="col">کد عضویت</th>
                            <th scope="col">نام و تخلص</th>
                            <th scope="col">وزن</th>
                            <th scope="col">گروه تمرینی</th>
                            <th scope="col">وضعیت</th>
                            <th scope="col" class="text-center" style="width: 140px;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php if (empty($members)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">هیچ شاگردی ثبت نشده است.</td>
                            </tr>
                        <?php else: ?>
                            <?php $i = 1;
                            foreach ($members as $member): ?>
                                <tr>
                                    <td class="text-muted fw-bold"><?php echo $i++; ?></td>
                                    <td>
                                        <?php if (!empty($member['photo']) && file_exists('../uploads/members/' . $member['photo'])): ?>
                                            <img src="../uploads/members/<?php echo htmlspecialchars($member['photo']); ?>" alt="عکس" class="rounded-circle object-fit-cover border" style="width: 40px; height: 40px;">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border text-secondary" style="width: 40px; height: 40px;">
                                                <i class="bi bi-person fs-5"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($member['member_code']); ?></span></td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($member['full_name']); ?></td>
                                    <td class="text-muted"><?php echo !empty($member['weight']) ? htmlspecialchars($member['weight']) . ' کیلوگرم' : '---'; ?></td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-dark">
                                            <?php echo !empty($member['group_name']) ? htmlspecialchars($member['group_name']) : 'تعیین نشده'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($member['status'] === 'active'): ?>
                                            <span class="badge-status badge-active">فعال</span>
                                        <?php else: ?>
                                            <span class="badge-status badge-inactive">غیرفعال</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <!-- دکمه کارت هویت داینامیک -->
                                        <a href="card.php?id=<?php echo $member['id']; ?>" class="btn btn-sm btn-outline-success p-1 px-2 me-1" title="چاپ کارت هویت">
                                            <i class="bi bi-person-badge"></i>
                                        </a>
                                        <!-- دکمه ویرایش -->
                                        <a href="edit.php?id=<?php echo $member['id']; ?>" class="btn btn-sm btn-outline-primary p-1 px-2 me-1" title="ویرایش">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <!-- دکمه حذف -->
                                        <a href="delete.php?id=<?php echo $member['id']; ?>" class="btn btn-sm btn-outline-danger p-1 px-2" onclick="return confirm('آیا از حذف این شاگرد مطمئن هستید؟')" title="حذف">
                                            <i class="bi bi-trash"></i>
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
</div>

<?php require_once '../includes/footer.php'; ?>