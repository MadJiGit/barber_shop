<?php

namespace App\Entity;

use App\Repository\BusinessHoursRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BusinessHoursRepository::class)]
#[ORM\Table(name: 'business_hours')]
class BusinessHours
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $day_of_week; // 0 = Sunday, 1 = Monday, ..., 6 = Saturday

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $open_time = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $close_time = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $is_closed = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDayOfWeek(): int
    {
        return $this->day_of_week;
    }

    public function setDayOfWeek(int $day_of_week): static
    {
        if ($day_of_week < 0 || $day_of_week > 6) {
            throw new \InvalidArgumentException('Day of week must be between 0 and 6');
        }
        $this->day_of_week = $day_of_week;

        return $this;
    }

    public function getDayName(): string
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return $days[$this->day_of_week];
    }

    public function getOpenTime(): ?\DateTimeInterface
    {
        return $this->open_time;
    }

    public function setOpenTime(?\DateTimeInterface $open_time): static
    {
        $this->open_time = $open_time;

        return $this;
    }

    public function getCloseTime(): ?\DateTimeInterface
    {
        return $this->close_time;
    }

    public function setCloseTime(?\DateTimeInterface $close_time): static
    {
        $this->close_time = $close_time;

        return $this;
    }

    public function getIsClosed(): bool
    {
        return $this->is_closed;
    }

    public function setIsClosed(bool $is_closed): static
    {
        $this->is_closed = $is_closed;

        return $this;
    }

    public function isOpenOn(\DateTimeInterface $date): bool
    {
        $dayOfWeek = (int) $date->format('w');
        return $dayOfWeek === $this->day_of_week && !$this->is_closed;
    }
}