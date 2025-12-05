<?php

namespace App\Tests\Service;

use App\Entity\BarberSchedule;
use App\Entity\BarberScheduleException;
use App\Entity\User;
use App\Repository\AppointmentsRepository;
use App\Repository\BarberScheduleExceptionRepository;
use App\Repository\BarberScheduleRepository;
use App\Service\BarberScheduleService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class BarberScheduleServiceTest extends TestCase
{
    private BarberScheduleRepository $scheduleRepository;
    private BarberScheduleExceptionRepository $exceptionRepository;
    private AppointmentsRepository $appointmentsRepository;
    private BarberScheduleService $service;

    protected function setUp(): void
    {
        // Create mocks
        $this->scheduleRepository = $this->createMock(BarberScheduleRepository::class);
        $this->exceptionRepository = $this->createMock(BarberScheduleExceptionRepository::class);
        $this->appointmentsRepository = $this->createMock(AppointmentsRepository::class);

        // Create service
        $this->service = new BarberScheduleService(
            $this->scheduleRepository,
            $this->exceptionRepository,
            $this->appointmentsRepository
        );
    }

    // ========================================
    // isBarberWorkingAt() Tests - CRITICAL
    // ========================================

    /**
     * Test: Barber is working during normal working hours.
     */
    public function testIsBarberWorkingAtDuringWorkingHoursReturnsTrue(): void
    {
        // Arrange
        $barber = $this->createMock(User::class);
        $dateTime = new DateTimeImmutable('2025-12-10 14:00:00'); // Wednesday 14:00

        // Mock schedule: Working Mon-Fri 09:00-18:00
        $schedule = $this->createMock(BarberSchedule::class);
        $schedule->method('getScheduleForDay')
            ->with(3) // Wednesday = 3
            ->willReturn([
                'working' => true,
                'start' => '09:00',
                'end' => '18:00'
            ]);

        $this->scheduleRepository
            ->method('findOrCreateForBarber')
            ->with($barber)
            ->willReturn($schedule);

        // No exception for this date
        $this->exceptionRepository
            ->method('findByBarberAndDate')
            ->willReturn(null);

        // Act
        $result = $this->service->isBarberWorkingAt($barber, $dateTime);

        // Assert
        $this->assertTrue($result, 'Barber should be working at 14:00 during working hours');
    }

    /**
     * Test: Barber is NOT working outside working hours.
     */
    public function testIsBarberWorkingAtOutsideWorkingHoursReturnsFalse(): void
    {
        // Arrange
        $barber = $this->createMock(User::class);
        $dateTime = new DateTimeImmutable('2025-12-10 19:00:00'); // 19:00 (after work)

        // Mock schedule: Working 09:00-18:00
        $schedule = $this->createMock(BarberSchedule::class);
        $schedule->method('getScheduleForDay')
            ->willReturn([
                'working' => true,
                'start' => '09:00',
                'end' => '18:00'
            ]);

        $this->scheduleRepository
            ->method('findOrCreateForBarber')
            ->willReturn($schedule);

        $this->exceptionRepository
            ->method('findByBarberAndDate')
            ->willReturn(null);

        // Act
        $result = $this->service->isBarberWorkingAt($barber, $dateTime);

        // Assert
        $this->assertFalse($result, 'Barber should NOT be working at 19:00 (after hours)');
    }

    /**
     * Test: Barber is NOT working on day off (e.g., Sunday).
     */
    public function testIsBarberWorkingAtOnDayOffReturnsFalse(): void
    {
        // Arrange
        $barber = $this->createMock(User::class);
        $dateTime = new DateTimeImmutable('2025-12-14 14:00:00'); // Sunday

        // Mock schedule: Not working on Sunday
        $schedule = $this->createMock(BarberSchedule::class);
        $schedule->method('getScheduleForDay')
            ->with(0) // Sunday = 0
            ->willReturn([
                'working' => false,
                'start' => null,
                'end' => null
            ]);

        $this->scheduleRepository
            ->method('findOrCreateForBarber')
            ->willReturn($schedule);

        $this->exceptionRepository
            ->method('findByBarberAndDate')
            ->willReturn(null);

        // Act
        $result = $this->service->isBarberWorkingAt($barber, $dateTime);

        // Assert
        $this->assertFalse($result, 'Barber should NOT be working on Sunday (day off)');
    }

    /**
     * Test: Exception - Full day off overrides regular schedule.
     */
    public function testIsBarberWorkingAtFullDayOffExceptionReturnsFalse(): void
    {
        // Arrange
        $barber = $this->createMock(User::class);
        $dateTime = new DateTimeImmutable('2025-12-10 14:00:00'); // Wednesday

        // Mock schedule: Normally working
        $schedule = $this->createMock(BarberSchedule::class);
        $schedule->method('getScheduleForDay')
            ->willReturn([
                'working' => true,
                'start' => '09:00',
                'end' => '18:00'
            ]);

        $this->scheduleRepository
            ->method('findOrCreateForBarber')
            ->willReturn($schedule);

        // Mock exception: Full day off (vacation, sick leave)
        $exception = $this->createMock(BarberScheduleException::class);
        $exception->method('isFullDayOff')->willReturn(true);

        $this->exceptionRepository
            ->method('findByBarberAndDate')
            ->willReturn($exception);

        // Act
        $result = $this->service->isBarberWorkingAt($barber, $dateTime);

        // Assert
        $this->assertFalse($result, 'Barber should NOT be working on full day off');
    }

    /**
     * Test: Exception - Custom hours override regular schedule.
     */
    public function testIsBarberWorkingAtCustomHoursException(): void
    {
        // Arrange
        $barber = $this->createMock(User::class);
        $dateTime = new DateTimeImmutable('2025-12-10 20:00:00'); // 20:00 (normally closed)

        // Mock schedule: Normally 09:00-18:00
        $schedule = $this->createMock(BarberSchedule::class);
        $schedule->method('getScheduleForDay')
            ->willReturn([
                'working' => true,
                'start' => '09:00',
                'end' => '18:00'
            ]);

        $this->scheduleRepository
            ->method('findOrCreateForBarber')
            ->willReturn($schedule);

        // Mock exception: Working late today (12:00-22:00)
        $exception = $this->createMock(BarberScheduleException::class);
        $exception->method('isFullDayOff')->willReturn(false);
        $exception->method('hasCustomHours')->willReturn(true);
        $exception->method('getStartTime')->willReturn(new \DateTime('12:00'));
        $exception->method('getEndTime')->willReturn(new \DateTime('22:00'));
        $exception->method('getExcludedSlots')->willReturn(null);

        $this->exceptionRepository
            ->method('findByBarberAndDate')
            ->willReturn($exception);

        // Act
        $result = $this->service->isBarberWorkingAt($barber, $dateTime);

        // Assert
        $this->assertTrue($result, 'Barber should be working at 20:00 due to custom hours exception');
    }

    /**
     * Test: Exception - Excluded slot within working hours.
     */
    public function testIsBarberWorkingAtExcludedSlotReturnsFalse(): void
    {
        // Arrange
        $barber = $this->createMock(User::class);
        $dateTime = new DateTimeImmutable('2025-12-10 14:00:00'); // 14:00

        // Mock schedule: Working 09:00-18:00
        $schedule = $this->createMock(BarberSchedule::class);
        $schedule->method('getScheduleForDay')
            ->willReturn([
                'working' => true,
                'start' => '09:00',
                'end' => '18:00'
            ]);

        $this->scheduleRepository
            ->method('findOrCreateForBarber')
            ->willReturn($schedule);

        // Mock exception: 14:00 slot is excluded (lunch break, personal appointment)
        $exception = $this->createMock(BarberScheduleException::class);
        $exception->method('isFullDayOff')->willReturn(false);
        $exception->method('hasCustomHours')->willReturn(false);
        $exception->method('getExcludedSlots')->willReturn(['14:00', '14:30']);

        $this->exceptionRepository
            ->method('findByBarberAndDate')
            ->willReturn($exception);

        // Act
        $result = $this->service->isBarberWorkingAt($barber, $dateTime);

        // Assert
        $this->assertFalse($result, 'Barber should NOT be working at 14:00 (excluded slot)');
    }

    /**
     * Test: Edge case - Exactly at start time (09:00).
     */
    public function testIsBarberWorkingAtExactlyAtStartTimeReturnsTrue(): void
    {
        // Arrange
        $barber = $this->createMock(User::class);
        $dateTime = new DateTimeImmutable('2025-12-10 09:00:00');

        $schedule = $this->createMock(BarberSchedule::class);
        $schedule->method('getScheduleForDay')
            ->willReturn([
                'working' => true,
                'start' => '09:00',
                'end' => '18:00'
            ]);

        $this->scheduleRepository
            ->method('findOrCreateForBarber')
            ->willReturn($schedule);

        $this->exceptionRepository
            ->method('findByBarberAndDate')
            ->willReturn(null);

        // Act
        $result = $this->service->isBarberWorkingAt($barber, $dateTime);

        // Assert
        $this->assertTrue($result, 'Barber should be working at exactly 09:00 (start time)');
    }

    /**
     * Test: Edge case - Exactly at end time (18:00) - should be false.
     */
    public function testIsBarberWorkingAtExactlyAtEndTimeReturnsFalse(): void
    {
        // Arrange
        $barber = $this->createMock(User::class);
        $dateTime = new DateTimeImmutable('2025-12-10 18:00:00');

        $schedule = $this->createMock(BarberSchedule::class);
        $schedule->method('getScheduleForDay')
            ->willReturn([
                'working' => true,
                'start' => '09:00',
                'end' => '18:00'
            ]);

        $this->scheduleRepository
            ->method('findOrCreateForBarber')
            ->willReturn($schedule);

        $this->exceptionRepository
            ->method('findByBarberAndDate')
            ->willReturn(null);

        // Act
        $result = $this->service->isBarberWorkingAt($barber, $dateTime);

        // Assert
        $this->assertFalse($result, 'Barber should NOT be working at exactly 18:00 (end time)');
    }

    // ========================================
    // getWorkingHoursForDate() Tests
    // ========================================

    /**
     * Test: Get working hours for normal working day.
     */
    public function testGetWorkingHoursForDateNormalDayReturnsHours(): void
    {
        // Arrange
        $barber = $this->createMock(User::class);
        $date = new DateTimeImmutable('2025-12-10'); // Wednesday

        $schedule = $this->createMock(BarberSchedule::class);
        $schedule->method('getScheduleForDay')
            ->with(3)
            ->willReturn([
                'working' => true,
                'start' => '09:00',
                'end' => '18:00'
            ]);

        $this->scheduleRepository
            ->method('findOrCreateForBarber')
            ->willReturn($schedule);

        $this->exceptionRepository
            ->method('findByBarberAndDate')
            ->willReturn(null);

        // Act
        $result = $this->service->getWorkingHoursForDate($barber, $date);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals('09:00', $result['start']);
        $this->assertEquals('18:00', $result['end']);
        $this->assertEmpty($result['excludedSlots']);
    }

    /**
     * Test: Get working hours for day off - returns null.
     */
    public function testGetWorkingHoursForDateDayOffReturnsNull(): void
    {
        // Arrange
        $barber = $this->createMock(User::class);
        $date = new DateTimeImmutable('2025-12-14'); // Sunday

        $schedule = $this->createMock(BarberSchedule::class);
        $schedule->method('getScheduleForDay')
            ->with(0)
            ->willReturn([
                'working' => false,
                'start' => null,
                'end' => null
            ]);

        $this->scheduleRepository
            ->method('findOrCreateForBarber')
            ->willReturn($schedule);

        $this->exceptionRepository
            ->method('findByBarberAndDate')
            ->willReturn(null);

        // Act
        $result = $this->service->getWorkingHoursForDate($barber, $date);

        // Assert
        $this->assertNull($result, 'Should return null for day off');
    }

    /**
     * Test: Get working hours with exception - custom hours.
     */
    public function testGetWorkingHoursForDateWithCustomHoursException(): void
    {
        // Arrange
        $barber = $this->createMock(User::class);
        $date = new DateTimeImmutable('2025-12-10');

        $schedule = $this->createMock(BarberSchedule::class);
        $schedule->method('getScheduleForDay')
            ->willReturn([
                'working' => true,
                'start' => '09:00',
                'end' => '18:00'
            ]);

        $this->scheduleRepository
            ->method('findOrCreateForBarber')
            ->willReturn($schedule);

        // Exception: Working 12:00-20:00 today
        $exception = $this->createMock(BarberScheduleException::class);
        $exception->method('isFullDayOff')->willReturn(false);
        $exception->method('hasCustomHours')->willReturn(true);
        $exception->method('getStartTime')->willReturn(new \DateTime('12:00'));
        $exception->method('getEndTime')->willReturn(new \DateTime('20:00'));
        $exception->method('getExcludedSlots')->willReturn(['14:00']);

        $this->exceptionRepository
            ->method('findByBarberAndDate')
            ->willReturn($exception);

        // Act
        $result = $this->service->getWorkingHoursForDate($barber, $date);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals('12:00', $result['start']);
        $this->assertEquals('20:00', $result['end']);
        $this->assertEquals(['14:00'], $result['excludedSlots']);
    }
}
