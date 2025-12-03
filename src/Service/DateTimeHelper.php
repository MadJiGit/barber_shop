<?php

namespace App\Service;

use DateTimeImmutable;
use DateTimeZone;
use Exception;

class DateTimeHelper
{
    private const TIMEZONE = 'Europe/Sofia';

    /**
     * Get current DateTime with application timezone
     * @throws Exception
     */
    public static function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone(self::TIMEZONE));
    }

    /**
     * Create DateTime from string with application timezone
     * @throws Exception
     */
    public static function createFromString(string $datetime): DateTimeImmutable
    {
        return new DateTimeImmutable($datetime, new DateTimeZone(self::TIMEZONE));
    }

    /**
     * Get application timezone
     */
    public static function getTimezone(): DateTimeZone
    {
        return new DateTimeZone(self::TIMEZONE);
    }
}
