<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/db.php';

try {
    $stmt = getDB()->prepare(
        'SELECT username, name, bio, role, last_login_at, last_login_ip, created_at
         FROM users WHERE id = ?'
    );
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
} catch (mysqli_sql_exception $e) {
    exit('정보를 불러오는 중 오류가 발생했습니다.');
}

require 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mb-3">마이페이지</h2>

        <div class="card">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">아이디</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($user['username']) ?></dd>

                    <dt class="col-sm-4">이름</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($user['name']) ?></dd>

                    <dt class="col-sm-4">자기소개</dt>
                    <dd class="col-sm-8"><?= nl2br(htmlspecialchars($user['bio'] ?? '')) ?: '<span class="text-muted">등록된 자기소개가 없습니다.</span>' ?></dd>

                    <dt class="col-sm-4">가입일</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($user['created_at']) ?></dd>

                    <dt class="col-sm-4">마지막 로그인</dt>
                    <dd class="col-sm-8">
                        <?= htmlspecialchars($user['last_login_at'] ?? '-') ?>
                        (<?= htmlspecialchars($user['last_login_ip'] ?? '-') ?>)
                    </dd>
                </dl>
            </div>
        </div>

        <div class="mt-3 d-flex gap-2">
            <a href="profile_edit.php" class="btn btn-primary">프로필 수정</a>
            <a href="password_change.php" class="btn btn-outline-secondary">비밀번호 변경</a>
            <a href="logout.php" class="btn btn-outline-danger">로그아웃</a>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>