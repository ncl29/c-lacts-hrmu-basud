<?php

namespace App\Services;

use App\Models\LeaveApplication;
use Carbon\Carbon;

/**
 * WorkingDayService
 *
 * Provides accurate municipal working days and holiday computation for C-LACTS:
 * - Excludes Saturdays and Sundays (regular rest days).
 * - Excludes Philippine national regular holidays and special non-working days.
 * - Excludes local / municipal non-working holidays.
 * - Calculates statutory periods such as the 7-working-day leave application cancellation window
 *   and Civil Service Section 49 SLA tracking.
 */
class WorkingDayService
{
    /**
     * Cache of resolved holidays by year.
     *
     * @var array<int, array<string, string>>
     */
    protected static array $holidayCache = [];

    /**
     * Retrieve all recognized regular, special non-working, and municipal holidays for a given year.
     *
     * @param int $year
     * @return array<string, string> Key: 'YYYY-MM-DD', Value: Holiday description
     */
    public static function getHolidays(int $year): array
    {
        if (isset(self::$holidayCache[$year])) {
            return self::$holidayCache[$year];
        }

        // Standard Philippine Nationwide Fixed Regular & Special Non-Working Holidays
        $holidays = [
            sprintf('%04d-01-01', $year) => "New Year's Day (Regular Holiday)",
            sprintf('%04d-01-02', $year) => "Special Non-Working Day",
            sprintf('%04d-02-25', $year) => "EDSA People Power Anniversary (Special Non-Working)",
            sprintf('%04d-04-09', $year) => "Araw ng Kagitingan / Day of Valor (Regular Holiday)",
            sprintf('%04d-05-01', $year) => "Labor Day (Regular Holiday)",
            sprintf('%04d-06-12', $year) => "Independence Day (Regular Holiday)",
            sprintf('%04d-08-21', $year) => "Ninoy Aquino Day (Special Non-Working)",
            sprintf('%04d-11-01', $year) => "All Saints' Day (Special Non-Working)",
            sprintf('%04d-11-02', $year) => "All Souls' Day (Special Non-Working)",
            sprintf('%04d-11-30', $year) => "Bonifacio Day (Regular Holiday)",
            sprintf('%04d-12-08', $year) => "Feast of the Immaculate Conception (Special Non-Working)",
            sprintf('%04d-12-24', $year) => "Christmas Eve (Special Non-Working)",
            sprintf('%04d-12-25', $year) => "Christmas Day (Regular Holiday)",
            sprintf('%04d-12-30', $year) => "Rizal Day (Regular Holiday)",
            sprintf('%04d-12-31', $year) => "Last Day of the Year (Special Non-Working)",
        ];

        // Holy Week Movable Holidays (Maundy Thursday, Good Friday, Black Saturday)
        if (function_exists('easter_date')) {
            $ts = easter_date($year);
            $easter = Carbon::create((int) date('Y', $ts), (int) date('m', $ts), (int) date('d', $ts), 0, 0, 0);
            $holidays[$easter->copy()->subDays(3)->format('Y-m-d')] = "Maundy Thursday (Regular Holiday)";
            $holidays[$easter->copy()->subDays(2)->format('Y-m-d')] = "Good Friday (Regular Holiday)";
            $holidays[$easter->copy()->subDays(1)->format('Y-m-d')] = "Black Saturday (Special Non-Working)";
        }

        // National Heroes Day: Last Monday of August
        $aug31 = Carbon::create($year, 8, 31, 0, 0, 0);
        $heroesDay = $aug31->isMonday() ? $aug31 : $aug31->copy()->previous(Carbon::MONDAY);
        $holidays[$heroesDay->format('Y-m-d')] = "National Heroes Day (Regular Holiday)";

        // Year-specific movable Islamic holidays & Chinese New Year (Proclamations)
        $movable = [
            2025 => [
                '2025-01-29' => "Chinese New Year",
                '2025-03-31' => "Eid'l Fitr",
                '2025-06-06' => "Eid'l Adha",
            ],
            2026 => [
                '2026-02-17' => "Chinese New Year",
                '2026-03-20' => "Eid'l Fitr",
                '2026-05-27' => "Eid'l Adha",
            ],
            2027 => [
                '2027-02-06' => "Chinese New Year",
                '2027-03-10' => "Eid'l Fitr",
                '2027-05-17' => "Eid'l Adha",
            ],
        ];

        if (isset($movable[$year])) {
            $holidays = array_merge($holidays, $movable[$year]);
        }

        // Support configuration overrides or local municipal holidays from config('holidays')
        $configHolidays = config('holidays.' . $year, []);
        if (!empty($configHolidays) && is_array($configHolidays)) {
            $holidays = array_merge($holidays, $configHolidays);
        }

        ksort($holidays);
        self::$holidayCache[$year] = $holidays;

        return $holidays;
    }

