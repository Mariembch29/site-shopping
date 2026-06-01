<?php
session_start();
require 'db.php';

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom   = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $mdp   = $_POST['mot_de_passe'];
    $mdp2  = $_POST['mot_de_passe2'];

    if (empty($nom) || empty($email) || empty($mdp)) {
        $erreur = "Tous les champs sont obligatoires.";
    } elseif ($mdp !== $mdp2) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($mdp) < 6) {
        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erreur = "Cet email est déjà utilisé.";
        } else {
            $hash = password_hash($mdp, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES (?, ?, ?)");
            $stmt->execute([$nom, $email, $hash]);
            $succes = "Compte créé avec succès ! <a href='login.php'>Se connecter</a>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Inscription - Mimi Shopping</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #fdf4ff, #ede9fe); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .card { background: white; border-radius: 16px; padding: 40px 36px; width: 100%; max-width: 420px; box-shadow: 0 20px 60px rgba(124,58,237,0.15); }
    .logo { text-align: center; margin-bottom: 28px; }
    .logo h1 { font-size: 26px; font-weight: 700; background: linear-gradient(135deg, #7c3aed, #c026d3); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .logo p { color: #9ca3af; font-size: 14px; margin-top: 4px; }
    label { display: block; font-size: 13px; color: #6b7280; margin-bottom: 6px; font-weight: 500; }
    input { width: 100%; padding: 11px 14px; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 14px; outline: none; transition: border 0.2s; margin-bottom: 16px; }
    input:focus { border-color: #7c3aed; }
    .btn { width: 100%; padding: 12px; background: linear-gradient(135deg, #7c3aed, #c026d3); color: white; border: none; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 4px; }
    .btn:hover { opacity: 0.9; }
    .erreur { background: #fef2f2; color: #dc2626; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
    .succes { background: #f0fdf4; color: #16a34a; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
    .lien { text-align: center; margin-top: 20px; font-size: 13px; color: #9ca3af; }
    .lien a { color: #7c3aed; text-decoration: none; font-weight: 600; }
  </style>
</head>
<body>
  <div class="card">
    <div class="logo">
      <h1>MIMI SHOPPING</h1>
      <p>Créer un compte</p>
    </div>

    <?php if ($erreur): ?>
      <div class="erreur"><?= $erreur ?></div>
    <?php endif; ?>
    <?php if ($succes): ?>
      <div class="succes"><?= $succes ?></div>
    <?php endif; ?>

    <form method="POST">
      <label>Nom complet</label>
      <input type="text" name="nom" placeholder="Votre nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" />

      <label>Email</label>
      <input type="email" name="email" placeholder="votre@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />

      <label>Mot de passe</label>
      <input type="password" name="mot_de_passe" placeholder="Minimum 6 caractères" />

      <label>Confirmer le mot de passe</label>
      <input type="password" name="mot_de_passe2" placeholder="Répéter le mot de passe" />

      <button type="submit" class="btn">Créer mon compte</button>
    </form>

    <div class="lien">Déjà un compte ? <a href="login.php">Se connecter</a></div>
  </div>
</body>
</html>
