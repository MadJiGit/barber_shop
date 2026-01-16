<?php

namespace App\Controller;

use App\Entity\BarberProcedure;
use App\Entity\Procedure;
use App\Repository\AppointmentsRepository;
use App\Service\BarberScheduleService;
use App\Service\DateTimeHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/barber')]
class BarberController extends AbstractController
{
    private EntityManagerInterface $em;
    private AppointmentsRepository $appointmentsRepository;
    private BarberScheduleService $scheduleService;
    private TranslatorInterface $translator;

    public function __construct(
        EntityManagerInterface $em,
        AppointmentsRepository $appointmentsRepository,
        BarberScheduleService $scheduleService,
        TranslatorInterface $translator
    ) {
        $this->em = $em;
        $this->appointmentsRepository = $appointmentsRepository;
        $this->scheduleService = $scheduleService;
        $this->translator = $translator;
    }

    /**
     * Barber calendar view - monthly schedule
     */
    #[Route('/calendar/{year}/{month}', name: 'barber_calendar', methods: ['GET'])]
    public function calendar(?int $year = null, ?int $month = null): Response
    {
        $authUser = parent::getUser();

        if (!$authUser) {
            $this->addFlash('error', $this->translator->trans('barber.error.must_login', [], 'flash_messages'));
            return $this->redirectToRoute('app_login');
        }

        // Check if user is barber
        if (!$authUser->isBarber()) {
            $this->addFlash('error', $this->translator->trans('barber.error.no_access', [], 'flash_messages'));
            return $this->redirectToRoute('main');
        }

        // Default to current month if not specified
        if (!$year || !$month) {
            $now = DateTimeHelper::now();
            $year = (int)$now->format('Y');
            $month = (int)$now->format('m');
        }

        // Get calendar data
        $calendar = $this->scheduleService->getMonthCalendar($authUser, $year, $month);

        // Calculate previous and next month
        $currentDate = DateTimeHelper::createFromString("$year-$month-01");
        $prevMonth = $currentDate->modify('-1 month');
        $nextMonth = $currentDate->modify('+1 month');

        return $this->render('barber/calendar.html.twig', [
            'user' => $authUser,
            'calendar' => $calendar,
            'year' => $year,
            'month' => $month,
            'monthName' => $this->getMonthName($month),
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
            $dateObj = DateTimeHelper::createFromString($date);
        } catch (Exception $e) {
            return $this->json(['error' => 'Invalid date format'], 400);
        }

        $daySchedule = $this->scheduleService->getDaySchedule($authUser, $dateObj);

        return $this->json([
            'date' => $date,
            'dayOfWeek' => $this->getDayName((int)$dateObj->format('w')),
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
            $date = DateTimeHelper::createFromString($data['date']);
        } catch (Exception $e) {
            return $this->json(['error' => 'Invalid date format'], 400);
        }

        // Prevent editing past dates
        $today = DateTimeHelper::now()->setTime(0, 0, 0);
        if ($date < $today) {
            return $this->json([
                'success' => false,
                'error' => 'Не можете да променяте графика за минали дни.',
            ], 400);
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
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Грешка при запазване: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save barber's procedures (which procedures they can perform)
     */
    #[Route('/procedures/save', name: 'barber_procedures_save', methods: ['POST'])]
    public function saveProcedures(Request $request): Response
    {
        $authUser = parent::getUser();

        if (!$authUser || !$authUser->isBarber()) {
            $this->addFlash('error', $this->translator->trans('barber.error.no_access', [], 'flash_messages'));
            return $this->redirectToRoute('main');
        }

        // Verify CSRF token
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('barber_procedures', $token)) {
            $this->addFlash('error', $this->translator->trans('barber.error.invalid_csrf', [], 'flash_messages'));
            $tab = $request->request->get('tab', 'calendar');
            return $this->redirectToRoute('profile_edit', ['id' => $authUser->getId(), 'tab' => $tab]);
        }

        $selectedProcedureIds = $request->request->all('procedures') ?? [];
        $barberProcedureRepo = $this->em->getRepository(BarberProcedure::class);
        $procedureRepo = $this->em->getRepository(Procedure::class);

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
                $barberProcedure = new BarberProcedure();
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

        $this->addFlash('success', $this->translator->trans('barber.success.procedures_updated', [], 'flash_messages'));
        $tab = $request->request->get('tab', 'procedures');
        return $this->redirectToRoute('profile_edit', ['id' => $authUser->getId(), 'tab' => $tab]);
    }

    /**
     * Helper: Get localized month name
     */
    private function getMonthName(int $month): string
    {
        return $this->translator->trans("months.$month");
    }

    /**
     * Helper: Get localized day name
     */
    private function getDayName(int $dayOfWeek): string
    {
        return $this->translator->trans("days.$dayOfWeek");
    }
}
