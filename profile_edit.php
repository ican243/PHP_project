<?php
require 'auth_check.php';
require 'db.php';
require 'csrf.php';

use App\Models\User;

$error = '';
$success = '';

$user = User::findById($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = '잘못된 요청입니다. 다시 시도하세요.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $bio  = trim($_POST['bio'] ?? '');

        if ($name === '') {
            $error = '이름을 입력하세요.';
        } elseif (mb_strlen($name) > 50) {
            $error = '이름은 50자 이내로.';
        } else {
            try {
                User::updateProfile($_SESSION['user_id'], $name, $bio);

                $_SESSION['name'] = $name;
                $success = '수정되었습니다.';
                $user = ['name' => $name, 'bio' => $bio];
            } catch (mysqli_sql_exception $e) {
                $error = '수정 중 오류가 발생했습니다.';
            }
        }
    }
}

require 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mb-3">프로필 수정</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="mb-3">
                <label class="form-label">이름</label>
                <input type="text" name="name" class="form-control"
                    value="<?= htmlspecialchars($user['name']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">자기소개</label>
                <textarea name="bio" rows="5" class="form-control"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">저장</button>
            <a href="mypage.php" class="btn btn-outline-secondary">취소</a>
        </form>
    </div>
</div>

<?php require 'footer.php'; ?>