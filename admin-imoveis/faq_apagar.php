<?php
require_once 'incluir/auth.php'; // Caminho corrigido
require_once 'incluir/db_connect.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: faq_gerir.php");
exit;
?>