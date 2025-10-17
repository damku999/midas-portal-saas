<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Convert database date (Y-m-d) to UI display format (d/m/Y)
     *
     * @param  string|null  $date  Database date in Y-m-d format
     * @return string|null Date in d/m/Y format or null
     */
    public static function toUiFormat($date)
    {
        if (empty($date)) {
            return;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $date)->format('d/m/Y');
        } catch (\Exception $e) {
            // If date is already in d/m/Y format or other format, try to parse it
            try {
                return Carbon::parse($date)->format('d/m/Y');
            } catch (\Exception $e) {
                return $date; // Return original if can't parse
            }
        }
    }

    /**
     * Convert UI date (d/m/Y) to database format (Y-m-d)
     *
     * @param  string|null  $date  UI date in d/m/Y format
     * @return string|null Date in Y-m-d format or null
     */
    public static function toDatabaseFormat($date)
    {
        if (empty($date)) {
            return;
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            // If date is already in Y-m-d format or other format, try to parse it
            try {
                return Carbon::parse($date)->format('Y-m-d');
            } catch (\Exception $e) {
                return $date; // Return original if can't parse
            }
        }
    }

    /**
     * Convert database datetime to UI display format (d/m/Y H:i)
     *
     * @param  string|null  $datetime  Database datetime
     * @return string|null Datetime in d/m/Y H:i format or null
     */
    public static function toUiDateTimeFormat($datetime)
    {
        if (empty($datetime)) {
            return;
        }

        try {
            return Carbon::parse($datetime)->format('d/m/Y H:i');
        } catch (\Exception $e) {
            return $datetime; // Return original if can't parse
        }
    }

    /**
     * Get current date in UI format
     *
     * @return string Current date in d/m/Y format
     */
    public static function currentUiDate()
    {
        return Carbon::now()->format('d/m/Y');
    }

    /**
     * Get current date in database format
     *
     * @return string Current date in Y-m-d format
     */
    public static function currentDatabaseDate()
    {
        return Carbon::now()->format('Y-m-d');
    }

    /**
     * Validate if date is in correct UI format (d/m/Y)
     *
     * @param  string  $date  Date to validate
     * @return bool True if valid d/m/Y format
     */
    public static function isValidUiFormat($date)
    {
        try {
            $parsedDate = Carbon::createFromFormat('d/m/Y', $date);

            return $parsedDate && $parsedDate->format('d/m/Y') === $date;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate if date is in correct database format (Y-m-d)
     *
     * @param  string  $date  Date to validate
     * @return bool True if valid Y-m-d format
     */
    public static function isValidDatabaseFormat($date)
    {
        try {
            $parsedDate = Carbon::createFromFormat('Y-m-d', $date);

            return $parsedDate && $parsedDate->format('Y-m-d') === $date;
        } catch (\Exception $e) {
            return false;
        }
    }
}
