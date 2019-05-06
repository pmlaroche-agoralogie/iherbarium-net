<?php

$SERVEUR= 'localhost';
$BASE = 'bswphistnat';
$USER = 'usrwphistnat';
$MDP = 'osh7z9Pxjs';

$connect_db = mysqli_connect($SERVEUR,$USER,$MDP,$BASE);
	
$table = 'iherba_photos';

// on vérifie les dates de dépôt qui ont déjà été enregistrées
$requetesql = "SELECT idphotos,all_exif_fields FROM ".$table." WHERE all_exif_fields != ''";
$resultat = mysqli_query($connect_db,$requetesql);	


$nb = 0;
			
while ($rangee = mysqli_fetch_assoc($resultat)) {

	// id de la photo
	$idphotos = $rangee['idphotos'];
	
	// récupère le contenu du champ exif
	$infos = $rangee['all_exif_fields'];
	$tab_infos = json_decode($infos,TRUE);

	// date de depot du champ exif en version Y-m-d H:i:s
	if (isset($tab_infos['EXIF']['DateTimeOriginal'])){
		$date_prise_de_vue = $tab_infos['EXIF']['DateTimeOriginal'];
	
		// on enregistre la date de prise de vue dans le champ correspondant
		$requetesql_update = "UPDATE ".$table." set DateTimeOriginal = '".$date_prise_de_vue."' WHERE idphotos = ".$idphotos;
		$resultat_update = mysqli_query($connect_db,$requetesql_update);
		$nb++;
	}else{
		// pas de date dans le champ exif => on vide le champ datetimeoriginal
		$requetesql_update = "UPDATE ".$table." set DateTimeOriginal = '' WHERE idphotos = ".$idphotos;
		$resultat_update = mysqli_query($connect_db,$requetesql_update);
		
	}
	
}


//on modifie les fiches qui n'ont pas le champ all_exif_fields mais qui ont une date de prise de vue (fausse date)
$requetesql_update = "UPDATE ".$table." set DateTimeOriginal = '' WHERE all_exif_fields = ''";
$resultat_update = mysqli_query($connect_db,$requetesql_update);



echo "<br>Nombre de photos modifi&eacute;es : ".$nb;	

?>