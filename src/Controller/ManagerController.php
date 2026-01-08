<?php

namespace App\Controller;

use App\Entity\Appointments;
use App\Entity\Procedure;
use App\Repository\AppointmentsRepository;
use App\Repository\UserRepository;
use App\Service\AppointmentValidator;
use App\Service\BarberScheduleService;
use App\Service\DateTimeHelper;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/manager')]
#[IsGranted('ROLE_MANAGER')]
class ManagerController extends AbstractController
{
    private EntityManagerInterface $em;
    private AppointmentsRepository $appointmentsRepository;
    private UserRepository $userRepository;
    private BarberScheduleService $scheduleService;
    private AppointmentValidator $appointmentValidator;

    public function __construct(
        EntityManagerInterface $em,
        AppointmentsRepository $appointmentsRepository,
        UserRepository $userRepository,
        BarberScheduleService $scheduleService,
        AppointmentValidator $appointmentValidator,
    ) {
        $this->em = $em;
        $this->appointmentsRepository = $appointmentsRepository;
        $this->userRepository = $userRepository;
        $this->scheduleService = $scheduleService;
        $this->appointmentValidator = $appointmentValidator;
    }

    /**
     * Manager Dashboard - Overview with key metrics.
     *
     * @throws \Exception
     */
    #[Route('/dashboard', name: 'manager_dashboard')]
    public function dashboard(): Response
    {
        $today = DateTimeHelper::now();
        $tomorrow = $today->modify('+1 day');
        $startOfWeek = $today->modify('monday this week');
        $endOfWeek = $today->modify('sunday this week')->modify('+1 day');
        $startOfMonth = new \DateTimeImmutable('first day of this month');
        $endOfMonth = $startOfMonth->modify('+1 month');

        // Today's appointments (from 00:00:00 today to 00:00:00 tomorrow)
        $todayAppointments = $this->appointmentsRepository->findBarberAppointments(
            barber: null,
            startDate: $today,
            endDate: $tomorrow
        );

        // This week's appointments (from Monday to end of Sunday)
        $weekAppointments = $this->appointmentsRepository->findBarberAppointments(
            barber: null,
            startDate: $startOfWeek,
            endDate: $endOfWeek
        );

        // This month's appointments (from 1st to end of month)
        $monthAppointments = $this->appointmentsRepository->findBarberAppointments(
            barber: null,
            startDate: $startOfMonth,
            endDate: $endOfMonth
        );

        // Get all barbers for stats
        $barbers = $this->userRepository->getAllBarbers();

        // Calculate today's stats
        $todayStats = [
            'total' => count($todayAppointments),
            'confirmed' => count(array_filter($todayAppointments, fn ($a) => 'confirmed' === $a->getStatus())),
            'completed' => count(array_filter($todayAppointments, fn ($a) => 'completed' === $a->getStatus())),
            'cancelled' => count(array_filter($todayAppointments, fn ($a) => 'cancelled' === $a->getStatus())),
        ];

        return $this->render('manager/dashboard.html.twig', [
            'todayAppointments' => $todayAppointments,
            'todayStats' => $todayStats,
            'weekCount' => count($weekAppointments),
            'monthCount' => count($monthAppointments),
            'barbers' => $barbers,
            'today' => $today,
        ]);
    }

    /**
     * List all appointments with filters, sorting, and pagination.
     */
    #[Route('/appointments', name: 'manager_appointments')]
    public function appointments(Request $request): Response
    {
        // Get filter parameters
        $filterDate = $request->query->get('date');
        $filterBarberId = $request->query->get('barber');
        $filterStatus = $request->query->get('status');
        $filterClientSearch = $request->query->get('client');

        // Get sorting parameters
        $sortBy = $request->query->get('sort', 'date'); // default: date
        $sortOrder = $request->query->get('order', 'desc'); // default: desc (newest first)

        // Get pagination parameters
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = (int) $request->query->get('perPage', 20); // default: 20
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20; // validate

        // Parse date filter
        $startDate = null;
        $endDate = null;
        if ($filterDate) {
            try {
                $startDate = new \DateTimeImmutable($filterDate);
                //                $startDate = new DateTimeInterface($filterDate);
                $endDate = $startDate->modify('+1 day');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Невалидна дата.');
            }
        }

        // Get barber filter
        $barber = null;
        if ($filterBarberId) {
            $barber = $this->userRepository->find($filterBarberId);
        }

        // Get appointments
        $appointments = $this->appointmentsRepository->findBarberAppointments(
            barber: $barber,
            startDate: $startDate,
            endDate: $endDate,
            status: $filterStatus
        );

        // Filter by client name (if provided)
        if ($filterClientSearch) {
            $searchTerm = mb_strtolower($filterClientSearch);
            $appointments = array_filter($appointments, function ($appointment) use ($searchTerm) {
                $client = $appointment->getClient();
                $fullName = mb_strtolower($client->getFirstName().' '.$client->getLastName());
                $email = mb_strtolower($client->getEmail());

                return str_contains($fullName, $searchTerm) || str_contains($email, $searchTerm);
            });
        }

        // Sorting logic
        $validSortFields = ['id', 'date', 'date_added', 'date_last_update'];
        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'date';
        }
        $sortOrder = 'asc' === strtolower($sortOrder) ? 'asc' : 'desc';

