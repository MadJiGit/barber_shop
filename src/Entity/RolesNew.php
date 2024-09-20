<?php

namespace App\Entity;

class RolesNew
{
    private static $array;

    public function __construct()
    {
        $this->array = [
            'super_admin' => 'SUPER_ADMIN',
            'admin' => 'ADMIN',
            'barber_master' => 'BARBER_MASTER',
            'barber' => 'BARBER',
            'barber_junior' => 'BARBER_JUNIOR',
            'client' => 'CLIENT',
        ];
    }

    public static function getRoles(): array
    {
        return [
            'super_admin' => 'SUPER_ADMIN',
            'admin' => 'ADMIN',
            'barber_master' => 'BARBER_MASTER',
            'barber' => 'BARBER',
            'barber_junior' => 'BARBER_JUNIOR',
            'client' => 'CLIENT',
        ];
    }
}
