<?php

namespace App\Controller;

use App\Entity\Appointments;
use App\Entity\Procedure;
use App\Entity\User;
use App\Form\AppointmentFormType;
use App\Repository\AppointmentsRepository;
use App\Repository\BarberProcedureRepository;
use App\Repository\ProcedureRepository;
use App\Repository\UserRepository;
use App\Service\AppointmentValidator;
use App\Service\BarberScheduleService;
use App\Service\DateTimeHelper;
use App\Service\EmailService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Centralized Appointment Controller
 * Handles all appointment operations: book, cancel, reschedule, complete, update status
 */
#[Route('/appointment')]
class AppointmentController extends AbstractController
{
    private EntityManagerInterface $em;
    private AppointmentsRepository $appointmentsRepository;
    private UserRepository $userRepository;
    private AppointmentValidator $appointmentValidator;
    private ProcedureRepository $procedureRepository;
    private BarberProcedureRepository $barberProcedureRepository;
    private BarberScheduleService $scheduleService;
    private EmailService $emailService;

    public function __construct(
        EntityManagerInterface $em,
        AppointmentsRepository $appointmentsRepository,
        UserRepository $userRepository,
        AppointmentValidator $appointmentValidator,
        ProcedureRepository $procedureRepository,
        BarberProcedureRepository $barberProcedureRepository,
        BarberScheduleService $scheduleService,
        EmailService $emailService
    ) {
        $this->em = $em;
        $this->appointmentsRepository = $appointmentsRepository;
        $this->userRepository = $userRepository;
        $this->appointmentValidator = $appointmentValidator;
        $this->procedureRepository = $procedureRepository;
        $this->barberProcedureRepository = $barberProcedureRepository;
        $this->scheduleService = $scheduleService;
        $this->emailService = $emailService;
    }

    // ========================================
    // CLIENT OPERATIONS
    // ========================================

