<?php

namespace App\Models;

use App\Database;

class AdminLog
{
    public static function record(int $adminId, int $targetId, string $action): void
    {
        $stmt = Database::get()->prepare(
            'INSERT INTO admin_logs (admin_id, target_user_id, action) VALUES (?, ?, ?)'
        );
        $stmt->bind_param('iis', $adminId, $targetId, $action);
        $stmt->execute();
    }
}
