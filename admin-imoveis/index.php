<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/incluir/db_connect.php';

// Filtro por tipo de simulação
$filter = '';
$params = [];
if (!empty($_GET['tipo'])) {
    $filter = 'WHERE tipo_simulacao = ?';
    $params[] = $_GET['tipo'];
}

$stmt = $pdo->prepare("SELECT id, nome_completo, tipo_simulacao, finalidade, data_submissao, resultado_pre_aprovacao FROM simulacoes $filter ORDER BY id DESC");
$stmt->execute($params);
$simulacoes = $stmt->fetchAll();

$page_title = "Dashboard - Simulações";
require_once __DIR__ . '/incluir/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Todas as Simulações</h2>
    <div class="btn-group">
        <a href="index.php" class="btn btn-outline-secondary <?= empty($_GET['tipo']) ? 'active' : '' ?>">Todas</a>
        <a href="index.php?tipo=Pessoal" class="btn btn-outline-secondary <?= ($_GET['tipo'] ?? '') == 'Pessoal' ? 'active' : '' ?>">Pessoal</a>
        <a href="index.php?tipo=Habitacao" class="btn btn-outline-secondary <?= ($_GET['tipo'] ?? '') == 'Habitacao' ? 'active' : '' ?>">Habitação</a>
        <a href="index.php?tipo=Consolidado" class="btn btn-outline-secondary <?= ($_GET['tipo'] ?? '') == 'Consolidado' ? 'active' : '' ?>">Consolidado</a>
    </div>
</div>
<p>Visualize todas as simulações submetidas através dos diferentes simuladores do portal.</p>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Finalidade</th>
                        <th>Data</th>
                        <th>Resultado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($simulacoes)): ?>
                        <tr><td colspan="7" class="text-center p-4">Nenhuma simulação encontrada para este filtro.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($simulacoes as $sim): ?>
                    <tr>
                        <td>#<?php echo $sim['id']; ?></td>
                        <td><?php echo htmlspecialchars($sim['nome_completo'] ?? 'N/A'); ?></td>
                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($sim['tipo_simulacao']); ?></span></td>
                        <td><?php echo htmlspecialchars($sim['finalidade'] ?? $sim['tipo_credito'] ?? 'N/A'); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($sim['data_submissao'])); ?></td>
                        <td><?php echo htmlspecialchars($sim['resultado_pre_aprovacao']); ?></td>
                        <td>
                            <a href="ver_simulacao.php?id=<?php echo $sim['id']; ?>" class="btn btn-primary btn-sm">Ver Detalhes</a>
                            <!-- Botão de Apagar adicionado -->
                            <form action="apagar_simulacao.php" method="POST" class="d-inline" onsubmit="return confirm('Tem a certeza que quer apagar esta simulação? Esta ação é irreversível.');">
                                <input type="hidden" name="simulacao_id" value="<?php echo $sim['id']; ?>">
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

<?php require_once __DIR__ . '/incluir/footer.php'; ?>

