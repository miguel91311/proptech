<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo $page_title ?? 'Admin Imóveis'; ?> - Marketplace Imobiliário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="/credito/assets/css/imoveis_admin.css"> 
    
    <link rel="stylesheet" href="css/admin_mobile.css">
</head>
<body>

<button class="sidebar-toggler" type="button">
    <i class="fas fa-bars"></i>
    <i class="fas fa-times"></i>
</button>

<div class="sidebar">
    <h3 class="text-white text-center p-3">Admin Imóveis</h3>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="leiloes_imoveis.php"><i class="fas fa-gavel"></i> Leilões</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="mercado_tradicional.php"><i class="fas fa-home"></i> Mercado Tradicional</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="leiloes_compradores.php"><i class="fas fa-id-card-alt"></i> Clientes</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="usuarios_registados.php"><i class="fas fa-users"></i> Vendedores</a>
        </li>
        <li class="nav-item border-top mt-2 pt-2">
            <a class="nav-link" href="atualizar_password.php"><i class="fas fa-key"></i> Mudar Password</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair do Painel</a>
        </li>
    </ul>
</div>
<div class="main-content">
    <nav class="navbar navbar-expand-lg navbar-light bg-light navbar-admin">
        <div class="container-fluid">
            <span class="navbar-brand d-lg-none"><?php echo $page_title ?? 'Admin'; ?></span>
            
            <div class="ms-auto">
                 <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">