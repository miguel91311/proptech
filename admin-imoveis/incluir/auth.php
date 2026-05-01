<?php
// Ficheiro: admin/incluir/auth.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Se a sessão 'admin_logged_in' não existir ou não for verdadeira,
// redireciona o utilizador para a página de login.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}