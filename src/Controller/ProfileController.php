<?php

namespace App\Controller;

use App\Form\UserFormType;
use App\Repository\AppointmentsRepository;
use App\Repository\UserRepository;
use App\Service\BarberScheduleService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $em;
    private AppointmentsRepository $appointmentsRepository;
    private BarberScheduleService $scheduleService;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $em,
        AppointmentsRepository $appointmentsRepository,
        BarberScheduleService $scheduleService
    ) {
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->appointmentsRepository = $appointmentsRepository;
        $this->scheduleService = $scheduleService;
    }

    /**
     * Edit user profile - works for ALL roles (CLIENT, BARBER, MANAGER, ADMIN)
     * Renders different templates based on user role
     */
    #[Route('/profile/{id}', name: 'profile_edit')]
    public function edit(Request $request, int $id): Response
    {
        // Get target user
        $user = $this->userRepository->findOneById($id);

        $authUser = parent::getUser();

        $isAuthUserAdmin = $authUser ? $authUser->isUserIsAdmin() : false;
        $isAuthUserSuperAdmin = $authUser ? $authUser->isUserIsSuperAdmin() : false;

        if (!$user) {
            throw $this->createNotFoundException('There is no user');
        }

        // Security check - only owner or admin can edit
        if (!$authUser || ($authUser->getId() !== $user->getId() && !$isAuthUserAdmin)) {
            $this->addFlash('error', 'Нямате достъп до този профил.');
            return $this->redirectToRoute('main');
        }

        $form = $this->createForm(UserFormType::class, $user);
        try {
            $form->handleRequest($request);
        } catch (\Exception $e) {
            echo 'failed : '.$e->getMessage();
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $firstName = $form->get('first_name')->getData();
            $lastName = $form->get('last_name')->getData();
            $nickName = $form->get('nick_name')->getData();
            $phone = $form->get('phone')->getData();

            // If nickname is empty, use first_name
            if (empty($nickName)) {
                $nickName = $firstName;
            }

            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setNickName($nickName);
            $user->setPhone($phone);
            $user->setDateLastUpdate(new \DateTimeImmutable('now'));

            $this->em->persist($user);
            $this->em->flush();
            $this->em->clear();

            $this->addFlash('success', 'Профилът е обновен успешно!');

            return $this->redirectToRoute('profile_edit', ['id' => $user->getId()]);
        }

        // Get user's appointments (all - past, future, cancelled)
        $userAppointments = $this->userRepository->findAppointmentsByUserId($user->getId());

        // Initialize variables for BARBER-specific data
        $allProcedures = [];
        $barberProcedureIds = [];
        $barberAppointments = [];
        $calendar = [];
        $calendarYear = null;
        $calendarMonth = null;
        $calendarMonthName = '';
        $prevYear = null;
        $prevMonth = null;
        $nextYear = null;
        $nextMonth = null;

        // Load BARBER-specific data if user is barber
        if ($user->isBarber()) {
            // Get barber's upcoming appointments
            $barberAppointments = $this->appointmentsRepository->findUpcomingAppointmentsByBarber($user);
            $allProcedures = $this->em->getRepository(\App\Entity\Procedure::class)->findAll();
            $barberProcedures = $this->em->getRepository(\App\Entity\BarberProcedure::class)
                ->findActiveProceduresForBarber($user);
            $barberProcedureIds = array_map(fn($p) => $p->getId(), $barberProcedures);

            // Get calendar data - check for year/month in query params
            $calendarYear = $request->query->get('year');
            $calendarMonth = $request->query->get('month');

            if (!$calendarYear || !$calendarMonth) {
                $now = new \DateTime('now');
                $calendarYear = (int)$now->format('Y');
                $calendarMonth = (int)$now->format('m');
            } else {
                $calendarYear = (int)$calendarYear;
                $calendarMonth = (int)$calendarMonth;
            }

            $calendar = $this->scheduleService->getMonthCalendar($user, $calendarYear, $calendarMonth);

            // Calculate previous and next month
            $currentDate = new \DateTime("$calendarYear-$calendarMonth-01");
            $prevMonthDate = (clone $currentDate)->modify('-1 month');
            $nextMonthDate = (clone $currentDate)->modify('+1 month');

            $calendarMonthName = $this->getMonthNameBg($calendarMonth);
            $prevYear = (int)$prevMonthDate->format('Y');
            $prevMonth = (int)$prevMonthDate->format('m');
            $nextYear = (int)$nextMonthDate->format('Y');
            $nextMonth = (int)$nextMonthDate->format('m');
        }

        // Render different templates based on user type
        $template = $user->isBarber() ? 'barber/profile.html.twig' : 'client/profile.html.twig';

        return $this->render($template, [
            'user' => $user,
            'isAdmin' => $isAuthUserAdmin,
            'isSuperAdmin' => $isAuthUserSuperAdmin,
            'form' => $form->createView(),
            'userAppointments' => $userAppointments,
            'barberAppointments' => $barberAppointments,
            'allProcedures' => $allProcedures,
            'barberProcedureIds' => $barberProcedureIds,
            'calendar' => $calendar,
            'year' => $calendarYear,
            'month' => $calendarMonth,
            'monthName' => $calendarMonthName,
            'prevYear' => $prevYear,
            'prevMonth' => $prevMonth,
            'nextYear' => $nextYear,
            'nextMonth' => $nextMonth,
        ]);
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
}
