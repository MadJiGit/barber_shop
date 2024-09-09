<?php

namespace App\DataFixtures;

use App\Entity\Roles;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UsersFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
//        $barber = new User();
//        $barber->setFirstName('Nasko');
//        $barber->setLastName('Nojicata');
//        $barber->setNickname('Nakata');
//        $barber->setEmail('nasko@barber.bg');
//        $barber->setPassword('1234567890');
//        $barber->setPhone('0812341234');
//        $barber->setRoles([Roles::SUPER_ADMIN, Roles::BARBER_MASTER, Roles::CLIENT]);
//
//        $manager->persist($barber);
//
//        $barber_1 = new User();
//        $barber_1->setFirstName('Joro');
//        $barber_1->setLastName('Brysnacha');
//        $barber_1->setNickname('Jokera');
//        $barber_1->setEmail('jokera@barber.bg');
//        $barber_1->setPassword('1234567890');
//        $barber_1->setPhone('0812341234');
//        $barber->setRoles([Roles::BARBER_JUNIOR, Roles::CLIENT]);
//
//        $manager->persist($barber_1);
//
//
//        $barber_2 = new User();
//        $barber_2->setFirstName('Pesho');
//        $barber_2->setLastName('Mashinata');
//        $barber_2->setNickname('Bruma');
//        $barber_2->setEmail('bruma@barber.bg');
//        $barber_2->setPassword('1234567890');
//        $barber_2->setPhone('0812341234');
//        $barber->setRoles([Roles::ADMIN, Roles::CLIENT]);
//
//        $manager->persist($barber_2);
//
//        $manager->flush();
    }
}
