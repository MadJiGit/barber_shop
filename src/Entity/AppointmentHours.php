<?php

namespace App\Entity;

class AppointmentHours
{
    /**
     * @return string[]
     */
    private static array $appointments = [
        '1' => '10:00',
        '2' => '11:00',
        '3' => '12:00',
        '4' => '13:00',
        '5' => '14:00',
        '6' => '15:00',
        '7' => '16:00',
        '8' => '17:00',
        //        '9' => '18:00',
    ];

    public static function getAppointmentHours(): array
    {
        return self::$appointments;
    }

    public static function getAppointmentIdByHour(string $hour): string|bool
    {
        return array_keys(self::getAppointmentHours(), $hour)[0] ?? false;
    }

    public static function getAppointmentHourById(string $id): string
    {
        return self::getAppointmentHours()[$id];
    }

    public static function ifKeyExist($key): bool
    {
        return key_exists($key, self::getAppointmentHours());
    }

    public static function ifHourExist($hour): bool
    {
        return self::getAppointmentIdByHour($hour) ?? false;
    }
}
