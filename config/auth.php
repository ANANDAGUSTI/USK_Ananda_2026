<?php
require_once __DIR__ . '/app.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function auth_user()
{
    if (isset($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    return null;
}

function auth_role()
{
    $user = auth_user();
    if ($user === null) {
        return null;
    }
    if (isset($user['role'])) {
        return $user['role'];
    }
    return null;
}

function require_role($role)
{
    $user = auth_user();
    $roleUser = '';
    if ($user !== null && isset($user['role'])) {
        $roleUser = $user['role'];
    }
    if ($user === null || $roleUser !== $role) {
        redirect('login.php');
    }
}

function require_any_role($roles)
{
    $user = auth_user();
    $roleUser = '';
    if ($user !== null && isset($user['role'])) {
        $roleUser = $user['role'];
    }
    if ($user === null) {
        redirect('login.php');
    }
    if (!in_array($roleUser, $roles, true)) {
        redirect('login.php');
    }
}
