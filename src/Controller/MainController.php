<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        return $this->render('main/homepage.html.twig', ['name' => $name]);
    }

    #[Route('/user/{username}', name: 'user', methods: ['GET'])]
    public function user($username = null): Response
    {
        if (!$username) {
            throw $this->createNotFoundException('There is no user');
        }

        return $this->redirectToRoute('appointment', ['username' => $username]);
    }

    #[Route('/appointment/{username}', name: 'appointment', methods: ['GET'])]
    public function appointment($username): Response
    {
        $error = '';

        //        $us = $this->userRepository->findBy([2], ['id' => 'DESC']);
        //        $us = $this->userRepository->findBy([], ['id' => 'DESC']);
        $user = $this->userRepository->findOneBy(['email' => $username], []);
        //        $us = $this->userRepository->findOneBy(['id' => 2], []);

        if (!$user) {
            throw $this->createNotFoundException('There is no user');
        }

        if (!$user->getFirstName()) {
            return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
        }

        if ($user->isUserIsSuperAdmin()) {
            return $this->redirectToRoute('user_admin', ['id' => $user->getId()]);
        }

        return $this->render('form/appointment_form.html.twig', ['user' => $user, 'error' => $error]);
    }
}
