<?php

namespace App\Service;

use App\Entity\BarberScheduleException;
use App\Entity\User;
use App\Repository\AppointmentsRepository;
use App\Repository\BarberScheduleExceptionRepository;
use App\Repository\BarberScheduleRepository;

class BarberScheduleService
{
    private BarberScheduleRepository $scheduleRepository;
    private BarberScheduleExceptionRepository $exceptionRepository;
    private AppointmentsRepository $appointmentsRepository;

    public function __construct(
        BarberScheduleRepository $scheduleRepository,
        BarberScheduleExceptionRepository $exceptionRepository,
        AppointmentsRepository $appointmentsRepository
    ) {
        $this->scheduleRepository = $scheduleRepository;
        $this->exceptionRepository = $exceptionRepository;
        $this->appointmentsRepository = $appointmentsRepository;
    }

    /**
     * Get monthly calendar data for barber
     * Returns array of dates with their availability status
     *
     * @return array [
     *   'date' => '2025-11-27',
     *   'dayOfWeek' => 4,
     *   'status' => 'available|unavailable|partial|full',
     *   'working' => true|false,
     *   'occupiedSlots' => 3,
     *   'totalSlots' => 18
     * ]
     */
    public function getMonthCalendar(User $barber, int $year, int $month): array
    {
        // Get or create barber schedule
        $schedule = $this->scheduleRepository->findOrCreateForBarber($barber);

        // Get all exceptions for this month
        $exceptions = $this->exceptionRepository->findByBarberAndMonth($barber, $year, $month);
        $exceptionsMap = [];
        foreach ($exceptions as $exception) {
            $exceptionsMap[$exception->getDate()->format('Y-m-d')] = $exception;
        }

        // Get first and last day of month
        $firstDay = new \DateTimeImmutable("$year-$month-01");
        $lastDay = $firstDay->modify('last day of this month');

        $calendar = [];
        $currentDate = $firstDay;

        while ($currentDate <= $lastDay) {
            $dateStr = $currentDate->format('Y-m-d');
            $dayOfWeek = (int)$currentDate->format('w'); // 0-6

            // Get default schedule for this day of week
            $daySchedule = $schedule->getScheduleForDay($dayOfWeek);
            $working = $daySchedule['working'] ?? false;
            $startTime = $daySchedule['start'] ?? null;
            $endTime = $daySchedule['end'] ?? null;

            // Check for exception
            if (isset($exceptionsMap[$dateStr])) {
                $exception = $exceptionsMap[$dateStr];

                if ($exception->isFullDayOff()) {
                    // Full day off
                    $working = false;
                } elseif ($exception->hasCustomHours()) {
                    // Custom hours for this day
                    $working = true;
                    $startTime = $exception->getStartTime()?->format('H:i');
                    $endTime = $exception->getEndTime()?->format('H:i');
                }
            }

            // Calculate occupied slots if working
            $occupiedSlots = 0;
            $totalSlots = 0;

            if ($working && $startTime && $endTime) {
                $totalSlots = $this->calculateTotalSlots($startTime, $endTime);
                $occupiedSlots = $this->getOccupiedSlotsCount($barber, $currentDate);
            }

            // Determine status
            $status = 'unavailable'; // Default: not working
            if ($working) {
                if ($occupiedSlots == 0) {
                    $status = 'available'; // Fully available
                } elseif ($occupiedSlots < $totalSlots) {
                    $status = 'partial'; // Some slots occupied
                } else {
                    $status = 'full'; // All slots occupied
                }
            }

            $calendar[] = [
                'date' => $dateStr,
                'dayOfWeek' => $dayOfWeek,
                'working' => $working,
                'status' => $status,
                'occupiedSlots' => $occupiedSlots,
                'totalSlots' => $totalSlots,
                'startTime' => $startTime,
                'endTime' => $endTime,
            ];

            $currentDate = $currentDate->modify('+1 day');
        }

        return $calendar;
    }

    /**
     * Get day schedule with all time slots for modal
     *
     * @return array [
     *   'time' => '09:00',
     *   'available' => true,
     *   'locked' => false,
     *   'client' => null|'Name'
     * ]
     */
    public function getDaySchedule(User $barber, \DateTimeImmutable $date): array
    {
        $schedule = $this->scheduleRepository->findOrCreateForBarber($barber);
        $dayOfWeek = (int)$date->format('w');

        // Get default schedule
        $daySchedule = $schedule->getScheduleForDay($dayOfWeek);
        $working = $daySchedule['working'] ?? false;
        $startTime = $daySchedule['start'] ?? '09:00';
        $endTime = $daySchedule['end'] ?? '18:00';

        // Check for exception
        $exception = $this->exceptionRepository->findByBarberAndDate($barber, $date);

        if ($exception) {
            if ($exception->isFullDayOff()) {
                return []; // Not working this day
            }

            if ($exception->hasCustomHours()) {
                $startTime = $exception->getStartTime()->format('H:i');
                $endTime = $exception->getEndTime()->format('H:i');
            }
        }

        if (!$working && !$exception) {
            return []; // Not working by default
        }

        // Generate time slots (30 min intervals)
        $slots = $this->generateTimeSlots($startTime, $endTime);

        // Get occupied slots from appointments
        $occupiedSlotsData = $this->appointmentsRepository->getOccupiedSlotsByDate($date->format('Y-m-d'));
        $barberOccupiedSlots = $occupiedSlotsData[$barber->getId()] ?? [];

        // Get excluded slots from exception
        $excludedSlots = [];
        if ($exception && $exception->getExcludedSlots()) {
            $excludedSlots = $exception->getExcludedSlots();
        }

        // Build result
        $result = [];
        foreach ($slots as $slot) {
            $locked = in_array($slot, $barberOccupiedSlots);
            $available = !in_array($slot, $excludedSlots) && !$locked;

            $result[] = [
                'time' => $slot,
                'available' => $available,
                'locked' => $locked,
                'client' => $locked ? 'Client' : null, // TODO: Get actual client name
            ];
        }

        return $result;
    }

