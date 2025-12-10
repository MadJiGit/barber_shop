<?php

namespace App\Entity;

use App\Repository\ProcedureRepository;
use App\Service\DateTimeHelper;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProcedureRepository::class)]
#[ORM\Table(name: '`procedures`')]
#[ORM\HasLifecycleCallbacks]
class Procedure
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
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
    private ?DateTimeInterface $date_added = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeInterface $date_last_update = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeInterface $date_stopped = null;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->date_added = DateTimeHelper::now();
    }

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

    public function getDateAdded(): ?DateTimeInterface
    {
        return $this->date_added;
    }

    /**
     * @throws Exception
     */
    public function setDateAdded(?DateTimeInterface $date_added = null): static
    {
        if (!$date_added) {
            $this->date_added = DateTimeHelper::now();
        } else {
            $this->date_added = $date_added;
        }

        return $this;
    }

    public function getDateLastUpdate(): ?DateTimeInterface
    {
        return $this->date_last_update;
    }

    /**
     * @throws Exception
     */
    public function setDateLastUpdate(?DateTimeInterface $date_last_update = null): static
    {
        if (!$date_last_update) {
            $this->date_last_update = DateTimeHelper::now();
        } else {
            $this->date_last_update = $date_last_update;
        }

        return $this;
    }

    public function getDateStopped(): ?DateTimeInterface
    {
        return $this->date_stopped;
    }

    public function setDateStopped(?DateTimeInterface $date_stopped = null): static
    {
        $this->date_stopped = $date_stopped;

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

    /**
     * @throws Exception
     */
    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->date_last_update = DateTimeHelper::now();
    }

    public function __toString(): string
    {
        return $this->type ?? '';
    }
}