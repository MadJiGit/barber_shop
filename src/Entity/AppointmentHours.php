<?php

namespace App\Entity;

class AppointmentHours
{
    /**
     * @return string[]
     */
    private static array $appointments = [
        '1' => '10:00',
        '2' => '11:00',
        '3' => '12:00',
        '4' => '13:00',
        '5' => '14:00',
        '6' => '15:00',
        '7' => '16:00',
        '8' => '17:00',
    ];

    public static function getAppointmentHours(): array
    {
        return self::$appointments;
    }

    public static function getAppointmentIdByHour(string $hour): string|bool
    {
        return array_keys(self::getAppointmentHours(), $hour)[0] ?? false;
    }

    public static function getAppointmentHourById(string $id): string
    {
        return self::getAppointmentHours()[$id];
    }

    public static function ifKeyExist($key): bool
    {
        return key_exists($key, self::getAppointmentHours());
    }

    public static function ifHourExist($hour): bool
    {
        return self::getAppointmentIdByHour($hour) ?? false;
    }
}
//
//if ('POST' === $_SERVER['REQUEST_METHOD']) {
//    echo '<pre>'.var_export($_SERVER['REQUEST_METHOD'], true).'</pre>';
//    echo '<pre>'.var_export($_POST, true).'</pre>';
//exit();
//    $method = $_POST;
//    $funcName = $method['functionName'];
//    // Include the class
//    require_once 'AppointmentHours.php';
//    $classInstance = new AppointmentHours();
//
//    // Get the data sent from JavaScript
//    $name = $method['name'];
//
//    // Call the class function
//    if ('getAppointmentHours' == $funcName) {
//        $response = $classInstance::getAppointmentHours();
//    } else {
//                echo '<pre>'.var_export("ne e tazi funkciq", true).'</pre>';
//                exit;
//    }
//
//    // Return the response
//    echo json_encode(['message' => $response]);
//    exit;
//}
