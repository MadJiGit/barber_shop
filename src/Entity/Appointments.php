<?php

namespace App\Entity;

use App\Enum\AppointmentStatus;
use App\Repository\AppointmentsRepository;
use App\Service\DateTimeHelper;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppointmentsRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(name: 'idx_appointments_date', columns: ['date'])]
#[ORM\Index(name: 'idx_appointments_barber_date', columns: ['barber_id', 'date'])]
#[ORM\Index(name: 'idx_appointments_client_date', columns: ['client_id', 'date'])]
#[ORM\Index(name: 'idx_appointments_status', columns: ['status'])]
class Appointments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeInterface $date = null;

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

    #[ORM\Column(type: Types::STRING, length: 30, enumType: AppointmentStatus::class)]
    private AppointmentStatus $status = AppointmentStatus::PENDING;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeInterface $date_added = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_last_update = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_canceled = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $cancellation_reason = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::STRING, length: 64, unique: true, nullable: true)]
    private ?string $confirmation_token = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $confirmed_at = null;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->date_added = DateTimeHelper::now();
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

    /**
     * @throws \Exception
     */
    public function setDateAdded(?\DateTimeInterface $date_added = null): static
    {
        if (empty($date_added)) {
            $date_added = DateTimeHelper::now();
        }
        $this->date_added = $date_added;

        return $this;
    }

    public function getDateCanceled(): ?\DateTimeInterface
    {
        return $this->date_canceled;
    }

    /**
     * @throws \Exception
     */
    public function setDateCanceled(?\DateTimeInterface $date_canceled = null): static
    {
        if (empty($date_canceled)) {
            $date_canceled = DateTimeHelper::now();
        }
        $this->date_canceled = $date_canceled;

        return $this;
    }

    public function getDateLastUpdate(): ?\DateTimeInterface
    {
        return $this->date_last_update;
    }

    /**
     * @throws \Exception
     */
    public function setDateLastUpdate(?\DateTimeInterface $date_last_update = null): static
    {
        if (empty($date_last_update)) {
            $date_last_update = DateTimeHelper::now();
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

    public function getStatus(): AppointmentStatus
    {
        return $this->status;
    }

    /**
     * @throws \Exception
     */
    public function setStatus(AppointmentStatus $status): static
    {
        $this->status = $status;
        $this->date_last_update = DateTimeHelper::now();

        return $this;
    }

    /**
     * Get status value as string (for backward compatibility).
     */
    public function getStatusValue(): string
    {
        return $this->status->value;
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

    public function getConfirmationToken(): ?string
    {
        return $this->confirmation_token;
    }

    public function setConfirmationToken(?string $confirmation_token): static
    {
        $this->confirmation_token = $confirmation_token;

        return $this;
    }

    public function getConfirmedAt(): ?\DateTimeInterface
    {
        return $this->confirmed_at;
    }

    public function setConfirmedAt(?\DateTimeInterface $confirmed_at): static
    {
        $this->confirmed_at = $confirmed_at;

        return $this;
    }

    /**
     * @throws \Exception
     */
    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->date_last_update = DateTimeHelper::now();
    }

    public function __toString(): string
    {
        return sprintf(
            '#%d - %s (%s)',
            $this->id ?? 0,
            $this->date?->format('d.m.Y H:i') ?? 'N/A',
            $this->client?->getEmail() ?? 'N/A'
        );
    }
}
