<?php

namespace App\Entity;

use App\Repository\BarberProcedureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BarberProcedureRepository::class)]
#[ORM\Table(name: 'barber_procedure')]
#[ORM\Index(name: 'idx_barber_procedure', columns: ['barber_id', 'procedure_id'])]
class BarberProcedure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $barber = null;

    #[ORM\ManyToOne(targetEntity: Procedure::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Procedure $procedure = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $can_perform = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $valid_from = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $valid_until = null;

    public function __construct()
    {
        $this->valid_from = new \DateTimeImmutable('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBarber(): ?User
    {
        return $this->barber;
    }

    public function setBarber(?User $barber): static
    {
        $this->barber = $barber;

        return $this;
    }

    public function getProcedure(): ?Procedure
    {
        return $this->procedure;
    }

    public function setProcedure(?Procedure $procedure): static
    {
        $this->procedure = $procedure;

        return $this;
    }

    public function getCanPerform(): bool
    {
        return $this->can_perform;
    }

    public function setCanPerform(bool $can_perform): static
    {
        $this->can_perform = $can_perform;

        return $this;
    }

    public function getValidFrom(): ?\DateTimeImmutable
    {
        return $this->valid_from;
    }

    public function setValidFrom(\DateTimeImmutable $valid_from): static
    {
        $this->valid_from = $valid_from;

        return $this;
    }

    public function getValidUntil(): ?\DateTimeImmutable
    {
        return $this->valid_until;
    }

    public function setValidUntil(?\DateTimeImmutable $valid_until): static
    {
        $this->valid_until = $valid_until;

        return $this;
    }

    /**
     * Check if this barber-procedure mapping is currently valid
     */
    public function isCurrentlyValid(): bool
    {
        $now = new \DateTimeImmutable('now');

        if (!$this->can_perform) {
            return false;
        }

        if ($this->valid_from > $now) {
            return false;
        }

        if ($this->valid_until !== null && $this->valid_until < $now) {
            return false;
        }

        return true;
    }
}
