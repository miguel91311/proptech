<?php
// admin/ver_simulacao.php - CÓDIGO COMPLETO E UNIFICADO

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/incluir/db_connect.php';

$simulacao_id = $_GET['id'] ?? null;
if (!$simulacao_id) {
    header('Location: index.php');
    exit;
}

// Busca os dados da simulação principal (inalterado)
$stmt_simulacao = $pdo->prepare("SELECT * FROM simulacoes WHERE id = ?");
$stmt_simulacao->execute([$simulacao_id]);
$sim = $stmt_simulacao->fetch();

if (!$sim) {
    die("Simulação não encontrada.");
}

// --- INÍCIO DA LÓGICA UNIFICADA DE BUSCA DE DOCUMENTOS ---
$documentos = [];
$fonte_documentos = ''; // Para sabermos de onde vieram os documentos

try {
    // Verifica o tipo de simulação para saber qual tabela consultar
    if (isset($sim['tipo_simulacao']) && $sim['tipo_simulacao'] === 'Habitacao') {
        // Se for Crédito Habitação, busca na tabela 'documentos_enviados'
        $stmt_docs = $pdo->prepare("SELECT * FROM documentos_enviados WHERE simulacao_id = ? ORDER BY tipo_documento");
        $stmt_docs->execute([$simulacao_id]);
        $documentos = $stmt_docs->fetchAll();
        if (!empty($documentos)) {
            $fonte_documentos = 'habitacao';
        }
    } else {
        // Para Crédito Pessoal ou qualquer outro, busca na tabela 'documentos_simulacao'
        $stmt_docs = $pdo->prepare("SELECT * FROM documentos_simulacao WHERE simulacao_id = ? ORDER BY titular, id");
        $stmt_docs->execute([$simulacao_id]);
        $documentos = $stmt_docs->fetchAll();
        if (!empty($documentos)) {
            $fonte_documentos = 'pessoal';
        }
    }
} catch (PDOException $e) {
    // Em caso de erro, a variável $documentos continua vazia.
    $documentos = [];
}
// --- FIM DA LÓGICA UNIFICADA ---

$page_title = "Detalhes da Simulação #" . $simulacao_id;
require_once __DIR__ . '/incluir/header.php';

// A sua função render_detail permanece exatamente igual
function render_detail($label, $value, $type = 'text') {
    $display_value = '';
    if (is_null($value) || $value === '' || $value === '0' || $value === 0.00) {
        $display_value = '<span class="text-muted">-</span>';
    } elseif ($type === 'currency') {
        $display_value = '€' . number_format((float)$value, 2, ',', '.');
    } elseif ($type === 'date') {
        $display_value = date('d/m/Y', strtotime($value));
    } elseif ($type === 'boolean') {
        $display_value = $value ? 'Sim' : 'Não';
    } elseif ($type === 'date_time') {
         $display_value = date('d/m/Y H:i', strtotime($value));
    } else {
        $display_value = htmlspecialchars($value);
    }
    echo "<tr><th style='width: 40%;'>{$label}</th><td>{$display_value}</td></tr>";
}

// O resto do seu código permanece exatamente igual
$module_name = $sim['tipo_simulacao'] == 'Habitacao' ? 'credito-habitacao' : ($sim['tipo_simulacao'] == 'Consolidado' ? 'credito-consolidado' : 'credito-pessoal');
$chat_api_path_admin = '/' . $module_name . '/incluir/chat_actions.php'; 

?>

<div class="mb-4">
    <a href="index.php" class="btn btn-secondary">‹ Voltar para Simulações</a>
</div>

<h2>Detalhes da Simulação #<?= $sim['id'] ?> <span class="badge bg-secondary"><?= $sim['tipo_simulacao'] ?></span></h2>

