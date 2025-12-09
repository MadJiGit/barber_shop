<?php

namespace App\Enum;

enum AppointmentStatus: string
{
    case PENDING = 'pending';
    case PENDING_CONFIRMATION = 'pending_confirmation';
    case CONFIRMED = 'confirmed';
    case NOT_CONFIRMED = 'not_confirmed';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case NO_SHOW = 'no_show';

    /**
     * Get human-readable label for the status
     */
    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::PENDING_CONFIRMATION => 'Pending Confirmation',
            self::CONFIRMED => 'Confirmed',
            self::NOT_CONFIRMED => 'Not Confirmed',
            self::CANCELLED => 'Cancelled',
            self::COMPLETED => 'Completed',
            self::NO_SHOW => 'No Show',
        };
    }

    /**
     * Get Bulgarian label for the status
     */
    public function getLabelBg(): string
    {
        return match($this) {
            self::PENDING => 'Чакащ',
            self::PENDING_CONFIRMATION => 'Чака Потвърждение',
            self::CONFIRMED => 'Потвърден',
            self::NOT_CONFIRMED => 'Непотвърден',
            self::CANCELLED => 'Отказан',
            self::COMPLETED => 'Завършен',
            self::NO_SHOW => 'Не се е появил',
        };
    }

    /**
     * Get CSS badge class for the status
     */
    public function getBadgeClass(): string
    {
        return match($this) {
            self::PENDING => 'badge-warning',
            self::PENDING_CONFIRMATION => 'badge-info',
            self::CONFIRMED => 'badge-success',
            self::NOT_CONFIRMED => 'badge-danger',
            self::CANCELLED => 'badge-secondary',
            self::COMPLETED => 'badge-primary',
            self::NO_SHOW => 'badge-dark',
        };
    }

    /**
     * Get all active statuses (not cancelled or no-show)
     */
    public static function getActiveStatuses(): array
    {
        return [
            self::PENDING,
            self::PENDING_CONFIRMATION,
            self::CONFIRMED,
            self::COMPLETED,
        ];
    }

    /**
     * Get statuses that require action
     */
    public static function getPendingStatuses(): array
    {
        return [
            self::PENDING,
            self::PENDING_CONFIRMATION,
        ];
    }
}
