<?php

namespace App\Controller;

use App\Repository\AppointmentsRepository;
use App\Service\BarberScheduleService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/barber')]
class BarberController extends AbstractController
{
    private EntityManagerInterface $em;
    private AppointmentsRepository $appointmentsRepository;
    private BarberScheduleService $scheduleService;

    public function __construct(
        EntityManagerInterface $em,
        AppointmentsRepository $appointmentsRepository,
        BarberScheduleService $scheduleService
    ) {
        $this->em = $em;
        $this->appointmentsRepository = $appointmentsRepository;
        $this->scheduleService = $scheduleService;
    }

    /**
     * Barber calendar view - monthly schedule
     */
    #[Route('/calendar/{year}/{month}', name: 'barber_calendar', methods: ['GET'])]
    public function calendar(?int $year = null, ?int $month = null): Response
    {
        $authUser = parent::getUser();

        if (!$authUser) {
            $this->addFlash('error', 'Трябва да влезете в профила си.');
            return $this->redirectToRoute('app_login');
        }

        // Check if user is barber
        if (!$authUser->isBarber()) {
            $this->addFlash('error', 'Нямате достъп до този раздел.');
            return $this->redirectToRoute('main');
        }

        // Default to current month if not specified
        if (!$year || !$month) {
            $now = new \DateTime('now');
            $year = (int)$now->format('Y');
            $month = (int)$now->format('m');
        }

        // Get calendar data
        $calendar = $this->scheduleService->getMonthCalendar($authUser, $year, $month);

        // Calculate previous and next month
        $currentDate = new \DateTime("$year-$month-01");
        $prevMonth = (clone $currentDate)->modify('-1 month');
        $nextMonth = (clone $currentDate)->modify('+1 month');

        return $this->render('barber/calendar.html.twig', [
            'user' => $authUser,
            'calendar' => $calendar,
            'year' => $year,
            'month' => $month,
            'monthName' => $this->getMonthNameBg($month),
            'prevYear' => (int)$prevMonth->format('Y'),
            'prevMonth' => (int)$prevMonth->format('m'),
            'nextYear' => (int)$nextMonth->format('Y'),
            'nextMonth' => (int)$nextMonth->format('m'),
        ]);
    }

    /**
     * Get day schedule modal data (AJAX)
     */
    #[Route('/schedule/day/{date}', name: 'barber_schedule_day', methods: ['GET'])]
    public function getDaySchedule(string $date): Response
    {
        $authUser = parent::getUser();

        if (!$authUser || !$authUser->isBarber()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $dateObj = new \DateTimeImmutable($date);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid date format'], 400);
        }

        $daySchedule = $this->scheduleService->getDaySchedule($authUser, $dateObj);

        return $this->json([
            'date' => $date,
            'dayOfWeek' => $this->getDayNameBg((int)$dateObj->format('w')),
            'slots' => $daySchedule,
        ]);
    }

    /**
     * Save schedule exception (full day or specific slots)
     */
    #[Route('/schedule/save', name: 'barber_schedule_save', methods: ['POST'])]
    public function saveSchedule(Request $request): Response
    {
        $authUser = parent::getUser();

        if (!$authUser || !$authUser->isBarber()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['date'])) {
            return $this->json(['error' => 'Date is required'], 400);
        }

        try {
            $date = new \DateTimeImmutable($data['date']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid date format'], 400);
        }

        $isAvailable = $data['is_available'] ?? true;
        $startTime = $data['start_time'] ?? null;
        $endTime = $data['end_time'] ?? null;
        $excludedSlots = $data['excluded_slots'] ?? null;
        $reason = $data['reason'] ?? null;

        try {
            $exception = $this->scheduleService->saveException(
                $authUser,
                $date,
                $isAvailable,
                $startTime,
                $endTime,
                $excludedSlots,
                $reason,
                $authUser
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
                'error' => 'Грешка при запазване: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete appointment (mark as done) - Barber only
     */
    #[Route('/appointment/{id}/complete', name: 'barber_appointment_complete', methods: ['POST'])]
    public function completeAppointment(int $id): Response
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
        $appointment->setDateLastUpdate(new \DateTimeImmutable('now'));

        $this->em->persist($appointment);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Часът е отбележен като завършен!',
        ]);
    }

