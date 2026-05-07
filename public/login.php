<?php
require_once '../config/database.php';
$msg = '';

if(isset($_POST['envoyer_otp'])) {
    $tel = $_POST['telephone'];
    $otp = rand(100000, 999999);
    $expire = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    
    $stmt = $pdo->prepare("INSERT INTO users (nom, telephone, otp_code, otp_expire) VALUES (?,?,?,?) 
                           ON DUPLICATE KEY UPDATE otp_code=?, otp_expire=?");
    $stmt->execute([$_POST['nom'], $tel, $otp, $expire, $otp, $expire]);
    
    $msg = "Code OTP pour test : $otp"; // En prod = envoi SMS
}

if(isset($_POST['verifier_otp'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE telephone =? AND otp_code =? AND otp_expire > NOW()");
    $stmt->execute([$_POST['telephone'], $_POST['otp']]);
    $user = $stmt->fetch();
    
    if($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['a_vote'] = $user['a_vote'];
        $_SESSION['role'] = $user['role'];
        header('Location: index.php'); exit;
    } else {
        $msg = "OTP invalide ou expiré";
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>MaVoix CI - Connexion</title></head><body class="bg-light">
<div class="container mt-5" style="max-width:400px">
    <h2 class="text-center mb-4"> MaVoix CI</h2>
    <p class="text-center text-muted">Vote en ligne sécurisé</p>
    <?php if($msg):?><div class="alert alert-info"><?= $msg?></div><?php endif;?>
    
    <form method="POST" class="card p-4">
        <h5>1. Ton numéro</h5>
        <input name="nom" class="form-control mb-2" placeholder="Nom complet" required>
        <input name="telephone" class="form-control mb-2" placeholder="0700000000" required>
        <button name="envoyer_otp" class="btn btn-success w-100">Recevoir code OTP</button>
    </form>

    <form method="POST" class="card p-4 mt-3">
        <h5>2. Entre le code</h5>
        <input name="telephone" class="form-control mb-2" placeholder="0700000000" required>
        <input name="otp" class="form-control mb-2" placeholder="Code 6 chiffres" required>
        <button name="verifier_otp" class="btn btn-primary w-100">Se connecter</button>
    </form>
</div>
</body>
</html>

