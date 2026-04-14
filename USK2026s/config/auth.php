<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function auth_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function auth_role(): ?string
{
    $u = auth_user();
    return $u['role'] ?? null;
}

function require_role(string $role): void
{
    $u = auth_user();
    if (!$u || ($u['role'] ?? '') !== $role) {
        redirect('login.php');
    }
}

function require_any_role(array $roles): void
{
    $u = auth_user();
    $r = $u['role'] ?? '';
    if (!$u || !in_array($r, $roles, true)) {
        redirect('login.php');
    }
}
