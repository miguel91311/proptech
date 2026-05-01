<?php
// Ficheiro para atualizar o hash na base de dados de forma segura

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['novo_hash'])) {
    // Se não for um POST ou se o hash estiver vazio, não faz nada
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/incluir/db_connect.php';

$novo_hash = $_POST['novo_hash'];
$admin_username = 'admin'; // O username do administrador que queremos atualizar

try {
    $stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE username = ?");
    $stmt->execute([$novo_hash, $admin_username]);
    $sucesso = true;
} catch (PDOException $e) {
    // Em caso de erro, não mostrar detalhes sensíveis
    $sucesso = false;
    $erro_msg = "Ocorreu um erro ao atualizar a base de dados."; 
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Atualização de Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { max-width: 500px; width: 100%; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow-sm text-center">
            <div class="card-body p-5">
                <?php if ($sucesso): ?>
                    <h3 class="text-success">Password Atualizada!</h3>
                    <p>A sua password foi alterada com sucesso.</p>
                    <p>Pode agora fazer login com as novas credenciais.</p>
                <?php else: ?>
                    <h3 class="text-danger">Erro na Atualização</h3>
                    <p><?php echo $erro_msg; ?></p>
                    <p>Por favor, tente gerar uma nova password novamente ou contacte o suporte técnico.</p>
                <?php endif; ?>
                <div class="d-grid mt-4">
                    <a href="login.php" class="btn btn-secondary">Ir para o Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>