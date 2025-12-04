<?php

namespace App\Repository;

use App\Entity\BarberScheduleException;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BarberScheduleException>
 */
class BarberScheduleExceptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BarberScheduleException::class);
    }

    /**
     * Find exception for specific barber and date
     */
    public function findByBarberAndDate(User $barber, \DateTimeImmutable $date): ?BarberScheduleException
    {
        return $this->findOneBy([
            'barber' => $barber,
            'date' => $date,
        ]);
    }

    /**
     * Find all exceptions for barber in date range
     */
    public function findByBarberAndDateRange(User $barber, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.barber = :barber')
            ->andWhere('e.date >= :startDate')
            ->andWhere('e.date <= :endDate')
            ->setParameter('barber', $barber)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('e.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all exceptions for barber in specific month
     */
    public function findByBarberAndMonth(User $barber, int $year, int $month): array
    {
        $startDate = new \DateTimeImmutable("$year-$month-01");
        $endDate = $startDate->modify('last day of this month');

        return $this->findByBarberAndDateRange($barber, $startDate, $endDate);
    }

    /**
     * Save or update exception
     */
    public function save(BarberScheduleException $exception, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->persist($exception);

        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Delete exception
     */
    public function delete(BarberScheduleException $exception, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $em->remove($exception);

        if ($flush) {
            $em->flush();
        }
    }
}
