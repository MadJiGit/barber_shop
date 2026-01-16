<?php

namespace App\Controller;

use App\Entity\BarberProcedure;
use App\Entity\Procedure;
use App\Form\UserFormType;
use App\Repository\AppointmentsRepository;
use App\Repository\UserRepository;
use App\Service\BarberScheduleService;
use App\Service\DateTimeHelper;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfileController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $em;
    private AppointmentsRepository $appointmentsRepository;
    private BarberScheduleService $scheduleService;
    private Security $security;
    private UserPasswordHasherInterface $passwordHasher;
    private EmailService $emailService;
    private TranslatorInterface $translator;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $em,
        AppointmentsRepository $appointmentsRepository,
        BarberScheduleService $scheduleService,
        Security $security,
        UserPasswordHasherInterface $passwordHasher,
        EmailService $emailService,
        TranslatorInterface $translator,
    ) {
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->appointmentsRepository = $appointmentsRepository;
        $this->scheduleService = $scheduleService;
        $this->security = $security;
        $this->passwordHasher = $passwordHasher;
        $this->emailService = $emailService;
        $this->translator = $translator;
    }

    /**
     * Edit user profile - works for ALL roles (CLIENT, BARBER, MANAGER, ADMIN)
     * Renders different templates based on user role.
     *
     * @throws \Exception
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
            $this->addFlash('error', $this->translator->trans('profile.error.no_access', [], 'flash_messages'));
            return $this->redirectToRoute('main');
        }

        $form = $this->createForm(UserFormType::class, $user);
        try {
            $form->handleRequest($request);
        } catch (\Exception $e) {
            echo 'failed : '.$e->getMessage();
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if user wants to change password
            $currentPassword = $form->get('current_password')->getData();
            $newPassword = $form->get('new_password')->getData();
            $newPasswordRepeat = $form->get('new_password_repeat')->getData();

            $passwordChangeRequested = !empty($currentPassword) || !empty($newPassword) || !empty($newPasswordRepeat);

            if ($passwordChangeRequested) {
                // Validate password change fields
                if (empty($currentPassword)) {
                    $this->addFlash('error', $this->translator->trans('profile.error.enter_current_password', [], 'flash_messages'));
                    $tab = $request->query->get('tab', 'profile');

                    return $this->redirect($this->generateUrl('profile_edit', ['id' => $user->getId()]).'?tab='.$tab);
                }

                if (empty($newPassword) || empty($newPasswordRepeat)) {
                    $this->addFlash('error', $this->translator->trans('profile.error.enter_new_password_twice', [], 'flash_messages'));
                    $tab = $request->query->get('tab', 'profile');

                    return $this->redirect($this->generateUrl('profile_edit', ['id' => $user->getId()]).'?tab='.$tab);
                }

                if ($newPassword !== $newPasswordRepeat) {
                    $this->addFlash('error', $this->translator->trans('profile.error.enter_new_password_twice', [], 'flash_messages'));
                    $tab = $request->query->get('tab', 'profile');

                    return $this->redirect($this->generateUrl('profile_edit', ['id' => $user->getId()]).'?tab='.$tab);
                }

                if (strlen($newPassword) < 6) {
                    $this->addFlash('error', $this->translator->trans('profile.error.password_min_length', [], 'flash_messages'));
                    $tab = $request->query->get('tab', 'profile');

                    return $this->redirect($this->generateUrl('profile_edit', ['id' => $user->getId()]).'?tab='.$tab);
                }

                // Verify current password
                if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
                    $this->addFlash('error', $this->translator->trans('profile.error.current_password_wrong', [], 'flash_messages'));
                    $tab = $request->query->get('tab', 'profile');

                    return $this->redirect($this->generateUrl('profile_edit', ['id' => $user->getId()]).'?tab='.$tab);
                }

                // Generate token for password change confirmation
                $changeToken = bin2hex(random_bytes(32));
                $user->setConfirmationToken($changeToken);

                // Set token expiration to 1 hour from now (using Sofia timezone)
                $expiresAt = DateTimeHelper::now()->modify('+1 hour');
                $user->setTokenExpiresAt($expiresAt);

                // Hash the new password and store it temporarily in a session or temp field
                // We'll store the hashed password in the confirmation_token field temporarily
                // Actually, we need a better approach - let's store it in session
                $newPasswordHashed = $this->passwordHasher->hashPassword($user, $newPassword);
                $request->getSession()->set('pending_password_change_'.$user->getId(), $newPasswordHashed);

                $user->setDateLastUpdate(DateTimeHelper::now());

                try {
                    $this->em->flush();
                } catch (\Exception $e) {
                    $this->addFlash('error', $this->translator->trans('profile.error.error_occurred', ['%error%' => $e->getMessage()], 'flash_messages'));

                    return $this->redirectToRoute('profile_edit', ['id' => $user->getId()]);
                }

                // Send confirmation email
                $this->emailService->sendPasswordChangeConfirmation($user, $changeToken, $newPasswordHashed);
                $this->addFlash('success', $this->translator->trans('profile.success.password_change_email_sent', [], 'flash_messages'));
                $tab = $request->query->get('tab', 'profile');

                return $this->redirect($this->generateUrl('profile_edit', ['id' => $user->getId()]).'?tab='.$tab);
            }

            // Normal profile update (no password change)
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
            $user->setDateLastUpdate(DateTimeHelper::now());

            try {
                $this->em->flush();
            } catch (\Exception $e) {
                $this->addFlash('error', $this->translator->trans('profile.error.error_updating_profile', [], 'flash_messages'));

                return $this->redirectToRoute('profile_edit', ['id' => $user->getId()]);
            }

            $this->addFlash('success', $this->translator->trans('profile.success.profile_updated', [], 'flash_messages'));

            // Preserve the active tab (from query string)
            $tab = $request->query->get('tab', 'profile');

            return $this->redirect($this->generateUrl('profile_edit', ['id' => $user->getId()]).'?tab='.$tab);
        }

        // Initialize variables
        $allProcedures = [];
        $barberProcedureIds = [];
        $barberAppointments = [];
        $clientAppointments = [];
        $appointmentsPagination = null;
        $filters = [];
        $calendar = [];
        $calendarYear = null;
        $calendarMonth = null;
        $calendarMonthName = '';
        $prevYear = null;
        $prevMonth = null;
        $nextYear = null;
        $nextMonth = null;

        // Get session for filter persistence
        $session = $request->getSession();

        // Load personal client appointments for ALL users (including barbers)
        // These are the appointments where THIS user is the CLIENT
        $clientDateFrom = $request->query->get('client_date_from', $session->get('client_filter_date_from', null));
        $clientDateTo = $request->query->get('client_date_to', $session->get('client_filter_date_to', null));
        $clientStatuses = $request->query->all('client_statuses') ?: $session->get('client_filter_statuses', ['confirmed', 'pending', 'completed', 'cancelled']);
        $clientPage = max(1, (int) $request->query->get('client_page', 1));
        $clientLimit = (int) $request->query->get('client_limit', $session->get('client_filter_limit', 20));

        // Save filters to session for persistence
        $session->set('client_filter_date_from', $clientDateFrom);
        $session->set('client_filter_date_to', $clientDateTo);
        $session->set('client_filter_statuses', $clientStatuses);
        $session->set('client_filter_limit', $clientLimit);

        // Convert date strings to DateTimeImmutable
        try {
            $clientDateFromObj = $clientDateFrom ? new \DateTimeImmutable($clientDateFrom.' 00:00:00') : null;
            $clientDateToObj = $clientDateTo ? new \DateTimeImmutable($clientDateTo.' 23:59:59') : null;
        } catch (\Exception $e) {
            $clientDateFromObj = null;
            $clientDateToObj = null;
        }

        // Get personal appointments where this user is the CLIENT
        $clientAppointmentsData = $this->appointmentsRepository->findAppointmentsWithFilters(
            user: $user,
            userType: 'client',
            dateFrom: $clientDateFromObj,
            dateTo: $clientDateToObj,
            statuses: $clientStatuses,
            searchTerm: null,
            page: $clientPage,
            limit: $clientLimit
        );

        $clientAppointments = $clientAppointmentsData['items'];
        $clientAppointmentsPagination = [
            'total' => $clientAppointmentsData['total'],
            'page' => $clientAppointmentsData['page'],
            'limit' => $clientAppointmentsData['limit'],
            'totalPages' => $clientAppointmentsData['totalPages'],
        ];

        $clientFilters = [
            'date_from' => $clientDateFrom,
            'date_to' => $clientDateTo,
            'statuses' => $clientStatuses,
            'limit' => $clientLimit,
        ];

        // Load BARBER-specific data if user is barber
        if ($user->isBarber()) {
            // Appointments filters (for "appointments" tab)
            $dateFrom = $request->query->get('date_from', $session->get('barber_filter_date_from', date('Y-m-d')));
            $dateTo = $request->query->get('date_to', $session->get('barber_filter_date_to', date('Y-m-d')));
            $statuses = $request->query->all('statuses') ?: $session->get('barber_filter_statuses', ['confirmed', 'pending']);
            $searchTerm = $request->query->get('search', $session->get('barber_filter_search', ''));
            $page = max(1, (int) $request->query->get('page', 1));
            $limit = (int) $request->query->get('limit', $session->get('barber_filter_limit', 20));

            // Save filters to session for persistence
            $session->set('barber_filter_date_from', $dateFrom);
            $session->set('barber_filter_date_to', $dateTo);
            $session->set('barber_filter_statuses', $statuses);
            $session->set('barber_filter_search', $searchTerm);
            $session->set('barber_filter_limit', $limit);

            // Convert date strings to DateTimeImmutable
            try {
                $dateFromObj = $dateFrom ? new \DateTimeImmutable($dateFrom.' 00:00:00') : null;
                $dateToObj = $dateTo ? new \DateTimeImmutable($dateTo.' 23:59:59') : null;
            } catch (\Exception $e) {
                $dateFromObj = null;
                $dateToObj = null;
            }

            // Get filtered appointments for "appointments" tab
            $appointmentsData = $this->appointmentsRepository->findAppointmentsWithFilters(
                user: $user,
                userType: 'barber',
                dateFrom: $dateFromObj,
                dateTo: $dateToObj,
                statuses: $statuses,
                searchTerm: $searchTerm,
                page: $page,
                limit: $limit
            );

            $barberAppointments = $appointmentsData['items'];
            $appointmentsPagination = [
                'total' => $appointmentsData['total'],
                'page' => $appointmentsData['page'],
                'limit' => $appointmentsData['limit'],
                'totalPages' => $appointmentsData['totalPages'],
            ];

            $filters = [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'statuses' => $statuses,
                'search' => $searchTerm,
                'limit' => $limit,
            ];

            // Get only available (manager-approved) procedures
            $allProcedures = $this->em->getRepository(Procedure::class)->getAvailableProcedures();
            $barberProcedures = $this->em->getRepository(BarberProcedure::class)
                ->findActiveProceduresForBarber($user);
            $barberProcedureIds = array_map(fn ($p) => $p->getId(), $barberProcedures);

            // Get calendar data - check for year/month in query params
            $calendarYear = $request->query->get('year');
            $calendarMonth = $request->query->get('month');

            if (!$calendarYear || !$calendarMonth) {
                $now = new \DateTime('now');
                $calendarYear = (int) $now->format('Y');
                $calendarMonth = (int) $now->format('m');
            } else {
                $calendarYear = (int) $calendarYear;
                $calendarMonth = (int) $calendarMonth;
            }

            $calendar = $this->scheduleService->getMonthCalendar($user, $calendarYear, $calendarMonth);

            // Calculate previous and next month
            $currentDate = new \DateTime("$calendarYear-$calendarMonth-01");
            $prevMonthDate = (clone $currentDate)->modify('-1 month');
            $nextMonthDate = (clone $currentDate)->modify('+1 month');

            $calendarMonthName = $this->getMonthName($calendarMonth);
            $prevYear = (int) $prevMonthDate->format('Y');
            $prevMonth = (int) $prevMonthDate->format('m');
            $nextYear = (int) $nextMonthDate->format('Y');
            $nextMonth = (int) $nextMonthDate->format('m');
        }

        // Render different templates based on user type
        $template = $user->isBarber() ? 'barber/profile.html.twig' : 'client/profile.html.twig';

        return $this->render($template, [
            'user' => $user,
            'isAdmin' => $isAuthUserAdmin,
            'isSuperAdmin' => $isAuthUserSuperAdmin,
            'form' => $form->createView(),
            'clientAppointments' => $clientAppointments,
            'clientAppointmentsPagination' => $clientAppointmentsPagination,
            'clientFilters' => $clientFilters,
            'barberAppointments' => $barberAppointments,
            'appointmentsPagination' => $appointmentsPagination ?? null,
            'filters' => $filters ?? [],
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
     * Helper: Get localized month name.
     */
    private function getMonthName(int $month): string
    {
        return $this->translator->trans("months.$month");
    }
}
