<?php
session_start();
require 'db.php';

// Protection admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Actions
$message = '';

// Supprimer produit
if (isset($_GET['delete_produit'])) {
    $stmt = $pdo->prepare("DELETE FROM produits WHERE id = ?");
    $stmt->execute([$_GET['delete_produit']]);
    $message = "Produit supprimé.";
}

// Supprimer utilisateur
if (isset($_GET['delete_user'])) {
    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ? AND role != 'admin'");
    $stmt->execute([$_GET['delete_user']]);
    $message = "Utilisateur supprimé.";
}

// Changer statut commande
if (isset($_GET['statut']) && isset($_GET['cmd_id'])) {
    $stmt = $pdo->prepare("UPDATE commandes SET statut = ? WHERE id = ?");
    $stmt->execute([$_GET['statut'], $_GET['cmd_id']]);
    $message = "Statut mis à jour.";
}

// Ajouter produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    $stmt = $pdo->prepare("INSERT INTO produits (nom, marque, categorie, sous_categorie, prix, image, stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['nom'], $_POST['marque'], $_POST['categorie'],
        $_POST['sous_categorie'], $_POST['prix'], $_POST['image'], $_POST['stock']
    ]);
    $message = "Produit ajouté avec succès !";
}

// Récupérer données
$produits = $pdo->query("SELECT * FROM produits ORDER BY date_ajout DESC")->fetchAll(PDO::FETCH_ASSOC);
$utilisateurs = $pdo->query("SELECT * FROM utilisateurs ORDER BY date_inscription DESC")->fetchAll(PDO::FETCH_ASSOC);
$commandes = $pdo->query("SELECT c.*, u.nom as client FROM commandes c JOIN utilisateurs u ON c.utilisateur_id = u.id ORDER BY c.date_commande DESC")->fetchAll(PDO::FETCH_ASSOC);

$nb_produits = count($produits);
$nb_users = count($utilisateurs);
$nb_commandes = count($commandes);
$total_ventes = array_sum(array_column($commandes, 'total'));

