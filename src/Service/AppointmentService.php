<?php

namespace App\Service;

use App\Entity\Appointments;
use App\Entity\Procedure;
use App\Entity\User;
use App\Enum\AppointmentStatus;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AppointmentService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EmailService $emailService,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * Generate a unique confirmation token for appointment.
     *
     * @throws RandomException
     */
    public function generateConfirmationToken(): string
    {
        return bin2hex(random_bytes(32)); // 64-character hex string
    }

    /**
     * Create a guest user for appointment booking.
     */
    public function createGuestUser(
        string $email,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $phone = null,
    ): User {
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setPhone($phone);
        $user->setIsActive(false); // Guest user is not active
        $user->setRoles(['ROLE_CLIENT']); // Default role
        // No password for guest users

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * Create appointment with confirmation token for guest users.
     *
     * @throws RandomException
     */
    public function createGuestAppointment(
        User $client,
        User $barber,
        Procedure $procedure,
        DateTimeInterface $date,
        int $duration,
    ): Appointments {
        $appointment = new Appointments();
        $appointment->setClient($client);
        $appointment->setBarber($barber);
        $appointment->setProcedureType($procedure);
        $appointment->setDate($date);
        $appointment->setDuration($duration);

        // Set the status to pending_confirmation for guest users
        $appointment->setStatus(AppointmentStatus::PENDING_CONFIRMATION);

        // Generate a confirmation token
        $confirmationToken = $this->generateConfirmationToken();
        $appointment->setConfirmationToken($confirmationToken);

        $this->entityManager->persist($appointment);
        $this->entityManager->flush();

        return $appointment;
    }

    /**
     * Create an appointment for registered users (no confirmation needed).
     */
    public function createRegisteredUserAppointment(
        User $client,
        User $barber,
        Procedure $procedure,
        DateTimeInterface $date,
        int $duration,
    ): Appointments {
        $appointment = new Appointments();
        $appointment->setClient($client);
        $appointment->setBarber($barber);
        $appointment->setProcedureType($procedure);
        $appointment->setDate($date);
        $appointment->setDuration($duration);

        // Registered users get confirmed status immediately
        $appointment->setStatus(AppointmentStatus::CONFIRMED);
        $appointment->setConfirmedAt(DateTimeHelper::now());

        $this->entityManager->persist($appointment);
        $this->entityManager->flush();

        return $appointment;
    }

    /**
     * Confirm appointment using token.
     */
    public function confirmAppointment(string $token): ?Appointments
    {
        $appointment = $this->entityManager->getRepository(Appointments::class)
            ->findOneBy(['confirmation_token' => $token]);

        if (!$appointment) {
            return null;
        }

        // Check if already confirmed
        if (AppointmentStatus::CONFIRMED === $appointment->getStatus()) {
            return $appointment;
        }

        // Confirm appointment
        $appointment->setStatus(AppointmentStatus::CONFIRMED);
        $appointment->setConfirmedAt(DateTimeHelper::now());
        $appointment->setConfirmationToken(null);

        $this->entityManager->flush();

        return $appointment;
    }

    /**
     * Cancel appointment using token.
     */
    public function cancelAppointmentByToken(string $token, ?string $reason = null): ?Appointments
    {
        $appointment = $this->entityManager->getRepository(Appointments::class)
            ->findOneBy(['confirmation_token' => $token]);

        if (!$appointment) {
            return null;
        }

        // Check if already canceled
        if (AppointmentStatus::CANCELLED === $appointment->getStatus()) {
            return $appointment;
        }

        // Cancel appointment
        $appointment->setStatus(AppointmentStatus::CANCELLED);
        $appointment->setDateCanceled(DateTimeHelper::now());

        if ($reason) {
            $appointment->setCancellationReason($reason);
        }

        $this->entityManager->flush();

        return $appointment;
    }

    /**
     * Send confirmation email to guest user.
     */
    public function sendGuestConfirmationEmail(Appointments $appointment): bool
    {
        $token = $appointment->getConfirmationToken();

        if (!$token) {
            return false;
        }

        // Generate confirmation and cancel URLs
        $confirmUrl = $this->urlGenerator->generate(
            'appointment_guest_confirm',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $cancelUrl = $this->urlGenerator->generate(
            'appointment_guest_cancel',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Send email using EmailService
        return $this->emailService->sendGuestAppointmentConfirmation($appointment, $confirmUrl, $cancelUrl);
    }
}
