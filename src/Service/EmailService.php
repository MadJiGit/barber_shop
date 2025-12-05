<?php

namespace App\Service;

use App\Entity\Appointments;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;

/**
 * Email Service
 * Handles sending all types of emails in the application
 */
class EmailService
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;
    private string $senderEmail;
    private string $senderName;

    public function __construct(
        MailerInterface $mailer,
        LoggerInterface $logger,
        string $senderEmail,
        string $senderName
    ) {
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
    }

    /**
     * Send email verification link
     */
    public function sendVerificationEmail(User $user, string $verificationToken): bool
    {
        try {
            $email = (new TemplatedEmail())
                ->from(new Address($this->senderEmail, $this->senderName))
                ->to(new Address($user->getEmail(), $user->getFirstName() ?: ''))
                ->subject('Потвърдете вашия имейл адрес')
                ->htmlTemplate('email/verification.html.twig')
                ->context([
                    'user' => $user,
                    'verificationToken' => $verificationToken,
                ]);

            $this->mailer->send($email);
            $this->logger->info('Verification email sent', ['email' => $user->getEmail()]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to send verification email', [
                'email' => $user->getEmail(),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send welcome email after successful registration
     * @throws TransportExceptionInterface
     */
    public function sendWelcomeEmail(User $user): bool
    {
        try {
            $email = (new TemplatedEmail())
                ->from(new Address($this->senderEmail, $this->senderName))
                ->to(new Address($user->getEmail(), $user->getFirstName() ?: ''))
                ->subject('Добре дошли в Barber Shop!')
                ->htmlTemplate('email/welcome.html.twig')
                ->context([
                    'user' => $user,
                ]);

            $this->mailer->send($email);
            $this->logger->info('Welcome email sent', ['email' => $user->getEmail()]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to send welcome email', [
                'email' => $user->getEmail(),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send appointment confirmation email
     * @throws TransportExceptionInterface
     */
    public function sendAppointmentConfirmation(Appointments $appointment): bool
    {
        try {
            $client = $appointment->getClient();
            $barber = $appointment->getBarber();

            $email = (new TemplatedEmail())
                ->from(new Address($this->senderEmail, $this->senderName))
                ->to(new Address($client->getEmail(), $client->getFirstName() ?: ''))
                ->subject('Потвърждение за запазен час')
                ->htmlTemplate('email/appointment_confirmation.html.twig')
                ->context([
                    'appointment' => $appointment,
                    'client' => $client,
                    'barber' => $barber,
                ]);

            $this->mailer->send($email);
            $this->logger->info('Appointment confirmation email sent', [
                'appointment_id' => $appointment->getId(),
                'client_email' => $client->getEmail()
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to send appointment confirmation email', [
                'appointment_id' => $appointment->getId(),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send appointment cancellation email
     * @throws TransportExceptionInterface
     */
    public function sendAppointmentCancellation(Appointments $appointment, string $cancelledBy = 'client'): bool
    {
        try {
            $client = $appointment->getClient();
            $barber = $appointment->getBarber();

            $email = (new TemplatedEmail())
                ->from(new Address($this->senderEmail, $this->senderName))
                ->to(new Address($client->getEmail(), $client->getFirstName() ?: ''))
                ->subject('Вашият час е отменен')
                ->htmlTemplate('email/appointment_cancellation.html.twig')
                ->context([
                    'appointment' => $appointment,
                    'client' => $client,
                    'barber' => $barber,
                    'cancelledBy' => $cancelledBy,
                ]);

            $this->mailer->send($email);
            $this->logger->info('Appointment cancellation email sent', [
                'appointment_id' => $appointment->getId(),
                'client_email' => $client->getEmail(),
                'cancelled_by' => $cancelledBy
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to send appointment cancellation email', [
                'appointment_id' => $appointment->getId(),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send appointment reminder email (24 hours before)
     * @throws TransportExceptionInterface
     */
    public function sendAppointmentReminder(Appointments $appointment): bool
    {
        try {
            $client = $appointment->getClient();
            $barber = $appointment->getBarber();

            $email = (new TemplatedEmail())
                ->from(new Address($this->senderEmail, $this->senderName))
                ->to(new Address($client->getEmail(), $client->getFirstName() ?: ''))
                ->subject('Напомняне за вашия час утре')
                ->htmlTemplate('email/appointment_reminder.html.twig')
                ->context([
                    'appointment' => $appointment,
                    'client' => $client,
                    'barber' => $barber,
                ]);

            $this->mailer->send($email);
            $this->logger->info('Appointment reminder email sent', [
                'appointment_id' => $appointment->getId(),
                'client_email' => $client->getEmail()
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to send appointment reminder email', [
                'appointment_id' => $appointment->getId(),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send password reset email
     * @throws TransportExceptionInterface
     */
    public function sendPasswordResetEmail(User $user, string $resetToken): bool
    {
        try {
            $email = (new TemplatedEmail())
                ->from(new Address($this->senderEmail, $this->senderName))
                ->to(new Address($user->getEmail(), $user->getFirstName() ?: ''))
                ->subject('Възстановяване на парола')
                ->htmlTemplate('email/password_reset.html.twig')
                ->context([
                    'user' => $user,
                    'resetToken' => $resetToken,
                ]);

            $this->mailer->send($email);
            $this->logger->info('Password reset email sent', ['email' => $user->getEmail()]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to send password reset email', [
                'email' => $user->getEmail(),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send password change confirmation email (with token link)
     * @throws TransportExceptionInterface
     */
    public function sendPasswordChangeConfirmation(User $user, string $changeToken, string $newPasswordHash): bool
    {
        try {
            $email = (new TemplatedEmail())
                ->from(new Address($this->senderEmail, $this->senderName))
                ->to(new Address($user->getEmail(), $user->getFirstName() ?: ''))
                ->subject('Потвърдете смяната на парола')
                ->htmlTemplate('email/password_change_confirmation.html.twig')
                ->context([
                    'user' => $user,
                    'changeToken' => $changeToken,
                ]);

            $this->mailer->send($email);
            $this->logger->info('Password change confirmation email sent', ['email' => $user->getEmail()]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to send password change confirmation email', [
                'email' => $user->getEmail(),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send password changed notification email (after successful change)
     * @throws TransportExceptionInterface
     */
    public function sendPasswordChangedNotification(User $user): bool
    {
        try {
            $email = (new TemplatedEmail())
                ->from(new Address($this->senderEmail, $this->senderName))
                ->to(new Address($user->getEmail(), $user->getFirstName() ?: ''))
                ->subject('Вашата парола беше променена')
                ->htmlTemplate('email/password_changed_notification.html.twig')
                ->context([
                    'user' => $user,
                ]);

            $this->mailer->send($email);
            $this->logger->info('Password changed notification email sent', ['email' => $user->getEmail()]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to send password changed notification email', [
                'email' => $user->getEmail(),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
