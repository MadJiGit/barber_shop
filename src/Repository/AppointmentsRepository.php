<?php

namespace App\Repository;

use App\Entity\Appointments;
use App\Entity\BarberScheduleException;
use App\Entity\User;
use App\Enum\AppointmentStatus;
use App\Service\DateTimeHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

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
     * Save an appointment to the database.
     *
     * @param bool $flush Whether to flush immediately (default: true)
     */
    public function save(Appointments $appointment, bool $flush = true): void
    {
        $this->entityManager->persist($appointment);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * Remove an appointment from the database.
     *
     * @param bool $flush Whether to flush immediately (default: true)
     */
    public function remove(Appointments $appointment, bool $flush = true): void
    {
        $this->entityManager->remove($appointment);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * Find appointments for a barber on a specific date.
     *
     * @return Appointments[]
     */
    public function findByBarberAndDate(User $barber, \DateTimeImmutable $date): array
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
            ->setParameter('cancelled', AppointmentStatus::CANCELLED)
            ->orderBy('a.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find appointments for a client on a specific date.
     *
     * @return Appointments[]
     */
    public function findByClientAndDate(User $client, \DateTimeImmutable $date): array
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
            ->setParameter('cancelled', AppointmentStatus::CANCELLED)
            ->orderBy('a.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get occupied time slots for a specific date
     * Returns array indexed by barber ID, containing arrays of occupied time strings
     * INCLUDES: appointments + excluded_slots from barber_schedule_exception.
     *
     * @param string $date Date in Y-m-d format
     *
     * @return array [barberId => ['09:00', '10:00', ...]]
     *
     * @throws \Exception
     */
    public function getOccupiedSlotsByDate(string $date): array
    {
        $startOfDay = new \DateTimeImmutable($date.' 00:00:00');
        $endOfDay = new \DateTimeImmutable($date.' 23:59:59');

        // Get appointments for this date
        $appointments = $this->createQueryBuilder('a')
            ->where('a.date >= :startOfDay')
            ->andWhere('a.date <= :endOfDay')
            ->andWhere('a.status != :cancelled')
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->setParameter('cancelled', AppointmentStatus::CANCELLED)
            ->getQuery()
            ->getResult();

        // Group by barber ID and format times
        $occupiedSlots = [];
        foreach ($appointments as $appointment) {
            // Skip expired pending confirmations (older than 15 minutes)
            // These are guest reservations that were never confirmed via email
            if ($appointment->getStatus() === AppointmentStatus::PENDING_CONFIRMATION) {
                $now = DateTimeHelper::now();
                $createdAt = $appointment->getDateAdded();

                if ($createdAt) {
                    $minutesElapsed = ($now->getTimestamp() - $createdAt->getTimestamp()) / 60;

                    if ($minutesElapsed > 15) {
                        // Ignore this expired pending appointment
                        // It will remain in database for history/analytics
                        continue;
                    }
                }
            }

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
            for ($i = 0; $i < $slotsNeeded; ++$i) {
                $slotTime = clone $startTime;
                $slotTime = $slotTime->modify('+'.($i * 30).' minutes');
                $occupiedSlots[$barberId][] = $slotTime->format('H:i');
            }
        }

        // Note: We do NOT include excluded slots or day-off markers here.
        // This method should ONLY return slots occupied by actual client appointments.
        // Excluded slots and schedule exceptions are handled separately in BarberScheduleService.

        return $occupiedSlots;
    }

    /**
     * Find all upcoming appointments for a barber.
     *
     * @param bool $includeToday Include today's appointments
     *
     * @return Appointments[]
     *
     * @throws \Exception
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
            ->setParameter('cancelled', AppointmentStatus::CANCELLED)
            ->orderBy('a.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all appointments for a barber within date range.
     *
     * @param string|null $status Filter by status (confirmed, cancelled, completed)
     *
     * @return Appointments[]
     */
    public function findBarberAppointments(
        ?User $barber = null,
        ?\DateTimeImmutable $startDate = null,
        ?\DateTimeImmutable $endDate = null,
        ?string $status = null,
    ): array {
        $qb = $this->createQueryBuilder('a');

        // Only filter by barber if provided (null = all barbers)
        if (null !== $barber) {
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
            $enumStatus = AppointmentStatus::tryFrom($status);
            if ($enumStatus) {
                $qb->andWhere('a.status = :status')
                    ->setParameter('status', $enumStatus);
            }
        }

        return $qb->orderBy('a.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find appointments with filters (for barbers and clients).
     *
     * @param User|null   $user       - The barber or client
     * @param string      $userType   - 'barber' or 'client'
     * @param array|null  $statuses   - Array of statuses to filter
     * @param string|null $searchTerm - Search in client name, email, phone
     * @param int         $page       - Page number for pagination
     * @param int         $limit      - Items per page
     *
     * @return array ['items' => Appointments[], 'total' => int]
     */
    public function findAppointmentsWithFilters(
        ?User $user,
        string $userType,
        ?\DateTimeImmutable $dateFrom = null,
        ?\DateTimeImmutable $dateTo = null,
        ?array $statuses = null,
        ?string $searchTerm = null,
        int $page = 1,
        int $limit = 20,
    ): array {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.client', 'c')
            ->leftJoin('a.barber', 'b')
            ->leftJoin('a.procedure_type', 'p');

        // Filter by user (barber or client)
        if ($user) {
            if ('barber' === $userType) {
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
            // Convert string statuses to AppointmentStatus enums
            $enumStatuses = array_map(function ($status) {
                return AppointmentStatus::tryFrom($status) ?? AppointmentStatus::PENDING;
            }, $statuses);

            $qb->andWhere('a.status IN (:statuses)')
                ->setParameter('statuses', $enumStatuses);
        }

        // Search term (client name, email, phone) - only for barbers
        if ($searchTerm && 'barber' === $userType) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('c.first_name', ':search'),
                    $qb->expr()->like('c.last_name', ':search'),
                    $qb->expr()->like('c.nick_name', ':search'),
                    $qb->expr()->like('c.email', ':search'),
                    $qb->expr()->like('c.phone', ':search')
                )
            )->setParameter('search', '%'.$searchTerm.'%');
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
