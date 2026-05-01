<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/incluir/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simulacao_id'])) {
    $simulacao_id_para_apagar = $_POST['simulacao_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM simulacoes WHERE id = ?");
        $stmt->execute([$simulacao_id_para_apagar]);
    } catch (Exception $e) {
        die("Ocorreu um erro ao tentar apagar o registo: " . $e->getMessage());
    }
}

header("Location: index.php");
exit;
?>
