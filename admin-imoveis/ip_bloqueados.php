<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/incluir/db_connect.php';

$ips_bloqueados = $pdo->query("SELECT * FROM ip_bloqueados ORDER BY data_bloqueio DESC")->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Gestão de IPs Bloqueados";
require_once __DIR__ . '/incluir/header.php';
?>

<h2>Gerir IPs Bloqueados</h2>
<p>Esta página lista todos os IPs que realizaram uma simulação recentemente. Ao remover um bloqueio, o utilizador poderá fazer uma nova simulação com o mesmo IP.</p>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Endereço de IP</th>
                        <th>Data do Bloqueio</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ips_bloqueados)): ?>
                        <tr><td colspan="3" class="text-center">Nenhum IP bloqueado de momento.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($ips_bloqueados as $ip_info): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ip_info['ip_address']); ?></td>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($ip_info['data_bloqueio'])); ?></td>
                        <td>
                            <form action="desbloquear_ip.php" method="POST" onsubmit="return confirm('Tem a certeza que quer desbloquear este IP?');">
                                <input type="hidden" name="desbloquear_ip" value="<?php echo htmlspecialchars($ip_info['ip_address']); ?>">
                                <button type="submit" class="btn btn-warning btn-sm">Desbloquear</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/incluir/footer.php'; ?>
