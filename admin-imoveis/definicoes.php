<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/incluir/db_connect.php';

$mensagem = '';
$tipo_mensagem = '';

// --- GUARDAR ALTERAÇÕES ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Taxa de Esforço Máxima
        $taxa = filter_input(INPUT_POST, 'taxa_esforco_maxima', FILTER_VALIDATE_FLOAT);
        if ($taxa !== false && $taxa > 0 && $taxa <= 100) {
            $stmt = $pdo->prepare("INSERT INTO regras_simulacao (regra_nome, regra_valor) VALUES ('taxa_esforco_maxima', ?) ON DUPLICATE KEY UPDATE regra_valor = ?");
            $stmt->execute([$taxa, $taxa]);
        }

        // Condições de Recusa
        $recusa = trim($_POST['condicoes_recusa'] ?? '');
        if (!empty($recusa)) {
            $stmt = $pdo->prepare("INSERT INTO regras_simulacao (regra_nome, regra_valor) VALUES ('condicoes_recusa', ?) ON DUPLICATE KEY UPDATE regra_valor = ?");
            $stmt->execute([$recusa, $recusa]);
        }

        // Condições de Análise
        $analise = trim($_POST['condicoes_analise'] ?? '');
        if (!empty($analise)) {
            $stmt = $pdo->prepare("INSERT INTO regras_simulacao (regra_nome, regra_valor) VALUES ('condicoes_analise', ?) ON DUPLICATE KEY UPDATE regra_valor = ?");
            $stmt->execute([$analise, $analise]);
        }

        // Guardar regras_estabilidade (tabela separada)
        // Apagar e recriar as regras de Não Aprovado e Revisão Manual
        pdo_delete_and_reinsert($pdo, 'nao_aprovado', 'tipo_contrato', $_POST['nao_aprovado_contrato'] ?? '');
        pdo_delete_and_reinsert($pdo, 'nao_aprovado', 'tipo_residencia', $_POST['nao_aprovado_residencia'] ?? '');
        pdo_delete_and_reinsert($pdo, 'revisao_manual', 'tipo_contrato', $_POST['revisao_contrato'] ?? '');
        pdo_delete_and_reinsert($pdo, 'revisao_manual', 'tipo_residencia', $_POST['revisao_residencia'] ?? '');

        $mensagem = 'Definições guardadas com sucesso!';
        $tipo_mensagem = 'success';
    } catch (PDOException $e) {
        $mensagem = 'Erro ao guardar: ' . $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}

function pdo_delete_and_reinsert($pdo, $nivel, $tipo, $valores_raw) {
    $pdo->prepare("DELETE FROM regras_estabilidade WHERE nivel_risco = ? AND tipo_regra = ?")->execute([$nivel, $tipo]);
    $valores = array_filter(array_map('trim', explode(',', $valores_raw)));
    foreach ($valores as $v) {
        $pdo->prepare("INSERT INTO regras_estabilidade (tipo_regra, valor, nivel_risco) VALUES (?, ?, ?)")->execute([$tipo, $v, $nivel]);
    }
}

// --- LER CONFIGURAÇÕES ATUAIS ---
$regras_sim = $pdo->query("SELECT regra_nome, regra_valor FROM regras_simulacao")->fetchAll(PDO::FETCH_KEY_PAIR);
$taxa_atual = $regras_sim['taxa_esforco_maxima'] ?? 35.0;
$condicoes_recusa = $regras_sim['condicoes_recusa'] ?? 'Não residente';
$condicoes_analise = $regras_sim['condicoes_analise'] ?? '';

$regras_est = $pdo->query("SELECT tipo_regra, valor, nivel_risco FROM regras_estabilidade")->fetchAll(PDO::FETCH_ASSOC);
$grupos = ['nao_aprovado' => ['tipo_contrato' => [], 'tipo_residencia' => []], 'revisao_manual' => ['tipo_contrato' => [], 'tipo_residencia' => []]];
foreach ($regras_est as $r) {
    if (isset($grupos[$r['nivel_risco']][$r['tipo_regra']])) {
        $grupos[$r['nivel_risco']][$r['tipo_regra']][] = $r['valor'];
    }
}

$page_title = "Definições - Regras de Aprovação";
require_once __DIR__ . '/incluir/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fas fa-sliders-h me-2 text-primary"></i>Definições do Sistema</h2>
        <p class="text-muted mb-0">Configure as regras de aprovação que se aplicam aos 3 simuladores de crédito.</p>
    </div>
</div>

