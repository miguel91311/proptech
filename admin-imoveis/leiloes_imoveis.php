<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/incluir/db_connect.php';

// Aprovar Imóvel e Agendar Leilão
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'schedule_auction') {
        $prop_id = (int)$_POST['prop_id'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        
        try {
            $pdo->beginTransaction();
            
            // Mudar estado do imóvel para ativo no leilão
            $pdo->prepare("UPDATE properties SET status = 'active_auction', property_verification_stage = 'ready_for_auction', documents_verified = 1 WHERE id = ?")->execute([$prop_id]);
            
            // Inserir cronograma na tabela auctions
            $pdo->prepare("INSERT INTO auctions (property_id, start_time, end_time, status) VALUES (?, ?, ?, 'active')")->execute([$prop_id, $start_time, $end_time]);
            
            $pdo->commit();
            $msg = "<div class='alert alert-success fw-bold'><i class='fas fa-check'></i> Fase 1 Validada e Leilão Inserido no Mercado com Sucesso.</div>";
        } catch(PDOException $e) {
            $pdo->rollBack();
            $msg = "<div class='alert alert-danger'>Erro crítico ao agendar leilão: " . $e->getMessage() . "</div>";
        }
    }
    
    // Validar Bloqueio CPCV e Regras da Fase 2
    if (isset($_POST['action']) && $_POST['action'] === 'approve_cpcv') {
        $prop_id = (int)$_POST['prop_id'];
        
        $stmt_check = $pdo->prepare("SELECT licenca_utilizacao_url FROM properties WHERE id = ?");
        $stmt_check->execute([$prop_id]);
        $check = $stmt_check->fetch();
        
        if (!$check || empty($check['licenca_utilizacao_url'])) {
            $msg = "<div class='alert alert-danger shadow-sm fw-bold'><i class='fas fa-shield-alt'></i> Endpoint Bloqueado: O Vendedor não completou a Fase 2 (Licença de Utilização em falta). Opcionalmente contacte-o.</div>";
        } else {
            $pdo->prepare("UPDATE properties SET property_verification_stage = 'ready_for_cpcv', status = 'sold' WHERE id = ?")->execute([$prop_id]);
            $msg = "<div class='alert alert-success shadow-sm fw-bold'><i class='fas fa-certificate'></i> Blindagem Jurídica Completa: Imóvel aprovado para CPCV final.</div>";
        }
    }
    
    // Finalizar Leilão Manualmente
    if (isset($_POST['action']) && $_POST['action'] === 'manual_finish_auction') {
        $prop_id = (int)$_POST['prop_id'];
        
        try {
            $pdo->beginTransaction();
            
            // Obter o leilão ativo referente a esta propriedade
            $stmt = $pdo->prepare("SELECT id, current_highest_bid, winner_id FROM auctions WHERE property_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
            $stmt->execute([$prop_id]);
            $auction = $stmt->fetch();
            
            if ($auction) {
                if ($auction['winner_id']) {
                    // Tem licitação vencedora -> Fecha leilão, Vende propriedade, mas não atualiza diretamente documentação se não for submetida
                    $pdo->prepare("UPDATE auctions SET status = 'finished', end_time = NOW() WHERE id = ?")->execute([$auction['id']]);
                    $pdo->prepare("UPDATE properties SET status = 'sold' WHERE id = ?")->execute([$prop_id]);
                    $pdo->prepare("INSERT IGNORE INTO contracts_cpcv (auction_id) VALUES (?)")->execute([$auction['id']]);
                    $msg = "<div class='alert alert-success shadow-sm fw-bold'><i class='fas fa-gavel'></i> Leilão Terminado Manualmente! Imóvel adjudicado ao maior licitante atual.</div>";
                } else {
                    // Sem licitações -> Leilão falha
                    $pdo->prepare("UPDATE auctions SET status = 'failed', end_time = NOW() WHERE id = ?")->execute([$auction['id']]);
                    $pdo->prepare("UPDATE properties SET status = 'cancelled' WHERE id = ?")->execute([$prop_id]);
                    $msg = "<div class='alert alert-warning shadow-sm fw-bold'><i class='fas fa-times'></i> Leilão concluído manualmente, mas arquivado por **falta de licitações**.</div>";
                }
            } else {
                $msg = "<div class='alert alert-danger shadow-sm'>Nenhum leilão em curso encontrado para este imóvel.</div>";
            }
            
            $pdo->commit();
        } catch(PDOException $e) {
            $pdo->rollBack();
            $msg = "<div class='alert alert-danger shadow-sm'>Erro ao terminar manualmente: " . $e->getMessage() . "</div>";
        }
    }

    // Apagar Imóvel
    if (isset($_POST['action']) && $_POST['action'] === 'delete_property') {
        $prop_id = (int)$_POST['prop_id'];
        
        try {
            // Em MySQL puro com ON DELETE CASCADE configurado, apaga os leilões e as apostas também
            $pdo->prepare("DELETE FROM properties WHERE id = ?")->execute([$prop_id]);
            $msg = "<div class='alert alert-info shadow-sm fw-bold'><i class='fas fa-trash-alt'></i> Imóvel Removido com Sucesso.</div>";
        } catch(PDOException $e) {
            $msg = "<div class='alert alert-danger'>Erro ao remover imóvel: " . $e->getMessage() . "</div>";
        }
    }
}

// Ler Imóveis Pendentes e Ativos (Apenas Leilões)
$stmt = $pdo->query("SELECT p.*, u.name AS seller_name, u.email, u.nif, a.current_highest_bid 
                     FROM properties p 
                     JOIN users u ON p.seller_id = u.id 
                     LEFT JOIN auctions a ON p.id = a.property_id AND a.status IN ('active', 'finished')
                     WHERE p.sale_type = 'auction'
                     ORDER BY p.status DESC, p.created_at DESC");
$imoveis = $stmt->fetchAll();

$page_title = "Leilões: Gestão de Imóveis e Prazos";
require_once __DIR__ . '/incluir/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-success"><i class="fas fa-home"></i> Mód. Leilões: Gestão de Imóveis no Mercado</h2>
</div>

<?= $msg ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Imóvel & Local</th>
                        <th>Vendedor</th>
                        <th>Preço Base (€)</th>
                        <th>Estado</th>
                        <th class="text-center">Painel de Ação (Agendar Leilão)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($imoveis as $p): ?>
                    <tr>
                        <td class="ps-4">
                            <strong><?= htmlspecialchars($p['title']) ?></strong><br>
                            <small class="text-muted"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($p['address']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($p['seller_name']) ?><br><small class="text-muted">NIF: <?= htmlspecialchars($p['nif']) ?></small></td>
                        <td>
                            <strong class="text-success">€<?= number_format($p['base_price'], 2, ',', '.') ?></strong><br>
                            <small class="text-danger"><i class="fas fa-lock text-muted"></i> R: €<?= number_format($p['reserve_price'], 2, ',', '.') ?></small>
                            <?php if(!empty($p['current_highest_bid'])): ?>
                                <br><span class="badge bg-success mt-1 shadow-sm"><i class="fas fa-arrow-up"></i> Atual: €<?= number_format($p['current_highest_bid'], 2, ',', '.') ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($p['status'] === 'under_review'): ?>
                                <span class="badge bg-warning text-dark"><i class="fas fa-search"></i> Em Análise Documental</span>
                            <?php elseif($p['status'] === 'active_auction'): ?>
                                <span class="badge bg-success shadow-sm px-2"><i class="fas fa-gavel"></i> Em Leilão Online</span>
                            <?php elseif($p['status'] === 'sold'): ?>
                                <span class="badge bg-primary px-2"><i class="fas fa-handshake"></i> CPCV Gerado</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($p['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="p-3 bg-light text-center border-start opacity-75">
                            
                            <!-- BOTÃO E MODAL VER DETALHES -->
                            <button type="button" class="btn btn-outline-secondary btn-sm mb-3 w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#modalDetails<?= $p['id'] ?>">
                                <i class="fas fa-search-plus"></i> Ver Detalhes Completos
                            </button>

                            <div class="modal fade text-start" id="modalDetails<?= $p['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $p['id'] ?>" aria-hidden="true">
                              <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                  <div class="modal-header bg-dark text-white shadow-sm border-0">
                                    <h5 class="modal-title" id="modalLabel<?= $p['id'] ?>"><i class="fas fa-info-circle"></i> Ficha Documental #<?= $p['id'] ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body p-4">
                                      <h3 class="text-primary mb-1"><?= htmlspecialchars($p['title']) ?></h3>
                                      <p class="text-muted border-bottom pb-3"><i class="fas fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($p['address']) ?></p>

                                      <div class="row bg-light rounded shadow-sm border p-3 mb-4">
                                          <div class="col-md-6 border-end">
                                              <small class="text-muted d-block fw-bold text-uppercase"><i class="fas fa-user"></i> Vendedor (Proprietário)</small>
                                              <span class="fs-5 fw-bold"><?= htmlspecialchars($p['seller_name']) ?></span><br>
                                              <span class="badge bg-secondary">NIF: <?= htmlspecialchars($p['nif']) ?></span><br>
                                              <small class="text-muted"><i class="fas fa-envelope"></i> <?= htmlspecialchars($p['email']) ?></small>
                                          </div>
                                          <div class="col-md-6 ps-md-4 pt-3 pt-md-0">
                                              <small class="text-muted d-block fw-bold text-uppercase"><i class="fas fa-coins"></i> Valores Declarados</small>
                                              <div class="d-flex justify-content-between">
                                                  <span>Preço Base:</span>
                                                  <span class="fs-6 fw-bold text-success">€<?= number_format($p['base_price'], 2, ',', '.') ?></span>
                                              </div>
                                              <div class="d-flex justify-content-between mt-1 pt-1 border-top">
                                                  <span>Preço Reserva:</span>
                                                  <span class="fs-6 fw-bold text-danger">€<?= number_format($p['reserve_price'], 2, ',', '.') ?></span>
                                              </div>
                                          </div>
                                      </div>

                                      <h6 class="text-uppercase fw-bold text-muted mb-2"><i class="fas fa-align-left"></i> Descrição Original</h6>
                                      <div class="bg-white border rounded p-3 text-dark mb-4" style="max-height: 250px; overflow-y: auto;">
                                          <?= nl2br(htmlspecialchars($p['description'])) ?>
                                      </div>
                                      
                                      <?php if(!empty($p['matterport_url'])): ?>
                                      <h6 class="text-uppercase fw-bold text-info mb-2"><i class="fas fa-vr-cardboard"></i> Digital Twin 3D</h6>
                                      <a href="<?= htmlspecialchars($p['matterport_url']) ?>" target="_blank" class="btn btn-outline-info w-100 text-truncate text-start shadow-sm mb-4">
                                          <i class="fas fa-external-link-alt"></i> Aceder a: <?= htmlspecialchars($p['matterport_url']) ?>
                                      </a>
                                      <?php endif; ?>

                                      <?php if(!empty($p['photos_json'])): ?>
                                      <h6 class="text-uppercase fw-bold text-primary mb-2"><i class="fas fa-camera"></i> Galeria de Fotos (Marketing)</h6>
                                      <div class="d-flex flex-wrap gap-2 mb-4">
                                          <?php 
                                          $fotos = json_decode($p['photos_json'], true);
                                          if(is_array($fotos)): 
                                              foreach($fotos as $foto):
                                          ?>
                                              <a href="../imoveis/uploads/properties_photos/<?= htmlspecialchars($foto) ?>" target="_blank" class="border rounded p-1 shadow-sm d-inline-block bg-white" title="Ver Foto">
                                                  <img src="../imoveis/uploads/properties_photos/<?= htmlspecialchars($foto) ?>" style="height: 100px; width: auto; object-fit: cover;">
                                              </a>
                                          <?php 
                                              endforeach; 
                                          endif; 
                                          ?>
                                      </div>
                                      <?php endif; ?>

                                      <?php if(!empty($p['plantas_url'])): ?>
                                      <h6 class="text-uppercase fw-bold text-muted mb-2"><i class="fas fa-images"></i> Documentação / Plantas Extra</h6>
                                      <div class="d-flex flex-wrap gap-2 mb-4">
                                          <?php 
                                          $plantas = json_decode($p['plantas_url'], true);
                                          if(is_array($plantas)): 
                                              foreach($plantas as $planta):
                                                  $ext = strtolower(pathinfo($planta, PATHINFO_EXTENSION));
                                                  $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                          ?>
                                              <?php if($isImg): ?>
                                                  <a href="../imoveis/uploads/properties_docs/<?= htmlspecialchars($planta) ?>" target="_blank" class="border rounded p-1 shadow-sm d-inline-block bg-white" title="Ver Imagem">
                                                      <img src="../imoveis/uploads/properties_docs/<?= htmlspecialchars($planta) ?>" style="height: 60px; width: auto; object-fit: cover;">
                                                  </a>
                                              <?php else: ?>
                                                  <a href="../imoveis/uploads/properties_docs/<?= htmlspecialchars($planta) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas fa-file-pdf"></i> Documento</a>
                                              <?php endif; ?>
                                          <?php 
                                              endforeach; 
                                          endif; 
                                          ?>
                                      </div>
                                      <?php endif; ?>

                                      <div class="row mb-4">
                                          <div class="col-md-6 border-end">
                                              <h6 class="text-uppercase fw-bold text-muted mb-2"><i class="fas fa-folder-open"></i> Análise Fase 1</h6>
                                              
                                              <?php if(!empty($p['cc_doc_url'])): ?>
                                              <a href="../imoveis/download_doc.php?f=<?= basename($p['cc_doc_url']) ?>&pid=<?=$p['id']?>" target="_blank" class="btn btn-outline-info btn-sm mb-2 w-100 text-start" title="Documento Identificação"><i class="fas fa-id-card"></i> Doc. Identificação (CC)</a>
                                              <?php endif; ?>
                                              
                                              <?php if(!empty($p['caderneta_predial_url'])): ?>
                                              <a href="../imoveis/download_doc.php?f=<?= basename($p['caderneta_predial_url']) ?>&pid=<?=$p['id']?>" target="_blank" class="btn btn-outline-info btn-sm mb-2 w-100 text-start" title="CP: <?= htmlspecialchars($p['certidao_teor_code']) ?>"><i class="fas fa-file-pdf"></i> Caderneta Predial</a>
                                              <small class="text-muted d-block mb-2">Certidão: <?= htmlspecialchars($p['certidao_teor_code']) ?></small>
                                              <?php endif; ?>

                                              <?php if(!empty($p['certificado_energetico_url'])): ?>
                                              <a href="../imoveis/download_doc.php?f=<?= basename($p['certificado_energetico_url']) ?>&pid=<?=$p['id']?>" target="_blank" class="btn btn-outline-info btn-sm mb-2 w-100 text-start" title="Certificado Energético"><i class="fas fa-leaf"></i> Certificado Energético</a>
                                              <?php endif; ?>
                                          </div>
                                          <div class="col-md-6 ps-0 ps-md-3">
                                              <h6 class="text-uppercase fw-bold text-muted mb-2"><i class="fas fa-shield-alt"></i> Análise Fase 2 (CPCV)</h6>
                                              
                                              <?php if(!empty($p['licenca_utilizacao_url'])): ?>
                                              <a href="../imoveis/download_doc.php?f=<?= basename($p['licenca_utilizacao_url']) ?>&pid=<?=$p['id']?>" target="_blank" class="btn btn-outline-primary btn-sm mb-2 w-100 text-start"><i class="fas fa-file-alt"></i> Licença Utilização</a>
                                              <?php endif; ?>

                                              <?php if(!empty($p['declaracao_condominio_url'])): ?>
                                              <a href="../imoveis/download_doc.php?f=<?= basename($p['declaracao_condominio_url']) ?>&pid=<?=$p['id']?>" target="_blank" class="btn btn-outline-primary btn-sm mb-2 w-100 text-start"><i class="fas fa-building"></i> Declaração Condomínio</a>
                                              <?php endif; ?>

                                              <?php if(!empty($p['distrate_bancario_url'])): ?>
                                              <a href="../imoveis/download_doc.php?f=<?= basename($p['distrate_bancario_url']) ?>&pid=<?=$p['id']?>" target="_blank" class="btn btn-outline-primary btn-sm mb-2 w-100 text-start"><i class="fas fa-university"></i> Distrate / Hipoteca</a>
                                              <?php endif; ?>

                                              <?php if(!empty($p['ficha_tecnica_url'])): ?>
                                              <a href="../imoveis/download_doc.php?f=<?= basename($p['ficha_tecnica_url']) ?>&pid=<?=$p['id']?>" target="_blank" class="btn btn-outline-primary btn-sm mb-2 w-100 text-start"><i class="fas fa-home"></i> Ficha Técnica</a>
                                              <?php endif; ?>

                                              <?php if(!empty($p['licenca_utilizacao_url']) && $p['property_verification_stage'] === 'phase_2_pending_review'): ?>
                                              <form method="POST" class="mt-2 text-end">
                                                  <input type="hidden" name="action" value="approve_cpcv">
                                                  <input type="hidden" name="prop_id" value="<?= $p['id'] ?>">
                                                  <button type="submit" class="btn btn-dark btn-sm fw-bold"><i class="fas fa-stamp"></i> Validar Fase 2 (Aprovar para CPCV)</button>
                                              </form>
                                              <?php endif; ?>
                                          </div>
                                      </div>

                                      <div class="text-muted small text-end pt-2 border-top border-2">
                                          <i class="fas fa-calendar-alt"></i> Submetido em: <?= date('d/m/Y \à\s H:i', strtotime($p['created_at'])) ?>
                                      </div>
                                      
                                      <?php if($p['status'] === 'active_auction'): ?>
                                      <div class="mt-4 p-3 bg-warning bg-opacity-10 border border-warning rounded text-center">
                                          <form method="POST" onsubmit="return confirm('ATENÇÃO: Deseja terminar este leilão manualmente? O imóvel será vendido ao cliente com a licitação mais alta atual IGNORANDO totalmente o Preço de Reserva. Se não existirem licitações, o leilão falha. Continuar?');">
                                              <input type="hidden" name="action" value="manual_finish_auction">
                                              <input type="hidden" name="prop_id" value="<?= $p['id'] ?>">
                                              <button type="submit" class="btn btn-warning fw-bold w-100 shadow-sm border-warning text-dark"><i class="fas fa-gavel"></i> Terminar Leilão Agora (Manual)</button>
                                          </form>
                                      </div>
                                      <?php endif; ?>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <!-- FIM MODAL -->

                            <!-- ACESSO SEGURO RLS AOS DOCUMENTOS FOI MOVIDO PARA O MODAL DETALHES ACIMA -->

                            <?php if($p['status'] === 'under_review'): ?>
                            <form method="POST" class="d-flex flex-column gap-2 mt-2" onsubmit="return confirm('Confirma a validação documental da Caderneta Predial e pretende ativar o leilão público agora?');">
                                <input type="hidden" name="action" value="schedule_auction">
                                <input type="hidden" name="prop_id" value="<?= $p['id'] ?>">
                                
                                <div class="d-flex text-start gap-2">
                                    <div class="flex-fill">
                                        <label class="small text-muted mb-1 fw-bold">Início</label>
                                        <input type="datetime-local" class="form-control form-control-sm" name="start_time" required value="<?= date('Y-m-d\TH:i') ?>">
                                    </div>
                                    <div class="flex-fill">
                                        <label class="small text-danger mb-1 fw-bold">Fim Padrão</label>
                                        <input type="datetime-local" class="form-control form-control-sm border-danger" name="end_time" required value="<?= date('Y-m-d\TH:i', strtotime('+7 days')) ?>">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-sm btn-success fw-bold w-100 mt-1 shadow-sm"><i class="fas fa-gavel"></i> Iniciar Leilão</button>
                            </form>
                            <?php elseif($p['status'] === 'active_auction'): ?>
                                <div class="text-success fw-bold mt-3"><i class="fas fa-clock"></i> Lance Aberto</div>
                            <?php elseif($p['status'] === 'sold'): ?>
                                <div class="text-primary fw-bold mt-3"><i class="fas fa-handshake"></i> Negócio Selado</div>
                            <?php else: ?>
                                <div class="text-muted fst-italic mt-3 mb-2">Estado: <?= htmlspecialchars($p['status']) ?></div>
                            <?php endif; ?>
                            
                            <hr class="my-3 opacity-25">
                            <form method="POST" onsubmit="return confirm('ATENÇÃO: Deseja apagar permanentemente este imóvel de todo o sistema? Tudo o que estiver ligado a ele desaparecerá.');">
                                <input type="hidden" name="action" value="delete_property">
                                <input type="hidden" name="prop_id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100" title="Apagar Propriedade"><i class="fas fa-trash-alt"></i> Eliminar Imóvel</button>
                            </form>
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
