<?php

declare(strict_types=1);

namespace RMS\Core\Traits;

use Illuminate\Http\Request;
use RMS\Core\Data\Field;

/**
 * Trait for converting Persian dates to Gregorian dates before validation.
 * Uses rmscms/helper package functions.
 *
 * @package RMS\Core\Traits
 */
trait PersianDateConverter
{
    /**
     * Prepare request data for validation by converting Persian dates to Gregorian.
     * This method should be called in the controller's prepareForValidation method.
     *
     * @param Request $request
     * @return void
     */
    protected function convertPersianDatesToGregorian(Request &$request): void
    {
        $dateFields = $this->getDateFieldsForConversion();
        
        \Log::info('Persian date conversion started', [
            'controller' => get_class($this),
            'date_fields' => $dateFields,
            'request_data' => $request->only($dateFields)
        ]);
        
        if (empty($dateFields)) {
            \Log::info('No date fields found for conversion');
            return;
        }

        $requestData = $request->all();
        $converted = [];

        foreach ($dateFields as $fieldKey) {
            if (!isset($requestData[$fieldKey]) || empty($requestData[$fieldKey])) {
                continue;
            }

            $originalValue = trim($requestData[$fieldKey]);
            
            \Log::info('Converting Persian date', [
                'field' => $fieldKey,
                'original' => $originalValue
            ]);
            
            // Convert Persian numbers to English first
            $normalizedValue = \RMS\Helper\changeNumberToEn($originalValue);
            
            \Log::info('After number conversion', [
                'field' => $fieldKey,
                'normalized' => $normalizedValue
            ]);
            
            // Convert Persian date to Gregorian
            $convertedValue = \RMS\Helper\gregorian_date($normalizedValue);
            
            \Log::info('After date conversion', [
                'field' => $fieldKey,
                'gregorian' => $convertedValue
            ]);
            
            if ($convertedValue && $convertedValue !== $originalValue) {
                $requestData[$fieldKey] = $convertedValue;
                $converted[$fieldKey] = [
                    'original' => $originalValue,
                    'normalized' => $normalizedValue,
                    'converted' => $convertedValue
                ];
            }
        }

        // Update request data if any conversions were made
        if (!empty($converted)) {
            $request->merge($requestData);
            
            \Log::info('Persian dates converted for validation', [
                'controller' => get_class($this),
                'conversions' => $converted
            ]);
        } else {
            \Log::info('No Persian dates were converted');
        }
    }

    /**
     * Get list of DATE and DATE_TIME field keys that need conversion.
     *
     * @return array
     */
    protected function getDateFieldsForConversion(): array
    {
        if (!method_exists($this, 'getFieldsForm')) {
            return [];
        }

        $dateFields = [];

        foreach ($this->getFieldsForm() as $field) {
            if (property_exists($field, 'type') && 
                in_array($field->type, [Field::DATE, Field::DATE_TIME])) {
                $dateFields[] = $field->key;
            }
        }

        return $dateFields;
    }
}
