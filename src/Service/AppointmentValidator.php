<?php

namespace App\Service;

use App\Entity\Appointments;
use App\Entity\Procedure;
use App\Entity\User;
use App\Repository\AppointmentsRepository;
use DateTimeImmutable;

/**
 * Service for validating appointments and checking conflicts
 */
class AppointmentValidator
{
    private AppointmentsRepository $appointmentsRepository;

    public function __construct(AppointmentsRepository $appointmentsRepository)
    {
        $this->appointmentsRepository = $appointmentsRepository;
    }

    /**
     * Check if a barber is available at the specified time
     *
     * @param User $barber The barber to check
     * @param DateTimeImmutable $startTime The start time of the appointment
     * @param int $duration Duration in minutes
     * @param Appointments|null $excludeAppointment Exclude this appointment from check (for updates)
     * @return bool True if available, false if conflict exists
     */
    public function isBarberAvailable(
        User $barber,
        DateTimeImmutable $startTime,
        int $duration,
        ?Appointments $excludeAppointment = null
    ): bool {
        $endTime = $startTime->modify("+{$duration} minutes");

        // Get all appointments for this barber on this date
        $existingAppointments = $this->appointmentsRepository->findByBarberAndDate(
            $barber,
            $startTime
        );

        foreach ($existingAppointments as $appointment) {
            // Skip the appointment we're updating
            if ($excludeAppointment && $appointment->getId() === $excludeAppointment->getId()) {
                continue;
            }

            $appointmentStart = $appointment->getDate();
            $appointmentEnd = $appointmentStart->modify("+{$appointment->getDuration()} minutes");

            // Check for overlap
            if ($this->timesOverlap($startTime, $endTime, $appointmentStart, $appointmentEnd)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a client already has an appointment at the specified time
     *
     * @param User $client The client to check
     * @param DateTimeImmutable $startTime The start time of the appointment
     * @param int $duration Duration in minutes
     * @param Appointments|null $excludeAppointment Exclude this appointment from check (for updates)
     * @return bool True if available, false if conflict exists
     */
    public function isClientAvailable(
        User $client,
        DateTimeImmutable $startTime,
        int $duration,
        ?Appointments $excludeAppointment = null
    ): bool {
        $endTime = $startTime->modify("+{$duration} minutes");

        // Get all appointments for this client on this date
        $existingAppointments = $this->appointmentsRepository->findByClientAndDate(
            $client,
            $startTime
        );

        foreach ($existingAppointments as $appointment) {
            // Skip the appointment we're updating
            if ($excludeAppointment && $appointment->getId() === $excludeAppointment->getId()) {
                continue;
            }

            $appointmentStart = $appointment->getDate();
            $appointmentEnd = $appointmentStart->modify("+{$appointment->getDuration()} minutes");

            // Check for overlap
            if ($this->timesOverlap($startTime, $endTime, $appointmentStart, $appointmentEnd)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the appointment time is in the past
     *
     * @param DateTimeImmutable $appointmentTime
     * @return bool True if in the past
     */
    public function isInPast(DateTimeImmutable $appointmentTime): bool
    {
        $now = new DateTimeImmutable('now');
        return $appointmentTime < $now;
    }

    /**
     * Validate all conditions for an appointment
     *
     * @param User $client
     * @param User $barber
     * @param DateTimeImmutable $startTime
     * @param int $duration
     * @param Appointments|null $excludeAppointment
     * @return array Array of validation errors (empty if valid)
     */
    public function validateAppointment(
        User $client,
        User $barber,
        DateTimeImmutable $startTime,
        int $duration,
        ?Appointments $excludeAppointment = null
    ): array {
        $errors = [];

        // Check if time is in the past
        if ($this->isInPast($startTime)) {
            $errors[] = 'Не можете да запазите час в миналото.';
        }

        // Check barber availability
        if (!$this->isBarberAvailable($barber, $startTime, $duration, $excludeAppointment)) {
            $errors[] = 'Избраният бръснар е зает в този час.';
        }

        // Check client availability
        if (!$this->isClientAvailable($client, $startTime, $duration, $excludeAppointment)) {
            $errors[] = 'Вие вече имате запазен час по това време.';
        }

        return $errors;
    }

    /**
     * Get all available time slots for a barber on a specific date
     *
     * @param User $barber
     * @param DateTimeImmutable $date
     * @param Procedure $procedure
     * @return array Array of available time slots as DateTimeImmutable objects
     */
    public function getAvailableTimeSlots(
        User $barber,
        DateTimeImmutable $date,
        Procedure $procedure
    ): array {
        // Get duration based on barber level
        $duration = $this->getDurationForBarber($barber, $procedure);

        // Define working hours (TODO: get from BusinessHours entity)
        $workStart = $date->setTime(10, 0);
        $workEnd = $date->setTime(18, 0);

        $availableSlots = [];
        $currentSlot = $workStart;

        while ($currentSlot < $workEnd) {
            if ($this->isBarberAvailable($barber, $currentSlot, $duration)) {
                $availableSlots[] = $currentSlot;
            }

            // Move to next slot (30 minute intervals)
            $currentSlot = $currentSlot->modify('+30 minutes');
        }

        return $availableSlots;
    }

    /**
     * Get duration for a procedure based on barber level
     */
    private function getDurationForBarber(User $barber, Procedure $procedure): int
    {
        if ($barber->isBarberJunior()) {
            return $procedure->getDurationJunior();
        }

        return $procedure->getDurationMaster();
    }

    /**
     * Check if two time ranges overlap
     *
     * @param DateTimeImmutable $start1
     * @param DateTimeImmutable $end1
     * @param DateTimeImmutable $start2
     * @param DateTimeImmutable $end2
     * @return bool True if they overlap
     */
    private function timesOverlap(
        DateTimeImmutable $start1,
        DateTimeImmutable $end1,
        DateTimeImmutable $start2,
        DateTimeImmutable $end2
    ): bool {
        // Two ranges overlap if:
        // start1 < end2 AND end1 > start2
        return $start1 < $end2 && $end1 > $start2;
    }
}
