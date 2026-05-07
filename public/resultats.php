<?php
require_once '../config/database.php';
$resultats = $pdo->query("SELECT c.prenom, c.nom, p.sigle, r.nombreVoix, r.pourcentage 
                          FROM ResultatTemp r 
                          JOIN Candidat c ON r.idCandidat = c.idCandidat 
                          LEFT JOIN PartiPolitique p ON c.idParti = p.idParti 
                          ORDER BY r.nombreVoix DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>
    <head><meta http-equiv="refresh" content="5">
</head>
<body>
<h1>Résultats Temps Réel</h1>
<table border="1" cellpadding="10">
<tr><th>Candidat</th><th>Parti</th><th>Voix</th><th>%</th></tr>
<?php foreach($resultats as $r): ?>
<tr><td><?= $r['prenom'].' '.$r['nom']; ?></td><td><?= $r['sigle']; ?></td><td><?= $r['nombreVoix']; ?></td><td><?= $r['pourcentage']; ?>%</td></tr>
<?php endforeach; ?>
</table>
</body>
</html>
