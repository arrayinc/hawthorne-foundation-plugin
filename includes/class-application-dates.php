<?php

require_once __DIR__ . '/interface-application-dates.php';

final class ApplicationDates implements IApplicationDates
{
    private $_begin_date; 
    private $_end_date; 

    public function __construct(DateTime $begin_date, DateTime $end_date)
    {
        $this->_begin_date = $begin_date;
        $this->_end_date = $end_date;
    }

    public function get_begin_date()
    {
        return $this->_begin_date;
    }

    public function get_end_date()
    {
        return $this->_end_date;
    }

    public function format_begin_date(string $format)
    {
        return $this->_begin_date->format($format);
    }

    public function format_end_date(string $format)
    {
        return $this->_end_date->format($format);
    }
}