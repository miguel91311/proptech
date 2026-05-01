<?php
// Ficheiro: admin/faq_salvar_ordem.php
require_once 'incluir/auth.php';
require_once 'incluir/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ordem'])) {
    $ordem_array = $_POST['ordem'];

    $pdo->beginTransaction();
    try {
        foreach ($ordem_array as $id => $ordem) {
            $stmt = $pdo->prepare("UPDATE faqs SET ordem = ? WHERE id = ?");
            $stmt->execute([(int)$ordem, (int)$id]);
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        // Pode adicionar uma mensagem de erro aqui, se desejar
    }
}

header('Location: faq_gerir.php');
exit;