    /**
     * Book new appointment (Client)
     * GET: Show booking form
     * POST: Create appointment
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    #[Route('/book/{id}', name: 'appointment_book', methods: ['GET', 'POST'])]
    public function book(Request $request, int|string $id = ''): Response
    {
        $error = '';
        $client = $this->checkIfUserExistAndHasProfile($id);

        // Get all barbers initially (will be filtered by JS when procedure is selected)
        $barbers = $this->userRepository->getAllBarbersSortedBySeniority();
        $allAppointments = $this->appointmentsRepository->getAllAppointments();
        // Get only available procedures for booking
        $procedures = $this->procedureRepository->getAvailableProcedures();

        $appointment = new Appointments();

        $form = $this->createForm(AppointmentFormType::class, $appointment);

        // Handle POST request directly (Symfony form used only for CSRF token)
        if ($request->isMethod('POST')) {
            // Get data from request

            $procedureId = $request->request->get('procedures');
            $barberId = $request->request->get('barbers');
            $appointmentStart = $request->request->get('appointment_start');
            $pickedHours = $request->request->get('pickedHours');

            // Validate required fields
            if (!$procedureId || !$barberId || !$appointmentStart || !$pickedHours) {
                $this->addFlash('error', 'Моля, попълнете всички полета.');
                return $this->redirectToRoute('appointment_book', ['id' => $client->getId()]);
            }

            $procedure = $this->procedureRepository->findOneProcedureById($procedureId);
            $barber = $this->userRepository->findOneById($barberId);

            // Validate entities exist
            if (!$procedure || !$barber) {
                $this->addFlash('error', 'Невалидна услуга или бръснар.');
                return $this->redirectToRoute('appointment_book', ['id' => $client->getId()]);
            }

            // Validate procedure is available
            if (!$procedure->getAvailable()) {
                $this->addFlash('error', 'Избраната услуга не е налична в момента.');
                return $this->redirectToRoute('appointment_book', ['id' => $client->getId()]);
            }

            // Validate barber can perform this procedure
            $canPerform = $this->barberProcedureRepository->canBarberPerformProcedure($barber, $procedure);
            if (!$canPerform) {
                $this->addFlash('error', 'Избраният бръснар не извършва тази услуга.');
                return $this->redirectToRoute('appointment_book', ['id' => $client->getId()]);
            }

            // Create appointment datetime
            try {
                $dateAppointment = new DateTimeImmutable($appointmentStart.' '.$pickedHours);
            } catch (Exception $e) {
                $this->addFlash('error', 'Невалидна дата или час.');
                return $this->redirectToRoute('appointment_book', ['id' => $client->getId()]);
            }

            $duration = $this->getDurationOfProcedure($procedure, $barber);

            // Validate appointment using AppointmentValidator
            $validationErrors = $this->appointmentValidator->validateAppointment(
                $client,
                $barber,
                $dateAppointment,
                $duration
            );

            if (!empty($validationErrors)) {
                foreach ($validationErrors as $validationError) {
                    $this->addFlash('error', $validationError);
                }
                return $this->redirectToRoute('appointment_book', ['id' => $client->getId()]);
            }

            // All validations passed - create appointment
            $appointment->setClient($client);
            $appointment->setBarber($barber);
            $appointment->setProcedureType($procedure);
            $appointment->setDate($dateAppointment);
            $appointment->setDateAdded();
            $appointment->setDuration($duration);
            $appointment->setStatus('confirmed');

            $this->appointmentsRepository->save($appointment);

            // Send confirmation email to client
            $this->emailService->sendAppointmentConfirmation($appointment);

            $this->addFlash('success', 'Успешно запазихте час!');

            return $this->redirectToRoute('appointment_book',
                ['id' => $client->getId()]);
        }

        // Get actual today (always from server time)
        $today = DateTimeHelper::now();
        $todayStr = $today->format('Y-m-d');

        // Prepare barber data for JavaScript
        $barbersData = [];
        $barberProcedureMap = [];

        foreach ($barbers as $barber) {
            // Serialize barber for JavaScript
            $barbersData[] = [
                'id' => $barber->getId(),
                'firstName' => $barber->getFirstName(),
                'lastName' => $barber->getLastName(),
                'barberTitleBg' => $barber->getBarberTitleBg(),
                'isBarberJunior' => $barber->isBarberJunior(),
            ];

            // Get procedure mapping
            $barberProcedures = $this->barberProcedureRepository->findActiveProceduresForBarber($barber);
            $barberProcedureMap[$barber->getId()] = array_map(fn($p) => $p->getId(), $barberProcedures);
        }

        return $this->render('client/appointment.html.twig',
            [
                'form' => $form,
                'user' => $client,
                'appointment' => $appointment,
                'error' => $error,
                'barbers' => $barbers,
                'barbersData' => $barbersData,
                'procedures' => $procedures,
                'appointments' => $allAppointments,
                'today' => $todayStr,
                'barberProcedureMap' => $barberProcedureMap,
            ]);
    }

    /**
     * Cancel appointment (Client)
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    #[Route('/{id}/client-cancel', name: 'appointment_client_cancel', methods: ['POST'])]
    public function clientCancel(Request $request, int $id): Response
    {
        // Verify CSRF token
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('cancel_appointment', $token)) {
            $this->addFlash('error', 'Невалиден CSRF токен.');
            return $this->redirectToRoute('main');
        }

        // Get appointment
        $appointment = $this->em->getRepository(Appointments::class)->find($id);

        if (!$appointment) {
            $this->addFlash('error', 'Часът не е намерен.');
            return $this->redirectToRoute('main');
        }

        // Verify user owns this appointment
        $authUser = parent::getUser();
        if (!$authUser || $appointment->getClient()->getId() !== $authUser->getId()) {
            $this->addFlash('error', 'Нямате право да отменяте този час.');
            return $this->redirectToRoute('main');
        }

        // Check if appointment is in the future
        $now = DateTimeHelper::now();
        if ($appointment->getDate() <= $now) {
            $this->addFlash('error', 'Не можете да отменяте час, който вече е минал.');
            $tab = $request->request->get('tab', 'profile');
            return $this->redirectToRoute('profile_edit', ['id' => $authUser->getId(), 'tab' => $tab]);
        }

        // Check if already cancelled
        if ($appointment->getStatus() === 'cancelled') {
            $this->addFlash('warning', 'Този час вече е отменен.');
            $tab = $request->request->get('tab', 'profile');
            return $this->redirectToRoute('profile_edit', ['id' => $authUser->getId(), 'tab' => $tab]);
        }

        // Cancel appointment - this automatically releases the slot
        $appointment->setStatus('cancelled');
        $appointment->setDateCanceled(DateTimeHelper::now());
        $appointment->setCancellationReason('Отменен от клиент');

        $this->em->persist($appointment);
        $this->em->flush();

        // Send cancellation email notification
        $this->emailService->sendAppointmentCancellation($appointment, 'client');

        $this->addFlash('success', 'Часът е успешно отменен.');

        $tab = $request->request->get('tab', 'profile');
        return $this->redirectToRoute('profile_edit', ['id' => $authUser->getId(), 'tab' => $tab]);
    }

    /**
     * Reschedule appointment (Client)
     * @throws Exception
     */
    #[Route('/{id}/reschedule', name: 'appointment_reschedule', methods: ['GET'])]
    public function reschedule(int $id): Response
    {
        // Get appointment
        $appointment = $this->em->getRepository(Appointments::class)->find($id);

        if (!$appointment) {
            $this->addFlash('error', 'Часът не е намерен.');
            return $this->redirectToRoute('main');
        }

        // Verify user owns this appointment
        $authUser = parent::getUser();
        if (!$authUser || $appointment->getClient()->getId() !== $authUser->getId()) {
            $this->addFlash('error', 'Нямате право да променяте този час.');
            return $this->redirectToRoute('main');
        }

        // Check if appointment is in the future
        $now = DateTimeHelper::now();
        if ($appointment->getDate() <= $now) {
            $this->addFlash('error', 'Не можете да променяте час, който вече е минал.');
            return $this->redirectToRoute('profile_edit', ['id' => $authUser->getId()]);
        }

        // Check if already cancelled
        if ($appointment->getStatus() === 'cancelled') {
            $this->addFlash('warning', 'Не можете да променяте отменен час.');
            return $this->redirectToRoute('profile_edit', ['id' => $authUser->getId()]);
        }

        // Cancel old appointment first
        $appointment->setStatus('cancelled');
        $appointment->setDateCanceled(DateTimeHelper::now());
        $appointment->setCancellationReason('Отменен за промяна на час');
        $this->em->persist($appointment);
        $this->em->flush();

        // Send cancellation email notification
        $this->emailService->sendAppointmentCancellation($appointment, 'client');

        // Redirect to booking page
        $this->addFlash('info', 'Изберете нов час за вашето посещение.');
        return $this->redirectToRoute('appointment_book', ['id' => $authUser->getId()]);
    }

