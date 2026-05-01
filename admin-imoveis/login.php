<?php
session_start();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/incluir/db_connect.php';

    $ip = $_SERVER['REMOTE_ADDR'];
    $max_attempts = 5;
    $lockout_time = 15; // minutos

    // Verificar se IP está explicitamente bloqueado na tabela de bans
    $stmt_ban = $pdo->prepare("SELECT COUNT(*) FROM ip_bloqueados WHERE ip_address = ?");
    $stmt_ban->execute([$ip]);
    if ($stmt_ban->fetchColumn() > 0) {
        die("Acesso negado.");
    }

    // Verificar tentativas falhadas recentes
    $stmt_attempts = $pdo->prepare("SELECT attempts, last_attempt FROM admin_login_attempts WHERE ip = ?");
    $stmt_attempts->execute([$ip]);
    $attempt_data = $stmt_attempts->fetch();

    if ($attempt_data) {
        $last_time = strtotime($attempt_data['last_attempt']);
        if ($attempt_data['attempts'] >= $max_attempts && (time() - $last_time) < ($lockout_time * 60)) {
            $error_message = 'Demasiadas tentativas falhadas. Tente novamente em 15 minutos.';
        } else if ((time() - $last_time) >= ($lockout_time * 60)) {
            // Reset se já passou o tempo
            $pdo->prepare("UPDATE admin_login_attempts SET attempts = 0 WHERE ip = ?")->execute([$ip]);
        }
    }

    if (empty($error_message)) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Sucesso - Limpar tentativas
            $pdo->prepare("DELETE FROM admin_login_attempts WHERE ip = ?")->execute([$ip]);
            
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: index.php');
            exit;
        } else {
            // Falha - Incrementar tentativas
            if ($attempt_data) {
                $pdo->prepare("UPDATE admin_login_attempts SET attempts = attempts + 1, last_attempt = CURRENT_TIMESTAMP WHERE ip = ?")->execute([$ip]);
            } else {
                $pdo->prepare("INSERT INTO admin_login_attempts (ip, attempts) VALUES (?, 1)")->execute([$ip]);
            }
            $error_message = 'Username ou password incorretos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Admin - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body class="login-body">
    <div class="card login-card shadow-sm">
        <div class="card-body p-5">
            <h3 class="card-title text-center mb-4">Painel de Administração</h3>
            <form method="POST" action="login.php">
                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Entrar</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
