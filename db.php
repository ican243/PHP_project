<?php

function getDB(): mysqli
{
    static $conn = null;

    if ($conn === null) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $conn = mysqli_connect(
            'localhost',
            'root',
            '',
            'member_db'
        );

        mysqli_set_charset($conn, 'utf8mb4');
    }

    return $conn;
}