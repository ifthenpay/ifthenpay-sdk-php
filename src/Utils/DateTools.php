<?php

namespace Ifthenpay\PaymentGateway\Utils;

use DateTimeImmutable;
use DateTime;
use DateTimeZone;
use DateTimeInterface;

class DateTools
{
    public const TIMEZONE      = 'Europe/Lisbon';
    public const DATE_FORMAT   = 'Y-m-d H:i';
    public const DATE_FORMAT_S = 'Y-m-d H:i:s';



    /**
     * Converts a date string from one format to another.
     *
     * @param string $inputFormat  The format of the input date string.
     * @param string $outputFormat The desired output date format.
     * @param string $dateStr      The date string to convert.
     * @return string              The converted date string in the output format, or an empty string if conversion fails.
     */
    public static function convertDate(string $inputFormat, string $outputFormat, string $dateStr): string
    {
        $date = DateTime::createFromFormat($inputFormat, $dateStr);

        if (!$date) {
            return '';
        }

        return $date->format($outputFormat);
    }



    /**
     * Checks if the given date is in the past compared to the current date and time.
     *
     * @param DateTimeInterface $date     The date to check.
     * @param string            $timezone The timezone to consider for the comparison. Default is 'Europe/Lisbon'.
     * @return bool                     Returns true if the date is in the past, false otherwise.
     */
    public static function isPastDate(DateTimeInterface $date, string $timezone = self::TIMEZONE): bool
    {
        $currentDateTime = new DateTimeImmutable('now', new DateTimeZone($timezone));
        return $date < $currentDateTime;
    }



    /**
     * Calculates a future date by adding the specified number of days, hours, and minutes to the current date and time.
     * @param int  $days     Number of days to add.
     * @param int  $hours    Number of hours to add.
     * @param int  $minutes  Number of minutes to add.
     * @param bool $setHourMin If true, sets the time to the specified hours and minutes instead of adding.
     * @return DateTimeImmutable The calculated future date.
     */
    public static function getFutureDate(int $days = 0, int $hours = 0, int $minutes = 0, bool $setHourMin = false): ?DateTimeImmutable
    {
        $date = new DateTime('now', new DateTimeZone(self::TIMEZONE));

        // Increment days
        if ($days !== 0) {
            $date = $date->modify("+{$days} days");
        }

        if ($setHourMin) {
            // Set hour and minute explicitly
            $date = $date->setTime($hours, $minutes);
        } else {
            // Increment hours and minutes
            if ($hours !== 0) {
                $date = $date->modify("+{$hours} hours");
            }
            if ($minutes !== 0) {
                $date = $date->modify("+{$minutes} minutes");
            }
        }

        return DateTimeImmutable::createFromMutable($date);
    }



    /**
     * Returns the current timestamp as a DateTimeImmutable object in the specified timezone.
     *
     * @return DateTimeImmutable|null The current timestamp.
     */
    public static function getTimeStamp(): ?DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone(self::TIMEZONE));
    }
}
