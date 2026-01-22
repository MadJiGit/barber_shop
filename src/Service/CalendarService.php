<?php

namespace App\Service;

use App\Entity\Appointments;

class CalendarService
{
    /**
     * Generate ICS (iCalendar) file content for an appointment
     */
    public function generateIcsContent(Appointments $appointment): string
    {
        $start = $appointment->getDate();
        $duration = $appointment->getDuration(); // in minutes
        $end = (clone $start)->modify("+{$duration} minutes");

        $barber = $appointment->getBarber();
        $client = $appointment->getClient();
        $procedure = $appointment->getProcedureType();

        // Format dates in UTC for ICS (YYYYMMDDTHHmmssZ)
        $dtStart = $start->format('Ymd\THis\Z');
        $dtEnd = $end->format('Ymd\THis\Z');
        $dtStamp = (new \DateTimeImmutable())->format('Ymd\THis\Z');

        // Generate unique UID
        $uid = 'appointment-' . $appointment->getId() . '@barbershop.com';

        // Escape text for ICS format
        $summary = $this->escapeIcsText($procedure->getType() . ' - Barber Shop');
        $description = $this->escapeIcsText(
            "Резервация за {$procedure->getType()}\n" .
            "Барбър: {$barber->getNickName()}\n" .
            "Клиент: {$client->getFirstName()} {$client->getLastName()}\n" .
            "Продължителност: {$duration} минути"
        );
        $location = $this->escapeIcsText('Barber Shop, Sofia');

        // Build ICS content
        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Barber Shop//Appointment Booking//EN\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:REQUEST\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:{$uid}\r\n";
        $ics .= "DTSTAMP:{$dtStamp}\r\n";
        $ics .= "DTSTART:{$dtStart}\r\n";
        $ics .= "DTEND:{$dtEnd}\r\n";
        $ics .= "SUMMARY:{$summary}\r\n";
        $ics .= "DESCRIPTION:{$description}\r\n";
        $ics .= "LOCATION:{$location}\r\n";
        $ics .= "STATUS:CONFIRMED\r\n";
        $ics .= "SEQUENCE:0\r\n";
        $ics .= "BEGIN:VALARM\r\n";
        $ics .= "TRIGGER:-PT1H\r\n"; // Reminder 1 hour before
        $ics .= "ACTION:DISPLAY\r\n";
        $ics .= "DESCRIPTION:Reminder: {$summary}\r\n";
        $ics .= "END:VALARM\r\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";

        return $ics;
    }

    /**
     * Generate Google Calendar URL for adding event
     */
    public function getGoogleCalendarUrl(Appointments $appointment): string
    {
        $start = $appointment->getDate();
        $duration = $appointment->getDuration();
        $end = (clone $start)->modify("+{$duration} minutes");

        $procedure = $appointment->getProcedureType();
        $barber = $appointment->getBarber();

        // Google Calendar date format: YYYYMMDDTHHmmssZ
        $dateStart = $start->format('Ymd\THis\Z');
        $dateEnd = $end->format('Ymd\THis\Z');

        $params = [
            'action' => 'TEMPLATE',
            'text' => $procedure->getType() . ' - Barber Shop',
            'dates' => $dateStart . '/' . $dateEnd,
            'details' => "Резервация за {$procedure->getType()} с барбър {$barber->getNickName()}",
            'location' => 'Barber Shop, Sofia',
        ];

        return 'https://calendar.google.com/calendar/render?' . http_build_query($params);
    }

    /**
     * Generate Apple/Outlook Calendar URL (downloads ICS file)
     */
    public function getDownloadIcsUrl(int $appointmentId): string
    {
        // This will be a route that serves the ICS file
        return "/appointment/calendar/{$appointmentId}.ics";
    }

    /**
     * Escape special characters for ICS format
     */
    private function escapeIcsText(string $text): string
    {
        // Escape special characters according to RFC 5545
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(',', '\\,', $text);
        $text = str_replace(';', '\\;', $text);
        $text = str_replace("\n", '\\n', $text);
        $text = str_replace("\r", '', $text);

        return $text;
    }
}
