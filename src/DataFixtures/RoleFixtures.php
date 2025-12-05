<?php

namespace App\DataFixtures;

use App\Entity\Roles;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public const ROLE_REFERENCE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    public const ROLE_REFERENCE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_REFERENCE_BARBER_MASTER = 'ROLE_BARBER_MASTER';
    public const ROLE_REFERENCE_BARBER = 'ROLE_BARBER';
    public const ROLE_REFERENCE_BARBER_JUNIOR = 'ROLE_BARBER_JUNIOR';
    public const ROLE_REFERENCE_CLIENT = 'ROLE_CLIENT';
    public const ROLE_REFERENCE_DEFAULT = 'ROLE_DEFAULT';

    public function load(ObjectManager $manager): void
    {
    }
}
