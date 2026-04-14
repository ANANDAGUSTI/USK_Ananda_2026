<?php
declare(strict_types=1);

const BASE_URL = '/USK2026';

function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return BASE_URL . ($path !== '' ? '/' . $path : '');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}
