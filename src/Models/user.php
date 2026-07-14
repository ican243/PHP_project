<?php

namespace App\Models;

use App\Database;

class User
{
    public static function findByUsername(string $username): ?array
    {
        $stmt = Database::get()->prepare(
            'SELECT id, username, password_hash, name, role, status, fail_count, locked_until
             FROM users
             WHERE username = ?'
        );

        $stmt->bind_param('s', $username);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();

        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::get()->prepare(
            'SELECT username, name, bio, role, last_login_at, last_login_ip, created_at
             FROM users
             WHERE id = ?'
        );

        $stmt->bind_param('i', $id);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();

        return $user ?: null;
    }

    public static function create(string $username, string $passwordHash, string $name): void
    {
        $stmt = Database::get()->prepare(
            'INSERT INTO users (username, password_hash, name)
             VALUES (?, ?, ?)'
        );

        $stmt->bind_param('sss', $username, $passwordHash, $name);
        $stmt->execute();
    }

    public static function updateLastLogin(int $id, string $ip): void
    {
        $stmt = Database::get()->prepare(
            'UPDATE users
             SET last_login_at = NOW(), last_login_ip = ?
             WHERE id = ?'
        );

        $stmt->bind_param('si', $ip, $id);
        $stmt->execute();
    }

    public static function updateProfile(int $id, string $name, string $bio): void
    {
        $stmt = Database::get()->prepare(
            'UPDATE users
             SET name = ?, bio = ?
             WHERE id = ?'
        );

        $stmt->bind_param('ssi', $name, $bio, $id);
        $stmt->execute();
    }

    public static function getPasswordHash(int $id): ?string
    {
        $stmt = Database::get()->prepare(
            'SELECT password_hash
             FROM users
             WHERE id = ?'
        );

        $stmt->bind_param('i', $id);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        return $row['password_hash'] ?? null;
    }

    public static function updatePassword(int $id, string $newHash): void
    {
        $stmt = Database::get()->prepare(
            'UPDATE users
             SET password_hash = ?
             WHERE id = ?'
        );

        $stmt->bind_param('si', $newHash, $id);
        $stmt->execute();
    }

    // ===== 로그인 실패 잠금 관련 =====

    public static function incrementFailCount(int $id): void
    {
        $stmt = Database::get()->prepare(
            'UPDATE users SET fail_count = fail_count + 1 WHERE id = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    public static function lockAccount(int $id): void
    {
        $stmt = Database::get()->prepare(
            'UPDATE users SET locked_until = DATE_ADD(NOW(), INTERVAL 5 MINUTE), fail_count = 0 WHERE id = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    public static function resetFailCount(int $id): void
    {
        $stmt = Database::get()->prepare(
            'UPDATE users SET fail_count = 0, locked_until = NULL WHERE id = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    // ===== 관리자용 =====

    public static function listForAdmin(string $keyword, int $perPage, int $offset): array
    {
        if ($keyword !== '') {
            $like = '%' . $keyword . '%';
            $stmt = Database::get()->prepare(
                "SELECT id, username, name, role, status, can_edit_users, created_at
                 FROM users WHERE username LIKE ? OR name LIKE ?
                 ORDER BY id DESC LIMIT ? OFFSET ?"
            );
            $stmt->bind_param('ssii', $like, $like, $perPage, $offset);
        } else {
            $stmt = Database::get()->prepare(
                "SELECT id, username, name, role, status, can_edit_users, created_at
                 FROM users ORDER BY id DESC LIMIT ? OFFSET ?"
            );
            $stmt->bind_param('ii', $perPage, $offset);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(\MYSQLI_ASSOC);
    }

    public static function countForAdmin(string $keyword): int
    {
        if ($keyword !== '') {
            $like = '%' . $keyword . '%';
            $stmt = Database::get()->prepare(
                "SELECT COUNT(*) AS cnt FROM users WHERE username LIKE ? OR name LIKE ?"
            );
            $stmt->bind_param('ss', $like, $like);
        } else {
            $stmt = Database::get()->prepare("SELECT COUNT(*) AS cnt FROM users");
        }
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['cnt'];
    }

    public static function listAllForCsv(string $keyword): array
    {
        if ($keyword !== '') {
            $like = '%' . $keyword . '%';
            $stmt = Database::get()->prepare(
                "SELECT id, username, name, role, status, created_at
                 FROM users WHERE username LIKE ? OR name LIKE ? ORDER BY id DESC"
            );
            $stmt->bind_param('ss', $like, $like);
        } else {
            $stmt = Database::get()->prepare(
                "SELECT id, username, name, role, status, created_at FROM users ORDER BY id DESC"
            );
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(\MYSQLI_ASSOC);
    }

    public static function updateStatus(int $id, string $status): void
    {
        $stmt = Database::get()->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
    }

    public static function setEditPermission(int $id, bool $value): void
    {
        $v = $value ? 1 : 0;
        $stmt = Database::get()->prepare(
            "UPDATE users SET can_edit_users = ? WHERE id = ? AND role = 'sub_admin'"
        );
        $stmt->bind_param('ii', $v, $id);
        $stmt->execute();
    }

    public static function resetPassword(int $id): string
    {
        $tempPassword = bin2hex(random_bytes(4));
        $hash = password_hash($tempPassword, PASSWORD_DEFAULT);
        $stmt = Database::get()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->bind_param('si', $hash, $id);
        $stmt->execute();
        return $tempPassword;
    }
}
