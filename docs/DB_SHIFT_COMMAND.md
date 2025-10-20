# 🔄 Database Schema Shift Command

## 📖 معرفی

دستور `db:shift` یک ابزار قدرتمند برای انتقال ایمن schema از یک دیتابیس به دیتابیس دیگر است. این دستور برای سناریوهای زیر طراحی شده:

- 🔄 **Migration از محیط Development به Production**
- 🆕 **Setup دیتابیس جدید بدون اجرای migrationهای قدیمی**
- 🔒 **محافظت از جداول حیاتی** (users, settings)
- 🧠 **Smart Detection** برای جلوگیری از اجرای مجدد migrationها

---

## ⚙️ نصب

فایل `DbDiffCommand.php` را در مسیر زیر قرار دهید:
```
app/Console/Commands/DbDiffCommand.php
```

Laravel خودکار آن را شناسایی می‌کند.

---

## 🚀 استفاده

### 1️⃣ **مقایسه دو دیتابیس (بدون تغییر)**

```bash
php artisan db:shift --a=database_old --b=database_new
```

این دستور فقط تفاوت‌ها را نمایش می‌دهد و هیچ تغییری ایجاد نمی‌کند.

**خروجی شامل:**
- ✅ جداول موجود فقط در A
- ⚠️ جداول موجود فقط در B
- 📊 تفاوت ستون‌ها و index‌ها
- 📋 پیشنهادات برای اعمال تغییرات

---

### 2️⃣ **مشاهده جزئیات کامل**

```bash
php artisan db:shift --a=old_db --b=new_db --details
```

نمایش دقیق تفاوت‌های ستون‌ها (نوع داده، nullable، default و...)

---

### 3️⃣ **اعمال تغییرات (Production Mode)**

```bash
php artisan db:shift --a=dev_db --b=prod_db --apply
```

⚠️ **هشدار:** این دستور migrationها را اجرا می‌کند!

**چه اتفاقی می‌افتد:**
1. Migrationهای محافظت شده را mark as Ran می‌کند
2. Smart-skip: migrationهای تکراری را skip می‌کند
3. Migrationهای جدید را اجرا می‌کند
4. گزارش کامل نمایش می‌دهد

---

### 4️⃣ **تست بدون تغییر (Dry Run)**

```bash
php artisan db:shift --a=dev_db --b=prod_db --apply --dry-run
```

✅ **امن‌ترین حالت:** شبیه‌سازی کامل بدون هیچ تغییری در دیتابیس

---

### 5️⃣ **محافظت از جداول خاص**

```bash
php artisan db:shift --a=db1 --b=db2 --ignore=users,settings,admins
```

جداول مشخص شده را در برنامه ignore می‌کند.

---

### 6️⃣ **Ignore کردن migrationهای خاص**

```bash
php artisan db:shift \
  --a=old --b=new \
  --ignore-migrations=2025_01_20_create_logs_table,2025_01_21_add_custom_field \
  --apply
```

---

## 🧠 ویژگی‌های Smart Detection

### 1. **Smart Create-Table Detection**
اگر جدولی در دیتابیس مقصد وجود داشته باشد:
- ✅ Migration مربوط به `Schema::create()` آن جدول خودکار skip می‌شود
- ⚡ از خطای "Table already exists" جلوگیری می‌کند

### 2. **Smart Add-Column Detection**
اگر تمام ستون‌های یک migration موجود باشند:
- ✅ Migration مربوط به `Schema::table()` خودکار skip می‌شود
- 🎯 فقط migrationهای واقعاً لازم اجرا می‌شوند

### 3. **Protected Migrations**
Migrationهای حیاتی (مثل `create_users_table`) هیچ‌وقت اجرا نمی‌شوند، فقط mark as Ran می‌شوند.

---

## 📊 مثال خروجی

```
Comparing schemas: A=iras_dev vs B=iras_prod

=== Table differences ===
🟢 Only in A:
+-------+-------------------+
| 📦    | Table             |
+-------+-------------------+
| 📦    | new_feature       |
| 📦    | temp_logs         |
+-------+-------------------+

🟠 Only in B: -

=== Column/index differences (summary) ===
+-----+-----------+----------------+----------------+-------------+
| Tbl | Name      | Only in A ➕   | Only in B ➖   | Changed ✏️  |
+-----+-----------+----------------+----------------+-------------+
| 🧱  | finances  | commission_protocol | -         | -           |
| 🧱  | users     | telegram_id    | -              | -           |
+-----+-----------+----------------+----------------+-------------+

=== Plan (safe suggestions) ===
🔒 Protected tables: users, settings

+-----+-------------------+----------------------+------------------+
| Act | Action            | Target               | Note             |
+-----+-------------------+----------------------+------------------+
| ➕  | add column        | finances.commission_protocol |         |
| ⛔  | [SKIP] add column | users.telegram_id    | protected table  |
+-----+-------------------+----------------------+------------------+

ℹ️ Note: This command does not change the database unless --apply is used.
```

