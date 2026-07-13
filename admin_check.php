<?php
require_once __DIR__ . '/auth_check.php';

if (!in_array($_SESSION['role'], ['sub_admin', 'super_admin'], true)) {
    http_response_code(403);
    require_once __DIR__ . '/header.php';
    echo '<div class="alert alert-danger">접근 권한이 없습니다.</div>';
    require_once __DIR__ . '/footer.php';
    exit;
}