        usort($appointments, function ($a, $b) use ($sortBy, $sortOrder) {
            $valueA = match ($sortBy) {
                'id' => $a->getId(),
                'date' => $a->getDate(),
                'date_added' => $a->getDateAdded(),
                'date_last_update' => $a->getDateLastUpdate(),
                default => $a->getDate(),
            };

            $valueB = match ($sortBy) {
                'id' => $b->getId(),
                'date' => $b->getDate(),
                'date_added' => $b->getDateAdded(),
                'date_last_update' => $b->getDateLastUpdate(),
                default => $b->getDate(),
            };

            $comparison = $valueA <=> $valueB;

            return 'asc' === $sortOrder ? $comparison : -$comparison;
        });

        // Pagination
        $totalAppointments = count($appointments);
        $totalPages = (int) ceil($totalAppointments / $perPage);
        $page = min($page, max(1, $totalPages)); // ensure page is within bounds

        $offset = ($page - 1) * $perPage;
        $paginatedAppointments = array_slice($appointments, $offset, $perPage);

        // Get all barbers for filter dropdown
        $allBarbers = $this->userRepository->getAllBarbers();

        return $this->render('manager/appointments.html.twig', [
            'appointments' => $paginatedAppointments,
            'allBarbers' => $allBarbers,
            'filterDate' => $filterDate,
            'filterBarberId' => $filterBarberId,
            'filterStatus' => $filterStatus,
            'filterClientSearch' => $filterClientSearch,
            // Sorting
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            // Pagination
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalAppointments' => $totalAppointments,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Get all available procedures (API endpoint for AJAX).
     */
    #[Route('/api/procedures', name: 'manager_api_procedures', methods: ['GET'])]
    public function getProcedures(): Response
    {
        $procedures = $this->em->getRepository(Procedure::class)
            ->getAvailableProcedures();

        $data = array_map(function ($proc) {
            return [
                'id' => $proc->getId(),
                'type' => $proc->getType(),
                'available' => $proc->getAvailable(),
                'price_master' => $proc->getPriceMaster(),
                'price_junior' => $proc->getPriceJunior(),
                'duration_master' => $proc->getDurationMaster(),
                'duration_junior' => $proc->getDurationJunior(),
            ];
        }, $procedures);

        return $this->json($data);
    }

    /**
     * Get available time slots for a barber on a specific date (API endpoint).
     */
    #[Route('/api/barber/{barberId}/timeslots/{date}', name: 'manager_api_timeslots', methods: ['GET'])]
    public function getAvailableTimeSlots(int $barberId, string $date): Response
    {
        $barber = $this->userRepository->find($barberId);

        if (!$barber) {
            return $this->json(['error' => 'Барбърът не е намерен.'], 404);
        }

        try {
            $selectedDate = new \DateTimeImmutable($date);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Невалидна дата.'], 400);
        }

        // Get working hours for this barber on this date
        $workingHours = $this->scheduleService->getWorkingHoursForDate($barber, $selectedDate);

        if (!$workingHours) {
            return $this->json(['slots' => [], 'message' => 'Барбърът не работи в този ден.']);
        }

        // Generate time slots (every 30 minutes)
        $slots = [];
        $startHour = (int) explode(':', $workingHours['start'])[0];
        $startMin = (int) explode(':', $workingHours['start'])[1];
        $endHour = (int) explode(':', $workingHours['end'])[0];
        $endMin = (int) explode(':', $workingHours['end'])[1];

        $startMinutes = $startHour * 60 + $startMin;
        $endMinutes = $endHour * 60 + $endMin;

        for ($minutes = $startMinutes; $minutes < $endMinutes; $minutes += 30) {
            $slotHour = floor($minutes / 60);
            $slotMin = $minutes % 60;
            $timeSlot = sprintf('%02d:%02d', $slotHour, $slotMin);

            // Skip excluded slots
            if (!in_array($timeSlot, $workingHours['excludedSlots'])) {
                $slots[] = $timeSlot;
            }
        }

        return $this->json([
            'slots' => $slots,
            'workingHours' => [
                'start' => $workingHours['start'],
                'end' => $workingHours['end'],
            ],
        ]);
    }

    /**
     * Barber schedules management page.
     */
    #[Route('/barber-schedules', name: 'manager_barber_schedules')]
    public function barberSchedules(Request $request): Response
    {
        // Get all barbers
        $barbers = $this->userRepository->getAllBarbers();

        // Get selected barber from query parameter
        $selectedBarberId = $request->query->get('barber_id');
        $selectedBarber = null;
        $calendar = [];
        $year = (int) ($request->query->get('year') ?: date('Y'));
        $month = (int) ($request->query->get('month') ?: date('n'));

        if ($selectedBarberId) {
            $selectedBarber = $this->userRepository->find($selectedBarberId);

            if ($selectedBarber && $selectedBarber->isBarber()) {
                // Get calendar for selected barber
                $calendar = $this->scheduleService->getMonthCalendar($selectedBarber, $year, $month);
            }
        }

        // Calculate prev/next month
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            --$prevYear;
        }

        $nextMonth = $month + 1;
        $nextYear = $year;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            ++$nextYear;
        }

        // Get month name in Bulgarian
        $monthNames = [
            1 => 'Януари', 2 => 'Февруари', 3 => 'Март', 4 => 'Април',
            5 => 'Май', 6 => 'Юни', 7 => 'Юли', 8 => 'Август',
            9 => 'Септември', 10 => 'Октомври', 11 => 'Ноември', 12 => 'Декември',
        ];
        $monthName = $monthNames[$month];

        return $this->render('manager/barber_schedules.html.twig', [
            'barbers' => $barbers,
            'selectedBarber' => $selectedBarber,
            'calendar' => $calendar,
            'year' => $year,
            'month' => $month,
            'monthName' => $monthName,
            'prevYear' => $prevYear,
            'prevMonth' => $prevMonth,
            'nextYear' => $nextYear,
            'nextMonth' => $nextMonth,
        ]);
    }

