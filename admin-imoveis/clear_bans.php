<?php
require __DIR__ . '/incluir/db_connect.php';
$stmt = $pdo->query('TRUNCATE TABLE ip_bloqueados');
$stmt2 = $pdo->query('TRUNCATE TABLE admin_login_attempts');
echo "Tabelas de bloqueio de IP e tentativas limpas com sucesso.";
?>
