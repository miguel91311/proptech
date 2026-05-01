<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php'); 
    exit;
}
require_once __DIR__ . '/incluir/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        // Opcional, mas recomendado: apagar o ficheiro da imagem do servidor
        $stmt_img = $pdo->prepare("SELECT imagem_destaque FROM blog_posts WHERE id = ?");
        $stmt_img->execute([$_POST['id']]);
        $imagem_path = $stmt_img->fetchColumn();
        if ($imagem_path && file_exists($_SERVER['DOCUMENT_ROOT'] . $imagem_path)) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $imagem_path);
        }

        // Apagar o registo da base de dados
        $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->execute([$_POST['id']]);

    } catch (PDOException $e) {
        // Se houver um erro, pode mostrar uma mensagem para debugging
        die("Erro ao apagar o artigo: " . $e->getMessage());
    }
}

// Redireciona de volta para a página de gestão do blog em qualquer caso
header('Location: blog_gerir.php?status=deleted');
exit;

