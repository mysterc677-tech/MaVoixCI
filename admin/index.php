<?php
require_once '../config/database.php';
if(isset($_POST['ajouter'])){
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $numeroElecteur = trim($_POST['numeroElecteur']);
    $numeroCNI = trim($_POST['numeroCNI']);
    $dateNaissance = $_POST['dateNaissance'];
    $msg = '';
    
    try {
        
        $stmt = $pdo->prepare("SELECT idUtilisateur FROM Utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        if($stmt->fetch()){
            $msg = "Erreur : Cet email est déjà utilisé";
        } 
        
        else {
            $stmt = $pdo->prepare("SELECT idUtilisateur FROM Utilisateur WHERE telephone = ?");
            $stmt->execute([$telephone]);
            if($stmt->fetch()){
                $msg = "Erreur : Ce numéro de téléphone est déjà utilisé";
            }
            
            else {
                $stmt = $pdo->prepare("SELECT idElecteur FROM Electeur WHERE numeroElecteur = ?");
                $stmt->execute([$numeroElecteur]);
                if($stmt->fetch()){
                    $msg = "Erreur : Ce numéro électeur existe déjà";
                } 
                
                else {
                    $pdo->beginTransaction();
                    
                    $idRoleVotant = 2;
                    $motDePasse = password_hash('1234', PASSWORD_DEFAULT);
                    
    
                    $stmt = $pdo->prepare("INSERT INTO Utilisateur (nom, prenom, email, telephone, motDePasseHash, idRole) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$nom, $prenom, $email, $telephone, $motDePasse, $idRoleVotant]);
                    $idUtilisateur = $pdo->lastInsertId();
                    
                    $stmt = $pdo->prepare("INSERT INTO Electeur (idUtilisateur, numeroElecteur, numeroCNI, dateNaissance, aVote) VALUES (?, ?, ?, ?, 0)");
                    $stmt->execute([$idUtilisateur, $numeroElecteur, $numeroCNI, $dateNaissance]);
                    
                    $pdo->commit();
                    $msg = "Électeur $prenom $nom ajouté !";
                }
            }
        }
    } catch(Exception $e){
        $pdo->rollBack();
        $msg = "Erreur : " . $e->getMessage();
    }
}
$electeurs = $pdo->query("SELECT u.nom, u.prenom, u.telephone, e.numeroElecteur, e.aVote FROM Electeur e JOIN Utilisateur u ON e.idUtilisateur = u.idUtilisateur ORDER BY e.idElecteur DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
    <body>
<h1>Admin MaVoix CI</h1>
<?php if(isset($msg)) echo "<p>$msg</p>"; ?>
<form method="POST">
    <input type="text" name="nom" placeholder="Nom" required>
    <input type="text" name="prenom" placeholder="Prénom" required><br>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="telephone" placeholder="0700000000" required><br>
    <input type="text" name="numeroElecteur" placeholder="Numéro électeur" required>
    <input type="text" name="numeroCNI" placeholder="Numéro CNI" required><br>
    <input type="date" name="dateNaissance" required><br><br>
    <button type="submit" name="ajouter">Ajouter électeur</button>
</form>
<h2>Liste</h2>
<table border="1" cellpadding="5">
<tr><th>Nom</th><th>Prénom</th><th>Téléphone</th><th>N° Électeur</th><th>A voté</th></tr>
<?php foreach($electeurs as $e): ?>
<tr>
<td><?= $e['nom']; ?></td><td><?= $e['prenom']; ?></td><td><?= $e['telephone']; ?></td>
<td><?= $e['numeroElecteur']; ?></td><td><?= $e['aVote'] ? 'Oui' : 'Non'; ?></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>
