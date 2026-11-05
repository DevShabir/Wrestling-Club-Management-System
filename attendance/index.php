<?php
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/jdf.php';

// تاریخ امروز به هجری شمسی (مثلاً: 1405/06/25)
$todayGregorian = date('Y-m-d');
$tParts = explode('-', $todayGregorian);
list($jy, $jm, $jd) = gregorian_to_shamsi((int)$tParts[0], (int)$tParts[1], (int)$tParts[2]);
$defaultShamsiDate = sprintf("%04d/%02d/%02d", $jy, $jm, $jd);

$selectedDate = trim($_GET['date'] ?? $defaultShamsiDate);
$selectedGroupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;

// دریافت گروه‌های فعال
$groups = $pdo->query("SELECT id, name FROM training_groups WHERE status = 'active' ORDER BY name ASC")->fetchAll();

if ($selectedGroupId === 0 && !empty($groups)) {
    $selectedGroupId = (int)$groups[0]['id'];
}

// دریافت شاگردان صنف و وضعیت حاضری آن‌ها
$members = [];
$totalCount = 0;
$presentCount = 0;
$absentCount = 0;

if ($selectedGroupId > 0) {
    $sql = "SELECT m.id, m.member_code, m.full_name, m.photo,
                   a.status AS attendance_status
            FROM members m
            LEFT JOIN attendances a ON m.id = a.member_id AND a.shamsi_date = ?
            WHERE m.group_id = ? AND m.status = 'active'
            ORDER BY m.full_name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$selectedDate, $selectedGroupId]);
    $members = $stmt->fetchAll();

    $totalCount = count($members);
    foreach ($members as $m) {
        if ($m['attendance_status'] === 'absent') {
            $absentCount++;
        } else {
            // در صورتی که حاضر ثبت شده یا بار اول است
            $presentCount++;
        }
    }
}

$attendancePercentage = $totalCount > 0 ? round(($presentCount / $totalCount) * 100) : 0;
?>

