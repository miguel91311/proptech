<?php
session_start();
// Adicionar no topo para debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php'); exit;
}
require_once __DIR__ . '/incluir/db_connect.php';

// Função para gerar um slug amigável e único
function create_slug($string){
   // Converte para minúsculas e remove acentos
   $slug = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $string));
   // Substitui tudo o que não for letra, número ou hífen por um hífen
   $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
   // Remove hífens duplicados e no início/fim
   return trim($slug, '-');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação básica dos campos
    if (empty($_POST['titulo']) || empty($_POST['conteudo'])) {
        header('Location: blog_form.php?error=empty_fields');
        exit;
    }

    $id = $_POST['id'];
    $titulo = $_POST['titulo'];
    $resumo = $_POST['resumo'];
    $conteudo = $_POST['conteudo'];
    $autor = $_POST['autor'];
    
    // Gera um slug inicial e verifica se já existe para evitar duplicados
    $slug_base = create_slug($titulo);
    $slug = $slug_base;
    $counter = 1;

    $check_slug_stmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug = ? AND id != ?");
    do {
        $check_slug_stmt->execute([$slug, $id ?? 0]);
        $count = $check_slug_stmt->fetchColumn();
        if ($count > 0) {
            $slug = $slug_base . '-' . $counter;
            $counter++;
        }
    } while ($count > 0);

    // Gestão da Imagem
    $imagem_path = $_POST['imagem_atual'] ?? null;
    if (isset($_FILES['imagem_destaque']) && $_FILES['imagem_destaque']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/credito/blog_uploads/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                 header('Location: blog_form.php?id=' . $id . '&error=mkdir_failed');
                 exit;
            }
        }
        $filename = uniqid() . '-' . basename($_FILES['imagem_destaque']['name']);
        $destination = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['imagem_destaque']['tmp_name'], $destination)) {
            if(!empty($imagem_path) && file_exists($_SERVER['DOCUMENT_ROOT'] . $imagem_path)){
                unlink($_SERVER['DOCUMENT_ROOT'] . $imagem_path);
            }
            $imagem_path = '/credito/blog_uploads/' . $filename;
        } else {
            header('Location: blog_form.php?id=' . $id . '&error=upload_failed');
            exit;
        }
    }

    $params = [
        'titulo' => $titulo,
        'resumo' => $resumo,
        'conteudo' => $conteudo,
        'autor' => $autor,
        'imagem_destaque' => $imagem_path,
        'slug' => $slug
    ];

    try {
        if (empty($id)) {
            $sql = "INSERT INTO blog_posts (titulo, resumo, conteudo, autor, imagem_destaque, slug) VALUES (:titulo, :resumo, :conteudo, :autor, :imagem_destaque, :slug)";
        } else {
            $sql = "UPDATE blog_posts SET titulo=:titulo, resumo=:resumo, conteudo=:conteudo, autor=:autor, imagem_destaque=:imagem_destaque, slug=:slug WHERE id=:id";
            $params['id'] = $id;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

    } catch (PDOException $e) {
        $error_message = urlencode($e->getMessage());
        header('Location: blog_form.php?id=' . $id . '&error=db_error&msg=' . $error_message);
        exit;
    }

    header('Location: blog_gerir.php?status=success');
    exit;
}
header('Location: blog_gerir.php');
exit;

