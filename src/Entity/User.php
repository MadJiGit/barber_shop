<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Service\DateTimeHelper;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
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
    #[ORM\Column(nullable: true)]
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

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private ?\DateTimeInterface $date_added = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_banned = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_last_update = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $is_active = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $is_banned = false;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $confirmation_token = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $token_expires_at = null;

    public function __construct()
    {
        $this->setDateAdded();
        $this->setDateLastUpdate();
        $this->setRoles();
    }

    public function isUserIsAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles());
    }

    public function isUserIsSuperAdmin(): bool
    {
        return in_array('ROLE_SUPER_ADMIN', $this->getRoles());
    }

    public function isClient(): bool
    {
        return in_array('ROLE_CLIENT', $this->getRoles());
    }

    public function isBarber(): bool
    {
        return in_array('ROLE_BARBER', $this->getRoles())
            || in_array('ROLE_BARBER_JUNIOR', $this->getRoles())
            || in_array('ROLE_BARBER_SENIOR', $this->getRoles());
    }

    public function isBarberSenior(): bool
    {
        return in_array('ROLE_BARBER_SENIOR', $this->getRoles());
    }

    public function isBarberJunior(): bool
    {
        return in_array('ROLE_BARBER_JUNIOR', $this->getRoles());
    }

    public function isManager(): bool
    {
        return in_array('ROLE_MANAGER', $this->getRoles());
    }

    public function isReceptionist(): bool
    {
        return in_array('ROLE_RECEPTIONIST', $this->getRoles());
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
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @see UserInterface
     */
    public function getRolesObjects(): array
    {
        return $this->roles;
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(?array $roles = null): static
    {
        if ($roles) {
            $this->addRole($roles);
        } else {
            $this->addRole(['ROLE_CLIENT']);
        }

        return $this;
    }

    private function addRole(array $role): void
    {
        $this->roles = $role;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
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

    public function setFirstName(?string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(?string $last_name): static
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getNickName(): ?string
    {
        return $this->nick_name;
    }

    public function setNickName(?string $nick_name): static
    {
        $this->nick_name = $nick_name;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getDateAdded(): ?\DateTimeInterface
    {
        return $this->date_added;
    }

    public function setDateAdded(?\DateTimeInterface $date = null): static
    {
        $this->date_added = DateTimeHelper::now();

        return $this;
    }

    public function getDateBanned(): ?\DateTimeInterface
    {
        return $this->date_banned;
    }

    public function setDateBanned(?\DateTimeInterface $date_banned = null): static
    {
        $this->date_banned = $date_banned;

        return $this;
    }

    public function getDateLastUpdate(): ?\DateTimeInterface
    {
        return $this->date_last_update;
    }

    public function setDateLastUpdate(?\DateTimeInterface $date_last_update = null): static
    {
        if ($date_last_update) {
            $this->date_last_update = DateTimeHelper::now();
        } else {
            $this->date_last_update = $date_last_update;
        }

        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getIsBanned(): bool
    {
        return $this->is_banned;
    }

    public function setIsBanned(bool $is_banned): static
    {
        $this->is_banned = $is_banned;
        if ($is_banned) {
            $this->date_banned = DateTimeHelper::now();
        }

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

    public function getTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->token_expires_at;
    }

    public function setTokenExpiresAt(?\DateTimeInterface $token_expires_at): static
    {
        $this->token_expires_at = $token_expires_at;

        return $this;
    }

    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return true;
        }

        return $this->token_expires_at < DateTimeHelper::now();
    }

    /**
     * Get barber title in Bulgarian based on role.
     */
    public function getBarberTitleBg(): string
    {
        if ($this->isBarberSenior()) {
            return 'Старши Бръснар';
        }
        if ($this->isBarberJunior()) {
            return 'Младши Бръснар';
        }
        if ($this->isBarber()) {
            return 'Бръснар';
        }

        return '';
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->date_last_update = DateTimeHelper::now();
    }

    public function __toString(): string
    {
        return $this->email ?? '';
    }
}
