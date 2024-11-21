<?php

namespace App\Repository;

use App\Entity\Appointments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Attribute\Route;

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

    public function getAllAppointments(): array
    {
        $res = $this->entityManager->createQueryBuilder()
                ->select('ub.nick_name')
                ->from(Appointments::class, 'a')
                ->leftJoin('a.barber', 'ub')
                ->getQuery()
                ->getArrayResult();

        return $res;
    }

    /**
     * @return Appointments[] Returns an array of Appointments objects
     */
    #[Route('/repo_test/{id}', name: 'repo_test')]
    public function findAllAppointmentsOfClientWithId($id): array
    {
        $date = date('Y-m-d');
        //        return
        $res = $this->createQueryBuilder('a')
        ->where('a.client = :id')
        ->andWhere('a.date > :date')
        ->setParameter('id', $id)
        ->setParameter('date', $date)
        ->orderBy('a.date', 'DESC')
        ->setMaxResults(10)
        ->getQuery()
//            ->getResult()
        ;

        //        echo '<pre>'.var_export($res->getSQL(), true).'</pre>';
        //        exit;
        return $res->getResult();
    }

    /**
     * @return Appointments[] Returns an array of Appointments objects
     */
    public function findAllAppointmentsOfBarberWithId($id): array
    {
        $date = date('Y-m-d');
        //        return
        $res = $this->createQueryBuilder('a')
            ->where('a.barber = :id')
            ->andWhere('a.date > :date')
            ->setParameter('id', $id)
            ->setParameter('date', $date)
            ->orderBy('a.date', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
//            ->getResult()
        ;

        //        echo '<pre>'.var_export($res->getSQL(), true).'</pre>';
        //        exit;
        return $res->getResult();
    }

    public function saveAppointment(array $data)
    {
    }

    public function findClientById(int $id)
    {
        var_dump('helllooo');
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
