<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    #[Route('/', name: 'main', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // Redirect to clean URL if there are any query parameters
        if (count($request->query->all()) > 0) {
            return $this->redirectToRoute('main');
        }

        $authUser = parent::getUser();
        $user = null;
        if ($authUser) {
            $user = $this->userRepository->findOneBy(['email' => $authUser->getUserIdentifier()]);
        }

        return $this->render('main/homepage.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/user/{username}', name: 'user', methods: ['GET'])]
    public function user($username = null): Response
    {
        $user = $this->userRepository->findOneBy(['email' => $username], []);

        if ($user && in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('admin_menu', ['id' => $user->getId()]);
        }

        return $this->redirectToRoute('main', ['id' => $user->getId()]);
    }
}
