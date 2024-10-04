<?php

namespace App\Controller;

use App\Entity\Procedure;
use App\Form\ProcedureFormType;
use App\Repository\ProcedureRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProcedureController extends AbstractController
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

    #[Route('/procedure_show/{id}', name: 'procedure_show')]
    public function procedureShow(Request $request, int $id): Response
    {
        $userAuth = parent::getUser();
        $user = $this->userRepository->findOneById($id);

        if ($userAuth->getId() != $user->getId()
            || !$user->isUserIsSuperAdmin()) {
            return $this->redirectToRoute('user', ['username' => $user->getEmail()]);
        }

        $procedures = $this->procedureRepository->getAllProcedures();
        $header_row = '';
        if (!empty($procedures)) {
            $header_row = array_keys($procedures[0]);
            $header_row[] = 'edit';
            $header_row[] = 'delete';
            array_shift($header_row);
        }

        return $this->render('admin/view_all_procedures.html.twig', [
            'procedures' => $procedures,
            'fields' => $header_row,
            'user' => $user,
        ]);
    }

    #[Route('/procedure_add/{id}', name: 'procedure_add')]
    public function procedureAdd(Request $request, int $id): Response
    {
        $userAuth = parent::getUser();
        $user = $this->userRepository->findOneById($id);

        if ($userAuth->getId() != $user->getId()
            || !$user->isUserIsSuperAdmin()) {
            return $this->redirectToRoute('user', ['username' => $user->getEmail()]);
        }

        $procedure = new Procedure();
        $form = $this->createForm(ProcedureFormType::class, $procedure);

        try {
            $form->handleRequest($request);
        } catch (\Exception $e) {
            echo 'failed : '.$e->getMessage();
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $procedure->setDateAdded();
            $procedure->setDateLastUpdate();

            $this->em->persist($procedure);
            $this->em->flush();
            $this->em->clear();

            return $this->redirectToRoute('appointment',
                ['id' => $user->getId()]);
        }

        return $this->render('form/procedure_form.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/procedure_edit/{id}', name: 'procedure_edit')]
    public function procedureEdit(Request $request, int $id): Response
    {
        $userAuth = parent::getUser();

        if (!$userAuth->isUserIsSuperAdmin()) {
            return $this->redirectToRoute('user', ['username' => $userAuth->getEmail()]);
        }

        $procedure = $this->procedureRepository->findOneBy(['id' => $id]);

        $form = $this->createForm(ProcedureFormType::class, $procedure);

        try {
            $form->handleRequest($request);
        } catch (\Exception $e) {
            echo 'failed : '.$e->getMessage();
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($procedure);
            $this->em->flush();
            $this->em->clear();

            return $this->redirectToRoute('appointment',
                ['id' => $userAuth->getId()]);
        }

        return $this->render('form/procedure_form.html.twig', [
            'user' => $userAuth,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/procedure_delete/{id}', name: 'procedure_delete')]
    public function procedureDelete(Request $request, int $id): Response
    {
        $userAuth = parent::getUser();

        if (!$userAuth->isUserIsSuperAdmin()) {
            return $this->redirectToRoute('user', ['username' => $userAuth->getEmail()]);
        }

        $procedure = $this->procedureRepository->findOneBy(['id' => $id]);

        $form = $this->createForm(ProcedureFormType::class, $procedure);

        try {
            $form->handleRequest($request);
        } catch (\Exception $e) {
            echo 'failed : '.$e->getMessage();
        }

        $this->em->remove($procedure);
        $this->em->flush();
        $this->em->clear();

        return $this->redirectToRoute('procedure_show', ['id' => $userAuth->getId()]);
    }
}