$page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - Mimi Shopping</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f9f4fc;
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 230px;
            min-width: 230px;
            background: linear-gradient(180deg, #7c3aed, #4c1d95);
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        .sidebar-logo {
            padding: 24px 20px;
            color: white;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 2px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-logo span {
            font-size: 12px;
            display: block;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 400;
            margin-top: 2px;
        }

        .sidebar-menu {
            padding: 16px 0;
            flex: 1;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        .sidebar-menu a i {
            width: 18px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-footer a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-footer a:hover {
            color: white;
        }

        /* MAIN */
        .main {
            flex: 1;
            padding: 28px;
            overflow-y: auto;
        }

        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: #4c1d95;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* MESSAGE */
        .msg {
            background: #f0fdf4;
            color: #16a34a;
            padding: 10px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        /* STATS */
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: white;
            border: 1px solid #f3e8ff;
            border-radius: 14px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }

        .stat-info h3 {
            font-size: 22px;
            font-weight: 700;
            color: #1f2937;
        }

        .stat-info p {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 2px;
        }

        /* TABLE */
        .table-card {
            background: white;
            border: 1px solid #f3e8ff;
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 28px;
        }

        .table-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f3e8ff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: #4c1d95;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #f3e8ff;
        }

        td {
            padding: 12px 16px;
            font-size: 14px;
            color: #1f2937;
            border-bottom: 1px solid #faf5ff;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: #faf5ff;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-green {
            background: #f0fdf4;
            color: #16a34a;
        }

        .badge-yellow {
            background: #fffbeb;
            color: #d97706;
        }

        .badge-blue {
            background: #eff6ff;
            color: #2563eb;
        }

        .badge-red {
            background: #fef2f2;
            color: #dc2626;
        }

        .btn-sm {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-block;
        }

        .btn-danger {
            background: #fef2f2;
            color: #dc2626;
        }

        .btn-danger:hover {
            background: #fee2e2;
        }

        .btn-primary {
            background: #f3e8ff;
            color: #7c3aed;
        }

        .btn-primary:hover {
            background: #ede9fe;
        }

        /* FORM */
        .form-card {
            background: white;
            border: 1px solid #f3e8ff;
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 28px;
        }

        .form-card h3 {
            font-size: 16px;
            font-weight: 600;
            color: #4c1d95;
            margin-bottom: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input,
        .form-group select {
            padding: 10px 12px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: border 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #7c3aed;
        }

        .btn-submit {
            margin-top: 16px;
            padding: 11px 24px;
            background: linear-gradient(135deg, #7c3aed, #c026d3);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-submit:hover {
            opacity: 0.9;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-logo">
            MIMI SHOPPING
            <span>Panel Admin</span>
        </div>
        <div class="sidebar-menu">
            <a href="?page=dashboard" class="<?= $page === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>
            <a href="?page=produits" class="<?= $page === 'produits' ? 'active' : '' ?>">
                <i class="fas fa-box"></i> Produits
            </a>
            <a href="?page=commandes" class="<?= $page === 'commandes' ? 'active' : '' ?>">
                <i class="fas fa-shopping-bag"></i> Commandes
            </a>
            <a href="?page=utilisateurs" class="<?= $page === 'utilisateurs' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Utilisateurs
            </a>
        </div>
        <div class="sidebar-footer">
            <a href="store.html"><i class="fas fa-store"></i> Voir le site</a>
        </div>
    </div>

    <!-- MAIN -->
    <div class="main">

        <?php if ($message): ?>
            <div class="msg"><i class="fas fa-check-circle"></i>
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if ($page === 'dashboard'): ?>
            <div class="page-title"><i class="fas fa-chart-pie"></i> Dashboard</div>

            <div class="stats">
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg,#7c3aed,#c026d3)"><i
                            class="fas fa-box"></i></div>
                    <div class="stat-info">
                        <h3>
                            <?= $nb_produits ?>
                        </h3>
                        <p>Produits</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg,#059669,#10b981)"><i
                            class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <h3>
                            <?= $nb_users ?>
                        </h3>
                        <p>Utilisateurs</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg,#d97706,#f59e0b)"><i
                            class="fas fa-shopping-bag"></i></div>
                    <div class="stat-info">
                        <h3>
                            <?= $nb_commandes ?>
                        </h3>
                        <p>Commandes</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg,#2563eb,#3b82f6)"><i
                            class="fas fa-coins"></i></div>
                    <div class="stat-info">
                        <h3>
                            <?= number_format($total_ventes, 2) ?> DT
                        </h3>
                        <p>Total ventes</p>
                    </div>
                </div>
            </div>

            <!-- Derniers produits -->
            <div class="table-card">
                <div class="table-header">
                    <h3>Derniers produits ajoutés</h3>
                    <a href="?page=produits" class="btn-sm btn-primary">Voir tout</a>
                </div>
                <table>
                    <tr>
                        <th>Nom</th>
                        <th>Marque</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Stock</th>
                    </tr>
                    <?php foreach (array_slice($produits, 0, 5) as $p): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($p['nom']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($p['marque']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($p['categorie']) ?>
                            </td>
                            <td>
                                <?= $p['prix'] ?> DT
                            </td>
                            <td><span class="badge <?= $p['stock'] > 10 ? 'badge-green' : 'badge-red' ?>"><?= $p['stock'] ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

        <?php elseif ($page === 'produits'): ?>
            <div class="page-title"><i class="fas fa-box"></i> Gestion des produits</div>

            <!-- Formulaire ajout -->
            <div class="form-card">
                <h3><i class="fas fa-plus"></i> Ajouter un produit</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="ajouter" />
                    <div class="form-grid">
                        <div class="form-group"><label>Nom</label><input type="text" name="nom" required /></div>
                        <div class="form-group"><label>Marque</label><input type="text" name="marque" /></div>
                        <div class="form-group"><label>Prix (DT)</label><input type="number" name="prix" step="0.01"
                                required /></div>
                        <div class="form-group">
                            <label>Catégorie</label>
                            <select name="categorie">
                                <option value="makeup">Make Up</option>
                                <option value="skincare">Skin Care</option>
                                <option value="clothes">Clothes</option>
                                <option value="accessoires">Accessoires</option>
                            </select>
                        </div>
                        <div class="form-group"><label>Sous-catégorie</label><input type="text" name="sous_categorie" />
                        </div>
                        <div class="form-group"><label>Stock</label><input type="number" name="stock" value="0" /></div>
                        <div class="form-group"><label>Image (chemin)</label><input type="text" name="image"
                                placeholder="images/produit.jpg" /></div>
                    </div>
                    <button type="submit" class="btn-submit"><i class="fas fa-plus"></i> Ajouter</button>
                </form>
            </div>

            <!-- Liste produits -->
            <div class="table-card">
                <div class="table-header">
                    <h3>Tous les produits (
                        <?= $nb_produits ?>)
                    </h3>
                </div>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Marque</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($produits as $p): ?>
                        <tr>
                            <td>#
                                <?= $p['id'] ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($p['nom']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($p['marque']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($p['categorie']) ?>
                            </td>
                            <td>
                                <?= $p['prix'] ?> DT
                            </td>
                            <td><span class="badge <?= $p['stock'] > 10 ? 'badge-green' : 'badge-red' ?>"><?= $p['stock'] ?></span></td>
                            <td><a href="?page=produits&delete_produit=<?= $p['id'] ?>" class="btn-sm btn-danger"
                                    onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

        <?php elseif ($page === 'commandes'): ?>
            <div class="page-title"><i class="fas fa-shopping-bag"></i> Commandes</div>
            <div class="table-card">
                <div class="table-header">
                    <h3>Toutes les commandes (
                        <?= $nb_commandes ?>)
                    </h3>
                </div>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                    <?php if (empty($commandes)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;color:#9ca3af;padding:30px;">Aucune commande pour le moment
                            </td>
                        </tr>
                    <?php else: foreach ($commandes as $c): ?>
                            <tr>
                                <td>#
                                    <?= $c['id'] ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($c['client']) ?>
                                </td>
                                <td>
                                    <?= $c['total'] ?> DT
                                </td>
                                <td>
                                    <?php
                                    $badges = ['en attente' => 'badge-yellow', 'confirmée' => 'badge-blue', 'livrée' => 'badge-green', 'annulée' => 'badge-red'];
                                    $badge = $badges[$c['statut']] ?? 'badge-yellow';
                                    ?>
                                    <span class="badge <?= $badge ?>"><?= $c['statut'] ?></span>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($c['date_commande'])) ?>
                                </td>
                                <td>
                                    <select
                                        onchange="window.location.href='?page=commandes&cmd_id=<?= $c['id'] ?>&statut='+this.value"
                                        style="padding:4px 8px;border:1px solid #e5e7eb;border-radius:6px;font-size:12px;">
                                        <option value="">Changer statut</option>
                                        <option value="en attente">En attente</option>
                                        <option value="confirmée">Confirmée</option>
                                        <option value="livrée">Livrée</option>
                                        <option value="annulée">Annulée</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                </table>
            </div>

        <?php elseif ($page === 'utilisateurs'): ?>
            <div class="page-title"><i class="fas fa-users"></i> Utilisateurs</div>
            <div class="table-card">
                <div class="table-header">
                    <h3>Tous les utilisateurs (
                        <?= $nb_users ?>)
                    </h3>
                </div>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Inscription</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($utilisateurs as $u): ?>
                        <tr>
                            <td>#
                                <?= $u['id'] ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($u['nom']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($u['email']) ?>
                            </td>
                            <td><span class="badge <?= $u['role'] === 'admin' ? 'badge-blue' : 'badge-green' ?>"><?= $u['role'] ?></span></td>
                            <td>
                                <?= date('d/m/Y', strtotime($u['date_inscription'])) ?>
                            </td>
                            <td>
                                <?php if ($u['role'] !== 'admin'): ?>
                                    <a href="?page=utilisateurs&delete_user=<?= $u['id'] ?>" class="btn-sm btn-danger"
                                        onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a>
                                <?php else: ?>
                                    <span style="color:#9ca3af;font-size:12px;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>

    </div>

</body>

</html>