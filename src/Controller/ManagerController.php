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
        $today = new \DateTimeImmutable('today');
        $startOfWeek = $today->modify('monday this week');
        $endOfWeek = $today->modify('sunday this week');
        $startOfMonth = new \DateTimeImmutable('first day of this month');

        // Today's appointments
        $todayAppointments = $this->appointmentsRepository->findBarberAppointments(
            barber: null,
            startDate: $today,
            endDate: $today->modify('+1 day')
        );

        // This week's appointments
        $weekAppointments = $this->appointmentsRepository->findBarberAppointments(
            barber: null,
            startDate: $startOfWeek,
            endDate: $endOfWeek->modify('+1 day')
        );

        // This month's appointments
        $monthAppointments = $this->appointmentsRepository->findBarberAppointments(
            barber: null,
            startDate: $startOfMonth,
            endDate: $startOfMonth->modify('+1 month')
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
     * List all appointments with filters
     */
    #[Route('/appointments', name: 'manager_appointments')]
    public function appointments(Request $request): Response
    {
        // Get filter parameters
        $filterDate = $request->query->get('date');
        $filterBarberId = $request->query->get('barber');
        $filterStatus = $request->query->get('status');
        $filterClientSearch = $request->query->get('client');

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

        // Sort by date (newest first)
        usort($appointments, fn($a, $b) => $b->getDate() <=> $a->getDate());

        // Get all barbers for filter dropdown
        $allBarbers = $this->userRepository->getAllBarbers();

        return $this->render('manager/appointments.html.twig', [
            'appointments' => $appointments,
            'allBarbers' => $allBarbers,
            'filterDate' => $filterDate,
            'filterBarberId' => $filterBarberId,
            'filterStatus' => $filterStatus,
            'filterClientSearch' => $filterClientSearch,
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
     * Update appointment (AJAX)
     */
    #[Route('/appointment/{id}/update', name: 'manager_appointment_update', methods: ['POST'])]
    public function updateAppointment(int $id, Request $request): Response
    {
        $appointment = $this->appointmentsRepository->find($id);

        if (!$appointment) {
            return $this->json(['success' => false, 'error' => 'Часът не е намерен.'], 404);
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

        // Determine duration based on barber seniority
        $duration = $barber->isBarberSenior() || $barber->isBarber()
            ? $procedure->getDurationMaster()
            : $procedure->getDurationJunior();

        // Validate appointment (exclude current appointment from conflict check)
        $errors = $this->appointmentValidator->validateAppointment(
            client: $appointment->getClient(),
            barber: $barber,
            startTime: $newDateTime,
            duration: $duration,
            excludeAppointment: $appointment
        );

        if (!empty($errors)) {
            return $this->json(['success' => false, 'error' => implode(' ', $errors)], 400);
        }

        // Update appointment
        $appointment->setBarber($barber);
        $appointment->setProcedureType($procedure);
        $appointment->setDate($newDateTime);
        $appointment->setDuration($duration);

        if (isset($data['status'])) {
            $appointment->setStatus($data['status']);
        }

        if (isset($data['notes'])) {
            $appointment->setNotes($data['notes']);
        }

        $appointment->setDateLastUpdate(new \DateTimeImmutable('now'));

        $this->em->persist($appointment);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Часът е обновен успешно!',
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
        $appointment->setDateCanceled(new \DateTimeImmutable('now'));
        $appointment->setCancellationReason($reason);
        $appointment->setDateLastUpdate(new \DateTimeImmutable('now'));

        $this->em->persist($appointment);
        $this->em->flush();

        // TODO: Send email notification to client

        return $this->json([
            'success' => true,
            'message' => 'Часът е отменен успешно!',
        ]);
    }
}