<?php if ($mensagem): ?>
    <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?= $tipo_mensagem === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
        <?= htmlspecialchars($mensagem) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form method="POST" action="definicoes.php">

    <!-- TAXA DE ESFORÇO -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-percentage me-2"></i>Taxa de Esforço Máxima</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <label for="taxa_esforco_maxima" class="form-label fw-bold">Limite máximo permitido (%)</label>
                    <p class="text-muted small">Se a taxa de esforço do cliente superar este valor, o pedido é automaticamente <strong>Não Aprovado</strong>.</p>
                </div>
                <div class="col-md-4">
                    <div class="input-group input-group-lg">
                        <input type="number" class="form-control" id="taxa_esforco_maxima" name="taxa_esforco_maxima"
                               value="<?= htmlspecialchars($taxa_atual) ?>" min="10" max="80" step="0.5" required>
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="mt-2">
                        <input type="range" class="form-range" min="10" max="80" step="0.5"
                               value="<?= htmlspecialchars($taxa_atual) ?>"
                               oninput="document.getElementById('taxa_esforco_maxima').value = this.value">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-info mb-0">
                        <strong>Valor atual:</strong> <?= htmlspecialchars($taxa_atual) ?>%<br>
                        <small>Aplica-se aos 3 simuladores (Pessoal, Habitação, Consolidado).</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CRITÉRIOS POR TIPO DE CONTRATO / RESIDÊNCIA -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="fas fa-times-circle me-2"></i>Critérios de "Não Aprovado" Automático</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Se o cliente tiver um tipo de contrato ou residência nestas listas, é automaticamente <strong>Não Aprovado</strong> — sem calcular a taxa de esforço.</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="fas fa-file-contract me-1"></i>Tipos de Contrato (separados por vírgula)</label>
                    <textarea class="form-control" name="nao_aprovado_contrato" rows="3"><?= htmlspecialchars(implode(', ', $grupos['nao_aprovado']['tipo_contrato'])) ?></textarea>
                    <small class="text-muted">Ex: Recibos Verdes, Desempregado, Estágio</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="fas fa-home me-1"></i>Tipos de Residência (separados por vírgula)</label>
                    <textarea class="form-control" name="nao_aprovado_residencia" rows="3"><?= htmlspecialchars(implode(', ', $grupos['nao_aprovado']['tipo_residencia'])) ?></textarea>
                    <small class="text-muted">Ex: Não Residente, Sem Abrigo</small>
                </div>
            </div>
        </div>
    </div>

    <!-- CRITÉRIOS DE REVISÃO MANUAL -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-search me-2"></i>Critérios de "Revisão Manual"</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Se o cliente tiver um tipo de contrato ou residência nestas listas (e não for reprovado automaticamente), o pedido vai para <strong>Revisão Manual</strong>.</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="fas fa-file-contract me-1"></i>Tipos de Contrato (separados por vírgula)</label>
                    <textarea class="form-control" name="revisao_contrato" rows="3"><?= htmlspecialchars(implode(', ', $grupos['revisao_manual']['tipo_contrato'])) ?></textarea>
                    <small class="text-muted">Ex: Recibos Verdes, A Termo Certo, A Termo Incerto</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><i class="fas fa-home me-1"></i>Tipos de Residência (separados por vírgula)</label>
                    <textarea class="form-control" name="revisao_residencia" rows="3"><?= htmlspecialchars(implode(', ', $grupos['revisao_manual']['tipo_residencia'])) ?></textarea>
                    <small class="text-muted">Ex: Residente Temporário</small>
                </div>
            </div>
        </div>
    </div>

    <!-- CRITÉRIOS TEXTO (regras_simulacao) -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Condições via regras_simulacao (Habitação)</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Condições de Recusa Direta</label>
                    <input type="text" class="form-control" name="condicoes_recusa" value="<?= htmlspecialchars($condicoes_recusa) ?>">
                    <small class="text-muted">Separadas por vírgula. Aplicado no simulador de Habitação.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Condições de Análise Manual</label>
                    <input type="text" class="form-control" name="condicoes_analise" value="<?= htmlspecialchars($condicoes_analise) ?>">
                    <small class="text-muted">Separadas por vírgula. Ex: Residente Temporário, A Termo Certo</small>
                </div>
            </div>
        </div>
    </div>

    <!-- RESUMO DO FLUXO DE DECISÃO -->
    <div class="card shadow-sm mb-4 border-0" style="background: #f8f9fa;">
        <div class="card-body">
            <h6 class="fw-bold mb-3"><i class="fas fa-route me-2 text-primary"></i>Fluxo de Decisão (igual nos 3 simuladores)</h6>
            <div class="d-flex gap-2 flex-wrap align-items-center" style="font-size: 0.9rem;">
                <span class="badge bg-secondary px-3 py-2">1. Recusa Automática</span>
                <i class="fas fa-arrow-right text-muted"></i>
                <span class="badge bg-danger px-3 py-2">Não Aprovado</span>
                <i class="fas fa-arrow-right text-muted mx-1">↓ se não</i>
                <span class="badge bg-secondary px-3 py-2">2. Taxa Esforço > <?= $taxa_atual ?>%</span>
                <i class="fas fa-arrow-right text-muted"></i>
                <span class="badge bg-danger px-3 py-2">Não Aprovado</span>
                <i class="fas fa-arrow-right text-muted mx-1">↓ se não</i>
                <span class="badge bg-secondary px-3 py-2">3. Condições de Análise</span>
                <i class="fas fa-arrow-right text-muted"></i>
                <span class="badge bg-warning text-dark px-3 py-2">Revisão Manual</span>
                <i class="fas fa-arrow-right text-muted mx-1">↓ se não</i>
                <span class="badge bg-success px-3 py-2">Pré-Aprovado ✓</span>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-5">
        <button type="submit" class="btn btn-primary btn-lg px-5">
            <i class="fas fa-save me-2"></i>Guardar Definições
        </button>
        <a href="index.php" class="btn btn-outline-secondary btn-lg">Cancelar</a>
    </div>

</form>

<?php require_once __DIR__ . '/incluir/footer.php'; ?>
