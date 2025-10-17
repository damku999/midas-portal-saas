<?php

if (! function_exists('formatDateForUi')) {
    /**
     * Format database date (Y-m-d) to UI display format (d/m/Y)
     *
     * @param  string|null  $date  Database date
     * @return string|null
     */
    function formatDateForUi($date)
    {
        return App\Helpers\DateHelper::toUiFormat($date);
    }
}

if (! function_exists('formatDateForDatabase')) {
    /**
     * Format UI date (d/m/Y) to database format (Y-m-d)
     *
     * @param  string|null  $date  UI date
     * @return string|null
     */
    function formatDateForDatabase($date)
    {
        return App\Helpers\DateHelper::toDatabaseFormat($date);
    }
}

if (! function_exists('formatDateTimeForUi')) {
    /**
     * Format database datetime to UI display format (d/m/Y H:i)
     *
     * @param  string|null  $datetime  Database datetime
     * @return string|null
     */
    function formatDateTimeForUi($datetime)
    {
        return App\Helpers\DateHelper::toUiDateTimeFormat($datetime);
    }
}

if (! function_exists('currentUiDate')) {
    /**
     * Get current date in UI format (d/m/Y)
     *
     * @return string
     */
    function currentUiDate()
    {
        return App\Helpers\DateHelper::currentUiDate();
    }
}

if (! function_exists('currentDatabaseDate')) {
    /**
     * Get current date in database format (Y-m-d)
     *
     * @return string
     */
    function currentDatabaseDate()
    {
        return App\Helpers\DateHelper::currentDatabaseDate();
    }
}
