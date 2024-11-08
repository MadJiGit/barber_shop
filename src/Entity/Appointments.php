<?php

namespace App\Entity;

use App\Repository\AppointmentsRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;

#[ORM\Entity(repositoryClass: AppointmentsRepository::class)]
class Appointments
{
    private UserRepository $userRepository;
    private EntityManagerInterface $em;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?int $duration = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\User", inversedBy: "id")]
    #[ORM\JoinColumn(name: "client_id", referencedColumnName: "id")]
    private User $client;

    #[ORM\ManyToOne(targetEntity: "App\Entity\User")]
    #[ORM\JoinColumn(name: "barber_id", referencedColumnName: "id")]
    private User $barber;
//    //    #[ManyToMany(targetEntity: User::class, inversedBy: 'id')]
//    //    #[ManyToMany(inversedBy: 'barber', cascade: ['persist', 'remove'])]
//    //    #[ORM\JoinColumn(nullable: false)]
//    private $client;

//    //    #[ManyToMany(targetEntity: User::class, inversedBy: 'id')]
//    //    #[ManyToMany(inversedBy: 'barber', cascade: ['persist', 'remove'])]
//    //    #[ORM\JoinColumn(nullable: false)]
//    private $barber;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_added = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_update = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_canceled = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_last_update = null;

    #[ORM\OneToOne(inversedBy: 'appointments', cascade: ['persist', 'remove'])]
    //    #[ManyToMany(targetEntity: Procedure::class, inversedBy: 'barber', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Procedure $procedure_type = null;

    public function __construct()
    {
        //        $this->em = $em;
        //        $this->userRepository = new UserRepository();
        //        $this->userRepository = $this->em->getRepository(User::class);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getClient(): ?User
    {
        //        $this->userRepository->findOneBy(["id" => $this->getId()]);
        return $this->client;
    }

    public function setClient(User $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getBarber(): ?User
    {
        return $this->barber;
    }

    public function setBarber(User $barber): static
    {
        $this->barber = $barber;

        return $this;
    }

    public function getDateAdded(): ?\DateTimeInterface
    {
        return $this->date_added;
    }

    public function setDateAdded(\DateTimeInterface $date_added): static
    {
        $this->date_added = $date_added;

        return $this;
    }

    public function getDateUpdate(): ?\DateTimeInterface
    {
        return $this->date_update;
    }

    public function setDateUpdate(?\DateTimeInterface $date_update): static
    {
        $this->date_update = $date_update;

        return $this;
    }

    public function getDateCanceled(): ?\DateTimeInterface
    {
        return $this->date_canceled;
    }

    public function setDateCanceled(?\DateTimeInterface $date_canceled): static
    {
        $this->date_canceled = $date_canceled;

        return $this;
    }

    public function getDateLastUpdate(): ?\DateTimeInterface
    {
        return $this->date_last_update;
    }

    public function setDateLastUpdate(?\DateTimeInterface $date_last_update): static
    {
        $this->date_last_update = $date_last_update;

        return $this;
    }

    public function getProcedureType(): ?Procedure
    {
        return $this->procedure_type;
    }

    public function setProcedureType(Procedure $procedure_type): static
    {
        $this->procedure_type = $procedure_type;

        return $this;
    }
}