    /**
     * Calculate total number of 30-min slots between start and end time
     */
    private function calculateTotalSlots(string $startTime, string $endTime): int
    {
        $start = new \DateTime($startTime);
        $end = new \DateTime($endTime);
        $diff = $start->diff($end);

        return ($diff->h * 2) + ($diff->i / 30);
    }

    /**
     * Get count of occupied slots for barber on specific date
     */
    private function getOccupiedSlotsCount(User $barber, \DateTimeImmutable $date): int
    {
        $occupiedSlots = $this->appointmentsRepository->getOccupiedSlotsByDate($date->format('Y-m-d'));

        return count($occupiedSlots[$barber->getId()] ?? []);
    }

    /**
     * Generate array of time slots in 30-min intervals
     * Example: ['09:00', '09:30', '10:00', '10:30', ...]
     */
    private function generateTimeSlots(string $startTime, string $endTime): array
    {
        $slots = [];
        $current = new \DateTime($startTime);
        $end = new \DateTime($endTime);

        while ($current < $end) {
            $slots[] = $current->format('H:i');
            $current->modify('+30 minutes');
        }

        return $slots;
    }

    /**
     * Check if barber is working at specific date and time
     *
     * @param User $barber
     * @param \DateTimeImmutable $dateTime
     * @return bool True if barber is working at this time
     */
    public function isBarberWorkingAt(User $barber, \DateTimeImmutable $dateTime): bool
    {
        $schedule = $this->scheduleRepository->findOrCreateForBarber($barber);
        $dayOfWeek = (int)$dateTime->format('w');

        // Get default schedule for this day
        $daySchedule = $schedule->getScheduleForDay($dayOfWeek);
        $working = $daySchedule['working'] ?? false;
        $startTime = $daySchedule['start'] ?? null;
        $endTime = $daySchedule['end'] ?? null;

        // Check for exception on this specific date
        $exception = $this->exceptionRepository->findByBarberAndDate($barber, $dateTime);

        if ($exception) {
            if ($exception->isFullDayOff()) {
                return false; // Not working this day
            }

            if ($exception->hasCustomHours()) {
                $working = true;
                $startTime = $exception->getStartTime()?->format('H:i');
                $endTime = $exception->getEndTime()?->format('H:i');
            }

            // Check if time slot is in excluded slots
            $timeStr = $dateTime->format('H:i');
            if ($exception->getExcludedSlots() && in_array($timeStr, $exception->getExcludedSlots())) {
                return false;
            }
        }

        // If not working by default and no exception, return false
        if (!$working || !$startTime || !$endTime) {
            return false;
        }

        // Check if time is within working hours
        $appointmentTime = $dateTime->format('H:i');
        return $appointmentTime >= $startTime && $appointmentTime < $endTime;
    }

    /**
     * Get working hours for barber on specific date
     * Returns null if not working, otherwise ['start' => 'HH:MM', 'end' => 'HH:MM']
     *
     * @param User $barber
     * @param \DateTimeImmutable $date
     * @return array|null
     */
    public function getWorkingHoursForDate(User $barber, \DateTimeImmutable $date): ?array
    {
        $schedule = $this->scheduleRepository->findOrCreateForBarber($barber);
        $dayOfWeek = (int)$date->format('w');

        // Get default schedule
        $daySchedule = $schedule->getScheduleForDay($dayOfWeek);
        $working = $daySchedule['working'] ?? false;
        $startTime = $daySchedule['start'] ?? null;
        $endTime = $daySchedule['end'] ?? null;

        // Check for exception
        $exception = $this->exceptionRepository->findByBarberAndDate($barber, $date);

        if ($exception) {
            if ($exception->isFullDayOff()) {
                return null; // Not working
            }

            if ($exception->hasCustomHours()) {
                $startTime = $exception->getStartTime()?->format('H:i');
                $endTime = $exception->getEndTime()?->format('H:i');
                $working = true;
            }
        }

        if (!$working || !$startTime || !$endTime) {
            return null;
        }

        return [
            'start' => $startTime,
            'end' => $endTime,
            'excludedSlots' => $exception?->getExcludedSlots() ?? []
        ];
    }

    /**
     * Save schedule exception (full day off or custom hours)
     */
    public function saveException(
        User $barber,
        \DateTimeImmutable $date,
        bool $isAvailable,
        ?string $startTime = null,
        ?string $endTime = null,
        ?array $excludedSlots = null,
        ?string $reason = null,
        ?User $createdBy = null
    ): BarberScheduleException {
        // Check if exception already exists
        $exception = $this->exceptionRepository->findByBarberAndDate($barber, $date);

        if (!$exception) {
            $exception = new BarberScheduleException();
            $exception->setBarber($barber);
            $exception->setDate($date);
        }

        $exception->setIsAvailable($isAvailable);
        $exception->setReason($reason);

        if ($createdBy) {
            $exception->setCreatedBy($createdBy);
        }

        if ($startTime && $endTime) {
            $exception->setStartTime(new \DateTime($startTime));
            $exception->setEndTime(new \DateTime($endTime));
        }

        if ($excludedSlots) {
            $exception->setExcludedSlots($excludedSlots);
        }

        $this->exceptionRepository->save($exception);

        return $exception;
    }
}