<div class="main-wrapper">
    <?php require_once '../includes/navbar.php'; ?>

    <div class="content-container">
        <!-- پیام ذخیره‌سازی -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'saved'): ?>
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-check-circle-fill"></i>
                <span>حاضری روزانه با موفقیت در دیتابیس ثبت شد.</span>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">حاضری روزانه شاگردان</h4>
                <p class="text-muted small mb-0">ثبت حضور و غیاب شاگردان بر اساس صنف تمرینی و تاریخ هجری شمسی</p>
            </div>
        </div>

        <!-- کارت‌های آمار زنده حاضری صنف -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">مجموع شاگردان صنف</span>
                        <h4 class="fw-bold mb-0 text-dark"><?php echo $totalCount; ?> نفر</h4>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">شاگردان حاضر</span>
                        <h4 class="fw-bold mb-0 text-success" id="stat-present"><?php echo $presentCount; ?> نفر</h4>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="bi bi-person-x-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">شاگردان غیرحاضر</span>
                        <h4 class="fw-bold mb-0 text-danger" id="stat-absent"><?php echo $absentCount; ?> نفر</h4>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-pie-chart-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">فیصدی نظم امروز</span>
                        <h4 class="fw-bold mb-0 text-info" id="stat-percent"><?php echo $attendancePercentage; ?>٪</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- نوار انتخاب صنف و تاریخ -->
        <div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
            <form method="GET" action="index.php" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <label class="form-label small fw-bold text-muted mb-1">انتخاب صنف تمرینی</label>
                    <select name="group_id" class="form-select bg-light" onchange="this.form.submit()">
                        <?php foreach ($groups as $g): ?>
                            <option value="<?php echo $g['id']; ?>" <?php echo $selectedGroupId == $g['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($g['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-muted mb-1">تاریخ هجری شمسی</label>
                    <input type="text" name="date" class="form-control bg-light" value="<?php echo htmlspecialchars($selectedDate); ?>" placeholder="1405/06/25">
                </div>

                <div class="col-12 col-md-3 pt-md-4">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-funnel ms-1"></i> نمایش شاگردان
                    </button>
                </div>
            </form>
        </div>

        <!-- جدول ثبت وضعیت -->
        <div class="card border-0 shadow-sm rounded-3 p-4">
            <?php if (empty($members)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                    <span>هیچ شاگرد فعالی در این صنف تمرینی ثبت نشده است.</span>
                </div>
            <?php else: ?>
                <form action="store.php" method="POST">
                    <input type="hidden" name="group_id" value="<?php echo $selectedGroupId; ?>">
                    <input type="hidden" name="shamsi_date" value="<?php echo htmlspecialchars($selectedDate); ?>">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted small">تعداد شاگردان صنف: <strong><?php echo count($members); ?> نفر</strong></span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="markAll('present')">حاضر کردن همه</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="markAll('absent')">غیرحاضر کردن همه</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th scope="col" style="width: 50px;">#</th>
                                    <th scope="col" style="width: 60px;">عکس</th>
                                    <th scope="col">نام و تخلص شاگرد</th>
                                    <th scope="col">کد عضویت</th>
                                    <th scope="col" class="text-center" style="width: 220px;">وضعیت حضور</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                <?php $i = 1; foreach ($members as $m): 
                                    $currentStatus = $m['attendance_status'] ?? 'present';
                                ?>
                                    <tr>
                                        <td class="text-muted fw-bold"><?php echo $i++; ?></td>
                                        <td>
                                            <?php if (!empty($m['photo']) && file_exists('../uploads/members/' . $m['photo'])): ?>
                                                <img src="../uploads/members/<?php echo htmlspecialchars($m['photo']); ?>" class="rounded-circle object-fit-cover border" style="width: 38px; height: 38px;">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border text-secondary" style="width: 38px; height: 38px;">
                                                    <i class="bi bi-person"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($m['full_name']); ?></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($m['member_code']); ?></span></td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <input type="radio" class="btn-check status-present" name="attendance[<?php echo $m['id']; ?>]" id="pres_<?php echo $m['id']; ?>" value="present" <?php echo $currentStatus === 'present' ? 'checked' : ''; ?> onchange="updateLiveStats()">
                                                <label class="btn btn-outline-success btn-sm px-3" for="pres_<?php echo $m['id']; ?>">حاضر</label>

                                                <input type="radio" class="btn-check status-absent" name="attendance[<?php echo $m['id']; ?>]" id="abs_<?php echo $m['id']; ?>" value="absent" <?php echo $currentStatus === 'absent' ? 'checked' : ''; ?> onchange="updateLiveStats()">
                                                <label class="btn btn-outline-danger btn-sm px-3" for="abs_<?php echo $m['id']; ?>">غیرحاضر</label>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 shadow-sm">
                            <i class="bi bi-check-circle ms-1"></i> ذخیره حاضری این صنف
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function markAll(status) {
    if (status === 'present') {
        document.querySelectorAll('.status-present').forEach(el => el.checked = true);
    } else {
        document.querySelectorAll('.status-absent').forEach(el => el.checked = true);
    }
    updateLiveStats();
}

function updateLiveStats() {
    const total = document.querySelectorAll('tbody tr').length;
    if (total === 0) return;

    const present = document.querySelectorAll('.status-present:checked').length;
    const absent = document.querySelectorAll('.status-absent:checked').length;
    const percent = Math.round((present / total) * 100);

    const presEl = document.getElementById('stat-present');
    const absEl = document.getElementById('stat-absent');
    const perEl = document.getElementById('stat-percent');

    if (presEl) presEl.innerText = present + ' نفر';
    if (absEl) absEl.innerText = absent + ' نفر';
    if (perEl) perEl.innerText = percent + '٪';
}
</script>

<?php require_once '../includes/footer.php'; ?>