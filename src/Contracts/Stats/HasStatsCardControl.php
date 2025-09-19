<?php

namespace RMS\Core\Contracts\Stats;

/**
 * Interface HasStatsCardControl
 * 
 * رابط برای کنترل وضعیت پیش‌فرض کارت آمار (باز یا بسته)
 * 
 * @package RMS\Core\Contracts\Stats
 * @version 1.0.0
 * @author RMS Core Team
 */
interface HasStatsCardControl
{
    /**
     * تعیین وضعیت پیش‌فرض کارت آمار (باز یا بسته)
     * 
     * @return bool true = باز (پیش‌فرض), false = بسته
     */
    public function getStatsCardExpanded(): bool;
    
    /**
     * تنظیم وضعیت کارت آمار (اختیاری - برای تغییر داینامیک)
     * 
     * @param bool $expanded باز (true) یا بسته (false)
     * @return void
     */
    public function setStatsCardExpanded(bool $expanded): void;
}