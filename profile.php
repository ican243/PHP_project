<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    require 'header.php';
    echo '<div class="alert alert-danger">잘못된 접근입니다.</div>';
    require 'footer.php';
    exit;
}

try {
    $stmt = getDB()->prepare(
        "SELECT username, name, bio FROM users WHERE id = ? AND status = 'active'"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
} catch (mysqli_sql_exception $e) {
    exit('오류가 발생했습니다.');
}

require 'header.php';

if (!$user) {
    echo '<div class="alert alert-warning">존재하지 않거나 비활성화된 사용자입니다.</div>';
    require 'footer.php';
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mb-3"><?= htmlspecialchars($user['name']) ?>님의 프로필</h2>
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-1">@<?= htmlspecialchars($user['username']) ?></p>
                <p><?= nl2br(htmlspecialchars($user['bio'] ?? '')) ?: '<span class="text-muted">등록된 자기소개가 없습니다.</span>' ?></p>
            </div>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>