<?php

namespace App\Controller;

use App\Entity\Procedure;
use App\Entity\User;
use App\Form\ProcedureFormType;
use App\Form\UserFormType;
use App\Repository\ProcedureRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    private UserRepository $userRepository;
    private ProcedureRepository $procedureRepository;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository, ProcedureRepository $procedureRepository)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->procedureRepository = $procedureRepository;
    }

    #[Route('/admin_menu/{id}', name: 'admin_menu')]
    public function adminUser(Request $request, $id): Response
    {
        $user = $this->userRepository->findOneById($id);
        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            // TODO exceptions need here
            return $this->redirectToRoute('user', ['username' => $user->getEmail()]);
        }

        return $this->render('admin/dashboard.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/view_all_clients', name: 'view_all_clients')]
    public function listAllClients(Request $request): Response
    {
        $allClients = $this->userRepository->getAllRolesByRolesName('ROLE_CLIENT');
        $clients_list = [];

        $header_row = array_keys($allClients[0]);
        $header_row[] = 'edit';

        foreach ($allClients as $client) {
            $clients_list[] = $client;
        }

        return $this->render('admin/users.html.twig', [
            'user' => parent::getUser(),
            'fields' => $header_row,
            'clients' => $clients_list,
        ]);
    }

    #[Route('/list_without_role', name: 'list_without_role')]
    public function listWithNoRole(Request $request): Response
    {
        $all = $this->userRepository->getWithNoRole();
        $all_list = [];

        foreach ($all as $u => $v) {
            if (empty($v['roles'])) {
                $all_list[] = $v;
            }
        }

        $header_row = array_keys($all[0]);
        $header_row[] = 'edit';

        return $this->render('admin/users.html.twig', [
            'user' => parent::getUser(),
            'fields' => $header_row,
            'clients' => $all_list,
        ]);
    }

    #[Route('/list_all_barbers', name: 'list_all_barbers')]
    public function listAllBarbers(Request $request): Response
    {
        $allBarbers = $this->userRepository->getAllBarbers();
        $barber_list = [];

        $header_row = array_keys($allBarbers[0]);
        $header_row[] = 'edit';

        foreach ($allBarbers as $barber) {
            $barber_list[] = $barber;
        }

        return $this->render('admin/users.html.twig', [
            'user' => parent::getUser(),
            'fields' => $header_row,
            'clients' => $barber_list,
        ]);
    }

    #[Route('admin/user_edit/{id}', name: 'admin_user_edit')]
    public function editUser(Request $request, $id): Response
    {
        $user = $this->userRepository->findOneById($id);
        $form = $this->createForm(UserFormType::class, $user);

        try {
            $form->handleRequest($request);
        } catch (\Exception $e) {
            echo 'failed : '.$e->getMessage();
        }

        if ($form->isSubmitted()) {
            $temp_roles = $form->get('roles')->getData();
            $user->setFirstName($form->get('first_name')->getData());
            $user->setLastName($form->get('last_name')->getData());
            $user->setNickName($form->get('nick_name')->getData());
            $user->setPhone($form->get('phone')->getData());
            $user->setRoles($temp_roles);
            $user->setDateLastUpdate(new \DateTime('now'));

            $this->em->persist($user);
            $this->em->flush();
            $this->em->clear();

            return $this->redirectToRoute('admin_menu',
                ['id' => parent::getUser()->getId()]
            );
        }
        $all_user_roles['name'] = $user->getRoles()[0] ?? null;
        $all_roles = [
            'ROLE_SUPER_ADMIN' => 'Super Admin',
            'ROLE_ADMIN' => 'Admin',
            'ROLE_MANAGER' => 'Manager',
            'ROLE_RECEPTIONIST' => 'Receptionist',
            'ROLE_BARBER_SENIOR' => 'Senior Barber',
            'ROLE_BARBER' => 'Barber',
            'ROLE_BARBER_JUNIOR' => 'Junior Barber',
            'ROLE_CLIENT' => 'Client',
        ];

        return $this->render('admin/user_edit.html.twig', [
            'user' => $user,
            'admin' => parent::getUser(),
            'form' => $form->createView(),
            'custom_user_roles' => $all_user_roles,
            'all_roles' => $all_roles,
        ]);
    }

    private function loadAuthUserData(): ?User
    {
        $user = parent::getUser();

        return $this->userRepository->findOneBy(['email' => $user->getUserIdentifier()], []);
    }

    /**
     * List all procedures
     */
    #[Route('/procedures', name: 'admin_procedures')]
    public function listProcedures(Request $request): Response
    {
        $userAuth = parent::getUser();
        $user = $this->userRepository->findOneById($userAuth->getId());

        if (!$user->isUserIsSuperAdmin()) {
            return $this->redirectToRoute('main');
        }

        $procedures = $this->procedureRepository->getAllProcedures();
        $header_row = '';
        if (!empty($procedures)) {
            $header_row = array_keys($procedures[0]);
            $header_row[] = 'edit';
            $header_row[] = 'delete';
            array_shift($header_row);
        }

        return $this->render('admin/procedures.html.twig', [
            'procedures' => $procedures,
            'fields' => $header_row,
            'user' => $user,
        ]);
    }

    /**
     * Add new procedure
     */
    #[Route('/procedure/add', name: 'admin_procedure_add')]
    public function addProcedure(Request $request): Response
    {
        $userAuth = parent::getUser();
        $user = $this->userRepository->findOneById($userAuth->getId());

        if (!$user->isUserIsSuperAdmin()) {
            return $this->redirectToRoute('main');
        }

        $procedure = new Procedure();
        $form = $this->createForm(ProcedureFormType::class, $procedure);

        try {
            $form->handleRequest($request);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Грешка: ' . $e->getMessage());
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $procedure->setDateAdded();
            $procedure->setDateLastUpdate();

            $this->em->persist($procedure);
            $this->em->flush();
            $this->em->clear();

            $this->addFlash('success', 'Процедурата е добавена успешно.');
            return $this->redirectToRoute('admin_procedures');
        }

        return $this->render('admin/procedure_form.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edit procedure
     */
    #[Route('/procedure/{id}/edit', name: 'admin_procedure_edit')]
    public function editProcedure(Request $request, int $id): Response
    {
        $userAuth = parent::getUser();

        if (!$userAuth->isUserIsSuperAdmin()) {
            return $this->redirectToRoute('main');
        }

        $procedure = $this->procedureRepository->findOneBy(['id' => $id]);

        if (!$procedure) {
            $this->addFlash('error', 'Процедурата не е намерена.');
            return $this->redirectToRoute('admin_procedures');
        }

        $form = $this->createForm(ProcedureFormType::class, $procedure);

        try {
            $form->handleRequest($request);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Грешка: ' . $e->getMessage());
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $procedure->setDateLastUpdate();
            $this->em->persist($procedure);
            $this->em->flush();
            $this->em->clear();

            $this->addFlash('success', 'Процедурата е редактирана успешно.');
            return $this->redirectToRoute('admin_procedures');
        }

        return $this->render('admin/procedure_form.html.twig', [
            'user' => $userAuth,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Delete procedure
     */
    #[Route('/procedure/{id}/delete', name: 'admin_procedure_delete')]
    public function deleteProcedure(Request $request, int $id): Response
    {
        $userAuth = parent::getUser();

        if (!$userAuth->isUserIsSuperAdmin()) {
            return $this->redirectToRoute('main');
        }

        $procedure = $this->procedureRepository->findOneBy(['id' => $id]);

        if ($procedure) {
            $this->em->remove($procedure);
            $this->em->flush();
            $this->em->clear();
            $this->addFlash('success', 'Процедурата е изтрита успешно.');
        } else {
            $this->addFlash('error', 'Процедурата не е намерена.');
        }

        return $this->redirectToRoute('admin_procedures');
    }
}
