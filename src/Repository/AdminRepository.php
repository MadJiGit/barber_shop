<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class AdminRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getAllClients(): array
    {
        $super_admin = 'ROLE_SUPER_ADMIN';

        return $this->createQueryBuilder('u')
            ->andWhere('u.roles = :role')
            ->setParameter('role', $super_admin)
            ->getQuery()
            ->getArrayResult();
    }
}
