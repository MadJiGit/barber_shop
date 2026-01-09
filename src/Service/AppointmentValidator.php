<?php

namespace App\Service;

use App\Entity\Appointments;
use App\Entity\Procedure;
use App\Entity\User;
use App\Enum\AppointmentStatus;
use App\Repository\AppointmentsRepository;
use DateTimeImmutable;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Service for validating appointments and checking conflicts.
 */
class AppointmentValidator
{
    private AppointmentsRepository $appointmentsRepository;
    private BarberScheduleService $scheduleService;
    private TranslatorInterface $translator;

    public function __construct(
        AppointmentsRepository $appointmentsRepository,
        BarberScheduleService $scheduleService,
        TranslatorInterface $translator
    ) {
        $this->appointmentsRepository = $appointmentsRepository;
        $this->scheduleService = $scheduleService;
        $this->translator = $translator;
    }

    /**
     * Check if a barber is available at the specified time.
     *
     * @param User               $barber             The barber to check
     * @param \DateTimeImmutable $startTime          The start time of the appointment
     * @param int                $duration           Duration in minutes
     * @param Appointments|null  $excludeAppointment Exclude this appointment from check (for updates)
     *
     * @return bool True if available, false if conflict exists
     */
    public function isBarberAvailable(
        User $barber,
        \DateTimeImmutable $startTime,
        int $duration,
        ?Appointments $excludeAppointment = null,
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
     * Check if a client already has an appointment at the specified time.
     *
     * @param User               $client             The client to check
     * @param \DateTimeImmutable $startTime          The start time of the appointment
     * @param int                $duration           Duration in minutes
     * @param Appointments|null  $excludeAppointment Exclude this appointment from check (for updates)
     *
     * @return bool True if available, false if conflict exists
     */
    public function isClientAvailable(
        User $client,
        \DateTimeImmutable $startTime,
        int $duration,
        ?Appointments $excludeAppointment = null,
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
     * Check if the appointment time is in the past.
     *
     * @return bool True if in the past
     */
    public function isInPast(\DateTimeImmutable $appointmentTime): bool
    {
        $now = DateTimeHelper::now();

        return $appointmentTime < $now;
    }

    /**
     * Validate all conditions for an appointment.
     *
     * @return array Array of validation errors (empty if valid)
     */
    public function validateAppointment(
        User $client,
        User $barber,
        \DateTimeImmutable $startTime,
        int $duration,
        ?Appointments $excludeAppointment = null,
    ): array {
        $errors = [];

        // Check if time is in the past
        if ($this->isInPast($startTime)) {
            $errors[] = $this->translator->trans('validator.error.past_time', [], 'flash_messages');
        }

        // Check if barber is working at this time
        if (!$this->scheduleService->isBarberWorkingAt($barber, $startTime)) {
            $errors[] = $this->translator->trans('validator.error.barber_not_working', [], 'flash_messages');
        }

        // Check if appointment end time is within working hours
        $endTime = $startTime->modify("+{$duration} minutes");
        if (!$this->scheduleService->isBarberWorkingAt($barber, $endTime->modify('-1 minute'))) {
            $errors[] = $this->translator->trans('validator.error.procedure_exceeds_working_hours', [], 'flash_messages');
        }

        // Check barber availability (conflicts with other appointments)
        if (!$this->isBarberAvailable($barber, $startTime, $duration, $excludeAppointment)) {
            $errors[] = $this->translator->trans('validator.error.barber_busy', [], 'flash_messages');
        }

        // Check client availability
        if (!$this->isClientAvailable($client, $startTime, $duration, $excludeAppointment)) {
            $errors[] = $this->translator->trans('validator.error.client_already_booked', [], 'flash_messages');
        }

        return $errors;
    }

    /**
     * Get all available time slots for a barber on a specific date.
     *
     * @return array Array of available time slots as DateTimeImmutable objects
     */
    public function getAvailableTimeSlots(
        User $barber,
        \DateTimeImmutable $date,
        Procedure $procedure,
    ): array {
        // Get duration based on barber level
        $duration = $this->getDurationForBarber($barber, $procedure);

        // Get working hours from BarberScheduleService
        $workingHours = $this->scheduleService->getWorkingHoursForDate($barber, $date);

        // If barber is not working on this date, return empty array
        if (!$workingHours) {
            return [];
        }

        // Parse working hours
        [$startHour, $startMinute] = explode(':', $workingHours['start']);
        [$endHour, $endMinute] = explode(':', $workingHours['end']);

        $workStart = $date->setTime((int) $startHour, (int) $startMinute);
        $workEnd = $date->setTime((int) $endHour, (int) $endMinute);

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
     * Get duration for a procedure based on barber level.
     */
    private function getDurationForBarber(User $barber, Procedure $procedure): int
    {
        if ($barber->isBarberJunior()) {
            return $procedure->getDurationJunior();
        }

        return $procedure->getDurationMaster();
    }

    /**
     * Check if two time ranges overlap.
     *
     * @return bool True if they overlap
     */
    private function timesOverlap(
        \DateTimeImmutable $start1,
        \DateTimeImmutable $end1,
        \DateTimeImmutable $start2,
        \DateTimeImmutable $end2,
    ): bool {
        // Two ranges overlap if:
        // start1 < end2 AND end1 > start2
        return $start1 < $end2 && $end1 > $start2;
    }
}
