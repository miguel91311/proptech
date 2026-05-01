<?php
// Gerar uma password segura e aleatória
$nova_password = bin2hex(random_bytes(8)); // Gera uma password com 16 caracteres

// Gerar o hash da nova password
$hash_gerado = password_hash($nova_password, PASSWORD_DEFAULT);
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Gerador de Nova Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { max-width: 600px; width: 100%; }
        .hash-result { word-wrap: break-word; background-color: #e9ecef; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-body p-5">
                <h3 class="card-title text-center mb-4">Nova Password Gerada</h3>
                
                <div class="alert alert-success">
                    <p class="mb-0"><strong>A sua nova password é:</strong></p>
                    <h4 class="text-center my-3"><?php echo htmlspecialchars($nova_password); ?></h4>
                    <p class="text-center small">Guarde esta password num local seguro antes de continuar.</p>
                </div>

                <hr class="my-4">
                
                <p>Para ativar esta nova password, clique no botão abaixo. O sistema irá atualizar a base de dados automaticamente.</p>
                
                <form action="atualizar_password.php" method="POST">
                    <input type="hidden" name="novo_hash" value="<?php echo htmlspecialchars($hash_gerado); ?>">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Ativar Nova Password Agora</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</body>
</html>