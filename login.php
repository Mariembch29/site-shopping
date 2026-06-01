<?php
session_start();
require 'db.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mdp   = $_POST['mot_de_passe'];

    if (empty($email) || empty($mdp)) {
        $erreur = "Tous les champs sont obligatoires.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($mdp, $user['mot_de_passe'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_nom']  = $user['nom'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: shopping.html");
            }
            exit;
        } else {
            $erreur = "Email ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Connexion - Mimi Shopping</title>
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
    .lien { text-align: center; margin-top: 20px; font-size: 13px; color: #9ca3af; }
    .lien a { color: #7c3aed; text-decoration: none; font-weight: 600; }
    .forgot { text-align: right; margin-top: -10px; margin-bottom: 16px; }
    .forgot a { font-size: 12px; color: #7c3aed; text-decoration: none; }
  </style>
</head>
<body>
  <div class="card">
    <div class="logo">
      <h1>MIMI SHOPPING</h1>
      <p>Connexion à votre compte</p>
    </div>

    <?php if ($erreur): ?>
      <div class="erreur"><?= $erreur ?></div>
    <?php endif; ?>

    <form method="POST">
      <label>Email</label>
      <input type="email" name="email" placeholder="votre@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />

      <label>Mot de passe</label>
      <input type="password" name="mot_de_passe" placeholder="Votre mot de passe" />

      <button type="submit" class="btn">Se connecter</button>
    </form>

    <div class="lien">Pas encore de compte ? <a href="register.php">S'inscrire</a></div>
  </div>
</body>
</html>
