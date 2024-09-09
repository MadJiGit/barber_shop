<?php

namespace App\Controller;

use App\Entity\Roles;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    #[Route('/user_admin/{id}', name: 'user_admin')]
    public function adminUser(Request $request, $id): Response
    {
        $user = $this->userRepository->findOneById($id);

        $authUser = parent::getUser();

        if ($user->getId() != $authUser->getId() || !array_search(Roles::SUPER_ADMIN->value, $authUser->getRoles())) {
//            throw new \ErrorException('User has no admin rights!');
            // TODO exceptions need here
            return $this->redirectToRoute('user', ['username' => $user->getEmail()]);
        }

        return $this->render('admin/menu.html.twig', [
            'user' => $user,
            //            'isAdmin' => $isAuthUserAdmin,
            //            'isSuperAdmin' => $isAuthUserSuperAdmin,
            //            'form' => $form->createView(),
        ]);
    }
}
