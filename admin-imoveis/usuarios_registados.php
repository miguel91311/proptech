<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/incluir/db_connect.php';

$msg = '';

// Ação de Eliminar Utilizador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $user_id = (int)$_POST['user_id'];
    
    // Proteção: Não permitir que o admin se elimine a si próprio se estiver na tabela users
    // (Nota: Depende de como os admins são geridos, mas por segurança aqui fica o check)
    if (isset($_SESSION['admin_user_id']) && $user_id === (int)$_SESSION['admin_user_id']) {
        $msg = "<div class='alert alert-danger shadow-sm fw-bold'><i class='fas fa-exclamation-triangle'></i> Erro: Não pode eliminar a sua própria conta de administrador.</div>";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$user_id])) {
                $msg = "<div class='alert alert-success shadow-sm fw-bold'><i class='fas fa-user-minus'></i> Utilizador eliminado com sucesso da base de dados.</div>";
            } else {
                $msg = "<div class='alert alert-danger shadow-sm'>Erro ao eliminar o utilizador.</div>";
            }
        } catch (PDOException $e) {
            $msg = "<div class='alert alert-danger shadow-sm'>Erro de base de dados: " . $e->getMessage() . "</div>";
        }
    }
}

// Ler apenas Vendedores Particulares e Agentes
$stmt = $pdo->query("SELECT id, name, email, nif, role, kyc_status, created_at FROM users WHERE role IN ('seller', 'agent') ORDER BY created_at DESC");
$usuarios = $stmt->fetchAll();

// Ler Imóveis para cruzar com Vendedores
$stmt_prop = $pdo->query("SELECT id, title, base_price, status, seller_id, sale_type, created_at FROM properties ORDER BY created_at DESC");
$all_props = $stmt_prop->fetchAll();
$props_by_seller = [];
foreach ($all_props as $p) {
    $props_by_seller[$p['seller_id']][] = $p;
}

$page_title = "Gestão de Vendedores";
require_once __DIR__ . '/incluir/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-primary"><i class="fas fa-user-tag"></i> Base de Dados: Vendedores e Agentes</h2>
</div>

