<?php
const BASE_URL = '/USK2026';

function url($path = '')
{
    $path = ltrim($path, '/');
    if ($path === '') {
        return BASE_URL;
    }
    return BASE_URL . '/' . $path;
}

function redirect($path)
{
    $tujuan = url($path);
    header('Location: ' . $tujuan);
    exit;
}
