<?php

namespace App\Controller;

use App\Repository\AppointmentsRepository;
use App\Repository\UserRepository;
use App\Service\AppointmentValidator;
use App\Service\BarberScheduleService;
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
        AppointmentValidator $appointmentValidator
    ) {
        $this->em = $em;
        $this->appointmentsRepository = $appointmentsRepository;
        $this->userRepository = $userRepository;
        $this->scheduleService = $scheduleService;
        $this->appointmentValidator = $appointmentValidator;
    }

    /**
     * Manager Dashboard - Overview with key metrics
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
            'confirmed' => count(array_filter($todayAppointments, fn($a) => $a->getStatus() === 'confirmed')),
            'completed' => count(array_filter($todayAppointments, fn($a) => $a->getStatus() === 'completed')),
            'cancelled' => count(array_filter($todayAppointments, fn($a) => $a->getStatus() === 'cancelled')),
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
     * List all appointments with filters, sorting, and pagination
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
            $appointments = array_filter($appointments, function($appointment) use ($searchTerm) {
                $client = $appointment->getClient();
                $fullName = mb_strtolower($client->getFirstName() . ' ' . $client->getLastName());
                $email = mb_strtolower($client->getEmail());
                return str_contains($fullName, $searchTerm) || str_contains($email, $searchTerm);
            });
        }

        // Sorting logic
        $validSortFields = ['id', 'date', 'date_added', 'date_last_update'];
        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'date';
        }
        $sortOrder = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        usort($appointments, function($a, $b) use ($sortBy, $sortOrder) {
            $valueA = match($sortBy) {
                'id' => $a->getId(),
                'date' => $a->getDate(),
                'date_added' => $a->getDateAdded(),
                'date_last_update' => $a->getDateLastUpdate(),
                default => $a->getDate(),
            };

            $valueB = match($sortBy) {
                'id' => $b->getId(),
                'date' => $b->getDate(),
                'date_added' => $b->getDateAdded(),
                'date_last_update' => $b->getDateLastUpdate(),
                default => $b->getDate(),
            };

            $comparison = $valueA <=> $valueB;
            return $sortOrder === 'asc' ? $comparison : -$comparison;
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
     * Get appointment details for edit modal (AJAX)
     */
    #[Route('/appointment/{id}/details', name: 'manager_appointment_details', methods: ['GET'])]
    public function getAppointmentDetails(int $id): Response
    {
        $appointment = $this->appointmentsRepository->find($id);

        if (!$appointment) {
            return $this->json(['error' => 'Часът не е намерен.'], 404);
        }

        return $this->json([
            'id' => $appointment->getId(),
            'date' => $appointment->getDate()->format('Y-m-d'),
            'time' => $appointment->getDate()->format('H:i'),
            'duration' => $appointment->getDuration(),
            'status' => $appointment->getStatus(),
            'client' => [
                'id' => $appointment->getClient()->getId(),
                'name' => $appointment->getClient()->getFirstName() . ' ' . $appointment->getClient()->getLastName(),
                'email' => $appointment->getClient()->getEmail(),
                'phone' => $appointment->getClient()->getPhone(),
            ],
            'barber' => [
                'id' => $appointment->getBarber()->getId(),
                'name' => $appointment->getBarber()->getFirstName() . ' ' . $appointment->getBarber()->getLastName(),
            ],
            'procedure' => [
                'id' => $appointment->getProcedureType()->getId(),
                'name' => $appointment->getProcedureType()->getType(),
            ],
            'notes' => $appointment->getNotes(),
        ]);
    }

    /**
     * Update appointment (AJAX) - Creates new appointment and cancels old one
     */
    #[Route('/appointment/{id}/update', name: 'manager_appointment_update', methods: ['POST'])]
    public function updateAppointment(int $id, Request $request): Response
    {
        $oldAppointment = $this->appointmentsRepository->find($id);

        if (!$oldAppointment) {
            return $this->json(['success' => false, 'error' => 'Часът не е намерен.'], 404);
        }

        // BLOCK EDITING OF PAST APPOINTMENTS - Check if ORIGINAL appointment is in the past
        $now = DateTimeHelper::now();
        if ($oldAppointment->getDate() < $now) {
            return $this->json([
                'success' => false,
                'error' => 'Не можете да променяте минали часове! Оригиналният час беше на ' .
                           $oldAppointment->getDate()->format('d.m.Y H:i') . ', който вече е изминал.'
            ], 400);
        }

        $data = json_decode($request->getContent(), true);

        // Validate required fields
        if (!isset($data['barber_id'], $data['date'], $data['time'], $data['procedure_id'])) {
            return $this->json(['success' => false, 'error' => 'Липсват задължителни полета.'], 400);
        }

        // Get entities
        $barber = $this->userRepository->find($data['barber_id']);
        $procedure = $this->em->getRepository(\App\Entity\Procedure::class)->find($data['procedure_id']);

        if (!$barber || !$procedure) {
            return $this->json(['success' => false, 'error' => 'Невалиден барбър или процедура.'], 400);
        }

        // Parse new date and time
        try {
            $newDateTime = new \DateTimeImmutable($data['date'] . ' ' . $data['time']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => 'Невалидна дата или час.'], 400);
        }

        // Prevent creating appointments in the past
        if ($newDateTime < $now) {
            return $this->json(['success' => false, 'error' => 'Не можете да запазите час в миналото.'], 400);
        }

        // Determine duration based on barber seniority
        $duration = $barber->isBarberSenior() || $barber->isBarber()
            ? $procedure->getDurationMaster()
            : $procedure->getDurationJunior();

        // Validate new appointment (exclude old appointment from conflict check)
        $errors = $this->appointmentValidator->validateAppointment(
            client: $oldAppointment->getClient(),
            barber: $barber,
            startTime: $newDateTime,
            duration: $duration,
            excludeAppointment: $oldAppointment
        );

        if (!empty($errors)) {
            return $this->json(['success' => false, 'error' => implode(' ', $errors)], 400);
        }

        // AUDIT TRAIL: Cancel old appointment and create new one
        // Step 1: Cancel the old appointment
        $oldAppointment->setStatus('cancelled');
        $oldAppointment->setDateCanceled(DateTimeHelper::now());
        $oldAppointment->setCancellationReason('Презаписан час от мениджър');
        $oldAppointment->setDateLastUpdate(DateTimeHelper::now());
        $this->em->persist($oldAppointment);

        // Step 2: Create NEW appointment with new details
        $newAppointment = new \App\Entity\Appointments();
        $newAppointment->setClient($oldAppointment->getClient());
        $newAppointment->setBarber($barber);
        $newAppointment->setProcedureType($procedure);
        $newAppointment->setDate($newDateTime);
        $newAppointment->setDuration($duration);
        $newAppointment->setStatus($data['status'] ?? 'confirmed');
        $newAppointment->setNotes($data['notes'] ?? 'Презаписан от час #' . $oldAppointment->getId());
        $newAppointment->setDateAdded(DateTimeHelper::now());
        $newAppointment->setDateLastUpdate(DateTimeHelper::now());

        $this->em->persist($newAppointment);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Часът е презаписан успешно! Старият час е отменен.',
            'new_appointment_id' => $newAppointment->getId(),
        ]);
    }

    /**
     * Cancel appointment (Manager side)
     */
    #[Route('/appointment/{id}/cancel', name: 'manager_appointment_cancel', methods: ['POST'])]
    public function cancelAppointment(int $id, Request $request): Response
    {
        $appointment = $this->appointmentsRepository->find($id);

        if (!$appointment) {
            return $this->json(['success' => false, 'error' => 'Часът не е намерен.'], 404);
        }

        if ($appointment->getStatus() === 'cancelled') {
            return $this->json(['success' => false, 'error' => 'Този час вече е отменен.'], 400);
        }

        $data = json_decode($request->getContent(), true);
        $reason = $data['reason'] ?? 'Отменен от мениджър';

        $appointment->setStatus('cancelled');
        $appointment->setDateCanceled(DateTimeHelper::now());
        $appointment->setCancellationReason($reason);
        $appointment->setDateLastUpdate(DateTimeHelper::now());

        $this->em->persist($appointment);
        $this->em->flush();

        // TODO: Send email notification to client

        return $this->json([
            'success' => true,
            'message' => 'Часът е отменен успешно!',
        ]);
    }

    /**
     * Get all available procedures (API endpoint for AJAX)
     */
    #[Route('/api/procedures', name: 'manager_api_procedures', methods: ['GET'])]
    public function getProcedures(): Response
    {
        $procedures = $this->em->getRepository(\App\Entity\Procedure::class)
            ->getAvailableProcedures();

        $data = array_map(function($proc) {
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
     * Get available time slots for a barber on a specific date (API endpoint)
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
            ]
        ]);
    }
}
