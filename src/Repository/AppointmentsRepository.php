<?php

namespace App\Repository;

use App\Entity\Appointments;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\AbstractQuery;

/**
 * @extends ServiceEntityRepository<Appointments>
 */
class AppointmentsRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Appointments::class);
        $this->entityManager = $entityManager;
    }

    public function getAllAppointments()
    {
        $res = $this->entityManager->createQueryBuilder()
                ->select('ub.nick_name')
                ->from(Appointments::class, 'a')
                ->leftJoin('a.barber', 'ub')
                ->getQuery()
                ->getArrayResult()
        ;

//            echo '<pre>'.var_export($res, true).'</pre>';
//        exit();

//                ->getArrayResult();

//        $q = $this->entityManager->createQuery("
//                SELECT a
//                FROM appointments a JOIN user u
//                WHERE a.barber_id = u.id
//        ");

//        $q->setParameter(6);
//        return $q->getResult();

        return $res;
    }

    public function findClientById(int $id)
    {
        var_dump("helllooo");
    }
    //    /**
    //     * @return Appointments[] Returns an array of Appointments objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Appointments
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