    /**
     * Checks if a given date is an official recognized holiday.
     */
    public static function isHoliday(Carbon|string $date): bool
    {
        $d = $date instanceof Carbon ? $date : Carbon::parse($date);
        $holidays = self::getHolidays($d->year);

        return isset($holidays[$d->format('Y-m-d')]);
    }

    /**
     * Returns the name/description of a holiday on a given date, or null if not a holiday.
     */
    public static function getHolidayName(Carbon|string $date): ?string
    {
        $d = $date instanceof Carbon ? $date : Carbon::parse($date);
        $holidays = self::getHolidays($d->year);

        return $holidays[$d->format('Y-m-d')] ?? null;
    }

    /**
     * Determines whether a given date is a working day (Monday–Friday, excluding weekends and holidays).
     */
    public static function isWorkingDay(Carbon|string $date): bool
    {
        $d = $date instanceof Carbon ? $date : Carbon::parse($date);

        if ($d->isWeekend()) {
            return false;
        }

        return !self::isHoliday($d);
    }

    /**
     * Adds a specified number of working days to a starting date, skipping weekends and holidays.
     * Preserves the starting timestamp's hour, minute, and second.
     *
     * @param Carbon|string $startDate Starting timestamp or date string
     * @param int $workingDays Number of working days to advance (e.g. 7)
     * @return Carbon
     */
    public static function addWorkingDays(Carbon|string $startDate, int $workingDays): Carbon
    {
        $current = $startDate instanceof Carbon ? $startDate->copy() : Carbon::parse($startDate);

        if ($workingDays <= 0) {
            return $current;
        }

        $daysAdded = 0;
        while ($daysAdded < $workingDays) {
            $current->addDay();
            if (self::isWorkingDay($current)) {
                $daysAdded++;
            }
        }

        return $current;
    }

    /**
     * Calculates the statutory 7-working-day cancellation deadline for a filed leave application.
     * Excludes weekends and official holidays.
     *
     * @param Carbon|string $filedAt Timestamp of application filing / creation
     * @param int $workingDays Defaults to 7 working days
     * @return Carbon
     */
    public static function getCancellationDeadline(Carbon|string $filedAt, int $workingDays = 7): Carbon
    {
        return self::addWorkingDays($filedAt, $workingDays);
    }

    /**
     * Calculates the remaining working days between now and the cancellation deadline.
     * Returns 0 if deadline has passed, or at least 1 working day if still within the window.
     */
    public static function getRemainingWorkingDays(Carbon $deadline, ?Carbon $from = null): int
    {
        $now = $from ? $from->copy() : Carbon::now();
        if ($now->gte($deadline)) {
            return 0;
        }

        $days = 0;
        $temp = $now->copy()->startOfDay();
        $end = $deadline->copy()->startOfDay();

        while ($temp->lt($end)) {
            $temp->addDay();
            if (self::isWorkingDay($temp)) {
                $days++;
            }
        }

        return max(1, $days);
    }

    /**
     * Checks if an application is still within its statutory 7-working-day cancellation window.
     */
    public static function isCancellationAllowed(LeaveApplication|Carbon|string $applicationOrDate, int $workingDays = 7): bool
    {
        if ($applicationOrDate instanceof LeaveApplication) {
            if (in_array($applicationOrDate->status, ['Cancelled', 'Disapproved'], true)) {
                return false;
            }
            $filedAt = $applicationOrDate->created_at ?? $applicationOrDate->date_of_filing;
        } else {
            $filedAt = $applicationOrDate;
        }

        $deadline = self::getCancellationDeadline($filedAt, $workingDays);

        return Carbon::now()->lte($deadline);
    }
}

