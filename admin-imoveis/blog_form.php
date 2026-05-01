<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/incluir/db_connect.php';

$artigo = [];
$page_title = 'Criar Novo Artigo';
$is_edit = false;

if (isset($_GET['id'])) {
    $is_edit = true;
    $page_title = 'Editar Artigo';
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $artigo = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$artigo) {
        header('Location: blog_gerir.php');
        exit;
    }
}

include __DIR__ . '/incluir/header.php';

// --- NOVO BLOCO PARA MOSTRAR ERROS ---
if(isset($_GET['error'])) {
    $error_message = 'Ocorreu um erro desconhecido.';
    switch($_GET['error']) {
        case 'empty_fields':
            $error_message = 'Erro: Os campos "Título" e "Conteúdo Completo" são obrigatórios.';
            break;
        case 'mkdir_failed':
            $error_message = 'Erro de permissões: Não foi possível criar a pasta para as imagens. Contacte o suporte do seu alojamento web e verifique as permissões da pasta raiz.';
            break;
        case 'upload_failed':
            $error_message = 'Erro: Falha ao carregar a imagem. Verifique o tamanho e o formato do ficheiro.';
            break;
        case 'db_error':
            $error_message = 'Erro de base de dados: ' . htmlspecialchars($_GET['msg'] ?? 'Não foi possível guardar o artigo.');
            break;
    }
    echo '<div class="alert alert-danger">' . $error_message . '</div>';
}
// --- FIM DO NOVO BLOCO ---
?>

<h2><?php echo $page_title; ?></h2>
<form action="blog_salvar.php" method="POST" class="mt-4" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $artigo['id'] ?? ''; ?>">
    
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título</label>
                <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($artigo['titulo'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="resumo" class="form-label">Resumo (Texto que aparece na listagem)</label>
                <textarea class="form-control" id="resumo" name="resumo" rows="3"><?php echo htmlspecialchars($artigo['resumo'] ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="conteudo" class="form-label">Conteúdo Completo</label>
                <textarea class="form-control" id="conteudo" name="conteudo" rows="15" required><?php echo htmlspecialchars($artigo['conteudo'] ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="autor" class="form-label">Autor</label>
                <input type="text" class="form-control" id="autor" name="autor" value="<?php echo htmlspecialchars($artigo['autor'] ?? 'Equipa O Crédito Unificado'); ?>">
            </div>
            <div class="mb-3">
                <label for="imagem_destaque" class="form-label">Imagem de Destaque</label>
                <input class="form-control" type="file" id="imagem_destaque" name="imagem_destaque" accept="image/*">
                <?php if ($is_edit && !empty($artigo['imagem_destaque'])): ?>
                    <div class="mt-2">
                        <small>Imagem Atual:</small><br>
                        <img src="<?php echo htmlspecialchars($artigo['imagem_destaque']); ?>" alt="Imagem atual" style="max-height: 100px; border: 1px solid #ccc; padding: 5px;">
                        <input type="hidden" name="imagem_atual" value="<?php echo htmlspecialchars($artigo['imagem_destaque']); ?>">
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary mt-3">Salvar Artigo</button>
    <a href="blog_gerir.php" class="btn btn-secondary mt-3">Cancelar</a>
</form>

<?php include __DIR__ . '/incluir/footer.php'; ?>
