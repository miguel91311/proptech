<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/incluir/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $tipo_credito = $_POST['tipo_credito'];
    $logo_url_para_db = $_POST['logo_url_atual'] ?? null;

    // --- LÓGICA DE UPLOAD DO LOGÓTIPO ---
    if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['logo_file'];

        // Define a pasta de destino com base no tipo de crédito
        $is_habitacao = in_array($tipo_credito, ['habitacao', 'habitacao_jovem', 'transferencia']);
        $is_pessoal = in_array($tipo_credito, ['Pessoal', 'Automóvel', 'Energias Renováveis']);
        
        $upload_dir_path = '';
        if ($is_habitacao) {
            // Caminho absoluto no servidor para a pasta de habitação
            $upload_dir_path = $_SERVER['DOCUMENT_ROOT'] . '/credito-habitacao/images/parceiros/';
            // Caminho que será guardado na base de dados
            $upload_dir_url = 'credito-habitacao/images/parceiros/';
        } elseif ($is_pessoal) {
            $upload_dir_path = $_SERVER['DOCUMENT_ROOT'] . '/credito-pessoal/images/';
            $upload_dir_url = 'credito-pessoal/images/';
        }
        
        if ($upload_dir_path) {
            // Cria um nome de ficheiro único para evitar sobreposições
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'logo_' . uniqid() . '.' . $file_extension;
            $destination_path = $upload_dir_path . $new_filename;

            // Move o ficheiro para a pasta de destino
            if (move_uploaded_file($file['tmp_name'], $destination_path)) {
                $logo_url_para_db = $upload_dir_url . $new_filename;
            }
        }
    }
    // --- FIM DA LÓGICA DE UPLOAD ---

    $params = [
        'nome_entidade' => $_POST['nome_entidade'],
        'logo_url' => $logo_url_para_db, // Usa o novo caminho do logótipo
        'tipo_credito' => $tipo_credito,
        'tan_desde' => $_POST['tan_desde'],
        'taeg_desde' => $_POST['taeg_desde'],
        'montante_minimo' => $_POST['montante_minimo'],
        'montante_maximo' => $_POST['montante_maximo'],
        'prazo_minimo_meses' => $_POST['prazo_minimo_meses'],
        'prazo_maximo_meses' => $_POST['prazo_maximo_meses']
    ];

    if (empty($id)) {
        // INSERIR NOVO PRODUTO
        $columns = implode(', ', array_keys($params));
        $placeholders = ':' . implode(', :', array_keys($params));
        $sql = "INSERT INTO produtos_financeiros ($columns) VALUES ($placeholders)";
    } else {
        // ATUALIZAR PRODUTO EXISTENTE
        $update_fields = [];
        foreach ($params as $key => $value) {
            $update_fields[] = "$key = :$key";
        }
        $sql = "UPDATE produtos_financeiros SET " . implode(', ', $update_fields) . " WHERE id = :id";
        $params['id'] = $id;
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } catch (PDOException $e) {
        die("Erro na operação com a base de dados: " . $e->getMessage());
    }

    header('Location: produtos.php?status=sucesso');
    exit;
}
?>