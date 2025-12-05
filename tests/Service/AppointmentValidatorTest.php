<?php

namespace App\Tests\Service;

use App\Entity\Appointments;
use App\Entity\User;
use App\Repository\AppointmentsRepository;
use App\Service\AppointmentValidator;
use App\Service\BarberScheduleService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class AppointmentValidatorTest extends TestCase
{
    private AppointmentsRepository $appointmentsRepository;
    private BarberScheduleService $scheduleService;
    private AppointmentValidator $validator;

    protected function setUp(): void
    {
        // Create mocks for dependencies
        $this->appointmentsRepository = $this->createMock(AppointmentsRepository::class);
        $this->scheduleService = $this->createMock(BarberScheduleService::class);

        // Create the service we're testing
        $this->validator = new AppointmentValidator(
            $this->appointmentsRepository,
            $this->scheduleService
        );
    }

    /**
     * Test: Barber is available when there are no appointments.
     */
    public function testIsBarberAvailableNoAppointmentsReturnsTrue(): void
    {
        // Arrange: Create test data
        $barber = $this->createMock(User::class);
        $startTime = new DateTimeImmutable('2025-12-10 10:00:00');
        $duration = 60;

        // Mock repository to return empty array (no appointments)
        $this->appointmentsRepository
            ->expects($this->once())
            ->method('findByBarberAndDate')
            ->with($barber, $startTime)
            ->willReturn([]);

        // Act: Call the method we're testing
        $result = $this->validator->isBarberAvailable($barber, $startTime, $duration);

        // Assert: Check the result
        $this->assertTrue($result, 'Barber should be available when there are no appointments');
    }

    /**
     * Test: Barber is NOT available when there's a conflicting appointment.
     */
    public function testIsBarberAvailableConflictingAppointmentReturnsFalse(): void
    {
        // Arrange
        $barber = $this->createMock(User::class);
        $startTime = new DateTimeImmutable('2025-12-10 10:00:00');
        $duration = 60; // 10:00 - 11:00

        // Create a mock appointment that conflicts (10:30 - 11:30)
        $existingAppointment = $this->createMock(Appointments::class);
        $existingAppointment
            ->method('getId')
            ->willReturn(1);
        $existingAppointment
            ->method('getDate')
            ->willReturn(new DateTimeImmutable('2025-12-10 10:30:00'));
        $existingAppointment
            ->method('getDuration')
            ->willReturn(60);

        // Mock repository to return conflicting appointment
        $this->appointmentsRepository
            ->expects($this->once())
            ->method('findByBarberAndDate')
            ->with($barber, $startTime)
            ->willReturn([$existingAppointment]);

        // Act
        $result = $this->validator->isBarberAvailable($barber, $startTime, $duration);

        // Assert
        $this->assertFalse($result, 'Barber should NOT be available when there is a conflicting appointment');
    }

    /**
     * Test: Barber is available when appointments don't overlap.
     */
    public function testIsBarberAvailableNoOverlapReturnsTrue(): void
    {
        // Arrange
        $barber = $this->createMock(User::class);
        $startTime = new DateTimeImmutable('2025-12-10 10:00:00');
        $duration = 60; // 10:00 - 11:00

        // Create appointment that ends exactly when new one starts (11:00 - 12:00)
        $existingAppointment = $this->createMock(Appointments::class);
        $existingAppointment
            ->method('getId')
            ->willReturn(1);
        $existingAppointment
            ->method('getDate')
            ->willReturn(new DateTimeImmutable('2025-12-10 11:00:00'));
        $existingAppointment
            ->method('getDuration')
            ->willReturn(60);

        $this->appointmentsRepository
            ->method('findByBarberAndDate')
            ->willReturn([$existingAppointment]);

        // Act
        $result = $this->validator->isBarberAvailable($barber, $startTime, $duration);

        // Assert
        $this->assertTrue($result, 'Barber should be available when appointments are consecutive (no overlap)');
    }

    /**
     * Test: Exclude appointment works correctly.
     */
    public function testIsBarberAvailableExcludeAppointmentReturnsTrue(): void
    {
        // Arrange
        $barber = $this->createMock(User::class);
        $startTime = new DateTimeImmutable('2025-12-10 10:00:00');
        $duration = 60;

        // Create appointment at same time (would conflict)
        $existingAppointment = $this->createMock(Appointments::class);
        $existingAppointment
            ->method('getId')
            ->willReturn(123);
        $existingAppointment
            ->method('getDate')
            ->willReturn(new DateTimeImmutable('2025-12-10 10:00:00'));
        $existingAppointment
            ->method('getDuration')
            ->willReturn(60);

        $this->appointmentsRepository
            ->method('findByBarberAndDate')
            ->willReturn([$existingAppointment]);

        // Act: Pass the same appointment as excludeAppointment (updating scenario)
        $result = $this->validator->isBarberAvailable($barber, $startTime, $duration, $existingAppointment);

        // Assert: Should be available because we're excluding this appointment
        $this->assertTrue($result, 'Barber should be available when the conflicting appointment is excluded');
    }

    // ========================================
    // isClientAvailable() Tests
    // ========================================

    /**
     * Test: Client is available when there are no appointments.
     */
    public function testIsClientAvailableNoAppointmentsReturnsTrue(): void
    {
        // Arrange
        $client = $this->createMock(User::class);
        $startTime = new DateTimeImmutable('2025-12-10 14:00:00');
        $duration = 30;

        // Mock repository to return empty array
        $this->appointmentsRepository
            ->expects($this->once())
            ->method('findByClientAndDate')
            ->with($client, $startTime)
            ->willReturn([]);

        // Act
        $result = $this->validator->isClientAvailable($client, $startTime, $duration);

        // Assert
        $this->assertTrue($result, 'Client should be available when there are no appointments');
    }

    /**
     * Test: Client is NOT available when there's a conflicting appointment.
     */
    public function testIsClientAvailableConflictingAppointmentReturnsFalse(): void
    {
        // Arrange
        $client = $this->createMock(User::class);
        $startTime = new DateTimeImmutable('2025-12-10 14:00:00');
        $duration = 60; // 14:00 - 15:00

        // Client already has appointment 14:30 - 15:30 (conflicts)
        $existingAppointment = $this->createMock(Appointments::class);
        $existingAppointment
            ->method('getId')
            ->willReturn(2);
        $existingAppointment
            ->method('getDate')
            ->willReturn(new DateTimeImmutable('2025-12-10 14:30:00'));
        $existingAppointment
            ->method('getDuration')
            ->willReturn(60);

        $this->appointmentsRepository
            ->expects($this->once())
            ->method('findByClientAndDate')
            ->with($client, $startTime)
            ->willReturn([$existingAppointment]);

        // Act
        $result = $this->validator->isClientAvailable($client, $startTime, $duration);

        // Assert
        $this->assertFalse($result, 'Client should NOT be available when there is a conflicting appointment');
    }

    /**
     * Test: Client is available when appointments don't overlap.
     */
    public function testIsClientAvailableNoOverlapReturnsTrue(): void
    {
        // Arrange
        $client = $this->createMock(User::class);
        $startTime = new DateTimeImmutable('2025-12-10 14:00:00');
        $duration = 60; // 14:00 - 15:00

        // Client has appointment 15:00 - 16:00 (no overlap)
        $existingAppointment = $this->createMock(Appointments::class);
        $existingAppointment
            ->method('getId')
            ->willReturn(3);
        $existingAppointment
            ->method('getDate')
            ->willReturn(new DateTimeImmutable('2025-12-10 15:00:00'));
        $existingAppointment
            ->method('getDuration')
            ->willReturn(60);

        $this->appointmentsRepository
            ->method('findByClientAndDate')
            ->willReturn([$existingAppointment]);

        // Act
        $result = $this->validator->isClientAvailable($client, $startTime, $duration);

        // Assert
        $this->assertTrue($result, 'Client should be available when appointments are consecutive (no overlap)');
    }

    /**
     * Test: Exclude appointment works for client.
     */
    public function testIsClientAvailableExcludeAppointmentReturnsTrue(): void
    {
        // Arrange
        $client = $this->createMock(User::class);
        $startTime = new DateTimeImmutable('2025-12-10 14:00:00');
        $duration = 60;

        // Same appointment (updating scenario)
        $existingAppointment = $this->createMock(Appointments::class);
        $existingAppointment
            ->method('getId')
            ->willReturn(456);
        $existingAppointment
            ->method('getDate')
            ->willReturn(new DateTimeImmutable('2025-12-10 14:00:00'));
        $existingAppointment
            ->method('getDuration')
            ->willReturn(60);

        $this->appointmentsRepository
            ->method('findByClientAndDate')
            ->willReturn([$existingAppointment]);

        // Act: Exclude this appointment
        $result = $this->validator->isClientAvailable($client, $startTime, $duration, $existingAppointment);

        // Assert
        $this->assertTrue($result, 'Client should be available when the conflicting appointment is excluded');
    }

    // ========================================
    // isInPast() Tests
    // ========================================

    /**
     * Test: Appointment in the past returns true.
     */
    public function testIsInPastAppointmentInPastReturnsTrue(): void
    {
        // Arrange: Time clearly in the past
        $pastTime = new DateTimeImmutable('2020-01-01 10:00:00');

        // Act
        $result = $this->validator->isInPast($pastTime);

        // Assert
        $this->assertTrue($result, 'Appointment in the past should return true');
    }

    /**
     * Test: Appointment in the future returns false.
     */
    public function testIsInPastAppointmentInFutureReturnsFalse(): void
    {
        // Arrange: Time clearly in the future
        $futureTime = new DateTimeImmutable('2030-12-31 23:59:59');

        // Act
        $result = $this->validator->isInPast($futureTime);

        // Assert
        $this->assertFalse($result, 'Appointment in the future should return false');
    }

    /**
     * Test: Multiple barber appointments - check all are considered.
     */
    public function testIsBarberAvailableMultipleAppointmentsChecksAll(): void
    {
        // Arrange
        $barber = $this->createMock(User::class);
        $startTime = new DateTimeImmutable('2025-12-10 13:00:00');
        $duration = 60; // 13:00 - 14:00

        // Barber has 2 appointments: 10:00-11:00 (no conflict) and 13:30-14:30 (conflict!)
        $appointment1 = $this->createMock(Appointments::class);
        $appointment1->method('getId')->willReturn(10);
        $appointment1->method('getDate')->willReturn(new DateTimeImmutable('2025-12-10 10:00:00'));
        $appointment1->method('getDuration')->willReturn(60);

        $appointment2 = $this->createMock(Appointments::class);
        $appointment2->method('getId')->willReturn(11);
        $appointment2->method('getDate')->willReturn(new DateTimeImmutable('2025-12-10 13:30:00'));
        $appointment2->method('getDuration')->willReturn(60);

        $this->appointmentsRepository
            ->method('findByBarberAndDate')
            ->willReturn([$appointment1, $appointment2]);

        // Act
        $result = $this->validator->isBarberAvailable($barber, $startTime, $duration);

        // Assert: Should be false because appointment2 conflicts
        $this->assertFalse($result, 'Barber should NOT be available when any appointment conflicts');
    }

    /**
     * Test: Edge case - appointment ends exactly when barber starts working.
     */
    public function testIsBarberAvailableAppointmentBeforeNoOverlap(): void
    {
        // Arrange
        $barber = $this->createMock(User::class);
        $startTime = new DateTimeImmutable('2025-12-10 10:00:00');
        $duration = 60; // 10:00 - 11:00

        // Previous appointment: 09:00 - 10:00 (ends exactly when new starts)
        $existingAppointment = $this->createMock(Appointments::class);
        $existingAppointment->method('getId')->willReturn(20);
        $existingAppointment->method('getDate')->willReturn(new DateTimeImmutable('2025-12-10 09:00:00'));
        $existingAppointment->method('getDuration')->willReturn(60);

        $this->appointmentsRepository
            ->method('findByBarberAndDate')
            ->willReturn([$existingAppointment]);

        // Act
        $result = $this->validator->isBarberAvailable($barber, $startTime, $duration);

        // Assert: Should be available (end1 == start2, no overlap)
        $this->assertTrue($result, 'Barber should be available when previous appointment ends exactly when new one starts');
    }

    // ========================================
    // validateAppointment() Tests - FULL VALIDATION
    // ========================================

    /**
     * Test: Valid appointment - no errors.
     */
    public function testValidateAppointmentValidAppointmentReturnsNoErrors(): void
    {
        // Arrange
        $client = $this->createMock(User::class);
        $barber = $this->createMock(User::class);
        $startTime = new DateTimeImmutable('2030-12-10 10:00:00'); // Future
        $duration = 60;

        // Mock: Barber is working at this time
        $this->scheduleService
            ->expects($this->exactly(2)) // Called twice: for start and end time
            ->method('isBarberWorkingAt')
            ->willReturn(true);

        // Mock: No existing appointments (barber and client both free)
        $this->appointmentsRepository
            ->method('findByBarberAndDate')
            ->willReturn([]);
        $this->appointmentsRepository
            ->method('findByClientAndDate')
            ->willReturn([]);

        // Act
        $errors = $this->validator->validateAppointment($client, $barber, $startTime, $duration);

        // Assert
        $this->assertEmpty($errors, 'Valid appointment should return no errors');
    }

    /**
     * Test: Appointment in the past - error.
     */
    public function testValidateAppointmentInPastReturnsError(): void
    {
        // Arrange
        $client = $this->createMock(User::class);
        $barber = $this->createMock(User::class);
        $startTime = new DateTimeImmutable('2020-01-01 10:00:00'); // Past
        $duration = 60;

        // Act
        $errors = $this->validator->validateAppointment($client, $barber, $startTime, $duration);

        // Assert
        $this->assertNotEmpty($errors, 'Appointment in the past should return errors');
        $this->assertContains('Не можете да запазите час в миналото.', $errors);
    }

    /**
     * Test: Barber not working at this time - error.
     */
    public function testValidateAppointmentBarberNotWorkingReturnsError(): void
    {
        // Arrange
        $client = $this->createMock(User::class);
        $barber = $this->createMock(User::class);
        $startTime = new DateTimeImmutable('2030-12-10 10:00:00');
        $duration = 60;

        // Mock: Barber is NOT working at start time
        $this->scheduleService
            ->method('isBarberWorkingAt')
            ->willReturn(false);

        // Act
        $errors = $this->validator->validateAppointment($client, $barber, $startTime, $duration);

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertContains('Избраният бръснар не работи в този час.', $errors);
    }

    /**
     * Test: Appointment ends outside working hours - error.
     */
    public function testValidateAppointmentEndsOutsideWorkingHoursReturnsError(): void
    {
        // Arrange
        $client = $this->createMock(User::class);
        $barber = $this->createMock(User::class);
        $startTime = new DateTimeImmutable('2030-12-10 17:30:00'); // 17:30
        $duration = 60; // Ends at 18:30

        // Mock: Barber works until 18:00
        $this->scheduleService
            ->method('isBarberWorkingAt')
            ->willReturnCallback(function ($barber, $time) {
                // Working at 17:30, NOT working at 18:29 (end time - 1 min)
                return $time->format('H:i') < '18:00';
            });

        // Mock: No conflicts
        $this->appointmentsRepository
            ->method('findByBarberAndDate')
            ->willReturn([]);
        $this->appointmentsRepository
            ->method('findByClientAndDate')
            ->willReturn([]);

        // Act
        $errors = $this->validator->validateAppointment($client, $barber, $startTime, $duration);

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertContains('Процедурата няма да приключи преди края на работното време на бръснаря.', $errors);
    }

    /**
     * Test: Barber is busy - error.
     */
    public function testValidateAppointmentBarberBusyReturnsError(): void
    {
        // Arrange
        $client = $this->createMock(User::class);
        $barber = $this->createMock(User::class);
        $startTime = new DateTimeImmutable('2030-12-10 10:00:00');
        $duration = 60;

        // Mock: Barber is working
        $this->scheduleService
            ->method('isBarberWorkingAt')
            ->willReturn(true);

        // Mock: Barber has conflicting appointment
        $conflictingAppointment = $this->createMock(Appointments::class);
        $conflictingAppointment->method('getId')->willReturn(100);
        $conflictingAppointment->method('getDate')->willReturn(new DateTimeImmutable('2030-12-10 10:30:00'));
        $conflictingAppointment->method('getDuration')->willReturn(60);

        $this->appointmentsRepository
            ->method('findByBarberAndDate')
            ->willReturn([$conflictingAppointment]);

        // Mock: Client is free
        $this->appointmentsRepository
            ->method('findByClientAndDate')
            ->willReturn([]);

        // Act
        $errors = $this->validator->validateAppointment($client, $barber, $startTime, $duration);

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertContains('Избраният бръснар е зает в този час.', $errors);
    }

    /**
     * Test: Client is busy - error.
     */
    public function testValidateAppointmentClientBusyReturnsError(): void
    {
        // Arrange
        $client = $this->createMock(User::class);
        $barber = $this->createMock(User::class);
        $startTime = new DateTimeImmutable('2030-12-10 10:00:00');
        $duration = 60;

        // Mock: Barber is working and free
        $this->scheduleService
            ->method('isBarberWorkingAt')
            ->willReturn(true);
        $this->appointmentsRepository
            ->method('findByBarberAndDate')
            ->willReturn([]);

        // Mock: Client has conflicting appointment
        $conflictingAppointment = $this->createMock(Appointments::class);
        $conflictingAppointment->method('getId')->willReturn(200);
        $conflictingAppointment->method('getDate')->willReturn(new DateTimeImmutable('2030-12-10 10:15:00'));
        $conflictingAppointment->method('getDuration')->willReturn(30);

        $this->appointmentsRepository
            ->method('findByClientAndDate')
            ->willReturn([$conflictingAppointment]);

        // Act
        $errors = $this->validator->validateAppointment($client, $barber, $startTime, $duration);

        // Assert
        $this->assertNotEmpty($errors);
        $this->assertContains('Вие вече имате запазен час по това време.', $errors);
    }

    /**
     * Test: Multiple errors at once.
     */
    public function testValidateAppointmentMultipleErrorsReturnsAll(): void
    {
        // Arrange
        $client = $this->createMock(User::class);
        $barber = $this->createMock(User::class);
        $startTime = new DateTimeImmutable('2020-01-01 10:00:00'); // Past - error 1
        $duration = 60;

        // Mock: Barber is NOT working - error 2
        $this->scheduleService
            ->method('isBarberWorkingAt')
            ->willReturn(false);

        // Mock: Barber is busy - error 3
        $barberAppointment = $this->createMock(Appointments::class);
        $barberAppointment->method('getId')->willReturn(300);
        $barberAppointment->method('getDate')->willReturn(new DateTimeImmutable('2020-01-01 10:30:00'));
        $barberAppointment->method('getDuration')->willReturn(60);

        $this->appointmentsRepository
            ->method('findByBarberAndDate')
            ->willReturn([$barberAppointment]);

        // Mock: Client is busy - error 4
        $clientAppointment = $this->createMock(Appointments::class);
        $clientAppointment->method('getId')->willReturn(400);
        $clientAppointment->method('getDate')->willReturn(new DateTimeImmutable('2020-01-01 10:00:00'));
        $clientAppointment->method('getDuration')->willReturn(60);

        $this->appointmentsRepository
            ->method('findByClientAndDate')
            ->willReturn([$clientAppointment]);

        // Act
        $errors = $this->validator->validateAppointment($client, $barber, $startTime, $duration);

        // Assert: Should have multiple errors (5 total because isBarberWorkingAt is called twice)
        $this->assertCount(5, $errors, 'Should return all 5 validation errors');
        $this->assertContains('Не можете да запазите час в миналото.', $errors);
        $this->assertContains('Избраният бръснар не работи в този час.', $errors);
        $this->assertContains('Процедурата няма да приключи преди края на работното време на бръснаря.', $errors);
        $this->assertContains('Избраният бръснар е зает в този час.', $errors);
        $this->assertContains('Вие вече имате запазен час по това време.', $errors);
    }

    /**
     * Test: Exclude appointment works in full validation.
     */
    public function testValidateAppointmentWithExcludeReturnsNoErrors(): void
    {
        // Arrange
        $client = $this->createMock(User::class);
        $barber = $this->createMock(User::class);
        $startTime = new DateTimeImmutable('2030-12-10 10:00:00');
        $duration = 60;

        // Existing appointment (we're updating this one)
        $existingAppointment = $this->createMock(Appointments::class);
        $existingAppointment->method('getId')->willReturn(500);
        $existingAppointment->method('getDate')->willReturn(new DateTimeImmutable('2030-12-10 10:00:00'));
        $existingAppointment->method('getDuration')->willReturn(60);

        // Mock: Barber is working
        $this->scheduleService
            ->method('isBarberWorkingAt')
            ->willReturn(true);

        // Mock: Same appointment for barber and client
        $this->appointmentsRepository
            ->method('findByBarberAndDate')
            ->willReturn([$existingAppointment]);
        $this->appointmentsRepository
            ->method('findByClientAndDate')
            ->willReturn([$existingAppointment]);

        // Act: Exclude this appointment (update scenario)
        $errors = $this->validator->validateAppointment($client, $barber, $startTime, $duration, $existingAppointment);

        // Assert: No errors because we excluded the conflicting appointment
        $this->assertEmpty($errors, 'Should have no errors when updating existing appointment');
    }
}
