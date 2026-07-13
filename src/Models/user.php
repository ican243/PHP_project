<?php

namespace App\Models;

use App\Database;

class User
{
    public static function findByUsername(string $username): ?array
    {
        $stmt = Database::get()->prepare(
            'SELECT id, username, password_hash, name, role, status
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
}
