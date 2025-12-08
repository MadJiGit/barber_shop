<?php

namespace App\Tests\Service;

use App\Entity\Appointments;
use App\Entity\User;
use App\Service\EmailService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;

class EmailServiceTest extends TestCase
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;
    private EmailService $emailService;

    protected function setUp(): void
    {
        // Create mocks
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        // Create EmailService with test credentials
        $this->emailService = new EmailService(
            $this->mailer,
            $this->logger,
            'noreply@barbershop.test',
            'Barber Shop Test'
        );
    }

    // ========================================
    // sendAppointmentConfirmation() Tests
    // ========================================

    /**
     * Test: Appointment confirmation email is sent successfully.
     */
    public function testSendAppointmentConfirmationSuccess(): void
    {
        // Arrange
        $client = $this->createMock(User::class);
        $client->method('getEmail')->willReturn('client@test.com');
        $client->method('getFirstName')->willReturn('Ivan');

        $barber = $this->createMock(User::class);
        $barber->method('getFirstName')->willReturn('Pesho');

        $appointment = $this->createMock(Appointments::class);
        $appointment->method('getId')->willReturn(123);
        $appointment->method('getClient')->willReturn($client);
        $appointment->method('getBarber')->willReturn($barber);

        // Expect mailer to be called once
        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (TemplatedEmail $email) {
                // Verify email properties
                $this->assertEquals('noreply@barbershop.test', $email->getFrom()[0]->getAddress());
                $this->assertEquals('client@test.com', $email->getTo()[0]->getAddress());
                $this->assertEquals('Потвърждение за запазен час', $email->getSubject());
                $this->assertEquals('email/appointment_confirmation.html.twig', $email->getHtmlTemplate());
                
                // Verify context
                $context = $email->getContext();
                $this->assertArrayHasKey('appointment', $context);
                $this->assertArrayHasKey('client', $context);
                $this->assertArrayHasKey('barber', $context);
                
                return true;
            }));

        // Expect logger to log success
        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Appointment confirmation email sent', ['appointment_id' => 123, 'client_email' => 'client@test.com']);

        // Act
        $result = $this->emailService->sendAppointmentConfirmation($appointment);

        // Assert
        $this->assertTrue($result, 'sendAppointmentConfirmation should return true on success');
    }

    /**
     * Test: Appointment confirmation email fails gracefully.
     */
    public function testSendAppointmentConfirmationFailsGracefully(): void
    {
        // Arrange
        $client = $this->createMock(User::class);
        $client->method('getEmail')->willReturn('client@test.com');
        $client->method('getFirstName')->willReturn('Ivan');

        $barber = $this->createMock(User::class);

        $appointment = $this->createMock(Appointments::class);
        $appointment->method('getId')->willReturn(456);
        $appointment->method('getClient')->willReturn($client);
        $appointment->method('getBarber')->willReturn($barber);

        // Mock mailer to throw exception
        $this->mailer
            ->method('send')
            ->willThrowException(new TransportException('SMTP connection failed'));

        // Expect logger to log error
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Failed to send appointment confirmation email',
                $this->callback(function ($context) {
                    return $context['appointment_id'] === 456 
                        && str_contains($context['error'], 'SMTP connection failed');
                })
            );

        // Act
        $result = $this->emailService->sendAppointmentConfirmation($appointment);

        // Assert
        $this->assertFalse($result, 'sendAppointmentConfirmation should return false on failure');
    }

    // ========================================
    // sendAppointmentCancellation() Tests
    // ========================================

    /**
     * Test: Cancellation email with cancelledBy parameter.
     */
    public function testSendAppointmentCancellationWithCancelledBy(): void
    {
        // Arrange
        $client = $this->createMock(User::class);
        $client->method('getEmail')->willReturn('client@test.com');
        $client->method('getFirstName')->willReturn('Maria');

        $barber = $this->createMock(User::class);

        $appointment = $this->createMock(Appointments::class);
        $appointment->method('getId')->willReturn(789);
        $appointment->method('getClient')->willReturn($client);
        $appointment->method('getBarber')->willReturn($barber);

        // Verify cancelledBy is passed to context
        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (TemplatedEmail $email) {
                $context = $email->getContext();
                $this->assertEquals('barber', $context['cancelledBy']);
                $this->assertEquals('Вашият час е отменен', $email->getSubject());
                $this->assertEquals('email/appointment_cancellation.html.twig', $email->getHtmlTemplate());
                return true;
            }));

        // Act
        $result = $this->emailService->sendAppointmentCancellation($appointment, 'barber');

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test: Cancellation email with different cancelledBy values.
     */
    public function testSendAppointmentCancellationDifferentCancelledBy(): void
    {
        $client = $this->createMock(User::class);
        $client->method('getEmail')->willReturn('client@test.com');

        $barber = $this->createMock(User::class);

        $appointment = $this->createMock(Appointments::class);
        $appointment->method('getId')->willReturn(100);
        $appointment->method('getClient')->willReturn($client);
        $appointment->method('getBarber')->willReturn($barber);

        // Test each cancelledBy option
        $cancelledByOptions = ['client', 'barber', 'manager', 'admin'];

        foreach ($cancelledByOptions as $cancelledBy) {
            $this->mailer
                ->expects($this->once())
                ->method('send')
                ->with($this->callback(function (TemplatedEmail $email) use ($cancelledBy) {
                    $context = $email->getContext();
                    $this->assertEquals($cancelledBy, $context['cancelledBy']);
                    return true;
                }));

            $result = $this->emailService->sendAppointmentCancellation($appointment, $cancelledBy);
            $this->assertTrue($result);

            // Reset mock expectations for next iteration
            $this->setUp();
        }
    }

    // ========================================
    // sendWelcomeEmail() Tests
    // ========================================

    /**
     * Test: Welcome email is sent successfully.
     */
    public function testSendWelcomeEmailSuccess(): void
    {
        // Arrange
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('newuser@test.com');
        $user->method('getFirstName')->willReturn('Georgi');

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (TemplatedEmail $email) {
                $this->assertEquals('Добре дошли в Barber Shop!', $email->getSubject());
                $this->assertEquals('email/welcome.html.twig', $email->getHtmlTemplate());
                $this->assertEquals('newuser@test.com', $email->getTo()[0]->getAddress());
                return true;
            }));

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Welcome email sent', ['email' => 'newuser@test.com']);

        // Act
        $result = $this->emailService->sendWelcomeEmail($user);

        // Assert
        $this->assertTrue($result);
    }

    // ========================================
    // sendPasswordResetEmail() Tests
    // ========================================

    /**
     * Test: Password reset email with token.
     */
    public function testSendPasswordResetEmailWithToken(): void
    {
        // Arrange
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('user@test.com');
        $user->method('getFirstName')->willReturn('Stoyan');

        $resetToken = 'abc123xyz789';

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (TemplatedEmail $email) use ($resetToken) {
                $this->assertEquals('Възстановяване на парола', $email->getSubject());
                $this->assertEquals('email/password_reset.html.twig', $email->getHtmlTemplate());
                
                $context = $email->getContext();
                $this->assertArrayHasKey('resetToken', $context);
                $this->assertEquals($resetToken, $context['resetToken']);
                
                return true;
            }));

        // Act
        $result = $this->emailService->sendPasswordResetEmail($user, $resetToken);

        // Assert
        $this->assertTrue($result);
    }

    // ========================================
    // sendVerificationEmail() Tests
    // ========================================

    /**
     * Test: Verification email with token.
     */
    public function testSendVerificationEmailWithToken(): void
    {
        // Arrange
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('verify@test.com');
        $user->method('getFirstName')->willReturn('Dimitar');

        $verificationToken = 'verify123token456';

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (TemplatedEmail $email) use ($verificationToken) {
                $this->assertEquals('Потвърдете вашия имейл адрес', $email->getSubject());
                $this->assertEquals('email/verification.html.twig', $email->getHtmlTemplate());
                
                $context = $email->getContext();
                $this->assertEquals($verificationToken, $context['verificationToken']);
                
                return true;
            }));

        // Act
        $result = $this->emailService->sendVerificationEmail($user, $verificationToken);

        // Assert
        $this->assertTrue($result);
    }

    // ========================================
    // sendPasswordChangeConfirmation() Tests
    // ========================================

    /**
     * Test: Password change confirmation email.
     */
    public function testSendPasswordChangeConfirmation(): void
    {
        // Arrange
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('change@test.com');
        $user->method('getFirstName')->willReturn('Nikolay');

        $changeToken = 'change_token_123';
        $newPasswordHash = 'hashed_password_abc';

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (TemplatedEmail $email) use ($changeToken) {
                $this->assertEquals('Потвърдете смяната на парола', $email->getSubject());
                $this->assertEquals('email/password_change_confirmation.html.twig', $email->getHtmlTemplate());
                
                $context = $email->getContext();
                $this->assertEquals($changeToken, $context['changeToken']);
                
                return true;
            }));

        // Act
        $result = $this->emailService->sendPasswordChangeConfirmation($user, $changeToken, $newPasswordHash);

        // Assert
        $this->assertTrue($result);
    }

    // ========================================
    // sendPasswordChangedNotification() Tests
    // ========================================

    /**
     * Test: Password changed notification.
     */
    public function testSendPasswordChangedNotification(): void
    {
        // Arrange
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('changed@test.com');
        $user->method('getFirstName')->willReturn('Plamen');

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (TemplatedEmail $email) {
                $this->assertEquals('Вашата парола беше променена', $email->getSubject());
                $this->assertEquals('email/password_changed_notification.html.twig', $email->getHtmlTemplate());
                return true;
            }));

        // Act
        $result = $this->emailService->sendPasswordChangedNotification($user);

        // Assert
        $this->assertTrue($result);
    }

    // ========================================
    // sendAppointmentReminder() Tests
    // ========================================

    /**
     * Test: Appointment reminder email.
     */
    public function testSendAppointmentReminder(): void
    {
        // Arrange
        $client = $this->createMock(User::class);
        $client->method('getEmail')->willReturn('reminder@test.com');
        $client->method('getFirstName')->willReturn('Stefan');

        $barber = $this->createMock(User::class);

        $appointment = $this->createMock(Appointments::class);
        $appointment->method('getId')->willReturn(999);
        $appointment->method('getClient')->willReturn($client);
        $appointment->method('getBarber')->willReturn($barber);

        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (TemplatedEmail $email) {
                $this->assertEquals('Напомняне за вашия час утре', $email->getSubject());
                $this->assertEquals('email/appointment_reminder.html.twig', $email->getHtmlTemplate());
                return true;
            }));

        // Act
        $result = $this->emailService->sendAppointmentReminder($appointment);

        // Assert
        $this->assertTrue($result);
    }
}
