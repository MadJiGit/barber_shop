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

    public static function getRole(?self $value): ?string
    {
        return match ($value) {
            self::SUPER_ADMIN => 'SUPER_ADMIN',
            self::ADMIN => 'ADMIN',
            self::BARBER_MASTER => 'BARBER_MASTER',
            self::BARBER => 'BARBER',
            self::BARBER_JUNIOR => 'BARBER_JUNIOR',
            self::CLIENT => 'CLIENT',
            self::DEFAULT => 'DEFAULT',
        };
    }

    public static function getCases(): array
    {
        $cases = self::cases();

//        return array_map(static fn (\UnitEnum $case) => $case->name, $cases);
        return array_map(static fn (\BackedEnum $case) => $case->name, $cases);
    }

    public static function getValues(): array
    {
        $cases = self::cases();

        return array_map(static fn (\UnitEnum $case) => $case->value, $cases);
    }

    public static function toArray()
    {
        $values = [];

        foreach (self::cases() as $props) {
            array_push($values, $props->value);
        }

        return $values;
    }

    //    public static function getValues()
    //    {
    //        return array_column(Roles::cases(), 'value');
    //    }

    public static function getAllCases(): array
    {
        //        return self::cases();
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::BARBER_MASTER,
            self::BARBER,
            self::BARBER_JUNIOR,
            self::CLIENT,
            self::DEFAULT,
        ];
    }

    public static function getTypeName($name)
    {
        return strval(self::tryFrom($name->value));
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
