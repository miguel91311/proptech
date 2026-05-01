<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/incluir/db_connect.php';

// Tratar Aprovação de Limite
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'approve_limit') {
    $cp_id = (int)$_POST['cp_id'];
    $new_limit = (float)$_POST['new_limit'];
    
    $pdo->prepare("UPDATE credit_profiles SET approved_max_limit = ?, credit_status = 'approved' WHERE id = ?")->execute([$new_limit, $cp_id]);
    
    // Marcar KYC do utilizador base como aprovado
    $stmt_user = $pdo->prepare("SELECT user_id FROM credit_profiles WHERE id = ?");
    $stmt_user->execute([$cp_id]);
    $uid = $stmt_user->fetchColumn();
    if($uid) {
        $pdo->prepare("UPDATE users SET kyc_status = 'approved' WHERE id = ?")->execute([$uid]);
    }
    
    $_SESSION['msg'] = "<div class='alert alert-success mt-2'>Limite de €" . number_format($new_limit, 2, ',', '.') . " Aprovado com sucesso!</div>";
    header('Location: leiloes_compradores.php');
    exit;
}

// Tratar Remoção de Dossier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_dossier') {
    $cp_id = (int)$_POST['cp_id'];
    
    // Obter user_id para resetar o KYC
    $stmt_u = $pdo->prepare("SELECT user_id FROM credit_profiles WHERE id = ?");
    $stmt_u->execute([$cp_id]);
    $uid = $stmt_u->fetchColumn();
    if($uid) {
        $pdo->prepare("UPDATE users SET kyc_status = 'pending' WHERE id = ?")->execute([$uid]);
    }
    
    $pdo->prepare("DELETE FROM credit_profiles WHERE id = ?")->execute([$cp_id]);
    
    $_SESSION['msg'] = "<div class='alert alert-info mt-2'><i class='fas fa-trash-alt'></i> Dossier de comprador removido! O cliente poderá submeter nova avaliação.</div>";
    header('Location: leiloes_compradores.php');
    exit;
}

// Ler pendentes
$stmt = $pdo->query("SELECT cp.*, u.name, u.email, u.nif FROM credit_profiles cp JOIN users u ON cp.user_id = u.id ORDER BY cp.credit_status DESC, cp.id DESC");
$profiles = $stmt->fetchAll();

$page_title = "Leilões: Gestão de Compradores CRM";
require_once __DIR__ . '/incluir/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-primary"><i class="fas fa-users-cog"></i> Mód. Leilões: CRM Cartões e Dossiers</h2>
</div>

