document.addEventListener('DOMContentLoaded', function() {
    // دکمه انتخاب همه
    document.getElementById('selectAllBtn').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.form-check-input');
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
    });

    // دکمه حذف همه انتخاب‌ها
    document.getElementById('deselectAllBtn').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.form-check-input');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
    });
});
