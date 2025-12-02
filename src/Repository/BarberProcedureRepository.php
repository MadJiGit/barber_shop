<?php

namespace App\Repository;

use App\Entity\BarberProcedure;
use App\Entity\Procedure;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BarberProcedure>
 */
class BarberProcedureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BarberProcedure::class);
    }

    /**
     * Get all procedures that a barber can currently perform
     *
     * @param User $barber
     * @return Procedure[]
     */
    public function findActiveProceduresForBarber(User $barber): array
    {
        $now = new \DateTimeImmutable('now');

        $qb = $this->createQueryBuilder('bp')
            ->select('bp, p')
            ->join('bp.procedure', 'p')
            ->where('bp.barber = :barber')
            ->andWhere('bp.can_perform = :can_perform')
            ->andWhere('bp.valid_from <= :now')
            ->andWhere('bp.valid_until IS NULL OR bp.valid_until >= :now')
            ->setParameter('barber', $barber)
            ->setParameter('can_perform', true)
            ->setParameter('now', $now)
            ->orderBy('p.type', 'ASC');

        $results = $qb->getQuery()->getResult();

        // Extract just the procedures from the BarberProcedure entities
        return array_map(fn($bp) => $bp->getProcedure(), $results);
    }

    /**
     * Get all barbers who can perform a specific procedure
     *
     * @param Procedure $procedure
     * @return User[]
     */
    public function findBarbersForProcedure(Procedure $procedure): array
    {
        $now = new \DateTimeImmutable('now');

        $qb = $this->createQueryBuilder('bp')
            ->select('bp, u')
            ->join('bp.barber', 'u')
            ->where('bp.procedure = :procedure')
            ->andWhere('bp.can_perform = :can_perform')
            ->andWhere('bp.valid_from <= :now')
            ->andWhere('bp.valid_until IS NULL OR bp.valid_until >= :now')
            ->andWhere('u.is_barber = :is_barber')
            ->setParameter('procedure', $procedure)
            ->setParameter('can_perform', true)
            ->setParameter('now', $now)
            ->setParameter('is_barber', true)
            ->orderBy('u.first_name', 'ASC');

        $results = $qb->getQuery()->getResult();

        // Extract just the barbers from the BarberProcedure entities
        return array_map(fn($bp) => $bp->getBarber(), $results);
    }

    /**
     * Check if a barber can perform a specific procedure
     *
     * @param User $barber
     * @param Procedure $procedure
     * @return bool
     */
    public function canBarberPerformProcedure(User $barber, Procedure $procedure): bool
    {
        $now = new \DateTimeImmutable('now');

        $qb = $this->createQueryBuilder('bp')
            ->select('COUNT(bp.id)')
            ->where('bp.barber = :barber')
            ->andWhere('bp.procedure = :procedure')
            ->andWhere('bp.can_perform = :can_perform')
            ->andWhere('bp.valid_from <= :now')
            ->andWhere('bp.valid_until IS NULL OR bp.valid_until >= :now')
            ->setParameter('barber', $barber)
            ->setParameter('procedure', $procedure)
            ->setParameter('can_perform', true)
            ->setParameter('now', $now);

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * Get barber-procedure mapping (if exists)
     *
     * @param User $barber
     * @param Procedure $procedure
     * @return BarberProcedure|null
     */
    public function findByBarberAndProcedure(User $barber, Procedure $procedure): ?BarberProcedure
    {
        return $this->createQueryBuilder('bp')
            ->where('bp.barber = :barber')
            ->andWhere('bp.procedure = :procedure')
            ->setParameter('barber', $barber)
            ->setParameter('procedure', $procedure)
            ->orderBy('bp.valid_from', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Save a barber-procedure mapping
     *
     * @param BarberProcedure $barberProcedure
     * @param bool $flush
     * @return void
     */
    public function save(BarberProcedure $barberProcedure, bool $flush = true): void
    {
        $this->getEntityManager()->persist($barberProcedure);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove a barber-procedure mapping
     *
     * @param BarberProcedure $barberProcedure
     * @param bool $flush
     * @return void
     */
    public function remove(BarberProcedure $barberProcedure, bool $flush = true): void
    {
        $this->getEntityManager()->remove($barberProcedure);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
