<?php
session_start();
require_once '../config/database.php';

$msg = '';
$erreur = '';

$stmt = $pdo->query("SELECT idElection, nom FROM Election WHERE estActive = TRUE LIMIT 1");
$election = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$election) {
    die("Aucune élection active. Créez une élection dans phpMyAdmin d'abord.");
}

$idElection = $election['idElection'];
$nomElection = $election['nom'];

if(isset($_POST['recevoir_otp'])){
    $numeroElecteur = trim($_POST['numeroElecteur']);
    
    $stmt = $pdo->prepare("SELECT e.idElecteur, u.nom, u.prenom 
                           FROM Electeur e 
                           JOIN Utilisateur u ON e.idUtilisateur = u.idUtilisateur 
                           WHERE e.numeroElecteur = ? AND e.aVote = 0");
    $stmt->execute([$numeroElecteur]);
    $electeur = $stmt->fetch();
    
    if($electeur){
        $otp = rand(100000, 999999); 
        $_SESSION['otp'] = $otp;
        $_SESSION['idElecteur'] = $electeur['idElecteur'];
        $_SESSION['idElection'] = $idElection;
        $_SESSION['nom_complet'] = $electeur['prenom'] . ' ' . $electeur['nom'];
        $_SESSION['otp_expire'] = time() + 300; 
        
        $msg = "Code OTP pour test : $otp";
    } else {
        $erreur = "Numéro électeur invalide ou vous avez déjà voté";
    }
}

if(isset($_GET['logout'])){
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>MaVoix CI - Vote</title>
</head>
<body>
    <h1>MaVoix CI</h1>
    <h2>Élection : <?=$nomElection?></h2>
    
    <?php if($erreur): ?>
        <p style="color:red; font-weight:bold;"><?=$erreur?></p>
    <?php endif; ?>
    
    <?php if($msg): ?>
        <p style="color:green; font-weight:bold;"><?=$msg?></p>
    <?php endif; ?>
    
    <?php if(!isset($_SESSION['otp'])): ?>

    <h3>1. Identifie-toi</h3>
    <form method="POST">
        <input type="text" name="numeroElecteur" placeholder="Numéro Électeur ex: CI001" required>
        <button type="submit" name="recevoir_otp">Recevoir code OTP</button>
    </form>
    
    <?php else: ?>
    <h3>2. Entre le code reçu</h3>
    <p>Électeur : <b><?=$_SESSION['nom_complet']?></b></p>
    <form method="POST" action="vote.php">
        <input type="text" name="otp_code" placeholder="Code à 6 chiffres" maxlength="6" required>
        <button type="submit" name="verifier_otp">Valider le code</button>
    </form>
    <p><a href="?logout=1">Annuler</a></p>
    <?php endif; ?>
</body>
</html>
