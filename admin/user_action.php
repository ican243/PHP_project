<?php
require_once '../auth_check.php';
require_once '../db.php';
require_once '../admin_check.php';
require_once '../csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    header('Location: users.php');
    exit;
}

$targetId = filter_input(INPUT_POST, 'target_id', FILTER_VALIDATE_INT);
$action   = $_POST['action'] ?? '';
$adminId  = $_SESSION['user_id'];

$allowedActions = ['deactivate', 'activate', 'withdraw', 'reset_password', 'grant_edit', 'revoke_edit'];

if (!$targetId || !in_array($action, $allowedActions, true)) {
    header('Location: users.php');
    exit;
}

// 자기 자신 대상 액션 금지
if ($targetId === $adminId) {
    header('Location: users.php?error=self');
    exit;
}

try {
    $db = getDB();

    switch ($action) {
        case 'deactivate':
            $stmt = $db->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
            $stmt->bind_param('i', $targetId);
            $stmt->execute();
            break;

        case 'activate':
            $stmt = $db->prepare("UPDATE users SET status = 'active' WHERE id = ?");
            $stmt->bind_param('i', $targetId);
            $stmt->execute();
            break;

        case 'withdraw':
            $stmt = $db->prepare("UPDATE users SET status = 'deleted' WHERE id = ?");
            $stmt->bind_param('i', $targetId);
            $stmt->execute();
            break;

        case 'reset_password':
            $tempPassword = bin2hex(random_bytes(4)); // 8자리 임시 비밀번호
            $hash = password_hash($tempPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->bind_param('si', $hash, $targetId);
            $stmt->execute();
            $_SESSION['temp_password_notice'] = $tempPassword;
            break;

        case 'grant_edit':
        case 'revoke_edit':
            // 최고관리자만 권한 위임 가능 (서버 단 재검증)
            if ($_SESSION['role'] !== 'super_admin') {
                header('Location: users.php?error=forbidden');
                exit;
            }
            $value = ($action === 'grant_edit') ? 1 : 0;
            $stmt = $db->prepare(
                "UPDATE users SET can_edit_users = ? WHERE id = ? AND role = 'sub_admin'"
            );
            $stmt->bind_param('ii', $value, $targetId);
            $stmt->execute();
            break;
    }

    // 감사 로그 기록
    $log = $db->prepare('INSERT INTO admin_logs (admin_id, target_user_id, action) VALUES (?, ?, ?)');
    $log->bind_param('iis', $adminId, $targetId, $action);
    $log->execute();
} catch (mysqli_sql_exception $e) {
    header('Location: users.php?error=db');
    exit;
}

header('Location: users.php?success=1');
exit;
