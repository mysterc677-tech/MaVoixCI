<?php
session_start();
require_once '../config/database.php';

$erreur = '';

if(!isset($_SESSION['otp']) || !isset($_SESSION['idElecteur']) || !isset($_SESSION['idElection'])){
    header("Location: index.php");
    exit();
}

if(time() > $_SESSION['otp_expire']){
    session_destroy();
    die("Code OTP expiré. <a href='index.php'>Recommencez</a>");
}

$idElection = $_SESSION['idElection'];
$idElecteur = $_SESSION['idElecteur'];

if(isset($_POST['verifier_otp'])){
    $otp_saisi = $_POST['otp_code'];
    
    if($otp_saisi == $_SESSION['otp']){
        
        if(!isset($_POST['idCandidat'])){
            $erreur = "Veuillez choisir un candidat";
        } else {
            $idCandidat = $_POST['idCandidat'];
            try {
    $pdo->beginTransaction();
    
    $hashVote = hash('sha256', $idElection . $idElecteur . time());
    
    $stmt = $pdo->prepare("INSERT INTO Vote (idElection, idElecteur, idCandidat, hashVote, timestampVote) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$idElection, $idElecteur, $idCandidat, $hashVote]);
    
    $stmt = $pdo->prepare("UPDATE Electeur SET aVote = 1 WHERE idElecteur = ?");
    $stmt->execute([$idElecteur]);
    
    $pdo->commit();
    session_destroy();
    die("Vote enregistré ! Merci d'avoir voté. <a href='index.php'>Retour</a>");
    
} catch(Exception $e){
    $pdo->rollBack();
    $erreur = "Erreur : " . $e->getMessage();
}
        }
    } else {
        $erreur = "Code OTP incorrect";
    }
}

$stmt = $pdo->prepare("SELECT c.idCandidat, c.nom, c.prenom, p.sigle 
                       FROM Candidat c 
                       JOIN PartiPolitique p ON c.idPartiPolitique = p.idPartiPolitique 
                       WHERE c.idElection = ?");
$stmt->execute([$idElection]);
$candidats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>MaVoix CI - Vote</title>
</head>
<body>
    <h1>MaVoix CI</h1>
    <h2>2. Votez pour un candidat</h2>
    <p>Électeur : <b><?=$_SESSION['nom_complet']?></b></p>
    
    <?php if($erreur): ?>
        <p style="color:red; font-weight:bold;"><?=$erreur?></p>
    <?php endif; ?>
    
    <form method="POST">
        <p>Entrez le code OTP reçu :</p>
        <input type="text" name="otp_code" placeholder="Code à 6 chiffres" maxlength="6" required>
        
        <h3>Choisissez votre candidat :</h3>
        <?php foreach($candidats as $c): ?>
        <label>
            <input type="radio" name="idCandidat" value="<?=$c['idCandidat']?>" required>
            <?=$c['prenom']?> <?=$c['nom']?> - <?=$c['sigle']?>
        </label><br>
        <?php endforeach; ?>
        <br>
        <button type="submit" name="verifier_otp">Voter</button>
    </form>
    <p><a href="index.php?logout=1">Annuler</a></p>
</body>
</html>
