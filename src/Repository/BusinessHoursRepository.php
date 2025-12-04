<?php

namespace App\Repository;

use App\Entity\BusinessHours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BusinessHours>
 */
class BusinessHoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BusinessHours::class);
    }

    public function findByDayOfWeek(int $dayOfWeek): ?BusinessHours
    {
        return $this->createQueryBuilder('bh')
            ->andWhere('bh.day_of_week = :day')
            ->setParameter('day', $dayOfWeek)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('bh')
            ->orderBy('bh.day_of_week', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function isOpenOnDay(int $dayOfWeek): bool
    {
        $hours = $this->findByDayOfWeek($dayOfWeek);
        return $hours && !$hours->getIsClosed();
    }
}