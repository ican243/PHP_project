<?php
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../admin_check.php';

use App\Models\User;

$keyword = trim($_GET['keyword'] ?? '');

try {
    $users = User::listAllForCsv($keyword);
} catch (mysqli_sql_exception $e) {
    exit('CSV 생성 중 오류가 발생했습니다.');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="users_' . date('Ymd_His') . '.csv"');

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF");
fputcsv($output, ['ID', '아이디', '이름', '등급', '상태', '가입일']);

foreach ($users as $u) {
    fputcsv($output, [
        $u['id'],
        $u['username'],
        $u['name'],
        $u['role'],
        $u['status'],
        $u['created_at']
    ]);
}

fclose($output);
exit;
