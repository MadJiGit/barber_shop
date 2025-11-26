<?php

namespace App\Entity;

use App\Repository\ProcedureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProcedureRepository::class)]
#[ORM\Table(name: '`procedure`')]
class Procedure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\Length(min: 5, minMessage: 'Your procedure name must be at least {{ limit }} characters long', )]
    private ?string $type = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price_master = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price_junior = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $duration_master = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $duration_junior = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $available = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $date_added = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $date_last_update = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $date_stopped = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getPriceMaster(): ?float
    {
        return $this->price_master;
    }

    public function setPriceMaster(float $price_master): static
    {
        $this->price_master = $price_master;

        return $this;
    }

    public function getPriceJunior(): ?float
    {
        return $this->price_junior;
    }

    public function setPriceJunior(float $price_junior): static
    {
        $this->price_junior = $price_junior;

        return $this;
    }

    public function getDurationMaster(): ?int
    {
        return $this->duration_master;
    }

    public function setDurationMaster(int $duration_master): static
    {
        $this->duration_master = $duration_master;

        return $this;
    }

    public function getDurationJunior(): ?int
    {
        return $this->duration_junior;
    }

    public function setDurationJunior(int $duration_junior): static
    {
        $this->duration_junior = $duration_junior;

        return $this;
    }

    public function getDateAdded(): ?string
    {
        return null == $this->date_added ? '' : $this->date_added->format('d-M-Y H:i:s');
    }

    public function setDateAdded(): static
    {
        $this->date_added = new \DateTime('now');

        return $this;
    }

    public function getDateLastUpdate(): ?string
    {
        return null == $this->date_last_update ? '' : $this->date_last_update->format('d-M-Y H:i:s');
    }

    public function getDateStopped(): ?string
    {
        return null == $this->date_stopped ? '' : $this->date_stopped->format('d-M-Y H:i:s');
    }

    public function setDateStopped(?\DateTimeInterface $date_stopped = null): static
    {
        $this->date_stopped = $date_stopped;

        return $this;
    }

    public function setDateLastUpdate(?\DateTimeInterface $date_last_update = null): static
    {
        if (!$date_last_update) {
            $this->date_last_update = new \DateTime('now');
        } else {
            $this->date_last_update = $date_last_update;
        }

        return $this;
    }

    public function getAvailable(): ?bool
    {
        return $this->available;
    }

    public function setAvailable(?bool $available): void
    {
        $this->available = $available;
    }
}