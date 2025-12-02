<?php

namespace App\Twig\Components;

use App\Entity\AppointmentHours;
use App\Entity\Procedure;
use App\Entity\User;
use App\Repository\AppointmentsRepository;
use App\Repository\ProcedureRepository;
use App\Repository\UserRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class TimeSlots
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $selectedDate = '';

    #[LiveProp(writable: true)]
    public int $procedureId = 0;

    private UserRepository $userRepository;
    private AppointmentsRepository $appointmentsRepository;
    private ProcedureRepository $procedureRepository;

    public function __construct(
        UserRepository $userRepository,
        AppointmentsRepository $appointmentsRepository,
        ProcedureRepository $procedureRepository
    ) {
        $this->userRepository = $userRepository;
        $this->appointmentsRepository = $appointmentsRepository;
        $this->procedureRepository = $procedureRepository;
    }

    /**
     * Get all barbers sorted by seniority
     */
    public function getBarbers(): array
    {
        return $this->userRepository->getAllBarbersSortedBySeniority();
    }

    /**
     * Get selected procedure
     */
    public function getProcedure(): ?Procedure
    {
        if (!$this->procedureId || $this->procedureId === 0) {
            return null;
        }

        return $this->procedureRepository->findOneProcedureById($this->procedureId);
    }

    /**
     * Get all time slots
     */
    public function getTimeSlots(): array
    {
        return AppointmentHours::getAppointmentHours();
    }

    /**
     * Get all appointments for the selected date
     */
    public function getAppointments(): array
    {
        if (!$this->selectedDate || $this->selectedDate === '') {
            return [];
        }

        try {
            $date = new \DateTimeImmutable($this->selectedDate);
            $startOfDay = $date->setTime(0, 0, 0);
            $endOfDay = $date->setTime(23, 59, 59);

            return $this->appointmentsRepository->createQueryBuilder('a')
                ->where('a.date >= :startOfDay')
                ->andWhere('a.date <= :endOfDay')
                ->andWhere('a.status != :cancelled')
                ->setParameter('startOfDay', $startOfDay)
                ->setParameter('endOfDay', $endOfDay)
                ->setParameter('cancelled', 'cancelled')
                ->getQuery()
                ->getResult();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Check if a time slot is booked for a barber
     */
    public function isSlotBooked(User $barber, string $time): bool
    {
        $appointments = $this->getAppointments();

        foreach ($appointments as $appointment) {
            if ($appointment->getBarber()->getId() === $barber->getId()
                && $appointment->getDate()->format('H:i') === $time) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the price for a procedure based on barber level
     */
    public function getPriceForBarber(User $barber): ?string
    {
        $procedure = $this->getProcedure();

        if (!$procedure) {
            return null;
        }

        if ($barber->isBarberJunior()) {
            return $procedure->getPriceJunior();
        }

        return $procedure->getPriceMaster();
    }
}
