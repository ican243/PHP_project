<?php
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../admin_check.php';

$keyword = trim($_GET['keyword'] ?? '');

try {
    $db = getDB();

    if ($keyword !== '') {
        $like = '%' . $keyword . '%';
        $stmt = $db->prepare(
            "SELECT id, username, name, role, status, created_at
             FROM users WHERE username LIKE ? OR name LIKE ? ORDER BY id DESC"
        );
        $stmt->bind_param('ss', $like, $like);
    } else {
        $stmt = $db->prepare(
            "SELECT id, username, name, role, status, created_at FROM users ORDER BY id DESC"
        );
    }

    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $e) {
    exit('CSV 생성 중 오류가 발생했습니다.');
}

// 다운로드 헤더 설정
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="users_' . date('Ymd_His') . '.csv"');

$output = fopen('php://output', 'w');

// 엑셀에서 한글 깨짐 방지용 BOM
fwrite($output, "\xEF\xBB\xBF");

// 헤더 행
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
