<?php

namespace App\Entity;

use App\Repository\AppointmentsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppointmentsRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Appointments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $duration = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', nullable: false)]
    private ?User $client = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'barber_id', referencedColumnName: 'id', nullable: false)]
    private ?User $barber = null;

    #[ORM\ManyToOne(targetEntity: Procedure::class)]
    #[ORM\JoinColumn(name: 'procedure_id', referencedColumnName: 'id', nullable: false)]
    private ?Procedure $procedure_type = null;

    #[ORM\Column(type: Types::STRING, length: 20, options: ['default' => 'pending'])]
    private string $status = 'pending';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $date_added = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $date_last_update = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $date_canceled = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $cancellation_reason = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    public function __construct()
    {
        $this->date_added = new \DateTimeImmutable('now');
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

    public function setDateAdded(?\DateTimeInterface $date_added = null): static
    {
        if (empty($date_added)) {
            $date_added = new \DateTime('now');
        }
        $this->date_added = $date_added;

        return $this;
    }

    public function getDateUpdate(): ?\DateTimeInterface
    {
        return $this->date_update;
    }

    public function setDateUpdate(?\DateTimeInterface $date_update = null): static
    {
        if (empty($date_update)) {
            $date_update = new \DateTime('now');
        }
        $this->date_update = $date_update;

        return $this;
    }

    public function getDateCanceled(): ?\DateTimeInterface
    {
        return $this->date_canceled;
    }

    public function setDateCanceled(?\DateTimeInterface $date_canceled = null): static
    {
        if (empty($date_canceled)) {
            $date_canceled = new \DateTime('now');
        }
        $this->date_canceled = $date_canceled;

        return $this;
    }

    public function getDateLastUpdate(): ?\DateTimeInterface
    {
        return $this->date_last_update;
    }

    public function setDateLastUpdate(?\DateTimeInterface $date_last_update = null): static
    {
        if (empty($date_last_update)) {
            $date_last_update = new \DateTime('now');
        }
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        $this->date_last_update = new \DateTimeImmutable('now');

        return $this;
    }

    public function getCancellationReason(): ?string
    {
        return $this->cancellation_reason;
    }

    public function setCancellationReason(?string $cancellation_reason): static
    {
        $this->cancellation_reason = $cancellation_reason;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->date_last_update = new \DateTimeImmutable('now');
    }
}