    /**
     * Cancel appointment - Barber side (notifies client)
     */
    #[Route('/appointment/{id}/cancel', name: 'barber_appointment_cancel', methods: ['POST'])]
    public function cancelAppointment(int $id): Response
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
        $now = new \DateTimeImmutable('now');
        if ($appointment->getDate() <= $now) {
            return $this->json(['success' => false, 'error' => 'Не можете да отменяте час, който вече е минал.'], 400);
        }

        // Cancel appointment - this automatically releases the slot
        $appointment->setStatus('cancelled');
        $appointment->setDateCanceled(new \DateTimeImmutable('now'));
        $appointment->setCancellationReason('Отменен от бръснар');
        $appointment->setDateLastUpdate(new \DateTimeImmutable('now'));

        $this->em->persist($appointment);
        $this->em->flush();

        // TODO: Send email notification to client about cancellation

        return $this->json([
            'success' => true,
            'message' => 'Часът е отменен успешно!',
        ]);
    }

    /**
     * Save barber's procedures (which procedures they can perform)
     */
    #[Route('/procedures/save', name: 'barber_procedures_save', methods: ['POST'])]
    public function saveProcedures(Request $request): Response
    {
        $authUser = parent::getUser();

        if (!$authUser || !$authUser->isBarber()) {
            $this->addFlash('error', 'Нямате достъп до този раздел.');
            return $this->redirectToRoute('main');
        }

        // Verify CSRF token
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('barber_procedures', $token)) {
            $this->addFlash('error', 'Невалиден CSRF токен.');
            $tab = $request->request->get('tab', 'calendar');
            return $this->redirectToRoute('profile_edit', ['id' => $authUser->getId(), 'tab' => $tab]);
        }

        $selectedProcedureIds = $request->request->all('procedures') ?? [];
        $barberProcedureRepo = $this->em->getRepository(\App\Entity\BarberProcedure::class);
        $procedureRepo = $this->em->getRepository(\App\Entity\Procedure::class);

        // Get all existing barber-procedure mappings
        $existingMappings = $barberProcedureRepo->createQueryBuilder('bp')
            ->where('bp.barber = :barber')
            ->setParameter('barber', $authUser)
            ->getQuery()
            ->getResult();

        // Create a map of existing mappings by procedure ID
        $existingMap = [];
        foreach ($existingMappings as $mapping) {
            $existingMap[$mapping->getProcedure()->getId()] = $mapping;
        }

        // Process selected procedures
        foreach ($selectedProcedureIds as $procedureId) {
            $procedureId = (int)$procedureId;
            $procedure = $procedureRepo->find($procedureId);

            if (!$procedure) {
                continue;
            }

            if (isset($existingMap[$procedureId])) {
                // Already exists, mark as still active
                $existingMap[$procedureId]->setCanPerform(true);
                $this->em->persist($existingMap[$procedureId]);
                unset($existingMap[$procedureId]);
            } else {
                // New mapping - create it
                $barberProcedure = new \App\Entity\BarberProcedure();
                $barberProcedure->setBarber($authUser);
                $barberProcedure->setProcedure($procedure);
                $barberProcedure->setCanPerform(true);
                // valid_from is set automatically in constructor
                $this->em->persist($barberProcedure);
            }
        }

        // Deactivate procedures that were not selected
        foreach ($existingMap as $mapping) {
            $mapping->setCanPerform(false);
            $this->em->persist($mapping);
        }

        $this->em->flush();

        $this->addFlash('success', 'Услугите са актуализирани успешно!');
        $tab = $request->request->get('tab', 'procedures');
        return $this->redirectToRoute('profile_edit', ['id' => $authUser->getId(), 'tab' => $tab]);
    }

    /**
     * Helper: Get Bulgarian month name
     */
    private function getMonthNameBg(int $month): string
    {
        $months = [
            1 => 'Януари', 2 => 'Февруари', 3 => 'Март', 4 => 'Април',
            5 => 'Май', 6 => 'Юни', 7 => 'Юли', 8 => 'Август',
            9 => 'Септември', 10 => 'Октомври', 11 => 'Ноември', 12 => 'Декември',
        ];

        return $months[$month] ?? '';
    }

    /**
     * Helper: Get Bulgarian day name
     */
    private function getDayNameBg(int $dayOfWeek): string
    {
        $days = [
            0 => 'Неделя', 1 => 'Понеделник', 2 => 'Вторник', 3 => 'Сряда',
            4 => 'Четвъртък', 5 => 'Петък', 6 => 'Събота',
        ];

        return $days[$dayOfWeek] ?? '';
    }
}
