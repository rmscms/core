/**
 * Modern Navbar JavaScript
 * کنترل عملکرد منوی floating و تعاملات navbar
 */

document.addEventListener('DOMContentLoaded', function() {
    const addButton = document.getElementById('addButton');
    const floatingMenu = document.getElementById('floatingMenu');
    const addIcon = document.getElementById('addIcon');
    let isMenuOpen = false;

    // کلیک روی دکمه افزودن
    addButton.addEventListener('click', function() {
        if (isMenuOpen) {
            // بستن منو
            floatingMenu.classList.remove('show');
            addIcon.classList.remove('rotate');
            isMenuOpen = false;
        } else {
            // باز کردن منو
            floatingMenu.classList.add('show');
            addIcon.classList.add('rotate');
            isMenuOpen = true;
        }
    });

    // کلیک روی گزینه‌های منو
    const floatingItems = document.querySelectorAll('.floating-item');
    floatingItems.forEach(item => {
        item.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            console.log('انتخاب شد:', action);

            // اینجا می‌تونی عملکرد مورد نظرت رو اضافه کنی
            switch(action) {
                case 'add-payment':
                    alert('پرداخت جدید');
                    break;
                case 'add-ticket':
                    alert('تیکت جدید');
                    break;
                case 'add-account':
                    alert('اکانت جدید');
                    break;
            }

            // بستن منو بعد از انتخاب
            floatingMenu.classList.remove('show');
            addIcon.classList.remove('rotate');
            isMenuOpen = false;
        });
    });

    // بستن منو با کلیک خارج از آن
    document.addEventListener('click', function(event) {
        if (!addButton.contains(event.target) && !floatingMenu.contains(event.target)) {
            floatingMenu.classList.remove('show');
            addIcon.classList.remove('rotate');
            isMenuOpen = false;
        }
    });
});