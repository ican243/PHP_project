<?php
require 'db.php';

use App\Models\User;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = '아이디와 비밀번호를 입력하세요.';
    } else {
        try {
            $user = User::findByUsername($username);

            if (!$user) {
                $error = '아이디 또는 비밀번호가 올바르지 않습니다.';
            } elseif ($user['status'] !== 'active') {
                $error = '비활성화된 계정입니다.';
            } elseif ($user['locked_until'] !== null && strtotime($user['locked_until']) > time()) {
                // 아직 잠금 시간이 안 지남 -> 비밀번호 확인도 하지 않고 바로 거부
                $remainSec = strtotime($user['locked_until']) - time();
                $remainMin = ceil($remainSec / 60);
                $error = "로그인 5회 실패로 계정이 잠겼습니다. {$remainMin}분 후 다시 시도하세요.";
            } elseif (!password_verify($password, $user['password_hash'])) {
                User::incrementFailCount($user['id']);
                $newCount = $user['fail_count'] + 1;

                if ($newCount >= 5) {
                    User::lockAccount($user['id']);
                    $error = '로그인 5회 실패로 계정이 5분간 잠겼습니다.';
                } else {
                    $left = 5 - $newCount;
                    $error = "아이디 또는 비밀번호가 올바르지 않습니다. (남은 시도 {$left}회)";
                }
            } else {
                // 로그인 성공
                User::resetFailCount($user['id']);
                User::updateLastLogin($user['id'], $_SERVER['REMOTE_ADDR']);

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