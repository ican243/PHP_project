<?php
require_once __DIR__ . '/db.php';
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = '아이디와 비밀번호를 입력하세요.';
    } else {
        try {
            $stmt = getDB()->prepare(
                'SELECT id, username, password_hash, name, role, status FROM users WHERE username = ?'
            );
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $error = '아이디 또는 비밀번호가 올바르지 않습니다.';
            } elseif ($user['status'] !== 'active') {
                $error = '비활성화된 계정입니다.';
            } else {
                $upd = getDB()->prepare('UPDATE users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?');
                $ip = $_SERVER['REMOTE_ADDR'];
                $upd->bind_param('si', $ip, $user['id']);
                $upd->execute();

                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name']    = $user['name'];
                $_SESSION['role']    = $user['role'];

                header('Location: mypage.php');
                exit;
            }
        } catch (mysqli_sql_exception $e) {
            $error = '로그인 처리 중 오류가 발생했습니다.';
        }
    }
}

require 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-4">
        <h2 class="mb-3">로그인</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">아이디</label>
                <input type="text" name="username" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">비밀번호</label>
                <input type="password" name="password" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary w-100">로그인</button>
        </form>
        <p class="mt-3 text-center"><a href="signup.php">회원가입</a></p>
    </div>
</div>

<?php require 'footer.php'; ?>