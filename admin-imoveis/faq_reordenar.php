<?php
// Ficheiro: admin/faq_reordenar.php
require_once 'incluir/auth.php';
require_once 'incluir/db_connect.php';

if (!isset($_GET['id']) || !isset($_GET['direction'])) {
    header('Location: faq_gerir.php');
    exit;
}

$id_a = (int)$_GET['id'];
$direction = $_GET['direction'];

// Inicia a transação
$pdo->beginTransaction();

try {
    // Obtém a ordem do item que queremos mover
    $stmt_a = $pdo->prepare("SELECT ordem FROM faqs WHERE id = ?");
    $stmt_a->execute([$id_a]);
    $ordem_a = $stmt_a->fetchColumn();

    $stmt_b = null;
    if ($direction === 'up') {
        // Encontra o item imediatamente acima (com a menor ordem maior que a atual)
        $stmt_b = $pdo->prepare("SELECT id, ordem FROM faqs WHERE ordem < ? ORDER BY ordem DESC LIMIT 1");
        $stmt_b->execute([$ordem_a]);
    } else { // down
        // Encontra o item imediatamente abaixo (com a maior ordem menor que a atual)
        $stmt_b = $pdo->prepare("SELECT id, ordem FROM faqs WHERE ordem > ? ORDER BY ordem ASC LIMIT 1");
        $stmt_b->execute([$ordem_a]);
    }

    $item_b = $stmt_b->fetch(PDO::FETCH_ASSOC);

    // Se um item para troca for encontrado
    if ($item_b) {
        $id_b = $item_b['id'];
        $ordem_b = $item_b['ordem'];

        // Troca as ordens
        $update_stmt = $pdo->prepare("UPDATE faqs SET ordem = ? WHERE id = ?");
        $update_stmt->execute([$ordem_b, $id_a]);
        $update_stmt->execute([$ordem_a, $id_b]);
    }

    // Confirma as alterações
    $pdo->commit();

} catch (Exception $e) {
    // Em caso de erro, desfaz tudo
    $pdo->rollBack();
    // Poderia adicionar uma mensagem de erro aqui
}

header('Location: faq_gerir.php');
exit;