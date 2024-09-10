<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var ?string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(min: 3, minMessage: 'Your first name must be at least {{ limit }} characters long', )]
    private ?string $first_name = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(min: 3, minMessage: 'Your first name must be at least {{ limit }} characters long', )]
    private ?string $last_name = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(min: 3, minMessage: 'Your first name must be at least {{ limit }} characters long', )]
    private ?string $nick_name = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Regex(
        pattern: '/\w/',
        message: 'Your phone cannot contain a letter',
        match: true, )]
    private ?string $phone = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_added = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_banned = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_last_update = null;

    #[ORM\OneToOne(mappedBy: 'barber', cascade: ['persist', 'remove'])]
    private ?Appointments $barber = null;

    #[ORM\OneToOne(mappedBy: 'client', cascade: ['persist', 'remove'])]
    private ?Appointments $client = null;

    public function __construct()
    {
        $this->setDateAdded();
    }

    public function isUserIsAdmin(): bool
    {
        //        dd(array_values($this->getRoles()));
        return in_array(Roles::ADMIN->value, $this->getRoles());
    }

    public function isUserIsSuperAdmin(): bool
    {
        return in_array(Roles::SUPER_ADMIN->value, $this->getRoles());
        //        return in_array(Roles::SUPER_ADMIN->value, $this->getRoles());
    }

    public function isClient(): bool
    {
        return in_array(Roles::CLIENT->value, $this->getRoles());
        //        return in_array(Roles::CLIENT->value, $this->getRoles());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): static
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getNickName(): ?string
    {
        return $this->nick_name;
    }

    public function setNickName(string $nick_name): static
    {
        $this->nick_name = $nick_name;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getDateAdded(): ?string
    {
        return $this->date_added->format('d-M-Y H:i:s');
    }

    public function setDateAdded(): static
    {
        $this->date_added = new \DateTime('now');

        return $this;
    }

    public function getDateBanned(): ?string
    {
        return $this->date_banned?->format('d-M-Y H:i:s');
    }

    public function setDateBanned(\DateTimeInterface $date_banned): static
    {
        $this->date_banned = $date_banned;

        return $this;
    }

    public function getDateLastUpdate(): ?string
    {
        return $this->date_last_update->format('d-M-Y H:i:s');
    }

    public function setDateLastUpdate(\DateTimeInterface $date_last_update): static
    {
        $this->date_last_update = $date_last_update;

        return $this;
    }

    public function getBarber(): ?Appointments
    {
        return $this->barber;
    }

    public function setBarber(Appointments $barber): static
    {
        // set the owning side of the relation if necessary
        if ($barber->getBarber() !== $this) {
            $barber->setBarber($this);
        }

        $this->barber = $barber;

        return $this;
    }

    public function getClient(): ?Appointments
    {
        return $this->client;
    }

    public function setClient(Appointments $client): static
    {
        // set the owning side of the relation if necessary
        if ($client->getClient() !== $this) {
            $client->setClient($this);
        }

        $this->client = $client;

        return $this;
    }
}

/*
 *             $user->setFirstName($form->get('first_name')->getData());
            $user->setLastName($form->get('last_name')->getData());
            $user->setNickName($form->get('nick_name')->getData());
            $user->setPhone($form->get('phone')->getData());
            $user->setDateLastUpdate(new \DateTime('now'));
 */