    /**
     * Get day schedule for a barber (API endpoint for manager).
     */
    #[Route('/barber/{barberId}/schedule/day/{date}', name: 'manager_barber_schedule_day', methods: ['GET'])]
    public function getBarberDaySchedule(int $barberId, string $date): Response
    {
        $barber = $this->userRepository->find($barberId);

        if (!$barber || !$barber->isBarber()) {
            return $this->json(['error' => 'Барбърът не е намерен'], 404);
        }

        try {
            $dateObj = new \DateTimeImmutable($date);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Невалиден формат на датата'], 400);
        }

        $daySchedule = $this->scheduleService->getDaySchedule($barber, $dateObj);

        $dayNames = [
            0 => 'Неделя', 1 => 'Понеделник', 2 => 'Вторник', 3 => 'Сряда',
            4 => 'Четвъртък', 5 => 'Петък', 6 => 'Събота',
        ];

        return $this->json([
            'date' => $date,
            'dayOfWeek' => $dayNames[(int) $dateObj->format('w')],
            'slots' => $daySchedule,
        ]);
    }

    /**
     * Save barber schedule exception (manager can edit any barber's schedule).
     */
    #[Route('/barber/{barberId}/schedule/save', name: 'manager_barber_schedule_save', methods: ['POST'])]
    public function saveBarberSchedule(int $barberId, Request $request): Response
    {
        $barber = $this->userRepository->find($barberId);

        if (!$barber || !$barber->isBarber()) {
            return $this->json(['error' => 'Барбърът не е намерен'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['date'])) {
            return $this->json(['error' => 'Датата е задължителна'], 400);
        }

        try {
            $date = new \DateTimeImmutable($data['date']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Невалиден формат на датата'], 400);
        }

        $isAvailable = $data['is_available'] ?? true;
        $startTime = $data['start_time'] ?? null;
        $endTime = $data['end_time'] ?? null;
        $excludedSlots = $data['excluded_slots'] ?? null;
        $reason = $data['reason'] ?? null;

        /** @var \App\Entity\User $manager */
        $manager = $this->getUser();

        try {
            $exception = $this->scheduleService->saveException(
                $barber,
                $date,
                $isAvailable,
                $startTime,
                $endTime,
                $excludedSlots,
                $reason,
                $manager
            );

            return $this->json([
                'success' => true,
                'message' => 'Графикът е запазен успешно.',
                'exception' => [
                    'id' => $exception->getId(),
                    'date' => $exception->getDate()->format('Y-m-d'),
                    'is_available' => $exception->getIsAvailable(),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Грешка при запазване: '.$e->getMessage(),
            ], 500);
        }
    }
}
