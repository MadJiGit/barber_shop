<?php

namespace App\Repository;

use App\Entity\BarberSchedule;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BarberSchedule>
 */
class BarberScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BarberSchedule::class);
    }

    /**
     * Find or create schedule for barber
     * If barber doesn't have a schedule, create one with default template
     */
    public function findOrCreateForBarber(User $barber): BarberSchedule
    {
        $schedule = $this->findOneBy(['barber' => $barber]);

        if (!$schedule) {
            $schedule = new BarberSchedule();
            $schedule->setBarber($barber);

            $em = $this->getEntityManager();
            $em->persist($schedule);
            $em->flush();
        }

        return $schedule;
    }

    /**
     * Get schedule for specific barber
     */
    public function findByBarber(User $barber): ?BarberSchedule
    {
        return $this->findOneBy(['barber' => $barber]);
    }

    /**
     * Update schedule for barber
     */
    public function updateSchedule(User $barber, array $scheduleData): BarberSchedule
    {
        $schedule = $this->findOrCreateForBarber($barber);
        $schedule->setScheduleData($scheduleData);

        $em = $this->getEntityManager();
        $em->persist($schedule);
        $em->flush();

        return $schedule;
    }
}
