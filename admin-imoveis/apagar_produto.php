<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/incluir/db_connect.php';

if (!isset($_GET['id'])) {
    header('Location: produtos.php');
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM produtos_financeiros WHERE id = ?");
$stmt->execute([$id]);

header('Location: produtos.php');
exit;
?>