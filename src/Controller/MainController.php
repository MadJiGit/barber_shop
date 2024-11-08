<?php

namespace App\Controller;

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

    #[Route('/barber_appointments/{id}', name: 'barber_appointments', methods: ['GET', 'POST'])]
    public function barber_appointments(Request $request, $id = ''): Response
    {
        // TODO table with barber's appointments
        $error = '';
        $user = $this->checkIfUserExistAndHasNickname($id);
        $barbers = $this->userRepository->getAllBarbers();
        //        $allAppointments = $this->appointmentRepository->findAll();
        $allAppointments = $this->appointmentRepository->getAllAppointments();
        $procedures = $this->procedureRepository->getAllProcedures();

        $appointment = new Appointments();

        //        echo '<pre>'.var_export($barbers[0]['nick_name'], true).'</pre>';
        //        echo '<pre>'.var_export($allAppointments[0], true).'</pre>';
        //        echo '<pre>'.var_export($user, true).'</pre>';
        //        echo '<pre>'.var_export($procedures, true).'</pre>';
        //        echo '<pre>'.var_export($allAppointments, true).'</pre>';
        //                    exit;
        //        exit();

        $form = $this->createForm(AppointmentFormType::class, $appointment);

        try {
            $form->handleRequest($request);
        } catch (\Exception $e) {
            echo 'failed : '.$e->getMessage();
        }

        // TODO show all barbers
        // TODO show all procedures type
        // TODO show calendar with hours
        //            echo '<pre>'.var_export($barbers, true).'</pre>';

        if ($form->isSubmitted()) {
            echo '<pre>'.var_export($form->getData(), true).'</pre>';
            exit;
            //            $procedure->setDateAdded();
            //            $procedure->setDateLastUpdate();
            //
            //            $this->em->persist($procedure);
            //            $this->em->flush();
            //            $this->em->clear();
            //
            //            return $this->redirectToRoute('appointment',
            //                ['id' => $user->getId()]);
        }

        //        echo '<pre>'.var_export($today, true).'</pre>';
        //        echo '<pre>'.var_export($table, true).'</pre>';
        //        exit();

        $today = now();
        $today = $today->format('Y-m-d');
        $table = ['10:00' => '',
            '11:00' => '',
            '12:00' => '',
            '13:00' => '',
            '14:00' => '',
            '15:00' => '',
            '16:00' => '',
            '17:00' => '',
        ];

        return $this->render('form/appointment_form.html.twig',
            [
                'form' => $form,
                'user' => $user,
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

    #[Route('/add_appointment/{$id}', name: 'add_appointment', methods: ['GET'])]
    public function addAppointment(Request $request, $id): Response
    {
        $error = '';
        $user = $this->checkIfUserExistAndHasNickname($id);

        $appointment = $this->appointmentRepository->findBy(['client_id' => $id], []) ?? false;
        //        $appointment = $this->appointmentRepository->findClientById($id) ?? false;
        echo '<pre>'.var_export($appointment, true).'</pre>';
        exit;
        $form = $this->createForm(AppointmentFormType::class, $appointment);
        try {
            $form->handleRequest($request);
        } catch (\Exception $e) {
            echo 'failed : '.$e->getMessage();
        }

        echo '<pre>'.var_export($form->getData(), true).'</pre>';
        echo '<pre>'.var_export($appointment, true).'</pre>';
        exit;

        return $this->render('form/appointment_form.html.twig',
            [
                'form' => $form,
                'user' => $user,
                'appointment' => $appointment,
                'error' => $error]);
    }

    /**
     * @return RedirectResponse|int
     */
    public function checkIfUserExistAndHasNickname(int $id): RedirectResponse|User
    {
        $user = $this->userRepository->findOneBy(['id' => $id], []);
        //        $user = $this->userRepository->findOneById($id);

        if (!$user) {
            throw $this->createNotFoundException('There is no user');
        }

        if (!$user->getFirstName()) {
            return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
        }

        return $user;
    }
}
