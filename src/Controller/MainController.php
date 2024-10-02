<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->userRepository = $em->getRepository(User::class);
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

        //        $user = $this->userRepository->findOneBy(['email' => $username], []);
        $user = parent::getUser();

        if ($user->isUserIsSuperAdmin()) {
            return $this->redirectToRoute('user_admin', ['id' => $user->getId()]);
        }

        return $this->redirectToRoute('appointment', ['username' => $username]);
    }

    #[Route('/barber_appointments/{id}', name: 'barber_appointments', methods: ['GET'])]
    public function barber_appointments($id): Response
    {
        // TODO table with barber's appointments
        $error = '';
        $user = $this->checkIfUserHasRights($id);

        var_dump('barber\'s appointments will be there after a while.... id '.$id);
        exit;
    }

//    #[Route('/appointment/{username}', name: 'appointment', methods: ['GET'])]
    #[Route('/appointment/{id}', name: 'appointment', methods: ['GET'])]
//    public function appointment($username): Response
    public function appointment($id): Response
    {
        //        $us = $this->userRepository->findBy([2], ['id' => 'DESC']);
        //        $us = $this->userRepository->findBy([], ['id' => 'DESC']);
//        $user = $this->userRepository->findOneBy(['email' => $username], []);
//        $user = $this->userRepository->findOneBy(['id' => $id], []);
        //        $us = $this->userRepository->findOneBy(['id' => 2], []);
        $error = '';
        $user = $this->checkIfUserHasRights($id);

        return $this->render('form/appointment_form.html.twig', ['user' => $user, 'error' => $error]);
    }

    #[Route('/add_appointment/{$id}', name: 'add_appointment', methods: ['GET'])]
    public function addAppointment($id): Response
    {
        $error = '';
        $user = $this->checkIfUserHasRights($id);

        return $this->render('form/appointment_form.html.twig', ['user' => $user, 'error' => $error]);
    }

    /**
     * @param int $id
     * @return RedirectResponse|int
     */
    public function checkIfUserHasRights(int $id): RedirectResponse|User
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
