<?php

namespace App;

class Database
{
    private static ?\mysqli $conn = null;

    public static function get(): \mysqli
    {
        if (self::$conn === null) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            self::$conn = mysqli_connect(
                $_ENV['DB_HOST'],
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                $_ENV['DB_NAME']
            );

            mysqli_set_charset(self::$conn, 'utf8mb4');
        }

        return self::$conn;
    }
}
