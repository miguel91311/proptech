<?php
require_once 'incluir/header.php';
require_once 'incluir/db_connect.php';

$stmt = $pdo->query("SELECT id, pergunta, is_active, ordem FROM faqs ORDER BY ordem ASC");
$faqs = $stmt->fetchAll();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Gerir Perguntas Frequentes (FAQs)</h2>
        <a href="faq_form.php" class="btn btn-success">Adicionar Nova Pergunta</a>
    </div>

    <form action="faq_salvar_ordem.php" method="post">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="width: 100px;">Ordem</th>
                    <th>Pergunta</th>
                    <th style="width: 120px;">Estado</th>
                    <th style="width: 180px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($faqs as $faq): ?>
                <tr>
                    <td>
                        <input type="number" name="ordem[<?php echo $faq['id']; ?>]" value="<?php echo $faq['ordem']; ?>" class="form-control text-center">
                    </td>
                    <td><?php echo htmlspecialchars($faq['pergunta']); ?></td>
                    <td><?php echo $faq['is_active'] ? 'Ativo' : 'Inativo'; ?></td>
                    <td>
                        <a href="faq_form.php?id=<?php echo $faq['id']; ?>" class="btn btn-primary btn-sm">Editar</a>
                        <a href="faq_apagar.php?id=<?php echo $faq['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem a certeza que deseja apagar esta pergunta?');">Apagar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="btn btn-info">Salvar Ordem</button>
    </form>
</div>

<?php require_once 'incluir/footer.php'; ?>