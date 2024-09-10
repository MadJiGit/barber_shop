<?php

namespace App\Controller;

use App\Entity\Roles;
use App\Entity\User;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Schema\Table;
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

    #[Route('/list_all_clients', name: 'list_all_clients')]
    public function listAllClients(): Response
    {
        $allClients = $this->userRepository->getAllClients();
//        $user = new User();
//        $form = $this->createForm(UserFormType::class, $user);

//        dd($allClients);


        $form_tab = array();
        $form_tab_2 = array();

        $form_tab = array_keys($allClients[0]);
//        foreach($allClients as $client => $value){
//            $form_tab[] = $value;
//        }

        foreach($allClients as $client){
//            echo '<pre>' . var_export($client . ' => ' . $value, true) . '</pre>';
//            echo '<pre>' . var_export($client, true) . '</pre>';
//            echo '<pre>' . var_export($value, true) . '</pre>';

          $form_tab_2[] = $client;
//            $form = $this->createForm(UserFormType::class, new User());
        }

//        echo '<pre>' . var_export($test, true) . '</pre>';
//        echo '<pre>' . var_export($form_tab, true) . '</pre>';
//        echo '<pre>' . var_export($form_tab_2[0], true) . '</pre>';
//        exit();
//        dd($allClients);

        return $this->render('admin/view_all_clients.html.twig', [
            'fields' => $form_tab,
            'clients' => $form_tab_2,
//            'form' => $form
        ]);
    }

    #[Route('/list_all_barbers', name: 'list_all_barbers')]
    public function listAllBarbers(): Response
    {
        dd("BARBERS");
    }
}
