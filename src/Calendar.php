<?php
namespace Calendar;

use Carbon\CarbonPeriod;
use DateTimeInterface;
use Carbon\Carbon;

class Calendar implements CalendarInterface
{
    public $datetime;

    public function __construct(DateTimeInterface $datetime)
    {
        $this->datetime = Carbon::instance($datetime);
    }

    public function getDay()
    {
        return $this->datetime->day;
    }

    public function getWeekDay()
    {
        return $this->datetime->dayOfWeekIso;
    }

    public function getFirstWeekDay()
    {
        return $this->datetime->clone()->startOfMonth()->dayOfWeekIso;
    }

    public function getFirstWeek()
    {
        return $this->datetime->clone()->startOfMonth()->weekOfYear;
    }

    public function getNumberOfDaysInThisMonth()
    {
        return $this->datetime->daysInMonth;
    }

    public function getNumberOfDaysInPreviousMonth()
    {
        return $this->datetime->clone()->startOfMonth()->subMonth()->daysInMonth;
    }

    public function getCalendar()
    {
        return collect($this->getCalendarPeriod())
            ->mapToGroups(function (Carbon $day) {
                return [$day->weekOfYear => $day];
            })
            ->map(function ($week) {
                return collect($week)->mapWithKeys(function (Carbon $day) {
                    return [$day->day => $this->shouldHighlightDay($day)];
                });
            })
            ->toArray();
    }

    private function getCalendarPeriod() : CarbonPeriod
    {
        return CarbonPeriod::create(
            $this->datetime->clone()->startOfMonth()->subDays($this->getFirstWeekDay() - 1),
            '1 day',
            $this->datetime->clone()->endOfMonth()->addDays(7 - $this->datetime->clone()->endOfMonth()->dayOfWeekIso)
        );
    }

    private function shouldHighlightDay(Carbon $day) : bool
    {
        return $day->weekOfYear !== $this->datetime->weekOfYear
            && $day->weekOfYear === $this->datetime->clone()->subWeek()->weekOfYear;
    }
}
