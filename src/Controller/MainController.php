<?php

namespace App\Controller;

use App\Entity\AppointmentHours;
use App\Entity\Appointments;
use App\Entity\Procedure;
use App\Entity\User;
use App\Form\AppointmentFormType;
use App\Repository\AppointmentsRepository;
use App\Repository\ProcedureRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function Symfony\Component\Clock\now;

class MainController extends AbstractController
{
    private UserRepository $userRepository;
    private AppointmentsRepository $appointmentRepository;
    private ProcedureRepository $procedureRepository;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->userRepository = $em->getRepository(User::class);
        $this->appointmentRepository = $em->getRepository(Appointments::class);
        $this->procedureRepository = $em->getRepository(Procedure::class);
    }

    #[Route('/', name: 'main', methods: ['GET'])]
    public function index($name = null): Response
    {
        $authUser = parent::getUser();
        $user = '';
        if ($authUser) {
            $user = $this->userRepository->findOneBy(['email' => $authUser->getUserIdentifier()]);
        }

        return $this->render('main/homepage.html.twig',
            [
                'name' => $name,
                'user' => $user,
            ]);
    }

    #[Route('/user/{username}', name: 'user', methods: ['GET'])]
    public function user($username = null): Response
    {
        if (!$username) {
            throw $this->createNotFoundException('There is no user');
        }

        $user = parent::getUser();

        if ($user->isUserIsSuperAdmin()) {
            return $this->redirectToRoute('admin_menu', ['id' => $user->getId()]);
        }

        return $this->redirectToRoute('main', ['id' => $user->getId()]);
    }

    /**
     * Display and handle appointment booking form
     *
     * @param Request $request
     * @param int|string $id Client user ID
     * @return Response
     * @throws \DateMalformedStringException
     */
    #[Route('/barber_appointments/{id}', name: 'barber_appointments', methods: ['GET', 'POST'])]
    public function barber_appointments(Request $request, int|string $id = ''): Response
    {
        $error = '';
        $client = $this->checkIfUserExistAndHasNickname($id);
        $barbers = $this->userRepository->getAllBarbers();
        $allAppointments = $this->appointmentRepository->getAllAppointments();
        $procedures = $this->procedureRepository->getAllProcedures();

        $appointment = new Appointments();

        $form = $this->createForm(AppointmentFormType::class, $appointment);

        try {
            $form->handleRequest($request);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to process form: '.$e->getMessage());
            return $this->redirectToRoute('barber_appointments', ['id' => $client->getId()]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'You reserved an appointment!');

            // Get data from request instead of $_POST
            $procedureId = $request->request->get('procedures');
            $barberId = $request->request->get('barbers');
            $appointmentStart = $request->request->get('appointment_start');
            $pickedHours = $request->request->get('pickedHours');

            $procedure = $this->procedureRepository->findOneProcedureById($procedureId);
            $barber = $this->userRepository->findOneById($barberId);
            $dateAppointment = new \DateTimeImmutable($appointmentStart.' '.$pickedHours);
            $duration = $this->getDurationOfProcedure($procedure, $barber);

            $appointment->setClient($client);
            $appointment->setBarber($barber);
            $appointment->setProcedureType($procedure);
            $appointment->setDate($dateAppointment);
            $appointment->setDateAdded();
            $appointment->setDuration($duration);

            $this->em->persist($appointment);
            $this->em->flush();
            $this->em->clear();

            return $this->redirectToRoute('barber_appointments',
                ['id' => $client->getId()]);
        }

        $today = now();
        $today = $today->format('Y-m-d');
        $table = AppointmentHours::getAppointmentHours();

        return $this->render('form/appointment_form.html.twig',
            [
                'form' => $form,
                'user' => $client,
                'appointment' => $appointment,
                'error' => $error,
                'barbers' => $barbers,
                'procedures' => $procedures,
                'appointments' => $allAppointments,
                'today' => $today,
                'table' => $table,
            ]);
    }

    #[Route('/appointment/{id}', name: 'appointment', methods: ['GET'])]
    public function appointment($id): Response
    {
        $error = '';
        $user = $this->checkIfUserExistAndHasNickname($id);

        return $this->render('form/appointment_form.html.twig', ['user' => $user, 'error' => $error]);
    }

    /**
     * Alternative appointment creation route (currently not used)
     * This method needs proper implementation or should be removed
     */
    #[Route('/add_appointment/{id}', name: 'add_appointment', methods: ['GET', 'POST'])]
    public function addAppointment(Request $request, int $id): Response
    {
        // Redirect to main appointment booking route
        return $this->redirectToRoute('barber_appointments', ['id' => $id]);
    }

    /**
     * Check if user exists and has required profile information
     * Redirects to profile edit if first name is missing
     *
     * @param int $id User ID
     * @return User
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function checkIfUserExistAndHasNickname(int $id): RedirectResponse|User
    {
        $user = $this->userRepository->findOneBy(['id' => $id], []);

        if (!$user) {
            throw $this->createNotFoundException('There is no user');
        }

        if (!$user->getFirstName()) {
            return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
        }

        return $user;
    }

    private function getDurationOfProcedure(Procedure $procedure, User $barber): int|bool
    {
        $roles = array_values($barber->getRoles());
        if (in_array('ROLE_BARBER_JUNIOR', $roles)) {
            return $procedure->getDurationJunior();
        } elseif (in_array('ROLE_BARBER', $roles) || in_array('ROLE_BARBER_SENIOR', $roles)) {
            return $procedure->getDurationMaster();
        }

        return false;
    }

    private function getProcedure(array $procedures, int $id): Procedure|bool
    {
        foreach ($procedures as $pro) {
            if ($pro['id'] == $id) {
                return $pro;
            }
        }

        return false;
    }
}
