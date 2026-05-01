<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/incluir/db_connect.php';

$artigos = $pdo->query("SELECT id, titulo, data_publicacao FROM blog_posts ORDER BY data_publicacao DESC")->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Gerir Blog";
include __DIR__ . '/incluir/header.php';

// --- NOVO BLOCO PARA MOSTRAR SUCESSO ---
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    echo '<div class="alert alert-success">Artigo guardado com sucesso!</div>';
}
// --- FIM DO NOVO BLOCO ---
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Gerir Artigos do Blog</h2>
    <a href="blog_form.php" class="btn btn-success">Criar Novo Artigo</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Título</th>
                        <th>Data de Publicação</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($artigos as $artigo): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($artigo['titulo']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($artigo['data_publicacao'])); ?></td>
                        <td>
                            <a href="blog_form.php?id=<?php echo $artigo['id']; ?>" class="btn btn-primary btn-sm">Editar</a>
                            <form action="blog_apagar.php" method="POST" class="d-inline" onsubmit="return confirm('Tem a certeza que quer apagar este artigo?');">
                                <input type="hidden" name="id" value="<?php echo $artigo['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Apagar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/incluir/footer.php'; ?>
