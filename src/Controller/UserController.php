<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    #[Route('/user_edit/{id}', name: 'user_edit')]
    public function editUser(Request $request, $id): Response
    {
        //        $n_user = $this->em->getRepository()
        $user = $this->userRepository->findOneById($id);

        $authUser = parent::getUser();

        $isAuthUserAdmin = $authUser ? $authUser->isUserIsAdmin() : false;
        $isAuthUserSuperAdmin = $authUser ? $authUser->isUserIsSuperAdmin() : false;

        if (!$user) {
            throw $this->createNotFoundException('There is no user');
        }

        $form = $this->createForm(UserFormType::class, $user);
        try {
            $form->handleRequest($request);
        } catch (\Exception $e) {
            echo 'failed : '.$e->getMessage();
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setFirstName($form->get('first_name')->getData());
            $user->setLastName($form->get('last_name')->getData());
            $user->setNickName($form->get('nick_name')->getData());
            $user->setPhone($form->get('phone')->getData());
            $user->setDateLastUpdate(new \DateTime('now'));

            $this->em->flush();

            return $this->redirectToRoute('appointment',
                //                ['id' => $user->getId(), 'nickname' => $user->getNickName()]);
                ['username' => $user->getEmail()]);
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'isAdmin' => $isAuthUserAdmin,
            'isSuperAdmin' => $isAuthUserSuperAdmin,
            'form' => $form->createView(),
        ]);
    }
}