<?= $msg ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Nome / Utilizador</th>
                        <th>Email / Contacto</th>
                        <th>NIF</th>
                        <th>Perfil (Role)</th>
                        <th>Estado KYC</th>
                        <th>Data Registo</th>
                        <th class="pe-4 text-end">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr><td colspan="8" class="text-center p-4 text-muted fst-italic">Ainda não existem utilizadores registados na plataforma.</td></tr>
                    <?php endif; ?>
                    
                    <?php foreach($usuarios as $u): ?>
                    <tr>
                        <td class="ps-4 text-muted small">#<?= $u['id'] ?></td>
                        <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['nif'] ?: '---') ?></td>
                        <td>
                            <?php if($u['role'] === 'seller'): ?>
                                <span class="badge bg-success"><i class="fas fa-home"></i> Particular</span>
                            <?php elseif($u['role'] === 'agent'): ?>
                                <span class="badge bg-warning text-dark"><i class="fas fa-building"></i> Agente / Imob.</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($u['role']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($u['kyc_status'] === 'approved'): ?>
                                <span class="text-success small fw-bold"><i class="fas fa-check-circle"></i> Aprovado</span>
                            <?php elseif($u['kyc_status'] === 'pending'): ?>
                                <span class="text-warning small fw-bold"><i class="fas fa-clock"></i> Pendente</span>
                            <?php else: ?>
                                <span class="text-danger small fw-bold"><i class="fas fa-times-circle"></i> Recusado</span>
                            <?php endif; ?>
                        </td>
                        <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></small></td>
                        <td class="pe-4 text-end text-nowrap">
                            <?php 
                            $modal_id = "modalSeller" . $u['id'];
                            $seller_props = $props_by_seller[$u['id']] ?? [];
                            ?>
                            <button type="button" class="btn btn-sm btn-primary me-1" data-bs-toggle="modal" data-bs-target="#<?= $modal_id ?>" title="Ver Detalhes e Imóveis">
                                <i class="fas fa-search-plus"></i> Ver Detalhes
                            </button>
                            
                            <form method="POST" onsubmit="return confirm('ATENÇÃO: Deseja eliminar este vendedor permanentemente? Todos os imóveis associados também poderão ficar órfãos ou ser apagados.');" class="d-inline">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar da Base de Dados">
                                    <i class="fas fa-trash-alt"></i> Eliminar
                                </button>
                            </form>
                            
                            <!-- MODAL DETALHES VENDEDOR -->
                            <div class="modal fade text-start text-wrap" id="<?= $modal_id ?>" tabindex="-1" aria-labelledby="label<?= $modal_id ?>" aria-hidden="true">
                              <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                  <div class="modal-header bg-dark text-white border-0">
                                    <h5 class="modal-title" id="label<?= $modal_id ?>"><i class="fas fa-user-circle"></i> Ficha de Vendedor: <?= htmlspecialchars($u['name']) ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body p-4">
                                      <div class="row bg-light rounded border p-3 mb-4 shadow-sm">
                                          <div class="col-md-6 border-end">
                                              <small class="text-muted d-block fw-bold text-uppercase">Contactos</small>
                                              <span class="fs-6 fw-bold"><?= htmlspecialchars($u['email']) ?></span><br>
                                              <span class="badge bg-secondary mt-1">NIF: <?= htmlspecialchars($u['nif'] ?: '---') ?></span>
                                          </div>
                                          <div class="col-md-6 ps-md-4 pt-3 pt-md-0">
                                              <small class="text-muted d-block fw-bold text-uppercase">Informação de Conta</small>
                                              <div>Perfil: <strong><?= $u['role'] === 'seller' ? 'Particular' : 'Agente / Imobiliária' ?></strong></div>
                                              <div>Registo: <?= date('d/m/Y \à\s H:i', strtotime($u['created_at'])) ?></div>
                                          </div>
                                      </div>
                                      
                                      <h6 class="text-uppercase fw-bold text-primary mb-3"><i class="fas fa-home"></i> Carteira de Imóveis Submetidos (<?= count($seller_props) ?>)</h6>
                                      <?php if(empty($seller_props)): ?>
                                          <div class="alert alert-warning text-center small shadow-sm"><i class="fas fa-exclamation-triangle"></i> Este vendedor ainda não publicou nenhum imóvel no mercado.</div>
                                      <?php else: ?>
                                          <div class="table-responsive bg-white rounded border shadow-sm">
                                              <table class="table table-sm table-hover align-middle mb-0">
                                                  <thead class="table-light">
                                                      <tr>
                                                          <th>Ref / Título</th>
                                                          <th>Preço Base</th>
                                                          <th>Data</th>
                                                          <th>Estado</th>
                                                          <th>Link</th>
                                                      </tr>
                                                  </thead>
                                                  <tbody>
                                                      <?php foreach($seller_props as $prop): ?>
                                                      <tr>
                                                          <td class="small fw-bold text-truncate" style="max-width: 150px;"><?= htmlspecialchars($prop['title']) ?></td>
                                                          <td class="text-success small fw-bold">€<?= number_format($prop['base_price'], 2, ',', '.') ?></td>
                                                          <td class="small text-muted"><?= date('d/m/Y', strtotime($prop['created_at'])) ?></td>
                                                          <td>
                                                              <?php if($prop['status'] === 'under_review'): ?>
                                                                  <span class="badge bg-warning text-dark" style="font-size:0.65rem;">Em Análise</span>
                                                              <?php elseif($prop['status'] === 'active_auction'): ?>
                                                                  <span class="badge bg-success" style="font-size:0.65rem;">Leilão</span>
                                                              <?php elseif($prop['status'] === 'sold'): ?>
                                                                  <span class="badge bg-primary" style="font-size:0.65rem;">CPCV</span>
                                                              <?php else: ?>
                                                                  <span class="badge bg-secondary" style="font-size:0.65rem;"><?= htmlspecialchars($prop['status']) ?></span>
                                                              <?php endif; ?>
                                                          </td>
                                                          <td>
                                                              <?php 
                                                              $manage_link = ($prop['sale_type'] === 'auction') ? 'leiloes_imoveis.php' : 'mercado_tradicional.php';
                                                              ?>
                                                              <a href="<?= $manage_link ?>" class="btn btn-xs btn-outline-primary" style="font-size: 10px; padding: 2px 6px;">Gerir</a>
                                                          </td>
                                                      </tr>
                                                      <?php endforeach; ?>
                                                  </tbody>
                                              </table>
                                          </div>
                                      <?php endif; ?>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <!-- FIM MODAL -->
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Mover os modais para o body para evitar problemas de opacity/stacking-context da tabela Bootstrap
    let modals = document.querySelectorAll('.modal');
    modals.forEach(function(modal) {
        document.body.appendChild(modal);
    });
});
</script>

<?php require_once __DIR__ . '/incluir/footer.php'; ?>
