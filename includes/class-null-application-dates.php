<?php

require_once __DIR__ . '/interface-application-dates.php';

final class NullApplicationDates implements IApplicationDates
{
    public function format_begin_date(string $format)
    {
        return '';
    }

    public function format_end_date(string $format)
    {
        return '';
    } 
}