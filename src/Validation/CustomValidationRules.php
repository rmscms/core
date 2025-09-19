<?php

namespace RMS\Core\Validation;

use Illuminate\Support\Facades\Validator;

/**
 * Custom validation rules for RMS CMS.
 * 
 * This class provides additional validation rules specifically designed
 * for Persian/Iranian applications and common CMS requirements.
 */
class CustomValidationRules
{
    /**
     * Register all custom validation rules.
     *
     * @return void
     */
    public static function register(): void
    {
        static::registerAlphaSpaces();
        static::registerMobileFormat();
        static::registerIranianNationalCode();
        static::registerPersianText();
        static::registerIranianPostalCode();
        static::registerIranianPhoneNumber();
        static::registerSafeUrl();
    }

    /**
     * Register alpha spaces validation rule.
     * Allows letters and spaces only (Unicode support for Persian).
     *
     * @return void
     */
    protected static function registerAlphaSpaces(): void
    {
        Validator::extend('alpha_spaces', function ($attribute, $value, $parameters) {
            return preg_match('/^[\pL\s]+$/u', $value);
        });

        Validator::replacer('alpha_spaces', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, ':attribute فقط می‌تواند شامل حروف و فاصله باشد.');
        });
    }

    /**
     * Register Iranian mobile phone validation.
     * Validates format: 09xxxxxxxxx (11 digits starting with 09).
     *
     * @return void
     */
    protected static function registerMobileFormat(): void
    {
        Validator::extend('iranian_mobile', function ($attribute, $value, $parameters) {
            // Remove any spaces or dashes
            $cleanValue = preg_replace('/[\s\-]+/', '', $value);
            
            // Check if it starts with 09 and has exactly 11 digits
            return preg_match('/^09[0-9]{9}$/', $cleanValue);
        });

        Validator::replacer('iranian_mobile', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, ':attribute باید یک شماره موبایل معتبر ایرانی باشد (09xxxxxxxxx).');
        });
    }

    /**
     * Register Iranian national code validation.
     * Validates Iranian national ID format and checksum.
     *
     * @return void
     */
    protected static function registerIranianNationalCode(): void
    {
        Validator::extend('iranian_national_code', function ($attribute, $value, $parameters) {
            if (!preg_match('/^[0-9]{10}$/', $value)) {
                return false;
            }

            // Check for invalid patterns
            $invalidCodes = [
                '0000000000', '1111111111', '2222222222', '3333333333',
                '4444444444', '5555555555', '6666666666', '7777777777',
                '8888888888', '9999999999'
            ];

            if (in_array($value, $invalidCodes)) {
                return false;
            }

            // Calculate checksum
            $sum = 0;
            for ($i = 0; $i < 9; $i++) {
                $sum += intval($value[$i]) * (10 - $i);
            }

            $remainder = $sum % 11;
            $checkDigit = $remainder < 2 ? $remainder : 11 - $remainder;

            return intval($value[9]) === $checkDigit;
        });

        Validator::replacer('iranian_national_code', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, ':attribute باید یک کد ملی معتبر ایرانی باشد.');
        });
    }

    /**
     * Register Persian text validation.
     * Allows Persian letters, numbers, spaces, and common punctuation.
     *
     * @return void
     */
    protected static function registerPersianText(): void
    {
        Validator::extend('persian_text', function ($attribute, $value, $parameters) {
            return preg_match('/^[\x{0600}-\x{06FF}\x{200C}\x{200D}\s\d\.\،\؟\!\:\;\"\'\(\)\-]+$/u', $value);
        });

        Validator::replacer('persian_text', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, ':attribute فقط می‌تواند شامل متن فارسی، اعداد و علائم مجاز باشد.');
        });
    }

    /**
     * Register Iranian postal code validation.
     * Validates Iranian postal code format (10 digits).
     *
     * @return void
     */
    protected static function registerIranianPostalCode(): void
    {
        Validator::extend('iranian_postal_code', function ($attribute, $value, $parameters) {
            // Remove any spaces or dashes
            $cleanValue = preg_replace('/[\s\-]+/', '', $value);
            
            return preg_match('/^[0-9]{10}$/', $cleanValue);
        });

        Validator::replacer('iranian_postal_code', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, ':attribute باید یک کد پستی معتبر ایرانی باشد (10 رقم).');
        });
    }

    /**
     * Register Iranian landline phone validation.
     * Validates Iranian landline phone format.
     *
     * @return void
     */
    protected static function registerIranianPhoneNumber(): void
    {
        Validator::extend('iranian_phone', function ($attribute, $value, $parameters) {
            // Remove any spaces, dashes, or parentheses
            $cleanValue = preg_replace('/[\s\-\(\)]+/', '', $value);
            
            // Check various Iranian landline formats
            return preg_match('/^0[1-9][0-9]{8,9}$/', $cleanValue);
        });

        Validator::replacer('iranian_phone', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, ':attribute باید یک شماره تلفن ثابت معتبر ایرانی باشد.');
        });
    }

    /**
     * Register safe URL validation.
     * More strict URL validation for security purposes.
     *
     * @return void
     */
    protected static function registerSafeUrl(): void
    {
        Validator::extend('safe_url', function ($attribute, $value, $parameters) {
            // Basic URL validation
            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                return false;
            }

            // Check for allowed schemes
            $allowedSchemes = ['http', 'https'];
            $scheme = parse_url($value, PHP_URL_SCHEME);
            
            if (!in_array($scheme, $allowedSchemes)) {
                return false;
            }

            // Block potentially dangerous domains/IPs
            $host = parse_url($value, PHP_URL_HOST);
            if (!$host) {
                return false;
            }

            // Block localhost and private IPs for security
            if (in_array($host, ['localhost', '127.0.0.1', '::1'])) {
                return false;
            }

            // Block private IP ranges
            if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false && filter_var($host, FILTER_VALIDATE_IP)) {
                return false;
            }

            return true;
        });

        Validator::replacer('safe_url', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, ':attribute باید یک URL معتبر و امن باشد.');
        });
    }

    /**
     * Get all available custom validation rules.
     *
     * @return array
     */
    public static function getAvailableRules(): array
    {
        return [
            'alpha_spaces' => 'حروف و فاصله',
            'iranian_mobile' => 'شماره موبایل ایرانی',
            'iranian_national_code' => 'کد ملی ایرانی', 
            'persian_text' => 'متن فارسی',
            'iranian_postal_code' => 'کد پستی ایرانی',
            'iranian_phone' => 'شماره تلفن ثابت ایرانی',
            'safe_url' => 'URL امن'
        ];
    }
}
