<?php
require_once 'incluir/header.php';
require_once 'incluir/db_connect.php';

$faq = ['id' => '', 'pergunta' => '', 'resposta' => '', 'is_active' => 1];
$page_title = 'Adicionar Nova Pergunta';

if (isset($_GET['id'])) {
    $page_title = 'Editar Pergunta';
    $stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $faq = $stmt->fetch();
    if (!$faq) {
        // Lidar com ID inválido
        header("Location: faq_gerir.php");
        exit;
    }
}
?>

<div class="container mt-4">
    <h2><?php echo $page_title; ?></h2>
    <form action="faq_salvar.php" method="post">
        <input type="hidden" name="id" value="<?php echo $faq['id']; ?>">

        <div class="mb-3">
            <label for="pergunta" class="form-label">Pergunta</label>
            <input type="text" class="form-control" id="pergunta" name="pergunta" value="<?php echo htmlspecialchars($faq['pergunta']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="resposta" class="form-label">Resposta</label>
            <textarea class="form-control" id="resposta" name="resposta" rows="6" required><?php echo htmlspecialchars($faq['resposta']); ?></textarea>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" <?php echo $faq['is_active'] ? 'checked' : ''; ?>>
            <label class="form-check-label" for="is_active">Visível no site</label>
        </div>

        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="faq_gerir.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php require_once 'incluir/footer.php'; ?>