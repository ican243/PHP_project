<?php

require_once __DIR__ . '/vendor/autoload.php';

date_default_timezone_set('Asia/Seoul');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
