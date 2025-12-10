<?php

namespace App\Entity;

use App\Repository\BarberScheduleRepository;
use App\Service\DateTimeHelper;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use InvalidArgumentException;

#[ORM\Entity(repositoryClass: BarberScheduleRepository::class)]
#[ORM\Table(name: 'barber_schedule')]
#[ORM\HasLifecycleCallbacks]
class BarberSchedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'barber_id', referencedColumnName: 'id', nullable: false)]
    private ?User $barber = null;

    /**
     * Weekly schedule template stored as JSON
     * Structure: {
     *   "0": {"working": false},  // Sunday - not working
     *   "1": {"start": "09:00", "end": "18:00", "working": true},  // Monday
     *   "2": {"start": "09:00", "end": "18:00", "working": true},  // Tuesday
     *   ...
     *   "6": {"start": "09:00", "end": "13:00", "working": true}   // Saturday - until noon
     * }
     */
    #[ORM\Column(type: Types::JSON)]
    private array $schedule_data = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeInterface $updated_at = null;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->created_at = DateTimeHelper::now();
        $this->initializeDefaultSchedule();
    }

    /**
     * Initialize default weekly schedule:
     * Monday-Friday: 09:00-18:00
     * Saturday: 09:00-13:00
     * Sunday: Not working
     */
    private function initializeDefaultSchedule(): void
    {
        $this->schedule_data = [
            '0' => ['working' => false], // Sunday
            '1' => ['start' => '09:00', 'end' => '18:00', 'working' => true], // Monday
            '2' => ['start' => '09:00', 'end' => '18:00', 'working' => true], // Tuesday
            '3' => ['start' => '09:00', 'end' => '18:00', 'working' => true], // Wednesday
            '4' => ['start' => '09:00', 'end' => '18:00', 'working' => true], // Thursday
            '5' => ['start' => '09:00', 'end' => '18:00', 'working' => true], // Friday
            '6' => ['start' => '09:00', 'end' => '13:00', 'working' => true], // Saturday
        ];
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

    public function getScheduleData(): array
    {
        return $this->schedule_data;
    }

    /**
     * @throws Exception
     */
    public function setScheduleData(array $schedule_data): static
    {
        $this->schedule_data = $schedule_data;
        $this->updated_at = DateTimeHelper::now();

        return $this;
    }

    /**
     * Get schedule for specific day of week (0-6)
     */
    public function getScheduleForDay(int $dayOfWeek): ?array
    {
        if ($dayOfWeek < 0 || $dayOfWeek > 6) {
            throw new InvalidArgumentException('Day of week must be between 0 and 6');
        }

        return $this->schedule_data[(string)$dayOfWeek] ?? null;
    }

    /**
     * Check if barber is working on specific day of week
     */
    public function isWorkingOnDay(int $dayOfWeek): bool
    {
        $daySchedule = $this->getScheduleForDay($dayOfWeek);

        return $daySchedule && ($daySchedule['working'] ?? false);
    }

    /**
     * Get working hours for specific day of week
     * Returns ['start' => '09:00', 'end' => '18:00'] or null if not working
     */
    public function getWorkingHoursForDay(int $dayOfWeek): ?array
    {
        $daySchedule = $this->getScheduleForDay($dayOfWeek);

        if (!$daySchedule || !($daySchedule['working'] ?? false)) {
            return null;
        }

        return [
            'start' => $daySchedule['start'] ?? null,
            'end' => $daySchedule['end'] ?? null,
        ];
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @throws Exception
     */
    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updated_at = DateTimeHelper::now();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('График за %s',
            $this->barber?->getEmail() ?? 'N/A'
        );
    }
}