    // ========================================
    // BARBER OPERATIONS
    // ========================================

    /**
     * Complete appointment (Barber)
     * Mark appointment as completed
     * @throws Exception
     */
    #[Route('/{id}/complete', name: 'appointment_complete', methods: ['POST'])]
    public function complete(int $id): Response
    {
        $authUser = parent::getUser();

        if (!$authUser || !$authUser->isBarber()) {
            return $this->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        // Get appointment
        $appointment = $this->appointmentsRepository->find($id);

        if (!$appointment) {
            return $this->json(['success' => false, 'error' => 'Часът не е намерен.'], 404);
        }

        // Verify barber owns this appointment
        if ($appointment->getBarber()->getId() !== $authUser->getId()) {
            return $this->json(['success' => false, 'error' => 'Нямате право да променяте този час.'], 403);
        }

        // Check if already completed or cancelled
        if ($appointment->getStatus() === 'completed') {
            return $this->json(['success' => false, 'error' => 'Този час вече е отбележен като завършен.'], 400);
        }

        if ($appointment->getStatus() === 'cancelled') {
            return $this->json(['success' => false, 'error' => 'Не можете да завършите отменен час.'], 400);
        }

        // Mark as completed
        $appointment->setStatus('completed');
        $appointment->setDateLastUpdate(DateTimeHelper::now());

        $this->em->persist($appointment);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Часът е отбележен като завършен!',
        ]);
    }

    /**
     * Cancel appointment - Barber side (notifies client)
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    #[Route('/{id}/barber-cancel', name: 'appointment_barber_cancel', methods: ['POST'])]
    public function barberCancel(int $id): Response
    {
        $authUser = parent::getUser();

        if (!$authUser || !$authUser->isBarber()) {
            return $this->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        // Get appointment
        $appointment = $this->appointmentsRepository->find($id);

        if (!$appointment) {
            return $this->json(['success' => false, 'error' => 'Часът не е намерен.'], 404);
        }

        // Verify barber owns this appointment
        if ($appointment->getBarber()->getId() !== $authUser->getId()) {
            return $this->json(['success' => false, 'error' => 'Нямате право да отменяте този час.'], 403);
        }

        // Check if already cancelled
        if ($appointment->getStatus() === 'cancelled') {
            return $this->json(['success' => false, 'error' => 'Този час вече е отменен.'], 400);
        }

        // Check if appointment is in the future
        $now = DateTimeHelper::now();
        if ($appointment->getDate() <= $now) {
            return $this->json(['success' => false, 'error' => 'Не можете да отменяте час, който вече е минал.'], 400);
        }

        // Cancel appointment - this automatically releases the slot
        $appointment->setStatus('cancelled');
        $appointment->setDateCanceled(DateTimeHelper::now());
        $appointment->setCancellationReason('Отменен от бръснар');
        $appointment->setDateLastUpdate(DateTimeHelper::now());

        $this->em->persist($appointment);
        $this->em->flush();

        // Send email notification to client about cancellation
        $this->emailService->sendAppointmentCancellation($appointment, 'barber');

        return $this->json([
            'success' => true,
            'message' => 'Часът е отменен успешно!',
        ]);
    }


    // ========================================
    // MANAGER OPERATIONS
    // ========================================

    /**
     * Get appointment details (Manager/Admin)
     * Used by AJAX modal
     */
    #[Route('/{id}/details', name: 'appointment_details', methods: ['GET'])]
    public function getDetails(int $id): Response
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
     * Update appointment (Manager/Admin)
     * For future appointments - can change date/time/barber/procedure
     * @throws Exception
     */
    #[Route('/{id}/update', name: 'appointment_update', methods: ['POST'])]
    public function update(int $id, Request $request): Response
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
        $procedure = $this->em->getRepository(Procedure::class)->find($data['procedure_id']);

        if (!$barber || !$procedure) {
            return $this->json(['success' => false, 'error' => 'Невалиден барбър или процедура.'], 400);
        }

        // Parse new date and time
        try {
            $newDateTime = new DateTimeImmutable($data['date'] . ' ' . $data['time']);
        } catch (Exception $e) {
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
        $newAppointment = new Appointments();
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
     * Update appointment status (Manager/Admin)
     * For past appointments - can only change status and notes
     * @throws Exception
     */
    #[Route('/{id}/update-status', name: 'appointment_update_status', methods: ['POST'])]
    public function updateStatus(int $id, Request $request): Response
    {
        $appointment = $this->appointmentsRepository->find($id);

        if (!$appointment) {
            return $this->json(['success' => false, 'error' => 'Часът не е намерен.'], 404);
        }

        // This endpoint is ONLY for past appointments
        $now = DateTimeHelper::now();
        if ($appointment->getDate() >= $now) {
            return $this->json([
                'success' => false,
                'error' => 'Този endpoint е само за минали часове. Използвайте update за бъдещи часове.'
            ], 400);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['status'])) {
            return $this->json(['success' => false, 'error' => 'Липсва статус.'], 400);
        }

        $newStatus = $data['status'];

        // Only allow completed or no_show for past appointments
        if (!in_array($newStatus, ['completed', 'no_show', 'cancelled'])) {
            return $this->json([
                'success' => false,
                'error' => 'Невалиден статус. Разрешени са само: completed, no_show, cancelled.'
            ], 400);
        }

        // Update status
        $appointment->setStatus($newStatus);
        $appointment->setDateLastUpdate(DateTimeHelper::now());

        // Add notes if provided
        if (isset($data['notes'])) {
            $appointment->setNotes($data['notes']);
        }

        $this->em->persist($appointment);
        $this->em->flush();

        $statusLabels = [
            'completed' => 'завършен',
            'no_show' => 'пропуснат',
            'cancelled' => 'отменен',
        ];

        return $this->json([
            'success' => true,
            'message' => 'Статусът е променен на "' . ($statusLabels[$newStatus] ?? $newStatus) . '".',
        ]);
    }

    /**
     * Cancel appointment (Manager side)
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    #[Route('/{id}/manager-cancel', name: 'appointment_manager_cancel', methods: ['POST'])]
    public function managerCancel(int $id, Request $request): Response
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

        // Send email notification to client
        $this->emailService->sendAppointmentCancellation($appointment, 'manager');

        return $this->json([
            'success' => true,
            'message' => 'Часът е отменен успешно!',
        ]);
    }

    // ========================================
    // API ENDPOINTS
    // ========================================

    /**
     * Get availability data for a specific date (AJAX)
     * Returns occupied slots and barber working hours
     * @throws Exception
     */
    #[Route('/api/availability/{date}', name: 'api_availability', methods: ['GET'])]
    public function getAvailability(string $date): Response
    {
        try {
            $selectedDate = new DateTimeImmutable($date);
        } catch (Exception $e) {
            return $this->json(['error' => 'Invalid date format'], 400);
        }

        // Get occupied slots for this date
        $occupiedSlots = $this->appointmentsRepository->getOccupiedSlotsByDate($date);

        // Get all barbers
        $barbers = $this->userRepository->getAllBarbersSortedBySeniority();

        // Get working hours for each barber on this date
        $barberWorkingHours = [];
        foreach ($barbers as $barber) {
            $workingHours = $this->scheduleService->getWorkingHoursForDate($barber, $selectedDate);
            if ($workingHours) {
                $barberWorkingHours[$barber->getId()] = $workingHours;
            }
        }

        return $this->json([
            'occupiedSlots' => $occupiedSlots,
            'barberWorkingHours' => $barberWorkingHours,
        ]);
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Helper: Check if user exists
     */
    private function checkIfUserExistAndHasProfile(int $id): User
    {
        $user = $this->userRepository->findOneBy(['id' => $id], []);

        if (!$user) {
            throw $this->createNotFoundException('There is no user');
        }

        return $user;
    }

    /**
     * Helper: Get procedure duration based on barber seniority
     */
    private function getDurationOfProcedure(Procedure $procedure, User $barber): int|bool
    {
        $roles = array_values($barber->getRoles());
        if (in_array('ROLE_BARBER_JUNIOR', $roles)) {
            return $procedure->getDurationJunior();
        }

        if (in_array('ROLE_BARBER_SENIOR', $roles) || in_array('ROLE_BARBER', $roles)) {
            return $procedure->getDurationMaster();
        }

        return false;
    }
}
