<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/incluir/db_connect.php';
$produtos = $pdo->query("SELECT * FROM produtos_financeiros ORDER BY tipo_credito, nome_entidade")->fetchAll();
$page_title = "Gestão de Produtos";
require_once __DIR__ . '/incluir/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Gestão de Produtos Financeiros</h2>
    <a href="produto_form.php" class="btn btn-success">Adicionar Novo Produto</a>
</div>

<?php
// Group products by 'tipo_credito'
$produtos_por_tipo = [];
foreach ($produtos as $p) {
    $tipo = $p['tipo_credito'];
    if (!isset($produtos_por_tipo[$tipo])) {
        $produtos_por_tipo[$tipo] = [];
    }
    $produtos_por_tipo[$tipo][] = $p;
}
?>

<?php if (empty($produtos)): ?>
    <div class="alert alert-info">Nenhum produto financeiro encontrado. <a href="produto_form.php" class="alert-link">Adicione o seu primeiro produto</a>.</div>
<?php else: ?>
    <div class="row row-cols-1 g-4">
        <?php foreach ($produtos_por_tipo as $tipo => $produtos_tipo): ?>
            <div class="col">
                <div class="card shadow-sm border-0 border-top border-primary border-4">
                    <div class="card-header bg-white pt-3 pb-2 border-bottom-0">
                        <h4 class="mb-0 text-primary">
                            <i class="fas fa-box-open me-2"></i><?= htmlspecialchars($tipo) ?>
                            <span class="badge bg-secondary rounded-pill ms-2 fs-6"><?= count($produtos_tipo) ?></span>
                        </h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Entidade</th>
                                        <th>TAEG Desde</th>
                                        <th>TAN Desde</th>
                                        <th class="text-end pe-4">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($produtos_tipo as $produto): ?>
                                    <tr>
                                        <td class="ps-4 fw-medium text-dark">
                                            <?= htmlspecialchars($produto['nome_entidade']) ?>
                                        </td>
                                        <td><span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1"><?= htmlspecialchars($produto['taeg_desde']) ?>%</span></td>
                                        <td><span class="badge bg-info bg-opacity-10 text-info border border-info-subtle px-2 py-1"><?= htmlspecialchars($produto['tan_desde']) ?>%</span></td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group" role="group">
                                                <a href="produto_form.php?id=<?= $produto['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <a href="apagar_produto.php?id=<?= $produto['id'] ?>" class="btn btn-sm btn-outline-danger" title="Apagar" onclick="return confirm('Tem a certeza que quer apagar este produto (<?= htmlspecialchars($produto['nome_entidade']) ?>)?');">
                                                    <i class="fas fa-trash-alt"></i> Apagar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/incluir/footer.php'; ?>