<?php
// تشخیص خودکار پیشوند مسیر با توجه به عمق پوشه جاری
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
$basePath = ($currentDir === 'wrestling-club' || $currentDir === 'htdocs' || $currentDir === 'www') ? '' : '../';
?>

<!-- Bootstrap 5 RTL Bundle JS -->
<script src="<?php echo $basePath; ?>assets/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js -->
<script src="<?php echo $basePath; ?>assets/js/chart.min.js"></script>

<!-- Custom JS -->
<script src="<?php echo $basePath; ?>assets/js/main.js"></script>
<?php if (!empty($isEnglish)): ?>
    <script src="<?php echo $basePath; ?>assets/js/translations-en.js"></script>
<?php endif; ?>
</body>
</html>