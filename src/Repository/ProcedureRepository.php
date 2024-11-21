<?php

namespace App\Repository;

use App\Entity\Procedure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Procedure>
 */
class ProcedureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Procedure::class);
    }

    public function getAllProcedures(): array
    {
        return $this->createQueryBuilder('u')
            ->select(
                'u.id',
                'u.type',
                'u.available',
                'u.price_master',
                'u.price_junior',
                'u.duration_master',
                'u.duration_junior',
                'u.date_added',
                'u.date_stopped',
                'u.date_last_update')
            ->orderBy('u.id')
            ->getQuery()
            ->getArrayResult();
    }

    public function getAllProceduresTypes(): array
    {
        return $this->createQueryBuilder('u')
        ->select(
            'u.id',
            'u.type',
            //                'u.available',
            //                'u.price_master',
            //                'u.price_junior',
            //                'u.duration_master',
            //                'u.duration_junior',
            //                'u.date_added',
            //                'u.date_stopped',
            //                'u.date_last_update'
        )
                ->orderBy('u.id')
                ->getQuery()
                ->getArrayResult();
    }

    public function findOneProcedureById($id): Procedure
    {
        return $this->createQueryBuilder('u')
            ->select('u')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Procedure[] Returns an array of Procedure objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Procedure
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