<?= $_SESSION['msg'] ?? '' ?>
<?php unset($_SESSION['msg']); ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Comprador</th>
                        <th>NIF / Email</th>
                        <th>Rendimentos Decl.</th>
                        <th>Estado de Crédito</th>
                        <th class="pe-4 text-end">Ação Administrativa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($profiles as $p): ?>
                    <?php
                        $modal_id = "modalDossier" . $p['id'];
                        // Obter o Produto de Habitação com menor TAN para cálculo da Estimativa base sugerida
                        $stmtProdAdmin = $pdo->prepare("SELECT MIN(tan_desde) as min_tan, MAX(prazo_maximo_meses) as max_prazo FROM produtos_financeiros WHERE is_active = 1 AND tipo_credito LIKE '%Habita%'");
                        $stmtProdAdmin->execute();
                        $prodAdminCalc = $stmtProdAdmin->fetch();
                        
                        $tan = (float)($prodAdminCalc['min_tan'] ?? 0);
                        if ($tan <= 0) $tan = 4.0;
                        $prazo_meses_db = (int)($prodAdminCalc['max_prazo'] ?? 480);
                        if ($prazo_meses_db <= 0) $prazo_meses_db = 480;
                        
                        // Matemática da Maturidade (75 Anos)
                        $age_1 = (int)($p['age_1'] ?? 35);
                        $age_2 = (int)($p['age_2'] ?? 0);
                        $max_age = max($age_1, $age_2);
                        $anos_maximos_idade = 75 - $max_age;
                        if ($anos_maximos_idade < 5) $anos_maximos_idade = 5;
                        
                        $prazo_meses = min($prazo_meses_db, $anos_maximos_idade * 12);

                        $rendaGlobal = $p['monthly_income'] + $p['rendimento_secundario'] + $p['rendimento_extra'];
                        $prestacao_maxima_sugerida = ($rendaGlobal * 0.35) - $p['current_debt'];
                        
                        if ($prestacao_maxima_sugerida > 0) {
                            $r = ($tan / 100) / 12;
                            if ($r > 0) {
                                $limiteEstimadoSugestao = $prestacao_maxima_sugerida * (1 - pow(1 + $r, -$prazo_meses)) / $r;
                            } else {
                                $limiteEstimadoSugestao = $prestacao_maxima_sugerida * $prazo_meses;
                            }
                        } else {
                            $limiteEstimadoSugestao = 0;
                        }
                    ?>
                    <tr>
                        <td class="ps-4 fw-bold"><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['nif']) ?><br><small class="text-muted"><?= htmlspecialchars($p['email']) ?></small></td>
                        <td>
                            Liq: €<?= number_format($p['monthly_income'], 2, ',', '.') ?><br>
                            Dív: €<?= number_format($p['current_debt'], 2, ',', '.') ?> (<?= $p['employment_status'] ?: 'N/A' ?>)
                        </td>
                        <td>
                            <?php if($p['credit_status'] === 'approved'): ?>
                                <span class="badge bg-success ps-2 pe-2 py-1"><i class="fas fa-check-circle"></i> Aprovado (Max: €<?= number_format($p['approved_max_limit'], 0, ',', '.') ?>)</span>
                            <?php elseif($p['credit_status'] === 'pending'): ?>
                                <span class="badge bg-warning text-dark ps-2 pe-2 py-1"><i class="fas fa-search"></i> Aguarda Aprovação</span>
                            <?php else: ?>
                                <span class="badge bg-secondary ps-2 pe-2 py-1"><i class="fas fa-times-circle"></i> Não Submetido</span>
                            <?php endif; ?>
                        </td>
                        <td class="pe-4 text-end">
                            <?php if($p['credit_status'] === 'pending' || $p['credit_status'] === 'approved'): ?>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#<?= $modal_id ?>">
                                    <i class="fas fa-folder-open"></i> Ver Dossier
                                </button>
                                
                                <form method="POST" style="display:inline-block;" onsubmit="return confirm('Tem a certeza absoluta de que deseja remover permanentemente este dossier? O Cliente perderá o acesso e terá de refazer o formulário do zero.');">
                                    <input type="hidden" name="action" value="delete_dossier">
                                    <input type="hidden" name="cp_id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger ms-1" title="Apagar Dossier">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                
                                <!-- Modal -->
                                <div class="modal fade" id="<?= $modal_id ?>" tabindex="-1" aria-labelledby="<?= $modal_id ?>Label" aria-hidden="true">
                                  <div class="modal-dialog modal-xl modal-dialog-centered">
                                    <div class="modal-content">
                                      <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title" id="<?= $modal_id ?>Label">Dossier de Crédito: <?= htmlspecialchars($p['name']) ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                      </div>
                                      <div class="modal-body text-start">
                                        <div class="row">
                                            <!-- Coluna Esquerda: Dados -->
                                            <div class="col-md-7 border-end pe-4">
                                                <h6 class="text-uppercase fw-bold text-muted mb-3"><i class="fas fa-file-invoice-dollar"></i> Declaração Financeira</h6>
                                                
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block text-primary fw-bold mb-1">Titular Principal</small>
                                                        <span class="fs-5 fw-bold">€<?= number_format($p['monthly_income'], 2, ',', '.') ?></span>
                                                        <span class="badge bg-secondary ms-1"><?= $p['age_1'] > 0 ? $p['age_1'] : 35 ?> Anos</span><br>
                                                        <small class="text-muted"><i class="fas fa-briefcase"></i> <?= htmlspecialchars($p['employment_status']) ?></small>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block text-primary fw-bold mb-1">Segundo Titular</small>
                                                        <?php if ($p['rendimento_secundario'] > 0 || $p['age_2'] > 0 || !empty($p['employment_status_2'])): ?>
                                                            <span class="fs-5 fw-bold">€<?= number_format($p['rendimento_secundario'], 2, ',', '.') ?></span>
                                                            <span class="badge bg-secondary ms-1"><?= $p['age_2'] > 0 ? $p['age_2'] : 'N/A' ?> Anos</span><br>
                                                            <small class="text-muted"><i class="fas fa-briefcase"></i> <?= htmlspecialchars($p['employment_status_2']) ?></small>
                                                        <?php else: ?>
                                                            <span class="text-muted fst-italic">Não existe / Não preenchido</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="row mb-3 border-top pt-3">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Rendimento Extra Mensal</small>
                                                        <span class="fs-5 fw-bold text-success">€<?= number_format($p['rendimento_extra'], 2, ',', '.') ?></span>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted d-block">Total Encargos (Dívidas)</small>
                                                        <span class="fs-5 fw-bold text-danger">€<?= number_format($p['current_debt'], 2, ',', '.') ?></span>
                                                    </div>
                                                </div>

                                                <h6 class="text-uppercase fw-bold text-muted mb-3"><i class="fas fa-file-pdf"></i> Documentos Submetidos</h6>
                                                <div class="d-flex gap-2">
                                                    <?php if(!empty($p['doc_irs'])): ?>
                                                        <a href="../imoveis/uploads/<?= htmlspecialchars($p['doc_irs']) ?>" target="_blank" class="btn btn-sm btn-outline-danger"><i class="fas fa-file-pdf"></i> IRS</a>
                                                    <?php else: ?>
                                                        <span class="btn btn-sm btn-outline-danger disabled"><i class="fas fa-times"></i> Sem IRS</span>
                                                    <?php endif; ?>

                                                    <?php 
                                                    if(!empty($p['doc_recibos'])): 
                                                        $recibos_arr = json_decode($p['doc_recibos'], true);
                                                        if(is_array($recibos_arr)) {
                                                            foreach($recibos_arr as $i => $recibo) {
                                                                echo '<a href="../imoveis/uploads/'.htmlspecialchars($recibo).'" target="_blank" class="btn btn-sm btn-outline-info text-nowrap mt-1"><i class="fas fa-file-invoice"></i> Recibo '.($i+1).'</a> ';
                                                            }
                                                        } else {
                                                            echo '<a href="../imoveis/uploads/'.htmlspecialchars($p['doc_recibos']).'" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-file-invoice"></i> Recibos</a>';
                                                        }
                                                    else: 
                                                    ?>
                                                        <span class="btn btn-sm btn-outline-info disabled mt-1"><i class="fas fa-times"></i> Sem Recibos</span>
                                                    <?php endif; ?>

                                                    <?php if(!empty($p['doc_identificacao'])): ?>
                                                        <a href="../imoveis/uploads/<?= htmlspecialchars($p['doc_identificacao']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas fa-id-card"></i> Cartão Cidadão</a>
                                                    <?php else: ?>
                                                        <span class="btn btn-sm btn-outline-secondary disabled"><i class="fas fa-times"></i> Sem ID</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Coluna Direita: Aprovação -->
                                            <div class="col-md-5 ps-4 bg-light rounded pt-3">
                                                <h6 class="text-uppercase fw-bold text-primary mb-3"><i class="fas fa-stamp"></i> Decisão Executiva</h6>
                                                
                                                <div class="alert alert-warning py-2 small mb-4">
                                                    <strong>Estimativa do Sistema (35% Esforço):</strong><br>
                                                    <span class="fs-5">€<?= number_format($limiteEstimadoSugestao, 2, ',', '.') ?></span>
                                                </div>
                                                
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="approve_limit">
                                                    <input type="hidden" name="cp_id" value="<?= $p['id'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold font-monospace">Escreva o Limite Final (€)</label>
                                                        <input type="number" class="form-control form-control-lg border-primary font-monospace shadow-sm" name="new_limit" value="<?= $p['approved_max_limit'] > 0 ? $p['approved_max_limit'] : $limiteEstimadoSugestao ?>" required step="5000">
                                                    </div>
                                                    <button type="submit" class="btn btn-lg btn-success w-100 fw-bold shadow-sm"><i class="fas fa-check-double"></i> Carimbar Aprovação</button>
                                                </form>
                                                
                                                <p class="text-muted small mt-3"><i class="fas fa-info-circle"></i> Ao carimbar este valor, o cliente será notificado digitalmente e poderá começar a licitar nos imóveis até ao teto estipulado.</p>
                                            </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <!-- Fim Modal -->

                            <?php else: ?>
                                <span class="text-muted small">Sem dados pendentes.</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/incluir/footer.php'; ?>

<?php if (!empty($_GET['open_user_id'])): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    <?php
    $open_user_id = (int)$_GET['open_user_id'];
    $modal_to_open = null;
    foreach($profiles as $p) {
        if ($p['user_id'] == $open_user_id) {
            $modal_to_open = "modalDossier" . $p['id'];
            break;
        }
    }
    if ($modal_to_open):
    ?>
    var myModal = new bootstrap.Modal(document.getElementById('<?= $modal_to_open ?>'));
    myModal.show();
    <?php endif; ?>
});
</script>
<?php endif; ?>
