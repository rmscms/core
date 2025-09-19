<?php

namespace RMS\Core\Traits\Stats;

/**
 * Trait StatsCardControl
 * 
 * پیاده‌سازی پیش‌فرض برای کنترل وضعیت کارت آمار
 * 
 * @package RMS\Core\Traits\Stats
 * @version 1.0.0
 * @author RMS Core Team
 */
trait StatsCardControl
{
    /**
     * وضعیت پیش‌فرض کارت آمار
     */
    protected bool $statsCardExpanded = true;
    
    /**
     * تعیین وضعیت پیش‌فرض کارت آمار (باز یا بسته)
     * 
     * @return bool true = باز (پیش‌فرض), false = بسته
     */
    public function getStatsCardExpanded(): bool
    {
        return $this->statsCardExpanded;
    }
    
    /**
     * تنظیم وضعیت کارت آمار
     * 
     * @param bool $expanded باز (true) یا بسته (false)
     * @return void
     */
    public function setStatsCardExpanded(bool $expanded): void
    {
        $this->statsCardExpanded = $expanded;
    }
    
    /**
     * بستن کارت آمار (helper method)
     * 
     * @return void
     */
    public function collapseStatsCard(): void
    {
        $this->setStatsCardExpanded(false);
    }
    
    /**
     * باز کردن کارت آمار (helper method)
     * 
     * @return void
     */
    public function expandStatsCard(): void
    {
        $this->setStatsCardExpanded(true);
    }
}