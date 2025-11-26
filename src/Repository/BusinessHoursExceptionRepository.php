<?php

namespace App\Repository;

use App\Entity\BusinessHoursException;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BusinessHoursException>
 */
class BusinessHoursExceptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BusinessHoursException::class);
    }

    public function findByDate(\DateTimeInterface $date): ?BusinessHoursException
    {
        return $this->createQueryBuilder('bhe')
            ->andWhere('bhe.date = :date')
            ->andWhere('bhe.barber IS NULL')
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByDateAndBarber(\DateTimeInterface $date, User $barber): ?BusinessHoursException
    {
        return $this->createQueryBuilder('bhe')
            ->andWhere('bhe.date = :date')
            ->andWhere('bhe.barber = :barber')
            ->setParameter('date', $date->format('Y-m-d'))
            ->setParameter('barber', $barber)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllExceptionsForDate(\DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('bhe')
            ->andWhere('bhe.date = :date')
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->getResult();
    }

    public function findUpcomingExceptions(int $days = 30): array
    {
        $today = new \DateTimeImmutable('today');
        $endDate = $today->modify("+{$days} days");

        return $this->createQueryBuilder('bhe')
            ->andWhere('bhe.date >= :today')
            ->andWhere('bhe.date <= :endDate')
            ->setParameter('today', $today)
            ->setParameter('endDate', $endDate)
            ->orderBy('bhe.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function isClosedOn(\DateTimeInterface $date, ?User $barber = null): bool
    {
        $qb = $this->createQueryBuilder('bhe')
            ->andWhere('bhe.date = :date')
            ->andWhere('bhe.is_closed = :closed')
            ->setParameter('date', $date->format('Y-m-d'))
            ->setParameter('closed', true);

        if ($barber) {
            $qb->andWhere('(bhe.barber = :barber OR bhe.barber IS NULL)')
                ->setParameter('barber', $barber);
        } else {
            $qb->andWhere('bhe.barber IS NULL');
        }

        return $qb->getQuery()->getOneOrNullResult() !== null;
    }
}