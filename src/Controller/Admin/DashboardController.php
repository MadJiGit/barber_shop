<?php

namespace App\Controller\Admin;

use App\Entity\Appointments;
use App\Entity\BarberProcedure;
use App\Entity\BarberSchedule;
use App\Entity\BarberScheduleException;
use App\Entity\Procedure;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Redirect to Users CRUD by default
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(UserCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Barber Shop - Admin Panel')
            ->setFaviconPath('favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Потребители');
        yield MenuItem::linkToCrud('Потребители', 'fa fa-users', User::class)
            ->setPermission('ROLE_ADMIN');

        yield MenuItem::section('Процедури');
        yield MenuItem::linkToCrud('Процедури', 'fa fa-scissors', Procedure::class)
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('Барбер Процедури', 'fa fa-user-tie', BarberProcedure::class)
            ->setPermission('ROLE_ADMIN');

        yield MenuItem::section('Записвания');
        yield MenuItem::linkToCrud('Записвания', 'fa fa-calendar-check', Appointments::class)
            ->setPermission('ROLE_MANAGER');

        yield MenuItem::section('Работно време');
        yield MenuItem::linkToCrud('График', 'fa fa-calendar', BarberSchedule::class)
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::linkToCrud('Изключения от график', 'fa fa-calendar-times', BarberScheduleException::class)
            ->setPermission('ROLE_ADMIN');

        yield MenuItem::section('Сайт');
        yield MenuItem::linkToRoute('Към сайта', 'fa fa-home', 'app_homepage');
    }
}
