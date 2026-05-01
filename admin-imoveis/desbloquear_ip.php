<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/incluir/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['desbloquear_ip'])) {
    $ip_para_desbloquear = $_POST['desbloquear_ip'];

    $stmt = $pdo->prepare("DELETE FROM ip_bloqueados WHERE ip_address = ?");
    $stmt->execute([$ip_para_desbloquear]);
}

header("Location: ip_bloqueados.php");
exit;
?>
