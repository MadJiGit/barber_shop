<?php

namespace App\Controller;

use App\Entity\Appointments;
use App\Entity\Procedure;
use App\Entity\User;
use App\Form\AppointmentFormType;
use App\Repository\AppointmentsRepository;
use App\Repository\BarberProcedureRepository;
use App\Repository\ProcedureRepository;
use App\Repository\UserRepository;
use App\Service\AppointmentValidator;
use App\Service\BarberScheduleService;
use App\Service\DateTimeHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function Symfony\Component\Clock\now;

class ClientController extends AbstractController
{
    private UserRepository $userRepository;
    private AppointmentsRepository $appointmentRepository;
    private ProcedureRepository $procedureRepository;
    private AppointmentValidator $appointmentValidator;
    private BarberProcedureRepository $barberProcedureRepository;
    private BarberScheduleService $scheduleService;
    private EntityManagerInterface $em;

    public function __construct(
        UserRepository $userRepository,
        AppointmentsRepository $appointmentRepository,
        ProcedureRepository $procedureRepository,
        AppointmentValidator $appointmentValidator,
        BarberProcedureRepository $barberProcedureRepository,
        BarberScheduleService $scheduleService,
        EntityManagerInterface $em
    ) {
        $this->userRepository = $userRepository;
        $this->appointmentRepository = $appointmentRepository;
        $this->procedureRepository = $procedureRepository;
        $this->appointmentValidator = $appointmentValidator;
        $this->barberProcedureRepository = $barberProcedureRepository;
        $this->scheduleService = $scheduleService;
        $this->em = $em;
    }
}
