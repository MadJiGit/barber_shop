<?php

namespace App\Repository;

use App\Entity\Appointments;
use App\Entity\User;
use App\Service\DateTimeHelper;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
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
        return $this->entityManager->createQueryBuilder()
                ->select('ub.nick_name')
                ->from(Appointments::class, 'a')
                ->leftJoin('a.barber', 'ub')
                ->getQuery()
                ->getArrayResult();
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
        ;

        return $res->getResult();
    }

    /**
     * Save an appointment to the database
     *
     * @param Appointments $appointment
     * @param bool $flush Whether to flush immediately (default: true)
     * @return void
     */
    public function save(Appointments $appointment, bool $flush = true): void
    {
        $this->entityManager->persist($appointment);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * Remove an appointment from the database
     *
     * @param Appointments $appointment
     * @param bool $flush Whether to flush immediately (default: true)
     * @return void
     */
    public function remove(Appointments $appointment, bool $flush = true): void
    {
        $this->entityManager->remove($appointment);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * Find appointments for a barber on a specific date
     *
     * @param User $barber
     * @param DateTimeImmutable $date
     * @return Appointments[]
     */
    public function findByBarberAndDate(User $barber, DateTimeImmutable $date): array
    {
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59);

        return $this->createQueryBuilder('a')
            ->where('a.barber = :barber')
            ->andWhere('a.date >= :startOfDay')
            ->andWhere('a.date <= :endOfDay')
            ->andWhere('a.status != :cancelled')
            ->setParameter('barber', $barber)
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->setParameter('cancelled', 'cancelled')
            ->orderBy('a.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find appointments for a client on a specific date
     *
     * @param User $client
     * @param DateTimeImmutable $date
     * @return Appointments[]
     */
    public function findByClientAndDate(User $client, DateTimeImmutable $date): array
    {
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59);

        return $this->createQueryBuilder('a')
            ->where('a.client = :client')
            ->andWhere('a.date >= :startOfDay')
            ->andWhere('a.date <= :endOfDay')
            ->andWhere('a.status != :cancelled')
            ->setParameter('client', $client)
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->setParameter('cancelled', 'cancelled')
            ->orderBy('a.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get occupied time slots for a specific date
     * Returns array indexed by barber ID, containing arrays of occupied time strings
     * INCLUDES: appointments + excluded_slots from barber_schedule_exception
     *
     * @param string $date Date in Y-m-d format
     * @return array [barberId => ['09:00', '10:00', ...]]
     * @throws Exception
     */
    public function getOccupiedSlotsByDate(string $date): array
    {
        $startOfDay = new DateTimeImmutable($date . ' 00:00:00');
        $endOfDay = new DateTimeImmutable($date . ' 23:59:59');

        // Get appointments for this date
        $appointments = $this->createQueryBuilder('a')
            ->where('a.date >= :startOfDay')
            ->andWhere('a.date <= :endOfDay')
            ->andWhere('a.status != :cancelled')
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->setParameter('cancelled', 'cancelled')
            ->getQuery()
            ->getResult();

        // Group by barber ID and format times
        $occupiedSlots = [];
        foreach ($appointments as $appointment) {
            $barberId = $appointment->getBarber()->getId();
            $startTime = $appointment->getDate();

            if (!isset($occupiedSlots[$barberId])) {
                $occupiedSlots[$barberId] = [];
            }

            // Get procedure duration based on barber level
            $barber = $appointment->getBarber();
            $procedure = $appointment->getProcedureType();
            $duration = $barber->isBarberJunior()
                ? $procedure->getDurationJunior()
                : $procedure->getDurationMaster();

            // Add all 30-minute slots that this appointment occupies
            $slotsNeeded = (int) ceil($duration / 30);
            for ($i = 0; $i < $slotsNeeded; $i++) {
                $slotTime = clone $startTime;
                $slotTime = $slotTime->modify('+' . ($i * 30) . ' minutes');
                $occupiedSlots[$barberId][] = $slotTime->format('H:i');
            }
        }

        // Also get excluded slots from barber_schedule_exception
        $em = $this->getEntityManager();
        $exceptions = $em->getRepository(\App\Entity\BarberScheduleException::class)
            ->findBy(['date' => new DateTimeImmutable($date)]);

        foreach ($exceptions as $exception) {
            $barberId = $exception->getBarber()->getId();

            // If barber is not available at all this day
            if (!$exception->getIsAvailable()) {
                // Mark entire day as occupied (all possible slots)
                if (!isset($occupiedSlots[$barberId])) {
                    $occupiedSlots[$barberId] = [];
                }
                // Add marker that entire day is unavailable
                $occupiedSlots[$barberId][] = '__FULL_DAY_OFF__';
                continue;
            }

            // If barber has excluded specific slots
            if ($exception->getExcludedSlots()) {
                if (!isset($occupiedSlots[$barberId])) {
                    $occupiedSlots[$barberId] = [];
                }

                foreach ($exception->getExcludedSlots() as $excludedSlot) {
                    $occupiedSlots[$barberId][] = $excludedSlot;
                }
            }
        }

        return $occupiedSlots;
    }

    /**
     * Find all upcoming appointments for a barber
     *
     * @param User $barber
     * @param bool $includeToday Include today's appointments
     * @return Appointments[]
     * @throws Exception
     */
    public function findUpcomingAppointmentsByBarber(User $barber, bool $includeToday = true): array
    {
        $now = DateTimeHelper::now();
        $startDate = $includeToday ? $now->setTime(0, 0, 0) : $now;

        return $this->createQueryBuilder('a')
            ->where('a.barber = :barber')
            ->andWhere('a.date >= :startDate')
            ->andWhere('a.status != :cancelled')
            ->setParameter('barber', $barber)
            ->setParameter('startDate', $startDate)
            ->setParameter('cancelled', 'cancelled')
            ->orderBy('a.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all appointments for a barber within date range
     *
     * @param User|null $barber
     * @param DateTimeImmutable|null $startDate
     * @param DateTimeImmutable|null $endDate
     * @param string|null $status Filter by status (confirmed, cancelled, completed)
     * @return Appointments[]
     */
    public function findBarberAppointments(
        ?User              $barber = null,
        ?DateTimeImmutable $startDate = null,
        ?DateTimeImmutable $endDate = null,
        ?string            $status = null
    ): array {
        $qb = $this->createQueryBuilder('a');

        // Only filter by barber if provided (null = all barbers)
        if ($barber !== null) {
            $qb->where('a.barber = :barber')
                ->setParameter('barber', $barber);
        }

        if ($startDate) {
            $qb->andWhere('a.date >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $qb->andWhere('a.date < :endDate')
                ->setParameter('endDate', $endDate);
        }

        if ($status) {
            $qb->andWhere('a.status = :status')
                ->setParameter('status', $status);
        }

        return $qb->orderBy('a.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find appointments with filters (for barbers and clients)
     *
     * @param User|null $user - The barber or client
     * @param string $userType - 'barber' or 'client'
     * @param DateTimeImmutable|null $dateFrom
     * @param DateTimeImmutable|null $dateTo
     * @param array|null $statuses - Array of statuses to filter
     * @param string|null $searchTerm - Search in client name, email, phone
     * @param int $page - Page number for pagination
     * @param int $limit - Items per page
     * @return array ['items' => Appointments[], 'total' => int]
     */
    public function findAppointmentsWithFilters(
        ?User $user,
        string $userType,
        ?DateTimeImmutable $dateFrom = null,
        ?DateTimeImmutable $dateTo = null,
        ?array $statuses = null,
        ?string $searchTerm = null,
        int $page = 1,
        int $limit = 20
    ): array {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.client', 'c')
            ->leftJoin('a.barber', 'b')
            ->leftJoin('a.procedure_type', 'p');

        // Filter by user (barber or client)
        if ($user) {
            if ($userType === 'barber') {
                $qb->andWhere('a.barber = :user');
            } else {
                $qb->andWhere('a.client = :user');
            }
            $qb->setParameter('user', $user);
        }

        // Date range filters
        if ($dateFrom) {
            $qb->andWhere('a.date >= :dateFrom')
                ->setParameter('dateFrom', $dateFrom);
        }

        if ($dateTo) {
            // Add 23:59:59 to include the entire end date
            $dateToEnd = $dateTo->modify('+1 day')->modify('-1 second');
            $qb->andWhere('a.date <= :dateTo')
                ->setParameter('dateTo', $dateToEnd);
        }

        // Status filter
        if ($statuses && count($statuses) > 0) {
            $qb->andWhere('a.status IN (:statuses)')
                ->setParameter('statuses', $statuses);
        }

        // Search term (client name, email, phone) - only for barbers
        if ($searchTerm && $userType === 'barber') {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('c.first_name', ':search'),
                    $qb->expr()->like('c.last_name', ':search'),
                    $qb->expr()->like('c.nick_name', ':search'),
                    $qb->expr()->like('c.email', ':search'),
                    $qb->expr()->like('c.phone', ':search')
                )
            )->setParameter('search', '%' . $searchTerm . '%');
        }

        // Count total
        $countQb = clone $qb;
        $total = $countQb->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Apply pagination
        $offset = ($page - 1) * $limit;
        $items = $qb->orderBy('a.date', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($total / $limit),
        ];
    }

}
