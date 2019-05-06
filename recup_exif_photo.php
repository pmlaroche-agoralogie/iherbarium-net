<?php

$SERVEUR= 'localhost';
$BASE = 'bswphistnat';
$USER = 'usrwphistnat';
$MDP = 'osh7z9Pxjs';

$connect_db = mysqli_connect($SERVEUR,$USER,$MDP,$BASE);

	
	
$table = 'iherba_photos';
$dossier_photo = 'medias/sources/';
	
//on boucle sur les photos
$requetesql = "SELECT * FROM ".$table." where nom_photo_final != '' AND all_exif_fields = ''";
$resultat = mysqli_query($connect_db,$requetesql);	

$nb = 0;
			
while ($rangee = mysqli_fetch_assoc($resultat)) {

	// on récupère l'id de la photo
	$idphotos = $rangee['idphotos'];
	// on récupère le nom de la photo
	$nom_photo = $rangee['nom_photo_final'];
	
	if (file_exists($dossier_photo.$nom_photo)) {

		if($exif=exif_read_data($dossier_photo.$nom_photo, 'GPS', true)){

			$exif_encode = json_encode($exif);
				
			// on enregistre le EXIF dans le champ correspondant
			$requetesql_update = "UPDATE ".$table." set all_exif_fields = '".addslashes($exif_encode)."' WHERE idphotos = ".$idphotos;
			$resultat_update = mysqli_query($connect_db,$requetesql_update);	
			$nb++;

		}
	
	}

}

echo "<br>Nombre de photos modifi&eacute;es : ".$nb;	


?>