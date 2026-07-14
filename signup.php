<?php
require 'db.php';

use App\Models\User;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $name     = trim($_POST['name'] ?? '');

    if ($username === '' || $password === '' || $name === '') {
        $error = '모든 항목을 입력하세요.';
    } elseif (!preg_match('/^[a-zA-Z0-9]{4,20}$/', $username)) {
        $error = '아이디는 영문/숫자 4~20자.';
    } elseif (strlen($password) < 8) {
        $error = '비밀번호는 8자 이상.';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            User::create($username, $hash, $name);

            header('Location: login.php');
            exit;
        } catch (mysqli_sql_exception $e) {
            $error = ($e->getCode() === 1062)
                ? '이미 존재하는 아이디입니다.'
                : '가입 처리 중 오류가 발생했습니다.';
        }
    }
}

require 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-4">
        <h2 class="mb-3">회원가입</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">아이디</label>
                <input type="text" name="username" class="form-control"
                    value="<?= htmlspecialchars($username ?? '') ?>">
                <div class="form-text">영문/숫자 4~20자</div>
            </div>
            <div class="mb-3">
                <label class="form-label">비밀번호</label>
                <input type="password" name="password" class="form-control">
                <div class="form-text">8자 이상</div>
            </div>
            <div class="mb-3">
                <label class="form-label">이름</label>
                <input type="text" name="name" class="form-control"
                    value="<?= htmlspecialchars($name ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary w-100">가입하기</button>
        </form>
        <p class="mt-3 text-center"><a href="login.php">이미 계정이 있나요? 로그인</a></p>
    </div>
</div>

<?php require 'footer.php'; ?>