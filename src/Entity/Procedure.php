<?php

namespace App\Entity;

use App\Repository\ProcedureRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProcedureRepository::class)]
#[ORM\Table(name: '`procedure`')]
class Procedure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $type = null;

    #[ORM\Column]
    private ?int $price_master = null;

    #[ORM\Column]
    private ?int $price_junior = null;

    #[ORM\Column]
    private ?int $duration_master = null;

    #[ORM\Column]
    private ?int $duration_junior = null;

    #[ORM\OneToOne(mappedBy: 'procedure_type', cascade: ['persist', 'remove'])]
    private ?Appointments $appointments = null;

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

    public function getPriceMaster(): ?int
    {
        return $this->price_master;
    }

    public function setPriceMaster(int $price_master): static
    {
        $this->price_master = $price_master;

        return $this;
    }

    public function getPriceJunior(): ?int
    {
        return $this->price_junior;
    }

    public function setPriceJunior(int $price_junior): static
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

    public function getAppointments(): ?Appointments
    {
        return $this->appointments;
    }

    public function setAppointments(Appointments $appointments): static
    {
        // set the owning side of the relation if necessary
        if ($appointments->getProcedureType() !== $this) {
            $appointments->setProcedureType($this);
        }

        $this->appointments = $appointments;

        return $this;
    }
}
