<?php

require_once __DIR__ . '/bootstrap.php';

use App\Database;

function getDB(): mysqli
{
    return Database::get();
}
