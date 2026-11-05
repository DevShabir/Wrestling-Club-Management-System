<?php
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

$success = '';
$error = '';
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$currentUser = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName        = trim($_POST['full_name'] ?? '');
    $username        = trim($_POST['username'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($currentPassword !== $currentUser['password']) {
        $error = 'رمز عبور فعلی نادرست است.';
    } elseif (!empty($newPassword) && strlen($newPassword) < 4) {
        $error = 'رمز عبور جدید باید حداقل ۴ کاراکتر باشد.';
    } elseif (!empty($newPassword) && $newPassword !== $confirmPassword) {
        $error = 'رمز عبور جدید با تکرار آن یکسان نیست.';
    } else {
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmtCheck->execute([$username, $userId]);
        if ($stmtCheck->rowCount() > 0) {
            $error = 'این نام کاربری قبلاً انتخاب شده است.';
        } else {
            $finalPassword = !empty($newPassword) ? $newPassword : $currentUser['password'];
            $photoName = $currentUser['photo'];

            // آپلود عکس پروفایل
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                if (!is_dir('uploads/users')) {
                    mkdir('uploads/users', 0777, true);
                }
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $photoName = 'user_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/users/' . $photoName);
            }

            $updateStmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, password = ?, photo = ? WHERE id = ?");
            $updateStmt->execute([$fullName, $username, $finalPassword, $photoName, $userId]);

            $_SESSION['full_name'] = $fullName;
            $_SESSION['username']  = $username;

            $success = 'اطلاعات و عکس پروفایل با موفقیت به‌روزرسانی شد.';
            
            $currentUser['full_name'] = $fullName;
            $currentUser['username']  = $username;
            $currentUser['password']  = $finalPassword;
            $currentUser['photo']     = $photoName;
        }
    }
}
?>

<div class="main-wrapper">
    <?php require_once 'includes/navbar.php'; ?>

    <div class="content-container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1">تنظیمات حساب و پروفایل مدیر</h4>
                <p class="text-muted small mb-0">تغییر عکس، نام کاربری، رمز عبور و مشخصات مدیر سیستم</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success border-0 py-2 small d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-check-circle-fill"></i>
                            <span><?php echo htmlspecialchars($success); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger border-0 py-2 small d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="profile.php" method="POST" enctype="multipart/form-data">
                        <!-- بخش عکس پروفایل -->
                        <div class="text-center mb-4">
                            <?php if (!empty($currentUser['photo']) && file_exists('uploads/users/' . $currentUser['photo'])): ?>
                                <img src="uploads/users/<?php echo htmlspecialchars($currentUser['photo']); ?>" class="rounded-circle border border-2 border-primary object-fit-cover shadow-sm mb-2" style="width: 85px; height: 85px;">
                            <?php else: ?>
                                <div class="rounded-circle bg-light border d-inline-flex align-items-center justify-content-center text-secondary mb-2" style="width: 85px; height: 85px; font-size: 2.5rem;">
                                    <i class="bi bi-person"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <label class="btn btn-sm btn-outline-primary px-3 rounded-3 cursor-pointer">
                                    <i class="bi bi-camera-fill ms-1"></i> انتخاب تصویر جدید
                                    <input type="file" name="photo" class="d-none" accept="image/*" onchange="this.form.submit()">
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">نام کامل مدیر</label>
                            <input type="text" name="full_name" class="form-control bg-light" value="<?php echo htmlspecialchars($currentUser['full_name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">نام کاربری (جهت ورود به سیستم)</label>
                            <input type="text" name="username" class="form-control bg-light" value="<?php echo htmlspecialchars($currentUser['username']); ?>" required>
                        </div>

                        <hr class="my-4 text-muted">

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-danger">رمز عبور فعلی (جهت تایید هویت)</label>
                            <input type="password" name="current_password" class="form-control bg-light" placeholder="رمز فعلی را بنویسید" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">رمز عبور جدید</label>
                            <input type="password" name="new_password" class="form-control bg-light" placeholder="در صورت عدم تمایل به تغییر، خالی بگذارید">
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">تکرار رمز عبور جدید</label>
                            <input type="password" name="confirm_password" class="form-control bg-light" placeholder="تکرار رمز جدید">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3 shadow-sm">
                            <i class="bi bi-shield-check ms-1"></i> ذخیره تغییرات
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>