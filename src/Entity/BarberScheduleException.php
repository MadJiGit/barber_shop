<?php

namespace App\Entity;

use App\Repository\BarberScheduleExceptionRepository;
use App\Service\DateTimeHelper;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;

#[ORM\Entity(repositoryClass: BarberScheduleExceptionRepository::class)]
#[ORM\Table(name: 'barber_schedule_exception')]
#[ORM\Index(name: 'idx_barber_date', columns: ['barber_id', 'date'])]
class BarberScheduleException
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'barber_id', referencedColumnName: 'id', nullable: false)]
    private ?User $barber = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?DateTimeInterface $date = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $is_available = true;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $start_time = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $end_time = null;

    /**
     * Array of excluded time slots in HH:MM format
     * Example: ["15:00", "15:30", "16:00", "16:30"]
     * Used when barber is working but wants to exclude specific hours
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $excluded_slots = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?DateTimeInterface $created_at = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: true)]
    private ?User $created_by = null;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->created_at = DateTimeHelper::now();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getIsAvailable(): bool
    {
        return $this->is_available;
    }

    public function setIsAvailable(bool $is_available): static
    {
        $this->is_available = $is_available;

        return $this;
    }

    public function getStartTime(): ?DateTimeInterface
    {
        return $this->start_time;
    }

    public function setStartTime(?DateTimeInterface $start_time): static
    {
        $this->start_time = $start_time;

        return $this;
    }

    public function getEndTime(): ?DateTimeInterface
    {
        return $this->end_time;
    }

    public function setEndTime(?DateTimeInterface $end_time): static
    {
        $this->end_time = $end_time;

        return $this;
    }

    public function getExcludedSlots(): ?array
    {
        return $this->excluded_slots;
    }

    public function setExcludedSlots(?array $excluded_slots): static
    {
        $this->excluded_slots = $excluded_slots;

        return $this;
    }

    /**
     * Check if a specific time slot is excluded
     */
    public function isSlotExcluded(string $timeSlot): bool
    {
        if (!$this->excluded_slots) {
            return false;
        }

        return in_array($timeSlot, $this->excluded_slots);
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->created_at;
    }

    public function getCreatedBy(): ?User
    {
        return $this->created_by;
    }

    public function setCreatedBy(?User $created_by): static
    {
        $this->created_by = $created_by;

        return $this;
    }

    /**
     * Check if this is a full day off (not available at all)
     */
    public function isFullDayOff(): bool
    {
        return !$this->is_available;
    }

    /**
     * Check if this has custom working hours
     */
    public function hasCustomHours(): bool
    {
        return $this->is_available && ($this->start_time !== null || $this->end_time !== null);
    }

    /**
     * Check if this only excludes specific slots
     */
    public function hasExcludedSlotsOnly(): bool
    {
        return $this->is_available
            && $this->excluded_slots !== null
            && count($this->excluded_slots) > 0
            && $this->start_time === null
            && $this->end_time === null;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('%s - %s',
            $this->barber?->getEmail() ?? 'N/A',
            $this->date?->format('d.m.Y') ?? ''
        );
    }
}
