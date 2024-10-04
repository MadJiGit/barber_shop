<?php

namespace App\Controller;

use App\Entity\Appointments;
use App\Entity\User;
use App\Repository\AppointmentsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    private UserRepository $userRepository;
    private AppointmentsRepository $appointmentRepository;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->userRepository = $em->getRepository(User::class);
        $this->appointmentRepository = $em->getRepository(Appointments::class);
    }

    #[Route('/', name: 'main', methods: ['GET'])]
    public function index($name = null): Response
    {
        $authUser = parent::getUser();

        return $this->render('main/homepage.html.twig', ['name' => $name]);
    }

    #[Route('/user/{username}', name: 'user', methods: ['GET'])]
    public function user($username = null): Response
    {
        if (!$username) {
            throw $this->createNotFoundException('There is no user');
        }

        $user = parent::getUser();

        if ($user->isUserIsSuperAdmin()) {
            return $this->redirectToRoute('user_admin', ['id' => $user->getId()]);
        }

        return $this->redirectToRoute('appointment', ['id' => $user->getId()]);
    }

    #[Route('/barber_appointments/{id}', name: 'barber_appointments', methods: ['GET'])]
    public function barber_appointments($id): Response
    {
        // TODO table with barber's appointments
        $error = '';
        $user = $this->checkIfUserExistAndHasNickname($id);

        var_dump('barber\'s appointments will be there after a while.... id '.$id);
        exit;
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

        $form = $this->createForm(AppointmentFormType::class, $appointment);
        try {
            $form->handleRequest($request);
        } catch (\Exception $e) {
            echo 'failed : '.$e->getMessage();
        }

        return $this->render('form/appointment_form.html.twig',
            ['user' => $user,
                'appointment' => $appointment,
                'error' => $error]);
    }

    /**
     * @return RedirectResponse|int
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
}
