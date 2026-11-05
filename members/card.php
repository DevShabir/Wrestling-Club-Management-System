<?php
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/jdf.php';
require_once '../includes/sidebar.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT m.*, tg.name AS group_name, c.full_name AS coach_name 
        FROM members m 
        LEFT JOIN training_groups tg ON m.group_id = tg.id 
        LEFT JOIN coaches c ON tg.coach_id = c.id 
        WHERE m.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$member = $stmt->fetch();

if (!$member) {
    header('Location: index.php');
    exit;
}
?>

<style>
    .id-card-wrapper {
        width: 360px;
        height: 540px;
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }

    .id-card-header {
        background: linear-gradient(135deg, #1e3a8a, #2563eb);
        color: #ffffff;
        padding: 20px 16px 45px 16px;
        text-align: center;
        position: relative;
    }

    .id-card-avatar-box {
        position: absolute;
        bottom: -45px;
        left: 50%;
        transform: translateX(-50%);
        width: 90px;
        height: 90px;
        border-radius: 50%;
        border: 4px solid #ffffff;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        background: #ffffff;
    }

    .id-card-avatar-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .id-card-body {
        padding: 55px 20px 20px 20px;
    }

    .id-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        border-bottom: 1px dashed #e2e8f0;
        font-size: 0.85rem;
    }

    .id-card-footer {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 12px 16px;
        position: absolute;
        bottom: 0;
        width: 100%;
    }

    @media print {
        body * {
            visibility: hidden;
        }

        .main-wrapper,
        .content-container {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        .print-area,
        .print-area * {
            visibility: visible;
        }

        .print-area {
            position: absolute;
            left: 50%;
            top: 20px;
            transform: translateX(-50%);
            margin: 0;
            box-shadow: none !important;
            border: 1px solid #94a3b8 !important;
        }

        .no-print {
            display: none !important;
        }
    }
</style>

<div class="main-wrapper">
    <?php require_once '../includes/navbar.php'; ?>

    <div class="content-container">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 no-print">
            <div>
                <h4 class="fw-bold mb-1">کارت عضویت شاگرد (ID Card)</h4>
                <p class="text-muted small mb-0">کارت رسمی ورزشکار: <?php echo htmlspecialchars($member['full_name']); ?></p>
            </div>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-outline-secondary px-3 py-2 rounded-3">
                    <i class="bi bi-arrow-right ms-1"></i> بازگشت به لیست
                </a>
                <button type="button" class="btn btn-primary px-4 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" onclick="window.print()">
                    <i class="bi bi-printer-fill fs-6"></i>
                    <span>چاپ کارت (Print)</span>
                </button>
            </div>
        </div>

        <div class="d-flex justify-content-center py-4">
            <div class="id-card-wrapper print-area">
                <div class="id-card-header">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                        <i class="bi bi-trophy-fill fs-4 text-warning"></i>
                        <h6 class="fw-bold m-0" style="font-size: 0.95rem;">کلپ ورزشی پهلوانی</h6>
                    </div>
                    <span class="text-light opacity-75" style="font-size: 0.72rem;">کارت هویت رسمی ورزشکار</span>

                    <div class="id-card-avatar-box">
                        <?php if (!empty($member['photo']) && file_exists('../uploads/members/' . $member['photo'])): ?>
                            <img src="../uploads/members/<?php echo htmlspecialchars($member['photo']); ?>" alt="عکس">
                        <?php else: ?>
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-secondary">
                                <i class="bi bi-person fs-1"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="id-card-body">
                    <div class="text-center mb-3">
                        <h5 class="fw-bold m-0 text-dark"><?php echo htmlspecialchars($member['full_name']); ?></h5>
                        <span class="badge bg-primary-subtle text-primary mt-1 px-3 py-1" style="font-size: 0.75rem;">کد: <?php echo htmlspecialchars($member['member_code']); ?></span>
                    </div>

                    <div class="id-info-row">
                        <span class="text-muted">نام پدر:</span>
                        <span class="fw-bold text-dark"><?php echo !empty($member['father_name']) ? htmlspecialchars($member['father_name']) : '---'; ?></span>
                    </div>

                    <div class="id-info-row">
                        <span class="text-muted">صنف تمرینی:</span>
                        <span class="fw-bold text-dark"><?php echo !empty($member['group_name']) ? htmlspecialchars($member['group_name']) : 'عمومی'; ?></span>
                    </div>

                    <div class="id-info-row">
                        <span class="text-muted">استاد مسئول:</span>
                        <span class="fw-bold text-dark"><?php echo !empty($member['coach_name']) ? htmlspecialchars($member['coach_name']) : '---'; ?></span>
                    </div>

                    <div class="id-info-row">
                        <span class="text-muted">شماره تماس:</span>
                        <span class="fw-bold text-dark" dir="ltr"><?php echo htmlspecialchars($member['phone']); ?></span>
                    </div>

                    <!-- کد جدید: -->
                    <div class="id-info-row border-bottom-0">
                        <span class="text-muted">تاریخ ثبت‌ نام:</span>
                        <span class="fw-bold text-success"><?php echo shamsi_date_afghan($member['registered_at']); ?></span>
                    </div>
                </div>

                <div class="id-card-footer d-flex align-items-center justify-content-between">
                    <div class="text-end">
                        <span class="d-block text-muted" style="font-size: 0.65rem;">امضای مدیریت:</span>
                        <span class="fw-bold text-dark" style="font-size: 0.75rem;">مهر و امضا</span>
                    </div>
                    <div>
                        <div class="bg-white border rounded p-1 px-2 text-center">
                            <i class="bi bi-qr-code fs-4 text-dark"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>