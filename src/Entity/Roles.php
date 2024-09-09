<?php

namespace App\Entity;

enum Roles: string
{
    case SUPER_ADMIN = 'SUPER_ADMIN';
    case ADMIN = 'ADMIN';
    case BARBER_MASTER = 'BARBER_MASTER';
    case BARBER = 'BARBER';
    case BARBER_JUNIOR = 'BARBER_JUNIOR';
    case CLIENT = 'CLIENT';
    case DEFAULT = 'DEFAULT';

    public static function toArray()
    {
        $values = [];

        foreach (self::cases() as $props) {
            array_push($values, $props->value);
        }

        return $values;
    }
}
// enum Roles: string
// {
//    case SUPER_ADMIN = 'SUPER_ADMIN';
//    case ADMIN = 'ADMIN';
//    case BARBER_MASTER = 'BARBER_MASTER';
//    case BARBER = 'BARBER';
//    case BARBER_JUNIOR = 'BARBER_JUNIOR';
//    case CLIENT = 'CLIENT';
//    case DEFAULT = 'DEFAULT';
// }
// trait EnumToArray
// {
//
//    public static function names(): array
//    {
//        return array_column(self::cases(), 'name');
//    }
//
//    public static function values(): array
//    {
//        return array_column(self::cases(), 'value');
//    }
//
//    public static function array(): array
//    {
//        return array_combine(self::values(), self::names());
//    }
//
// }
