document.addEventListener('DOMContentLoaded', function () {
    // پیام تایید حذف به زبان دری
    const deleteButtons = document.querySelectorAll('.btn-confirm-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            const message = document.documentElement.lang === 'en'
                ? 'Are you sure you want to delete this item?'
                : 'آیا مطمئن هستید که می‌خواهید این مورد را حذف کنید؟';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});