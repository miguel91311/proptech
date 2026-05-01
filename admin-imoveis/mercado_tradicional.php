<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/incluir/db_connect.php';

// Aprovar Anúncio
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'approve_ad') {
        $prop_id = (int)$_POST['prop_id'];
        try {
            $pdo->prepare("UPDATE properties SET status = 'active', documents_verified = 1 WHERE id = ?")->execute([$prop_id]);
            $msg = "<div class='alert alert-success fw-bold'><i class='fas fa-check'></i> Anúncio Aprovado e Publicado com Sucesso.</div>";
        } catch(PDOException $e) {
            $msg = "<div class='alert alert-danger'>Erro ao aprovar anúncio: " . $e->getMessage() . "</div>";
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

// Ler Imóveis Normais
$stmt = $pdo->query("SELECT p.*, u.name AS seller_name, u.email, u.nif 
                     FROM properties p 
                     JOIN users u ON p.seller_id = u.id 
                     WHERE p.sale_type != 'auction' OR p.sale_type IS NULL
                     ORDER BY p.status DESC, p.created_at DESC");
$imoveis = $stmt->fetchAll();

$page_title = "Mercado Tradicional: Gestão de Anúncios";
require_once __DIR__ . '/incluir/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-primary"><i class="fas fa-home"></i> Mód. Mercado Tradicional: Venda Direta / Arrendamento</h2>
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
                        <th class="text-center">Painel de Ação (Aprovar Anúncio)</th>
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
                                <span class="badge bg-warning text-dark"><i class="fas fa-search"></i> Pendente Aprovação</span>
                            <?php elseif($p['status'] === 'active'): ?>
                                <span class="badge bg-success shadow-sm px-2"><i class="fas fa-check"></i> Publicado Ativo</span>
                            <?php elseif($p['status'] === 'sold'): ?>
                                <span class="badge bg-primary px-2"><i class="fas fa-handshake"></i> Vendido/Arrendado</span>
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

                                          </div>
                                      </div>

                                      <div class="text-muted small text-end pt-2 border-top border-2">
                                          <i class="fas fa-calendar-alt"></i> Submetido em: <?= date('d/m/Y \à\s H:i', strtotime($p['created_at'])) ?>
                                      </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <!-- FIM MODAL -->

                            <!-- ACESSO SEGURO RLS AOS DOCUMENTOS FOI MOVIDO PARA O MODAL DETALHES ACIMA -->

                            <?php if($p['status'] === 'under_review'): ?>
                            <form method="POST" class="mt-3" onsubmit="return confirm('Aprovar e publicar este anúncio diretamente no portal?');">
                                <input type="hidden" name="action" value="approve_ad">
                                <input type="hidden" name="prop_id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-success fw-bold w-100 shadow-sm"><i class="fas fa-check"></i> Aprovar e Publicar Anúncio</button>
                            </form>
                            <?php elseif($p['status'] === 'active'): ?>
                                <div class="text-success fw-bold mt-3"><i class="fas fa-eye"></i> Online no Portal</div>
                            <?php elseif($p['status'] === 'sold'): ?>
                                <div class="text-primary fw-bold mt-3"><i class="fas fa-handshake"></i> Negócio Concluído</div>
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
