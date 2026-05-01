<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/incluir/db_connect.php';

$produto = [];
$page_title = 'Adicionar Novo Produto';
$is_edit = false;

if (isset($_GET['id'])) {
    $is_edit = true;
    $page_title = 'Editar Produto';
    $stmt = $pdo->prepare("SELECT * FROM produtos_financeiros WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$produto) {
        header('Location: produtos.php');
        exit;
    }
}
require_once __DIR__ . '/incluir/header.php';
?>

<h2><?php echo $page_title; ?></h2>
<form action="salvar_produto.php" method="POST" class="mt-4" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $produto['id'] ?? ''; ?>">
    <input type="hidden" name="logo_url_atual" value="<?= htmlspecialchars($produto['logo_url'] ?? '') ?>">


    <div class="card shadow-sm">
        <div class="card-body">
            <h4>Informação Principal</h4>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nome_entidade" class="form-label">Nome da Entidade</label>
                    <input type="text" class="form-control" id="nome_entidade" name="nome_entidade" value="<?= htmlspecialchars($produto['nome_entidade'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tipo_credito" class="form-label">Tipo de Crédito</label>
                    <select class="form-select" id="tipo_credito" name="tipo_credito" required>
                        <option value="">Selecione...</option>
                        <optgroup label="Crédito Pessoal">
    <option value="Pessoal" <?php echo ($produto['tipo_credito'] ?? '') === 'Pessoal' ? 'selected' : ''; ?>>Pessoal</option>
    <option value="Automóvel" <?php echo ($produto['tipo_credito'] ?? '') === 'Automóvel' ? 'selected' : ''; ?>>Automóvel</option>
    <option value="Energias Renováveis" <?php echo ($produto['tipo_credito'] ?? '') === 'Energias Renováveis' ? 'selected' : ''; ?>>Energias Renováveis</option>
    <option value="Moto" <?php echo ($produto['tipo_credito'] ?? '') === 'Moto' ? 'selected' : ''; ?>>Moto</option>
    <option value="Educação" <?php echo ($produto['tipo_credito'] ?? '') === 'Educação' ? 'selected' : ''; ?>>Educação</option>
</optgroup>
                        <optgroup label="Crédito Habitação">
                            <option value="habitacao" <?= ($produto['tipo_credito'] ?? '') == 'habitacao' ? 'selected' : '' ?>>Habitação</option>
                            <option value="habitacao_jovem" <?= ($produto['tipo_credito'] ?? '') == 'habitacao_jovem' ? 'selected' : '' ?>>Habitação Jovem</option>
                            <option value="transferencia" <?= ($produto['tipo_credito'] ?? '') == 'transferencia' ? 'selected' : '' ?>>Transferência</option>
                        </optgroup>
                         <optgroup label="Crédito Consolidado">
                            <option value="Consolidado sem Hipoteca" <?= ($produto['tipo_credito'] ?? '') == 'Consolidado sem Hipoteca' ? 'selected' : '' ?>>Consolidado s/ Hipoteca</option>
                            <option value="Consolidado com Hipoteca" <?= ($produto['tipo_credito'] ?? '') == 'Consolidado com Hipoteca' ? 'selected' : '' ?>>Consolidado c/ Hipoteca</option>
                        </optgroup>
                    </select>
                </div>
            </div>

            <div class="row mt-3">
                 <div class="col-md-12 mb-3">
                    <label for="logo_file" class="form-label">Logótipo</label>
                    <input class="form-control" type="file" id="logo_file" name="logo_file">
                    <?php if ($is_edit && !empty($produto['logo_url'])): ?>
                        <div class="mt-2">
                            <small>Logótipo Atual:</small><br>
                            <img src="/credito/<?= htmlspecialchars($produto['logo_url']) ?>" alt="Logótipo" style="max-height: 40px; background: #f0f0f0; padding: 5px; border-radius: 5px;">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <hr>
            <h4>Taxas e Prazos</h4>
             <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tan_desde" class="form-label">TAN (Desde %)</label>
                    <input type="number" step="0.01" class="form-control" id="tan_desde" name="tan_desde" value="<?= htmlspecialchars($produto['tan_desde'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="taeg_desde" class="form-label">TAEG (Desde %)</label>
                    <input type="number" step="0.01" class="form-control" id="taeg_desde" name="taeg_desde" value="<?= htmlspecialchars($produto['taeg_desde'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="montante_minimo" class="form-label">Montante Mínimo (€)</label>
                    <input type="number" class="form-control" id="montante_minimo" name="montante_minimo" value="<?= htmlspecialchars($produto['montante_minimo'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="montante_maximo" class="form-label">Montante Máximo (€)</label>
                    <input type="number" class="form-control" id="montante_maximo" name="montante_maximo" value="<?= htmlspecialchars($produto['montante_maximo'] ?? '') ?>" required>
                </div>
                 <div class="col-md-6 mb-3">
                    <label for="prazo_minimo_meses" class="form-label">Prazo Mínimo (meses)</label>
                    <input type="number" class="form-control" id="prazo_minimo_meses" name="prazo_minimo_meses" value="<?= htmlspecialchars($produto['prazo_minimo_meses'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="prazo_maximo_meses" class="form-label">Prazo Máximo (meses)</label>
                    <input type="number" class="form-control" id="prazo_maximo_meses" name="prazo_maximo_meses" value="<?= htmlspecialchars($produto['prazo_maximo_meses'] ?? '') ?>" required>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary mt-3">Salvar Produto</button>
    <a href="produtos.php" class="btn btn-secondary mt-3">Cancelar</a>
</form>

<?php require_once __DIR__ . '/incluir/footer.php'; ?>