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

    public function load(ObjectManager $manager)
    {
//        $role_1 = new Roles();
//        $role_1->setRole('SUPER_ADMIN');
//        $manager->persist($role_1);
//
//        $role_2 = new Roles();
//        $role_2->setRole('ADMIN');
//        $manager->persist($role_2);
//
//        $role_3 = new Roles();
//        $role_3->setRole('BARBER_MASTER');
//        $manager->persist($role_3);
//
//        $role_4 = new Roles();
//        $role_4->setRole('BARBER');
//        $manager->persist($role_4);
//
//        $role_5 = new Roles();
//        $role_5->setRole('BARBER_JUNIOR');
//        $manager->persist($role_5);
//
//        $role_6 = new Roles();
//        $role_6->setRole('CLIENT');
//        $manager->persist($role_6);
//
//        $role_7 = new Roles();
//        $role_7->setRole('DEFAULT');
//        $manager->persist($role_7);
//
//        $manager->flush();
//
//        $this->addReference(self::ROLE_REFERENCE_SUPER_ADMIN, $role_1);
//        $this->addReference(self::ROLE_REFERENCE_ADMIN, $role_1);
//        $this->addReference(self::ROLE_REFERENCE_BARBER_MASTER, $role_3);
//        $this->addReference(self::ROLE_REFERENCE_BARBER, $role_4);
//        $this->addReference(self::ROLE_REFERENCE_BARBER_JUNIOR, $role_5);
//        $this->addReference(self::ROLE_REFERENCE_CLIENT, $role_6);
//        $this->addReference(self::ROLE_REFERENCE_DEFAULT, $role_7);
    }
}
