// نگاشت اعداد به متن فارسی
const units = ['صفر', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه'];
const teens = ['ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده'];
const tens = ['', '', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'];
const hundreds = ['', 'صد', 'دویست', 'سیصد', 'چهارصد', 'پنصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'];
const scales = ['', 'هزار', 'میلیون', 'میلیارد', 'تریلیون'];

// تبدیل عدد به متن فارسی
function numberToPersianText(number) {
    if (number === 0) return units[0];

    let result = [];
    let groupIndex = 0;

    // شکستن عدد به گروه‌های سه‌تایی
    while (number > 0) {
        let group = number % 1000;
        if (group > 0) {
            let groupText = '';

            // صدگان
            if (group >= 100) {
                groupText += hundreds[Math.floor(group / 100)];
                group %= 100;
                if (group > 0) groupText += ' و ';
            }

            // اعداد 10 تا 99
            if (group >= 20) {
                groupText += tens[Math.floor(group / 10)];
                group %= 10;
                if (group > 0) groupText += ' و ' + units[group];
            } else if (group >= 10) {
                groupText += teens[group - 10];
            } else if (group > 0) {
                groupText += units[group];
            }

            // اضافه کردن مقیاس (هزار، میلیون، ...)
            if (groupIndex > 0 && groupText) {
                groupText += ' ' + scales[groupIndex];
            }

            result.unshift(groupText);
        }
        number = Math.floor(number / 1000);
        groupIndex++;
    }

    return result.join(' و ') + ' تومان';
}

// تابع اعتبارسنجی و تبدیل ورودی با jQuery
function convertNumberToText() {
    let value = $('#amount').val().replace(/[^0-9]/g, ''); // فقط اعداد

    if (value === '') {
        $('#amountHelp').text('');
        return;
    }

    const number = parseInt(value);
    if (isNaN(number)) {
        $('#amountHelp').text('لطفاً فقط عدد وارد کنید!');
        return;
    }

    $('#amountHelp').text(numberToPersianText(number));
}

// تابع فرمت کردن عدد با جداکننده
function formatNumberWithSeparator(value) {
    // حذف همه کاراکترهای غیر عددی
    let cleanValue = value.replace(/[^0-9]/g, '');

    if (cleanValue === '') return '';

    // اضافه کردن جداکننده هر سه رقم
    return cleanValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// اضافه کردن event listener با jQuery
$(document).ready(function() {
    // بررسی وجود فیلد amount
    if ($('#amount').length > 0) {
        // تبدیل عدد به متن فارسی
        convertNumberToText();

        $('#amount').on('input', function() {
            // فرمت کردن عدد با جداکننده
            let formattedValue = formatNumberWithSeparator($(this).val());
            $(this).val(formattedValue);

            // تبدیل به متن فارسی
            convertNumberToText();
        });
    }
});