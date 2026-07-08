<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = '잘못된 요청입니다. 다시 시도하세요.';
    } else {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($current === '' || $new === '' || $confirm === '') {
            $error = '모든 항목을 입력하세요.';
        } elseif ($new !== $confirm) {
            $error = '새 비밀번호가 서로 일치하지 않습니다.';
        } elseif (strlen($new) < 8) {
            $error = '새 비밀번호는 8자 이상.';
        } else {
            try {
                $stmt = getDB()->prepare('SELECT password_hash FROM users WHERE id = ?');
                $stmt->bind_param('i', $_SESSION['user_id']);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();

                if (!password_verify($current, $row['password_hash'])) {
                    $error = '현재 비밀번호가 올바르지 않습니다.';
                } else {
                    $hash = password_hash($new, PASSWORD_DEFAULT);
                    $stmt = getDB()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                    $stmt->bind_param('si', $hash, $_SESSION['user_id']);
                    $stmt->execute();
                    $success = '비밀번호가 변경되었습니다.';
                }
            } catch (mysqli_sql_exception $e) {
                $error = '변경 중 오류가 발생했습니다.';
            }
        }
    }
}

require 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <h2 class="mb-3">비밀번호 변경</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="mb-3">
                <label class="form-label">현재 비밀번호</label>
                <input type="password" name="current_password" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">새 비밀번호</label>
                <input type="password" name="new_password" class="form-control">
                <div class="form-text">8자 이상</div>
            </div>
            <div class="mb-3">
                <label class="form-label">새 비밀번호 확인</label>
                <input type="password" name="confirm_password" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">변경</button>
            <a href="mypage.php" class="btn btn-outline-secondary">취소</a>
        </form>
    </div>
</div>

<?php require 'footer.php'; ?>