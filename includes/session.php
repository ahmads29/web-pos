<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /nafahat pos/login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /dashboard.php');
        exit();
    }
}

function logout() {
    session_destroy();
    header('Location: /nafahat pos/login.php');
    exit();
}
?> 