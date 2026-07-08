<?php

require_once __DIR__ . '/db.php';

try {
    $conn = getDB();

    echo 'DB 연결 성공';
} catch (mysqli_sql_exception $e) {
    echo 'DB 연결 실패';
    echo '<br>';
    echo $e->getMessage();
}