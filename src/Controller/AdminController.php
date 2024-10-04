<?php

namespace App\Controller;

use App\Entity\Procedure;
use App\Entity\Roles;
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

    #[Route('/user_admin/{id}', name: 'user_admin')]
    public function adminUser(Request $request, $id): Response
    {
        $user = $this->userRepository->findOneById($id);
        if (!in_array(Roles::SUPER_ADMIN->value, $user->getRoles())) {
            // TODO exceptions need here
            return $this->redirectToRoute('user', ['username' => $user->getEmail()]);
        }

        return $this->render('admin/menu.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/list_all_clients', name: 'list_all_clients')]
    public function listAllClients(Request $request): Response
    {
        $allClients = $this->userRepository->getAllRolesByRolesName(Roles::CLIENT->value);
        $clients_list = [];

        $header_row = array_keys($allClients[0]);
        $header_row[] = 'edit';

        foreach ($allClients as $client) {
            $clients_list[] = $client;
        }

        return $this->render('admin/view_all_clients.html.twig', [
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

        return $this->render('admin/view_all_clients.html.twig', [
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

        return $this->render('admin/view_all_clients.html.twig', [
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

            return $this->redirectToRoute('user_admin',
                ['id' => parent::getUser()->getId()]
            );
        }
        $all_user_roles['name'] = $user->getRoles()[0] ?? null;
        $all_roles = Roles::cases();

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

    public static function bind_params(&$stmt, $params)
    {
        if (empty($params)) {
            return false;
        }
        if (!is_array($params)) {
            return false;
        }
        if (!is_a($stmt, 'mysqli_stmt')) {
            return false;
        }

        $res = call_user_func_array([$stmt, 'bind_param'], self::refValues($params));

        return true;
    }

    public static function refValues($arr)
    {
        if (strnatcmp(phpversion(), '5.3') >= 0) {
            $refs = [];
            foreach ($arr as $key => $value) {
                $refs[$key] = &$arr[$key];
            }

            return $refs;
        }

        return $arr;
    }

    public function new_test($conn, string $name, int $age, ?string $neshto)
    {
        $dbparams = [];
        $dbparams[0] = '';
        $dbsubquery = [];

        $this->add_data_to_query_origin($dbsubquery, $dbparams, 's', 'name = ?', !empty($name) ? 'null' : 'nqma');
        $this->add_data_to_query_origin($dbsubquery, $dbparams, 'i', 'age = ?', 16);
        $this->add_data_to_query_origin($dbsubquery, $dbparams, 's', 'neshto = ?', null);

        $all_params = implode(",\n", $dbsubquery);
        $query = "INSERT INTO barber_shop.test SET \n".$all_params.';';
        $stmt = $conn->prepare($query);
        $this->bind_params($stmt, $dbparams);

        $stmt->execute();
        $stmt->get_result();
        $odit_docs_id = $conn->insert_id;
        $stmt->close();

        dd($odit_docs_id);
    }

    public function add_data_to_query($conn, string $name, int $age, ?string $neshto): void
    {
        //                $query = 'INSERT INTO barber_shop.test (`name`, `age`, `neshto`) values ("pesho_3", 32, "null")';
        $query = 'INSERT INTO barber_shop.test (`name`, `age`, `neshto`) VALUES (?, ?, ?)';
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sis', $name, $age, $neshto);
        $stmt->execute();
        $stmt->get_result();
        $odit_docs_id = $conn->insert_id;
        $stmt->close();

        dd($odit_docs_id);
    }

    public function select_query($conn)
    {
        $query = 'SELECT id FROM barber_shop.test WHERE test.neshto = "neshto1"';
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();

        $t = $res->fetch_assoc();

        if (empty($t['id'])) {
            //            if(isset($t) && $t['id']){
            //                echo '<pre>'.var_export($t['id'], true).'</pre>';
            echo '<pre>'.var_export('tuk', true).'</pre>';
        } else {
            echo '<pre>'.var_export('nishto nqma', true).'</pre>';
        }
        //        while ($t = $res->fetch_assoc()) {
        //            echo '<pre>'.var_export($t, true).'</pre>';
        //        }
    }

    public function add_data_to_query_origin(array &$dbsubquery, array &$dbparams, string $param_type, string $column, ?string $value): void
    {
        $dbsubquery[] = $column;
        $dbparams[0] .= $param_type;
        $dbparams[] = $value;
    }

    #[Route('/test', name: 'test')]
    public function test()
    {
        $conn = mysqli_connect('localhost', 'root', 'neam-nervi');
        //        $this->new_test($conn, 'ivan_2', 66, 'null');
        //        $this->add_data_to_query();
        $this->select_query($conn);

        exit;
    }
}
