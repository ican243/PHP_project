<?php
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../admin_check.php';
require_once __DIR__ . '/../csrf.php';

use App\Models\User;
use App\Models\AdminLog;

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

if ($targetId === $adminId) {
    header('Location: users.php?error=self');
    exit;
}

try {
    switch ($action) {
        case 'deactivate':
            User::updateStatus($targetId, 'inactive');
            break;

        case 'activate':
            User::updateStatus($targetId, 'active');
            break;

        case 'withdraw':
            User::updateStatus($targetId, 'deleted');
            break;

        case 'reset_password':
            $tempPassword = User::resetPassword($targetId);
            $_SESSION['temp_password_notice'] = $tempPassword;
            break;

        case 'grant_edit':
        case 'revoke_edit':
            if ($_SESSION['role'] !== 'super_admin') {
                header('Location: users.php?error=forbidden');
                exit;
            }
            User::setEditPermission($targetId, $action === 'grant_edit');
            break;
    }

    AdminLog::record($adminId, $targetId, $action);
} catch (mysqli_sql_exception $e) {
    header('Location: users.php?error=db');
    exit;
}

header('Location: users.php?success=1');
exit;