---

## ⚠️ نکات مهم

### ✅ **Do's (انجام بده)**
1. **همیشه ابتدا بدون `--apply`** اجرا کن
2. **از `--dry-run`** برای تست استفاده کن
3. **Backup بگیر** قبل از `--apply`
4. **جداول حیاتی رو ignore کن** (`--ignore=users,settings`)

### ❌ **Don'ts (انجام نده)**
1. **مستقیم روی Production** بدون تست نزن
2. **بدون backup** اعمال نکن
3. **جداول حیاتی رو تغییر نده** (از ignore استفاده کن)

---

## 🔧 تنظیمات پیشرفته

### تغییر Connection دیتابیس مقصد

در `config/database.php`:

```php
'connections' => [
    // ... 
    'mysql_b' => [
        'driver' => 'mysql',
        'host' => env('DB_B_HOST', '127.0.0.1'),
        'database' => env('DB_B_DATABASE'),
        'username' => env('DB_B_USERNAME'),
        'password' => env('DB_B_PASSWORD'),
    ],
],
```

سپس:
```bash
php artisan db:shift --a=dev --b=prod --b-connection=mysql_b --apply
```

---

## 📋 Checklist استقرار Production

- [ ] ✅ Backup کامل از دیتابیس گرفته‌ام
- [ ] ✅ دستور را بدون `--apply` تست کردم
- [ ] ✅ با `--dry-run` شبیه‌سازی کردم
- [ ] ✅ جداول حیاتی را ignore کردم
- [ ] ✅ Migration guard ها را چک کردم (`Schema::hasColumn()`)
- [ ] ✅ زمان مناسب (ساعت کم‌کاری) را انتخاب کردم
- [ ] ✅ تیم فنی آماده‌اند
- [ ] ✅ اکنون می‌توانم `--apply` را اجرا کنم! 🚀

---

## 🆘 عیب‌یابی

### خطا: "Table already exists"
**راه‌حل:** Migration guard ندارد. اضافه کنید:
```php
if (!Schema::hasTable('table_name')) {
    Schema::create('table_name', ...);
}
```

### خطا: "Column already exists"
**راه‌حل:** Migration guard ندارد. اضافه کنید:
```php
if (!Schema::hasColumn('table_name', 'column_name')) {
    $table->string('column_name');
}
```

### گزارش گیج‌کننده است
**راه‌حل:** از `--details` برای جزئیات بیشتر استفاده کنید.

---

## 🎯 مثال‌های کاربردی

### سناریو 1: Setup سرور جدید
```bash
# 1. مقایسه
php artisan db:shift --a=dev_db --b=new_prod_db

# 2. تست
php artisan db:shift --a=dev_db --b=new_prod_db --apply --dry-run

# 3. اعمال
php artisan db:shift --a=dev_db --b=new_prod_db --apply
```

### سناریو 2: بروزرسانی Production
```bash
php artisan db:shift \
  --a=dev_db \
  --b=prod_db \
  --ignore=users,settings,admins \
  --apply \
  --dry-run

# اگر OK بود:
php artisan db:shift \
  --a=dev_db \
  --b=prod_db \
  --ignore=users,settings,admins \
  --apply
```

---

## 📞 پشتیبانی

در صورت بروز مشکل:
1. لاگ‌ها را چک کنید: `storage/logs/laravel.log`
2. از `--details` برای اطلاعات بیشتر استفاده کنید
3. با تیم فنی تماس بگیرید

---

## 📜 تاریخچه نسخه‌ها

### v1.0.0 (2025-10-18)
- ✨ انتشار اولیه
- 🧠 Smart create-table detection
- 🧠 Smart add-column detection
- 🔒 Protected tables support
- 📊 Detailed reporting
- ✅ Dry-run mode

---

**ساخته شده با ❤️ توسط تیم IRAS**
