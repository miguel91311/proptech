<?php
require_once 'incluir/auth.php'; // Caminho corrigido
require_once 'incluir/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $pergunta = $_POST['pergunta'];
    $resposta = $_POST['resposta'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($id)) {
        $stmt = $pdo->prepare("INSERT INTO faqs (pergunta, resposta, is_active) VALUES (?, ?, ?)");
        $stmt->execute([$pergunta, $resposta, $is_active]);
    } else {
        $stmt = $pdo->prepare("UPDATE faqs SET pergunta = ?, resposta = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$pergunta, $resposta, $is_active, $id]);
    }

    header("Location: faq_gerir.php");
    exit;
}
?>