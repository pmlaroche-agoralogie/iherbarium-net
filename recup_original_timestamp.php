<?php

$SERVEUR= 'localhost';
$BASE = 'bswphistnat';
$USER = 'usrwphistnat';
$MDP = 'osh7z9Pxjs';

$connect_db = mysqli_connect($SERVEUR,$USER,$MDP,$BASE);
	
$table = 'iherba_observations';

// on vérifie les dates de dépôt qui ont déjà été enregistrées
$requetesql = "SELECT * FROM ".$table;
$resultat = mysqli_query($connect_db,$requetesql);	


$nb = 0;
			
while ($rangee = mysqli_fetch_assoc($resultat)) {

	// id de l'observation
	$idobs = $rangee['idobs'];
	
	$requetesql2 = 'SELECT DateTimeOriginal FROM iherba_photos WHERE id_obs='.$idobs.' order by idphotos DESC LIMIT 1';
	$resultat2 = mysqli_query($connect_db,$requetesql2);	

	$num_rows = mysqli_num_rows($resultat2);	
	
	if ($num_rows > 0){
		$rangee2 = mysqli_fetch_assoc($resultat2);
		
		$date_prise_de_vue = $rangee2['DateTimeOriginal'];
		if ($date_prise_de_vue != '0000-00-00 00:00:00'){
			// on enregistre la date de prise de vue dans le champ correspondant
			$requetesql_update = "UPDATE ".$table." set original_timestamp = '".$date_prise_de_vue."' WHERE idobs = ".$idobs;
			echo "<br>".$requetesql_update;
			$resultat_update = mysqli_query($connect_db,$requetesql_update);

			$nb++;
		}
	}
}


echo "<br>Nombre d'observations modifi&eacute;es : ".$nb;	

?>