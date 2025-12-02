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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function Symfony\Component\Clock\now;

class ClientController extends AbstractController
{
    private UserRepository $userRepository;
    private AppointmentsRepository $appointmentRepository;
    private ProcedureRepository $procedureRepository;
    private AppointmentValidator $appointmentValidator;
    private BarberProcedureRepository $barberProcedureRepository;
    private BarberScheduleService $scheduleService;
    private EntityManagerInterface $em;

    public function __construct(
        UserRepository $userRepository,
        AppointmentsRepository $appointmentRepository,
        ProcedureRepository $procedureRepository,
        AppointmentValidator $appointmentValidator,
        BarberProcedureRepository $barberProcedureRepository,
        BarberScheduleService $scheduleService,
        EntityManagerInterface $em
    ) {
        $this->userRepository = $userRepository;
        $this->appointmentRepository = $appointmentRepository;
        $this->procedureRepository = $procedureRepository;
        $this->appointmentValidator = $appointmentValidator;
        $this->barberProcedureRepository = $barberProcedureRepository;
        $this->scheduleService = $scheduleService;
        $this->em = $em;
    }

    /**
     * Display and handle appointment booking form
     */
    #[Route('/book-appointment/{id}', name: 'client_book_appointment', methods: ['GET', 'POST'])]
    public function bookAppointment(Request $request, int|string $id = ''): Response
    {
        $error = '';
        $client = $this->checkIfUserExistAndHasProfile($id);

        // Get all barbers initially (will be filtered by JS when procedure is selected)
        $barbers = $this->userRepository->getAllBarbersSortedBySeniority();
        $allAppointments = $this->appointmentRepository->getAllAppointments();
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
                return $this->redirectToRoute('client_book_appointment', ['id' => $client->getId()]);
            }

            $procedure = $this->procedureRepository->findOneProcedureById($procedureId);
            $barber = $this->userRepository->findOneById($barberId);

            // Validate entities exist
            if (!$procedure || !$barber) {
                $this->addFlash('error', 'Невалидна услуга или бръснар.');
                return $this->redirectToRoute('client_book_appointment', ['id' => $client->getId()]);
            }

            // Validate procedure is available
            if (!$procedure->getAvailable()) {
                $this->addFlash('error', 'Избраната услуга не е налична в момента.');
                return $this->redirectToRoute('client_book_appointment', ['id' => $client->getId()]);
            }

            // Validate barber can perform this procedure
            $canPerform = $this->barberProcedureRepository->canBarberPerformProcedure($barber, $procedure);
            if (!$canPerform) {
                $this->addFlash('error', 'Избраният бръснар не извършва тази услуга.');
                return $this->redirectToRoute('client_book_appointment', ['id' => $client->getId()]);
            }

            // Create appointment datetime
            try {
                $dateAppointment = new \DateTimeImmutable($appointmentStart.' '.$pickedHours);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Невалидна дата или час.');
                return $this->redirectToRoute('client_book_appointment', ['id' => $client->getId()]);
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
                return $this->redirectToRoute('client_book_appointment', ['id' => $client->getId()]);
            }

            // All validations passed - create appointment
            $appointment->setClient($client);
            $appointment->setBarber($barber);
            $appointment->setProcedureType($procedure);
            $appointment->setDate($dateAppointment);
            $appointment->setDateAdded();
            $appointment->setDuration($duration);
            $appointment->setStatus('confirmed');

            $this->appointmentRepository->save($appointment);

            $this->addFlash('success', 'Успешно запазихте час!');

            return $this->redirectToRoute('client_book_appointment',
                ['id' => $client->getId()]);
        }

        // Get selected date from query parameter, or use today as default
        $selectedDateStr = $request->query->get('date');
        if (!$selectedDateStr || !strtotime($selectedDateStr)) {
            $today = now();
            $selectedDateStr = $today->format('Y-m-d');
        }

        $selectedDate = new \DateTimeImmutable($selectedDateStr);

        // Get occupied slots for selected date
        $occupiedSlots = $this->appointmentRepository->getOccupiedSlotsByDate($selectedDateStr);

        // Get barber-procedure mapping and working hours for filtering
        $barberProcedureMap = [];
        $barberWorkingHours = [];

        foreach ($barbers as $barber) {
            $barberProcedures = $this->barberProcedureRepository->findActiveProceduresForBarber($barber);
            $barberProcedureMap[$barber->getId()] = array_map(fn($p) => $p->getId(), $barberProcedures);

            // Get working hours for selected date
            $workingHours = $this->scheduleService->getWorkingHoursForDate($barber, $selectedDate);

            if ($workingHours) {
                $barberWorkingHours[$barber->getId()] = $workingHours;
            }
        }

        return $this->render('client/book_appointment.html.twig',
            [
                'form' => $form,
                'user' => $client,
                'appointment' => $appointment,
                'error' => $error,
                'barbers' => $barbers,
                'procedures' => $procedures,
                'appointments' => $allAppointments,
                'today' => $selectedDateStr,
                'occupiedSlots' => $occupiedSlots,
                'barberProcedureMap' => $barberProcedureMap,
                'barberWorkingHours' => $barberWorkingHours,
            ]);
    }

    /**
     * Cancel an appointment
     */
    #[Route('/appointment/cancel/{id}', name: 'client_cancel_appointment', methods: ['POST'])]
    public function cancelAppointment(Request $request, int $id): Response
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
        $now = new \DateTimeImmutable('now');
        if ($appointment->getDate() <= $now) {
            $this->addFlash('error', 'Не можете да отменяте час, който вече е минал.');
            return $this->redirectToRoute('profile_edit', ['id' => $authUser->getId()]);
        }

        // Check if already cancelled
        if ($appointment->getStatus() === 'cancelled') {
            $this->addFlash('warning', 'Този час вече е отменен.');
            return $this->redirectToRoute('profile_edit', ['id' => $authUser->getId()]);
        }

        // Cancel appointment - this automatically releases the slot
        $appointment->setStatus('cancelled');
        $appointment->setDateCanceled(new \DateTimeImmutable('now'));
        $appointment->setCancellationReason('Отменен от клиент');

        $this->em->persist($appointment);
        $this->em->flush();

        $this->addFlash('success', 'Часът е успешно отменен.');

        return $this->redirectToRoute('profile_edit', ['id' => $authUser->getId()]);
    }

    /**
     * Reschedule an appointment (cancel and redirect to booking)
     */
    #[Route('/appointment/reschedule/{id}', name: 'client_reschedule_appointment', methods: ['GET'])]
    public function rescheduleAppointment(int $id): Response
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
        $now = new \DateTimeImmutable('now');
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
        $appointment->setDateCanceled(new \DateTimeImmutable('now'));
        $appointment->setCancellationReason('Отменен за промяна на час');
        $this->em->persist($appointment);
        $this->em->flush();

        // Redirect to booking page
        $this->addFlash('info', 'Изберете нов час за вашето посещение.');
        return $this->redirectToRoute('client_book_appointment', ['id' => $authUser->getId()]);
    }

    /**
     * Helper: Check if user exists and has completed profile
     */
    private function checkIfUserExistAndHasProfile(int $id): RedirectResponse|User
    {
        $user = $this->userRepository->findOneBy(['id' => $id], []);

        if (!$user) {
            throw $this->createNotFoundException('There is no user');
        }

        if (!$user->getFirstName()) {
            return $this->redirectToRoute('profile_edit', ['id' => $user->getId()]);
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
