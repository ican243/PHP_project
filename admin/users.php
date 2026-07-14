<?php
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../admin_check.php';
require_once __DIR__ . '/../csrf.php';

use App\Models\User;

$perPage = 10;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $perPage;
$keyword = trim($_GET['keyword'] ?? '');

try {
    $total = User::countForAdmin($keyword);
    $users = User::listForAdmin($keyword, $perPage, $offset);
    $totalPages = max(1, ceil($total / $perPage));
} catch (mysqli_sql_exception $e) {
    exit('유저 목록을 불러오는 중 오류가 발생했습니다.');
}

require_once __DIR__ . '/../header.php';
?>

<h2 class="mb-3">유저 관리</h2>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">처리되었습니다.</div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'self'): ?>
    <div class="alert alert-warning">본인 계정에는 해당 작업을 할 수 없습니다.</div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'db'): ?>
    <div class="alert alert-danger">처리 중 오류가 발생했습니다.</div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'forbidden'): ?>
    <div class="alert alert-danger">권한이 없습니다.</div>
<?php endif; ?>

<?php if (isset($_SESSION['temp_password_notice'])): ?>
    <div class="alert alert-info">
        임시 비밀번호: <strong><?= htmlspecialchars($_SESSION['temp_password_notice']) ?></strong>
        (이 화면을 벗어나면 다시 볼 수 없습니다. 유저에게 전달하세요.)
    </div>
    <?php unset($_SESSION['temp_password_notice']); ?>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <form method="get" class="d-flex gap-2">
        <input type="text" name="keyword" class="form-control" placeholder="아이디 또는 이름 검색"
            value="<?= htmlspecialchars($keyword) ?>">
        <button type="submit" class="btn btn-outline-primary">검색</button>
        <?php if ($keyword !== ''): ?>
            <a href="users.php" class="btn btn-outline-secondary">초기화</a>
        <?php endif; ?>
    </form>
    <a href="users_csv.php<?= $keyword !== '' ? '?keyword=' . urlencode($keyword) : '' ?>"
        class="btn btn-outline-success">CSV 다운로드</a>
</div>

<table class="table table-bordered table-hover bg-white align-middle">
    <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>아이디</th>
            <th>이름</th>
            <th>등급</th>
            <th>상태</th>
            <th>가입일</th>
            <th>관리</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($users)): ?>
            <tr>
                <td colspan="7" class="text-center text-muted">검색 결과가 없습니다.</td>
            </tr>
        <?php endif; ?>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td>
                    <?= htmlspecialchars($u['role']) ?>
                    <?php if ($u['role'] === 'sub_admin' && $u['can_edit_users']): ?>
                        <span class="badge bg-info text-dark">수정권한</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($u['status'] === 'active'): ?>
                        <span class="badge bg-success">활성</span>
                    <?php elseif ($u['status'] === 'inactive'): ?>
                        <span class="badge bg-secondary">비활성</span>
                    <?php else: ?>
                        <span class="badge bg-danger">탈퇴</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($u['created_at']) ?></td>
                <td>
                    <div class="d-flex flex-wrap gap-1">
                        <a href="../profile.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-secondary">보기</a>

                        <?php if ($u['id'] != $_SESSION['user_id'] && $u['status'] !== 'deleted'): ?>

                            <form method="post" action="user_action.php" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="target_id" value="<?= $u['id'] ?>">
                                <?php if ($u['status'] === 'active'): ?>
                                    <input type="hidden" name="action" value="deactivate">
                                    <button type="submit" class="btn btn-sm btn-outline-warning">비활성화</button>
                                <?php else: ?>
                                    <input type="hidden" name="action" value="activate">
                                    <button type="submit" class="btn btn-sm btn-outline-success">재활성화</button>
                                <?php endif; ?>
                            </form>

                            <form method="post" action="user_action.php" class="d-inline"
                                onsubmit="return confirm('정말 초기화하시겠습니까?');">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="target_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="action" value="reset_password">
                                <button type="submit" class="btn btn-sm btn-outline-primary">비번 초기화</button>
                            </form>

                            <?php if ($_SESSION['role'] === 'super_admin' && $u['role'] === 'sub_admin'): ?>
                                <form method="post" action="user_action.php" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="target_id" value="<?= $u['id'] ?>">
                                    <?php if ($u['can_edit_users']): ?>
                                        <input type="hidden" name="action" value="revoke_edit">
                                        <button type="submit" class="btn btn-sm btn-outline-dark">수정권한 회수</button>
                                    <?php else: ?>
                                        <input type="hidden" name="action" value="grant_edit">
                                        <button type="submit" class="btn btn-sm btn-outline-info">수정권한 부여</button>
                                    <?php endif; ?>
                                </form>
                            <?php endif; ?>

                            <form method="post" action="user_action.php" class="d-inline"
                                onsubmit="return confirm('정말 탈퇴 처리하시겠습니까? 되돌릴 수 없습니다.');">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="target_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="action" value="withdraw">
                                <button type="submit" class="btn btn-sm btn-outline-danger">탈퇴</button>
                            </form>

                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if ($totalPages > 1): ?>
    <nav>
        <ul class="pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $p ?><?= $keyword !== '' ? '&keyword=' . urlencode($keyword) : '' ?>">
                        <?= $p ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

<?php require_once __DIR__ . '/../footer.php'; ?>