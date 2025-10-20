# 🔄 RMS Core - Database Schema Shift Command

## 📦 بخشی از RMS Core Package

این Command بخشی از **RMS Core Package** است و به صورت خودکار در تمام پروژه‌های مبتنی بر RMS در دسترس است.

---

## ✨ ویژگی‌ها

- ✅ **Smart Migration Detection** - خودکار تشخیص migrationهای تکراری
- ✅ **Protected Tables** - محافظت از جداول حیاتی (users, settings, admins)
- ✅ **Dry Run Mode** - تست کامل بدون تغییر
- ✅ **Detailed Reporting** - گزارش جامع از تمام تغییرات
- ✅ **Safe by Default** - هیچ تغییری بدون `--apply` ایجاد نمی‌شود

---

## 🚀 نصب خودکار

این Command همراه با RMS Core نصب می‌شود:

```bash
composer require rms/core
```

بعد از نصب، دستور `db:shift` به صورت خودکار در دسترس است:

```bash
php artisan db:shift --help
```

---

##مستندات کامل

برای مستندات کامل، فایل زیر را مطالعه کنید:

📄 **[DB_SHIFT_COMMAND.md](./docs/DB_SHIFT_COMMAND.md)**

---

## 🎯 استفاده سریع

### مقایسه دو دیتابیس
```bash
php artisan db:shift --a=dev_db --b=prod_db
```

### تست بدون تغییر
```bash
php artisan db:shift --a=dev_db --b=prod_db --apply --dry-run
```

### اعمال تغییرات
```bash
php artisan db:shift --a=dev_db --b=prod_db --apply
```

---

## ⚠️ نکات امنیتی

1. **همیشه backup بگیرید** قبل از `--apply`
2. **ابتدا بدون `--apply`** اجرا کنید
3. **از `--dry-run`** برای تست استفاده کنید
4. **جداول حیاتی را ignore کنید**

---

## 🆘 پشتیبانی

- 📖 مستندات کامل: [DB_SHIFT_COMMAND.md](./docs/DB_SHIFT_COMMAND.md)
- 🐛 گزارش باگ: GitHub Issues
- 💬 پشتیبانی: RMS Team

---

## 📜 لایسنس

بخشی از RMS Core Package - تحت لایسنس MIT

---

**ساخته شده با ❤️ توسط تیم RMS**