<div class="row mt-4">
    
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white"><h4>Resumo Financeiro e Contactos</h4></div>
            <div class="card-body">
                <table class="table table-sm table-striped">
                    <tbody>
                        <?php
                        render_detail('Nome Completo', $sim['nome_completo']);
                        render_detail('Email', $sim['email']);
                        render_detail('Telemóvel', $sim['telemovel']);
                        render_detail('IP', $sim['ip_simulacao']);
                        render_detail('Data Submissão', $sim['data_submissao'], 'date_time');
                        
                        echo '<tr><td colspan="2"><hr class="my-1"></td></tr>';

                        render_detail('Resultado Pré-Aprovação', $sim['resultado_pre_aprovacao']);
                        render_detail('Status do Processo', $sim['status']);
                        render_detail('Taxa de Esforço Calculada', $sim['taxa_esforco_calculada'] ?? '-', 'percent');
                        render_detail('Prestação Estimada', $sim['prestacao_estimada'], 'currency');
                        
                        echo '<tr><td colspan="2"><hr class="my-1"></td></tr>';
                        
                        render_detail('Finalidade', $sim['finalidade']);
                        if ($sim['tipo_simulacao'] === 'Pessoal') {
                            render_detail('Montante Solicitado', $sim['montante_solicitado'], 'currency');
                            render_detail('Prazo', $sim['prazo_meses'] . ' meses');
                        } elseif ($sim['tipo_simulacao'] === 'Habitacao') {
                            render_detail('Valor do Imóvel', $sim['valor_imovel'], 'currency');
                            render_detail('Montante Financiamento', $sim['montante_financiamento'], 'currency');
                            render_detail('Prazo', $sim['prazo_anos'] . ' anos');
                        } elseif ($sim['tipo_simulacao'] === 'Consolidado') {
                            render_detail('Inclui Crédito Habitação', $sim['inclui_ch'], 'boolean');
                            render_detail('Valor Dívida CH', $sim['valor_divida_ch'], 'currency');
                            render_detail('Prestação CH', $sim['prestacao_ch'], 'currency');
                            render_detail('Soma Outros Créditos', $sim['soma_outros_creditos'], 'currency');
                            render_detail('Financiamento Extra', $sim['financiamento_extra'], 'currency');
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-info text-white"><h4>Dados dos Titulares</h4></div>
            <div class="card-body">
                
                <h5 class="mb-3">1º Titular (Principal)</h5>
                <table class="table table-sm table-borderless">
                    <?php
                    render_detail('Data Nascimento', $sim['data_nascimento'], 'date');
                    render_detail('Estado Civil', $sim['estado_civil']);
                    render_detail('Tipo Residência', $sim['tipo_residencia']);
                    render_detail('Entidade Patronal', $sim['entidade_patronal']);
                    render_detail('Tipo Contrato', $sim['tipo_contrato']);
                    render_detail('Trabalha há +1 ano', $sim['trabalha_mais_1_ano']);
                    render_detail('Vencimento Líquido', $sim['vencimento_liquido'], 'currency');
                    render_detail('Rendimento Extra', $sim['rendimento_extra'], 'currency');
                    render_detail('Outros Créditos (Prestações)', $sim['outros_creditos'], 'currency');
                    ?>
                </table>
                
                <?php if ($sim['inclui_2_titular']): ?>
                    <h5 class="mt-4 mb-3 border-top pt-3">2º Titular</h5>
                    <table class="table table-sm table-borderless">
                        <?php
                        render_detail('Nome', $sim['nome_2_titular']);
                        render_detail('Data Nascimento', $sim['data_nascimento_2_titular'], 'date');
                        render_detail('Estado Civil', $sim['estado_civil_2_titular']);
                        render_detail('Tipo Residência', $sim['tipo_residencia_2_titular']);
                        render_detail('Entidade Patronal', $sim['entidade_patronal_2_titular']);
                        render_detail('Tipo Contrato', $sim['tipo_contrato_2_titular']);
                        render_detail('Trabalha há +1 ano', $sim['trabalha_mais_1_ano_2_titular']);
                        render_detail('Vencimento Líquido', $sim['vencimento_liquido_2_titular'], 'currency');
                        render_detail('Rendimento Extra', $sim['rendimento_extra_2_titular'], 'currency');
                        render_detail('Outros Créditos (Prestações)', $sim['outros_creditos_2_titular'], 'currency');
                        ?>
                    </table>
                <?php else: ?>
                    <p class="text-muted mt-3">Não foi incluído um 2º titular nesta simulação.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-warning text-dark"><h4>Documentos Originais Enviados</h4></div>
            <div class="card-body">
                 <?php if(empty($documentos)): ?>
                    <p class="text-muted">Nenhum documento foi enviado na submissão original.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach($documentos as $doc): ?>
                            <li class="list-group-item">
                                <?php
                                // --- INÍCIO DA LÓGICA DE VISUALIZAÇÃO UNIFICADA ---
                                $nome_original = '';
                                $caminho_doc = '#';
                                $texto_display = '';

                                if ($fonte_documentos === 'habitacao') {
                                    $nome_original = htmlspecialchars($doc['nome_original'] ?? 'Ficheiro');
                                    $caminho_doc = '../credito-habitacao/' . htmlspecialchars($doc['caminho_ficheiro']);
                                    $tipo_doc_formatado = ucfirst(str_replace('_', ' ', htmlspecialchars($doc['tipo_documento'])));
                                    $texto_display = "<strong>{$tipo_doc_formatado}:</strong> {$nome_original}";

                                } elseif ($fonte_documentos === 'pessoal') {
                                    $nome_original = htmlspecialchars($doc['nome_ficheiro_original'] ?? 'Ficheiro');
                                    $caminho_doc = '../credito-pessoal/' . htmlspecialchars($doc['caminho_ficheiro_servidor']);
                                    $titular_num = htmlspecialchars($doc['titular']);
                                    $doc_type = ucfirst(htmlspecialchars($doc['tipo_documento']));
                                    $tipo_doc_formatado = "Titular {$titular_num} - {$doc_type}";
                                    $texto_display = "<strong>{$tipo_doc_formatado}:</strong> {$nome_original}";
                                }
                                ?>
                                <a href="<?php echo $caminho_doc; ?>" target="_blank" class="small text-decoration-underline" title="<?php echo $nome_original; ?>">
                                   <?php echo $texto_display; ?>
                                </a>
                                <small class="text-muted d-block">Enviado a: <?php echo date('d/m/Y H:i', strtotime($doc['data_upload'] ?? $doc['criado_em'] ?? 'NOW')); ?></small>
                            </li>
                        <?php endforeach; ?>
                         </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-lg chat-admin-card">
            <div class="card-header bg-success text-white">
                <i class="fas fa-comment-dots"></i> Chat com Cliente
            </div>
            <div class="card-body p-0">
                <div id="chat-box" class="chat-box">
                    <div id="messages-list" class="messages-list">
                        <div class="chat-placeholder">A carregar histórico...</div>
                    </div>
                </div>
                
                <form id="chat-form-admin" class="p-3" enctype="multipart/form-data">
                    <input type="hidden" name="simulacao_id" value="<?= $simulacao_id ?>">
                    <input type="hidden" name="action" value="send">
                    
                    <div class="message-input-group">
                        <textarea id="message-text-admin" name="message_text" placeholder="Responder..." rows="1" class="form-control" oninput="autoExpand(this)"></textarea>
                        
                        <label for="file-upload-input-admin" class="file-upload-label" title="Anexar Ficheiro">
                            <i class="fas fa-paperclip"></i>
                        </label>
                        <input type="file" id="file-upload-input-admin" name="file_upload" accept=".pdf,image/*">
                        
                        <button type="submit" class="btn btn-primary btn-sm ms-2"><i class="fas fa-paper-plane"></i></button>
                    </div>
                    <span id="file-name-display-admin" class="small text-success d-block mt-1"></span>
                    <p id="error-message-admin" class="text-danger small mt-1" style="display: none;"></p>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // URL DE CHAT (Aponta para o chat_actions.php do módulo correto)
    const chatApiUrlAdmin = '<?= $chat_api_path_admin ?>';
    const simulacaoIdAdmin = '<?= htmlspecialchars($simulacao_id) ?>';
    const isAdmin = true;

    function autoExpand(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = (textarea.scrollHeight) + 'px';
    }
    
    function renderMessage(msg) {
        const isClient = msg.sender_type === 'user';
        const messageClass = isClient ? 'user-message-admin' : 'admin-message-admin';
        const messageHtml = document.createElement('div');
        messageHtml.className = `message-admin ${messageClass}`;
        
        let contentHtml = '';
        if (msg.file_path && msg.original_file_name) {
             contentHtml += `
                 <i class="fas fa-file-upload"></i> Ficheiro Anexado:
                 <a href="${msg.file_path}" target="_blank" class="message-file-link">${msg.original_file_name}</a>
             `;
        }
        
        if (msg.message_text) {
             contentHtml += `<p class="mb-0">${msg.message_text}</p>`;
        }
        
        const date = new Date(msg.timestamp);
        const timeStr = date.toLocaleTimeString('pt-PT', { hour: '2-digit', minute: '2-digit' });
        
        messageHtml.innerHTML = `<div>${contentHtml}</div><span class="message-time-admin">${isClient ? 'Cliente' : 'Admin'} - ${timeStr}</span>`;
        return messageHtml;
    }
    
    function fetchMessagesAdmin() {
        const messagesList = document.getElementById('messages-list');
        fetch(`${chatApiUrlAdmin}?action=fetch&simulacao_id=${simulacaoIdAdmin}`)
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    messagesList.innerHTML = '';
                    data.mensagens.forEach(msg => {
                        messagesList.appendChild(renderMessage(msg));
                    });
                    messagesList.scrollTop = messagesList.scrollHeight;
                    
                    markAdminMessagesAsRead();
                }
            })
            .catch(error => {
                console.error('Erro ao buscar mensagens:', error);
            });
    }
    
    function markAdminMessagesAsRead() {
        fetch(`${chatApiUrlAdmin}?action=mark_read&simulacao_id=${simulacaoIdAdmin}`)
             .catch(error => console.error('Erro ao marcar como lido:', error));
    }

    document.getElementById('chat-form-admin').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = e.target;
        const messageText = form.querySelector('#message-text-admin').value.trim();
        const fileInput = form.querySelector('#file-upload-input-admin');
        const errorMessage = document.getElementById('error-message-admin');
        
        if (messageText === '' && fileInput.files.length === 0) {
            errorMessage.textContent = 'Mensagem ou ficheiro em falta.';
            errorMessage.style.display = 'block';
            return;
        }

        errorMessage.style.display = 'none';
        
        const formData = new FormData(form);
        
        fetch(chatApiUrlAdmin, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                form.querySelector('#message-text-admin').value = '';
                fileInput.value = '';
                document.getElementById('file-name-display-admin').textContent = '';
                fetchMessagesAdmin();
            } else {
                errorMessage.textContent = data.mensagem || 'Erro ao enviar mensagem.';
                errorMessage.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Erro de rede:', error);
            errorMessage.textContent = 'Erro de rede. Por favor, tente novamente.';
            errorMessage.style.display = 'block';
        });
    });
    
    document.getElementById('file-upload-input-admin').addEventListener('change', function() {
        const display = document.getElementById('file-name-display-admin');
        display.textContent = this.files.length > 0 ? 'Ficheiro: ' + this.files[0].name : '';
    });
    
    const style = document.createElement('style');
    style.textContent = `
        .chat-admin-card .card-header {
            background-color: #0d2344 !important;
            color: #fff;
            font-weight: 600;
        }
        .chat-admin-card .chat-box {
            height: 450px;
            overflow-y: auto;
            border: none;
            border-radius: 0;
            padding: 15px;
            background-color: #fff;
        }
        .message-admin {
            max-width: 95%;
            padding: 8px 12px;
            border-radius: 10px;
            margin-bottom: 8px;
            line-height: 1.3;
            font-size: 0.95rem;
            word-wrap: break-word;
        }
        .user-message-admin {
            align-self: flex-start;
            background-color: #e3f2fd;
            color: #000;
        }
        .admin-message-admin {
            align-self: flex-end;
            background-color: #d4edda;
            color: #155724;
            text-align: right;
        }
        .message-time-admin {
            display: block;
            font-size: 0.65rem;
            margin-top: 3px;
            opacity: 0.8;
            font-style: italic;
        }
        .message-input-group {
             border: 1px solid #ddd;
        }
        .message-input-group textarea {
             padding: 8px;
             font-size: 0.9rem;
        }
    `;
    document.head.appendChild(style);

    fetchMessagesAdmin();
    setInterval(fetchMessagesAdmin, 5000); 
</script>

<?php require_once __DIR__ . '/incluir/footer.php'; ?>